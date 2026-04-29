<?php
/**
 * MembershipHandler.php
 * 
 * Handler untuk menampilkan membership name yang kontekstual
 * berdasarkan asal halaman dan setting masthead (roles vs editorial board)
 * Menampilkan url yang mengarah pada displayMembership.tpl pada navmenu 
 * @author Rochmady and Wizdam Team
 * @version 1.8
 * @date 2025-05-28
 * @compatible OJS 2.4.8.2
 */

class MembershipHandler {
    
    /**
     * Get contextual membership for user
     * 
     * @param object $smarty Smarty template object
     * @return string User membership name
     */
    public static function getUserMembership($smarty) {
        try {
            $journal = Request::getJournal();
            $user = $smarty->get_template_vars('user');
            
            if (!$journal || !$user) {
                return self::getDefaultMembership();
            }
            
            $userId = $user->getId();
            $contextFrom = Request::getUserVar('from');
            $journalId = $journal->getId();
            
            $userMembership = '';
            
            if ($contextFrom == 'editorial') {
                // Legacy support: Check masthead setting untuk backward compatibility
                $useEditorialBoard = self::getEditorialBoardSetting($journalId);
                
                if ($useEditorialBoard) {
                    // useEditorialBoard=1: Editorial Board mode - use groups
                    $userMembership = self::getGroupBasedMembership($userId, $journalId, 1);
                } else {
                    // useEditorialBoard=0: Assigned Roles mode - use roles
                    $userMembership = self::getRoleBasedMembership($userId, $journalId);
                }
            } elseif ($contextFrom == 'team') {
                // Specifically from editorialTeam.tpl - always use roles (assigned roles mode)
                $userMembership = self::getRoleBasedMembership($userId, $journalId);
            } elseif ($contextFrom == 'board') {
                // Specifically from editorialTeamBoard.tpl - always use groups (editorial board mode)
                $userMembership = self::getGroupBasedMembership($userId, $journalId, 1);
            } elseif ($contextFrom == 'roles') {
                // Force roles mode (override everything)
                $userMembership = self::getRoleBasedMembership($userId, $journalId);
            } elseif ($contextFrom == 'membership') {
                // Display Membership - always use groups with context=2
                $userMembership = self::getGroupBasedMembership($userId, $journalId, 2);
            }
            
            return $userMembership ?: self::getDefaultMembership($contextFrom);
            
        } catch (Exception $e) {
            error_log("MembershipHandler::getUserMembership Error: " . $e->getMessage());
            return self::getDefaultMembership();
        }
    }
    
    /**
     * Get editorial board setting (the correct setting name)
     * 
     * @param int $journalId Journal ID
     * @return boolean True if using editorial board, false if using roles
     */
    private static function getEditorialBoardSetting($journalId) {
        try {
            // Use DAORegistry untuk konsistensi dengan OJS
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($journalId);
            
            if ($journal) {
                $setting = $journal->getSetting('useEditorialBoard');
                return ($setting == '1' || $setting === true);
            }
            
            return false; // Default to roles mode (useEditorialBoard = 0)
            
        } catch (Exception $e) {
            error_log("MembershipHandler::getEditorialBoardSetting Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get role-based membership (for assigned roles approach)
     * Only customize Journal Manager → Editor-in-Chief, others use OJS defaults
     * 
     * @param int $userId User ID
     * @param int $journalId Journal ID
     * @return string Role name
     */
    private static function getRoleBasedMembership($userId, $journalId) {
        try {
            // Get user roles with priority order using direct query untuk OJS 2.4.8.2  
            $result = DBConnection::getConn()->Execute(
                "SELECT r.role_id 
                 FROM roles r 
                 WHERE r.user_id = ? AND r.journal_id = ?
                 ORDER BY r.role_id",
                array($userId, $journalId)
            );
            
            if ($result && !$result->EOF) {
                $userRoles = array();
                while (!$result->EOF) {
                    $userRoles[] = $result->fields['role_id'];
                    $result->MoveNext();
                }
                
                // Check for Journal Manager first (special case: 16 → Editor-in-Chief)
                if (in_array(16, $userRoles)) { // ROLE_ID_JOURNAL_MANAGER = 16
                    return 'Editor-in-Chief';
                }
                
                // For other roles, use OJS default role names with correct mapping
                $roleNames = array(
                    256 => 'Editor',           // ROLE_ID_EDITOR
                    512 => 'Section Editor',   // ROLE_ID_SECTION_EDITOR
                    1024 => 'Layout Editor',   // ROLE_ID_LAYOUT_EDITOR
                    2048 => 'Copyeditor',      // ROLE_ID_COPYEDITOR
                    4096 => 'Proofreader',     // ROLE_ID_PROOFREADER
                    8192 => 'Author',          // ROLE_ID_AUTHOR
                    16384 => 'Reviewer',       // ROLE_ID_REVIEWER
                    65536 => 'Reader'          // ROLE_ID_READER
                );
                
                // Priority: Editor > Section Editor > Layout Editor > Copyeditor > Proofreader
                $editorialPriority = array(256, 512, 1024, 2048, 4096);
                
                foreach ($editorialPriority as $roleId) {
                    if (in_array($roleId, $userRoles)) {
                        return isset($roleNames[$roleId]) ? $roleNames[$roleId] : 'Editorial Team Member';
                    }
                }
                
                // If no editorial role found, return first role
                if (!empty($userRoles)) {
                    $firstRole = $userRoles[0];
                    return isset($roleNames[$firstRole]) ? $roleNames[$firstRole] : 'Editorial Team Member';
                }
            }
            
            return '';
            
        } catch (Exception $e) {
            error_log("MembershipHandler::getRoleBasedMembership Error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Get group-based membership
     * 
     * @param int $userId User ID
     * @param int $journalId Journal ID  
     * @param int $context Context filter (1=editorial, 2=membership)
     * @return string Group title
     */
    private static function getGroupBasedMembership($userId, $journalId, $context) {
        try {
            // Use direct database query untuk OJS 2.4.8.2 compatibility
            $result = DBConnection::getConn()->Execute(
                "SELECT gs.setting_value as title
                 FROM groups g 
                 LEFT JOIN group_settings gs ON (g.group_id = gs.group_id AND gs.setting_name = 'title' AND gs.locale = ?) 
                 INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
                 WHERE g.assoc_type = 256 AND g.assoc_id = ? AND g.about_displayed = 1 
                 AND gm.user_id = ? AND g.context = ?
                 ORDER BY g.seq
                 LIMIT 1",
                array(AppLocale::getLocale(), $journalId, $userId, $context)
            );
            
            if ($result && !$result->EOF) {
                return $result->fields['title'];
            }
            
            return '';
            
        } catch (Exception $e) {
            error_log("MembershipHandler::getGroupBasedMembership Error: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Setup navigation groups for template - MINIMAL NO-ASSIGN VERSION
     * Method ini HANYA return data, TIDAK assign apapun ke Smarty
     * 
     * @param object $smarty Smarty template object
     */
    public static function setupNavigationGroups($smarty) {
        // TIDAK MELAKUKAN ASSIGNMENT APAPUN KE SMARTY
        // Hanya log bahwa method dipanggil
        try {
            error_log("MembershipHandler::setupNavigationGroups called - doing nothing to avoid conflicts");
        } catch (Exception $e) {
            error_log("MembershipHandler::setupNavigationGroups Error: " . $e->getMessage());
        }
    }
    
    /**
     * Get navigation groups data untuk digunakan langsung di template
     * Method ini return data tanpa assign ke Smarty
     * 
     * @param object $smarty Smarty template object
     * @return array Array of group data
     */
    public static function getNavigationGroupsData($smarty) {
        try {
            $journal = Request::getJournal();
            
            if (!$journal) {
                return array();
            }
            
            $journalId = $journal->getId();
            
            // Get groups data dan format sebagai array associative
            $result = DBConnection::getConn()->Execute(
                "SELECT DISTINCT g.group_id, g.seq, gs.setting_value as title
                 FROM groups g 
                 LEFT JOIN group_settings gs ON (g.group_id = gs.group_id AND gs.setting_name = 'title' AND gs.locale = ?) 
                 INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
                 WHERE g.assoc_type = 256 AND g.assoc_id = ? AND g.about_displayed = 1 AND g.context = 2
                 ORDER BY g.seq",
                array(AppLocale::getLocale(), $journalId)
            );
            
            $displayGroups = array();
            if ($result && !$result->EOF) {
                while (!$result->EOF) {
                    // Create array associative yang template bisa akses sebagai array
                    $groupArray = array(
                        'group_id' => $result->fields['group_id'],
                        'title' => $result->fields['title'],
                        'sequence' => $result->fields['seq']
                    );
                    
                    $displayGroups[] = $groupArray;
                    $result->MoveNext();
                }
            }
            
            return $displayGroups;
            
        } catch (Exception $e) {
            error_log("MembershipHandler::getNavigationGroupsData Error: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Get navigation groups - Return array associative
     * 
     * @param object $smarty Smarty template object
     * @return array Array associative untuk template compatibility
     */
    public static function getNavigationGroups($smarty) {
        try {
            $journal = Request::getJournal();
            
            if (!$journal) {
                return array();
            }
            
            $journalId = $journal->getId();
            
            // Get groups data dan format sebagai array associative
            $result = DBConnection::getConn()->Execute(
                "SELECT DISTINCT g.group_id, g.seq, gs.setting_value as title
                 FROM groups g 
                 LEFT JOIN group_settings gs ON (g.group_id = gs.group_id AND gs.setting_name = 'title' AND gs.locale = ?) 
                 INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
                 WHERE g.assoc_type = 256 AND g.assoc_id = ? AND g.about_displayed = 1 AND g.context = 2
                 ORDER BY g.seq",
                array(AppLocale::getLocale(), $journalId)
            );
            
            $displayGroups = array();
            if ($result && !$result->EOF) {
                while (!$result->EOF) {
                    // Create array associative
                    $groupArray = array(
                        'group_id' => $result->fields['group_id'],
                        'title' => $result->fields['title'],
                        'sequence' => $result->fields['seq']
                    );
                    
                    $displayGroups[] = $groupArray;
                    $result->MoveNext();
                }
            }
            
            return $displayGroups;
            
        } catch (Exception $e) {
            error_log("MembershipHandler::getNavigationGroups Error: " . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Check if displayMembership groups exist
     * MINIMAL function that won't interfere with template system
     * 
     * @param object $smarty Smarty template object
     * @return boolean True if displayMembership groups exist
     */
    public static function hasDisplayMembershipGroups($smarty) {
        try {
            $journal = Request::getJournal();
            
            if (!$journal) {
                return false;
            }
            
            $journalId = $journal->getId();
            
            // Simple COUNT query to avoid object conflicts
            $result = DBConnection::getConn()->Execute(
                "SELECT COUNT(*) as group_count
                 FROM groups g 
                 INNER JOIN group_memberships gm ON (g.group_id = gm.group_id)
                 WHERE g.assoc_type = 256 AND g.assoc_id = ? AND g.about_displayed = 1 AND g.context = 2",
                array($journalId)
            );
            
            if ($result && !$result->EOF) {
                return ($result->fields['group_count'] > 0);
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("MembershipHandler::hasDisplayMembershipGroups Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get default membership based on context
     * 
     * @param string $context Context parameter
     * @return string Default membership name
     */
    private static function getDefaultMembership($context = null) {
        switch ($context) {
            case 'membership':
                return 'Member';
            case 'editorial':
            case 'roles':
            case 'board':
            default:
                return 'Editorial Team Member';
        }
    }
    
    /**
     * Get version info
     * 
     * @return array Version information
     */
    public static function getVersion() {
        return array(
            'version' => '1.8',
            'date' => '2025-05-28',
            'author' => 'Custom Development',
            'compatible' => 'OJS 2.4.8.2',
            'description' => 'Safe implementation - methods exist but avoid template conflicts'
        );
    }
}
?>
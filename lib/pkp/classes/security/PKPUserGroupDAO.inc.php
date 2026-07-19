<?php
declare(strict_types=1);

/**
 * @file classes/security/PKPUserGroupDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPUserGroupDAO
 * @ingroup security
 * @see UserGroup
 *
 * @brief Operations for retrieving and modifying User Groups and user group assignments.
 */

import('lib.pkp.classes.security.UserGroup');

class PKPUserGroupDAO extends DAO {

    /** @var UserDAO a shortcut to get the UserDAO **/
    public $userDao;

    /** @var UserGroupAssignmentDAO a shortcut to get the UserGroupAssignmentDAO **/
    public $userGroupAssignmentDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->userDao = DAORegistry::getDAO('UserDAO');
        $this->userGroupAssignmentDao = DAORegistry::getDAO('UserGroupAssignmentDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPUserGroupDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPUserGroupDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        
        $this->__construct();
    }

    /**
     * create new data object
     * (allows DAO to be subclassed)
     * @return UserGroup
     */
    public function newDataObject() {
        return new UserGroup();
    }

    /**
     * Internal function to return a UserGroup object from a row.
     * @param array $row
     * @return UserGroup
     */
    public function _returnFromRow($row) {
        $userGroup = $this->newDataObject();
        $userGroup->setId($row['user_group_id']);
        $userGroup->setRoleId($row['role_id']);
        $userGroup->setContextId($row['context_id']);
        $userGroup->setPath($row['path']);
        $userGroup->setDefault($row['is_default']);

        $this->getDataObjectSettings('user_group_settings', 'user_group_id', $row['user_group_id'], $userGroup);

        // [LUMERA FIX] Modern array syntax, removed legacy reference &
        HookRegistry::dispatch('PKPUserGroupDAO::_returnFromRow', [$userGroup, $row]);

        return $userGroup;
    }

    /**
     * Insert a user group.
     * @param UserGroup $userGroup
     * @return int Inserted ID
     */
    public function insertUserGroup($userGroup) {
        $this->update(
            'INSERT INTO user_groups (role_id, path, context_id, is_default) VALUES (?, ?, ?, ?)',
            [
                (int) $userGroup->getRoleId(),
                $userGroup->getPath(),
                (int) $userGroup->getContextId(),
                (int) $userGroup->getDefault()
            ]
        );

        $userGroup->setId($this->getInsertUserGroupId());
        $this->updateLocaleFields($userGroup);
        return $this->getInsertUserGroupId();
    }

    /**
     * Delete a user group by its id
     * will also delete related settings and all the assignments to this group
     * @param int $contextId
     * @param int $userGroupId
     * @return boolean
     */
    public function deleteById($contextId, $userGroupId) {
        $ret1 = $this->userGroupAssignmentDao->deleteAssignmentsByUserGroupId($userGroupId);
        $ret2 = $this->update('DELETE FROM user_group_settings WHERE user_group_id = ?', [(int) $userGroupId]);
        $ret3 = $this->update('DELETE FROM user_groups WHERE user_group_id = ?', [(int) $userGroupId]);
        $ret4 = $this->removeAllStagesFromGroup($contextId, $userGroupId);
        return $ret1 && $ret2 && $ret3 && $ret4;
    }

    /**
     * Delete a user group.
     * will also delete related settings and all the assignments to this group
     * @param UserGroup $userGroup
     * @return boolean
     */
    public function deleteUserGroup($userGroup) {
        return $this->deleteById($userGroup->getContextId(), $userGroup->getId());
    }

    /**
     * Delete a user group by its context id
     * @param int $contextId
     * @return boolean
     */
    public function deleteByContextId($contextId) {
        $result = $this->retrieve('SELECT user_group_id FROM user_groups WHERE context_id = ?', [(int) $contextId]);

        $returner = true;
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $userGroupId = (int) $row['user_group_id'];

            $ret1 = $this->update('DELETE FROM user_group_stage WHERE user_group_id = ?', [$userGroupId]);
            $ret2 = $this->update('DELETE FROM user_group_settings WHERE user_group_id = ?', [$userGroupId]);
            $ret3 = $this->update('DELETE FROM user_groups WHERE user_group_id = ?', [$userGroupId]);

            $returner = $returner && $ret1 && $ret2 && $ret3;
            $result->MoveNext();
        }
        $result->Close();

        return $returner;
    }

    /**
     * Get the ID of the last inserted user group.
     * @return int
     */
    public function getInsertUserGroupId() {
        return $this->getInsertId('user_groups', 'user_group_id');
    }

    /**
     * Get field names for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), ['name', 'abbrev']);
    }

    /**
     * Update the localized data for this object
     * @param UserGroup $userGroup
     */
    public function updateLocaleFields(&$userGroup) {
        $this->updateDataObjectSettings('user_group_settings', $userGroup, [
            'user_group_id' => (int) $userGroup->getId()
        ]);
    }

    /**
     * Get an individual user group
     * @param int $userGroupId
     * @param int|null $contextId
     * @return UserGroup|null
     */
    public function getById($userGroupId, $contextId = null) {
        $params = [(int) $userGroupId];
        if ($contextId !== null) {
            $params[] = (int) $contextId;
        }
        
        $result = $this->retrieve(
            'SELECT user_group_id, context_id, role_id, path, is_default FROM user_groups WHERE user_group_id = ?' . ($contextId !== null ? ' AND context_id = ?' : ''),
            $params
        );

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_returnFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Get a single default user group with a particular roleId
     * @param int $contextId
     * @param int $roleId
     * @return UserGroup|null
     */
    public function getDefaultByRoleId($contextId, $roleId) {
        $returner = null;
        $allDefaults = $this->getByRoleId($contextId, $roleId, true);
        if (!$allDefaults->eof()) {
            $returner = $allDefaults->next();
        }
        return $returner;
    }

    /**
     * Get all user groups belonging to a role
     * @param int $contextId
     * @param int $roleId
     * @param boolean $default
     * @return DAOResultFactory
     */
    public function getByRoleId($contextId, $roleId, $default = false) {
        $params = [(int) $contextId, (int) $roleId];
        if ($default) {
            $params[] = 1;
        }
        
        $result = $this->retrieve(
            'SELECT * FROM user_groups WHERE context_id = ? AND role_id = ?' . ($default ? ' AND is_default = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnFromRow');
    }

    /**
     * Get an array of user group ids belonging to a given role
     * @param int $roleId
     * @param int|null $contextId
     * @return array
     */
    public function getUserGroupIdsByRoleId($roleId, $contextId = null) {
        $sql = 'SELECT user_group_id FROM user_groups WHERE role_id = ?';
        $params = [(int) $roleId];

        if ($contextId) {
            $sql .= ' AND context_id = ?';
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve($sql, $params);

        $userGroupIds = [];
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $userGroupIds[] = (int) $row['user_group_id'];
            $result->MoveNext();
        }
        $result->Close();
        
        return $userGroupIds;
    }

    /**
     * Check if a user is in a particular user group
     * @param int $userId
     * @param int $userGroupId
     * @return boolean
     */
    public function userInGroup($userId, $userGroupId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id WHERE uug.user_id = ? AND ug.user_group_id = ?',
            [(int) $userId, (int) $userGroupId]
        );

        $returner = false;
        if (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = isset($row['count']) && ((int) $row['count']) > 0;
        }

        $result->Close();
        return $returner;
    }

    /**
     * Check if a user is in any user group
     * @param int $userId
     * @param int|null $contextId
     * @return boolean
     */
    public function userInAnyGroup($userId, $contextId = null) {
        $params = [(int) $userId];
        if ($contextId) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id WHERE uug.user_id = ?' . ($contextId ? ' AND ug.context_id = ?' : ''),
            $params
        );

        $returner = false;
        if (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = isset($row['count']) && ((int) $row['count']) > 0;
        }

        $result->Close();
        return $returner;
    }

    /**
     * Retrieve user groups to which a user is assigned.
     * @param int $userId
     * @param int|null $contextId
     * @return DAOResultFactory
     */
    public function getByUserId($userId, $contextId = null) {
        $params = [(int) $userId];
        if ($contextId) {
            $params[] = (int) $contextId;
        }
        
        $result = $this->retrieve(
            'SELECT ug.* FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id WHERE uug.user_id = ?' . ($contextId ? ' AND ug.context_id = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnFromRow');
    }

    /**
     * Validation check to see if user group exists for a given context
     * @param int $contextId
     * @param int $userGroupId
     * @return bool
     */
    public function contextHasGroup($contextId, $userGroupId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM user_groups ug WHERE ug.user_group_id = ? AND ug.context_id = ?',
            [(int) $userGroupId, (int) $contextId]
        );

        $returner = false;
        if (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = isset($row['count']) && ((int) $row['count']) > 0;
        }

        $result->Close();
        return $returner;
    }

    /**
     * Retrieve user groups for a given context (all contexts if null)
     * @param int|null $contextId
     * @return DAOResultFactory
     */
    public function getByContextId($contextId = null) {
        $params = [];
        if ($contextId) {
            $params[] = (int) $contextId;
        }
        
        $result = $this->retrieve(
            'SELECT ug.* FROM user_groups ug' . ($contextId ? ' WHERE ug.context_id = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnFromRow');
    }

    /**
     * Retrieve the number of users associated with the specified context.
     * @param int $contextId
     * @param int|null $userGroupId
     * @param int|null $roleId
     * @return int
     */
    public function getContextUsersCount($contextId, $userGroupId = null, $roleId = null) {
        $params = [(int) $contextId];
        if ($userGroupId) {
            $params[] = (int) $userGroupId;
        }
        if ($roleId) {
            $params[] = (int) $roleId;
        }

        $result = $this->retrieve(
            'SELECT COUNT(DISTINCT(uug.user_id)) AS user_count FROM user_groups ug JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id WHERE context_id = ?' . ($userGroupId ? ' AND ug.user_group_id = ?' : '') . ($roleId ? ' AND ug.role_id = ?' : ''),
            $params
        );

        $returner = 0;
        if (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) $row['user_count'];
        }

        $result->Close();
        return $returner;
    }

    /**
     * return an Iterator of User objects given the search parameters
     * @param int|null $contextId
     * @param string|null $searchType
     * @param string|null $search
     * @param string|null $searchMatch
     * @param DBResultRange|null $dbResultRange
     * @return DAOResultFactory|null
     */
    public function getUsersByContextId($contextId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null) {
        return $this->getUsersById(null, $contextId, $searchType, $search, $searchMatch, $dbResultRange);
    }

    /**
     * Find users that don't have a given role
     * @param int $roleId
     * @param int|null $contextId
     * @param string|null $search
     * @return DAOResultFactory
     */
    public function getUsersNotInRole($roleId, $contextId = null, $search = null) {
        $params = [(int) $roleId];
        if ($contextId) {
            $params[] = (int) $contextId;
        }
        if (isset($search)) {
            // [LUMERA FIX] Modern array syntax
            $params = array_merge($params, array_pad([], 5, '%' . $search . '%'));
        }

        $result = $this->retrieve(
            'SELECT DISTINCT u.* FROM users u, user_groups ug, user_user_groups uug WHERE ug.user_group_id = uug.user_group_id AND u.user_id = uug.user_id AND ug.role_id <> ?' .
            ($contextId ? ' AND ug.context_id = ?' : '') .
            (isset($search) ? ' AND (u.first_name LIKE ? OR u.middle_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ? OR u.username LIKE ?)' : ''),
            $params
        );

        return new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
    }

    /**
     * return an Iterator of User objects given the search parameters
     * @param int|null $userGroupId
     * @param int|null $contextId
     * @param string|null $searchType
     * @param string|null $search
     * @param string|null $searchMatch
     * @param DBResultRange|null $dbResultRange
     * @return DAOResultFactory|null
     */
    public function getUsersById($userGroupId = null, $contextId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null) {
        $paramArray = [];

        if (isset($userGroupId)) {
            $paramArray[] = (int) $userGroupId;
        }
        if (isset($contextId)) {
            $paramArray[] = (int) $contextId;
        }

        if ($contextId === null && $userGroupId === null) {
            return null;
        }

        $searchSql = $this->_getSearchSql($searchType, $search, $searchMatch, $paramArray);

        $sql = 'SELECT DISTINCT u.*
            FROM users AS u
            LEFT JOIN user_settings us ON (us.user_id = u.user_id AND us.setting_name = "affiliation")
            LEFT JOIN user_interests ui ON (u.user_id = ui.user_id)
            LEFT JOIN controlled_vocab_entry_settings cves ON (ui.controlled_vocab_entry_id = cves.controlled_vocab_entry_id)
            LEFT JOIN user_user_groups uug ON (uug.user_id = u.user_id)
            LEFT JOIN user_groups ug ON (ug.user_group_id = uug.user_group_id) WHERE';

        $sql .= (isset($userGroupId) ? ' ug.user_group_id = ? ' . (isset($contextId) ? 'AND ' : '') : ' ');
        $sql .= (isset($contextId) ? ' ug.context_id = ? ' : ' ') . $searchSql;

        $result = $this->retrieveRange($sql, $paramArray, $dbResultRange);

        return new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
    }

    /**
     * Retrieve those users with no group assignments in any press.
     * @param array|null $filter
     * @param boolean $allowDisabled
     * @param DBResultRange|null $dbResultRange
     * @return DAOResultFactory
     */
    public function getUsersWithNoUserGroupAssignments($filter = null, $allowDisabled = true, $dbResultRange = null) {
        $sql = 'SELECT DISTINCT u.*
            FROM users AS u
            LEFT JOIN user_settings us ON (us.user_id = u.user_id AND us.setting_name = "affiliation")
            LEFT JOIN user_interests ui ON (u.user_id = ui.user_id)
            LEFT JOIN controlled_vocab_entry_settings cves ON (ui.controlled_vocab_entry_id = cves.controlled_vocab_entry_id)
            LEFT JOIN user_user_groups uug ON u.user_id=uug.user_id WHERE uug.user_group_id IS NULL ';

        $sql .= ($allowDisabled ? '' : ' AND u.disabled = 0');

        $searchSql = '';
        $paramArray = [];

        if (isset($filter)) {
            $searchType = $filter['searchType'] ?? null;
            $search = $filter['search'] ?? null;
            $searchMatch = $filter['searchMatch'] ?? null;

            $searchSql = $this->_getSearchSql($searchType, $search, $searchMatch, $paramArray);
            $sql .= $searchSql;
        }

        $result = $this->retrieveRange($sql, $paramArray, $dbResultRange);
        return new DAOResultFactory($result, $this->userDao, '_returnUserFromRowWithData');
    }

    //
    // UserGroupAssignment related
    //
    /**
     * Delete all user group assignments for a given userId
     * @param int $userId
     * @param int|null $userGroupId
     */
    public function deleteAssignmentsByUserId($userId, $userGroupId = null) {
        $this->userGroupAssignmentDao->deleteByUserId($userId, $userGroupId);
    }

    /**
     * Delete all assignments to a given user group
     * @param int $userGroupId
     */
    public function deleteAssignmentsByUserGroupId($userGroupId) {
        $this->userGroupAssignmentDao->deleteAssignmentsByUserGroupId($userGroupId);
    }

    /**
     * Remove all user group assignments for a given user in a context
     * @param int $contextId
     * @param int|null $userId
     */
    public function deleteAssignmentsByContextId($contextId, $userId = null) {
        $this->userGroupAssignmentDao->deleteAssignmentsByContextId($contextId, $userId);
    }

    /**
     * Assign a given user to a given user group
     * @param int $userId
     * @param int $groupId
     * @return int|bool
     */
    public function assignUserToGroup($userId, $groupId) {
        $assignment = $this->userGroupAssignmentDao->newDataObject();
        $assignment->setUserId($userId);
        $assignment->setUserGroupId($groupId);
        return $this->userGroupAssignmentDao->insertAssignment($assignment);
    }

    /**
     * remove a given user from a given user group
     * @param int $userId
     * @param int $groupId
     * @param int $contextId
     */
    public function removeUserFromGroup($userId, $groupId, $contextId) {
        $assignments = $this->userGroupAssignmentDao->getByUserId($userId, $contextId);
        while ($assignment = $assignments->next()) {
            if ($assignment->getUserGroupId() == $groupId) {
                $this->userGroupAssignmentDao->deleteAssignment($assignment);
            }
            // [LUMERA] Removed unset($assignment). PHP 8 GC handles this efficiently.
        }
    }

    /**
     * Delete all stage assignments in a user group.
     * @param int $contextId
     * @param int $userGroupId
     * @return bool Returns true on success or if stage features are not supported in this version.
     */
    public function removeAllStagesFromGroup($contextId, $userGroupId) {
        if (!method_exists($this, 'getAssignedStagesByUserGroupId')) {
            return true; 
        }
        $assignedStages = $this->getAssignedStagesByUserGroupId($contextId, $userGroupId);
        if (!empty($assignedStages)) {
            foreach ($assignedStages as $stageId => $stageLocaleKey) {
                if (method_exists($this, 'removeGroupFromStage')) {
                    $this->removeGroupFromStage($contextId, $userGroupId, $stageId);
                } else {
                    // Opsional: Catat untuk melacak fitur yang hilang
                    error_log("[LUMERA WARNING] removeGroupFromStage not found for stage: $stageId");
                }
            }
        }

        return true;
    }

    //
    // Extra settings (not handled by rest of Dao)
    //
    /**
     * Method for update a userGroup setting
     * @param int $userGroupId
     * @param string $name
     * @param mixed $value
     * @param string|null $type data type of the setting. If omitted, type will be guessed
     * @param boolean $isLocalized
     */
    public function updateSetting($userGroupId, $name, $value, $type = null, $isLocalized = false) {
        $keyFields = ['setting_name', 'locale', 'user_group_id'];

        if (!$isLocalized) {
            $value = $this->convertToDB($value, $type);
            $this->replace('user_group_settings',
                [
                    'user_group_id' => (int) $userGroupId,
                    'setting_name' => $name,
                    'setting_value' => $value,
                    'setting_type' => $type,
                    'locale' => ''
                ],
                $keyFields
            );
        } else {
            if (is_array($value)) {
                foreach ($value as $locale => $localeValue) {
                    $this->update('DELETE FROM user_group_settings WHERE user_group_id = ? AND setting_name = ? AND locale = ?', [(int) $userGroupId, $name, $locale]);
                    if (empty($localeValue)) {
                        continue;
                    }
                    $type = null;
                    $this->update('INSERT INTO user_group_settings (user_group_id, setting_name, setting_value, setting_type, locale) VALUES (?, ?, ?, ?, ?)',
                        [
                            (int) $userGroupId, $name, $this->convertToDB($localeValue, $type), $type, $locale
                        ]
                    );
                }
            }
        }
    }

    /**
     * Retrieve a context setting value.
     * @param int $userGroupId
     * @param string $name
     * @param string|null $locale
     * @return mixed
     */
    public function getSetting($userGroupId, $name, $locale = null) {
        $params = [(int) $userGroupId, $name];
        if ($locale) {
            $params[] = $locale;
        }
        
        $result = $this->retrieve(
            'SELECT setting_name, setting_value, setting_type, locale FROM user_group_settings WHERE user_group_id = ? AND setting_name = ?' . ($locale ? ' AND locale = ?' : ''),
            $params
        );

        $recordCount = $result->RecordCount();
        $returner = false;
        
        if ($recordCount == 1) {
            $row = $result->GetRowAssoc(false);
            $returner = $this->convertFromDB($row['setting_value'], $row['setting_type']);
        } elseif ($recordCount > 1) {
            $returner = [];
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $returner[$row['locale']] = $this->convertFromDB($row['setting_value'], $row['setting_type']);
                $result->MoveNext();
            }
            $result->Close();
        }
        return $returner;
    }

    //
    // Install/Defaults with settings
    //
    /**
     * Load the XML file and move the settings to the DB
     * @param int $contextId
     * @param string $filename
     * @return bool
     */
    public function installSettings($contextId, $filename) {
        $xmlParser = new PKPXMLParser();
        $tree = $xmlParser->parse($filename);

        if (!$tree) {
            $xmlParser->destroy();
            return false;
        }

        // [LUMERA FIX 3] Define fallback constants if this is a 3.x backport running on 2.4.x
        // This prevents Fatal Error: Undefined constant
        $stageProduction = defined('WORKFLOW_STAGE_ID_PRODUCTION') ? WORKFLOW_STAGE_ID_PRODUCTION : 4;
        $stageSubmission = defined('WORKFLOW_STAGE_ID_SUBMISSION') ? WORKFLOW_STAGE_ID_SUBMISSION : 1;

        foreach ($tree->getChildren() as $setting) {
            $roleId = hexdec($setting->getAttribute('roleId'));
            $nameKey = $setting->getAttribute('name');
            $abbrevKey = $setting->getAttribute('abbrev');
            $defaultStages = explode(',', $setting->getAttribute('stages'));
            
            $userGroup = $this->newDataObject();
            $role = new Role();
            $userGroup->setRoleId($roleId);
            
            $path = method_exists($role, 'getPath') ? $role->getPath() : 'default';
            $userGroup->setPath($path);
            
            $userGroup->setContextId($contextId);
            $userGroup->setDefault(true);

            $userGroupId = $this->insertUserGroup($userGroup);

            foreach ($defaultStages as $stageId) {
                $stageId = (int) trim($stageId);
                if (!empty($stageId) && $stageId <= $stageProduction && $stageId >= $stageSubmission) {
                    if (method_exists($this, 'assignGroupToStage')) {
                        $this->assignGroupToStage($contextId, $userGroupId, $stageId);
                    }
                }
            }

            $this->updateSetting($userGroup->getId(), 'nameLocaleKey', $nameKey);
            $this->updateSetting($userGroup->getId(), 'abbrevLocaleKey', $abbrevKey);
        }

        $this->installLocale(AppLocale::getLocale(), $contextId);

        // Good practice: free XML parser resources after processing
        $xmlParser->destroy();
        
        return true;
    }

    /**
     * use the locale keys stored in the settings table to install the locale settings
     * @param string $locale
     * @param int|null $contextId
     */
    public function installLocale($locale, $contextId = null) {
        $userGroups = $this->getByContextId($contextId);
        while (!$userGroups->eof()) {
            $userGroup = $userGroups->next();
            $nameKey = $this->getSetting($userGroup->getId(), 'nameLocaleKey');
            $this->updateSetting($userGroup->getId(), 'name', [$locale => __($nameKey, null, $locale)], 'string', $locale);

            $abbrevKey = $this->getSetting($userGroup->getId(), 'abbrevLocaleKey');
            $this->updateSetting($userGroup->getId(), 'abbrev', [$locale => __($abbrevKey, null, $locale)], 'string', $locale);
        }
    }

    /**
     * Remove all settings associated with a locale
     * @param string $locale
     */
    public function deleteSettingsByLocale($locale) {
        return $this->update('DELETE FROM user_group_settings WHERE locale = ?', [$locale]);
    }

    /**
     * private function to assemble the SQL for searching users.
     * @param string $searchType the field to search on.
     * @param string $search the keywords to search for.
     * @param string $searchMatch where to match (is, contains, startsWith).
     * @param array $paramArray SQL parameter array reference
     */
    public function _getSearchSql($searchType, $search, $searchMatch, &$paramArray) {
        $searchTypeMap = [
            USER_FIELD_FIRSTNAME => 'u.first_name',
            USER_FIELD_LASTNAME => 'u.last_name',
            USER_FIELD_USERNAME => 'u.username',
            USER_FIELD_EMAIL => 'u.email',
            USER_FIELD_AFFILIATION => 'us.setting_value'
        ];

        $searchSql = '';

        if (!empty($search)) {
            if (!isset($searchTypeMap[$searchType])) {
                $concatFields = ' ( LOWER(CONCAT(' . join(', ', $searchTypeMap) . ')) LIKE ? OR LOWER(cves.setting_value) LIKE ? ) ';
                $search = strtolower($search);
                $words = preg_split('{\s+}', $search);
                $searchFieldMap = [];

                foreach ($words as $word) {
                    $searchFieldMap[] = $concatFields;
                    $term = '%' . $word . '%';
                    $paramArray[] = $term;
                    $paramArray[] = $term;
                }

                $searchSql .= ' AND (  ' . join(' AND ', $searchFieldMap) . '  ) ';
            } else {
                $fieldName = $searchTypeMap[$searchType];
                switch ($searchMatch) {
                    case 'is':
                        $searchSql = "AND LOWER($fieldName) = LOWER(?)";
                        $paramArray[] = $search;
                        break;
                    case 'contains':
                        $searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
                        $paramArray[] = '%' . $search . '%';
                        break;
                    case 'startsWith':
                        $searchSql = "AND LOWER($fieldName) LIKE LOWER(?)";
                        $paramArray[] = $search . '%';
                        break;
                }
            }
        } else {
            switch ($searchType) {
                case USER_FIELD_USERID:
                    $searchSql = 'AND u.user_id = ?';
                    break;
                case USER_FIELD_INITIAL:
                    $searchSql = 'AND LOWER(u.last_name) LIKE LOWER(?)';
                    break;
            }
        }

        $searchSql .= ' ORDER BY u.last_name, u.first_name';

        return $searchSql;
    }
    
}
?>
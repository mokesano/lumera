<?php
declare(strict_types=1);

/**
 * @file classes/user/UserDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserDAO
 * @ingroup user
 * @see PKPUserDAO
 *
 * @brief Basic class describing users existing in the system.
 * [WIZDAM EDITION] PHP 7.4+ Compatible
 */

import('classes.user.User');
import('lib.pkp.classes.user.PKPUserDAO');

class UserDAO extends PKPUserDAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::UserDAO(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Renew a membership to dateEnd + 1 year
     * @param $user User
     */
    public function renewMembership($user){
        $dateEnd = $user->getSetting('dateEndMembership', 0);
        if (!$dateEnd) $dateEnd = 0;
        
        $time = time();
        if ($dateEnd < $time ) $dateEnd = $time;

        $dateEnd = mktime(23, 59, 59, date("m", $dateEnd), date("d", $dateEnd), date("Y", $dateEnd)+1);
        $user->updateSetting('dateEndMembership', $dateEnd, 'date', 0);
    }

    /**
     * Retrieve an array of journal users matching a particular field value.
     * 
     * @param $field int One of the USER_FIELD_* constants
     * @param $match string 'is' or 'contains'
     * @param $value mixed The value to match against
     * @param $allowDisabled boolean Whether to include disabled users
     * @param $journalId int Optional journal ID to limit search to users with roles in that journal
     * @param $dbResultRange DBResultRange optional range to limit results
     * @return DAOResultFactory matching users
     */
    public function getJournalUsersByField($field = USER_FIELD_NONE, $match = null, $value = null, $allowDisabled = true, $journalId = null, $dbResultRange = null) {
        $params = array();
    
        $sql = 'SELECT * FROM users u WHERE 1=1';
        if ($journalId) {
            $sql = 'SELECT u.* FROM users u LEFT JOIN roles r ON u.user_id=r.user_id WHERE (r.journal_id=? or r.role_id IS NULL)';
            $params[] = $journalId;
        }
    
        switch ($field) {
            case USER_FIELD_USERID:
                $sql .= ' AND u.user_id = ?';
                $params[] = $value;
                break;
            case USER_FIELD_USERNAME:
                $sql .= ' AND LOWER(u.username) ' . ($match == 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $match == 'is' ? $value : "%$value%";
                break;
            case USER_FIELD_INITIAL:
                $sql .= ' AND LOWER(u.last_name) LIKE LOWER(?)';
                $params[] = "$value%";
                break;
            case USER_FIELD_EMAIL:
                $sql .= ' AND LOWER(u.email) ' . ($match == 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $match == 'is' ? $value : "%$value%";
                break;
            case USER_FIELD_URL:
                $sql .= ' AND LOWER(u.url) ' . ($match == 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $match == 'is' ? $value : "%$value%";
                break;
            case USER_FIELD_FIRSTNAME:
                $sql .= ' AND LOWER(u.first_name) ' . ($match == 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $match == 'is' ? $value : "%$value%";
                break;
            case USER_FIELD_LASTNAME:
                $sql .= ' AND LOWER(u.last_name) ' . ($match == 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $match == 'is' ? $value : "%$value%";
                break;
        }
    
        $groupSql = ' GROUP BY u.user_id';
        $orderSql = ' ORDER BY u.last_name, u.first_name';
        
        if ($field != USER_FIELD_NONE) {
            $result = $this->retrieveRange($sql . ($allowDisabled?'':' AND u.disabled = 0') . $groupSql . $orderSql, count($params) > 0 ? $params : false, $dbResultRange);
        } else {
            $result = $this->retrieveRange($sql . ($allowDisabled?'':' AND u.disabled = 0') . $groupSql . $orderSql, count($params) > 0 ? $params : false, $dbResultRange);
        }
    
        $returner = new DAOResultFactory($result, $this, '_returnUserFromRowWithData');
        return $returner;
    }
    
    /**
     * Get the list of additional field names.
     * @return array
     */
    public function getAdditionalFieldNames() {
        return array_merge(parent::getAdditionalFieldNames(), array('orcid'));
    }
    
	/**
     * [MOD FORK v7] Mencari user ID berdasarkan ORCID (logika dari {php}).
     * 
     * Get user ID by normalized ORCID.
     * @param $orcid string
     * @return int|null User ID or null if not found
     */
    public function getUserIdByNormalizedOrcid($orcid) {
        if (empty($orcid)) return null;
        
        $result = $this->retrieve(
            // PERBAIKAN: Gunakan kutip satu ('orcid') bukan kutip dua ("orcid").
            // Cegah error pada database PostgreSQL atau mode Strict SQL.
            'SELECT user_id FROM user_settings WHERE setting_name = \'orcid\' AND setting_value LIKE ?',
            array('%' . $orcid)
        );
        
        $userId = null;
        if ($result && !$result->EOF) {
            // PERBAIKAN KECIL: Memastikan output adalah Integer (angka), bukan String "45"
            $userId = (int) $result->fields['user_id'];
        }
        $result->Close();
        return $userId;
    }

    /**
     * Retrieve a user by their ORCID value stored in user_settings.
     * [WIZDAM] Digunakan oleh SSO ORCID untuk lookup saat login dan link/unlink account.
     *
     * orcid disimpan di user_settings (bukan kolom native users table) karena
     * terdaftar di getAdditionalFieldNames(). Format nilai: https://orcid.org/XXXX-XXXX-XXXX-XXXX
     *
     * @param string $orcid Full ORCID URL: https://orcid.org/XXXX-XXXX-XXXX-XXXX
     * @param bool $allowDisabled Sertakan user yang dinonaktifkan, default false
     * @return User|null
     */
    public function getUserByOrcid(string $orcid, bool $allowDisabled = false): ?object {
        if (empty($orcid)) return null;
    
        $result = $this->retrieve(
            'SELECT u.*
             FROM   users u
             INNER JOIN user_settings us
                     ON us.user_id       = u.user_id
                    AND us.setting_name  = \'orcid\'
                    AND us.setting_value = ?'
            . ($allowDisabled ? '' : ' AND u.disabled = 0'),
            [$orcid]
        );
    
        if ($result->RecordCount() == 0) {
            $result->Close();
            return null;
        }
    
        // _returnUserFromRowWithData() agar setting orcid ikut ter-load ke object
        $user = $this->_returnUserFromRowWithData($result->GetRowAssoc(false));
        $result->Close();
        return $user;
    }
    
    /**
     * Retrieve all users yang memiliki ORCID terdaftar di user_settings.
     * [WIZDAM] Berguna untuk migrasi data atau audit ORCID.
     *
     * @return DAOResultFactory
     */
    public function getUsersWithOrcid(): DAOResultFactory {
        $result = $this->retrieve(
            'SELECT u.*
             FROM   users u
             INNER JOIN user_settings us
                     ON us.user_id      = u.user_id
                    AND us.setting_name = \'orcid\'
                    AND us.setting_value != \'\'
             WHERE  u.disabled = 0
             ORDER BY u.last_name, u.first_name'
        );
    
        return new DAOResultFactory($result, $this, '_returnUserFromRowWithData');
    }

    /**
     * [WIZDAM INTEGRATION]
     * Match author to user and retrieve detailed profile data
     * 
     * Get user by author attributes.
     * @param $firstName string
     * @param $lastName string
     * @param $email string
     * @param $orcid string|null
     * @return User|null
     */
    public function getAuthorUserMatch($firstName, $lastName, $email, $orcid) {
        $data = array(
            'found'     => false,
            'userId'    => null,
            'user'      => null,
            'hasImage'  => false,
            'imgUrl'    => '',
            'interests' => array()
        );
    
        // 1. Match User ID
        $userId = null;
    
        // Try ORCID
        if (!empty($orcid)) {
            $cleanOrcid = preg_replace('/(https?:\/\/)?(orcid\.org\/)?/', '', $orcid);
            $result = $this->retrieve(
                "SELECT user_id FROM user_settings WHERE setting_name = 'orcid' AND (setting_value = ? OR setting_value LIKE ?)",
                array($cleanOrcid, '%' . $cleanOrcid . '%')
            );
            if (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $userId = $row['user_id'];
            }
            $result->Close();
        }
    
        // Try Email
        if (!$userId && !empty($email)) {
            $user = $this->getUserByEmail($email);
            if ($user) $userId = $user->getId();
        }
    
        // Try Name
        if (!$userId) {
            $result = $this->retrieve(
                "SELECT user_id FROM users WHERE first_name = ? AND last_name = ?",
                array($firstName, $lastName)
            );
            if (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $userId = $row['user_id'];
            }
            $result->Close();
        }
    
        // 2. Fetch User Data if Found
        if ($userId) {
            $data['found']  = true;
            $data['userId'] = $userId;
    
            // [WIZDAM REFACTOR] Satu getById() menggantikan semua query terpisah.
            // User object membawa semua data — field baru di PKPUser otomatis
            // tersedia tanpa perlu mengubah method ini di masa depan.
            $data['user'] = $this->getById((int) $userId);
    
            // Get Profile Image (Robust Logic — logika filesystem, bukan DB)
            $baseUrl = Request::getBaseUrl();
            $extensions = array('.jpg', '.jpeg', '.png', '.gif');
            $profileImageName = 'profileImage-' . $userId;
    
            // Check public/site/
            foreach ($extensions as $ext) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/public/site/' . $profileImageName . $ext)) {
                    $data['hasImage'] = true;
                    $data['imgUrl'] = $baseUrl . '/public/site/' . $profileImageName . $ext;
                    break;
                }
            }
    
            // Check alternates
            if (!$data['hasImage']) {
                $alternates = array(
                    '/public/site/images/' . $profileImageName,
                    '/public/uploads/users/' . $userId . '/profile'
                );
                foreach ($alternates as $alt) {
                    foreach ($extensions as $ext) {
                        if (file_exists($_SERVER['DOCUMENT_ROOT'] . $alt . $ext)) {
                            $data['hasImage'] = true;
                            $data['imgUrl'] = $baseUrl . $alt . $ext;
                            break 2;
                        }
                    }
                }
            }
    
            // Gravatar Fallback
            if (!$data['hasImage'] && !empty($email)) {
                $data['hasImage'] = true;
                $data['imgUrl'] = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?s=150&d=identicon";
            }
        }
    
        return $data;
    }
}
?>
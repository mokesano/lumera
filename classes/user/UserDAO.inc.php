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
     * [SHIM] Backward Compatibility.
     */
    public function UserDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Renew a membership to dateEnd + 1 year.
     * @param User $user
     * @return void
     */
    public function renewMembership($user) {
        $dateEnd = (int) ($user->getSetting('dateEndMembership') ?? 0);
        $time = time();
        
        if ($dateEnd < $time) {
            $dateEnd = $time;
        }

        $newDateEnd = mktime(
            23, 59, 59, 
            (int) date('m', $dateEnd), 
            (int) date('d', $dateEnd), 
            (int) date('Y', $dateEnd) + 1
        );
        $user->updateSetting('dateEndMembership', $newDateEnd, 'date', 0);
    }

    /**
     * Retrieve an array of journal users matching a particular field value.
     * @param int $field
     * @param string|null $match
     * @param mixed $value
     * @param bool $allowDisabled
     * @param int|null $journalId
     * @param mixed $dbResultRange
     * @return DAOResultFactory
     */
    public function getJournalUsersByField($field = USER_FIELD_NONE, $match = null, $value = null, $allowDisabled = true, $journalId = null, $dbResultRange = null) {
        $params = [];
        $sql = 'SELECT * FROM users u WHERE 1=1';
        
        if ($journalId !== null) {
            $sql = 'SELECT u.* FROM users u LEFT JOIN roles r ON u.user_id=r.user_id WHERE (r.journal_id=? or r.role_id IS NULL)';
            $params[] = (int) $journalId;
        }
    
        $stringValue = $value !== null ? (string) $value : '';
        
        switch ($field) {
            case USER_FIELD_USERID:
                $sql .= ' AND u.user_id = ?';
                $params[] = (int) $value;
                break;
            case USER_FIELD_USERNAME:
            case USER_FIELD_EMAIL:
            case USER_FIELD_URL:
            case USER_FIELD_FIRSTNAME:
            case USER_FIELD_LASTNAME:
                $column = match($field) {
                    USER_FIELD_USERNAME => 'u.username',
                    USER_FIELD_EMAIL => 'u.email',
                    USER_FIELD_URL => 'u.url',
                    USER_FIELD_FIRSTNAME => 'u.first_name',
                    USER_FIELD_LASTNAME => 'u.last_name',
                };
                $sql .= " AND LOWER($column) " . ($match === 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $match === 'is' ? $stringValue : '%' . $stringValue . '%';
                break;
            case USER_FIELD_INITIAL:
                $sql .= ' AND LOWER(u.last_name) LIKE LOWER(?)';
                $params[] = $stringValue . '%';
                break;
        }
    
        $groupSql = ' GROUP BY u.user_id';
        $orderSql = ' ORDER BY u.last_name, u.first_name';
        $disabledSql = (bool) $allowDisabled ? '' : ' AND u.disabled = 0';
        
        $result = $this->retrieveRange($sql . $disabledSql . $groupSql . $orderSql, $params, $dbResultRange);
    
        return new DAOResultFactory($result, $this, '_returnUserFromRowWithData');
    }
    
    /**
     * Get the list of additional field names.
     * @return array
     */
    public function getAdditionalFieldNames() {
        return array_merge(parent::getAdditionalFieldNames(), ['orcid']);
    }
    
    /**
     * Get user ID by normalized ORCID.
     * @param string $orcid
     * @return int|null
     */
    public function getUserIdByNormalizedOrcid($orcid) {
        if (empty($orcid)) {
            return null;
        }
        
        $result = $this->retrieve(
            'SELECT user_id FROM user_settings WHERE setting_name = \'orcid\' AND setting_value LIKE ?',
            ['%' . (string) $orcid]
        );
        
        $userId = null;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $userId = (int) $row['user_id'];
        }
        if ($result) {
            $result->Close();
        }
        
        return $userId;
    }

    /**
     * Retrieve a user by their ORCID value stored in user_settings.
     * @param string $orcid
     * @param bool $allowDisabled
     * @return User|null
     */
    public function getUserByOrcid(string $orcid, bool $allowDisabled = false): ?object {
        if (empty($orcid)) {
            return null;
        }
    
        $result = $this->retrieve(
            'SELECT u.*
             FROM   users u
             INNER JOIN user_settings us
                     ON us.user_id       = u.user_id
                    AND us.setting_name  = \'orcid\'
                    AND us.setting_value = ?'
            . ($allowDisabled ? '' : ' AND u.disabled = 0'),
            [(string) $orcid]
        );
    
        if ($result->RecordCount() === 0) {
            if ($result) {
                $result->Close();
            }
            return null;
        }

        $user = $this->_returnUserFromRowWithData($result->GetRowAssoc(false));
        if ($result) {
            $result->Close();
        }

        return $user;
    }
    
    /**
     * Retrieve all users that have a registered ORCID.
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
     * Match author to user and retrieve detailed profile data.
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string|null $orcid
     * @return array
     */
    public function getAuthorUserMatch($firstName, $lastName, $email, $orcid) {
        if (is_array($orcid))     { $orcid = reset($orcid) ?: null; }
        if (is_array($firstName)) { $firstName = reset($firstName) ?: null; }
        if (is_array($lastName))  { $lastName = reset($lastName) ?: null; }
        if (is_array($email))     { $email = reset($email) ?: null; }

        static $cache = [];
        $cacheKey = md5(serialize([$firstName, $lastName, $email, $orcid]));
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        $data = [
            'found'     => false,
            'userId'    => null,
            'user'      => null,
            'hasImage'  => false,
            'imgUrl'    => '',
            'interests' => []
        ];
    
        $userId = null;
    
        // 1. Try ORCID
        if (!empty($orcid)) {
            $cleanOrcid = preg_replace('/(https?:\/\/)?(orcid\.org\/)?/', '', $orcid);
            $result = $this->retrieve(
                "SELECT user_id FROM user_settings WHERE setting_name = 'orcid' AND (setting_value = ? OR setting_value LIKE ?)",
                [$cleanOrcid, '%' . $cleanOrcid . '%']
            );
            if ($result && !$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $userId = (int) $row['user_id'];
            }
            if ($result) {
                $result->Close();
            }
        }
    
        // 2. Try Email
        if ($userId === null && !empty($email)) {
            $user = $this->getUserByEmail($email);
            if ($user !== null) {
                $userId = (int) $user->getId();
            }
        }
    
        // 3. Try Name
        if ($userId === null) {
            $result = $this->retrieve(
                "SELECT user_id FROM users WHERE first_name = ? AND last_name = ?",
                [$firstName, $lastName]
            );
            if ($result && !$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $userId = (int) $row['user_id'];
            }
            if ($result) {
                $result->Close();
            }
        }
    
        // 4. Fetch User Data if Found
        if ($userId !== null) {
            $data['found']  = true;
            $data['userId'] = $userId;
            $data['user'] = $this->getById($userId);
    
            $request = Application::get()->getRequest();
            $baseUrl = $request->getBaseUrl();
            $extensions = ['.jpg', '.jpeg', '.png', '.gif'];
            $profileImageName = 'profileImage-' . $userId;
            $baseDir = Core::getBaseDir();
    
            // Check files in public/site/
            foreach ($extensions as $ext) {
                $filePath = $baseDir . '/public/site/' . $profileImageName . $ext;
                if (file_exists($filePath)) {
                    $data['hasImage'] = true;
                    $data['imgUrl'] = $baseUrl . '/public/site/' . $profileImageName . $ext;
                    break;
                }
            }
    
            // Check alternates
            if (!$data['hasImage']) {
                $alternates = [
                    '/public/site/images/' . $profileImageName,
                    '/public/uploads/users/' . $userId . '/profile'
                ];
                foreach ($alternates as $alt) {
                    foreach ($extensions as $ext) {
                        $filePath = $baseDir . $alt . $ext;
                        if (file_exists($filePath)) {
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
    
        $cache[$cacheKey] = $data;
        return $data;
    }
    
}
?>
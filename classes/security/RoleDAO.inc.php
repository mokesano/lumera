<?php
declare(strict_types=1);

/**
 * @file classes/security/RoleDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleDAO
 * @ingroup security
 * @see Role
 *
 * @brief Operations for retrieving and modifying Role objects.
 */

import('classes.security.Role');

class RoleDAO extends DAO {
    
    /** @var UserDAO */
    protected $_userDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_userDao = DAORegistry::getDAO('UserDAO');
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function RoleDAO() {
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
     * Retrieve a role.
     * @param int $journalId
     * @param int $userId
     * @param int $roleId
     * @return Role|null
     */
    public function getRole($journalId, $userId, $roleId) {
        $result = $this->retrieve(
            'SELECT * FROM roles WHERE journal_id = ? AND user_id = ? AND role_id = ?',
            [(int) $journalId, (int) $userId, (int) $roleId]
        );
        
        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnRoleFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Internal function to return a Role object from a row.
     * @param array $row
     * @return Role
     */
    public function _returnRoleFromRow($row) {
        $role = new Role();
        $role->setJournalId((int) $row['journal_id']);
        $role->setUserId((int) $row['user_id']);
        $role->setRoleId((int) $row['role_id']);

        $tempRole = $role;
        $tempRow = $row;
        HookRegistry::dispatch('RoleDAO::_returnRoleFromRow', [&$tempRole, &$tempRow]);

        return $role;
    }

    /**
     * Insert a new role.
     * @param Role $role
     * @return bool
     */
    public function insertRole($role) {
        return (bool) $this->update(
            'INSERT INTO roles (journal_id, user_id, role_id) VALUES (?, ?, ?)',
            [(int) $role->getJournalId(), (int) $role->getUserId(), (int) $role->getRoleId()]
        );
    }

    /**
     * Delete a role.
     * @param Role $role
     * @return bool
     */
    public function deleteRole($role) {
        return (bool) $this->update(
            'DELETE FROM roles WHERE journal_id = ? AND user_id = ? AND role_id = ?',
            [(int) $role->getJournalId(), (int) $role->getUserId(), (int) $role->getRoleId()]
        );
    }

    /**
     * Retrieve a list of all roles for a specified user.
     * @param int $userId
     * @param int|null $journalId optional, include roles only in this journal
     * @return array matching Roles
     */
    public function getRolesByUserId($userId, $journalId = null) {
        $roles = [];
        $params = [(int) $userId];
        $sql = 'SELECT * FROM roles WHERE user_id = ?';
        
        if ($journalId !== null) {
            $sql .= ' AND journal_id = ?';
            $params[] = (int) $journalId;
        }
        $sql .= ' ORDER BY journal_id';
        
        $result = $this->retrieve($sql, $params);
        
        if ($result) {
            while (!$result->EOF) {
                $roles[] = $this->_returnRoleFromRow($result->GetRowAssoc(false));
                $result->MoveNext();
            }
            $result->Close();
        }
        return $roles;
    }

    /**
     * Return an array of objects corresponding to the roles a given user has, grouped by context id.
     * @param int $userId
     * @return array
     */
    public function getByUserIdGroupedByContext($userId) {
        $roles = $this->getRolesByUserId($userId);
        $groupedRoles = [];
        foreach ($roles as $role) {
            $groupedRoles[(int) $role->getJournalId()][(int) $role->getRoleId()] = $role;
        }
        return $groupedRoles;
    }

    /**
     * Retrieve a list of users in a specified role.
     * @param int|null $roleId
     * @param int|null $journalId
     * @param string|null $searchType
     * @param string|null $search
     * @param string|null $searchMatch
     * @param DBResultRange|null $dbResultRange
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory|null matching Users
     */
    public function getUsersByRoleId($roleId = null, $journalId = null, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        // For security / resource usage reasons, a role or journal ID must be specified.
        if ($journalId === null && $roleId === null) {
            return null;
        }

        $paramArray = ['interest'];
        $sqlConditions = ' WHERE u.user_id = r.user_id ';
        
        if ($roleId !== null) {
            $paramArray[] = (int) $roleId;
            $sqlConditions .= ' AND r.role_id = ? ';
        }
        if ($journalId !== null) {
            $paramArray[] = (int) $journalId;
            $sqlConditions .= ' AND r.journal_id = ? ';
        }

        $searchSql = '';
        $searchTypeMap = [
            USER_FIELD_FIRSTNAME => 'u.first_name',
            USER_FIELD_LASTNAME => 'u.last_name',
            USER_FIELD_USERNAME => 'u.username',
            USER_FIELD_EMAIL => 'u.email',
            USER_FIELD_INTERESTS => 'cves.setting_value'
        ];

        if ($search !== null && $search !== '' && $searchType !== null && isset($searchTypeMap[$searchType])) {
            $fieldName = $searchTypeMap[$searchType];
            if ($searchMatch === 'is') {
                $searchSql = " AND LOWER($fieldName) = LOWER(?)";
                $paramArray[] = (string) $search;
            } elseif ($searchMatch === 'startsWith') {
                $searchSql = " AND LOWER($fieldName) LIKE LOWER(?)";
                $paramArray[] = (string) $search . '%';
            } else { // contains
                $searchSql = " AND LOWER($fieldName) LIKE LOWER(?)";
                $paramArray[] = '%' . (string) $search . '%';
            }
        } elseif ($search !== null && $search !== '') {
            if ($searchType === USER_FIELD_USERID) {
                $searchSql = ' AND u.user_id = ?';
                $paramArray[] = (int) $search;
            } elseif ($searchType === USER_FIELD_INITIAL) {
                $searchSql = ' AND LOWER(u.last_name) LIKE LOWER(?)';
                $paramArray[] = (string) $search . '%';
            }
        }

        if ($sortBy !== null && $sortBy !== '') {
            $searchSql .= ' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection);
        }

        $result = $this->retrieveRange(
            'SELECT DISTINCT u.*
            FROM users u
                LEFT JOIN controlled_vocabs cv ON (cv.symbolic = ?)
                LEFT JOIN user_interests ui ON (ui.user_id = u.user_id)
                LEFT JOIN controlled_vocab_entries cve ON (cve.controlled_vocab_id = cv.controlled_vocab_id AND cve.controlled_vocab_entry_id = ui.controlled_vocab_entry_id)
                LEFT JOIN controlled_vocab_entry_settings cves ON (cves.controlled_vocab_entry_id = cve.controlled_vocab_entry_id),
                roles AS r ' . $sqlConditions . $searchSql,
            $paramArray,
            $dbResultRange
        );

        return new DAOResultFactory($result, $this->_userDao, '_returnUserFromRowWithData');
    }

    /**
     * Retrieve a list of all users with some role in the specified journal.
     * @param int $journalId
     * @param string|null $searchType
     * @param string|null $search
     * @param string|null $searchMatch
     * @param DBResultRange|null $dbResultRange
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory
     */
    public function getUsersByJournalId($journalId, $searchType = null, $search = null, $searchMatch = null, $dbResultRange = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        $paramArray = ['interest', (int) $journalId];
        $searchSql = '';

        $searchTypeMap = [
            USER_FIELD_FIRSTNAME => 'u.first_name',
            USER_FIELD_LASTNAME => 'u.last_name',
            USER_FIELD_USERNAME => 'u.username',
            USER_FIELD_EMAIL => 'u.email',
            USER_FIELD_INTERESTS => 'cves.setting_value'
        ];

        if ($search !== null && $search !== '' && $searchType !== null && isset($searchTypeMap[$searchType])) {
            $fieldName = $searchTypeMap[$searchType];
            if ($searchMatch === 'is') {
                $searchSql = " AND LOWER($fieldName) = LOWER(?)";
                $paramArray[] = (string) $search;
            } elseif ($searchMatch === 'startsWith') {
                $searchSql = " AND LOWER($fieldName) LIKE LOWER(?)";
                $paramArray[] = (string) $search . '%';
            } else { // contains
                $searchSql = " AND LOWER($fieldName) LIKE LOWER(?)";
                $paramArray[] = '%' . (string) $search . '%';
            }
        } elseif ($search !== null && $search !== '') {
            if ($searchType === USER_FIELD_USERID) {
                $searchSql = ' AND u.user_id = ?';
                $paramArray[] = (int) $search;
            } elseif ($searchType === USER_FIELD_INITIAL) {
                $searchSql = ' AND LOWER(u.last_name) LIKE LOWER(?)';
                $paramArray[] = (string) $search . '%';
            }
        }

        if ($sortBy !== null && $sortBy !== '') {
            $searchSql .= ' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection);
        }

        $result = $this->retrieveRange(
            'SELECT DISTINCT u.*
            FROM users u
                LEFT JOIN controlled_vocabs cv ON (cv.symbolic = ?)
                LEFT JOIN user_interests ui ON (ui.user_id = u.user_id)
                LEFT JOIN controlled_vocab_entries cve ON (cve.controlled_vocab_id = cv.controlled_vocab_id AND ui.controlled_vocab_entry_id = cve.controlled_vocab_entry_id)
                LEFT JOIN controlled_vocab_entry_settings cves ON (cves.controlled_vocab_entry_id = cve.controlled_vocab_entry_id),
                roles AS r WHERE u.user_id = r.user_id AND r.journal_id = ? ' . $searchSql,
            $paramArray,
            $dbResultRange
        );

        return new DAOResultFactory($result, $this->_userDao, '_returnUserFromRowWithData');
    }

    /**
     * Retrieve the number of users associated with the specified journal.
     * @param int $journalId
     * @param int|null $roleId
     * @return int
     */
    public function getJournalUsersCount($journalId, $roleId = null) {
        $params = [(int) $journalId];
        $sql = 'SELECT COUNT(DISTINCT user_id) AS count FROM roles WHERE journal_id = ?';
        
        if ($roleId !== null) {
            $sql .= ' AND role_id = ?';
            $params[] = (int) $roleId;
        }
        
        $result = $this->retrieve($sql, $params);
        
        $returner = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) ($row['count'] ?? 0);
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve the number of users with a given role associated with the specified journal.
     * @param int $journalId
     * @param int $roleId
     * @return int
     */
    public function getJournalUsersRoleCount($journalId, $roleId) {
        $result = $this->retrieve(
            'SELECT COUNT(DISTINCT user_id) AS count FROM roles WHERE journal_id = ? AND role_id = ?',
            [(int) $journalId, (int) $roleId]
        );
        
        $returner = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) ($row['count'] ?? 0);
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Select all roles for a specified journal.
     * @param int|null $journalId
     * @param int|null $roleId
     * @return DAOResultFactory
     */
    public function getRolesByJournalId($journalId = null, $roleId = null) {
        $params = [];
        $conditions = [];
        
        if ($journalId !== null) {
            $params[] = (int) $journalId;
            $conditions[] = 'journal_id = ?';
        }
        if ($roleId !== null) {
            $params[] = (int) $roleId;
            $conditions[] = 'role_id = ?';
        }

        $sql = 'SELECT * FROM roles';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $result = $this->retrieve($sql, $params);
        return new DAOResultFactory($result, $this, '_returnRoleFromRow');
    }

    /**
     * Delete all roles for a specified journal.
     * @param int $journalId
     * @return bool
     */
    public function deleteRoleByJournalId($journalId) {
        return (bool) $this->update('DELETE FROM roles WHERE journal_id = ?', [(int) $journalId]);
    }

    /**
     * Delete roles by user ID, optionally filtered by journal and role.
     * @param int $userId
     * @param int|null $journalId
     * @param int|null $roleId
     * @return bool
     */
    public function deleteRoleByUserId($userId, $journalId = null, $roleId = null) {
        $sql = 'DELETE FROM roles WHERE user_id = ?';
        $params = [(int) $userId];
        
        if ($journalId !== null) {
            $sql .= ' AND journal_id = ?';
            $params[] = (int) $journalId;
        }
        if ($roleId !== null) {
            $sql .= ' AND role_id = ?';
            $params[] = (int) $roleId;
        }
        
        return (bool) $this->update($sql, $params);
    }

    /**
     * [DEPRECATED] Validation check to see if a user belongs to any group that has a given role.
     * @param int $journalId
     * @param int $userId
     * @param int $roleId
     * @return bool
     * @deprecated
     */
    public function roleExists($journalId, $userId, $roleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->userHasRole($journalId, $userId, $roleId);
    }

    /**
     * Validation check to see if a user belongs to any group that has a given role.
     * @param int $journalId
     * @param int $userId
     * @param int $roleId
     * @return bool
     */
    public function userHasRole($journalId, $userId, $roleId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM roles WHERE journal_id = ? AND user_id = ? AND role_id = ?', 
            [(int) $journalId, (int) $userId, (int) $roleId]
        );
        
        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Get the i18n key name associated with the specified role.
     * @param int $roleId
     * @param bool $plural
     * @return string
     */
    public static function getRoleName($roleId, $plural = false) {
        $suffix = $plural ? 's' : '';
        switch ((int) $roleId) {
            case ROLE_ID_SITE_ADMIN: return 'user.role.siteAdmin' . $suffix;
            case ROLE_ID_JOURNAL_MANAGER: return 'user.role.manager' . $suffix;
            case ROLE_ID_EDITOR: return 'user.role.editor' . $suffix;
            case ROLE_ID_SECTION_EDITOR: return 'user.role.sectionEditor' . $suffix;
            case ROLE_ID_LAYOUT_EDITOR: return 'user.role.layoutEditor' . $suffix;
            case ROLE_ID_REVIEWER: return 'user.role.reviewer' . $suffix;
            case ROLE_ID_COPYEDITOR: return 'user.role.copyeditor' . $suffix;
            case ROLE_ID_PROOFREADER: return 'user.role.proofreader' . $suffix;
            case ROLE_ID_AUTHOR: return 'user.role.author' . $suffix;
            case ROLE_ID_READER: return 'user.role.reader' . $suffix;
            case ROLE_ID_SUBSCRIPTION_MANAGER: return 'user.role.subscriptionManager' . $suffix;
            default: return '';
        }
    }

    /**
     * Get the URL path associated with the specified role's operations.
     * @param int $roleId
     * @return string
     */
    public static function getRolePath($roleId) {
        switch ((int) $roleId) {
            case ROLE_ID_SITE_ADMIN: return 'admin';
            case ROLE_ID_JOURNAL_MANAGER: return 'manager';
            case ROLE_ID_EDITOR: return 'editor';
            case ROLE_ID_SECTION_EDITOR: return 'sectionEditor';
            case ROLE_ID_LAYOUT_EDITOR: return 'layoutEditor';
            case ROLE_ID_REVIEWER: return 'reviewer';
            case ROLE_ID_COPYEDITOR: return 'copyeditor';
            case ROLE_ID_PROOFREADER: return 'proofreader';
            case ROLE_ID_AUTHOR: return 'author';
            case ROLE_ID_READER: return 'reader';
            case ROLE_ID_SUBSCRIPTION_MANAGER: return 'subscriptionManager';
            default: return '';
        }
    }

    /**
     * Get a role's ID based on its path.
     * @param string $rolePath
     * @return int|null
     */
    public static function getRoleIdFromPath($rolePath) {
        switch ((string) $rolePath) {
            case 'admin': return ROLE_ID_SITE_ADMIN;
            case 'manager': return ROLE_ID_JOURNAL_MANAGER;
            case 'editor': return ROLE_ID_EDITOR;
            case 'sectionEditor': return ROLE_ID_SECTION_EDITOR;
            case 'layoutEditor': return ROLE_ID_LAYOUT_EDITOR;
            case 'reviewer': return ROLE_ID_REVIEWER;
            case 'copyeditor': return ROLE_ID_COPYEDITOR;
            case 'proofreader': return ROLE_ID_PROOFREADER;
            case 'author': return ROLE_ID_AUTHOR;
            case 'reader': return ROLE_ID_READER;
            case 'subscriptionManager': return ROLE_ID_SUBSCRIPTION_MANAGER;
            default: return null;
        }
    }

    /**
     * Map a column heading value to a database value for sorting.
     * @param string $heading
     * @return string|null
     */
    public static function getSortMapping($heading) {
        switch ((string) $heading) {
            case 'username': return 'u.username';
            case 'name': return 'u.last_name';
            case 'email': return 'u.email';
            case 'id': return 'u.user_id';
            default: return null;
        }
    }
    
}
?>
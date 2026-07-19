<?php
declare(strict_types=1);

/**
 * @file classes/security/UserGroupAssignmentDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupAssignmentDAO
 * @ingroup security
 * @see UserGroupAssignment
 *
 * @brief Operations for retrieving and modifying user group assignments
 */

import('lib.pkp.classes.security.UserGroupAssignment');

class UserGroupAssignmentDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserGroupAssignmentDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::UserGroupAssignmentDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        $this->__construct();
    }

    /**
     * Create a new UserGroupAssignment object (allows extensibility)
     * @return UserGroupAssignment
     */
    public function newDataObject() {
        return new UserGroupAssignment();
    }

    /**
     * Internal function to return a UserGroupAssignment object from a row.
     * @param array $row
     * @return UserGroupAssignment
     */
    public function _returnFromRow($row) {
        $userGroupAssignment = $this->newDataObject();
        $userGroupAssignment->setUserGroupId((int) $row['user_group_id']);
        $userGroupAssignment->setUserId((int) $row['user_id']);

        return $userGroupAssignment;
    }

    /**
     * Delete all user group assignments for a given userId
     * @param int $userId
     * @param int|null $userGroupId optional
     * @return bool
     */
    public function deleteByUserId($userId, $userGroupId = null) {
        $params = [(int) $userId];
        if ($userGroupId) {
            $params[] = (int) $userGroupId;
        }

        return $this->update(
            'DELETE FROM user_user_groups WHERE user_id = ?' . ($userGroupId ? ' AND user_group_id = ?' : ''),
            $params
        );
    }

    /**
     * Remove all user group assignments for a given group
     * @param int $userGroupId
     * @return bool
     */
    public function deleteAssignmentsByUserGroupId($userGroupId) {
        return $this->update(
            'DELETE FROM user_user_groups WHERE user_group_id = ?',
            [(int) $userGroupId] 
        );
    }

    /**
     * Remove all user group assignments in a given context
     * @param int $contextId
     * @param int|null $userId
     * @return bool
     */
    public function deleteAssignmentsByContextId($contextId, $userId = null) {
        $sql = 'DELETE FROM user_user_groups WHERE user_group_id IN (
                    SELECT user_group_id FROM user_groups WHERE context_id = ?
                )';
        
        $params = [(int) $contextId];

        if ($userId) {
            $sql .= ' AND user_id = ?';
            $params[] = (int) $userId;
        }

        return $this->update($sql, $params);
    }

    /**
     * Retrieve user group assignments for a user
     * @param int $userId
     * @param int|null $contextId
     * @param int|null $roleId
     * @return DAOResultFactory
     */
    public function getByUserId($userId, $contextId = null, $roleId = null) {
        $params = [(int) $userId];
        if ($contextId) $params[] = (int) $contextId;
        if ($roleId) $params[] = (int) $roleId;

        $result = $this->retrieve(
            'SELECT uug.user_group_id, uug.user_id
             FROM user_groups ug 
             JOIN user_user_groups uug ON ug.user_group_id = uug.user_group_id
             WHERE uug.user_id = ?' . 
             ($contextId ? ' AND ug.context_id = ?' : '') . 
             ($roleId ? ' AND ug.role_id = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnFromRow');
    }

    /**
     * Insert an assignment
     * @param UserGroupAssignment $userGroupAssignment
     * @return bool
     */
    public function insertAssignment($userGroupAssignment) {
        return $this->update(
            'INSERT INTO user_user_groups (user_id, user_group_id) VALUES (?, ?)',
            [(int) $userGroupAssignment->getUserId(), (int) $userGroupAssignment->getUserGroupId()]
        );
    }

    /**
     * Remove an assignment
     * @param UserGroupAssignment $userGroupAssignment
     * @return bool
     */
    public function deleteAssignment($userGroupAssignment) {
        return $this->update(
            'DELETE FROM user_user_groups WHERE user_id = ? AND user_group_id = ?',
            [(int) $userGroupAssignment->getUserId(), (int) $userGroupAssignment->getUserGroupId()]
        );
    }

}
?>
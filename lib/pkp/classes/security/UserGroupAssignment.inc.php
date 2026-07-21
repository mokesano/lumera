<?php
declare(strict_types=1);

/**
 * @file classes/security/UserGroupAssignment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupAssignment
 * @ingroup security
 * @see RoleDAO
 *
 * @brief Describes user roles within the system and the associated permissions.
 */

import('lib.pkp.classes.security.UserGroup');

class UserGroupAssignment extends DataObject {

    /** @var UserGroup|null the UserGroup object associated with this assignment **/
    public $userGroup = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserGroupAssignment() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::UserGroupAssignment(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        $this->__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get user group ID associated with a user group assignment.
     * @return int|null
     */
    public function getUserGroupId() {
        $data = $this->getData('userGroupId');
        return $data !== null ? (int) $data : null;
    }

    /**
     * Set user group ID associated with a user group assignment.
     * @param mixed $userGroupId
     * @return boolean
     */
    public function setUserGroupId($userGroupId) {
        $this->setData('userGroupId', $userGroupId !== null ? (int) $userGroupId : null);
        
        /** @var UserGroupDAO $userGroupDao */
        $userGroupDao = DAORegistry::getDAO('UserGroupDAO');
        $safeId = $userGroupId !== null ? (int) $userGroupId : 0;
        $userGroup = $userGroupDao->getById($safeId);
        $this->userGroup = $userGroup;
        
        return $this->userGroup !== null;
    }

    /**
     * Get user ID associated with role.
     * @return int|null
     */
    public function getUserId() {
        $data = $this->getData('userId');
        return $data !== null ? (int) $data : null;
    }

    /**
     * Set user ID associated with role.
     * @param mixed $userId
     */
    public function setUserId($userId) {
        $this->setData('userId', $userId !== null ? (int) $userId : null);
    }

}
?>
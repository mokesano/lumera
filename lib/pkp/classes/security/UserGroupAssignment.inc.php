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
    /** @var UserGroup the UserGroup object associated with this assignment **/
    public $userGroup;

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
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get user ID associated with a user group assignment.
     * @return int
     */
    public function getUserGroupId() {
        return $this->getData('userGroupId');
    }

    /**
     * Set user ID associated with a user group assignment.
     * also sets the $userGroup property
     * @param $userGroupId int
     * @return boolean
     */
    public function setUserGroupId($userGroupId) {
        $this->setData('userGroupId', $userGroupId);
        // Removed & reference
        $userGroupDao = DAORegistry::getDAO('UserGroupDAO');
        $userGroup = $userGroupDao->getById($userGroupId);
        $this->userGroup = $userGroup;
        return (boolean) $this->userGroup;
    }

    /**
     * Get user ID associated with role.
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * Set user ID associated with role.
     * @param $userId int
     */
    public function setUserId($userId) {
        return $this->setData('userId', $userId);
    }
}

?>
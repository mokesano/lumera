<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/RoleBasedHandlerOperationPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RoleBasedHandlerOperationPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to handler operations via role based access
 * control.
 */

import('lib.pkp.classes.security.authorization.HandlerOperationPolicy');

class RoleBasedHandlerOperationPolicy extends HandlerOperationPolicy {
    /** @var array the target roles */
    public $_roles = array();

    /** @var boolean */
    public $_allRoles;

    /** @var boolean */
    public $_bypassOperationCheck;

    /**
     * Constructor
     */
    public function __construct($request, $roles, $operations,
            $message = 'user.authorization.roleBasedAccessDenied',
            $allRoles = false, $bypassOperationCheck = false) {
        // Removed & from request
        parent::__construct($request, $operations, $message);

        // Make sure a single role doesn't have to be
        // passed in as an array.
        assert(is_integer($roles) || is_array($roles));
        if (!is_array($roles)) {
            $roles = array($roles);
        }
        $this->_roles = $roles;
        $this->_allRoles = $allRoles;
        $this->_bypassOperationCheck = $bypassOperationCheck;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RoleBasedHandlerOperationPolicy($request, $roles, $operations,
            $message = 'user.authorization.roleBasedAccessDenied',
            $allRoles = false, $bypassOperationCheck = false) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::RoleBasedHandlerOperationPolicy(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request, $roles, $operations, $message, $allRoles, $bypassOperationCheck);
    }

    //
    // Implement template methods from AuthorizationPolicy
    //
    /**
     * @see AuthorizationPolicy::effect()
     */
    public function effect() {
        // Check whether the user has one of the allowed roles
        // assigned. If that's the case we'll permit access.
        // Get user roles grouped by context.
        $userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
        if (empty($userRoles)) return AUTHORIZATION_DENY;

        if (!$this->_checkUserRoleAssignment($userRoles)) return AUTHORIZATION_DENY;

        // FIXME: Remove the "bypass operation check" code once we've removed the
        // HandlerValidatorRole compatibility class and make the operation
        // check unconditional, see #5868.
        if ($this->_bypassOperationCheck) {
            assert($this->getOperations() === array());
        } else {
            if (!$this->_checkOperationWhitelist()) return AUTHORIZATION_DENY;
        }

        return AUTHORIZATION_PERMIT;
    }


    //
    // Private helper methods
    //
    /**
     * Check whether the given user has been assigned
     * to any of the allowed roles. If so then grant
     * access.
     * @param $userRoles array
     * @return boolean
     */
    protected function _checkUserRoleAssignment($userRoles) {
        // Find matching roles.
        $foundMatchingRole = false;
        foreach($this->_roles as $roleId) {
            $foundMatchingRole = in_array($roleId, $userRoles);

            if ($this->_allRoles) {
                if (!$foundMatchingRole) {
                    // When the "all roles" flag is switched on then
                    // one missing role is enough to fail.
                    return false;
                }
            } else {
                if ($foundMatchingRole) {
                    // When the "all roles" flag is not set then
                    // one matching role is enough to succeed.
                    return true;
                }
            }
        }

        if ($this->_allRoles) {
            // All roles matched, otherwise we'd have failed before.
            return true;
        } else {
            // None of the roles matched, otherwise we'd have succeeded already.
            return false;
        }
    }
}

?>
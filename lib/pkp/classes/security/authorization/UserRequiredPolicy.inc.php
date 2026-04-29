<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/UserRequiredPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserRequiredPolicy
 * @ingroup security_authorization
 *
 * @brief Policy to deny access if a context cannot be found in the request.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class UserRequiredPolicy extends AuthorizationPolicy {
    /** @var PKPRequest */
    public $_request;

    /**
     * Constructor
     */
    public function __construct($request, $message = 'user.authorization.userRequired') {
        parent::__construct($message);
        // Removed & from reference
        $this->_request = $request;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserRequiredPolicy($request, $message = 'user.authorization.userRequired') {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::UserRequiredPolicy(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request, $message);
    }

    //
    // Implement template methods from AuthorizationPolicy
    //
    /**
     * @see AuthorizationPolicy::effect()
     */
    public function effect() {
        if ($this->_request->getUser()) {
            return AUTHORIZATION_PERMIT;
        } else {
            return AUTHORIZATION_DENY;
        }
    }
}

?>
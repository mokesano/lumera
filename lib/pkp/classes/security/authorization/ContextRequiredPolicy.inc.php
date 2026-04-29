<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/ContextRequiredPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContextRequiredPolicy
 * @ingroup security_authorization
 *
 * @brief Policy to deny access if a context cannot be found in the request.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class ContextRequiredPolicy extends AuthorizationPolicy {
    /** @var PKPRequest */
    public $_request;

    /**
     * Constructor
     *
     * @param $request PKPRequest
     * @param $message string
     */
    public function __construct($request, $message = 'user.authorization.contextRequired') {
        parent::__construct($message);
        // Removed & from reference assignment
        $this->_request = $request;
    }

    /**
     * [SHIM] Backward Compatibility
     * @param $request PKPRequest
     * @param $message string
     */
    public function ContextRequiredPolicy($request, $message = 'user.authorization.contextRequired') {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ContextRequiredPolicy(). Please refactor to use parent::__construct().",
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
        // Removed & from getRouter() return
        $router = $this->_request->getRouter();
        if (is_object($router->getContext($this->_request))) {
            return AUTHORIZATION_PERMIT;
        } else {
            return AUTHORIZATION_DENY;
        }
    }
}

?>
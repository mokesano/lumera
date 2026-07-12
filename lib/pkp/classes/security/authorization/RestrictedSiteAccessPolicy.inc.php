<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/RestrictedSiteAccessPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RestrictedSiteAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Policy enforcing restricted site access when the context
 * contains such a setting.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class RestrictedSiteAccessPolicy extends AuthorizationPolicy {

    /** @var PKPRouter */
    public $_router;

    /** @var PKPRequest */
    public $_request;

    /**
     * Constructor.
     * @param mixed $request
     */
    public function __construct($request) {
        parent::__construct('user.authorization.restrictedSiteAccess');
        $this->_request = $request;
        $this->_router = $request->getRouter();
    }

    /**
     * [SHIM] Backward Compatibility
     * @param mixed $request
     */
    public function RestrictedSiteAccessPolicy($request) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::RestrictedSiteAccessPolicy(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request);
    }

    //
    // Implement template methods from AuthorizationPolicy
    //
    /**
     * Applies restricts
     * @see AuthorizationPolicy::applies()
     */
    public function applies() {
        $context = $this->_router->getContext($this->_request);
        return ($context && $context->getSetting('restrictSiteAccess'));
    }

    /**
     * Effect of restricts
     * @see AuthorizationPolicy::effect()
     */
    public function effect() {
        // Modernized type check
        if ($this->_router instanceof PKPPageRouter) {
            $page = $this->_router->getRequestedPage($this->_request);
        } else {
            $page = null;
        }

        if (Validation::isLoggedIn() || in_array($page, self::_getLoginExemptions())) {
            return AUTHORIZATION_PERMIT;
        } else {
            return AUTHORIZATION_DENY;
        }
    }

    //
    // Private helper method
    //
    /**
     * Return the pages that can be accessed even while in restricted site mode.
     * @return array
     */
    public static function _getLoginExemptions() {
        return array('user', 'login', 'help', 'header', 'payment');
    }
}
?>
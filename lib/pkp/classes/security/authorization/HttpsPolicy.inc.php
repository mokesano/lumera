<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/HttpsPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HttpsPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to handler operations based on protocol.
 */

import('lib.pkp.classes.security.authorization.AuthorizationPolicy');

class HttpsPolicy extends AuthorizationPolicy {
    /** @var PKPRequest */
    public $_request;

    /**
     * Constructor
     *
     * @param $request PKPRequest
     */
    public function __construct($request) {
        parent::__construct();
        // Removed & from reference assignment
        $this->_request = $request;

        // Add advice
        // redirectSSL is a method of PKPRequest
        $callOnDeny = array($request, 'redirectSSL', array());
        $this->setAdvice(AUTHORIZATION_ADVICE_CALL_ON_DENY, $callOnDeny);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param $request PKPRequest
     */
    public function HttpsPolicy($request) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::HttpsPolicy(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request);
    }

    //
    // Implement template methods from AuthorizationPolicy
    //
    /**
     * @see AuthorizationPolicy::applies()
     */
    public function applies() {
        return (boolean)Config::getVar('security', 'force_ssl');
    }

    /**
     * @see AuthorizationPolicy::effect()
     */
    public function effect() {
        // Check the request protocol
        if ($this->_request->getProtocol() == 'https') {
            return AUTHORIZATION_PERMIT;
        } else {
            return AUTHORIZATION_DENY;
        }
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/security/authorization/PKPProcessAccessPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPProcessAccessPolicy
 * @ingroup security_authorization
 *
 * @brief Class to control access to handler operations based on a one time key
 * that authorizes a process to execute.
 */

import('lib.pkp.classes.security.authorization.PKPPublicAccessPolicy');

class PKPProcessAccessPolicy extends PKPPublicAccessPolicy {
    /** @var string the process authorization token */
    public $authToken;

    /**
     * Constructor
     * @param $request PKPRequest
     * @param $args array request arguments (containing authToken)
     * @param $operations array|string either a single operation or a list of operations that
     * this policy is targeting.
     * @param $message string a message to be displayed if the authorization fails
     */
    public function __construct($request, $args, $operations, $message = 'user.authorization.processAuthenticationTokenRequired') {
        if (isset($args['authToken'])) {
            $this->authToken = $args['authToken'];
        }

        parent::__construct($request, $operations, $message);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param $request PKPRequest
     * @param $args array
     * @param $operations array|string
     * @param $message string
     */
    public function PKPProcessAccessPolicy($request, $args, $operations, $message = 'user.authorization.processAuthenticationTokenRequired') {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPProcessAccessPolicy(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request, $args, $operations, $message);
    }

    //
    // Implement template methods from AuthorizationPolicy
    //
    /**
     * @see AuthorizationPolicy::effect()
     */
    public function effect() {
        // Check whether the requested operation is a remote public operation.
        if (parent::effect() == AUTHORIZATION_DENY) {
            return AUTHORIZATION_DENY;
        }

        // Check whether an authentication token is present in the request.
        if (empty($this->authToken) || strlen($this->authToken) != 23) {
            return AUTHORIZATION_DENY;
        }

        // Try to authorize the process with the token.
        // Removed & from reference
        $processDao = DAORegistry::getDAO('ProcessDAO');
        if ($processDao->authorizeProcess($this->authToken)) {
            return AUTHORIZATION_PERMIT;
        }

        // In all other cases deny access.
        return AUTHORIZATION_DENY;
    }
}

?>
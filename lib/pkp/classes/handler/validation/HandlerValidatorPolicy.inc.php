<?php
declare(strict_types=1);

/**
 * @file classes/handler/HandlerValidatorPolicy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HandlerValidator
 * @ingroup security
 *
 * @brief Class to represent a policy based validation check.
 *
 * NB: This class is deprecated and only exists for backward compatibility.
 * Please use AuthorizationPolicy classes for authorization from now on.
 */

import('lib.pkp.classes.handler.validation.HandlerValidator');

class HandlerValidatorPolicy extends HandlerValidator {
    /** @var AuthorizationPolicy */
    public $_policy;

    /**
     * Constructor.
     * @param $policy AuthorizationPolicy
     * @param $handler Handler
     * @param $redirectToLogin boolean
     * @param $message string
     * @param $additionalArgs array
     */
    public function __construct($policy, $handler, $redirectToLogin = false, $message = null, $additionalArgs = array()) {
        // Hapus '&' pada assignment dan parameter parent
        $this->_policy = $policy;
        parent::__construct($handler, $redirectToLogin, $message, $additionalArgs);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HandlerValidatorPolicy($policy, $handler, $redirectToLogin = false, $message = null, $additionalArgs = array()) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::HandlerValidatorPolicy(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($policy, $handler, $redirectToLogin, $message, $additionalArgs);
    }

    /**
     * @see HandlerValidator::isValid()
     * @return boolean
     */
    public function isValid() {
        // Delegate to the AuthorizationPolicy
        if (!$this->_policy->applies()) return false;
        
        // Pass the authorized context to the police.
        // Asumsikan handler->getAuthorizedContext() mengembalikan array/objek,
        // di PHP 8 passing objek tidak perlu '&'.
        $this->_policy->setAuthorizedContext($this->handler->getAuthorizedContext());
        
        if ($this->_policy->effect() == AUTHORIZATION_DENY) {
            return false;
        } else {
            return true;
        }
    }
}

?>
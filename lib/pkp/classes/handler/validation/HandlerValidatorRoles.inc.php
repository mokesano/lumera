<?php
declare(strict_types=1);

/**
 * @file classes/handler/HandlerValidatorRoles.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HandlerValidator
 * @ingroup security
 *
 * @brief Class to represent a page validation check.
 *
 * NB: Deprecated - please use RoleBasedHandlerOperationPolicy instead.
 */

import('lib.pkp.classes.handler.validation.HandlerValidatorPolicy');
import('lib.pkp.classes.security.authorization.RoleBasedHandlerOperationPolicy');

class HandlerValidatorRoles extends HandlerValidatorPolicy {
    /**
     * Constructor.
     * @param $handler Handler the associated form
     * @param $redirectLogin bool
     * @param $message string
     * @param $additionalArgs array
     * @param $roles array of role id's
     * @param $all bool flag for whether all roles must exist or just 1
     */
    public function __construct($handler, $redirectLogin = true, $message = null, $additionalArgs = array(), $roles = array(), $all = false) {
        // Hapus '&' pada assignment objek
        $application = PKPApplication::getApplication();
        $request = $application->getRequest();
        
        $policy = new RoleBasedHandlerOperationPolicy($request, $roles, array(), $message, $all, true);
        parent::__construct($policy, $handler, $redirectLogin, $message, $additionalArgs);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HandlerValidatorRoles($handler, $redirectLogin = true, $message = null, $additionalArgs = array(), $roles = array(), $all = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::HandlerValidatorRoles(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($handler, $redirectLogin, $message, $additionalArgs, $roles, $all);
    }
}

?>
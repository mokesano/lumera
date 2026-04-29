<?php
declare(strict_types=1);

/**
 * @file classes/handler/HandlerValidatorCustom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HandlerValidator
 * @ingroup security
 *
 * @brief Class to represent a page validation check.
 */

import('lib.pkp.classes.handler.validation.HandlerValidator');

class HandlerValidatorCustom extends HandlerValidator {
    /** @var callable The user supplied function to call */
    public $userFunction;

    /** @var array additionalArguments to apss to the user function */
    public $userFunctionArgs;

    /** @var bool If true, field is considered valid if user function returns false instead of true */
    public $complementReturn;

    /**
     * Constructor.
     * @param $handler Handler the associated form
     * @param $redirectLogin boolean
     * @param $message string the error message for validation failures (i18n key)
     * @param $urlArgs array
     * @param $userFunction callable
     * @param $userFunctionArgs array
     * @param $complementReturn boolean
     */
    public function __construct($handler, $redirectLogin = false, $message = null, $urlArgs = array(), $userFunction = null, $userFunctionArgs = array(), $complementReturn = false) {
        parent::__construct($handler, $redirectLogin, $message, $urlArgs);
        $this->userFunction = $userFunction;
        $this->userFunctionArgs = $userFunctionArgs;
        $this->complementReturn = $complementReturn;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HandlerValidatorCustom($handler, $redirectLogin = false, $message = null, $urlArgs = array(), $userFunction = null, $userFunctionArgs = array(), $complementReturn = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::HandlerValidatorCustom(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($handler, $redirectLogin, $message, $urlArgs, $userFunction, $userFunctionArgs, $complementReturn);
    }

    /**
     * Check if field value is valid.
     * Value is valid if it is empty and optional or validated by user-supplied function.
     * @return boolean
     */
    public function isValid() {
        if (is_callable($this->userFunction)) {
            $ret = call_user_func_array($this->userFunction, $this->userFunctionArgs);
            return $this->complementReturn ? !$ret : $ret;
        }
        // Fail safe if function is not callable
        return false;
    }
}

?>
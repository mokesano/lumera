<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorCustom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorCustom
 * @ingroup form_validation
 *
 * @brief Form validation check with a custom user function 
 * performing the validation check.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorCustom extends FormValidator {

    /** @var callable Custom validation function */
    protected $_userFunction;

    /** @var array Additional arguments to pass to $userFunction */
    protected array $_additionalArguments;

    /** @var boolean If true, field is considered valid if user function returns false instead of true */
    protected bool $_complementReturn;

    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures
     * @param callable $userFunction function the user function
     * @param array $additionalArguments optional
     * @param boolean $complementReturn optional
     */
    public function __construct($form, $field, $type, $message, $userFunction, $additionalArguments = [], $complementReturn = false) {
        parent::__construct($form, $field, $type, $message);
        $this->_userFunction = $userFunction;
        $this->_additionalArguments = $additionalArguments;
        $this->_complementReturn = $complementReturn;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorCustom($form, $field, $type, $message, $userFunction, $additionalArguments = [], $complementReturn = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $userFunction, $additionalArguments, $complementReturn);
    }

    //
    // Public methods
    //
    
    /**
     * Value is valid if it is empty and optional or validated by user-supplied.
     * @see FormValidator::isValid()
     * @return boolean
     */
    public function isValid() {
        if ($this->isEmptyAndOptional()) {
            return true;
        } else {
            // [WIZDAM] PHP 8 Safety: Merge arguments and call.
            // Note: $this->_userFunction MUST be a valid callable or PHP 8 will throw TypeError.
            $ret = call_user_func_array(
                $this->_userFunction, 
                array_merge([$this->getFieldValue()], $this->_additionalArguments)
            );
            
            // Ensure return is boolean
            $boolRet = (bool) $ret;
            
            return $this->_complementReturn ? !$boolRet : $boolRet;
        }
    }
}

?>
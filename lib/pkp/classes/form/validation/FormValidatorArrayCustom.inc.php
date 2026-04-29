<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorArrayCustom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorArrayCustom
 * @ingroup form_validation
 *
 * @brief Form validation check with a custom user function performing the validation check of an array of fields.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorArrayCustom extends FormValidator {

    /** @var array Array of fields to check */
    protected array $_fields;

    /** @var array Array of field names where an error occurred */
    protected array $_errorFields;

    /** @var boolean is the field a multilingual-capable field */
    protected bool $_isLocaleField;

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
     * @param string $message the error message for validation failures (i18n key)
     * @param callable $userFunction function the user function to use for validation
     * @param array $additionalArguments optional, a list of additional arguments to pass to $userFunction
     * @param boolean $complementReturn optional, complement the value returned by $userFunction
     * @param array $fields all subfields for each item in the array, i.e. name[][foo]. If empty it is assumed that name[] is a data field
     * @param boolean $isLocaleField
     */
    public function __construct($form, $field, $type, $message, $userFunction, $additionalArguments = [], $complementReturn = false, $fields = [], $isLocaleField = false) {
        parent::__construct($form, $field, $type, $message);
        $this->_fields = $fields;
        $this->_errorFields = [];
        $this->_isLocaleField = $isLocaleField;
        $this->_userFunction = $userFunction;
        $this->_additionalArguments = $additionalArguments;
        $this->_complementReturn = $complementReturn;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorArrayCustom($form, $field, $type, $message, $userFunction, $additionalArguments = [], $complementReturn = false, $fields = [], $isLocaleField = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $userFunction, $additionalArguments, $complementReturn, $fields, $isLocaleField);
    }

    //
    // Setters and Getters
    //
    /**
     * Get array of fields where an error occurred.
     * @return array
     */
    public function getErrorFields() {
        return $this->_errorFields;
    }

    /**
     * Is it a multilingual-capable field.
     * @return boolean
     */
    public function isLocaleField() {
        return $this->_isLocaleField;
    }


    //
    // Public methods
    //
    /**
     * @see FormValidator::isValid()
     * @return boolean
     */
    public function isValid() {
        if ($this->isEmptyAndOptional()) return true;

        $data = $this->getFieldValue();
        if (!is_array($data)) return false;

        $isValid = true;
        foreach ($data as $key => $value) {
            // Bypass check for empty sub-fields if validation type is "optional"
            // [WIZDAM] PHP 8 cleanup: Explicit empty checks instead of loose comparison
            if ($this->getType() == FORM_VALIDATOR_OPTIONAL_VALUE) {
                if (is_array($value) && empty($value)) continue;
                if (is_null($value)) continue;
                if (is_scalar($value) && trim((string)$value) === '') continue;
            }

            // Case 1: One-dimensional array (e.g., simple list of values)
            if (empty($this->_fields)) {
                $args = array_merge([$value], $this->_additionalArguments);
                
                // If locale field, inject the key (locale) into arguments
                if ($this->isLocaleField()) {
                    // Logic: func($value, $key, ...args)
                    // Original code: array_merge(array($value, $key), ...)
                    $args = array_merge([$value, $key], $this->_additionalArguments);
                }

                $ret = call_user_func_array($this->_userFunction, $args);
                $ret = $this->_complementReturn ? !$ret : $ret;

                if (!$ret) {
                    $isValid = false;
                    if ($this->isLocaleField()) {
                        $this->_errorFields[$key] = $this->getField() . "[{$key}]";
                    } else {
                        array_push($this->_errorFields, $this->getField() . "[{$key}]");
                    }
                }
            } 
            // Case 2: Two-dimensional array (e.g., list of objects/rows)
            else {
                // In the two-dimensional case we always expect a value array.
                if (!is_array($value)) {
                    $isValid = false;
                    if ($this->isLocaleField()) {
                        $this->_errorFields[$key] = $this->getField() . "[{$key}]";
                    } else {
                        array_push($this->_errorFields, $this->getField() . "[{$key}]");
                    }
                    continue;
                }

                foreach ($this->_fields as $field) {
                    // Bypass check for empty sub-sub-fields if validation type is "optional"
                    if ($this->getType() == FORM_VALIDATOR_OPTIONAL_VALUE) {
                        if (!isset($value[$field])) continue;
                        $subValue = $value[$field];
                        // [WIZDAM] Strict empty checks
                        if (is_array($subValue) && empty($subValue)) continue;
                        if (is_null($subValue)) continue;
                        if (is_scalar($subValue) && trim((string)$subValue) === '') continue;
                    } else {
                        // Make sure that we pass in 'null' to the user function
                        // if the expected field doesn't exist in the value array.
                        if (!array_key_exists($field, $value)) $value[$field] = null;
                    }

                    $args = array_merge([$value[$field]], $this->_additionalArguments);

                    if ($this->isLocaleField()) {
                        // Logic: func($value, $key, ...args)
                        $args = array_merge([$value[$field], $key], $this->_additionalArguments);
                    }

                    $ret = call_user_func_array($this->_userFunction, $args);
                    $ret = $this->_complementReturn ? !$ret : $ret;

                    if (!$ret) {
                        $isValid = false;
                        if ($this->isLocaleField()) {
                            if (!isset($this->_errorFields[$key])) $this->_errorFields[$key] = [];
                            array_push($this->_errorFields[$key], $this->getField() . "[{$key}][{$field}]");
                        } else {
                            array_push($this->_errorFields, $this->getField() . "[{$key}][{$field}]");
                        }
                    }
                }
            }
        }
        return $isValid;
    }

    /**
     * Is the field an array.
     * @return boolean
     */
    public function isArray() {
        return is_array($this->getFieldValue());
    }
}

?>
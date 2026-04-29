<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorArray.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorArray
 * @ingroup form_validation
 *
 * @brief Form validation check that checks an array of fields.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorArray extends FormValidator {

    /** @var array Array of fields to check */
    protected array $_fields;

    /** @var array Array of field names where an error occurred */
    protected array $_errorFields;

    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param array $fields all subfields for each item in the array, i.e. name[][foo]. If empty it is assumed that name[] is a data field
     */
    public function __construct($form, $field, $type, $message, $fields = []) {
        parent::__construct($form, $field, $type, $message);
        $this->_fields = $fields;
        $this->_errorFields = [];
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorArray($form, $field, $type, $message, $fields = []) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $fields);
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


    //
    // Public methods
    //
    /**
     * @see FormValidator::isValid()
     * Value is valid if it is empty and optional or all field values are set.
     * @return boolean
     */
    public function isValid() {
        if ($this->getType() == FORM_VALIDATOR_OPTIONAL_VALUE) return true;

        $data = $this->getFieldValue();
        if (!is_array($data)) return false;

        $isValid = true;
        foreach ($data as $key => $value) {
            if (empty($this->_fields)) {
                // We expect all fields to contain simple values (1-Dimensional Check).
                // [WIZDAM] PHP 8 Safety: check if scalar before string casting to avoid warnings on nested arrays
                if (is_array($value)) {
                    // Structure mismatch: we expected a value but got an array
                    $isValid = false;
                    array_push($this->_errorFields, $this->getField() . "[{$key}]");
                } elseif (is_null($value) || trim((string)$value) === '') {
                    $isValid = false;
                    array_push($this->_errorFields, $this->getField() . "[{$key}]");
                }
            } else {
                // In the two-dimensional case we always expect a value array.
                if (!is_array($value)) {
                    $isValid = false;
                    array_push($this->_errorFields, $this->getField() . "[{$key}]");
                    continue;
                }

                // Go through all sub-sub-fields and check them explicitly
                foreach ($this->_fields as $field) {
                    // [WIZDAM] PHP 8 Safety: Handle missing keys or non-scalar values safely
                    if (!isset($value[$field])) {
                        $isValid = false;
                        array_push($this->_errorFields, $this->getField() . "[{$key}][{$field}]");
                    } else {
                        $subValue = $value[$field];
                        // If the sub-field is itself an array, we cannot trim it. 
                        // Assuming simple values for leaf nodes.
                        if (is_array($subValue) || (is_scalar($subValue) && trim((string)$subValue) === '')) {
                            // If it's an array (unexpected) or empty string (invalid)
                             if (!is_scalar($subValue) && !is_null($subValue)) {
                                 // It is an array/object, strictly not what we want in a simple field check
                                 // But technically 'not empty'. However, original logic implied string checks.
                                 // We will treat non-scalars here as valid ONLY if not null, to preserve loose legacy behavior,
                                 // unless it's strictly empty string.
                             } elseif (trim((string)$subValue) === '') {
                                $isValid = false;
                                array_push($this->_errorFields, $this->getField() . "[{$key}][{$field}]");
                             }
                        } elseif (is_null($subValue)) {
                             $isValid = false;
                             array_push($this->_errorFields, $this->getField() . "[{$key}][{$field}]");
                        }
                    }
                }
            }
        }

        return $isValid;
    }
}

?>
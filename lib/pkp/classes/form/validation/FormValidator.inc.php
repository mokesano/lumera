<?php
declare(strict_types=1);

/**
 * @defgroup form_validation
 */

/**
 * @file classes/form/validation/FormValidator.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidator
 * @ingroup form_validation
 *
 * @brief Class to represent a form validation check.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

// The two allowed states for the type field
define('FORM_VALIDATOR_OPTIONAL_VALUE', 'optional');
define('FORM_VALIDATOR_REQUIRED_VALUE', 'required');

class FormValidator {

    /** @var Form The Form associated with the check */
    protected Form $_form;

    /** @var string The name of the field */
    protected string $_field;

    /** @var string The type of check ("required" or "optional") */
    protected string $_type;

    /** @var string The error message associated with a validation failure */
    protected string $_message;

    /** @var Validator|null The validator used to validate the field */
    protected $_validator;

    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param Validator|null $validator the validator used to validate this form field (optional)
     */
    public function __construct($form, $field, $type, $message, $validator = null) {
        // [WIZDAM] Removed reference assignment =&
        $this->_form = $form;
        $this->_field = $field;
        $this->_type = $type;
        $this->_message = $message;
        $this->_validator = $validator;

        // Initialize cssValidation array if not present
        if (!isset($form->cssValidation[$field])) {
            $form->cssValidation[$field] = [];
        }
        
        if ($type == FORM_VALIDATOR_REQUIRED_VALUE) {
            array_push($form->cssValidation[$field], 'required');
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidator($form, $field, $type, $message, $validator = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $validator);
    }

    //
    // Setters and Getters
    //
    /**
     * Get the field associated with the check.
     * @return string
     */
    public function getField() {
        return $this->_field;
    }

    /**
     * Get the error message associated with a failed validation check.
     * @return string
     */
    public function getMessage() {
        return __($this->_message);
    }

    /**
     * Set the form associated with this check.
     * @param Form $form
     */
    public function setForm($form) {
        $this->_form = $form;
    }

    /**
     * Get the form associated with the check
     * @return Form
     */
    public function getForm() {
        return $this->_form;
    }

    /**
     * Get the validator associated with the check
     * @return Validator|null
     */
    public function getValidator() {
        return $this->_validator;
    }

    /**
     * Get the type of the validated field ('optional' or 'required')
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    //
    // Public methods
    //
    /**
     * Check if field value is valid.
     * Default check is that field is either optional or not empty.
     * @return boolean
     */
    public function isValid() {
        if ($this->isEmptyAndOptional()) return true;

        $validator = $this->getValidator();
        if (is_null($validator)) {
            $fieldValue = $this->getFieldValue();
            return is_scalar($fieldValue) ? (string) $fieldValue !== '' : $fieldValue !== [];
        } else {
            $fieldValue = $this->getFieldValue();
            
            // [WIZDAM FIX] Pastikan Closure maupun Objek Validator dieksekusi
            if ($validator instanceof Closure) {
                // Gunakan try-catch sederhana untuk mencegah crash saat validasi dinamis
                try {
                    return $validator($fieldValue); 
                } catch (Exception $e) {
                    return false;
                }
            } elseif (is_object($validator) && method_exists($validator, 'isValid')) {
                // EKSEKUSI PENTING: Jalankan validator standar OJS
                return $validator->isValid($fieldValue);
            }
            
            // Jika ada validator tapi tidak dikenali jenisnya, anggap tidak valid demi keamanan
            return false; 
        }
    }

    //
    // Protected helper methods
    //
    /**
     * Get field value
     * @return mixed
     */
    public function getFieldValue() {
        $form = $this->getForm();
        $fieldValue = $form->getData($this->getField());
        
        // [WIZDAM] PHP 8 Safety: explicit cast to string handles null correctly before trim
        if (is_null($fieldValue) || is_scalar($fieldValue)) {
            $fieldValue = trim((string)$fieldValue);
        }
        
        return $fieldValue;
    }

    /**
     * Check if field value is empty and optional.
     * @return boolean
     */
    public function isEmptyAndOptional() {
        if ($this->getType() != FORM_VALIDATOR_OPTIONAL_VALUE) return false;

        $fieldValue = $this->getFieldValue();
        if (is_scalar($fieldValue)) {
            return (string)$fieldValue === '';
        } else {
            return empty($fieldValue);
        }
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorLocale.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorLocale
 * @ingroup form_validation
 *
 * @brief Class to represent a form validation check for localized fields.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorLocale extends FormValidator {
    /** @var string Symbolic name of the locale to require */
    protected string $_requiredLocale;

    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param string|null $requiredLocale The name of the required locale, i.e. en_US
     * @param Validator|null $validator the validator used to validate this form field (optional)
     */
    public function __construct($form, $field, $type, $message, $requiredLocale = null, $validator = null) {
        // [WIZDAM] Use parent constructor to initialize standard properties ($this->_form, etc.)
        parent::__construct($form, $field, $type, $message, $validator);

        if ($requiredLocale === null) {
            $requiredLocale = AppLocale::getPrimaryLocale();
        }
        $this->_requiredLocale = $requiredLocale;

        // [WIZDAM] PHP 8 Safety: Initialize array key if missing
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
    public function FormValidatorLocale($form, $field, $type, $message, $requiredLocale = null, $validator = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $requiredLocale, $validator);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the error message associated with a failed validation check.
     * @see FormValidator::getMessage()
     * @return string
     */
    public function getMessage() {
        $allLocales = AppLocale::getAllLocales();
        // [WIZDAM] Safety check if locale exists in registry
        $localeName = isset($allLocales[$this->_requiredLocale]) ? $allLocales[$this->_requiredLocale] : $this->_requiredLocale;
        
        return parent::getMessage() . ' (' . $localeName . ')';
    }

    //
    // Protected helper methods
    //
    /**
     * @see FormValidator::getFieldValue()
     * @return mixed
     */
    public function getFieldValue() {
        $form = $this->getForm();
        $data = $form->getData($this->getField());

        $fieldValue = '';
        
        // [WIZDAM] Strict check for array (localized data is always array)
        if (is_array($data) && isset($data[$this->_requiredLocale])) {
            $fieldValue = $data[$this->_requiredLocale];
            
            // PHP 8 requires explicit string cast for trim if value is not string
            if (is_scalar($fieldValue)) {
                $fieldValue = trim((string)$fieldValue);
            }
        }
        return $fieldValue;
    }
}

?>
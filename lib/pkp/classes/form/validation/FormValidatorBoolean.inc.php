<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorBoolean.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorBoolean
 * @ingroup form_validation
 *
 * @brief Form validation check that checks if the value can be
 * interpreted as a boolean value. An empty field is considered
 * 'false', a value of '1' is considered 'true'.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorBoolean extends FormValidator {
    
    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $message the error message for validation failures (i18n key)
     */
    public function __construct($form, $field, $message) {
        parent::__construct($form, $field, FORM_VALIDATOR_OPTIONAL_VALUE, $message);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorBoolean($form, $field, $message) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $message);
    }

    //
    // Public methods
    //
    /**
     * Value is valid if it is empty (false) or has
     * value '1' (true). This assumes checkbox
     * behavior in the form.
     * @see FormValidator::isValid()
     * @return boolean
     */
    public function isValid() {
        $value = $this->getFieldValue();
        
        // [WIZDAM] PHP 8 Safety: strict comparison for 'on', loose check for empty (handles null/empty string)
        if (empty($value) || $value === 'on') {
            // Make sure that the form will contain a real
            // boolean value after validation.
            // [WIZDAM] Removed reference assignment =&
            $form = $this->getForm();
            
            // Normalize value to boolean primitive
            $normalizedValue = ($value === 'on');
            
            $form->setData($this->getField(), $normalizedValue);
            return true;
        } else {
            return false;
        }
    }
}

?>
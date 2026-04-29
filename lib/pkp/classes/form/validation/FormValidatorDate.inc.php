<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorDate.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorDate
 * @ingroup form_validation
 *
 * @brief Form validation check that field is a date or date part.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');
import('lib.pkp.classes.validation.ValidatorDate');

class FormValidatorDate extends FormValidator {

    /** * @var int Date minimum resolution required
     */
    protected int $_scopeMin;

    /** * @var int Date maximum resolution allowed
     */
    protected int $_scopeMax;
    
    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param int $dateFormat the ValidatorDate date format to allow
     * @param int $dateScopeMin the minimum resolution of a date to allow
     * @param int $dateScopeMax the maximum resolution of a date to allow
     */
    public function __construct($form, $field, $type, $message, $dateFormat = DATE_FORMAT_ISO, $dateScopeMin = VALIDATOR_DATE_SCOPE_YEAR, $dateScopeMax = VALIDATOR_DATE_SCOPE_DAY) {
        $validator = new ValidatorDate($dateFormat);
        $this->_scopeMin = (int) $dateScopeMin;
        $this->_scopeMax = (int) $dateScopeMax;
        parent::__construct($form, $field, $type, $message, $validator);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorDate($form, $field, $type, $message, $dateFormat = DATE_FORMAT_ISO, $dateScopeMin = VALIDATOR_DATE_SCOPE_YEAR, $dateScopeMax = VALIDATOR_DATE_SCOPE_DAY) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $dateFormat, $dateScopeMin, $dateScopeMax);
    }

    //
    // Implement abstract methods from Validator
    //
    /**
     * @see Validator::isValid()
     * @return boolean
     */
    public function isValid() {
        // check if generally formatted as a date and if required
        if (!parent::isValid()) return false;
        
        // if parent::isValid is true and $value is empty, this value is optional
        $fieldValue = $this->getFieldValue();
        
        // [WIZDAM] PHP 8 Safety: Handle empty strings/nulls gracefully
        if (empty($fieldValue)) return true;

        /** @var ValidatorDate $validator */
        $validator = parent::getValidator();
        return $validator->isValid($fieldValue, $this->_scopeMin, $this->_scopeMax);
    }
}

?>
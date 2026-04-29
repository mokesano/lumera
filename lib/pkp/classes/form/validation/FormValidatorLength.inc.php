<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorLength.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorLength
 * @ingroup form_validation
 *
 * @brief Form validation check that checks if a field's length meets certain requirements.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import ('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorLength extends FormValidator {

    /** @var string comparator to use (== | != | < | > | <= | >= ) */
    protected string $_comparator;

    /** @var int length to compare with */
    protected int $_length;

    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param string $comparator
     * @param int $length
     */
    public function __construct($form, $field, $type, $message, $comparator, $length) {
        parent::__construct($form, $field, $type, $message);
        $this->_comparator = $comparator;
        $this->_length = (int) $length;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorLength($form, $field, $type, $message, $comparator, $length) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $comparator, $length);
    }

    //
    // Public methods
    //
    /**
     * @see FormValidator::isValid()
     * Value is valid if it is empty and optional or meets the specified length requirements.
     * @return boolean
     */
    public function isValid() {
        if ($this->isEmptyAndOptional()) {
            return true;
        } else {
            $value = $this->getFieldValue();
            
            // [WIZDAM] PHP 8 Safety: Handle null or non-string values before strlen
            if (is_null($value)) {
                $value = '';
            } elseif (!is_string($value) && !is_numeric($value)) {
                // If it's an array or object, cast to string might trigger warnings or return "Array"
                // Safest to force string cast immediately
                $value = (string) $value;
            }

            $length = PKPString::strlen((string)$value);
            
            switch ($this->_comparator) {
                case '==':
                    return $length == $this->_length;
                case '!=':
                    return $length != $this->_length;
                case '<':
                    return $length < $this->_length;
                case '>':
                    return $length > $this->_length;
                case '<=':
                    return $length <= $this->_length;
                case '>=':
                    return $length >= $this->_length;
            }
            return false;
        }
    }
}

?>
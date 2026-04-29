<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorInSet.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorInSet
 * @ingroup form_validation
 *
 * @brief Form validation check that checks if value is within a certain set.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorInSet extends FormValidator {

    /** @var array of all values accepted as valid */
    protected array $_acceptedValues;

    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param array $acceptedValues all possible accepted values
     */
    public function __construct($form, $field, $type, $message, array $acceptedValues) {
        parent::__construct($form, $field, $type, $message);
        $this->_acceptedValues = $acceptedValues;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorInSet($form, $field, $type, $message, $acceptedValues) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $acceptedValues);
    }

    //
    // Public methods
    //
    /**
     * Value is valid if it is empty and optional or is in the set of accepted values.
     * @see FormValidator::isValid()
     * @return boolean
     */
    public function isValid() {
        import('lib.pkp.classes.validation.ValidatorInSet');
        $validator = new ValidatorInSet($this->_acceptedValues);
        return $this->isEmptyAndOptional() || $validator->isValid($this->getFieldValue());
    }
}

?>
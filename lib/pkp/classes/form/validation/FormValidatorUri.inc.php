<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorUri.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorUri
 * @ingroup form_validation
 * @see FormValidator
 *
 * @brief Form validation check for URIs.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */


import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorUri extends FormValidator {
    
    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param array|null $allowedSchemes the allowed URI schemes
     */
    public function __construct($form, $field, $type, $message, ?array $allowedSchemes = null) {
        import('lib.pkp.classes.validation.ValidatorUri');
        $validator = new ValidatorUri($allowedSchemes);
        parent::__construct($form, $field, $type, $message, $validator);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorUri($form, $field, $type, $message, $allowedSchemes = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $allowedSchemes);
    }
}
?>
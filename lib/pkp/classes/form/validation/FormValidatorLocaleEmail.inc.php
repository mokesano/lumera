<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorLocaleEmail.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorLocaleEmail
 * @ingroup form_validation
 * @see FormValidatorLocale
 *
 * @brief Form validation check for email addresses.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidatorLocale');
import('lib.pkp.classes.validation.ValidatorEmail');

class FormValidatorLocaleEmail extends FormValidatorLocale {
    
    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param string|null $requiredLocale The symbolic name of the required locale
     */
    public function __construct($form, $field, $type, $message, $requiredLocale = null) {
        $validator = new ValidatorEmail();
        parent::__construct($form, $field, $type, $message, $requiredLocale, $validator);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorLocaleEmail($form, $field, $type, $message, $requiredLocale = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $requiredLocale);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorUrl.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorUrl
 * @ingroup form_validation
 * @see FormValidator
 *
 * @brief Form validation check for URLs.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');
import('lib.pkp.classes.validation.ValidatorUrl');

class FormValidatorUrl extends FormValidator {
    
    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     */
    public function __construct($form, $field, $type, $message) {
        $validator = new ValidatorUrl();
        parent::__construct($form, $field, $type, $message, $validator);
        
        // [WIZDAM] PHP 8 Safety: Initialize array key if missing before push
        if (!isset($form->cssValidation[$field])) {
            $form->cssValidation[$field] = [];
        }
        array_push($form->cssValidation[$field], 'url');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorUrl($form, $field, $type, $message) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message);
    }
}

?>
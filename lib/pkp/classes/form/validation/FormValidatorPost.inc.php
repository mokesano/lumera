<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorPost.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorPost
 * @ingroup form_validation
 *
 * @brief Form validation check to make sure the form is POSTed.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import ('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorPost extends FormValidator {
    
    /**
     * Constructor.
     * @param Form $form
     * @param string $message the locale key to use (optional)
     */
    public function __construct($form, string $message = 'form.postRequired') {
        // 'dummy' field and REQUIRED type are passed to satisfy parent signature,
        // as this validator checks the request method, not a specific field.
        parent::__construct($form, 'dummy', FORM_VALIDATOR_REQUIRED_VALUE, $message);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorPost($form, $message = 'form.postRequired') {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $message);
    }

    //
    // Public methods
    //
    /**
     * Check if form was posted.
     * overrides FormValidator::isValid()
     * @return boolean
     */
    public function isValid() {
        // [WIZDAM] Use Application context to retrieve request instead of static wrapper
        return Application::get()->getRequest()->isPost();
    }
}

?>
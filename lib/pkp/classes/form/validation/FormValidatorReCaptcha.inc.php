<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorReCaptcha.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorReCaptcha
 * @ingroup form_validation
 *
 * @brief Form validation check reCaptcha values.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorReCaptcha extends FormValidator {
    /** @var string reCaptcha challenge form field name */
    protected string $_challengeField;

    /** @var string reCaptcha response form field name */
    protected string $_responseField;

    /** @var string */
    protected string $_userIp;

    /** @var string hostname to enforce on response */
    protected string $_hostEnforced;

    /**
     * Constructor.
     * @param Form $form object
     * @param string $challengeField Name of the field containing the challenge
     * @param string $responseField Name of the field containing the user response
     * @param string $userIp IP address of user request
     * @param string $message Key of message to display on mismatch
     * @param string $host A hostname to enforce
     */
    public function __construct($form, string $challengeField, string $responseField, string $userIp, string $message, string $host = '') {
        parent::__construct($form, $challengeField, FORM_VALIDATOR_REQUIRED_VALUE, $message);
        $this->_challengeField = $challengeField;
        $this->_responseField = $responseField;
        $this->_userIp = $userIp;
        $this->_hostEnforced = $host;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorReCaptcha($form, $challengeField, $responseField, $userIp, $message, $host = '') {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $challengeField, $responseField, $userIp, $message, $host);
    }

    //
    // Public methods
    //
    /**
     * @see FormValidator::isValid()
     * Determine whether or not the form meets this ReCaptcha constraint.
     * @return boolean
     */
    public function isValid() {
        import('lib.pkp.lib.recaptcha.recaptchalib');
        $privateKey = Config::getVar('captcha', 'recaptcha_private_key');
        $reCaptchaVersion = (int) Config::getVar('captcha', 'recaptcha_version', RECAPTCHA_VERSION_LEGACY);
        
        $form = $this->getForm();
        
        // [WIZDAM] PHP 8 Safety: Handle nulls if fields are missing in POST
        $challengeVal = $form->getData($this->_challengeField);
        $responseVal = $form->getData($this->_responseField);

        // Ensure we pass strings to the library function
        $challengeField = is_string($challengeVal) ? $challengeVal : '';
        $responseField = is_string($responseVal) ? $responseVal : '';

        $checkResponse = recaptcha_versioned_check_answer (
            $reCaptchaVersion,
            $this->_hostEnforced,
            $privateKey,
            $this->_userIp,
            $challengeField,
            $responseField
        );

        if ($checkResponse && $checkResponse->is_valid) {
            return true;
        } else {
            return false;
        }
    }
}

?>
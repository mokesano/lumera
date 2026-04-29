<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorCaptcha.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorCaptcha
 * @ingroup form_validation
 *
 * @brief Form validation check captcha values.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorCaptcha extends FormValidator {
    /** @var string Name of the captcha ID field in the form */
    protected string $_captchaIdField;

    /**
     * Constructor.
     * @param Form $form object
     * @param string $field Name of captcha value submitted by user
     * @param string $captchaIdField Name of captcha ID field
     * @param string $message Key of message to display on mismatch
     */
    public function __construct($form, $field, $captchaIdField, $message) {
        parent::__construct($form, $field, FORM_VALIDATOR_REQUIRED_VALUE, $message);
        $this->_captchaIdField = $captchaIdField;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorCaptcha($form, $field, $captchaIdField, $message) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $captchaIdField, $message);
    }

    //
    // Public methods
    //
    /**
     * @see FormValidator::isValid()
     * Determine whether or not the form meets this Captcha constraint.
     * @return boolean
     */
    public function isValid() {
        $captchaDao = DAORegistry::getDAO('CaptchaDAO'); /* @var CaptchaDAO $captchaDao */
        
        $form = $this->getForm();
        
        // [WIZDAM] PHP 8 Safety: Validate ID exists and is likely numeric/valid before DAO call
        $captchaId = $form->getData($this->_captchaIdField);
        
        if (empty($captchaId)) {
            return false;
        }

        $captchaValue = $this->getFieldValue();
        
        // Fetch captcha object (removed reference &)
        $captcha = $captchaDao->getCaptcha((int)$captchaId);
        
        if ($captcha && $captcha->getValue() === $captchaValue) {
            $captchaDao->deleteObject($captcha);
            return true;
        }
        
        return false;
    }
}

?>
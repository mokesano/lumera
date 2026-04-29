<?php
declare(strict_types=1);

/**
 * @defgroup form
 */

/**
 * @file classes/form/Form.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Form
 * @ingroup core
 *
 * @brief Class defining basic operations for handling HTML forms.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor, Ref removal, Visibility)
 * - Strict Hook Dispatch
 * - HTML Entity Handling
 */

import('lib.pkp.classes.form.FormError');
import('lib.pkp.classes.form.FormBuilderVocabulary');

// Import all form validators for convenient use in sub-classes
import('lib.pkp.classes.form.validation.FormValidatorAlphaNum');
import('lib.pkp.classes.form.validation.FormValidatorArray');
import('lib.pkp.classes.form.validation.FormValidatorArrayCustom');
import('lib.pkp.classes.form.validation.FormValidatorControlledVocab');
import('lib.pkp.classes.form.validation.FormValidatorCustom');
import('lib.pkp.classes.form.validation.FormValidatorCaptcha');
import('lib.pkp.classes.form.validation.FormValidatorReCaptcha');
import('lib.pkp.classes.form.validation.FormValidatorDate');
import('lib.pkp.classes.form.validation.FormValidatorEmail');
import('lib.pkp.classes.form.validation.FormValidatorInSet');
import('lib.pkp.classes.form.validation.FormValidatorLength');
import('lib.pkp.classes.form.validation.FormValidatorListbuilder');
import('lib.pkp.classes.form.validation.FormValidatorLocale');
import('lib.pkp.classes.form.validation.FormValidatorLocaleEmail');
import('lib.pkp.classes.form.validation.FormValidatorPost');
import('lib.pkp.classes.form.validation.FormValidatorRegExp');
import('lib.pkp.classes.form.validation.FormValidatorUri');
import('lib.pkp.classes.form.validation.FormValidatorUrl');
import('lib.pkp.classes.form.validation.FormValidatorLocaleUrl');
import('lib.pkp.classes.form.validation.FormValidatorISSN');
import('lib.pkp.classes.form.validation.FormValidatorORCID');
import('lib.pkp.classes.form.validation.FormValidatorCSRF');

class Form {

    /** The template file containing the HTML form */
    public $_template;

    /** Associative array containing form data */
    public $_data;

    /** Validation checks for this form */
    public $_checks;

    /** Errors occurring in form validation */
    public $_errors;

    /** Array of field names where an error occurred and the associated error message */
    public $errorsArray;

    /** Array of field names where an error occurred */
    public $errorFields;

    /** Array of errors for the form section currently being processed */
    public $formSectionErrors;

    /** Client-side validation rules **/
    public $cssValidation;

    /** @var $requiredLocale string Symbolic name of required locale */
    public $requiredLocale;

    /** @var $supportedLocales array Set of supported locales */
    public $supportedLocales;

    /**
     * Constructor.
     * @param string|null $template the path to the form template file
     * @param boolean $callHooks
     * @param string|null $requiredLocale
     * @param array|null $supportedLocales
     */
    public function __construct($template = null, $callHooks = true, $requiredLocale = null, $supportedLocales = null) {

        if ($requiredLocale === null) $requiredLocale = AppLocale::getPrimaryLocale();
        $this->requiredLocale = $requiredLocale;
        if ($supportedLocales === null) $supportedLocales = AppLocale::getSupportedFormLocales();
        $this->supportedLocales = $supportedLocales;

        $this->_template = $template;
        $this->_data = array();
        $this->_checks = array();
        $this->_errors = array();
        $this->errorsArray = array();
        $this->errorFields = array();
        $this->formSectionErrors = array();

        if ($callHooks === true) {
            // Hook Dispatch: Object ($this) by val, Template (string) by ref (if modification allowed)
            HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::Constructor', array($this, &$template));
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Form($template = null, $callHooks = true, $requiredLocale = null, $supportedLocales = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::Form(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($template, $callHooks, $requiredLocale, $supportedLocales);
    }


    //
    // Setters and Getters
    //
    /**
     * Set the template
     * @param string $template
     */
    public function setTemplate($template) {
        $this->_template = $template;
    }

    /**
     * Get the template
     * @return string
     */
    public function getTemplate() {
        return $this->_template;
    }

    /**
     * Get the required locale for this form
     * @return string
     */
    public function getRequiredLocale() {
        return $this->requiredLocale;
    }

    //
    // Public Methods
    //
    /**
     * Display the form.
     * @param PKPRequest $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $this->fetch($request, $template, true);
    }

    /**
     * Returns a string of the rendered form
     * @param PKPRequest $request (No & needed)
     * @param string|null $template
     * @param boolean $display
     * @return string the rendered form
     */
    public function fetch($request, $template = null, $display = false) {
        // Set custom template.
        if (!is_null($template)) $this->_template = $template;

        // Hook Dispatch
        $returner = null;
        if (HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::display', array($this, &$returner))) {
            return $returner;
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->setCacheability(CACHEABILITY_NO_STORE);


        // Attach this form object to the Form Builder Vocabulary
        $fbv = $templateMgr->getFBV();
        $fbv->setForm($this);

        $templateMgr->assign($this->_data);
        $templateMgr->assign('isError', !$this->isValid());
        $templateMgr->assign('errors', $this->getErrorsArray());

        // PHP 8: Use array callback without &
        $templateMgr->register_function('form_language_chooser', array($this, 'smartyFormLanguageChooser'));
        $templateMgr->assign('formLocales', $this->supportedLocales);

        // Determine the current locale to display fields with
        $formLocale = $this->getFormLocale();
        $templateMgr->assign('formLocale', $this->getFormLocale());

        // N.B: We have to call $templateMgr->display instead of ->fetch($display)
        $returner = $templateMgr->display($this->_template, null, null, $display);

        // Need to reset the FBV's form
        $nullVar = null;
        $fbv->setForm($nullVar);

        return $returner;
    }

    /**
     * Get the value of a form field.
     * @param string $key
     * @return mixed
     */
    public function getData($key) {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * Set the value of a form field.
     * @param string $key
     * @param mixed $value
     */
    public function setData($key, $value) {
        if (is_string($value)) $value = Core::cleanVar($value);
        $this->_data[$key] = $value;
    }

    /**
     * Initialize form data for a new form.
     */
    public function initData() {
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::initData'), array($this));
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        // Default implementation does nothing.
    }

    /**
     * Validate form data.
     * @param boolean $callHooks
     * @return boolean
     */
    public function validate($callHooks = true) {
        if (!isset($this->errorsArray)) {
            $this->getErrorsArray();
        }

        foreach ($this->_checks as $check) {
            // PHP 8: Passing $this by value (handle) is fine
            $check->setForm($this);

            if (!isset($this->errorsArray[$check->getField()]) && !$check->isValid()) {
                if (method_exists($check, 'getErrorFields') && method_exists($check, 'isArray') && $check->isArray()) {
                    $errorFields = $check->getErrorFields();
                    for ($i=0, $count=count($errorFields); $i < $count; $i++) {
                        $this->addError($errorFields[$i], $check->getMessage());
                        $this->errorFields[$errorFields[$i]] = 1;
                    }
                } else {
                    $this->addError($check->getField(), $check->getMessage());
                    $this->errorFields[$check->getField()] = 1;
                }
            }
        }

        if ($callHooks === true) {
            $value = null;
            if (HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::validate'), array($this, &$value))) {
                return $value;
            }
        }

        if (!defined('SESSION_DISABLE_INIT')) {
            $application = PKPApplication::getApplication();
            $request = $application->getRequest();
            $user = $request->getUser();

            if (!$this->isValid() && $user) {
                // Create a form error notification.
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification(
                    $user->getId(), NOTIFICATION_TYPE_FORM_ERROR, array('contents' => $this->getErrorsArray())
                );
            }
        }

        return $this->isValid();
    }

    /**
     * Execute the form's action.
     * @param object|null $object
     * @return object|null
     */
    public function execute($object = null) {
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::execute'), array($this, &$object));
        return $object;
    }

    /**
     * Get the list of field names that need to support multiple locales
     * @return array
     */
    public function getLocaleFieldNames() {
        $returner = array();
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::getLocaleFieldNames'), array($this, &$returner));
        return $returner;
    }

    /**
     * Determine whether or not the current request results from a resubmit
     * @return boolean
     */
    public function isLocaleResubmit() {
        $formLocale = Request::getUserVar('formLocale');
        return (!empty($formLocale));
    }

    /**
     * Get the default form locale.
     * @return string
     */
    public function getDefaultFormLocale() {
        // PHP 8 Safety: Initialize $formLocale
        $formLocale = AppLocale::getLocale();
        if (!isset($this->supportedLocales[$formLocale])) $formLocale = $this->requiredLocale;
        return $formLocale;
    }

    /**
     * Get the current form locale.
     * @return string
     */
    public function getFormLocale() {
        $formLocale = Request::getUserVar('formLocale');
        if (!$formLocale || !isset($this->supportedLocales[$formLocale])) {
            $formLocale = $this->getDefaultFormLocale();
        }
        return $formLocale;
    }

    /**
     * Adds specified user variables to input data.
     * @param array $vars the names of the variables to read
     */
    public function readUserVars($vars) {
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::readUserVars'), array($this, &$vars));
        foreach ($vars as $k) {
            $this->setData($k, Request::getUserVar($k));
        }
    }

    /**
     * Adds specified user date variables to input data.
     * @param array $vars the names of the date variables to read
     */
    public function readUserDateVars($vars) {
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::readUserDateVars'), array($this, &$vars));
        foreach ($vars as $k) {
            $this->setData($k, Request::getUserDateVar($k));
        }
    }

    /**
     * Add a validation check to the form.
     * @param FormValidator $formValidator
     */
    public function addCheck($formValidator) {
        $this->_checks[] = $formValidator;
    }

    /**
     * Add an error to the form.
     * @param string $field
     * @param string $message
     */
    public function addError($field, $message) {
        $this->_errors[] = new FormError($field, $message);
    }

    /**
     * Add an error field for highlighting on form
     * @param string $field
     */
    public function addErrorField($field) {
        $this->errorFields[$field] = 1;
    }

    /**
     * Check if form passes all validation checks.
     * @return boolean
     */
    public function isValid() {
        return empty($this->_errors);
    }

    /**
     * Return set of errors that occurred in form validation.
     * @return array
     */
    public function getErrorsArray() {
        $this->errorsArray = array();
        foreach ($this->_errors as $error) {
            if (!isset($this->errorsArray[$error->getField()])) {
                $this->errorsArray[$error->getField()] = $error->getMessage();
            }
        }
        return $this->errorsArray;
    }

    /**
     * Add hidden form parameters for the localized fields for this form
     * @param array $params
     * @param object $smarty (No & needed)
     * @return string
     */
    public function smartyFormLanguageChooser($params, $smarty) {
        $returner = '';

        $formLocale = $this->getFormLocale();
        foreach ($this->getLocaleFieldNames() as $field) {
            $values = $this->getData($field);
            if (!is_array($values)) continue;
            foreach ($values as $locale => $value) {
                if ($locale != $formLocale) $returner .= $this->_decomposeArray($field, $value, array($locale));
            }
        }

        $returner .= '<div id="languageSelector"><select size="1" name="formLocale" id="formLocale" class="selectMenu">';
        foreach ($this->supportedLocales as $locale => $name) {
            $returner .= '<option ' . ($locale == $formLocale?'selected="selected" ':'') . 'value="' . htmlentities($locale, ENT_COMPAT, LOCALE_ENCODING) . '">' . htmlentities($name, ENT_COMPAT, LOCALE_ENCODING) . '</option>';
        }
        // PHP 8 Safety: Strict check for params
        $formAction = isset($params['form']) ? htmlentities($params['form'], ENT_COMPAT, LOCALE_ENCODING) : '';
        $formUrl = isset($params['url']) ? htmlentities($params['url'], ENT_QUOTES, LOCALE_ENCODING) : '';
        
        $returner .= '</select><input type="submit" class="button" value="'. __('form.submit'). '" onclick="changeFormAction(\'' . $formAction . '\', \'' . $formUrl . '\'); return false" /></div>';
        return $returner;
    }

    //
    // Private helper methods
    //
    
    /**
     * Convert PHP variable (literals or arrays) into HTML containing hidden input fields.
     * @param string $name
     * @param mixed $value
     * @param array $stack
     * @return string
     */
    public function _decomposeArray($name, $value, $stack) {
        $returner = '';
        if (is_array($value)) {
            foreach ($value as $key => $subValue) {
                $newStack = $stack;
                $newStack[] = $key;
                $returner .= $this->_decomposeArray($name, $subValue, $newStack);
            }
        } else {
            $name = htmlentities((string)$name, ENT_COMPAT, LOCALE_ENCODING);
            $value = htmlentities((string)$value, ENT_COMPAT, LOCALE_ENCODING);
            $returner .= '<input type="hidden" name="' . $name;
            while (($item = array_shift($stack)) !== null) {
                $item = htmlentities((string)$item, ENT_COMPAT, LOCALE_ENCODING);
                $returner .= '[' . $item . ']';
            }
            $returner .= '" value="' . $value . "\" />\n";
        }
        return $returner;
    }
}
?>
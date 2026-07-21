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

    /** @var string|null $_template The template file containing the HTML form */
    public $_template;

    /** @var array $_data Associative array containing form data */
    public $_data;

    /** @var array $_checks Validation checks for this form */
    public $_checks;

    /** @var array $_errors Errors occurring in form validation */
    public $_errors;

    /** @var array $errorsArray Array of field names where an error occurred and the associated error message */
    public $errorsArray;

    /** @var array $errorFields Array of field names where an error occurred */
    public $errorFields;

    /** @var array $formSectionErrors Array of errors for the form section currently being processed */
    public $formSectionErrors;

    /** @var mixed $cssValidation Client-side validation rules **/
    public $cssValidation;

    /** @var string $requiredLocale Symbolic name of required locale */
    public $requiredLocale;

    /** @var array $supportedLocales Set of supported locales */
    public $supportedLocales;

    /**
     * Constructor.
     * @param mixed $template the path to the form template file
     * @param boolean $callHooks
     * @param mixed $requiredLocale
     * @param mixed $supportedLocales
     */
    public function __construct($template = null, $callHooks = true, $requiredLocale = null, $supportedLocales = null) {

        if ($requiredLocale === null) {
            $requiredLocale = AppLocale::getPrimaryLocale();
        }
        $this->requiredLocale = (string) $requiredLocale;
        
        if ($supportedLocales === null) {
            $supportedLocales = AppLocale::getSupportedFormLocales();
        }
        $this->supportedLocales = is_array($supportedLocales) ? $supportedLocales : [];

        $this->_template = $template !== null ? (string) $template : null;
        $this->_data = [];
        $this->_checks = [];
        $this->_errors = [];
        $this->errorsArray = [];
        $this->errorFields = [];
        $this->formSectionErrors = [];

        if ($callHooks === true) {
            HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::Constructor', [$this, &$template]);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     * @param mixed $template
     * @param boolean $callHooks
     * @param mixed $requiredLocale
     * @param mixed $supportedLocales
     */
    public function Form($template = null, $callHooks = true, $requiredLocale = null, $supportedLocales = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        $this->__construct($template, $callHooks, $requiredLocale, $supportedLocales);
    }


    //
    // Setters and Getters
    //
    /**
     * Set the template
     * @param mixed $template
     */
    public function setTemplate($template) {
        $this->_template = $template !== null ? (string) $template : null;
    }

    /**
     * Get the template
     * @return string|null
     */
    public function getTemplate() {
        return $this->_template;
    }

    /**
     * Get the required locale for this form
     * @return string
     */
    public function getRequiredLocale() {
        return (string) $this->requiredLocale;
    }

    //
    // Public Methods
    //
    /**
     * Display the form.
     * @param mixed $request
     * @param mixed $template
     */
    public function display($request = null, $template = null) {
        $this->fetch($request, $template, true);
    }

    /**
     * Returns a string of the rendered form
     * @param mixed $request
     * @param mixed $template
     * @param boolean $display
     * @return string the rendered form
     */
    public function fetch($request, $template = null, $display = false) {
        // Set custom template.
        if ($template !== null) {
            $this->_template = (string) $template;
        }

        // Hook Dispatch
        $returner = null;
        if (HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::display', [$this, &$returner])) {
            return (string) $returner;
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->setCacheability(CACHEABILITY_NO_STORE);

        // Attach this form object to the Form Builder Vocabulary
        $fbv = $templateMgr->getFBV();
        $fbv->setForm($this);

        $templateMgr->assign($this->_data);
        $templateMgr->assign('isError', !$this->isValid());
        $templateMgr->assign('errors', $this->getErrorsArray());

        $templateMgr->register_function('form_language_chooser', [$this, 'smartyFormLanguageChooser']);
        $templateMgr->assign('formLocales', $this->supportedLocales);
        $templateMgr->assign('formLocale', $this->getFormLocale());

        $returner = $templateMgr->display($this->_template, null, null, (bool) $display);

        // Need to reset the FBV's form
        $fbv->setForm(null);

        return (string) $returner;
    }

    /**
     * Get the value of a form field.
     * @param mixed $key
     * @return mixed
     */
    public function getData($key) {
        $key = (string) $key;
        return $this->_data[$key] ?? null;
    }

    /**
     * Set the value of a form field.
     * @param mixed $key
     * @param mixed $value
     */
    public function setData($key, $value) {
        $key = (string) $key;
        if (is_string($value)) {
            $value = Core::cleanVar($value);
        }
        $this->_data[$key] = $value;
    }

    /**
     * Initialize form data for a new form.
     */
    public function initData() {
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::initData'), [$this]);
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
        if (!is_array($this->errorsArray)) {
            $this->getErrorsArray();
        }

        foreach ($this->_checks as $check) {
            $check->setForm($this);
            $field = $check->getField();

            if (!isset($this->errorsArray[$field]) && !$check->isValid()) {
                if (method_exists($check, 'getErrorFields') && method_exists($check, 'isArray') && $check->isArray()) {
                    $errorFields = $check->getErrorFields();
                    if (is_array($errorFields)) {
                        foreach ($errorFields as $errorField) {
                            $this->addError($errorField, $check->getMessage());
                            $this->errorFields[$errorField] = 1;
                        }
                    }
                } else {
                    $this->addError($field, $check->getMessage());
                    $this->errorFields[$field] = 1;
                }
            }
        }

        if ($callHooks === true) {
            $value = null;
            if (HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::validate'), [$this, &$value])) {
                return (bool) $value;
            }
        }

        if (!defined('SESSION_DISABLE_INIT')) {
            $application = PKPApplication::getApplication();
            if ($application) {
                $request = $application->getRequest();
                $user = $request ? $request->getUser() : null;

                if (!$this->isValid() && $user) {
                    import('classes.notification.NotificationManager');
                    $notificationManager = new NotificationManager();
                    $notificationManager->createTrivialNotification(
                        $user->getId(), NOTIFICATION_TYPE_FORM_ERROR, ['contents' => $this->getErrorsArray()]
                    );
                }
            }
        }

        return $this->isValid();
    }

    /**
     * Execute the form's action.
     * @param mixed $object
     * @return mixed
     */
    public function execute($object = null) {
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::execute'), [$this, &$object]);
        return $object;
    }

    /**
     * Get the list of field names that need to support multiple locales
     * @return array
     */
    public function getLocaleFieldNames() {
        $returner = [];
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::getLocaleFieldNames'), [$this, &$returner]);
        return is_array($returner) ? $returner : [];
    }

    /**
     * Determine whether or not the current request results from a resubmit
     * @return boolean
     */
    public function isLocaleResubmit() {
        $formLocale = Request::getUserVar('formLocale');
        return !empty($formLocale);
    }

    /**
     * Get the default form locale.
     * @return string
     */
    public function getDefaultFormLocale() {
        $formLocale = AppLocale::getLocale();
        if (!is_array($this->supportedLocales) || !isset($this->supportedLocales[$formLocale])) {
            $formLocale = $this->requiredLocale;
        }
        return (string) $formLocale;
    }

    /**
     * Get the current form locale.
     * @return string
     */
    public function getFormLocale() {
        $formLocale = Request::getUserVar('formLocale');
        if (!$formLocale || !is_array($this->supportedLocales) || !isset($this->supportedLocales[$formLocale])) {
            $formLocale = $this->getDefaultFormLocale();
        }
        return (string) $formLocale;
    }

    /**
     * Adds specified user variables to input data.
     * @param mixed $vars the names of the variables to read
     */
    public function readUserVars($vars) {
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::readUserVars'), [$this, &$vars]);
        if (is_array($vars)) {
            foreach ($vars as $k) {
                $this->setData($k, Request::getUserVar($k));
            }
        }
    }

    /**
     * Adds specified user date variables to input data.
     * @param mixed $vars the names of the date variables to read
     */
    public function readUserDateVars($vars) {
        HookRegistry::dispatch(strtolower_codesafe(get_class($this) . '::readUserDateVars'), [$this, &$vars]);
        if (is_array($vars)) {
            foreach ($vars as $k) {
                $this->setData($k, Request::getUserDateVar($k));
            }
        }
    }

    /**
     * Add a validation check to the form.
     * @param mixed $formValidator
     */
    public function addCheck($formValidator) {
        $this->_checks[] = $formValidator;
    }

    /**
     * Add an error to the form.
     * @param mixed $field
     * @param mixed $message
     */
    public function addError($field, $message) {
        $this->_errors[] = new FormError((string) $field, (string) $message);
    }

    /**
     * Add an error field for highlighting on form
     * @param mixed $field
     */
    public function addErrorField($field) {
        $this->errorFields[(string) $field] = 1;
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
        $this->errorsArray = [];
        if (is_array($this->_errors)) {
            foreach ($this->_errors as $error) {
                $field = $error->getField();
                if (!isset($this->errorsArray[$field])) {
                    $this->errorsArray[$field] = $error->getMessage();
                }
            }
        }
        return $this->errorsArray;
    }

    /**
     * Add hidden form parameters for the localized fields for this form
     * @param mixed $params
     * @param mixed $smarty
     * @return string
     */
    public function smartyFormLanguageChooser($params, $smarty) {
        $returner = '';

        $formLocale = $this->getFormLocale();
        $localeFieldNames = $this->getLocaleFieldNames();
        
        if (is_array($localeFieldNames)) {
            foreach ($localeFieldNames as $field) {
                $values = $this->getData($field);
                if (!is_array($values)) continue;
                foreach ($values as $locale => $value) {
                    if ($locale !== $formLocale) {
                        $returner .= $this->_decomposeArray($field, $value, [$locale]);
                    }
                }
            }
        }

        $returner .= '<div id="languageSelector"><select size="1" name="formLocale" id="formLocale" class="selectMenu">';
        if (is_array($this->supportedLocales)) {
            foreach ($this->supportedLocales as $locale => $name) {
                $selected = ($locale === $formLocale) ? 'selected="selected" ' : '';
                $returner .= '<option ' . $selected . 'value="' . htmlentities((string)$locale, ENT_COMPAT, LOCALE_ENCODING) . '">' . htmlentities((string)$name, ENT_COMPAT, LOCALE_ENCODING) . '</option>';
            }
        }

        $formAction = isset($params['form']) ? htmlentities((string)$params['form'], ENT_COMPAT, LOCALE_ENCODING) : '';
        $formUrl = isset($params['url']) ? htmlentities((string)$params['url'], ENT_QUOTES, LOCALE_ENCODING) : '';
        
        $returner .= '</select><input type="submit" class="button" value="'. __('form.submit'). '" onclick="changeFormAction(\'' . $formAction . '\', \'' . $formUrl . '\'); return false" /></div>';
        return $returner;
    }

    //
    // Private helper methods
    //
    
    /**
     * Convert PHP variable (literals or arrays) into HTML containing hidden input fields.
     * @param mixed $name
     * @param mixed $value
     * @param mixed $stack
     * @return string
     */
    public function _decomposeArray($name, $value, $stack) {
        $returner = '';
        if (is_array($value)) {
            foreach ($value as $key => $subValue) {
                $newStack = is_array($stack) ? $stack : [];
                $newStack[] = $key;
                $returner .= $this->_decomposeArray($name, $subValue, $newStack);
            }
        } else {
            $name = htmlentities((string)$name, ENT_COMPAT, LOCALE_ENCODING);
            $value = htmlentities((string)$value, ENT_COMPAT, LOCALE_ENCODING);
            $returner .= '<input type="hidden" name="' . $name;

            if (is_array($stack)) {
                foreach ($stack as $item) {
                    $item = htmlentities((string)$item, ENT_COMPAT, LOCALE_ENCODING);
                    $returner .= '[' . $item . ']';
                }
            }
            $returner .= '" value="' . $value . "\" />\n";
        }
        return $returner;
    }
    
}
?>
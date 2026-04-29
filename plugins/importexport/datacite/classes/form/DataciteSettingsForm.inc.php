<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/datacite/classes/form/DataciteSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataciteSettingsForm
 * @ingroup plugins_importexport_datacite_classes_form
 *
 * @brief Form for journal managers to setup the DataCite plug-in.
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */


if (!class_exists('DOIExportSettingsForm')) { // Bug #7848
    import('plugins.importexport.datacite.classes.form.DOIExportSettingsForm');
}

class DataciteSettingsForm extends DOIExportSettingsForm {

    //
    // Constructor
    //
    /**
     * Constructor
     * @param object $plugin DataciteExportPlugin
     * @param integer $journalId
     */
    public function __construct($plugin, $journalId) {
        // Configure the object.
        parent::__construct($plugin, $journalId);

        // Add form validation checks.
        // The username is used in HTTP basic authentication and according to RFC2617 it therefore may not contain a colon.
        $this->addCheck(new FormValidatorRegExp($this, 'username', FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.importexport.datacite.settings.form.usernameRequired', '/^[^:]+$/'));
        
        // [PHP 8 FIX] Replaced deprecated create_function with anonymous Closure
        $this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'plugins.importexport.datacite.settings.form.usernameRequired', function($username) {
            if ($this->getData('automaticRegistration') && empty($username)) { return false; } return true;
        }));
        
        $this->addCheck(new FormValidatorCustom($this, 'password', 'required', 'plugins.importexport.datacite.settings.form.passwordRequired', function($password) {
            if ($this->getData('automaticRegistration') && empty($password)) { return false; } return true;
        }));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataciteSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::DataciteSettingsForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param PKPRequest $request
     * @param string $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        $plugin = $this->_plugin;
        $templateMgr->assign('unregisteredURL', $request->url(null, null, 'importexport', ['plugin', $plugin->getName(), 'all']));
        parent::display($request);
    }

    //
    // Implement template methods from DOIExportSettingsForm
    //
    /**
     * Get the names and types of form fields.
     * @see DOIExportSettingsForm::getFormFields()
     * @return array
     */
    public function getFormFields() {
        return [
            'username' => 'string',
            'password' => 'string',
            'automaticRegistration' => 'bool'
        ];
    }

    /**
     * Determine if a setting is optional.
     * @see DOIExportSettingsForm::isOptional()
     * @param string $settingName
     * @return bool
     */
    public function isOptional($settingName) {
        return in_array($settingName, ['username', 'password', 'automaticRegistration']);
    }
}

?>
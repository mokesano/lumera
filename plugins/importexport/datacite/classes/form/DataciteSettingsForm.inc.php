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
 */

if (!class_exists('DOIExportSettingsForm')) {
    import('plugins.importexport.datacite.classes.form.DOIExportSettingsForm');
}

class DataciteSettingsForm extends DOIExportSettingsForm {

    //
    // Constructor
    //
    /**
     * Constructor
     * @param DataciteDoiExportPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        parent::__construct($plugin, (int) $journalId);

        // Add form validation checks.
        // The username is used in HTTP basic authentication and according to RFC2617 it therefore may not contain a colon.
        $this->addCheck(new FormValidatorRegExp($this, 'username', FORM_VALIDATOR_OPTIONAL_VALUE, 'plugins.importexport.datacite.settings.form.usernameRequired', '/^[^:]+$/'));
        
        $this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'plugins.importexport.datacite.settings.form.usernameRequired', function($username) {
            if ($this->getData('automaticRegistration') && empty($username)) {
                return false;
            }
            return true;
        }));
        
        $this->addCheck(new FormValidatorCustom($this, 'password', 'required', 'plugins.importexport.datacite.settings.form.passwordRequired', function($password) {
            if ($this->getData('automaticRegistration') && empty($password)) {
                return false;
            }
            return true;
        }));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param DataciteDoiExportPlugin $plugin
     * @param int $journalId
     */
    public function DataciteSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param PKPRequest|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);
        $plugin = $this->_plugin;
        
        if ($plugin) {
            $templateMgr->assign('unregisteredURL', $request->url(null, null, 'importexport', ['plugin', $plugin->getName(), 'all']));
        }
        
        parent::display($request, $template);
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
        return in_array($settingName, ['username', 'password', 'automaticRegistration'], true);
    }

}
?>
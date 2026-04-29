<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/classes/form/CrossRefSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossRefSettingsForm
 * @ingroup plugins_importexport_crossref_classes_form
 *
 * @brief Form for journal managers to setup the CrossRef plug-in.
 * * MODERNIZED FOR WIZDAM FORK
 */


if (!class_exists('DOIExportSettingsForm')) { // Bug #7848
    import('plugins.importexport.crossref.classes.form.DOIExportSettingsForm');
}

class CrossRefSettingsForm extends DOIExportSettingsForm {

    //
    // Constructor
    //
    /**
     * Constructor
     * @param $plugin CrossRefExportPlugin
     * @param $journalId integer
     */
    public function __construct($plugin, $journalId) {
        // Configure the object.
        parent::__construct($plugin, $journalId);

        // Add form validation checks.
        $this->addCheck(new FormValidator($this, 'depositorName', 'required', 'plugins.importexport.crossref.settings.form.depositorNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'depositorEmail', 'required', 'plugins.importexport.crossref.settings.form.depositorEmailRequired'));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CrossRefSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param $request PKPRequest
     * @param $template string
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        $plugin = $this->_plugin;
        $templateMgr->assign('unregisteredURL', $request->url(null, null, 'importexport', ['plugin', $plugin->getName(), 'articles']));
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
            'depositorName' => 'string',
            'depositorEmail' => 'string',
            'username' => 'string',
            'password' => 'string',
            'automaticRegistration' => 'bool'
        ];
    }

    /**
     * Determine if a setting is optional.
     * @see DOIExportSettingsForm::isOptional()
     * @param $settingName string
     * @return bool
     */
    public function isOptional($settingName) {
        return in_array($settingName, ['username', 'password', 'automaticRegistration']);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/medra/classes/form/MedraSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MedraSettingsForm
 * @ingroup plugins_importexport_medra_classes_form
 *
 * @brief Form for journal managers to setup the mEDRA plug-in.
 */

if (!class_exists('DOIExportSettingsForm')) { // Bug #7848
    import('plugins.importexport.medra.classes.form.DOIExportSettingsForm');
}

class MedraSettingsForm extends DOIExportSettingsForm {

    /**
     * Constructor
     * @param MedraExportPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        // Configure the object.
        parent::__construct($plugin, (int) $journalId);

        // Add form validation checks.
        $this->addCheck(new FormValidator($this, 'registrantName', 'required', 'plugins.importexport.medra.settings.form.registrantNameRequired'));
        $this->addCheck(new FormValidator($this, 'fromCompany', 'required', 'plugins.importexport.medra.settings.form.fromCompanyRequired'));
        $this->addCheck(new FormValidator($this, 'fromName', 'required', 'plugins.importexport.medra.settings.form.fromNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'fromEmail', 'required', 'plugins.importexport.medra.settings.form.fromEmailRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'exportIssuesAs', 'required', 'plugins.importexport.medra.settings.form.exportIssuesAs', array(O4DOI_ISSUE_AS_WORK, O4DOI_ISSUE_AS_MANIFESTATION)));
        $this->addCheck(new FormValidatorInSet($this, 'publicationCountry', 'required', 'plugins.importexport.medra.settings.form.publicationCountry', array_keys($this->_getCountries())));
        // The username is used in HTTP basic authentication and according to RFC2617 it therefore may not contain a colon.
        $this->addCheck(new FormValidatorRegExp($this, 'username', 'optional', 'plugins.importexport.medra.settings.form.usernameRequired', '/^[^:]+$/'));
        
        $this->addCheck(new FormValidatorCustom($this, 'username', 'required', 'plugins.importexport.medra.settings.form.usernameRequired', function($username, $form) {
            if ($form->getData('automaticRegistration') && empty($username)) {
                return false;
            }
            return true;
        }, array($this)));

        $this->addCheck(new FormValidatorCustom($this, 'password', 'required', 'plugins.importexport.medra.settings.form.passwordRequired', function($password, $form) {
            if ($form->getData('automaticRegistration') && empty($password)) {
                return false;
            }
            return true;
        }, array($this)));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MedraSettingsForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Implement template methods from Form
    //
    /**
     * Display the form.
     * @see Form::display()
     * @param object|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null): void {
        $templateMgr = TemplateManager::getManager($request);
        $plugin = $this->_plugin;
        $templateMgr->assign('unregisteredURL', $request->url(null, null, 'importexport', array('plugin', $plugin->getName(), 'all')));

        // Issue export options.
        $exportIssueOptions = array(
            O4DOI_ISSUE_AS_WORK => __('plugins.importexport.medra.settings.form.work'),
            O4DOI_ISSUE_AS_MANIFESTATION => __('plugins.importexport.medra.settings.form.manifestation'),
        );
        $templateMgr->assign('exportIssueOptions', $exportIssueOptions);

        // Countries.
        $templateMgr->assign('countries', $this->_getCountries());
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
    public function getFormFields(): array {
        return array(
            'registrantName' => 'string',
            'fromCompany' => 'string',
            'fromName' => 'string',
            'fromEmail' => 'string',
            'publicationCountry' => 'string',
            'exportIssuesAs' => 'int',
            'username' => 'string',
            'password' => 'string',
            'automaticRegistration' => 'bool'
        );
    }

    /**
     * Determine if a setting is optional.
     * @see DOIExportSettingsForm::isOptional()
     * @param string $settingName
     * @return bool
     */
    public function isOptional($settingName): bool {
        return in_array($settingName, array('username', 'password', 'automaticRegistration'));
    }

    //
    // Private helper methods
    //
    /**
     * Return a list of countries eligible as publication countries.
     * @return array
     */
    public function _getCountries(): array {
        $countryDao = DAORegistry::getDAO('CountryDAO'); /* @var $countryDao CountryDAO */
        $countries = $countryDao->getCountries();
        return $countries;
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/classes/form/DOIExportSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOIExportSettingsForm
 * @ingroup plugins_importexport_crossref_classes_form
 *
 * @brief Form base class for journal managers to setup DOI export plug-ins.
 * * MODERNIZED FOR WIZDAM FORK
 */


import('lib.pkp.classes.form.Form');

class DOIExportSettingsForm extends Form {

    //
    // Protected properties
    //
    /** @var int */
    public $_journalId;

    /**
     * Get the journal ID.
     * @return int
     */
    public function getJournalId() {
        return $this->_journalId;
    }

    /** @var DoiExportPlugin */
    public $_plugin;

    /**
     * Get the plugin.
     * @return DoiExportPlugin
     */
    public function getPlugIn() {
        return $this->_plugin;
    }


    //
    // Constructor
    //
    /**
     * Constructor
     * @param $plugin DoiExportPlugin
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {
        // Configure the object.
        parent::__construct($plugin->getTemplatePath() . 'settings.tpl');
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;

        // Add form validation checks.
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DOIExportSettingsForm($plugin, $journalId) {
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
     * Initialize form data.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        foreach ($this->getFormFields() as $settingName => $settingType) {
            $this->setData($settingName, $this->getSetting($settingName));
        }
    }

    /**
     * Read user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(array_keys($this->getFormFields()));
    }

    /**
     * Save settings.
     * @see Form::execute()
     * @return void
     */
    public function execute($object = null) {
        $plugin = $this->getPlugIn();
        foreach($this->getFormFields() as $settingName => $settingType) {
            $plugin->updateSetting($this->getJournalId(), $settingName, $this->getData($settingName), $settingType);
        }
    }


    //
    // Protected template methods
    //
    /**
     * Get a plugin setting.
     * @param $settingName
     * @return mixed The setting value.
     */
    public function getSetting($settingName) {
        $plugin = $this->getPlugIn();
        $settingValue = $plugin->getSetting($this->getJournalId(), $settingName);
        return $settingValue;
    }

    /**
     * Return a list of form fields.
     * @return array
     */
    public function getFormFields() {
        return [];
    }

    /**
     * Check whether a given setting is optional.
     * @param $settingName string
     * @return boolean
     */
    public function isOptional($settingName) {
        return in_array($settingName, ['username', 'password']);
    }
}
?>
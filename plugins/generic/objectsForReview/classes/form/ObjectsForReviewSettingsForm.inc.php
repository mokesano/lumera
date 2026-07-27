<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/form/ObjectsForReviewSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewSettingsForm
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Form for editors to modify objects for review plugin settings.
 */

import('lib.pkp.classes.form.Form');

class ObjectsForReviewSettingsForm extends Form {

    /** @var GenericPlugin */
    protected $_plugin;

    /** @var int */
    protected $_journalId;

    /** @var array Keys are valid review due weeks values */
    protected $_validDueWeeks;

    /** @var array Keys are valid email reminder days values */
    protected $_validNumDays;

    /**
     * Constructor.
     * @param GenericPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_plugin = $plugin;
        $this->_journalId = (int) $journalId;

        $validModes = [
            OFR_MODE_FULL,
            OFR_MODE_METADATA
        ];

        $this->_validDueWeeks = range(0, 50);
        $this->_validNumDays = range(0, 30);

        parent::__construct($plugin->getTemplatePath() . 'editor/settingsForm.tpl');

        // Management mode provided and valid
        $this->addCheck(new FormValidator($this, 'mode', 'required', 'plugins.generic.objectsForReview.settings.modeRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'mode', 'required', 'plugins.generic.objectsForReview.settings.modeValid', $validModes));
        
        // Check if due weeks are valid
        $this->addCheck(new FormValidatorInSet($this, 'dueWeeks', 'optional', 'plugins.generic.objectsForReview.settings.dueWeeksValid', array_keys($this->_validDueWeeks)));
        
        // If provided, check if the reminder days before and after are valid
        $this->addCheck(new FormValidatorInSet($this, 'numDaysBeforeDueReminder', 'optional', 'plugins.generic.objectsForReview.settings.numDaysReminderValid', array_keys($this->_validNumDays)));
        $this->addCheck(new FormValidatorInSet($this, 'numDaysAfterDueReminder', 'optional', 'plugins.generic.objectsForReview.settings.numDaysReminderValid', array_keys($this->_validNumDays)));
        
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param GenericPlugin $plugin
     * @param int $journalId
     */
    public function ObjectsForReviewSettingsForm($plugin, $journalId) {
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
     * @param mixed $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager($request);
        if (Config::getVar('general', 'scheduled_tasks')) {
            $templateMgr->assign('scheduledTasksEnabled', true);
        }

        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $templateMgr->assign('counts', $ofrAssignmentDao->getStatusCounts($this->_journalId));
        $templateMgr->assign('validDueWeeks', $this->_validDueWeeks);
        $templateMgr->assign('validNumDays', $this->_validNumDays);

        parent::display($request, $template);
    }

    /**
     * Get the list of field names for which the form has locale data.
     * @see Form::getLocaleFieldNames()
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['additionalInformation'];
    }

    /**
     * Initialize form data from current settings.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        $this->_data = [
            'mode' => $this->_plugin->getSetting($this->_journalId, 'mode'),
            'displayAbstract' => $this->_plugin->getSetting($this->_journalId, 'displayAbstract'),
            'displayListing' => $this->_plugin->getSetting($this->_journalId, 'displayListing'),
            'dueWeeks' => $this->_plugin->getSetting($this->_journalId, 'dueWeeks'),
            'enableDueReminderBefore' => $this->_plugin->getSetting($this->_journalId, 'enableDueReminderBefore'),
            'numDaysBeforeDueReminder' => $this->_plugin->getSetting($this->_journalId, 'numDaysBeforeDueReminder'),
            'enableDueReminderAfter' => $this->_plugin->getSetting($this->_journalId, 'enableDueReminderAfter'),
            'numDaysAfterDueReminder' => $this->_plugin->getSetting($this->_journalId, 'numDaysAfterDueReminder'),
            'additionalInformation' => $this->_plugin->getSetting($this->_journalId, 'additionalInformation')
        ];
    }

    /**
     * Assign form data from user input.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'mode',
            'displayAbstract',
            'displayListing',
            'dueWeeks',
            'enableDueReminderBefore',
            'numDaysBeforeDueReminder',
            'enableDueReminderAfter',
            'numDaysAfterDueReminder',
            'additionalInformation',
        ]);
        
        // If full management mode, due weeks provided and valid
        if ($this->_data['mode'] === OFR_MODE_FULL) {
            $this->addCheck(new FormValidator($this, 'dueWeeks', 'required', 'plugins.generic.objectsForReview.settings.dueWeeksRequired'));
            $this->addCheck(new FormValidatorInSet($this, 'dueWeeks', 'required', 'plugins.generic.objectsForReview.settings.dueWeeksValid', array_keys($this->_validDueWeeks)));
        }
    }

    /**
     * Save the settings.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $this->_plugin->updateSetting($this->_journalId, 'mode', (int) $this->getData('mode'), 'int');
        $this->_plugin->updateSetting($this->_journalId, 'displayAbstract', (bool) $this->getData('displayAbstract'), 'bool');
        $this->_plugin->updateSetting($this->_journalId, 'displayListing', (bool) $this->getData('displayListing'), 'bool');
        $this->_plugin->updateSetting($this->_journalId, 'dueWeeks', (int) $this->getData('dueWeeks'), 'int');
        $this->_plugin->updateSetting($this->_journalId, 'enableDueReminderBefore', (bool) $this->getData('enableDueReminderBefore'), 'bool');
        $this->_plugin->updateSetting($this->_journalId, 'numDaysBeforeDueReminder', (int) $this->getData('numDaysBeforeDueReminder'), 'int');
        $this->_plugin->updateSetting($this->_journalId, 'enableDueReminderAfter', (bool) $this->getData('enableDueReminderAfter'), 'bool');
        $this->_plugin->updateSetting($this->_journalId, 'numDaysAfterDueReminder', (int) $this->getData('numDaysAfterDueReminder'), 'int');
        $this->_plugin->updateSetting($this->_journalId, 'additionalInformation', $this->getData('additionalInformation'), 'object'); // Localized
    }
    
}
?>
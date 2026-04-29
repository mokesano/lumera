<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/classes/BooksForReviewSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BooksForReviewSettingsForm
 * @ingroup plugins_generic_booksForReview
 *
 * @brief Form for editors to modify books for review plugin settings
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

import('lib.pkp.classes.form.Form');

class BooksForReviewSettingsForm extends Form {

    /** @var int */
    public $journalId;

    /** @var object */
    public $plugin;

    /** @var array keys are valid management mode values */
    public $validModes;

    /** @var array keys are valid review due weeks values */
    public $validDueWeeks;

    /** @var array keys are valid email reminder days values */
    public $validNumDays;

    /**
     * Constructor
     * @param $plugin object
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {
        $this->journalId = $journalId;
        $this->plugin = $plugin;

        $this->validModes = array(
            BFR_MODE_FULL,
            BFR_MODE_METADATA
        );

        $this->validDueWeeks = range(0, 50);
        $this->validNumDays = range(0, 30);

        // [MODERNISASI] Parent Constructor
        parent::__construct($plugin->getTemplatePath() . 'editor' . '/' . 'settingsForm.tpl');

        // Management mode provided and valid
        $this->addCheck(new FormValidator($this, 'mode', 'required', 'plugins.generic.booksForReview.settings.modeRequired'));
        $this->addCheck(new FormValidatorInSet($this, 'mode', 'required', 'plugins.generic.booksForReview.settings.modeValid', $this->validModes));

        // If provided reminder days before value is valid value
        $this->addCheck(new FormValidatorInSet($this, 'numDaysBeforeDueReminder', 'optional', 'plugins.generic.booksForReview.settings.numDaysBeforeDueReminderValid', array_keys($this->validNumDays)));

        // If provided reminder days after value is valid value
        $this->addCheck(new FormValidatorInSet($this, 'numDaysAfterDueReminder', 'optional', 'plugins.generic.booksForReview.settings.numDaysAfterDueReminderValid', array_keys($this->validNumDays)));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BooksForReviewSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::BooksForReviewSettingsForm(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($plugin, $journalId);
    }

    /**
     * Display the form.
     */
    public function display() {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('validDueWeeks', $this->validDueWeeks);
        $templateMgr->assign('validNumDays', $this->validNumDays);
        parent::display();
    }

    /**
     * Get the names of the fields for which localized settings are used
     * @return array
     */
    public function getLocaleFieldNames() {
        return array('additionalInformation');
    }

    /**
     * Initialize form data.
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $this->_data = array(
            'mode' => $plugin->getSetting($journalId, 'mode'),
            'coverPageIssue' => $plugin->getSetting($journalId, 'coverPageIssue'),
            'coverPageAbstract' => $plugin->getSetting($journalId, 'coverPageAbstract'),
            'dueWeeks' => $plugin->getSetting($journalId, 'dueWeeks'),
            'enableDueReminderBefore' => $plugin->getSetting($journalId, 'enableDueReminderBefore'),
            'numDaysBeforeDueReminder' => $plugin->getSetting($journalId, 'numDaysBeforeDueReminder'),
            'enableDueReminderAfter' => $plugin->getSetting($journalId, 'enableDueReminderAfter'),
            'numDaysAfterDueReminder' => $plugin->getSetting($journalId, 'numDaysAfterDueReminder'),
            'additionalInformation' => $plugin->getSetting($journalId, 'additionalInformation')
        );
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            array(
                'mode',
                'coverPageIssue',
                'coverPageAbstract',
                'dueWeeks',
                'enableDueReminderBefore',
                'numDaysBeforeDueReminder',
                'enableDueReminderAfter',
                'numDaysAfterDueReminder',
                'additionalInformation',
            )
        );

        // If full management mode, book review due weeks provided and valid
        if ($this->_data['mode'] == BFR_MODE_FULL) {
            $this->addCheck(new FormValidator($this, 'dueWeeks', 'required', 'plugins.generic.booksForReview.settings.dueWeeksRequired'));
            $this->addCheck(new FormValidatorInSet($this, 'dueWeeks', 'required', 'plugins.generic.booksForReview.settings.dueWeeksValid', array_keys($this->validDueWeeks)));
        }
    }

    /**
     * Save settings. 
     */
    public function execute($object = null) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;

        $plugin->updateSetting($journalId, 'mode', $this->getData('mode'), 'int');
        $plugin->updateSetting($journalId, 'coverPageIssue', $this->getData('coverPageIssue'), 'bool');
        $plugin->updateSetting($journalId, 'coverPageAbstract', $this->getData('coverPageAbstract'), 'bool');
        $plugin->updateSetting($journalId, 'dueWeeks', $this->getData('dueWeeks'), 'int');
        $plugin->updateSetting($journalId, 'enableDueReminderBefore', $this->getData('enableDueReminderBefore'), 'bool');
        $plugin->updateSetting($journalId, 'numDaysBeforeDueReminder', $this->getData('numDaysBeforeDueReminder'), 'int');
        $plugin->updateSetting($journalId, 'enableDueReminderAfter', $this->getData('enableDueReminderAfter'), 'bool');
        $plugin->updateSetting($journalId, 'numDaysAfterDueReminder', $this->getData('numDaysAfterDueReminder'), 'int');
        $plugin->updateSetting($journalId, 'additionalInformation', $this->getData('additionalInformation'), 'object'); // Localized
    }

}

?>
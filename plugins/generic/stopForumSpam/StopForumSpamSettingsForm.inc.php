<?php
declare(strict_types=1);

/**
 * @file plugins/generic/stopForumSpam/StopForumSpamSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StopForumSpamSettingsForm
 * @ingroup plugins_generic_stopForumSpam
 *
 * @brief Form for journal managers to modify the Stop Forum Spam plugin settings
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.form.Form');

class StopForumSpamSettingsForm extends Form {

    /** @var int */
    public $journalId;

    /** @var object */
    public $plugin;

    /**
     * Constructor
     * @param object $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->journalId = $journalId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function StopForumSpamSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::StopForumSpamSettingsForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $this->_data = [
            'checkIp' => $plugin->getSetting($journalId, 'checkIp'),
            'checkEmail' => $plugin->getSetting($journalId, 'checkEmail'),
            'checkUsername' => $plugin->getSetting($journalId, 'checkUsername'),
        ];
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(['checkIp', 'checkEmail', 'checkUsername']);
    }

    /**
     * Save settings.
     * @see Form::execute()
     */
    public function execute($object = null) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;

        $checkIp = $this->getData('checkIp') ? true : false;
        $checkEmail = $this->getData('checkEmail') ? true : false;
        $checkUsername = $this->getData('checkUsername') ? true : false;

        $plugin->updateSetting($journalId, 'checkIp', $checkIp, 'bool');
        $plugin->updateSetting($journalId, 'checkEmail', $checkEmail, 'bool');
        $plugin->updateSetting($journalId, 'checkUsername', $checkUsername, 'bool');
    }
}
?>
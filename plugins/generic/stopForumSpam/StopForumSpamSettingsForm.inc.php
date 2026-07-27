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
 * @brief Form for journal managers to modify the Stop Forum Spam plugin settings.
 */

import('lib.pkp.classes.form.Form');

class StopForumSpamSettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var StopForumSpamPlugin */
    protected $_plugin;

    /**
     * Constructor
     * @param StopForumSpamPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * [SHIM] Backward Compatibility
     * @param StopForumSpamPlugin $plugin
     * @param int $journalId
     */
    public function StopForumSpamSettingsForm($plugin, $journalId) {
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
     * Initialize form data.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        $this->_data = [
            'checkIp' => $this->_plugin->getSetting($this->_journalId, 'checkIp'),
            'checkEmail' => $this->_plugin->getSetting($this->_journalId, 'checkEmail'),
            'checkUsername' => $this->_plugin->getSetting($this->_journalId, 'checkUsername'),
        ];
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['checkIp', 'checkEmail', 'checkUsername']);
    }

    /**
     * Save settings.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $checkIp = (bool) $this->getData('checkIp');
        $checkEmail = (bool) $this->getData('checkEmail');
        $checkUsername = (bool) $this->getData('checkUsername');

        $this->_plugin->updateSetting($this->_journalId, 'checkIp', $checkIp, 'bool');
        $this->_plugin->updateSetting($this->_journalId, 'checkEmail', $checkEmail, 'bool');
        $this->_plugin->updateSetting($this->_journalId, 'checkUsername', $checkUsername, 'bool');
    }

}
?>
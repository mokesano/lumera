<?php
declare(strict_types=1);

/**
 * @file plugins/generic/referral/ReferralPluginSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReferralPluginSettingsForm
 * @ingroup plugins_generic_referral
 *
 * @brief Form for journal managers to modify referral plugin settings.
 */

import('lib.pkp.classes.form.Form');

class ReferralPluginSettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var ReferralPlugin */
    protected $_plugin;

    /**
     * Constructor.
     * @param ReferralPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param ReferralPlugin $plugin
     * @param int $journalId
     */
    public function ReferralPluginSettingsForm($plugin, $journalId) {
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
            'exclusions' => $this->_plugin->getSetting($this->_journalId, 'exclusions')
        ];
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['exclusions']);
    }

    /**
     * Save settings.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $exclusions = (string) ($this->getData('exclusions') ?? '');
        $this->_plugin->updateSetting($this->_journalId, 'exclusions', trim($exclusions), 'string');
    }

}
?>
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
 * @brief Form for journal managers to modify referral plugin settings
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.form.Form');

class ReferralPluginSettingsForm extends Form {

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
    public function ReferralPluginSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::ReferralPluginSettingsForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Initialize form data.
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $this->_data = [
            'exclusions' => $plugin->getSetting($journalId, 'exclusions')
        ];
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['exclusions']);
    }

    /**
     * Save settings. 
     */
    public function execute($object = null) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;

        // [PHP 8 FIX] Handle null data before trim
        $plugin->updateSetting($journalId, 'exclusions', trim($this->getData('exclusions') ?? ''), 'string');
    }
}

?>
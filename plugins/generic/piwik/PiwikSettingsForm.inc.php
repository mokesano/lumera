<?php
declare(strict_types=1);

/**
 * @file plugins/generic/piwik/PiwikSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PiwikSettingsForm
 * @ingroup plugins_generic_piwik
 *
 * @brief Form for journal managers to modify piwik plugin settings
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('lib.pkp.classes.form.Form');

class PiwikSettingsForm extends Form {

    /** @var int */
    protected $journalId;

    /** @var object */
    protected $plugin;

    /**
     * Constructor
     * @param object $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->journalId = $journalId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

        $this->addCheck(new FormValidatorUrl($this, 'piwikUrl', 'required', 'plugins.generic.piwik.manager.settings.piwikUrlRequired'));
        $this->addCheck(new FormValidator($this, 'piwikSiteId', 'required', 'plugins.generic.piwik.manager.settings.piwikSiteIdRequired'));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PiwikSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::PiwikSettingsForm(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
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
            'piwikUrl' => $plugin->getSetting($journalId, 'piwikUrl'),
            'piwikSiteId' => $plugin->getSetting($journalId, 'piwikSiteId')
        ];
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['piwikUrl', 'piwikSiteId']);
    }

    /**
     * Save settings.
     */
    public function execute($object = null) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;

        $plugin->updateSetting($journalId, 'piwikUrl', rtrim($this->getData('piwikUrl') ?? '', "/"), 'string');
        $plugin->updateSetting($journalId, 'piwikSiteId', $this->getData('piwikSiteId'), 'int');
    }
}

?>
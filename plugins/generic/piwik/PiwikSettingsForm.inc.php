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
 * @brief Form for journal managers to modify piwik plugin settings.
 */

import('lib.pkp.classes.form.Form');

class PiwikSettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var PiwikPlugin */
    protected $_plugin;

    /**
     * Constructor.
     * @param PiwikPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

        $this->addCheck(new FormValidatorUrl($this, 'piwikUrl', 'required', 'plugins.generic.piwik.manager.settings.piwikUrlRequired'));
        $this->addCheck(new FormValidator($this, 'piwikSiteId', 'required', 'plugins.generic.piwik.manager.settings.piwikSiteIdRequired'));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param PiwikPlugin $plugin
     * @param int $journalId
     */
    public function PiwikSettingsForm($plugin, $journalId) {
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
            'piwikUrl' => $this->_plugin->getSetting($this->_journalId, 'piwikUrl'),
            'piwikSiteId' => $this->_plugin->getSetting($this->_journalId, 'piwikSiteId')
        ];
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['piwikUrl', 'piwikSiteId']);
    }

    /**
     * Save settings.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $piwikUrl = (string) ($this->getData('piwikUrl') ?? '');
        $piwikSiteId = (string) ($this->getData('piwikSiteId') ?? '');

        $this->_plugin->updateSetting($this->_journalId, 'piwikUrl', rtrim($piwikUrl, '/'), 'string');
        $this->_plugin->updateSetting($this->_journalId, 'piwikSiteId', (int) $piwikSiteId, 'int');
    }

}
?>
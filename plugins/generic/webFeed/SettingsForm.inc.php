<?php
declare(strict_types=1);

/**
 * @file plugins/generic/webFeed/SettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsForm
 * @ingroup plugins_generic_webFeed
 *
 * @brief Form for journal managers to modify web feeds plugin settings.
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var WebFeedPlugin */
    protected $_plugin;

    /**
     * Constructor
     * @param WebFeedPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param WebFeedPlugin $plugin
     * @param int $journalId
     */
    public function SettingsForm($plugin, $journalId) {
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
     * @return void
     */
    public function initData() {
        $this->setData('displayPage', $this->_plugin->getSetting($this->_journalId, 'displayPage'));
        $this->setData('displayItems', $this->_plugin->getSetting($this->_journalId, 'displayItems'));
        $this->setData('recentItems', $this->_plugin->getSetting($this->_journalId, 'recentItems'));
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['displayPage', 'displayItems', 'recentItems']);

        // Check that recent items value is a positive integer
        $recentItems = (int) $this->getData('recentItems');
        if ($recentItems <= 0) {
            $this->setData('recentItems', '');
        }

        // If recent items is selected, check that we have a value
        if ($this->getData('displayItems') === 'recent') {
            $this->addCheck(new FormValidator($this, 'recentItems', 'required', 'plugins.generic.webfeed.settings.recentItemsRequired'));
        }
    }

    /**
     * Save settings.
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $this->_plugin->updateSetting($this->_journalId, 'displayPage', $this->getData('displayPage'));
        $this->_plugin->updateSetting($this->_journalId, 'displayItems', $this->getData('displayItems'));
        $this->_plugin->updateSetting($this->_journalId, 'recentItems', $this->getData('recentItems'));
    }

}
?>
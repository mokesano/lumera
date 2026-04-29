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
 * @brief Form for journal managers to modify web feeds plugin settings
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {

    /** @var int */
    public $journalId;

    /** @var object */
    public $plugin;

    /**
     * Constructor
     * @param $plugin object
     * @param $journalId int
     */
    public function __construct($plugin, $journalId) {
        $this->journalId = (int) $journalId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Initialize form data.
     * @return void
     */
    public function initData() {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $this->setData('displayPage', $plugin->getSetting($journalId, 'displayPage'));
        $this->setData('displayItems', $plugin->getSetting($journalId, 'displayItems'));
        $this->setData('recentItems', $plugin->getSetting($journalId, 'recentItems'));
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['displayPage', 'displayItems', 'recentItems']);

        // check that recent items value is a positive integer
        if ((int) $this->getData('recentItems') <= 0) $this->setData('recentItems', '');

        // if recent items is selected, check that we have a value
        if ($this->getData('displayItems') == "recent") {
            $this->addCheck(new FormValidator($this, 'recentItems', 'required', 'plugins.generic.webfeed.settings.recentItemsRequired'));
        }
    }

    /**
     * Save settings.
     * @return void
     */
    public function execute($object = NULL) {
        $plugin = $this->plugin;
        $journalId = $this->journalId;

        $plugin->updateSetting($journalId, 'displayPage', $this->getData('displayPage'));
        $plugin->updateSetting($journalId, 'displayItems', $this->getData('displayItems'));
        $plugin->updateSetting($journalId, 'recentItems', $this->getData('recentItems'));
    }

}

?>
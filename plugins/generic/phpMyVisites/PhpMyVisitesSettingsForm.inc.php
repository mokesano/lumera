<?php
declare(strict_types=1);

/**
 * @file plugins/generic/phpMyVisites/PhpMyVisitesSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PhpMyVisitesSettingsForm
 * @ingroup plugins_generic_phpMyVisites
 *
 * @brief Form for journal managers to modify phpMyVisites plugin settings.
 */

import('lib.pkp.classes.form.Form');

class PhpMyVisitesSettingsForm extends Form {

    /** @var int */
    protected $_journalId;

    /** @var PhpMyVisitesPlugin */
    protected $_plugin;

    /**
     * Constructor.
     * @param PhpMyVisitesPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->_journalId = (int) $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

        $this->addCheck(new FormValidatorUrl($this, 'phpmvUrl', 'required', 'plugins.generic.phpmv.manager.settings.phpmvUrlRequired'));
        $this->addCheck(new FormValidator($this, 'phpmvSiteId', 'required', 'plugins.generic.phpmv.manager.settings.phpmvSiteIdRequired'));
    }

    /**
     * Initialize form data.
     * @see Form::initData()
     * @return void
     */
    public function initData() {
        $this->_data = [
            'phpmvUrl' => $this->_plugin->getSetting($this->_journalId, 'phpmvUrl'),
            'phpmvSiteId' => $this->_plugin->getSetting($this->_journalId, 'phpmvSiteId')
        ];
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['phpmvUrl', 'phpmvSiteId']);
    }

    /**
     * Save settings.
     * @see Form::execute()
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null) {
        $phpmvUrl = (string) ($this->getData('phpmvUrl') ?? '');
        $phpmvSiteId = (string) ($this->getData('phpmvSiteId') ?? '');

        $this->_plugin->updateSetting($this->_journalId, 'phpmvUrl', rtrim($phpmvUrl, '/'), 'string');
        $this->_plugin->updateSetting($this->_journalId, 'phpmvSiteId', (int) $phpmvSiteId, 'int');
    }

}
?>
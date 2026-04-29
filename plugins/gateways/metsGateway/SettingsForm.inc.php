<?php
declare(strict_types=1);

/**
 * @file plugins/gateways/metsGateway/SettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsForm
 * @ingroup plugins_gateways_metsGateway
 *
 * @brief Form for METS gateway plugin settings
 */

import('lib.pkp.classes.form.Form');

class SettingsForm extends Form {

    /** @var int */
    public $journalId;

    /** @var METSGatewayPlugin */
    public $plugin;

    /**
     * Constructor
     * @param METSGatewayPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, int $journalId) {
        $this->journalId = $journalId;
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
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Initialize form data.
     * @return void
     */
    public function initData(): void {
        $journalId = $this->journalId;
        $plugin = $this->plugin;

        $organization = $plugin->getSetting($journalId, 'organization');
        if (empty($organization)) {
            $siteDao = DAORegistry::getDAO('SiteDAO');
            $site = $siteDao->getSite();
            $organization = $site->getLocalizedTitle();
        }
        $this->setData('organization', $organization);

        $this->setData('contentWrapper', $plugin->getSetting($journalId, 'contentWrapper') ?: 'FLocat');
        $this->setData('preservationLevel', $plugin->getSetting($journalId, 'preservationLevel') ?: '1');
        $this->setData('exportSuppFiles', $plugin->getSetting($journalId, 'exportSuppFiles'));
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData(): void {
        $this->readUserVars(['contentWrapper', 'organization', 'preservationLevel', 'exportSuppFiles']);
    }

    /**
     * Save settings.
     * @param null|object $object Ignored.
     * @return void
     */
    public function execute($object = NULL): void {
        $plugin = $this->plugin;
        $journalId = $this->journalId;

        $plugin->updateSetting($journalId, 'contentWrapper', $this->getData('contentWrapper'));
        $plugin->updateSetting($journalId, 'organization', $this->getData('organization'));
        $plugin->updateSetting($journalId, 'preservationLevel', $this->getData('preservationLevel'));
        $plugin->updateSetting($journalId, 'exportSuppFiles', $this->getData('exportSuppFiles'));
    }
}

?>
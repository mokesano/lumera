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
    protected int $_journalId;

    /** @var METSGatewayPlugin */
    protected $_plugin;

    /**
     * Constructor
     * @param METSGatewayPlugin $plugin
     * @param int $journalId
     */
    public function __construct($plugin, int $journalId) {
        $this->_journalId = $journalId;
        $this->_plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param METSGatewayPlugin $plugin
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
    public function initData(): void {
        $journalId = $this->_journalId;
        $plugin = $this->_plugin;

        $organization = $plugin->getSetting($journalId, 'organization');
        if (empty($organization)) {
            /** @var SiteDAO $siteDao */
            $siteDao = DAORegistry::getDAO('SiteDAO');
            $site = $siteDao->getSite();
            $organization = $site ? (string) $site->getLocalizedTitle() : '';
        }
        $this->setData('organization', $organization);

        $contentWrapper = $plugin->getSetting($journalId, 'contentWrapper');
        $this->setData('contentWrapper', $contentWrapper !== null && $contentWrapper !== '' ? $contentWrapper : 'FLocat');
        
        $preservationLevel = $plugin->getSetting($journalId, 'preservationLevel');
        $this->setData('preservationLevel', $preservationLevel !== null && $preservationLevel !== '' ? $preservationLevel : '1');
        
        $this->setData('exportSuppFiles', (bool) $plugin->getSetting($journalId, 'exportSuppFiles'));
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
     * @param mixed $object Ignored.
     * @return void
     */
    public function execute($object = null): void {
        $plugin = $this->_plugin;
        $journalId = $this->_journalId;

        $plugin->updateSetting($journalId, 'contentWrapper', $this->getData('contentWrapper') ?? 'FLocat');
        $plugin->updateSetting($journalId, 'organization', $this->getData('organization'));
        $plugin->updateSetting($journalId, 'preservationLevel', $this->getData('preservationLevel') ?? '1');
        $plugin->updateSetting($journalId, 'exportSuppFiles', (bool) $this->getData('exportSuppFiles'));
    }

}
?>
<?php
declare(strict_types=1);

/**
 * @file plugins/generic/googleAnalytics/GoogleAnalyticsSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GoogleAnalyticsSettingsForm
 * @ingroup plugins_generic_googleAnalytics
 *
 * @brief Form for journal managers to modify Google Analytics plugin settings
 */

define('GOOGLE_ANALYTICS_SITE_ENABLE', 1);
define('GOOGLE_ANALYTICS_SITE_DISABLE', -1);
define('GOOGLE_ANALYTICS_SITE_UNCHANGED', 0);

import('lib.pkp.classes.form.Form');

class GoogleAnalyticsSettingsForm extends Form {

    /** @var int */
    public $journalId;

    /** @var GoogleAnalyticsPlugin */
    public $plugin;

    /**
     * Constructor
     * @param GoogleAnalyticsPlugin $plugin 
     * @param int $journalId
     */
    public function __construct($plugin, $journalId) {
        $this->journalId = $journalId;
        $this->plugin = $plugin;

        parent::__construct($plugin->getTemplatePath() . 'settingsForm.tpl');

        $this->addCheck(new FormValidator($this, 'googleAnalyticsSiteId', 'required', 'plugins.generic.googleAnalytics.manager.settings.googleAnalyticsSiteIdRequired'));
        $this->addCheck(new FormValidator($this, 'trackingCode', 'required', 'plugins.generic.googleAnalytics.manager.settings.trackingCodeRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     * @param GoogleAnalyticsPlugin $plugin 
     * @param int $journalId
     */
    public function GoogleAnalyticsSettingsForm($plugin, $journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::GoogleAnalyticsSettingsForm(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($plugin, $journalId);
    }

    /**
     * Display the form.
     * @see Form::display()
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        if (Validation::isSiteAdmin()) {
            /** @var GoogleAnalyticsPlugin $plugin */
            $plugin = $this->plugin;
            
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('siteAdmin', true);
            
            if ($plugin->getSetting(CONTEXT_ID_NONE, 'enabled')) {
                $templateMgr->assign('siteEnabled', true);
                $templateMgr->assign('siteTrackingCode', $plugin->getSetting(CONTEXT_ID_NONE, 'trackingCode'));
                $templateMgr->assign('siteGoogleAnalyticsSiteId', $plugin->getSetting(CONTEXT_ID_NONE, 'googleAnalyticsSiteId'));
            } else {
                $templateMgr->assign('siteEnabled', false);
                $templateMgr->assign('siteTrackingCode', __('plugins.generic.googleAnalytics.manager.settings.disabled'));
                $templateMgr->assign('siteGoogleAnalyticsSiteId', __('plugins.generic.googleAnalytics.manager.settings.disabled'));
            }
        }
        parent::display($request, $template);
    }

    /**
     * Initialize form data.
     */
    public function initData() {
        $journalId = $this->journalId;
        /** @var GoogleAnalyticsPlugin $plugin */
        $plugin = $this->plugin;

        $this->_data = [
            'googleAnalyticsSiteId' => $plugin->getSetting($journalId, 'googleAnalyticsSiteId'),
            'trackingCode' => $plugin->getSetting($journalId, 'trackingCode')
        ];
        
        if (Validation::isSiteAdmin()) {
            $this->_data['enableSite'] = GOOGLE_ANALYTICS_SITE_UNCHANGED;
        }
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $vars = ['googleAnalyticsSiteId', 'trackingCode'];
        if (Validation::isSiteAdmin()) {
            $vars[] = 'enableSite';
        }
        $this->readUserVars($vars);
    }

    /**
     * Save settings.
     * @param mixed $object (optional) Unused.
     */
    public function execute($object = null) {
        /** @var GoogleAnalyticsPlugin $plugin */
        $plugin = $this->plugin;
        $journalId = $this->journalId;

        $plugin->updateSetting(
            $journalId, 
            'googleAnalyticsSiteId', 
            trim((string) $this->getData('googleAnalyticsSiteId'), "\"\';"), 
            'string'
        );

        $trackingCode = (string) $this->getData('trackingCode');
        if ($trackingCode !== 'urchin' && $trackingCode !== 'ga' && $trackingCode !== 'analytics') {
            $trackingCode = 'urchin';
        }
        $plugin->updateSetting($journalId, 'trackingCode', $trackingCode, 'string');
        
        if (Validation::isSiteAdmin()) {
            // Enable this code on the site level
            if ($this->getData('enableSite')) {
                $isEnabled = ($this->getData('enableSite') == GOOGLE_ANALYTICS_SITE_ENABLE);
                
                $plugin->updateSetting(CONTEXT_ID_NONE, 'enabled', $isEnabled, 'bool');
                $plugin->updateSetting(CONTEXT_ID_NONE, 'trackingCode', $isEnabled ? $trackingCode : '', 'string');
                $plugin->updateSetting(
                    CONTEXT_ID_NONE, 
                    'googleAnalyticsSiteId', 
                    $isEnabled ? trim((string) $this->getData('googleAnalyticsSiteId'), "\"\';") : '', 
                    'string'
                );
            }
        }
    }

}
?>
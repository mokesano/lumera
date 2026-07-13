<?php
declare(strict_types=1);

/**
 * @file classes/admin/form/AboutSiteForm.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class AboutSiteForm
 * @ingroup admin_form
 * 
 * @brief Form to manage static "About Site" settings (Mission, History, Leadership, Awards).
 */

import('lib.pkp.classes.form.Form');

class AboutSiteForm extends Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('admin/aboutSite.tpl');
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AboutSiteForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . ". Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Initialize form data from site settings.
     */
    public function initData() {
        $request = Application::get()->getRequest();
        $site = $request->getSite();
        
        if ($site) {
            $this->setData('publisherMission', $site->getSetting('publisherMission'));
            $this->setData('publisherHistory', $site->getSetting('publisherHistory'));
            $this->setData('publisherLeaderships', $site->getSetting('publisherLeaderships'));
            $this->setData('publisherAwards', $site->getSetting('publisherAwards'));
        }
    }

    /**
     * Read user input.
     */
    public function readInputData() {
        $this->readUserVars([
            'publisherMission', 
            'publisherHistory', 
            'publisherLeaderships', 
            'publisherAwards'
        ]);
    }
    
    /**
     * Validate the form.
     * 
     * @param bool $callHooks
     * @return bool
     */
    public function validate($callHooks = true) {
        return parent::validate($callHooks);
    }

    /**
     * Save settings.
     * 
     * @param mixed $object
     * @return bool
     */
    public function execute($object = null) {
        /** @var SiteSettingsDAO $siteSettingsDao */
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        
        // [WIZDAM] Null-safety check
        if (!$siteSettingsDao) {
            return false;
        }

        $siteSettingsDao->updateSetting('publisherMission', $this->getData('publisherMission'), 'string', true);
        $siteSettingsDao->updateSetting('publisherHistory', $this->getData('publisherHistory'), 'string', true);
        $siteSettingsDao->updateSetting('publisherLeaderships', $this->getData('publisherLeaderships'), 'string', true);
        $siteSettingsDao->updateSetting('publisherAwards', $this->getData('publisherAwards'), 'string', true);
        
        return true;
    }

    /**
     * Display the form.
     * 
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback dengan strict type check
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        
        // [WIZDAM] Micro-Payload
        $templateMgr->assign([
            'pageTitle' => 'admin.aboutSiteSettings',
            'publisherMissionKey' => 'admin.siteSettings.publisherMission',
            'publisherHistoryKey' => 'admin.siteSettings.publisherHistory',
            'publisherLeadershipsKey' => 'admin.siteSettings.publisherLeaderships',
            'publisherAwardsKey' => 'admin.siteSettings.publisherAwards',
            
            // Data untuk template (berupa array untuk field multibahasa)
            'publisherMission' => $this->getData('publisherMission'),
            'publisherHistory' => $this->getData('publisherHistory'),
            'publisherLeaderships' => $this->getData('publisherLeaderships'),
            'publisherAwards' => $this->getData('publisherAwards'),
        ]);
        
        parent::display($request, $template);
    }

}
?>
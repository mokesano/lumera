<?php
declare(strict_types=1);

/**
 * @file classes/admin/form/AboutSiteForm.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class AboutSiteForm
 * @ingroup admin_form
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
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
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
            // [FIX] Gunakan ?? [] agar default selalu array, mencegah error "null" di PHP 8
            $this->setData('publisherMission', $site->getSetting('publisherMission') ?? []);
            $this->setData('publisherHistory', $site->getSetting('publisherHistory') ?? []);
            $this->setData('publisherLeaderships', $site->getSetting('publisherLeaderships') ?? []);
            $this->setData('publisherAwards', $site->getSetting('publisherAwards') ?? []);
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
     * [FIX] Ubah parameter tipe dari 'string' menjadi null untuk field multibahasa (array)
     * 
     * @param mixed $functionArgs
     * @return bool
     */
    public function execute(...$functionArgs) {
        /** @var SiteSettingsDAO $siteSettingsDao */
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        
        if (!$siteSettingsDao) {
            return false;
        }

        // [FIX] Parameter ke-3 harus 'null' (bukan 'string') agar DAO menangani array multibahasa dengan benar
        $siteSettingsDao->updateSetting('publisherMission', $this->getData('publisherMission'), null, true);
        $siteSettingsDao->updateSetting('publisherHistory', $this->getData('publisherHistory'), null, true);
        $siteSettingsDao->updateSetting('publisherLeaderships', $this->getData('publisherLeaderships'), null, true);
        $siteSettingsDao->updateSetting('publisherAwards', $this->getData('publisherAwards'), null, true);
        
        return true;
    }

    /**
     * Display the form.
     * 
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        
        $templateMgr->assign([
            'pageTitle' => 'admin.aboutSiteSettings',
            'publisherMissionKey' => 'admin.siteSettings.publisherMission',
            'publisherHistoryKey' => 'admin.siteSettings.publisherHistory',
            'publisherLeadershipsKey' => 'admin.siteSettings.publisherLeaderships',
            'publisherAwardsKey' => 'admin.siteSettings.publisherAwards',
            
            // [FIX] Pastikan data yang dikirim ke template selalu array, tidak pernah null
            'publisherMission' => $this->getData('publisherMission') ?? [],
            'publisherHistory' => $this->getData('publisherHistory') ?? [],
            'publisherLeaderships' => $this->getData('publisherLeaderships') ?? [],
            'publisherAwards' => $this->getData('publisherAwards') ?? [],
        ]);
        
        parent::display($request, $template);
    }

}
?>
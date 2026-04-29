<?php
declare(strict_types=1);

/**
 * @file classes/admin/form/AboutSiteForm.inc.php
 *
 * @class AboutSiteForm
 * @ingroup admin_form
 * @brief Form to manage static "About Site" settings (Mission, History, Leadership, Awards).
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.Form');

class AboutSiteForm extends Form {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('admin/aboutSite.tpl');
        
        // [WIZDAM] Ensure form checks for POST data validity
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AboutSiteForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
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
        
        // [WIZDAM] Use getSetting() instead of getLocalizedSetting() 
        // to ensure we load data for all locales, not just the current one.
        $this->_data = [
            'publisherMission' => $site->getSetting('publisherMission'),
            'publisherHistory' => $site->getSetting('publisherHistory'),
            'publisherLeaderships' => $site->getSetting('publisherLeaderships'),
            'publisherAwards' => $site->getSetting('publisherAwards'),
        ];
    }

    /**
     * Read user input.
     */
    public function readInputData() {
        // [WIZDAM] Explicitly list variables to read. 
        // This handles both string and array (multilingual) values correctly.
        $this->readUserVars([
            'publisherMission', 
            'publisherHistory', 
            'publisherLeaderships', 
            'publisherAwards'
        ]);
    }
    
    /**
     * Validate the form.
     * @param bool $callHooks
     * @return bool
     */
    public function validate($callHooks = true) {
        return parent::validate($callHooks);
    }

    /**
     * Save settings.
     * @param object|null $object
     * @return bool
     */
    public function execute($object = null) {
        $request = Application::get()->getRequest();
        $site = $request->getSite();
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');

        // [WIZDAM] The 'string' type in updateSetting handles localized arrays automatically 
        // if the input data is an array (which readUserVars ensures for localized fields).
        $siteSettingsDao->updateSetting($site->getId(), 'publisherMission', $this->getData('publisherMission'), 'string', true);
        $siteSettingsDao->updateSetting($site->getId(), 'publisherHistory', $this->getData('publisherHistory'), 'string', true);
        $siteSettingsDao->updateSetting($site->getId(), 'publisherLeaderships', $this->getData('publisherLeaderships'), 'string', true);
        $siteSettingsDao->updateSetting($site->getId(), 'publisherAwards', $this->getData('publisherAwards'), 'string', true);
        
        return true;
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageTitle', 'admin.aboutSiteSettings');
        
        // Assign translation keys for labels
        $templateMgr->assign('publisherMissionKey', 'admin.siteSettings.publisherMission');
        $templateMgr->assign('publisherHistoryKey', 'admin.siteSettings.publisherHistory');
        $templateMgr->assign('publisherLeadershipsKey', 'admin.siteSettings.publisherLeaderships');
        $templateMgr->assign('publisherAwardsKey', 'admin.siteSettings.publisherAwards');
        
        // Pass data to template
        // Note: _data contains arrays for localized fields, which the template engine expects.
        $templateMgr->assign('publisherMission', $this->getData('publisherMission'));
        $templateMgr->assign('publisherHistory', $this->getData('publisherHistory'));
        $templateMgr->assign('publisherLeaderships', $this->getData('publisherLeaderships'));
        $templateMgr->assign('publisherAwards', $this->getData('publisherAwards'));
        
        parent::display($request, $template);
    }
}

?>
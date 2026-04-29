<?php
declare(strict_types=1);

/**
 * @file classes/admin/form/SiteSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteSettingsForm
 * @ingroup admin_form
 * @see PKPSiteSettingsForm
 *
 * @brief Form to edit site settings.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.admin.form.PKPSiteSettingsForm');

class SiteSettingsForm extends PKPSiteSettingsForm {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SiteSettingsForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournalTitles();
        $templateMgr = TemplateManager::getManager($request);

        $allThemes = PluginRegistry::loadCategory('themes');
        $themes = [];
        
        // [WIZDAM] Simplified iteration
        if (!empty($allThemes)) {
            foreach ($allThemes as $plugin) {
                $themes[basename($plugin->getPluginPath())] = $plugin;
            }
        }
        
        $templateMgr->assign('themes', $themes);
        $templateMgr->assign('redirectOptions', $journals);

        $application = Application::get();
        $templateMgr->assign('availableMetricTypes', $application->getMetricTypes(true));

        return parent::display($request, $template);
    }

    /**
     * Initialize the form from the current settings.
     */
    public function initData() {
        parent::initData();

        $siteDao = DAORegistry::getDAO('SiteDAO');
        $site = $siteDao->getSite();

        $this->_data['useAlphalist'] = $site->getSetting('useAlphalist');
        $this->_data['usePaging'] = $site->getSetting('usePaging');
        $this->_data['defaultMetricType'] = $site->getSetting('defaultMetricType');
        $this->_data['preventManagerPluginManagement'] = $site->getSetting('preventManagerPluginManagement');
    }

    /**
     * Assign user-submitted data to form.
     * @param bool $callHooks
     */
    public function readInputData($callHooks = true) {
        $this->readUserVars(['useAlphalist', 'usePaging', 'defaultMetricType', 'preventManagerPluginManagement']);
        return parent::readInputData($callHooks);
    }

    /**
     * Save the from parameters.
     * @param object|null $object
     */
    public function execute($object = null) {
        parent::execute($object);

        /** @var SiteSettingsDAO $siteSettingsDao */
        $siteSettingsDao = $this->siteSettingsDao; 
        
        $siteSettingsDao->updateSetting('useAlphalist', (bool) $this->getData('useAlphalist'), 'bool');
        $siteSettingsDao->updateSetting('usePaging', (bool) $this->getData('usePaging'), 'bool');
        $siteSettingsDao->updateSetting('defaultMetricType', (string) $this->getData('defaultMetricType'), 'string');
        $siteSettingsDao->updateSetting('preventManagerPluginManagement', (bool) $this->getData('preventManagerPluginManagement'), 'bool');
    }
}

?>
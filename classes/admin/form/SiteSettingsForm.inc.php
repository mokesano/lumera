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
     * 
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // [WIZDAM] Strict type check fallback
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao ? $journalDao->getJournalTitles() : [];
        
        $templateMgr = TemplateManager::getManager($request);

        $allThemes = PluginRegistry::loadCategory('themes');
        $themes = [];
        
        // [WIZDAM] Safe iteration with null check
        if (is_array($allThemes)) {
            foreach ($allThemes as $plugin) {
                if ($plugin) {
                    $themes[basename($plugin->getPluginPath())] = $plugin;
                }
            }
        }
        
        $application = Application::get();
        
        // [WIZDAM] Micro-Payload: Gabungkan assign untuk kode yang lebih bersih
        $templateMgr->assign([
            'themes' => $themes,
            'redirectOptions' => $journals,
            'availableMetricTypes' => $application ? $application->getMetricTypes(true) : [],
        ]);

        parent::display($request, $template);
    }

    /**
     * Initialize the form from the current settings.
     */
    public function initData() {
        parent::initData();

        /** @var SiteDAO $siteDao */
        $siteDao = DAORegistry::getDAO('SiteDAO');
        $site = $siteDao ? $siteDao->getSite() : null;

        if ($site) {
            $this->setData('useAlphalist', (bool) $site->getSetting('useAlphalist'));
            $this->setData('usePaging', (bool) $site->getSetting('usePaging'));
            $this->setData('defaultMetricType', (string) $site->getSetting('defaultMetricType'));
            $this->setData('preventManagerPluginManagement', (bool) $site->getSetting('preventManagerPluginManagement'));
        } else {
            $this->setData('useAlphalist', false);
            $this->setData('usePaging', false);
            $this->setData('defaultMetricType', '');
            $this->setData('preventManagerPluginManagement', false);
        }
    }

    /**
     * Assign user-submitted data to form.
     * 
     * @param bool $callHooks
     * @return void
     */
    public function readInputData($callHooks = true) {
        $this->readUserVars([
            'useAlphalist', 
            'usePaging', 
            'defaultMetricType', 
            'preventManagerPluginManagement'
        ]);
        parent::readInputData();
    }

    /**
     * Save the form parameters.
     * 
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        parent::execute($object);

        /** @var SiteSettingsDAO $siteSettingsDao */
        $siteSettingsDao = DAORegistry::getDAO('SiteSettingsDAO');
        
        // [WIZDAM] Null-safety check sebelum menyimpan
        if (!$siteSettingsDao) {
            return;
        }
        
        $siteSettingsDao->updateSetting('useAlphalist', (bool) $this->getData('useAlphalist'), 'bool');
        $siteSettingsDao->updateSetting('usePaging', (bool) $this->getData('usePaging'), 'bool');
        $siteSettingsDao->updateSetting('defaultMetricType', (string) $this->getData('defaultMetricType'), 'string');
        $siteSettingsDao->updateSetting('preventManagerPluginManagement', (bool) $this->getData('preventManagerPluginManagement'), 'bool');
    }

}
?>
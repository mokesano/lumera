<?php
declare(strict_types=1);

/**
 * @file plugins/generic/phpMyVisites/PhpMyVisitesPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PhpMyVisitesPlugin
 * @ingroup plugins_generic_phpMyVisites
 *
 * @brief phpMyVisites plugin class
 * * MODERNIZED FOR PHP 7.4+ & OJS FORK
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PhpMyVisitesPlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Called as a plugin is registered to the registry
     * @param $category String Name of category plugin was registered to
     * @return boolean True iff plugin initialized successfully; if false,
     * the plugin will not be registered.
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
        if ($success && $this->getEnabled()) {
            // Modernized: Removed & references
            // Insert phpmv page tag to common footer
            HookRegistry::register('Templates::Common::Footer::PageFooter', array($this, 'insertFooter'));

            // Insert phpmv page tag to article footer
            HookRegistry::register('Templates::Article::Footer::PageFooter', array($this, 'insertFooter'));

            // Insert phpmv page tag to article interstitial footer
            HookRegistry::register('Templates::Article::Interstitial::PageFooter', array($this, 'insertFooter'));

            // Insert phpmv page tag to article pdf interstitial footer
            HookRegistry::register('Templates::Article::PdfInterstitial::PageFooter', array($this, 'insertFooter'));

            // Insert phpmv page tag to reading tools footer
            HookRegistry::register('Templates::Rt::Footer::PageFooter', array($this, 'insertFooter'));

            // Insert phpmv page tag to help footer
            HookRegistry::register('Templates::Help::Footer::PageFooter', array($this, 'insertFooter'));
        }
        return $success;
    }

    /**
     * Get display name
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.phpmv.displayName');
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.phpmv.description');
    }

    /**
     * Extend the {url ...} smarty to support this plugin.
     * FIX: Signature matched (Removed & from $smarty)
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = array($this->getCategory(), $this->getName());
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, array($params['path']));
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], array($params['id']));
            unset($params['id']);
        }
        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items
     * to append.
     * @param $subclass boolean
     */
    public function setBreadcrumbs($isSubclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = array(
            array(
                Request::url(null, 'user'),
                'navigation.user'
            ),
            array(
                Request::url(null, 'manager'),
                'user.role.manager'
            )
        );
        if ($isSubclass) $pageCrumbs[] = array(
            Request::url(null, 'manager', 'plugins'),
            'manager.plugins'
        );

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display verbs for the management interface.
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request); 

        if ($this->getEnabled($request)) { 
            $verbs[] = array('settings', __('plugins.generic.phpmv.manager.settings'));
        }
        
        return $verbs;
    }

    /**
     * Insert phpmv page tag to footer
     */
    public function insertFooter($hookName, $params) {
        if ($this->getEnabled()) {
            $smarty = $params[1];
            $output = $params[2];
            $templateMgr = TemplateManager::getManager();
            $currentJournal = $templateMgr->get_template_vars('currentJournal');

            if (!empty($currentJournal)) {
                $journal = Request::getJournal();
                $journalId = $journal->getId();
                $phpmvSiteId = $this->getSetting($journalId, 'phpmvSiteId');
                $phpmvUrl = $this->getSetting($journalId, 'phpmvUrl');

                if (!empty($phpmvSiteId) && !empty($phpmvUrl)) {
                    $templateMgr->assign('phpmvSiteId', $phpmvSiteId);
                    $templateMgr->assign('phpmvUrl', $phpmvUrl);
                    $output .= $templateMgr->fetch($this->getTemplatePath() . 'pageTag.tpl');
                }
            }
        }
        return false;
    }

    /**
     * Execute a management verb on this plugin
     * FIX: Signature matched (Removed & from $message, $messageParams)
     * @param $verb string
     * @param $args array
     * @param $message string Result status message
     * @param $messageParams array Parameters for the message key
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) return false;

        if (!$request) $request = Registry::get('request');

        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
                $journal = $request->getJournal();

                AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
                $this->import('PhpMyVisitesSettingsForm');
                $form = new PhpMyVisitesSettingsForm($this, $journal->getId());
                if (Request::getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                        return false;
                    } else {
                        $this->setBreadCrumbs(true);
                        $form->display();
                    }
                } else {
                    $this->setBreadCrumbs(true);
                    $form->initData();
                    $form->display();
                }
                return true;
            default:
                // Unknown management verb
                assert(false);
                return false;
        }
    }
}
?>
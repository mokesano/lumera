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
 * @brief phpMyVisites plugin class.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class PhpMyVisitesPlugin extends GenericPlugin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Called as a plugin is registered to the registry.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return true;
        }
        if ($success && $this->getEnabled()) {
            // Insert phpmv page tag to common footer
            HookRegistry::register('Templates::Common::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert phpmv page tag to article footer
            HookRegistry::register('Templates::Article::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert phpmv page tag to article interstitial footer
            HookRegistry::register('Templates::Article::Interstitial::PageFooter', [$this, 'insertFooter']);

            // Insert phpmv page tag to article pdf interstitial footer
            HookRegistry::register('Templates::Article::PdfInterstitial::PageFooter', [$this, 'insertFooter']);

            // Insert phpmv page tag to reading tools footer
            HookRegistry::register('Templates::Rt::Footer::PageFooter', [$this, 'insertFooter']);

            // Insert phpmv page tag to help footer
            HookRegistry::register('Templates::Help::Footer::PageFooter', [$this, 'insertFooter']);
        }
        return $success;
    }

    /**
     * Get display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.phpmv.displayName');
    }

    /**
     * Get description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.phpmv.description');
    }

    /**
     * Extend the {url ...} smarty to support this plugin.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = [$this->getCategory(), $this->getName()];
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [$params['path']]);
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], [$params['id']]);
            unset($params['id']);
        }
        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items to append.
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user'
            ],
            [
                $request->url(null, 'manager'),
                'user.role.manager'
            ]
        ];
        if ($isSubclass) {
            $pageCrumbs[] = [
                $request->url(null, 'manager', 'plugins'),
                'manager.plugins'
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs
     * @param mixed $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request); 

        if ($this->getEnabled($request)) { 
            $verbs[] = ['settings', __('plugins.generic.phpmv.manager.settings')];
        }
        
        return $verbs;
    }

    /**
     * Insert phpmv page tag to footer.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function insertFooter($hookName, $params) {
        if ($this->getEnabled()) {
            // Lumera Singleton Fallback
            $request = Application::get()->getRequest();
            $journal = $request->getJournal();

            if ($journal) {
                $journalId = (int) $journal->getId();
                $phpmvSiteId = (string) $this->getSetting($journalId, 'phpmvSiteId');
                $phpmvUrl = (string) $this->getSetting($journalId, 'phpmvUrl');

                if ($phpmvSiteId !== '' && $phpmvUrl !== '') {
                    $templateMgr = TemplateManager::getManager($request);
                    $templateMgr->assign('phpmvSiteId', $phpmvSiteId);
                    $templateMgr->assign('phpmvUrl', $phpmvUrl);

                    $params[2] .= $templateMgr->fetch($this->getTemplatePath() . 'pageTag.tpl');
                }
            }
        }
        return false;
    }

    /**
     * Execute a management verb on this plugin.
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param mixed $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) {
            return false;
        }

        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $journal = $request->getJournal();

                if (!$journal) {
                    return false;
                }

                AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_CORE_MANAGER);
                $this->import('PhpMyVisitesSettingsForm');
                $form = new PhpMyVisitesSettingsForm($this, (int) $journal->getId());
                
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', [$this->getCategory()]);
                        return false;
                    } else {
                        $this->setBreadcrumbs(true);
                        $form->display($request);
                    }
                } else {
                    $this->setBreadcrumbs(true);
                    $form->initData();
                    $form->display($request);
                }
                return true;
            default:
                throw new \BadMethodCallException('Unknown management verb');
        }
    }

}
?>
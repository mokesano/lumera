<?php
declare(strict_types=1);

/**
 * @file pages/manager/PluginHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for plugin management functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class PluginHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PluginHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display a list of plugins along with management options.
     * @param array $args
     * @param PKPRequest $request
     */
    public function plugins($args, $request) {
        $category = isset($args[0]) ? $args[0] : null;
        $categories = PluginRegistry::getCategories();

        $templateMgr = TemplateManager::getManager();
        $this->validate();

        if (isset($category) && in_array($category, $categories)) {
            // The user specified a category of plugins to view;
            // get the plugins in that category only.
            $mainPage = false;
            $plugins = PluginRegistry::loadCategory($category);

            $this->setupTemplate(false);
            $templateMgr->assign('pageTitle', 'plugins.categories.' . $category);
            $templateMgr->assign('pageHierarchy', $this->setBreadcrumbs($request, true));
        } else {
            // No plugin specified; display all.
            $mainPage = true;
            $plugins = [];
            foreach ($categories as $category) {
                $newPlugins = PluginRegistry::loadCategory($category);
                if (isset($newPlugins)) {
                    $plugins = array_merge($plugins, PluginRegistry::loadCategory($category));
                }
            }

            $this->setupTemplate(true);
            $templateMgr->assign('pageTitle', 'manager.plugins.pluginManagement');
            $templateMgr->assign('pageHierarchy', $this->setBreadcrumbs($request, false));
        }

        $templateMgr->assign('plugins', $plugins);
        $templateMgr->assign('categories', $categories);
        $templateMgr->assign('mainPage', $mainPage);
        $templateMgr->assign('isSiteAdmin', Validation::isSiteAdmin());
        $templateMgr->assign('helpTopicId', 'journal.managementPages.plugins');

        $site = $request->getSite();
        $preventManagerPluginManagement = $site->getSetting('preventManagerPluginManagement');
        $templateMgr->assign('preventManagerPluginManagement', !Validation::isSiteAdmin() && $preventManagerPluginManagement);

        $templateMgr->display('manager/plugins/plugins.tpl');
    }

    /**
     * Perform plugin-specific management functions.
     * @param array $args
     * @param PKPRequest $request
     */
    public function plugin($args, $request) {
        $category = array_shift($args);
        $plugin = array_shift($args);
        $verb = array_shift($args);

        $this->validate();
        $this->setupTemplate(true);

        $plugins = PluginRegistry::loadCategory($category);
        $message = null;
        $messageParams = null;
        $pluginObject = null;
        
        if (isset($plugins[$plugin])) {
            $pluginObject = $plugins[$plugin];
        }

        if ($pluginObject === null) {
            $request->redirect(null, null, 'plugins', [$category]);
        }
        
        $message = '';          
        $messageParams = [];
        
        if (!$pluginObject->manage($verb, $args, $message, $messageParams, $request)) {
            // [WIZDAM] Hooks usually expect reference params if modification is intended, 
            // but here we just pass values as per legacy signature
            HookRegistry::dispatch('PluginHandler::plugin', [$verb, $args, $message, $messageParams, $pluginObject]);
            
            if ($message) {
                $user = $request->getUser();
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification($user->getId(), $message, $messageParams);
            }
            $request->redirect(null, null, 'plugins', [$category]);
        }
    }

    /**
     * Set the page's breadcrumbs
     * @param PKPRequest $request
     * @param bool $subclass
     * @return array
     */
    public function setBreadcrumbs($request, $subclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user',
                false
            ],
            [
                $request->url(null, 'manager'),
                'manager.journalManagement',
                false
            ]
        ];

        if ($subclass) {
            $pageCrumbs[] = [
                $request->url(null, 'manager', 'plugins'),
                'manager.plugins.pluginManagement',
                false
            ];
        }

        return $pageCrumbs;
    }
}
?>
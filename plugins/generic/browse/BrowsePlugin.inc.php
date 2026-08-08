<?php
declare(strict_types=1);

/**
 * @file plugins/generic/browse/BrowsePlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BrowsePlugin
 * @ingroup plugins_generic_browse
 *
 * @brief Browse by additional objects plugin class.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class BrowsePlugin extends GenericPlugin {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function BrowsePlugin() {
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
     * Called as a plugin is registered to the registry.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                HookRegistry::register('Plugins::Blocks::Navigation::BrowseBy', [$this, 'addNavigationItem']);
                HookRegistry::register('LoadHandler', [$this, 'setupBrowseHandler']);
            }
            return true;
        }
        return false;
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.browse.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.browse.description');
    }

    /**
     * Get the template path for this plugin.
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates/';
    }

    /**
     * Get the handler path for this plugin.
     * @return string
     */
    public function getHandlerPath(): string {
        return $this->getPluginPath() . '/pages/';
    }

    /**
     * Add additional navigation items.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function addNavigationItem($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2];

        $journal = $smarty->get_template_vars('currentJournal');
        if ($journal === null) {
            return false;
        }

        $templateMgr = TemplateManager::getManager();
        $journalId = (int) $journal->getId();

        if ($this->getSetting($journalId, 'enableBrowseBySections')) {
            $output .= '<li id="linkBrowseBySections"><a href="' . $templateMgr->smartyUrl(['page' => 'browseSearch', 'op' => 'sections'], $smarty) . '">' . $templateMgr->smartyTranslate(['key' => 'plugins.generic.browse.search.sections'], $smarty) . '</a></li>';
        }
        if ($this->getSetting($journalId, 'enableBrowseByIdentifyTypes')) {
            $output .= '<li id="linkBrowseByIdentifyTypes"><a href="' . $templateMgr->smartyUrl(['page' => 'browseSearch', 'op' => 'identifyTypes'], $smarty) . '">' . $templateMgr->smartyTranslate(['key' => 'plugins.generic.browse.search.identifyTypes'], $smarty) . '</a></li>';
        }
        return false;
    }

    /**
     * Enable editor pixel tags management.
     * @param string $hookName
     * @param array $params
     * @return void
     */
    public function setupBrowseHandler($hookName, $params) {
        $page = $params[0];

        if ($page === 'browseSearch') {
            $op = $params[1];

            if ($op !== null && $op !== '') {
                $editorPages = [
                    'sections',
                    'identifyTypes'
                ];

                if (in_array($op, $editorPages, true)) {
                    if (!defined('HANDLER_CLASS')) {
                        define('HANDLER_CLASS', 'BrowseHandler');
                    }
                    if (!defined('BROWSE_PLUGIN_NAME')) {
                        define('BROWSE_PLUGIN_NAME', $this->getName());
                    }
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                    $handlerFile =& $params[2]; 
                    $handlerFile = $this->getHandlerPath() . 'BrowseHandler.inc.php';
                }
            }
        }
    }

    /**
     * Set the breadcrumbs, given the plugin's tree of items to append.
     * @param bool $isSubclass
     * @return void
     */
    public function setBreadcrumbs($isSubclass = false) {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager();
        $router = $request->getRouter();
        
        $pageCrumbs = [
            [
                $router->url($request, null, 'user'),
                'navigation.user'
            ],
            [
                $router->url($request, null, 'manager'),
                'user.role.manager'
            ]
        ];
        
        if ($isSubclass) {
            $pageCrumbs[] = [
                $router->url($request, null, 'manager', 'plugins'),
                'manager.plugins'
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs
     * @param object|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled($request)) {
            $verbs[] = ['settings', __('plugins.generic.browse.manager.settings')];
        }
        return $verbs;
    }

    /**
     * Execute a management verb on this plugin.
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param object|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) {
            return false;
        }

        $request = $request ?? Application::get()->getRequest();

        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $journal = $request->getJournal();
                
                if ($journal === null) {
                    return false;
                }

                $this->import('classes.form.BrowseSettingsForm');
                $form = new BrowseSettingsForm($this, (int) $journal->getId());

                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                        return false;
                    } else {
                        $this->setBreadcrumbs(true);
                        $form->display();
                    }
                } else {
                    $this->setBreadcrumbs(true);
                    $form->initData();
                    $form->display();
                }
                return true;
            default:
                assert(false);
                return false;
        }
    }
    
}
?>
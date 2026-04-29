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
 * * MODERNIZED FOR PHP 7.4+ & OJS FORK
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class BrowsePlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BrowsePlugin() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::BrowsePlugin(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Called as a plugin is registered to the registry
     * @param $category
     * @return bool
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                // Add new navigation items in the navigation block plugin
                HookRegistry::register('Plugins::Blocks::Navigation::BrowseBy', array($this, 'addNavigationItem'));
                // Handler for browse plugin pages
                HookRegistry::register('LoadHandler', array($this, 'setupBrowseHandler'));
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
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates/';
    }

    /**
     * Get the handler path for this plugin.
     */
    public function getHandlerPath(): string {
        return $this->getPluginPath() . '/pages/';
    }

    /**
     * Add additional navigation items.
     */
    public function addNavigationItem($hookName, $params) {
        $smarty = $params[1];
        $output =& $params[2]; // [WIZDAM NOTE]: $output harus by reference.

        $journal = $smarty->get_template_vars('currentJournal');

        $templateMgr = TemplateManager::getManager();
        if ($this->getSetting($journal->getId(), 'enableBrowseBySections')) {
            $output .= '<li id="linkBrowseBySections"><a href="' . $templateMgr->smartyUrl(array('page' => 'browseSearch', 'op'=>'sections'), $smarty) . '">' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.browse.search.sections'), $smarty) . '</a></li>';
        }
        if ($this->getSetting($journal->getId(), 'enableBrowseByIdentifyTypes')) {
            $output .= '<li id="linkBrowseByIdentifyTypes"><a href="' . $templateMgr->smartyUrl(array('page' => 'browseSearch', 'op'=>'identifyTypes'), $smarty).'">' . $templateMgr->smartyTranslate(array('key'=>'plugins.generic.browse.search.identifyTypes'), $smarty) . '</a></li>';
        }
        return false;
    }

    /**
     * Enable editor pixel tags management.
     */
    public function setupBrowseHandler($hookName, $params) {
        $page = $params[0];

        if ($page == 'browseSearch') {
            $op = $params[1];

            if ($op) {
                $editorPages = array(
                    'sections',
                    'identifyTypes'
                );

                if (in_array($op, $editorPages)) {
                    define('HANDLER_CLASS', 'BrowseHandler');
                    define('BROWSE_PLUGIN_NAME', $this->getName());
                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
                    // [WIZDAM NOTE]: $handlerFile harus by reference untuk mengubah path handler yang akan dimuat sistem.
                    $handlerFile =& $params[2]; 
                    $handlerFile = $this->getHandlerPath() . 'BrowseHandler.inc.php';
                }
            }
        }
    }

    /**
     * Set the breadcrumbs, given the plugin's tree of items to append.
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
            $verbs[] = array('settings', __('plugins.generic.browse.manager.settings'));
        }
        return $verbs;
    }

    /**
     * Execute a management verb on this plugin
     * @param $verb string
     * @param $args array
     * @param $message string Location for the plugin to put a result msg
     * @param $messageParams array
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) return false;

        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
                $journal = Request::getJournal();

                $this->import('classes.form.BrowseSettingsForm');
                $form = new BrowseSettingsForm($this, $journal->getId());

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
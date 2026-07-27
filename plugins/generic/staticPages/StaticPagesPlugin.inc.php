<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StaticPagesPlugin
 * @ingroup plugins_generic_staticPages
 *
 * @brief StaticPagesPlugin class.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class StaticPagesPlugin extends GenericPlugin {

    /**
     * Get the display name of this plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.staticPages.displayName');
    }

    /**
     * Get a description of the plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        $description = __('plugins.generic.staticPages.description');
        if (!$this->isTinyMCEInstalled()) {
            $description .= '<br />' . __('plugins.generic.staticPages.requirement.tinymce');
        }
        return $description;
    }

    /**
     * Check if the TinyMCE plugin is installed and enabled.
     * @see PKPApplication::getEnabledProducts()
     * @return bool
     */
    public function isTinyMCEInstalled(): bool {
        // Lumera Singleton Fallback
        $application = Application::get();
        $products = $application->getEnabledProducts('plugins.generic');
        return isset($products['tinymce']);
    }

    /**
     * Register the plugin, attaching to hooks as necessary.
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if (\Config::getVar('general', 'installed')) {
                $this->import('StaticPagesDAO');
                $staticPagesDao = new StaticPagesDAO($this->getName());
                DAORegistry::registerDAO('StaticPagesDAO', $staticPagesDao);
            }

            HookRegistry::register('LoadHandler', [$this, 'callbackHandleContent']);
            return true;
        }
        return false;
    }

    /**
     * Declare the handler function to process the actual page PATH.
     * @see PKPApplication::getRequest()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackHandleContent($hookName, $args) {
        $page = $args[0] ?? '';
        
        if ($page === 'pages') {
            define('STATIC_PAGES_PLUGIN_NAME', $this->getName()); // Kludge
            define('HANDLER_CLASS', 'StaticPagesHandler');
            $this->import('StaticPagesHandler');
            return true;
        }
        return false;
    }

    /**
     * Display verbs for the management interface.
     * @see PKPPlugin::getManagementVerbs()
     * @param array $verbs
     * @param mixed $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled($request)) {
            if ($this->isTinyMCEInstalled()) {
                $verbs[] = ['settings', __('plugins.generic.staticPages.editAddContent')];
            }
        }
        return $verbs;
    }

    /**
     * Perform management functions.
     * @see PKPPlugin::manage()
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

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        $templateMgr->assign('pagesPath', $request->url(null, 'pages', 'view', 'REPLACEME'));

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

        switch ($verb) {
            case 'settings':
                $journal = $request->getJournal();
                $this->import('StaticPagesSettingsForm');
                $form = new StaticPagesSettingsForm($this, $journal ? (int) $journal->getId() : 0);

                $templateMgr->assign('pageHierarchy', $pageCrumbs);
                $form->initData();
                $form->display($request);
                return true;

            case 'edit':
            case 'add':
                $journal = $request->getJournal();
                $this->import('StaticPagesEditForm');

                $staticPageId = !empty($args[0]) ? (int) $args[0] : null;
                $form = new StaticPagesEditForm($this, $journal ? (int) $journal->getId() : 0, $staticPageId);

                if ($form->isLocaleResubmit()) {
                    $form->readInputData();
                    $form->addTinyMCE();
                } else {
                    $form->initData();
                }

                $pageCrumbs[] = [
                    $request->url(null, 'manager', 'plugin', ['generic', $this->getName(), 'settings']),
                    $this->getDisplayName(),
                    true
                ];
                $templateMgr->assign('pageHierarchy', $pageCrumbs);
                $form->display($request);
                return true;

            case 'save':
                $journal = $request->getJournal();
                $this->import('StaticPagesEditForm');

                $staticPageId = !empty($args[0]) ? (int) $args[0] : null;
                $form = new StaticPagesEditForm($this, $journal ? (int) $journal->getId() : 0, $staticPageId);

                if ($request->getUserVar('edit')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->save();
                        $templateMgr->assign([
                            'currentUrl' => $request->url(null, null, null, [$this->getCategory(), $this->getName(), 'settings']),
                            'pageTitle' => 'plugins.generic.staticPages.displayName',
                            'pageHierarchy' => $pageCrumbs,
                            'message' => 'plugins.generic.staticPages.pageSaved',
                            'backLink' => $request->url(null, null, null, [$this->getCategory(), $this->getName(), 'settings']),
                            'backLinkLabel' => 'common.continue'
                        ]);
                        $templateMgr->display('common/message.tpl');
                        exit;
                    } else {
                        $form->addTinyMCE();
                        $form->display($request);
                        exit;
                    }
                }
                $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                return false;

            case 'delete':
                $staticPageId = !empty($args[0]) ? (int) $args[0] : null;

                /** @var StaticPagesDAO $staticPagesDao */
                $staticPagesDao = DAORegistry::getDAO('StaticPagesDAO');
                $staticPagesDao->deleteStaticPageById($staticPageId);

                $templateMgr->assign([
                    'currentUrl' => $request->url(null, null, null, [$this->getCategory(), $this->getName(), 'settings']),
                    'pageTitle' => 'plugins.generic.staticPages.displayName',
                    'message' => 'plugins.generic.staticPages.pageDeleted',
                    'backLink' => $request->url(null, null, null, [$this->getCategory(), $this->getName(), 'settings']),
                    'backLinkLabel' => 'common.continue'
                ]);

                $templateMgr->assign('pageHierarchy', $pageCrumbs);
                $templateMgr->display('common/message.tpl');
                return true;

            default:
                throw new \BadMethodCallException('Unknown management verb');
        }
    }

    /**
     * Get the filename of the ADODB schema for this plugin.
     * @see PKPPlugin::getInstallSchemaFile()
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'schema.xml';
    }
    
}
?>
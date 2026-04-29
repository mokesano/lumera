<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 * @class StaticPagesPlugin
 *
 * StaticPagesPlugin class
 * * MODERNIZED FOR WIZDAM FORK
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
        if ( !$this->isTinyMCEInstalled() )
            $description .= "<br />".__('plugins.generic.staticPages.requirement.tinymce');
        return $description;
    }

    /**
     * Check if the TinyMCE plugin is installed and enabled.
     * @see PKPApplication::getEnabledProducts()
     * @return boolean
     */
    public function isTinyMCEInstalled() {
        // If the thesis plugin isn't enabled, don't do anything.
        $application = PKPApplication::getApplication();
        $products = $application->getEnabledProducts('plugins.generic');
        return (isset($products['tinymce']));
    }

    /**
     * Register the plugin, attaching to hooks as necessary.
     * @see PKPPlugin::register()
     * @param $category string
     * @param $path string
     * @return boolean
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if (\Config::getVar('general', 'installed')) {
                $this->import('StaticPagesDAO');
                $staticPagesDao = new StaticPagesDAO($this->getName());
                DAORegistry::registerDAO('StaticPagesDAO', $staticPagesDao);
            }

            HookRegistry::register('LoadHandler', array($this, 'callbackHandleContent'));
            return true;
        }
        return false;
    }

    /**
     * Declare the handler function to process the actual page PATH
     * @see PKPApplication::getRequest()
     * @param $hookName string
     * @param $args array
     * @return boolean
     */
    public function callbackHandleContent($hookName, $args) {
        $templateMgr = TemplateManager::getManager();

        $page = $args[0];
        $op = $args[1];

        if ( $page == 'pages' ) {
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
     * @param $verbs array
     * @param $request PKPRequest
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled($request)) {
            if ($this->isTinyMCEInstalled()) {
                $verbs[] = array('settings', __('plugins.generic.staticPages.editAddContent'));
            }
        }
        return $verbs;
    }

    /**
     * Perform management functions
     * @see PKPPlugin::manage()
     * @param $verb string
     * @param $args array
     * @param $message string
     * @param $messageParams array
     * @param $request PKPRequest
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) return false;

        if (!$request) $request = Application::getRequest();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
        $templateMgr->assign('pagesPath', $request->url(null, 'pages', 'view', 'REPLACEME'));

        $pageCrumbs = array(
            array(
                $request->url(null, 'user'),
                'navigation.user'
            ),
            array(
                $request->url(null, 'manager'),
                'user.role.manager'
            )
        );

        switch ($verb) {
            case 'settings':
                $journal = $request->getJournal();

                $this->import('StaticPagesSettingsForm');
                $form = new StaticPagesSettingsForm($this, $journal ? $journal->getId() : 0);

                $templateMgr->assign('pageHierarchy', $pageCrumbs);
                $form->initData();
                $form->display();
                return true;
            case 'edit':
            case 'add':
                $journal = $request->getJournal();

                $this->import('StaticPagesEditForm');

                $staticPageId = isset($args[0])?(int)$args[0]:null;
                $form = new StaticPagesEditForm($this, $journal ? $journal->getId() : 0, $staticPageId);

                if ($form->isLocaleResubmit()) {
                    $form->readInputData();
                    $form->addTinyMCE();
                } else {
                    $form->initData();
                }

                $pageCrumbs[] = array(
                    $request->url(null, 'manager', 'plugin', array('generic', $this->getName(), 'settings')),
                    $this->getDisplayName(),
                    true
                );
                $templateMgr->assign('pageHierarchy', $pageCrumbs);
                $form->display();
                return true;
            case 'save':
                $journal = $request->getJournal();

                $this->import('StaticPagesEditForm');

                $staticPageId = isset($args[0])?(int)$args[0]:null;
                $form = new StaticPagesEditForm($this, $journal ? $journal->getId() : 0, $staticPageId);

                if ($request->getUserVar('edit')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->save();
                        $templateMgr->assign(array(
                            'currentUrl' => $request->url(null, null, null, array($this->getCategory(), $this->getName(), 'settings')),
                            'pageTitle' => 'plugins.generic.staticPages.displayName',
                            'pageHierarchy' => $pageCrumbs,
                            'message' => 'plugins.generic.staticPages.pageSaved',
                            'backLink' => $request->url(null, null, null, array($this->getCategory(), $this->getName(), 'settings')),
                            'backLinkLabel' => 'common.continue'
                        ));
                        $templateMgr->display('common/message.tpl');
                        exit;
                    } else {
                        $form->addTinyMCE();
                        $form->display();
                        exit;
                    }
                }
                $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                return false;
            case 'delete':
                $staticPageId = isset($args[0])?(int) $args[0]:null;
                $staticPagesDao = DAORegistry::getDAO('StaticPagesDAO');
                $staticPagesDao->deleteStaticPageById($staticPageId);

                $templateMgr->assign(array(
                    'currentUrl' => $request->url(null, null, null, array($this->getCategory(), $this->getName(), 'settings')),
                    'pageTitle' => 'plugins.generic.staticPages.displayName',
                    'message' => 'plugins.generic.staticPages.pageDeleted',
                    'backLink' => $request->url(null, null, null, array($this->getCategory(), $this->getName(), 'settings')),
                    'backLinkLabel' => 'common.continue'
                ));

                $templateMgr->assign('pageHierarchy', $pageCrumbs);
                $templateMgr->display('common/message.tpl');
                return true;
            default:
                // Unknown management verb
                assert(false);
                return false;
        }
    }

    /**
     * Get the filename of the ADODB schema for this plugin.
     * @see PKPPlugin::getInstallSchemaFile()
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . '/' . 'schema.xml';
    }
}
?>
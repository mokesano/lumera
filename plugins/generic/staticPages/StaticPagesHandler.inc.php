<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StaticPagesHandler
 * @ingroup plugins_generic_staticPages
 *
 * @brief Find the content and display the appropriate page.
 */

import('classes.handler.Handler');

class StaticPagesHandler extends Handler {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function StaticPagesHandler() {
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
     * Index handler.
     * @param array $args
     * @param mixed $request
     */
    public function index($args = [], $request = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $this->view($args, $request);
    }

    /**
     * View handler.
     * @param array $args
     * @param mixed $request
     */
    public function view($args, $request) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        if (empty($args)) {
            return;
        }

        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_COMMON, 
            LOCALE_COMPONENT_APPLICATION_COMMON, 
            LOCALE_COMPONENT_CORE_USER
        );
        
        $journal = $request->getJournal();
        $journalId = $journal ? (int) $journal->getId() : 0;
        $path = (string) $args[0];

        /** @var GenericPlugin $staticPagesPlugin */
        $staticPagesPlugin = PluginRegistry::getPlugin('generic', STATIC_PAGES_PLUGIN_NAME);
        if (!$staticPagesPlugin || !$staticPagesPlugin->getEnabled($request)) {
            $request->redirect(null, 'index');
            return; 
        }

        $templateMgr = TemplateManager::getManager($request);

        /** @var StaticPagesDAO $staticPagesDao */
        $staticPagesDao = DAORegistry::getDAO('StaticPagesDAO');
        $staticPage = $staticPagesDao->getStaticPageByPath($journalId, $path);

        if (!$staticPage) {
            $request->redirect(null, 'index'); 
            return;
        }

        $templateMgr->assign('title', (string) $staticPage->getStaticPageTitle());
        $templateMgr->assign('content', (string) $staticPage->getStaticPageContent());
        $templateMgr->display($staticPagesPlugin->getTemplatePath() . 'content.tpl');
    }

}
?>
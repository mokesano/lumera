<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPagesHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 * @class StaticPagesHandler
 *
 * Find the content and display the appropriate page
 * * MODERNIZED FOR WIZDAM FORK
 */

import('classes.handler.Handler');

class StaticPagesHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function StaticPagesHandler() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::StaticPagesHandler(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Index handler
     * @param $args array
     * @param $request PKPRequest
     */
    public function index($args = array(), $request = null) {
        if (!$request) {
            $request = Application::getRequest();
        }
        $this->view($args, $request);
    }

    /**
     * View handler
     * @param $args array
     * @param $request PKPRequest
     */
    public function view($args, $request) {
        if (count($args) > 0 ) {
            AppLocale::requireComponents(LOCALE_COMPONENT_CORE_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_CORE_USER);
            
            // [MODERNISASI] Hapus tanda &
            $journal = $request->getJournal();
            $journalId = $journal ? $journal->getId() : 0;
            $path = $args[0];

            // [MODERNISASI] Hapus tanda &
            $staticPagesPlugin = PluginRegistry::getPlugin('generic', STATIC_PAGES_PLUGIN_NAME);
            
            if (!$staticPagesPlugin || !$staticPagesPlugin->getEnabled($request)) {
                $request->redirect(null, 'index');
                return; 
            }

            // [MODERNISASI] Hapus tanda &
            $templateMgr = TemplateManager::getManager($request);

            // [MODERNISASI] Hapus tanda &
            $staticPagesDao = DAORegistry::getDAO('StaticPagesDAO');
            $staticPage = $staticPagesDao->getStaticPageByPath($journalId, $path);

            if (!$staticPage) {
                $request->redirect(null, 'index'); 
                return;
            }

            $templateMgr->assign('title', $staticPage->getStaticPageTitle());
            $templateMgr->assign('content',  $staticPage->getStaticPageContent());
            $templateMgr->display($staticPagesPlugin->getTemplatePath().'content.tpl');
        }
    }
}

?>
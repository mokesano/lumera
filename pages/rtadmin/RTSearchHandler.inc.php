<?php
declare(strict_types=1);

/**
 * @file pages/rtadmin/RTSearchHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTSearchHandler
 * @ingroup pages_rtadmin
 *
 * @brief Handle Reading Tools administration requests -- contexts section.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.rtadmin.RTAdminHandler');

class RTSearchHandler extends RTAdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RTSearchHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::RTSearchHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Create search.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createSearch($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);

        import('classes.rt.ojs.form.SearchForm');
        $searchForm = new SearchForm(null, $contextId, $versionId);

        if (isset($args[2]) && $args[2]=='save') {
            $searchForm->readInputData();
            $searchForm->execute();
            $request->redirect(null, null, 'searches', [$versionId, $contextId]);
        } else {
            $this->setupTemplate(true, $version, $context);
            $searchForm->display();
        }
    }

    /**
     * List searches.
     * @param array $args
     * @param PKPRequest $request
     */
    public function searches($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        $rtDao = DAORegistry::getDAO('RTDAO');
        $rangeInfo = $this->getRangeInfo('searches');

        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());

        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);

        if ($context && $version && $context->getVersionId() == $version->getVersionId()) {
            $this->setupTemplate(true, $version, $context);

            $templateMgr = TemplateManager::getManager();

            $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
            $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');

            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('version', $version);
            $templateMgr->assign('context', $context);
            
            import('lib.pkp.classes.core.ArrayItemIterator');
            $templateMgr->assign('searches', new ArrayItemIterator($context->getSearches(), $rangeInfo->getPage(), $rangeInfo->getCount()));

            $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.contexts');
            $templateMgr->display('rtadmin/searches.tpl');
        } else {
            $request->redirect(null, null, 'versions');
        }
    }

    /**
     * Edit search.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editSearch($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);
        $searchId = isset($args[2]) ? (int)$args[2] : 0;
        $search = $rtDao->getSearch($searchId);

        if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
            import('classes.rt.ojs.form.SearchForm');
            $this->setupTemplate(true, $version, $context, $search);
            $searchForm = new SearchForm($searchId, $contextId, $versionId);
            $searchForm->initData();
            $searchForm->display();
        } else {
            $request->redirect(null, null, 'searches', [$versionId, $contextId]);
        }
    }

    /**
     * Delete search.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteSearch($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);
        $searchId = isset($args[2]) ? (int)$args[2] : 0;
        $search = $rtDao->getSearch($searchId);

        if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
            $rtDao->deleteSearch($searchId, $contextId);
        }

        $request->redirect(null, null, 'searches', [$versionId, $contextId]);
    }

    /**
     * Save search.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveSearch($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);
        $searchId = isset($args[2]) ? (int)$args[2] : 0;
        $search = $rtDao->getSearch($searchId);

        if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
            import('classes.rt.ojs.form.SearchForm');
            $searchForm = new SearchForm($searchId, $contextId, $versionId);
            $searchForm->readInputData();
            $searchForm->execute();
        }

        $request->redirect(null, null, 'searches', [$versionId, $contextId]);
    }

    /**
     * Move search.
     * @param array $args
     * @param PKPRequest $request
     */
    public function moveSearch($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);
        
        // [SECURITY FIX] Amankan 'id' (searchId, ID integer) with (int) trim()
        $searchId = (int) trim((string) $request->getUserVar('id'));
        
        $search = $rtDao->getSearch($searchId);

        if (isset($version) && isset($context) && isset($search) && $context->getVersionId() == $version->getVersionId() && $search->getContextId() == $context->getContextId()) {
            
            // [SECURITY FIX] Amankan 'dir' (direction, string key) with trim()
            $direction = trim((string) $request->getUserVar('dir')); 
            
            if (!empty($direction)) {
                // moving with up or down arrow
                // Gunakan whitelisting yang ketat untuk arah yang valid
                $isDown = $direction == 'd'; 
                $search->setOrder($search->getOrder() + ($isDown ? 1.5 : -1.5));
            } else {
                // drag and drop
                
                // [SECURITY FIX] Amankan 'prevId' (ID integer) wit (int) trim()
                $prevId = (int) trim((string) $request->getUserVar('prevId'));
                
                if ($prevId == 0) { // $prevId akan 0 jika null/kosong karena (int) casting
                    $prevSeq = 0;
                } else {
                    // Gunakan ID yang sudah diamankan
                    $prevSearch = $rtDao->getSearch($prevId);
                    $prevSeq = $prevSearch ? $prevSearch->getOrder() : 0;
                }

                $search->setOrder($prevSeq + .5);
            }
            $rtDao->updateSearch($search);
            $rtDao->resequenceSearches($context->getContextId());
        }

        // Moving up or down with the arrows requires a page reload.
        // In the case of a drag and drop move, the display has been
        // updated on the client side, so no reload is necessary.
        if (!empty($direction)) {
            $request->redirect(null, null, 'searches', [$versionId, $contextId]);
        }
    }
}
?>
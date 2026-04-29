<?php
declare(strict_types=1);

/**
 * @file pages/rtadmin/RTContextHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTContextHandler
 * @ingroup pages_rtadmin
 *
 * @brief Handle Reading Tools administration requests -- contexts section.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.rtadmin.RTAdminHandler');

class RTContextHandler extends RTAdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RTContextHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::RTContextHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Create context.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createContext($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        $rtDao = DAORegistry::getDAO('RTDAO');
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());

        import('classes.rt.ojs.form.ContextForm');
        $contextForm = new ContextForm(null, $versionId);

        if (isset($args[1]) && $args[1]=='save') {
            $contextForm->readInputData();
            $contextForm->execute();
            $request->redirect(null, null, 'contexts', $versionId);
        } else {
            $this->setupTemplate(true, $version);
            $contextForm->display();
        }
    }

    /**
     * List contexts.
     * @param array $args
     * @param PKPRequest $request
     */
    public function contexts($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        $rtDao = DAORegistry::getDAO('RTDAO');
        $rangeInfo = $this->getRangeInfo('contexts');

        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());

        if ($version) {
            $this->setupTemplate(true, $version);

            $templateMgr = TemplateManager::getManager();
            $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
            $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');

            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('version', $version);

            import('lib.pkp.classes.core.ArrayItemIterator');
            $templateMgr->assign('contexts', new ArrayItemIterator($version->getContexts(), $rangeInfo->getPage(), $rangeInfo->getCount()));

            $templateMgr->assign('helpTopicId', 'journal.managementPages.readingTools.contexts');
            $templateMgr->display('rtadmin/contexts.tpl');
        } else {
            $request->redirect(null, null, 'versions');
        }
    }

    /**
     * Edit context.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editContext($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);

        if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
            import('classes.rt.ojs.form.ContextForm');
            $this->setupTemplate(true, $version, $context);
            $contextForm = new ContextForm($contextId, $versionId);
            $contextForm->initData();
            $contextForm->display();
        } else {
            $request->redirect(null, null, 'contexts', $versionId);
        }
    }

    /**
     * Delete context.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteContext($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);

        if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
            $rtDao->deleteContext($contextId, $versionId);
        }

        $request->redirect(null, null, 'contexts', $versionId);
    }

    /**
     * Save context.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveContext($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        $contextId = isset($args[1]) ? (int)$args[1] : 0;
        $context = $rtDao->getContext($contextId);

        if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
            import('classes.rt.ojs.form.ContextForm');
            $contextForm = new ContextForm($contextId, $versionId);
            $contextForm->readInputData();
            $contextForm->execute();
        }

        $request->redirect(null, null, 'contexts', $versionId);
    }

    /**
     * Move context.
     * @param array $args
     * @param PKPRequest $request
     */
    public function moveContext($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $rtDao = DAORegistry::getDAO('RTDAO');

        $journal = $request->getJournal();
        $versionId = isset($args[0]) ? (int)$args[0] : 0;
        $version = $rtDao->getVersion($versionId, $journal->getId());
        
        // [SECURITY FIX] Amankan 'id' (contextId, ID integer) with (int) trim()
        $contextId = (int) trim((string) $request->getUserVar('id'));
        
        $context = $rtDao->getContext($contextId);

        // [SECURITY FIX] Whitelist 'dir' (direction)
        $direction = trim((string) $request->getUserVar('dir')); 

        if (isset($version) && isset($context) && $context->getVersionId() == $version->getVersionId()) {
            
            if (!empty($direction)) {
                // moving with up or down arrow
                // Gunakan whitelisting yang ketat untuk arah yang valid
                $isDown = $direction == 'd'; 
                $context->setOrder($context->getOrder() + ($isDown ? 1.5 : -1.5));
            } else {
                // drag and drop
                
                // [SECURITY FIX] Amankan 'prevId' (ID integer) wit (int) trim()
                $prevId = (int) trim((string) $request->getUserVar('prevId'));
                
                if ($prevId == 0) { // $prevId akan 0 jika null/kosong karena (int) casting
                    $prevSeq = 0;
                } else {
                    $prevContext = $rtDao->getContext($prevId); 
                    $prevSeq = $prevContext ? $prevContext->getOrder() : 0;
                }

                $context->setOrder($prevSeq + .5);
            }
            $rtDao->updateContext($context);
            $rtDao->resequenceContexts($version->getVersionId());
        }

        // Moving up or down with the arrows requires a page reload.
        // In the case of a drag and drop move, the display has been
        // updated on the client side, so no reload is necessary.
        if (!empty($direction)) {
            $request->redirect(null, null, 'contexts', $versionId);
        }
    }
}
?>
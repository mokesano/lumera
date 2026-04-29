<?php
declare(strict_types=1);

/**
 * @file pages/proofreader/SubmissionProofreadHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionProofreadHandler
 * @ingroup pages_proofreader
 *
 * @brief Handle requests for proofreader submission functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.proofreader.ProofreaderHandler');

class SubmissionProofreadHandler extends ProofreaderHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubmissionProofreadHandler() {
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
     * Submission - Proofreading view
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function submission($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $journal = $request->getJournal();

        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId);

        $useProofreaders = $journal->getSetting('useProofreaders');

        $authorDao = DAORegistry::getDAO('AuthorDAO');
        $authors = $authorDao->getAuthorsBySubmissionId($articleId);

        ProofreaderAction::proofreadingUnderway($this->submission, 'SIGNOFF_PROOFREADING_PROOFREADER');
        $useLayoutEditors = $journal->getSetting('useLayoutEditors');

        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('useProofreaders', $useProofreaders);
        $templateMgr->assign('authors', $authors);
        $templateMgr->assign('submission', $this->submission);
        $templateMgr->assign('useLayoutEditors', $useLayoutEditors);
        $templateMgr->assign('helpTopicId', 'editorial.proofreadersRole.proofreading');

        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($this->submission->getId());
        if ($publishedArticle) {
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($publishedArticle->getIssueId());
            $templateMgr->assign('publishedArticle', $publishedArticle);
            $templateMgr->assign('issue', $issue);
        }

        $templateMgr->display('proofreader/submission.tpl');
    }

    /**
     * Sets proofreader completion date
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function completeProofreader($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) $request->getUserVar('articleId');

        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        // [WIZDAM FIX] Gunakan '!== null' untuk deteksi tombol
        $sendButtonPressed = ($request->getUserVar('send') !== null);

        if (ProofreaderAction::proofreadEmail($articleId, 'PROOFREAD_COMPLETE', $request, $sendButtonPressed ? '' : $request->url(null, 'proofreader', 'completeProofreader'))) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * View submission metadata.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function viewMetadata($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $journal = $request->getJournal();
        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId, 'summary');

        ProofreaderAction::viewMetadata($this->submission, $journal);
    }

    //
    // Misc
    //

    /**
     * Download a file.
     * @param array $args ($articleId, $fileId, [$revision])
     * @param object|null $request PKPRequest
     */
    public function downloadFile($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = array_shift($args); // May be null or int

        $this->validate($request, $articleId);
        
        // Ensure revision is int if present
        if ($revision !== null) $revision = (int) $revision;

        if (!ProofreaderAction::downloadProofreaderFile($this->submission, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Proof / "preview" a galley.
     * @param array $args ($articleId, $galleyId)
     * @param object|null $request PKPRequest
     */
    public function proofGalley($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $galleyId = (int) array_shift($args);
        $this->validate($request, $articleId);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->display('submission/layout/proofGalley.tpl');
    }

    /**
     * Proof galley (shows frame header).
     * @param array $args ($articleId, $galleyId)
     * @param object|null $request PKPRequest
     */
    public function proofGalleyTop($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $galleyId = (int) array_shift($args);
        $this->validate($request, $articleId);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('backHandler', 'submission');
        $templateMgr->display('submission/layout/proofGalleyTop.tpl');
    }

    /**
     * Proof galley (outputs file contents).
     * @param array $args ($articleId, $galleyId)
     * @param object|null $request PKPRequest
     */
    public function proofGalleyFile($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $galleyId = (int) array_shift($args);
        $this->validate($request, $articleId);

        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galley = $galleyDao->getGalley($galleyId, $articleId);

        import('classes.file.ArticleFileManager'); 

        if (isset($galley)) {
            if ($galley->isHTMLGalley()) {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('galley', $galley);
                if ($galley->isHTMLGalley() && $styleFile = $galley->getStyleFile()) {
                    $templateMgr->addStyleSheet($request->url(null, 'article', 'viewFile', [
                        $articleId, $galleyId, $styleFile->getFileId()
                    ]));
                }
                $templateMgr->display('submission/layout/proofGalleyHTML.tpl');

            } else {
                // View non-HTML file inline
                $this->viewFile([$articleId, $galley->getFileId()], $request);
            }
        }
    }

    /**
     * View a file (inlines file).
     * @param array $args ($articleId, $fileId, [$revision])
     * @param object|null $request PKPRequest
     */
    public function viewFile($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = array_shift($args); 
        
        if ($revision !== null) $revision = (int) $revision;

        $this->validate($request, $articleId);
        if (!ProofreaderAction::viewFile($articleId, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }
}
?>
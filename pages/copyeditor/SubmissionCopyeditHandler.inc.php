<?php
declare(strict_types=1);

/**
 * @file pages/copyeditor/SubmissionCopyeditHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCopyeditHandler
 * @ingroup pages_copyeditor
 *
 * @brief Handle requests for submission tracking.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.copyeditor.CopyeditorHandler');

class SubmissionCopyeditHandler extends CopyeditorHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubmissionCopyeditHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::SubmissionCopyeditHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Copyeditor's view of a submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function submission($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate($request, $articleId);

        $router = $request->getRouter();

        $submission = $this->submission;
        $this->setupTemplate(true, $articleId);

        CopyeditorAction::copyeditUnderway($submission, $request);

        $journal = $router->getContext($request);
        $useLayoutEditors = $journal->getSetting('useLayoutEditors');
        $metaCitations = $journal->getSetting('metaCitations');

        $templateMgr = TemplateManager::getManager();

        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('submission', $submission);
        $templateMgr->assign('copyeditor', $submission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
        $templateMgr->assign('initialCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL'));
        $templateMgr->assign('editorAuthorCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR'));
        $templateMgr->assign('finalCopyeditFile', $submission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL'));
        $templateMgr->assign('useLayoutEditors', $useLayoutEditors);
        $templateMgr->assign('metaCitations', $metaCitations);
        $templateMgr->assign('helpTopicId', 'editorial.copyeditorsRole.copyediting');
        $templateMgr->display('copyeditor/submission.tpl');
    }

    /**
     * Complete a copyedit.
     * @param array $args
     * @param PKPRequest $request
     */
    public function completeCopyedit($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId);

        // [SECURITY FIX] Terapkan (int) pada parameter 'send'
        if (CopyeditorAction::completeCopyedit($this->submission, (int) $request->getUserVar('send'), $request)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Complete a final copyedit.
     * @param array $args
     * @param PKPRequest $request
     */
    public function completeFinalCopyedit($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId);
        
        // [SECURITY FIX] Terapkan (int) pada parameter 'send'
        if (CopyeditorAction::completeFinalCopyedit($this->submission, (int) $request->getUserVar('send'), $request)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Upload a copyedit version
     * @param array $args
     * @param object $request
     */
    public function uploadCopyeditVersion($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $copyeditStage = (int) $request->getUserVar('copyeditStage');
        CopyeditorAction::uploadCopyeditVersion($this->submission, $copyeditStage, $request);

        $request->redirect(null, null, 'submission', $articleId);
    }

    //
    // Misc
    //

    /**
     * Download a file.
     * @param array $args ($articleId, $fileId, [$revision])
     * @param PKPRequest $request
     */
    public function downloadFile($args, $request) {
        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = array_shift($args); // Can be null

        $this->validate($request, $articleId);
        if (!CopyeditorAction::downloadCopyeditorFile($this->submission, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * View a file (inlines file).
     * @param array $args ($articleId, $fileId, [$revision])
     * @param PKPRequest $request
     */
    public function viewFile($args, $request) {
        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = array_shift($args); // May be null

        $this->validate($request, $articleId);
        if (!CopyeditorAction::viewFile($articleId, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    //
    // Proofreading
    //

    /**
     * Set the author proofreading date completion
     * @param array $args
     * @param PKPRequest $request
     */
    public function authorProofreadingComplete($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId);

        $send = (int) $request->getUserVar('send') === 1;

        import('classes.submission.proofreader.ProofreaderAction');

        if (ProofreaderAction::proofreadEmail($articleId, 'PROOFREAD_AUTHOR_COMPLETE', $request, $send ? '' : $request->url(null, 'copyeditor', 'authorProofreadingComplete', 'send'))) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Proof / "preview" a galley.
     * @param array $args ($articleId, $galleyId)
     * @param PKPRequest $request
     */
    public function proofGalley($args, $request) {
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
     * @param PKPRequest $request
     */
    public function proofGalleyTop($args, $request) {
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
     * @param PKPRequest $request
     */
    public function proofGalleyFile($args, $request) {
        $articleId = (int) array_shift($args);
        $galleyId = (int) array_shift($args);
        $this->validate($request, $articleId);

        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galley = $galleyDao->getGalley($galleyId, $articleId);

        import('classes.file.ArticleFileManager'); // FIXME

        if (isset($galley)) {
            if ($galley->isHTMLGalley()) {
                $templateMgr = TemplateManager::getManager();
                // [WIZDAM] Removed assign_by_ref
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
     * Metadata functions.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewMetadata($args, $request) {
        $articleId = (int) array_shift($args);
        $journal = $request->getJournal();
        $this->validate($request, $articleId);
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_AUTHOR);
        $submission = $this->submission;
        $this->setupTemplate(true, $articleId, 'editing');
        CopyeditorAction::viewMetadata($submission, $journal);
    }

    /**
     * Save modified metadata.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveMetadata($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId);

        if (CopyeditorAction::saveMetadata($this->submission, $request)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Remove cover page from article
     * @param array $args
     * @param PKPRequest $request
     */
    public function removeArticleCoverPage($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate($request, $articleId);

        $formLocale = array_shift($args);
        if (!AppLocale::isLocaleValid($formLocale)) {
            $request->redirect(null, null, 'viewMetadata', $articleId);
        }

        import('classes.submission.sectionEditor.SectionEditorAction');
        if (SectionEditorAction::removeArticleCoverPage($this->submission, $formLocale)) {
            $request->redirect(null, null, 'viewMetadata', $articleId);
        }
    }


    //
    // Citation Editing
    //
    /**
     * Display the citation editing assistant.
     * @param array $args
     * @param Request $request
     */
    public function submissionCitations($args, $request) {
        // Authorize the request.
        $articleId = (int) array_shift($args);
        $this->validate($request, $articleId);

        // Prepare the view.
        $this->setupTemplate(true, $articleId);

        // Insert the citation editing assistant into the view.
        CopyeditorAction::editCitations($request, $this->submission);

        // Render the view.
        $templateMgr = TemplateManager::getManager();
        $templateMgr->display('copyeditor/submissionCitations.tpl');
    }
}
?>
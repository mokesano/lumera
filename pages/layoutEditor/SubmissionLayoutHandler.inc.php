<?php
declare(strict_types=1);

/**
 * @file pages/layoutEditor/SubmissionLayoutHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionLayoutHandler
 * @ingroup pages_layoutEditor
 *
 * @brief Handle requests related to submission layout editing.
 */

import('pages.layoutEditor.LayoutEditorHandler');

class SubmissionLayoutHandler extends LayoutEditorHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubmissionLayoutHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Submission Management
    //

    /**
     * View an assigned submission's layout editing page.
     * @param array $args ($articleId)
     * @param mixed $request
     */
    public function submission(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $journal = $request->getJournal();

        $this->validate($request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate(true, $articleId);
        
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        import('classes.submission.proofreader.ProofreaderAction');
        ProofreaderAction::proofreadingUnderway($submission, 'SIGNOFF_PROOFREADING_LAYOUT');

        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);

        if ($layoutSignoff->getDateNotified() !== null && $layoutSignoff->getDateUnderway() === null) {
            // Set underway date
            $layoutSignoff->setDateUnderway(Core::getCurrentDate());
            $signoffDao->updateObject($layoutSignoff);
        }

        $disableEdit = !$this->_layoutEditingEnabled($submission);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('submission', $submission);
        $templateMgr->assign('disableEdit', $disableEdit);
        $templateMgr->assign('useProofreaders', $journal->getSetting('useProofreaders'));
        $templateMgr->assign('templates', $journal->getSetting('templates'));
        $templateMgr->assign('helpTopicId', 'editorial.layoutEditorsRole.layout');

        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($submission->getId());
        
        if ($publishedArticle) {
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($publishedArticle->getIssueId());
            $templateMgr->assign('publishedArticle', $publishedArticle);
            $templateMgr->assign('issue', $issue);
        }

        $templateMgr->display('layoutEditor/submission.tpl');
    }

    /**
     * View submission metadata.
     * @param array $args
     * @param mixed $request
     */
    public function viewMetadata(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $journal = $request->getJournal();
        
        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId, 'summary');
        
        LayoutEditorAction::viewMetadata($this->submission, $journal);
    }

    /**
     * Mark assignment as complete.
     * @param array $args
     * @param mixed $request
     */
    public function completeAssignment(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        $this->setupTemplate(true, $articleId, 'editing');
        $this->validate($request, $articleId);
        
        $send = (int) trim((string) $request->getUserVar('send'));
        
        if (LayoutEditorAction::completeLayoutEditing($this->submission, $send, $request)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    //
    // Galley Management
    //

    /**
     * Create a new layout file (layout version, galley, or supp file) with the uploaded file.
     * @param array $args
     * @param mixed $request
     */
    public function uploadLayoutFile(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        $this->validate($request, $articleId);
        $submission = $this->submission;

        $layoutFileType = trim((string) $request->getUserVar('layoutFileType'));
        
        switch ($layoutFileType) {
            case 'submission':
                LayoutEditorAction::uploadLayoutVersion($submission);
                $request->redirect(null, null, 'submission', $articleId);
                break;
            case 'galley':
                import('classes.submission.form.ArticleGalleyForm');

                $galleyForm = new ArticleGalleyForm($articleId);
                $galleyId = $galleyForm->execute('layoutFile');

                $request->redirect(null, null, 'editGalley', [$articleId, $galleyId]);
                break;
            case 'supp':
                import('classes.submission.form.SuppFileForm');
                $journal = $request->getJournal();
                $suppFileForm = new SuppFileForm($submission, $journal);
                $suppFileForm->setData('title', [$submission->getLocale() => __('common.untitled')]);
                $suppFileId = $suppFileForm->execute('layoutFile');

                $request->redirect(null, null, 'editSuppFile', [$articleId, $suppFileId]);
                break;
            default:
                // Invalid upload type.
                $request->redirect(null, 'layoutEditor');
        }
    }

    /**
     * Edit a galley.
     * @param array $args ($articleId, $galleyId)
     * @param mixed $request
     */
    public function editGalley(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $galleyId = (int) ($args[1] ?? 0);
        
        $this->validate($request, $articleId);
        $submission = $this->submission;

        $this->setupTemplate(true, $articleId, 'editing');

        if ($this->_layoutEditingEnabled($submission)) {
            import('classes.submission.form.ArticleGalleyForm');

            $submitForm = new ArticleGalleyForm($articleId, $galleyId);

            if ($submitForm->isLocaleResubmit()) {
                $submitForm->readInputData();
            } else {
                $submitForm->initData();
            }
            $submitForm->display();

        } else {
            // View galley only
            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
            $galley = $galleyDao->getGalley($galleyId, $articleId);

            if (!isset($galley)) {
                $request->redirect(null, null, 'submission', $articleId);
                return;
            }

            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('articleId', $articleId);
            $templateMgr->assign('galley', $galley);
            $templateMgr->display('submission/layout/galleyView.tpl');
        }
    }

    /**
     * Save changes to a galley.
     * @param array $args ($articleId, $galleyId)
     * @param mixed $request
     */
    public function saveGalley(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $galleyId = (int) ($args[1] ?? 0);
        
        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId, 'editing');

        import('classes.submission.form.ArticleGalleyForm');

        $submitForm = new ArticleGalleyForm($articleId, $galleyId);
        $submitForm->readInputData();

        if ($submitForm->validate()) {
            $submitForm->execute();

            // Send a notification to associated users
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($articleId);
            $notificationUsers = $article->getAssociatedUserIds(true, false);
            
            foreach ($notificationUsers as $userRole) {
                $notificationManager->createNotification(
                    $request, 
                    $userRole['id'], 
                    NOTIFICATION_TYPE_GALLEY_MODIFIED,
                    $article->getJournalId(), 
                    ASSOC_TYPE_ARTICLE, 
                    $article->getId()
                );
            }

            $uploadImage = (int) trim((string) $request->getUserVar('uploadImage'));
            
            if ($uploadImage) {
                $submitForm->uploadImage();
                $request->redirect(null, null, 'editGalley', [$articleId, $galleyId]);
            } else {
                $deleteImage = $request->getUserVar('deleteImage');
                
                // Pastikan input adalah array, memiliki tepat 1 elemen, dan kuncinya adalah integer (imageId)
                if (is_array($deleteImage) && count($deleteImage) === 1) {
                    $imageId = (int) trim((string) key($deleteImage)); // Amankan key sebagai ID
                    if ($imageId > 0) {
                        $submitForm->deleteImage($imageId);
                        $request->redirect(null, null, 'editGalley', [$articleId, $galleyId]);
                    }
                }
            }
            $request->redirect(null, null, 'submission', $articleId);
        } else {
            $submitForm->display();
        }
    }

    /**
     * Delete a galley file.
     * @param array $args ($articleId, $galleyId)
     * @param mixed $request
     */
    public function deleteGalley(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $galleyId = (int) ($args[1] ?? 0);
        
        $this->validate($request, $articleId);
        $submission = $this->submission;

        LayoutEditorAction::deleteGalley($submission, $galleyId);

        $request->redirect(null, null, 'submission', $articleId);
    }

    /**
     * Change the sequence order of a galley.
     * @param array $args
     * @param mixed $request
     */
    public function orderGalley(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        $this->validate($request, $articleId);
        
        $submission = $this->submission;

        $galleyId = (int) trim((string) $request->getUserVar('galleyId'));
        $direction = (int) trim((string) $request->getUserVar('d')); 
        
        LayoutEditorAction::orderGalley($submission, $galleyId, $direction);

        $request->redirect(null, null, 'submission', $articleId);
    }

    /**
     * Proof / "preview" a galley.
     * @param array $args ($articleId, $galleyId)
     * @param mixed $request
     */
    public function proofGalley(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $galleyId = (int) ($args[1] ?? 0);
        $this->validate($request, $articleId);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->display('submission/layout/proofGalley.tpl');
    }

    /**
     * Proof galley (shows frame header).
     * @param array $args ($articleId, $galleyId)
     * @param mixed $request
     */
    public function proofGalleyTop(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $galleyId = (int) ($args[1] ?? 0);
        $this->validate($request, $articleId);

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('backHandler', 'submissionEditing');
        $templateMgr->display('submission/layout/proofGalleyTop.tpl');
    }

    /**
     * Proof galley (outputs file contents).
     * @param array $args ($articleId, $galleyId)
     * @param mixed $request
     */
    public function proofGalleyFile(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $galleyId = (int) ($args[1] ?? 0);
        $this->validate($request, $articleId);

        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galley = $galleyDao->getGalley($galleyId, $articleId);

        import('classes.file.ArticleFileManager'); // FIXME

        if (isset($galley)) {
            if ($galley->isHTMLGalley()) {
                $templateMgr = TemplateManager::getManager($request);
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
     * Delete an article image.
     * @param array $args ($articleId, $fileId)
     * @param mixed $request
     */
    public function deleteArticleImage(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $galleyId = (int) ($args[1] ?? 0);
        $fileId = (int) ($args[2] ?? 0);
        $revisionId = (int) ($args[3] ?? 0);
        
        $this->validate($request, $articleId);
        LayoutEditorAction::deleteArticleImage($this->submission, $fileId, $revisionId);

        $request->redirect(null, null, 'editGalley', [$articleId, $galleyId]);
    }

    //
    // Supplementary File Management
    //

    /**
     * Edit a supplementary file.
     * @param array $args ($articleId, $suppFileId)
     * @param mixed $request
     */
    public function editSuppFile(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $suppFileId = (int) ($args[1] ?? 0);
        $journal = $request->getJournal();

        $this->validate($request, $articleId);
        $submission = $this->submission;

        $this->setupTemplate(true, $articleId, 'editing');

        if ($this->_layoutEditingEnabled($submission)) {
            import('classes.submission.form.SuppFileForm');

            $submitForm = new SuppFileForm($submission, $journal, $suppFileId);

            if ($submitForm->isLocaleResubmit()) {
                $submitForm->readInputData();
            } else {
                $submitForm->initData();
            }
            $submitForm->display();
        } else {
            // View supplementary file only
            $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
            $suppFile = $suppFileDao->getSuppFile($suppFileId, $articleId);

            if (!isset($suppFile)) {
                $request->redirect(null, null, 'submission', $articleId);
                return;
            }

            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('articleId', $articleId);
            $templateMgr->assign('suppFile', $suppFile);
            $templateMgr->display('submission/suppFile/suppFileView.tpl');
        }
    }

    /**
     * Save a supplementary file.
     * @param array $args ($suppFileId)
     * @param mixed $request
     */
    public function saveSuppFile(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        $this->validate($request, $articleId);
        $submission = $this->submission;
        $this->setupTemplate(true, $articleId, 'editing');

        $suppFileId = (int) ($args[0] ?? 0);
        $journal = $request->getJournal();

        import('classes.submission.form.SuppFileForm');

        $submitForm = new SuppFileForm($submission, $journal, $suppFileId);
        $submitForm->readInputData();

        if ($submitForm->validate()) {
            $submitForm->execute();

            // Send a notification to associated users
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($articleId);
            $notificationUsers = $article->getAssociatedUserIds(true, false);
            
            foreach ($notificationUsers as $userRole) {
                $notificationManager->createNotification(
                    $request, 
                    $userRole['id'], 
                    NOTIFICATION_TYPE_SUPP_FILE_MODIFIED,
                    $article->getJournalId(), 
                    ASSOC_TYPE_ARTICLE, 
                    $article->getId()
                );
            }

            $request->redirect(null, null, 'submission', $articleId);
        } else {
            $submitForm->display();
        }
    }

    /**
     * Delete a supplementary file.
     * @param array $args ($articleId, $suppFileId)
     * @param mixed $request
     */
    public function deleteSuppFile(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $suppFileId = (int) ($args[1] ?? 0);
        
        $this->validate($request, $articleId);
        LayoutEditorAction::deleteSuppFile($this->submission, $suppFileId);
        $request->redirect(null, null, 'submission', $articleId);
    }

    /**
     * Change the sequence order of a supplementary file.
     * @param array $args
     * @param mixed $request
     */
    public function orderSuppFile(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        $this->validate($request, $articleId);
        
        $suppFileId = (int) trim((string) $request->getUserVar('suppFileId'));
        $direction = (int) trim((string) $request->getUserVar('d')); 
        
        LayoutEditorAction::orderSuppFile($this->submission, $suppFileId, $direction);

        $request->redirect(null, null, 'submission', $articleId);
    }

    //
    // File Access
    //

    /**
     * Download a file.
     * @param array $args ($articleId, $fileId, [$revision])
     * @param mixed $request
     */
    public function downloadFile(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $fileId = (int) ($args[1] ?? 0);
        $revision = isset($args[2]) ? (int) $args[2] : null;

        $this->validate($request, $articleId);
        $submission = $this->submission;
        
        if (!LayoutEditorAction::downloadFile($submission, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * View a file (inlines file).
     * @param array $args ($articleId, $fileId, [$revision])
     * @param mixed $request
     */
    public function viewFile(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $fileId = (int) ($args[1] ?? 0);
        $revision = isset($args[2]) ? (int) $args[2] : null;

        $this->validate($request, $articleId);
        
        if (!LayoutEditorAction::viewFile($articleId, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    //
    // Proofreading
    //

    /**
     * Sets the date of layout editor proofreading completion
     * @param array $args
     * @param mixed $request
     */
    public function layoutEditorProofreadingComplete(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) trim((string) $request->getUserVar('articleId'));

        // Validate sets $this->submission
        $this->validate($request, $articleId);
        $this->setupTemplate(true, $articleId);

        $send = isset($args[0]) ? (int) trim((string) $request->getUserVar('send')) : false;

        import('classes.submission.proofreader.ProofreaderAction');
        
        $url = $send ? '' : $request->url(null, 'layoutEditor', 'layoutEditorProofreadingComplete', 'send');

        if (ProofreaderAction::proofreadEmail($articleId, 'PROOFREAD_LAYOUT_COMPLETE', $request, $url)) {
            $request->redirect(null, null, 'submission', $articleId);
        }
    }

    /**
     * Download a layout template.
     * @param array $args
     * @param mixed $request
     */
    public function downloadLayoutTemplate(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        parent::validate($request);
        $journal = $request->getJournal();
        $templates = $journal->getSetting('templates');
        
        import('classes.file.JournalFileManager');
        $journalFileManager = new JournalFileManager($journal);
        
        $templateId = (int) ($args[0] ?? -1);
        
        if ($templateId >= count($templates) || $templateId < 0) {
            $request->redirect(null, 'layoutEditor');
            return;
        }
        
        $template = $templates[$templateId];

        $filename = "template-$templateId." . $journalFileManager->parseFileExtension($template['originalFilename']);
        $journalFileManager->downloadFile($filename, $template['fileType']);
    }
}

?>
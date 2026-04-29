<?php
declare(strict_types=1);

/**
 * @file classes/submission/copyeditor/CopyeditorAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditorAction
 * @ingroup submission
 * @see CopyeditorSubmissionDAO
 *
 * @brief CopyeditorAction class.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & HookRegistry::dispatch
 */

import('classes.submission.common.Action');

class CopyeditorAction extends Action {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CopyeditorAction() {
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
     * Actions.
     */

    /**
     * Copyeditor completes initial copyedit.
     * @param object $copyeditorSubmission
     * @param boolean $send
     * @param object $request PKPRequest
     * @return boolean
     */
    public static function completeCopyedit($copyeditorSubmission, $send, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        
        $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();

        $initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
        if ($initialSignoff->getDateCompleted() != null) {
            return true;
        }

        $user = $request->getUser();
        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($copyeditorSubmission, 'COPYEDIT_COMPLETE');

        $editAssignments = $copyeditorSubmission->getEditAssignments();

        $author = $copyeditorSubmission->getUser();

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            // [WIZDAM] HookRegistry::dispatch
            HookRegistry::dispatch('CopyeditorAction::completeCopyedit', [&$copyeditorSubmission, &$editAssignments, &$author, &$email]);
            
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $initialSignoff->setDateCompleted(Core::getCurrentDate());

            $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
            $authorSignoff->setUserId($author->getId());
            $authorSignoff->setDateNotified(Core::getCurrentDate());
            $signoffDao->updateObject($initialSignoff);
            $signoffDao->updateObject($authorSignoff);

            // Add log entry
            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $copyeditorSubmission, ARTICLE_LOG_COPYEDIT_INITIAL, 'log.copyedit.initialEditComplete', ['copyeditorName' => $user->getFullName()]);

            return true;

        } else {
            if (!$request->getUserVar('continued')) {
                $email->addRecipient($author->getEmail(), $author->getFullName());
                $email->ccAssignedEditingSectionEditors($copyeditorSubmission->getId());
                $email->ccAssignedEditors($copyeditorSubmission->getId());

                $paramArray = [
                    'editorialContactName' => $author->getFullName(),
                    'copyeditorName' => $user->getFullName(),
                    'authorUsername' => $author->getUsername(),
                    'submissionEditingUrl' => $request->url(null, 'author', 'submissionEditing', [$copyeditorSubmission->getId()])
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, 'copyeditor', 'completeCopyedit', 'send'), ['articleId' => $copyeditorSubmission->getId()]);

            return false;
        }
    }

    /**
     * Copyeditor completes final copyedit.
     * @param object $copyeditorSubmission
     * @param boolean $send
     * @param object $request PKPRequest
     * @return boolean
     */
    public static function completeFinalCopyedit($copyeditorSubmission, $send, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();

        $finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
        if ($finalSignoff->getDateCompleted() != null) {
            return true;
        }

        $user = $request->getUser();
        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($copyeditorSubmission, 'COPYEDIT_FINAL_COMPLETE');

        $editAssignments = $copyeditorSubmission->getEditAssignments();

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            // [WIZDAM] HookRegistry::dispatch
            HookRegistry::dispatch('CopyeditorAction::completeFinalCopyedit', [&$copyeditorSubmission, &$editAssignments, &$email]);
            
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $finalSignoff->setDateCompleted(Core::getCurrentDate());
            $signoffDao->updateObject($finalSignoff);

            if ($copyEdFile = $copyeditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL')) {
                // Set initial layout version to final copyedit version
                $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());

                if (!$layoutSignoff->getFileId()) {
                    import('classes.file.ArticleFileManager');
                    $articleFileManager = new ArticleFileManager($copyeditorSubmission->getId());
                    if ($layoutFileId = $articleFileManager->copyToLayoutFile($copyEdFile->getFileId(), $copyEdFile->getRevision())) {
                        $layoutSignoff->setFileId($layoutFileId);
                        $signoffDao->updateObject($layoutSignoff);
                    }
                }
            }

            // Add log entry
            import('classes.article.log.ArticleLog');
            import('classes.article.log.ArticleEventLogEntry');
            ArticleLog::logEvent($request, $copyeditorSubmission, ARTICLE_LOG_COPYEDIT_FINAL, 'log.copyedit.finalEditComplete', ['copyeditorName' => $user->getFullName()]);

            return true;

        } else {
            if (!$request->getUserVar('continued')) {
                $assignedSectionEditors = $email->toAssignedEditingSectionEditors($copyeditorSubmission->getId());
                $assignedEditors = $email->ccAssignedEditors($copyeditorSubmission->getId());
                
                if (empty($assignedSectionEditors) && empty($assignedEditors)) {
                    $email->addRecipient($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                    $paramArray = [
                        'editorialContactName' => $journal->getSetting('contactName'),
                        'copyeditorName' => $user->getFullName()
                    ];
                } else {
                    $editorialContact = array_shift($assignedSectionEditors);
                    if (!$editorialContact) $editorialContact = array_shift($assignedEditors);

                    $paramArray = [
                        'editorialContactName' => $editorialContact->getEditorFullName(),
                        'copyeditorName' => $user->getFullName()
                    ];
                }
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, 'copyeditor', 'completeFinalCopyedit', 'send'), ['articleId' => $copyeditorSubmission->getId()]);

            return false;
        }
    }

    /**
     * Set that the copyedit is underway.
     * @param object $copyeditorSubmission
     * @param object $request PKPRequest
     */
    public static function copyeditUnderway($copyeditorSubmission, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [WIZDAM] HookRegistry::dispatch. $copyeditorSubmission passed by reference in array for hook modification.
        if (!HookRegistry::dispatch('CopyeditorAction::copyeditUnderway', [&$copyeditorSubmission])) {
            $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
            $signoffDao = DAORegistry::getDAO('SignoffDAO');

            $initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
            $finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());

            $update = false;
            if ($initialSignoff->getDateNotified() != null && $initialSignoff->getDateUnderway() == null) {
                $initialSignoff->setDateUnderway(Core::getCurrentDate());
                $signoffDao->updateObject($initialSignoff);
                $update = true;

            } elseif ($finalSignoff->getDateNotified() != null && $finalSignoff->getDateUnderway() == null) {
                $finalSignoff->setDateUnderway(Core::getCurrentDate());
                $signoffDao->updateObject($finalSignoff);
                $update = true;
            }

            if ($update) {
                // Add log entry
                $user = $request->getUser();
                import('classes.article.log.ArticleLog');
                ArticleLog::logEvent($request, $copyeditorSubmission, ARTICLE_LOG_COPYEDIT_INITIATE, 'log.copyedit.initiate', ['copyeditorName' => $user->getFullName()]);
            }
        }
    }

    /**
     * Upload the copyedited version of an article.
     * @param object $copyeditorSubmission
     * @param string $copyeditStage
     * @param object $request PKPRequest
     */
    public static function uploadCopyeditVersion($copyeditorSubmission, $copyeditStage, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        import('classes.file.ArticleFileManager');
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        if($copyeditStage == 'initial') {
            $signoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
        } elseif($copyeditStage == 'final') {
            $signoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
        } else {
            return;
        }

        // Only allow an upload if they're in the initial or final copyediting
        // stages.
        if ($copyeditStage == 'initial' && ($signoff->getDateNotified() == null || $signoff->getDateCompleted() != null)) return;
        if ($copyeditStage == 'final' && ($signoff->getDateNotified() == null || $signoff->getDateCompleted() != null)) return;

        $articleFileManager = new ArticleFileManager($copyeditorSubmission->getId());
        $user = $request->getUser();

        $fileName = 'upload';
        $fileId = 0;
        
        if ($articleFileManager->uploadedFileExists($fileName)) {
            // [WIZDAM] HookRegistry::dispatch
            HookRegistry::dispatch('CopyeditorAction::uploadCopyeditVersion', [&$copyeditorSubmission]);
            if ($signoff->getFileId() != null) {
                $fileId = $articleFileManager->uploadCopyeditFile($fileName, $copyeditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true));
            } else {
                $fileId = $articleFileManager->uploadCopyeditFile($fileName);
            }
        }

        if (isset($fileId) && $fileId != 0) {
            $signoff->setFileId($fileId);
            $signoff->setFileRevision($articleFileDao->getRevisionNumber($fileId));
            $signoffDao->updateObject($signoff);

            // Add log
            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $copyeditorSubmission, ARTICLE_LOG_COPYEDIT_COPYEDITOR_FILE, 'log.copyedit.copyeditorFile', ['copyeditorName' => $user->getFullName(), 'fileId' => $fileId]);
        }
    }

    //
    // Comments
    //

    /**
     * View layout comments.
     * @param object $article
     */
    public static function viewLayoutComments($article) {
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('CopyeditorAction::viewLayoutComments', [&$article])) {
            import('classes.submission.form.comment.LayoutCommentForm');

            $commentForm = new LayoutCommentForm($article, ROLE_ID_COPYEDITOR);
            $commentForm->initData();
            $commentForm->display();
        }
    }

    /**
     * Post layout comment.
     * @param object $article
     * @param boolean $emailComment
     * @param object $request PKPRequest
     */
    public static function postLayoutComment($article, $emailComment, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('CopyeditorAction::postLayoutComment', [&$article, &$emailComment])) {
            import('classes.submission.form.comment.LayoutCommentForm');

            $commentForm = new LayoutCommentForm($article, ROLE_ID_COPYEDITOR);
            $commentForm->readInputData();

            if ($commentForm->validate()) {
                $commentForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationUsers = $article->getAssociatedUserIds(true, false);
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, $userRole['id'], NOTIFICATION_TYPE_LAYOUT_COMMENT,
                        $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                    );
                }

                if ($emailComment) {
                    $commentForm->email($request);
                }

            } else {
                $commentForm->display();
                return false;
            }
            return true;
        }
    }

    /**
     * View copyedit comments.
     * @param object $article
     */
    public static function viewCopyeditComments($article) {
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('CopyeditorAction::viewCopyeditComments', [&$article])) {
            import('classes.submission.form.comment.CopyeditCommentForm');

            $commentForm = new CopyeditCommentForm($article, ROLE_ID_COPYEDITOR);
            $commentForm->initData();
            $commentForm->display();
        }
    }

    /**
     * Post copyedit comment.
     * @param object $article
     * @param boolean $emailComment
     * @param object $request PKPRequest
     */
    public static function postCopyeditComment($article, $emailComment, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('CopyeditorAction::postCopyeditComment', [&$article, &$emailComment])) {
            import('classes.submission.form.comment.CopyeditCommentForm');

            $commentForm = new CopyeditCommentForm($article, ROLE_ID_COPYEDITOR);
            $commentForm->readInputData();

            if ($commentForm->validate()) {
                $commentForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationUsers = $article->getAssociatedUserIds(true, false);
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, $userRole['id'], NOTIFICATION_TYPE_COPYEDIT_COMMENT,
                        $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                    );
                }

                if ($emailComment) {
                    $commentForm->email($request);
                }

            } else {
                $commentForm->display();
                return false;
            }
            return true;
        }
    }

    //
    // Misc
    //

    /**
     * Download a file a copyeditor has access to.
     * @param object $copyeditorSubmission
     * @param int $fileId
     * @param int|null $revision
     */
    public static function downloadCopyeditorFile($copyeditorSubmission, $fileId, $revision = null) {
        $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');

        $canDownload = false;

        // Copyeditors have access to:
        // 1) The first revision of the copyedit file
        // 2) The initial copyedit revision
        // 3) The author copyedit revision, after the author copyedit has been completed
        // 4) The final copyedit revision
        // 5) Layout galleys
        if ($copyeditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true) == $fileId) {
            $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
            $signoffDao = DAORegistry::getDAO('SignoffDAO');
            $currentRevision = $articleFileDao->getRevisionNumber($fileId);

            if ($revision == null) {
                $revision = $currentRevision;
            }

            $initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
            $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
            $finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());

            if ($revision == 1) {
                $canDownload = true;
            } elseif ($initialSignoff->getFileRevision() == $revision) {
                $canDownload = true;
            } elseif ($finalSignoff->getFileRevision() == $revision) {
                $canDownload = true;
            }
        } elseif ($copyeditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR', true) == $fileId) {
            $signoffDao = DAORegistry::getDAO('SignoffDAO');
            $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $copyeditorSubmission->getId());
            if($authorSignoff->getDateCompleted() != null) {
                $canDownload = true;
            }
        } elseif ($copyeditorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_FINAL', true) == $fileId) {
            $canDownload = true;
        }
        else {
            // Check galley files
            // [WIZDAM] Null Coalescing
            $galleys = $copyeditorSubmission->getGalleys() ?? [];
            foreach ($galleys as $galleyFile) {
                if ($galleyFile->getFileId() == $fileId) {
                    $canDownload = true;
                }
            }
            // Check supp files
            // [WIZDAM] Null Coalescing
            $suppFiles = $copyeditorSubmission->getSuppFiles() ?? [];
            foreach ($suppFiles as $suppFile) {
                if ($suppFile->getFileId() == $fileId) {
                    $canDownload = true;
                }
            }
        }

        $result = false;
        // [WIZDAM] HookRegistry::dispatch
        if (!HookRegistry::dispatch('CopyeditorAction::downloadCopyeditorFile', [&$copyeditorSubmission, &$fileId, &$revision, &$result])) {
            if ($canDownload) {
                return Action::downloadFile($copyeditorSubmission->getId(), $fileId, $revision);
            } else {
                return false;
            }
        }

        return $result;
    }
}
?>
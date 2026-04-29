<?php
declare(strict_types=1);

/**
 * @file classes/submission/author/AuthorAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorAction
 * @ingroup submission
 *
 * @brief AuthorAction class.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & HookRegistry::dispatch
 */

import('classes.submission.common.Action');

class AuthorAction extends Action {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorAction() {
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
     * Designates the original file the review version.
     * @param object $authorSubmission
     * @param boolean $designate
     */
    public static function designateReviewVersion($authorSubmission, $designate = false) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($authorSubmission->getId());
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');

        if ($designate && !HookRegistry::dispatch('AuthorAction::designateReviewVersion', [&$authorSubmission])) {
            $submissionFile = $authorSubmission->getSubmissionFile();
            if ($submissionFile) {
                $reviewFileId = $articleFileManager->copyToReviewFile($submissionFile->getFileId());

                $authorSubmission->setReviewFileId($reviewFileId);

                $authorSubmissionDao->updateAuthorSubmission($authorSubmission);

                $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
                $sectionEditorSubmissionDao->createReviewRound($authorSubmission->getId(), 1, 1);
            }
        }
    }

    /**
     * Delete an author file from a submission.
     * @param object $article
     * @param int $fileId
     * @param int $revisionId
     */
    public static function deleteArticleFile($article, $fileId, $revisionId) {
        import('classes.file.ArticleFileManager');

        $articleFileManager = new ArticleFileManager($article->getId());
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');

        $articleFile = $articleFileDao->getArticleFile($fileId, $revisionId, $article->getId());
        $authorSubmission = $authorSubmissionDao->getAuthorSubmission($article->getId());
        $authorRevisions = $authorSubmission->getAuthorFileRevisions();

        // Ensure that this is actually an author file.
        if (isset($articleFile)) {
            HookRegistry::dispatch('AuthorAction::deleteArticleFile', [&$articleFile, &$authorRevisions]);
            foreach ($authorRevisions as $round) {
                foreach ($round as $revision) {
                    if ($revision->getFileId() == $articleFile->getFileId() &&
                        $revision->getRevision() == $articleFile->getRevision()) {
                        $articleFileManager->deleteFile($articleFile->getFileId(), $articleFile->getRevision());
                    }
                }
            }
        }
    }

    /**
     * Upload the revised version of an article.
     * @param object $authorSubmission
     * @param object $request PKPRequest
     */
    public static function uploadRevisedVersion($authorSubmission, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($authorSubmission->getId());
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');

        $fileName = 'upload';
        $fileId = 0;
        if ($articleFileManager->uploadedFileExists($fileName)) {
            HookRegistry::dispatch('AuthorAction::uploadRevisedVersion', [&$authorSubmission]);
            if ($authorSubmission->getRevisedFileId() != null) {
                $fileId = $articleFileManager->uploadEditorDecisionFile($fileName, $authorSubmission->getRevisedFileId());
            } else {
                $fileId = $articleFileManager->uploadEditorDecisionFile($fileName);
            }
        }

        if (isset($fileId) && $fileId != 0) {
            $authorSubmission->setRevisedFileId($fileId);

            $authorSubmissionDao->updateAuthorSubmission($authorSubmission);

            $user = $request->getUser();
            $journal = $request->getJournal();
            import('classes.mail.ArticleMailTemplate');
            $email = new ArticleMailTemplate($authorSubmission, 'REVISED_VERSION_NOTIFY', null, null, null, false);
            if ($email->isEnabled()) {
                $isEditor = false;
                $assignedSectionEditors = $email->toAssignedEditingSectionEditors($authorSubmission->getId());
                $editor = array_shift($assignedSectionEditors);
                if (!$editor) {
                    $isEditor = true;
                    $assignedEditors = $email->toAssignedEditors($authorSubmission->getId());
                    $editor = array_shift($assignedEditors);
                }
                if (!$editor) {
                    $email->addRecipient($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                    $editorName = $journal->getSetting('contactName');
                } else {
                    $editorName = $editor->getEditorFullName();
                }
                
                $paramArray = [
                    'editorialContactName' => $editorName,
                    'articleTitle' => $authorSubmission->getLocalizedTitle(),
                    'authorName' => $user->getFullName(),
                    'submissionUrl' => $request->url(null, $isEditor ? 'editor' : 'sectionEditor', 'submissionReview', $authorSubmission->getId()),
                    'editorialContactSignature' => $journal->getSetting('contactName') . "\n" . $journal->getLocalizedTitle()
                ];
                $email->assignParams($paramArray);
                $email->send($request);
            }
            // Add log entry
            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $authorSubmission, ARTICLE_LOG_AUTHOR_REVISION, 'log.author.documentRevised', ['authorName' => $user->getFullName(), 'fileId' => $fileId]);
        }
    }

    /**
     * Author completes editor / author review.
     * @param object $authorSubmission
     * @param boolean $send
     * @param object $request PKPRequest
     * @return boolean
     */
    public static function completeAuthorCopyedit($authorSubmission, $send, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = $request->getJournal();

        $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $authorSubmission->getId());
        if ($authorSignoff->getDateCompleted() != null) {
            return true;
        }

        $user = $request->getUser();
        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($authorSubmission, 'COPYEDIT_AUTHOR_COMPLETE');

        $editAssignments = $authorSubmission->getEditAssignments();

        $copyeditor = $authorSubmission->getUserBySignoffType('SIGNOFF_COPYEDITING_INITIAL');

        if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
            HookRegistry::dispatch('AuthorAction::completeAuthorCopyedit', [&$authorSubmission, &$email]);
            if ($email->isEnabled()) {
                $email->send($request);
            }

            $authorSignoff->setDateCompleted(Core::getCurrentDate());

            $finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $authorSubmission->getId());
            if ($copyeditor) $finalSignoff->setUserId($copyeditor->getId());
            $finalSignoff->setDateNotified(Core::getCurrentDate());

            $signoffDao->updateObject($authorSignoff);
            $signoffDao->updateObject($finalSignoff);

            // Add log entry
            import('classes.article.log.ArticleLog');
            ArticleLog::logEvent($request, $authorSubmission, ARTICLE_LOG_COPYEDIT_REVISION, 'log.copyedit.authorFile');

            return true;
        } else {
            if (!$request->getUserVar('continued')) {
                if (isset($copyeditor)) {
                    $email->addRecipient($copyeditor->getEmail(), $copyeditor->getFullName());
                    $assignedSectionEditors = $email->ccAssignedEditingSectionEditors($authorSubmission->getId());
                    $assignedEditors = $email->ccAssignedEditors($authorSubmission->getId());
                    if (empty($assignedSectionEditors) && empty($assignedEditors)) {
                        $email->addCc($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                        $editorName = $journal->getSetting('contactName');
                    } else {
                        $editor = array_shift($assignedSectionEditors);
                        if (!$editor) $editor = array_shift($assignedEditors);
                        $editorName = $editor->getEditorFullName();
                    }
                } else {
                    $assignedSectionEditors = $email->toAssignedEditingSectionEditors($authorSubmission->getId());
                    $assignedEditors = $email->ccAssignedEditors($authorSubmission->getId());
                    if (empty($assignedSectionEditors) && empty($assignedEditors)) {
                        $email->addRecipient($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                        $editorName = $journal->getSetting('contactName');
                    } else {
                        $editor = array_shift($assignedSectionEditors);
                        if (!$editor) $editor = array_shift($assignedEditors);
                        $editorName = $editor->getEditorFullName();
                    }
                }

                $paramArray = [
                    'editorialContactName' => isset($copyeditor) ? $copyeditor->getFullName() : $editorName,
                    'authorName' => $user->getFullName()
                ];
                $email->assignParams($paramArray);
            }
            $email->displayEditForm($request->url(null, 'author', 'completeAuthorCopyedit', 'send'), ['articleId' => $authorSubmission->getId()]);

            return false;
        }
    }

    /**
     * Set that the copyedit is underway.
     * @param object $authorSubmission
     */
    public static function copyeditUnderway($authorSubmission) {
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $authorSubmission->getId());
        if ($authorSignoff->getDateNotified() != null && $authorSignoff->getDateUnderway() == null) {
            HookRegistry::dispatch('AuthorAction::copyeditUnderway', [&$authorSubmission]);
            $authorSignoff->setDateUnderway(Core::getCurrentDate());
            $signoffDao->updateObject($authorSignoff);
        }
    }

    /**
     * Upload the revised version of a copyedit file.
     * @param object $authorSubmission
     * @param string $copyeditStage
     */
    public static function uploadCopyeditVersion($authorSubmission, $copyeditStage) {
        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager($authorSubmission->getId());
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $signoffDao = DAORegistry::getDAO('SignoffDAO');

        // Authors cannot upload if the assignment is not active, i.e.
        // they haven't been notified or the assignment is already complete.
        $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $authorSubmission->getId());
        if (!$authorSignoff->getDateNotified() || $authorSignoff->getDateCompleted()) return;

        $fileName = 'upload';
        $fileId = 0;
        if ($articleFileManager->uploadedFileExists($fileName)) {
            HookRegistry::dispatch('AuthorAction::uploadCopyeditVersion', [&$authorSubmission, &$copyeditStage]);
            if ($authorSignoff->getFileId() != null) {
                $fileId = $articleFileManager->uploadCopyeditFile($fileName, $authorSignoff->getFileId());
            } else {
                $fileId = $articleFileManager->uploadCopyeditFile($fileName);
            }
        }

        $authorSignoff->setFileId($fileId);

        if ($copyeditStage == 'author') {
            $authorSignoff->setFileRevision($articleFileDao->getRevisionNumber($fileId));
        }

        $signoffDao->updateObject($authorSignoff);
    }

    //
    // Comments
    //

    /**
     * View layout comments.
     * @param object $article
     */
    public static function viewLayoutComments($article) {
        if (!HookRegistry::dispatch('AuthorAction::viewLayoutComments', [&$article])) {
            import('classes.submission.form.comment.LayoutCommentForm');
            $commentForm = new LayoutCommentForm($article, ROLE_ID_EDITOR);
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

        if (!HookRegistry::dispatch('AuthorAction::postLayoutComment', [&$article, &$emailComment])) {
            import('classes.submission.form.comment.LayoutCommentForm');

            $commentForm = new LayoutCommentForm($article, ROLE_ID_AUTHOR);
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
     * View editor decision comments.
     * @param object $article
     */
    public static function viewEditorDecisionComments($article) {
        if (!HookRegistry::dispatch('AuthorAction::viewEditorDecisionComments', [&$article])) {
            import('classes.submission.form.comment.EditorDecisionCommentForm');

            $commentForm = new EditorDecisionCommentForm($article, ROLE_ID_AUTHOR);
            $commentForm->initData();
            $commentForm->display();
        }
    }

    /**
     * Email editor decision comment.
     * @param object $authorSubmission
     * @param boolean $send
     * @param object $request PKPRequest
     */
    public static function emailEditorDecisionComment($authorSubmission, $send, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($authorSubmission);

        $editAssignments = $authorSubmission->getEditAssignments();
        $editors = [];
        foreach ($editAssignments as $editAssignment) {
            $editors[] = $userDao->getById($editAssignment->getEditorId());
        }

        if ($send && !$email->hasErrors()) {
            HookRegistry::dispatch('AuthorAction::emailEditorDecisionComment', [&$authorSubmission, &$email]);
            $email->send($request);

            $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
            $articleComment = new ArticleComment();
            $articleComment->setCommentType(COMMENT_TYPE_EDITOR_DECISION);
            $articleComment->setRoleId(ROLE_ID_AUTHOR);
            $articleComment->setArticleId($authorSubmission->getId());
            $articleComment->setAuthorId($authorSubmission->getUserId());
            $articleComment->setCommentTitle($email->getSubject());
            $articleComment->setComments($email->getBody());
            $articleComment->setDatePosted(Core::getCurrentDate());
            $articleComment->setViewable(true);
            $articleComment->setAssocId($authorSubmission->getId());
            $articleCommentDao->insertArticleComment($articleComment);

            return true;
        } else {
            if (!$request->getUserVar('continued')) {
                $email->setSubject($authorSubmission->getLocalizedTitle());
                if (!empty($editors)) {
                    foreach ($editors as $editor) {
                        $email->addRecipient($editor->getEmail(), $editor->getFullName());
                    }
                } else {
                    $email->addRecipient($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                }
            }

            $email->displayEditForm($request->url(null, null, 'emailEditorDecisionComment', 'send'), ['articleId' => $authorSubmission->getId()], 'submission/comment/editorDecisionEmail.tpl');

            return false;
        }
    }

    /**
     * View copyedit comments.
     * @param object $article
     */
    public static function viewCopyeditComments($article) {
        if (!HookRegistry::dispatch('AuthorAction::viewCopyeditComments', [&$article])) {
            import('classes.submission.form.comment.CopyeditCommentForm');

            $commentForm = new CopyeditCommentForm($article, ROLE_ID_AUTHOR);
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

        if (!HookRegistry::dispatch('AuthorAction::postCopyeditComment', [&$article, &$emailComment])) {
            import('classes.submission.form.comment.CopyeditCommentForm');

            $commentForm = new CopyeditCommentForm($article, ROLE_ID_AUTHOR);
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

    /**
     * View proofread comments.
     * @param object $article
     */
    public static function viewProofreadComments($article) {
        if (!HookRegistry::dispatch('AuthorAction::viewProofreadComments', [&$article])) {
            import('classes.submission.form.comment.ProofreadCommentForm');

            $commentForm = new ProofreadCommentForm($article, ROLE_ID_AUTHOR);
            $commentForm->initData();
            $commentForm->display();
        }
    }

    /**
     * Post proofread comment.
     * @param object $article
     * @param boolean $emailComment
     * @param object $request PKPRequest
     */
    public static function postProofreadComment($article, $emailComment, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (!HookRegistry::dispatch('AuthorAction::postProofreadComment', [&$article, &$emailComment])) {
            import('classes.submission.form.comment.ProofreadCommentForm');

            $commentForm = new ProofreadCommentForm($article, ROLE_ID_AUTHOR);
            $commentForm->readInputData();

            if ($commentForm->validate()) {
                $commentForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationUsers = $article->getAssociatedUserIds(true, false);
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, $userRole['id'], NOTIFICATION_TYPE_PROOFREAD_COMMENT,
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
     * Download a file an author has access to.
     * @param object $article
     * @param int $fileId
     * @param int|null $revision
     * @return boolean
     */
    public static function downloadAuthorFile($article, $fileId, $revision = null) {
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');

        $authorSubmission = $authorSubmissionDao->getAuthorSubmission($article->getId());
        $layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $authorSubmission->getId());

        $canDownload = false;

        // Authors have access to:
        // 1) The original submission file.
        // 2) Any files uploaded by the reviewers that are "viewable",
        //    although only after a decision has been made by the editor.
        // 3) The initial and final copyedit files, after initial copyedit is complete.
        // 4) Any of the author-revised files.
        // 5) The layout version of the file.
        // 6) Any supplementary file
        // 7) Any galley file
        // 8) All review versions of the file
        // 9) Current editor versions of the file
        if ($authorSubmission->getSubmissionFileId() == $fileId) {
            $canDownload = true;
        } elseif ($authorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_INITIAL', true) == $fileId) {
            if ($revision != null) {
                $initialSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $authorSubmission->getId());
                $authorSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $authorSubmission->getId());
                $finalSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $authorSubmission->getId());

                if ($initialSignoff && $initialSignoff->getFileRevision()==$revision && $initialSignoff->getDateCompleted()!=null) $canDownload = true;
                elseif ($finalSignoff && $finalSignoff->getFileRevision()==$revision && $finalSignoff->getDateCompleted()!=null) $canDownload = true;
                elseif ($authorSignoff && $authorSignoff->getFileRevision()==$revision) $canDownload = true;
            } else {
                $canDownload = false;
            }
        } elseif ($authorSubmission->getFileBySignoffType('SIGNOFF_COPYEDITING_AUTHOR', true) == $fileId){
            $canDownload = true;
        } elseif ($authorSubmission->getRevisedFileId() == $fileId) {
            $canDownload = true;
        } elseif ($layoutSignoff->getFileId() == $fileId) {
            $canDownload = true;
        } else {
            // Check reviewer files
            foreach ($authorSubmission->getReviewAssignments() as $roundReviewAssignments) {
                foreach ($roundReviewAssignments as $reviewAssignment) {
                    if ($reviewAssignment->getReviewerFileId() == $fileId) {
                        $articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
                        $articleFile = $articleFileDao->getArticleFile($fileId, $revision);
                        if ($articleFile != null && $articleFile->getViewable()) {
                            $canDownload = true;
                        }
                    }
                }
            }

            // Check supplementary files
            // [WIZDAM] Null Coalescing
            $suppFiles = $authorSubmission->getSuppFiles() ?? [];
            foreach ($suppFiles as $suppFile) {
                if ($suppFile->getFileId() == $fileId) {
                    $canDownload = true;
                }
            }

            // Check galley files
            // [WIZDAM] Null Coalescing
            $galleys = $authorSubmission->getGalleys() ?? [];
            foreach ($galleys as $galleyFile) {
                if ($galleyFile->getFileId() == $fileId) {
                    $canDownload = true;
                }
            }

            // Check current review version
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            $reviewFilesByRound = $reviewAssignmentDao->getReviewFilesByRound($article->getId());
            // [WIZDAM] Replaced @ suppression with safe check
            $reviewFile = isset($reviewFilesByRound[$article->getCurrentRound()]) ? $reviewFilesByRound[$article->getCurrentRound()] : null;
            if ($reviewFile && $fileId == $reviewFile->getFileId()) {
                $canDownload = true;
            }

            // Check editor version
            $editorFiles = $authorSubmission->getEditorFileRevisions($article->getCurrentRound());
            if (is_array($editorFiles)) foreach ($editorFiles as $editorFile) {
                if ($editorFile->getFileId() == $fileId) {
                    $canDownload = true;
                }
            }
        }

        $result = false;
        if (!HookRegistry::dispatch('AuthorAction::downloadAuthorFile', [&$article, &$fileId, &$revision, &$canDownload, &$result])) {
            if ($canDownload) {
                return Action::downloadFile($article->getId(), $fileId, $revision);
            } else {
                return false;
            }
        }
        return $result;
    }
}
?>
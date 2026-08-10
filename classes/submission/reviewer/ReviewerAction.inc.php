<?php
declare(strict_types=1);

/**
 * @file classes/submission/reviewer/ReviewerAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerAction
 * @ingroup submission
 *
 * @brief ReviewerAction class.
 */

import('classes.submission.common.Action');

class ReviewerAction extends Action {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReviewerAction() {
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
     * Actions.
     */

    /**
     * Records whether or not the reviewer accepts the review assignment.
     * @param object $reviewerSubmission ReviewerSubmission
     * @param bool $decline
     * @param bool $send
     * @param object $request PKPRequest
     * @return bool
     */
    public function confirmReview($reviewerSubmission, $decline, $send, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $reviewId = (int) $reviewerSubmission->getReviewId();

        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        if ($reviewAssignment === null) {
            return true;
        }

        $reviewer = $userDao->getById((int) $reviewAssignment->getReviewerId());
        if (!($reviewer instanceof User)) {
            return true;
        }

        // Only confirm the review for the reviewer if he has not previously done so.
        if ($reviewAssignment->getDateConfirmed() === null) {
            import('classes.mail.ArticleMailTemplate');
            $email = new ArticleMailTemplate($reviewerSubmission, $decline ? 'REVIEW_DECLINE' : 'REVIEW_CONFIRM');
            
            // Must explicitly set sender because we may be here on an access
            // key, in which case the user is not technically logged in
            $email->setFrom((string) $reviewer->getEmail(), (string) $reviewer->getFullName());

            if ($send && $email->hasErrors()) {
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                if (method_exists($notificationManager, 'createTrivialNotification')) {
                    $notificationManager->createTrivialNotification(
                        (int) $reviewer->getId(),
                        NOTIFICATION_TYPE_ERROR,
                        ['contents' => __('reviewer.article.confirmationEmailFailed')]
                    );
                }
                error_log(sprintf(
                    'ReviewerAction::confirmReview - confirmation email for reviewId=%d had no valid recipient/errors; reviewer left unconfirmed.',
                    $reviewId
                ));
            }

            if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
                HookRegistry::dispatch('ReviewerAction::confirmReview', [&$reviewerSubmission, &$email, $decline]);
                
                if ($email->isEnabled()) {
                    $email->send($request);
                }

                $reviewAssignment->setDateReminded(null);
                $reviewAssignment->setReminderWasAutomatic(null);
                $reviewAssignment->setDeclined((int) $decline);
                $reviewAssignment->setDateConfirmed(Core::getCurrentDate());
                $reviewAssignment->stampModified();
                $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

                // Add log
                import('classes.article.log.ArticleLog');
                ArticleLog::logEventHeadless(
                    $request->getJournal(), 
                    (int) $reviewer->getId(), 
                    $reviewerSubmission, 
                    $decline ? ARTICLE_LOG_REVIEW_DECLINE : ARTICLE_LOG_REVIEW_ACCEPT, 
                    $decline ? 'log.review.reviewDeclined' : 'log.review.reviewAccepted', 
                    [
                        'reviewerName' => (string) $reviewer->getFullName(), 
                        'articleId' => (int) $reviewAssignment->getSubmissionId(), 
                        'round' => (int) $reviewAssignment->getRound(), 
                        'reviewId' => (int) $reviewAssignment->getId()
                    ]
                );
                return true;
            } else {
                if (!$request->getUserVar('continued')) {
                    $assignedEditors = $email->ccAssignedEditors((int) $reviewerSubmission->getId());
                    $reviewingSectionEditors = $email->toAssignedReviewingSectionEditors((int) $reviewerSubmission->getId());
                    
                    if (empty($assignedEditors) && empty($reviewingSectionEditors)) {
                        $journal = $request->getJournal();
                        if ($journal !== null) {
                            $email->addRecipient((string) $journal->getSetting('contactEmail'), (string) $journal->getSetting('contactName'));
                            $editorialContactName = (string) $journal->getSetting('contactName');
                        } else {
                            $editorialContactName = '';
                        }
                    } else {
                        if (!empty($reviewingSectionEditors)) {
                            $editorialContact = array_shift($reviewingSectionEditors);
                        } else {
                            $editorialContact = array_shift($assignedEditors);
                        }
                        $editorialContactName = (string) $editorialContact->getEditorFullName();
                    }
                    $email->promoteCcsIfNoRecipients();

                    // Format the review due date
                    $reviewDueDate = strtotime((string) $reviewAssignment->getDateDue());
                    $dateFormatShort = Config::getVar('general', 'date_format_short');
                    
                    if ($reviewDueDate === -1) {
                        $reviewDueDate = (string) $dateFormatShort; // Default to something human-readable if no date specified
                    } else {
                        $reviewDueDate = strftime((string) $dateFormatShort, $reviewDueDate);
                    }

                    $email->assignParams([
                        'editorialContactName' => $editorialContactName,
                        'reviewerName' => (string) $reviewer->getFullName(),
                        'reviewDueDate' => (string) $reviewDueDate
                    ]);
                }
                
                $paramArray = ['reviewId' => $reviewId];
                if ($decline) {
                    $paramArray['declineReview'] = 1;
                }
                
                $email->displayEditForm($request->url(null, 'reviewer', 'confirmReview'), $paramArray);
                return false;
            }
        }
        return true;
    }

    /**
     * Records the reviewer's submission recommendation.
     * @param object $reviewerSubmission ReviewerSubmission
     * @param int $recommendation
     * @param bool $send
     * @param object $request PKPRequest
     * @return bool
     */
    public function recordRecommendation($reviewerSubmission, $recommendation, $send, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        // Check validity of selected recommendation
        $reviewerRecommendationOptions = ReviewAssignment::getReviewerRecommendationOptions();
        if (!isset($reviewerRecommendationOptions[(string) $recommendation])) {
            return true;
        }

        $reviewAssignment = $reviewAssignmentDao->getById((int) $reviewerSubmission->getReviewId());
        if ($reviewAssignment === null) {
            return true;
        }

        $reviewer = $userDao->getById((int) $reviewAssignment->getReviewerId());
        if (!($reviewer instanceof User)) {
            return true;
        }

        // Only record the reviewers recommendation if no recommendation has previously been submitted.
        $currentRecommendation = $reviewAssignment->getRecommendation();
        if ($currentRecommendation === null || $currentRecommendation === '') {
            import('classes.mail.ArticleMailTemplate');
            $email = new ArticleMailTemplate($reviewerSubmission, 'REVIEW_COMPLETE');
            
            // Must explicitly set sender because we may be here on an access
            // key, in which case the user is not technically logged in
            $email->setFrom((string) $reviewer->getEmail(), (string) $reviewer->getFullName());

            if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
                HookRegistry::dispatch('ReviewerAction::recordRecommendation', [&$reviewerSubmission, &$email, $recommendation]);
                
                if ($email->isEnabled()) {
                    $email->send($request);
                }

                $reviewAssignment->setRecommendation((int) $recommendation);
                $reviewAssignment->setDateCompleted(Core::getCurrentDate());
                $reviewAssignment->stampModified();
                $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

                // Add log
                import('classes.article.log.ArticleLog');
                ArticleLog::logEventHeadless(
                    $request->getJournal(), 
                    (int) $reviewer->getId(), 
                    $reviewerSubmission, 
                    ARTICLE_LOG_REVIEW_RECOMMENDATION, 
                    'log.review.reviewRecommendationSet', 
                    [
                        'reviewerName' => (string) $reviewer->getFullName(), 
                        'articleId' => (int) $reviewAssignment->getSubmissionId(), 
                        'round' => (int) $reviewAssignment->getRound(), 
                        'reviewId' => (int) $reviewAssignment->getId()
                    ]
                );
            } else {
                if (!$request->getUserVar('continued')) {
                    $assignedEditors = $email->ccAssignedEditors((int) $reviewerSubmission->getId());
                    $reviewingSectionEditors = $email->toAssignedReviewingSectionEditors((int) $reviewerSubmission->getId());
                    
                    if (empty($assignedEditors) && empty($reviewingSectionEditors)) {
                        $journal = $request->getJournal();
                        if ($journal !== null) {
                            $email->addRecipient((string) $journal->getSetting('contactEmail'), (string) $journal->getSetting('contactName'));
                            $editorialContactName = (string) $journal->getSetting('contactName');
                        } else {
                            $editorialContactName = '';
                        }
                    } else {
                        if (!empty($reviewingSectionEditors)) {
                            $editorialContact = array_shift($reviewingSectionEditors);
                        } else {
                            $editorialContact = array_shift($assignedEditors);
                        }
                        $editorialContactName = (string) $editorialContact->getEditorFullName();
                    }

                    $reviewerRecommendationOptions = ReviewAssignment::getReviewerRecommendationOptions();

                    $email->assignParams([
                        'editorialContactName' => $editorialContactName,
                        'reviewerName' => (string) $reviewer->getFullName(),
                        'articleTitle' => strip_tags((string) $reviewerSubmission->getLocalizedTitle()),
                        'recommendation' => __((string) $reviewerRecommendationOptions[(string) $recommendation])
                    ]);
                }

                $email->displayEditForm(
                    $request->url(null, 'reviewer', 'recordRecommendation'),
                    ['reviewId' => (int) $reviewerSubmission->getReviewId(), 'recommendation' => (int) $recommendation]
                );
                return false;
            }
        }
        return true;
    }

    /**
     * Upload the annotated version of an article.
     * @param int $reviewId
     * @param object $reviewerSubmission ReviewerSubmission
     * @param object $request PKPRequest
     * @return void
     */
    public static function uploadReviewerVersion($reviewId, $reviewerSubmission, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        import('classes.file.ArticleFileManager');
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById((int) $reviewId);

        if ($reviewAssignment === null) {
            return;
        }

        $articleFileManager = new ArticleFileManager((int) $reviewAssignment->getSubmissionId());

        // Only upload the file if the reviewer has yet to submit a recommendation
        // and if review forms are not used
        $fileId = 0;
        $currentRecommendation = $reviewAssignment->getRecommendation();
        if (($currentRecommendation === null || $currentRecommendation === '') && !$reviewAssignment->getCancelled()) {
            $fileName = 'upload';
            if ($articleFileManager->uploadedFileExists($fileName)) {
                HookRegistry::dispatch('ReviewerAction::uploadReviewFile', [&$reviewAssignment]);
                if ($reviewAssignment->getReviewerFileId() !== null) {
                    $fileId = $articleFileManager->uploadReviewFile($fileName, (int) $reviewAssignment->getReviewerFileId());
                } else {
                    $fileId = $articleFileManager->uploadReviewFile($fileName);
                }
            }
        }

        if (isset($fileId) && $fileId !== 0) {
            $reviewAssignment->setReviewerFileId((int) $fileId);
            $reviewAssignment->stampModified();
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            $reviewer = $userDao->getById((int) $reviewAssignment->getReviewerId());

            if ($reviewer !== null) {
                // Add log
                import('classes.article.log.ArticleLog');
                ArticleLog::logEventHeadless(
                    $request->getJournal(), 
                    (int) $reviewer->getId(), 
                    $reviewerSubmission, 
                    ARTICLE_LOG_REVIEW_FILE, 
                    'log.review.reviewerFile', 
                    ['reviewId' => (int) $reviewAssignment->getId()]
                );
            }
        }
    }

    /**
     * Delete an annotated version of an article.
     * @param int $reviewId
     * @param int $fileId
     * @param int|null $revision If null, then all revisions are deleted.
     * @return void
     */
    public function deleteReviewerVersion($reviewId, $fileId, $revision = null) {
        import('classes.file.ArticleFileManager');

        // [WIZDAM] Modern Request Usage
        $request = Application::get()->getRequest();
        $articleId = (int) $request->getUserVar('articleId');
        
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById((int) $reviewId);

        if ($reviewAssignment === null) {
            return;
        }

        if (!HookRegistry::dispatch('ReviewerAction::deleteReviewerVersion', [&$reviewAssignment, &$fileId, &$revision])) {
            $articleFileManager = new ArticleFileManager((int) $reviewAssignment->getSubmissionId());
            $articleFileManager->deleteFile((int) $fileId, $revision !== null ? (int) $revision : null);
        }
    }

    /**
     * View reviewer comments.
     * @param object $user Current user
     * @param object $article
     * @param int $reviewId
     * @return void
     */
    public function viewPeerReviewComments($user, $article, $reviewId) {
        if (!HookRegistry::dispatch('ReviewerAction::viewPeerReviewComments', [&$user, &$article, &$reviewId])) {
            import('classes.submission.form.comment.PeerReviewCommentForm');

            $commentForm = new PeerReviewCommentForm($article, (int) $reviewId, ROLE_ID_REVIEWER);
            $commentForm->setUser($user);
            $commentForm->initData();
            $commentForm->setData('reviewId', (int) $reviewId);
            $commentForm->display();
        }
    }

    /**
     * Post reviewer comments.
     * @param object $user Current user
     * @param object $article
     * @param int $reviewId
     * @param bool $emailComment
     * @param object $request Request
     * @return bool
     */
    public function postPeerReviewComment($user, $article, $reviewId, $emailComment, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (!HookRegistry::dispatch('ReviewerAction::postPeerReviewComment', [&$user, &$article, &$reviewId, &$emailComment])) {
            import('classes.submission.form.comment.PeerReviewCommentForm');

            $commentForm = new PeerReviewCommentForm($article, (int) $reviewId, ROLE_ID_REVIEWER);
            $commentForm->setUser($user);
            $commentForm->readInputData();

            if ($commentForm->validate()) {
                $commentForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationUsers = $article->getAssociatedUserIds(false, false);
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, 
                        (int) $userRole['id'], 
                        NOTIFICATION_TYPE_REVIEWER_COMMENT,
                        (int) $article->getJournalId(), 
                        ASSOC_TYPE_ARTICLE, 
                        (int) $article->getId()
                    );
                }

                if ($emailComment) {
                    $recipients = [];
                    $commentForm->email($recipients, $request);
                }
            } else {
                $commentForm->display();
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Edit review form response.
     * @param int $reviewId
     * @param int $reviewFormId
     * @return void
     */
    public function editReviewFormResponse($reviewId, $reviewFormId) {
        if (!HookRegistry::dispatch('ReviewerAction::editReviewFormResponse', [(int) $reviewId, (int) $reviewFormId])) {
            import('classes.submission.form.ReviewFormResponseForm');

            $reviewForm = new ReviewFormResponseForm((int) $reviewId, (int) $reviewFormId);
            $reviewForm->initData();
            $reviewForm->display();
        }
    }

    /**
     * Save review form response.
     * @param int $reviewId
     * @param int $reviewFormId
     * @param object $request Request
     * @return bool
     */
    public function saveReviewFormResponse($reviewId, $reviewFormId, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (!HookRegistry::dispatch('ReviewerAction::saveReviewFormResponse', [(int) $reviewId, (int) $reviewFormId])) {
            import('classes.submission.form.ReviewFormResponseForm');

            $reviewForm = new ReviewFormResponseForm((int) $reviewId, (int) $reviewFormId);
            $reviewForm->readInputData();
            if ($reviewForm->validate()) {
                $reviewForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                /** @var ReviewAssignmentDAO $reviewAssignmentDao */
                $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
                $reviewAssignment = $reviewAssignmentDao->getById((int) $reviewId);
                
                if ($reviewAssignment !== null) {
                    $articleId = (int) $reviewAssignment->getSubmissionId();

                    /** @var ArticleDAO $articleDao */
                    $articleDao = DAORegistry::getDAO('ArticleDAO');
                    $article = $articleDao->getArticle($articleId);
                    
                    if ($article !== null) {
                        $notificationUsers = $article->getAssociatedUserIds(false, false);
                        foreach ($notificationUsers as $userRole) {
                            $notificationManager->createNotification(
                                $request, 
                                (int) $userRole['id'], 
                                NOTIFICATION_TYPE_REVIEWER_FORM_COMMENT,
                                (int) $article->getJournalId(), 
                                ASSOC_TYPE_ARTICLE, 
                                (int) $article->getId()
                            );
                        }
                    }
                }
            } else {
                $reviewForm->display();
                return false;
            }
            return true;
        }
        return false;
    }

    //
    // Misc
    //

    /**
     * Download a file a reviewer has access to.
     * @param int $reviewId
     * @param object $article
     * @param int $fileId
     * @param int|null $revision
     * @return bool
     */
    public function downloadReviewerFile($reviewId, $article, $fileId, $revision = null) {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById((int) $reviewId);
        
        if ($reviewAssignment === null) {
            return false;
        }

        // [WIZDAM] Singleton Fallback for Request
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        $canDownload = false;

        // Reviewers have access to:
        // 1) The current revision of the file to be reviewed.
        // 2) Any file that he uploads.
        // 3) Any supplementary file that is visible to reviewers.
        $dateConfirmed = $reviewAssignment->getDateConfirmed();
        $declined = $reviewAssignment->getDeclined();
        
        if ((!$dateConfirmed || $declined) && $journal !== null && $journal->getSetting('restrictReviewerFileAccess')) {
            // Restrict files until review is accepted
        } else if ($reviewAssignment->getReviewFileId() === (int) $fileId) {
            if ($revision !== null) {
                $canDownload = ($reviewAssignment->getReviewRevision() === (int) $revision);
            } else {
                $canDownload = true;
            }
        } else if ($reviewAssignment->getReviewerFileId() === (int) $fileId) {
            $canDownload = true;
        } else {
            $suppFiles = $reviewAssignment->getSuppFiles();
            if (is_array($suppFiles)) {
                foreach ($suppFiles as $suppFile) {
                    if ($suppFile->getFileId() === (int) $fileId && $suppFile->getShowReviewers()) {
                        $canDownload = true;
                        break;
                    }
                }
            }
        }

        $result = false;
        if (!HookRegistry::dispatch('ReviewerAction::downloadReviewerFile', [&$article, &$fileId, &$revision, &$canDownload, &$result])) {
            if ($canDownload) {
                return Action::downloadFile((int) $article->getId(), (int) $fileId, $revision !== null ? (int) $revision : null);
            } else {
                return false;
            }
        }
        return (bool) $result;
    }

    /**
     * Edit comment.
     * @param object $article
     * @param object $comment
     * @return void
     */
    public static function editComment($article, $comment) {
        // [WIZDAM] Safety check for undefined variable in legacy code
        $reviewId = (method_exists($comment, 'getReviewId')) ? $comment->getReviewId() : null;

        if (!HookRegistry::dispatch('ReviewerAction::editComment', [&$article, &$comment, &$reviewId])) {
            import('classes.submission.form.comment.EditCommentForm');

            $commentForm = new EditCommentForm($article, $comment);
            $commentForm->initData();
            $commentForm->setData('reviewId', $reviewId);
            $commentForm->display(['reviewId' => $reviewId]);
        }
    }
    
}
?>
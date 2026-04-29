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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
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
     * [SHIM] Backward Compatibility
     */
    public function ReviewerAction() {
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
     * Records whether or not the reviewer accepts the review assignment.
     * @param object $reviewerSubmission ReviewerSubmission
     * @param boolean $decline
     * @param boolean $send
     * @param object $request PKPRequest
     */
    public function confirmReview($reviewerSubmission, $decline, $send, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $reviewId = (int) $reviewerSubmission->getReviewId();

        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewer = $userDao->getById((int) $reviewAssignment->getReviewerId());
        
        if (!($reviewer instanceof User)) return true;

        // Only confirm the review for the reviewer if
        // he has not previously done so.
        if ($reviewAssignment->getDateConfirmed() == null) {
            import('classes.mail.ArticleMailTemplate');
            $email = new ArticleMailTemplate($reviewerSubmission, $decline ? 'REVIEW_DECLINE' : 'REVIEW_CONFIRM');
            
            // Must explicitly set sender because we may be here on an access
            // key, in which case the user is not technically logged in
            $email->setFrom($reviewer->getEmail(), $reviewer->getFullName());
            
            if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
                HookRegistry::dispatch('ReviewerAction::confirmReview', [&$reviewerSubmission, &$email, $decline]);
                
                if ($email->isEnabled()) {
                    $email->send($request);
                }

                $reviewAssignment->setDateReminded(null);
                $reviewAssignment->setReminderWasAutomatic(null);
                $reviewAssignment->setDeclined($decline);
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
                        'reviewerName' => $reviewer->getFullName(), 
                        'articleId' => $reviewAssignment->getSubmissionId(), 
                        'round' => $reviewAssignment->getRound(), 
                        'reviewId' => $reviewAssignment->getId()
                    ]
                );
                return true;
            } else {
                if (!$request->getUserVar('continued')) {
                    $assignedEditors = $email->ccAssignedEditors($reviewerSubmission->getId());
                    $reviewingSectionEditors = $email->toAssignedReviewingSectionEditors($reviewerSubmission->getId());
                    
                    if (empty($assignedEditors) && empty($reviewingSectionEditors)) {
                        $journal = $request->getJournal();
                        $email->addRecipient($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                        $editorialContactName = $journal->getSetting('contactName');
                    } else {
                        if (!empty($reviewingSectionEditors)) $editorialContact = array_shift($reviewingSectionEditors);
                        else $editorialContact = array_shift($assignedEditors);
                        
                        $editorialContactName = $editorialContact->getEditorFullName();
                    }
                    $email->promoteCcsIfNoRecipients();

                    // Format the review due date
                    $reviewDueDate = strtotime($reviewAssignment->getDateDue());
                    $dateFormatShort = Config::getVar('general', 'date_format_short');
                    
                    if ($reviewDueDate == -1) $reviewDueDate = $dateFormatShort; // Default to something human-readable if no date specified
                    else $reviewDueDate = strftime($dateFormatShort, $reviewDueDate);

                    $email->assignParams([
                        'editorialContactName' => $editorialContactName,
                        'reviewerName' => $reviewer->getFullName(),
                        'reviewDueDate' => $reviewDueDate
                    ]);
                }
                
                $paramArray = ['reviewId' => $reviewId];
                if ($decline) $paramArray['declineReview'] = 1;
                
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
     * @param boolean $send
     * @param object $request PKPRequest
     */
    public function recordRecommendation($reviewerSubmission, $recommendation, $send, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        // Check validity of selected recommendation
        $reviewerRecommendationOptions = ReviewAssignment::getReviewerRecommendationOptions();
        if (!isset($reviewerRecommendationOptions[$recommendation])) return true;

        $reviewAssignment = $reviewAssignmentDao->getById($reviewerSubmission->getReviewId());
        $reviewer = $userDao->getUser($reviewAssignment->getReviewerId());
        
        if (!($reviewer instanceof User)) return true;

        // Only record the reviewers recommendation if
        // no recommendation has previously been submitted.
        if ($reviewAssignment->getRecommendation() === null || $reviewAssignment->getRecommendation() === '') {
            import('classes.mail.ArticleMailTemplate');
            $email = new ArticleMailTemplate($reviewerSubmission, 'REVIEW_COMPLETE');
            // Must explicitly set sender because we may be here on an access
            // key, in which case the user is not technically logged in
            $email->setFrom($reviewer->getEmail(), $reviewer->getFullName());

            if (!$email->isEnabled() || ($send && !$email->hasErrors())) {
                HookRegistry::dispatch('ReviewerAction::recordRecommendation', [&$reviewerSubmission, &$email, $recommendation]);
                
                if ($email->isEnabled()) {
                    $email->send($request);
                }

                $reviewAssignment->setRecommendation($recommendation);
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
                        'reviewerName' => $reviewer->getFullName(), 
                        'articleId' => $reviewAssignment->getSubmissionId(), 
                        'round' => $reviewAssignment->getRound(), 
                        'reviewId' => $reviewAssignment->getId()
                    ]
                );
            } else {
                if (!$request->getUserVar('continued')) {
                    $assignedEditors = $email->ccAssignedEditors($reviewerSubmission->getId());
                    $reviewingSectionEditors = $email->toAssignedReviewingSectionEditors($reviewerSubmission->getId());
                    
                    if (empty($assignedEditors) && empty($reviewingSectionEditors)) {
                        $journal = $request->getJournal();
                        $email->addRecipient($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
                        $editorialContactName = $journal->getSetting('contactName');
                    } else {
                        if (!empty($reviewingSectionEditors)) $editorialContact = array_shift($reviewingSectionEditors);
                        else $editorialContact = array_shift($assignedEditors);
                        
                        $editorialContactName = $editorialContact->getEditorFullName();
                    }

                    $reviewerRecommendationOptions = ReviewAssignment::getReviewerRecommendationOptions();

                    $email->assignParams([
                        'editorialContactName' => $editorialContactName,
                        'reviewerName' => $reviewer->getFullName(),
                        'articleTitle' => strip_tags($reviewerSubmission->getLocalizedTitle()),
                        'recommendation' => __($reviewerRecommendationOptions[$recommendation])
                    ]);
                }

                $email->displayEditForm(
                    $request->url(null, 'reviewer', 'recordRecommendation'),
                    ['reviewId' => $reviewerSubmission->getReviewId(), 'recommendation' => $recommendation]
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
     */
    public function uploadReviewerVersion($reviewId, $reviewerSubmission, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        import('classes.file.ArticleFileManager');
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

        $articleFileManager = new ArticleFileManager($reviewAssignment->getSubmissionId());

        // Only upload the file if the reviewer has yet to submit a recommendation
        // and if review forms are not used
        $fileId = 0;
        if (($reviewAssignment->getRecommendation() === null || $reviewAssignment->getRecommendation() === '') && !$reviewAssignment->getCancelled()) {
            $fileName = 'upload';
            if ($articleFileManager->uploadedFileExists($fileName)) {
                HookRegistry::dispatch('ReviewerAction::uploadReviewFile', [&$reviewAssignment]);
                if ($reviewAssignment->getReviewerFileId() != null) {
                    $fileId = $articleFileManager->uploadReviewFile($fileName, $reviewAssignment->getReviewerFileId());
                } else {
                    $fileId = $articleFileManager->uploadReviewFile($fileName);
                }
            }
        }

        if (isset($fileId) && $fileId != 0) {
            $reviewAssignment->setReviewerFileId($fileId);
            $reviewAssignment->stampModified();
            $reviewAssignmentDao->updateReviewAssignment($reviewAssignment);

            $userDao = DAORegistry::getDAO('UserDAO');
            $reviewer = $userDao->getUser($reviewAssignment->getReviewerId());

            // Add log
            import('classes.article.log.ArticleLog');
            ArticleLog::logEventHeadless(
                $request->getJournal(), 
                (int) $reviewer->getId(), 
                $reviewerSubmission, 
                ARTICLE_LOG_REVIEW_FILE, 
                'log.review.reviewerFile', 
                ['reviewId' => $reviewAssignment->getId()]
            );
        }
    }

    /**
     * Delete an annotated version of an article.
     * @param int $reviewId
     * @param int $fileId
     * @param int|null $revision If null, then all revisions are deleted.
     */
    public function deleteReviewerVersion($reviewId, $fileId, $revision = null) {
        import('classes.file.ArticleFileManager');

        // [WIZDAM] Modern Request Usage
        $request = Application::get()->getRequest();
        $articleId = $request->getUserVar('articleId');
        
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

        if (!HookRegistry::dispatch('ReviewerAction::deleteReviewerVersion', [&$reviewAssignment, &$fileId, &$revision])) {
            $articleFileManager = new ArticleFileManager($reviewAssignment->getSubmissionId());
            $articleFileManager->deleteFile($fileId, $revision);
        }
    }

    /**
     * View reviewer comments.
     * @param object $user Current user
     * @param object $article
     * @param int $reviewId
     */
    public function viewPeerReviewComments($user, $article, $reviewId) {
        if (!HookRegistry::dispatch('ReviewerAction::viewPeerReviewComments', [&$user, &$article, &$reviewId])) {
            import('classes.submission.form.comment.PeerReviewCommentForm');

            $commentForm = new PeerReviewCommentForm($article, $reviewId, ROLE_ID_REVIEWER);
            $commentForm->setUser($user);
            $commentForm->initData();
            $commentForm->setData('reviewId', $reviewId);
            $commentForm->display();
        }
    }

    /**
     * Post reviewer comments.
     * @param object $user Current user
     * @param object $article
     * @param int $reviewId
     * @param boolean $emailComment
     * @param object $request Request
     */
    public function postPeerReviewComment($user, $article, $reviewId, $emailComment, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (!HookRegistry::dispatch('ReviewerAction::postPeerReviewComment', [&$user, &$article, &$reviewId, &$emailComment])) {
            import('classes.submission.form.comment.PeerReviewCommentForm');

            $commentForm = new PeerReviewCommentForm($article, $reviewId, ROLE_ID_REVIEWER);
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
                        $userRole['id'], 
                        NOTIFICATION_TYPE_REVIEWER_COMMENT,
                        $article->getJournalId(), 
                        ASSOC_TYPE_ARTICLE, 
                        $article->getId()
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
     * Edit review form response.
     * @param int $reviewId
     * @param int $reviewFormId
     */
    public function editReviewFormResponse($reviewId, $reviewFormId) {
        if (!HookRegistry::dispatch('ReviewerAction::editReviewFormResponse', [$reviewId, $reviewFormId])) {
            import('classes.submission.form.ReviewFormResponseForm');

            $reviewForm = new ReviewFormResponseForm($reviewId, $reviewFormId);
            $reviewForm->initData();
            $reviewForm->display();
        }
    }

    /**
     * Save review form response.
     * @param int $reviewId
     * @param int $reviewFormId
     * @param object $request Request
     */
    public function saveReviewFormResponse($reviewId, $reviewFormId, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        if (!HookRegistry::dispatch('ReviewerAction::saveReviewFormResponse', [$reviewId, $reviewFormId])) {
            import('classes.submission.form.ReviewFormResponseForm');

            $reviewForm = new ReviewFormResponseForm($reviewId, $reviewFormId);
            $reviewForm->readInputData();
            if ($reviewForm->validate()) {
                $reviewForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
                $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
                $articleId = $reviewAssignment->getSubmissionId();
                $articleDao = DAORegistry::getDAO('ArticleDAO');
                $article = $articleDao->getArticle($articleId);
                $notificationUsers = $article->getAssociatedUserIds(false, false);
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, 
                        $userRole['id'], 
                        NOTIFICATION_TYPE_REVIEWER_FORM_COMMENT,
                        $article->getJournalId(), 
                        ASSOC_TYPE_ARTICLE, 
                        $article->getId()
                    );
                }

            } else {
                $reviewForm->display();
                return false;
            }
            return true;
        }
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
     */
    public function downloadReviewerFile($reviewId, $article, $fileId, $revision = null) {
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        
        // [WIZDAM] Singleton Fallback for Request
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        $canDownload = false;

        // Reviewers have access to:
        // 1) The current revision of the file to be reviewed.
        // 2) Any file that he uploads.
        // 3) Any supplementary file that is visible to reviewers.
        if ((!$reviewAssignment->getDateConfirmed() || $reviewAssignment->getDeclined()) && $journal->getSetting('restrictReviewerFileAccess')) {
            // Restrict files until review is accepted
        } else if ($reviewAssignment->getReviewFileId() == $fileId) {
            if ($revision != null) {
                $canDownload = ($reviewAssignment->getReviewRevision() == $revision);
            }
        } else if ($reviewAssignment->getReviewerFileId() == $fileId) {
            $canDownload = true;
        } else {
            foreach ($reviewAssignment->getSuppFiles() as $suppFile) {
                if ($suppFile->getFileId() == $fileId && $suppFile->getShowReviewers()) {
                    $canDownload = true;
                }
            }
        }

        $result = false;
        if (!HookRegistry::dispatch('ReviewerAction::downloadReviewerFile', [&$article, &$fileId, &$revision, &$canDownload, &$result])) {
            if ($canDownload) {
                return Action::downloadFile($article->getId(), $fileId, $revision);
            } else {
                return false;
            }
        }
        return $result;
    }

    /**
     * Edit comment.
     * @param object $article
     * @param object $comment
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
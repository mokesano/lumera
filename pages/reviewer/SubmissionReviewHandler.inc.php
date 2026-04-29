<?php
declare(strict_types=1);

/**
 * @file pages/reviewer/SubmissionReviewHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionReviewHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for submission tracking.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.reviewer.ReviewerHandler');

class SubmissionReviewHandler extends ReviewerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubmissionReviewHandler() {
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
     * Display the submission review page.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function submission($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $journal = $request->getJournal();
        $reviewId = (int) array_shift($args);

        $this->validate($request, $reviewId);
        $user = $this->user;
        $submission = $this->submission;

        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewFormResponseDao = DAORegistry::getDAO('ReviewFormResponseDAO');
        
        $confirmedStatus = ($submission->getDateConfirmed() == null) ? 0 : 1;

        $this->setupTemplate(true, $reviewAssignment->getSubmissionId(), $reviewId);
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('user', $user);
        $templateMgr->assign('submission', $submission);
        $templateMgr->assign('reviewAssignment', $reviewAssignment);
        $templateMgr->assign('confirmedStatus', $confirmedStatus);
        $templateMgr->assign('declined', $submission->getDeclined());
        $templateMgr->assign('reviewFormResponseExists', $reviewFormResponseDao->reviewFormResponseExists($reviewId));
        $templateMgr->assign('reviewFile', $reviewAssignment->getReviewFile());
        $templateMgr->assign('reviewerFile', $submission->getReviewerFile());
        $templateMgr->assign('suppFiles', $submission->getSuppFiles());
        $templateMgr->assign('journal', $journal);
        $templateMgr->assign('reviewGuidelines', $journal->getLocalizedSetting('reviewGuidelines'));

        import('classes.submission.reviewAssignment.ReviewAssignment');
        $templateMgr->assign('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());
        $templateMgr->assign('helpTopicId', 'editorial.reviewersRole.review');
        $templateMgr->display('reviewer/submission.tpl');
    }

    /**
     * Confirm whether the review has been accepted or not.
     * @param array $args optional
     * @param object $request PKPRequest
     */
    public function confirmReview($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));
        
        // [FIX] Ambil raw value dulu untuk pengecekan logic
        $rawDeclineReview = $request->getUserVar('declineReview');
        
        // Logika: Jika user klik 'Will do the review', parameter declineReview biasanya null.
        // Jika klik 'Unable to do review', param ini bernilai 1.
        $decline = (!empty($rawDeclineReview)) ? 1 : 0;

        $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
        $this->validate($request, $reviewId);
        $reviewerSubmission = $this->submission;

        $this->setupTemplate();

        if (!$reviewerSubmission->getCancelled()) {
            $sendFlag = ($request->getUserVar('send') !== null);
            
            if (ReviewerAction::confirmReview($reviewerSubmission, $decline, $sendFlag, $request)) { 
                $request->redirect(null, null, 'submission', $reviewId); 
            }
        } else {
            $request->redirect(null, null, 'submission', $reviewId);
        }
    }

    /**
     * Save the competing interests statement, if allowed.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function saveCompetingInterests($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));
        $this->validate($request, $reviewId);
        $reviewerSubmission = $this->submission;

        if ($reviewerSubmission->getDateConfirmed() && !$reviewerSubmission->getDeclined() && !$reviewerSubmission->getCancelled() && !$reviewerSubmission->getRecommendation()) {
            $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
            $competingInterests = trim((string) $request->getUserVar('competingInterests'));
            $reviewerSubmission->setCompetingInterests($competingInterests);
            $reviewerSubmissionDao->updateReviewerSubmission($reviewerSubmission);
        }
        $request->redirect(null, 'reviewer', 'submission', [$reviewId]);
    }

    /**
     * Record the reviewer recommendation.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function recordRecommendation($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));
        $recommendation = (int) trim((string) $request->getUserVar('recommendation'));

        $this->validate($request, $reviewId);
        $reviewerSubmission = $this->submission;
        $this->setupTemplate(true);

        if (!$reviewerSubmission->getCancelled()) {
            $sendFlag = ($request->getUserVar('send') !== null);
            if (ReviewerAction::recordRecommendation($reviewerSubmission, $recommendation, $sendFlag, $request)) {
                $request->redirect(null, null, 'submission', $reviewId);
            }
        } else {
            $request->redirect(null, null, 'submission', $reviewId);
        }
    }

    /**
     * View the submission metadata
     * @param array $args
     * @param object $request PKPRequest
     */
    public function viewMetadata($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $reviewId = (int) array_shift($args);
        $articleId = (int) array_shift($args);
        $journal = $request->getJournal();

        $this->validate($request, $reviewId);
        $reviewerSubmission = $this->submission;

        $this->setupTemplate(true, $articleId, $reviewId);

        ReviewerAction::viewMetadata($reviewerSubmission, $journal);
    }

    /**
     * Upload the reviewer's annotated version of an article.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function uploadReviewerVersion($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'reviewId' dengan trim() dan (int)
        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));

        $this->validate($request, $reviewId);
        $this->setupTemplate(true);

        ReviewerAction::uploadReviewerVersion($reviewId, $this->submission, $request);
        $request->redirect(null, null, 'submission', $reviewId);
    }

    /**
     * Delete one of the reviewer's annotated versions of an article.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function deleteReviewerVersion($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $reviewId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = (int) array_shift($args);
        if (!$revision) $revision = null;

        $this->validate($request, $reviewId);
        $reviewerSubmission = $this->submission;

        if (!$reviewerSubmission->getCancelled()) {
            ReviewerAction::deleteReviewerVersion($reviewId, $fileId, $revision);
        }
        $request->redirect(null, null, 'submission', $reviewId);
    }

    //
    // Misc
    //

    /**
     * Download a file.
     * @param array $args ($articleId, $fileId, [$revision])
     * @param object $request PKPRequest
     */
    public function downloadFile($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $reviewId = (int) array_shift($args);
        $articleId = (int) array_shift($args);
        $fileId = (int) array_shift($args);
        $revision = (int) array_shift($args);
        if (!$revision) $revision = null;

        $this->validate($request, $reviewId);
        $reviewerSubmission = $this->submission;

        if (!ReviewerAction::downloadReviewerFile($reviewId, $reviewerSubmission, $fileId, $revision)) {
            $request->redirect(null, null, 'submission', $reviewId);
        }
    }

    //
    // Review Form
    //

    /**
     * Edit or preview review form response.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function editReviewFormResponse($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $reviewId = (int) array_shift($args);

        $this->validate($request, $reviewId);
        $reviewerSubmission = $this->submission;
        $this->setupTemplate(true, $reviewerSubmission->getId(), $reviewId);

        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);
        $reviewFormId = $reviewAssignment->getReviewFormId();
        if ($reviewFormId != null) {
            ReviewerAction::editReviewFormResponse($reviewId, $reviewFormId);
        }
    }

    /**
     * Save review form response
     * @param array $args
     * @param object $request PKPRequest
     */
    public function saveReviewFormResponse($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $reviewId = (int) array_shift($args);
        $reviewFormId = (int) array_shift($args);

        $this->validate($request, $reviewId);
        $this->setupTemplate(true);

        if (ReviewerAction::saveReviewFormResponse($reviewId, $reviewFormId, $request)) {
            $request->redirect(null, null, 'submission', $reviewId);
        }
    }
}
?>
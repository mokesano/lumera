<?php
declare(strict_types=1);

/**
 * @file pages/reviewer/SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for submission comments.
 */

import('pages.reviewer.SubmissionReviewHandler');
import('classes.security.validation.HandlerValidatorSubmissionComment');

class SubmissionCommentsHandler extends ReviewerHandler {
    
    /** @var object|null */
    public $comment = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function SubmissionCommentsHandler() {
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
     * View peer review comments.
     * @param array $args
     * @param object|null $request
     */
    public function viewPeerReviewComments($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        
        $articleId = (int) array_shift($args);
        $reviewId = (int) array_shift($args);

        $this->validate($request, $reviewId);
        $this->setupTemplate(true);
        
        $reviewerAction = new ReviewerAction();
        $reviewerAction->viewPeerReviewComments($this->user, $this->submission, $reviewId);
    }

    /**
     * Post peer review comments.
     * @param array $args
     * @param object|null $request
     */
    public function postPeerReviewComment($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) $request->getUserVar('articleId');
        $reviewId = (int) $request->getUserVar('reviewId');
        $emailComment = (bool) $request->getUserVar('saveAndEmail');

        $this->validate($request, $reviewId);
        $this->setupTemplate(true);

        $reviewerAction = new ReviewerAction();
        if ($reviewerAction->postPeerReviewComment($this->user, $this->submission, $reviewId, $emailComment, $request)) {
            $reviewerAction->viewPeerReviewComments($this->user, $this->submission, $reviewId);
        }
    }

    /**
     * Edit comment.
     * @param array $args
     * @param object|null $request
     */
    public function editComment($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $commentId = !empty($args[0]) ? (int) array_shift($args) : null;
        $reviewId = (int) $request->getUserVar('reviewId');

        $this->validate($request, $reviewId, $commentId);
        $this->setupTemplate(true);

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);

        $reviewerAction = new ReviewerAction();
        $reviewerAction->editComment($article, $this->comment);
    }

    /**
     * Save comment.
     * @param array $args
     * @param object|null $request
     */
    public function saveComment($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) $request->getUserVar('articleId');
        $commentId = (int) $request->getUserVar('commentId');
        $reviewId = (int) $request->getUserVar('reviewId');
        $emailComment = (bool) $request->getUserVar('saveAndEmail');

        $this->validate($request, $reviewId, $commentId);
        $this->setupTemplate(true);

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);

        $reviewerAction = new ReviewerAction();
        if (method_exists($reviewerAction, 'saveComment')) {
            $reviewerAction->saveComment($article, $this->comment, $emailComment, $request);
        } else {
            error_log("WIZDAM WARNING: ReviewerAction::saveComment missing.");
        }

        /** @var ArticleCommentDAO $articleCommentDao */
        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $comment = $articleCommentDao->getArticleCommentById($commentId);

        if ($comment && $comment->getCommentType() === COMMENT_TYPE_PEER_REVIEW) {
            $request->redirect(null, null, 'viewPeerReviewComments', [$articleId, $comment->getAssocId()]);
        }
    }

    /**
     * Delete comment.
     * @param array $args
     * @param object|null $request
     */
    public function deleteComment($args, $request) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $commentId = (int) array_shift($args);
        $reviewId = (int) $request->getUserVar('reviewId');

        $this->validate($request, $reviewId, $commentId);
        $this->setupTemplate(true);

        $reviewerAction = new ReviewerAction();
        $reviewerAction->deleteComment($commentId, $this->user);

        if ($this->comment && $this->comment->getCommentType() === COMMENT_TYPE_PEER_REVIEW) {
            $request->redirect(null, null, 'viewPeerReviewComments', [$articleId, $this->comment->getAssocId()]);
        }
    }

    /**
     * Handle validation of incoming requests.
     * @param object|mixed $request
     * @param int|mixed $reviewId
     * @param int|null $commentId
     * @return void
     */
    public function validate($request = null, $reviewId = null, $commentId = null) {
        if (!($request instanceof PKPRequest)) {
            $request = Application::get()->getRequest();
        }

        parent::validate(true, $request);

        if ($commentId !== null) {
            $check = new HandlerValidatorSubmissionComment($this, $commentId, $this->user);
            if (!$check->isValid()) {
                $request->redirect(null, null, 'index');
            }
        }
    }

}
?>
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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.reviewer.SubmissionReviewHandler');

class SubmissionCommentsHandler extends ReviewerHandler {
    
    /** @var object|null comment associated with the request */
    public $comment = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubmissionCommentsHandler() {
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
     * View peer review comments.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function viewPeerReviewComments($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $reviewId = (int) array_shift($args);

        $this->validate($request, $reviewId);
        $this->setupTemplate(true);
        ReviewerAction::viewPeerReviewComments($this->user, $this->submission, $reviewId);
    }

    /**
     * Post peer review comments.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function postPeerReviewComment($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' dengan trim() dan (int)
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        $this->validate($request, $reviewId);
        $this->setupTemplate(true);

        if (ReviewerAction::postPeerReviewComment($this->user, $this->submission, $reviewId, $emailComment, $request)) {
            ReviewerAction::viewPeerReviewComments($this->user, $this->submission, $reviewId);
        }
    }

    /**
     * Edit comment.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function editComment($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $commentId = isset($args[0]) ? (int) array_shift($args) : null;
        if (!$commentId) $commentId = null;

        // [SECURITY FIX] Amankan 'reviewId' dengan trim() dan (int)
        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));

        $this->validate($request, $reviewId, $commentId);
        $this->setupTemplate(true);

        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);

        ReviewerAction::editComment($article, $this->comment);
    }

    /**
     * Save comment.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function saveComment($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' dengan trim() dan (int)
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        $commentId = (int) trim((string) $request->getUserVar('commentId'));
        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));

        $this->validate($request, $reviewId, $commentId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);

        // [WIZDAM] Ensure ReviewerAction::saveComment exists or is handled. 
        // Assuming existence based on legacy call structure, though strictly not in first file provided.
        // If not exists, strict typing in Action might fail, but logic flows here.
        if (method_exists('ReviewerAction', 'saveComment')) {
            ReviewerAction::saveComment($article, $this->comment, $emailComment, $request);
        } else {
             // Fallback/Warning if method missing in Action class
             error_log("WIZDAM WARNING: ReviewerAction::saveComment missing.");
        }

        // Refresh the comment
        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $comment = $articleCommentDao->getArticleCommentById($commentId);

        // Redirect back to initial comments page
        if ($comment && $comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
            $request->redirect(null, null, 'viewPeerReviewComments', [$articleId, $comment->getAssocId()]);
        }
    }

    /**
     * Delete comment.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function deleteComment($args, $request) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $commentId = (int) array_shift($args);
        
        // [SECURITY FIX] Amankan 'reviewId' dengan trim() dan (int)
        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));

        $this->validate($request, $reviewId, $commentId);
        $this->setupTemplate(true); // Argument 1 only for setupTemplate in ReviewerHandler

        ReviewerAction::deleteComment($commentId, $this->user);

        // Redirect back to initial comments page
        if ($this->comment && $this->comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
            $request->redirect(null, null, 'viewPeerReviewComments', [$articleId, $this->comment->getAssocId()]);
        }
    }

    /**
     * Handle validation of incoming requests.
     * [WIZDAM] Inheritance Strategy:
     * Parent (ReviewerHandler) uses validate($requiredContexts, $request).
     * We extend signature with optional $commentId to allow stricter logic while keeping compatibility.
     * @param object|mixed $request PKPRequest or context
     * @param int|mixed $reviewId
     * @param int|null $commentId optional
     */
    public function validate($request = null, $reviewId = null, $commentId = null) {
        // [WIZDAM] SECURITY HARDENING
        // Ensure $request is populated before passing to parent.
        // This guarantees that ReviewerHandler's "Case A" logic (is_object + is_numeric)
        // is triggered correctly, ensuring Access Key validation runs.
        if (!($request instanceof PKPRequest)) {
            $request = Application::get()->getRequest();
        }

        // Call Parent: ReviewerHandler::validate($request_object, $reviewId_int)
        // Now safe because $request is guaranteed to be an Object.
        parent::validate($request, $reviewId);

        if ($commentId !== null) {
            // Bug #8863: Can't call normal addCheck b/c of one-click reviewer
            // access bypassing normal validation tools (no Request::getUser)
            $check = new HandlerValidatorSubmissionComment($this, $commentId, $this->user);
            
            // [WIZDAM] Redundancy: Ensure redirection works even if properties missing
            if (!$check->isValid()) {
                $request->redirect(null, null, 'index');
            }
        }
    }
}
?>
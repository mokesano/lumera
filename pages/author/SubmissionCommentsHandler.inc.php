<?php
declare(strict_types=1);

/**
 * @file pages/author/SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_author
 *
 * @brief Handle requests for submission comments.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.author.TrackSubmissionHandler');

class SubmissionCommentsHandler extends AuthorHandler {
    /** @var Comment|null comment associated with the request */
    public $comment;

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
                "Class '" . get_class($this) . "' uses deprecated constructor parent::SubmissionCommentsHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * View editor decision comments.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewEditorDecisionComments($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);
        AuthorAction::viewEditorDecisionComments($this->submission);
    }

    /**
     * View copyedit comments.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewCopyeditComments($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);
        AuthorAction::viewCopyeditComments($this->submission);
    }

    /**
     * Post copyedit comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function postCopyeditComment($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = (int) $request->getUserVar('saveAndEmail') === 1;

        if (AuthorAction::postCopyeditComment($this->submission, $emailComment, $request)) {
            AuthorAction::viewCopyeditComments($this->submission);
        }
    }

    /**
     * View proofread comments.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewProofreadComments($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);
        AuthorAction::viewProofreadComments($this->submission);
    }

    /**
     * Post proofread comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function postProofreadComment($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = (int) $request->getUserVar('saveAndEmail') === 1;

        if (AuthorAction::postProofreadComment($this->submission, $emailComment, $request)) {
            AuthorAction::viewProofreadComments($this->submission);
        }
    }

    /**
     * View layout comments.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewLayoutComments($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);
        AuthorAction::viewLayoutComments($this->submission);
    }

    /**
     * Post layout comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function postLayoutComment($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = (int) $request->getUserVar('saveAndEmail') === 1;

        if (AuthorAction::postLayoutComment($this->submission, $emailComment, $request)) {
            AuthorAction::viewLayoutComments($this->submission);
        }
    }

    /**
     * Email an editor decision comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function emailEditorDecisionComment($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->setupTemplate($request, true);
        $this->validate(null, $request, $articleId);
        if (AuthorAction::emailEditorDecisionComment($this->submission, (int) $request->getUserVar('send'), $request)) {
            $request->redirect(null, null, 'submissionReview', [$articleId]);
        }
    }

    /**
     * Edit comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editComment($args, $request) {
        $articleId = (int) array_shift($args);
        $commentId = array_shift($args);

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);
        if ($this->comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
            // Cannot edit an editor decision comment.
            $request->redirect(null, $request->getRequestedPage());
        }
        AuthorAction::editComment($this->submission, $this->comment);
    }

    /**
     * Save comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveComment($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $commentId = (int) $request->getUserVar('commentId');

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = (int) $request->getUserVar('saveAndEmail') === 1;

        if ($this->comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
            // Cannot edit an editor decision comment.
            $request->redirect(null, $request->getRequestedPage());
        }

        AuthorAction::saveComment($this->submission, $this->comment, $emailComment, $request);

        // refresh the comment
        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $comment = $articleCommentDao->getArticleCommentById($commentId);

        // Redirect back to initial comments page
        if ($this->comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
            $request->redirect(null, null, 'viewEditorDecisionComments', $articleId);
        } elseif ($this->comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
            $request->redirect(null, null, 'viewCopyeditComments', $articleId);
        } elseif ($this->comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
            $request->redirect(null, null, 'viewLayoutComments', $articleId);
        } elseif ($this->comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
            $request->redirect(null, null, 'viewProofreadComments', $articleId);
        }
    }

    /**
     * Delete comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteComment($args, $request) {
        $articleId = (int) array_shift($args);
        $commentId = (int) array_shift($args);

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate(null, $request, $articleId);
        $this->setupTemplate($request, true);

        AuthorAction::deleteComment($commentId);

        // Redirect back to initial comments page
        if ($this->comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
            $request->redirect(null, null, 'viewEditorDecisionComments', $articleId);
        } elseif ($this->comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
            $request->redirect(null, null, 'viewCopyeditComments', $articleId);
        } elseif ($this->comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
            $request->redirect(null, null, 'viewLayoutComments', $articleId);
        } elseif ($this->comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
            $request->redirect(null, null, 'viewProofreadComments', $articleId);
        }
    }
}
?>
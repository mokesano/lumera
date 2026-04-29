<?php
declare(strict_types=1);

/**
 * @file pages/copyeditor/SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_copyeditor
 *
 * @brief Handle requests for submission comments.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.copyeditor.SubmissionCopyeditHandler');

class SubmissionCommentsHandler extends CopyeditorHandler {
    /** @var Comment|null comment associated with this request */
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
     * View layout comments.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewLayoutComments($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate($request, $articleId);
        $this->setupTemplate(true);
        CopyeditorAction::viewLayoutComments($this->submission);
    }

    /**
     * Post layout comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function postLayoutComment($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = (int) $request->getUserVar('saveAndEmail') === 1;

        if (CopyeditorAction::postLayoutComment($this->submission, $emailComment, $request)) {
            CopyeditorAction::viewLayoutComments($this->submission);
        }
    }

    /**
     * View copyedit comments.
     * @param array $args
     * @param PKPRequest $request
     */
    public function viewCopyeditComments($args, $request) {
        $articleId = (int) array_shift($args);
        $this->validate($request, $articleId);
        $this->setupTemplate(true);
        CopyeditorAction::viewCopyeditComments($this->submission);
    }

    /**
     * Post copyedit comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function postCopyeditComment($args, $request) {
        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = (int) $request->getUserVar('saveAndEmail') === 1;
        if (CopyeditorAction::postCopyeditComment($this->submission, $emailComment, $request)) {
            CopyeditorAction::viewCopyeditComments($this->submission);
        }
    }

    /**
     * Edit comment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editComment($args, $request) {
        $articleId = (int) array_shift($args);
        $commentId = (int) array_shift($args);

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($request, $articleId);
        $this->setupTemplate(true);
        CopyeditorAction::editComment($this->submission, $this->comment);
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
        $this->validate($request, $articleId);
        $comment = $this->comment;

        $this->setupTemplate(true);
        
        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = (int) $request->getUserVar('saveAndEmail') === 1;

        CopyeditorAction::saveComment($this->submission, $comment, $emailComment, $request);

        // refresh the comment
        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $comment = $articleCommentDao->getArticleCommentById($commentId);

        // Redirect back to initial comments page
        if ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
            $request->redirect(null, null, 'viewCopyeditComments', $articleId);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
            $request->redirect(null, null, 'viewLayoutComments', $articleId);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
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
        $this->validate($request, $articleId);
        $comment = $this->comment;

        $this->setupTemplate(true);

        CopyeditorAction::deleteComment($commentId);

        // Redirect back to initial comments page
        if ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
            $request->redirect(null, null, 'viewCopyeditComments', $articleId);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
            $request->redirect(null, null, 'viewLayoutComments', $articleId);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
            $request->redirect(null, null, 'viewProofreadComments', $articleId);
        }
    }
}
?>
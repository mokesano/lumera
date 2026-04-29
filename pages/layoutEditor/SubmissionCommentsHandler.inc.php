<?php
declare(strict_types=1);

/**
 * @file pages/layoutEditor/SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_layoutEditor
 *
 * @brief Handle requests for submission comments.
 */

import('pages.layoutEditor.SubmissionLayoutHandler');

class SubmissionCommentsHandler extends LayoutEditorHandler {
    
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
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }
    
    /**
     * View layout comments.
     * @param array $args
     * @param mixed $request
     */
    public function viewLayoutComments(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        LayoutEditorAction::viewLayoutComments($this->submission);
    }

    /**
     * Post layout comment.
     * @param array $args
     * @param mixed $request
     */
    public function postLayoutComment(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = $request->getUserVar('saveAndEmail') ? true : false;

        if (LayoutEditorAction::postLayoutComment($this->submission, $emailComment, $request)) {
            LayoutEditorAction::viewLayoutComments($this->submission);
        }
    }

    /**
     * View proofread comments.
     * @param array $args
     * @param mixed $request
     */
    public function viewProofreadComments(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        LayoutEditorAction::viewProofreadComments($this->submission);
    }

    /**
     * Post proofread comment.
     * @param array $args
     * @param mixed $request
     */
    public function postProofreadComment(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) $request->getUserVar('articleId');
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = $request->getUserVar('saveAndEmail') ? true : false;

        if (LayoutEditorAction::postProofreadComment($this->submission, $emailComment, $request)) {
            LayoutEditorAction::viewProofreadComments($this->submission);
        }
    }

    /**
     * Edit comment.
     * @param array $args
     * @param mixed $request
     */
    public function editComment(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $commentId = (int) ($args[1] ?? 0);

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        LayoutEditorAction::editComment($this->submission, $this->comment);
    }

    /**
     * Save comment.
     * @param array $args
     * @param mixed $request
     */
    public function saveComment(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) $request->getUserVar('articleId');
        $commentId = (int) $request->getUserVar('commentId');

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        $emailComment = $request->getUserVar('saveAndEmail') ? true : false;

        LayoutEditorAction::saveComment($this->submission, $this->comment, $emailComment, $request);

        // Redirect back to initial comments page
        if ($this->comment->getCommentType() === COMMENT_TYPE_LAYOUT) {
            $request->redirect(null, null, 'viewLayoutComments', $articleId);
        } elseif ($this->comment->getCommentType() === COMMENT_TYPE_PROOFREAD) {
            $request->redirect(null, null, 'viewProofreadComments', $articleId);
        }
    }

    /**
     * Delete comment.
     * @param array $args
     * @param mixed $request
     */
    public function deleteComment(array $args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $articleId = (int) ($args[0] ?? 0);
        $commentId = (int) ($args[1] ?? 0);

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        LayoutEditorAction::deleteComment($commentId);

        // Redirect back to initial comments page
        if ($this->comment->getCommentType() === COMMENT_TYPE_LAYOUT) {
            $request->redirect(null, null, 'viewLayoutComments', $articleId);
        } elseif ($this->comment->getCommentType() === COMMENT_TYPE_PROOFREAD) {
            $request->redirect(null, null, 'viewProofreadComments', $articleId);
        }
    }
}

?>
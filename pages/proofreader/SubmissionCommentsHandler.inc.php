<?php
declare(strict_types=1);

/**
 * @file pages/proofreader/SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_proofreader
 *
 * @brief Handle requests for submission comments.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.proofreader.SubmissionProofreadHandler');

class SubmissionCommentsHandler extends ProofreaderHandler {
    
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
     * View proofread comments.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function viewProofreadComments($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        ProofreaderAction::viewProofreadComments($this->submission);
    }

    /**
     * Post proofread comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function postProofreadComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' dengan trim() dan (int)
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        if (ProofreaderAction::postProofreadComment($this->submission, $emailComment, $request)) {
            ProofreaderAction::viewProofreadComments($this->submission);
        }
    }

    /**
     * View layout comments.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function viewLayoutComments($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        ProofreaderAction::viewLayoutComments($this->submission);
    }

    /**
     * Post layout comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function postLayoutComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' dengan trim() dan (int)
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        if (ProofreaderAction::postLayoutComment($this->submission, $emailComment, $request)) {
            ProofreaderAction::viewLayoutComments($this->submission);
        }
    }

    /**
     * Edit comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function editComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $commentId = (int) array_shift($args);

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        ProofreaderAction::editComment($this->submission, $this->comment);
    }

    /**
     * Save comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function saveComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' dengan trim() dan (int)
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        // [SECURITY FIX] Amankan 'commentId' dengan trim() dan (int)
        $commentId = (int) trim((string) $request->getUserVar('commentId'));

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($request, $articleId);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        ProofreaderAction::saveComment($this->submission, $this->comment, $emailComment, $request);

        // Determine which page to redirect back to.
        $commentPageMap = [
            COMMENT_TYPE_PROOFREAD => 'viewProofreadComments',
            COMMENT_TYPE_LAYOUT => 'viewLayoutComments'
        ];

        // Redirect back to initial comments page
        $request->redirect(null, null, $commentPageMap[$this->comment->getCommentType()], $articleId);
    }

    /**
     * Delete comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function deleteComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $commentId = (int) array_shift($args);

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($request, $articleId);
        ProofreaderAction::deleteComment($commentId);

        // Determine which page to redirect back to.
        $commentPageMap = [
            COMMENT_TYPE_PROOFREAD => 'viewProofreadComments',
            COMMENT_TYPE_LAYOUT => 'viewLayoutComments'
        ];

        // Redirect back to initial comments page
        $request->redirect(null, null, $commentPageMap[$this->comment->getCommentType()], $articleId);
    }
}
?>
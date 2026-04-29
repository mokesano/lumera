<?php
declare(strict_types=1);

/**
 * @file pages/sectionEditor/SubmissionCommentsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionCommentsHandler
 * @ingroup pages_sectionEditor
 *
 * @brief Handle requests for submission comments.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.sectionEditor.SubmissionEditHandler');

class SubmissionCommentsHandler extends SectionEditorHandler {
    
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
     * @param object|null $request PKPRequest
     */
    public function viewPeerReviewComments($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $reviewId = (int) array_shift($args);

        $this->validate($articleId);
        $this->setupTemplate(true);

        SectionEditorAction::viewPeerReviewComments($this->submission, $reviewId);
    }

    /**
     * Post peer review comments.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function postPeerReviewComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' (ID integer) dengan trim()
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        // [SECURITY FIX] Amankan 'reviewId' (ID integer) dengan trim()
        $reviewId = (int) trim((string) $request->getUserVar('reviewId'));

        $this->validate($articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        if (SectionEditorAction::postPeerReviewComment($this->submission, $reviewId, $emailComment, $request)) {
            SectionEditorAction::viewPeerReviewComments($this->submission, $reviewId);
        }
    }

    /**
     * View editor decision comments.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function viewEditorDecisionComments($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);

        $this->validate($articleId);
        $this->setupTemplate(true);

        SectionEditorAction::viewEditorDecisionComments($this->submission);
    }

    /**
     * Post editor decision comments.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function postEditorDecisionComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' (ID integer) dengan trim()
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        $this->validate($articleId);

        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        if (SectionEditorAction::postEditorDecisionComment($this->submission, $emailComment, $request)) {
            SectionEditorAction::viewEditorDecisionComments($this->submission);
        }
    }

    /**
     * View copyedit comments.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function viewCopyeditComments($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);

        $this->validate($articleId);
        $this->setupTemplate(true);

        SectionEditorAction::viewCopyeditComments($this->submission);
    }

    /**
     * Post copyedit comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function postCopyeditComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' (ID integer) dengan trim()
        $articleId = (int) trim((string) $request->getUserVar('articleId'));

        $this->validate($articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        if (SectionEditorAction::postCopyeditComment($this->submission, $emailComment, $request)) {
            SectionEditorAction::viewCopyeditComments($this->submission);
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

        $this->validate($articleId);
        $this->setupTemplate(true);

        SectionEditorAction::viewLayoutComments($this->submission, $request);
    }

    /**
     * Post layout comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function postLayoutComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' (ID integer) dengan trim()
        $articleId = (int) trim((string) $request->getUserVar('articleId'));

        $this->validate($articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        if (SectionEditorAction::postLayoutComment($this->submission, $emailComment, $request)) {
            SectionEditorAction::viewLayoutComments($this->submission);
        }
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

        $this->validate($articleId);
        $this->setupTemplate(true);

        SectionEditorAction::viewProofreadComments($this->submission);
    }

    /**
     * Post proofread comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function postProofreadComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' (ID integer) dengan trim()
        $articleId = (int) trim((string) $request->getUserVar('articleId'));

        $this->validate($articleId);
        $this->setupTemplate(true);

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        if (SectionEditorAction::postProofreadComment($this->submission, $emailComment, $request)) {
            SectionEditorAction::viewProofreadComments($this->submission);
        }
    }

    /**
     * Email an editor decision comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function emailEditorDecisionComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' (ID integer) - Ini sudah benar
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        $this->validate($articleId);

        $this->setupTemplate(true);
        
        // [FIX CRITICAL] Jangan gunakan (int) pada tombol 'send'.
        // Tombol submit mengirim string (misal: "Send"), jika di-(int) akan jadi 0 (False).
        // Kita hanya perlu cek apakah tombol ditekan (not null/true).
        $sendFlag = $request->getUserVar('send') ? true : false;
        
        if (SectionEditorAction::emailEditorDecisionComment($this->submission, $sendFlag, $request)) {
            
            // [FIX] Cek blindCcReviewers secara boolean juga, lebih aman daripada (int)
            // karena checkbox kadang mengirim nilai "on" (yang jika di-int jadi 0).
            $blindCcVar = $request->getUserVar('blindCcReviewers');
            $blindCcReviewersFlag = (!empty($blindCcVar)); 
            
            if ($blindCcReviewersFlag) {
                // Pastikan $articleId yang digunakan sudah diamankan
                $request->redirect(null, null, 'bccEditorDecisionCommentToReviewers', null, ['articleId' => $articleId]);
            } else {
                $request->redirect(null, null, 'submissionReview', [$articleId]);
            }
        }
    }

    /**
     * Blind CC the editor decision email to reviewers.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function bccEditorDecisionCommentToReviewers($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' (ID integer) dengan trim()
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        $this->validate($articleId);

        $this->setupTemplate(true);
        
        // [SECURITY FIX] Amankan 'send' sebagai flag boolean with (int) trim()
        $sendFlag = (int) trim((string) $request->getUserVar('send'));
        
        if (SectionEditorAction::bccEditorDecisionCommentToReviewers($this->submission, $sendFlag, $request)) {
            // Kita asumsikan $articleId di sini sudah diamankan
            $request->redirect(null, null, 'submissionReview', [$articleId]);
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
        $this->validate($articleId);
        $comment = $this->comment;

        $this->setupTemplate(true);

        if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
            // Cannot edit an editor decision comment.
            $request->redirect(null, $request->getRequestedPage());
        }

        SectionEditorAction::editComment($this->submission, $comment);
    }

    /**
     * Save comment.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function saveComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        // [SECURITY FIX] Amankan 'articleId' (ID integer) dengan trim()
        $articleId = (int) trim((string) $request->getUserVar('articleId'));
        
        // [SECURITY FIX] Amankan 'commentId' (ID integer) dengan trim()
        $commentId = (int) trim((string) $request->getUserVar('commentId'));

        // If the user pressed the "Save and email" button, then email the comment.
        // [SECURITY FIX] Amankan 'saveAndEmail' sebagai flag boolean dengan (int) trim()
        $saveAndEmailInput = (int) trim((string) $request->getUserVar('saveAndEmail'));
        $emailComment = $saveAndEmailInput != 0;

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($articleId);
        $comment = $this->comment;

        $this->setupTemplate(true);

        if ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
            // Cannot edit an editor decision comment.
            $request->redirect(null, $request->getRequestedPage());
        }

        // Save the comment.
        SectionEditorAction::saveComment($this->submission, $comment, $emailComment, $request);

        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $comment = $articleCommentDao->getArticleCommentById($commentId);

        // Redirect back to initial comments page
        if ($comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
            $request->redirect(null, null, 'viewPeerReviewComments', [$articleId, $comment->getAssocId()]);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
            $request->redirect(null, null, 'viewEditorDecisionComments', $articleId);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
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
     * @param object|null $request PKPRequest
     */
    public function deleteComment($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $articleId = (int) array_shift($args);
        $commentId = (int) array_shift($args);

        $this->addCheck(new HandlerValidatorSubmissionComment($this, $commentId));
        $this->validate($articleId);
        $comment = $this->comment;

        $this->setupTemplate(true);

        SectionEditorAction::deleteComment($commentId);

        // Redirect back to initial comments page
        if ($comment->getCommentType() == COMMENT_TYPE_PEER_REVIEW) {
            $request->redirect(null, null, 'viewPeerReviewComments', [$articleId, $comment->getAssocId()]);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_EDITOR_DECISION) {
            $request->redirect(null, null, 'viewEditorDecisionComments', $articleId);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_COPYEDIT) {
            $request->redirect(null, null, 'viewCopyeditComments', $articleId);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_LAYOUT) {
            $request->redirect(null, null, 'viewLayoutComments', $articleId);
        } elseif ($comment->getCommentType() == COMMENT_TYPE_PROOFREAD) {
            $request->redirect(null, null, 'viewProofreadComments', $articleId);
        }
    }
}
?>
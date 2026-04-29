<?php
declare(strict_types=1);

/**
 * @file classes/submission/form/comment/PeerReviewCommentForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PeerReviewCommentForm
 * @ingroup submission_form
 *
 * @brief Comment form.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.submission.form.comment.CommentForm');

class PeerReviewCommentForm extends CommentForm {

    /** @var int|null the ID of the review assignment */
    public $reviewId = null;

    /** @var array the IDs of the inserted comments */
    public $insertedComments = [];

    /**
     * Constructor.
     * @param object $article Article
     * @param int $reviewId
     * @param int $roleId
     */
    public function __construct($article, $reviewId, $roleId) {
        parent::__construct($article, COMMENT_TYPE_PEER_REVIEW, $roleId, $reviewId);
        $this->reviewId = (int) $reviewId;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PeerReviewCommentForm($article, $reviewId, $roleId) {
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
     * Display the form.
     * @param object|null $request
     * @param object|null $template
     */
    public function display($request = null, $template = null) {
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $reviewAssignment = $reviewAssignmentDao->getById($this->reviewId);
        $reviewLetters = $reviewAssignmentDao->getReviewIndexesForRound($this->article->getId(), $this->article->getCurrentRound());

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('commentType', 'peerReview');
        $templateMgr->assign('pageTitle', 'submission.comments.review');
        $templateMgr->assign('commentAction', 'postPeerReviewComment');
        $templateMgr->assign('commentTitle', strip_tags($this->article->getLocalizedTitle()));
        $templateMgr->assign('isLocked', isset($reviewAssignment) && $reviewAssignment->getDateCompleted() != null);
        $templateMgr->assign('canEmail', false); // Previously, editors could always email.
        $templateMgr->assign('showReviewLetters', ($this->roleId == ROLE_ID_EDITOR || $this->roleId == ROLE_ID_SECTION_EDITOR) ? true : false);
        $templateMgr->assign('reviewLetters', $reviewLetters);
        $templateMgr->assign('reviewer', ROLE_ID_REVIEWER);
        $templateMgr->assign('hiddenFormParams', 
            [
                'articleId' => $this->article->getId(),
                'reviewId' => $this->reviewId
            ]
        );

        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'commentTitle',
                'authorComments',
                'comments'
            ]
        );
    }

    /**
     * Add the comment.
     */
    public function execute($object = NULL) {
        // Personalized execute() method since now there are possibly two comments contained within each form submission.

        $commentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $this->insertedComments = [];

        // Assign all common information    
        $baseComment = new ArticleComment();
        $baseComment->setCommentType($this->commentType);
        $baseComment->setRoleId($this->roleId);
        $baseComment->setArticleId($this->article->getId());
        $baseComment->setAssocId($this->assocId);
        $baseComment->setAuthorId($this->user->getId());
        $baseComment->setCommentTitle($this->getData('commentTitle'));
        $baseComment->setDatePosted(Core::getCurrentDate());

        // If comments "For authors and editor" submitted
        if ($this->getData('authorComments') != null) {
            // [WIZDAM] Clone object to prevent reference pollution in PHP 8
            $authorComment = clone $baseComment;
            $authorComment->setComments($this->getData('authorComments'));
            $authorComment->setViewable(1);
            $this->insertedComments[] = $commentDao->insertArticleComment($authorComment);
        }        

        // If comments "For editor" submitted
        if ($this->getData('comments') != null) {
            // [WIZDAM] Clone object to prevent reference pollution in PHP 8
            $editorComment = clone $baseComment;
            $editorComment->setComments($this->getData('comments'));
            $editorComment->setViewable(null);
            $this->insertedComments[] = $commentDao->insertArticleComment($editorComment);
        }
    }
}
?>
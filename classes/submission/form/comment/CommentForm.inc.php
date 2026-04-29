<?php
declare(strict_types=1);

/**
 * @file classes/submission/form/comment/CommentForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CommentForm
 * @ingroup submission_form
 * @see Comment, ArticleCommentDAO
 *
 * @brief Comment form.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.form.Form');

class CommentForm extends Form {

    /** @var int|null the comment type */
    public $commentType = null;

    /** @var int|null the role id of the comment poster */
    public $roleId = null;

    /** @var object|null Article current article */
    public $article = null;

    /** @var object|null User comment author */
    public $user = null;

    /** @var int|null the ID of the comment after insertion */
    public $commentId = null;

    /** @var int|null the association ID */
    public $assocId = null;

    /**
     * Constructor.
     * @param object $article Article
     * @param int $commentType
     * @param int $roleId
     * @param int|null $assocId
     */
    public function __construct($article, $commentType, $roleId, $assocId = null) {
        AppLocale::requireComponents([LOCALE_COMPONENT_APP_EDITOR]); // editor.article.commentsRequired

        if ($commentType == COMMENT_TYPE_PEER_REVIEW) {
            parent::__construct('submission/comment/peerReviewComment.tpl');
        } elseif ($commentType == COMMENT_TYPE_EDITOR_DECISION) {
            parent::__construct('submission/comment/editorDecisionComment.tpl');
        } else {
            parent::__construct('submission/comment/comment.tpl');
        }

        $this->article = $article;
        $this->commentType = (int) $commentType;
        $this->roleId = (int) $roleId;
        $this->assocId = $assocId == null ? (int) $article->getId() : (int) $assocId;

        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $this->user = $request->getUser();

        if ($commentType != COMMENT_TYPE_PEER_REVIEW) {
            $this->addCheck(new FormValidator($this, 'comments', 'required', 'editor.article.commentsRequired'));
        }
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CommentForm($article, $commentType, $roleId, $assocId = null) {
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
     * Set the user this comment form is associated with.
     * @param object $user User
     */
    public function setUser($user) {
        $this->user = $user;
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param object|null $template
     */
    public function display($request = null, $template = null) {
        $article = $this->article;

        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $articleComments = $articleCommentDao->getArticleComments($article->getId(), $this->commentType, $this->assocId);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('articleId', $article->getId());
        $templateMgr->assign('commentTitle', strip_tags($article->getLocalizedTitle()));
        
        $user = $this->user;
        $templateMgr->assign('userId', $user ? $user->getId() : null);
        $templateMgr->assign('articleComments', $articleComments);

        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(
            [
                'commentTitle',
                'comments',
                'viewable'
            ]
        );
    }

    /**
     * Add the comment.
     */
    public function execute($object = NULL) {
        $commentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $article = $this->article;

        // Insert new comment        
        $comment = new ArticleComment();
        $comment->setCommentType($this->commentType);
        $comment->setRoleId($this->roleId);
        $comment->setArticleId($article->getId());
        $comment->setAssocId($this->assocId);
        $comment->setAuthorId($this->user->getId());
        $comment->setCommentTitle($this->getData('commentTitle'));
        $comment->setComments($this->getData('comments'));
        $comment->setDatePosted(Core::getCurrentDate());
        $comment->setViewable($this->getData('viewable'));

        $this->commentId = $commentDao->insertArticleComment($comment);
    }

    /**
     * Email the comment.
     * @param array $recipients array of recipients (email address => name)
     * @param object $request PKPRequest
     */
    public function email($recipients, $request) {
        $article = $this->article;
        $articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
        $journal = $request->getJournal();

        import('classes.mail.ArticleMailTemplate');
        $email = new ArticleMailTemplate($article, 'SUBMISSION_COMMENT');
        $email->setFrom($this->user->getEmail(), $this->user->getFullName());

        $commentText = $this->getData('comments');

        // Individually send an email to each of the recipients.
        foreach ($recipients as $emailAddress => $name) {
            $email->addRecipient($emailAddress, $name);

            $paramArray = [
                'name' => $name,
                'commentName' => $this->user->getFullName(),
                'comments' => PKPString::html2text($commentText)
            ];

            $email->sendWithParams($paramArray, $request);
            $email->clearRecipients();
        }
    }
}
?>
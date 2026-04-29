<?php
declare(strict_types=1);

/**
 * @file pages/comment/CommentHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CommentHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user comments.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.rt.ojs.RTDAO');
import('classes.rt.ojs.JournalRT');
import('classes.handler.Handler');

class CommentHandler extends Handler {
    
    /** @var Issue|null issue associated with this request */
    public $issue = null;

    /** @var Article|null article associated with this request */
    public $article = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CommentHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::CommentHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * View a comment
     * @param array $args
     * @param PKPRequest $request
     */
    public function view($args, $request) {
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;
        $commentId = isset($args[2]) ? (int) $args[2] : 0;

        $this->validate($request, $articleId);
        $article = $this->article;

        $user = $request->getUser();
        $userId = isset($user) ? $user->getId() : null;

        $commentDao = DAORegistry::getDAO('CommentDAO');
        $comment = $commentDao->getById($commentId, $articleId, 2);

        $journal = $request->getJournal();

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $isManager = $roleDao->userHasRole($journal->getId(), $userId, ROLE_ID_JOURNAL_MANAGER);

        if (!$comment) {
            $comments = $commentDao->getRootCommentsBySubmissionId($articleId, 1);
        } else {
            $comments = $comment->getChildren();
        }

        $this->setupTemplate($request, $article, $galleyId, $comment);

        $templateMgr = TemplateManager::getManager();
        if ((int) $request->getUserVar('refresh')) {
            $templateMgr->setCacheability(CACHEABILITY_NO_CACHE);
        }
        if ($comment) {
            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('comment', $comment);
            $templateMgr->assign('parent', $commentDao->getById($comment->getParentCommentId(), $articleId));
        }
        $templateMgr->assign('comments', $comments);
        $templateMgr->assign('articleId', $articleId);
        $templateMgr->assign('galleyId', $galleyId);
        $templateMgr->assign('enableComments', $journal->getSetting('enableComments'));
        $templateMgr->assign('isManager', $isManager);

        $templateMgr->display('comment/comments.tpl');
    }

    /**
     * Add a comment
     * @param array $args
     * @param PKPRequest $request
     */
    public function add($args, $request) {
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;
        $parentId = isset($args[2]) ? (int) $args[2] : 0;
        $journal = $request->getJournal();
        $commentDao = DAORegistry::getDAO('CommentDAO');

        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($articleId);

        $parent = $commentDao->getById($parentId, $articleId);
        if (isset($parent) && $parent->getSubmissionId() != $articleId) {
            $request->redirect(null, null, 'view', [$articleId, $galleyId]);
        }

        $this->validate($request, $articleId);
        $this->setupTemplate($request, $publishedArticle, $galleyId, $parent);

        // Bring in comment constants
        $enableComments = $journal->getSetting('enableComments');
        switch ($enableComments) {
            case COMMENTS_UNAUTHENTICATED:
                break;
            case COMMENTS_AUTHENTICATED:
            case COMMENTS_ANONYMOUS:
                // The user must be logged in to post comments.
                if (!$request->getUser()) {
                    Validation::redirectLogin();
                }
                break;
            default:
                // Comments are disabled.
                Validation::redirectLogin();
        }

        import('classes.comment.form.CommentForm');
        $commentForm = new CommentForm(null, $articleId, $galleyId, isset($parent) ? $parentId : null);
        $commentForm->initData();

        if (isset($args[3]) && $args[3]=='save') {
            $commentForm->readInputData();
            if ($commentForm->validate()) {
                $commentForm->execute();

                // Send a notification to associated users
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $articleDao = DAORegistry::getDAO('ArticleDAO');
                $article = $articleDao->getArticle($articleId);
                $notificationUsers = $article->getAssociatedUserIds();
                foreach ($notificationUsers as $userRole) {
                    $notificationManager->createNotification(
                        $request, $userRole['id'], NOTIFICATION_TYPE_USER_COMMENT,
                        $article->getJournalId(), ASSOC_TYPE_ARTICLE, $article->getId()
                    );
                }

                $request->redirect(null, null, 'view', [$articleId, $galleyId, $parentId], ['refresh' => 1]);
            }
        }

        $commentForm->display();
    }

    /**
     * Delete the specified comment and all its children.
     * @param array $args
     * @param PKPRequest $request
     */
    public function delete($args, $request) {
        $articleId = isset($args[0]) ? (int) $args[0] : 0;
        $galleyId = isset($args[1]) ? (int) $args[1] : 0;
        $commentId = isset($args[2]) ? (int) $args[2] : 0;

        $this->validate($request, $articleId);
        $journal = $request->getJournal();
        $user = $request->getUser();
        $userId = isset($user) ? $user->getId() : null;

        $commentDao = DAORegistry::getDAO('CommentDAO');

        $roleDao = DAORegistry::getDAO('RoleDAO');
        if (!$roleDao->userHasRole($journal->getId(), $userId, ROLE_ID_JOURNAL_MANAGER)) {
            $request->redirect(null, 'index');
        }

        $comment = $commentDao->getById($commentId, $articleId, SUBMISSION_COMMENT_RECURSE_ALL);
        if ($comment) $commentDao->deleteComment($comment);

        $request->redirect(null, null, 'view', [$articleId, $galleyId], ['refresh' => '1']);
    }

    /**
     * Validation
     * @param PKPRequest $request
     * @param int $articleId
     * @return bool
     */
    public function validate($request, $articleId) {
        parent::validate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');

        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $article = $publishedArticleDao->getPublishedArticleByArticleId($articleId);

        // Bring in comment constants
        $commentDao = DAORegistry::getDAO('CommentDAO');

        $enableComments = $journal->getSetting('enableComments');

        if ((!Validation::isLoggedIn() && $journalSettingsDao->getSetting($journalId,'restrictArticleAccess')) || ($article && !$article->getEnableComments()) || ($enableComments != COMMENTS_ANONYMOUS && $enableComments != COMMENTS_AUTHENTICATED && $enableComments != COMMENTS_UNAUTHENTICATED)) {
            Validation::redirectLogin();
        }

        // Subscription Access
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getIssueByArticleId($articleId);

        if (isset($issue) && isset($article)) {
            import('classes.issue.IssueAction');
            $subscriptionRequired = IssueAction::subscriptionRequired($issue);
            $subscribedUser = IssueAction::subscribedUser($journal, $issue->getId(), $articleId);

            if (!(!$subscriptionRequired || $article->getAccessStatus() == ARTICLE_ACCESS_OPEN || $subscribedUser)) {
                $request->redirect(null, 'index');
            }
        } else {
            $request->redirect(null, 'index');
        }

        $this->issue = $issue;
        $this->article = $article;
        return true;
    }

    /**
     * Set up the comment template.
     * @param PKPRequest $request
     * @param Article $article
     * @param int $galleyId
     * @param Comment|null $comment
     */
    public function setupTemplate($request, $article, $galleyId, $comment = null) {
        parent::setupTemplate();
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_READER);
        $templateMgr = TemplateManager::getManager();
        $journal = $request->getJournal();

        if (!$journal || !$journal->getSetting('restrictSiteAccess')) {
            $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        }

        $pageHierarchy = [
            [
                $request->url(null, 'article', 'view', [
                    $article->getBestArticleId($request->getJournal()), $galleyId
                ]),
                PKPString::stripUnsafeHtml($article->getLocalizedTitle()),
                true
            ]
        ];

        if ($comment) {
            $pageHierarchy[] = [
                $request->url(null, 'comment', 'view', [$article->getId(), $galleyId]),
                'comments.readerComments'
            ];
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }
}
?>
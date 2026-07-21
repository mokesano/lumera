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

        /** @var CommentDAO $commentDao */
        $commentDao = DAORegistry::getDAO('CommentDAO');
        $comment = $commentDao->getById($commentId, $articleId, 2);

        $journal = $request->getJournal();

        /** @var RoleDAO $roleDao */
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
        
        /** @var CommentDAO $commentDao */
        $commentDao = DAORegistry::getDAO('CommentDAO');
        /** @var PublishedArticleDAO $publishedArticleDao */
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

                /** @var ArticleDAO $articleDao */
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

        /** @var CommentDAO $commentDao */
        $commentDao = DAORegistry::getDAO('CommentDAO');
        /** @var RoleDAO $roleDao */
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
     * @param array|null $requiredContexts
     * @param PKPRequest|int|null $request
     * @return bool
     */
    public function validate($requiredContexts = null, $request = null) {
        // [LUMERA FIX] Adapter untuk pemanggilan legacy: $this->validate($request, $articleId)
        $actualRequest = null;
        $articleId = 0;

        if (is_object($requiredContexts) && is_a($requiredContexts, 'PKPRequest') && is_numeric($request)) {
            $actualRequest = $requiredContexts;
            $articleId = (int) $request;
        } else {
            // Dipanggil sebagai: parent::validate($requiredContexts, $request)
            $actualRequest = $request ?: $this->getRequest();
            $args = $actualRequest->getRequestedArgs();
            $articleId = isset($args[0]) ? (int) $args[0] : 0;
        }

        parent::validate($requiredContexts, $request);

        if (!$actualRequest) {
            $actualRequest = Application::get()->getRequest();
        }

        $journal = $actualRequest->getJournal();
        $journalId = $journal ? $journal->getId() : 0;
        
        /** @var JournalSettingsDAO $journalSettingsDao */
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $article = $publishedArticleDao->getPublishedArticleByArticleId($articleId);

        /** @var CommentDAO $commentDao */
        $commentDao = DAORegistry::getDAO('CommentDAO');

        $enableComments = $journal ? $journal->getSetting('enableComments') : COMMENTS_UNAUTHENTICATED;

        if (
            (!Validation::isLoggedIn() && $journalSettingsDao->getSetting($journalId, 'restrictArticleAccess')) || 
            ($article && !$article->getEnableComments()) || 
            ($enableComments != COMMENTS_ANONYMOUS && $enableComments != COMMENTS_AUTHENTICATED && $enableComments != COMMENTS_UNAUTHENTICATED)
        ) {
            Validation::redirectLogin();
        }

        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $article ? $issueDao->getIssueByArticleId($articleId) : null;

        if (isset($issue) && isset($article)) {
            import('classes.issue.IssueAction');
            $subscriptionRequired = IssueAction::subscriptionRequired($issue);
            $subscribedUser = IssueAction::subscribedUser($journal, $issue->getId(), $articleId);

            if (!(!$subscriptionRequired || $article->getAccessStatus() == ARTICLE_ACCESS_OPEN || $subscribedUser)) {
                $actualRequest->redirect(null, 'index');
            }
        } else {
            $actualRequest->redirect(null, 'index');
        }

        $this->issue = $issue;
        $this->article = $article;
        return true;
    }

    /**
     * Set up the comment template.
     * @param PKPRequest|null $request
     * @param Article|null $article
     * @param int|null $galleyId
     * @param Comment|null $comment
     */
    public function setupTemplate($request = null, $article = null, $galleyId = null, $comment = null) {
        parent::setupTemplate();

        $actualRequest = $request ?: $this->getRequest();
        $actualArticle = $article ?: $this->article;

        if (!$actualRequest || !$actualArticle) {
            return;
        }

        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_READER);
        $templateMgr = TemplateManager::getManager();
        $journal = $actualRequest->getJournal();

        if (!$journal || !$journal->getSetting('restrictSiteAccess')) {
            $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        }

        $pageHierarchy = [
            [
                $actualRequest->url(null, 'article', 'view', [
                    $actualArticle->getBestArticleId($actualRequest->getJournal()), 
                    $galleyId ?: 0
                ]),
                PKPString::stripUnsafeHtml($actualArticle->getLocalizedTitle()),
                true
            ]
        ];

        if ($comment) {
            $pageHierarchy[] = [
                $actualRequest->url(null, 'comment', 'view', [$actualArticle->getId(), $galleyId ?: 0]),
                'comments.readerComments'
            ];
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }
    
}
?>
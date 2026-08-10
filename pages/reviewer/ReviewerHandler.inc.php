<?php
declare(strict_types=1);

/**
 * @file pages/reviewer/ReviewerHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerHandler
 * @ingroup pages_reviewer
 *
 * @brief Handle requests for reviewer functions.
 */

import('classes.submission.reviewer.ReviewerAction');
import('classes.handler.Handler');

class ReviewerHandler extends Handler {
    
    /** @var object|null */
    public $user = null;

    /** @var object|null */
    public $submission = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorJournal($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReviewerHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display reviewer index page.
     * @param array $args
     * @param object|null $request
     */
    public function index($args = [], $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        $journal = $request->getJournal();
        $user = $request->getUser();

        if ($journal === null || $user === null) {
            $request->redirect(null, 'index');
            return;
        }

        /** @var ReviewerSubmissionDAO $reviewerSubmissionDao */
        $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
        $rangeInfo = $this->getRangeInfo('submissions');

        $page = isset($args[0]) ? (string) $args[0] : '';
        $active = ($page !== 'completed');
        if (!$active) {
            $page = 'completed';
        } else {
            $page = 'active';
        }

        $sort = trim((string) $request->getUserVar('sort'));
        $allowedSorts = ['id', 'title', 'status', 'dateAssigned', 'decision'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'title';
        }
        
        $sortDirection = trim(strtoupper((string) $request->getUserVar('sortDirection')));
        if ($sortDirection !== 'DESC') {
            $sortDirection = 'ASC';
        }

        if ($sort === 'decision') {
            $submissions = $reviewerSubmissionDao->getReviewerSubmissionsByReviewerId($user->getId(), $journal->getId(), $active, $rangeInfo);
            $submissionsArray = $submissions->toArray();
            
            usort($submissionsArray, function($s1, $s2) {
                $d1 = (string) $s1->getMostRecentDecision();
                $d2 = (string) $s2->getMostRecentDecision();
                return strcmp($d1, $d2);
            });

            if ($sortDirection === 'DESC') {
                $submissionsArray = array_reverse($submissionsArray);
            }

            import('lib.pkp.classes.core.ArrayItemIterator');
            $submissions = ArrayItemIterator::fromRangeInfo($submissionsArray, $rangeInfo);
        } else {
            $submissions = $reviewerSubmissionDao->getReviewerSubmissionsByReviewerId($user->getId(), $journal->getId(), $active, $rangeInfo, $sort, $sortDirection);
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageToDisplay', $page);
        $templateMgr->assign('submissions', $submissions);

        import('classes.submission.reviewAssignment.ReviewAssignment');
        $templateMgr->assign('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());

        import('classes.issue.IssueAction');
        $issueAction = new IssueAction();
        $templateMgr->register_function('print_issue_id', [$issueAction, 'smartyPrintIssueId']);
        $templateMgr->assign('helpTopicId', 'editorial.reviewersRole.submissions');
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);
        $templateMgr->display('reviewer/index.tpl');
    }

    /**
     * Validate access keys when allowed.
     * @param object $request
     * @param int $userId
     * @param int $reviewId
     * @param string|null $newKey
     * @return object|null
     */
    public static function validateAccessKey($request, $userId, $reviewId, $newKey = null) {
        $journal = $request->getJournal();
        if (!$journal || !$journal->getSetting('reviewerAccessKeysEnabled')) {
            return null;
        }

        if (!defined('REVIEWER_ACCESS_KEY_SESSION_VAR')) {
            define('REVIEWER_ACCESS_KEY_SESSION_VAR', 'ReviewerAccessKey');
        }

        import('lib.pkp.classes.security.AccessKeyManager');
        $accessKeyManager = new AccessKeyManager();

        $session = $request->getSession();
        if (!empty($newKey)) {
            if (Validation::isLoggedIn()) {
                Validation::logout();
            }
            $keyHash = $accessKeyManager->generateKeyHash((string) $newKey);
            $session->setSessionVar(REVIEWER_ACCESS_KEY_SESSION_VAR, $keyHash);
        } else {
            $keyHash = $session->getSessionVar(REVIEWER_ACCESS_KEY_SESSION_VAR);
        }

        $accessKey = $accessKeyManager->validateKey('ReviewerContext', $userId, $keyHash, $reviewId);

        if ($accessKey) {
            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            return $userDao->getUser($accessKey->getUserId(), false);
        }

        return null;
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
     * @param int $articleId
     * @param int $reviewId
     * @return void
     */
    public function setupTemplate($subclass = false, $articleId = 0, $reviewId = 0) {
        parent::setupTemplate();
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_SUBMISSION, 
            LOCALE_COMPONENT_APP_EDITOR
        );
        
        $templateMgr = TemplateManager::getManager();
        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        
        $pageHierarchy = [
            [$router->url($request, null, 'user'), 'navigation.user'], 
            [$router->url($request, null, 'reviewer'), 'user.role.reviewer']
        ];

        if ($articleId && $reviewId) {
            $pageHierarchy[] = [$router->url($request, null, 'reviewer', 'submission', [$reviewId]), "#$articleId", true];
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }

    /**
     * Validate that the user is an assigned reviewer for the article.
     * @param mixed $requiredContexts
     * @param mixed $request
     * @return void|bool
     */
    public function validate($requiredContexts = null, $request = null) {
        $reviewId = null;
        $realRequest = null;

        if (is_object($requiredContexts) && is_numeric($request)) {
            $realRequest = $requiredContexts;
            $reviewId = (int) $request;
        } elseif (is_object($requiredContexts) && $request === null) {
            $realRequest = $requiredContexts;
        } elseif (is_object($request)) {
            $realRequest = $request;
        }

        if ($realRequest === null) {
            $realRequest = Application::get()->getRequest();
            if ($reviewId === null) {
                $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_REVIEWER]));
                parent::validate();
                return;
            }
        }

        if ($reviewId !== null) {
            /** @var ReviewerSubmissionDAO $reviewerSubmissionDao */
            $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
            $journal = $realRequest->getJournal();
            $user = $realRequest->getUser();

            $isValid = true;
            $newKey = trim((string) $realRequest->getUserVar('key'));

            $reviewerSubmission = $reviewerSubmissionDao->getReviewerSubmission($reviewId);
            if (!$reviewerSubmission || $journal === null || $reviewerSubmission->getJournalId() !== $journal->getId()) {
                $isValid = false;
            } elseif ($user !== null && empty($newKey)) {
                if ($reviewerSubmission->getReviewerId() !== $user->getId()) {
                    $isValid = false;
                }
            } else {
                $user = self::validateAccessKey($realRequest, $reviewerSubmission->getReviewerId(), $reviewId, $newKey);
                if (!$user) {
                    $isValid = false;
                }
            }

            if (!$isValid) {
                $realRequest->redirect(null, $realRequest->getRequestedPage());
            }

            $this->submission = $reviewerSubmission;
            $this->user = $user;
            return true;
        }

        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_REVIEWER]));
        parent::validate($realRequest);
    }

}
?>
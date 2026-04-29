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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.submission.reviewer.ReviewerAction');
import('classes.handler.Handler');

class ReviewerHandler extends Handler {
    /** @var object|null user associated with the request */
    public $user = null;

    /** @var object|null submission associated with the request */
    public $submission = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorJournal($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewerHandler() {
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
     * Display reviewer index page.
     * @param array $args
     * @param object $request PKPRequest
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        $journal = $request->getJournal();
        $user = $request->getUser();
        $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
        $rangeInfo = $this->getRangeInfo('submissions');

        $page = isset($args[0]) ? $args[0] : '';
        switch($page) {
            case 'completed':
                $active = false;
                break;
            default:
                $page = 'active';
                $active = true;
        }

        $sort = trim((string) $request->getUserVar('sort'));
        $allowedSorts = ['id', 'title', 'status', 'dateAssigned', 'decision']; // Whitelist columns
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'title'; // Safe default
        }
        
        $sortDirection = trim(strtoupper((string) $request->getUserVar('sortDirection')));
        if ($sortDirection !== 'DESC') {
            $sortDirection = 'ASC'; // Safe default
        }

        if ($sort == 'decision') {
            $submissions = $reviewerSubmissionDao->getReviewerSubmissionsByReviewerId($user->getId(), $journal->getId(), $active, $rangeInfo);

            // Sort all submissions by status, which is too complex to do in the DB
            $submissionsArray = $submissions->toArray();
            
            // [WIZDAM] PHP 8 Compatibility: Replaced deprecated create_function with anonymous function
            usort($submissionsArray, function($s1, $s2) {
                // Ensure values are strings for strcmp, handling potential nulls
                $d1 = (string) $s1->getMostRecentDecision();
                $d2 = (string) $s2->getMostRecentDecision();
                return strcmp($d1, $d2);
            });

            if($sortDirection == 'DESC') {
                $submissionsArray = array_reverse($submissionsArray);
            }

            // Convert submission array back to an ItemIterator class
            import('lib.pkp.classes.core.ArrayItemIterator');
            $submissions = ArrayItemIterator::fromRangeInfo($submissionsArray, $rangeInfo);
        } else {
            $submissions = $reviewerSubmissionDao->getReviewerSubmissionsByReviewerId($user->getId(), $journal->getId(), $active, $rangeInfo, $sort, $sortDirection);
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('reviewerRecommendationOptions', ReviewAssignment::getReviewerRecommendationOptions());
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
     * Used by subclasses to validate access keys when they are allowed.
     * [WIZDAM] Made static to allow static calls from validation logic without warnings in PHP 8
     * @param object $request
     * @param int $userId The user this key refers to
     * @param int $reviewId The ID of the review this key refers to
     * @param string|null $newKey The new key name
     * @return object|null Valid user object if the key was valid; otherwise NULL.
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
        // Check to see if a new access key is being used.
        if (!empty($newKey)) {
            if (Validation::isLoggedIn()) {
                Validation::logout();
            }
            $keyHash = $accessKeyManager->generateKeyHash($newKey);
            $session->setSessionVar(REVIEWER_ACCESS_KEY_SESSION_VAR, $keyHash);
        } else {
            $keyHash = $session->getSessionVar(REVIEWER_ACCESS_KEY_SESSION_VAR);
        }

        // Now that we've gotten the key hash (if one exists), validate it.
        $accessKey = $accessKeyManager->validateKey(
            'ReviewerContext',
            $userId,
            $keyHash,
            $reviewId
        );

        if ($accessKey) {
            $userDao = DAORegistry::getDAO('UserDAO');
            $user = $userDao->getUser($accessKey->getUserId(), false);
            return $user;
        }

        return null;
    }

    /**
     * Setup common template variables.
     * @param boolean $subclass set to true if caller is below this handler in the hierarchy
     * @param int $articleId
     * @param int $reviewId
     */
    public function setupTemplate($subclass = false, $articleId = 0, $reviewId = 0) {
        parent::setupTemplate();
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_SUBMISSION, 
            LOCALE_COMPONENT_APP_EDITOR
        );
        $templateMgr = TemplateManager::getManager();
        $pageHierarchy = $subclass 
            ? [[Request::url(null, 'user'), 'navigation.user'], [Request::url(null, 'reviewer'), 'user.role.reviewer']]
            : [[Request::url(null, 'user'), 'navigation.user'], [Request::url(null, 'reviewer'), 'user.role.reviewer']];

        if ($articleId && $reviewId) {
            $pageHierarchy[] = [Request::url(null, 'reviewer', 'submission', $reviewId), "#$articleId", true];
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }

    //
    // Validation
    //

    /**
     * Validate that the user is an assigned reviewer for the article.
     * Redirects to reviewer index page if validation fails.
     * * [WIZDAM] Transition Mode: Loose signature to handle legacy parameter swapping
     * * @param mixed $requiredContexts (Could be Request object, or null)
     * @param mixed $request (Could be Request object, or reviewId (int), or null)
     */
    public function validate($requiredContexts = null, $request = null) {
        $reviewId = null;
        $realRequest = null;

        // Case A: Call from 'child' (SubmissionReviewHandler) - Legacy quirk
        // Parameter mismatch: validate($request, $reviewId)
        if (is_object($requiredContexts) && is_numeric($request)) {
            $realRequest = $requiredContexts;
            $reviewId = (int) $request;
        } 
        // Case B: Standard call (from 'index' or 'parent')
        // validate($request) or validate(null, $request)
        else if (is_object($requiredContexts) && $request === null) {
            $realRequest = $requiredContexts;
        } else if (is_object($request)) {
            $realRequest = $request;
        }

        // [WIZDAM] Final Fallback for Request
        if ($realRequest === null) {
             $realRequest = Application::get()->getRequest();
             // If we still can't determine context, assume generic validation
             if ($reviewId === null) {
                 $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_REVIEWER]));
                 parent::validate();
                 return;
             }
        }

        // --- Safe Validation ---

        // Case A continues: We have a specific reviewId
        if ($reviewId !== null) {
            $reviewerSubmissionDao = DAORegistry::getDAO('ReviewerSubmissionDAO');
            $journal = $realRequest->getJournal();
            $user = $realRequest->getUser();

            $isValid = true;
            $newKey = trim((string) $realRequest->getUserVar('key'));

            $reviewerSubmission = $reviewerSubmissionDao->getReviewerSubmission($reviewId);

            if (!$reviewerSubmission || $reviewerSubmission->getJournalId() != $journal->getId()) {
                $isValid = false;
            } elseif ($user && empty($newKey)) {
                if ($reviewerSubmission->getReviewerId() != $user->getId()) {
                    $isValid = false;
                }
            } else {
                // [WIZDAM] Static call to local static method
                $user = self::validateAccessKey($realRequest, $reviewerSubmission->getReviewerId(), $reviewId, $newKey);
                if (!$user) $isValid = false;
            }

            if (!$isValid) {
                $realRequest->redirect(null, $realRequest->getRequestedPage());
            }

            $this->submission = $reviewerSubmission;
            $this->user = $user;
            return true;
        }

        // Case B continues: No reviewId (Role checks only)
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_REVIEWER]));
        parent::validate($realRequest);
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file pages/author/AuthorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorHandler
 * @ingroup pages_author
 *
 * @brief Handle requests for journal author functions.
 */

import('classes.submission.author.AuthorAction');
import('classes.handler.Handler');

class AuthorHandler extends Handler {

    /** @var AuthorSubmission|null */
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
    public function AuthorHandler() {
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
     * Display journal author index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate(null, $request);
        $this->setupTemplate($request);
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $journal = $request->getJournal();
        $user = $request->getUser();
        
        if (!$user) {
            $request->redirect(null, 'login', null, null, ['source' => $request->url(null, 'author')]);
            return;
        }
        
        $rangeInfo = $this->getRangeInfo('submissions');
        /** @var AuthorSubmissionDAO $authorSubmissionDao */
        $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');

        $page = array_shift($args);
        switch($page) {
            case 'completed':
                $active = false;
                break;
            default:
                $page = 'active';
                $active = true;
        }

        $sort = $request->getUserVar('sort');
        $sort = isset($sort) ? (string) $sort : 'title';
        $allowedSortFields = [
            'id', 
            'title', 
            'status'
        ];
        
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        
        $sortDirection = (string) $request->getUserVar('sortDirection');
        $allowedSortDirections = [
            'ASC', 
            'DESC'
        ];
        
        $normalizedDirection = strtoupper($sortDirection);
        if (!in_array($normalizedDirection, $allowedSortDirections)) {
            $sortDirection = 'ASC'; 
        } else {
            $sortDirection = $normalizedDirection;
        }

        if ($sort == 'status') {
            // FIXME Does not pass $rangeInfo else we only get partial results
            $unsortedSubmissions = $authorSubmissionDao->getAuthorSubmissions($user->getId(), $journal->getId(), $active, null, $sort, $sortDirection);

            $submissionsArray = $unsortedSubmissions->toArray();
            $compare = function($s1, $s2) { 
                return strcmp($s1->getSubmissionStatus(), $s2->getSubmissionStatus()); 
            };
            
            usort ($submissionsArray, $compare);
            if($sortDirection == SORT_DIRECTION_DESC) {
                $submissionsArray = array_reverse($submissionsArray);
            }
            import('lib.pkp.classes.core.ArrayItemIterator');
            $submissions = ArrayItemIterator::fromRangeInfo($submissionsArray, $rangeInfo);
        } else {
            $submissions = $authorSubmissionDao->getAuthorSubmissions($user->getId(), $journal->getId(), $active, $rangeInfo, $sort, $sortDirection);
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageToDisplay', $page);
        if (!$active) {
            $templateMgr->assign('statViews', $journal->getSetting('statViews'));
        }
        $templateMgr->assign('submissions', $submissions);

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);

        if ( $paymentManager->isConfigured() ) {        
            $templateMgr->assign('submissionEnabled', $paymentManager->submissionEnabled());
            $templateMgr->assign('fastTrackEnabled', $paymentManager->fastTrackEnabled());
            $templateMgr->assign('publicationEnabled', $paymentManager->publicationEnabled());
            /** @var OJSCompletedPaymentDAO $completedPaymentDAO */
            $completedPaymentDAO = DAORegistry::getDAO('OJSCompletedPaymentDAO');
            $templateMgr->assign('completedPaymentDAO', $completedPaymentDAO);
        }

        import('classes.issue.IssueAction');
        $issueAction = new IssueAction();
        
        // Note: register_function might be deprecated depending on Smarty version, consider registering plugin/modifier.
        $templateMgr->register_function('print_issue_id', [$issueAction, 'smartyPrintIssueId']);
        
        $templateMgr->assign('helpTopicId', 'editorial.authorsRole.submissions');
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);

        $templateMgr->display('author/index.tpl');
    }

    /**
     * Validate that user has author permissions in the selected journal
     * and, optionally, for the specified article.
     * Redirects to user index page if not properly authenticated.
     * @param mixed $requiredContexts
     * @param PKPRequest $request
     * @param int|null $articleId optional
     * @param string|null $reason optional
     */
    public function validate($requiredContexts = null, $request = null, $articleId = null, $reason = null) {
        if ($requiredContexts !== null && is_object($requiredContexts)) {
            if (method_exists($requiredContexts, 'getRouter')) {
                $request = $requiredContexts;
                $requiredContexts = null;
            }
        }
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        // Call parent
        parent::validate($requiredContexts, $request);
    
        $this->addCheck(new HandlerValidatorRoles($this, true, $reason, null, [ROLE_ID_AUTHOR]));

        if ($articleId !== null && $articleId != 0) {
            /** @var AuthorSubmissionDAO $authorSubmissionDao */
            $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
            $journal = $request->getJournal();
            $user = $request->getUser();

            $isValid = true;

            $authorSubmission = $authorSubmissionDao->getAuthorSubmission((int) $articleId);

            if ($authorSubmission == null) {
                $isValid = false;
            } else if ($authorSubmission->getJournalId() != $journal->getId()) {
                $isValid = false;
            } else {
                if (!$user || ($authorSubmission->getUserId() != $user->getId())) {
                    $isValid = false;
                }
            }

            if (!$isValid) {
                $request->redirect(null, $request->getRequestedPage());
            }

            $this->submission = $authorSubmission;
        }

        return true;
    }

    /**
     * Setup common template variables.
     * @param PKPRequest $request
     * @param bool $subclass
     * @param int $articleId
     * @param string|null $parentPage
     */
    public function setupTemplate($request = null, $subclass = false, $articleId = 0, $parentPage = null) {
        parent::setupTemplate();
        
        AppLocale::requireComponents(
            LOCALE_COMPONENT_APP_AUTHOR, 
            LOCALE_COMPONENT_CORE_SUBMISSION
        );
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $templateMgr = TemplateManager::getManager();

        $pageHierarchy = $subclass ? [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'author'), 'user.role.author'], [$request->url(null, 'author'), 'article.submissions']]
            : [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'author'), 'user.role.author']];

        import('classes.submission.sectionEditor.SectionEditorAction');
        $submissionCrumb = SectionEditorAction::submissionBreadcrumb($articleId, $parentPage, 'author');
        if (isset($submissionCrumb)) {
            $pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }

    /**
     * Display submission management instructions.
     * @param array $args
     * @param PKPRequest $request
     */
    public function instructions($args, $request = null) {
        import('classes.submission.proofreader.ProofreaderAction');
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        if (!isset($args[0]) || !ProofreaderAction::instructions($args[0], ['copy', 'proof'])) {
            $request->redirect(null, null, 'index');
        }
    }
    
}
?>
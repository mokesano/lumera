<?php
declare(strict_types=1);

/**
 * @file pages/copyeditor/CopyeditorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditorHandler
 * @ingroup pages_copyeditor
 *
 * @brief Handle requests for copyeditor functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.submission.copyeditor.CopyeditorAction');
import('classes.handler.Handler');

class CopyeditorHandler extends Handler {
    
    /** @var CopyeditorSubmission|null submission associated with the request, if any */
    public $submission = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_COPYEDITOR]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CopyeditorHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::CopyeditorHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display copyeditor index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args, $request) {
        $this->validate($request);
        $this->setupTemplate();

        $journal = $request->getJournal();
        $user = $request->getUser();
        $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');

        // Get the user's search conditions, if any
        $searchField = trim((string) $request->getUserVar('searchField'));
        $allowedFields = ['title', 'author', 'editor', SUBMISSION_FIELD_TITLE, SUBMISSION_FIELD_AUTHOR, SUBMISSION_FIELD_EDITOR, SUBMISSION_FIELD_ID]; 
        if (!in_array($searchField, $allowedFields)) {
            $searchField = SUBMISSION_FIELD_TITLE; // Default aman
        }
        
        $dateSearchField = trim((string) $request->getUserVar('dateSearchField'));
        $allowedDateFields = ['dateSubmitted', 'dateCopyeditComplete', SUBMISSION_FIELD_DATE_SUBMITTED, SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE, SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE, SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE]; 
        if (!in_array($dateSearchField, $allowedDateFields)) {
            $dateSearchField = SUBMISSION_FIELD_DATE_SUBMITTED; // Default aman
        }

        $searchMatch = trim((string) $request->getUserVar('searchMatch'));
        $allowedMatches = ['all', 'any', 'phrase', 'is', 'contains', 'startsWith']; 
        if (!in_array($searchMatch, $allowedMatches)) {
            $searchMatch = 'contains'; // Default aman
        }
        
        $search = trim((string) $request->getUserVar('search'));

        $fromDate = $request->getUserDateVar('dateFrom', 1, 1);
        if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
        $toDate = $request->getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
        
        if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

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
        // Allowed sort columns based on active DAO/Grid logic usually
        $allowedSorts = ['id', 'title', 'status', 'dateSubmitted', 'submitDate']; 
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'title'; // Default aman
        }

        $sortDirection = trim(strtoupper((string) $request->getUserVar('sortDirection')));
        if ($sortDirection !== 'DESC') {
            $sortDirection = 'ASC'; // Default aman
        }

        $submissions = $copyeditorSubmissionDao->getCopyeditorSubmissionsByCopyeditorId($user->getId(), $journal->getId(), $searchField, $searchMatch, $search, $dateSearchField, $fromDate, $toDate, $active, $rangeInfo, $sort, $sortDirection);

        // If only result is returned from a search, fast-forward to it
        if ($search && $submissions && $submissions->getCount() == 1) {
            $submission = $submissions->next();
            $request->redirect(null, null, 'submission', [$submission->getId()]);
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageToDisplay', $page);
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('submissions', $submissions);

        // Set search parameters
        $duplicateParameters = [
            'searchField', 'searchMatch', 'search',
            'dateFromMonth', 'dateFromDay', 'dateFromYear',
            'dateToMonth', 'dateToDay', 'dateToYear',
            'dateSearchField'
        ];
        foreach ($duplicateParameters as $param) {
            // [SECURITY FIX] Terapkan htmlspecialchars untuk mencegah XSS
            $value = $request->getUserVar($param);
            $templateMgr->assign($param, htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'));
        }

        $templateMgr->assign('dateFrom', $fromDate);
        $templateMgr->assign('dateTo', $toDate);
        $templateMgr->assign('fieldOptions', [
            SUBMISSION_FIELD_TITLE => 'article.title',
            SUBMISSION_FIELD_ID => 'article.submissionId',
            SUBMISSION_FIELD_AUTHOR => 'user.role.author',
            SUBMISSION_FIELD_EDITOR => 'user.role.editor'
        ]);
        $templateMgr->assign('dateFieldOptions', [
            SUBMISSION_FIELD_DATE_SUBMITTED => 'submissions.submitted',
            SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE => 'submissions.copyeditComplete',
            SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE => 'submissions.layoutComplete',
            SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE => 'submissions.proofreadingComplete'
        ]);

        import('classes.issue.IssueAction');
        $issueAction = new IssueAction();
        // Note: register_function is legacy Smarty. Consider update if upgrading Smarty.
        $templateMgr->register_function('print_issue_id', [$issueAction, 'smartyPrintIssueId']);
        
        $templateMgr->assign('helpTopicId', 'editorial.copyeditorsRole.submissions');
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);
        $templateMgr->display('copyeditor/index.tpl');
    }

    /**
     * Setup common template variables.
     * @param bool $subclass set to true if caller is below this handler in the hierarchy
     * @param int $articleId
     * @param string|null $parentPage
     */
    public function setupTemplate($subclass = false, $articleId = 0, $parentPage = null) {
        parent::setupTemplate();
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_SUBMISSION);
        $templateMgr = TemplateManager::getManager();
        
        // [WIZDAM] Singleton fallback for request within helper
        $request = Application::get()->getRequest();
        
        $pageHierarchy = $subclass ? [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'copyeditor'), 'user.role.copyeditor']]
                : [['user', 'navigation.user'], ['copyeditor', 'user.role.copyeditor']];

        import('classes.submission.sectionEditor.SectionEditorAction');
        $submissionCrumb = SectionEditorAction::submissionBreadcrumb($articleId, $parentPage, 'copyeditor');
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
    public function instructions($args, $request) {
        $this->setupTemplate();
        import('classes.submission.proofreader.ProofreaderAction');
        if (!isset($args[0]) || !ProofreaderAction::instructions($args[0], ['copy'])) {
            $request->redirect(null, $request->getRequestedPage());
        }
    }

    /**
     * Validate that the user is the assigned copyeditor for
     * the article, if specified. Validate user role.
     * @param PKPRequest $request
     * @param int|null $articleId optional
     */
    public function validate($request, $articleId = null) {
        parent::validate();

        if ($articleId !== null) {
            $copyeditorSubmissionDao = DAORegistry::getDAO('CopyeditorSubmissionDAO');
            $journal = $request->getJournal();
            $user = $request->getUser();

            $isValid = true;

            $copyeditorSubmission = $copyeditorSubmissionDao->getCopyeditorSubmission($articleId, $user->getId());

            if ($copyeditorSubmission == null) {
                $isValid = false;
            } else {
                if ($copyeditorSubmission->getJournalId() != $journal->getId()) {
                    $isValid = false;
                } else {
                    if ($copyeditorSubmission->getUserIdBySignoffType('SIGNOFF_COPYEDITING_INITIAL') != $user->getId()) {
                        $isValid = false;
                    }
                }
            }

            if (!$isValid) {
                $request->redirect(null, $request->getRequestedPage());
            }

            $this->submission = $copyeditorSubmission;
        }
    }
}
?>
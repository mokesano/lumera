<?php
declare(strict_types=1);

/**
 * @file pages/proofreader/ProofreaderHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofreaderHandler
 * @ingroup pages_proofreader
 *
 * @brief Handle requests for proofreader functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.submission.proofreader.ProofreaderAction');
import('classes.handler.Handler');

class ProofreaderHandler extends Handler {
    /** @var object|null submission associated with the request */
    public $submission = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_PROOFREADER]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ProofreaderHandler() {
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
     * Display proofreader index page.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate($request);
        $this->setupTemplate();

        $journal = $request->getJournal();
        $user = $request->getUser();
        $proofreaderSubmissionDao = DAORegistry::getDAO('ProofreaderSubmissionDAO');

        // Get the user's search conditions, if any
        // [SECURITY FIX] Amankan semua parameter pencarian dengan trim()
        $searchField = trim((string) $request->getUserVar('searchField'));
        $dateSearchField = trim((string) $request->getUserVar('dateSearchField'));
        $searchMatch = trim((string) $request->getUserVar('searchMatch'));
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

        // [SECURITY FIX] Amankan 'sort' (string key) dengan trim()
        $sort = trim((string) $request->getUserVar('sort'));
        $allowedSorts = ['id', 'title', 'status', 'dateAssigned', 'dateCompleted', 'section', 'authors']; // Whitelist
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'title';
        }
        
        // [SECURITY FIX] Amankan 'sortDirection'
        $sortDirection = trim(strtoupper((string) $request->getUserVar('sortDirection')));
        if ($sortDirection != 'DESC') $sortDirection = 'ASC';

        $submissions = $proofreaderSubmissionDao->getSubmissions($user->getId(), $journal->getId(), $searchField, $searchMatch, $search, $dateSearchField, $fromDate, $toDate, $active, $rangeInfo, $sort, $sortDirection);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('pageToDisplay', $page);
        $templateMgr->assign('submissions', $submissions);

        // Set search parameters
        $duplicateParameters = [
            'searchField', 'searchMatch', 'search',
            'dateFromMonth', 'dateFromDay', 'dateFromYear',
            'dateToMonth', 'dateToDay', 'dateToYear',
            'dateSearchField'
        ];
        // [SECURITY FIX] Sanitasi dan escape semua parameter duplikat sebelum di-assign ke template
        foreach ($duplicateParameters as $param) {
            // Ambil input mentah
            $rawValue = (string) $request->getUserVar($param);
            
            // Sanitasi (trim) dan Escape (htmlspecialchars)
            $safeValue = htmlspecialchars(trim($rawValue), ENT_QUOTES, 'UTF-8');
            
            $templateMgr->assign($param, $safeValue);
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
        $templateMgr->register_function('print_issue_id', [$issueAction, 'smartyPrintIssueId']);
        $templateMgr->assign('helpTopicId', 'editorial.proofreadersRole.submissions');
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);
        $templateMgr->display('proofreader/index.tpl');
    }

    /**
     * Setup common template variables.
     * @param boolean $subclass set to true if caller is below this handler in the hierarchy
     * @param int $articleId
     * @param string|null $parentPage
     * @param boolean $showSidebar
     */
    public function setupTemplate($subclass = false, $articleId = 0, $parentPage = null, $showSidebar = true) {
        parent::setupTemplate();
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_SUBMISSION, 
            LOCALE_COMPONENT_APP_EDITOR
        );
        $templateMgr = TemplateManager::getManager();
        $pageHierarchy = $subclass 
            ? [[Request::url(null, 'user'), 'navigation.user'], [Request::url(null, 'proofreader'), 'user.role.proofreader']]
            : [[Request::url(null, 'user'), 'navigation.user'], [Request::url(null, 'proofreader'), 'user.role.proofreader']];

        import('classes.submission.sectionEditor.SectionEditorAction');
        $submissionCrumb = SectionEditorAction::submissionBreadcrumb($articleId, $parentPage, 'proofreader');
        if (isset($submissionCrumb)) {
            $pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }

    /**
     * Display submission management instructions.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function instructions($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $this->setupTemplate();
        if (!isset($args[0]) || !ProofreaderAction::instructions($args[0], ['proof'])) {
            $request->redirect(null, $request->getRequestedPage());
        }
    }

    /**
     * Validate that the user is the assigned proofreader for the submission,
     * if a submission ID is specified.
     * Redirects to proofreader index page if validation fails.
     * * [WIZDAM] Transition Mode: Loose signature to handle legacy parameter swapping
     * @param mixed $requiredContexts (Could be Request object, or null)
     * @param mixed $request (Could be Request object, or articleId, or null)
     */
    public function validate($requiredContexts = null, $request = null) {
        // --- Deteksi bagaimana fungsi ini dipanggil ---
        
        $articleId = null;
        $realRequest = null;

        // Case A: Call from 'child' (SubmissionProofreadHandler)
        // Parameter mismatch: validate($request, $articleId)
        if (is_object($requiredContexts) && is_numeric($request)) {
            $realRequest = $requiredContexts;
            $articleId = (int) $request;
        } 
        // Case B: Standard call (from 'index')
        // validate($request)
        else if (is_object($requiredContexts) && $request === null) {
            $realRequest = $requiredContexts;
        } 
        // Case C: Fallback (e.g. parent::validate(null, $request))
        else if (is_object($request)) { 
            $realRequest = $request;
        }

        // Ensure realRequest is populated
        if ($realRequest === null) {
            $realRequest = Application::get()->getRequest();
        }

        // --- Sekarang kita bisa memvalidasi dengan aman ---

        // Panggil validasi 'Kakek' (PKPHandler) terlebih dahulu
        // Ini akan menangani validasi jurnal dasar
        parent::validate($realRequest);

        // Case A continues: We have $articleId (specific validation)
        if ($articleId !== null) {
            $isValid = false;

            // $realRequest dijamin Objek Request di sini
            $journal = $realRequest->getJournal();
            $user = $realRequest->getUser();

            $proofreaderDao = DAORegistry::getDAO('ProofreaderSubmissionDAO');
            $signoffDao = DAORegistry::getDAO('SignoffDAO');
            $submission = $proofreaderDao->getSubmission($articleId, $journal->getId());

            if (isset($submission)) {
                $proofSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
                if ($proofSignoff->getUserId() == $user->getId()) {
                    $isValid = true;
                }
            }

            if (!$isValid) {
                $realRequest->redirect(null, $realRequest->getRequestedPage());
            }

            $this->submission = $submission;
        }

        return true;
    }
}
?>
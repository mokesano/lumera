<?php
declare(strict_types=1);

/**
 * @file pages/sectionEditor/SectionEditorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorHandler
 * @ingroup pages_sectionEditor
 *
 * @brief Handle requests for section editor functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

// Filter section
define('FILTER_SECTION_ALL', 0);

import('classes.submission.sectionEditor.SectionEditorAction');
import('classes.handler.Handler');

class SectionEditorHandler extends Handler {
    /** @var object|null submission associated with the request */
    public $submission = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->addCheck(new HandlerValidatorJournal($this));
        
        // [WIZDAM] Request Singleton
        $request = Application::get()->getRequest();
        $page = $request->getRequestedPage();
        
        if ( $page == 'sectionEditor' )
            $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_SECTION_EDITOR]));
        elseif ( $page == 'editor' )
            $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_EDITOR]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SectionEditorHandler() {
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
     * Display section editor index page.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate();

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $user = $request->getUser();

        $rangeInfo = $this->getRangeInfo('submissions');

        // Get the user's search conditions, if any
        $searchField = (string) $request->getUserVar('searchField');
        // [SECURITY FIX] Whitelisting
        $allowedSearchFields = ['title', 'author', 'editor', 'id'];
        if (!in_array($searchField, $allowedSearchFields)) {
            $searchField = 'title';
        }
        
        $dateSearchField = (string) $request->getUserVar('dateSearchField');
        // [SECURITY FIX] Whitelisting
        $allowedDateFields = ['dateSubmitted', 'datePublished', 'dateCopyeditComplete', 'dateLayoutComplete', 'dateProofreadingComplete'];
        if (!in_array($dateSearchField, $allowedDateFields)) {
            $dateSearchField = 'dateSubmitted';
        }
        
        $searchMatch = (string) $request->getUserVar('searchMatch');
        $allowedMatches = ['all', 'any', 'phrase'];
        if (!in_array($searchMatch, $allowedMatches)) {
            $searchMatch = 'all';
        }
        
        // [DATA INTEGRITY FIX] Sanitasi
        $search = trim((string) $request->getUserVar('search'));

        $fromDate = $request->getUserDateVar('dateFrom', 1, 1);
        if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
        $toDate = $request->getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
        if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');

        $page = isset($args[0]) ? $args[0] : '';
        $sections = $sectionDao->getSectionTitles($journal->getId());

        $sort = (string) $request->getUserVar('sort');
        // [SECURITY FIX] Whitelisting
        $allowedSortFields = ['id', 'title', 'status', 'dateSubmitted', 'submitDate']; 
        if (!in_array($sort, $allowedSortFields)) {
            $sort = 'id';
        }
        
        $sortDirection = (string) $request->getUserVar('sortDirection');
        // [SECURITY FIX] Whitelisting
        $normalizedDirection = strtoupper($sortDirection);
        if ($normalizedDirection !== 'DESC') {
            $sortDirection = 'ASC'; 
        } else {
            $sortDirection = 'DESC';
        }

        $filterSectionOptions = [
            FILTER_SECTION_ALL => AppLocale::Translate('editor.allSections')
        ] + $sections;

        switch($page) {
            case 'submissionsInEditing':
                $functionName = 'getSectionEditorSubmissionsInEditing';
                $helpTopicId = 'editorial.sectionEditorsRole.submissions.inEditing';
                break;
            case 'submissionsArchives':
                $functionName = 'getSectionEditorSubmissionsArchives';
                $helpTopicId = 'editorial.sectionEditorsRole.submissions.archives';
                break;
            default:
                $page = 'submissionsInReview';
                $functionName = 'getSectionEditorSubmissionsInReview';
                $helpTopicId = 'editorial.sectionEditorsRole.submissions.inReview';
        }

        $filterSection = $request->getUserVar('filterSection');
        if ($filterSection != '' && array_key_exists($filterSection, $filterSectionOptions)) {
            $user->updateSetting('filterSection', (int) $filterSection, 'int', $journalId);
        } else {
            $filterSection = $user->getSetting('filterSection', $journalId);
            if ($filterSection == null) {
                $filterSection = FILTER_SECTION_ALL;
                $user->updateSetting('filterSection', $filterSection, 'int', $journalId);
            }
        }

        $submissions = $sectionEditorSubmissionDao->$functionName(
            $user->getId(),
            $journal->getId(),
            $filterSection,
            $searchField,
            $searchMatch,
            $search,
            $dateSearchField,
            $fromDate,
            $toDate,
            $rangeInfo,
            $sort,
            $sortDirection
        );

        // If only result is returned from a search, fast-forward to it
        if ($search && $submissions && $submissions->getCount() == 1) {
            $submission = $submissions->next();
            $request->redirect(null, null, 'submission', [$submission->getId()]);
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', $helpTopicId);
        $templateMgr->assign('sectionOptions', $filterSectionOptions);
        $templateMgr->assign('submissions', $submissions);
        $templateMgr->assign('filterSection', $filterSection);
        $templateMgr->assign('pageToDisplay', $page);
        $templateMgr->assign('sectionEditor', $user->getFullName());

        // Set search parameters
        $duplicateParameters = [
            'searchField', 'searchMatch', 'search',
            'dateFromMonth', 'dateFromDay', 'dateFromYear',
            'dateToMonth', 'dateToDay', 'dateToYear',
            'dateSearchField'
        ];
        foreach ($duplicateParameters as $param)
            $templateMgr->assign($param, $request->getUserVar($param));

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
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);

        $templateMgr->display('sectionEditor/index.tpl');
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
            LOCALE_COMPONENT_CORE_MANAGER, 
            LOCALE_COMPONENT_CORE_SUBMISSION, 
            LOCALE_COMPONENT_APP_EDITOR, 
            LOCALE_COMPONENT_APP_AUTHOR, 
            LOCALE_COMPONENT_APP_MANAGER
        );
        $templateMgr = TemplateManager::getManager();
        $isEditor = Validation::isEditor();
        $request = Application::get()->getRequest();

        if ($request->getRequestedPage() == 'editor') {
            $templateMgr->assign('helpTopicId', 'editorial.editorsRole');
        } else {
            $templateMgr->assign('helpTopicId', 'editorial.sectionEditorsRole');
        }

        $roleSymbolic = $isEditor ? 'editor' : 'sectionEditor';
        $roleKey = $isEditor ? 'user.role.editor' : 'user.role.sectionEditor';
        $pageHierarchy = $subclass 
            ? [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, $roleSymbolic), $roleKey], [$request->url(null, $roleSymbolic), 'article.submissions']]
            : [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, $roleSymbolic), $roleKey]];

        import('classes.submission.sectionEditor.SectionEditorAction');
        $submissionCrumb = SectionEditorAction::submissionBreadcrumb($articleId, $parentPage, $roleSymbolic);
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
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->setupTemplate();
        import('classes.submission.proofreader.ProofreaderAction');
        if (!isset($args[0]) || !ProofreaderAction::instructions($args[0], ['copy', 'layout', 'proof', 'referenceLinking'])) {
            $request->redirect(null, null, 'index');
        }
    }

    //
    // Validation
    //

    /**
     * Validate that the user is the assigned section editor for
     * the article, or is a managing editor.
     * Redirects to sectionEditor index page if validation fails.
     * [WIZDAM] Signature Polyfill: 
     * Original OJS 2.x Signature: validate($articleId, $access)
     * Parent Signature: validate($requiredContexts, $request)
     * We support both via type checking.
     * @param mixed $requiredContexts (Could be articleId (int) or contexts)
     * @param mixed $request (Could be access (int/string) or Request object)
     */
    public function validate($requiredContexts = null, $request = null) {
        // [WIZDAM] Polyfill Logic
        $articleId = null;
        $access = null;
        $realRequest = null;

        // Detection: Legacy call validate($articleId, $access)
        if (is_numeric($requiredContexts) || ($requiredContexts === null && is_numeric($request))) {
            $articleId = $requiredContexts !== null ? (int) $requiredContexts : null;
            $access = $request; // 2nd arg is access level
            
            $requiredContexts = null; // Reset for parent call
            $realRequest = Application::get()->getRequest(); // Get singleton for parent
        } else {
            // Standard call validate($requiredContexts, $request)
            $realRequest = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        }

        parent::validate($requiredContexts, $realRequest);
        
        $isValid = true;

        $sectionEditorSubmissionDao = DAORegistry::getDAO('SectionEditorSubmissionDAO');
        $journal = $realRequest->getJournal();
        $user = $realRequest->getUser();

        if ($articleId !== null) {
            $sectionEditorSubmission = $sectionEditorSubmissionDao->getSectionEditorSubmission($articleId);

            if ($sectionEditorSubmission == null) {
                $isValid = false;

            } else if ($sectionEditorSubmission->getJournalId() != $journal->getId()) {
                $isValid = false;

            } else if ($sectionEditorSubmission->getDateSubmitted() == null) {
                $isValid = false;

            } else {
                $templateMgr = TemplateManager::getManager();

                if (Validation::isEditor()) {
                    // Make canReview and canEdit available to templates.
                    // Since this user is an editor, both are available.
                    $templateMgr->assign('canReview', true);
                    $templateMgr->assign('canEdit', true);
                } else {
                    // If this user isn't the submission's editor, they don't have access.
                    $editAssignments = $sectionEditorSubmission->getEditAssignments();
                    $wasFound = false;
                    foreach ($editAssignments as $editAssignment) {
                        if ($editAssignment->getEditorId() == $user->getId()) {
                            $templateMgr->assign('canReview', $editAssignment->getCanReview());
                            $templateMgr->assign('canEdit', $editAssignment->getCanEdit());
                            switch ($access) {
                                case SECTION_EDITOR_ACCESS_EDIT:
                                    if ($editAssignment->getCanEdit()) {
                                        $wasFound = true;
                                    }
                                    break;
                                case SECTION_EDITOR_ACCESS_REVIEW:
                                    if ($editAssignment->getCanReview()) {
                                        $wasFound = true;
                                    }
                                    break;

                                default:
                                    $wasFound = true;
                            }
                            break;
                        }
                    }

                    if (!$wasFound) $isValid = false;
                }
            }

            if (!$isValid) {
                $realRequest->redirect(null, $realRequest->getRequestedPage());
            }

            // If necessary, note the current date and time as the "underway" date/time
            $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
            $editAssignments = $sectionEditorSubmission->getEditAssignments();
            foreach ($editAssignments as $editAssignment) {
                if ($editAssignment->getEditorId() == $user->getId() && $editAssignment->getDateUnderway() === null) {
                    $editAssignment->setDateUnderway(Core::getCurrentDate());
                    $editAssignmentDao->updateEditAssignment($editAssignment);
                }
            }

            $this->submission = $sectionEditorSubmission;
            return true;
        }
    }
}
?>
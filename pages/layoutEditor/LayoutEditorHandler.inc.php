<?php
declare(strict_types=1);

/**
 * @file pages/layoutEditor/LayoutEditorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LayoutEditorHandler
 * @ingroup pages_layoutEditor
 *
 * @brief Handle requests for layout editor functions.
 */

import('classes.submission.layoutEditor.LayoutEditorAction');
import('classes.submission.proofreader.ProofreaderAction');
import('classes.handler.Handler');

class LayoutEditorHandler extends Handler {
    
    /** @var LayoutEditorSubmission|null submission associated with the request */
    public $submission = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_LAYOUT_EDITOR]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LayoutEditorHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::LayoutEditorHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display layout editor index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate($request);
        $this->setupTemplate();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('helpTopicId', 'editorial.layoutEditorsRole');

        $templateMgr->display('layoutEditor/index.tpl');
    }

    /**
     * Display layout editor submissions page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function submissions($args, $request) {
        $this->validate($request);
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $user = $request->getUser();

        /** @var LayoutEditorSubmissionDAO $layoutEditorSubmissionDao */
        $layoutEditorSubmissionDao = DAORegistry::getDAO('LayoutEditorSubmissionDAO');

        $page = isset($args[0]) ? $args[0] : '';
        switch($page) {
            case 'completed':
                $active = false;
                break;
            default:
                $page = 'active';
                $active = true;
        }

        $sortInput = trim((string) $request->getUserVar('sort'));
        $validSortColumns = ['title', 'id', 'status', 'dateSubmitted']; 
        if (!empty($sortInput) && in_array($sortInput, $validSortColumns)) {
            $sort = $sortInput;
        } else {
            $sort = 'title'; // Default aman
        }

        $sortDirectionInput = trim((string) $request->getUserVar('sortDirection'));
        if ($sortDirectionInput == SORT_DIRECTION_DESC) {
            $sortDirection = SORT_DIRECTION_DESC;
        } else {
            $sortDirection = SORT_DIRECTION_ASC; // Default aman
        }

        $searchFieldInput = $request->getUserVar('searchField');
        $validSearchFields = [
            SUBMISSION_FIELD_TITLE, 
            SUBMISSION_FIELD_AUTHOR, 
            SUBMISSION_FIELD_EDITOR
        ];
        if (in_array($searchFieldInput, $validSearchFields)) {
            $searchField = $searchFieldInput;
        } else {
            $searchField = null; // Default aman
        }

        $dateSearchFieldInput = $request->getUserVar('dateSearchField');
        $validDateSearchFields = [
            SUBMISSION_FIELD_DATE_SUBMITTED
        ];
        if (in_array($dateSearchFieldInput, $validDateSearchFields)) {
            $dateSearchField = $dateSearchFieldInput;
        } else {
            $dateSearchField = null; // Default aman
        }

        $searchMatchInput = trim((string) $request->getUserVar('searchMatch'));
        $validSearchMatches = ['is', 'contains', 'startsWith'];
        if (in_array($searchMatchInput, $validSearchMatches)) {
            $searchMatch = $searchMatchInput;
        } else {
            $searchMatch = 'contains'; // Default aman
        }

        $search = trim((string) $request->getUserVar('search'));
        $fromDate = $request->getUserDateVar('dateFrom', 1, 1);
        if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
        $toDate = $request->getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
        if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

        $rangeInfo = $this->getRangeInfo('submissions');
        $submissions = $layoutEditorSubmissionDao->getSubmissions($user->getId(), $journal->getId(), $searchField, $searchMatch, $search, $dateSearchField, $fromDate, $toDate, $active, $rangeInfo, $sort, $sortDirection);

        if ($search && $submissions && $submissions->getCount() == 1) {
            $submission = $submissions->next();
            $request->redirect(null, null, 'submission', [$submission->getId()]);
        }

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
        foreach ($duplicateParameters as $param) {
            $templateMgr->assign(
                $param,
                htmlspecialchars(trim((string)$request->getUserVar($param)), ENT_QUOTES, 'UTF-8')
            );
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
        
        $templateMgr->assign('helpTopicId', 'editorial.layoutEditorsRole.submissions');
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);

        $templateMgr->display('layoutEditor/submissions.tpl');
    }

    /**
     * Display Future Isshes page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function futureIssues($args, $request) {
        $this->validate($request);
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $rangeInfo = $this->getRangeInfo('issues');
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('issues', $issueDao->getUnpublishedIssues($journal->getId(), $rangeInfo));
        $templateMgr->assign('helpTopicId', 'publishing.index');

        $templateMgr->display('layoutEditor/futureIssues.tpl');
    }

    /**
     * Displays the listings of back (published) issues
     * @param array $args
     * @param PKPRequest $request
     */
    public function backIssues($args, $request) {
        $this->validate($request);
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $rangeInfo = $this->getRangeInfo('issues');
        $sortInput = trim((string) $request->getUserVar('sort'));
        $validSortColumns = ['title', 'issue_id', 'date_published']; 
        
        if (!empty($sortInput) && in_array($sortInput, $validSortColumns)) {
            $sort = $sortInput;
        } else {
            $sort = 'title'; // Default aman
        }

        $sortDirectionInput = trim((string) $request->getUserVar('sortDirection'));
        if ($sortDirectionInput == SORT_DIRECTION_DESC) {
            $sortDirection = SORT_DIRECTION_DESC;
        } else {
            $sortDirection = SORT_DIRECTION_ASC; // Default aman
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('issues', $issueDao->getPublishedIssues($journal->getId(), $rangeInfo));

        $allIssuesIterator = $issueDao->getPublishedIssues($journal->getId());
        $issueMap = [];
        while ($issue = $allIssuesIterator->next()) {
            $issueMap[$issue->getId()] = $issue->getIssueIdentification();
            unset($issue);
        }
        $templateMgr->assign('allIssues', $issueMap);

        $currentIssue = $issueDao->getCurrentIssue($journal->getId());
        $currentIssueId = $currentIssue ? $currentIssue->getId() : null;

        $templateMgr->assign('currentIssueId', $currentIssueId);
        $templateMgr->assign('helpTopicId', 'publishing.index');
        $templateMgr->assign('usesCustomOrdering', $issueDao->customIssueOrderingExists($journal->getId()));
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);

        $templateMgr->display('layoutEditor/backIssues.tpl');
    }

    /**
     * Sets proofreader completion date
     * @param array $args
     * @param PKPRequest $request
     */
    public function completeProofreader($args, $request) {
        $articleId = (int) trim((string) $request->getUserVar('articleId'));

        $this->validate($request, $articleId);
        $this->setupTemplate(true);

        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $signoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $articleId);
        $signoff->setDateNotified(Core::getCurrentDate());
        $signoffDao->updateObject($signoff);

        $signoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
        $signoff->setDateCompleted(Core::getCurrentDate());
        $signoffDao->updateObject($signoff);

        if (ProofreaderAction::proofreadEmail($articleId, 'PROOFREAD_COMPLETE', $request, (int) trim((string) $request->getUserVar('send')) ? '' : $request->url(null, 'layoutEditor', 'completeProofreader'))) {
            $request->redirect(null, null, 'submission', [$articleId]);
        }
    }

    /**
     * Setup common template variables.
     * @param bool $subclass set to true if caller is below this handler in the hierarchy
     * @param int $articleId optional
     * @param string|null $parentPage optional
     */
    public function setupTemplate($subclass = false, $articleId = 0, $parentPage = null) {
        parent::setupTemplate();
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_SUBMISSION, LOCALE_COMPONENT_APP_EDITOR);
        $templateMgr = TemplateManager::getManager();
        
        // [WIZDAM] Request from singleton
        $request = Application::get()->getRequest();
        
        $pageHierarchy = $subclass ? [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'layoutEditor'), 'user.role.layoutEditor']]
                : [[$request->url(null, 'user'), 'navigation.user']];

        import('classes.submission.sectionEditor.SectionEditorAction');
        $submissionCrumb = SectionEditorAction::submissionBreadcrumb($articleId, $parentPage, 'layoutEditor');
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
        if (!isset($args[0]) || !LayoutEditorAction::instructions($args[0], ['layout', 'proof', 'referenceLinking'])) {
            $request->redirect(null, $request->getRequestedPage());
        }
    }


    //
    // Validation
    //
    /**
     * Validate that the user is the assigned layout editor for the submission.
     * Redirects to layoutEditor index page if validation fails.
     * @param PKPRequest $request
     * @param int|null $articleId
     * @param bool $checkEdit
     * @return bool|void
     */
    public function validate($request = null, $articleId = null, $checkEdit = false) {
        parent::validate();

        if ($articleId !== null) {
            $isValid = false;

            $journal = $request->getJournal();
            $user = $request->getUser();

            /** @var LayoutEditorSubmissionDAO $layoutDao */
            $layoutDao = DAORegistry::getDAO('LayoutEditorSubmissionDAO');
            /** @var SignoffDAO $signoffDao */
            $signoffDao = DAORegistry::getDAO('SignoffDAO');
            $submission = $layoutDao->getSubmission($articleId, $journal->getId());

            if (isset($submission)) {
                $layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $articleId);
                if (!isset($layoutSignoff)) $isValid = false;
                elseif ($layoutSignoff->getUserId() == $user->getId()) {
                    if ($checkEdit) {
                        $isValid = $this->_layoutEditingEnabled($submission);
                    } else {
                        $isValid = true;
                    }
                }
            }

            if (!$isValid) {
                $request->redirect(null, $request->getRequestedPage());
            }

            $this->submission = $submission;
            return true;
        }
    }

    /**
     * Check if a layout editor is allowed to make changes to the submission.
     * This is allowed if there is an outstanding galley creation or layout editor
     * proofreading request.
     * @param LayoutEditorSubmission $submission
     * @return bool
     */
    public function _layoutEditingEnabled($submission) {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $layoutEditorProofreadSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $submission->getId());
        $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $submission->getId());

        return(($layoutSignoff->getDateNotified() != null
            && $layoutSignoff->getDateCompleted() == null)
        || ($layoutEditorProofreadSignoff->getDateNotified() != null
            && $layoutEditorProofreadSignoff->getDateCompleted() == null));
    }
    
}
?>
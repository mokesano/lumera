<?php
declare(strict_types=1);

/**
 * @file pages/editor/EditorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorHandler
 * @ingroup pages_editor
 *
 * @brief Handle requests for editor functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.sectionEditor.SectionEditorHandler');

define('EDITOR_SECTION_HOME', 0);
define('EDITOR_SECTION_SUBMISSIONS', 1);
define('EDITOR_SECTION_ISSUES', 2);

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

import ('classes.submission.editor.EditorAction');

class EditorHandler extends SectionEditorHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_EDITOR]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EditorHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::EditorHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Displays the editor role selection page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate(EDITOR_SECTION_HOME);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager();
        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $user = $request->getUser();

        $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
        $sectionDao = DAORegistry::getDAO('SectionDAO');

        $sections = $sectionDao->getSectionTitles($journal->getId());
        $templateMgr->assign('sectionOptions', [0 => AppLocale::Translate('editor.allSections')] + $sections);
        $templateMgr->assign('fieldOptions', $this->_getSearchFieldOptions());
        $templateMgr->assign('dateFieldOptions', $this->_getDateFieldOptions());

        import('classes.issue.IssueAction');
        $issueAction = new IssueAction();
        // Note: register_function is legacy Smarty. Consider update if upgrading Smarty.
        $templateMgr->register_function('print_issue_id', [$issueAction, 'smartyPrintIssueId']);

        // If a search was performed, get the necessary info.
        if (array_shift($args) == 'search') {
            $rangeInfo = $this->getRangeInfo('submissions');

            // Get the user's search conditions, if any
            $searchField = trim((string) $request->getUserVar('searchField'));
            $allowedFields = ['title', 'author', 'editor', 'abstract', SUBMISSION_FIELD_TITLE, SUBMISSION_FIELD_AUTHOR, SUBMISSION_FIELD_EDITOR, SUBMISSION_FIELD_ID]; 
            if (!in_array($searchField, $allowedFields)) {
                $searchField = SUBMISSION_FIELD_TITLE; // Default aman
            }
            
            $dateSearchField = trim((string) $request->getUserVar('dateSearchField'));
            $allowedDateFields = ['dateSubmitted', 'dateCopyeditComplete', 'dateLayoutComplete', SUBMISSION_FIELD_DATE_SUBMITTED, SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE, SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE, SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE]; 
            if (!in_array($dateSearchField, $allowedDateFields)) {
                $dateSearchField = SUBMISSION_FIELD_DATE_SUBMITTED; // Default aman
            }
            
            $searchMatch = trim((string) $request->getUserVar('searchMatch'));
            $allowedMatches = ['all', 'any', 'phrase', 'contains', 'is', 'startsWith']; 
            if (!in_array($searchMatch, $allowedMatches)) {
                $searchMatch = 'contains'; // Default aman
            }
            
            $search = trim((string) $request->getUserVar('search'));

            $sort = trim((string) $request->getUserVar('sort'));
            $allowedSorts = ['id', 'title', 'status', 'dateSubmitted', 'submitDate']; 
            if (!in_array($sort, $allowedSorts)) {
                $sort = 'id'; // Default aman
            }
            
            $sortDirection = trim(strtoupper((string) $request->getUserVar('sortDirection')));
            if ($sortDirection !== 'DESC') {
                $sortDirection = 'ASC'; // Default aman
            }

            $fromDate = $request->getUserDateVar('dateFrom', 1, 1);
            if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
            $toDate = $request->getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
            if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

            if ($sort == 'status') {
                $rawSubmissions = $editorSubmissionDao->_getUnfilteredEditorSubmissions(
                    $journal->getId(),
                    (int) $request->getUserVar('section'),
                    0,
                    $searchField,
                    $searchMatch,
                    $search,
                    $dateSearchField,
                    $fromDate,
                    $toDate,
                    null,
                    null,
                    $sort,
                    $sortDirection
                );
                $submissions = new DAOResultFactory($rawSubmissions, $editorSubmissionDao, '_returnEditorSubmissionFromRow');

                // Sort all submissions by status, which is too complex to do in the DB
                $submissionsArray = $submissions->toArray();
                
                // [WIZDAM FIX] Replaced create_function with anonymous Closure
                usort($submissionsArray, function($s1, $s2) {
                    return strcmp($s1->getSubmissionStatus(), $s2->getSubmissionStatus());
                });
                
                if($sortDirection == SORT_DIRECTION_DESC) {
                    $submissionsArray = array_reverse($submissionsArray);
                }
                // Convert submission array back to an ItemIterator class
                import('lib.pkp.classes.core.ArrayItemIterator');
                $submissions = ArrayItemIterator::fromRangeInfo($submissionsArray, $rangeInfo);
            } else {
                $rawSubmissions = $editorSubmissionDao->_getUnfilteredEditorSubmissions(
                    $journal->getId(),
                    (int) $request->getUserVar('section'),
                    0,
                    $searchField,
                    $searchMatch,
                    $search,
                    $dateSearchField,
                    $fromDate,
                    $toDate,
                    null,
                    $rangeInfo,
                    $sort,
                    $sortDirection
                );
                $submissions = new DAOResultFactory($rawSubmissions, $editorSubmissionDao, '_returnEditorSubmissionFromRow');
            }


            // If only result is returned from a search, fast-forward to it
            if ($search && $submissions && $submissions->getCount() == 1) {
                $submission = $submissions->next();
                $request->redirect(null, null, 'submission', [$submission->getId()]);
            }

            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('submissions', $submissions);
            // [SECURITY FIX] Terapkan htmlspecialchars untuk mencegah XSS
            $templateMgr->assign('section', htmlspecialchars((string) $request->getUserVar('section'), ENT_QUOTES, 'UTF-8'));

            // Set search parameters
            foreach ($this->_getSearchFormDuplicateParameters() as $param) {
                $value = $request->getUserVar($param);
                // [SECURITY FIX] Terapkan htmlspecialchars untuk mencegah XSS
                $templateMgr->assign($param, htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
            }

            $templateMgr->assign('dateFrom', $fromDate);
            $templateMgr->assign('dateTo', $toDate);
            $templateMgr->assign('displayResults', true);
            $templateMgr->assign('sort', $sort);
            $templateMgr->assign('sortDirection', $sortDirection);
        }

        $submissionsCount = $editorSubmissionDao->getEditorSubmissionsCount($journal->getId());
        $templateMgr->assign('submissionsCount', $submissionsCount);
        $templateMgr->assign('helpTopicId', 'editorial.editorsRole');
        $templateMgr->display('editor/index.tpl');
    }

    /**
     * Display editor submission queue pages.
     * @param array $args
     * @param PKPRequest $request
     */
    public function submissions($args, $request) {
        $this->validate();
        $this->setupTemplate(EDITOR_SECTION_SUBMISSIONS);

        $journal = $request->getJournal();
        $journalId = $journal->getId();
        $user = $request->getUser();

        $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
        $sectionDao = DAORegistry::getDAO('SectionDAO');

        $page = isset($args[0]) ? $args[0] : '';
        $sections = $sectionDao->getSectionTitles($journalId);

        $sort = trim((string) $request->getUserVar('sort'));
        $allowedSorts = ['id', 'title', 'status', 'dateSubmitted', 'submitDate']; 
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'id'; // Default aman
        }
        
        $sortDirection = trim(strtoupper((string) $request->getUserVar('sortDirection')));
        if ($sortDirection !== 'DESC') {
            $sortDirection = 'ASC'; // Default aman
        }

        $filterEditorOptions = [
            FILTER_EDITOR_ALL => AppLocale::Translate('editor.allEditors'),
            FILTER_EDITOR_ME => AppLocale::Translate('editor.me')
        ];

        $filterSectionOptions = [
            FILTER_SECTION_ALL => AppLocale::Translate('editor.allSections')
        ] + $sections;

        // Get the user's search conditions, if any
        $searchField = trim((string) $request->getUserVar('searchField'));
        $allowedFields = ['title', 'author', 'editor', 'abstract', SUBMISSION_FIELD_TITLE, SUBMISSION_FIELD_AUTHOR, SUBMISSION_FIELD_EDITOR, SUBMISSION_FIELD_ID]; 
        if (!in_array($searchField, $allowedFields)) {
            $searchField = SUBMISSION_FIELD_TITLE; // Default aman
        }
        
        $dateSearchField = trim((string) $request->getUserVar('dateSearchField'));
        $allowedDateFields = ['dateSubmitted', 'dateCopyeditComplete', 'dateLayoutComplete', SUBMISSION_FIELD_DATE_SUBMITTED, SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE, SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE, SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE]; 
        if (!in_array($dateSearchField, $allowedDateFields)) {
            $dateSearchField = SUBMISSION_FIELD_DATE_SUBMITTED; // Default aman
        }
        
        $searchMatch = trim((string) $request->getUserVar('searchMatch'));
        $allowedMatches = ['all', 'any', 'phrase', 'contains', 'is', 'startsWith']; 
        if (!in_array($searchMatch, $allowedMatches)) {
            $searchMatch = 'contains'; // Default aman
        }
        
        $search = trim((string) $request->getUserVar('search'));

        $fromDate = $request->getUserDateVar('dateFrom', 1, 1);
        if ($fromDate !== null) $fromDate = date('Y-m-d H:i:s', $fromDate);
        $toDate = $request->getUserDateVar('dateTo', 32, 12, null, 23, 59, 59);
        if ($toDate !== null) $toDate = date('Y-m-d H:i:s', $toDate);

        $rangeInfo = $this->getRangeInfo('submissions');

        switch($page) {
            case 'submissionsUnassigned':
                $functionName = 'getEditorSubmissionsUnassigned';
                $helpTopicId = 'editorial.editorsRole.submissions.unassigned';
                break;
            case 'submissionsInEditing':
                $functionName = 'getEditorSubmissionsInEditing';
                $helpTopicId = 'editorial.editorsRole.submissions.inEditing';
                break;
            case 'submissionsArchives':
                $functionName = 'getEditorSubmissionsArchives';
                $helpTopicId = 'editorial.editorsRole.submissions.archives';
                break;
            default:
                $page = 'submissionsInReview';
                $functionName = 'getEditorSubmissionsInReview';
                $helpTopicId = 'editorial.editorsRole.submissions.inReview';
        }

        $filterEditor = (int) $request->getUserVar('filterEditor');
        
        if ($filterEditor != '' && array_key_exists($filterEditor, $filterEditorOptions)) {
            $user->updateSetting('filterEditor', $filterEditor, 'int', $journalId);
        } else {
            $filterEditor = $user->getSetting('filterEditor', $journalId);
            if ($filterEditor == null) {
                $filterEditor = FILTER_EDITOR_ALL;
                $user->updateSetting('filterEditor', $filterEditor, 'int', $journalId);
            }
        }

        if ($filterEditor == FILTER_EDITOR_ME) {
            $editorId = $user->getId();
        } else {
            $editorId = FILTER_EDITOR_ALL;
        }

        $filterSection = (int) $request->getUserVar('filterSection');
        
        if ($filterSection != '' && array_key_exists($filterSection, $filterSectionOptions)) {
            $user->updateSetting('filterSection', $filterSection, 'int', $journalId);
        } else {
            $filterSection = $user->getSetting('filterSection', $journalId);
            if ($filterSection == null) {
                $filterSection = FILTER_SECTION_ALL;
                $user->updateSetting('filterSection', $filterSection, 'int', $journalId);
            }
        }

        $submissions = $editorSubmissionDao->$functionName(
            $journalId,
            $filterSection,
            $editorId,
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

        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('pageToDisplay', $page);
        $templateMgr->assign('editor', $user->getFullName());
        $templateMgr->assign('editorOptions', $filterEditorOptions);
        $templateMgr->assign('sectionOptions', $filterSectionOptions);

        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('submissions', $submissions);
        $templateMgr->assign('filterEditor', $filterEditor);
        $templateMgr->assign('filterSection', $filterSection);

        // Set search parameters
        foreach ($this->_getSearchFormDuplicateParameters() as $param) {
            $value = $request->getUserVar($param);
            // [SECURITY FIX] Terapkan htmlspecialchars untuk mencegah XSS
            $templateMgr->assign($param, htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'));
        }

        $templateMgr->assign('dateFrom', $fromDate);
        $templateMgr->assign('dateTo', $toDate);
        $templateMgr->assign('fieldOptions', $this->_getSearchFieldOptions());
        $templateMgr->assign('dateFieldOptions', $this->_getDateFieldOptions());

        import('classes.issue.IssueAction');
        $issueAction = new IssueAction();
        $templateMgr->register_function('print_issue_id', [$issueAction, 'smartyPrintIssueId']);

        $templateMgr->assign('helpTopicId', $helpTopicId);
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);
        $templateMgr->display('editor/submissions.tpl');
    }

    /**
     * Get the list of parameter names that should be duplicated when
     * displaying the search form (i.e. made available to the template
     * based on supplied user data).
     * @return array
     */
    public function _getSearchFormDuplicateParameters() {
        return [
            'searchField', 'searchMatch', 'search',
            'dateFromMonth', 'dateFromDay', 'dateFromYear',
            'dateToMonth', 'dateToDay', 'dateToYear',
            'dateSearchField'
        ];
    }

    /**
     * Get the list of fields that can be searched by contents.
     * @return array
     */
    public function _getSearchFieldOptions() {
        return [
            SUBMISSION_FIELD_TITLE => 'article.title',
            SUBMISSION_FIELD_ID => 'article.submissionId',
            SUBMISSION_FIELD_AUTHOR => 'user.role.author',
            SUBMISSION_FIELD_EDITOR => 'user.role.editor',
            SUBMISSION_FIELD_REVIEWER => 'user.role.reviewer',
            SUBMISSION_FIELD_COPYEDITOR => 'user.role.copyeditor',
            SUBMISSION_FIELD_LAYOUTEDITOR => 'user.role.layoutEditor',
            SUBMISSION_FIELD_PROOFREADER => 'user.role.proofreader'
        ];
    }

    /**
     * Get the list of date fields that can be searched.
     * @return array
     */
    public function _getDateFieldOptions() {
        return [
            SUBMISSION_FIELD_DATE_SUBMITTED => 'submissions.submitted',
            SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE => 'submissions.copyeditComplete',
            SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE => 'submissions.layoutComplete',
            SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE => 'submissions.proofreadingComplete'
        ];
    }

    /**
     * Set the canEdit / canReview flags for this submission's edit assignments.
     * @param array $args
     * @param PKPRequest $request
     */
    public function setEditorFlags($args, $request) {
        $this->validate();

        $journal = $request->getJournal();
        $articleId = (int) $request->getUserVar('articleId');

        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);

        if ($article && $article->getJournalId() === $journal->getId()) {
            $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
            $editAssignments = $editAssignmentDao->getEditAssignmentsByArticleId($articleId);

            while($editAssignment = $editAssignments->next()) {
                if ($editAssignment->getIsEditor()) continue;

                $canReview = $request->getUserVar('canReview-' . $editAssignment->getEditId()) ? 1 : 0;
                $canEdit = $request->getUserVar('canEdit-' . $editAssignment->getEditId()) ? 1 : 0;

                $editAssignment->setCanReview($canReview);
                $editAssignment->setCanEdit($canEdit);

                $editAssignmentDao->updateEditAssignment($editAssignment);
            }
        }

        $request->redirect(null, null, 'submission', [$articleId]);
    }

    /**
     * Delete the specified edit assignment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteEditAssignment($args, $request) {
        $this->validate();

        $journal = $request->getJournal();
        $editId = (int) array_shift($args);

        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $editAssignment = $editAssignmentDao->getEditAssignment($editId);

        if ($editAssignment) {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($editAssignment->getArticleId());

            if ($article && $article->getJournalId() === $journal->getId()) {
                $editAssignmentDao->deleteEditAssignmentById($editAssignment->getEditId());
                $request->redirect(null, null, 'submission', [$article->getId()]);
            }
        }

        $request->redirect(null, null, 'submissions');
    }

    /**
     * Assigns the selected editor to the submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function assignEditor($args, $request) {
        $this->validate();
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_MANAGER); // manager.people.noneEnrolled

        $journal = $request->getJournal();
        $articleId = (int) $request->getUserVar('articleId');
        $editorId = (int) $request->getUserVar('editorId');
        $roleDao = DAORegistry::getDAO('RoleDAO');

        $isSectionEditor = $roleDao->userHasRole($journal->getId(), $editorId, ROLE_ID_SECTION_EDITOR);
        $isEditor = $roleDao->userHasRole($journal->getId(), $editorId, ROLE_ID_EDITOR);

        if (isset($editorId) && $editorId != null && ($isEditor || $isSectionEditor)) {
            // A valid section editor has already been chosen;
            // either prompt with a modifiable email or, if this
            // has been done, send the email and store the editor
            // selection.

            $this->setupTemplate(EDITOR_SECTION_SUBMISSIONS, $articleId, 'summary');

            // FIXME: Prompt for due date.
            // [SECURITY FIX] Terapkan (int) pada parameter 'send'
            if (EditorAction::assignEditor($articleId, $editorId, $isEditor, (int) $request->getUserVar('send'), $request)) {
                $request->redirect(null, null, 'submission', [$articleId]);
            }
        } else {
            // Allow the user to choose a section editor or editor.
            $this->setupTemplate(EDITOR_SECTION_SUBMISSIONS, $articleId, 'summary');

            $searchType = null;
            $searchMatch = null;
            $search = trim((string) $request->getUserVar('search'));
            
            $searchInitial = trim((string) $request->getUserVar('searchInitial'));
            if (!preg_match('/^[A-Z]$/i', $searchInitial)) {
                $searchInitial = ''; // Default aman jika input bukan 1 huruf
            }
            if (!empty($search)) {
                $searchType = trim((string) $request->getUserVar('searchField'));
                $allowedFields = ['title', 'author', 'editor', 'abstract', SUBMISSION_FIELD_TITLE, SUBMISSION_FIELD_AUTHOR, SUBMISSION_FIELD_EDITOR, SUBMISSION_FIELD_ID]; 
                if (!in_array($searchType, $allowedFields)) {
                    $searchType = SUBMISSION_FIELD_TITLE; // Default aman
                }
                
                $searchMatch = trim((string) $request->getUserVar('searchMatch'));
                $allowedMatches = ['all', 'any', 'phrase', 'contains']; 
                if (!in_array($searchMatch, $allowedMatches)) {
                    $searchMatch = 'all'; // Default aman
                }

            } elseif (!empty($searchInitial)) {
                $searchInitial = PKPString::strtoupper($searchInitial);
                $searchType = USER_FIELD_INITIAL;
                $search = $searchInitial;
            }

            $rangeInfo = $this->getRangeInfo('editors');
            $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');

            if (isset($args[0]) && $args[0] === 'editor') {
                $roleName = 'user.role.editor';
                $rolePath = 'editor';
                $editors = $editorSubmissionDao->getUsersNotAssignedToArticle($journal->getId(), $articleId, RoleDAO::getRoleIdFromPath('editor'), $searchType, $search, $searchMatch, $rangeInfo);
            } else {
                $roleName = 'user.role.sectionEditor';
                $rolePath = 'sectionEditor';
                $editors = $editorSubmissionDao->getUsersNotAssignedToArticle($journal->getId(), $articleId, RoleDAO::getRoleIdFromPath('sectionEditor'), $searchType, $search, $searchMatch, $rangeInfo);
            }

            $templateMgr = TemplateManager::getManager();

            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('editors', $editors);
            $templateMgr->assign('roleName', $roleName);
            $templateMgr->assign('rolePath', $rolePath);
            $templateMgr->assign('articleId', $articleId);

            $sectionDao = DAORegistry::getDAO('SectionDAO');
            $sectionEditorSections = $sectionDao->getEditorSections($journal->getId());

            $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
            $editorStatistics = $editAssignmentDao->getEditorStatistics($journal->getId());

            $templateMgr->assign('editorSections', $sectionEditorSections);
            $templateMgr->assign('editorStatistics', $editorStatistics);

            $templateMgr->assign('searchField', $searchType);
            $templateMgr->assign('searchMatch', $searchMatch);
            $templateMgr->assign('search', $search);
            // [SECURITY FIX] Gunakan variabel $searchInitial yang sudah bersih
            $templateMgr->assign('searchInitial', $searchInitial);

            $templateMgr->assign('fieldOptions', [
                USER_FIELD_FIRSTNAME => 'user.firstName',
                USER_FIELD_LASTNAME => 'user.lastName',
                USER_FIELD_USERNAME => 'user.username',
                USER_FIELD_EMAIL => 'user.email'
            ]);
            $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
            $templateMgr->assign('helpTopicId', 'editorial.editorsRole.submissionSummary.submissionManagement');
            $templateMgr->display('editor/selectSectionEditor.tpl');
        }
    }

    /**
     * Delete a submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteSubmission($args, $request) {
        $articleId = (int) array_shift($args);

        $this->validate($articleId);
        parent::setupTemplate(true);

        $journal = $request->getJournal();

        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($articleId);

        $status = $article->getStatus();

        if ($article->getJournalId() == $journal->getId() && ($status == STATUS_DECLINED || $status == STATUS_ARCHIVED)) {
            // Delete article files
            import('classes.file.ArticleFileManager');
            $articleFileManager = new ArticleFileManager($articleId);
            $articleFileManager->deleteArticleTree();

            // Delete article database entries
            $articleDao->deleteArticleById($articleId);
        }

        $request->redirect(null, null, 'submissions', 'submissionsArchives');
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
     * @param int $articleId
     * @param string|null $parentPage
     * @param bool $showSidebar
     */
    public function setupTemplate($subclass = false, $articleId = 0, $parentPage = null, $showSidebar = true) {
        // Note: The original method signature had $level but body used $level without declaring it (assuming it meant $subclass logic or global define?)
        // The original code used global defines EDITOR_SECTION_*.
        // I will adapt the signature to match usage in index() where setupTemplate(EDITOR_SECTION_HOME) was called.
        // It seems the original code signature was `setupTemplate($subclass = false...)` but called as `setupTemplate(0)`.
        // So $subclass acts as $level.
        
        $level = $subclass; // Alias for clarity based on original logic inside method
        
        parent::setupTemplate();

        // Layout Editors have access to some Issue Mgmt functions. Make sure we give them
        // the appropriate breadcrumbs and sidebar.
        $request = Application::get()->getRequest();
        $isLayoutEditor = $request->getRequestedPage() == 'layoutEditor';

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();

        $pageHierarchy = [];
        if ($level === EDITOR_SECTION_HOME) {
            $pageHierarchy = [[$request->url(null, 'user'), 'navigation.user']];
        } elseif ($level === EDITOR_SECTION_SUBMISSIONS) {
            $pageHierarchy = [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'editor'), 'user.role.editor'], [$request->url(null, 'editor', 'submissions'), 'article.submissions']];
        } elseif ($level === EDITOR_SECTION_ISSUES) {
            $pageHierarchy = [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, $isLayoutEditor?'layoutEditor':'editor'), $isLayoutEditor?'user.role.layoutEditor':'user.role.editor'], [$request->url(null, $isLayoutEditor?'layoutEditor':'editor', 'futureIssues'), 'issue.issues']];
        }

        import('classes.submission.sectionEditor.SectionEditorAction');
        $submissionCrumb = SectionEditorAction::submissionBreadcrumb($articleId, $parentPage, 'editor');
        if (isset($submissionCrumb)) {
            $pageHierarchy = array_merge($pageHierarchy, $submissionCrumb);
        }
        $templateMgr->assign('pageHierarchy', $pageHierarchy);
    }
}
?>
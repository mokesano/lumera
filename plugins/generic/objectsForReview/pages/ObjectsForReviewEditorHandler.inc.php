<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/pages/ObjectsForReviewEditorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectsForReviewEditorHandler
 * @ingroup plugins_generic_objectsForReview
 *
 * @brief Handle requests for editor objects for review functions.
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('classes.handler.Handler');

class ObjectsForReviewEditorHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ObjectsForReviewEditorHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::ObjectsForReviewEditorHandler(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Display objects for review listing pages.
     * @param array $args
     * @param PKPRequest $request
     */
    public function objectsForReview($args, $request) {
        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // Search
        $duplicateParameters = [
            'searchField', 'searchMatch', 'search'
        ];
        $fieldOptions = [
            OFR_FIELD_TITLE => 'plugins.generic.objectsForReview.search.field.title',
            OFR_FIELD_ABSTRACT => 'plugins.generic.objectsForReview.search.field.abstract'
        ];
        $searchField = null;
        $searchMatch = null;
        
        // [SECURITY FIX] Amankan 'search' (string) dengan trim() + Null Coalescing PHP 8
        $search = trim($request->getUserVar('search') ?? '');

        if (!empty($search)) {
            // [SECURITY FIX] Whitelist 'searchField'
            $validSearchFields = [
                OFR_OBJECT_SEARCH_TITLE,
                OFR_OBJECT_SEARCH_METADATA
            ];
            $searchField = $request->getUserVar('searchField');
            if (!in_array($searchField, $validSearchFields)) {
                $searchField = null; // Set default aman
            }

            // [SECURITY FIX] Whitelist 'searchMatch'
            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatch = trim($request->getUserVar('searchMatch') ?? '');
            if (!in_array($searchMatch, $validSearchMatches)) {
                $searchMatch = 'contains'; // Set default aman
            }
        }

        // Filter by editor
        import('pages.editor.EditorHandler');
        
        $user = $request->getUser();
        $filterEditorOptions = [
            FILTER_EDITOR_ALL => AppLocale::Translate('editor.allEditors'),
            FILTER_EDITOR_ME => AppLocale::Translate('editor.me')
        ];
        
        // [SECURITY FIX] Amankan 'filterEditor' (diharapkan integer)
        $filterEditor = (int) trim($request->getUserVar('filterEditor') ?? '');
        
        if (array_key_exists($filterEditor, $filterEditorOptions)) {
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
            $editorId = null;
        }

        // Filter by review object type
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        $allTypes = $reviewObjectTypeDao->getTypeIdsAlphabetizedByContext($journalId);
        $filterTypeOptions = [0 => __('common.all')];
        
        $createTypeOptions = [];
        foreach ($allTypes as $type) {
            $typeId = $type['typeId'];
            $filterTypeOptions[$typeId] = $type['typeName'];
            if ($type['typeActive']) {
                $createTypeOptions[$typeId] = $type['typeName'];
            }
        }
        
        // [SECURITY FIX] Amankan 'filterType'
        $filterType = (int) trim($request->getUserVar('filterType') ?? '');
        
        if (array_key_exists($filterType, $filterTypeOptions)) {
            $user->updateSetting('filterReviewObjectType', $filterType, 'int', $journalId);
        } else {
            $filterType = $user->getSetting('filterReviewObjectType', $journalId);
            if ($filterType == null) {
                $filterType = 0;
                $user->updateSetting('filterReviewObjectType', $filterType, 'int', $journalId);
            }
        }

        // Sort
        // [SECURITY FIX] Whitelist 'sort'
        $sortInput = trim($request->getUserVar('sort') ?? '');
        
        $validSortColumns = ['title', 'type', 'status', 'dateAdded']; 
        
        if (!empty($sortInput) && in_array($sortInput, $validSortColumns)) {
            $sort = $sortInput;
        } else {
            $sort = 'title'; // Default aman
        }

        // [SECURITY FIX] Whitelist 'sortDirection'
        $sortDirectionInput = trim($request->getUserVar('sortDirection') ?? '');
        
        if ($sortDirectionInput == SORT_DIRECTION_DESC) {
            $sortDirection = SORT_DIRECTION_DESC;
        } else {
            $sortDirection = SORT_DIRECTION_ASC; // Default aman
        }

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $mode = $ofrPlugin->getSetting($journalId, 'mode');

        $ofrPlugin->import('classes.ObjectForReviewAssignment');
        $path = !isset($args) || empty($args) ? null : $args[0];
        $template = 'objectsForReviewAssignments.tpl';
        
        switch($path) {
            case '':
                $status = null;
                $pageTitle = 'plugins.generic.objectsForReview.objectsForReview.pageTitle';
                $template = 'objectsForReview.tpl';
                break;
            case 'requested':
                $status = OFR_STATUS_REQUESTED;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleRequested';
                break;
            case 'assigned':
                $status = OFR_STATUS_ASSIGNED;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleAssigned';
                break;
            case 'mailed':
                $status = OFR_STATUS_MAILED;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleMailed';
                break;
            case 'submitted':
                $status = OFR_STATUS_SUBMITTED;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleSubmitted';
                break;
            case 'all':
            default:
                $path = 'all';
                $status = null;
                $pageTitle = 'plugins.generic.objectsForReview.objectForReviewAssignments.pageTitleAll';
        }

        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);

        foreach ($duplicateParameters as $param) {
            // [SECURITY FIX] Escape output
            $templateMgr->assign($param, htmlspecialchars(trim($request->getUserVar($param) ?? ''), ENT_QUOTES, 'UTF-8'));
        }
        $templateMgr->assign('fieldOptions', $fieldOptions);

        $templateMgr->assign('editorOptions', $filterEditorOptions);
        $templateMgr->assign('filterEditor', $filterEditor);
        $templateMgr->assign('filterTypeOptions', $filterTypeOptions);
        $templateMgr->assign('createTypeOptions', $createTypeOptions);
        $templateMgr->assign('filterType', $filterType);

        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);

        $templateMgr->assign('mode', $mode);
        $templateMgr->assign('returnPage', $path);

        if ($path == '') {
            $rangeInfo = Handler::getRangeInfo('objectsForReview');
            $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
            $objectsForReview = $ofrDao->getAllByContextId($journalId, $searchField, $search, $searchMatch, $status, $editorId, $filterType, $rangeInfo, $sort, $sortDirection);
            $templateMgr->assign('objectsForReview', $objectsForReview);
        } else {
            $rangeInfo = Handler::getRangeInfo('objectForReviewAssignments');
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            $objectForReviewAssignments = $ofrAssignmentDao->getAllByContextId($journalId, $searchField, $search, $searchMatch, $status, null, $editorId, $filterType, $rangeInfo, $sort, $sortDirection);
            $templateMgr->assign('objectForReviewAssignments', $objectForReviewAssignments);
            $templateMgr->assign('counts', $ofrAssignmentDao->getStatusCounts($journalId));
        }

        $templateMgr->assign('pageTitle', $pageTitle);
        $templateMgr->display($ofrPlugin->getTemplatePath() . 'editor' . '/' . $template);
    }

    /**
     * Edit and update object for review (plug-in) settings.
     * @param array $args
     * @param PKPRequest $request
     */
    public function objectsForReviewSettings($args, $request) {
        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.form.ObjectsForReviewSettingsForm');
        $settingsForm = new ObjectsForReviewSettingsForm($ofrPlugin, $journalId);
        
        // [SECURITY FIX] Amankan flag boolean 'save'
        if ($settingsForm->isLocaleResubmit() || (int) trim($request->getUserVar('save') ?? '')) {
            $settingsForm->readInputData();
            
            // [SECURITY FIX] Amankan flag boolean 'save'
            if ((int) trim($request->getUserVar('save') ?? '')) {
                if ($settingsForm->validate()) {
                    $settingsForm->execute();
                    
                    // Notification
                    $user = $request->getUser();
                    import('classes.notification.NotificationManager');
                    $notificationManager = new NotificationManager();
                    $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_OFR_SETTINGS_SAVED);

                    $request->redirect(null, 'editor', 'objectsForReviewSettings');
                }
            }
        } else {
            $settingsForm->initData();
        }
        $settingsForm->display($request);
    }

    /**
     * Create/edit object for review.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createObjectForReview($args, $request) {
        $this->editObjectForReview($args, $request);
    }

    /**
     * Create/edit object for review.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editObjectForReview($args, $request) {
        $objectId = array_shift($args);
        // [SECURITY FIX] Amankan 'reviewObjectTypeId'
        $reviewObjectTypeId = (int) trim($request->getUserVar('reviewObjectTypeId') ?? '');

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        if (!$this->_ensureObjectExists($objectId, $journalId, $reviewObjectTypeId) && !isset($reviewObjectTypeId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        if (!$reviewObjectTypeDao->reviewObjectTypeExists($reviewObjectTypeId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager($request);
        if ($objectId) {
            $templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.edit');
        } else {
            $templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.create');
        }
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.form.ObjectForReviewForm');
        $ofrForm = new ObjectForReviewForm($ofrPlugin->getName(), $objectId, $reviewObjectTypeId);
        $ofrForm->initData();
        $ofrForm->display($request);
    }

    /**
     * Update object for review.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateObjectForReview($args, $request) {
        // [SECURITY FIX] Amankan 'objectId'
        $objectId = (int) trim($request->getUserVar('objectId') ?? '');
        
        // [SECURITY FIX] Amankan 'reviewObjectTypeId'
        $reviewObjectTypeId = (int) trim($request->getUserVar('reviewObjectTypeId') ?? '');

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        if ($objectId && !$this->_ensureObjectExists($objectId, $journalId, $reviewObjectTypeId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.form.ObjectForReviewForm');
        $ofrForm = new ObjectForReviewForm($ofrPlugin->getName(), $objectId, $reviewObjectTypeId);
        $ofrForm->readInputData();

        // Add a role block
        // [SECURITY FIX] Amankan flag boolean 'addPerson'
        if ((int) trim($request->getUserVar('addPerson') ?? '')) {
            $editData = true;
            $persons = $ofrForm->getData('persons');
            array_push($persons, []);
            $ofrForm->setData('persons', $persons);

        // Delete persons
        // [SECURITY FIX] Amankan 'delPerson' sebagai array
        } else if (($delPerson = (array) $request->getUserVar('delPerson')) && count($delPerson) == 1) {
            $editData = true;
            list($delPerson) = array_keys($delPerson);
            $delPerson = (int) $delPerson; 
            $persons = $ofrForm->getData('persons');
            if (isset($persons[$delPerson]['personId']) && !empty($persons[$delPerson]['personId'])) {
                $deletedPersons = explode(':', $ofrForm->getData('deletedPersons'));
                array_push($deletedPersons, $persons[$delPerson]['personId']);
                $ofrForm->setData('deletedPersons', implode(':', $deletedPersons));
            }
            array_splice($persons, $delPerson, 1);
            $ofrForm->setData('persons', $persons);

        // Change person order
        // [SECURITY FIX] Amankan flag boolean 'movePerson'
        } else if ((int) trim($request->getUserVar('movePerson') ?? '')) {
            $editData = true;
            
            // [SECURITY FIX] Amankan string key 'movePersonDir'
            $movePersonDir = trim($request->getUserVar('movePersonDir') ?? '');
            $movePersonDir = $movePersonDir == 'u' ? 'u' : 'd'; 
            
            // [SECURITY FIX] Amankan 'movePersonIndex'
            $movePersonIndex = (int) trim($request->getUserVar('movePersonIndex') ?? '');
            $persons = $ofrForm->getData('persons');

            if (!(($movePersonDir == 'u' && $movePersonIndex <= 0) || ($movePersonDir == 'd' && $movePersonIndex >= count($persons) - 1))) {
                $tmpPerson = $persons[$movePersonIndex];
                if ($movePersonDir == 'u') {
                    $persons[$movePersonIndex] = $persons[$movePersonIndex - 1];
                    $persons[$movePersonIndex - 1] = $tmpPerson;
                } else {
                    $persons[$movePersonIndex] = $persons[$movePersonIndex + 1];
                    $persons[$movePersonIndex + 1] = $tmpPerson;
                }
            }
            $ofrForm->setData('persons', $persons);
        }

        if (!isset($editData) && $ofrForm->validate()) {
            $ofrForm->execute();
            // Notification
            if ($objectId) {
                $notificationType = NOTIFICATION_TYPE_OFR_UPDATED;
            } else {
                $notificationType = NOTIFICATION_TYPE_OFR_CREATED;
            }
            $this->_createTrivialNotification($notificationType, $request);

            // [SECURITY FIX] Amankan flag boolean 'createAnother'
            if ((int) trim($request->getUserVar('createAnother') ?? '')) {
                $request->redirect(null, 'editor', 'createObjectForReview');
            // [SECURITY FIX] Amankan flag boolean
            } elseif ((int) trim($request->getUserVar('addPerson') ?? '') || (int) trim($request->getUserVar('delPerson') ?? '') || (int) trim($request->getUserVar('movePerson') ?? '')) {
                $request->redirect(null, 'editor', 'editObjectForReview', $objectId, ['reviewObjectTypeId' => $reviewObjectTypeId]);
            } else {
                $request->redirect(null, 'editor', 'objectsForReview');
            }
        } else {
            $this->setupTemplate($request, true);
            $templateMgr = TemplateManager::getManager($request);
            if ($objectId) {
                $templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.edit');
            } else {
                $templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.create');
            }
            $ofrForm->display($request);
        }
    }

    /**
     * Remove object for review cover page image.
     * @param array $args
     * @param PKPRequest $request
     */
    public function removeObjectForReviewCoverPage($args, $request) {
        $objectId = array_shift($args);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        if (!$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }

        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($objectId, $journalId);
        $coverPageSetting = $objectForReview->getCoverPage();
        if ($coverPageSetting) {
            // Delete cover image file from the filesystem
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $publicFileManager->removeJournalFile($journalId, $coverPageSetting['fileName']);
            // Delete object for review setting
            $ofrPlugin = $this->_getObjectsForReviewPlugin();
            $ofrPlugin->import('classes.ReviewObjectMetadata');
            $metadataId = $objectForReview->getMetadataId(REVIEW_OBJECT_METADATA_KEY_COVERPAGE);
            $ofrSettingsDao = DAORegistry::getDAO('ObjectForReviewSettingsDAO');
            $ofrSettingsDao->deleteSetting($objectId, $metadataId);
        }
        $request->redirect(null, 'editor', 'editObjectForReview', $objectId, ['reviewObjectTypeId' => $objectForReview->getReviewObjectTypeId()]);
    }

    /**
     * Delete object for review.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteObjectForReview($args, $request) {
        $objectId = array_shift($args);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        if ($this->_ensureObjectExists($objectId, $journalId)) {
            $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
            $objectForReview = $ofrDao->getById($objectId, $journalId);
            $ofrDao->deleteObject($objectForReview);
            $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_DELETED, $request);
        }
        $request->redirect(null, 'editor', 'objectsForReview');
    }

    /**
     * Display a list of authors from which to choose an object reviewer.
     * @param array $args
     * @param PKPRequest $request
     */
    public function selectObjectForReviewAuthor($args, $request) {
        $objectId = array_shift($args);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        if (!$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }

        // Search
        $searchField = null;
        $searchMatch = null;
        
        // [SECURITY FIX] Amankan 'search' (string)
        $search = trim($request->getUserVar('search') ?? '');

        // [SECURITY FIX] Amankan 'searchInitial'
        $searchInitial = trim($request->getUserVar('searchInitial') ?? '');
        if (!preg_match('/^[A-Z0-9]$/i', $searchInitial)) {
            $searchInitial = ''; // Set default aman
        }

        if (!empty($search)) {
            // [SECURITY FIX] Whitelist 'searchField'
            $validSearchFields = [
                OFR_OBJECT_SEARCH_TITLE,
                OFR_OBJECT_SEARCH_METADATA
            ];
            $searchFieldInput = $request->getUserVar('searchField');
            if (in_array($searchFieldInput, $validSearchFields)) {
                $searchField = $searchFieldInput;
            } else {
                $searchField = null; // Default aman
            }

            // [SECURITY FIX] Whitelist 'searchMatch'
            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatchInput = trim($request->getUserVar('searchMatch') ?? '');
            if (in_array($searchMatchInput, $validSearchMatches)) {
                $searchMatch = $searchMatchInput;
            } else {
                $searchMatch = 'contains'; // Default aman
            }

        } else if (!empty($searchInitial)) {
            $searchInitial = PKPString::strtoupper($searchInitial);
            $searchField = USER_FIELD_INITIAL; // Konstanta ini aman
            $search = $searchInitial;
        }
        $fieldOptions = [
            USER_FIELD_FIRSTNAME => 'user.firstName',
            USER_FIELD_LASTNAME => 'user.lastName',
            USER_FIELD_USERNAME => 'user.username',
            USER_FIELD_EMAIL => 'user.email'
        ];

        // Get all and those authors assigned to this object
        $rangeInfo = Handler::getRangeInfo('users');
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $users = $roleDao->getUsersByRoleId(ROLE_ID_AUTHOR, $journalId, $searchField, $search, $searchMatch, $rangeInfo);
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $usersAssigned = $ofrAssignmentDao->getUserIds($objectId);

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('objectId', $objectId);

        $templateMgr->assign('searchField', $searchField);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $search);
        $templateMgr->assign('searchInitial', $searchInitial);
        $templateMgr->assign('searchFieldOptions', $fieldOptions);
        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));

        $templateMgr->assign('users', $users);
        $templateMgr->assign('usersAssigned', $usersAssigned);

        import('classes.security.Validation');
        $templateMgr->assign('isJournalManager', Validation::isJournalManager());

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $templateMgr->display($ofrPlugin->getTemplatePath() . 'editor' . '/' . 'authors.tpl');
    }

    /**
     * Assign an object for review author.
     * @param array $args
     * @param PKPRequest $request
     */
    public function assignObjectForReviewAuthor($args, $request) {
        $objectId = array_shift($args);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        if (!$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($objectId, $journalId);

        $redirect = true;
        if ($objectForReview->getAvailable()) {
            // [SECURITY FIX] Amankan 'userId'
            $userId = (int) trim($request->getUserVar('userId') ?? '');
            
            // Ensure there is no assignment for this object and user
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            if ($ofrAssignmentDao->assignmentExists($objectId, $userId)) {
                $request->redirect(null, 'editor', 'objectsForReview');
            }
            // Ensure the user exists and is an author for this journal
            $userDao = DAORegistry::getDAO('UserDAO');
            $user = $userDao->getById($userId);
            $roleDao = DAORegistry::getDAO('RoleDAO');
            if (isset($user) && $roleDao->userHasRole($journalId, $userId, ROLE_ID_AUTHOR)) {
                $returnUrl = $request->url(null, 'editor', 'assignObjectForReviewAuthor', $objectId, ['userId' => $userId]);
                // Assign
                $redirect = $this->_assign(null, $objectForReview, $user, $returnUrl, $request);
            }
        }
        if ($redirect) $request->redirect(null, 'editor', 'objectsForReview', 'assigned');
    }

    /**
     * Accept an object for review author.
     * @param array $args
     * @param PKPRequest $request
     */
    public function acceptObjectForReviewAuthor($args, $request) {
        $returnPage = $this->_getReturnpage($request);

        $assignmentId = array_shift($args);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $redirect = true;
        // Ensure the assignment exists
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.ObjectForReviewAssignment');
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId, OFR_STATUS_REQUESTED)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);
        // Get the author
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getById($ofrAssignment->getUserId());
        $returnUrl = $request->url(null, 'editor', 'acceptObjectForReviewAuthor', $assignmentId, ['returnPage' => $returnPage]);
        // Assign
        $redirect = $this->_assign($ofrAssignment, $ofrAssignment->getObjectForReview(), $user, $returnUrl, $request);

        if ($redirect) {
            if ($returnPage != 'all') $returnPage = 'assigned';
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }
    }

    /**
     * Deny an object for review request.
     * @param array $args
     * @param PKPRequest $request
     */
    public function denyObjectForReviewAuthor($args, $request) {
        $returnPage = $this->_getReturnpage($request);

        $assignmentId = array_shift($args);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // Ensure the assignment exists
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.ObjectForReviewAssignment');
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId, OFR_STATUS_REQUESTED)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);

        $redirect = true;
        import('classes.mail.MailTemplate');
        $email = new MailTemplate('OFR_OBJECT_DENIED');
        
        // [SECURITY FIX] Amankan flag boolean 'send'
        $send = (int) trim($request->getUserVar('send') ?? '');
        
        // Editor has filled out mail form or skipped mail
        if ($send && !$email->hasErrors()) {
            // Delete the assignment
            $ofrAssignmentDao->deleteById($assignmentId);
            $email->send();
            $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_AUTHOR_DENIED, $request);
        } else {
            $returnUrl = $request->url(null, 'editor', 'denyObjectForReviewAuthor', $assignmentId, ['returnPage' => $returnPage]);
            $this->_displayEmailForm($email, $ofrAssignment->getObjectForReview(), $ofrAssignment->getUser(), $returnUrl, 'OFR_OBJECT_DENIED', $request);
            $redirect = false;
        }
        if ($redirect) $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
    }

    /**
     * Mark an object for review assignment as mailed.
     * @param array $args
     * @param PKPRequest $request
     */
    public function notifyObjectForReviewMailed($args, $request) {
        $returnPage = $this->_getReturnpage($request);

        $assignmentId = array_shift($args);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // Ensure the assignment exists
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.ObjectForReviewAssignment');
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId, OFR_STATUS_ASSIGNED)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);

        $redirect = true;
        import('classes.mail.MailTemplate');
        $email = new MailTemplate('OFR_OBJECT_MAILED');
        
        // [SECURITY FIX] Amankan flag boolean 'send'
        $send = (int) trim($request->getUserVar('send') ?? '');
        
        // Editor has filled out mail form or skipped mail
        if ($send && !$email->hasErrors()) {
            // Update status
            $ofrAssignment->setStatus(OFR_STATUS_MAILED);
            // Update due date
            $dueWeeks = $ofrPlugin->getSetting($journalId, 'dueWeeks');
            $dueDateTimestamp = time() + ($dueWeeks * 7 * 24 * 60 * 60);
            $dueDate = date('Y-m-d H:i:s', $dueDateTimestamp);
            $ofrAssignment->setDateDue($dueDate);
            // Set date mailed and update the assignment
            $ofrAssignment->setDateMailed(Core::getCurrentDate());
            $ofrAssignmentDao->updateObject($ofrAssignment);
            $email->send();
            $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_AUTHOR_MAILED, $request);
        } else {
            $returnUrl = $request->url(null, 'editor', 'notifyObjectForReviewMailed', $assignmentId, ['returnPage' => $returnPage]);
            $this->_displayEmailForm($email, $ofrAssignment->getObjectForReview(), $ofrAssignment->getUser(), $returnUrl, 'OFR_OBJECT_MAILED', $request);
            $redirect = false;
        }
        if ($returnPage != 'all') $returnPage = 'mailed';
        if ($redirect) $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
    }

    /**
     * Remove object reviewer assignment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function removeObjectForReviewAssignment($args, $request) {
        $returnPage = $this->_getReturnpage($request);

        $assignmentId = array_shift($args);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // Ensure the assignment exists
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);
        // Ensure the assignment can be removed
        if (!$this->_canBeRemoved($ofrAssignment)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }

        $redirect = true;
        import('classes.mail.MailTemplate');
        $email = new MailTemplate('OFR_REVIEWER_REMOVED');
        
        // [SECURITY FIX] Amankan flag boolean 'send'
        $send = (int) trim($request->getUserVar('send') ?? '');
        
        // Editor has filled out mail form or skipped mail
        if ($send && !$email->hasErrors()) {
            // Delete the assignment
            $ofrAssignmentDao->deleteById($assignmentId);
            $email->send();
            $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_AUTHOR_REMOVED, $request);
        } else {
            $returnUrl = $request->url(null, 'editor', 'removeObjectForReviewAssignment', $assignmentId, ['returnPage' => $returnPage]);
            $this->_displayEmailForm($email, $ofrAssignment->getObjectForReview(), $ofrAssignment->getUser(), $returnUrl, 'OFR_REVIEWER_REMOVED', $request);
            $redirect = false;
        }
        if ($redirect) $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
    }

    /**
     * Display a list of submissions from which to choose an object review submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function selectObjectForReviewSubmission($args, $request) {
        $returnPage = $this->_getReturnpage($request);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $mode = $ofrPlugin->getSetting($journalId, 'mode');

        $assignmentId = array_shift($args);
        if ($mode == OFR_MODE_FULL) {
            if (!$this->_ensureAssignmentExists($assignmentId, $journalId)) {
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
            }
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);
            $objectId = $ofrAssignment->getObjectId();
        }
        if ($mode == OFR_MODE_METADATA) {
            // [SECURITY FIX] Amankan 'objectId'
            $objectId = (int) trim($request->getUserVar('objectId') ?? '');
            // Ensure the object exists
            if (!$this->_ensureObjectExists($objectId, $journalId)) {
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
            }
        }

        // Search
        $searchField = null;
        $searchMatch = null;
        // [SECURITY FIX] Amankan 'search' (string)
        $search = trim($request->getUserVar('search') ?? '');

        if (!empty($search)) {
            // [SECURITY FIX] Whitelist 'searchField'
            $validSearchFields = [
                OFR_OBJECT_SEARCH_TITLE,
                OFR_OBJECT_SEARCH_METADATA
            ];
            $searchField = $request->getUserVar('searchField');
            if (!in_array($searchField, $validSearchFields)) {
                $searchField = null; // Set default aman
            }

            // [SECURITY FIX] Whitelist 'searchMatch'
            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatch = trim($request->getUserVar('searchMatch') ?? '');
            if (!in_array($searchMatch, $validSearchMatches)) {
                $searchMatch = 'contains'; // Set default aman
            }
        }
        import('classes.submission.common.Action');
        $fieldOptions = [
            SUBMISSION_FIELD_TITLE => 'article.title',
            SUBMISSION_FIELD_ID => 'article.submissionId',
            SUBMISSION_FIELD_AUTHOR => 'user.role.author',
        ];

        // Get submissions assigned to this user/editor
        $user = $request->getUser();
        $editorId = $user->getId();
        $rangeInfo = Handler::getRangeInfo('submissions');
        $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
        $submissions = $editorSubmissionDao->getEditorSubmissions(
            $journalId,
            0,
            $editorId,
            $searchField,
            $searchMatch,
            $search,
            null,
            null,
            null,
            $rangeInfo,
            'id',
            SORT_DIRECTION_DESC
        );

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('assignmentId', $assignmentId);
        $templateMgr->assign('objectId', $objectId);
        $templateMgr->assign('returnPage', $returnPage);

        $templateMgr->assign('searchField', $searchField);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $search);
        $templateMgr->assign('searchFieldOptions', $fieldOptions);

        $templateMgr->assign('submissions', $submissions);
        $templateMgr->display($ofrPlugin->getTemplatePath() . 'editor' . '/' . 'submissions.tpl');
    }

    /**
     * Assign an object for review submission.
     * @param array $args
     * @param PKPRequest $request
     */
    public function assignObjectForReviewSubmission($args, $request) {
        $returnPage = $this->_getReturnpage($request);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $mode = $ofrPlugin->getSetting($journalId, 'mode');

        $assignmentId = array_shift($args);
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $insert = false;
        
        $ofrAssignment = null;
        if ($mode == OFR_MODE_FULL) {
            if (!$this->_ensureAssignmentExists($assignmentId, $journalId)) {
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
            }
            $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);
        } elseif ($mode == OFR_MODE_METADATA) {
            // [SECURITY FIX] Amankan 'objectId'
            $objectId = (int) trim($request->getUserVar('objectId') ?? '');
            if (!$this->_ensureObjectExists($objectId, $journalId)) {
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
            }
            $ofrAssignment = $ofrAssignmentDao->newDataObject();
            $ofrAssignment->setObjectId($objectId);
            $insert = true;
        }

        // [SECURITY FIX] Amankan 'submissionId'
        $submissionId = (int) trim($request->getUserVar('submissionId') ?? '');
        
        // Ensure article is for this journal and update object for review assignment
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        if ($ofrAssignment && $articleDao->getArticleJournalId($submissionId) == $journalId) {
            $ofrAssignment->setSubmissionId($submissionId);
            $ofrAssignment->setStatus(OFR_STATUS_SUBMITTED);
            if ($insert) {
                $ofrAssignmentDao->insertObject($ofrAssignment);
            } else {
                $ofrAssignmentDao->updateObject($ofrAssignment);
            }
            $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_SUBMISSION_ASSIGNED, $request);
        }

        if ($returnPage != 'all') $returnPage = 'submitted';
        $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
    }

    /**
     * Edit object for review assignment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editObjectForReviewAssignment($args, $request) {
        $returnPage = $this->_getReturnpage($request);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $assignmentId = array_shift($args);
        // [SECURITY FIX] Amankan 'objectId'
        $objectId = (int) trim($request->getUserVar('objectId') ?? '');
        
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId) || !$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId, $objectId);
        if (!isset($ofrAssignment)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.edit');

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.form.ObjectForReviewAssignmentForm');
        $ofrAssignmentForm = new ObjectForReviewAssignmentForm($ofrPlugin->getName(), $assignmentId, $objectId);
        $ofrAssignmentForm->initData();
        $mode = $ofrPlugin->getSetting($journalId, 'mode');
        $templateMgr->assign('mode', $mode);
        $templateMgr->assign('returnPage', $returnPage);
        $ofrAssignmentForm->display($request);
    }

    /**
     * Update object for review assignment.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateObjectForReviewAssignment($args, $request) {
        $returnPage = $this->_getReturnpage($request);

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        // [SECURITY FIX] Amankan 'assignmentId'
        $assignmentId = (int) trim($request->getUserVar('assignmentId') ?? '');
        
        // [SECURITY FIX] Amankan 'objectId'
        $objectId = (int) trim($request->getUserVar('objectId') ?? '');
        
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId) || !$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId, $objectId);
        if (!isset($ofrAssignment)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        }

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $ofrPlugin->import('classes.form.ObjectForReviewAssignmentForm');
        $ofrAssignmentForm = new ObjectForReviewAssignmentForm($ofrPlugin->getName(), $assignmentId, $objectId);
        $ofrAssignmentForm->readInputData();
        if ($ofrAssignmentForm->validate()) {
            $ofrAssignmentForm->execute();
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage);
        } else {
            $this->setupTemplate($request, true);
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.edit');
            $mode = $ofrPlugin->getSetting($journalId, 'mode');
            $templateMgr->assign('mode', $mode);
            $templateMgr->assign('returnPage', $returnPage);
            $ofrAssignmentForm->display($request);
        }
    }

    /**
     * Return valid landing/return pages
     * @return array
     */
    public function &getValidReturnPages() {
        $validPages = [
            'all',
            'requested',
            'assigned',
            'mailed',
            'submitted'
        ];
        return $validPages;
    }

    /**
     * Ensure that we have a journal, plugin is enabled, and user is editor.
     * @see PKPHandler::authorize()
     */
    public function authorize($request, $args, $roleAssignments) {
        $journal = $request->getJournal();
        if (!isset($journal)) return false;

        $ofrPlugin = $this->_getObjectsForReviewPlugin();

        if (!isset($ofrPlugin)) return false;

        if (!$ofrPlugin->getEnabled()) return false;

        if (!Validation::isEditor($journal->getId())) Validation::redirectLogin();;

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param PKPRequest $request
     * @param boolean $subclass set to true if caller is below this handler in the hierarchy
     * @param int $objectId
     */
    public function setupTemplate($request, $subclass = false, $objectId = null) {
        $templateMgr = TemplateManager::getManager($request);
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user'
            ],
            [
                $request->url(null, 'editor'),
                'user.role.editor'
            ]
        ];
        if ($subclass) {
            // [SECURITY FIX] Amankan 'returnPage'
            $returnPage = trim($request->getUserVar('returnPage') ?? '');
    
            if (!empty($returnPage)) { 
                $validPages = $this->getValidReturnPages();
                if (!in_array($returnPage, $validPages)) {
                    $returnPage = null; // Set default aman
                }
            }
            $pageCrumbs[] = [
                $request->url(null, 'editor', 'objectsForReview', $returnPage),
                AppLocale::Translate('plugins.generic.objectsForReview.displayName'),
                true
            ];
        }
        if ($objectId) {
            $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
            $objectForReview = $ofrDao->getById($objectId);
            $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
            $reviewObjectType = $reviewObjectTypeDao->getById($objectForReview->getReviewObjectTypeId());
            
            $pageCrumbs[] = [
                $request->url(null, 'editor', 'objectsForReview', $objectId),
                $reviewObjectType->getLocalizedName(),
                true
            ];
        }
        $templateMgr->assign('pageHierarchy', $pageCrumbs);
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        $templateMgr->addStyleSheet(Request::getBaseUrl() . '/' . $ofrPlugin->getStyleSheet());
    }

    //
    // Private helper methods
    //
    /**
     * Get the objectForReview plugin object
     * @return ObjectsForReviewPlugin
     */
    protected function _getObjectsForReviewPlugin() {
        $plugin = PluginRegistry::getPlugin('generic', OBJECTS_FOR_REVIEW_PLUGIN_NAME);
        return $plugin;
    }

    /**
     * Get return page
     * @param PKPRequest $request
     * @return string
     */
    protected function _getReturnpage($request) {
        
        // [SECURITY FIX] Amankan 'returnPage'
        $returnPage = trim($request->getUserVar('returnPage') ?? '');

        if (!empty($returnPage)) {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages)) {
                $returnPage = null; // Set default aman
            }
        }
        return $returnPage;
    }

    /**
     * Ensure object for review exists
     * @param int $objectId
     * @param int $journalId
     * @param int $reviewObjectTypeId (optional)
     * @return boolean
     */
    protected function _ensureObjectExists($objectId, $journalId, $reviewObjectTypeId = null) {
        if (!$objectId) {
            return false;
        }
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($objectId, $journalId);
        if (!isset($objectForReview)) {
            return false;
        }
        if ($reviewObjectTypeId && ($objectForReview->getReviewObjectTypeId() != $reviewObjectTypeId)) {
            return false;
        }
        return true;
    }

    /**
     * Ensure object for review assignment exists
     * @param int $assignmentId
     * @param int $journalId
     * @param int $status (optional)
     * @return boolean
     */
    protected function _ensureAssignmentExists($assignmentId, $journalId, $status = null) {
        if (!$assignmentId) {
            return false;
        }
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);
        if (!isset($ofrAssignment)) {
            return false;
        }
        // Ensure status
        if ($status && ($ofrAssignment->getStatus() != $status)) {
            return false;
        }
        // Ensure the object exists
        return $this->_ensureObjectExists($ofrAssignment->getObjectId(), $journalId);
    }

    /**
     * Assign an author to an object for review.
     * @param ObjectForReviewAssignment $ofrAssignment
     * @param ObjectForReview $objectForReview
     * @param User $author
     * @param string $returnUrl
     * @param PKPRequest $request
     */
    protected function _assign($ofrAssignment, $objectForReview, $author, $returnUrl, $request) {
        import('classes.mail.MailTemplate');
        $email = new MailTemplate('OFR_OBJECT_ASSIGNED');
        // [SECURITY FIX] Amankan flag boolean 'send'
        $send = (int) trim($request->getUserVar('send') ?? '');

        // Editor has filled out mail form or skipped mail
        if ($send && !$email->hasErrors()) {
            // Update object for review
            $ofrPlugin = $this->_getObjectsForReviewPlugin();
            $journal = $request->getJournal();
            $dueWeeks = $ofrPlugin->getSetting($journal->getId(), 'dueWeeks');
            $dueDateTimestamp = time() + ($dueWeeks * 7 * 24 * 60 * 60);
            $dueDate = date('Y-m-d H:i:s', $dueDateTimestamp);

            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            if (!isset($ofrAssignment)) {
                $ofrAssignment = $ofrAssignmentDao->newDataObject();
                $ofrAssignment->setObjectId($objectForReview->getId());
                $ofrAssignment->setUserId($author->getId());
            }
            $ofrAssignment->setStatus(OFR_STATUS_ASSIGNED);
            $ofrAssignment->setDateAssigned(Core::getCurrentDate());
            $ofrAssignment->setDateDue($dueDate);
            if ($ofrAssignment->getId() == null) {
                $ofrAssignmentDao->insertObject($ofrAssignment);
            } else {
                $ofrAssignmentDao->updateObject($ofrAssignment);
            }
            $email->send();
            $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_AUTHOR_ASSIGNED, $request);
            return true;
        } else {
            $this->_displayEmailForm($email, $objectForReview, $author, $returnUrl, 'OFR_OBJECT_ASSIGNED', $request);
            return false;
        }
    }

    /**
     * Is remove action allowed
     * @param ObjectForReviewAssignment $ofrAssignment
     * @return boolean
     */
    protected function _canBeRemoved($ofrAssignment) {
        return ($ofrAssignment->getStatus() == OFR_STATUS_ASSIGNED) || ($ofrAssignment->getStatus() == OFR_STATUS_MAILED) || ($ofrAssignment->getStatus() == OFR_STATUS_SUBMITTED);
    }

    /**
     * Display email form for the editor
     * @param MailTemplate $email
     * @param ObjectForReview $objectForReview
     * @param User $user
     * @param string $returnUrl
     * @param string $action
     * @param PKPRequest $request
     */
    protected function _displayEmailForm($email, $objectForReview, $user, $returnUrl, $action, $request) {
        // [SECURITY FIX] Amankan flag boolean 'continued'
        if (!(int) trim($request->getUserVar('continued') ?? '')) {
            $userFullName = $user->getFullName();
            $userEmail = $user->getEmail();
            $userMailingAddress = $user->getMailingAddress();
            $userCountryCode = $user->getCountry();
            if (empty($userMailingAddress)) {
                $userMailingAddress = __('plugins.generic.objectsForReview.editor.noMailingAddress');
            } else {
                $countryDao = DAORegistry::getDAO('CountryDAO');
                $countries = $countryDao->getCountries();
                $userCountry = $countries[$userCountryCode];
                $userMailingAddress .= "\n" . $userCountry;
            }

            $editor = $objectForReview->getEditor();
            $editorFullName = $editor->getFullName();
            $editorEmail = $editor->getEmail();
            $editorContactSignature = $editor->getContactSignature();
            
            $paramArray = [];

            if ($action == 'OFR_OBJECT_ASSIGNED') {
                $ofrPlugin = $this->_getObjectsForReviewPlugin();
                $journal = $request->getJournal();
                $dueWeeks = $ofrPlugin->getSetting($journal->getId(), 'dueWeeks');
                $dueDateTimestamp = time() + ($dueWeeks * 7 * 24 * 60 * 60);
                $paramArray = [
                    'authorName' => strip_tags($userFullName),
                    'authorMailingAddress' => PKPString::html2text($userMailingAddress),
                    'objectForReviewTitle' => '"' . strip_tags($objectForReview->getTitle()) . '"',
                    'objectForReviewDueDate' => date('l, F j, Y', $dueDateTimestamp),
                    'userProfileUrl' => $request->url(null, 'user', 'profile'),
                    'submissionUrl' => $request->url(null, 'author', 'submit'),
                    'editorialContactSignature' => PKPString::html2text($editorContactSignature)
                ];
            } elseif ($action == 'OFR_OBJECT_DENIED') {
                $paramArray = [
                    'authorName' => strip_tags($userFullName),
                    'objectForReviewTitle' => '"' . strip_tags($objectForReview->getTitle()) . '"',
                    'submissionUrl' => $request->url(null, 'author', 'submit'),
                    'editorialContactSignature' => PKPString::html2text($editorContactSignature)
                ];
            } elseif ($action == 'OFR_OBJECT_MAILED') {
                $paramArray = [
                    'authorName' => strip_tags($userFullName),
                    'authorMailingAddress' => PKPString::html2text($userMailingAddress),
                    'objectForReviewTitle' => '"' . strip_tags($objectForReview->getTitle()) . '"',
                    'submissionUrl' => $request->url(null, 'author', 'submit'),
                    'editorialContactSignature' => PKPString::html2text($editorContactSignature)
                ];
            } elseif ($action == 'OFR_REVIEWER_REMOVED') {
                $paramArray = [
                    'authorName' => strip_tags($userFullName),
                    'objectForReviewTitle' => '"' . strip_tags($objectForReview->getTitle()) . '"',
                    'editorialContactSignature' => PKPString::html2text($editorContactSignature)
                ];
            }
            $email->addRecipient($userEmail, $userFullName);
            $email->setFrom($editorEmail, $editorFullName);
            $email->assignParams($paramArray);
        }
        $email->displayEditForm($returnUrl);
    }

    /**
     * Create trivial notification
     * @param int $notificationType
     * @param PKPRequest $request
     */
    protected function _createTrivialNotification($notificationType, $request) {
        $user = $request->getUser();
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();
        $notificationManager->createTrivialNotification($user->getId(), $notificationType);
    }

}

?>
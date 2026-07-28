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
 */

import('classes.handler.Handler');

class ObjectsForReviewEditorHandler extends Handler {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ObjectsForReviewEditorHandler() {
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
     * Display objects for review listing pages.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function objectsForReview($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        // Search
        $duplicateParameters = ['searchField', 'searchMatch', 'search'];
        $fieldOptions = [
            OFR_FIELD_TITLE => 'plugins.generic.objectsForReview.search.field.title',
            OFR_FIELD_ABSTRACT => 'plugins.generic.objectsForReview.search.field.abstract'
        ];
        $searchField = null;
        $searchMatch = null;
        
        $search = (string) $request->getUserVar('search');

        if ($search !== '') {
            // [WIZDAM FIX] Replaced undefined constants with correct user/object search fields
            $validSearchFields = [OFR_FIELD_TITLE, OFR_FIELD_ABSTRACT];
            $searchFieldInput = (string) $request->getUserVar('searchField');
            if (in_array($searchFieldInput, $validSearchFields, true)) {
                $searchField = $searchFieldInput;
            }

            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatchInput = (string) $request->getUserVar('searchMatch');
            if (in_array($searchMatchInput, $validSearchMatches, true)) {
                $searchMatch = $searchMatchInput;
            } else {
                $searchMatch = 'contains';
            }
        }

        // Filter by editor
        import('pages.editor.EditorHandler');
        
        $user = $request->getUser();
        if (!$user) {
            $request->redirect(null, 'index');
        }
        
        $filterEditorOptions = [
            FILTER_EDITOR_ALL => __('editor.allEditors'),
            FILTER_EDITOR_ME => __('editor.me')
        ];
        
        $filterEditorInput = $request->getUserVar('filterEditor');
        $filterEditor = $filterEditorInput !== null && $filterEditorInput !== '' ? (int) $filterEditorInput : null;
        
        if (array_key_exists($filterEditor, $filterEditorOptions)) {
            $user->updateSetting('filterEditor', $filterEditor, 'int', $journalId);
        } else {
            $filterEditor = $user->getSetting('filterEditor', $journalId);
            if ($filterEditor === null) {
                $filterEditor = FILTER_EDITOR_ALL;
                $user->updateSetting('filterEditor', $filterEditor, 'int', $journalId);
            } else {
                $filterEditor = (int) $filterEditor;
            }
        }
        
        $editorId = ($filterEditor === FILTER_EDITOR_ME) ? (int) $user->getId() : null;

        // Filter by review object type
        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        $allTypes = $reviewObjectTypeDao->getTypeIdsAlphabetizedByContext($journalId);
        $filterTypeOptions = [0 => __('common.all')];
        
        $createTypeOptions = [];
        foreach ($allTypes as $type) {
            $typeId = (int) $type['typeId'];
            $filterTypeOptions[$typeId] = (string) $type['typeName'];
            if ((int) $type['typeActive'] === 1) {
                $createTypeOptions[$typeId] = (string) $type['typeName'];
            }
        }
        
        $filterTypeInput = $request->getUserVar('filterType');
        $filterType = $filterTypeInput !== null && $filterTypeInput !== '' ? (int) $filterTypeInput : null;
        
        if (array_key_exists($filterType, $filterTypeOptions)) {
            $user->updateSetting('filterReviewObjectType', $filterType, 'int', $journalId);
        } else {
            $filterType = $user->getSetting('filterReviewObjectType', $journalId);
            if ($filterType === null) {
                $filterType = 0;
                $user->updateSetting('filterReviewObjectType', $filterType, 'int', $journalId);
            } else {
                $filterType = (int) $filterType;
            }
        }

        // Sort
        $sortInput = (string) $request->getUserVar('sort');
        $validSortColumns = ['title', 'type', 'status', 'dateAdded']; 
        
        if ($sortInput !== '' && in_array($sortInput, $validSortColumns, true)) {
            $sort = $sortInput;
        } else {
            $sort = 'title';
        }

        $sortDirectionInput = (string) $request->getUserVar('sortDirection');
        $sortDirection = ($sortDirectionInput === SORT_DIRECTION_DESC) ? SORT_DIRECTION_DESC : SORT_DIRECTION_ASC;

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if (!$ofrPlugin) {
            $request->redirect(null, 'index');
        }
        $mode = (int) $ofrPlugin->getSetting($journalId, 'mode');

        $ofrPlugin->import('classes.ObjectForReviewAssignment');
        $path = isset($args[0]) ? (string) $args[0] : '';
        $template = 'objectsForReviewAssignments.tpl';
        
        switch ($path) {
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
            $templateMgr->assign($param, htmlspecialchars((string) $request->getUserVar($param), ENT_QUOTES, 'UTF-8'));
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

        if ($path === '') {
            $rangeInfo = Handler::getRangeInfo('objectsForReview');
            /** @var ObjectForReviewDAO $ofrDao */
            $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
            $objectsForReview = $ofrDao->getAllByContextId($journalId, $searchField, $search, $searchMatch, $status, $editorId, $filterType, $rangeInfo, $sort, $sortDirection);
            $templateMgr->assign('objectsForReview', $objectsForReview);
        } else {
            $rangeInfo = Handler::getRangeInfo('objectForReviewAssignments');
            /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            $objectForReviewAssignments = $ofrAssignmentDao->getAllByContextId($journalId, $searchField, $search, $searchMatch, $status, null, $editorId, $filterType, $rangeInfo, $sort, $sortDirection);
            $templateMgr->assign('objectForReviewAssignments', $objectForReviewAssignments);
            $templateMgr->assign('counts', $ofrAssignmentDao->getStatusCounts($journalId));
        }

        $templateMgr->assign('pageTitle', $pageTitle);
        $templateMgr->display($ofrPlugin->getTemplatePath() . 'editor/' . $template);
    }

    /**
     * Edit and update object for review (plug-in) settings.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function objectsForReviewSettings($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if (!$ofrPlugin) {
            $request->redirect(null, 'index');
        }
        $ofrPlugin->import('classes.form.ObjectsForReviewSettingsForm');
        $settingsForm = new ObjectsForReviewSettingsForm($ofrPlugin, $journalId);
        
        $saveFlag = (bool) $request->getUserVar('save');
        if ($settingsForm->isLocaleResubmit() || $saveFlag) {
            $settingsForm->readInputData();
            
            if ($saveFlag) {
                if ($settingsForm->validate()) {
                    $settingsForm->execute();
                    
                    $user = $request->getUser();
                    if ($user) {
                        import('classes.notification.NotificationManager');
                        $notificationManager = new NotificationManager();
                        $notificationManager->createTrivialNotification((int) $user->getId(), NOTIFICATION_TYPE_OFR_SETTINGS_SAVED);
                    }

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
     * @param mixed $request
     * @return void
     */
    public function createObjectForReview($args, $request) {
        $this->editObjectForReview($args, $request);
    }

    /**
     * Create/edit object for review.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function editObjectForReview($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $objectId = isset($args[0]) ? (int) $args[0] : null;
        $reviewObjectTypeIdInput = $request->getUserVar('reviewObjectTypeId');
        $reviewObjectTypeId = $reviewObjectTypeIdInput !== null && $reviewObjectTypeIdInput !== '' ? (int) $reviewObjectTypeIdInput : null;

        if (!$this->_ensureObjectExists($objectId, $journalId, $reviewObjectTypeId) && $reviewObjectTypeId === null) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }
        
        /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
        $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
        if ($reviewObjectTypeId !== null && !$reviewObjectTypeDao->reviewObjectTypeExists($reviewObjectTypeId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageTitle', $objectId ? 'plugins.generic.objectsForReview.editor.edit' : 'plugins.generic.objectsForReview.editor.create');
        
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $ofrPlugin->import('classes.form.ObjectForReviewForm');
            $ofrForm = new ObjectForReviewForm($ofrPlugin->getName(), $objectId, $reviewObjectTypeId);
            $ofrForm->initData();
            $ofrForm->display($request);
        }
    }

    /**
     * Update object for review.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function updateObjectForReview($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $objectIdInput = $request->getUserVar('objectId');
        $objectId = $objectIdInput !== null && $objectIdInput !== '' ? (int) $objectIdInput : null;
        
        $reviewObjectTypeIdInput = $request->getUserVar('reviewObjectTypeId');
        $reviewObjectTypeId = $reviewObjectTypeIdInput !== null && $reviewObjectTypeIdInput !== '' ? (int) $reviewObjectTypeIdInput : null;

        if ($objectId !== null && !$this->_ensureObjectExists($objectId, $journalId, $reviewObjectTypeId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if (!$ofrPlugin) {
            $request->redirect(null, 'index');
        }
        $ofrPlugin->import('classes.form.ObjectForReviewForm');
        $ofrForm = new ObjectForReviewForm($ofrPlugin->getName(), $objectId, $reviewObjectTypeId);
        $ofrForm->readInputData();

        $editData = false;
        $addPersonFlag = (bool) $request->getUserVar('addPerson');
        $delPerson = $request->getUserVar('delPerson');
        $movePersonFlag = (bool) $request->getUserVar('movePerson');

        if ($addPersonFlag) {
            $editData = true;
            $persons = $ofrForm->getData('persons');
            if (is_array($persons)) {
                $persons[] = [];
                $ofrForm->setData('persons', $persons);
            }
        } elseif (is_array($delPerson) && count($delPerson) === 1) {
            $editData = true;
            $delPersonKey = array_keys($delPerson)[0];
            $delPersonId = (int) $delPersonKey; 
            $persons = $ofrForm->getData('persons');
            if (is_array($persons) && isset($persons[$delPersonId]['personId']) && !empty($persons[$delPersonId]['personId'])) {
                $deletedPersons = explode(':', (string) $ofrForm->getData('deletedPersons'));
                $deletedPersons[] = (string) $persons[$delPersonId]['personId'];
                $ofrForm->setData('deletedPersons', implode(':', $deletedPersons));
            }
            if (is_array($persons)) {
                array_splice($persons, $delPersonId, 1);
                $ofrForm->setData('persons', $persons);
            }
        } elseif ($movePersonFlag) {
            $editData = true;
            $movePersonDir = (string) $request->getUserVar('movePersonDir');
            $movePersonDir = ($movePersonDir === 'u') ? 'u' : 'd'; 
            
            $movePersonIndexInput = $request->getUserVar('movePersonIndex');
            $movePersonIndex = $movePersonIndexInput !== null && $movePersonIndexInput !== '' ? (int) $movePersonIndexInput : 0;
            $persons = $ofrForm->getData('persons');

            if (is_array($persons) && !(($movePersonDir === 'u' && $movePersonIndex <= 0) || ($movePersonDir === 'd' && $movePersonIndex >= count($persons) - 1))) {
                $tmpPerson = $persons[$movePersonIndex];
                if ($movePersonDir === 'u') {
                    $persons[$movePersonIndex] = $persons[$movePersonIndex - 1];
                    $persons[$movePersonIndex - 1] = $tmpPerson;
                } else {
                    $persons[$movePersonIndex] = $persons[$movePersonIndex + 1];
                    $persons[$movePersonIndex + 1] = $tmpPerson;
                }
                $ofrForm->setData('persons', $persons);
            }
        }

        if (!$editData && $ofrForm->validate()) {
            $ofrForm->execute();
            $notificationType = $objectId ? NOTIFICATION_TYPE_OFR_UPDATED : NOTIFICATION_TYPE_OFR_CREATED;
            $this->_createTrivialNotification($notificationType, $request);

            $createAnotherFlag = (bool) $request->getUserVar('createAnother');
            if ($createAnotherFlag) {
                $request->redirect(null, 'editor', 'createObjectForReview');
            } elseif ($addPersonFlag || is_array($delPerson) || $movePersonFlag) {
                $request->redirect(null, 'editor', 'editObjectForReview', $objectId, ['reviewObjectTypeId' => (string) $reviewObjectTypeId]);
            } else {
                $request->redirect(null, 'editor', 'objectsForReview');
            }
        } else {
            $this->setupTemplate($request, true);
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('pageTitle', $objectId ? 'plugins.generic.objectsForReview.editor.edit' : 'plugins.generic.objectsForReview.editor.create');
            $ofrForm->display($request);
        }
    }

    /**
     * Remove object for review cover page image.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function removeObjectForReviewCoverPage($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $objectId = isset($args[0]) ? (int) $args[0] : null;
        if (!$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }

        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($objectId, $journalId);
        
        if ($objectForReview) {
            $coverPageSetting = $objectForReview->getCoverPage();
            if (is_array($coverPageSetting) && isset($coverPageSetting['fileName'])) {
                import('classes.file.PublicFileManager');
                $publicFileManager = new PublicFileManager();
                $publicFileManager->removeJournalFile($journalId, (string) $coverPageSetting['fileName']);
                
                $ofrPlugin = $this->_getObjectsForReviewPlugin();
                if ($ofrPlugin) {
                    $ofrPlugin->import('classes.ReviewObjectMetadata');
                    $metadataId = $objectForReview->getMetadataId(REVIEW_OBJECT_METADATA_KEY_COVERPAGE);
                    if ($metadataId !== null) {
                        /** @var ObjectForReviewSettingsDAO $ofrSettingsDao */
                        $ofrSettingsDao = DAORegistry::getDAO('ObjectForReviewSettingsDAO');
                        $ofrSettingsDao->deleteSetting($objectId, $metadataId);
                    }
                }
            }
            $request->redirect(null, 'editor', 'editObjectForReview', $objectId, ['reviewObjectTypeId' => (string) $objectForReview->getReviewObjectTypeId()]);
        }
    }

    /**
     * Delete object for review.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function deleteObjectForReview($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $objectId = isset($args[0]) ? (int) $args[0] : null;
        if ($this->_ensureObjectExists($objectId, $journalId)) {
            /** @var ObjectForReviewDAO $ofrDao */
            $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
            $objectForReview = $ofrDao->getById($objectId, $journalId);
            if ($objectForReview) {
                $ofrDao->deleteObject($objectForReview);
                $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_DELETED, $request);
            }
        }
        $request->redirect(null, 'editor', 'objectsForReview');
    }

    /**
     * Display a list of authors from which to choose an object reviewer.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function selectObjectForReviewAuthor($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $objectId = isset($args[0]) ? (int) $args[0] : null;
        if (!$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }

        // Search
        $searchField = null;
        $searchMatch = null;
        $search = (string) $request->getUserVar('search');
        $searchInitial = (string) $request->getUserVar('searchInitial');
        
        if ($searchInitial !== '' && !preg_match('/^[A-Z0-9]$/i', $searchInitial)) {
            $searchInitial = '';
        }

        if ($search !== '') {
            // [WIZDAM FIX] Corrected undefined constants to valid user search fields
            $validSearchFields = [USER_FIELD_FIRSTNAME, USER_FIELD_LASTNAME, USER_FIELD_USERNAME, USER_FIELD_EMAIL];
            $searchFieldInput = (string) $request->getUserVar('searchField');
            if (in_array($searchFieldInput, $validSearchFields, true)) {
                $searchField = $searchFieldInput;
            }

            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatchInput = (string) $request->getUserVar('searchMatch');
            if (in_array($searchMatchInput, $validSearchMatches, true)) {
                $searchMatch = $searchMatchInput;
            } else {
                $searchMatch = 'contains';
            }
        } elseif ($searchInitial !== '') {
            $searchInitial = PKPString::strtoupper($searchInitial);
            $searchField = USER_FIELD_INITIAL;
            $search = $searchInitial;
        }
        
        $fieldOptions = [
            USER_FIELD_FIRSTNAME => 'user.firstName',
            USER_FIELD_LASTNAME => 'user.lastName',
            USER_FIELD_USERNAME => 'user.username',
            USER_FIELD_EMAIL => 'user.email'
        ];

        $rangeInfo = Handler::getRangeInfo('users');
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $users = $roleDao->getUsersByRoleId(ROLE_ID_AUTHOR, $journalId, $searchField, $search, $searchMatch, $rangeInfo);
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
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
        $templateMgr->assign('isJournalManager', Validation::isJournalManager((int) $journal->getId()));

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $templateMgr->display($ofrPlugin->getTemplatePath() . 'editor/authors.tpl');
        }
    }

    /**
     * Assign an object for review author.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function assignObjectForReviewAuthor($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $objectId = isset($args[0]) ? (int) $args[0] : null;
        if (!$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview');
        }
        
        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById($objectId, $journalId);

        $redirect = true;
        if ($objectForReview && (int) $objectForReview->getAvailable() === 1) {
            $userIdInput = $request->getUserVar('userId');
            $userId = $userIdInput !== null && $userIdInput !== '' ? (int) $userIdInput : null;
            
            /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            if ($userId !== null && $ofrAssignmentDao->assignmentExists($objectId, $userId)) {
                $request->redirect(null, 'editor', 'objectsForReview');
            }
            
            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            $user = $userDao->getById($userId);
            
            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            if ($user !== null && $roleDao->userHasRole($journalId, (int) $user->getId(), ROLE_ID_AUTHOR)) {
                $returnUrl = $request->url(null, 'editor', 'assignObjectForReviewAuthor', $objectId, ['userId' => (string) $user->getId()]);
                $redirect = $this->_assign(null, $objectForReview, $user, $returnUrl, $request);
            }
        }
        if ($redirect) {
            $request->redirect(null, 'editor', 'objectsForReview', 'assigned');
        }
    }

    /**
     * Accept an object for review author.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function acceptObjectForReviewAuthor($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $returnPage = $this->_getReturnpage($request);
        $assignmentId = isset($args[0]) ? (int) $args[0] : null;

        $redirect = true;
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $ofrPlugin->import('classes.ObjectForReviewAssignment');
        }
        
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId, OFR_STATUS_REQUESTED)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);
        
        if ($ofrAssignment) {
            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            $user = $userDao->getById((int) $ofrAssignment->getUserId());
            $returnUrl = $request->url(null, 'editor', 'acceptObjectForReviewAuthor', $assignmentId, ['returnPage' => $returnPage !== '' ? $returnPage : null]);
            
            if ($user) {
                $redirect = $this->_assign($ofrAssignment, $ofrAssignment->getObjectForReview(), $user, $returnUrl, $request);
            }
        }

        if ($redirect) {
            $finalReturnPage = ($returnPage !== '' && $returnPage !== 'all') ? 'assigned' : $returnPage;
            $request->redirect(null, 'editor', 'objectsForReview', $finalReturnPage !== '' ? $finalReturnPage : null);
        }
    }

    /**
     * Deny an object for review request.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function denyObjectForReviewAuthor($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $returnPage = $this->_getReturnpage($request);
        $assignmentId = isset($args[0]) ? (int) $args[0] : null;

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $ofrPlugin->import('classes.ObjectForReviewAssignment');
        }
        
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId, OFR_STATUS_REQUESTED)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);

        $redirect = true;
        if ($ofrAssignment) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('OFR_OBJECT_DENIED');
            
            $sendFlag = (bool) $request->getUserVar('send');
            
            if ($sendFlag && !$email->hasErrors()) {
                $ofrAssignmentDao->deleteById($assignmentId);
                $email->send();
                $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_AUTHOR_DENIED, $request);
            } else {
                $returnUrl = $request->url(null, 'editor', 'denyObjectForReviewAuthor', $assignmentId, ['returnPage' => $returnPage !== '' ? $returnPage : null]);
                $this->_displayEmailForm($email, $ofrAssignment->getObjectForReview(), $ofrAssignment->getUser(), $returnUrl, 'OFR_OBJECT_DENIED', $request);
                $redirect = false;
            }
        }
        
        if ($redirect) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }
    }

    /**
     * Mark an object for review assignment as mailed.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function notifyObjectForReviewMailed($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $returnPage = $this->_getReturnpage($request);
        $assignmentId = isset($args[0]) ? (int) $args[0] : null;

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $ofrPlugin->import('classes.ObjectForReviewAssignment');
        }
        
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId, OFR_STATUS_ASSIGNED)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);

        $redirect = true;
        if ($ofrAssignment && $ofrPlugin) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('OFR_OBJECT_MAILED');
            
            $sendFlag = (bool) $request->getUserVar('send');
            
            if ($sendFlag && !$email->hasErrors()) {
                $ofrAssignment->setStatus(OFR_STATUS_MAILED);
                $dueWeeks = (int) $ofrPlugin->getSetting($journalId, 'dueWeeks');
                $dueDateTimestamp = time() + ($dueWeeks * 7 * 24 * 60 * 60);
                $ofrAssignment->setDateDue(date('Y-m-d H:i:s', $dueDateTimestamp));
                $ofrAssignment->setDateMailed(Core::getCurrentDate());
                $ofrAssignmentDao->updateObject($ofrAssignment);
                $email->send();
                $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_AUTHOR_MAILED, $request);
            } else {
                $returnUrl = $request->url(null, 'editor', 'notifyObjectForReviewMailed', $assignmentId, ['returnPage' => $returnPage !== '' ? $returnPage : null]);
                $this->_displayEmailForm($email, $ofrAssignment->getObjectForReview(), $ofrAssignment->getUser(), $returnUrl, 'OFR_OBJECT_MAILED', $request);
                $redirect = false;
            }
        }
        
        if ($redirect) {
            $finalReturnPage = ($returnPage !== '' && $returnPage !== 'all') ? 'mailed' : $returnPage;
            $request->redirect(null, 'editor', 'objectsForReview', $finalReturnPage !== '' ? $finalReturnPage : null);
        }
    }

    /**
     * Remove object reviewer assignment.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function removeObjectForReviewAssignment($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $returnPage = $this->_getReturnpage($request);
        $assignmentId = isset($args[0]) ? (int) $args[0] : null;

        if (!$this->_ensureAssignmentExists($assignmentId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);

        $redirect = true;
        if ($ofrAssignment && $this->_canBeRemoved($ofrAssignment)) {
            import('classes.mail.MailTemplate');
            $email = new MailTemplate('OFR_REVIEWER_REMOVED');
            
            $sendFlag = (bool) $request->getUserVar('send');
            
            if ($sendFlag && !$email->hasErrors()) {
                $ofrAssignmentDao->deleteById($assignmentId);
                $email->send();
                $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_AUTHOR_REMOVED, $request);
            } else {
                $returnUrl = $request->url(null, 'editor', 'removeObjectForReviewAssignment', $assignmentId, ['returnPage' => $returnPage !== '' ? $returnPage : null]);
                $this->_displayEmailForm($email, $ofrAssignment->getObjectForReview(), $ofrAssignment->getUser(), $returnUrl, 'OFR_REVIEWER_REMOVED', $request);
                $redirect = false;
            }
        }
        
        if ($redirect) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }
    }

    /**
     * Display a list of submissions from which to choose an object review submission.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function selectObjectForReviewSubmission($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $returnPage = $this->_getReturnpage($request);
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if (!$ofrPlugin) {
            $request->redirect(null, 'index');
        }
        $mode = (int) $ofrPlugin->getSetting($journalId, 'mode');

        $assignmentId = isset($args[0]) ? (int) $args[0] : null;
        $objectId = null;
        
        if ($mode === OFR_MODE_FULL) {
            if (!$this->_ensureAssignmentExists($assignmentId, $journalId)) {
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
            }
            /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);
            if ($ofrAssignment) {
                $objectId = (int) $ofrAssignment->getObjectId();
            }
        } elseif ($mode === OFR_MODE_METADATA) {
            $objectIdInput = $request->getUserVar('objectId');
            $objectId = $objectIdInput !== null && $objectIdInput !== '' ? (int) $objectIdInput : null;
            if (!$this->_ensureObjectExists($objectId, $journalId)) {
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
            }
        }

        // Search
        $searchField = null;
        $searchMatch = null;
        $search = (string) $request->getUserVar('search');

        if ($search !== '') {
            // [WIZDAM FIX] Corrected undefined constants to valid submission search fields
            $validSearchFields = [SUBMISSION_FIELD_TITLE, SUBMISSION_FIELD_ID, SUBMISSION_FIELD_AUTHOR];
            $searchFieldInput = (string) $request->getUserVar('searchField');
            if (in_array($searchFieldInput, $validSearchFields, true)) {
                $searchField = $searchFieldInput;
            }

            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatchInput = (string) $request->getUserVar('searchMatch');
            if (in_array($searchMatchInput, $validSearchMatches, true)) {
                $searchMatch = $searchMatchInput;
            } else {
                $searchMatch = 'contains';
            }
        }
        
        import('classes.submission.common.Action');
        $fieldOptions = [
            SUBMISSION_FIELD_TITLE => 'article.title',
            SUBMISSION_FIELD_ID => 'article.submissionId',
            SUBMISSION_FIELD_AUTHOR => 'user.role.author',
        ];

        $user = $request->getUser();
        $editorId = $user ? (int) $user->getId() : null;
        $rangeInfo = Handler::getRangeInfo('submissions');
        
        /** @var EditorSubmissionDAO $editorSubmissionDao */
        $editorSubmissionDao = DAORegistry::getDAO('EditorSubmissionDAO');
        $submissions = $editorSubmissionDao->getEditorSubmissions(
            $journalId, 0, $editorId, $searchField, $searchMatch, $search, null, null, null, $rangeInfo, 'id', SORT_DIRECTION_DESC
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
        
        $templateMgr->display($ofrPlugin->getTemplatePath() . 'editor/submissions.tpl');
    }

    /**
     * Assign an object for review submission.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function assignObjectForReviewSubmission($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $returnPage = $this->_getReturnpage($request);
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if (!$ofrPlugin) {
            $request->redirect(null, 'index');
        }
        $mode = (int) $ofrPlugin->getSetting($journalId, 'mode');

        $assignmentId = isset($args[0]) ? (int) $args[0] : null;
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $insert = false;
        $ofrAssignment = null;
        
        if ($mode === OFR_MODE_FULL) {
            if (!$this->_ensureAssignmentExists($assignmentId, $journalId)) {
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
            }
            $ofrAssignment = $ofrAssignmentDao->getById($assignmentId);
        } elseif ($mode === OFR_MODE_METADATA) {
            $objectIdInput = $request->getUserVar('objectId');
            $objectId = $objectIdInput !== null && $objectIdInput !== '' ? (int) $objectIdInput : null;
            if (!$this->_ensureObjectExists($objectId, $journalId)) {
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
            }
            $ofrAssignment = $ofrAssignmentDao->newDataObject();
            $ofrAssignment->setObjectId($objectId);
            $insert = true;
        }

        $submissionIdInput = $request->getUserVar('submissionId');
        $submissionId = $submissionIdInput !== null && $submissionIdInput !== '' ? (int) $submissionIdInput : null;
        
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        if ($ofrAssignment !== null && $articleDao->getArticleJournalId($submissionId) === $journalId) {
            $ofrAssignment->setSubmissionId($submissionId);
            $ofrAssignment->setStatus(OFR_STATUS_SUBMITTED);
            if ($insert) {
                $ofrAssignmentDao->insertObject($ofrAssignment);
            } else {
                $ofrAssignmentDao->updateObject($ofrAssignment);
            }
            $this->_createTrivialNotification(NOTIFICATION_TYPE_OFR_SUBMISSION_ASSIGNED, $request);
        }

        $finalReturnPage = ($returnPage !== '' && $returnPage !== 'all') ? 'submitted' : $returnPage;
        $request->redirect(null, 'editor', 'objectsForReview', $finalReturnPage !== '' ? $finalReturnPage : null);
    }

    /**
     * Edit object for review assignment.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function editObjectForReviewAssignment($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $returnPage = $this->_getReturnpage($request);
        $assignmentId = isset($args[0]) ? (int) $args[0] : null;
        
        $objectIdInput = $request->getUserVar('objectId');
        $objectId = $objectIdInput !== null && $objectIdInput !== '' ? (int) $objectIdInput : null;
        
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId) || !$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId, $objectId);
        if ($ofrAssignment === null) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.edit');

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $ofrPlugin->import('classes.form.ObjectForReviewAssignmentForm');
            $ofrAssignmentForm = new ObjectForReviewAssignmentForm($ofrPlugin->getName(), $assignmentId, $objectId);
            $ofrAssignmentForm->initData();
            $mode = (int) $ofrPlugin->getSetting($journalId, 'mode');
            $templateMgr->assign('mode', $mode);
            $templateMgr->assign('returnPage', $returnPage);
            $ofrAssignmentForm->display($request);
        }
    }

    /**
     * Update object for review assignment.
     * @param array $args
     * @param mixed $request
     * @return void
     */
    public function updateObjectForReviewAssignment($args, $request) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        if (!$journal) {
            $request->redirect(null, 'index');
        }
        $journalId = (int) $journal->getId();

        $returnPage = $this->_getReturnpage($request);
        $assignmentIdInput = $request->getUserVar('assignmentId');
        $assignmentId = $assignmentIdInput !== null && $assignmentIdInput !== '' ? (int) $assignmentIdInput : null;
        
        $objectIdInput = $request->getUserVar('objectId');
        $objectId = $objectIdInput !== null && $objectIdInput !== '' ? (int) $objectIdInput : null;
        
        if (!$this->_ensureAssignmentExists($assignmentId, $journalId) || !$this->_ensureObjectExists($objectId, $journalId)) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }
        
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById($assignmentId, $objectId);
        if ($ofrAssignment === null) {
            $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
        }

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $ofrPlugin->import('classes.form.ObjectForReviewAssignmentForm');
            $ofrAssignmentForm = new ObjectForReviewAssignmentForm($ofrPlugin->getName(), $assignmentId, $objectId);
            $ofrAssignmentForm->readInputData();
            
            if ($ofrAssignmentForm->validate()) {
                $ofrAssignmentForm->execute();
                $request->redirect(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null);
            } else {
                $this->setupTemplate($request, true);
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign('pageTitle', 'plugins.generic.objectsForReview.editor.edit');
                $mode = (int) $ofrPlugin->getSetting($journalId, 'mode');
                $templateMgr->assign('mode', $mode);
                $templateMgr->assign('returnPage', $returnPage);
                $ofrAssignmentForm->display($request);
            }
        }
    }

    /**
     * Return valid landing/return pages.
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
     * @param mixed $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
        
        if (!$journal) {
            return false;
        }

        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if (!$ofrPlugin || !$ofrPlugin->getEnabled()) {
            return false;
        }

        if (!Validation::isEditor((int) $journal->getId())) {
            Validation::redirectLogin();
        }

        return parent::authorize($request, $args, $roleAssignments);
    }

    /**
     * Setup common template variables.
     * @param mixed $request
     * @param bool $subclass
     * @param int|null $objectId
     * @return void
     */
    public function setupTemplate($request = null, $subclass = false, $objectId = null) {
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        $router = $request->getRouter();
        $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;

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
            $returnPage = (string) $request->getUserVar('returnPage');
            if ($returnPage !== '') { 
                $validPages = $this->getValidReturnPages();
                if (!in_array($returnPage, $validPages, true)) {
                    $returnPage = '';
                }
            }
            $pageCrumbs[] = [
                $request->url(null, 'editor', 'objectsForReview', $returnPage !== '' ? $returnPage : null),
                __('plugins.generic.objectsForReview.displayName'),
                true
            ];
        }
        if ($objectId !== null) {
            /** @var ObjectForReviewDAO $ofrDao */
            $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
            $objectForReview = $ofrDao->getById((int) $objectId);
            if ($objectForReview) {
                /** @var ReviewObjectTypeDAO $reviewObjectTypeDao */
                $reviewObjectTypeDao = DAORegistry::getDAO('ReviewObjectTypeDAO');
                $reviewObjectType = $reviewObjectTypeDao->getById((int) $objectForReview->getReviewObjectTypeId());
                if ($reviewObjectType) {
                    $pageCrumbs[] = [
                        $request->url(null, 'editor', 'objectsForReview', (string) $objectId),
                        (string) $reviewObjectType->getLocalizedName(),
                        true
                    ];
                }
            }
        }
        $templateMgr->assign('pageHierarchy', $pageCrumbs);
        $ofrPlugin = $this->_getObjectsForReviewPlugin();
        if ($ofrPlugin) {
            $templateMgr->addStyleSheet($request->getBaseUrl() . '/' . $ofrPlugin->getStyleSheet());
        }
    }

    //
    // Private helper methods
    //

    /**
     * Get the objectForReview plugin object.
     * @return ObjectsForReviewPlugin|null
     */
    protected function _getObjectsForReviewPlugin() {
        return PluginRegistry::getPlugin('generic', OBJECTS_FOR_REVIEW_PLUGIN_NAME);
    }

    /**
     * Get return page.
     * @param mixed $request
     * @return string
     */
    protected function _getReturnpage($request) {
        $returnPage = (string) $request->getUserVar('returnPage');

        if ($returnPage !== '') {
            $validPages = $this->getValidReturnPages();
            if (!in_array($returnPage, $validPages, true)) {
                $returnPage = '';
            }
        }
        return $returnPage;
    }

    /**
     * Ensure object for review exists.
     * @param int|null $objectId
     * @param int $journalId
     * @param int|null $reviewObjectTypeId
     * @return bool
     */
    protected function _ensureObjectExists($objectId, $journalId, $reviewObjectTypeId = null) {
        if ($objectId === null || $objectId === '') {
            return false;
        }
        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        $objectForReview = $ofrDao->getById((int) $objectId, (int) $journalId);
        if ($objectForReview === null) {
            return false;
        }
        if ($reviewObjectTypeId !== null && (int) $objectForReview->getReviewObjectTypeId() !== (int) $reviewObjectTypeId) {
            return false;
        }
        return true;
    }

    /**
     * Ensure object for review assignment exists.
     * @param int|null $assignmentId
     * @param int $journalId
     * @param int|null $status
     * @return bool
     */
    protected function _ensureAssignmentExists($assignmentId, $journalId, $status = null) {
        if ($assignmentId === null || $assignmentId === '') {
            return false;
        }
        /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
        $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
        $ofrAssignment = $ofrAssignmentDao->getById((int) $assignmentId);
        if ($ofrAssignment === null) {
            return false;
        }
        if ($status !== null && (int) $ofrAssignment->getStatus() !== (int) $status) {
            return false;
        }
        return $this->_ensureObjectExists((int) $ofrAssignment->getObjectId(), (int) $journalId);
    }

    /**
     * Assign an author to an object for review.
     * @param ObjectForReviewAssignment|null $ofrAssignment
     * @param ObjectForReview $objectForReview
     * @param User $author
     * @param string $returnUrl
     * @param mixed $request
     * @return bool
     */
    protected function _assign($ofrAssignment, $objectForReview, $author, $returnUrl, $request) {
        import('classes.mail.MailTemplate');
        $email = new MailTemplate('OFR_OBJECT_ASSIGNED');
        $sendFlag = (bool) $request->getUserVar('send');

        if ($sendFlag && !$email->hasErrors()) {
            $ofrPlugin = $this->_getObjectsForReviewPlugin();
            $router = $request->getRouter();
            $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
            $journalId = $journal ? (int) $journal->getId() : 0;
            
            $dueWeeks = $ofrPlugin ? (int) $ofrPlugin->getSetting($journalId, 'dueWeeks') : 0;
            $dueDateTimestamp = time() + ($dueWeeks * 7 * 24 * 60 * 60);

            /** @var ObjectForReviewAssignmentDAO $ofrAssignmentDao */
            $ofrAssignmentDao = DAORegistry::getDAO('ObjectForReviewAssignmentDAO');
            if ($ofrAssignment === null) {
                $ofrAssignment = $ofrAssignmentDao->newDataObject();
                $ofrAssignment->setObjectId((int) $objectForReview->getId());
                $ofrAssignment->setUserId((int) $author->getId());
            }
            $ofrAssignment->setStatus(OFR_STATUS_ASSIGNED);
            $ofrAssignment->setDateAssigned(Core::getCurrentDate());
            $ofrAssignment->setDateDue(date('Y-m-d H:i:s', $dueDateTimestamp));
            
            if ($ofrAssignment->getId() === null) {
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
     * Is remove action allowed.
     * @param ObjectForReviewAssignment $ofrAssignment
     * @return bool
     */
    protected function _canBeRemoved($ofrAssignment) {
        $status = (int) $ofrAssignment->getStatus();
        return ($status === OFR_STATUS_ASSIGNED) || ($status === OFR_STATUS_MAILED) || ($status === OFR_STATUS_SUBMITTED);
    }

    /**
     * Display email form for the editor.
     * @param MailTemplate $email
     * @param ObjectForReview $objectForReview
     * @param User $user
     * @param string $returnUrl
     * @param string $action
     * @param mixed $request
     * @return void
     */
    protected function _displayEmailForm($email, $objectForReview, $user, $returnUrl, $action, $request) {
        $continuedFlag = (bool) $request->getUserVar('continued');
        $paramArray = [];

        if (!$continuedFlag) {
            $userFullName = (string) $user->getFullName();
            $userEmail = (string) $user->getEmail();
            $userMailingAddress = (string) $user->getMailingAddress();
            $userCountryCode = (string) $user->getCountry();
            
            if ($userMailingAddress === '') {
                $userMailingAddress = __('plugins.generic.objectsForReview.editor.noMailingAddress');
            } else {
                /** @var CountryDAO $countryDao */
                $countryDao = DAORegistry::getDAO('CountryDAO');
                $countries = $countryDao->getCountries();
                if (isset($countries[$userCountryCode])) {
                    $userMailingAddress .= "\n" . (string) $countries[$userCountryCode];
                }
            }

            $editor = $objectForReview->getEditor();
            if ($editor) {
                $editorFullName = (string) $editor->getFullName();
                $editorEmail = (string) $editor->getEmail();
                $editorContactSignature = (string) $editor->getContactSignature();
                
                if ($action === 'OFR_OBJECT_ASSIGNED') {
                    $router = $request->getRouter();
                    $journal = ($router instanceof PKPPageRouter) ? $router->getContext($request) : null;
                    $journalId = $journal ? (int) $journal->getId() : 0;
                    
                    $ofrPlugin = $this->_getObjectsForReviewPlugin();
                    $dueWeeks = $ofrPlugin ? (int) $ofrPlugin->getSetting($journalId, 'dueWeeks') : 0;
                    $dueDateTimestamp = time() + ($dueWeeks * 7 * 24 * 60 * 60);
                    
                    $paramArray = [
                        'authorName' => strip_tags($userFullName),
                        'authorMailingAddress' => PKPString::html2text($userMailingAddress),
                        'objectForReviewTitle' => '"' . strip_tags((string) $objectForReview->getTitle()) . '"',
                        'objectForReviewDueDate' => date('l, F j, Y', $dueDateTimestamp),
                        'userProfileUrl' => $request->url(null, 'user', 'profile'),
                        'submissionUrl' => $request->url(null, 'author', 'submit'),
                        'editorialContactSignature' => PKPString::html2text($editorContactSignature)
                    ];
                } elseif ($action === 'OFR_OBJECT_DENIED') {
                    $paramArray = [
                        'authorName' => strip_tags($userFullName),
                        'objectForReviewTitle' => '"' . strip_tags((string) $objectForReview->getTitle()) . '"',
                        'submissionUrl' => $request->url(null, 'author', 'submit'),
                        'editorialContactSignature' => PKPString::html2text($editorContactSignature)
                    ];
                } elseif ($action === 'OFR_OBJECT_MAILED') {
                    $paramArray = [
                        'authorName' => strip_tags($userFullName),
                        'authorMailingAddress' => PKPString::html2text($userMailingAddress),
                        'objectForReviewTitle' => '"' . strip_tags((string) $objectForReview->getTitle()) . '"',
                        'submissionUrl' => $request->url(null, 'author', 'submit'),
                        'editorialContactSignature' => PKPString::html2text($editorContactSignature)
                    ];
                } elseif ($action === 'OFR_REVIEWER_REMOVED') {
                    $paramArray = [
                        'authorName' => strip_tags($userFullName),
                        'objectForReviewTitle' => '"' . strip_tags((string) $objectForReview->getTitle()) . '"',
                        'editorialContactSignature' => PKPString::html2text($editorContactSignature)
                    ];
                }
                $email->addRecipient($userEmail, $userFullName);
                $email->setFrom($editorEmail, $editorFullName);
                $email->assignParams($paramArray);
            }
        }
        $email->displayEditForm($returnUrl);
    }

    /**
     * Create trivial notification.
     * @param int $notificationType
     * @param mixed $request
     * @return void
     */
    protected function _createTrivialNotification($notificationType, $request) {
        $user = $request->getUser();
        if ($user) {
            import('classes.notification.NotificationManager');
            $notificationManager = new NotificationManager();
            $notificationManager->createTrivialNotification((int) $user->getId(), (int) $notificationType);
        }
    }

}
?>
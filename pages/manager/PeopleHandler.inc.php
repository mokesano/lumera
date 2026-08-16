<?php
declare(strict_types=1);

/**
 * @file pages/manager/PeopleHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PeopleHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for people management functions.
 */

import('pages.manager.ManagerHandler');

class PeopleHandler extends ManagerHandler {
    
    /**
     * Constructor.
     **/
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function PeopleHandler() {
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
     * Display list of people in the selected role.
     * @param array $args first parameter is the role ID to display
     */
    public function people($args) {
        $this->validate();
        $this->setupTemplate(true);

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');

        $roleSymbolicInput = trim((string) Request::getUserVar('roleSymbolic'));
        if ($roleSymbolicInput != '') {
            $roleSymbolic = $roleSymbolicInput;
        } else {
            $roleSymbolic = isset($args[0]) ? trim((string)$args[0]) : 'all';
        }

        $sort = trim((string) Request::getUserVar('sort'));
        $sort = $sort != '' ? $sort : 'name';
        
        $sortDirection = strtoupper(trim((string) Request::getUserVar('sortDirection')));
        $sortDirection = in_array($sortDirection, [SORT_DIRECTION_ASC, SORT_DIRECTION_DESC]) ? $sortDirection : SORT_DIRECTION_ASC;

        $roleId = 0;
        $roleName = 'manager.people.allUsers';
        $matches = [];
        if ($roleSymbolic != 'all' && PKPString::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
            $path = (string) ($matches[1] ?? '');
            if ($path === '') {
                Request::redirect(null, null, null, 'all');
            }

            $checkRoleId = $roleDao->getRoleIdFromPath($path);
            
            if ($checkRoleId == null) {
                Request::redirect(null, null, null, 'all');
            }
            
            $roleId = $checkRoleId;
            $roleName = $roleDao->getRoleName($roleId, true);
        }

        $journal = Request::getJournal();
        $templateMgr = TemplateManager::getManager();

        $searchType = null;
        $searchMatch = null;
        $search = trim((string) Request::getUserVar('search'));
        $searchInitial = trim((string) Request::getUserVar('searchInitial'));
        
        if (!preg_match('/^[A-Z0-9]$/i', $searchInitial)) { 
            $searchInitial = '';
        }
        
        if (!empty($search)) {
            $validSearchFields = [
                USER_FIELD_FIRSTNAME, USER_FIELD_LASTNAME, USER_FIELD_USERNAME,
                USER_FIELD_EMAIL, USER_FIELD_INTERESTS, USER_FIELD_AFFILIATION
            ];
            
            $searchType = (string) Request::getUserVar('searchField');
            if (!in_array($searchType, $validSearchFields)) {
                $searchType = null; 
            }
            
            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatch = trim((string) Request::getUserVar('searchMatch'));
            if (!in_array($searchMatch, $validSearchMatches)) {
                $searchMatch = 'contains'; 
            }
        
        } elseif (!empty($searchInitial)) {
            $searchInitial = PKPString::strtoupper($searchInitial);
            $searchType = USER_FIELD_INITIAL;
            $search = $searchInitial;
        }
        
        $rangeInfo = $this->getRangeInfo('users');

        if ($roleId) {
            $users = $roleDao->getUsersByRoleId($roleId, $journal->getId(), $searchType, $search, $searchMatch, $rangeInfo, $sort, $sortDirection);
            $templateMgr->assign('roleId', $roleId);
            switch($roleId) {
                case ROLE_ID_JOURNAL_MANAGER:
                    $helpTopicId = 'journal.roles.journalManager';
                    break;
                case ROLE_ID_EDITOR:
                    $helpTopicId = 'journal.roles.editor';
                    break;
                case ROLE_ID_SECTION_EDITOR:
                    $helpTopicId = 'journal.roles.sectionEditor';
                    break;
                case ROLE_ID_LAYOUT_EDITOR:
                    $helpTopicId = 'journal.roles.layoutEditor';
                    break;
                case ROLE_ID_REVIEWER:
                    $helpTopicId = 'journal.roles.reviewer';
                    break;
                case ROLE_ID_COPYEDITOR:
                    $helpTopicId = 'journal.roles.copyeditor';
                    break;
                case ROLE_ID_PROOFREADER:
                    $helpTopicId = 'journal.roles.proofreader';
                    break;
                case ROLE_ID_AUTHOR:
                    $helpTopicId = 'journal.roles.author';
                    break;
                case ROLE_ID_READER:
                    $helpTopicId = 'journal.roles.reader';
                    break;
                case ROLE_ID_SUBSCRIPTION_MANAGER:
                    $helpTopicId = 'journal.roles.subscriptionManager';
                    break;
                default:
                    $helpTopicId = 'journal.roles.index';
                    break;
            }
        } else {
            $users = $roleDao->getUsersByJournalId($journal->getId(), $searchType, $search, $searchMatch, $rangeInfo, $sort, $sortDirection);
            $helpTopicId = 'journal.users.allUsers';
        }

        $templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
        $templateMgr->assign('roleName', $roleName);
        $templateMgr->assign('users', $users);
        $templateMgr->assign('thisUser', Request::getUser());
        $templateMgr->assign('isReviewer', $roleId == ROLE_ID_REVIEWER);

        $templateMgr->assign('searchField', $searchType);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $search);
        $templateMgr->assign('searchInitial', htmlspecialchars(trim((string)Request::getUserVar('searchInitial')), ENT_QUOTES, 'UTF-8'));
        $templateMgr->assign('statistics', []);

        $roleSettings = $this->retrieveRoleAssignmentPreferences($journal->getId());
        $templateMgr->assign('roleSettings', is_array($roleSettings) ? $roleSettings : []);

        if ($roleId == ROLE_ID_REVIEWER) {
            /** @var ReviewAssignmentDAO $reviewAssignmentDao */
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            $templateMgr->assign('rateReviewerOnQuality', $journal->getSetting('rateReviewerOnQuality'));
            $templateMgr->assign('qualityRatings', $journal->getSetting('rateReviewerOnQuality') ? $reviewAssignmentDao->getAverageQualityRatings($journal->getId()) : null);
        }
        $templateMgr->assign('helpTopicId', $helpTopicId);
        $fieldOptions = [
            USER_FIELD_FIRSTNAME => 'user.firstName',
            USER_FIELD_LASTNAME => 'user.lastName',
            USER_FIELD_USERNAME => 'user.username',
            USER_FIELD_INTERESTS => 'user.interests',
            USER_FIELD_EMAIL => 'user.email'
        ];
        if ($roleId == ROLE_ID_REVIEWER) $fieldOptions = array_merge([USER_FIELD_INTERESTS => 'user.interests'], $fieldOptions);
        
        $templateMgr->assign('fieldOptions', $fieldOptions);
        $templateMgr->assign('rolePath', $roleDao->getRolePath($roleId));
        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
        $templateMgr->assign('roleSymbolic', $roleSymbolic);
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);

        $session = Request::getSession();
        $session->setSessionVar('enrolmentReferrer', Request::getRequestedArgs());

        $templateMgr->display('manager/people/enrollment.tpl');
    }

    /**
     * Search for users to enroll in a specific role.
     * @param array $args first parameter is the selected role ID
     */
    public function enrollSearch($args) {
        $this->validate();

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $roleId = (int) (isset($args[0]) ? trim($args[0]) : trim((string)Request::getUserVar('roleId')));
        $journal = $journalDao->getJournalByPath(Request::getRequestedJournalPath());

        $sort = trim((string)Request::getUserVar('sort'));
        $sort = $sort != '' ? $sort : 'name';
        
        $sortDirection = strtoupper(trim((string)Request::getUserVar('sortDirection')));
        $sortDirection = in_array($sortDirection, [SORT_DIRECTION_ASC, SORT_DIRECTION_DESC]) ? $sortDirection : SORT_DIRECTION_ASC;

        $templateMgr = TemplateManager::getManager();
        $this->setupTemplate(true);

        $searchType = null;
        $searchMatch = null;
        $search = trim((string)Request::getUserVar('search'));
        $searchInitial = trim((string)Request::getUserVar('searchInitial'));
        
        if (!preg_match('/^[A-Z0-9]$/i', $searchInitial)) { 
            $searchInitial = '';
        }
        
        if (!empty($search)) {
            $validSearchFields = [
                USER_FIELD_FIRSTNAME, USER_FIELD_LASTNAME, USER_FIELD_USERNAME,
                USER_FIELD_EMAIL, USER_FIELD_INTERESTS, USER_FIELD_AFFILIATION
            ];
            
            $searchType = (string) Request::getUserVar('searchField');
            if (!in_array($searchType, $validSearchFields)) {
                $searchType = null; 
            }
            
            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatch = trim((string) Request::getUserVar('searchMatch'));
            if (!in_array($searchMatch, $validSearchMatches)) {
                $searchMatch = 'contains'; 
            }
        
        } elseif (!empty($searchInitial)) {
            $searchInitial = PKPString::strtoupper($searchInitial);
            $searchType = USER_FIELD_INITIAL;
            $search = $searchInitial;
        }

        $rangeInfo = $this->getRangeInfo('users');
        $users = $userDao->getUsersByField($searchType, $searchMatch, $search, true, $rangeInfo, $sort);

        $templateMgr->assign('searchField', $searchType);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $search);
        $templateMgr->assign('searchInitial', htmlspecialchars(trim((string)Request::getUserVar('searchInitial')), ENT_QUOTES, 'UTF-8'));
        $templateMgr->assign('statistics', []);

        $roleSettings = $this->retrieveRoleAssignmentPreferences($journal->getId());
        $templateMgr->assign('roleSettings', is_array($roleSettings) ? $roleSettings : []);

        $templateMgr->assign('roleId', $roleId);
        $templateMgr->assign('roleName', $roleDao->getRoleName($roleId));
        $fieldOptions = [
            USER_FIELD_FIRSTNAME => 'user.firstName',
            USER_FIELD_LASTNAME => 'user.lastName',
            USER_FIELD_USERNAME => 'user.username',
            USER_FIELD_EMAIL => 'user.email'
        ];
        if ($roleId == ROLE_ID_REVIEWER) $fieldOptions = array_merge([USER_FIELD_INTERESTS => 'user.interests'], $fieldOptions);
        
        $templateMgr->assign('fieldOptions', $fieldOptions);
        $templateMgr->assign('users', $users);
        $templateMgr->assign('thisUser', Request::getUser());
        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
        $templateMgr->assign('helpTopicId', 'journal.users.index');
        $templateMgr->assign('sort', $sort);

        $session = Request::getSession();
        $referrerUrl = $session->getSessionVar('enrolmentReferrer');
        $templateMgr->assign('enrolmentReferrerUrl', isset($referrerUrl) ? Request::url(null,'manager','people',$referrerUrl) : Request::url(null,'manager'));
        $session->unsetSessionVar('enrolmentReferrer');

        $templateMgr->display('manager/people/searchUsers.tpl');
    }

    /**
     * Show users with no role.
     */
    public function showNoRole() {
        $this->validate();
        $this->setupTemplate(true);

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $journal = Request::getJournal();
        $journalId = $journal ? $journal->getId() : 0;

        $templateMgr = TemplateManager::getManager();
        $rangeInfo = $this->getRangeInfo('users');
        $users = $userDao->getUsersWithNoRole(true, $rangeInfo);

        $roleId = 0; 
        $templateMgr->assign('roleId', $roleId);

        $templateMgr->assign('omitSearch', true);
        $templateMgr->assign('users', $users);
        $templateMgr->assign('thisUser', Request::getUser());
        $templateMgr->assign('helpTopicId', 'journal.users.index');
        $templateMgr->assign('statistics', []);
        $templateMgr->assign('roleId', 0);

        $roleSettings = $this->retrieveRoleAssignmentPreferences($journal ? $journal->getId() : 0);
        $templateMgr->assign('roleSettings', is_array($roleSettings) ? $roleSettings : []);
        
        $templateMgr->assign('fieldOptions', [
            USER_FIELD_FIRSTNAME => 'user.firstName',
            USER_FIELD_LASTNAME => 'user.lastName',
            USER_FIELD_USERNAME => 'user.username',
            USER_FIELD_EMAIL => 'user.email'
        ]);
        
        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
        $templateMgr->assign('search', '');
        $templateMgr->assign('searchInitial', '');
        $templateMgr->assign('searchField', null);
        $templateMgr->assign('searchMatch', 'contains');
        $templateMgr->assign('sort', 'name');
        $templateMgr->assign('sortDirection', SORT_DIRECTION_ASC);

        $templateMgr->display('manager/people/searchUsers.tpl');
    }

    /**
     * Enroll a user in a role.
     * @param mixed $args
     */
    public function enroll($args) {
        $this->validate();
        $roleId = (int)(isset($args[0]) ? $args[0] : Request::getUserVar('roleId'));
        $userId = (int) Request::getUserVar('userId');
        $users = array_map('intval', (array) Request::getUserVar('users'));
        
        if (empty($users) && $userId != 0) {
            $users = [$userId];
        }

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getJournalByPath(Request::getRequestedJournalPath());
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $rolePath = $roleDao->getRolePath($roleId);

        if (!empty($users) && is_array($users) && $rolePath != '' && $rolePath != 'admin') {
            foreach ($users as $uId) {
                // Ensure ID is valid integer
                if ($uId > 0 && !$roleDao->userHasRole($journal->getId(), $uId, $roleId)) {
                    $role = new Role();
                    $role->setJournalId($journal->getId());
                    $role->setUserId($uId);
                    $role->setRoleId($roleId);

                    $roleDao->insertRole($role);
                }
            }
        }

        Request::redirect(null, null, 'people', (empty($rolePath) ? null : $rolePath . 's'));
    }

    /**
     * Unenroll a user from a role.
     * @param mixed $args
     */
    public function unEnroll($args) {
        $roleId = (int) array_shift($args);
        $journalId = (int) Request::getUserVar('journalId');
        $userId = (int) Request::getUserVar('userId');

        $this->validate();

        $journal = Request::getJournal();
        if ($roleId != ROLE_ID_SITE_ADMIN && (Validation::isSiteAdmin() || $journalId = $journal->getId())) {
            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roleDao->deleteRoleByUserId($userId, $journalId, $roleId);
        }

        Request::redirect(null, null, 'people', RoleDao::getRolePath($roleId) . 's');
    }

    /**
     * Show form to synchronize user enrollment with another journal.
     * @param mixed $args
     */
    public function enrollSyncSelect($args) {
        $this->validate();
        $this->setupTemplate(true);

        $rolePath = isset($args[0]) ? (string)$args[0] : '';
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roleId = $roleDao->getRoleIdFromPath($rolePath);
        if ($roleId) {
            $roleName = $roleDao->getRoleName($roleId, true);
        } else {
            $rolePath = '';
            $roleName = '';
        }

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journalTitles = $journalDao->getJournalTitles();

        $journal = Request::getJournal();
        unset($journalTitles[$journal->getId()]);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('rolePath', $rolePath);
        $templateMgr->assign('roleName', $roleName);
        $templateMgr->assign('journalOptions', $journalTitles);

        $templateMgr->display('manager/people/enrollSync.tpl');
    }

    /**
     * Synchronize user enrollment with another journal.
     */
    public function enrollSync($args) {
        $this->validate();

        $journal = Request::getJournal();
        $rolePath = trim((string)Request::getUserVar('rolePath'));
        $syncJournalInput = Request::getUserVar('syncJournal');
        $syncJournal = $syncJournalInput === 'all' ? 'all' : (int)$syncJournalInput;

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roleId = $roleDao->getRoleIdFromPath($rolePath);

        if ((!empty($roleId) || $rolePath == 'all') && !empty($syncJournal)) {
            $roles = $roleDao->getRolesByJournalId($syncJournal == 'all' ? null : $syncJournal, $roleId);
            while (!$roles->eof()) {
                $role = $roles->next();
                $role->setJournalId($journal->getId());
                if ($role->getRolePath() != 'admin' && !$roleDao->userHasRole($role->getJournalId(), $role->getUserId(), $role->getRoleId())) {
                    $roleDao->insertRole($role);
                }
            }
        }

        Request::redirect(null, null, 'people', $roleDao->getRolePath($roleId));
    }

    /**
     * Display form to create a new user.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createUser($args, &$request) {
        $this->editUser($args, $request);
    }

    /**
     * Get a suggested username, making sure it's not
     * already used by the system. (Poor-man's AJAX.)
     */
    public function suggestUsername() {
        $this->validate();
    
        $firstName = trim((string)Request::getUserVar('firstName'));
        $lastName = trim((string)Request::getUserVar('lastName'));
    
        $suggestion = Validation::suggestUsername($firstName, $lastName);
    
        echo htmlspecialchars($suggestion, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Display form to create/edit a user profile.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editUser($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        $journal = Request::getJournal();

        $userId = isset($args[0]) ? (int)$args[0] : null;

        $templateMgr = TemplateManager::getManager();

        if ($userId !== null && !Validation::canAdminister($journal->getId(), $userId)) {
            // We don't have administrative rights over this user.
            $templateMgr->assign('pageTitle', 'manager.people');
            $templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
            $templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
            $templateMgr->assign('backLinkLabel', 'manager.people.allUsers');

            return $templateMgr->display('common/error.tpl');
        }

        import('classes.manager.form.UserManagementForm');
        $templateMgr->assign('roleSettings', $this->retrieveRoleAssignmentPreferences($journal->getId()));
        $templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
        $userForm = new UserManagementForm($userId);

        if ($userForm->isLocaleResubmit()) {
            $userForm->readInputData();
        } else {
            $userForm->initData();
        }
        $userForm->display();
    }

    /**
     * Allow the Journal Manager to merge user accounts.
     * @param array $args
     */
    public function mergeUsers($args) {
        $this->validate();
        $this->setupTemplate(true);

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = Request::getJournal();
        $journalId = $journal->getId();
        $templateMgr = TemplateManager::getManager();
        $oldUserIds = array_map('intval', (array) Request::getUserVar('oldUserIds'));
        $newUserId = (int) Request::getUserVar('newUserId');

        $canAdministerAll = true;
        foreach ($oldUserIds as $oldUserId) {
            if (!Validation::canAdminister($journalId, $oldUserId)) {
                $canAdministerAll = false;
            }
        }

        if (
            (!empty($oldUserIds) && !$canAdministerAll) ||
            (!empty($newUserId) && !Validation::canAdminister($journalId, $newUserId))
        ) {
            $templateMgr->assign('pageTitle', 'manager.people');
            $templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
            $templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
            $templateMgr->assign('backLinkLabel', 'manager.people.allUsers');

            return $templateMgr->display('common/error.tpl');
        }

        if (!empty($oldUserIds) && !empty($newUserId)) {
            import('classes.user.UserAction');
            $userAction = new UserAction();
            
            foreach ($oldUserIds as $oldUserId) {
                $userAction->mergeUsers($oldUserId, $newUserId);
            }
            Request::redirect(null, 'manager');
        }

        $roleSymbolicInput = trim((string)Request::getUserVar('roleSymbolic'));
        if (!empty($roleSymbolicInput)) {
            $roleSymbolic = $roleSymbolicInput;
        } else {
            $roleSymbolic = isset($args[0]) ? trim((string)$args[0]) : 'all';
        }

        $roleId = 0;
        $roleName = 'manager.people.allUsers';

        $matches = [];
        if ($roleSymbolic != 'all' && PKPString::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
            $path = (string) ($matches[1] ?? '');
            
            if ($path === '') {
                Request::redirect(null, null, null, 'all');
            }
            
            $checkRoleId = $roleDao->getRoleIdFromPath($path);
            if ($checkRoleId == null) {
                Request::redirect(null, null, null, 'all');
            }
            $roleId = $checkRoleId;
            $roleName = $roleDao->getRoleName($roleId, true);
        }

        $sort = trim((string)Request::getUserVar('sort'));
        $sort = $sort != '' ? $sort : 'name';
        $sortDirection = strtoupper(trim((string)Request::getUserVar('sortDirection')));
        $sortDirection = in_array($sortDirection, [SORT_DIRECTION_ASC, SORT_DIRECTION_DESC]) ? $sortDirection : SORT_DIRECTION_ASC;

        $searchType = null;
        $searchMatch = null;
        $search = trim((string)Request::getUserVar('search'));
        $searchInitial = trim((string)Request::getUserVar('searchInitial'));
        if (!preg_match('/^[A-Z0-9]$/i', $searchInitial)) {
            $searchInitial = '';
        }

        if (!empty($search)) {
            $validSearchFields = [
                USER_FIELD_FIRSTNAME, USER_FIELD_LASTNAME, USER_FIELD_USERNAME,
                USER_FIELD_EMAIL, USER_FIELD_INTERESTS, USER_FIELD_AFFILIATION
            ];
            $searchType = (string)Request::getUserVar('searchField');
            if (!in_array($searchType, $validSearchFields)) {
                $searchType = null; 
            }

            $validSearchMatches = ['is', 'contains', 'startsWith'];
            $searchMatch = trim((string)Request::getUserVar('searchMatch'));
            if (!in_array($searchMatch, $validSearchMatches)) {
                $searchMatch = 'contains'; 
            }

        } else if (!empty($searchInitial)) {
            $searchInitial = PKPString::strtoupper($searchInitial);
            $searchType = USER_FIELD_INITIAL;
            $search = $searchInitial;
        }

        $rangeInfo = $this->getRangeInfo('users');

        if ($roleId) {
            $users = $roleDao->getUsersByRoleId($roleId, $journalId, $searchType, $search, $searchMatch, $rangeInfo, $sort);
            $templateMgr->assign('roleId', $roleId);
        } else {
            $users = $roleDao->getUsersByJournalId($journalId, $searchType, $search, $searchMatch, $rangeInfo, $sort);
        }

        $templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
        $templateMgr->assign('helpTopicId', 'journal.managementPages.mergeUsers');
        $templateMgr->assign('roleName', $roleName);
        $templateMgr->assign('users', $users);
        $templateMgr->assign('thisUser', Request::getUser());
        $templateMgr->assign('isReviewer', $roleId == ROLE_ID_REVIEWER);

        $templateMgr->assign('searchField', $searchType);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $search);
        $templateMgr->assign('searchInitial', htmlspecialchars(trim((string)Request::getUserVar('searchInitial')), ENT_QUOTES, 'UTF-8'));

        if ($roleId == ROLE_ID_REVIEWER) {
            /** @var ReviewAssignmentDAO $reviewAssignmentDao */
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            $templateMgr->assign('rateReviewerOnQuality', $journal->getSetting('rateReviewerOnQuality'));
            $templateMgr->assign('qualityRatings', $journal->getSetting('rateReviewerOnQuality') ? $reviewAssignmentDao->getAverageQualityRatings($journalId) : null);
        }

        $templateMgr->assign('statistics', []);

        $roleSettings = $this->retrieveRoleAssignmentPreferences($journal->getId());
        $templateMgr->assign('roleSettings', is_array($roleSettings) ? $roleSettings : []);

        $templateMgr->assign('fieldOptions', [
            USER_FIELD_FIRSTNAME => 'user.firstName',
            USER_FIELD_LASTNAME => 'user.lastName',
            USER_FIELD_USERNAME => 'user.username',
            USER_FIELD_EMAIL => 'user.email',
            USER_FIELD_INTERESTS => 'user.interests'
        ]);
        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
        $templateMgr->assign('oldUserIds', $oldUserIds);
        $templateMgr->assign('rolePath', $roleDao->getRolePath($roleId));
        $templateMgr->assign('roleSymbolic', $roleSymbolic);
        $templateMgr->assign('sort', $sort);
        $templateMgr->assign('sortDirection', $sortDirection);

        $templateMgr->display('manager/people/selectMergeUser.tpl');
    }

    /**
     * Disable a user's account.
     * @param array $args the ID of the user to disable
     */
    public function disableUser($args) {
        $this->validate();
        $this->setupTemplate(true);

        $userId = (int) (isset($args[0]) ? trim($args[0]) : trim((string)Request::getUserVar('userId')));

        $user = Request::getUser();
        $journal = Request::getJournal();

        if ($userId != null && $userId != $user->getId()) {

            if (!Validation::canAdminister($journal->getId(), $userId)) {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('pageTitle', 'manager.people');
                $templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
                $templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
                $templateMgr->assign('backLinkLabel', 'manager.people.allUsers');

                return $templateMgr->display('common/error.tpl');
            }
            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            $userTarget = $userDao->getById($userId);

            if ($userTarget) {
                $userTarget->setDisabled(1);
                $reason = htmlspecialchars(trim((string) Request::getUserVar('reason')), ENT_QUOTES, 'UTF-8');
                $userTarget->setDisabledReason($reason);
                $userDao->updateObject($userTarget);
            }
        }

        Request::redirect(null, null, 'people', 'all');
    }

    /**
     * Enable a user's account.
     * @param array $args the ID of the user to enable
     */
    public function enableUser($args) {
        $this->validate();
        $this->setupTemplate(true);

        $userId = isset($args[0]) ? (int)$args[0] : null;
        $user = Request::getUser();

        if ($userId != null && $userId != $user->getId()) {
            /** @var UserDAO $userDao */
            $userDao = DAORegistry::getDAO('UserDAO');
            $userTarget = $userDao->getById($userId, true);

            if ($userTarget) {
                $wasUnvalidated = ($userTarget->getDateValidated() === null);

                $userTarget->setDisabled(0);

                if ($wasUnvalidated) {
                    $userTarget->setDateValidated(Core::getCurrentDate());
                }

                $userTarget->setDisabledReason('');
                $userDao->updateObject($userTarget);

                if ($wasUnvalidated) {
                    $this->_sendValidationConfirmedEmail($userTarget, $user);
                }
            }
        }

        Request::redirect(null, null, 'people', 'all');
    }

    /**
     * [WIZDAM] Kirim notifikasi "akun Anda telah diaktifkan" ke pengguna
     * yang baru saja divalidasi lewat aksi Enable oleh Journal Manager.
     * @param User $userTarget Pengguna yang diaktifkan.
     * @param User $actingUser Journal Manager yang melakukan aksi Enable --
     * penerima notifikasi kalau pengiriman gagal, supaya mereka tahu
     * SEKETIKA di halaman yang sama, bukan lewat log server.
     */
    protected function _sendValidationConfirmedEmail($userTarget, $actingUser = null) {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        import('classes.mail.MailTemplate');
        $mail = new MailTemplate('USER_VALIDATE_CONFIRMED');
        if ($journal) {
            $mail->setFrom($journal->getSetting('contactEmail'), $journal->getSetting('contactName'));
        }

        $mail->assignParams([
            'userFullName' => $userTarget->getFullName(),
            'username'     => $userTarget->getUsername(),
            'userEmail'    => $userTarget->getEmail(),
            'loginUrl'     => $request->url($journal ? $journal->getPath() : null, 'login'),
        ]);
        $mail->addRecipient($userTarget->getEmail(), $userTarget->getFullName());

        if (!$mail->send()) {
            // Log teknis tetap dipertahankan untuk audit developer.
            error_log(sprintf(
                '[WIZDAM] enableUser: gagal mengirim USER_VALIDATE_CONFIRMED ke %s (user ID %d).',
                $userTarget->getEmail(), $userTarget->getId()
            ));

            if ($actingUser) {
                import('classes.notification.NotificationManager');
                $notificationManager = new NotificationManager();
                $notificationManager->createTrivialNotification(
                    (int) $actingUser->getId(),
                    NOTIFICATION_TYPE_ERROR,
                    ['contents' => __('notification.email.validateConfirmedFailed', ['userEmail' => $userTarget->getEmail()])]
                );
            }
        }
    }

    /**
     * Remove a user from all roles for the current journal.
     * @param array $args the ID of the user to remove
     */
    public function removeUser($args) {
        $this->validate();
        $this->setupTemplate(true);

        $userId = isset($args[0]) ? (int)$args[0] : null;
        $user = Request::getUser();
        $journal = Request::getJournal();

        if ($userId != null && $userId != $user->getId()) {
            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roleDao->deleteRoleByUserId($userId, $journal->getId());
        }

        Request::redirect(null, null, 'people', 'all');
    }

    /**
     * Save changes to a user profile.
     * @param mixed $args
     * @param mixed $request
     */
    public function updateUser($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        $journal = $request->getJournal();
        $userId = (int) $request->getUserVar('userId');

        if (!empty($userId) && !Validation::canAdminister($journal->getId(), $userId)) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('pageTitle', 'manager.people');
            $templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
            $templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
            $templateMgr->assign('backLinkLabel', 'manager.people.allUsers');

            return $templateMgr->display('common/error.tpl');
        }

        import('classes.manager.form.UserManagementForm');
        $userForm = new UserManagementForm($userId);

        $userForm->readInputData();
        if ($userForm->validate()) {
            $userForm->execute();

            if ((int) $request->getUserVar('createAnother')) {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('currentUrl', $request->url(null, null, 'people', 'all'));
                $templateMgr->assign('userCreated', true);
                unset($userForm);
                $userForm = new UserManagementForm();
                $userForm->initData();
                $userForm->display();

            } else {
                $source = trim((string)$request->getUserVar('source'));
                
                if (!empty($source) && Request::isPathValid($source)) {
                    $request->redirectUrl($source);
                } else {
                    $request->redirect(null, null, 'people', 'all');
                }
            }
        } else {
            $userForm->display();
        }
    }

    /**
     * Display a user's profile.
     * @param array $args first parameter is the ID or username of the user to display
     */
    public function userProfile($args) {
        $this->validate();
        $this->setupTemplate(true);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
        $templateMgr->assign('helpTopicId', 'journal.users.index');

        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $userId = isset($args[0]) ? $args[0] : 0;
        if (is_numeric($userId)) {
            $userId = (int) $userId;
            $user = $userDao->getById($userId);
        } else {
            $user = $userDao->getByUsername((string) $userId);
        }

        if ($user == null) {
            $templateMgr->assign('pageTitle', 'manager.people');
            $templateMgr->assign('errorMsg', 'manager.people.invalidUser');
            $templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
            $templateMgr->assign('backLinkLabel', 'manager.people.allUsers');

            $templateMgr->display('common/error.tpl');
        } else {
            $site = Request::getSite();
            $journal = Request::getJournal();

            $isSiteAdmin = Validation::isSiteAdmin();
            $templateMgr->assign('isSiteAdmin', $isSiteAdmin);
            /** @var RoleDAO $roleDao */
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roles = $roleDao->getRolesByUserId($user->getId(), $isSiteAdmin ? null : $journal->getId());
            $templateMgr->assign('userRoles', $roles);
            if ($isSiteAdmin) {
                /** @var JournalDAO $journalDao */
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $journalTitles = $journalDao->getJournalTitles();
                $templateMgr->assign('journalTitles', $journalTitles);
            }

            /** @var CountryDAO $countryDao */
            $countryDao = DAORegistry::getDAO('CountryDAO');
            $country = null;
            if ($user->getCountry() != '') {
                $country = $countryDao->getCountry($user->getCountry());
            }

            $templateMgr->assign('country', $country);
            $templateMgr->assign('userInterests', $user->getInterestString());
            $templateMgr->assign('user', $user);
            $templateMgr->assign('localeNames', AppLocale::getAllLocales());

            $templateMgr->display('manager/people/userProfile.tpl');
        }
    }

}
?>
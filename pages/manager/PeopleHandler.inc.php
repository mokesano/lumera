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
 *
 * [WIZDAM EDITION] FULL REFACTOR: PHP 8.1+ Strict Types, Security Hardening, Smarty Modernization
 */

import('pages.manager.ManagerHandler');

class PeopleHandler extends ManagerHandler {
    
    /**
     * Constructor
     **/
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PeopleHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Display list of people in the selected role.
     * @param array $args first parameter is the role ID to display
     */
    public function people($args) {
        $this->validate();
        $this->setupTemplate(true);

        $roleDao = DAORegistry::getDAO('RoleDAO');

        // FIX: Input dibersihkan dengan trim() untuk menghilangkan whitespace berbahaya.
        $roleSymbolicInput = trim((string) Request::getUserVar('roleSymbolic'));
        if ($roleSymbolicInput != '') {
            $roleSymbolic = $roleSymbolicInput;
        } else {
            // Input dari $args[0] juga harus dibersihkan.
            $roleSymbolic = isset($args[0]) ? trim((string)$args[0]) : 'all';
        }

        // FIX: Input dibersihkan dengan trim().
        $sort = trim((string) Request::getUserVar('sort'));
        $sort = $sort != '' ? $sort : 'name';
        
        // FIX: Input di-Whitelisting terhadap nilai yang valid dan di-uppercase.
        $sortDirection = strtoupper(trim((string) Request::getUserVar('sortDirection')));
        $sortDirection = in_array($sortDirection, [SORT_DIRECTION_ASC, SORT_DIRECTION_DESC]) ? $sortDirection : SORT_DIRECTION_ASC;

        $roleId = 0;
        $roleName = 'manager.people.allUsers';

        if ($roleSymbolic != 'all' && PKPString::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
            // Logika ini secara implisit memvalidasi $roleSymbolic (Whitelisting).
            $checkRoleId = $roleDao->getRoleIdFromPath($matches[1]);
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
        
        // [FIX 1] Sanitize 'search' input
        $search = trim((string) Request::getUserVar('search'));

        // [FIX 2] Sanitize 'searchInitial' input
        $searchInitial = trim((string) Request::getUserVar('searchInitial'));
        if (!preg_match('/^[A-Z0-9]$/i', $searchInitial)) { 
            $searchInitial = '';
        }
        
        if (!empty($search)) {
            // Definisi Whitelist untuk searchField
            $validSearchFields = [
                USER_FIELD_FIRSTNAME, USER_FIELD_LASTNAME, USER_FIELD_USERNAME,
                USER_FIELD_EMAIL, USER_FIELD_INTERESTS, USER_FIELD_AFFILIATION
            ];
            
            // [FIX 3] Sanitize 'searchField' (searchType): Whitelisting
            $searchType = (string) Request::getUserVar('searchField');
            if (!in_array($searchType, $validSearchFields)) {
                $searchType = null; 
            }
            
            // Definisi Whitelist untuk searchMatch
            $validSearchMatches = ['is', 'contains', 'startsWith'];
            
            // [FIX 4] Sanitize 'searchMatch': Whitelisting
            $searchMatch = trim((string) Request::getUserVar('searchMatch'));
            if (!in_array($searchMatch, $validSearchMatches)) {
                $searchMatch = 'contains'; 
            }
        
        } elseif (!empty($searchInitial)) {
            // Jika menggunakan searchInitial, input sudah disanitasi di atas.
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

        $templateMgr->assign('roleSettings', $this->retrieveRoleAssignmentPreferences($journal->getId()));

        if ($roleId == ROLE_ID_REVIEWER) {
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

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $roleId = (int) (isset($args[0]) ? trim($args[0]) : trim((string)Request::getUserVar('roleId')));
        $journal = $journalDao->getJournalByPath(Request::getRequestedJournalPath());

        $sort = trim((string)Request::getUserVar('sort'));
        $sort = $sort != '' ? $sort : 'name';
        
        // FIX: Input di-Whitelisting
        $sortDirection = strtoupper(trim((string)Request::getUserVar('sortDirection')));
        $sortDirection = in_array($sortDirection, [SORT_DIRECTION_ASC, SORT_DIRECTION_DESC]) ? $sortDirection : SORT_DIRECTION_ASC;

        $templateMgr = TemplateManager::getManager();

        $this->setupTemplate(true);

        $searchType = null;
        $searchMatch = null;
        
        // FIX: Sanitasi input search
        $search = trim((string)Request::getUserVar('search'));
        
        // FIX: Sanitasi input searchInitial
        $searchInitial = trim((string)Request::getUserVar('searchInitial'));
        if (!preg_match('/^[A-Z0-9]$/i', $searchInitial)) { 
            $searchInitial = '';
        }
        
        if (!empty($search)) {
            $validSearchFields = [
                USER_FIELD_FIRSTNAME, USER_FIELD_LASTNAME, USER_FIELD_USERNAME,
                USER_FIELD_EMAIL, USER_FIELD_INTERESTS, USER_FIELD_AFFILIATION
            ];
            
            // FIX: Whitelisting searchField
            $searchType = (string) Request::getUserVar('searchField');
            if (!in_array($searchType, $validSearchFields)) {
                $searchType = null; 
            }
            
            $validSearchMatches = ['is', 'contains', 'startsWith'];
            
            // FIX: Whitelisting searchMatch
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
        // [SECURITY FIX] Sanitasi input untuk display/template dengan htmlspecialchars()
        $templateMgr->assign('searchInitial', htmlspecialchars(trim((string)Request::getUserVar('searchInitial')), ENT_QUOTES, 'UTF-8'));

        $templateMgr->assign('roleSettings', $this->retrieveRoleAssignmentPreferences($journal->getId()));

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

        $userDao = DAORegistry::getDAO('UserDAO');

        $templateMgr = TemplateManager::getManager();

        parent::setupTemplate(true);

        $rangeInfo = $this->getRangeInfo('users');

        $users = $userDao->getUsersWithNoRole(true, $rangeInfo);

        $templateMgr->assign('omitSearch', true);
        $templateMgr->assign('users', $users);
        $templateMgr->assign('thisUser', Request::getUser());
        $templateMgr->assign('helpTopicId', 'journal.users.index');
        $templateMgr->display('manager/people/searchUsers.tpl');
    }

    /**
     * Enroll a user in a role.
     */
    public function enroll($args) {
        $this->validate();
        $roleId = (int)(isset($args[0]) ? $args[0] : Request::getUserVar('roleId'));

        // [SECURITY FIX] Ambil dan sanitasi 'userId' sebagai integer terlebih dahulu.
        $userId = (int) Request::getUserVar('userId');
        
        // [SECURITY FIX] Ambil dan sanitasi 'users' sebagai array yang berisi integer.
        $users = array_map('intval', (array) Request::getUserVar('users'));
        
        if (empty($users) && $userId != 0) {
            $users = [$userId];
        }

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getJournalByPath(Request::getRequestedJournalPath());
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
     */
    public function unEnroll($args) {
        $roleId = (int) array_shift($args);
        $journalId = (int) Request::getUserVar('journalId');
        $userId = (int) Request::getUserVar('userId');

        $this->validate();

        $journal = Request::getJournal();
        if ($roleId != ROLE_ID_SITE_ADMIN && (Validation::isSiteAdmin() || $journalId = $journal->getId())) {
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roleDao->deleteRoleByUserId($userId, $journalId, $roleId);
        }

        Request::redirect(null, null, 'people', $roleDao->getRolePath($roleId) . 's');
    }

    /**
     * Show form to synchronize user enrollment with another journal.
     */
    public function enrollSyncSelect($args) {
        $this->validate();
        $this->setupTemplate(true);

        $rolePath = isset($args[0]) ? (string)$args[0] : '';
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roleId = $roleDao->getRoleIdFromPath($rolePath);
        if ($roleId) {
            $roleName = $roleDao->getRoleName($roleId, true);
        } else {
            $rolePath = '';
            $roleName = '';
        }

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
        // [SECURITY FIX] Amankan rolePath
        $rolePath = trim((string)Request::getUserVar('rolePath'));
        
        // [SECURITY FIX] Amankan syncJournal
        $syncJournalInput = Request::getUserVar('syncJournal');
        $syncJournal = $syncJournalInput === 'all' ? 'all' : (int)$syncJournalInput;

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
    
        // [SECURITY FIX] Bersihkan input string dengan trim()
        $firstName = trim((string)Request::getUserVar('firstName'));
        $lastName = trim((string)Request::getUserVar('lastName'));
    
        $suggestion = Validation::suggestUsername($firstName, $lastName);
    
        // [SECURITY FIX] Escape output untuk mencegah Reflected XSS
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
            $userForm->initData($args, $request);
        }
        $userForm->display();
    }

    /**
     * Allow the Journal Manager to merge user accounts.
     */
    public function mergeUsers($args) {
        $this->validate();
        $this->setupTemplate(true);

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $userDao = DAORegistry::getDAO('UserDAO');

        $journal = Request::getJournal();
        $journalId = $journal->getId();
        $templateMgr = TemplateManager::getManager();

        // [SECURITY FIX] Amankan array oldUserIds
        $oldUserIds = array_map('intval', (array) Request::getUserVar('oldUserIds'));
        
        // [SECURITY FIX] Amankan newUserId
        $newUserId = (int) Request::getUserVar('newUserId');

        // Ensure that we have administrative priveleges over the specified user(s).
        $canAdministerAll = true;
        foreach ($oldUserIds as $oldUserId) {
            if (!Validation::canAdminister($journalId, $oldUserId)) $canAdministerAll = false;
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
            foreach ($oldUserIds as $oldUserId) {
                UserAction::mergeUsers($oldUserId, $newUserId);
            }
            Request::redirect(null, 'manager');
        }

        // [SECURITY FIX] Ambil dan trim input 'roleSymbolic'
        $roleSymbolicInput = trim((string)Request::getUserVar('roleSymbolic'));
        if (!empty($roleSymbolicInput)) {
            $roleSymbolic = $roleSymbolicInput;
        } else {
            $roleSymbolic = isset($args[0]) ? trim((string)$args[0]) : 'all';
        }

        $roleId = 0;
        $roleName = 'manager.people.allUsers';

        if ($roleSymbolic != 'all' && PKPString::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
            $checkRoleId = $roleDao->getRoleIdFromPath($matches[1]);
            if ($checkRoleId == null) {
                Request::redirect(null, null, null, 'all');
            }
            $roleId = $checkRoleId;
            $roleName = $roleDao->getRoleName($roleId, true);
        }

        // [SECURITY FIX] Amankan 'sort'
        $sort = trim((string)Request::getUserVar('sort'));
        $sort = $sort != '' ? $sort : 'name';

        // [SECURITY FIX] Whitelist 'sortDirection'
        $sortDirection = strtoupper(trim((string)Request::getUserVar('sortDirection')));
        $sortDirection = in_array($sortDirection, [SORT_DIRECTION_ASC, SORT_DIRECTION_DESC]) ? $sortDirection : SORT_DIRECTION_ASC;

        $searchType = null;
        $searchMatch = null;

        // [SECURITY FIX] Amankan 'search'
        $search = trim((string)Request::getUserVar('search'));

        // [SECURITY FIX] Amankan 'searchInitial'
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

        $templateMgr->assign('roleSettings', $this->retrieveRoleAssignmentPreferences($journal->getId()));

        $templateMgr->assign('currentUrl', Request::url(null, null, 'people', 'all'));
        $templateMgr->assign('helpTopicId', 'journal.managementPages.mergeUsers');
        $templateMgr->assign('roleName', $roleName);
        $templateMgr->assign('users', $users);
        $templateMgr->assign('thisUser', Request::getUser());
        $templateMgr->assign('isReviewer', $roleId == ROLE_ID_REVIEWER);

        $templateMgr->assign('searchField', $searchType);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $search);
        // [SECURITY FIX] Amankan output 'searchInitial'
        $templateMgr->assign('searchInitial', htmlspecialchars(trim((string)Request::getUserVar('searchInitial')), ENT_QUOTES, 'UTF-8'));

        if ($roleId == ROLE_ID_REVIEWER) {
            $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
            $templateMgr->assign('rateReviewerOnQuality', $journal->getSetting('rateReviewerOnQuality'));
            $templateMgr->assign('qualityRatings', $journal->getSetting('rateReviewerOnQuality') ? $reviewAssignmentDao->getAverageQualityRatings($journalId) : null);
        }
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

        // [SECURITY FIX] Amankan $userId
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
            $userDao = DAORegistry::getDAO('UserDAO');
            $userTarget = $userDao->getById($userId);
            if ($userTarget) {
                $userTarget->setDisabled(1);
                // [SECURITY FIX] Amankan input string 'reason'
                $reason = htmlspecialchars(trim((string)Request::getUserVar('reason')), ENT_QUOTES, 'UTF-8');
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
            $userDao = DAORegistry::getDAO('UserDAO');
            $userTarget = $userDao->getById($userId, true);
            if ($userTarget) {
                $userTarget->setDisabled(0);
                $userDao->updateObject($userTarget);
            }
        }

        Request::redirect(null, null, 'people', 'all');
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
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roleDao->deleteRoleByUserId($userId, $journal->getId());
        }

        Request::redirect(null, null, 'people', 'all');
    }

    /**
     * Save changes to a user profile.
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

            // [SECURITY FIX] Amankan flag boolean 'createAnother'
            if ((int) $request->getUserVar('createAnother')) {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('currentUrl', $request->url(null, null, 'people', 'all'));
                $templateMgr->assign('userCreated', true);
                unset($userForm);
                $userForm = new UserManagementForm();
                $userForm->initData($args, $request);
                $userForm->display();

            } else {
                // [SECURITY FIX] Amankan 'source' dari Open Redirect
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

        $userDao = DAORegistry::getDAO('UserDAO');
        $userId = isset($args[0]) ? $args[0] : 0;
        if (is_numeric($userId)) {
            $userId = (int) $userId;
            $user = $userDao->getById($userId);
        } else {
            // Jika username, pastikan aman stringnya (walau getByUsername biasanya safe via parameter binding)
            $user = $userDao->getByUsername((string)$userId);
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
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $roles = $roleDao->getRolesByUserId($user->getId(), $isSiteAdmin ? null : $journal->getId());
            $templateMgr->assign('userRoles', $roles);
            if ($isSiteAdmin) {
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $journalTitles = $journalDao->getJournalTitles();
                $templateMgr->assign('journalTitles', $journalTitles);
            }

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
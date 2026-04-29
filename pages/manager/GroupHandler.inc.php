<?php
declare(strict_types=1);

/**
 * @file pages/manager/GroupHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GroupHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for editorial team management functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class GroupHandler extends ManagerHandler {
    
    /** @var Group|null group associated with the request */
    public $group;

    /** @var GroupMembership|null groupMembership associated with the request */
    public $groupMembership;

    /** @var User|null user associated with the request */
    public $user;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GroupHandler() {
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
     * Display a list of groups for the current journal.
     */
    public function groups() {
        $this->validate();
        $this->setupTemplate();

        $journal = Application::get()->getRequest()->getJournal();

        $rangeInfo = $this->getRangeInfo('groups');

        $groupDao = DAORegistry::getDAO('GroupDAO');
        $groups = $groupDao->getGroups(ASSOC_TYPE_JOURNAL, $journal->getId(), null, $rangeInfo);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');
        // [WIZDAM] assign_by_ref deprecated
        $templateMgr->assign('groups', $groups);
        $templateMgr->assign('boardEnabled', $journal->getSetting('boardEnabled'));
        $templateMgr->display('manager/groups/groups.tpl');
    }

    /**
     * Delete a group.
     * @param array $args first parameter is the ID of the group to delete
     */
    public function deleteGroup($args) {
        $groupId = isset($args[0]) ? (int)$args[0] : 0;
        $this->validate($groupId);

        $group = $this->group;

        $groupDao = DAORegistry::getDAO('GroupDAO');
        $groupDao->deleteObject($group);
        $groupDao->resequenceGroups($group->getAssocType(), $group->getAssocId());

        Application::get()->getRequest()->redirect(null, null, 'groups');
    }

    /**
     * Change the sequence of a group.
     */
    public function moveGroup() {
        $request = Application::get()->getRequest();
        // [SECURITY FIX] Amankan 'id' dengan trim() dan (int)
        $groupId = (int) trim((string) $request->getUserVar('id'));
        $this->validate($groupId);

        $group = $this->group;
        $groupDao = DAORegistry::getDAO('GroupDAO');
        
        // [SECURITY FIX] Whitelist 'd' (direction)
        $direction = trim((string) $request->getUserVar('d'));

        if (!empty($direction)) {
            // Whitelist arah pergerakan yang valid
            if ($direction == 'u') {
                $group->setSequence($group->getSequence() - 1.5);
            } elseif ($direction == 'd') {
                $group->setSequence($group->getSequence() + 1.5);
            }
            // Jika $direction tidak valid, aksi dilewati

        } else {
            // Dragging and dropping
            
            // [SECURITY FIX] Amankan 'prevId' (integer ID) dengan trim()
            $prevId = (int) trim((string) $request->getUserVar('prevId'));
            
            if ($prevId == 0) { // Jika $prevId tidak disetel atau 0
                $prevSeq = 0;
            } else {
                // [MODERNISASI] Hapus operator legacy = &
                $journal = $request->getJournal();
                
                // [MODERNISASI] Hapus operator legacy = &
                // Kita juga menggunakan $prevId yang sudah diamankan
                $prevGroup = $groupDao->getById($prevId, ASSOC_TYPE_JOURNAL, $journal->getId());
                $prevSeq = $prevGroup->getSequence();
            }

            $group->setSequence($prevSeq + .5);
        }

        $groupDao->updateObject($group);
        $groupDao->resequenceGroups($group->getAssocType(), $group->getAssocId());

        // Moving up or down with the arrows requires a page reload.
        // In the case of a drag and drop move, the display has been
        // updated on the client side, so no reload is necessary.
        if ($direction != null) {
            $request->redirect(null, null, 'groups');
        }
    }

    /**
     * Display form to edit a group.
     * @param array $args optional, first parameter is the ID of the group to edit
     */
    public function editGroup($args = []) {
        $groupId = isset($args[0]) ? (int)$args[0] : null;
        $this->validate($groupId);
        $journal = Application::get()->getRequest()->getJournal();

        if ($groupId !== null) {
            $groupDao = DAORegistry::getDAO('GroupDAO');
            $group = $groupDao->getById($groupId, ASSOC_TYPE_JOURNAL, $journal->getId());
            if (!$group) {
                Application::get()->getRequest()->redirect(null, null, 'groups');
            }
        } else {
            $group = null;
        }

        $this->setupTemplate($group, true);
        import('classes.manager.form.GroupForm');

        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('pageTitle',
            $group === null ?
                'manager.groups.createTitle' :
                'manager.groups.editTitle'
        );

        $groupForm = new GroupForm($group);
        if ($groupForm->isLocaleResubmit()) {
            $groupForm->readInputData();
        } else {
            $groupForm->initData();
        }
        $groupForm->display();
    }

    /**
     * Display form to create new group.
     * @param array $args
     */
    public function createGroup($args) {
        $this->editGroup($args);
    }

    /**
     * Save changes to a group.
     */
    public function updateGroup() {
        $request = Application::get()->getRequest();
        // [SECURITY FIX] Amankan 'groupId' dengan trim() sebelum (int)
        // Kita juga pastikan trim() hanya dilakukan pada string non-null.
        $groupIdInput = $request->getUserVar('groupId');
        $groupId = $groupIdInput === null ? null : (int) trim((string) $groupIdInput); 
        
        if ($groupId === null) {
            $this->validate();
            $group = null;
        } else {
            $this->validate($groupId);
            // [MODERNISASI] Hapus operator legacy = &
            $group = $this->group; 
        }
        $this->setupTemplate($group);

        import('classes.manager.form.GroupForm');

        $groupForm = new GroupForm($group);
        $groupForm->readInputData();

        if ($groupForm->validate()) {
            $groupForm->execute();
            $request->redirect(null, null, 'groups');
        } else {

            $templateMgr = TemplateManager::getManager();
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'groups'), 'manager.groups']);

            $templateMgr->assign('pageTitle',
                $group ?
                    'manager.groups.editTitle' :
                    'manager.groups.createTitle'
            );

            $groupForm->display();
        }
    }

    /**
     * View group membership.
     * @param array $args
     */
    public function groupMembership($args) {
        $groupId = isset($args[0]) ? (int)$args[0] : 0;
        $this->validate($groupId);
        $group = $this->group;

        $rangeInfo = $this->getRangeInfo('memberships');

        $this->setupTemplate($group, true);
        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
        $memberships = $groupMembershipDao->getMemberships($group->getId(), $rangeInfo);
        $templateMgr = TemplateManager::getManager();
        $templateMgr->addJavaScript('lib/pkp/js/lib/jquery/plugins/jquery.tablednd.js');
        $templateMgr->addJavaScript('lib/pkp/js/functions/tablednd.js');
        
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('memberships', $memberships);
        $templateMgr->assign('group', $group);
        
        $templateMgr->display('manager/groups/memberships.tpl');
    }

    /**
     * Add group membership (or list users if none chosen).
     * @param array $args
     */
    public function addMembership($args) {
        $groupId = isset($args[0]) ? (int)$args[0] : 0;
        $userId = isset($args[1]) ? (int)$args[1] : null;

        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
        $request = Application::get()->getRequest();

        // If a user has been selected, add them to the group.
        // Otherwise list users.
        if ($userId !== null) {
            $this->validate($groupId, $userId);
            $group = $this->group;
            $user = $this->user;
            // A valid user has been chosen. Add them to
            // the membership list and redirect.

            // Avoid duplicating memberships.
            $groupMembership = $groupMembershipDao->getMembership($group->getId(), $user->getId());

            if (!$groupMembership) {
                $groupMembership = new GroupMembership();
                $groupMembership->setGroupId($group->getId());
                $groupMembership->setUserId($user->getId());
                // For now, all memberships are displayed in About
                $groupMembership->setAboutDisplayed(true);
                $groupMembershipDao->insertMembership($groupMembership);
            }
            $request->redirect(null, null, 'groupMembership', $group->getId());
        } else {
            $this->validate($groupId);
            $group = $this->group;
            $this->setupTemplate($group, true);

            $searchType = null;
            $searchMatch = null;
            // [SECURITY FIX] Amankan 'search' dan 'searchInitial' dengan trim()
            $search = $searchQuery = trim((string) $request->getUserVar('search'));
            $searchInitial = trim((string) $request->getUserVar('searchInitial'));
            
            if (!empty($search)) {
                // [SECURITY FIX] Amankan 'searchField' (key) dengan trim()
                $searchType = trim((string) $request->getUserVar('searchField'));
                
                // [SECURITY FIX] Amankan 'searchMatch' (key) dengan trim()
                $searchMatch = trim((string) $request->getUserVar('searchMatch')); 

            } elseif (!empty($searchInitial)) {
                // $searchInitial sudah diamankan
                $searchInitial = PKPString::strtoupper($searchInitial);
                $searchType = USER_FIELD_INITIAL;
                $search = $searchInitial;
            }

            $roleDao = DAORegistry::getDAO('RoleDAO');
            $journal = $request->getJournal();
            $users = $roleDao->getUsersByRoleId(null, $journal->getId(), $searchType, $search, $searchMatch);

            $templateMgr = TemplateManager::getManager();

            $templateMgr->assign('searchField', $searchType);
            $templateMgr->assign('searchMatch', $searchMatch);
            $templateMgr->assign('search', $searchQuery);
            // [SECURITY FIX] Amankan 'searchInitial' dengan trim() dan htmlspecialchars() untuk mencegah XSS
            $searchInitialRaw = trim((string) $request->getUserVar('searchInitial'));
            $templateMgr->assign('searchInitial', htmlspecialchars($searchInitialRaw, ENT_QUOTES, 'UTF-8'));

            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('users', $users);
            
            $templateMgr->assign('fieldOptions', [
                USER_FIELD_FIRSTNAME => 'user.firstName',
                USER_FIELD_LASTNAME => 'user.lastName',
                USER_FIELD_USERNAME => 'user.username',
                USER_FIELD_EMAIL => 'user.email'
            ]);
            $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
            $templateMgr->assign('group', $group);

            $templateMgr->display('manager/groups/selectUser.tpl');
        }
    }

    /**
     * Delete group membership.
     * @param array $args
     */
    public function deleteMembership($args) {
        $groupId = isset($args[0]) ? (int)$args[0] : 0;
        $userId = isset($args[1]) ? (int)$args[1] : 0;

        $this->validate($groupId, $userId, true);
        $group = $this->group;
        $user = $this->user;
        $groupMembership = $this->groupMembership;

        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
        $groupMembershipDao->deleteMembershipById($group->getId(), $user->getId());
        $groupMembershipDao->resequenceMemberships($group->getId());

        Application::get()->getRequest()->redirect(null, null, 'groupMembership', $group->getId());
    }

    /**
     * Change the sequence of a group membership.
     * @param array $args
     */
    public function moveMembership($args) {
        $request = Application::get()->getRequest();
        $groupId = isset($args[0]) ? (int)$args[0] : 0;
        // [SECURITY FIX] Amankan 'id' (userId) dengan trim() dan (int)
        $userId = (int) trim((string) $request->getUserVar('id'));
        $this->validate($groupId, $userId, true);
        $group = $this->group;
        $groupMembership = $this->groupMembership;

        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
        
        // [SECURITY FIX] Whitelist 'd' (direction)
        $direction = trim((string) $request->getUserVar('d'));
        
        if (!empty($direction)) {
            // Whitelist arah pergerakan yang valid
            if ($direction == 'u') {
                $groupMembership->setSequence($groupMembership->getSequence() - 1.5);
            } elseif ($direction == 'd') {
                $groupMembership->setSequence($groupMembership->getSequence() + 1.5);
            }
        } else {
            // drag and drop
            
            // [SECURITY FIX] Amankan 'prevId' (ID numerik) dengan (int) trim()
            $prevId = (int) trim((string) $request->getUserVar('prevId'));
            
            if ($prevId == 0) { // $prevId akan 0 jika null/kosong karena (int) casting
                $prevSeq = 0;
            } else {
                // [MODERNISASI] Hapus operator legacy = &
                $prevMembership = $groupMembershipDao->getMembership($groupId, $prevId);
                $prevSeq = $prevMembership->getSequence();
            }

            $groupMembership->setSequence($prevSeq + .5);
        }
        $groupMembershipDao->updateObject($groupMembership);
        $groupMembershipDao->resequenceMemberships($group->getId());

        // Moving up or down with the arrows requires a page reload.
        // In the case of a drag and drop move, the display has been
        // updated on the client side, so no reload is necessary.
        if ($direction != null) {
            $request->redirect(null, null, 'groupMembership', $group->getId());
        }
    }

    /**
     * @param array $args
     */
    public function setBoardEnabled($args) {
        $this->validate();
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        // [SECURITY FIX] Amankan 'boardEnabled' dengan (int) trim()
        $boardEnabled = (int) trim((string) $request->getUserVar('boardEnabled')) == 1;
        
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalSettingsDao->updateSetting($journal->getId(), 'boardEnabled', $boardEnabled);
        $request->redirect(null, null, 'groups');
    }

    /**
     * @param Group|null $group
     * @param bool $subclass
     */
    public function setupTemplate($group = null, $subclass = false) {
        parent::setupTemplate(true);
        $templateMgr = TemplateManager::getManager();
        $request = Application::get()->getRequest();
        
        if ($subclass) {
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'groups'), 'manager.groups']);
        }
        if ($group) {
            $templateMgr->append('pageHierarchy', [$request->url(null, 'manager', 'editGroup', $group->getId()), $group->getLocalizedTitle(), true]);
        }
        $templateMgr->assign('helpTopicId', 'journal.managementPages.groups');
    }

    /**
     * Validate the request. If a group ID is supplied, the group object
     * will be fetched and validated against the current journal. If,
     * additionally, the user ID is supplied, the user and membership
     * objects will be validated and fetched.
     * @param int|null $groupId optional
     * @param int|null $userId optional
     * @param bool $fetchMembership Whether or not to fetch membership object
     * @return bool
     */
    public function validate($groupId = null, $userId = null, $fetchMembership = false) {
        parent::validate();

        $journal = Application::get()->getRequest()->getJournal();

        $passedValidation = true;

        if ($groupId !== null) {
            $groupDao = DAORegistry::getDAO('GroupDAO');
            $group = $groupDao->getById($groupId, ASSOC_TYPE_JOURNAL, $journal->getId());

            if (!$group) $passedValidation = false;
            else $this->group = $group;

            if ($userId !== null) {
                $userDao = DAORegistry::getDAO('UserDAO');
                $user = $userDao->getById($userId);

                if (!$user) $passedValidation = false;
                else $this->user = $user;

                if ($fetchMembership === true) {
                    $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
                    $groupMembership = $groupMembershipDao->getMembership($groupId, $userId);
                    if (!$groupMembership) $passedValidation = false;
                    else $this->groupMembership = $groupMembership;
                }
            }
        }
        if (!$passedValidation) {
            Application::get()->getRequest()->redirect(null, null, 'groups');
        }
        return true;
    }
}
?>
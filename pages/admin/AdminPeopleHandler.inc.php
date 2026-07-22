<?php
declare(strict_types=1);

/**
 * @file pages/admin/AdminPeopleHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminPeopleHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for people management functions.
 */

import('pages.admin.AdminHandler');

class AdminPeopleHandler extends AdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AdminPeopleHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::AdminPeopleHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Allow the Site Administrator to merge user accounts, including attributed articles etc.
     * @param array $args
     * @param PKPRequest $request
     */
    public function mergeUsers($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $templateMgr = TemplateManager::getManager();

        $oldUserIds = (array) $request->getUserVar('oldUserIds');
        $oldUserIds = array_map('intval', $oldUserIds);
        
        $newUserId = (int) $request->getUserVar('newUserId');

        if (!empty($oldUserIds) && !empty($newUserId)) {
            import('classes.user.UserAction');
            foreach ($oldUserIds as $oldUserId) {
                $userAction = new UserAction();
                $userAction->mergeUsers($oldUserId, $newUserId);
            }
            $request->redirect(null, 'admin', 'mergeUsers');
        }

        $roleSymbolicInput = $request->getUserVar('roleSymbolic');
        if ($roleSymbolicInput != null) {
            $roleSymbolic = trim((string) $roleSymbolicInput);
        } else {
            $roleSymbolic = isset($args[0]) ? (string) $args[0] : 'all';
        }

        $matches = [];
        if ($roleSymbolic != 'all' && PKPString::regexp_match_get('/^(\w+)s$/', $roleSymbolic, $matches)) {
            $roleId = $roleDao->getRoleIdFromPath($matches[1]);
            if ($roleId == null) {
                $request->redirect(null, null, null, 'all');
            }
            $roleName = $roleDao->getRoleName($roleId, true);
        } else {
            $roleId = 0;
            $roleName = 'admin.mergeUsers.allUsers';
        }

        $searchType = null;
        $searchMatch = null;
        $search = trim((string) $request->getUserVar('search'));
        $searchInitial = trim((string) $request->getUserVar('searchInitial'));

        if (!preg_match('/^[A-Z]$/i', $searchInitial)) {
            $searchInitial = '';
        }
        
        if (!empty($search)) {
            $searchType = trim((string) $request->getUserVar('searchField'));
            $allowedFields = ['name', 'username', 'email', 'affiliation', USER_FIELD_FIRSTNAME, USER_FIELD_LASTNAME, USER_FIELD_USERNAME, USER_FIELD_EMAIL, USER_FIELD_INTERESTS]; 
            if (!in_array($searchType, $allowedFields)) {
                $searchType = USER_FIELD_FIRSTNAME;
                if($searchType == 'name') $searchType = USER_FIELD_FIRSTNAME;
            }
            
            $searchMatch = trim((string) $request->getUserVar('searchMatch'));
            $allowedMatches = ['is', 'contains', 'startsWith'];
            if (!in_array($searchMatch, $allowedMatches)) {
                $searchMatch = 'contains';
            }

        } elseif (!empty($searchInitial)) {
            $searchInitial = PKPString::strtoupper($searchInitial);
            $searchType = USER_FIELD_INITIAL;
            $search = $searchInitial;
        }

        $rangeInfo = $this->getRangeInfo('users');

        if ($roleId) {
            $users = $roleDao->getUsersByRoleId($roleId, null, $searchType, $search, $searchMatch, $rangeInfo);
            $templateMgr->assign('roleId', $roleId);
        } else {
            $users = $userDao->getUsersByField($searchType, $searchMatch, $search, true, $rangeInfo);
        }

        $templateMgr->assign('currentUrl', $request->url(null, null, 'mergeUsers'));
        $templateMgr->assign('helpTopicId', 'site.administrativeFunctions');
        $templateMgr->assign('roleName', $roleName);
        $templateMgr->assign('users', $users);
        $templateMgr->assign('thisUser', $request->getUser());
        $templateMgr->assign('isReviewer', $roleId == ROLE_ID_REVIEWER);

        $templateMgr->assign('searchField', $searchType);
        $templateMgr->assign('searchMatch', $searchMatch);
        $templateMgr->assign('search', $search);
        $templateMgr->assign('searchInitial', $request->getUserVar('searchInitial'));

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
        
        $templateMgr->display('admin/selectMergeUser.tpl');
    }

}
?>
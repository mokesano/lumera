<?php
declare(strict_types=1);

/**
 * @file pages/manager/ManagerHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManagerHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for journal management functions.
 */

import('classes.handler.Handler');

class ManagerHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->addCheck(new HandlerValidatorRoles($this, true, null, null, [ROLE_ID_SITE_ADMIN, ROLE_ID_JOURNAL_MANAGER]));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ManagerHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ManagerHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        
        $args = func_get_args();
        self::__construct(...$args);
    }

    /**
     * Display journal management index page.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate();
        
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);

        $newVersionAvailable = false;
        if (Config::getVar('general', 'show_upgrade_warning')) {
            import('lib.pkp.classes.site.VersionCheck');
            $latestVersion = VersionCheck::checkIfNewVersionExists();
            
            if ($latestVersion) {
                $newVersionAvailable = true;
                $templateMgr->assign('latestVersion', $latestVersion);
                
                $currentVersion = VersionCheck::getCurrentDBVersion();
                $templateMgr->assign('currentVersion', $currentVersion ? $currentVersion->getVersionString() : '');
                
                /** @var RoleDAO $roleDao */
                $roleDao = DAORegistry::getDAO('RoleDAO');
                $siteAdmins = $roleDao->getUsersByRoleId(ROLE_ID_SITE_ADMIN);
                $siteAdmin = $siteAdmins ? $siteAdmins->next() : null;
                $templateMgr->assign('siteAdmin', $siteAdmin);
            }
        }

        $templateMgr->assign('newVersionAvailable', $newVersionAvailable);
        $templateMgr->assign('publishingMode', $journal ? $journal->getSetting('publishingMode') : null);
        $templateMgr->assign('announcementsEnabled', $journal ? (bool) $journal->getSetting('enableAnnouncements') : false);
        
        $session = $request->getSession();
        if ($session) {
            $session->unsetSessionVar('enrolmentReferrer');
        }

        $templateMgr->assign('helpTopicId', 'journal.index');
        $templateMgr->display('manager/index.tpl');
    }

    /**
     * Send an email to a user or group of users.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function email($args, $request = null) {
        $this->validate();
        $this->setupTemplate(true);
        
        if (!$request) {
            $request = Application::get()->getRequest();
        }
        
        $templateMgr = TemplateManager::getManager($request); 
        $templateMgr->assign('helpTopicId', 'journal.users.emailUsers');

        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.MailTemplate');
        $templateKey = trim((string) ($request->getUserVar('template') ?? ''));
        $localeKey = trim((string) ($request->getUserVar('locale') ?? ''));
        $email = new MailTemplate($templateKey, $localeKey);
        
        $sendFlag = (bool) $request->getUserVar('send');
        
        if ($sendFlag && !$email->hasErrors()) {
            $email->send();
            $request->redirect(null, $request->getRequestedPage());
        } else {
            $email->assignParams(); 
            
            $continued = (bool) $request->getUserVar('continued');
            if (!$continued) {
                $groupId = (int) $request->getUserVar('toGroup');
                
                if ($groupId !== 0) {
                    /** @var GroupDAO $groupDao */
                    $groupDao = DAORegistry::getDAO('GroupDAO');
                    $group = $groupDao->getById($groupId);

                    if ($group && $group->getAssocId() === (int) $journal->getId() && $group->getAssocType() === ASSOC_TYPE_JOURNAL) {
                        /** @var GroupMembershipDAO $groupMembershipDao */
                        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
                        
                        $membershipsIterator = $groupMembershipDao->getMemberships($group->getId());
                        $membershipsArray = $membershipsIterator ? $membershipsIterator->toArray() : [];
                        
                        foreach ($membershipsArray as $membership) {
                            $memberUser = $membership->getUser();
                            if ($memberUser) {
                                $email->addRecipient($memberUser->getEmail(), $memberUser->getFullName());
                            }
                        }
                    }
                }
                
                $recipients = $email->getRecipients();
                if (!is_array($recipients) || count($recipients) === 0) {
                    if ($user) {
                        $email->addRecipient($user->getEmail(), $user->getFullName());
                    }
                }
            }
            $email->displayEditForm($request->url(null, null, 'email'), [], 'manager/people/email.tpl');
        }
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
     */
    public function setupTemplate($subclass = false) {
        parent::setupTemplate();
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_ADMIN,
            LOCALE_COMPONENT_CORE_MANAGER, 
            LOCALE_COMPONENT_APP_MANAGER 
        );
        
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        
        $pageHierarchy = $subclass 
            ? [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'manager'), 'manager.journalManagement']]
            : [[$request->url(null, 'user'), 'navigation.user']];
            
        $templateMgr->assign('pageHierarchy', $pageHierarchy);

        $journal = $request->getJournal();
        if ($journal) {
            $templateMgr->assign('roleSettings', $this->retrieveRoleAssignmentPreferences($journal->getId()));
        } else {
            $templateMgr->assign('roleSettings', ['useLayoutEditors' => 0, 'useCopyeditors' => 0, 'useProofreaders' => 0]);
        }
    }
       
    /**
     * Retrieves a list of special Journal Management settings related 
     * to the journal's inclusion of individual copyeditors, 
     * layout editors, and proofreaders.
     * @param int $journalId Journal ID
     * @return array
     */    
    public function retrieveRoleAssignmentPreferences($journalId) {
        /** @var JournalSettingsDAO $journalSettingsDao */
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalSettings = $journalSettingsDao->getJournalSettings($journalId);
        if (!is_array($journalSettings)) {
            $journalSettings = [];
        }
        
        $returner = ['useLayoutEditors' => 0, 'useCopyeditors' => 0, 'useProofreaders' => 0];

        foreach ($returner as $specific => $value) {
            if (isset($journalSettings[$specific]) && $journalSettings[$specific]) {
                $returner[$specific] = 1;
            }
        }
        return $returner;
    }
    
}
?>
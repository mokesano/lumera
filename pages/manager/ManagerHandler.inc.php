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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
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
     * FIX: Menggunakan self::__construct() untuk memutus rantai polimorfisme
     * yang menyebabkan infinite loop jika dipanggil dari Child Class.
     */
    public function ManagerHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ManagerHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        
        // [WIZDAM CRITICAL FIX]
        // JANGAN gunakan $this->__construct() atau call_user_func([$this...])
        // Karena $this merujuk ke Child Class (AnnouncementHandler), yang akan
        // memanggil parent::__construct, yang kembali ke sini -> LOOP.
        // Gunakan self::__construct() untuk memaksa eksekusi konstruktor kelas INI.
        
        $args = func_get_args();
        // Memanggil __construct milik ManagerHandler secara eksplisit
        self::__construct(...$args);
    }

    /**
     * Display journal management index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();

        // Display a warning message if there is a new version of OJS available
        $newVersionAvailable = false;
        if (Config::getVar('general', 'show_upgrade_warning')) {
            import('lib.pkp.classes.site.VersionCheck');
            if ($latestVersion = VersionCheck::checkIfNewVersionExists()) {
                $newVersionAvailable = true;
                $templateMgr->assign('latestVersion', $latestVersion);
                $currentVersion = VersionCheck::getCurrentDBVersion();
                $templateMgr->assign('currentVersion', $currentVersion->getVersionString());
                
                // Get contact information for site administrator
                $roleDao = DAORegistry::getDAO('RoleDAO');
                $siteAdmins = $roleDao->getUsersByRoleId(ROLE_ID_SITE_ADMIN);
                $templateMgr->assign('siteAdmin', $siteAdmins->next());
            }
        }

        $templateMgr->assign('newVersionAvailable', $newVersionAvailable);
        // Kode dipindahkan ke setup template agar global
        // $templateMgr->assign('roleSettings', $this->retrieveRoleAssignmentPreferences($journal->getId()));
        $templateMgr->assign('publishingMode', $journal->getSetting('publishingMode'));
        $templateMgr->assign('announcementsEnabled', $journal->getSetting('enableAnnouncements'));
        
        $session = $request->getSession();
        $session->unsetSessionVar('enrolmentReferrer');

        $templateMgr->assign('helpTopicId', 'journal.index');
        $templateMgr->display('manager/index.tpl');
    }

    /**
     * Send an email to a user or group of users.
     * @param array $args
     * @param PKPRequest $request
     */
    public function email($args, $request = null) {
        $this->validate();
        $this->setupTemplate(true);
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $templateMgr = TemplateManager::getManager(); 
        $templateMgr->assign('helpTopicId', 'journal.users.emailUsers');

        $userDao = DAORegistry::getDAO('UserDAO');

        $site = $request->getSite();
        $journal = $request->getJournal();
        $user = $request->getUser();

        import('classes.mail.MailTemplate');
        
        // [SECURITY FIX] Amankan 'template' dan 'locale' (string key) trim()
        $templateKey = trim((string) ($request->getUserVar('template') ?? ''));
        $localeKey = trim((string) ($request->getUserVar('locale') ?? ''));
        $email = new MailTemplate($templateKey, $localeKey);

        // [SECURITY FIX] Amankan flag boolean 'send' dengan (int) trim()
        $sendFlag = (int) trim((string) ($request->getUserVar('send') ?? ''));
        
        if ($sendFlag && !$email->hasErrors()) {
            $email->send();
            $request->redirect(null, $request->getRequestedPage());
        } else {
            $email->assignParams(); // FIXME Forces default parameters to be assigned (should do this automatically in MailTemplate?)
            
            // [SECURITY FIX] Amankan flag boolean 'continued' with (int) trim()
            if (!(int) trim((string) ($request->getUserVar('continued') ?? ''))) {
                
                // [SECURITY FIX] Amankan 'toGroup' (groupId) with (int) trim()
                $groupId = (int) trim((string) ($request->getUserVar('toGroup') ?? ''));
                
                if ($groupId != 0) {
                    // Special case for emailing entire groups:
                    // Check for a group ID and add recipients.
                    
                    $groupDao = DAORegistry::getDAO('GroupDAO');
                    
                    $group = $groupDao->getById($groupId);
                    
                    if ($group && $group->getAssocId() == $journal->getId() && $group->getAssocType() == ASSOC_TYPE_JOURNAL) {
                        
                        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
                        
                        $memberships = $groupMembershipDao->getMemberships($group->getId());
                        $memberships = $memberships->toArray();
                        
                        foreach ($memberships as $membership) {
                            $memberUser = $membership->getUser();
                            $email->addRecipient($memberUser->getEmail(), $memberUser->getFullName());
                        }
                    }
                }
                // KODE BARU — Aman di semua versi PHP
                $recipients = $email->getRecipients();
                if (!is_array($recipients) || count($recipients) === 0) {
                    $email->addRecipient($user->getEmail(), $user->getFullName());
                }
            }
            $email->displayEditForm($request->url(null, null, 'email'), [], 'manager/people/email.tpl');
        }
    }

    /**
     * Setup common template variables.
     * @param bool $subclass set to true if caller is below this handler in the hierarchy
     */
    public function setupTemplate($subclass = false) {
        parent::setupTemplate();
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_ADMIN,
            LOCALE_COMPONENT_CORE_MANAGER, 
            LOCALE_COMPONENT_APP_MANAGER 
        );
        $templateMgr = TemplateManager::getManager();
        $request = Application::get()->getRequest();
        
        $templateMgr->assign('pageHierarchy',
            $subclass ? [[$request->url(null, 'user'), 'navigation.user'], [$request->url(null, 'manager'), 'manager.journalManagement']]
                : [[$request->url(null, 'user'), 'navigation.user']]
        );
        
        // [WIZDAM FIX] Globalisasi roleSettings.
        $journal = $request->getJournal();
        if ($journal) {
            $templateMgr->assign('roleSettings', $this->retrieveRoleAssignmentPreferences($journal->getId()));
        } else {
            // Jaring pengaman PHP 8 jika context journal tidak ditemukan
            $templateMgr->assign('roleSettings', ['useLayoutEditors' => 0, 'useCopyeditors' => 0, 'useProofreaders' => 0]);
        }
    }
       
    /**
     * Retrieves a list of special Journal Management settings related to the journal's inclusion of individual copyeditors, layout editors, and proofreaders.
     * @param int $journalId Journal ID of the journal from which the settings will be obtained
     * @return array
     */    
    public function retrieveRoleAssignmentPreferences($journalId) {
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalSettings = $journalSettingsDao->getJournalSettings($journalId);
        $returner = ['useLayoutEditors' => 0, 'useCopyeditors' => 0, 'useProofreaders' => 0];

        foreach($returner as $specific => $value) {
            if (isset($journalSettings[$specific])) {
                if ($journalSettings[$specific]) {
                    $returner[$specific] = 1;
                }
            }
        }
        return $returner;
    }
}

?>
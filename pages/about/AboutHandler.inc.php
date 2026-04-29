<?php
declare(strict_types=1);

/**
 * @file pages/about/AboutHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutHandler
 * @ingroup pages_editor
 *
 * @brief Handle requests for editor functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.handler.Handler');

class AboutHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AboutHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::AboutHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display about index page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager();
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journalPath = $request->getRequestedJournalPath();

        if ($journalPath != 'index' && $journalDao->journalExistsByPath($journalPath)) {
            $journal = $request->getJournal();

            $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('journalSettings', $journalSettingsDao->getJournalSettings($journal->getId()));

            $customAboutItems = $journalSettingsDao->getSetting($journal->getId(), 'customAboutItems');
            if (isset($customAboutItems[AppLocale::getLocale()])) {
                $templateMgr->assign('customAboutItems', $customAboutItems[AppLocale::getLocale()]);
            } elseif (isset($customAboutItems[AppLocale::getPrimaryLocale()])) {
                $templateMgr->assign('customAboutItems', $customAboutItems[AppLocale::getPrimaryLocale()]);
            }

            foreach ($this->_getPublicStatisticsNames() as $name) {
                if ($journal->getSetting($name)) {
                    $templateMgr->assign('publicStatisticsEnabled', true);
                    break;
                } 
            }
            
            // Hide membership if the payment method is not configured
            import('classes.payment.ojs.OJSPaymentManager');
            $paymentManager = new OJSPaymentManager($request);
            $templateMgr->assign('paymentConfigured', $paymentManager->isConfigured());

            if ($journal->getSetting('boardEnabled')) {
                $groupDao = DAORegistry::getDAO('GroupDAO');
                $groups = $groupDao->getGroups(ASSOC_TYPE_JOURNAL, $journal->getId(), GROUP_CONTEXT_PEOPLE);
                // [WIZDAM] Removed assign_by_ref
                $templateMgr->assign('peopleGroups', $groups);
            }

            $templateMgr->assign('helpTopicId', 'user.about');
            $templateMgr->display('about/index.tpl');
        } else {
            $site = $request->getSite();
            $about = $site->getLocalizedAbout();
            $templateMgr->assign('about', $about);

            $journals = $journalDao->getJournals(true);
            // [WIZDAM] Removed assign_by_ref
            $templateMgr->assign('journals', $journals);
            $templateMgr->display('about/site.tpl');
        }
    }


    /**
     * Setup common template variables.
     * @param bool $subclass set to true if caller is below this handler in the hierarchy
     */
    public function setupTemplate($subclass = false) {
        parent::setupTemplate();
        $templateMgr = TemplateManager::getManager();
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_CORE_MANAGER);

        if (!$journal || !$journal->getSetting('restrictSiteAccess')) {
            $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        }
        
        if ($subclass) {
            $templateMgr->assign('pageHierarchy', [[$request->url(null, 'about'), 'about.aboutTheJournal']]);
        }
        
        // [WIZDAM] CORE INJECTION: Global Navigation Data
        // Memastikan data dropdown menu "Membership" (context = 2) 
        // otomatis tersedia (di-assign) di seluruh halaman About.
        // ==========================================================
        if ($journal) {
            $journalId = (int) $journal->getId();
            $groupDao = DAORegistry::getDAO('GroupDAO');
            
            $templateMgr->assign(array(
                'hasDisplayMembership' => $groupDao->hasDisplayMembershipGroups($journalId),
                'displayMembershipGroups' => $groupDao->getDisplayMembershipGroupsData($journalId)
            ));
        }
    }

    /**
     * Display contact page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function contact($args = [], $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);
        $site = $request->getSite();

        // CABANG LOGIKA: JURNAL vs SITE (PUBLISHER)
        if ($journal) {
            // --- A. LEVEL JURNAL ---
            // Aktifkan satpam jurnal dan validasi
            $this->addCheck(new HandlerValidatorJournal($this));
            $this->validate();
            $this->setupTemplate(true);

            // Ambil data spesifik Jurnal
            $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
            $journalSettings = $journalSettingsDao->getJournalSettings($journal->getId());
            
            $templateMgr->assign([
                'journalSettings' => $journalSettings,
                'isSiteLevel'     => false // Penanda untuk Smarty .tpl
            ]);
            
        } else {
            // --- B. LEVEL SITE / PUBLISHER ROOT ---
            // Lewati HandlerValidatorJournal karena kita tidak butuh jurnal di sini
            $this->validate();
            $this->setupTemplate(true);
            
            $templateMgr->assign('isSiteLevel', true); // Penanda untuk Smarty .tpl
        }

        // [WIZDAM] Injeksi data Global Site (Principal Contact) langsung dari Micro-Payload SiteDAO
        $templateMgr->assign([
            'sitePrincipalContactName'  => $site->getLocalizedData('contactName'),
            'sitePrincipalContactEmail' => $site->getLocalizedData('contactEmail'),
            'siteMailingAddress'        => $site->getLocalizedData('contactMailingAddress')
        ]);

        $templateMgr->display('about/contact.tpl');
    }

    /**
     * Display editorialTeam page.
     */
    public function editorialTeam() {
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        // [WIZDAM] Ambil template manager dengan request instance
        $templateMgr = TemplateManager::getManager($request);

        // [WIZDAM] 2. Context-Aware Controller (Logika Percabangan Konteks)
        if ($journal) {
            $this->addCheck(new HandlerValidatorJournal($this));
            $this->validate();
            $this->setupTemplate(true);

            $templateMgr->assign('isSiteLevel', false);
            
            // [WIZDAM] 6. PHP 8 Strictness: Explicit Type Casting
            $journalId = (int) $journal->getId();

            $countryDao = DAORegistry::getDAO('CountryDAO');
            $countries = $countryDao->getCountries();
            // [WIZDAM] Removed assign_by_ref, gunakan assign standar
            $templateMgr->assign('countries', $countries);

            if ($journal->getSetting('boardEnabled') != true) {
                // Don't use the Editorial Team feature. Generate
                // Editorial Team information using Role info.
                $roleDao = DAORegistry::getDAO('RoleDAO');

                $editorsIterator = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $journalId);
                $editors = $editorsIterator->toArray();

                // =========================================================
                // [WIZDAM] INJEKSI LOGIKA: Pisahkan EIC dan Regular Editor
                // =========================================================
                $editorInChiefs = [];
                $regularEditors = [];

                if (!empty($editors) && is_iterable($editors)) {
                    foreach ($editors as $editor) {
                        // [WIZDAM] 6. Validasi objek skalar untuk PHP 8 Strictness
                        if (!is_object($editor) || !method_exists($editor, 'getId')) {
                            continue;
                        }

                        $userId = (int) $editor->getId();
                        // Cek role Journal Manager (ROLE_ID_JOURNAL_MANAGER)
                        $isManager = $roleDao->userHasRole($journalId, $userId, ROLE_ID_JOURNAL_MANAGER);

                        if ($isManager) {
                            $editorInChiefs[] = $editor;
                        } else {
                            $regularEditors[] = $editor;
                        }
                    }
                }
                // =========================================================

                // Array-kan semua iterator untuk role lain
                $sectionEditors = $roleDao->getUsersByRoleId(ROLE_ID_SECTION_EDITOR, $journalId)->toArray();
                $layoutEditors = $roleDao->getUsersByRoleId(ROLE_ID_LAYOUT_EDITOR, $journalId)->toArray();
                $copyEditors = $roleDao->getUsersByRoleId(ROLE_ID_COPYEDITOR, $journalId)->toArray();
                $proofreaders = $roleDao->getUsersByRoleId(ROLE_ID_PROOFREADER, $journalId)->toArray();

                // [WIZDAM] 3. Strict MVC & Micro-Payloads
                // Lempar ke View menggunakan satu array payload yang bersih
                $templateMgr->assign([
                    'editors'        => $editors, // Dipertahankan untuk backward compatibility (jika masih dipanggil loop lama)
                    'editorInChiefs' => $editorInChiefs,
                    'regularEditors' => $regularEditors,
                    'sectionEditors' => $sectionEditors,
                    'layoutEditors'  => $layoutEditors,
                    'copyEditors'    => $copyEditors,
                    'proofreaders'   => $proofreaders
                ]);
                
                $templateMgr->display('about/editorialTeam.tpl');

            } else {
                // The Editorial Team feature has been enabled.
                // Generate information using Group data.
                $groupDao = DAORegistry::getDAO('GroupDAO');
                $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');

                $allGroups = $groupDao->getGroups(ASSOC_TYPE_JOURNAL, $journalId, GROUP_CONTEXT_EDITORIAL_TEAM);
                $teamInfo = [];
                $groups = [];
                
                while ($group = $allGroups->next()) {
                    if (!$group->getAboutDisplayed()) continue;
                    
                    $memberships = [];
                    $groupId = (int) $group->getId();
                    $allMemberships = $groupMembershipDao->getMemberships($groupId);
                    
                    while ($membership = $allMemberships->next()) {
                        if (!$membership->getAboutDisplayed()) continue;
                        $memberships[] = $membership;
                        unset($membership);
                    }
                    
                    if (!empty($memberships)) {
                        $groups[] = $group;
                    }
                    
                    $teamInfo[$groupId] = $memberships;
                    unset($group);
                }

                // [WIZDAM] Micro-Payloads
                $templateMgr->assign([
                    'groups'   => $groups,
                    'teamInfo' => $teamInfo
                ]);
                $templateMgr->display('about/editorialTeamBoard.tpl');
            }

        } else {
            // [WIZDAM] 2. Logika Publisher Root (Level Site)
            // Bypass validasi jurnal untuk menampilkan Publisher Board
            $this->validate();
            $this->setupTemplate(true);
            
            $templateMgr->assign('isSiteLevel', true);
            
            // Inisialisasi DAO yang dibutuhkan
            $roleDao = DAORegistry::getDAO('RoleDAO');
            $siteDao = DAORegistry::getDAO('SiteDAO');
            
            $site = $siteDao->getSite();
            
            // OJS menggunakan ID 0 untuk entitas level Site (sering disebut CONTEXT_ID_NONE)
            $siteContextId = 0; 

            // Ambil semua pengguna yang memiliki Role "Site Administrator" (ID: 1)
            // Di ScholarWizdam, kita proyeksikan mereka sebagai "Publisher Board / Steering Committee"
            $siteAdminsIterator = $roleDao->getUsersByRoleId(ROLE_ID_SITE_ADMIN, $siteContextId);
            
            // [WIZDAM] 6. PHP 8 Strictness: Pastikan iterator valid sebelum di-cast ke array
            $publisherBoard = [];
            if ($siteAdminsIterator) {
                $publisherBoard = $siteAdminsIterator->toArray();
            }

            // Ambil informasi tambahan dari Site Settings (jika Anda membuat custom setting)
            // Cast sebagai (string) agar view tidak menerima null
            $publisherTeamDescription = (string) $site->getLocalizedSetting('aboutField') ?: 'Welcome to our Publisher Editorial Board.';
            $siteTitle = (string) $site->getLocalizedTitle();

            // [WIZDAM] 3. Strict MVC & Micro-Payloads
            // Lempar data ke view dalam satu gerbong array
            $templateMgr->assign([
                'publisherBoard'           => $publisherBoard,
                'publisherTeamDescription' => $publisherTeamDescription,
                'siteTitle'                => $siteTitle
            ]);
            
            // Load template khusus Site Level
            $templateMgr->display('about/leadership.tpl');
        }
    }

    /**
     * Display group info for a particular group.
     * @param array $args
     */
    public function displayMembership($args) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();
        $groupId = (int) array_shift($args);

        $groupDao = DAORegistry::getDAO('GroupDAO');
        $group = $groupDao->getById($groupId);

        if (!$journal || !$group ||
            $group->getContext() != GROUP_CONTEXT_PEOPLE ||
            $group->getAssocType() != ASSOC_TYPE_JOURNAL ||
            $group->getAssocId() != $journal->getId()
        ) {
            $request->redirect(null, 'about');
        }

        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
        $allMemberships = $groupMembershipDao->getMemberships($group->getId());
        $memberships = [];
        while ($membership = $allMemberships->next()) {
            if (!$membership->getAboutDisplayed()) continue;
            $memberships[] = $membership;
            unset($membership);
        }

        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('countries', $countries);

        $templateMgr->assign('group', $group);
        $templateMgr->assign('memberships', $memberships);
        $templateMgr->display('about/displayMembership.tpl');
    }

    /**
     * Display a biography for an editorial team member.
     * @param array $args
     */
    public function editorialTeamBio($args) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $journal = Application::get()->getRequest()->getJournal();

        $templateMgr = TemplateManager::getManager();

        $userId = isset($args[0]) ? (int) $args[0] : 0;

        $user = null;
        if ($journal->getSetting('boardEnabled') != true) {
            $roles = $roleDao->getRolesByUserId($userId, $journal->getId());
            $acceptableRoles = [
                ROLE_ID_EDITOR,
                ROLE_ID_SECTION_EDITOR,
                ROLE_ID_LAYOUT_EDITOR,
                ROLE_ID_COPYEDITOR,
                ROLE_ID_PROOFREADER
            ];
            foreach ($roles as $role) {
                $roleId = $role->getRoleId();
                if (in_array($roleId, $acceptableRoles)) {
                    $userDao = DAORegistry::getDAO('UserDAO');
                    $user = $userDao->getById($userId);
                    break;
                }
            }

            // Currently we always publish emails in this mode.
            $publishEmail = true;
        } else {
            $groupDao = DAORegistry::getDAO('GroupDAO');
            $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');

            $allGroups = $groupDao->getGroups(ASSOC_TYPE_JOURNAL, $journal->getId());
            $publishEmail = false;
            while ($group = $allGroups->next()) {
                if (!$group->getAboutDisplayed()) continue;
                $allMemberships = $groupMembershipDao->getMemberships($group->getId());
                while ($membership = $allMemberships->next()) {
                    if (!$membership->getAboutDisplayed()) continue;
                    $potentialUser = $membership->getUser();
                    if ($potentialUser->getId() == $userId) {
                        $user = $potentialUser;
                        if ($group->getPublishEmail()) $publishEmail = true;
                    }
                    unset($membership);
                }
                unset($group);
            }
        }

        if (!$user) {
            Application::get()->getRequest()->redirect(null, 'about', 'editorialTeam');
        }

        $countryDao = DAORegistry::getDAO('CountryDAO');
        if ($user && $user->getCountry() != '') {
            $country = $countryDao->getCountry($user->getCountry());
            $templateMgr->assign('country', $country);
        }
        
        // [WIZDAM] CORE INJECTION: Resolve User Membership Title
        $userMembership = $this->_getUserMembershipContext($journal, $user);
        $templateMgr->assign('userMembership', $userMembership);

        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('user', $user);
        $templateMgr->assign('publishEmail', $publishEmail);
        $templateMgr->display('about/editorialTeamBio.tpl');
    }

    /**
     * Display editorialPolicies page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editorialPolicies($args, $request = null) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $journal = $request->getJournal();
        
        // --- MODIFIKASI 1: Siapkan DAOs dan Locale ---
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $userDao = DAORegistry::getDAO('UserDAO'); // Kita membutuhkan ini
        $primaryLocale = $journal->getPrimaryLocale();
        if (empty($primaryLocale)) {
                $primaryLocale = AppLocale::getLocale();
        }
        // --- AKHIR MODIFIKASI 1 ---

        $templateMgr = TemplateManager::getManager();
        $sections = $sectionDao->getJournalSections($journal->getId());
        $sections = $sections->toArray();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('sections', $sections); 

        // --- MODIFIKASI UTAMA: Mengirim data yang persis diharapkan TPL ---
        $sectionEditorEntriesBySection = [];
        foreach ($sections as $section) {
            // Ini mengembalikan array dari array: [ 0 => ['user' => (object)], 1 => ['user' => (object)] ]
            $sectionEditorEntriesArray = $sectionEditorsDao->getEditorsBySectionId($journal->getId(), $section->getId());
            
            $richEditorData = []; // Array baru untuk data kaya

            // Loop melalui setiap $entryArray (yaitu: ['user' => (object)])
            foreach ($sectionEditorEntriesArray as $entryArray) {
                
                if (!isset($entryArray['user']) || !is_object($entryArray['user'])) continue;
                $sectionEditorObject = $entryArray['user']; // Objek SectionEditor Asli
                $userId = $sectionEditorObject->getId();
                $user = $userDao->getById($userId);
                
                if (!$user) continue; 

                // Ambil Afiliasi (sebagai String, seperti {php} asli)
                $affiliationData = $user->getAffiliation($primaryLocale);
                if (is_array($affiliationData)) {
                    $affiliationData = implode("\n", $affiliationData);
                }

                // Ambil Nama Negara (sebagai String)
                $countryCode = $user->getCountry();
                $countryName = '';
                if (!empty($countryCode)) {
                        $countryName = $countryDao->getCountry($countryCode, $primaryLocale);
                        if (empty($countryName)) $countryName = $countryDao->getCountry($countryCode, 'en_US');
                }

                // Masukkan data dengan NAMA KUNCI (KEY) YANG DIHARAPKAN OLEH .TPL
                $richEditorData[] = [
                        'user' => $sectionEditorObject, // Ini adalah $sectionEditorEntry.user
                        'affiliationString' => $affiliationData, // Ini akan menjadi $editorAffiliation
                        'countryString' => $countryName       // Ini akan menjadi $editorCountry
                ];
            }
            $sectionEditorEntriesBySection[$section->getId()] = $richEditorData;
        }
        $templateMgr->assign('sectionEditorEntriesBySection', $sectionEditorEntriesBySection);
        // --- AKHIR MODIFIKASI UTAMA ---

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $templateMgr->assign('paymentConfigured', $paymentManager->isConfigured());

        $templateMgr->display('about/editorialPolicies.tpl');
    }

    /**
     * Display subscriptions page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function subscriptions($args, $request = null) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        // [WIZDAM FIX] Blokir akses URL langsung ke halaman subscriptions
        $journal = $request->getJournal();
        if ($journal->getSetting('publishingMode') != PUBLISHING_MODE_SUBSCRIPTION) {
            $request->redirect(null, 'about');
            return;
        }

        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');

        $journal = $request->getJournal();
        $journalId = $journal->getId();

        $subscriptionName = $journalSettingsDao->getSetting($journalId, 'subscriptionName');
        $subscriptionEmail = $journalSettingsDao->getSetting($journalId, 'subscriptionEmail');
        $subscriptionPhone = $journalSettingsDao->getSetting($journalId, 'subscriptionPhone');
        $subscriptionFax = $journalSettingsDao->getSetting($journalId, 'subscriptionFax');
        $subscriptionMailingAddress = $journalSettingsDao->getSetting($journalId, 'subscriptionMailingAddress');
        $subscriptionAdditionalInformation = $journal->getLocalizedSetting('subscriptionAdditionalInformation');
        $individualSubscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, false, false);
        $institutionalSubscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, true, false);

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptGiftSubscriptionPayments = $paymentManager->acceptGiftSubscriptionPayments();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('subscriptionName', $subscriptionName);
        $templateMgr->assign('subscriptionEmail', $subscriptionEmail);
        $templateMgr->assign('subscriptionPhone', $subscriptionPhone);
        $templateMgr->assign('subscriptionFax', $subscriptionFax);
        $templateMgr->assign('subscriptionMailingAddress', $subscriptionMailingAddress);
        $templateMgr->assign('subscriptionAdditionalInformation', $subscriptionAdditionalInformation);
        $templateMgr->assign('acceptGiftSubscriptionPayments', $acceptGiftSubscriptionPayments);
        $templateMgr->assign('individualSubscriptionTypes', $individualSubscriptionTypes);
        $templateMgr->assign('institutionalSubscriptionTypes', $institutionalSubscriptionTypes);
        
        $templateMgr->display('about/subscriptions.tpl');
    }

    /**
     * Display memberships page.
     * @param array $args
     * @param PKPRequest $request
     */
    public function memberships($args, $request = null) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $journal = $request->getJournal();

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);

        $membershipEnabled = $paymentManager->membershipEnabled();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('membershipEnabled', $membershipEnabled);      
        if ( $membershipEnabled ) {
            $membershipFee  = $journal->getSetting('membershipFee');
            $membershipFeeName = $journal->getLocalizedSetting('membershipFeeName');
            $membershipFeeDescription = $journal->getLocalizedSetting('membershipFeeDescription');
            $currency = $journal->getSetting('currency');

            $templateMgr->assign('membershipFee', $membershipFee);
            $templateMgr->assign('currency', $currency);
            $templateMgr->assign('membershipFeeName', $membershipFeeName);
            $templateMgr->assign('membershipFeeDescription', $membershipFeeDescription);
            $templateMgr->display('about/memberships.tpl');
            return;
        }       
        $request->redirect(null, 'about');
    }

    /**
     * Display submissions page.
     */
    public function submissions() {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        $journalDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journal = Application::get()->getRequest()->getJournal();

        $templateMgr = TemplateManager::getManager();
        $journalSettings = $journalDao->getJournalSettings($journal->getId());
        $submissionChecklist = $journal->getLocalizedSetting('submissionChecklist');
        if (!empty($submissionChecklist)) {
            ksort($submissionChecklist);
            reset($submissionChecklist);
        }
        $templateMgr->assign('submissionChecklist', $submissionChecklist);
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('journalSettings', $journalSettings);
        $templateMgr->assign('helpTopicId','submission.authorGuidelines');
        $templateMgr->display('about/submissions.tpl');
    }

    /**
     * Display Journal Sponsorship page.
     */
    public function sponsorship() {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        $journal = Application::get()->getRequest()->getJournal();

        $templateMgr = TemplateManager::getManager();
        // [WIZDAM] Removed assign_by_ref calls
        $templateMgr->assign('publisherInstitution', $journal->getSetting('publisherInstitution'));
        $templateMgr->assign('publisherUrl', $journal->getSetting('publisherUrl'));
        $templateMgr->assign('publisherNote', $journal->getLocalizedSetting('publisherNote'));
        $templateMgr->assign('contributorNote', $journal->getLocalizedSetting('contributorNote'));
        $templateMgr->assign('contributors', $journal->getSetting('contributors'));
        $templateMgr->assign('sponsorNote', $journal->getLocalizedSetting('sponsorNote'));
        $templateMgr->assign('sponsors', $journal->getSetting('sponsors'));
        $templateMgr->display('about/journalSponsorship.tpl');
    }

    /**
     * Display siteMap page.
     */
    public function sitemap() {
        $this->validate();
        $this->setupTemplate(true);

        $templateMgr = TemplateManager::getManager();

        $journalDao = DAORegistry::getDAO('JournalDAO');

        $user = Application::get()->getRequest()->getUser();
        $roleDao = DAORegistry::getDAO('RoleDAO');

        if ($user) {
            $rolesByJournal = [];
            $journals = $journalDao->getJournals(true);
            // Fetch the user's roles for each journal
            foreach ($journals->toArray() as $journal) {
                $roles = $roleDao->getRolesByUserId($user->getId(), $journal->getId());
                if (!empty($roles)) {
                    $rolesByJournal[$journal->getId()] = $roles;
                }
            }
        }

        $journals = $journalDao->getJournals(true);
        $templateMgr->assign('journals', $journals->toArray());
        if (isset($rolesByJournal)) {
            $templateMgr->assign('rolesByJournal', $rolesByJournal);
        }
        if ($user) {
            $templateMgr->assign('isSiteAdmin', $roleDao->getRole(0, $user->getId(), ROLE_ID_SITE_ADMIN));
        }

        $templateMgr->display('about/sitemap.tpl');
    }

    /**
     * Display journal history.
     */
    public function history() {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        $journal = Application::get()->getRequest()->getJournal();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('history', $journal->getLocalizedSetting('history'));
        $templateMgr->display('about/history.tpl');
    }

    /**
     * Menangkap URL lama 'aboutThisPublishingSystem' dan mengalihkannya 
     * (redirect) ke lokasi yang baru dengan 301 (Moved Permanently) untuk SEO.
     * @param array $args
     * @param PKPRequest $request
     */
    public function aboutThisPublishingSystem($args, $request = null) {
        $this->validate();
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $journal = $request->getJournal();

        if ($journal) {
            // == KONTEKS JURNAL ==
            // Alihkan (301) ke halaman 'insight' baru
            $targetUrl = $request->url(null, null, 'insights'); 
            header("Location: $targetUrl", true, 301);
            exit();

        } else {
            // == KONTEKS SITUS ==
            $baseUrl = $request->getBaseUrl();
            header("Location: $baseUrl", true, 301);
            exit();
        }
    }
    
    /**
     * Display Journal Insight page.
     * HANYA UNTUK KONTEKS JURNAL.
     * @param array $args
     * @param PKPRequest $request
     */
    public function insights($args, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Validasi ini memastikan kita HANYA berada di dalam konteks jurnal
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true); // 'true' untuk breadcrumbs
    
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager();
        
        // Kirim variabel 'currentJournal' agar bisa digunakan di template
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('currentJournal', $journal);
        
        // Panggil template baru
        $templateMgr->display('about/insights.tpl');
    }

    /**
     * Menampilkan halaman Statistik Kustom (Versi modernisasi).
     * @param array $args
     * @param PKPRequest $request
     */
    public function statistics($args, $request = null) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true); // Setup template (breadcrumb, etc)

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // 1. LOGIKA REDIRECT
        $statisticsYear = $request->getUserVar('statisticsYear');
        
        if ($statisticsYear) {
            $targetUrl = $request->url(null, null, 'statistics');
            header("Location: $targetUrl", true, 301);
            exit(); 
        }

        // 2. Inisialisasi Objek Utama
        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);

        // 3. --- MULAI BLOK PKPWizdamStats ---
        import('lib.pkp.classes.core.PKPWizdamStats');
        $refreshStats = $request->getUserVar('refresh_stats');
        $forceRefresh = trim((string) $refreshStats) == 'true';

        try {
            // Panggil mesin statistik utama
            $journalStats = PKPWizdamStats::getStats($journal->getId(), $forceRefresh);
            
            if (is_array($journalStats) && !isset($journalStats['error'])) {
                // Kirim SEMUA data statistik ke template
                foreach ($journalStats as $key => $value) {
                    $templateMgr->assign($key, $value);
                }

                // Buat dan kirim $jsonPath
                $journalId = $journal->getId();
                $basePath = $request->getBasePath();
                $jsonPath = $basePath . '/public/wizdam_cache/stats/journal_' . $journalId . '_stats.json.gz';
                $templateMgr->assign('statsJsonPath', $jsonPath);

            } else {
                 $templateMgr->assign('statsError', 'Data statistik tidak valid.');
                 $templateMgr->assign('statsJsonPath', ''); 
            }
        } catch (Exception $e) { 
            if (Config::getVar('debug', 'log_errors')) {
                error_log('WizdamStats (Handler): Exception loading PKPWizdamStats for Statistics Page: ' . $e->getMessage());
            }
            $templateMgr->assign('statsError', 'Gagal memuat statistik jurnal.');
            $templateMgr->assign('statsJsonPath', '');
        }
        // --- AKHIR BLOK PKPWizdamStats ---

        $templateMgr->assign('helpTopicId','user.about'); 
        $templateMgr->display('about/statistics.tpl');
    }

    /**
     * Mengembalikan daftar nama statistik yang diakses publik halaman statistik
     * @see StatisticsHandler::_getPublicStatisticsNames()
     * @return array
     */
    public function _getPublicStatisticsNames() {
        import ('pages.manager.ManagerHandler');
        import ('pages.manager.StatisticsHandler');
        // Note: _getPublicStatisticsNames is protected in StatisticsHandler refactor.
        // If strict mode prevents access, this needs adaptation. 
        // For now assuming we can access or reflect it, or simply duplicate the list.
        // Duplicating list for safety in strict context:
        return [
            'statNumPublishedIssues',
            'statItemsPublished',
            'statNumSubmissions',
            'statPeerReviewed',
            'statCountAccept',
            'statCountDecline',
            'statCountRevise',
            'statDaysPerReview',
            'statDaysToPublication',
            'statRegisteredUsers',
            'statRegisteredReaders',
            'statSubscriptions',
        ];
    }
    
    /**
     * Menampilkan halaman statis penerbit (Misi).
     * HANYA KONTEKS SITUS.
     * @param array $args
     * @param PKPRequest $request
     */
    public function mission($args, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if ($request->getJournal()) { 
            $request->redirect(null, 'about'); 
            return; 
        }
        $this->setupTemplate(true);
        $templateMgr = TemplateManager::getManager();
        $site = $request->getSite();
        
        $templateMgr->assign('pageTitleKey', 'about.mission');
        $templateMgr->assign('pageContent', $site->getLocalizedSetting('publisherMission'));
        
        $templateMgr->display('about/publisherPage.tpl');
    }

    /**
     * Menampilkan halaman statis penerbit (Sejarah).
     * HANYA KONTEKS SITUS.
     * @param array $args
     * @param PKPRequest $request
     */
    public function publisherHistory($args, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if ($request->getJournal()) { 
            $request->redirect(null, 'about'); 
            return; 
        }
        $this->setupTemplate(true);
        $templateMgr = TemplateManager::getManager();
        $site = $request->getSite();
        
        $templateMgr->assign('pageTitleKey', 'about.history');
        $templateMgr->assign('pageContent', $site->getLocalizedSetting('publisherHistory'));
        
        $templateMgr->display('about/publisherPage.tpl');
    }

    /**
     * Menampilkan halaman statis penerbit (Kepemimpinan).
     * HANYA KONTEKS SITUS.
     * @param array $args
     * @param PKPRequest $request
     */
    public function leaderships($args, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if ($request->getJournal()) { 
            $request->redirect(null, 'about'); 
            return; 
        }
        $this->setupTemplate(true);
        $templateMgr = TemplateManager::getManager();
        $site = $request->getSite();
        
        $templateMgr->assign('pageTitleKey', 'about.leaderships');
        $templateMgr->assign('pageContent', $site->getLocalizedSetting('publisherLeaderships'));
        
        $templateMgr->display('about/publisherPage.tpl');
    }

    /**
     * Menampilkan halaman statis penerbit (Penghargaan).
     * HANYA KONTEKS SITUS.
     * @param array $args
     * @param PKPRequest $request
     */
    public function award($args, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        if ($request->getJournal()) { 
            $request->redirect(null, 'about'); 
            return; 
        }
        $this->setupTemplate(true);
        $templateMgr = TemplateManager::getManager();
        $site = $request->getSite();
        
        $templateMgr->assign('pageTitleKey', 'about.awards');
        $templateMgr->assign('pageContent', $site->getLocalizedSetting('publisherAwards'));
        
        $templateMgr->display('about/publisherPage.tpl');
    }
    
    /**
     * [WIZDAM] LOGIKA UTAMA: Menentukan keanggotaan pengguna.
     * Disesuaikan untuk mendeteksi variabel 'boardEnabled'.
     * @param Journal $journal
     * @param User $user
     * @return string Judul keanggotaan yang sesuai atau default
     */
    private function _getUserMembershipContext($journal, $user) {
        $defaultTitle = __('about.editorialTeam');
        if (!$journal || !$user) return $defaultTitle;
        
        // Memanggil request secara statis menyesuaikan OJS versi ini
        $request = Application::get()->getRequest();
        $journalId = (int) $journal->getId();
        $userId = (int) $user->getId();
        $contextFrom = $request->getUserVar('from');
        
        // Menggunakan boardEnabled sesuai dengan core Anda
        $boardEnabled = (bool) $journal->getSetting('boardEnabled');
        $userMembership = '';
        
        if ($contextFrom == 'membership') {
             $userMembership = $this->_getGroupMembershipTitle($journalId, $userId, 2);
             if (empty($userMembership)) $userMembership = __('user.role.member');
        } elseif ($boardEnabled || $contextFrom == 'board') {
            $customTitle = $this->_getGroupMembershipTitle($journalId, $userId, 1);
            $userMembership = !empty($customTitle) ? $customTitle : $defaultTitle;
        } else {
            $userMembership = $this->_getRoleMembershipWithLocale($journalId, $userId);
        }
        
        return !empty($userMembership) ? $userMembership : $defaultTitle;
    }

    /**
     * [WIZDAM] Helper Mode 1: Mengambil Judul Kustom dari DAO Group.
     * @param int $journalId
     * @param int $userId
     * @param int $context 1 untuk Board, 2 untuk Membership
     * @return string Judul keanggotaan yang sesuai atau kosong
     */
    private function _getGroupMembershipTitle($journalId, $userId, $context) {
        $groupDao = DAORegistry::getDAO('GroupDAO');
        return $groupDao->getMembershipTitleByUser($journalId, $userId, (int) $context);
    }

    /**
     * [WIZDAM] Helper Mode 2: Peran standar OJS dengan Locale.
     * @param int $journalId
     * @param int $userId
     * @return string Judul keanggotaan berdasarkan peran atau kosong
     */
    private function _getRoleMembershipWithLocale($journalId, $userId) {
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roles = $roleDao->getRolesByUserId($userId, $journalId);
        
        $userRoles = array();
        foreach ($roles as $role) {
            $userRoles[] = $role->getRoleId();
        }
        
        if (in_array(ROLE_ID_JOURNAL_MANAGER, $userRoles)) {
            return __('user.role.editorInChief'); 
        }
        
        $roleLocales = array(
            ROLE_ID_EDITOR => 'user.role.editor',
            ROLE_ID_SECTION_EDITOR => 'user.role.sectionEditor',
            ROLE_ID_LAYOUT_EDITOR => 'user.role.layoutEditor',
            ROLE_ID_COPYEDITOR => 'user.role.copyeditor',
            ROLE_ID_PROOFREADER => 'user.role.proofreader',
            ROLE_ID_AUTHOR => 'user.role.author',
            ROLE_ID_REVIEWER => 'user.role.reviewer'
        );
        
        $editorialPriority = array(ROLE_ID_EDITOR, ROLE_ID_SECTION_EDITOR, ROLE_ID_LAYOUT_EDITOR, ROLE_ID_COPYEDITOR, ROLE_ID_PROOFREADER);
        
        foreach ($editorialPriority as $roleId) {
            if (in_array($roleId, $userRoles) && isset($roleLocales[$roleId])) {
                return __($roleLocales[$roleId]);
            }
        }
        
        if (!empty($userRoles)) {
            $firstRole = $userRoles[0];
            if (isset($roleLocales[$firstRole])) {
                return __($roleLocales[$firstRole]);
            }
        }
        
        return '';
    }
}
?>
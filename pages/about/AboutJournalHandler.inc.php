<?php
declare(strict_types=1);

/**
 * @file pages/about/AboutJournalHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutJournalHandler
 * @ingroup pages_about
 *
 * @brief Handler KHUSUS level Jurnal -- terpisah total dari
 * AboutPublisherHandler (konteks Site/Penerbit). Hasil split dari
 * AboutHandler lama yang sebelumnya menangani kedua konteks sekaligus
 * lewat percabangan `if ($journal) {...} else {...}`, menyebabkan
 * breadcrumb, page title, dan template ikut tertukar antar konteks.
 * Tidak ada satupun method di sini yang boleh menambahkan cabang untuk
 * konteks site/publisher lagi -- taruh di AboutPublisherHandler.
 *
 */

import('pages.about.AboutHandler');

class AboutJournalHandler extends AboutHandler {

    /**
     * Display contact page (Journal).
     * HANYA UNTUK KONTEKS JURNAL. Versi Site/Publisher ada di
     * AboutPublisherHandler::contact().
     * @param array $args
     * @param PKPRequest $request
     */
    public function contact($args = [], $request = null) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);
        $site = $request->getSite();

        // Ambil data spesifik Jurnal
        /** @var JournalSettingsDAO $journalSettingsDao */
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalSettings = $journalSettingsDao->getJournalSettings($journal->getId());

        $templateMgr->assign('journalSettings', $journalSettings);
        $templateMgr->assign([
            'sitePrincipalContactName'  => $site->getLocalizedData('contactName'),
            'sitePrincipalContactEmail' => $site->getLocalizedData('contactEmail'),
        ]);

        $templateMgr->display('about/contact.tpl');
    }

    /**
     * Display editorialTeam page.
     */
    public function editorialTeam() {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        $this->setupTemplate(true);

        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        // [WIZDAM] Ambil template manager dengan request instance
        $templateMgr = TemplateManager::getManager($request);

        // [WIZDAM] 6. PHP 8 Strictness: Explicit Type Casting
        $journalId = (int) $journal->getId();

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();
        $templateMgr->assign('countries', $countries);

        if ($journal->getSetting('boardEnabled') != true) {
                // Don't use the Editorial Team feature. 
                // Generate Editorial Team information using Role info.
                /** @var RoleDAO $roleDao */
                $roleDao = DAORegistry::getDAO('RoleDAO');
                $editorsIterator = $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $journalId);
                $editors = $editorsIterator->toArray();

                // =========================================================
                // [WIZDAM] INJEKSI LOGIKA: Pisahkan EIC dan Regular Editor
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
                /** @var GroupDAO $groupDao */
                $groupDao = DAORegistry::getDAO('GroupDAO');
                /** @var GroupMembershipDAO $groupMembershipDao */
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
        /** @var GroupDAO $groupDao */
        $groupDao = DAORegistry::getDAO('GroupDAO');
        $group = $groupDao->getById($groupId);

        if (!$journal || !$group ||
            $group->getContext() != GROUP_CONTEXT_PEOPLE ||
            $group->getAssocType() != ASSOC_TYPE_JOURNAL ||
            $group->getAssocId() != $journal->getId()
        ) {
            $request->redirect(null, 'about');
        }

        /** @var GroupMembershipDAO $groupMembershipDao */
        $groupMembershipDao = DAORegistry::getDAO('GroupMembershipDAO');
        $allMemberships = $groupMembershipDao->getMemberships($group->getId());
        $memberships = [];
        while ($membership = $allMemberships->next()) {
            if (!$membership->getAboutDisplayed()) continue;
            $memberships[] = $membership;
            unset($membership);
        }

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $countries = $countryDao->getCountries();

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

        /** @var RoleDAO $roleDao */
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
                    /** @var UserDAO $userDao */
                    $userDao = DAORegistry::getDAO('UserDAO');
                    $user = $userDao->getById($userId);
                    break;
                }
            }

            // Currently we always publish emails in this mode.
            $publishEmail = true;
        } else {
            /** @var GroupDAO $groupDao */
            $groupDao = DAORegistry::getDAO('GroupDAO');
            /** @var GroupMembershipDAO $groupMembershipDao */
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

        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        if ($user && $user->getCountry() != '') {
            $country = $countryDao->getCountry($user->getCountry());
            $templateMgr->assign('country', $country);
        }
        
        // [WIZDAM] CORE INJECTION: Resolve User Membership Title
        $userMembership = $this->_getUserMembershipContext($journal, $user);

        $templateMgr->assign('userMembership', $userMembership);
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

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        /** @var SectionEditorsDAO $sectionEditorsDao */
        $sectionEditorsDao = DAORegistry::getDAO('SectionEditorsDAO');
        $journal = $request->getJournal();
        
        // --- MODIFIKASI 1: Siapkan DAOs dan Locale ---
        /** @var CountryDAO $countryDao */
        $countryDao = DAORegistry::getDAO('CountryDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $primaryLocale = $journal->getPrimaryLocale();
        if (empty($primaryLocale)) {
                $primaryLocale = AppLocale::getLocale();
        }
        // --- AKHIR MODIFIKASI 1 ---

        $templateMgr = TemplateManager::getManager();
        $sections = $sectionDao->getJournalSections($journal->getId());
        $sections = $sections->toArray();
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

        /** @var JournalSettingsDAO $journalSettingsDao */
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
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

        /** @var JournalSettingsDAO $journalDao */
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
     * [SHIM] Display journal history.
     * @deprecated Rute op='history' TIDAK mengarah ke sini -- direbut
     * AboutPublisherHandler (lihat pages/about/index.php) untuk halaman
     * History level Penerbit. Method ini dipertahankan sebagai logika inti;
     * dipanggil lewat alias journalHistory() di bawah, yang benar-benar
     * routable lewat op='journal-history'.
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
     * [FIX] Alias routable untuk history() di atas. PKPPageRouter::route()
     * mengubah op 'journal-history' (lihat pages/about/index.php dan link
     * menu navmenu.tpl/navigation.tpl) menjadi nama method camelCase
     * 'journalHistory' sebelum memanggilnya -- method dengan nama itu HARUS
     * benar-benar ada di class ini, karena router tidak pernah memetakan op
     * ke method lain secara otomatis.
     * @param array $args
     * @param Request|null $request
     */
    public function journalHistory($args = [], $request = null) {
        $this->history();
    }

    /**
     * [SHIM] @deprecated Menangkap URL lama 'aboutThisPublishingSystem' dan mengalihkannya 
     * (redirect) ke halaman 'insight' baru dengan 301 (Moved Permanently).
     * HANYA UNTUK KONTEKS JURNAL. Versi Site/Publisher ada di
     * AboutPublisherHandler::aboutThisPublishingSystem().
     * @param array $args
     * @param PKPRequest $request
     */
    public function aboutThisPublishingSystem($args, $request = null) {
        $this->addCheck(new HandlerValidatorJournal($this));
        $this->validate();
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $targetUrl = $request->url(null, null, 'insights');
        header("Location: $targetUrl", true, 301);
        exit();
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

        // 3. --- MULAI BLOK WizdamStats ---
        import('lib.wizdam.statistics.WizdamStats');

        try {
            $journalStats = WizdamStats::getStats($journal->getId());
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
                error_log('WizdamStats (Handler): Exception loading WizdamStats for Statistics Page: ' . $e->getMessage());
            }
            $templateMgr->assign('statsError', 'Gagal memuat statistik jurnal.');
            $templateMgr->assign('statsJsonPath', '');
        }
        // --- AKHIR BLOK WizdamStats ---

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $yearRange = $publishedArticleDao->getArticleYearRange($journal->getId());
        $templateMgr->assign('firstYear', $yearRange[0] ?? '');
        $templateMgr->assign('lastPublicationYear', $yearRange[1] ?? '');
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
    
    //
    // Helpers
    //
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
        
        // Memanggil request secara statis
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
        /** @var GroupDAO $groupDao */
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
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roles = $roleDao->getRolesByUserId($userId, $journalId);
        
        $userRoles = [];
        foreach ($roles as $role) {
            $userRoles[] = $role->getRoleId();
        }
        
        if (in_array(ROLE_ID_JOURNAL_MANAGER, $userRoles)) {
            return __('user.role.editorInChief'); 
        }
        
        $roleLocales = [
            ROLE_ID_EDITOR => 'user.role.editor',
            ROLE_ID_SECTION_EDITOR => 'user.role.sectionEditor',
            ROLE_ID_LAYOUT_EDITOR => 'user.role.layoutEditor',
            ROLE_ID_COPYEDITOR => 'user.role.copyeditor',
            ROLE_ID_PROOFREADER => 'user.role.proofreader',
            ROLE_ID_AUTHOR => 'user.role.author',
            ROLE_ID_REVIEWER => 'user.role.reviewer'
        ];
        
        $editorialPriority = [
            ROLE_ID_EDITOR, 
            ROLE_ID_SECTION_EDITOR, 
            ROLE_ID_LAYOUT_EDITOR, 
            ROLE_ID_COPYEDITOR, 
            ROLE_ID_PROOFREADER
        ];
        
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
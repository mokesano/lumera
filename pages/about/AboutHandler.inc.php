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
 * @ingroup pages_about
 *
 * @brief Handle ops shared BETWEEN journal and publisher/site context
 * (index, contact, sitemap), AND serve as the shared base class
 * (constructor, setupTemplate()) for AboutJournalHandler dan
 * AboutPublisherHandler.
 *
 * [WIZDAM] Sebelumnya class ini (1125 baris) menyimpan SEMUA method --
 * termasuk salinan mati dari method yang sudah punya rumah aktif di
 * AboutJournalHandler dan AboutPublisherHandler -- tidak pernah
 * tereksekusi karena routing (pages/about/index.php) mengarah langsung
 * ke class yang tepat untuk ops itu. Dipangkas jadi HANYA method yang
 * genuinely dipakai bersama.
 *
 * [WIZDAM BUGFIX] Percobaan pemangkasan SEBELUMNYA pakai batas baris yang
 * salah hitung (asumsi "baris method berikutnya minus satu" tanpa
 * verifikasi kurung kurawal aktual) -- meninggalkan fragmen docblock
 * menggantung tanpa method di akhir file (docblock milik history() dan
 * mission() ikut terpotong terbawa, tanpa deklarasi method-nya). Batas
 * sekarang diverifikasi presisi lewat penghitungan kurung kurawal
 * otomatis, bukan tebakan.
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
     * [DEPRECATED] Backward compatibility.
     * Use __construct() instead.
     * @deprecated
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
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journalPath = $request->getRequestedJournalPath();

        if ($journalPath != 'index' && $journalDao->journalExistsByPath($journalPath)) {
            $journal = $request->getJournal();
            /** @var JournalSettingsDAO $journalSettingsDao */
            $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
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
                /** @var GroupDAO $groupDao */
                $groupDao = DAORegistry::getDAO('GroupDAO');
                $groups = $groupDao->getGroups(ASSOC_TYPE_JOURNAL, $journal->getId(), GROUP_CONTEXT_PEOPLE);
                $templateMgr->assign('peopleGroups', $groups);
            }

            $templateMgr->assign('helpTopicId', 'user.about');
            $templateMgr->display('about/index.tpl');
        } else {
            $site = $request->getSite();
            $about = $site->getLocalizedAbout();

            $templateMgr->assign('about', $about);
            $journals = $journalDao->getJournals(true);
            $templateMgr->assign('journals', $journals);

            $templateMgr->display('about/site.tpl');
        }
    }

    /**
     * Setup common template variables.
     * @param bool $subclass
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
            /** @var GroupDAO $groupDao */
            $groupDao = DAORegistry::getDAO('GroupDAO');
            
            $templateMgr->assign(array(
                'hasDisplayMembership' => $groupDao->hasDisplayMembershipGroups($journalId),
                'displayMembershipGroups' => $groupDao->getDisplayMembershipGroupsData($journalId)
            ));
        }
    }

    /**
     * Display contact page.
     * 
     * [WIZDAM] Sebelumnya method ini punya percabangan if/else internal
     * (jurnal vs site) dan menampilkan SATU template yang sama untuk
     * keduanya. Sekarang jadi delegator tipis -- AboutJournalHandler dan
     * AboutPublisherHandler masing-masing punya method contact() SENDIRI
     * (halaman kontak dedicated per konteks, bukan lagi digabung), supaya
     * pageHierarchy/breadcrumb dan logic masing-masing benar-benar sesuai
     * konteksnya (lihat AboutPublisherHandler::setupTemplate() yang pakai
     * locale key 'about.aboutThePublisher', beda dari 'about.aboutTheJournal'
     * milik jurnal). Method ini TETAP di AboutHandler karena op 'contact'
     * di routing (pages/about/index.php) masih mengarah ke sini -- bukan
     * dipecah routing-nya, cukup didelegasikan ke class yang tepat di sini.
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function contact($args = [], $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $journal = $request->getJournal();

        if ($journal) {
            import('pages.about.AboutJournalHandler');
            $handler = new AboutJournalHandler();
            return $handler->contact($args, $request);
        }

        import('pages.about.AboutPublisherHandler');
        $handler = new AboutPublisherHandler();
        return $handler->contact($args, $request);
    }

    /**
     * About sitemap.
     */
    public function sitemap() {
        $this->validate();
        $this->setupTemplate(true);

        $templateMgr = TemplateManager::getManager();
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $user = Application::get()->getRequest()->getUser();
        /** @var RoleDAO $roleDao */
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
     * HELPER: Get public statistics names.
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

}
?>
<?php
declare(strict_types=1);

/**
 * @file pages/index/IndexHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IndexHandler
 * @ingroup pages_index
 *
 * @brief Handle site index requests.
 * Modifikasi: Pemisahan logika index Jurnal dan Publisher.
 * 
 */

import('classes.handler.Handler');
import('lib.wizdam.statistics.WizdamStats');
import('lib.wizdam.classes.services.PublisherProfileService');

class IndexHandler extends Handler {
    
    /**
     * Constructor
     **/
    public function __construct() {
        parent::__construct();
    }

    /**
     * [DEPRECATED] Backward compatibility.
     * Use __construct() instead.
     * @deprecated
     */
    public function IndexHandler() {
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
     * If no journal is selected, display list of journals.
     * Otherwise, display the index page for the selected journal.
     * @param array $args
     * @param PKPRequest $request
     * @return void
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate();

        $router = $request->getRouter();
        $templateMgr = TemplateManager::getManager();
        $journal = $router->getContext($request);
        $site = $request->getSite();

        $templateMgr->assign('helpTopicId', 'user.home');

        if ($journal) {
            $this->journal($journal, $request, $templateMgr);
        } else {
            $this->publisher($site, $request, $templateMgr);
        }
    }

    /**
     * Display the journal index page.
     * @param Journal $journal
     * @param PKPRequest $request
     * @param TemplateManager $templateMgr
     * @return void
     */
    private function journal($journal, $request, $templateMgr) {
        // [FIX] Flag "kita sedang di homepage jurnal" -- di-assign LANGSUNG
        // dari handler yang memang secara definisi menangani homepage, tidak
        // bergantung pada string $requestedPage dari router (terbukti tidak
        // konsisten di lingkungan production: guard $requestedPage != 'index'
        // && $requestedPage != '' gagal mendeteksi homepage, sehingga
        // breadcrumb tetap tampil walau journal ID ada di
        // hide_breadcrumb_journal_ids). Dipakai oleh
        // plugins/themes/sangiapub/templates/common/breadcrumbs.tpl untuk
        // menekan bagian hierarki/current-crumb di SEMUA homepage jurnal
        // (bukan penentu hide/show total -- itu tugas isBreadcrumbHiddenJournal
        // di bawah).
        $templateMgr->assign('isJournalHomepage', true);

        // [FIX] Seleksi hide_breadcrumb_journal_ids DIPINDAH ke sini --
        // di LUAR blok "if displayCurrentIssue" -- karena SEBELUMNYA
        // seleksi ini menumpang di dalam blok tersebut, sehingga kalau
        // Current Issue nonaktif (atau jurnal belum punya current issue),
        // seleksi ID hidden ini TIDAK PERNAH DIHITUNG SAMA SEKALI, walau
        // journal ID-nya memang ada di config.inc.php. Desainnya sendiri
        // (hide_breadcrumb_journal_ids) TIDAK PERNAH terkait dengan
        // Current Issue -- ini murni soal "jurnal ID mana yang breadcrumb
        // homepage-nya disembunyikan", jadi harus dihitung tanpa syarat
        // untuk SETIAP render homepage.
        $journalId = (int) $journal->getId();
        $hiddenIdsRaw = (string) Config::getVar('lumera', 'hide_breadcrumb_journal_ids');
        $hiddenIds = [];
        if (!empty($hiddenIdsRaw)) {
            $hiddenIds = array_map('trim', explode(',', $hiddenIdsRaw));
        }

        // [FIX] Dukungan wildcard "*" / "all" / "All" (case-insensitive):
        // kalau ada TOKEN wildcard di antara nilai hide_breadcrumb_journal_ids,
        // breadcrumb homepage disembunyikan untuk SEMUA jurnal tanpa perlu
        // daftar ID eksplisit -- termasuk otomatis berlaku untuk jurnal baru
        // yang ditambahkan nanti. Contoh isi config yang valid:
        //   hide_breadcrumb_journal_ids = "*"          -> semua jurnal
        //   hide_breadcrumb_journal_ids = "all"         -> semua jurnal
        //   hide_breadcrumb_journal_ids = "1,4,7"       -> hanya ID 1, 4, 7
        // Kalau tidak ada token wildcard, kembali ke perilaku lama: strict
        // match per journal ID lewat in_array() di bawah.
        $hideAllJournals = false;
        foreach ($hiddenIds as $hiddenIdToken) {
            if ($hiddenIdToken === '*' || strcasecmp($hiddenIdToken, 'all') === 0) {
                $hideAllJournals = true;
                break;
            }
        }

        // Satu-satunya sumber kebenaran: apakah jurnal INI (via journal ID,
        // BUKAN via $requestedPage atau state lain apa pun) memang di-set
        // untuk disembunyikan breadcrumb homepage-nya -- atau wildcard aktif.
        $isBreadcrumbHiddenJournal = $hideAllJournals || in_array((string) $journalId, $hiddenIds, true);
        $templateMgr->assign('isBreadcrumbHiddenJournal', $isBreadcrumbHiddenJournal);

        // Assign header and content for home page
        $templateMgr->assign('displayPageHeaderTitle', $journal->getLocalizedPageHeaderTitle(true));
        $templateMgr->assign('displayPageHeaderLogo', $journal->getLocalizedPageHeaderLogo(true));
        $templateMgr->assign('displayPageHeaderTitleAltText', $journal->getLocalizedSetting('homeHeaderTitleImageAltText'));
        $templateMgr->assign('displayPageHeaderLogoAltText', $journal->getLocalizedSetting('homeHeaderLogoImageAltText'));
        $templateMgr->assign('additionalHomeContent', $journal->getLocalizedSetting('additionalHomeContent'));
        $templateMgr->assign('homepageImage', $journal->getLocalizedSetting('homepageImage'));
        $templateMgr->assign('homepageImageAltText', $journal->getLocalizedSetting('homepageImageAltText'));
        $templateMgr->assign('journalDescription', $journal->getLocalizedSetting('description'));

        $displayCurrentIssue = (bool) $journal->getSetting('displayCurrentIssue');
        
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao ? $issueDao->getCurrentIssue((int) $journal->getId(), true) : null;
        
        if ($displayCurrentIssue && $issue !== null) {
            import('pages.issue.IssueHandler');
            IssueHandler::_setupIssueTemplate($request, $issue); // The current issue TOC/cover.

            // [UI/UX] Reset breadcrumb agar volume tidak muncul di Homepage Jurnal
            //
            // Catatan: sekarang bagian hierarki+current-crumb ini SELALU
            // ditekan di homepage lewat guard {if !$isJournalHomepage} di
            // breadcrumbs.tpl (lihat komentar di file itu), jadi
            // pageHierarchy/pageCrumbTitleTranslated di bawah ini sudah
            // tidak lagi terlihat pada breadcrumb homepage tema sangiapub.
            // TETAP dipertahankan (bukan dihapus) sebagai fallback yang
            // aman untuk tema lain yang mungkin masih membaca variabel ini
            // secara langsung tanpa guard $isJournalHomepage.
            $journalName = (string) $journal->getLocalizedTitle();
            if (empty($journalName)) {
                $journalName = (string) $journal->getPath();
            }
            $router = $request->getRouter();
            $homeUrl = $router->url($request, null, 'index');

            // [FIX] IssueHandler::_setupIssueTemplate() di atas menimpa
            // 'pageCrumbTitleTranslated' dengan identitas current issue (mis.
            // "Vol. 5 No. 2 (2024)"). Itu benar untuk halaman /issue/view/...,
            // tapi bocor ke breadcrumb Homepage Jurnal saat Current Issue
            // ditampilkan di sini -- jadi HARUS direset di kedua cabang di
            // bawah. TAPI cara mereset berbeda per cabang, karena
            // lib/pkp/templates/common/header.tpl punya fallback:
            //     {elseif !$pageCrumbTitleTranslated}
            //         {assign var="pageCrumbTitleTranslated" value=$pageTitleTranslated}
            // yaitu: NILAI FALSY (null/"") APA PUN otomatis diisi ulang
            // dengan $pageTitleTranslated (site title) oleh header.tpl
            // sendiri. Ini yang diinginkan untuk jurnal NORMAL (supaya
            // breadcrumb jatuh balik ke judul halaman utama, bukan identitas
            // issue) -- tapi untuk jurnal yang di-hide TOTAL lewat
            // hide_breadcrumb_journal_ids, fallback ini justru
            // memunculkan kembali site title sebagai crumb terakhir,
            // sehingga breadcrumb TIDAK PERNAH benar-benar kosong walau
            // pageHierarchy sudah di-set []. Reset dengan null di kedua
            // cabang (versi sebelumnya) gagal untuk skenario "hidden" ini.
            //
            // Solusinya: pakai idiom yang sudah ada di codebase ini sendiri
            // (lihat templates/comment/comments.tpl) -- assign string TRUTHY
            // tapi visualnya kosong ("&nbsp;") supaya fallback header.tpl
            // tidak pernah terpicu untuk jurnal yang memang harus disembunyikan.
            //
            // Pakai $isBreadcrumbHiddenJournal yang sudah dihitung di atas --
            // BUKAN menghitung ulang in_array() di sini -- supaya cuma ada
            // SATU sumber kebenaran untuk "jurnal ini hidden atau tidak".
            if ($isBreadcrumbHiddenJournal) {
                $templateMgr->assign('pageHierarchy', []);
                $templateMgr->assign('pageCrumbTitleTranslated', '&nbsp;');
            } else {
                $templateMgr->assign('pageHierarchy', [
                    [$homeUrl, $journalName]
                ]);
                $templateMgr->assign('pageCrumbTitleTranslated', null);
            }
            $templateMgr->assign('pageCrumbTitle', null);

            // FIX: Kalkulasi total artikel untuk validasi Homepage ---
            $publishedArticles = $templateMgr->getTemplateVars('publishedArticles');
            $homepageArticleCount = 0;
            
            if (is_array($publishedArticles)) {
                foreach ($publishedArticles as $section) {
                    if (isset($section['articles']) && is_array($section['articles'])) {
                        $homepageArticleCount += count($section['articles']);
                    }
                }
            }
            $templateMgr->assign('homepageArticleCount', $homepageArticleCount);
        }

        $enableAnnouncements = (bool) $journal->getSetting('enableAnnouncements');
        if ($enableAnnouncements) {
            $enableAnnouncementsHomepage = (bool) $journal->getSetting('enableAnnouncementsHomepage');
            if ($enableAnnouncementsHomepage) {
                $numAnnouncementsHomepage = (int) $journal->getSetting('numAnnouncementsHomepage');
                
                /** @var AnnouncementDAO $announcementDao */
                $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
                if ($announcementDao) {
                    $announcements = $announcementDao->getNumAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_JOURNAL, (int) $journal->getId(), $numAnnouncementsHomepage);
                    $templateMgr->assign('announcements', $announcements);
                    $templateMgr->assign('enableAnnouncementsHomepage', $enableAnnouncementsHomepage);
                }
            }
        }
        
        // [LUMERA] Editor Staff ---
        import('lib.pkp.classes.core.EditorialStaff');
        $maxStaffToShow = (int) Config::getVar('lumera', 'max_staff_show');
        if ($maxStaffToShow <= 0) {
            $maxStaffToShow = 3; 
        }
        EditorialStaff::displayHomepageStaff($journal, $templateMgr, $maxStaffToShow);
        
        // [LUMERA] STATS JURNAL ---
        $journalId = (int) $journal->getId();
        try {
            $journalStats = WizdamStats::getStats($journalId);
            if (is_array($journalStats) && !isset($journalStats['error'])) {
                foreach ($journalStats as $key => $value) {
                    $templateMgr->assign((string) $key, $value);
                }
            } else {
                 $templateMgr->assign('statsError', 'Data statistik tidak valid.');
                 if (isset($journalStats['error']) && Config::getVar('debug', 'log_errors')) {
                     error_log('WizdamStats: getStats() returned an error for JournalID ' . $journalId . ': ' . $journalStats['error']);
                 }
            }
        } catch (Exception $e) { 
            if (Config::getVar('debug', 'log_errors')) {
                error_log('WizdamStats (Handler): Exception loading WizdamStats for JournalID ' . $journalId . ': ' . $e->getMessage());
            }
            $templateMgr->assign('statsError', 'Gagal memuat statistik jurnal.');
        }
        $basePath = $request->getBasePath();
        $jsonPath = $basePath . '/public/wizdam_cache/stats/journal_' . $journalId . '_stats.json.gz';
        $templateMgr->assign('statsJsonPath', $jsonPath);

        import('lib.wizdam.trends.TrendsManager');
        TrendsManager::assignMostPopularPayload($templateMgr, $journal, $request, $issue, 10);
        TrendsManager::assignMostDownloadedHomepagePayload($templateMgr, $journal, $request, $issue);
        TrendsManager::assignMostCitedHomepagePayload($templateMgr, $journal, $request, $issue);
        
        import('lib.wizdam.hero.ArticleHeroService');
        ArticleHeroService::assignArticleHeroPayload($templateMgr, $journal, $request);

        $templateMgr->display('index/journal.tpl');
    }

    /**
     * Display the publisher page.
     * @param Site $site
     * @param PKPRequest $request
     * @param TemplateManager $templateMgr
     * @return void
     */
    private function publisher($site, $request, $templateMgr) {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');

        // [LUMERA] STATS 2: ROOT EDITORIAL SYSTEM
        try {
            $siteStats = WizdamStats::getSiteWideStats();
            
            if (Config::getVar('debug', 'log_errors')) {
                error_log("DEBUG IndexHandler (Site-Wide): Isi \$siteStats['journalsStats'] = " . print_r($siteStats['journalsStats'] ?? [], true)); 
            }
        
            if (is_array($siteStats) && !isset($siteStats['error'])) {
                foreach ($siteStats as $key => $value) {
                    $templateMgr->assign((string) $key, $value);
                }
            } else {
                $templateMgr->assign('statsError', 'Data statistik situs tidak valid.');
                $templateMgr->assign('journalsStats', []);
                $templateMgr->assign('allTotalViews', 0);
                $templateMgr->assign('allTotalDownloads', 0);
                $templateMgr->assign('allTotalAuthors', 0);
                
                if (isset($siteStats['error']) && Config::getVar('debug', 'log_errors')) {
                     error_log('WizdamStats (Handler): getSiteWideStats() returned an error: '. $siteStats['error']);
                }
            }
        } catch (Exception $e) { 
            if (Config::getVar('debug', 'log_errors')) {
                error_log('WizdamStats (Handler): Exception loading WizdamStats (Site-Wide): ' . $e->getMessage());
            }
            $templateMgr->assign('statsError', 'Gagal memuat statistik situs.');
            $templateMgr->assign('journalsStats', []);
            $templateMgr->assign('allTotalViews', 0);
            $templateMgr->assign('allTotalDownloads', 0);
            $templateMgr->assign('allTotalAuthors', 0);
        }

        $redirectId = $site->getRedirect();
        if ($redirectId && $journalDao && ($redirectJournal = $journalDao->getById((int) $redirectId)) !== null) {
            $request->redirect($redirectJournal->getPath());
        }
        
        $templateMgr->assign('intro', $site->getLocalizedIntro());
        $templateMgr->assign('about', $site->getLocalizedAbout());
        $templateMgr->assign('journalFilesPath', $request->getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/journals/');

        // If we're using paging, fetch the parameters
        $usePaging = (bool) $site->getSetting('usePaging');
        $rangeInfo = $usePaging ? $this->getRangeInfo('journals') : null;
        $templateMgr->assign('usePaging', $usePaging);

        // Fetch the alpha list parameters
        $searchInitial = trim((string) $request->getUserVar('searchInitial'));
        if (!preg_match('/^[A-Z]$/i', $searchInitial)) {
            $searchInitial = '';
        }
        
        $templateMgr->assign('searchInitial', $searchInitial);
        $templateMgr->assign('useAlphalist', (bool) $site->getSetting('useAlphalist'));

        // [LUMERA] Null-safety guard untuk pemanggilan DAO
        if ($journalDao) {
            $journals = $journalDao->getJournals(
                true,
                $rangeInfo,
                $searchInitial ? JOURNAL_FIELD_TITLE : JOURNAL_FIELD_SEQUENCE,
                $searchInitial ? JOURNAL_FIELD_TITLE : null,
                $searchInitial ? 'startsWith' : null,
                $searchInitial,
                true // [LUMERA] Aktifkan filter khusus Homepage
            );
            $templateMgr->assign('journals', $journals);
        } else {
            // Fallback jika DAO gagal dimuat
            $templateMgr->assign('journals', []); 
        }
        
        $templateMgr->assign('site', $site);
        $templateMgr->assign([
            'sitePrincipalContactEmail' => (string) $site->getLocalizedData('contactEmail')
        ]);

        // [BARU] Identitas resmi Penerbit -- logo, nama, motto, tagline
        $templateMgr->assign('publisher', (new PublisherProfileService())->getProfile());
        
        // [LUMERA] Most Popular ---
        import('lib.wizdam.trends.TrendsManager');
        TrendsManager::assignMostPopularPayload($templateMgr, null, $request, null, 4);

        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
        $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        $templateMgr->display('index/publisher.tpl');
    }

}
?>
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
            $journalId = (int) $journal->getId();
            // 1. Ambil nama jurnal
            $journalName = (string) $journal->getLocalizedTitle();
            if (empty($journalName)) {
                $journalName = (string) $journal->getPath();
            }
            // 2. Ambil URL homepage menggunakan Router
            $router = $request->getRouter();
            $homeUrl = $router->url($request, null, 'index');
            // 3. Cek apakah jurnal breadcrumb-nya dari config.inc.php
            $hiddenIdsRaw = (string) Config::getVar('lumera', 'hide_breadcrumb_journal_ids');
            $hiddenIds = [];
            if (!empty($hiddenIdsRaw)) {
                $hiddenIds = array_map('trim', explode(',', $hiddenIdsRaw));
            }
            // 4. Tentukan isi breadcrumb berdasarkan konfigurasi
            if (in_array((string) $journalId, $hiddenIds, true)) {
                $templateMgr->assign('pageHierarchy', []);
            } else {
                $templateMgr->assign('pageHierarchy', [
                    [$homeUrl, $journalName]
                ]);
            }

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
        
        // [TRENDS] Most Popular ---
        import('lib.wizdam.trends.TrendsManager');
        TrendsManager::assignMostPopularPayload($templateMgr, $journal, $request, $issue);

        // [TRENDS] Most Downloaded (widget homepage, common/featured/mostDownloads.tpl) ---
        // Data + keputusan tampil/tidaknya grid "app-reviews-row" (jumlah artikel > 5)
        // sepenuhnya dihitung di backend, lihat TrendsManager::assignMostDownloadedHomepagePayload().
        TrendsManager::assignMostDownloadedHomepagePayload($templateMgr, $journal, $request, $issue);

        // [TRENDS] Most Cited (widget homepage, common/featured/mostCitedArticles.tpl) ---
        // Data + keputusan tampil/tidaknya grid "app-reviews-row" (jumlah artikel > 5)
        // sepenuhnya dihitung di backend, lihat TrendsManager::assignMostCitedHomepagePayload().
        TrendsManager::assignMostCitedHomepagePayload($templateMgr, $journal, $request, $issue);

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

        // [BARU] Identitas resmi Penerbit -- logo, nama, motto, tagline,
        // warna brand. Menggantikan hardcode "Sangia..." di publisher.tpl.
        $templateMgr->assign('publisher', (new PublisherProfileService())->getProfile());
        
        // [LUMERA] Most Popular ---
        import('lib.wizdam.trends.TrendsManager');
        TrendsManager::assignMostPopularPayload($templateMgr, null, $request);

        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
        $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        $templateMgr->display('index/publisher.tpl');
    }

}
?>
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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * Modifikasi: Pemisahan logika index Jurnal dan Publisher.
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.PKPWizdamStats');

class IndexHandler extends Handler {
    
    /**
     * Constructor
     **/
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
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
     */
    public function index($args = [], $request = null) {
        $this->validate();
        $this->setupTemplate();

        $router = $request->getRouter();
        $templateMgr = TemplateManager::getManager();
        $journal = $router->getContext($request);
        $site = $request->getSite();

        $templateMgr->assign('helpTopicId', 'user.home');

        // [WIZDAM FIX] Initialize forceRefresh to prevent undefined variable error in strict mode
        $forceRefresh = $request->getUserVar('refresh') ? true : false;

        if ($journal) {
            $this->journal($journal, $request, $templateMgr, $forceRefresh);
        } else {
            $this->publisher($site, $request, $templateMgr, $forceRefresh);
        }
    }

    /**
     * Display the journal index page.
     * @param Journal $journal
     * @param PKPRequest $request
     * @param TemplateManager $templateMgr
     * @param bool $forceRefresh
     */
    private function journal($journal, $request, $templateMgr, $forceRefresh) {
        // Assign header and content for home page
        $templateMgr->assign('displayPageHeaderTitle', $journal->getLocalizedPageHeaderTitle(true));
        $templateMgr->assign('displayPageHeaderLogo', $journal->getLocalizedPageHeaderLogo(true));
        $templateMgr->assign('displayPageHeaderTitleAltText', $journal->getLocalizedSetting('homeHeaderTitleImageAltText'));
        $templateMgr->assign('displayPageHeaderLogoAltText', $journal->getLocalizedSetting('homeHeaderLogoImageAltText'));
        $templateMgr->assign('additionalHomeContent', $journal->getLocalizedSetting('additionalHomeContent'));
        $templateMgr->assign('homepageImage', $journal->getLocalizedSetting('homepageImage'));
        $templateMgr->assign('homepageImageAltText', $journal->getLocalizedSetting('homepageImageAltText'));
        $templateMgr->assign('journalDescription', $journal->getLocalizedSetting('description'));

        $displayCurrentIssue = $journal->getSetting('displayCurrentIssue');
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getCurrentIssue($journal->getId(), true);
        
        if ($displayCurrentIssue && isset($issue)) {
            import('pages.issue.IssueHandler');
            // The current issue TOC/cover page should be displayed below the custom home page.
            IssueHandler::_setupIssueTemplate($request, $issue);

            // --- WIZDAM FIX: Kalkulasi total artikel untuk validasi Homepage ---
            // Ambil array publishedArticles yang baru saja di-assign oleh IssueHandler
            $publishedArticles = $templateMgr->getTemplateVars('publishedArticles');
            $homepageArticleCount = 0;
            
            if (!empty($publishedArticles) && is_array($publishedArticles)) {
                foreach ($publishedArticles as $section) {
                    if (isset($section['articles']) && is_array($section['articles'])) {
                        $homepageArticleCount += count($section['articles']);
                    }
                }
            }
            // Assign hasil kalkulasi ke Smarty
            $templateMgr->assign('homepageArticleCount', $homepageArticleCount);
            // --- AKHIR WIZDAM FIX ---
        }

        $enableAnnouncements = $journal->getSetting('enableAnnouncements');
        if ($enableAnnouncements) {
            $enableAnnouncementsHomepage = $journal->getSetting('enableAnnouncementsHomepage');
            if ($enableAnnouncementsHomepage) {
                $numAnnouncementsHomepage = $journal->getSetting('numAnnouncementsHomepage');
                $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
                $announcements = $announcementDao->getNumAnnouncementsNotExpiredByAssocId(ASSOC_TYPE_JOURNAL, $journal->getId(), $numAnnouncementsHomepage);
                $templateMgr->assign('announcements', $announcements);
                $templateMgr->assign('enableAnnouncementsHomepage', $enableAnnouncementsHomepage);
            }
        }
        
        // --- MODIFIKASI DIMULAI (WIZDAM Editor Staff) ---
        import('lib.pkp.classes.core.PKPWizdamEditorStaff');
        $maxStaffToShow = 3; 
        PKPWizdamEditorStaff::displayHomepageStaff($journal, $templateMgr, $maxStaffToShow);
        // --- MODIFIKASI SELESAI (WIZDAM Editor Staff) ---
        
        // --- BLOK WIZDAM STATS JURNAL DIMULAI ---
        $journalId = $journal->getId();
        try {
            $journalStats = PKPWizdamStats::getStats($journalId, $forceRefresh);
            if (is_array($journalStats) && !isset($journalStats['error'])) {
                foreach ($journalStats as $key => $value) {
                    $templateMgr->assign($key, $value);
                }
            } else {
                 $templateMgr->assign('statsError', 'Data statistik tidak valid.');
                 if (isset($journalStats['error']) && Config::getVar('debug', 'log_errors')) {
                     error_log('WizdamStats: getStats() returned an error for JID ' . $journalId . ': ' . $journalStats['error']);
                 }
            }
        } catch (Exception $e) { 
            if (Config::getVar('debug', 'log_errors')) {
                error_log('WizdamStats (Handler): Exception loading PKPWizdamStats for JID ' . $journalId . ': ' . $e->getMessage());
            }
            $templateMgr->assign('statsError', 'Gagal memuat statistik jurnal.');
        }
        $basePath = $request->getBasePath();
        $jsonPath = $basePath . '/public/wizdam_cache/stats/journal_' . $journalId . '_stats.json.gz';
        $templateMgr->assign('statsJsonPath', $jsonPath);
        // --- AKHIR BLOK WIZDAM STATS JURNAL SELESAI ---
        
        // --- [TRENDS] WIZDAM Most Popular ---
        import('lib.wizdam.trends.WizdamTrendsManager');
        WizdamTrendsManager::assignMostPopularPayload($templateMgr, $journal, $request);
        
        $templateMgr->display('index/journal.tpl');
    }

    /**
     * Display the publisher page.
     * @param Site $site
     * @param PKPRequest $request
     * @param TemplateManager $templateMgr
     * @param bool $forceRefresh
     */
    private function publisher($site, $request, $templateMgr, $forceRefresh) {
        $journalDao = DAORegistry::getDAO('JournalDAO');

        // WIZDAM STATS 2: ROOT WIZDAM EDITORIAL SYSTEM
        try {
            $siteStats = PKPWizdamStats::getSiteWideStats($forceRefresh);
            
            if (Config::getVar('debug', 'log_errors')) {
                error_log("DEBUG IndexHandler (Site-Wide): Isi \$siteStats['journalsStats'] = " . print_r($siteStats['journalsStats'], true)); 
            }
        
            if (is_array($siteStats) && !isset($siteStats['error'])) {
                foreach ($siteStats as $key => $value) {
                    $templateMgr->assign($key, $value);
                }
            } else {
                $templateMgr->assign('statsError', 'Data statistik situs tidak valid.');
                
                // PERBAIKAN KRITIS UNTUK PHP 7.4+
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
                error_log('WizdamStats (Handler): Exception loading PKPWizdamStats (Site-Wide): ' . $e->getMessage());
            }
            $templateMgr->assign('statsError', 'Gagal memuat statistik situs.');
            
            // PERBAIKAN KRITIS UNTUK PHP 7.4+
            $templateMgr->assign('journalsStats', []);
            $templateMgr->assign('allTotalViews', 0);
            $templateMgr->assign('allTotalDownloads', 0);
            $templateMgr->assign('allTotalAuthors', 0);
        }
        
        if ($site->getRedirect() && ($redirectJournal = $journalDao->getById($site->getRedirect())) != null) {
            $request->redirect($redirectJournal->getPath());
        }
        
        $templateMgr->assign('intro', $site->getLocalizedIntro());
        $templateMgr->assign('about', $site->getLocalizedAbout());
        $templateMgr->assign('journalFilesPath', $request->getBaseUrl() . '/' . Config::getVar('files', 'public_files_dir') . '/journals/');

        // If we're using paging, fetch the parameters
        $usePaging = $site->getSetting('usePaging');
        $rangeInfo = $usePaging ? $this->getRangeInfo('journals') : null;
        $templateMgr->assign('usePaging', $usePaging);

        // Fetch the alpha list parameters
        $searchInitial = trim((string) $request->getUserVar('searchInitial'));
        if (!preg_match('/^[A-Z]$/i', $searchInitial)) {
            $searchInitial = '';
        }
        
        $templateMgr->assign('searchInitial', $searchInitial);
        $templateMgr->assign('useAlphalist', $site->getSetting('useAlphalist'));

        $journals = $journalDao->getJournals(
            true,
            $rangeInfo,
            $searchInitial ? JOURNAL_FIELD_TITLE : JOURNAL_FIELD_SEQUENCE,
            $searchInitial ? JOURNAL_FIELD_TITLE : null,
            $searchInitial ? 'startsWith' : null,
            $searchInitial
        );
        $templateMgr->assign('journals', $journals);
        
        $templateMgr->assign('site', $site);
        $templateMgr->assign([
            'sitePrincipalContactEmail' => $site->getLocalizedData('contactEmail')
        ]);
        
        // --- [TRENDS] WIZDAM Most Popular ---
        import('lib.wizdam.trends.WizdamTrendsManager');
        WizdamTrendsManager::assignMostPopularPayload($templateMgr, null, $request);

        $templateMgr->assign('alphaList', explode(' ', __('common.alphaList')));
        $templateMgr->setCacheability(CACHEABILITY_PUBLIC);
        $templateMgr->display('index/publisher.tpl');
    }
}
?>
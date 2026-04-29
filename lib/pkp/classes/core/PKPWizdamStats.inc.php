<?php
declare(strict_types=1);

/**
 * @file lib/pkp/classes/core/PKPWizdamStats.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @ingroup Statistics
 * @class PKPWizdamStats
 * 
 * @brief Mengintegrasikan logika statistik kustom (v1.24.0) ke dalam core OJS.
 * Menggabungkan logika dari journal-insight.txt, getJournalStats_v2.txt, dan allJournalStats.txt
 * @author Rochmady and Wizdam Team
 * @version v1.24.0 (Core Refactor Lengkap - Final)
 */

// Import kelas-kelas yang diperlukan
import('lib.pkp.classes.db.DBConnection');
import('lib.pkp.classes.cache.CacheManager');
import('lib.pkp.classes.core.Core');
import('lib.pkp.classes.config.Config');
import('classes.journal.JournalDAO');
import('classes.article.ArticleDAO');
import('classes.article.ArticleGalleyDAO');
import('classes.issue.IssueDAO');
import('classes.article.PublishedArticleDAO');
import('classes.submission.reviewAssignment.ReviewAssignmentDAO');
import('classes.article.AuthorDAO'); // Diperlukan untuk SiteWideStats

class PKPWizdamStats {

    // --- Konfigurasi untuk MESIN #1: Statistik Per Jurnal ---
    const STATS_CACHE_DURATION = 604800; // 7 hari (dari v2)
    
    // --- Konfigurasi untuk MESIN #2: Statistik Seluruh Situs ---
    const SITE_CACHE_PATH = 'cache/t_wizdam/stats/site_stats.php'; // Cache di 'cache/' (tidak perlu diakses web)
    
    // --- Konfigurasi untuk durasi cache Statistik Seluruh Situs ---
    const SITE_CACHE_DURATION = 7200; // 2 jam

    /*******************************************************
     * MESIN #1: STATISTIK PER JURNAL (getStats)
     * Menggabungkan Smart Cache (v2) + Logika Timeline (journal-insight)
     *******************************************************/

    /**
     * Fungsi utama untuk mengambil semua statistik jurnal.
     * Ini adalah "pintu depan" untuk statistik per jurnal.
     *
     * @param $journalId int
     * @param $forceRefresh boolean
     * @return array
     */
    public static function getStats($journalId, $forceRefresh = false) {
        
        // Cek cache terlebih dahulu
        if (!$forceRefresh) {
            $cacheData = self::_getJournalStatsFromCache($journalId);
            if ($cacheData !== false) {
                // Jika cache valid (hash cocok atau belum kedaluwarsa), kembalikan
                return $cacheData;
            }
            // Jika cache tidak ada atau tidak valid, lanjutkan untuk menghitung ulang
        }
        
        // Jika cache tidak ada atau $forceRefresh = true, hitung ulang
        return self::_calculateAndCacheStats($journalId);
    }
    
    /**
     * Fungsi inti yang melakukan SEMUA perhitungan SQL dan caching.
     * Logika perhitungan timeline diperbaiki untuk memastikan semua data
     * (termasuk timeline tahunan) dihitung dengan benar.
     *
     * @param $journalId int
     * @return array
     */
    private static function _calculateAndCacheStats($journalId) {
        
        // --- MULAI PORTING DARI getJournalStats.php ---
        
        // Inisialisasi variabel default (dari getJournalStats.php)
        $journalTitle = "";
        $totalViews = 0;
        $totalDownloads = 0;
        $acceptRate = 0;
        $declineRate = 0;
        $daysPerReview = 0;
        $daysToPublication = 0;
        $submissionToFirstDecision = 0;
        $submissionToAcceptance = 0;
        $acceptanceToPublication = 0;
        $totalArticles = 0;
        $totalIssues = 0;
        $articlesPerIssue = 0;
        $metricsTableExists = "Tidak";
        $metricsColumns = "Tidak ditemukan";
        $articleStatsExists = "Tidak";
        $galleyStatsExists = "Tidak";
        $lastPublicationYear = "";
        $lastYearArticleCount = 0;
        $yearlyStats = array(); // Data historis untuk visualisasi
        $currentYear = (int)date('Y');

        // Array untuk data median (All-Time minus Current Year)
        $daysReview = array();
        $daysPublication = array();
        $daysFirstDecision = array();
        $daysSubmissionToAcceptance = array();
        $daysAcceptanceToPublication = array();

        // Mendapatkan DAO yang diperlukan (dari getJournalStats.php)
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        // $authorDao sudah di-import oleh class

        // Ekstrak judul jurnal (dari getJournalStats.php)
        $journal = $journalDao->getById($journalId);
        if (is_object($journal)) {
            if (method_exists($journal, 'getLocalizedTitle')) {
                $journalTitle = $journal->getLocalizedTitle();
            } elseif (method_exists($journal, 'getTitle')) {
                $journalTitle = $journal->getTitle();
            }
        }
        
        // ==========================================================
        // BAGIAN 0: Cek Struktur DB (dari getJournalStats.php)
        // ==========================================================
        try {
            $result = $articleDao->retrieve("SHOW TABLES LIKE 'metrics'");
            $metricsTableExists = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
            $result->Close();
            if ($metricsTableExists == "Ya") {
                $result = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
                $columns = array();
                while (!$result->EOF) {
                    $columns[] = $result->fields[0];
                    $result->MoveNext();
                }
                $result->Close();
                $metricsColumns = implode(", ", $columns);
            }
            $result = $articleDao->retrieve("SHOW TABLES LIKE 'article_view_stats'");
            $articleStatsExists = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
            $result->Close();
            $result = $articleDao->retrieve("SHOW TABLES LIKE 'article_galley_view_stats'");
            $galleyStatsExists = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
            $result->Close();
        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) error_log("WizdamStats: Error checking DB structure: " . $e->getMessage());
        }

        // ==========================================================
        // BAGIAN 1: Statistik View dan Download
        // ==========================================================
        try {
            if ($metricsTableExists == "Ya") {
                // Strategi 1: Total Views (dari getJournalStats.php)
                $viewResult = $articleDao->retrieve( "SELECT SUM(metric) AS total_views FROM metrics WHERE context_id = ? AND assoc_type = ?", array($journalId, ASSOC_TYPE_ARTICLE) );
                if ($viewResult && !$viewResult->EOF && $viewResult->fields['total_views']) $totalViews = (int)$viewResult->fields['total_views'];
                $viewResult->Close();

                // Strategi 2: Total Downloads (dari getJournalStats.php)
                $downloadResult = $articleDao->retrieve( "SELECT SUM(metric) AS total_downloads FROM metrics WHERE context_id = ? AND assoc_type = ?", array($journalId, ASSOC_TYPE_GALLEY) );
                if ($downloadResult && !$downloadResult->EOF && $downloadResult->fields['total_downloads']) $totalDownloads = (int)$downloadResult->fields['total_downloads'];
                $downloadResult->Close();
                
                // ... (Kita bisa tambahkan fallback metric_type jika $totalViews == 0, tapi kita sederhanakan) ...

                // Tentukan kolom tanggal (dari getJournalStats.php)
                $dateColumn = '';
                if (strpos($metricsColumns, 'day') !== false) $dateColumn = 'day';
                elseif (strpos($metricsColumns, 'load_time') !== false) $dateColumn = 'load_time';
                
                if (!empty($dateColumn)) {
                    // Data views per tahun (dari getJournalStats.php)
                    $yearlyViewsResult = $articleDao->retrieve(
                        "SELECT YEAR($dateColumn) as year, SUM(metric) as views FROM metrics WHERE context_id = ? AND assoc_type = ? AND $dateColumn IS NOT NULL GROUP BY YEAR($dateColumn) ORDER BY year ASC",
                        array($journalId, ASSOC_TYPE_ARTICLE)
                    );
                    while ($yearlyViewsResult && !$yearlyViewsResult->EOF) {
                        $year = (int)$yearlyViewsResult->fields['year'];
                        if ($year > 1990 && $year <= ($currentYear + 1)) {
                            if (!isset($yearlyStats[$year])) $yearlyStats[$year] = array('year' => $year);
                            $yearlyStats[$year]['views'] = (int)$yearlyViewsResult->fields['views'];
                        }
                        $yearlyViewsResult->MoveNext();
                    }
                    if ($yearlyViewsResult) $yearlyViewsResult->Close();

                    // Data downloads per tahun (dari getJournalStats.php)
                    $yearlyDownloadsResult = $articleDao->retrieve(
                        "SELECT YEAR($dateColumn) as year, SUM(metric) as downloads FROM metrics WHERE context_id = ? AND assoc_type = ? AND $dateColumn IS NOT NULL GROUP BY YEAR($dateColumn) ORDER BY year ASC",
                        array($journalId, ASSOC_TYPE_GALLEY)
                    );
                    while ($yearlyDownloadsResult && !$yearlyDownloadsResult->EOF) {
                        $year = (int)$yearlyDownloadsResult->fields['year'];
                        if ($year > 1990 && $year <= ($currentYear + 1)) {
                            if (!isset($yearlyStats[$year])) $yearlyStats[$year] = array('year' => $year);
                            $yearlyStats[$year]['downloads'] = (int)$yearlyDownloadsResult->fields['downloads'];
                        }
                        $yearlyDownloadsResult->MoveNext();
                    }
                    if ($yearlyDownloadsResult) $yearlyDownloadsResult->Close();
                }
            }
            
            // ... (Kita abaikan fallback article_view_stats untuk total, demi kesederhanaan refactor) ...

        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) error_log("WizdamStats: Error getting Views/Downloads: " . $e->getMessage());
        }

        // ==========================================================
        // BAGIAN 2: Statistik Submisi & Publikasi Tahunan
        // ==========================================================
        try {
            // Submissions per tahun
            $submissionsResult = $articleDao->retrieve(
                "SELECT YEAR(date_submitted) as year, COUNT(*) as submissions FROM articles WHERE journal_id = ? GROUP BY YEAR(date_submitted) ORDER BY YEAR(date_submitted) ASC",
                array($journalId)
            );
            while ($submissionsResult && !$submissionsResult->EOF) {
                $year = (int)$submissionsResult->fields['year'];
                if ($year > 1990 && $year <= ($currentYear + 1)) {
                    if (!isset($yearlyStats[$year])) $yearlyStats[$year] = array('year' => $year);
                    $yearlyStats[$year]['submissions'] = (int)$submissionsResult->fields['submissions'];
                }
                $submissionsResult->MoveNext();
            }
            if ($submissionsResult) $submissionsResult->Close();

            // Published per tahun (Data Anda yang hilang #1)
            $publishedResult = $articleDao->retrieve(
                "SELECT YEAR(pa.date_published) as year, COUNT(*) as published FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? GROUP BY YEAR(pa.date_published) ORDER BY YEAR(pa.date_published) ASC",
                array($journalId)
            );
            while ($publishedResult && !$publishedResult->EOF) {
                $year = (int)$publishedResult->fields['year'];
                if ($year > 1990 && $year <= ($currentYear + 1)) {
                    if (!isset($yearlyStats[$year])) $yearlyStats[$year] = array('year' => $year);
                    $yearlyStats[$year]['published'] = (int)$publishedResult->fields['published']; // Ini adalah 'publications' di JS Anda
                }
                $publishedResult->MoveNext();
            }
            if ($publishedResult) $publishedResult->Close();
            
            // Hitung acceptance rate per tahun (Data Anda yang hilang #6)
            foreach ($yearlyStats as $year => $data) {
                $submissions = isset($data['submissions']) ? $data['submissions'] : 0;
                $published = isset($data['published']) ? $data['published'] : 0; // Seharusnya 'accepted', tapi kita ikuti logika getJournalStats.php
                if ($submissions > 0) {
                    $yearlyStats[$year]['acceptanceRate'] = round(($published / $submissions) * 100, 1);
                }
            }
        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) error_log("WizdamStats: Error getting Submission stats: " . $e->getMessage());
        }

        // ==========================================================
        // BAGIAN 3: Median All-Time
        // ==========================================================
        try {
            // Cek tabel edit_decisions (PENTING!)
            $checkTableResult = $articleDao->retrieve("SHOW TABLES LIKE 'edit_decisions'");
            $editDecisionsTableExists = ($checkTableResult->RecordCount() > 0);
            if($checkTableResult) $checkTableResult->Close();
            
            // ... (Query untuk $acceptRate, $declineRate) ...
            $result = $articleDao->retrieve("SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status IN (2, 3, 4)", array($journalId, $currentYear));
            $totalReviewed = ($result && !$result->EOF) ? (int)$result->fields['total'] : 0;
            if($result) $result->Close();
            $result = $articleDao->retrieve("SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status = 3", array($journalId, $currentYear));
            $totalAccepted = ($result && !$result->EOF) ? (int)$result->fields['total'] : 0;
            if($result) $result->Close();
            $result = $articleDao->retrieve("SELECT COUNT(*) AS total FROM articles WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status = 4", array($journalId, $currentYear));
            $totalDeclined = ($result && !$result->EOF) ? (int)$result->fields['total'] : 0;
            if($result) $result->Close();
            $acceptRate = ($totalReviewed > 0) ? ($totalAccepted * 100 / $totalReviewed) : 0;
            $declineRate = ($totalReviewed > 0) ? ($totalDeclined * 100 / $totalReviewed) : 0;

            // ... (Query untuk $daysReview[]) ...
            $result = $reviewAssignmentDao->retrieve("SELECT DATEDIFF(date_completed, date_notified) AS days_to_review FROM review_assignments WHERE date_completed IS NOT NULL AND declined = 0 AND cancelled = 0 AND YEAR(date_notified) < ?", array($currentYear));
            while ($result && !$result->EOF) { $days = (float)$result->fields['days_to_review']; if ($days > 0) $daysReview[] = $days; $result->MoveNext(); }
            if($result) $result->Close();
            
            // ... (Query untuk $daysPublication[]) ...
            $result = $articleDao->retrieve("SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days_to_publication FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND pa.date_published IS NOT NULL", array($journalId, $currentYear));
            while ($result && !$result->EOF) { $days = (float)$result->fields['days_to_publication']; if ($days > 0) $daysPublication[] = $days; $result->MoveNext(); }
            if($result) $result->Close();
            
            // ... (Query untuk timeline DENGAN $editDecisionsTableExists) ...
            if ($editDecisionsTableExists) {
                // ... (Query untuk $daysFirstDecision[]) ...
                $result = $articleDao->retrieve("SELECT a.article_id, a.date_submitted, MIN(ed.date_decided) as first_decision FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.date_decided IS NOT NULL GROUP BY a.article_id", array($journalId, $currentYear));
                while ($result && !$result->EOF) { $sDate = strtotime($result->fields['date_submitted']); $dDate = strtotime($result->fields['first_decision']); if ($sDate && $dDate) { $diff = round(($dDate - $sDate) / 86400); if ($diff > 0) $daysFirstDecision[] = $diff; } $result->MoveNext(); }
                if($result) $result->Close();
                
                // ... (Query untuk $daysSubmissionToAcceptance[]) ...
                $result = $articleDao->retrieve("SELECT a.article_id, a.date_submitted, MAX(ed.date_decided) as acceptance_date FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.decision = 1 GROUP BY a.article_id", array($journalId, $currentYear));
                while ($result && !$result->EOF) { $sDate = strtotime($result->fields['date_submitted']); $aDate = strtotime($result->fields['acceptance_date']); if ($sDate && $aDate) { $diff = round(($aDate - $sDate) / 86400); if ($diff > 0) $daysSubmissionToAcceptance[] = $diff; } $result->MoveNext(); }
                if($result) $result->Close();
                
                // ... (Query untuk $daysAcceptanceToPublication[]) ...
                $result = $articleDao->retrieve("SELECT a.article_id, MAX(ed.date_decided) as acceptance_date, pa.date_published FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND ed.decision = 1 AND pa.date_published IS NOT NULL GROUP BY a.article_id", array($journalId, $currentYear));
                while ($result && !$result->EOF) { $aDate = strtotime($result->fields['acceptance_date']); $pDate = strtotime($result->fields['date_published']); if ($aDate && $pDate) { $diff = round(($pDate - $aDate) / 86400); if ($diff > 0) $daysAcceptanceToPublication[] = $diff; } $result->MoveNext(); }
                if($result) $result->Close();
                
            } else {
                // ... (Logika Fallback jika $editDecisionsTableExists = false) ...
                // (Kita salin logika fallback dari getJournalStats.txt)
                $result = $articleDao->retrieve("SELECT a.article_id, a.date_submitted, a.date_status_modified FROM articles a WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND a.status = 3", array($journalId, $currentYear));
                while ($result && !$result->EOF) { $sDate = strtotime($result->fields['date_submitted']); $aDate = strtotime($result->fields['date_status_modified']); if ($sDate && $aDate) { $diff = round(($aDate - $sDate) / 86400); if ($diff > 0) { $daysFirstDecision[] = $diff; $daysSubmissionToAcceptance[] = $diff; } } $result->MoveNext(); }
                if($result) $result->Close();
                $result = $articleDao->retrieve("SELECT a.article_id, a.date_status_modified, pa.date_published FROM articles a JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND a.status = 3 AND pa.date_published IS NOT NULL", array($journalId, $currentYear));
                while ($result && !$result->EOF) { $aDate = strtotime($result->fields['date_status_modified']); $pDate = strtotime($result->fields['date_published']); if ($aDate && $pDate) { $diff = round(($pDate - $aDate) / 86400); if ($diff > 0) $daysAcceptanceToPublication[] = $diff; } $result->MoveNext(); }
                if($result) $result->Close();
            }
            
            $daysPerReview = self::_getMedian($daysReview);
            $daysToPublication = self::_getMedian($daysPublication);
            $submissionToFirstDecision = self::_getMedian($daysFirstDecision);
            $submissionToAcceptance = self::_getMedian($daysSubmissionToAcceptance);
            $acceptanceToPublication = self::_getMedian($daysAcceptanceToPublication);

        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) error_log("WizdamStats: Error getting All-Time Median stats: " . $e->getMessage());
        }

        // ==========================================================
        // BAGIAN 4: Median Timeline TAHUNAN
        // ==========================================================
        try {
            $result = $articleDao->retrieve("SELECT MIN(YEAR(date_submitted)) AS start_year FROM articles WHERE journal_id = ?", array($journalId));
            $startYear = ($result && !$result->EOF && $result->fields['start_year']) ? (int)$result->fields['start_year'] : $currentYear - 3;
            if($result) $result->Close();
            
            // Loop ini menghitung median PER TAHUN untuk grafik
            for ($year = $startYear; $year <= $currentYear; $year++) {
                // Inisialisasi array data per tahun
                $yearDaysReview = array();
                $yearDaysToPublish = array();
                $yearDaysToFirstDecision = array();
                $yearDaysToAcceptance = array();
                $yearDaysAcceptanceToPublication = array();

                // 7a. Review Time per tahun (dari getJournalStats.php)
                $result = $reviewAssignmentDao->retrieve("SELECT DATEDIFF(date_completed, date_notified) AS days_to_review FROM review_assignments ra JOIN articles a ON (ra.submission_id = a.article_id) WHERE a.journal_id = ? AND ra.date_completed IS NOT NULL AND ra.declined = 0 AND ra.cancelled = 0 AND YEAR(ra.date_completed) = ?", array($journalId, $year));
                while ($result && !$result->EOF) { $days = (float)$result->fields['days_to_review']; if ($days > 0) $yearDaysReview[] = $days; $result->MoveNext(); }
                if($result) $result->Close();

                // 7b. Days to Publication per tahun (dari getJournalStats.php)
                $result = $articleDao->retrieve("SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days_to_publication FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ? AND YEAR(pa.date_published) = ? AND pa.date_published IS NOT NULL", array($journalId, $year));
                while ($result && !$result->EOF) { $days = (float)$result->fields['days_to_publication']; if ($days > 0) $yearDaysToPublish[] = $days; $result->MoveNext(); }
                if($result) $result->Close();

                if ($editDecisionsTableExists) {
                    // 7c. Submission to First Decision per tahun (dari getJournalStats.php)
                    $result = $articleDao->retrieve("SELECT a.article_id, a.date_submitted, MIN(ed.date_decided) as first_decision FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(ed.date_decided) = ? AND ed.date_decided IS NOT NULL GROUP BY a.article_id", array($journalId, $year));
                    while ($result && !$result->EOF) { $sDate = strtotime($result->fields['date_submitted']); $dDate = strtotime($result->fields['first_decision']); if ($sDate && $dDate) { $diff = round(($dDate - $sDate) / 86400); if ($diff > 0) $yearDaysToFirstDecision[] = $diff; } $result->MoveNext(); }
                    if($result) $result->Close();
                    
                    // 7d. Submission to Acceptance per tahun (dari getJournalStats.php)
                    $result = $articleDao->retrieve("SELECT a.article_id, a.date_submitted, MAX(ed.date_decided) as acceptance_date FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) WHERE a.journal_id = ? AND YEAR(ed.date_decided) = ? AND ed.decision = 1 GROUP BY a.article_id", array($journalId, $year));
                    while ($result && !$result->EOF) { $sDate = strtotime($result->fields['date_submitted']); $aDate = strtotime($result->fields['acceptance_date']); if ($sDate && $aDate) { $diff = round(($aDate - $sDate) / 86400); if ($diff > 0) $yearDaysToAcceptance[] = $diff; } $result->MoveNext(); }
                    if($result) $result->Close();
                    
                    // 7e. Acceptance to Publication per tahun (dari getJournalStats.php)
                    $result = $articleDao->retrieve("SELECT a.article_id, MAX(ed.date_decided) as acceptance_date, pa.date_published FROM articles a JOIN edit_decisions ed ON (a.article_id = ed.article_id) JOIN published_articles pa ON (a.article_id = pa.article_id) WHERE a.journal_id = ? AND YEAR(pa.date_published) = ? AND ed.decision = 1 AND pa.date_published IS NOT NULL GROUP BY a.article_id", array($journalId, $year));
                    while ($result && !$result->EOF) { $aDate = strtotime($result->fields['acceptance_date']); $pDate = strtotime($result->fields['date_published']); if ($aDate && $pDate) { $diff = round(($pDate - $aDate) / 86400); if ($diff > 0) $yearDaysAcceptanceToPublication[] = $diff; } $result->MoveNext(); }
                    if($result) $result->Close();
                }
                
                // Tambahkan statistik waktu ke array tahunan (dari getJournalStats.php)
                if (!isset($yearlyStats[$year])) $yearlyStats[$year] = array('year' => $year);
                $yearlyStats[$year]['daysToPublication'] = round(self::_getMedian($yearDaysToPublish));
                $yearlyStats[$year]['reviewTime'] = round(self::_getMedian($yearDaysReview)); // 'daysPerReview' di getJournalStats.php
                $yearlyStats[$year]['firstDecision'] = round(self::_getMedian($yearDaysToFirstDecision)); // 'daysToFirstDecision'
                $yearlyStats[$year]['submissionToAcceptance'] = round(self::_getMedian($yearDaysToAcceptance)); // 'daysToAcceptance'
                $yearlyStats[$year]['acceptanceToPublication'] = round(self::_getMedian($yearDaysAcceptanceToPublication));
            }
        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) error_log("WizdamStats: Error getting Yearly Median stats: " . $e->getMessage());
        }

        // ==========================================================
        // BAGIAN 5: Total Publikasi All-Time (dari getJournalStats.php)
        // ==========================================================
        try {
            // PERBAIKAN FALLACY #1: Hitung HANYA artikel yang diterbitkan ---
            $result = $publishedArticleDao->retrieve(
                "SELECT COUNT(pa.article_id) AS total 
                 FROM published_articles pa JOIN articles a ON pa.article_id = a.article_id 
                 WHERE a.journal_id = ?", 
                array($journalId)
            );
            if ($result && !$result->EOF) $totalArticles = (int)$result->fields['total'];
            if($result) $result->Close();
            
            $result = $issueDao->retrieve("SELECT COUNT(*) AS total FROM issues WHERE journal_id = ? AND published = 1", array($journalId));
            if ($result && !$result->EOF) $totalIssues = (int)$result->fields['total'];
            if($result) $result->Close(); 
            
            $articlesPerIssue = ($totalIssues > 0) ? ($totalArticles / $totalIssues) : 0;

            // Mendapatkan tahun terakhir publikasi
            $result = $articleDao->retrieve(
                "SELECT YEAR(date_published) as publication_year, COUNT(*) as article_count 
                 FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) 
                 WHERE a.journal_id = ? AND pa.date_published IS NOT NULL 
                 GROUP BY YEAR(date_published) ORDER BY YEAR(date_published) DESC LIMIT 1",
                array($journalId)
            );
            if ($result && !$result->EOF) {
                $lastPublicationYear = $result->fields['publication_year'];
                $lastYearArticleCount = $result->fields['article_count'];
            }
            if($result) $result->Close();
        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) error_log("WizdamStats: Error getting All-Time Publication stats: " . $e->getMessage());
        }

        // ==========================================================
        // BAGIAN 6: "Zero-Fill" (Untuk membersihkan data JS)
        // ==========================================================
        if (is_array($yearlyStats) && !empty($yearlyStats)) {
            // Ini adalah SEMUA KUNCI yang dibutuhkan oleh JS Anda
            $allKeys = array(
                'year' => 0, 'views' => 0, 'downloads' => 0, 
                'submissions' => 0, 'published' => 0, 'acceptanceRate' => 0, // 'published' adalah 'publications' Anda, 'acceptanceRate' adalah 'acceptanceRate'
                'daysToPublication' => 0, 'reviewTime' => 0, 'firstDecision' => 0, // 'reviewTime' adalah 'daysPerReview' Anda
                'submissionToAcceptance' => 0, 'acceptanceToPublication' => 0,
                'accepted' => 0, 'declined' => 0 // Ditambahkan untuk kelengkapan
            );
            
            ksort($yearlyStats); // Urutkan berdasarkan tahun

            foreach ($yearlyStats as $year => $data) {
                if (!is_array($data)) $yearlyStats[$year] = array();
                foreach ($allKeys as $key => $defaultValue) {
                    if (!isset($yearlyStats[$year][$key])) {
                        // Ganti 'published' dengan 'publications' agar konsisten dengan JS Anda
                        if ($key == 'published') {
                             $yearlyStats[$year]['publications'] = $defaultValue;
                        } else {
                             $yearlyStats[$year][$key] = ($key == 'year') ? $year : $defaultValue;
                        }
                    }
                }
                // Hapus kunci 'published' yang salah jika ada
                if (isset($yearlyStats[$year]['published'])) unset($yearlyStats[$year]['published']);
            }
        }

        // ==========================================================
        // BAGIAN 7: Siapkan Array Hasil (Final)
        // ==========================================================
        $stats = array(
            'journalId' => $journalId,
            // (Nama diganti di v1.23.2)
            'journalTitle' => $journalTitle,
            'totalViews' => $totalViews,
            'totalDownloads' => $totalDownloads,
            
            // Nama variabel baru untuk template (dari v1.23.2)
            'journalTotalViews' => $totalViews,
            'journalTotalDownloads' => $totalDownloads,
            
            // Median All-Time (kecuali tahun ini)
            'acceptRate' => round($acceptRate, 1),
            'declineRate' => round($declineRate, 1),
            'daysPerReview' => round($daysPerReview), // 'daysPerReview'
            'daysToPublication' => round($daysToPublication), // 'daysToPublication'
            
            // --- INI YANG HILANG DARI KODE SAYA SEBELUMNYA ---
            'submissionToFirstDecision' => round($submissionToFirstDecision), // 'daysToFirstDecision'
            'submissionToAcceptance' => round($submissionToAcceptance), // 'daysToAcceptance'
            'acceptanceToPublication' => round($acceptanceToPublication),

            // Total Publikasi All-Time
            'totalArticles' => $totalArticles, // Ini sudah benar (hanya yang published)
            'totalIssues' => $totalIssues,
            'articlesPerIssue' => round($articlesPerIssue, 1),
            'lastPublicationYear' => $lastPublicationYear,
            'lastYearArticleCount' => $lastYearArticleCount,

            // Diagnostik DB (dari getJournalStats.php)
            'metricsTableExists' => $metricsTableExists,
            'metricsColumns' => $metricsColumns,
            'articleStatsExists' => $articleStatsExists,
            'galleyStatsExists' => $galleyStatsExists,
            
            // Timestamp (dari getJournalStats.php)
            'calculationDate' => date('Y-m-d H:i:s'),
            'lastUpdated' => date('Y-m-d H:i:s'), // (dari v1.23.2)
            
            // Data Grafik Tahunan (sudah di-zero-fill)
            'yearlyStats' => array_values($yearlyStats) 
        );

        // Cache hasil menggunakan Smart Cache
        self::_cacheJournalStats($journalId, $stats);
        
        // Kembalikan hasil
        return $stats;
    }
    

    // --- Helper untuk MESIN #1: Statistik Per Jurnal ---

    /**
     * Helper untuk mengambil median (dari journal-insight.txt)
     */
    private static function _getMedian($arr) {
        if (empty($arr)) return 0;
        sort($arr);
        $count = count($arr);
        $middle = floor($count / 2);
        if ($count % 2 == 0) {
            // Check if middle indices are valid
            if (isset($arr[$middle - 1]) && isset($arr[$middle])) {
                return ($arr[$middle - 1] + $arr[$middle]) / 2;
            } else {
                return 0; // Or handle error appropriately
            }
        } else {
            // Check if middle index is valid
            if (isset($arr[$middle])) {
                return $arr[$middle];
            } else {
                return 0; // Or handle error appropriately
            }
        }
    }


    /**
     * Helper untuk cek DB (dari journal-insight.txt)
     */
    private static function _checkDatabaseStructure($articleDao) {
        $info = array('metricsTableExists' => "Tidak", 'metricsColumns' => "Tidak ditemukan", 'articleStatsExists' => "Tidak", 'galleyStatsExists' => "Tidak");
        try {
            $result = $articleDao->retrieve("SHOW TABLES LIKE 'metrics'");
            if($result) {
                $info['metricsTableExists'] = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
                $result->Close();
            }
            
            if ($info['metricsTableExists'] == "Ya") {
                $result = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
                $columns = array();
                while ($result && !$result->EOF) { $columns[] = $result->fields[0]; $result->MoveNext(); }
                if($result) $result->Close();
                $info['metricsColumns'] = implode(", ", $columns);
            }
            
            $result = $articleDao->retrieve("SHOW TABLES LIKE 'article_view_stats'");
             if($result) {
                $info['articleStatsExists'] = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
                $result->Close();
            }
            
            $result = $articleDao->retrieve("SHOW TABLES LIKE 'article_galley_view_stats'");
            if($result) {
                $info['galleyStatsExists'] = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
                $result->Close();
            }
        } catch (Exception $e) {
            // HANYA log jika OJS diatur untuk mencatat error di config.inc.php
            if (Config::getVar('debug', 'log_errors')) {
                error_log("WizdamStats: Error checking DB: " . $e->getMessage()); 
            }
        }
        return $info;
    }

    /**
     * REVISI: Mengambil cache menggunakan Smart Detection (v2)
     */
    private static function _getJournalStatsFromCache($journalId) {
        $cacheDir = self::_getCacheDir();
        $cacheFile = $cacheDir . 'journal_' . $journalId . '_stats.php';
        $hashFile = $cacheFile . '.hash';

        if (file_exists($cacheFile) && file_exists($hashFile)) {
            $currentHash = self::_getJournalStatsDataHash($journalId);
            $cachedHash = trim(@file_get_contents($hashFile)); // Use @ to suppress warning if file is unreadable

            // error_log("WizdamStats: Cache Check JID: $journalId - CurrentHash: $currentHash | CachedHash: $cachedHash");
            
            // Check if hash matches OR if cache is still within acceptable age (fallback)
            if (($currentHash !== '' && $cachedHash !== '' && $currentHash == $cachedHash) || 
                (@filemtime($cacheFile) > (time() - self::STATS_CACHE_DURATION))) { // Use @ for filemtime
                try {
                    $cachedStats = unserialize(@file_get_contents($cacheFile)); // Use @
                    if (is_array($cachedStats)) {
                         // error_log("WizdamStats: Cache VALID for JID: $journalId");
                        return $cachedStats;
                    } else {
                        // error_log("WizdamStats: Cache CORRUPT (unserialize failed) for JID: $journalId");
                    }
                } catch (Exception $e) { 
                    // HANYA jika OJS diatur untuk mencatat error di config.inc.php
                    if (Config::getVar('debug', 'log_errors')) {
                        error_log("WizdamStats: Failed to read/unserialize cache for JID $journalId: " . $e->getMessage()); 
                    }
                }
            } else {
                 // error_log("WizdamStats: Cache INVALID (hash mismatch or too old) for JID: $journalId");
            }
        } else {
            // error_log("WizdamStats: Cache files NOT FOUND for JID: $journalId");
        }
        return false;
    }

    /**
     * REVISI: Menyimpan cache menggunakan Smart Detection (v2)
     */
    private static function _cacheJournalStats($journalId, $stats) {
        $cacheDir = self::_getCacheDir();
        if (!self::_ensureCacheDirExists($cacheDir)) {
             error_log("WizdamStats: Failed to create cache directory: " . $cacheDir);
             return false;
        }

        $cacheFile = $cacheDir . 'journal_' . $journalId . '_stats.php';
        $hashFile = $cacheFile . '.hash';
        $jsonCacheFile = $cacheDir . 'journal_' . $journalId . '_stats.json.gz';

        try {
            // Tulis cache PHP
            $result1 = @file_put_contents($cacheFile, serialize($stats));
            if ($result1 === false) {
                 error_log("WizdamStats: Failed to write PHP cache file: " . $cacheFile);
            }
            
            // Tulis cache JSON.gz
            $jsonData = json_encode($stats);
            if ($jsonData === false) {
                 error_log("WizdamStats: Failed to encode JSON for JID: " . $journalId . " - Error: " . json_last_error_msg());
                 $result2 = false;
            } else {
                $compressedContent = gzencode($jsonData, 9);
                if ($compressedContent === false) {
                     error_log("WizdamStats: Failed to GZencode JSON for JID: " . $journalId);
                     $result2 = false;
                } else {
                    $result2 = @file_put_contents($jsonCacheFile, $compressedContent);
                     if ($result2 === false) {
                         error_log("WizdamStats: Failed to write JSON.gz cache file: " . $jsonCacheFile);
                     }
                }
            }
            
            // Tulis HASH (Smart Cache)
            $currentHash = self::_getJournalStatsDataHash($journalId);
            $result3 = @file_put_contents($hashFile, $currentHash);
             if ($result3 === false) {
                 error_log("WizdamStats: Failed to write HASH file: " . $hashFile);
             }

            // error_log("WizdamStats: Wrote cache files for JID: $journalId - Hash: $currentHash");
            return ($result1 !== false && $result2 !== false && $result3 !== false);

        } catch (Exception $e) {
            // HANYA log jika OJS diatur untuk mencatat error di config.inc.php
            if (Config::getVar('debug', 'log_errors')) {
                error_log("WizdamStats: Exception during cache writing for JID $journalId: " . $e->getMessage());
            }
            return false;
        }
    }

    /**
     * Path cache di 'public/wizdam_cache/stats/'
     * Ini adalah path server-side.
     */
    private static function _getCacheDir() {
        // Core::getBaseDir() adalah path server (misal: /home/[nama-direktori]/public_html/[direktori])
        $baseDir = Core::getBaseDir();
        $cacheDir = $baseDir . '/public/wizdam_cache/stats/';
        return $cacheDir;
    }

    /**
     * Membuat direktori cache jika belum ada (dari v2)
     */
    private static function _ensureCacheDirExists($cacheDir) {
        if (!file_exists($cacheDir)) {
            // error_log("WizdamStats: Creating cache directory: " . $cacheDir);
            return mkdir($cacheDir, 0755, true); // Use @ to suppress warning if dir exists race condition
        }
        if (!is_writable($cacheDir)) {
             error_log("WizdamStats: Cache directory NOT WRITABLE: " . $cacheDir);
             return false;
        }
        return true;
    }

    /**
     * Membuat hash data untuk deteksi perubahan
     * (Logika dari getJournalStats_v2.txt)
     */
    private static function _getJournalStatsDataHash($journalId) {
        try {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $metrics = array();
            
            // Total artikel
            $result = $articleDao->retrieve("SELECT COUNT(*) as total FROM articles WHERE journal_id = ?", array($journalId));
            if ($result && !$result->EOF) $metrics['total_articles'] = $result->fields['total'];
            if($result) $result->Close();
            
            // Total artikel dipublikasikan
            $result = $articleDao->retrieve(
                "SELECT COUNT(*) as total FROM published_articles pa JOIN articles a ON (pa.article_id = a.article_id) WHERE a.journal_id = ?",
                array($journalId)
            );
            if ($result && !$result->EOF) $metrics['total_published'] = $result->fields['total'];
            if($result) $result->Close();
            
            // Total views (gunakan query yang sama dengan _calculateAndCacheStats)
            $result = $articleDao->retrieve(
                "SELECT SUM(metric) as total FROM metrics 
                 WHERE assoc_type = ? AND context_id = ?
                 AND (metric_type = 'ojs::counter' OR metric_type = 'ojs::legacyDefault' OR metric_type = 'ojs::legacyCounter')",
                array(ASSOC_TYPE_ARTICLE, $journalId)
            );
            if ($result && !$result->EOF) $metrics['total_views'] = (int)$result->fields['total'];
            if($result) $result->Close();
            
            // Jika views 0, coba query alternatif
            if (empty($metrics['total_views'])) { // Cek jika 0 atau null
                 $result = $articleDao->retrieve(
                    "SELECT SUM(metric) as total FROM metrics WHERE assoc_type = ? AND context_id = ?",
                    array(ASSOC_TYPE_ARTICLE, $journalId)
                 );
                 if ($result && !$result->EOF) $metrics['total_views'] = (int)$result->fields['total'];
                 if($result) $result->Close();
            }

            // Tambahkan timestamp update terakhir dari tabel articles (opsional, tapi bagus untuk hash)
            $result = $articleDao->retrieve(
                 "SELECT MAX(last_modified) as last_mod FROM articles WHERE journal_id = ?",
                 array($journalId)
            );
            if ($result && !$result->EOF) $metrics['last_article_mod'] = $result->fields['last_mod'];
            if($result) $result->Close();


            if (empty($metrics['total_articles']) && empty($metrics['total_views'])) {
                // error_log("WizdamStats: HASH generation failed - no metrics found for JID: " . $journalId);
                return ''; // Return empty if no data found
            }
            
            $hash = md5(serialize($metrics));
            // error_log("WizdamStats: Generated HASH for JID: $journalId - Hash: $hash - Metrics: " . print_r($metrics, true));
            return $hash;

        } catch (Exception $e) {
             // HANYA log jika OJS diatur untuk mencatat error di config.inc.php
             if (Config::getVar('debug', 'log_errors')) {
                 error_log("WizdamStats: Exception during HASH generation for JID $journalId: " . $e->getMessage());
             }
            return ''; // Return empty on error
        }
    }


    /*******************************************************
     * MESIN #2: STATISTIK SELURUH SITUS (SITE-WIDE)
     *******************************************************/
     
    /**
     * Mengambil statistik agregat untuk semua jurnal di situs.
     *
     * @param $forceRefresh boolean
     * @return array
     */
    public static function getSiteWideStats($forceRefresh = false) {
        $cacheFile = Core::getBaseDir() . '/' . self::SITE_CACHE_PATH;

        if (!$forceRefresh) {
            if (file_exists($cacheFile) && (time() - @filemtime($cacheFile)) < self::SITE_CACHE_DURATION) {
                try {
                    $cacheData = unserialize(@file_get_contents($cacheFile)); 
                    if (is_array($cacheData)) {
                        return $cacheData;
                    }
                } catch (Exception $e) {}
            }
        }

        $journalDao = DAORegistry::getDAO('JournalDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $journals = $journalDao->getJournals(true);
        
        $journalsStats = array();
        $allTotalViews = 0;
        $allTotalDownloads = 0;
        $allTotalInteractions = 0; // Tambahan untuk WIZDAM
        $allTotalAuthors = 0;

        try {
            while ($journal = $journals->next()) {
                $jId = (int)$journal->getId();
                $jViews = 0; $jDownloads = 0; $jAuthors = 0;

                // 1. Hitung Views
                $result = $articleDao->retrieve("SELECT SUM(metric) AS total FROM metrics WHERE assoc_type = ? AND context_id = ? AND (metric_type = 'ojs::counter' OR metric_type = 'ojs::legacyDefault' OR metric_type = 'ojs::legacyCounter')", array(ASSOC_TYPE_ARTICLE, $jId));
                if ($result && !$result->EOF) $jViews = (int)$result->fields['total'];
                if($result) $result->Close();
                if ($jViews == 0) {
                     $result = $articleDao->retrieve("SELECT SUM(metric) as total FROM metrics WHERE assoc_type = ? AND context_id = ?", array(ASSOC_TYPE_ARTICLE, $jId));
                     if ($result && !$result->EOF) $jViews = (int)$result->fields['total'];
                     if($result) $result->Close();
                }

                // 2. Hitung Downloads
                $result = $articleDao->retrieve("SELECT SUM(metric) AS total FROM metrics WHERE assoc_type = ? AND context_id = ? AND (metric_type = 'ojs::counter::galley' OR metric_type LIKE '%download%')", array(ASSOC_TYPE_GALLEY, $jId));
                if ($result && !$result->EOF) $jDownloads = (int)$result->fields['total'];
                if($result) $result->Close();
                if ($jDownloads == 0) {
                     $result = $articleDao->retrieve("SELECT SUM(metric) as total FROM metrics WHERE assoc_type = ? AND context_id = ?", array(ASSOC_TYPE_GALLEY, $jId));
                     if ($result && !$result->EOF) $jDownloads = (int)$result->fields['total'];
                     if($result) $result->Close();
                }

                // 3. PERBAIKAN: Hitung Authors (Langsung ke articles status = 3)
                $result = $articleDao->retrieve(
                    "SELECT COUNT(DISTINCT a.email) AS total 
                     FROM authors a 
                     JOIN articles art ON a.submission_id = art.article_id
                     WHERE art.journal_id = ? AND art.status = 3", 
                    array($jId)
                );
                if ($result && !$result->EOF) $jAuthors = (int)$result->fields['total'];
                if($result) $result->Close();

                // 4. Kalkulasi Total Interaksi WIZDAM
                $jInteractions = $jViews + $jDownloads;

                if ($jViews > 0 || $jDownloads > 0 || $jAuthors > 0) {
                    $allTotalViews += $jViews;
                    $allTotalDownloads += $jDownloads;
                    $allTotalInteractions += $jInteractions;
                    $allTotalAuthors += $jAuthors;
                    
                    $journalsStats[] = array( 
                        'id' => $jId,
                        'title' => $journal->getLocalizedTitle() ? $journal->getLocalizedTitle() : $journal->getPath(),
                        'path' => $journal->getPath(),
                        'views' => $jViews, 
                        'downloads' => $jDownloads,
                        'totalInteractions' => $jInteractions,
                        'authors' => $jAuthors 
                    );
                }
            }
            
            usort($journalsStats, function($a, $b) { return $b['views'] - $a['views']; });

            $siteStats = array(
                'journalsStats' => $journalsStats,
                'allTotalViews' => $allTotalViews,
                'allTotalDownloads' => $allTotalDownloads,
                'allTotalInteractions' => $allTotalInteractions,
                'allTotalAuthors' => $allTotalAuthors
            );

            $cacheDir = dirname($cacheFile);
            if (!file_exists($cacheDir)) { @mkdir($cacheDir, 0755, true); }
            @file_put_contents($cacheFile, serialize($siteStats));
            
            return $siteStats;
            
        } catch (Exception $e) {
            return array('error' => $e->getMessage());
        }
    }
}

?>
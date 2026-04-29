<?php
/**
 * Journal Statistics
 * @file getJournalStats.php
 * @brief Function untuk statistik jurnal OJS 2.4.8.2 dengan dukungan cache
 * @author Rochmady and Wizdam Team
 * @version v1.24.0 - Smart cache detection with relative paths
 * last update: 29-05-2025
 */

// Konfigurasi Cache - SMART DETECTION + WEEKLY UPDATES
$CACHE_ENABLED = true;
$CACHE_DIR = __DIR__ . '/cache';
$CACHE_DURATION = 604800; // 7 days in seconds

/**
 * Mendapatkan hash data untuk deteksi perubahan
 * @param $journalId int ID jurnal
 * @return string Hash dari data penting
 */
function getJournalStatsDataHash($journalId) {
    try {
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        
        // Ambil beberapa metrik penting untuk hash
        $metrics = array();
        
        // Total artikel
        $result = $articleDao->retrieve(
            "SELECT COUNT(*) as total FROM articles WHERE journal_id = ?",
            array($journalId)
        );
        if ($result && !$result->EOF) {
            $metrics['total_articles'] = $result->fields['total'];
        }
        $result->Close();
        
        // Total artikel dipublikasikan
        $result = $articleDao->retrieve(
            "SELECT COUNT(*) as total FROM published_articles pa 
             JOIN articles a ON pa.article_id = a.article_id 
             WHERE a.journal_id = ?",
            array($journalId)
        );
        if ($result && !$result->EOF) {
            $metrics['published_articles'] = $result->fields['total'];
        }
        $result->Close();
        
        // Artikel terbaru
        $result = $articleDao->retrieve(
            "SELECT MAX(date_submitted) as latest FROM articles WHERE journal_id = ?",
            array($journalId)
        );
        if ($result && !$result->EOF) {
            $metrics['latest_submission'] = $result->fields['latest'];
        }
        $result->Close();
        
        // Publikasi terbaru
        $result = $articleDao->retrieve(
            "SELECT MAX(pa.date_published) as latest 
             FROM published_articles pa 
             JOIN articles a ON pa.article_id = a.article_id 
             WHERE a.journal_id = ?",
            array($journalId)
        );
        if ($result && !$result->EOF) {
            $metrics['latest_publication'] = $result->fields['latest'];
        }
        $result->Close();
        
        // Generate hash dari metrics
        return md5(serialize($metrics));
        
    } catch (Exception $e) {
        error_log("Error generating stats data hash: " . $e->getMessage());
        return md5(time()); // Return unique hash on error
    }
}

/**
 * Memeriksa apakah cache masih valid
 * @param $cacheFile string Path ke file cache
 * @param $currentHash string Hash data saat ini
 * @return bool True jika cache valid
 */
function isJournalStatsCacheValid($cacheFile, $currentHash) {
    global $CACHE_DURATION;
    
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    // Cek umur cache (weekly update)
    $cacheAge = time() - filemtime($cacheFile);
    if ($cacheAge > $CACHE_DURATION) {
        return false;
    }
    
    // Cek hash file
    $hashFile = $cacheFile . '.hash';
    if (file_exists($hashFile)) {
        $storedHash = trim(file_get_contents($hashFile));
        if ($storedHash !== $currentHash) {
            return false; // Data has changed
        }
    }
    
    return true;
}

/**
 * Memastikan direktori cache ada dan writable
 * @param $dir string Path ke direktori
 * @return bool Success
 */
function ensureJournalStatsCacheDirectory($dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log("Failed to create journal stats cache directory: " . $dir);
            return false;
        }
    }
    
    if (!is_writable($dir)) {
        error_log("Journal stats cache directory is not writable: " . $dir);
        return false;
    }
    
    // Create .htaccess for security
    $htaccessFile = $dir . '/.htaccess';
    if (!file_exists($htaccessFile)) {
        file_put_contents($htaccessFile, "Deny from all\n");
    }
    
    return true;
}

/**
 * Menghitung statistik jurnal
 * @param $journalId int ID jurnal
 * @param $forceRefresh bool Paksa menghitung ulang statistik (abaikan cache)
 * @return array Array berisi semua statistik yang dihitung
 */
function getJournalStats($journalId, $forceRefresh = false) {
    global $CACHE_ENABLED, $CACHE_DIR;
    
    // Definisi konstanta ASSOC_TYPE jika belum tersedia
    if (!defined('ASSOC_TYPE_JOURNAL')) define('ASSOC_TYPE_JOURNAL', 256);
    if (!defined('ASSOC_TYPE_ISSUE')) define('ASSOC_TYPE_ISSUE', 257);
    if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
    if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);
    
    try {
        // Setup cache
        if ($CACHE_ENABLED && ensureJournalStatsCacheDirectory($CACHE_DIR)) {
            $cacheFile = $CACHE_DIR . '/journal_' . $journalId . '_stats.php';
            $currentDataHash = getJournalStatsDataHash($journalId);
            
            // Check cache validity
            if (!$forceRefresh && isJournalStatsCacheValid($cacheFile, $currentDataHash)) {
                $cachedData = getJournalStatsFromCache($journalId);
                if ($cachedData !== false) {
                    // Add cache info
                    $cachedData['cacheInfo'] = array(
                        'hit' => true,
                        'hash' => substr($currentDataHash, 0, 8),
                        'expires_at' => date('Y-m-d H:i:s', filemtime($cacheFile) + 604800)
                    );
                    return $cachedData;
                }
            }
        }
        
        // Inisialisasi variabel default
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
        
        // Data historis untuk visualisasi
        $yearlyStats = array();
        
        // Mendapatkan DAO yang diperlukan - inisialisasi sekali saja
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        
        // Fungsi untuk memeriksa struktur database
        function checkDatabaseStructure($articleDao) {
            $info = array(
                'metricsTableExists' => "Tidak",
                'metricsColumns' => "Tidak ditemukan",
                'articleStatsExists' => "Tidak",
                'galleyStatsExists' => "Tidak"
            );
            
            try {
                // Cek tabel metrics
                $result = $articleDao->retrieve("SHOW TABLES LIKE 'metrics'");
                $info['metricsTableExists'] = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
                $result->Close();
                
                // Jika tabel metrics ada, cek kolomnya
                if ($info['metricsTableExists'] == "Ya") {
                    $result = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
                    $columns = array();
                    while (!$result->EOF) {
                        $columns[] = $result->fields[0];
                        $result->MoveNext();
                    }
                    $result->Close();
                    $info['metricsColumns'] = implode(", ", $columns);
                }
                
                // Cek tabel article_view_stats
                $result = $articleDao->retrieve("SHOW TABLES LIKE 'article_view_stats'");
                $info['articleStatsExists'] = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
                $result->Close();
                
                // Cek tabel article_galley_view_stats
                $result = $articleDao->retrieve("SHOW TABLES LIKE 'article_galley_view_stats'");
                $info['galleyStatsExists'] = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
                $result->Close();
            } catch (Exception $e) {
                error_log("OJS Stats Error checking tables: " . $e->getMessage());
            }
            
            return $info;
        }
        
        // Fungsi untuk menghitung median
        function getMedian($arr) {
            if (empty($arr)) return 0;
            sort($arr);
            $count = count($arr);
            $middle = floor(($count - 1) / 2);
            return ($count % 2) ? $arr[$middle] : ($arr[$middle] + $arr[$middle + 1]) / 2;
        }
        
        // Ekstrak judul jurnal jika ID jurnal valid
        if ($journalId > 0) {
            $journal = $journalDao->getJournal($journalId);
            if (is_object($journal)) {
                // Dapatkan judul jurnal
                if (method_exists($journal, 'getLocalizedTitle')) {
                    $journalTitle = $journal->getLocalizedTitle();
                } elseif (method_exists($journal, 'getTitle')) {
                    $journalTitle = $journal->getTitle();
                } else {
                    // Fallback jika metode tidak ditemukan
                    $journalTitle = "ID " . $journalId;
                }
                
                // Pastikan journalTitle adalah string
                if (!is_string($journalTitle)) {
                    $journalTitle = "ID " . $journalId;
                }
                
                // Cek keberadaan tabel metrics dan struktur DB
                $dbInfo = checkDatabaseStructure($articleDao);
                $metricsTableExists = $dbInfo['metricsTableExists'];
                $metricsColumns = $dbInfo['metricsColumns'];
                $articleStatsExists = $dbInfo['articleStatsExists'];
                $galleyStatsExists = $dbInfo['galleyStatsExists'];
                
                // BAGIAN 1: Statistik View dan Download
                try {
                    if ($metricsTableExists == "Ya") {
                        // Cek kolom yang tersedia di tabel metrics terlebih dahulu
                        $availableColumns = array();
                        $columnsResult = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
                        while ($columnsResult && !$columnsResult->EOF) {
                            $availableColumns[] = $columnsResult->fields[0];
                            $columnsResult->MoveNext();
                        }
                        $columnsResult->Close();
                        
                        // Strategi 1: Ambil semua data views dari tabel metrics
                        $viewResult = $articleDao->retrieve(
                            "SELECT SUM(metric) AS total_views FROM metrics 
                             WHERE context_id = ? AND assoc_type = ?",
                            array($journalId, ASSOC_TYPE_ARTICLE)
                        );
                        
                        if ($viewResult && !$viewResult->EOF && $viewResult->fields['total_views']) {
                            $totalViews = (int)$viewResult->fields['total_views'];
                        }
                        $viewResult->Close();
                        
                        // Strategi 2: Ambil semua data downloads dari tabel metrics
                        $downloadResult = $articleDao->retrieve(
                            "SELECT SUM(metric) AS total_downloads FROM metrics 
                             WHERE context_id = ? AND assoc_type = ?",
                            array($journalId, ASSOC_TYPE_GALLEY)
                        );
                        
                        if ($downloadResult && !$downloadResult->EOF && $downloadResult->fields['total_downloads']) {
                            $totalDownloads = (int)$downloadResult->fields['total_downloads'];
                        }
                        $downloadResult->Close();
                        
                        // Jika masih 0, coba dengan metric_type filter
                        if ($totalViews == 0) {
                            $viewResult = $articleDao->retrieve(
                                "SELECT SUM(metric) AS total_views FROM metrics 
                                 WHERE context_id = ? AND assoc_type = ? 
                                 AND (metric_type LIKE '%view%' OR metric_type LIKE '%counter%')",
                                array($journalId, ASSOC_TYPE_ARTICLE)
                            );
                            
                            if ($viewResult && !$viewResult->EOF && $viewResult->fields['total_views']) {
                                $totalViews = (int)$viewResult->fields['total_views'];
                            }
                            $viewResult->Close();
                        }
                        
                        if ($totalDownloads == 0) {
                            $downloadResult = $articleDao->retrieve(
                                "SELECT SUM(metric) AS total_downloads FROM metrics 
                                 WHERE context_id = ? AND assoc_type = ? 
                                 AND (metric_type LIKE '%download%' OR metric_type LIKE '%galley%')",
                                array($journalId, ASSOC_TYPE_GALLEY)
                            );
                            
                            if ($downloadResult && !$downloadResult->EOF && $downloadResult->fields['total_downloads']) {
                                $totalDownloads = (int)$downloadResult->fields['total_downloads'];
                            }
                            $downloadResult->Close();
                        }
                        
                        // Ambil data tahunan REAL dari metrics
                        // Tentukan kolom tanggal yang tersedia
                        $dateColumn = '';
                        if (in_array('day', $availableColumns)) {
                            $dateColumn = 'day';
                        } elseif (in_array('load_time', $availableColumns)) {
                            $dateColumn = 'load_time';
                        } elseif (in_array('entry_time', $availableColumns)) {
                            $dateColumn = 'entry_time';
                        } elseif (in_array('date', $availableColumns)) {
                            $dateColumn = 'date';
                        }
                        
                        // Hanya ambil data tahunan jika kolom tanggal tersedia
                        if (!empty($dateColumn)) {
                            // Data views per tahun
                            $yearlyViewsResult = $articleDao->retrieve(
                                "SELECT YEAR($dateColumn) as year, SUM(metric) as views 
                                 FROM metrics 
                                 WHERE context_id = ? AND assoc_type = ? AND $dateColumn IS NOT NULL
                                 GROUP BY YEAR($dateColumn)
                                 ORDER BY year ASC",
                                array($journalId, ASSOC_TYPE_ARTICLE)
                            );
                            
                            while ($yearlyViewsResult && !$yearlyViewsResult->EOF) {
                                $year = (int)$yearlyViewsResult->fields['year'];
                                if ($year > 1990 && $year <= date('Y')) {
                                    if (!isset($yearlyStats[$year])) {
                                        $yearlyStats[$year] = array(
                                            'year' => $year,
                                            'views' => 0,
                                            'downloads' => 0,
                                            'submissions' => 0,
                                            'published' => 0,
                                            'acceptRate' => 0,
                                            'daysToPublish' => 0,
                                            'daysPerReview' => 0,
                                            'daysToFirstDecision' => 0,
                                            'daysToAcceptance' => 0,
                                            'daysAcceptanceToPublication' => 0
                                        );
                                    }
                                    $yearlyStats[$year]['views'] = (int)$yearlyViewsResult->fields['views'];
                                }
                                $yearlyViewsResult->MoveNext();
                            }
                            if ($yearlyViewsResult) $yearlyViewsResult->Close();
                            
                            // Data downloads per tahun
                            $yearlyDownloadsResult = $articleDao->retrieve(
                                "SELECT YEAR($dateColumn) as year, SUM(metric) as downloads 
                                 FROM metrics 
                                 WHERE context_id = ? AND assoc_type = ? AND $dateColumn IS NOT NULL
                                 GROUP BY YEAR($dateColumn)
                                 ORDER BY year ASC",
                                array($journalId, ASSOC_TYPE_GALLEY)
                            );
                            
                            while ($yearlyDownloadsResult && !$yearlyDownloadsResult->EOF) {
                                $year = (int)$yearlyDownloadsResult->fields['year'];
                                if ($year > 1990 && $year <= date('Y')) {
                                    if (!isset($yearlyStats[$year])) {
                                        $yearlyStats[$year] = array(
                                            'year' => $year,
                                            'views' => 0,
                                            'downloads' => 0,
                                            'submissions' => 0,
                                            'published' => 0,
                                            'acceptRate' => 0,
                                            'daysToPublish' => 0,
                                            'daysPerReview' => 0,
                                            'daysToFirstDecision' => 0,
                                            'daysToAcceptance' => 0,
                                            'daysAcceptanceToPublication' => 0
                                        );
                                    }
                                    $yearlyStats[$year]['downloads'] = (int)$yearlyDownloadsResult->fields['downloads'];
                                }
                                $yearlyDownloadsResult->MoveNext();
                            }
                            if ($yearlyDownloadsResult) $yearlyDownloadsResult->Close();
                        }
                    }
                    
                    // Fallback sederhana HANYA untuk total views/downloads
                    if ($totalViews == 0 && $totalDownloads == 0) {
                        // Coba tabel article_view_stats
                        if ($articleStatsExists == "Ya") {
                            $result = $articleDao->retrieve(
                                "SELECT SUM(views) AS total_views FROM article_view_stats avs
                                 JOIN articles a ON avs.article_id = a.article_id
                                 WHERE a.journal_id = ?",
                                array($journalId)
                            );
                            if ($result && !$result->EOF && $result->fields['total_views']) {
                                $totalViews = (int)$result->fields['total_views'];
                            }
                            $result->Close();
                        }
                        
                        // Coba tabel article_galley_view_stats
                        if ($galleyStatsExists == "Ya") {
                            $result = $articleDao->retrieve(
                                "SELECT SUM(views) AS total_downloads FROM article_galley_view_stats agvs
                                 JOIN article_galleys ag ON agvs.galley_id = ag.galley_id
                                 JOIN articles a ON ag.article_id = a.article_id
                                 WHERE a.journal_id = ?",
                                array($journalId)
                            );
                            if ($result && !$result->EOF && $result->fields['total_downloads']) {
                                $totalDownloads = (int)$result->fields['total_downloads'];
                            }
                            $result->Close();
                        }
                    }
                    
                    // Log hasil untuk debugging
                    error_log("Journal ID $journalId - Total Views: $totalViews, Total Downloads: $totalDownloads");
                    
                } catch (Exception $e) {
                    error_log("Error getting view statistics: " . $e->getMessage());
                }
                
                // Kumpulkan statistik tahunan untuk submissions, published
                try {
                    $submissionsResult = $articleDao->retrieve(
                        "SELECT YEAR(date_submitted) as year, COUNT(*) as submissions 
                         FROM articles 
                         WHERE journal_id = ? 
                         GROUP BY YEAR(date_submitted) 
                         ORDER BY YEAR(date_submitted) ASC",
                        array($journalId)
                    );
                    
                    while ($submissionsResult && !$submissionsResult->EOF) {
                        $year = $submissionsResult->fields['year'];
                        if (!isset($yearlyStats[$year])) {
                            $yearlyStats[$year] = array(
                                'year' => (int)$year,
                                'views' => 0,
                                'downloads' => 0,
                                'submissions' => 0,
                                'published' => 0,
                                'acceptRate' => 0,
                                'daysToPublish' => 0,
                                'daysPerReview' => 0,
                                'daysToFirstDecision' => 0,
                                'daysToAcceptance' => 0,
                                'daysAcceptanceToPublication' => 0
                            );
                        }
                        $yearlyStats[$year]['submissions'] = (int)$submissionsResult->fields['submissions'];
                        $submissionsResult->MoveNext();
                    }
                    $submissionsResult->Close();
                    
                    $publishedResult = $articleDao->retrieve(
                        "SELECT YEAR(pa.date_published) as year, COUNT(*) as published 
                         FROM published_articles pa 
                         JOIN articles a ON (pa.article_id = a.article_id) 
                         WHERE a.journal_id = ? 
                         GROUP BY YEAR(pa.date_published) 
                         ORDER BY YEAR(pa.date_published) ASC",
                        array($journalId)
                    );
                    
                    while ($publishedResult && !$publishedResult->EOF) {
                        $year = $publishedResult->fields['year'];
                        if (!isset($yearlyStats[$year])) {
                            $yearlyStats[$year] = array(
                                'year' => (int)$year,
                                'views' => 0,
                                'downloads' => 0,
                                'submissions' => 0,
                                'published' => 0,
                                'acceptRate' => 0,
                                'daysToPublish' => 0,
                                'daysPerReview' => 0,
                                'daysToFirstDecision' => 0,
                                'daysToAcceptance' => 0,
                                'daysAcceptanceToPublication' => 0
                            );
                        }
                        $yearlyStats[$year]['published'] = (int)$publishedResult->fields['published'];
                        $publishedResult->MoveNext();
                    }
                    $publishedResult->Close();
                    
                    // Hitung acceptance rate per tahun
                    foreach ($yearlyStats as $year => $data) {
                        if ($data['submissions'] > 0) {
                            $yearlyStats[$year]['acceptRate'] = round(($data['published'] / $data['submissions']) * 100, 1);
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error getting submission statistics: " . $e->getMessage());
                }
                
                // BAGIAN 2: Statistik Review dan Timeline
                try {
                    // Array untuk menyimpan data statistik individual
                    $daysReview = array();
                    $daysPublication = array();
                    $daysFirstDecision = array();
                    $daysSubmissionToAcceptance = array();
                    $daysAcceptanceToPublication = array();
                    
                    // Tentukan tahun statistik - menggunakan semua tahun kecuali tahun berjalan
                    $currentYear = date('Y');
                    
                    // Query untuk mendapatkan tahun submisi paling awal
                    $result = $articleDao->retrieve(
                        "SELECT MIN(YEAR(date_submitted)) AS start_year FROM articles WHERE journal_id = ?", 
                        array($journalId)
                    );
                    
                    $startYear = $currentYear - 3; // Nilai default jika query tidak berhasil
                    if ($result->RecordCount() > 0 && $result->fields['start_year']) {
                        $startYear = (int)$result->fields['start_year'];
                    }
                    $result->Close();
                    
                    // Untuk menghitung accept/decline rate - gunakan data keseluruhan
                    $result = $articleDao->retrieve(
                        "SELECT COUNT(*) AS total FROM articles 
                         WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status IN (2, 3, 4)", 
                        array($journalId, $currentYear)
                    );
                    $totalReviewed = 0;
                    if ($result->RecordCount() > 0) {
                        $totalReviewed = (int)$result->fields['total'];
                    }
                    $result->Close();
                    
                    $result = $articleDao->retrieve(
                        "SELECT COUNT(*) AS total FROM articles 
                         WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status = 3", 
                        array($journalId, $currentYear)
                    );
                    $totalAccepted = 0;
                    if ($result->RecordCount() > 0) {
                        $totalAccepted = (int)$result->fields['total'];
                    }
                    $result->Close();
                    
                    $result = $articleDao->retrieve(
                        "SELECT COUNT(*) AS total FROM articles 
                         WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status = 4", 
                        array($journalId, $currentYear)
                    );
                    $totalDeclined = 0;
                    if ($result->RecordCount() > 0) {
                        $totalDeclined = (int)$result->fields['total'];
                    }
                    $result->Close();
                    
                    // Hitung rate berdasarkan total
                    $acceptRate = ($totalReviewed > 0) ? ($totalAccepted * 100 / $totalReviewed) : 0;
                    $declineRate = ($totalReviewed > 0) ? ($totalDeclined * 100 / $totalReviewed) : 0;
                    
                    // Dapatkan data waktu review untuk SEMUA review yang selesai
                    $result = $reviewAssignmentDao->retrieve(
                        "SELECT DATEDIFF(date_completed, date_notified) AS days_to_review 
                         FROM review_assignments 
                         WHERE date_completed IS NOT NULL AND declined = 0 AND cancelled = 0 
                         AND YEAR(date_notified) < ?",
                        array($currentYear)
                    );
                    
                    while ($result && !$result->EOF) {
                        $days = (float)$result->fields['days_to_review'];
                        if ($days > 0) {
                            $daysReview[] = $days;
                        }
                        $result->MoveNext();
                    }
                    $result->Close();
                    
                    // Dapatkan data waktu publikasi untuk SEMUA artikel yang dipublikasikan
                    $result = $articleDao->retrieve(
                        "SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days_to_publication
                         FROM published_articles pa 
                         JOIN articles a ON (pa.article_id = a.article_id) 
                         WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? 
                         AND pa.date_published IS NOT NULL",
                        array($journalId, $currentYear)
                    );
                    
                    while ($result && !$result->EOF) {
                        $days = (float)$result->fields['days_to_publication'];
                        if ($days > 0) {
                            $daysPublication[] = $days;
                        }
                        $result->MoveNext();
                    }
                    $result->Close();
                    
                    // Cek keberadaan tabel edit_decisions
                    $checkTableResult = $articleDao->retrieve("SHOW TABLES LIKE 'edit_decisions'");
                    $editDecisionsTableExists = ($checkTableResult->RecordCount() > 0);
                    $checkTableResult->Close();
                    
                    if ($editDecisionsTableExists) {
                        // 1. Submission to First Decision - data individual
                        $result = $articleDao->retrieve(
                            "SELECT a.article_id, a.date_submitted, MIN(ed.date_decided) as first_decision
                             FROM articles a
                             JOIN edit_decisions ed ON (a.article_id = ed.article_id)
                             WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? 
                             AND ed.date_decided IS NOT NULL
                             GROUP BY a.article_id",
                            array($journalId, $currentYear)
                        );
                        
                        while ($result && !$result->EOF) {
                            $submissionDate = strtotime($result->fields['date_submitted']);
                            $decisionDate = strtotime($result->fields['first_decision']);
                            
                            if ($submissionDate && $decisionDate) {
                                $daysDiff = round(($decisionDate - $submissionDate) / (60*60*24));
                                if ($daysDiff > 0) {
                                    $daysFirstDecision[] = $daysDiff;
                                }
                            }
                            $result->MoveNext();
                        }
                        $result->Close();
                        
                        // 2. Submission to Acceptance
                        $result = $articleDao->retrieve(
                            "SELECT a.article_id, a.date_submitted, MAX(ed.date_decided) as acceptance_date
                            FROM articles a
                            JOIN edit_decisions ed ON (a.article_id = ed.article_id)
                            WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ?
                            AND ed.decision = 1 
                            GROUP BY a.article_id",
                           array($journalId, $currentYear)
                       );
                       
                       while ($result && !$result->EOF) {
                           $submissionDate = strtotime($result->fields['date_submitted']);
                           $acceptanceDate = strtotime($result->fields['acceptance_date']);
                           
                           if ($submissionDate && $acceptanceDate) {
                               $daysDiff = round(($acceptanceDate - $submissionDate) / (60*60*24));
                               if ($daysDiff > 0) {
                                   $daysSubmissionToAcceptance[] = $daysDiff;
                               }
                           }
                           $result->MoveNext();
                       }
                       $result->Close();
                       
                       // 3. Acceptance to Publication
                       $result = $articleDao->retrieve(
                           "SELECT a.article_id, MAX(ed.date_decided) as acceptance_date, pa.date_published
                            FROM articles a
                            JOIN edit_decisions ed ON (a.article_id = ed.article_id)
                            JOIN published_articles pa ON (a.article_id = pa.article_id)
                            WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ?
                            AND ed.decision = 1 AND pa.date_published IS NOT NULL
                            GROUP BY a.article_id",
                           array($journalId, $currentYear)
                       );
                       
                       while ($result && !$result->EOF) {
                           $acceptanceDate = strtotime($result->fields['acceptance_date']);
                           $publishDate = strtotime($result->fields['date_published']);
                           
                           if ($acceptanceDate && $publishDate) {
                               $daysDiff = round(($publishDate - $acceptanceDate) / (60*60*24));
                               if ($daysDiff > 0) {
                                   $daysAcceptanceToPublication[] = $daysDiff;
                               }
                           }
                           $result->MoveNext();
                       }
                       $result->Close();
                   } else {
                       // Fallback jika tabel edit_decisions tidak tersedia
                       // Gunakan data dari artikel dan tanggal publikasi
                       
                       // Untuk Submission to Acceptance, gunakan status artikel 
                       $result = $articleDao->retrieve(
                           "SELECT a.article_id, a.date_submitted, a.date_status_modified
                            FROM articles a
                            WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ?
                            AND a.status = 3", // status 3 = diterima
                           array($journalId, $currentYear)
                       );
                       
                       while ($result && !$result->EOF) {
                           $submissionDate = strtotime($result->fields['date_submitted']);
                           $acceptanceDate = strtotime($result->fields['date_status_modified']);
                           
                           if ($submissionDate && $acceptanceDate) {
                               $daysDiff = round(($acceptanceDate - $submissionDate) / (60*60*24));
                               if ($daysDiff > 0) {
                                   // Gunakan untuk first decision dan submission to acceptance
                                   $daysFirstDecision[] = $daysDiff;
                                   $daysSubmissionToAcceptance[] = $daysDiff;
                               }
                           }
                           $result->MoveNext();
                       }
                       $result->Close();
                       
                       // Untuk Acceptance to Publication, bandingkan date_status_modified dengan date_published
                       $result = $articleDao->retrieve(
                           "SELECT a.article_id, a.date_status_modified, pa.date_published
                            FROM articles a
                            JOIN published_articles pa ON (a.article_id = pa.article_id)
                            WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ?
                            AND a.status = 3 AND pa.date_published IS NOT NULL",
                           array($journalId, $currentYear)
                       );
                       
                       while ($result && !$result->EOF) {
                           $acceptanceDate = strtotime($result->fields['date_status_modified']);
                           $publishDate = strtotime($result->fields['date_published']);
                           
                           if ($acceptanceDate && $publishDate) {
                               $daysDiff = round(($publishDate - $acceptanceDate) / (60*60*24));
                               if ($daysDiff > 0) {
                                   $daysAcceptanceToPublication[] = $daysDiff;
                               }
                           }
                           $result->MoveNext();
                       }
                       $result->Close();
                   }
                   
                   // Gunakan median untuk statistik waktu
                   $daysPerReview = getMedian($daysReview);
                   $daysToPublication = getMedian($daysPublication);
                   $submissionToFirstDecision = getMedian($daysFirstDecision);
                   $submissionToAcceptance = getMedian($daysSubmissionToAcceptance);
                   $acceptanceToPublication = getMedian($daysAcceptanceToPublication);
                   
                   // Statistik waktu per tahun
                   for ($year = $startYear; $year < $currentYear; $year++) {
                       // Waktu review per tahun
                       $yearDaysReview = array();
                       $result = $reviewAssignmentDao->retrieve(
                           "SELECT DATEDIFF(date_completed, date_notified) AS days_to_review 
                            FROM review_assignments 
                            WHERE date_completed IS NOT NULL AND declined = 0 AND cancelled = 0 
                            AND YEAR(date_notified) = ?",
                           array($year)
                       );
                       
                       while ($result && !$result->EOF) {
                           $days = (float)$result->fields['days_to_review'];
                           if ($days > 0) {
                               $yearDaysReview[] = $days;
                           }
                           $result->MoveNext();
                       }
                       $result->Close();
                       
                       // Waktu publikasi per tahun
                       $yearDaysToPublish = array();
                       $result = $articleDao->retrieve(
                           "SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days_to_publication
                            FROM published_articles pa 
                            JOIN articles a ON (pa.article_id = a.article_id) 
                            WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ? 
                            AND pa.date_published IS NOT NULL",
                           array($journalId, $year)
                       );
                       
                       while ($result && !$result->EOF) {
                           $days = (float)$result->fields['days_to_publication'];
                           if ($days > 0) {
                               $yearDaysToPublish[] = $days;
                           }
                           $result->MoveNext();
                       }
                       $result->Close();
                       
                       // Waktu ke keputusan pertama per tahun
                       $yearDaysToFirstDecision = array();
                       if ($editDecisionsTableExists) {
                           $result = $articleDao->retrieve(
                               "SELECT a.article_id, a.date_submitted, MIN(ed.date_decided) as first_decision
                                FROM articles a
                                JOIN edit_decisions ed ON (a.article_id = ed.article_id)
                                WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ? 
                                AND ed.date_decided IS NOT NULL
                                GROUP BY a.article_id",
                               array($journalId, $year)
                           );
                           
                           while ($result && !$result->EOF) {
                               $submissionDate = strtotime($result->fields['date_submitted']);
                               $decisionDate = strtotime($result->fields['first_decision']);
                               
                               if ($submissionDate && $decisionDate) {
                                   $daysDiff = round(($decisionDate - $submissionDate) / (60*60*24));
                                   if ($daysDiff > 0) {
                                       $yearDaysToFirstDecision[] = $daysDiff;
                                   }
                               }
                               $result->MoveNext();
                           }
                           $result->Close();
                       }
                       
                       // Waktu dari submisi hingga penerimaan per tahun
                       $yearDaysToAcceptance = array();
                       if ($editDecisionsTableExists) {
                           $result = $articleDao->retrieve(
                               "SELECT a.article_id, a.date_submitted, MAX(ed.date_decided) as acceptance_date
                                FROM articles a
                                JOIN edit_decisions ed ON (a.article_id = ed.article_id)
                                WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ?
                                AND ed.decision = 1 
                                GROUP BY a.article_id",
                               array($journalId, $year)
                           );
                           
                           while ($result && !$result->EOF) {
                               $submissionDate = strtotime($result->fields['date_submitted']);
                               $acceptanceDate = strtotime($result->fields['acceptance_date']);
                               
                               if ($submissionDate && $acceptanceDate) {
                                   $daysDiff = round(($acceptanceDate - $submissionDate) / (60*60*24));
                                   if ($daysDiff > 0) {
                                       $yearDaysToAcceptance[] = $daysDiff;
                                   }
                               }
                               $result->MoveNext();
                           }
                           $result->Close();
                       }
                       
                       // Waktu dari penerimaan hingga publikasi per tahun
                       $yearDaysAcceptanceToPublication = array();
                       if ($editDecisionsTableExists) {
                           $result = $articleDao->retrieve(
                               "SELECT a.article_id, MAX(ed.date_decided) as acceptance_date, pa.date_published
                                FROM articles a
                                JOIN edit_decisions ed ON (a.article_id = ed.article_id)
                                JOIN published_articles pa ON (a.article_id = pa.article_id)
                                WHERE a.journal_id = ? AND YEAR(a.date_submitted) = ?
                                AND ed.decision = 1 AND pa.date_published IS NOT NULL
                                GROUP BY a.article_id",
                               array($journalId, $year)
                           );
                           
                           while ($result && !$result->EOF) {
                               $acceptanceDate = strtotime($result->fields['acceptance_date']);
                               $publishDate = strtotime($result->fields['date_published']);
                               
                               if ($acceptanceDate && $publishDate) {
                                   $daysDiff = round(($publishDate - $acceptanceDate) / (60*60*24));
                                   if ($daysDiff > 0) {
                                       $yearDaysAcceptanceToPublication[] = $daysDiff;
                                   }
                               }
                               $result->MoveNext();
                           }
                           $result->Close();
                       }
                       
                       // Tambahkan statistik waktu ke array tahunan
                       if (!isset($yearlyStats[$year])) {
                           $yearlyStats[$year] = array(
                               'year' => (int)$year,
                               'views' => 0,
                               'downloads' => 0,
                               'submissions' => 0,
                               'published' => 0,
                               'acceptRate' => 0,
                               'daysToPublish' => 0,
                               'daysPerReview' => 0, 
                               'daysToFirstDecision' => 0,
                               'daysToAcceptance' => 0,
                               'daysAcceptanceToPublication' => 0
                           );
                       }
                       
                       $yearlyStats[$year]['daysToPublish'] = round(getMedian($yearDaysToPublish));
                       $yearlyStats[$year]['daysPerReview'] = round(getMedian($yearDaysReview));
                       $yearlyStats[$year]['daysToFirstDecision'] = round(getMedian($yearDaysToFirstDecision));
                       $yearlyStats[$year]['daysToAcceptance'] = round(getMedian($yearDaysToAcceptance));
                       $yearlyStats[$year]['daysAcceptanceToPublication'] = round(getMedian($yearDaysAcceptanceToPublication));
                   }
               } catch (Exception $e) {
                   error_log("Error getting review statistics: " . $e->getMessage());
               }
               
               // BAGIAN 3: Publikasi
               try {
                   // Hitung total artikel yang dipublikasikan
                   if (method_exists($publishedArticleDao, 'getPublishedArticleCountByJournalId')) {
                       $totalArticles = $publishedArticleDao->getPublishedArticleCountByJournalId($journalId);
                   } else {
                       $result = $articleDao->retrieve(
                           "SELECT COUNT(*) AS total FROM published_articles pa 
                            JOIN articles a ON (pa.article_id = a.article_id) 
                            WHERE a.journal_id = ?",
                           array($journalId)
                       );
                       
                       if ($result->RecordCount() > 0) {
                           $totalArticles = $result->fields['total'];
                       }
                       $result->Close();
                   }
                   
                   // Hitung jumlah terbitan/issue yang telah dipublikasikan
                   if (method_exists($issueDao, 'getPublishedIssueCount')) {
                       $totalIssues = $issueDao->getPublishedIssueCount($journalId);
                   } else if (method_exists($issueDao, 'getIssueCount')) {
                       $totalIssues = $issueDao->getIssueCount($journalId, true);
                   } else {
                       $result = $articleDao->retrieve(
                           "SELECT COUNT(*) AS total FROM issues 
                            WHERE journal_id = ? AND published = 1",
                           array($journalId)
                       );
                       
                       if ($result->RecordCount() > 0) {
                           $totalIssues = $result->fields['total'];
                       }
                       $result->Close();
                   }
                   
                   // Hitung artikel per terbitan/issue
                   $articlesPerIssue = ($totalIssues > 0) ? ($totalArticles / $totalIssues) : 0;
                   
                   // Mendapatkan tahun terakhir publikasi dan jumlah artikel
                   $result = $articleDao->retrieve(
                       "SELECT YEAR(date_published) as publication_year, COUNT(*) as article_count
                        FROM published_articles pa 
                        JOIN articles a ON (pa.article_id = a.article_id) 
                        WHERE a.journal_id = ? AND pa.date_published IS NOT NULL
                        GROUP BY YEAR(date_published)
                        ORDER BY YEAR(date_published) DESC
                        LIMIT 1",
                       array($journalId)
                   );
                   
                   if ($result->RecordCount() > 0) {
                       $lastPublicationYear = $result->fields['publication_year'];
                       $lastYearArticleCount = $result->fields['article_count'];
                   }
                   $result->Close();
               } catch (Exception $e) {
                   error_log("Error getting publication statistics: " . $e->getMessage());
               }
               
               // Verifikasi konsistensi data (HANYA untuk views/downloads)
               $totalPublishedFromYears = 0;
               $totalViewsFromYears = 0;
               $totalDownloadsFromYears = 0;
               
               foreach ($yearlyStats as $year => $data) {
                   $totalPublishedFromYears += $data['published'];
                   $totalViewsFromYears += $data['views'];
                   $totalDownloadsFromYears += $data['downloads'];
               }
               
               // Log untuk debugging
               error_log("Stats verification - Total views: $totalViews, Sum from years: $totalViewsFromYears");
               error_log("Stats verification - Total downloads: $totalDownloads, Sum from years: $totalDownloadsFromYears");
               
               // Perbaiki jika ada ketidaksesuaian
               if ($totalViews > 0 && $totalViewsFromYears === 0) {
                   // Distribusikan total views ke tahun-tahun berdasarkan proporsi artikel
                   foreach ($yearlyStats as $year => $data) {
                       if ($totalPublishedFromYears > 0 && $data['published'] > 0) {
                           $proportion = $data['published'] / $totalPublishedFromYears;
                           $yearlyStats[$year]['views'] = round($totalViews * $proportion);
                       }
                   }
               }
               
               if ($totalDownloads > 0 && $totalDownloadsFromYears === 0) {
                   // Distribusikan total downloads ke tahun-tahun berdasarkan proporsi artikel
                   foreach ($yearlyStats as $year => $data) {
                       if ($totalPublishedFromYears > 0 && $data['published'] > 0) {
                           $proportion = $data['published'] / $totalPublishedFromYears;
                           $yearlyStats[$year]['downloads'] = round($totalDownloads * $proportion);
                       }
                   }
               }
           }
       }
       
       // Siapkan hasil
       $stats = array(
           'journalId' => $journalId,
           'journalTitle' => $journalTitle,
           'totalViews' => $totalViews,
           'totalDownloads' => $totalDownloads,
           'acceptRate' => round($acceptRate, 1),
           'declineRate' => round($declineRate, 1),
           'daysPerReview' => round($daysPerReview),
           'daysToPublication' => round($daysToPublication),
           'submissionToFirstDecision' => round($submissionToFirstDecision),
           'submissionToAcceptance' => round($submissionToAcceptance),
           'acceptanceToPublication' => round($acceptanceToPublication),
           'totalArticles' => $totalArticles,
           'totalIssues' => $totalIssues,
           'articlesPerIssue' => round($articlesPerIssue, 1),
           'lastPublicationYear' => $lastPublicationYear,
           'lastYearArticleCount' => $lastYearArticleCount,
           'calculationDate' => date('Y-m-d H:i:s'),
           'yearlyStats' => array_values($yearlyStats),
           // Tambahkan variabel baru untuk template
           'journalTotalViews' => $totalViews,
           'journalTotalDownloads' => $totalDownloads,
           'lastUpdated' => date('Y-m-d H:i:s')
       );
       
       // Simpan hasil dalam cache
       if ($CACHE_ENABLED) {
           $cacheResult = cacheJournalStats($journalId, $stats, $currentDataHash);
           if (!$cacheResult) {
               error_log("Failed to cache journal stats for journal ID: " . $journalId);
           }
       }
       
       // Add cache info
       $stats['cacheInfo'] = array(
           'hit' => false,
           'hash' => substr($currentDataHash, 0, 8),
           'generated_at' => date('Y-m-d H:i:s')
       );
       
       return $stats;
   } catch (Exception $e) {
       error_log("Fatal error in OJS statistics module: " . $e->getMessage());
       return array('error' => 'Error calculating statistics: ' . $e->getMessage());
   }
}

/**
* Mendapatkan statistik jurnal dari cache
* @param $journalId int ID jurnal
* @return array|bool Array statistik atau false jika cache tidak ditemukan/tidak valid
*/
function getJournalStatsFromCache($journalId) {
   global $CACHE_DIR;
   
   $cacheFile = $CACHE_DIR . '/journal_' . $journalId . '_stats.php';
   $jsonCacheFile = $CACHE_DIR . '/journal_' . $journalId . '_stats.json.gz';
   
   // Periksa apakah file cache ada
   if (!file_exists($cacheFile)) {
       return false;
   }
   
   // Baca file cache
   $cacheContent = file_get_contents($cacheFile);
   if ($cacheContent === false) {
       return false;
   }
   
   // Format file cache adalah PHP array yang aman
   // dengan format yang dimulai dengan komentar PHP untuk mencegah akses langsung
   $cacheContent = preg_replace('/^<\?php exit\(\); \?>/', '', $cacheContent);
   $cachedStats = unserialize($cacheContent);
   
   return $cachedStats;
}

/**
* Menyimpan statistik jurnal dalam cache
* @param $journalId int ID jurnal
* @param $stats array Statistik yang akan disimpan
* @param $dataHash string Hash data untuk tracking perubahan
* @return bool Berhasil atau tidak
*/
function cacheJournalStats($journalId, $stats, $dataHash) {
   global $CACHE_DIR;
   
   // Siapkan direktori cache
   if (!ensureJournalStatsCacheDirectory($CACHE_DIR)) {
       error_log("Failed to create cache directory: " . $CACHE_DIR);
       return false;
   }
   
   // File cache reguler
   $cacheFile = $CACHE_DIR . '/journal_' . $journalId . '_stats.php';
   
   // Simpan statistik ke file cache
   // Tambahkan header PHP di awal file untuk mencegah akses langsung
   $cacheContent = '<?php exit(); ?>' . serialize($stats);
   $result1 = file_put_contents($cacheFile, $cacheContent);
   
   // Simpan hash
   $hashFile = $cacheFile . '.hash';
   file_put_contents($hashFile, $dataHash);
   
   // Log jika gagal menyimpan file cache utama
   if ($result1 === false) {
       error_log("Failed to write main cache file: " . $cacheFile);
   }
   
   // File cache JSON terkompresi (untuk visualisasi)
   $jsonCacheFile = $CACHE_DIR . '/journal_' . $journalId . '_stats.json.gz';
   
   // Siapkan data untuk visualisasi
   $jsonData = array(
       'journalId' => $journalId,
       'journalTitle' => $stats['journalTitle'],
       'calculationDate' => $stats['calculationDate'],
       'yearlyStats' => $stats['yearlyStats'],
       'journalTotalViews' => $stats['totalViews'],
       'journalTotalDownloads' => $stats['totalDownloads'],
       'dataHash' => $dataHash
   );
   
   // Compress and save as JSON.gz
   try {
       // Enkode data ke format JSON dengan penanganan error
       $jsonOptions = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
       if (defined('JSON_PRETTY_PRINT')) {
           $jsonOptions |= JSON_PRETTY_PRINT;
       }
       
       $jsonContent = json_encode($jsonData, $jsonOptions);
       
       // Periksa error JSON
       if ($jsonContent === false) {
           throw new Exception("JSON encoding failed: " . json_last_error_msg());
       }
       
       // Pastikan fungsi gzencode tersedia
       if (!function_exists('gzencode')) {
           file_put_contents($CACHE_DIR . '/journal_' . $journalId . '_stats.json', $jsonContent);
           error_log("GZIP compression not available, using plain JSON instead");
           return ($result1 !== false);
       }
       
       // Kompres data JSON
       $compressedContent = gzencode($jsonContent, 9);
       if ($compressedContent === false) {
           throw new Exception("GZIP compression failed");
       }
       
       // Simpan file
       $result2 = file_put_contents($jsonCacheFile, $compressedContent);
       if ($result2 === false) {
           throw new Exception("Failed to write JSON.gz cache file");
       }
       
       // Log ukuran file untuk debugging
       error_log("JSON.gz cache file written: " . $jsonCacheFile . " (" . strlen($compressedContent) . " bytes)");
       
       return ($result1 !== false && $result2 !== false);
   } catch (Exception $e) {
       error_log("Error creating JSON.gz cache: " . $e->getMessage());
       // Fallback: save uncompressed JSON
       file_put_contents($CACHE_DIR . '/journal_' . $journalId . '_stats.json', json_encode($jsonData));
       return false;
   }
}

/**
* Mengambil data statistik dalam format JSON terkompresi
* @param $journalId int ID jurnal
* @return string|bool Konten JSON terkompresi atau false jika gagal
*/
function getJournalStatsJsonGz($journalId) {
   global $CACHE_DIR;
   
   $jsonCacheFile = $CACHE_DIR . '/journal_' . $journalId . '_stats.json.gz';
   
   // Periksa apakah file cache ada
   if (!file_exists($jsonCacheFile)) {
       // Jika tidak ada, coba buat cache baru
       $stats = getJournalStats($journalId);
       if (isset($stats['error'])) {
           return false;
       }
       // Cache sudah dibuat dalam fungsi getJournalStats
   }
   
   // Baca file cache JSON terkompresi
   $compressedContent = file_get_contents($jsonCacheFile);
   if ($compressedContent === false) {
       return false;
   }
   
   return $compressedContent;
}

/**
* Memeriksa file JSON.gz
* @param $journalId int ID jurnal
* @return array Informasi diagnosa
*/
function checkJsonGzFile($journalId) {
   global $CACHE_DIR;
   
   $jsonCacheFile = $CACHE_DIR . '/journal_' . $journalId . '_stats.json.gz';
   
   $result = array(
       'exists' => file_exists($jsonCacheFile),
       'size' => 0,
       'modified' => '',
       'content_sample' => '',
       'valid_json' => false,
       'has_yearly_stats' => false
   );
   
   if ($result['exists']) {
       $result['size'] = filesize($jsonCacheFile);
       $result['modified'] = date('Y-m-d H:i:s', filemtime($jsonCacheFile));
       
       // Baca dan dekompresi file
       $compressedContent = file_get_contents($jsonCacheFile);
       if ($compressedContent !== false) {
           try {
               $decompressedContent = gzdecode($compressedContent);
               if ($decompressedContent !== false) {
                   // Sampel konten (max 100 karakter)
                   $result['content_sample'] = substr($decompressedContent, 0, 100) . '...';
                   
                   // Cek apakah JSON valid
                   $jsonData = json_decode($decompressedContent, true);
                   $result['valid_json'] = (json_last_error() == JSON_ERROR_NONE);
                   
                   // Cek apakah ada yearlyStats
                   if ($result['valid_json'] && isset($jsonData['yearlyStats']) && is_array($jsonData['yearlyStats'])) {
                       $result['has_yearly_stats'] = count($jsonData['yearlyStats']) > 0;
                       $result['yearly_stats_count'] = count($jsonData['yearlyStats']);
                       
                       // Cek juga konten yearlyStats
                       $hasViews = false;
                       $hasDownloads = false;
                       foreach ($jsonData['yearlyStats'] as $yearStat) {
                           if (isset($yearStat['views']) && $yearStat['views'] > 0) {
                               $hasViews = true;
                           }
                           if (isset($yearStat['downloads']) && $yearStat['downloads'] > 0) {
                               $hasDownloads = true;
                           }
                       }
                       $result['has_views_data'] = $hasViews;
                       $result['has_downloads_data'] = $hasDownloads;
                       
                       // Cek total statistik
                       $result['total_views'] = isset($jsonData['journalTotalViews']) ? $jsonData['journalTotalViews'] : 'Not Set';
                       $result['total_downloads'] = isset($jsonData['journalTotalDownloads']) ? $jsonData['journalTotalDownloads'] : 'Not Set';
                       
                       // Cek hash
                       $result['data_hash'] = isset($jsonData['dataHash']) ? substr($jsonData['dataHash'], 0, 8) : 'Not Set';
                   }
               }
           } catch (Exception $e) {
               $result['error'] = $e->getMessage();
           }
       }
   }
   
   return $result;
}

/**
* Membersihkan cache lama
* @param $daysOld int Hapus cache yang lebih tua dari X hari
* @return int Jumlah file yang dihapus
*/
function cleanOldJournalStatsCache($daysOld = 30) {
   global $CACHE_DIR;
   
   if (!is_dir($CACHE_DIR)) {
       return 0;
   }
   
   $deletedCount = 0;
   $oldTimestamp = time() - ($daysOld * 86400);
   
   // Scan directory for cache files
   $files = glob($CACHE_DIR . '/journal_*_stats.*');
   
   foreach ($files as $file) {
       if (filemtime($file) < $oldTimestamp) {
           if (unlink($file)) {
               $deletedCount++;
           }
       }
   }
   
   return $deletedCount;
}

/**
* Mendapatkan informasi cache untuk semua jurnal
* @return array Informasi cache
*/
function getAllJournalsCacheInfo() {
   global $CACHE_DIR;
   
   $cacheInfo = array(
       'cache_dir' => $CACHE_DIR,
       'cache_enabled' => $GLOBALS['CACHE_ENABLED'],
       'total_size' => 0,
       'journals' => array()
   );
   
   if (!is_dir($CACHE_DIR)) {
       return $cacheInfo;
   }
   
   // Scan for all journal cache files
   $phpFiles = glob($CACHE_DIR . '/journal_*_stats.php');
   
   foreach ($phpFiles as $phpFile) {
       // Extract journal ID from filename
       if (preg_match('/journal_(\d+)_stats\.php$/', $phpFile, $matches)) {
           $journalId = $matches[1];
           
           $journalCache = array(
               'journal_id' => $journalId,
               'php_file' => basename($phpFile),
               'php_size' => filesize($phpFile),
               'modified' => date('Y-m-d H:i:s', filemtime($phpFile)),
               'age_hours' => round((time() - filemtime($phpFile)) / 3600, 1)
           );
           
           // Check for JSON.gz file
           $jsonGzFile = $CACHE_DIR . '/journal_' . $journalId . '_stats.json.gz';
           if (file_exists($jsonGzFile)) {
               $journalCache['json_gz_file'] = basename($jsonGzFile);
               $journalCache['json_gz_size'] = filesize($jsonGzFile);
           }
           
           // Check for hash file
           $hashFile = $phpFile . '.hash';
           if (file_exists($hashFile)) {
               $journalCache['data_hash'] = substr(trim(file_get_contents($hashFile)), 0, 8);
           }
           
           $cacheInfo['journals'][] = $journalCache;
           $cacheInfo['total_size'] += $journalCache['php_size'];
           
           if (isset($journalCache['json_gz_size'])) {
               $cacheInfo['total_size'] += $journalCache['json_gz_size'];
           }
       }
   }
   
   // Sort by modification time (newest first)
   usort($cacheInfo['journals'], function($a, $b) {
       return strcmp($b['modified'], $a['modified']);
   });
   
   return $cacheInfo;
}

// Legacy functions for backward compatibility
function getCacheDir() {
   return $GLOBALS['CACHE_DIR'];
}

function ensureCacheDirExists($cacheDir) {
   return ensureJournalStatsCacheDirectory($cacheDir);
}
?>
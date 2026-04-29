<?php
/**
 * Article Hero
 * File: plugins/themes/[theme-name]/php/hero_futured/article_hero.php
 * Sistem artikel hero dengan smart caching dan weekly updates
 * @author Rochmady and Wizdam Team approach
 * @version 2.0 - Smart Detection + Weekly Updates + Modern Features
 * Last Update: 2025-05-25
 */

// Definisi konstanta ASSOC_TYPE jika belum tersedia
if (!defined('ASSOC_TYPE_JOURNAL')) define('ASSOC_TYPE_JOURNAL', 256);
if (!defined('ASSOC_TYPE_ISSUE')) define('ASSOC_TYPE_ISSUE', 257);
if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);

// Konfigurasi Cache - SMART DETECTION + WEEKLY UPDATES
$cacheEnabled = true;
$CACHE_DIR = __DIR__ . '/cache';
$action = isset($_GET['action']) ? $_GET['action'] : 'template';
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] == '1';

// Fungsi untuk memastikan direktori cache ada dan writable
function ensureHeroCacheDirectory($dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log("Failed to create hero cache directory: " . $dir);
            return false;
        }
    }
    
    if (!is_writable($dir)) {
        error_log("Hero cache directory is not writable: " . $dir);
        return false;
    }
    
    return true;
}

// Pastikan direktori cache ada dan writable
if (!ensureHeroCacheDirectory($CACHE_DIR)) {
    $cacheEnabled = false;
    error_log("Hero cache disabled due to directory issues: " . $CACHE_DIR);
}

// Ambil journal ID dari template vars
$journal = $this->get_template_vars('currentJournal');
$journalId = $journal->getId();

// Generate cache key dan file
$cacheKey = 'article_hero_' . $journalId;
$cacheFile = $CACHE_DIR . DIRECTORY_SEPARATOR . $cacheKey . '.json.gz';

/**
 * Fungsi untuk mendapatkan hash dari data artikel hero untuk SMART DETECTION
 * @param int $journalId ID jurnal
 * @return string Hash untuk deteksi perubahan
 */
function getHeroArticlesDataHash($journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    $hashData = array();
    
    // Cek kolom yang tersedia di tabel metrics terlebih dahulu
    $availableColumns = array();
    try {
        $columnsResult = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
        while ($columnsResult && !$columnsResult->EOF) {
            $availableColumns[] = $columnsResult->fields[0];
            $columnsResult->MoveNext();
        }
        $columnsResult->Close();
    } catch (Exception $e) {
        $availableColumns = array();
    }
    
    // Tentukan kolom tanggal yang tersedia untuk metrics
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
    
    // Query untuk mendapatkan data yang bisa berubah
    if (!empty($dateColumn) && !empty($availableColumns)) {
        // Dengan metrics data
        $result = $articleDao->retrieve(
            "SELECT a.article_id, a.date_status_modified, pa.date_published, 
                    COALESCE(m.last_metric_update, '1970-01-01') as last_metric_update
             FROM articles a
             LEFT JOIN published_articles pa ON a.article_id = pa.article_id
             LEFT JOIN issues i ON pa.issue_id = i.issue_id
             LEFT JOIN (
                SELECT assoc_id, MAX($dateColumn) as last_metric_update
                FROM metrics 
                WHERE (assoc_type = ? OR assoc_type = ?) AND assoc_id IS NOT NULL
                GROUP BY assoc_id
             ) m ON a.article_id = m.assoc_id
             WHERE a.journal_id = ?
               AND a.status = ?
               AND i.published = 1
               AND pa.date_published IS NOT NULL
             ORDER BY pa.date_published DESC
             LIMIT 10",
            array(ASSOC_TYPE_ARTICLE, ASSOC_TYPE_GALLEY, $journalId, STATUS_PUBLISHED)
        );
    } else {
        // Tanpa metrics data (fallback)
        $result = $articleDao->retrieve(
            "SELECT a.article_id, a.date_status_modified, pa.date_published
             FROM articles a
             LEFT JOIN published_articles pa ON a.article_id = pa.article_id
             LEFT JOIN issues i ON pa.issue_id = i.issue_id
             WHERE a.journal_id = ?
               AND a.status = ?
               AND i.published = 1
               AND pa.date_published IS NOT NULL
             ORDER BY pa.date_published DESC
             LIMIT 10",
            array($journalId, STATUS_PUBLISHED)
        );
    }
    
    if ($result && !$result->EOF) {
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $hashData[] = array(
                'id' => $row['article_id'],
                'published' => $row['date_published'],
                'modified' => $row['date_status_modified'],
                'last_metric_update' => isset($row['last_metric_update']) ? $row['last_metric_update'] : '1970-01-01'
            );
            $result->MoveNext();
        }
        $result->Close();
    }
    
    // SMART DETECTION: Hanya berubah jika ada perubahan real
    return md5(serialize($hashData));
}

/**
 * Fungsi untuk cek apakah cache masih valid - SMART DETECTION + WEEKLY UPDATES
 * @param string $cacheFile Path ke file cache
 * @param string $currentHash Hash data saat ini
 * @return bool True jika cache valid
 */
function isHeroCacheValid($cacheFile, $currentHash) {
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    $cachedData = loadHeroFromCache($cacheFile);
    if ($cachedData === false) {
        return false;
    }
    
    // SMART DETECTION: Cek apakah data berubah berdasarkan hash
    if (!isset($cachedData['data_hash']) || $cachedData['data_hash'] !== $currentHash) {
        error_log("Hero smart detection: Data changed, regenerating cache");
        return false; // Data berubah, regenerate cache
    }
    
    // WEEKLY UPDATES: Cache expires setiap 7 hari (604800 detik)
    $cacheTime = filemtime($cacheFile);
    $weeklyExpiry = 604800; // 7 days in seconds
    
    if ($cacheTime === false || (time() - $cacheTime) > $weeklyExpiry) {
        error_log("Hero weekly update: Cache expired after 7 days, regenerating");
        return false; // Weekly expiry
    }
    
    // Cache masih valid
    return true;
}

/**
 * Fungsi untuk load data dari cache (JSON.GZ format)
 * @param string $cacheFile Path ke file cache
 * @return array|false Array data atau false jika gagal
 */
function loadHeroFromCache($cacheFile) {
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    $compressedContent = file_get_contents($cacheFile);
    if ($compressedContent === false) {
        return false;
    }
    
    $content = gzuncompress($compressedContent);
    if ($content === false) {
        return false;
    }
    
    $data = json_decode($content, true);
    return $data !== null ? $data : false;
}

/**
 * Fungsi untuk save data ke cache (JSON.GZ format) - IMPROVED
 * @param string $cacheFile Path ke file cache
 * @param array $data Data yang akan disimpan
 * @return bool True jika berhasil
 */
function saveHeroToCache($cacheFile, $data) {
    $dir = dirname($cacheFile);
    
    // Pastikan direktori cache ada
    if (!ensureHeroCacheDirectory($dir)) {
        error_log("Cannot create hero cache directory: " . $dir);
        return false;
    }
    
    try {
        // Tambahkan informasi cache
        $data['cache_info'] = array(
            'cache_file' => $cacheFile,
            'saved_at' => date('Y-m-d H:i:s'),
            'file_size_before' => file_exists($cacheFile) ? filesize($cacheFile) : 0
        );
        
        $content = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        
        if ($content === false) {
            error_log("Hero JSON encode failed: " . json_last_error_msg());
            return false;
        }
        
        $compressedContent = gzcompress($content, 9);
        
        if ($compressedContent === false) {
            error_log("Hero GZIP compression failed");
            return false;
        }
        
        $result = file_put_contents($cacheFile, $compressedContent);
        
        if ($result === false) {
            error_log("Failed to write hero cache file: " . $cacheFile);
            return false;
        }
        
        // Verifikasi file berhasil dibuat
        if (!file_exists($cacheFile)) {
            error_log("Hero cache file was not created: " . $cacheFile);
            return false;
        }
        
        error_log("Hero cache file successfully created: " . $cacheFile . " (size: " . filesize($cacheFile) . " bytes)");
        return true;
        
    } catch (Exception $e) {
        error_log("Exception while saving hero cache: " . $e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk cek open access status - MENGGUNAKAN 5-METHOD SYSTEM
 */
function checkHeroOpenAccessStatus($article, $journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    $articleId = $article->getId();
    
    // Method 1: Cek dari setting artikel langsung
    if (method_exists($article, 'getAccessStatus')) {
        $accessStatus = $article->getAccessStatus();
        if ($accessStatus == ARTICLE_ACCESS_OPEN) {
            return true;
        }
    }
    
    // Method 2: Cek dari published_articles table
    try {
        $result = $articleDao->retrieve(
            "SELECT pa.access_status 
             FROM published_articles pa 
             WHERE pa.article_id = ?",
            array($articleId)
        );
        
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $accessStatus = $row['access_status'];
            $result->Close();
            
            // OJS 2.x constants: 0 = subscription required, 1 = open access
            if ($accessStatus == 1) {
                return true;
            }
        }
    } catch (Exception $e) {
        error_log("Error checking hero published_articles access_status: " . $e->getMessage());
    }
    
    // Method 3: Cek dari issue level
    if (method_exists($article, 'getIssueId')) {
        $issueId = $article->getIssueId();
        if ($issueId) {
            $issueDao = &DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($issueId);
            if ($issue) {
                // Cek access status dari issue
                if (method_exists($issue, 'getAccessStatus')) {
                    $issueAccessStatus = $issue->getAccessStatus();
                    // ISSUE_ACCESS_OPEN = 1 di OJS 2.x
                    if ($issueAccessStatus == 1) {
                        return true;
                    }
                }
                
                // Cek open access date
                if (method_exists($issue, 'getOpenAccessDate')) {
                    $openAccessDate = $issue->getOpenAccessDate();
                    if ($openAccessDate && strtotime($openAccessDate) <= time()) {
                        return true;
                    }
                }
            }
        }
    }
    
    // Method 4: Cek dari galley (file) level
    try {
        $result = $articleDao->retrieve(
            "SELECT ag.galley_id 
             FROM article_galleys ag 
             WHERE ag.article_id = ? 
             AND ag.remote_url IS NOT NULL 
             AND ag.remote_url != ''",
            array($articleId)
        );
        
        if ($result && !$result->EOF) {
            $result->Close();
            return true; // Ada remote URL = biasanya open access
        }
    } catch (Exception $e) {
        error_log("Error checking hero galley remote_url: " . $e->getMessage());
    }
    
    // Method 5: Cek dari journal settings (default policy)
    try {
        $result = $articleDao->retrieve(
            "SELECT setting_value 
             FROM journal_settings 
             WHERE journal_id = ? 
             AND setting_name = 'publishingMode'",
            array($journalId)
        );
        
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $publishingMode = $row['setting_value'];
            $result->Close();
            
            // Publishing mode 0 = open access journal
            if ($publishingMode == 0) {
                return true;
            }
        }
    } catch (Exception $e) {
        error_log("Error checking hero journal publishingMode: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Fungsi untuk mencari cover image dengan berbagai locale
 */
function findHeroCoverImage($journalId, $articleId) {
    $locales = array('en_US', 'id_ID', 'en', 'id');
    $extensions = array('jpg', 'jpeg', 'png', 'gif');
    
    foreach ($locales as $locale) {
        foreach ($extensions as $ext) {
            $coverImagePath = "public/journals/{$journalId}/cover_article_{$articleId}_{$locale}.{$ext}";
            if (file_exists($coverImagePath)) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                return array(
                    'file_exists' => true,
                    'file_url' => $protocol . '://' . $host . '/' . $coverImagePath,
                    'file_path' => $coverImagePath,
                    'locale' => $locale,
                    'extension' => $ext
                );
            }
        }
    }
    
    // Fallback tanpa locale
    foreach ($extensions as $ext) {
        $coverImagePath = "public/journals/{$journalId}/cover_article_{$articleId}.{$ext}";
        if (file_exists($coverImagePath)) {
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'];
            return array(
                'file_exists' => true,
                'file_url' => $protocol . '://' . $host . '/' . $coverImagePath,
                'file_path' => $coverImagePath,
                'locale' => 'default',
                'extension' => $ext
            );
        }
    }
    
    return array('file_exists' => false, 'file_url' => null, 'file_path' => null);
}

/**
 * Fungsi untuk mendapatkan artikel hero dengan logika VOLUME-BASED
 * Volume terakhir sudah include Issue terakhir di dalamnya
 * @param int $journalId ID jurnal
 * @return array Array artikel hero dan featured
 */
function getHeroArticlesAdvanced($journalId) {
    error_log("Hero Selection: Starting VOLUME-BASED selection");
    
    // Step 1: Ambil artikel dari volume terakhir (sudah include issue terakhir)
    $articles = getArticlesFromLatestVolume($journalId);
    error_log("Hero Selection: Found " . count($articles) . " articles from latest volume (includes latest issue)");
    
    // Step 2: Jika artikel dari volume < 5, perluas ke multiple volumes
    if (count($articles) < 5) {
        error_log("Hero Selection: Volume insufficient, expanding to multiple volumes");
        $articles = getArticlesFromMultipleVolumes($journalId);
        error_log("Hero Selection: Expanded to " . count($articles) . " articles from multiple volumes");
    }
    
    // Step 3: Tentukan mode berdasarkan jumlah artikel
    if (count($articles) < 5) {
        // Mode jurnal baru: kronologis
        error_log("Hero Selection: Using NEW JOURNAL mode (< 5 articles)");
        return handleNewJournalMode($journalId, $articles);
    } else {
        // Mode jurnal matang: volume-based selection
        error_log("Hero Selection: Using MATURE JOURNAL mode (≥ 5 articles) - Volume-based");
        return handleMatureJournalMode($articles, 'volume_based');
    }
}

/**
 * Fungsi untuk menggabungkan pool artikel tanpa duplikasi
 * @param array $primaryPool Pool artikel utama (issue articles)
 * @param array $secondaryPool Pool artikel tambahan (volume articles)
 * @return array Combined pool tanpa duplikasi
 */
function combineArticlePool($primaryPool, $secondaryPool) {
    $combined = $primaryPool;
    $existingIds = array_column($primaryPool, 'article_id');
    
    // Tambahkan artikel dari secondary pool yang belum ada
    foreach ($secondaryPool as $article) {
        if (!in_array($article['article_id'], $existingIds)) {
            $combined[] = $article;
            $existingIds[] = $article['article_id'];
        }
    }
    
    // Sort by publish date (terbaru di atas) untuk konsistensi
    usort($combined, function($a, $b) {
        return strtotime($b['date_published']) - strtotime($a['date_published']);
    });
    
    return $combined;
}

/**
 * Fungsi untuk mendapatkan artikel dari issue/edisi terakhir - FIXED for MariaDB compatibility
 * @param int $journalId ID jurnal
 * @return array Array artikel dari issue terakhir
 */
function getArticlesFromLatestIssue($journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    // Step 1: Get latest issue ID - COMPATIBLE dengan semua MariaDB versions
    $latestIssueId = null;
    try {
        $issueResult = $articleDao->retrieve(
            "SELECT i.issue_id 
             FROM issues i 
             WHERE i.journal_id = ? AND i.published = 1
             ORDER BY i.date_published DESC, i.volume DESC, i.number DESC 
             LIMIT 1",
            array($journalId)
        );
        
        if ($issueResult && !$issueResult->EOF) {
            $issueRow = $issueResult->GetRowAssoc(false);
            $latestIssueId = $issueRow['issue_id'];
            $issueResult->Close();
        }
    } catch (Exception $e) {
        error_log("Error getting latest issue ID: " . $e->getMessage());
        return array();
    }
    
    if (!$latestIssueId) {
        return array();
    }
    
    // Step 2: Get articles from that issue - NO SUBQUERY dengan LIMIT
    $metricsTableExists = checkMetricsTableExists();
    $availableColumns = getAvailableMetricsColumns();
    $contextField = in_array('context_id', $availableColumns) ? 'context_id' : 'journal_id';
    
    if ($metricsTableExists) {
        $query = "
            SELECT 
                a.article_id, 
                pa.date_published,
                i.issue_id,
                i.volume,
                i.number,
                COALESCE(views.total_views, 0) as total_views,
                COALESCE(downloads.total_downloads, 0) as total_downloads
            FROM articles a
            JOIN published_articles pa ON a.article_id = pa.article_id
            JOIN issues i ON pa.issue_id = i.issue_id
            LEFT JOIN (
                SELECT assoc_id, SUM(metric) as total_views
                FROM metrics m
                WHERE m.$contextField = ? AND m.assoc_type = ?
                GROUP BY assoc_id
            ) views ON a.article_id = views.assoc_id
            LEFT JOIN (
                SELECT ag.article_id, SUM(m.metric) as total_downloads
                FROM metrics m
                JOIN article_galleys ag ON m.assoc_id = ag.galley_id
                WHERE m.$contextField = ? AND m.assoc_type = ?
                GROUP BY ag.article_id
            ) downloads ON a.article_id = downloads.article_id
            WHERE a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                AND i.issue_id = ?
            ORDER BY pa.date_published DESC, a.article_id DESC
        ";
        
        $result = $articleDao->retrieve($query, array(
            $journalId, ASSOC_TYPE_ARTICLE,    // views query
            $journalId, ASSOC_TYPE_GALLEY,     // downloads query
            $journalId, STATUS_PUBLISHED, $latestIssueId
        ));
    } else {
        // Fallback tanpa metrics
        $query = "
            SELECT 
                a.article_id, 
                pa.date_published,
                i.issue_id,
                i.volume,
                i.number,
                0 as total_views,
                0 as total_downloads
            FROM articles a
            JOIN published_articles pa ON a.article_id = pa.article_id
            JOIN issues i ON pa.issue_id = i.issue_id
            WHERE a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                AND i.issue_id = ?
            ORDER BY pa.date_published DESC, a.article_id DESC
        ";
        
        $result = $articleDao->retrieve($query, array($journalId, STATUS_PUBLISHED, $latestIssueId));
    }
    
    return processArticleResults($result, $journalId);
}

/**
 * Fungsi untuk mendapatkan artikel dari volume terakhir - FIXED for MariaDB compatibility
 * @param int $journalId ID jurnal
 * @return array Array artikel dari volume terakhir
 */
function getArticlesFromLatestVolume($journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    // Step 1: Get latest volume number - COMPATIBLE dengan semua MariaDB versions
    $latestVolume = null;
    try {
        $volumeResult = $articleDao->retrieve(
            "SELECT i.volume 
             FROM issues i 
             WHERE i.journal_id = ? AND i.published = 1
             ORDER BY i.date_published DESC, i.volume DESC, i.number DESC 
             LIMIT 1",
            array($journalId)
        );
        
        if ($volumeResult && !$volumeResult->EOF) {
            $volumeRow = $volumeResult->GetRowAssoc(false);
            $latestVolume = $volumeRow['volume'];
            $volumeResult->Close();
        }
    } catch (Exception $e) {
        error_log("Error getting latest volume: " . $e->getMessage());
        return array();
    }
    
    if (!$latestVolume) {
        return array();
    }
    
    // Step 2: Get articles from that volume - NO SUBQUERY dengan LIMIT
    $metricsTableExists = checkMetricsTableExists();
    $availableColumns = getAvailableMetricsColumns();
    $contextField = in_array('context_id', $availableColumns) ? 'context_id' : 'journal_id';
    
    if ($metricsTableExists) {
        $query = "
            SELECT 
                a.article_id, 
                pa.date_published,
                i.issue_id,
                i.volume,
                i.number,
                COALESCE(views.total_views, 0) as total_views,
                COALESCE(downloads.total_downloads, 0) as total_downloads
            FROM articles a
            JOIN published_articles pa ON a.article_id = pa.article_id
            JOIN issues i ON pa.issue_id = i.issue_id
            LEFT JOIN (
                SELECT assoc_id, SUM(metric) as total_views
                FROM metrics m
                WHERE m.$contextField = ? AND m.assoc_type = ?
                GROUP BY assoc_id
            ) views ON a.article_id = views.assoc_id
            LEFT JOIN (
                SELECT ag.article_id, SUM(m.metric) as total_downloads
                FROM metrics m
                JOIN article_galleys ag ON m.assoc_id = ag.galley_id
                WHERE m.$contextField = ? AND m.assoc_type = ?
                GROUP BY ag.article_id
            ) downloads ON a.article_id = downloads.article_id
            WHERE a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                AND i.volume = ?
            ORDER BY pa.date_published DESC, a.article_id DESC
        ";
        
        $result = $articleDao->retrieve($query, array(
            $journalId, ASSOC_TYPE_ARTICLE,    // views query
            $journalId, ASSOC_TYPE_GALLEY,     // downloads query
            $journalId, STATUS_PUBLISHED, $latestVolume
        ));
    } else {
        // Fallback tanpa metrics
        $query = "
            SELECT 
                a.article_id, 
                pa.date_published,
                i.issue_id,
                i.volume,
                i.number,
                0 as total_views,
                0 as total_downloads
            FROM articles a
            JOIN published_articles pa ON a.article_id = pa.article_id
            JOIN issues i ON pa.issue_id = i.issue_id
            WHERE a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                AND i.volume = ?
            ORDER BY pa.date_published DESC, a.article_id DESC
        ";
        
        $result = $articleDao->retrieve($query, array($journalId, STATUS_PUBLISHED, $latestVolume));
    }
    
    return processArticleResults($result, $journalId);
}

/**
 * Fungsi untuk mendapatkan artikel dari multiple volume - FIXED for MariaDB compatibility
 * @param int $journalId ID jurnal
 * @return array Array artikel dari multiple volume
 */
function getArticlesFromMultipleVolumes($journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    // Step 1: Get top 2 latest volume numbers - COMPATIBLE dengan semua MariaDB versions
    $latestVolumes = array();
    try {
        $volumesResult = $articleDao->retrieve(
            "SELECT DISTINCT i.volume 
             FROM issues i 
             WHERE i.journal_id = ? AND i.published = 1
             ORDER BY i.volume DESC 
             LIMIT 2",
            array($journalId)
        );
        
        if ($volumesResult && !$volumesResult->EOF) {
            while (!$volumesResult->EOF) {
                $volumeRow = $volumesResult->GetRowAssoc(false);
                $latestVolumes[] = $volumeRow['volume'];
                $volumesResult->MoveNext();
            }
            $volumesResult->Close();
        }
    } catch (Exception $e) {
        error_log("Error getting latest volumes: " . $e->getMessage());
        return array();
    }
    
    if (empty($latestVolumes)) {
        return array();
    }
    
    // Step 2: Get articles from those volumes - NO SUBQUERY dengan LIMIT
    $metricsTableExists = checkMetricsTableExists();
    $availableColumns = getAvailableMetricsColumns();
    $contextField = in_array('context_id', $availableColumns) ? 'context_id' : 'journal_id';
    
    // Create placeholders untuk IN clause
    $volumePlaceholders = implode(',', array_fill(0, count($latestVolumes), '?'));
    
    if ($metricsTableExists) {
        $query = "
            SELECT 
                a.article_id, 
                pa.date_published,
                i.issue_id,
                i.volume,
                i.number,
                COALESCE(views.total_views, 0) as total_views,
                COALESCE(downloads.total_downloads, 0) as total_downloads
            FROM articles a
            JOIN published_articles pa ON a.article_id = pa.article_id
            JOIN issues i ON pa.issue_id = i.issue_id
            LEFT JOIN (
                SELECT assoc_id, SUM(metric) as total_views
                FROM metrics m
                WHERE m.$contextField = ? AND m.assoc_type = ?
                GROUP BY assoc_id
            ) views ON a.article_id = views.assoc_id
            LEFT JOIN (
                SELECT ag.article_id, SUM(m.metric) as total_downloads
                FROM metrics m
                JOIN article_galleys ag ON m.assoc_id = ag.galley_id
                WHERE m.$contextField = ? AND m.assoc_type = ?
                GROUP BY ag.article_id
            ) downloads ON a.article_id = downloads.article_id
            WHERE a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                AND i.volume IN ($volumePlaceholders)
            ORDER BY pa.date_published DESC, a.article_id DESC
            LIMIT 20
        ";
        
        $params = array(
            $journalId, ASSOC_TYPE_ARTICLE,    // views query
            $journalId, ASSOC_TYPE_GALLEY,     // downloads query
            $journalId, STATUS_PUBLISHED
        );
        $params = array_merge($params, $latestVolumes);
        
        $result = $articleDao->retrieve($query, $params);
    } else {
        // Fallback tanpa metrics
        $query = "
            SELECT 
                a.article_id, 
                pa.date_published,
                i.issue_id,
                i.volume,
                i.number,
                0 as total_views,
                0 as total_downloads
            FROM articles a
            JOIN published_articles pa ON a.article_id = pa.article_id
            JOIN issues i ON pa.issue_id = i.issue_id
            WHERE a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                AND i.volume IN ($volumePlaceholders)
            ORDER BY pa.date_published DESC, a.article_id DESC
            LIMIT 20
        ";
        
        $params = array($journalId, STATUS_PUBLISHED);
        $params = array_merge($params, $latestVolumes);
        
        $result = $articleDao->retrieve($query, $params);
    }
    
    return processArticleResults($result, $journalId);
}

/**
 * Fungsi untuk menangani jurnal baru (< 5 artikel) - MODE KRONOLOGIS
 * @param int $journalId ID jurnal
 * @param array $articles Artikel yang tersedia
 * @return array Hero selection result
 */
function handleNewJournalMode($journalId, $articles) {
    error_log("Hero Selection: New journal mode activated (< 5 articles), using chronological order");
    
    if (empty($articles)) {
        return array(
            'hero' => null,
            'remaining' => array(),
            'hero_selection_logic' => array(
                'mode' => 'new_journal_empty',
                'selection_method' => 'none',
                'total_candidates' => 0,
                'reason' => 'No articles available'
            )
        );
    }
    
    // Mode kronologis: artikel terbaru = hero, sisanya = featured
    $heroArticle = $articles[0]; // Artikel terbaru
    $remainingArticles = array_slice($articles, 1); // Sisanya
    
    return array(
        'hero' => $heroArticle,
        'remaining' => $remainingArticles,
        'hero_selection_logic' => array(
            'mode' => 'new_journal_chronological',
            'selection_method' => 'latest_article_as_hero',
            'total_candidates' => count($articles),
            'hero_article_id' => $heroArticle['article_id'],
            'reason' => 'Insufficient articles for scoring algorithm, using chronological order'
        )
    );
}

/**
 * Fungsi untuk menangani jurnal matang (≥ 5 artikel) - MODE VOLUME-BASED SELECTION
 * @param array $articles Array artikel kandidat dari volume
 * @param string $mode Mode selection ('volume_based' atau default)
 * @return array Hero selection result
 */
function handleMatureJournalMode($articles, $mode = 'default') {
    $logMessage = "Hero Selection: Mature journal mode activated (≥ 5 articles)";
    
    if ($mode === 'volume_based') {
        $logMessage .= " - VOLUME-BASED SELECTION";
        error_log($logMessage);
        
        // Identifikasi komposisi pool dari volume
        $latestIssueId = !empty($articles) ? $articles[0]['issue_id'] : null;
        $issueArticles = array_filter($articles, function($article) use ($latestIssueId) {
            return $article['issue_id'] == $latestIssueId;
        });
        
        error_log("Pool composition: " . count($issueArticles) . " from latest issue, " . 
                 count($articles) . " total from volume (issue included in volume)");
    } else {
        error_log($logMessage);
    }
    
    $heroResult = selectHeroWithWeeklyGrace($articles);
    $remainingArticles = array();
    $heroIndex = $heroResult['hero_index'];
    
    // Remove hero dari kandidat featured
    for ($i = 0; $i < count($articles); $i++) {
        if ($i !== $heroIndex) {
            $remainingArticles[] = $articles[$i];
        }
    }
    
    // Select featured articles dengan weekly grace period juga
    $featuredArticles = selectFeaturedWithWeeklyGrace($remainingArticles, 4);
    
    $selectionLogic = array_merge($heroResult['selection_logic'], array(
        'mode' => $mode === 'volume_based' ? 'mature_journal_advanced' : 'mature_journal_standard',
        'pool_type' => $mode === 'volume_based' ? 'volume_based' : 'single_source',
        'selection_strategy' => 'volume_includes_latest_issue',
        'featured_selection_method' => 'weekly_grace_period_scoring',
        'total_pool_size' => count($articles)
    ));
    
    // Tambahkan informasi pool composition untuk mode volume-based
    if ($mode === 'volume_based') {
        $latestIssueId = !empty($articles) ? $articles[0]['issue_id'] : null;
        $issueArticles = array_filter($articles, function($article) use ($latestIssueId) {
            return $article['issue_id'] == $latestIssueId;
        });
        
        $selectionLogic['pool_composition'] = array(
            'latest_issue_articles' => count($issueArticles),
            'total_volume_articles' => count($articles),
            'selection_pool' => count($articles) . ' articles from volume (includes latest issue)',
            'note' => 'Latest issue articles are included within volume articles'
        );
    }
    
    return array(
        'hero' => $heroResult['hero'],
        'remaining' => $featuredArticles,
        'hero_selection_logic' => $selectionLogic
    );
}

/**
 * Fungsi untuk seleksi hero dengan weekly grace period + DETAILED SCORING
 * @param array $articles Array artikel kandidat
 * @return array Hero selection result with detailed scoring
 */
function selectHeroWithWeeklyGrace($articles) {
    $latestArticle = $articles[0];
    $oneWeekAgo = strtotime('-7 days');
    
    // Cek apakah artikel terbaru masih dalam grace period (< 1 minggu)
    $latestPublishTime = strtotime($latestArticle['date_published']);
    $isInGracePeriod = $latestPublishTime > $oneWeekAgo;
    
    // DETAILED SCORING untuk semua kandidat
    $candidatesScoring = array();
    
    for ($i = 0; $i < count($articles); $i++) {
        $article = $articles[$i];
        
        // Komponen scoring
        $viewsScore = intval($article['total_views']);
        $downloadsScore = intval($article['total_downloads']) * 2; // Downloads worth 2x
        $recencyScore = max(0, 100 - ($i * 10)); // Posisi dalam array (terbaru = score tinggi)
        
        // Bonus untuk artikel dalam grace period
        $gracePeriodBonus = 0;
        $publishTime = strtotime($article['date_published']);
        $daysSincePublish = (time() - $publishTime) / (60*60*24);
        
        if ($publishTime > $oneWeekAgo) {
            $gracePeriodBonus = max(0, 50 - ($daysSincePublish * 5)); // Bonus menurun seiring waktu
        }
        
        $totalScore = $viewsScore + $downloadsScore + $recencyScore + $gracePeriodBonus;
        
        $candidatesScoring[] = array(
            'article_id' => $article['article_id'],
            'title' => $article['title'],
            'position' => $i + 1,
            'total_views' => $viewsScore,
            'total_downloads' => intval($article['total_downloads']),
            'views_score' => $viewsScore,
            'downloads_score' => $downloadsScore,
            'recency_score' => $recencyScore,
            'grace_period_bonus' => $gracePeriodBonus,
            'days_since_publish' => round($daysSincePublish, 1),
            'is_in_grace_period' => ($publishTime > $oneWeekAgo),
            'total_score' => $totalScore,
            'date_published' => $article['date_published']
        );
    }
    
    // Sort berdasarkan total score untuk menentukan ranking
    usort($candidatesScoring, function($a, $b) {
        return $b['total_score'] - $a['total_score'];
    });
    
    // Add ranking
    for ($i = 0; $i < count($candidatesScoring); $i++) {
        $candidatesScoring[$i]['final_rank'] = $i + 1;
    }
    
    if ($isInGracePeriod) {
        // Grace period: artikel terbaru otomatis jadi hero
        error_log("Hero Selection: Article " . $latestArticle['article_id'] . " selected as hero (weekly grace period)");
        
        return array(
            'hero' => $latestArticle,
            'hero_index' => 0,
            'selection_logic' => array(
                'selection_method' => 'weekly_grace_period',
                'hero_article_id' => $latestArticle['article_id'],
                'hero_publish_date' => $latestArticle['date_published'],
                'days_since_publish' => round((time() - $latestPublishTime) / (60*60*24), 1),
                'grace_period_active' => true,
                'total_candidates' => count($articles),
                'candidates_scoring' => $candidatesScoring // ← DETAILED SCORING TABLE
            )
        );
    } else {
        // Setelah grace period: gunakan algoritma scoring
        error_log("Hero Selection: Grace period expired, using scoring algorithm");
        
        // Hero adalah artikel dengan score tertinggi
        $heroCandidate = $candidatesScoring[0]; // Sudah di-sort berdasarkan score
        $heroArticle = null;
        $heroIndex = 0;
        
        // Find hero article dalam array asli
        for ($i = 0; $i < count($articles); $i++) {
            if ($articles[$i]['article_id'] == $heroCandidate['article_id']) {
                $heroArticle = $articles[$i];
                $heroIndex = $i;
                break;
            }
        }
        
        return array(
            'hero' => $heroArticle,
            'hero_index' => $heroIndex,
            'selection_logic' => array(
                'selection_method' => 'scoring_algorithm',
                'hero_article_id' => $heroArticle['article_id'],
                'hero_score' => $heroCandidate['total_score'],
                'hero_views' => $heroArticle['total_views'],
                'hero_downloads' => $heroArticle['total_downloads'],
                'grace_period_active' => false,
                'total_candidates' => count($articles),
                'candidates_scoring' => $candidatesScoring // ← DETAILED SCORING TABLE
            )
        );
    }
}

/**
 * Fungsi untuk seleksi featured articles dengan weekly grace period + DETAILED SCORING
 * @param array $articles Array artikel kandidat (sudah exclude hero)
 * @param int $limit Jumlah featured articles yang diinginkan
 * @return array Featured articles with detailed scoring
 */
function selectFeaturedWithWeeklyGrace($articles, $limit = 4) {
    if (empty($articles)) {
        return array();
    }
    
    $oneWeekAgo = strtotime('-7 days');
    $featured = array();
    $featuredScoring = array();
    
    // DETAILED SCORING untuk semua kandidat featured
    $candidatesScoring = array();
    
    for ($i = 0; $i < count($articles); $i++) {
        $article = $articles[$i];
        
        // Komponen scoring untuk featured (sedikit berbeda dengan hero)
        $viewsScore = intval($article['total_views']);
        $downloadsScore = intval($article['total_downloads']) * 2;
        $recencyScore = max(0, 50 - ($i * 5)); // Skor recency lebih rendah untuk featured
        
        // Grace period bonus
        $gracePeriodBonus = 0;
        $publishTime = strtotime($article['date_published']);
        $daysSincePublish = (time() - $publishTime) / (60*60*24);
        $isInGracePeriod = $publishTime > $oneWeekAgo;
        
        if ($isInGracePeriod) {
            $gracePeriodBonus = max(0, 30 - ($daysSincePublish * 3)); // Bonus lebih kecil untuk featured
        }
        
        $totalScore = $viewsScore + $downloadsScore + $recencyScore + $gracePeriodBonus;
        
        $candidatesScoring[] = array(
            'article_id' => $article['article_id'],
            'title' => $article['title'],
            'position' => $i + 1,
            'total_views' => $viewsScore,
            'total_downloads' => intval($article['total_downloads']),
            'views_score' => $viewsScore,
            'downloads_score' => $downloadsScore,
            'recency_score' => $recencyScore,
            'grace_period_bonus' => $gracePeriodBonus,
            'days_since_publish' => round($daysSincePublish, 1),
            'is_in_grace_period' => $isInGracePeriod,
            'total_score' => $totalScore,
            'date_published' => $article['date_published']
        );
    }
    
    // Sort berdasarkan kombinasi grace period dan score
    usort($candidatesScoring, function($a, $b) {
        // Prioritas: Grace period dulu, lalu score
        if ($a['is_in_grace_period'] && !$b['is_in_grace_period']) {
            return -1;
        } elseif (!$a['is_in_grace_period'] && $b['is_in_grace_period']) {
            return 1;
        } else {
            return $b['total_score'] - $a['total_score'];
        }
    });
    
    // Add ranking
    for ($i = 0; $i < count($candidatesScoring); $i++) {
        $candidatesScoring[$i]['final_rank'] = $i + 1;
        $candidatesScoring[$i]['selected_as_featured'] = ($i < $limit);
    }
    
    // Ambil top featured berdasarkan ranking
    $selectedFeatured = array();
    for ($i = 0; $i < min($limit, count($candidatesScoring)); $i++) {
        $featuredCandidate = $candidatesScoring[$i];
        
        // Find artikel dalam array asli
        foreach ($articles as $article) {
            if ($article['article_id'] == $featuredCandidate['article_id']) {
                $selectedFeatured[] = $article;
                error_log("Featured Selection: Article " . $article['article_id'] . " selected (rank: " . ($i+1) . ", score: " . $featuredCandidate['total_score'] . ")");
                break;
            }
        }
    }
    
    // Store scoring data untuk template
    global $featuredCandidatesScoring;
    $featuredCandidatesScoring = $candidatesScoring;
    
    return $selectedFeatured;
}

/**
 * Helper functions
 */
function checkMetricsTableExists() {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    try {
        $checkResult = $articleDao->retrieve("SHOW TABLES LIKE 'metrics'");
        $exists = ($checkResult->RecordCount() > 0);
        $checkResult->Close();
        return $exists;
    } catch (Exception $e) {
        return false;
    }
}

function getAvailableMetricsColumns() {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    $availableColumns = array();
    try {
        $columnsResult = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
        while ($columnsResult && !$columnsResult->EOF) {
            $availableColumns[] = $columnsResult->fields[0];
            $columnsResult->MoveNext();
        }
        $columnsResult->Close();
    } catch (Exception $e) {
        // Return empty array if error
    }
    return $availableColumns;
}

function processArticleResults($result, $journalId) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    $articles = array();
    
    if ($result && !$result->EOF) {
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $articleId = $row['article_id'];
            
            $article = $articleDao->getArticle($articleId);
            if (!$article || $article->getJournalId() != $journalId) {
                $result->MoveNext();
                continue;
            }
            
            // Process artikel seperti sebelumnya (authors, section, open access, etc.)
            $authorDao = &DAORegistry::getDAO('AuthorDAO');
            $authors = $authorDao->getAuthorsBySubmissionId($articleId);
            $authorList = array();
            
            if (is_array($authors)) {
                foreach ($authors as $author) {
                    $firstName = trim($author->getFirstName());
                    $middleName = trim($author->getMiddleName());
                    $lastName = trim($author->getLastName());
                    
                    $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
                    if (empty($fullName)) {
                        $fullName = !empty($firstName) ? $firstName : (!empty($lastName) ? $lastName : 'Unknown Author');
                    }
                    
                    $authorList[] = array(
                        'first_name' => $firstName,
                        'middle_name' => $middleName,
                        'last_name' => $lastName,
                        'full_name' => $fullName,
                        'affiliation' => $author->getLocalizedAffiliation(),
                        'email' => $author->getEmail()
                    );
                }
            }
            
            // Ambil section
            $sectionDao = &DAORegistry::getDAO('SectionDAO');
            $section = $sectionDao->getSection($article->getSectionId());
            $articleType = $section ? $section->getLocalizedTitle() : 'Article';
            
            // Cek open access
            $isOpenAccess = checkHeroOpenAccessStatus($article, $journalId);
            
            // Ambil keywords
            $keywords = array();
            $keywordString = $article->getLocalizedSubject();
            if (!empty($keywordString)) {
                $keywords = array_map('trim', explode(';', $keywordString));
                $keywords = array_filter($keywords, function($keyword) {
                    return !empty($keyword);
                });
                $keywords = array_values($keywords);
            }
            
            // Ambil DOI
            $doi = '';
            if (method_exists($article, 'getPubId')) {
                $doi = $article->getPubId('doi');
            }
            
            // Cari cover image
            $coverImage = findHeroCoverImage($journalId, $articleId);
            
            $articles[] = array(
                'article_id' => $articleId,
                'title' => $article->getLocalizedTitle(),
                'abstract' => $article->getLocalizedAbstract(),
                'authors' => $authorList,
                'total_views' => intval($row['total_views']),
                'total_downloads' => intval($row['total_downloads']),
                'date_published' => $row['date_published'],
                'date_published_formatted' => $row['date_published'] ? date('Y-m-d', strtotime($row['date_published'])) : '',
                'is_open_access' => $isOpenAccess,
                'article_type' => $articleType,
                'cover_image' => $coverImage,
                'article_url' => Request::url(null, 'article', 'view', $articleId),
                'keywords' => $keywords,
                'doi' => $doi,
                'issue_id' => $row['issue_id'],
                'volume' => $row['volume'],
                'number' => $row['number']
            );
            
            $result->MoveNext();
        }
        $result->Close();
    }
    
    return $articles;
}

// === MAIN EXECUTION - UPDATED ===

// Generate hash untuk deteksi perubahan data
$currentDataHash = getHeroArticlesDataHash($journalId);

// Cek cache jika enabled dan tidak force refresh
if ($cacheEnabled && !$forceRefresh && isHeroCacheValid($cacheFile, $currentDataHash)) {
    $cachedData = loadHeroFromCache($cacheFile);
    if ($cachedData !== false) {
        // Load dari cache - PASTIKAN SCORING DATA TERSEDIA
        $this->assign('heroArticle', $cachedData['clusters']['hero']);
        $this->assign('latestArticles', $cachedData['clusters']['latest_articles']);
        $this->assign('totalLatestArticles', $cachedData['meta']['total_articles']);
        $this->assign('allLatestArticles', $cachedData['all_articles']);
        $this->assign('lastUpdateDate', $cachedData['meta']['last_update']);
        $this->assign('heroSelectionInfo', $cachedData['hero_selection_logic']);
        
        // ← ASSIGN DETAILED SCORING DATA DARI CACHE
        if (isset($cachedData['hero_selection_logic']['candidates_scoring'])) {
            $this->assign('heroCandidatesScoring', $cachedData['hero_selection_logic']['candidates_scoring']);
        }
        
        // Assign featured scoring jika tersedia di cache
        if (isset($cachedData['featured_candidates_scoring'])) {
            $this->assign('featuredCandidatesScoring', $cachedData['featured_candidates_scoring']);
        }
        
        $this->assign('cacheInfo', array(
            'enabled' => true,
            'hit' => true,
            'expires_at' => date('Y-m-d H:i:s', $cachedData['generated_at'] + 604800), // 7 days
            'file' => basename($cacheFile),
            'full_path' => $cacheFile,
            'cache_dir' => $CACHE_DIR,
            'cache_dir_exists' => is_dir($CACHE_DIR),
            'cache_dir_writable' => is_writable($CACHE_DIR),
            'cache_file_exists' => file_exists($cacheFile),
            'cache_file_size' => file_exists($cacheFile) ? filesize($cacheFile) : 0,
            'hash' => substr($currentDataHash, 0, 8)
        ));
        
        // Untuk JSON output
        if ($action == 'json' || $action == 'api') {
            $fullData = $cachedData;
            $fullData['meta']['cache_hit'] = true;
        } else {
            return; // Keluar jika template assignment
        }
    }
}

// Jika cache tidak valid atau tidak ada, generate data baru
if (!isset($fullData)) {
    // Gunakan algoritma advanced untuk selection
    $heroData = getHeroArticlesAdvanced($journalId);
    $heroArticle = $heroData['hero'];
    $featuredArticles = $heroData['remaining'];
    
    // Ambil semua artikel untuk allLatestArticles
    $allArticles = array();
    if ($heroArticle) {
        $allArticles[] = $heroArticle;
    }
    $allArticles = array_merge($allArticles, $featuredArticles);

    // Prepare clustered data
    $clusteredData = array(
        'hero' => $heroArticle ? array($heroArticle) : array(),
        'latest_articles' => array_slice($featuredArticles, 0, 4) // Maksimal 4 featured
    );

    // Prepare full data dengan metadata - INCLUDE FEATURED SCORING
    $currentTime = time();
    $lastUpdateFormatted = date('Y-m-d H:i:s');

    // Ambil featured scoring data dari global variable
    global $featuredCandidatesScoring;

    $fullData = array(
        'clusters' => $clusteredData,
        'all_articles' => $allArticles,
        'hero_selection_logic' => $heroData['hero_selection_logic'],
        'featured_candidates_scoring' => isset($featuredCandidatesScoring) ? $featuredCandidatesScoring : array(), // ← SIMPAN KE CACHE
        'meta' => array(
            'journal_id' => $journalId,
            'total_articles' => count($allArticles),
            'last_update' => $lastUpdateFormatted,
            'cache_hit' => false,
            'cache_format' => 'json.gz',
            'metric_type' => 'views_downloads_combined_advanced',
            'version' => '2.1-smart-weekly-issue-based-with-scoring',
            'method' => 'advanced_hero_selection_algorithm'
        ),
        'generated_at' => $currentTime,
        'data_hash' => $currentDataHash
    );

    // Save ke cache dengan error handling yang lebih baik
    if ($cacheEnabled) {
        $cacheSuccess = saveHeroToCache($cacheFile, $fullData);
        if (!$cacheSuccess) {
            error_log("Failed to save hero cache for journal ID: " . $journalId);
            error_log("Hero cache file path: " . $cacheFile);
            error_log("Hero cache directory exists: " . (is_dir($CACHE_DIR) ? 'Yes' : 'No'));
            error_log("Hero cache directory writable: " . (is_writable($CACHE_DIR) ? 'Yes' : 'No'));
        } else {
            error_log("Successfully saved hero cache for journal ID: " . $journalId . " at " . $cacheFile);
        }
    }

    // Template assignment - DENGAN DETAILED SCORING
    $this->assign('heroArticle', $clusteredData['hero']);
    $this->assign('latestArticles', $clusteredData['latest_articles']);
    $this->assign('totalLatestArticles', count($allArticles));
    $this->assign('allLatestArticles', $allArticles);
    $this->assign('lastUpdateDate', $lastUpdateFormatted);
    $this->assign('heroSelectionInfo', $fullData['hero_selection_logic']);
    
    // ASSIGN DETAILED SCORING DATA
    if (isset($fullData['hero_selection_logic']['candidates_scoring'])) {
        $this->assign('heroCandidatesScoring', $fullData['hero_selection_logic']['candidates_scoring']);
    }
    
    // Assign featured scoring jika tersedia
    global $featuredCandidatesScoring;
    if (isset($featuredCandidatesScoring)) {
        $this->assign('featuredCandidatesScoring', $featuredCandidatesScoring);
    }
    
    $this->assign('cacheInfo', array(
        'enabled' => $cacheEnabled,
        'hit' => false,
        'expires_at' => date('Y-m-d H:i:s', $currentTime + 604800), // 7 days
        'file' => basename($cacheFile),
        'full_path' => $cacheFile,
        'cache_dir' => $CACHE_DIR,
        'cache_dir_exists' => is_dir($CACHE_DIR),
        'cache_dir_writable' => is_writable($CACHE_DIR),
        'cache_file_exists' => file_exists($cacheFile),
        'cache_file_size' => file_exists($cacheFile) ? filesize($cacheFile) : 0,
        'hash' => substr($currentDataHash, 0, 8)
    ));
}

// Handle different actions
if ($action == 'json' || $action == 'api') {
    header('Content-Type: application/json; charset=utf-8');
    header('X-Journal-ID: ' . $journalId);
    header('X-Last-Update: ' . $fullData['meta']['last_update']);
    header('X-Cache-Hit: ' . ($fullData['meta']['cache_hit'] ? 'true' : 'false'));
    header('X-Data-Hash: ' . substr($currentDataHash, 0, 8));
    header('X-Hero-Article: ' . ($fullData['hero_selection_logic']['hero_article_id'] ?: 'none'));
    header('X-Metric-Type: views_downloads_combined');
    
    // Tambahkan informasi testing context dan cache debugging
    $fullData['testing_context'] = array(
        'journal_id' => $journalId,
        'action' => $action,
        'cache_file' => $cacheFile,
        'cache_dir' => $CACHE_DIR,
        'cache_enabled' => $cacheEnabled,
        'force_refresh' => $forceRefresh,
        'cache_hit' => $fullData['meta']['cache_hit'],
        'data_hash' => $currentDataHash,
        'metric_focus' => 'views_downloads_combined',
        'url_params' => $_GET,
        'cache_debug' => array(
            'cache_dir_exists' => is_dir($CACHE_DIR),
            'cache_dir_writable' => is_writable($CACHE_DIR),
            'cache_file_exists' => file_exists($cacheFile),
            'cache_file_size' => file_exists($cacheFile) ? filesize($cacheFile) : 0,
            'cache_file_readable' => file_exists($cacheFile) ? is_readable($cacheFile) : false,
            'cache_file_writable' => file_exists($cacheFile) ? is_writable($cacheFile) : false,
            'current_working_dir' => getcwd(),
            'script_dir' => __DIR__,
            'cache_enabled' => $cacheEnabled,
            'cache_creation_test' => function_exists('gzcompress') ? 'gzcompress available' : 'gzcompress not available'
        )
    );
    
    echo json_encode($fullData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit();
}
?>
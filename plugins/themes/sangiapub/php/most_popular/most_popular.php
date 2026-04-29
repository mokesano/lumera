<?php
/**
 * Articles Most Popular base views counts
 * File: plugins/themes/[theme-name]/php/most_popular/most_popular.php
 * Artikel populer dengan smart caching dan weekly updates
 * @author Rochmady and Wizdam Team
 * @version 2.1 - Smart Detection + Weekly Updates + Fixed Open Access
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
function ensurePopularCacheDirectory($dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log("Failed to create popular cache directory: " . $dir);
            return false;
        }
    }
    
    if (!is_writable($dir)) {
        error_log("Popular cache directory is not writable: " . $dir);
        return false;
    }
    
    return true;
}

// Pastikan direktori cache ada dan writable
if (!ensurePopularCacheDirectory($CACHE_DIR)) {
    $cacheEnabled = false;
    error_log("Popular cache disabled due to directory issues: " . $CACHE_DIR);
}

// Ambil journal ID dari template vars
$journal = $this->get_template_vars('currentJournal');
$journalId = $journal->getId();

// Generate cache key dan file
$cacheKey = 'popular_articles_' . $journalId;
$cacheFile = $CACHE_DIR . DIRECTORY_SEPARATOR . $cacheKey . '.json.gz';

/**
 * Fungsi untuk mendapatkan hash dari data artikel untuk SMART DETECTION
 * @param int $journalId ID jurnal
 * @return string Hash untuk deteksi perubahan
 */
function getPopularArticlesDataHash($journalId) {
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
                WHERE assoc_type = ? AND assoc_id IS NOT NULL
                GROUP BY assoc_id
             ) m ON a.article_id = m.assoc_id
             WHERE a.journal_id = ?
               AND a.status = ?
               AND i.published = 1
               AND pa.date_published IS NOT NULL
             ORDER BY pa.date_published DESC
             LIMIT 20",
            array(ASSOC_TYPE_ARTICLE, $journalId, STATUS_PUBLISHED)
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
             LIMIT 20",
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
function isPopularCacheValid($cacheFile, $currentHash) {
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    $cachedData = loadPopularFromCache($cacheFile);
    if ($cachedData === false) {
        return false;
    }
    
    // SMART DETECTION: Cek apakah data berubah berdasarkan hash
    if (!isset($cachedData['data_hash']) || $cachedData['data_hash'] !== $currentHash) {
        error_log("Popular smart detection: Data changed, regenerating cache");
        return false; // Data berubah, regenerate cache
    }
    
    // WEEKLY UPDATES: Cache expires setiap 7 hari (604800 detik)
    $cacheTime = filemtime($cacheFile);
    $weeklyExpiry = 604800; // 7 days in seconds
    
    if ($cacheTime === false || (time() - $cacheTime) > $weeklyExpiry) {
        error_log("Popular weekly update: Cache expired after 7 days, regenerating");
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
function loadPopularFromCache($cacheFile) {
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
function savePopularToCache($cacheFile, $data) {
    $dir = dirname($cacheFile);
    
    // Pastikan direktori cache ada
    if (!ensurePopularCacheDirectory($dir)) {
        error_log("Cannot create popular cache directory: " . $dir);
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
            error_log("Popular JSON encode failed: " . json_last_error_msg());
            return false;
        }
        
        $compressedContent = gzcompress($content, 9);
        
        if ($compressedContent === false) {
            error_log("Popular GZIP compression failed");
            return false;
        }
        
        $result = file_put_contents($cacheFile, $compressedContent);
        
        if ($result === false) {
            error_log("Failed to write popular cache file: " . $cacheFile);
            return false;
        }
        
        // Verifikasi file berhasil dibuat
        if (!file_exists($cacheFile)) {
            error_log("Popular cache file was not created: " . $cacheFile);
            return false;
        }
        
        error_log("Popular cache file successfully created: " . $cacheFile . " (size: " . filesize($cacheFile) . " bytes)");
        return true;
        
    } catch (Exception $e) {
        error_log("Exception while saving popular cache: " . $e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk cek open access status - DIPERBAIKI SEPERTI MOST DOWNLOADED
 */
function checkPopularOpenAccessStatus($article, $journalId) {
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
        error_log("Error checking popular published_articles access_status: " . $e->getMessage());
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
        error_log("Error checking popular galley remote_url: " . $e->getMessage());
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
        error_log("Error checking popular journal publishingMode: " . $e->getMessage());
    }
    
    return false;
}

/**
 * Fungsi untuk mencari cover image dengan berbagai locale
 */
function findPopularArticleCoverImage($journalId, $articleId) {
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
 * Fungsi untuk mendapatkan artikel populer berdasarkan views - IMPROVED QUERY
 * @param int $journalId ID jurnal
 * @param int $limit Jumlah artikel yang akan diambil
 * @return array Array artikel populer
 */
function getMostPopularArticles($journalId, $limit = 10) {
    $articleDao = &DAORegistry::getDAO('ArticleDAO');
    
    // Cek keberadaan tabel metrics
    $metricsTableExists = false;
    try {
        $checkResult = $articleDao->retrieve("SHOW TABLES LIKE 'metrics'");
        $metricsTableExists = ($checkResult->RecordCount() > 0);
        $checkResult->Close();
    } catch (Exception $e) {
        error_log("Error checking popular metrics table: " . $e->getMessage());
    }
    
    $articles = array();
    
    if ($metricsTableExists) {
        // Ambil kolom yang tersedia di tabel metrics
        $availableColumns = array();
        try {
            $columnsResult = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
            while ($columnsResult && !$columnsResult->EOF) {
                $availableColumns[] = $columnsResult->fields[0];
                $columnsResult->MoveNext();
            }
            $columnsResult->Close();
        } catch (Exception $e) {
            error_log("Error getting popular metrics columns: " . $e->getMessage());
        }
        
        // Cek apakah menggunakan context_id atau journal_id
        $contextField = in_array('context_id', $availableColumns) ? 'context_id' : 'journal_id';
        
        // Query untuk mendapatkan views per artikel - HANYA ASSOC_TYPE_ARTICLE
        $viewsQuery = "
            SELECT a.article_id, SUM(m.metric) as total_views, pa.date_published
            FROM metrics m
            JOIN articles a ON m.assoc_id = a.article_id
            JOIN published_articles pa ON a.article_id = pa.article_id
            JOIN issues i ON pa.issue_id = i.issue_id
            WHERE m.$contextField = ? 
            AND m.assoc_type = ?
            AND a.journal_id = ?
            AND a.status = ?
            AND i.published = 1
            AND pa.date_published IS NOT NULL
            GROUP BY a.article_id, pa.date_published
            HAVING SUM(m.metric) > 0
            ORDER BY total_views DESC, pa.date_published DESC
            LIMIT ?
        ";
        
        try {
            $result = $articleDao->retrieve($viewsQuery, array($journalId, ASSOC_TYPE_ARTICLE, $journalId, STATUS_PUBLISHED, $limit * 2));
            
            $viewsData = array();
            if ($result && !$result->EOF) {
                while (!$result->EOF) {
                    $row = $result->GetRowAssoc(false);
                    $viewsData[$row['article_id']] = array(
                        'views' => intval($row['total_views']),
                        'date_published' => $row['date_published']
                    );
                    $result->MoveNext();
                }
                $result->Close();
            }
            
            // Ambil detail artikel berdasarkan views data
            foreach ($viewsData as $articleId => $data) {
                if (count($articles) >= $limit) break;
                
                $article = $articleDao->getArticle($articleId);
                if (!$article || $article->getJournalId() != $journalId) continue;
                
                // Ambil authors
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
                
                // Cek open access - MENGGUNAKAN FUNGSI YANG DIPERBAIKI
                $isOpenAccess = checkPopularOpenAccessStatus($article, $journalId);
                
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
                $coverImage = findPopularArticleCoverImage($journalId, $articleId);
                
                $articles[] = array(
                    'article_id' => $articleId,
                    'title' => $article->getLocalizedTitle(),
                    'abstract' => $article->getLocalizedAbstract(),
                    'authors' => $authorList,
                    'total_views' => $data['views'],
                    'date_published' => $data['date_published'],
                    'date_published_formatted' => $data['date_published'] ? date('Y-m-d', strtotime($data['date_published'])) : '',
                    'is_open_access' => $isOpenAccess,
                    'article_type' => $articleType,
                    'cover_image' => $coverImage,
                    'article_url' => Request::url(null, 'article', 'view', $articleId),
                    'keywords' => $keywords,
                    'doi' => $doi
                );
            }
            
        } catch (Exception $e) {
            error_log("Error getting most popular articles: " . $e->getMessage());
        }
    }
    
    // Sort berdasarkan views (descending) dan tanggal publikasi
    usort($articles, function($a, $b) {
        if ($a['total_views'] == $b['total_views']) {
            return strtotime($b['date_published']) - strtotime($a['date_published']);
        }
        return $b['total_views'] - $a['total_views'];
    });
    
    return $articles;
}

// === MAIN EXECUTION ===

// Generate hash untuk deteksi perubahan data
$currentDataHash = getPopularArticlesDataHash($journalId);

// Cek cache jika enabled dan tidak force refresh
if ($cacheEnabled && !$forceRefresh && isPopularCacheValid($cacheFile, $currentDataHash)) {
    $cachedData = loadPopularFromCache($cacheFile);
    if ($cachedData !== false) {
        // Load dari cache
        $this->assign('topArticle', $cachedData['clusters']['cluster_1']);
        $this->assign('secondTierArticles', $cachedData['clusters']['cluster_2']);
        $this->assign('thirdTierArticles', $cachedData['clusters']['cluster_3']);
        $this->assign('totalPopularArticles', $cachedData['meta']['total_articles']);
        $this->assign('popularArticlesList', $cachedData['all_articles']);
        $this->assign('lastUpdateDate', $cachedData['meta']['last_update']);
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
    // Ambil artikel populer menggunakan fungsi yang diperbaiki
    $articles = getMostPopularArticles($journalId, 10);

    // Prepare clustered data
    $clusteredData = array(
        'cluster_1' => array_slice($articles, 0, 1),
        'cluster_2' => array_slice($articles, 1, 4),
        'cluster_3' => array_slice($articles, 5, 4)
    );

    // Prepare full data dengan metadata
    $currentTime = time();
    $lastUpdateFormatted = date('Y-m-d H:i:s');

    $fullData = array(
        'clusters' => $clusteredData,
        'all_articles' => $articles,
        'meta' => array(
            'journal_id' => $journalId,
            'total_articles' => count($articles),
            'last_update' => $lastUpdateFormatted,
            'cache_hit' => false,
            'cache_format' => 'json.gz',
            'metric_type' => 'views',
            'version' => '2.1-smart-weekly',
            'method' => 'article_views_only'
        ),
        'generated_at' => $currentTime,
        'data_hash' => $currentDataHash
    );

    // Save ke cache dengan error handling yang lebih baik
    if ($cacheEnabled) {
        $cacheSuccess = savePopularToCache($cacheFile, $fullData);
        if (!$cacheSuccess) {
            error_log("Failed to save popular cache for journal ID: " . $journalId);
            error_log("Popular cache file path: " . $cacheFile);
            error_log("Popular cache directory exists: " . (is_dir($CACHE_DIR) ? 'Yes' : 'No'));
            error_log("Popular cache directory writable: " . (is_writable($CACHE_DIR) ? 'Yes' : 'No'));
        } else {
            error_log("Successfully saved popular cache for journal ID: " . $journalId . " at " . $cacheFile);
        }
    }

    // Assign ke template
    $this->assign('topArticle', $clusteredData['cluster_1']);
    $this->assign('secondTierArticles', $clusteredData['cluster_2']);
    $this->assign('thirdTierArticles', $clusteredData['cluster_3']);
    $this->assign('totalPopularArticles', count($articles));
    $this->assign('popularArticlesList', $articles);
    $this->assign('lastUpdateDate', $lastUpdateFormatted);
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
    header('X-Metric-Type: views');
    
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
        'metric_focus' => 'views',
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
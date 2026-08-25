<?php
/**
 * Journal Visitor Country Statistics
 * @file getJournalVisitorCountry.php
 * @brief Function untuk mengambil data pengunjung jurnal berdasarkan negara untuk OJS 2.4.8.2
 * @author Rochmady and Wizdam Team
 * @version v1.1.0 - Smart Detection + Weekly Updates + Dynamic Cache
 */

// Konfigurasi Cache - SMART DETECTION + WEEKLY UPDATES
$cacheEnabled = true;
$CACHE_DIR = __DIR__ . '/cache';

/**
 * Fungsi untuk memastikan direktori cache ada dan writable
 */
function ensureVisitorCacheDirectory($dir) {
    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true)) {
            error_log("Failed to create visitor cache directory: " . $dir);
            return false;
        }
    }
    
    if (!is_writable($dir)) {
        error_log("Visitor cache directory is not writable: " . $dir);
        return false;
    }
    
    return true;
}

// Pastikan direktori cache ada dan writable
if (!ensureVisitorCacheDirectory($CACHE_DIR)) {
    $cacheEnabled = false;
    error_log("Visitor cache disabled due to directory issues: " . $CACHE_DIR);
}

/**
 * Fungsi untuk mendapatkan hash dari data visitor untuk SMART DETECTION
 * @param int $journalId ID jurnal
 * @return string Hash untuk deteksi perubahan
 */
function getVisitorDataHash($journalId) {
    $articleDao = DAORegistry::getDAO('ArticleDAO');
    
    $hashData = array();
    
    try {
        // Cek metrics table untuk perubahan terbaru
        $result = $articleDao->retrieve(
            "SELECT 
                COUNT(*) as total_records,
                MAX(CONCAT(IFNULL(day, ''), IFNULL(load_time, ''), IFNULL(entry_time, ''))) as last_update,
                COUNT(DISTINCT UPPER(IFNULL(country_id, IFNULL(country, '')))) as country_count
             FROM metrics 
             WHERE context_id = ?",
            array($journalId)
        );
        
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $hashData['total_records'] = $row['total_records'];
            $hashData['last_update'] = $row['last_update'];
            $hashData['country_count'] = $row['country_count'];
            $result->Close();
        }
        
        // Tambahkan informasi artikel publikasi terbaru
        $result = $articleDao->retrieve(
            "SELECT MAX(pa.date_published) as last_published
             FROM published_articles pa
             JOIN articles a ON pa.article_id = a.article_id
             WHERE a.journal_id = ?",
            array($journalId)
        );
        
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $hashData['last_published'] = $row['last_published'];
            $result->Close();
        }
        
    } catch (Exception $e) {
        error_log("Error generating visitor data hash: " . $e->getMessage());
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
function isVisitorCacheValid($cacheFile, $currentHash) {
    if (!file_exists($cacheFile)) {
        return false;
    }
    
    // Baca header cache untuk cek hash
    $cacheContent = file_get_contents($cacheFile);
    if ($cacheContent === false) {
        return false;
    }
    
    // Extract hash dari cache
    $cacheContent = preg_replace('/^<\?php exit\(\); \?>/', '', $cacheContent);
    $cachedData = unserialize($cacheContent);
    
    if ($cachedData === false) {
        return false;
    }
    
    // SMART DETECTION: Cek apakah data berubah berdasarkan hash
    if (!isset($cachedData['data_hash']) || $cachedData['data_hash'] !== $currentHash) {
        error_log("Visitor smart detection: Data changed, regenerating cache");
        return false; // Data berubah, regenerate cache
    }
    
    // WEEKLY UPDATES: Cache expires setiap 7 hari (604800 detik)
    $cacheTime = filemtime($cacheFile);
    $weeklyExpiry = 604800; // 7 days in seconds
    
    if ($cacheTime === false || (time() - $cacheTime) > $weeklyExpiry) {
        error_log("Visitor weekly update: Cache expired after 7 days, regenerating");
        return false; // Weekly expiry
    }
    
    // Cache masih valid
    return true;
}

/**
 * Menghitung statistik pengunjung berdasarkan negara
 * @param $journalId int ID jurnal
 * @param $forceRefresh bool Paksa menghitung ulang statistik (abaikan cache)
 * @return array Array berisi data pengunjung per negara
 */
function getJournalVisitorCountry($journalId, $forceRefresh = false) {
    global $CACHE_DIR, $cacheEnabled;
    
    // Definisi konstanta ASSOC_TYPE jika belum tersedia
    if (!defined('ASSOC_TYPE_JOURNAL')) define('ASSOC_TYPE_JOURNAL', 256);
    if (!defined('ASSOC_TYPE_ISSUE')) define('ASSOC_TYPE_ISSUE', 257);
    if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
    if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);
    
    try {
        // Generate hash untuk deteksi perubahan data
        $currentDataHash = getVisitorDataHash($journalId);
        
        // Setup cache file
        $cacheFile = $CACHE_DIR . '/journal_' . $journalId . '_visitor_country.php';
        
        // Periksa cache terlebih dahulu (kecuali jika force refresh)
        if ($cacheEnabled && !$forceRefresh && isVisitorCacheValid($cacheFile, $currentDataHash)) {
            $cacheData = getVisitorCountryFromCache($journalId);
            if ($cacheData !== false) {
                // Tambahkan info cache hit
                $cacheData['cache_info'] = array(
                    'cache_hit' => true,
                    'cache_file' => $cacheFile,
                    'data_hash' => $currentDataHash
                );
                return $cacheData;
            }
        }
        
        // Inisialisasi variabel default
        $journalTitle = "";
        $countryData = array();
        $totalUniqueVisitors = 0;
        $totalCountries = 0;
        $topCountries = array();
        $yearlyCountryStats = array();
        $metricsTableExists = "Tidak";
        $countryColumnExists = "Tidak";
        $availableColumns = array();
        
        // Mendapatkan DAO yang diperlukan
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        
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
                    $journalTitle = "ID " . $journalId;
                }
                
                // Pastikan journalTitle adalah string
                if (!is_string($journalTitle)) {
                    $journalTitle = "ID " . $journalId;
                }
                
                // Cek keberadaan tabel metrics
                try {
                    $result = $articleDao->retrieve("SHOW TABLES LIKE 'metrics'");
                    $metricsTableExists = ($result->RecordCount() > 0) ? "Ya" : "Tidak";
                    $result->Close();
                    
                    // Jika tabel metrics ada, cek kolomnya
                    if ($metricsTableExists == "Ya") {
                        $result = $articleDao->retrieve("SHOW COLUMNS FROM metrics");
                        while (!$result->EOF) {
                            $availableColumns[] = $result->fields[0];
                            $result->MoveNext();
                        }
                        $result->Close();
                        
                        // Cek apakah ada kolom country
                        $possibleCountryColumns = array('country_id', 'country', 'geo_country', 'country_code');
                        $countryColumn = '';
                        foreach ($possibleCountryColumns as $col) {
                            if (in_array($col, $availableColumns)) {
                                $countryColumn = $col;
                                $countryColumnExists = "Ya";
                                break;
                            }
                        }
                        
                        // Jika ada kolom country, ambil data
                        if ($countryColumnExists == "Ya") {
                            // Query untuk mendapatkan data per negara
                            $query = "SELECT 
                                        UPPER(TRIM($countryColumn)) as country_code,
                                        COUNT(DISTINCT CONCAT(IFNULL(ip, 'no-ip'), '-', IFNULL(user_agent, 'no-ua'))) as unique_visitors,
                                        SUM(metric) as total_metrics,
                                        COUNT(*) as total_events
                                      FROM metrics 
                                      WHERE context_id = ? 
                                      AND $countryColumn IS NOT NULL 
                                      AND $countryColumn != ''
                                      AND $countryColumn != 'XX'
                                      GROUP BY UPPER(TRIM($countryColumn))
                                      ORDER BY total_metrics DESC";
                            
                            $result = $articleDao->retrieve($query, array($journalId));
                            
                            while ($result && !$result->EOF) {
                                $countryCode = trim($result->fields['country_code']);
                                $uniqueVisitors = (int)$result->fields['unique_visitors'];
                                $totalMetrics = (int)$result->fields['total_metrics'];
                                $totalEvents = (int)$result->fields['total_events'];
                                
                                // Validasi country code (harus 2 karakter)
                                if (strlen($countryCode) == 2 && preg_match('/^[A-Z]{2}$/', $countryCode)) {
                                    $countryData[] = array(
                                        'country_code' => $countryCode,
                                        'unique_visitors' => $uniqueVisitors,
                                        'total_metrics' => $totalMetrics,
                                        'total_events' => $totalEvents,
                                        'avg_metrics_per_visitor' => ($uniqueVisitors > 0) ? round($totalMetrics / $uniqueVisitors, 2) : 0
                                    );
                                    
                                    $totalUniqueVisitors += $uniqueVisitors;
                                }
                                $result->MoveNext();
                            }
                            $result->Close();
                            
                            $totalCountries = count($countryData);
                            
                            // Ambil top 10 countries
                            $topCountries = array_slice($countryData, 0, 10);
                            
                            // Ambil data tahunan jika kolom tanggal tersedia
                            $dateColumn = '';
                            $possibleDateColumns = array('day', 'date', 'load_time', 'entry_time', 'metric_date');
                            foreach ($possibleDateColumns as $col) {
                                if (in_array($col, $availableColumns)) {
                                    $dateColumn = $col;
                                    break;
                                }
                            }
                            
                            if (!empty($dateColumn)) {
                                // Query untuk data tahunan per negara
                                $yearlyQuery = "SELECT 
                                                  YEAR($dateColumn) as year,
                                                  UPPER(TRIM($countryColumn)) as country_code,
                                                  COUNT(DISTINCT CONCAT(IFNULL(ip, 'no-ip'), '-', IFNULL(user_agent, 'no-ua'))) as unique_visitors,
                                                  SUM(metric) as total_metrics
                                                FROM metrics 
                                                WHERE context_id = ? 
                                                AND $countryColumn IS NOT NULL 
                                                AND $countryColumn != ''
                                                AND $countryColumn != 'XX'
                                                AND $dateColumn IS NOT NULL
                                                AND YEAR($dateColumn) >= 2010
                                                AND YEAR($dateColumn) <= YEAR(CURDATE())
                                                GROUP BY YEAR($dateColumn), UPPER(TRIM($countryColumn))
                                                ORDER BY year ASC, total_metrics DESC";
                                
                                $result = $articleDao->retrieve($yearlyQuery, array($journalId));
                                
                                while ($result && !$result->EOF) {
                                    $year = (int)$result->fields['year'];
                                    $countryCode = trim($result->fields['country_code']);
                                    $uniqueVisitors = (int)$result->fields['unique_visitors'];
                                    $totalMetrics = (int)$result->fields['total_metrics'];
                                    
                                    if ($year > 1990 && $year <= date('Y') && strlen($countryCode) == 2) {
                                        if (!isset($yearlyCountryStats[$year])) {
                                            $yearlyCountryStats[$year] = array();
                                        }
                                        
                                        $yearlyCountryStats[$year][] = array(
                                            'country_code' => $countryCode,
                                            'unique_visitors' => $uniqueVisitors,
                                            'total_metrics' => $totalMetrics
                                        );
                                    }
                                    $result->MoveNext();
                                }
                                $result->Close();
                            }
                        } else {
                            // Fallback: coba ambil dari kolom IP dan city
                            error_log("Country column not found in metrics table for journal ID: " . $journalId);
                            
                            // Cek apakah ada kolom IP
                            if (in_array('ip', $availableColumns)) {
                                // Query untuk mendapatkan unique IPs
                                $query = "SELECT 
                                            ip,
                                            COUNT(*) as total_events,
                                            SUM(metric) as total_metrics
                                          FROM metrics 
                                          WHERE context_id = ? 
                                          AND ip IS NOT NULL 
                                          AND ip != ''
                                          GROUP BY ip
                                          ORDER BY total_metrics DESC";
                                
                                $result = $articleDao->retrieve($query, array($journalId));
                                
                                $ipCount = 0;
                                while ($result && !$result->EOF) {
                                    $ipCount++;
                                    $result->MoveNext();
                                }
                                $result->Close();
                                
                                // Simpan info bahwa data IP tersedia tapi tidak ada data country
                                $countryData[] = array(
                                    'info' => 'IP data available but no country information',
                                    'total_unique_ips' => $ipCount
                                );
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Error checking metrics table structure: " . $e->getMessage());
                }
            }
        }
        
        // Siapkan hasil
        $stats = array(
            'journalId' => $journalId,
            'journalTitle' => $journalTitle,
            'totalUniqueVisitors' => $totalUniqueVisitors,
            'totalCountries' => $totalCountries,
            'countryData' => $countryData,
            'topCountries' => $topCountries,
            'yearlyCountryStats' => $yearlyCountryStats,
            'metricsTableExists' => $metricsTableExists,
            'countryColumnExists' => $countryColumnExists,
            'availableColumns' => implode(", ", $availableColumns),
            'calculationDate' => date('Y-m-d H:i:s'),
            'data_hash' => $currentDataHash, // Tambahkan hash untuk smart detection
            'cache_info' => array(
                'cache_hit' => false,
                'cache_file' => $cacheFile
            )
        );
        
        // Simpan hasil dalam cache
        if ($cacheEnabled) {
            $cacheResult = cacheVisitorCountryStats($journalId, $stats);
            if (!$cacheResult) {
                error_log("Failed to cache visitor country stats for journal ID: " . $journalId);
            } else {
                error_log("Successfully saved visitor country cache for journal ID: " . $journalId);
            }
        }
        
        return $stats;
        
    } catch (Exception $e) {
        error_log("Fatal error in visitor country statistics module: " . $e->getMessage());
        return array('error' => 'Error calculating visitor country statistics: ' . $e->getMessage());
    }
}

/**
 * Mendapatkan statistik pengunjung dari cache
 * @param $journalId int ID jurnal
 * @return array|bool Array statistik atau false jika cache tidak ditemukan/tidak valid
 */
function getVisitorCountryFromCache($journalId) {
    global $CACHE_DIR;
    
    $cacheFile = $CACHE_DIR . '/journal_' . $journalId . '_visitor_country.php';
    
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
    $cacheContent = preg_replace('/^<\?php exit\(\); \?>/', '', $cacheContent);
    $cachedStats = unserialize($cacheContent);
    
    return $cachedStats;
}

/**
 * Menyimpan statistik pengunjung dalam cache
 * @param $journalId int ID jurnal
 * @param $stats array Statistik yang akan disimpan
 * @return bool Berhasil atau tidak
 */
function cacheVisitorCountryStats($journalId, $stats) {
    global $CACHE_DIR;
    
    // Siapkan direktori cache
    if (!ensureVisitorCacheDirectory($CACHE_DIR)) {
        error_log("Failed to create cache directory: " . $CACHE_DIR);
        return false;
    }
    
    // File cache reguler
    $cacheFile = $CACHE_DIR . '/journal_' . $journalId . '_visitor_country.php';
    
    try {
        // Simpan statistik ke file cache
        $cacheContent = '<?php exit(); ?>' . serialize($stats);
        $result1 = file_put_contents($cacheFile, $cacheContent);
        
        if ($result1 === false) {
            error_log("Failed to write visitor country cache file: " . $cacheFile);
            return false;
        }
        
        // Verifikasi file berhasil dibuat
        if (!file_exists($cacheFile)) {
            error_log("Visitor cache file was not created: " . $cacheFile);
            return false;
        }
        
        // File cache JSON terkompresi
        $jsonCacheFile = $CACHE_DIR . '/journal_' . $journalId . '_visitor_country.json.gz';
        
        // Siapkan data untuk JSON
        $jsonData = array(
            'journalId' => $journalId,
            'journalTitle' => $stats['journalTitle'],
            'totalUniqueVisitors' => $stats['totalUniqueVisitors'],
            'totalCountries' => $stats['totalCountries'],
            'topCountries' => $stats['topCountries'],
            'yearlyCountryStats' => $stats['yearlyCountryStats'],
            'calculationDate' => $stats['calculationDate'],
            'data_hash' => $stats['data_hash']
        );
        
        $jsonContent = json_encode($jsonData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        
        if ($jsonContent === false) {
            error_log("JSON encoding failed: " . json_last_error_msg());
            return $result1 !== false;
        }
        
        if (function_exists('gzcompress')) {
            $compressedContent = gzcompress($jsonContent, 9);
            if ($compressedContent !== false) {
                $result2 = file_put_contents($jsonCacheFile, $compressedContent);
                if ($result2 !== false) {
                    error_log("Visitor JSON.gz cache file written: " . $jsonCacheFile . " (" . strlen($compressedContent) . " bytes)");
                }
            }
        } else {
            // Fallback to uncompressed JSON
            $result2 = file_put_contents($CACHE_DIR . '/journal_' . $journalId . '_visitor_country.json', $jsonContent);
            error_log("GZIP compression not available, using plain JSON instead");
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error creating visitor country cache: " . $e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk mendapatkan data JSON terkompresi
 * @param $journalId int ID jurnal
 * @return string|bool Konten JSON terkompresi atau false jika gagal
 */
function getJournalVisitorCountryJsonGz($journalId) {
    global $CACHE_DIR;
    
    $jsonCacheFile = $CACHE_DIR . '/journal_' . $journalId . '_visitor_country.json.gz';
    
    // Periksa apakah file cache ada
    if (!file_exists($jsonCacheFile)) {
        // Jika tidak ada, coba buat cache baru
        $stats = getJournalVisitorCountry($journalId);
        if (isset($stats['error'])) {
            return false;
        }
        // Cache sudah dibuat dalam fungsi getJournalVisitorCountry
    }
    
    // Baca file cache JSON terkompresi
    $compressedContent = file_get_contents($jsonCacheFile);
    if ($compressedContent === false) {
        return false;
    }
    
    return $compressedContent;
}
?>
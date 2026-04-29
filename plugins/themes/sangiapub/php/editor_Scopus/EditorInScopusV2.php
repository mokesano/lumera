<?php
/**
 * scopus_editor.php - True Smart Detection Update Version
 * Mencari artikel penulis yang terindeks Scopus dengan True Smart Detection Update
 * 
 * New Features:
 * - Complete author name parsing (first, middle, last name)
 * - Extended cache expiry to 1 month
 * - TRUE Smart Detection Update (not just time-based)
 * - Regular monthly updates
 * - Data change detection
 * - Activity-based intelligence
 * - Context-aware optimization
 * 
 * @version 3.0 - True Smart Detection Update
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Konfigurasi
$apiKey = "73e21cba2e777a3093e24a781e0ee1a9";
$authorId = isset($_GET['authorid']) ? $_GET['authorid'] : null;
$count = isset($_GET['count']) ? min((int)$_GET['count'], 25) : 10;
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$smartUpdate = isset($_GET['smart_update']) && $_GET['smart_update'] === '1';

// Konfigurasi cache yang diperpanjang menjadi 1 bulan
$cachePath = __DIR__ . '/cache';
$cacheExpiry = 2592000; // 1 bulan (30 * 24 * 60 * 60)
$smartUpdateThreshold = 604800; // 1 minggu untuk smart update check
$monthlyUpdateDay = 1; // Tanggal 1 setiap bulan untuk regular update

// Smart Detection Configuration
$activityCheckDays = 30; // Cek aktivitas dalam 30 hari terakhir
$highActivityThreshold = 3; // Author dengan 3+ publikasi dalam 30 hari = high activity
$criticalAuthorThreshold = 1000; // Author dengan 1000+ citation = critical priority

if (!$authorId) {
    echo json_encode(['error' => 'Parameter authorid diperlukan'], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Fungsi untuk parsing nama lengkap penulis - ENHANCED
 * Memisahkan first name, middle name, dan last name dengan logic yang lebih baik
 */
function parseCompleteAuthorName($givenName, $surname, $fullName = '') {
    $givenName = trim($givenName ?? '');
    $surname = trim($surname ?? '');
    $fullName = trim($fullName ?? '');
    
    // Jika ada fullName dari API, gunakan sebagai fallback
    if (empty($givenName) && empty($surname) && !empty($fullName)) {
        $nameParts = array_filter(explode(' ', $fullName));
        if (count($nameParts) >= 2) {
            $givenName = $nameParts[0];
            $surname = end($nameParts);
            if (count($nameParts) > 2) {
                $middleParts = array_slice($nameParts, 1, -1);
                $givenName .= ' ' . implode(' ', $middleParts);
            }
        }
    }
    
    // Pisahkan given name menjadi first dan middle name
    $givenParts = array_filter(explode(' ', $givenName));
    $firstName = '';
    $middleName = '';
    
    if (count($givenParts) > 0) {
        $firstName = $givenParts[0];
        if (count($givenParts) > 1) {
            $middleName = implode(' ', array_slice($givenParts, 1));
        }
    }
    
    // Handle edge cases
    if (empty($firstName) && !empty($surname)) {
        $firstName = $surname;
        $surname = '';
    }
    
    $fullNameCombined = trim("$firstName " . ($middleName ? "$middleName " : "") . $surname);
    $displayName = !empty($fullNameCombined) ? $fullNameCombined : ($fullName ?: 'Unknown Author');
    
    return [
        'first_name' => $firstName,
        'middle_name' => $middleName,
        'last_name' => $surname,
        'full_name' => $displayName,
        'display_name' => $displayName,
        'original_given' => $givenName,
        'original_surname' => $surname
    ];
}

/**
 * TRUE SMART DETECTION - Data Change Detection
 * Generate hash dari data yang sensitif terhadap perubahan
 */
function generateAuthorDataHash($authorId, $apiKey) {
    // Data yang akan dimonitor untuk changes
    $monitoringData = [];
    
    // 1. Cek publication count dari API
    $countUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId)&count=0";
    $countData = callScopusApiQuick($countUrl, $apiKey);
    
    if (!isset($countData['error'])) {
        $totalResults = $countData['search-results']['opensearch:totalResults'] ?? 0;
        $monitoringData['total_publications'] = (int)$totalResults;
    }
    
    // 2. Cek recent publications (last 30 days)
    $recentDate = date('Y-m-d', strtotime('-30 days'));
    $recentUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId) AND PUBYEAR > " . date('Y', strtotime('-1 year')) . "&count=5&sort=coverDate&field=eid,dc:title,prism:coverDate,citedby-count";
    $recentData = callScopusApiQuick($recentUrl, $apiKey);
    
    if (!isset($recentData['error']) && isset($recentData['search-results']['entry'])) {
        $recentPubs = [];
        foreach ($recentData['search-results']['entry'] as $pub) {
            $recentPubs[] = [
                'eid' => $pub['eid'] ?? '',
                'title' => $pub['dc:title'] ?? '',
                'date' => $pub['prism:coverDate'] ?? '',
                'citations' => $pub['citedby-count'] ?? 0
            ];
        }
        $monitoringData['recent_publications'] = $recentPubs;
    }
    
    // 3. Cek author profile basic info
    $profileUrl = "https://api.elsevier.com/content/author/author_id/$authorId?field=document-count,citation-count,h-index";
    $profileData = callScopusApiQuick($profileUrl, $apiKey);
    
    if (!isset($profileData['error']) && isset($profileData['author-retrieval-response'][0])) {
        $profile = $profileData['author-retrieval-response'][0];
        $monitoringData['profile_stats'] = [
            'document_count' => $profile['document-count'] ?? 0,
            'citation_count' => $profile['citation-count'] ?? 0,
            'h_index' => $profile['h-index'] ?? 0
        ];
    }
    
    // Generate hash dari monitoring data
    return md5(serialize($monitoringData));
}

/**
 * TRUE SMART DETECTION - Activity Analysis
 * Analisis aktivitas author untuk menentukan update frequency
 */
function analyzeAuthorActivity($authorId, $apiKey) {
    $activityLevel = 'low';
    $activityScore = 0;
    $recommendations = [];
    
    // 1. Cek publikasi dalam 30 hari terakhir
    $recentDate = date('Y') - 1; // Cek tahun lalu untuk lebih realistic
    $recentUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId) AND PUBYEAR = $recentDate&count=25";
    $recentData = callScopusApiQuick($recentUrl, $apiKey);
    
    $recentPublications = 0;
    if (!isset($recentData['error']) && isset($recentData['search-results']['opensearch:totalResults'])) {
        $recentPublications = (int)$recentData['search-results']['opensearch:totalResults'];
    }
    
    // 2. Calculate activity score
    if ($recentPublications >= 10) {
        $activityLevel = 'very_high';
        $activityScore = 100;
        $recommendations[] = "Update setiap 3 hari (very active author)";
    } elseif ($recentPublications >= 5) {
        $activityLevel = 'high';
        $activityScore = 75;
        $recommendations[] = "Update setiap 1 minggu (active author)";
    } elseif ($recentPublications >= 2) {
        $activityLevel = 'medium';
        $activityScore = 50;
        $recommendations[] = "Update setiap 2 minggu (moderate author)";
    } elseif ($recentPublications >= 1) {
        $activityLevel = 'low';
        $activityScore = 25;
        $recommendations[] = "Update setiap 1 bulan (low activity)";
    } else {
        $activityLevel = 'dormant';
        $activityScore = 0;
        $recommendations[] = "Update setiap 3 bulan (dormant author)";
    }
    
    // 3. Cek total citation count untuk context
    $profileUrl = "https://api.elsevier.com/content/author/author_id/$authorId";
    $profileData = callScopusApiQuick($profileUrl, $apiKey);
    
    $totalCitations = 0;
    $hIndex = 0;
    if (!isset($profileData['error']) && isset($profileData['author-retrieval-response'][0])) {
        $profile = $profileData['author-retrieval-response'][0];
        $totalCitations = (int)($profile['citation-count'] ?? 0);
        $hIndex = (int)($profile['h-index'] ?? 0);
    }
    
    // 4. Adjust activity level based on citation impact
    global $criticalAuthorThreshold;
    if ($totalCitations > $criticalAuthorThreshold || $hIndex > 20) {
        $activityScore += 25; // Boost for high-impact authors
        $recommendations[] = "High-impact author (boosted update frequency)";
    }
    
    return [
        'activity_level' => $activityLevel,
        'activity_score' => min(100, $activityScore),
        'recent_publications' => $recentPublications,
        'total_citations' => $totalCitations,
        'h_index' => $hIndex,
        'recommendations' => $recommendations,
        'suggested_update_days' => $activityLevel === 'very_high' ? 3 : 
                                  ($activityLevel === 'high' ? 7 : 
                                  ($activityLevel === 'medium' ? 14 : 
                                  ($activityLevel === 'low' ? 30 : 90)))
    ];
}

/**
 * TRUE SMART DETECTION - Context-Aware Update Decision
 */
function needsTrueSmartUpdate($cacheData, $authorId, $apiKey) {
    if (!$cacheData) {
        return ['should_update' => true, 'reason' => 'cache_miss', 'confidence' => 100];
    }
    
    $lastUpdate = $cacheData['timestamp'];
    $timeSinceUpdate = time() - $lastUpdate;
    
    // 1. Hard expiry check (1 month)
    global $cacheExpiry;
    if ($timeSinceUpdate >= $cacheExpiry) {
        return ['should_update' => true, 'reason' => 'cache_expired', 'confidence' => 100];
    }
    
    // 2. Monthly regular update check
    global $monthlyUpdateDay;
    $currentDay = (int)date('j');
    $lastUpdateDay = (int)date('j', $lastUpdate);
    
    if ($currentDay === $monthlyUpdateDay && $lastUpdateDay !== $monthlyUpdateDay) {
        return ['should_update' => true, 'reason' => 'monthly_regular_update', 'confidence' => 90];
    }
    
    // 3. Data change detection
    $currentHash = generateAuthorDataHash($authorId, $apiKey);
    $cachedHash = $cacheData['data_hash'] ?? '';
    
    if ($currentHash !== $cachedHash) {
        return ['should_update' => true, 'reason' => 'data_changed', 'confidence' => 95];
    }
    
    // 4. Activity-based intelligence
    $activityAnalysis = analyzeAuthorActivity($authorId, $apiKey);
    $suggestedUpdateDays = $activityAnalysis['suggested_update_days'];
    
    if ($timeSinceUpdate > ($suggestedUpdateDays * 24 * 60 * 60)) {
        return [
            'should_update' => true, 
            'reason' => 'activity_based_update', 
            'confidence' => $activityAnalysis['activity_score'],
            'activity_analysis' => $activityAnalysis
        ];
    }
    
    // 5. Context-aware checks
    $daysSinceUpdate = $timeSinceUpdate / (24 * 60 * 60);
    
    // High-impact authors get more frequent updates
    if ($activityAnalysis['activity_score'] > 75 && $daysSinceUpdate > 3) {
        return [
            'should_update' => true, 
            'reason' => 'high_impact_author', 
            'confidence' => 80,
            'activity_analysis' => $activityAnalysis
        ];
    }
    
    // Weekend updates for active authors (reduced API load during weekdays)
    if ($activityAnalysis['activity_score'] > 50 && date('N') >= 6 && $daysSinceUpdate > 7) {
        return [
            'should_update' => true, 
            'reason' => 'weekend_update_active_author', 
            'confidence' => 60,
            'activity_analysis' => $activityAnalysis
        ];
    }
    
    // Cache is still valid
    return [
        'should_update' => false, 
        'reason' => 'cache_valid', 
        'confidence' => 0,
        'days_until_suggested_update' => max(0, $suggestedUpdateDays - $daysSinceUpdate),
        'activity_analysis' => $activityAnalysis
    ];
}

/**
 * Quick API call untuk monitoring (dengan timeout lebih pendek)
 */
function callScopusApiQuick($url, $apiKey, $timeout = 10) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => [
            "X-ELS-APIKey: $apiKey",
            "Accept: application/json",
            "User-Agent: ScopusEditor/3.0-SmartDetection"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_VERBOSE => false,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300 && $response) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
    }
    
    return ['error' => 'API call failed: HTTP ' . $httpCode . ($error ? ' - ' . $error : '')];
}

/**
 * Fungsi untuk menghasilkan nama file cache
 */
function generateCacheFilename($authorId, $compressed = true) {
    $cacheCode = substr(md5($authorId), 0, 8);
    $filename = 'authorid_' . $cacheCode . '_' . $authorId;
    return $filename . ($compressed ? '.json.gz' : '.json');
}

/**
 * Fungsi untuk mengambil data dari cache
 */
function getFromCache($cacheFile) {
    if (!file_exists($cacheFile)) {
        return null;
    }

    $isCompressed = (substr($cacheFile, -3) === '.gz');

    try {
        $cacheData = $isCompressed ? gzdecode(file_get_contents($cacheFile)) : file_get_contents($cacheFile);
        $cacheObj = json_decode($cacheData, true);
        
        if (!$cacheObj || !isset($cacheObj['timestamp'])) {
            return null;
        }
        
        return $cacheObj;
    } catch (Exception $e) {
        error_log("Cache read error: " . $e->getMessage());
        return null;
    }
}

/**
 * Fungsi untuk menyimpan data ke cache
 */
function saveToCache($cacheFile, $data) {
    if (!is_dir(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0755, true);
    }
    
    $cacheObj = [
        'timestamp' => time(),
        'last_updated' => date('Y-m-d H:i:s'),
        'cache_version' => '3.0',
        'update_type' => 'smart_detection',
        'data' => $data
    ];
    
    try {
        $jsonData = json_encode($cacheObj);
        $isCompressed = (substr($cacheFile, -3) === '.gz');
        
        if ($isCompressed) {
            file_put_contents($cacheFile, gzencode($jsonData, 9));
        } else {
            file_put_contents($cacheFile, $jsonData);
        }
        return true;
    } catch (Exception $e) {
        error_log("Cache write error: " . $e->getMessage());
        return false;
    }
}

/**
 * Fungsi untuk memanggil Scopus API dengan retry mechanism
 */
function callScopusApi($url, $apiKey, $maxRetries = 3) {
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                "X-ELS-APIKey: $apiKey",
                "Accept: application/json",
                "User-Agent: ScopusEditor/3.0-SmartDetection"
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_VERBOSE => false,
            CURLOPT_SSL_VERIFYPEER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode >= 200 && $httpCode < 300 && $response) {
            $data = json_decode($response, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $data;
            }
        }
        
        if ($httpCode === 429) {
            sleep(2 ** $retryCount);
        }
        
        $retryCount++;
        
        if ($retryCount < $maxRetries) {
            usleep(500000);
            continue;
        }
        
        if ($httpCode >= 400) {
            return ['error' => 'API error: HTTP ' . $httpCode . ($error ? ' - ' . $error : '')];
        }
        
        if (!$response) {
            return ['error' => 'Empty response' . ($error ? ' - ' . $error : '')];
        }
        
        return ['error' => 'JSON decode error: ' . json_last_error_msg()];
    }
    
    return ['error' => 'Maximum retries exceeded'];
}

/**
 * Fungsi untuk memproses data author dengan parsing nama lengkap ENHANCED
 */
function processAuthorsEnhanced($doc) {
    $authors = [];
    
    if (isset($doc['author']) && is_array($doc['author'])) {
        foreach ($doc['author'] as $author) {
            $givenName = isset($author['ce:given-name']) ? trim($author['ce:given-name']) : '';
            $surname = isset($author['ce:surname']) ? trim($author['ce:surname']) : '';
            $fullName = isset($author['ce:indexed-name']) ? trim($author['ce:indexed-name']) : '';
            
            if (!empty($givenName) || !empty($surname) || !empty($fullName)) {
                $nameData = parseCompleteAuthorName($givenName, $surname, $fullName);
                
                $authors[] = [
                    'first_name' => $nameData['first_name'],
                    'middle_name' => $nameData['middle_name'],
                    'last_name' => $nameData['last_name'],
                    'full_name' => $nameData['full_name'],
                    'display_name' => $nameData['display_name'],
                    'original_given' => $nameData['original_given'],
                    'original_surname' => $nameData['original_surname'],
                    'authid' => isset($author['@auid']) ? $author['@auid'] : null,
                    'affiliation' => isset($author['affiliation']) ? $author['affiliation'] : null,
                    'orcid' => isset($author['@orcid']) ? $author['@orcid'] : null
                ];
            }
        }
    }
    
    // Fallback untuk dc:creator
    if (empty($authors) && isset($doc['dc:creator'])) {
        if (is_string($doc['dc:creator'])) {
            $creatorNames = array_filter(array_map('trim', explode(';', $doc['dc:creator'])));
            foreach ($creatorNames as $name) {
                $nameData = parseCompleteAuthorName('', '', $name);
                $authors[] = [
                    'first_name' => $nameData['first_name'],
                    'middle_name' => $nameData['middle_name'],
                    'last_name' => $nameData['last_name'],
                    'full_name' => $nameData['full_name'],
                    'display_name' => $nameData['display_name'],
                    'original_given' => $nameData['original_given'],
                    'original_surname' => $nameData['original_surname']
                ];
            }
        }
    }
    
    return $authors;
}

/**
 * Fungsi untuk mengambil detail lengkap publikasi dengan parsing nama ENHANCED
 */
function fetchPublicationDetailsEnhanced($eid, $apiKey) {
    global $cachePath, $cacheExpiry;
    
    $cacheFile = $cachePath . '/pubdetails_' . substr(md5($eid), 0, 8) . '_' . $eid . '.json.gz';
    $cacheData = getFromCache($cacheFile);
    
    if ($cacheData && (time() - $cacheData['timestamp'] < $cacheExpiry)) {
        return $cacheData['data'];
    }
    
    $url = "https://api.elsevier.com/content/abstract/eid/$eid?view=FULL";
    $data = callScopusApi($url, $apiKey);
    
    if (isset($data['error'])) {
        return ['error' => $data['error']];
    }
    
    $details = [
        'abstract' => 'N/A',
        'publication_type' => 'N/A',
        'open_access' => false,
        'volume' => 'N/A',
        'issue' => 'N/A',
        'pages' => 'N/A',
        'publication_date' => 'N/A',
        'authors' => [],
        'keywords' => [],
        'funding' => []
    ];
    
    if (isset($data['abstracts-retrieval-response'])) {
        $response = $data['abstracts-retrieval-response'];
        
        if (isset($response['coredata']['dc:description'])) {
            $details['abstract'] = $response['coredata']['dc:description'];
        }
        
        // Enhanced author parsing
        if (isset($response['authors']['author'])) {
            $authors = $response['authors']['author'];
            if (isset($authors['@auid'])) {
                $authors = array($authors);
            }
            
            foreach ($authors as $author) {
                $givenName = '';
                $surname = '';
                $fullName = '';
                
                if (isset($author['preferred-name'])) {
                    $givenName = trim($author['preferred-name']['given-name'] ?? '');
                    $surname = trim($author['preferred-name']['surname'] ?? '');
                }
                
                if (isset($author['ce:indexed-name'])) {
                    $fullName = trim($author['ce:indexed-name']);
                }
                
                if (empty($givenName) && isset($author['ce:given-name'])) {
                    $givenName = trim($author['ce:given-name']);
                }
                if (empty($surname) && isset($author['ce:surname'])) {
                    $surname = trim($author['ce:surname']);
                }
                
                if (!empty($givenName) || !empty($surname) || !empty($fullName)) {
                    $nameData = parseCompleteAuthorName($givenName, $surname, $fullName);
                    
                    $affiliation = 'N/A';
                    if (isset($author['affiliation']) && is_array($author['affiliation'])) {
                        if (isset($author['affiliation']['organization'])) {
                            $affiliation = $author['affiliation']['organization'];
                        } elseif (isset($author['affiliation'][0]['organization'])) {
                            $affiliation = $author['affiliation'][0]['organization'];
                        }
                    }
                    
                    $details['authors'][] = [
                        'first_name' => $nameData['first_name'],
                        'middle_name' => $nameData['middle_name'],
                        'last_name' => $nameData['last_name'],
                        'full_name' => $nameData['full_name'],
                        'display_name' => $nameData['display_name'],
                        'original_given' => $nameData['original_given'],
                        'original_surname' => $nameData['original_surname'],
                        'authid' => isset($author['@auid']) ? $author['@auid'] : null,
                        'affiliation' => $affiliation,
                        'orcid' => isset($author['@orcid']) ? $author['@orcid'] : null
                    ];
                }
            }
        }
        
        // Extract keywords
        if (isset($response['authkeywords']['author-keyword'])) {
            $keywords = $response['authkeywords']['author-keyword'];
            if (!is_array($keywords[0])) {
                $keywords = [$keywords];
            }
            foreach ($keywords as $keyword) {
                if (isset($keyword['$'])) {
                    $details['keywords'][] = trim($keyword['$']);
                }
            }
        }
        
        // Fill other details
        if (isset($response['coredata'])) {
            $coredata = $response['coredata'];
            $details['publication_type'] = isset($coredata['prism:aggregationType']) ? $coredata['prism:aggregationType'] : 'N/A';
            $details['open_access'] = isset($coredata['openaccessFlag']) ? ($coredata['openaccessFlag'] === '1') : false;
            $details['volume'] = isset($coredata['prism:volume']) ? $coredata['prism:volume'] : 'N/A';
            $details['issue'] = isset($coredata['prism:issueIdentifier']) ? $coredata['prism:issueIdentifier'] : 'N/A';
            $details['pages'] = isset($coredata['prism:pageRange']) ? $coredata['prism:pageRange'] : 'N/A';
            $details['publication_date'] = isset($coredata['prism:coverDate']) ? $coredata['prism:coverDate'] : 'N/A';
        }
    }
    
    saveToCache($cacheFile, $details);
    return $details;
}

// === MAIN EXECUTION ===

// Output structure yang diperbaiki dengan True Smart Detection
$output = [
    'author_id' => $authorId,
    'status' => 'success',
    'cache_status' => null,
    'cache_file' => null,
    'cache_expires' => null,
    'cache_expiry_days' => 30,
    'last_updated' => date('Y-m-d H:i:s'),
    'data_source' => null,
    'smart_detection' => [
        'enabled' => true,
        'version' => '3.0_true_smart',
        'features' => [
            'data_change_detection' => true,
            'activity_based_intelligence' => true,
            'context_aware_optimization' => true,
            'regular_monthly_updates' => true,
            'hash_based_monitoring' => true
        ]
    ],
    'update_info' => [
        'smart_update_enabled' => $smartUpdate,
        'force_refresh' => $forceRefresh,
        'next_regular_update' => date('Y-m-d', strtotime('first day of next month')),
        'cache_version' => '3.0'
    ],
    'data' => [
        'author' => null,
        'publications' => [],
        'total_citations' => 0,
        'publication_count' => 0,
        'author_statistics' => [
            'publications_by_year' => [],
            'citations_by_year' => [],
            'avg_citations_per_paper' => 0,
            'activity_analysis' => null
        ]
    ],
    'errors' => [],
    'performance_metrics' => [
        'api_calls_made' => 0,
        'cache_hits' => 0,
        'processing_time_start' => microtime(true)
    ]
];

// Generate nama file cache
$cacheFile = $cachePath . '/' . generateCacheFilename($authorId, true);
$output['cache_file'] = basename($cacheFile);

// Check cache dan True Smart Detection
$cacheData = getFromCache($cacheFile);
if ($cacheData) {
    $output['cache_expires'] = date('Y-m-d H:i:s', $cacheData['timestamp'] + $cacheExpiry);
    $output['smart_detection']['data_hash'] = $cacheData['data_hash'] ?? 'not_available';
}

// TRUE SMART DETECTION LOGIC
$shouldUpdate = false;
$updateReason = '';
$smartAnalysis = null;
$activityAnalysis = null;

if ($forceRefresh) {
    $shouldUpdate = true;
    $updateReason = 'force_refresh';
    $output['performance_metrics']['api_calls_made']++;
} elseif (!$cacheData) {
    $shouldUpdate = true;
    $updateReason = 'cache_miss';
    $output['performance_metrics']['api_calls_made']++;
} else {
    // Run True Smart Detection
    $smartAnalysis = needsTrueSmartUpdate($cacheData, $authorId, $apiKey);
    $output['performance_metrics']['api_calls_made'] += 3; // Smart detection API calls
    
    if ($smartAnalysis['should_update']) {
        $shouldUpdate = true;
        $updateReason = $smartAnalysis['reason'];
        $activityAnalysis = $smartAnalysis['activity_analysis'] ?? null;
    } else {
        // Cache is valid, use it
        $output['cache_status'] = 'hit';
        $output['data_source'] = 'cache';
        $output['data'] = $cacheData['data'];
        $output['last_updated'] = $cacheData['last_updated'];
        $output['update_info']['update_reason'] = $smartAnalysis['reason'];
        $output['smart_detection']['analysis'] = $smartAnalysis;
        $output['performance_metrics']['cache_hits'] = 1;
        $output['performance_metrics']['processing_time'] = round((microtime(true) - $output['performance_metrics']['processing_time_start']) * 1000, 2) . 'ms';
        
        echo json_encode($output, JSON_PRETTY_PRINT);
        exit;
    }
}

// Jika sampai di sini, data akan diambil dari API
$output['cache_status'] = $cacheData ? 'expired' : 'miss';
$output['data_source'] = 'api';
$output['update_info']['update_reason'] = $updateReason;
$output['smart_detection']['analysis'] = $smartAnalysis;

// Generate current data hash untuk monitoring
$currentDataHash = generateAuthorDataHash($authorId, $apiKey);
$output['smart_detection']['current_data_hash'] = substr($currentDataHash, 0, 8);
$output['performance_metrics']['api_calls_made'] += 3; // Hash generation API calls

// 1. Ambil semua publikasi dengan paginasi yang lebih baik
$allPublications = [];
$start = 0;
$maxResults = 200;
$publicationsByYear = [];
$citationsByYear = [];

do {
    $docsUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId)&start=$start&count=$count&field=eid,dc:title,prism:coverDate,citedby-count,prism:doi,prism:publicationName,author,dc:creator,prism:aggregationType,openaccessFlag,prism:volume,prism:issueIdentifier,prism:pageRange,subtypeDescription&sort=coverDate";
    $docsData = callScopusApi($docsUrl, $apiKey);
    $output['performance_metrics']['api_calls_made']++;

    if (isset($docsData['error'])) {
        $output['errors']['publications'] = $docsData['error'];
        break;
    }

    if (empty($docsData['search-results']['entry'])) break;

    foreach ($docsData['search-results']['entry'] as $doc) {
        $eid = isset($doc['eid']) ? $doc['eid'] : null;
        $pubDetails = $eid ? fetchPublicationDetailsEnhanced($eid, $apiKey) : [];
        if ($eid) $output['performance_metrics']['api_calls_made']++;
        
        $authors = isset($pubDetails['authors']) && !empty($pubDetails['authors']) ? 
                   $pubDetails['authors'] : processAuthorsEnhanced($doc);

        $year = isset($doc['prism:coverDate']) ? substr($doc['prism:coverDate'], 0, 4) : 'N/A';
        $citationCount = isset($doc['citedby-count']) ? (int)$doc['citedby-count'] : 0;
        
        // Statistik per tahun
        if ($year !== 'N/A') {
            $publicationsByYear[$year] = ($publicationsByYear[$year] ?? 0) + 1;
            $citationsByYear[$year] = ($citationsByYear[$year] ?? 0) + $citationCount;
        }

        $publication = [
            'eid' => $eid ?: 'N/A',
            'title' => isset($doc['dc:title']) ? $doc['dc:title'] : 'N/A',
            'year' => $year,
            'citation_count' => $citationCount,
            'doi' => isset($doc['prism:doi']) ? $doc['prism:doi'] : 'N/A',
            'source' => isset($doc['prism:publicationName']) ? $doc['prism:publicationName'] : 'N/A',
            'authors' => $authors,
            'authors_string' => !empty($authors) ? implode('; ', array_column($authors, 'display_name')) : 'N/A',
            'authors_detailed' => !empty($authors) ? array_map(function($author) {
                return [
                    'first_name' => $author['first_name'] ?? '',
                    'middle_name' => $author['middle_name'] ?? '',
                    'last_name' => $author['last_name'] ?? '',
                    'full_name' => $author['full_name'] ?? '',
                    'display_name' => $author['display_name'] ?? '',
                    'authid' => $author['authid'] ?? null,
                    'orcid' => $author['orcid'] ?? null,
                    'affiliation' => $author['affiliation'] ?? 'N/A'
                ];
            }, $authors) : [],
            'abstract' => isset($pubDetails['abstract']) ? $pubDetails['abstract'] : 'N/A',
            'keywords' => isset($pubDetails['keywords']) ? $pubDetails['keywords'] : [],
            'publication_type' => isset($doc['prism:aggregationType']) ? $doc['prism:aggregationType'] : 
                                (isset($pubDetails['publication_type']) ? $pubDetails['publication_type'] : 'N/A'),
            'subtype' => isset($doc['subtypeDescription']) ? $doc['subtypeDescription'] : 'N/A',
            'open_access' => isset($doc['openaccessFlag']) ? ($doc['openaccessFlag'] === '1') : 
                            (isset($pubDetails['open_access']) ? $pubDetails['open_access'] : false),
            'volume' => isset($doc['prism:volume']) ? $doc['prism:volume'] : 
                      (isset($pubDetails['volume']) ? $pubDetails['volume'] : 'N/A'),
            'issue' => isset($doc['prism:issueIdentifier']) ? $doc['prism:issueIdentifier'] : 
                     (isset($pubDetails['issue']) ? $pubDetails['issue'] : 'N/A'),
            'pages' => isset($doc['prism:pageRange']) ? $doc['prism:pageRange'] : 
                     (isset($pubDetails['pages']) ? $pubDetails['pages'] : 'N/A'),
            'publication_date' => isset($doc['prism:coverDate']) ? $doc['prism:coverDate'] : 
                                (isset($pubDetails['publication_date']) ? $pubDetails['publication_date'] : 'N/A')
        ];
        
        $allPublications[] = $publication;
    }

    $start += $count;
} while ($start < $maxResults && count($allPublications) < $maxResults);

// 2. Hitung statistik
$totalCitations = array_sum(array_column($allPublications, 'citation_count'));
$avgCitations = count($allPublications) > 0 ? round($totalCitations / count($allPublications), 2) : 0;

// Sort statistik by year
ksort($publicationsByYear);
ksort($citationsByYear);

// Get activity analysis if not already done
if (!$activityAnalysis) {
    $activityAnalysis = analyzeAuthorActivity($authorId, $apiKey);
    $output['performance_metrics']['api_calls_made'] += 2;
}

// 3. Isi data output
$output['data']['publications'] = $allPublications;
$output['data']['total_citations'] = $totalCitations;
$output['data']['publication_count'] = count($allPublications);
$output['data']['author_statistics'] = [
    'publications_by_year' => $publicationsByYear,
    'citations_by_year' => $citationsByYear,
    'avg_citations_per_paper' => $avgCitations,
    'activity_analysis' => $activityAnalysis,
    'most_cited_paper' => !empty($allPublications) ? 
        array_reduce($allPublications, function($max, $current) {
            return ($current['citation_count'] > ($max['citation_count'] ?? 0)) ? $current : $max;
        }) : null
];

// 4. Ambil profil penulis dengan parsing nama enhanced
$authorProfileCache = $cachePath . '/profile_' . generateCacheFilename($authorId, true);
$profileCacheData = getFromCache($authorProfileCache);
$useProfileCache = false;

if (!$forceRefresh && $profileCacheData && (time() - $profileCacheData['timestamp'] < $cacheExpiry)) {
    $output['data']['author'] = $profileCacheData['data'];
    $useProfileCache = true;
    $output['performance_metrics']['cache_hits']++;
}

if (!$useProfileCache) {
    $authorUrl = "https://api.elsevier.com/content/author/author_id/$authorId";
    $authorData = callScopusApi($authorUrl, $apiKey);
    $output['performance_metrics']['api_calls_made']++;

    if (!isset($authorData['error']) && isset($authorData['author-retrieval-response'][0])) {
        $profile = $authorData['author-retrieval-response'][0];
        
        $givenName = isset($profile['author-profile']['preferred-name']['given-name']) ? 
                    $profile['author-profile']['preferred-name']['given-name'] : '';
        $surname = isset($profile['author-profile']['preferred-name']['surname']) ? 
                  $profile['author-profile']['preferred-name']['surname'] : '';
        $indexedName = isset($profile['author-profile']['preferred-name']['indexed-name']) ? 
                      $profile['author-profile']['preferred-name']['indexed-name'] : '';
        
        $nameData = parseCompleteAuthorName($givenName, $surname, $indexedName);
        
        $output['data']['author'] = [
            'first_name' => $nameData['first_name'],
            'middle_name' => $nameData['middle_name'],
            'last_name' => $nameData['last_name'],
            'full_name' => $nameData['full_name'],
            'display_name' => $nameData['display_name'],
            'original_given' => $nameData['original_given'],
            'original_surname' => $nameData['original_surname'],
            'indexed_name' => $indexedName,
            'affiliation' => isset($profile['author-profile']['affiliation-current']['affiliation']['organization']) ? 
                           $profile['author-profile']['affiliation-current']['affiliation']['organization'] : 'N/A',
            'h_index' => isset($profile['h-index']) ? (int)$profile['h-index'] : 0,
            'document_count' => isset($profile['document-count']) ? (int)$profile['document-count'] : 0,
            'citation_count' => isset($profile['citation-count']) ? (int)$profile['citation-count'] : $totalCitations,
            'orcid' => isset($profile['coredata']['orcid']) ? $profile['coredata']['orcid'] : null,
            'scopus_id' => $authorId
        ];
        
        saveToCache($authorProfileCache, $output['data']['author']);
    } else {
        // Fallback: rekonstruksi dari publikasi
        $authorInfo = null;
        foreach ($allPublications as $pub) {
            foreach ($pub['authors'] as $author) {
                if (isset($author['authid']) && $author['authid'] == $authorId) {
                    $authorInfo = [
                        'first_name' => $author['first_name'] ?? '',
                        'middle_name' => $author['middle_name'] ?? '',
                        'last_name' => $author['last_name'] ?? '',
                        'full_name' => $author['full_name'] ?? '',
                        'display_name' => $author['display_name'] ?? '',
                        'original_given' => $author['original_given'] ?? '',
                        'original_surname' => $author['original_surname'] ?? '',
                        'affiliation' => isset($author['affiliation']) ? $author['affiliation'] : 'N/A',
                        'h_index' => 0,
                        'document_count' => count($allPublications),
                        'citation_count' => $totalCitations,
                        'scopus_id' => $authorId
                    ];
                    break 2;
                }
            }
        }
        
        if ($authorInfo) {
            $output['data']['author'] = $authorInfo;
            saveToCache($authorProfileCache, $authorInfo);
        } else {
            $output['errors']['author_profile'] = isset($authorData['error']) ? $authorData['error'] : 'Profil tidak tersedia';
        }
    }
}

// Simpan hasil ke cache dengan hash baru dan timestamp
$cacheDataToSave = $output['data'];
$cacheDataToSave['data_hash'] = $currentDataHash;
$cacheDataToSave['smart_detection_info'] = [
    'activity_analysis' => $activityAnalysis,
    'update_reason' => $updateReason,
    'generated_at' => time(),
    'api_calls_made' => $output['performance_metrics']['api_calls_made']
];

if (saveToCache($cacheFile, $cacheDataToSave)) {
    $output['cache_expires'] = date('Y-m-d H:i:s', time() + $cacheExpiry);
    $output['update_info']['cache_saved'] = true;
} else {
    $output['errors']['cache'] = 'Gagal menyimpan cache';
    $output['update_info']['cache_saved'] = false;
}

// Final performance metrics
$output['performance_metrics']['processing_time'] = round((microtime(true) - $output['performance_metrics']['processing_time_start']) * 1000, 2) . 'ms';
unset($output['performance_metrics']['processing_time_start']);

// Smart Detection Summary
$output['smart_detection']['summary'] = [
    'data_hash_changed' => isset($smartAnalysis) ? ($smartAnalysis['reason'] === 'data_changed') : false,
    'activity_level' => $activityAnalysis['activity_level'] ?? 'unknown',
    'suggested_update_frequency' => $activityAnalysis['suggested_update_days'] ?? 30,
    'confidence_score' => isset($smartAnalysis) ? $smartAnalysis['confidence'] : 0,
    'next_smart_check' => date('Y-m-d H:i:s', time() + ($activityAnalysis['suggested_update_days'] ?? 30) * 24 * 60 * 60)
];

echo json_encode($output, JSON_PRETTY_PRINT);
?>
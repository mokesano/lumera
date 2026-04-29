<?php
/**
 * scopus_editor.php - Smart Detection Update
 * Mencari artikel penulis yang terindeks Scopus dengan Smart Detection Update
 * 
 * Features:
 * - Complete author name parsing (first, middle, last name)
 * - Cache expiry 1 month
 * - Smart Detection Update (update only if data changed)
 * - Regular monthly updates
 * - MODERN = SIMPLICITY (1 file cache)
 * 
 * @version 3.1 - Clean & Simple
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Konfigurasi
$apiKey = "73e21cba2e777a3093e24a781e0ee1a9";
$authorId = isset($_GET['authorid']) ? $_GET['authorid'] : null;
$count = isset($_GET['count']) ? min((int)$_GET['count'], 25) : 10;
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$smartUpdate = isset($_GET['smart_update']) && $_GET['smart_update'] === '1';

// Konfigurasi cache - 1 BULAN
$cachePath = __DIR__ . '/cache';
$cacheExpiry = 2592000; // 1 bulan (30 * 24 * 60 * 60)
$smartUpdateThreshold = 604800; // 1 minggu
$monthlyUpdateDay = 1; // Tanggal 1 setiap bulan

if (!$authorId) {
    echo json_encode(['error' => 'Parameter authorid diperlukan'], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Parsing nama lengkap penulis
 */
function parseAuthorName($givenName, $surname, $fullName = '') {
    $givenName = trim($givenName ?? '');
    $surname = trim($surname ?? '');
    $fullName = trim($fullName ?? '');
    
    // Fallback jika given/surname kosong
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
    
    // Pisahkan given name menjadi first dan middle
    $givenParts = array_filter(explode(' ', $givenName));
    $firstName = isset($givenParts[0]) ? $givenParts[0] : '';
    $middleName = count($givenParts) > 1 ? implode(' ', array_slice($givenParts, 1)) : '';
    
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
 * Generate hash untuk Smart Detection Update
 */
function generateDataHash($authorId, $apiKey) {
    $hashData = [];
    
    // Cek publication count
    $countUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId)&count=0";
    $countData = callScopusApiQuick($countUrl, $apiKey);
    
    if (!isset($countData['error'])) {
        $hashData['total_publications'] = (int)($countData['search-results']['opensearch:totalResults'] ?? 0);
    }
    
    // Cek recent publications
    $recentYear = date('Y') - 1;
    $recentUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId) AND PUBYEAR = $recentYear&count=5";
    $recentData = callScopusApiQuick($recentUrl, $apiKey);
    
    if (!isset($recentData['error']) && isset($recentData['search-results']['entry'])) {
        $recentPubs = [];
        foreach ($recentData['search-results']['entry'] as $pub) {
            $recentPubs[] = [
                'eid' => $pub['eid'] ?? '',
                'citations' => $pub['citedby-count'] ?? 0
            ];
        }
        $hashData['recent_publications'] = $recentPubs;
    }
    
    // Cek profile stats
    $profileUrl = "https://api.elsevier.com/content/author/author_id/$authorId?field=document-count,citation-count,h-index";
    $profileData = callScopusApiQuick($profileUrl, $apiKey);
    
    if (!isset($profileData['error']) && isset($profileData['author-retrieval-response'][0])) {
        $profile = $profileData['author-retrieval-response'][0];
        $hashData['profile_stats'] = [
            'document_count' => $profile['document-count'] ?? 0,
            'citation_count' => $profile['citation-count'] ?? 0,
            'h_index' => $profile['h-index'] ?? 0
        ];
    }
    
    return md5(serialize($hashData));
}

/**
 * Smart Detection Update - cek apakah perlu update
 */
function needsUpdate($cacheData, $authorId, $apiKey) {
    if (!$cacheData) {
        return ['should_update' => true, 'reason' => 'cache_miss'];
    }
    
    $lastUpdate = $cacheData['timestamp'];
    $timeSinceUpdate = time() - $lastUpdate;
    
    // Hard expiry (1 bulan)
    global $cacheExpiry;
    if ($timeSinceUpdate >= $cacheExpiry) {
        return ['should_update' => true, 'reason' => 'cache_expired'];
    }
    
    // Monthly regular update
    global $monthlyUpdateDay;
    $currentDay = (int)date('j');
    $lastUpdateDay = (int)date('j', $lastUpdate);
    
    if ($currentDay === $monthlyUpdateDay && $lastUpdateDay !== $monthlyUpdateDay) {
        return ['should_update' => true, 'reason' => 'monthly_update'];
    }
    
    // SMART DETECTION - Data change detection
    $currentHash = generateDataHash($authorId, $apiKey);
    $cachedHash = $cacheData['data_hash'] ?? '';
    
    if ($currentHash !== $cachedHash) {
        return ['should_update' => true, 'reason' => 'data_changed'];
    }
    
    // Time-based check (1 minggu)
    global $smartUpdateThreshold;
    if ($timeSinceUpdate > $smartUpdateThreshold) {
        return ['should_update' => true, 'reason' => 'time_threshold'];
    }
    
    return ['should_update' => false, 'reason' => 'cache_valid'];
}

/**
 * Quick API call untuk monitoring
 */
function callScopusApiQuick($url, $apiKey, $timeout = 10) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => [
            "X-ELS-APIKey: $apiKey",
            "Accept: application/json"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_CONNECTTIMEOUT => 5
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300 && $response) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
    }
    
    return ['error' => 'API call failed: HTTP ' . $httpCode];
}

/**
 * Generate cache filename
 */
function generateCacheFilename($authorId) {
    $cacheCode = substr(md5($authorId), 0, 8);
    return 'author_' . $cacheCode . '_' . $authorId . '.json.gz';
}

/**
 * Load dari cache
 */
function getFromCache($cacheFile) {
    if (!file_exists($cacheFile)) {
        return null;
    }

    try {
        $cacheData = gzdecode(file_get_contents($cacheFile));
        $cacheObj = json_decode($cacheData, true);
        
        if (!$cacheObj || !isset($cacheObj['timestamp'])) {
            return null;
        }
        
        return $cacheObj;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Save ke cache
 */
function saveToCache($cacheFile, $data) {
    if (!is_dir(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0755, true);
    }
    
    $cacheObj = [
        'timestamp' => time(),
        'last_updated' => date('Y-m-d H:i:s'),
        'cache_version' => '3.1',
        'data' => $data
    ];
    
    try {
        $jsonData = json_encode($cacheObj);
        file_put_contents($cacheFile, gzencode($jsonData, 9));
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * API call dengan retry
 */
function callScopusApi($url, $apiKey, $maxRetries = 3) {
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => [
                "X-ELS-APIKey: $apiKey",
                "Accept: application/json"
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
    }
    
    return ['error' => 'API call failed after ' . $maxRetries . ' retries'];
}

/**
 * Process authors dengan parsing nama lengkap
 */
function processAuthors($doc) {
    $authors = [];
    
    if (isset($doc['author']) && is_array($doc['author'])) {
        foreach ($doc['author'] as $author) {
            $givenName = trim($author['ce:given-name'] ?? '');
            $surname = trim($author['ce:surname'] ?? '');
            $fullName = trim($author['ce:indexed-name'] ?? '');
            
            if (!empty($givenName) || !empty($surname) || !empty($fullName)) {
                $nameData = parseAuthorName($givenName, $surname, $fullName);
                
                $authors[] = [
                    'first_name' => $nameData['first_name'],
                    'middle_name' => $nameData['middle_name'],
                    'last_name' => $nameData['last_name'],
                    'full_name' => $nameData['full_name'],
                    'display_name' => $nameData['display_name'],
                    'authid' => $author['@auid'] ?? null,
                    'affiliation' => $author['affiliation'] ?? null
                ];
            }
        }
    }
    
    // Fallback dc:creator
    if (empty($authors) && isset($doc['dc:creator'])) {
        if (is_string($doc['dc:creator'])) {
            $creatorNames = array_filter(array_map('trim', explode(';', $doc['dc:creator'])));
            foreach ($creatorNames as $name) {
                $nameData = parseAuthorName('', '', $name);
                $authors[] = [
                    'first_name' => $nameData['first_name'],
                    'middle_name' => $nameData['middle_name'],
                    'last_name' => $nameData['last_name'],
                    'full_name' => $nameData['full_name'],
                    'display_name' => $nameData['display_name']
                ];
            }
        }
    }
    
    return $authors;
}

// === MAIN EXECUTION ===

$startTime = microtime(true);
$apiCalls = 0;

// Output structure
$output = [
    'author_id' => $authorId,
    'status' => 'success',
    'cache_status' => null,
    'data_source' => null,
    'cache_expiry_days' => 30,
    'smart_detection' => [
        'enabled' => true,
        'principle' => 'Update only if data changed - MODERN = SIMPLICITY'
    ],
    'data' => [
        'author' => null,
        'publications' => [],
        'total_citations' => 0,
        'publication_count' => 0,
        'author_statistics' => []
    ],
    'errors' => []
];

// Cache file - HANYA 1 FILE
$cacheFile = $cachePath . '/' . generateCacheFilename($authorId);
$cacheData = getFromCache($cacheFile);

if ($cacheData) {
    $output['cache_expires'] = date('Y-m-d H:i:s', $cacheData['timestamp'] + $cacheExpiry);
    $output['last_updated'] = $cacheData['last_updated'];
}

// Determine if update needed
$shouldUpdate = false;
$updateReason = '';

if ($forceRefresh) {
    $shouldUpdate = true;
    $updateReason = 'force_refresh';
} elseif (!$cacheData) {
    $shouldUpdate = true;
    $updateReason = 'cache_miss';
} elseif ($smartUpdate) {
    $updateAnalysis = needsUpdate($cacheData, $authorId, $apiKey);
    $apiCalls += 3; // Smart detection API calls
    
    if ($updateAnalysis['should_update']) {
        $shouldUpdate = true;
        $updateReason = $updateAnalysis['reason'];
    }
}

// Use cache if no update needed
if (!$shouldUpdate && $cacheData) {
    $output['cache_status'] = 'hit';
    $output['data_source'] = 'cache';
    $output['data'] = $cacheData['data'];
    $output['smart_detection']['analysis'] = [
        'should_update' => false,
        'reason' => 'cache_valid'
    ];
    $output['performance'] = [
        'processing_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
        'api_calls' => $apiCalls
    ];
    
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}

// Fetch fresh data from API
$output['cache_status'] = $cacheData ? 'expired' : 'miss';
$output['data_source'] = 'api';
$output['update_reason'] = $updateReason;

// Generate current data hash
$currentDataHash = generateDataHash($authorId, $apiKey);
$apiCalls += 3;

// Fetch publications
$allPublications = [];
$publicationsByYear = [];
$citationsByYear = [];
$start = 0;
$maxResults = 200;

do {
    $docsUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId)&start=$start&count=$count&field=eid,dc:title,prism:coverDate,citedby-count,prism:doi,prism:publicationName,author,dc:creator,prism:aggregationType,openaccessFlag,prism:volume,prism:issueIdentifier,prism:pageRange,subtypeDescription&sort=coverDate";
    $docsData = callScopusApi($docsUrl, $apiKey);
    $apiCalls++;

    if (isset($docsData['error'])) {
        $output['errors']['publications'] = $docsData['error'];
        break;
    }

    if (empty($docsData['search-results']['entry'])) break;

    foreach ($docsData['search-results']['entry'] as $doc) {
        $authors = processAuthors($doc);
        $year = isset($doc['prism:coverDate']) ? substr($doc['prism:coverDate'], 0, 4) : 'N/A';
        $citationCount = (int)($doc['citedby-count'] ?? 0);
        
        // Statistics
        if ($year !== 'N/A') {
            $publicationsByYear[$year] = ($publicationsByYear[$year] ?? 0) + 1;
            $citationsByYear[$year] = ($citationsByYear[$year] ?? 0) + $citationCount;
        }

        $publication = [
            'eid' => $doc['eid'] ?? 'N/A',
            'title' => $doc['dc:title'] ?? 'N/A',
            'year' => $year,
            'citation_count' => $citationCount,
            'doi' => $doc['prism:doi'] ?? 'N/A',
            'source' => $doc['prism:publicationName'] ?? 'N/A',
            'authors' => $authors,
            'authors_string' => !empty($authors) ? implode('; ', array_column($authors, 'display_name')) : 'N/A',
            'abstract' => $doc['dc:description'] ?? 'N/A',
            'publication_type' => $doc['prism:aggregationType'] ?? 'N/A',
            'subtype' => $doc['subtypeDescription'] ?? 'N/A',
            'open_access' => ($doc['openaccessFlag'] ?? '0') === '1',
            'volume' => $doc['prism:volume'] ?? 'N/A',
            'issue' => $doc['prism:issueIdentifier'] ?? 'N/A',
            'pages' => $doc['prism:pageRange'] ?? 'N/A',
            'publication_date' => $doc['prism:coverDate'] ?? 'N/A'
        ];
        
        $allPublications[] = $publication;
    }

    $start += $count;
} while ($start < $maxResults && count($allPublications) < $maxResults);

// Calculate statistics
$totalCitations = array_sum(array_column($allPublications, 'citation_count'));
$avgCitations = count($allPublications) > 0 ? round($totalCitations / count($allPublications), 2) : 0;

ksort($publicationsByYear);
ksort($citationsByYear);

// Fetch author profile
$authorUrl = "https://api.elsevier.com/content/author/author_id/$authorId";
$authorData = callScopusApi($authorUrl, $apiKey);
$apiCalls++;

$authorProfile = null;
if (!isset($authorData['error']) && isset($authorData['author-retrieval-response'][0])) {
    $profile = $authorData['author-retrieval-response'][0];
    
    $givenName = $profile['author-profile']['preferred-name']['given-name'] ?? '';
    $surname = $profile['author-profile']['preferred-name']['surname'] ?? '';
    $indexedName = $profile['author-profile']['preferred-name']['indexed-name'] ?? '';
    
    $nameData = parseAuthorName($givenName, $surname, $indexedName);
    
    $authorProfile = [
        'first_name' => $nameData['first_name'],
        'middle_name' => $nameData['middle_name'],
        'last_name' => $nameData['last_name'],
        'full_name' => $nameData['full_name'],
        'display_name' => $nameData['display_name'],
        'indexed_name' => $indexedName,
        'affiliation' => $profile['author-profile']['affiliation-current']['affiliation']['organization'] ?? 'N/A',
        'h_index' => (int)($profile['h-index'] ?? 0),
        'document_count' => (int)($profile['document-count'] ?? 0),
        'citation_count' => (int)($profile['citation-count'] ?? $totalCitations),
        'orcid' => $profile['coredata']['orcid'] ?? null,
        'scopus_id' => $authorId
    ];
} else {
    // Fallback from publications
    foreach ($allPublications as $pub) {
        foreach ($pub['authors'] as $author) {
            if (isset($author['authid']) && $author['authid'] == $authorId) {
                $authorProfile = [
                    'first_name' => $author['first_name'],
                    'middle_name' => $author['middle_name'],
                    'last_name' => $author['last_name'],
                    'full_name' => $author['full_name'],
                    'display_name' => $author['display_name'],
                    'affiliation' => $author['affiliation'] ?? 'N/A',
                    'h_index' => 0,
                    'document_count' => count($allPublications),
                    'citation_count' => $totalCitations,
                    'scopus_id' => $authorId
                ];
                break 2;
            }
        }
    }
}

// Set output data
$output['data'] = [
    'author' => $authorProfile,
    'publications' => $allPublications,
    'total_citations' => $totalCitations,
    'publication_count' => count($allPublications),
    'author_statistics' => [
        'publications_by_year' => $publicationsByYear,
        'citations_by_year' => $citationsByYear,
        'avg_citations_per_paper' => $avgCitations,
        'most_cited_paper' => !empty($allPublications) ? 
            array_reduce($allPublications, function($max, $current) {
                return ($current['citation_count'] > ($max['citation_count'] ?? 0)) ? $current : $max;
            }) : null
    ]
];

// Save to cache dengan data hash
$cacheDataToSave = $output['data'];
$cacheDataToSave['data_hash'] = $currentDataHash;

if (saveToCache($cacheFile, $cacheDataToSave)) {
    $output['cache_expires'] = date('Y-m-d H:i:s', time() + $cacheExpiry);
    $output['cache_saved'] = true;
} else {
    $output['errors']['cache'] = 'Failed to save cache';
    $output['cache_saved'] = false;
}

// Performance metrics
$output['performance'] = [
    'processing_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
    'api_calls' => $apiCalls
];

// Smart Detection summary
$output['smart_detection']['summary'] = [
    'data_hash' => substr($currentDataHash, 0, 8),
    'cache_files_used' => 1,
    'principle' => 'MODERN = SIMPLICITY'
];

echo json_encode($output, JSON_PRETTY_PRINT);
?>
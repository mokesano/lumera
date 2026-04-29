<?php
/**
 * scopus_editor_final.php - Clean & Optimized Version
 * Mencari artikel penulis dengan Scopus API dan OpenAlex fallback
 * 
 * Features:
 * - Citing documents count from author profile
 * - OpenAlex fallback untuk authors dan abstracts
 * - Smart author identification
 * - Optimized performance
 * - Clean single authors format
 * 
 * @author Rochmady and Wizdam Team
 * @version 6.0 - Final Clean Version
 * @date 2025-06-13
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ===== CONFIGURATION =====
$apiKey = "73e21cba2e777a3093e24a781e0ee1a9";
$authorId = isset($_GET['authorid']) ? $_GET['authorid'] : null;
$count = isset($_GET['count']) ? min((int)$_GET['count'], 25) : 10;
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

// Cache configuration
$cachePath = __DIR__ . '/cache';
$cacheExpiry = 2592000; // 1 month

if (!$authorId) {
    echo json_encode(array('error' => 'Parameter authorid diperlukan'), JSON_PRETTY_PRINT);
    exit;
}

// ===== UTILITY FUNCTIONS =====

function callScopusApi($url, $apiKey, $maxRetries = 3) {
    $retryCount = 0;
    
    while ($retryCount < $maxRetries) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => array(
                "X-ELS-APIKey: $apiKey",
                "Accept: application/json"
            ),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ));
        
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
            sleep(pow(2, $retryCount));
        }
        
        $retryCount++;
        if ($retryCount < $maxRetries) {
            usleep(500000);
        }
    }
    
    return array('error' => 'API call failed after ' . $maxRetries . ' retries');
}

function callOpenAlexApi($url) {
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => array(
            "User-Agent: ScopusEditor/6.0 (https://example.com; mailto:contact@example.com)",
            "Accept: application/json"
        ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_FOLLOWLOCATION => true
    ));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode >= 200 && $httpCode < 300 && $response) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $data;
        }
    }
    
    return array('error' => 'OpenAlex API call failed: HTTP ' . $httpCode);
}

// ===== CACHE FUNCTIONS =====

function generateCacheFilename($authorId) {
    $cacheCode = substr(md5($authorId), 0, 8);
    return 'author_' . $cacheCode . '_' . $authorId . '.json.gz';
}

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

function saveToCache($cacheFile, $data) {
    if (!is_dir(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0755, true);
    }
    
    $cacheObj = array(
        'timestamp' => time(),
        'last_updated' => date('Y-m-d H:i:s'),
        'cache_version' => '6.0',
        'data' => $data
    );
    
    try {
        $jsonData = json_encode($cacheObj);
        file_put_contents($cacheFile, gzencode($jsonData, 9));
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// ===== NAME PARSING =====

function parseAuthorName($author) {
    $givenName = isset($author['ce:given-name']) ? trim($author['ce:given-name']) : '';
    $surname = isset($author['ce:surname']) ? trim($author['ce:surname']) : '';
    $indexedName = isset($author['ce:indexed-name']) ? trim($author['ce:indexed-name']) : '';
    
    $firstName = '';
    $middleName = '';
    $lastName = '';
    $fullName = '';
    
    if (!empty($indexedName)) {
        if (strpos($indexedName, ',') !== false) {
            $parts = array_map('trim', explode(',', $indexedName, 2));
            $lastName = $parts[0];
            $givenPart = isset($parts[1]) ? $parts[1] : '';
            
            if (!empty($givenPart)) {
                $givenParts = array_filter(explode(' ', $givenPart));
                $firstName = isset($givenParts[0]) ? $givenParts[0] : '';
                $middleName = count($givenParts) > 1 ? implode(' ', array_slice($givenParts, 1)) : '';
            }
        } else {
            $nameParts = array_filter(explode(' ', $indexedName));
            if (count($nameParts) >= 2) {
                $firstName = $nameParts[0];
                $lastName = end($nameParts);
                if (count($nameParts) > 2) {
                    $middleName = implode(' ', array_slice($nameParts, 1, -1));
                }
            } else {
                $firstName = $indexedName;
            }
        }
        $fullName = $indexedName;
    }
    
    if (empty($fullName) && (!empty($givenName) || !empty($surname))) {
        $firstName = $givenName;
        $lastName = $surname;
        
        if (!empty($givenName)) {
            $givenParts = array_filter(explode(' ', $givenName));
            if (count($givenParts) > 1) {
                $firstName = $givenParts[0];
                $middleName = implode(' ', array_slice($givenParts, 1));
            }
        }
        
        $fullName = trim($firstName . ($middleName ? " $middleName" : "") . ($lastName ? " $lastName" : ""));
    }
    
    if (empty($fullName)) {
        $fullName = 'Unknown Author';
        $firstName = 'Unknown';
    }
    
    return array(
        'first_name' => $firstName,
        'middle_name' => $middleName,
        'last_name' => $lastName,
        'full_name' => $fullName,
        'display_name' => $fullName
    );
}

// ===== OPENALEX FUNCTIONS =====

function getOpenAlexWork($doi, $title, $year = null) {
    if (!empty($doi) && $doi !== 'N/A') {
        $cleanDoi = str_replace('https://doi.org/', '', $doi);
        $url = "https://api.openalex.org/works/https://doi.org/" . urlencode($cleanDoi);
        $data = callOpenAlexApi($url);
        
        if (!isset($data['error'])) {
            return $data;
        }
    }
    
    if (!empty($title) && $title !== 'N/A') {
        $searchTitle = urlencode(trim($title));
        $url = "https://api.openalex.org/works?search=" . $searchTitle;
        
        if ($year && $year !== 'N/A') {
            $url .= "&filter=publication_year:" . $year;
        }
        
        $url .= "&per-page=3";
        
        $data = callOpenAlexApi($url);
        
        if (!isset($data['error']) && isset($data['results']) && !empty($data['results'])) {
            foreach ($data['results'] as $work) {
                if (isset($work['title'])) {
                    $similarity = calculateTitleSimilarity($title, $work['title']);
                    if ($similarity > 0.7) {
                        return $work;
                    }
                }
            }
            return $data['results'][0];
        }
    }
    
    return null;
}

function calculateTitleSimilarity($title1, $title2) {
    $title1 = strtolower(preg_replace('/[^\w\s]/', '', $title1));
    $title2 = strtolower(preg_replace('/[^\w\s]/', '', $title2));
    
    $words1 = array_filter(explode(' ', $title1));
    $words2 = array_filter(explode(' ', $title2));
    
    if (empty($words1) || empty($words2)) {
        return 0;
    }
    
    $intersection = count(array_intersect($words1, $words2));
    $union = count(array_unique(array_merge($words1, $words2)));
    
    return $union > 0 ? $intersection / $union : 0;
}

function extractOpenAlexAuthors($openalexWork) {
    $authors = array();
    
    if (!isset($openalexWork['authorships']) || !is_array($openalexWork['authorships'])) {
        return $authors;
    }
    
    foreach ($openalexWork['authorships'] as $authorship) {
        $author = isset($authorship['author']) ? $authorship['author'] : array();
        $institutions = isset($authorship['institutions']) ? $authorship['institutions'] : array();
        
        $displayName = isset($author['display_name']) ? $author['display_name'] : 'Unknown Author';
        
        $nameParts = explode(' ', $displayName);
        $firstName = isset($nameParts[0]) ? $nameParts[0] : '';
        $lastName = count($nameParts) > 1 ? end($nameParts) : '';
        $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';
        
        $affiliation = 'N/A';
        $affiliationCountry = '';
        if (!empty($institutions)) {
            $mainInst = $institutions[0];
            $affiliation = isset($mainInst['display_name']) ? $mainInst['display_name'] : 'N/A';
            $affiliationCountry = isset($mainInst['country_code']) ? $mainInst['country_code'] : '';
        }
        
        $authors[] = array(
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'full_name' => $displayName,
            'display_name' => $displayName,
            'orcid' => isset($author['orcid']) ? $author['orcid'] : null,
            'affiliation' => $affiliation,
            'affiliation_country' => $affiliationCountry,
            'data_source' => 'openalex'
        );
    }
    
    return $authors;
}

function reconstructAbstract($invertedIndex) {
    if (!is_array($invertedIndex) || empty($invertedIndex)) {
        return null;
    }
    
    $words = array();
    foreach ($invertedIndex as $word => $positions) {
        if (is_array($positions)) {
            foreach ($positions as $position) {
                $words[$position] = $word;
            }
        }
    }
    
    ksort($words);
    $abstract = implode(' ', $words);
    $abstract = preg_replace('/\s+/', ' ', trim($abstract));
    
    return !empty($abstract) ? $abstract : null;
}

// ===== MAIN PROCESSING FUNCTIONS =====

function processAuthors($doc, $doi, $title, $year) {
    $authors = array();
    
    if (isset($doc['author']) && is_array($doc['author']) && count($doc['author']) > 0) {
        foreach ($doc['author'] as $authorData) {
            $nameData = parseAuthorName($authorData);
            
            $authors[] = array(
                'first_name' => $nameData['first_name'],
                'middle_name' => $nameData['middle_name'],
                'last_name' => $nameData['last_name'],
                'full_name' => $nameData['full_name'],
                'display_name' => $nameData['display_name'],
                'orcid' => null,
                'affiliation' => isset($authorData['affiliation']) ? $authorData['affiliation'] : 'N/A',
                'affiliation_country' => '',
                'data_source' => 'scopus'
            );
        }
    }
    
    if (count($authors) <= 1 && (empty($authors) || $authors[0]['affiliation'] === 'N/A')) {
        $openalexWork = getOpenAlexWork($doi, $title, $year);
        if ($openalexWork) {
            $openalxAuthors = extractOpenAlexAuthors($openalexWork);
            if (count($openalxAuthors) > count($authors)) {
                $authors = $openalxAuthors;
            }
        }
    }
    
    if (empty($authors) && isset($doc['dc:creator'])) {
        $creatorNames = array();
        if (is_string($doc['dc:creator'])) {
            if (strpos($doc['dc:creator'], ';') !== false) {
                $creatorNames = array_filter(array_map('trim', explode(';', $doc['dc:creator'])));
            } elseif (strpos($doc['dc:creator'], ',') !== false) {
                $creatorNames = array_filter(array_map('trim', explode(',', $doc['dc:creator'])));
            } else {
                $creatorNames = array($doc['dc:creator']);
            }
        }
        
        foreach ($creatorNames as $creatorName) {
            $nameData = parseAuthorName(array('ce:indexed-name' => $creatorName));
            
            $authors[] = array(
                'first_name' => $nameData['first_name'],
                'middle_name' => $nameData['middle_name'],
                'last_name' => $nameData['last_name'],
                'full_name' => $nameData['full_name'],
                'display_name' => $nameData['display_name'],
                'orcid' => null,
                'affiliation' => 'N/A',
                'affiliation_country' => '',
                'data_source' => 'dc_creator'
            );
        }
    }
    
    return $authors;
}

function fetchAbstract($eid, $basicAbstract, $doi, $title, $year, $apiKey) {
    if (!empty($basicAbstract) && trim($basicAbstract) !== '' && $basicAbstract !== 'N/A' && strlen($basicAbstract) > 50) {
        return array(
            'abstract' => trim($basicAbstract),
            'source' => 'scopus_search'
        );
    }
    
    $openalexWork = getOpenAlexWork($doi, $title, $year);
    if ($openalexWork && isset($openalexWork['abstract_inverted_index'])) {
        $abstract = reconstructAbstract($openalexWork['abstract_inverted_index']);
        if (!empty($abstract) && strlen($abstract) > 50) {
            return array(
                'abstract' => $abstract,
                'source' => 'openalex'
            );
        }
    }
    
    return array(
        'abstract' => 'N/A',
        'source' => 'not_found'
    );
}

function getAuthorProfile($authorId, $apiKey) {
    // First try: Get author profile with all fields
    $authorUrl = "https://api.elsevier.com/content/author/author_id/$authorId";
    $authorData = callScopusApi($authorUrl, $apiKey);

    if (!isset($authorData['error']) && isset($authorData['author-retrieval-response'])) {
        $authorResponse = $authorData['author-retrieval-response'];
        $authorInfo = is_array($authorResponse) && isset($authorResponse[0]) ? $authorResponse[0] : $authorResponse;
        
        if (isset($authorInfo['author-profile']['preferred-name'])) {
            $preferredName = $authorInfo['author-profile']['preferred-name'];
            $nameData = parseAuthorName(array(
                'ce:given-name' => isset($preferredName['given-name']) ? $preferredName['given-name'] : '',
                'ce:surname' => isset($preferredName['surname']) ? $preferredName['surname'] : '',
                'ce:indexed-name' => isset($preferredName['indexed-name']) ? $preferredName['indexed-name'] : ''
            ));
            
            $affiliation = 'N/A';
            $affiliationCity = '';
            $affiliationCountry = '';
            
            if (isset($authorInfo['author-profile']['affiliation-current']['affiliation'])) {
                $affData = $authorInfo['author-profile']['affiliation-current']['affiliation'];
                if (is_array($affData)) {
                    $affiliation = isset($affData['organization']) ? $affData['organization'] : 'N/A';
                    if (isset($affData['address'])) {
                        $affiliationCity = isset($affData['address']['city']) ? $affData['address']['city'] : '';
                        $affiliationCountry = isset($affData['address']['country']) ? $affData['address']['country'] : '';
                    }
                }
            }
            
            // FIXED: Get citing documents count from multiple possible locations
            $citingDocumentsCount = 0;
            
            // Method 1: Try cited-by-count from coredata
            if (isset($authorInfo['coredata']['cited-by-count'])) {
                $citingDocumentsCount = (int)$authorInfo['coredata']['cited-by-count'];
            }
            
            // Method 2: Try from author-profile if coredata is empty
            if ($citingDocumentsCount == 0 && isset($authorInfo['author-profile']['cited-by-count'])) {
                $citingDocumentsCount = (int)$authorInfo['author-profile']['cited-by-count'];
            }
            
            // Method 3: Try from top level
            if ($citingDocumentsCount == 0 && isset($authorInfo['cited-by-count'])) {
                $citingDocumentsCount = (int)$authorInfo['cited-by-count'];
            }
            
            return array(
                'author_id' => $authorId,
                'first_name' => $nameData['first_name'],
                'middle_name' => $nameData['middle_name'],
                'last_name' => $nameData['last_name'],
                'full_name' => $nameData['full_name'],
                'display_name' => $nameData['display_name'],
                'affiliation' => $affiliation,
                'affiliation_city' => $affiliationCity,
                'affiliation_country' => $affiliationCountry,
                'h_index' => isset($authorInfo['h-index']) ? (int)$authorInfo['h-index'] : 0,
                'document_count' => isset($authorInfo['coredata']['document-count']) ? (int)$authorInfo['coredata']['document-count'] : 0,
                'citation_count' => isset($authorInfo['coredata']['citation-count']) ? (int)$authorInfo['coredata']['citation-count'] : 0,
                'citing_documents_count' => $citingDocumentsCount,
                'orcid' => isset($authorInfo['coredata']['orcid']) ? $authorInfo['coredata']['orcid'] : null,
                'scopus_id' => $authorId,
                'data_source' => 'scopus_author_api',
                'debug_citing_sources' => array(
                    'coredata_cited_by_count' => isset($authorInfo['coredata']['cited-by-count']) ? $authorInfo['coredata']['cited-by-count'] : 'not_found',
                    'profile_cited_by_count' => isset($authorInfo['author-profile']['cited-by-count']) ? $authorInfo['author-profile']['cited-by-count'] : 'not_found',
                    'top_level_cited_by_count' => isset($authorInfo['cited-by-count']) ? $authorInfo['cited-by-count'] : 'not_found'
                )
            );
        }
    }
    
    return null;
}

function identifyTargetAuthor($publications) {
    $authorNameCounts = array();
    
    foreach ($publications as $pub) {
        if (!empty($pub['authors'])) {
            foreach ($pub['authors'] as $author) {
                $name = $author['display_name'];
                if (!isset($authorNameCounts[$name])) {
                    $authorNameCounts[$name] = array(
                        'count' => 0,
                        'author_data' => $author
                    );
                }
                $authorNameCounts[$name]['count']++;
            }
        }
    }
    
    $totalPubs = count($publications);
    $threshold = max(1, $totalPubs * 0.7);
    
    $targetAuthor = null;
    $maxCount = 0;
    
    foreach ($authorNameCounts as $name => $data) {
        if ($data['count'] >= $threshold && $data['count'] > $maxCount) {
            $maxCount = $data['count'];
            $targetAuthor = $data['author_data'];
            $targetAuthor['identified_by'] = 'recurring_name_analysis';
            $targetAuthor['appears_in_publications'] = $data['count'];
            $targetAuthor['total_publications'] = $totalPubs;
        }
    }
    
    return $targetAuthor;
}

// ===== MAIN EXECUTION =====

$startTime = microtime(true);
$apiCalls = 0;

$output = array(
    'author_id' => $authorId,
    'status' => 'success',
    'cache_status' => null,
    'data_source' => null,
    'data' => array(
        'author' => null,
        'publications' => array(),
        'total_citations' => 0,
        'citing_documents_count' => 0,
        'publication_count' => 0,
        'author_statistics' => array()
    ),
    'errors' => array()
);

// Check cache
$cacheFile = $cachePath . '/' . generateCacheFilename($authorId);
$cacheData = getFromCache($cacheFile);

if (!$forceRefresh && $cacheData && (time() - $cacheData['timestamp']) < $cacheExpiry) {
    $output['cache_status'] = 'hit';
    $output['data_source'] = 'cache';
    $output['data'] = $cacheData['data'];
    $output['last_updated'] = $cacheData['last_updated'];
    $output['performance'] = array(
        'processing_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
        'api_calls' => 0
    );
    
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}

// Fetch fresh data
$output['cache_status'] = $cacheData ? 'expired' : 'miss';
$output['data_source'] = 'api';

// Get author profile
$authorProfile = getAuthorProfile($authorId, $apiKey);
$apiCalls++;

// Get publications
$allPublications = array();
$publicationsByYear = array();
$citationsByYear = array();
$abstractSources = array();
$start = 0;
$maxResults = 200;

do {
    $docsUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId)&start=$start&count=$count&field=eid,dc:title,dc:description,prism:coverDate,citedby-count,prism:doi,prism:publicationName,author,prism:aggregationType,openaccessFlag&sort=coverDate";
    $docsData = callScopusApi($docsUrl, $apiKey);
    $apiCalls++;

    if (isset($docsData['error'])) {
        $output['errors']['publications'] = $docsData['error'];
        break;
    }

    if (empty($docsData['search-results']['entry'])) break;

    foreach ($docsData['search-results']['entry'] as $doc) {
        $title = isset($doc['dc:title']) ? $doc['dc:title'] : '';
        $doi = isset($doc['prism:doi']) ? $doc['prism:doi'] : '';
        $year = isset($doc['prism:coverDate']) ? substr($doc['prism:coverDate'], 0, 4) : 'N/A';
        $citationCount = isset($doc['citedby-count']) ? (int)$doc['citedby-count'] : 0;
        
        $authors = processAuthors($doc, $doi, $title, $year);
        
        $basicAbstract = isset($doc['dc:description']) ? $doc['dc:description'] : '';
        $abstractResult = fetchAbstract($doc['eid'], $basicAbstract, $doi, $title, $year, $apiKey);
        
        if (!isset($abstractSources[$abstractResult['source']])) {
            $abstractSources[$abstractResult['source']] = 0;
        }
        $abstractSources[$abstractResult['source']]++;
        
        if ($year !== 'N/A') {
            $publicationsByYear[$year] = isset($publicationsByYear[$year]) ? $publicationsByYear[$year] + 1 : 1;
            $citationsByYear[$year] = isset($citationsByYear[$year]) ? $citationsByYear[$year] + $citationCount : $citationCount;
        }

        $publication = array(
            'eid' => isset($doc['eid']) ? $doc['eid'] : 'N/A',
            'title' => $title,
            'abstract' => $abstractResult['abstract'],
            'year' => $year,
            'citation_count' => $citationCount,
            'doi' => $doi,
            'source' => isset($doc['prism:publicationName']) ? $doc['prism:publicationName'] : 'N/A',
            'authors' => $authors,
            'authors_string' => !empty($authors) ? implode('; ', array_column($authors, 'display_name')) : 'N/A',
            'all_authors_count' => count($authors),
            'abstract_source' => $abstractResult['source'],
            'authors_data_source' => !empty($authors) ? $authors[0]['data_source'] : 'none',
            'publication_type' => isset($doc['prism:aggregationType']) ? $doc['prism:aggregationType'] : 'N/A',
            'open_access' => isset($doc['openaccessFlag']) && $doc['openaccessFlag'] === '1',
            'publication_date' => isset($doc['prism:coverDate']) ? $doc['prism:coverDate'] : 'N/A'
        );
        
        $allPublications[] = $publication;
    }

    $start += $count;
} while ($start < $maxResults && count($allPublications) < $maxResults);

// Calculate statistics
$totalCitations = array_sum(array_column($allPublications, 'citation_count'));
$avgCitations = count($allPublications) > 0 ? round($totalCitations / count($allPublications), 2) : 0;

// ENHANCED: Get citing documents count with fallback methods
$citingDocumentsCount = 0;

// Method 1: From author profile
if ($authorProfile && isset($authorProfile['citing_documents_count'])) {
    $citingDocumentsCount = $authorProfile['citing_documents_count'];
}

// Method 2: Fallback - manual calculation using REF query if author profile fails
if ($citingDocumentsCount == 0) {
    $citingUrl = "https://api.elsevier.com/content/search/scopus?query=REF(AU-ID($authorId))&count=0";
    $citingData = callScopusApi($citingUrl, $apiKey);
    $apiCalls++;
    
    if (!isset($citingData['error']) && isset($citingData['search-results']['opensearch:totalResults'])) {
        $citingDocumentsCount = (int)$citingData['search-results']['opensearch:totalResults'];
    }
}

$documentsWithCitations = 0;
$openAccessCount = 0;
$publicationTypes = array();
$authorDataSources = array();

foreach ($allPublications as $pub) {
    if ($pub['citation_count'] > 0) {
        $documentsWithCitations++;
    }
    
    if ($pub['open_access']) {
        $openAccessCount++;
    }
    
    $type = $pub['publication_type'];
    $publicationTypes[$type] = isset($publicationTypes[$type]) ? $publicationTypes[$type] + 1 : 1;
    
    $authorSource = $pub['authors_data_source'];
    $authorDataSources[$authorSource] = isset($authorDataSources[$authorSource]) ? $authorDataSources[$authorSource] + 1 : 1;
}

// Calculate h-index
$hIndexCalculated = 0;
if (!empty($allPublications)) {
    $citations = array_column($allPublications, 'citation_count');
    rsort($citations);
    for ($i = 0; $i < count($citations); $i++) {
        if ($citations[$i] >= ($i + 1)) {
            $hIndexCalculated = $i + 1;
        } else {
            break;
        }
    }
}

ksort($publicationsByYear);
ksort($citationsByYear);

// Update author profile
if ($authorProfile) {
    if ($authorProfile['h_index'] == 0 && $hIndexCalculated > 0) {
        $authorProfile['h_index'] = $hIndexCalculated;
    }
    
    if ($authorProfile['document_count'] == 0) {
        $authorProfile['document_count'] = count($allPublications);
    }
    
    if ($authorProfile['citation_count'] == 0) {
        $authorProfile['citation_count'] = $totalCitations;
    }
    
    $authorProfile['documents_cited'] = $documentsWithCitations;
    $authorProfile['documents_not_cited'] = count($allPublications) - $documentsWithCitations;
    $authorProfile['citations_per_document'] = $avgCitations;
    $authorProfile['open_access_percentage'] = count($allPublications) > 0 ? round(($openAccessCount / count($allPublications)) * 100, 1) : 0;
}

// Smart identification
if (!$authorProfile || empty($authorProfile['full_name']) || $authorProfile['full_name'] === 'Unknown Author') {
    $smartIdentified = identifyTargetAuthor($allPublications);
    
    if ($smartIdentified) {
        $authorProfile = array(
            'author_id' => $authorId,
            'first_name' => $smartIdentified['first_name'],
            'middle_name' => $smartIdentified['middle_name'],
            'last_name' => $smartIdentified['last_name'],
            'display_name' => $smartIdentified['display_name'],
            'affiliation' => $smartIdentified['affiliation'],
            'affiliation_city' => '',
            'affiliation_country' => isset($smartIdentified['affiliation_country']) ? $smartIdentified['affiliation_country'] : '',
            'h_index' => $hIndexCalculated,
            'document_count' => count($allPublications),
            'citation_count' => $totalCitations,
            'citing_documents_count' => $citingDocumentsCount,
            'documents_cited' => $documentsWithCitations,
            'documents_not_cited' => count($allPublications) - $documentsWithCitations,
            'citations_per_document' => $avgCitations,
            'open_access_percentage' => count($allPublications) > 0 ? round(($openAccessCount / count($allPublications)) * 100, 1) : 0,
            'orcid' => isset($smartIdentified['orcid']) ? $smartIdentified['orcid'] : null,
            'scopus_id' => $authorId,
            'data_source' => 'smart_identification',
            'identified_by' => $smartIdentified['identified_by'],
            'appears_in_publications' => $smartIdentified['appears_in_publications']
        );
    }
}

// Final fallback
if (!$authorProfile) {
    $authorProfile = array(
        'author_id' => $authorId,
        'first_name' => 'Unknown',
        'middle_name' => '',
        'last_name' => 'Author',
        'full_name' => 'Author ID: ' . $authorId,
        'display_name' => 'Scopus Author ID ' . $authorId,
        'affiliation' => 'N/A',
        'affiliation_city' => '',
        'affiliation_country' => '',
        'h_index' => $hIndexCalculated,
        'document_count' => count($allPublications),
        'citation_count' => $totalCitations,
        'citing_documents_count' => $citingDocumentsCount,
        'documents_cited' => $documentsWithCitations,
        'documents_not_cited' => count($allPublications) - $documentsWithCitations,
        'citations_per_document' => $avgCitations,
        'open_access_percentage' => count($allPublications) > 0 ? round(($openAccessCount / count($allPublications)) * 100, 1) : 0,
        'orcid' => null,
        'scopus_id' => $authorId,
        'data_source' => 'fallback_generated'
    );
}

// Find most cited paper
$mostCitedPaper = null;
if (!empty($allPublications)) {
    $mostCitedPaper = $allPublications[0];
    foreach ($allPublications as $pub) {
        if ($pub['citation_count'] > $mostCitedPaper['citation_count']) {
            $mostCitedPaper = $pub;
        }
    }
}

// Set output data
$output['data'] = array(
    'author' => $authorProfile,
    'publications' => $allPublications,
    'total_citations' => $totalCitations,
    'citing_documents_count' => $citingDocumentsCount,
    'publication_count' => count($allPublications),
    'author_statistics' => array(
        'publications_by_year' => $publicationsByYear,
        'citations_by_year' => $citationsByYear,
        'avg_citations_per_paper' => $avgCitations,
        'most_cited_paper' => $mostCitedPaper,
        'documents_with_citations' => $documentsWithCitations,
        'documents_without_citations' => count($allPublications) - $documentsWithCitations,
        'open_access_count' => $openAccessCount,
        'open_access_percentage' => count($allPublications) > 0 ? round(($openAccessCount / count($allPublications)) * 100, 1) : 0,
        'publication_types' => $publicationTypes,
        'abstract_sources' => $abstractSources,
        'author_data_sources' => $authorDataSources,
        'h_index_calculated' => $hIndexCalculated,
        'citing_documents_count' => $citingDocumentsCount,
        'total_citations_received' => $totalCitations
    )
);

// Save to cache
$cacheDataToSave = $output['data'];
if (saveToCache($cacheFile, $cacheDataToSave)) {
    $output['cache_expires'] = date('Y-m-d H:i:s', time() + $cacheExpiry);
    $output['cache_saved'] = true;
} else {
    $output['errors']['cache'] = 'Failed to save cache';
    $output['cache_saved'] = false;
}

// Performance metrics
$output['performance'] = array(
    'processing_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
    'api_calls' => $apiCalls,
    'optimizations_applied' => array(
        'reduced_scopus_fields',
        'citing_count_from_author_profile',
        'conditional_openalex_fallback',
        'optimized_abstract_fetching'
    )
);

echo json_encode($output, JSON_PRETTY_PRINT);
?>
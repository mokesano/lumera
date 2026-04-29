<?php
/**
 * UserScopus_v32.php - Fixed Version
 * Memperbaiki masalah parsing data penulis dari API Scopus
 * 
 * Features:
 * - Fixed author parsing from Scopus API response
 * - Better fallback mechanisms for author data
 * - Improved affiliation extraction
 * - Enhanced error handling for author data
 * 
 * @author Rochmady and Wizdam Team
 * @version 6.1 - Fixed Author Parsing
 * @date 2025-06-27
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// ===== CONFIGURATION =====
$apiKey = "73e21cba2e777a3093e24a781e0ee1a9";
$authorId = isset($_GET['authorid']) ? $_GET['authorid'] : null;
$count = isset($_GET['count']) ? min((int)$_GET['count'], 25) : 10;
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

// Cache configuration - No time-based expiry, only hash-based updates
$cachePath = __DIR__ . '/cache';

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
            "User-Agent: ScopusEditor/6.1 (https://example.com; mailto:contact@example.com)",
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

// ===== SMART CACHE FUNCTIONS WITH HASH DETECTION =====

function generateCacheFilename($authorId) {
    $cacheCode = substr(md5($authorId), 0, 8);
    return 'author_' . $cacheCode . '_' . $authorId . '.json.gz';
}

function generateDataHash($data) {
    // Create hash from essential data that indicates real changes
    $hashData = array(
        'publication_count' => count($data['publications']),
        'total_citations' => $data['total_citations'],
        'latest_publication_date' => '',
        'publication_titles_hash' => ''
    );
    
    // Get latest publication date and create titles hash
    $titles = array();
    $latestDate = '';
    foreach ($data['publications'] as $pub) {
        $titles[] = $pub['title'];
        if ($pub['publication_date'] > $latestDate) {
            $latestDate = $pub['publication_date'];
        }
    }
    
    $hashData['latest_publication_date'] = $latestDate;
    $hashData['publication_titles_hash'] = md5(implode('|', $titles));
    
    return md5(json_encode($hashData));
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
    
    $dataHash = generateDataHash($data);
    
    $cacheObj = array(
        'timestamp' => time(),
        'last_updated' => date('Y-m-d H:i:s'),
        'cache_version' => '6.2',
        'data_hash' => $dataHash,
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

function shouldUpdateCache($cacheData, $newData) {
    if (!$cacheData) {
        return array('should_update' => true, 'reason' => 'no_cache');
    }
    
    // Smart detection - check if data hash has changed
    $currentHash = isset($cacheData['data_hash']) ? $cacheData['data_hash'] : '';
    $newHash = generateDataHash($newData);
    
    if ($currentHash !== $newHash) {
        return array('should_update' => true, 'reason' => 'data_changed', 'old_hash' => $currentHash, 'new_hash' => $newHash);
    }
    
    return array('should_update' => false, 'reason' => 'no_changes', 'hash' => $currentHash);
}

// ===== IMPROVED NAME PARSING =====

function parseAuthorName($author) {
    $givenName = '';
    $surname = '';
    $indexedName = '';
    
    // Extract names from different possible fields
    if (isset($author['ce:given-name'])) {
        $givenName = trim($author['ce:given-name']);
    }
    
    if (isset($author['ce:surname'])) {
        $surname = trim($author['ce:surname']);
    }
    
    if (isset($author['ce:indexed-name'])) {
        $indexedName = trim($author['ce:indexed-name']);
    }
    
    // Fallback to other possible field names
    if (empty($givenName) && isset($author['given-name'])) {
        $givenName = trim($author['given-name']);
    }
    
    if (empty($surname) && isset($author['surname'])) {
        $surname = trim($author['surname']);
    }
    
    if (empty($indexedName) && isset($author['indexed-name'])) {
        $indexedName = trim($author['indexed-name']);
    }
    
    // Initialize variables
    $firstName = '';
    $middleName = '';
    $lastName = '';
    $fullName = '';
    
    // Parse indexed name first (most reliable)
    if (!empty($indexedName)) {
        if (strpos($indexedName, ',') !== false) {
            // Format: "Surname, Given Names"
            $parts = array_map('trim', explode(',', $indexedName, 2));
            $lastName = $parts[0];
            $givenPart = isset($parts[1]) ? $parts[1] : '';
            
            if (!empty($givenPart)) {
                $givenParts = array_filter(explode(' ', $givenPart));
                $firstName = isset($givenParts[0]) ? $givenParts[0] : '';
                $middleName = count($givenParts) > 1 ? implode(' ', array_slice($givenParts, 1)) : '';
            }
        } else {
            // Format: "Given Names Surname" or single name
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
    
    // If indexed name parsing didn't work, use given-name and surname
    if (empty($fullName) && (!empty($givenName) || !empty($surname))) {
        $firstName = $givenName;
        $lastName = $surname;
        
        // Split given name if it contains multiple parts
        if (!empty($givenName)) {
            $givenParts = array_filter(explode(' ', $givenName));
            if (count($givenParts) > 1) {
                $firstName = $givenParts[0];
                $middleName = implode(' ', array_slice($givenParts, 1));
            }
        }
        
        $fullName = trim($firstName . ($middleName ? " $middleName" : "") . ($lastName ? " $lastName" : ""));
    }
    
    // Final fallback
    if (empty($fullName)) {
        $fullName = 'Unknown Author';
        $firstName = 'Unknown';
        $lastName = 'Author';
    }
    
    return array(
        'first_name' => $firstName,
        'middle_name' => $middleName,
        'last_name' => $lastName,
        'full_name' => $fullName,
        'display_name' => $fullName
    );
}

// ===== IMPROVED AFFILIATION PARSING =====

function parseAffiliation($authorData) {
    $affiliation = '';
    $affiliationCity = '';
    $affiliationCountry = '';
    
    // Try different affiliation field structures
    if (isset($authorData['affiliation'])) {
        $affData = $authorData['affiliation'];
        
        // Handle array of affiliations (take first one)
        if (is_array($affData) && !empty($affData)) {
            $affData = $affData[0];
        }
        
        if (is_array($affData)) {
            // Extract organization name
            if (isset($affData['affilname'])) {
                $affiliation = $affData['affilname'];
            } elseif (isset($affData['organization'])) {
                $affiliation = $affData['organization'];
            } elseif (isset($affData['ce:text'])) {
                $affiliation = $affData['ce:text'];
            }
            
            // Extract city and country
            if (isset($affData['affiliation-city'])) {
                $affiliationCity = $affData['affiliation-city'];
            }
            
            if (isset($affData['affiliation-country'])) {
                $affiliationCountry = $affData['affiliation-country'];
            }
        } elseif (is_string($affData)) {
            $affiliation = $affData;
        }
    }
    
    return array(
        'affiliation' => !empty($affiliation) ? $affiliation : 'N/A',
        'city' => $affiliationCity,
        'country' => $affiliationCountry
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

// ===== ENHANCED OPEN ACCESS DETECTION =====

function detectOpenAccessStatus($doc, $doi, $title, $year, $openalexWork = null) {
    $openAccessStatus = false;
    $openAccessSource = 'not_detected';
    $openAccessType = null;
    
    // Method 1: Check Scopus openaccessFlag
    if (isset($doc['openaccessFlag']) && $doc['openaccessFlag'] === '1') {
        $openAccessStatus = true;
        $openAccessSource = 'scopus_flag';
        $openAccessType = 'scopus_detected';
    }
    
    // Method 2: Check OpenAlex data (more reliable for open access)
    if (!$openAccessStatus && $openalexWork) {
        if (isset($openalexWork['open_access'])) {
            $oaInfo = $openalexWork['open_access'];
            
            if (isset($oaInfo['is_oa']) && $oaInfo['is_oa'] === true) {
                $openAccessStatus = true;
                $openAccessSource = 'openalex';
                $openAccessType = isset($oaInfo['oa_type']) ? $oaInfo['oa_type'] : 'unknown';
            }
        }
    }
    
    // Method 3: Check from detailed publication data if available
    if (!$openAccessStatus && isset($doc['eid'])) {
        $detailedOAStatus = fetchOpenAccessFromEID($doc['eid']);
        if ($detailedOAStatus['is_open_access']) {
            $openAccessStatus = true;
            $openAccessSource = 'scopus_detailed';
            $openAccessType = $detailedOAStatus['type'];
        }
    }
    
    // Method 4: Heuristic detection from DOI patterns (for common OA publishers)
    if (!$openAccessStatus && !empty($doi) && $doi !== 'N/A') {
        $heuristicOA = detectOpenAccessFromDOI($doi);
        if ($heuristicOA['is_open_access']) {
            $openAccessStatus = true;
            $openAccessSource = 'doi_heuristic';
            $openAccessType = $heuristicOA['type'];
        }
    }
    
    return array(
        'is_open_access' => $openAccessStatus,
        'source' => $openAccessSource,
        'type' => $openAccessType
    );
}

function fetchOpenAccessFromEID($eid) {
    global $apiKey;
    
    if (empty($eid)) return array('is_open_access' => false, 'type' => null);
    
    // NO SEPARATE CACHE - akan disimpan di main author cache
    $url = "https://api.elsevier.com/content/abstract/eid/$eid?view=FULL";
    $data = callScopusApi($url, $apiKey);
    
    $result = array('is_open_access' => false, 'type' => null);
    
    if (!isset($data['error']) && isset($data['abstracts-retrieval-response'])) {
        $response = $data['abstracts-retrieval-response'];
        
        // Check various OA indicators in detailed response
        if (isset($response['coredata']['openaccessFlag']) && $response['coredata']['openaccessFlag'] === '1') {
            $result['is_open_access'] = true;
            $result['type'] = 'scopus_detailed_flag';
        }
        
        // Check for OA indicators in item metadata
        if (!$result['is_open_access'] && isset($response['item'])) {
            $item = $response['item'];
            
            // Check for open access in bibrecord
            if (isset($item['bibrecord']['head']['enhancement'])) {
                $enhancement = $item['bibrecord']['head']['enhancement'];
                if (isset($enhancement['classificationgroup']['classifications']['classification'])) {
                    $classifications = $enhancement['classificationgroup']['classifications']['classification'];
                    if (!isset($classifications[0])) {
                        $classifications = array($classifications);
                    }
                    
                    foreach ($classifications as $classification) {
                        if (isset($classification['@type']) && 
                            (strpos(strtolower($classification['@type']), 'open') !== false ||
                             strpos(strtolower($classification['@type']), 'access') !== false)) {
                            $result['is_open_access'] = true;
                            $result['type'] = 'scopus_classification';
                            break;
                        }
                    }
                }
            }
        }
    }
    
    // NO CACHE SAVE - akan disimpan di main author cache
    return $result;
}

function detectOpenAccessFromDOI($doi) {
    $result = array('is_open_access' => false, 'type' => null);
    
    if (empty($doi) || $doi === 'N/A') {
        return $result;
    }
    
    $doi = strtolower($doi);
    
    // Common Open Access publisher patterns
    $oaPatterns = array(
        '10.1371/' => 'plos',           // PLOS journals
        '10.1186/' => 'biomed_central', // BioMed Central
        '10.3389/' => 'frontiers',      // Frontiers
        '10.1038/s41598' => 'nature_scientific_reports', // Scientific Reports
        '10.1038/srep' => 'nature_scientific_reports',   // Scientific Reports (old)
        '10.1371/journal.pone' => 'plos_one', // PLOS ONE
        '10.3390/' => 'mdpi',           // MDPI
        '10.1080/.*open' => 'taylor_francis_open', // Taylor & Francis Open
        '10.1016/j.heliyon' => 'heliyon', // Heliyon (Elsevier OA)
        '10.1038/s41467' => 'nature_communications', // Nature Communications
        '10.7554/' => 'elife',          // eLife
        '10.1371/journal.pcbi' => 'plos_comp_bio', // PLOS Computational Biology
    );
    
    foreach ($oaPatterns as $pattern => $publisher) {
        if (strpos($doi, $pattern) !== false || preg_match('/' . str_replace('/', '\/', $pattern) . '/', $doi)) {
            $result['is_open_access'] = true;
            $result['type'] = 'publisher_' . $publisher;
            break;
        }
    }
    
    return $result;
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

function getOpenAlexWorkWithOA($doi, $title, $year = null) {
    $openalexWork = getOpenAlexWork($doi, $title, $year);
    
    // If we got OpenAlex data, enhance it with OA detection
    if ($openalexWork && !isset($openalexWork['open_access'])) {
        // Sometimes OA data is in different fields
        if (isset($openalexWork['is_oa'])) {
            $openalexWork['open_access'] = array(
                'is_oa' => $openalexWork['is_oa'],
                'oa_type' => isset($openalexWork['oa_type']) ? $openalexWork['oa_type'] : 'unknown'
            );
        }
    }
    
    return $openalexWork;
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

// ===== IMPROVED MULTI-AUTHOR PROCESSING =====

function processAuthors($doc, $doi, $title, $year) {
    $authors = array();
    $scopusAuthors = array();
    $needsOpenAlexSupplement = false;
    
    // Method 1: Extract from author field in Scopus response (PRIORITIZED)
    if (isset($doc['author']) && is_array($doc['author']) && count($doc['author']) > 0) {
        foreach ($doc['author'] as $authorData) {
            $nameData = parseAuthorName($authorData);
            $affiliationData = parseAffiliation($authorData);
            
            // Only add if we get a meaningful name (not "Unknown Author")
            if ($nameData['full_name'] !== 'Unknown Author' && !empty(trim($nameData['full_name']))) {
                $scopusAuthors[] = array(
                    'first_name' => $nameData['first_name'],
                    'middle_name' => $nameData['middle_name'],
                    'last_name' => $nameData['last_name'],
                    'full_name' => $nameData['full_name'],
                    'display_name' => $nameData['display_name'],
                    'orcid' => isset($authorData['orcid']) ? $authorData['orcid'] : null,
                    'affiliation' => $affiliationData['affiliation'],
                    'affiliation_city' => $affiliationData['city'],
                    'affiliation_country' => $affiliationData['country'],
                    'data_source' => 'scopus_author_field'
                );
            }
        }
        
        // Check if we have incomplete author data (missing affiliations or few authors)
        $authorsWithAffiliation = count(array_filter($scopusAuthors, function($author) {
            return $author['affiliation'] !== 'N/A';
        }));
        
        // Consider OpenAlex supplement if:
        // 1. We have very few authors (likely incomplete)
        // 2. Most authors lack affiliation data
        // 3. We have DOI for verification
        if (count($scopusAuthors) <= 2 || 
            ($authorsWithAffiliation / count($scopusAuthors)) < 0.5 || 
            (count($scopusAuthors) > 0 && !empty($doi) && $doi !== 'N/A')) {
            $needsOpenAlexSupplement = true;
        }
        
        $authors = $scopusAuthors;
    }
    
    // Method 2: Extract from dc:creator field (PRIORITY BACKUP)
    if (empty($authors) && isset($doc['dc:creator'])) {
        $creatorNames = array();
        
        if (is_string($doc['dc:creator'])) {
            // Split by common delimiters - improved logic
            if (strpos($doc['dc:creator'], ';') !== false) {
                $creatorNames = array_filter(array_map('trim', explode(';', $doc['dc:creator'])));
            } elseif (strpos($doc['dc:creator'], ',') !== false) {
                // Be more careful with comma splitting
                $parts = array_map('trim', explode(',', $doc['dc:creator']));
                // If we have many parts separated by comma, likely multiple authors
                if (count($parts) > 2) {
                    $creatorNames = $parts;
                } else {
                    // Might be "Surname, Given Name" format
                    $creatorNames = array($doc['dc:creator']);
                }
            } else {
                $creatorNames = array($doc['dc:creator']);
            }
        } elseif (is_array($doc['dc:creator'])) {
            $creatorNames = $doc['dc:creator'];
        }
        
        foreach ($creatorNames as $creatorName) {
            if (!empty(trim($creatorName))) {
                $nameData = parseAuthorName(array('ce:indexed-name' => trim($creatorName)));
                
                // Only add if we get a meaningful name
                if ($nameData['full_name'] !== 'Unknown Author' && !empty(trim($nameData['full_name']))) {
                    $authors[] = array(
                        'first_name' => $nameData['first_name'],
                        'middle_name' => $nameData['middle_name'],
                        'last_name' => $nameData['last_name'],
                        'full_name' => $nameData['full_name'],
                        'display_name' => $nameData['display_name'],
                        'orcid' => null,
                        'affiliation' => 'N/A',
                        'affiliation_city' => '',
                        'affiliation_country' => '',
                        'data_source' => 'scopus_dc_creator'
                    );
                }
            }
        }
        
        // If we got authors from dc:creator, also check if we need OpenAlex supplement
        if (!empty($authors) && (!empty($doi) && $doi !== 'N/A')) {
            $needsOpenAlexSupplement = true;
        }
    }
    
    // Method 3: Detailed publication fetch
    if (empty($authors) && isset($doc['eid'])) {
        $detailedAuthors = fetchDetailedAuthorsFromEID($doc['eid']);
        if (!empty($detailedAuthors)) {
            $authors = $detailedAuthors;
            
            // Check if detailed fetch gave us complete data
            $authorsWithAffiliation = count(array_filter($detailedAuthors, function($author) {
                return $author['affiliation'] !== 'N/A';
            }));
            
            if (($authorsWithAffiliation / count($detailedAuthors)) < 0.7 && !empty($doi) && $doi !== 'N/A') {
                $needsOpenAlexSupplement = true;
            }
        }
    }
    
    // Method 4: OpenAlex supplement/fallback
    if ($needsOpenAlexSupplement || empty($authors)) {
        if ((!empty($doi) && $doi !== 'N/A') || (!empty($title) && $title !== 'N/A')) {
            $openalexWork = getOpenAlexWorkWithOA($doi, $title, $year);
            if ($openalexWork) {
                $openalexAuthors = extractOpenAlexAuthors($openalexWork);
                
                if (!empty($openalexAuthors)) {
                    if (empty($authors)) {
                        // No Scopus authors found, use OpenAlex
                        $authors = $openalexAuthors;
                    } else {
                        // Supplement Scopus data with OpenAlex for better coverage
                        $authors = supplementAuthorsWithOpenAlex($authors, $openalexAuthors);
                    }
                }
            }
        }
    }
    
    // Method 5: Final fallback - ensure we always have author data
    if (empty($authors)) {
        // Try to construct from any available name data in the document
        $fallbackName = 'Unknown Author';
        
        // Look for any name-like fields
        if (isset($doc['dc:creator']) && is_string($doc['dc:creator']) && !empty(trim($doc['dc:creator']))) {
            $fallbackName = trim($doc['dc:creator']);
        }
        
        $nameData = parseAuthorName(array('ce:indexed-name' => $fallbackName));
        
        $authors[] = array(
            'first_name' => $nameData['first_name'],
            'middle_name' => $nameData['middle_name'],
            'last_name' => $nameData['last_name'],
            'full_name' => $nameData['full_name'],
            'display_name' => $nameData['display_name'],
            'orcid' => null,
            'affiliation' => 'N/A',
            'affiliation_city' => '',
            'affiliation_country' => '',
            'data_source' => 'fallback_generated'
        );
    }
    
    // Return authors along with OpenAlex work data for OA detection
    return array(
        'authors' => $authors,
        'openalex_work' => isset($openalexWork) ? $openalexWork : null
    );
}

// ===== AUTHOR SUPPLEMENTATION FUNCTION =====

function supplementAuthorsWithOpenAlex($scopusAuthors, $openalexAuthors) {
    // If OpenAlex has significantly more authors, prefer OpenAlex for completeness
    if (count($openalexAuthors) > count($scopusAuthors) * 1.5) {
        // Add metadata to indicate this was supplemented
        foreach ($openalexAuthors as &$author) {
            $author['supplemented_from'] = 'openalex_complete_list';
        }
        return $openalexAuthors;
    }
    
    // Otherwise, enhance Scopus authors with OpenAlex data
    $enhancedAuthors = $scopusAuthors;
    
    // Try to match and enhance existing authors
    foreach ($enhancedAuthors as &$scopusAuthor) {
        if ($scopusAuthor['affiliation'] === 'N/A' || empty($scopusAuthor['orcid'])) {
            // Try to find matching author in OpenAlex
            foreach ($openalexAuthors as $oaAuthor) {
                if (authorNamesMatch($scopusAuthor['display_name'], $oaAuthor['display_name'])) {
                    // Enhance with OpenAlex data
                    if ($scopusAuthor['affiliation'] === 'N/A' && $oaAuthor['affiliation'] !== 'N/A') {
                        $scopusAuthor['affiliation'] = $oaAuthor['affiliation'];
                        $scopusAuthor['affiliation_country'] = $oaAuthor['affiliation_country'];
                        $scopusAuthor['enhanced_with'] = 'openalex_affiliation';
                    }
                    
                    if (empty($scopusAuthor['orcid']) && !empty($oaAuthor['orcid'])) {
                        $scopusAuthor['orcid'] = $oaAuthor['orcid'];
                        $scopusAuthor['enhanced_with'] = isset($scopusAuthor['enhanced_with']) 
                            ? $scopusAuthor['enhanced_with'] . '+orcid' 
                            : 'openalex_orcid';
                    }
                    break;
                }
            }
        }
    }
    
    // Add any additional authors from OpenAlex that weren't in Scopus
    foreach ($openalexAuthors as $oaAuthor) {
        $found = false;
        foreach ($scopusAuthors as $scopusAuthor) {
            if (authorNamesMatch($scopusAuthor['display_name'], $oaAuthor['display_name'])) {
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            $oaAuthor['data_source'] = 'openalex_additional';
            $enhancedAuthors[] = $oaAuthor;
        }
    }
    
    return $enhancedAuthors;
}

// ===== AUTHOR NAME MATCHING FUNCTION =====

function authorNamesMatch($name1, $name2, $threshold = 0.7) {
    // Normalize names for comparison
    $name1 = strtolower(preg_replace('/[^\w\s]/', '', $name1));
    $name2 = strtolower(preg_replace('/[^\w\s]/', '', $name2));
    
    // Split into words
    $words1 = array_filter(explode(' ', $name1));
    $words2 = array_filter(explode(' ', $name2));
    
    if (empty($words1) || empty($words2)) {
        return false;
    }
    
    // Calculate similarity
    $intersection = count(array_intersect($words1, $words2));
    $union = count(array_unique(array_merge($words1, $words2)));
    
    $similarity = $union > 0 ? $intersection / $union : 0;
    
    // Also check if last names match (common scenario)
    $lastName1 = end($words1);
    $lastName2 = end($words2);
    
    if ($lastName1 === $lastName2 && $similarity > 0.4) {
        return true;
    }
    
    return $similarity >= $threshold;
}

// ===== DETAILED AUTHOR FETCH (Inspired by editor_scopus.php) =====

function fetchDetailedAuthorsFromEID($eid) {
    global $apiKey;
    
    if (empty($eid)) return array();
    
    // NO SEPARATE CACHE - akan disimpan di main author cache
    $url = "https://api.elsevier.com/content/abstract/eid/$eid?view=FULL";
    $data = callScopusApi($url, $apiKey);
    
    if (isset($data['error']) || !isset($data['abstracts-retrieval-response'])) {
        return array();
    }
    
    $authors = array();
    $response = $data['abstracts-retrieval-response'];
    
    if (isset($response['authors']['author'])) {
        $authorsData = $response['authors']['author'];
        
        // Handle single author case
        if (isset($authorsData['@auid'])) {
            $authorsData = array($authorsData);
        }
        
        foreach ($authorsData as $authorData) {
            $givenName = '';
            $surname = '';
            
            // Extract preferred name
            if (isset($authorData['preferred-name'])) {
                $givenName = isset($authorData['preferred-name']['given-name']) ? 
                           trim($authorData['preferred-name']['given-name']) : '';
                $surname = isset($authorData['preferred-name']['surname']) ? 
                         trim($authorData['preferred-name']['surname']) : '';
            }
            
            // Fallback to ce:indexed-name if preferred name not available
            if (empty($givenName) && empty($surname) && isset($authorData['ce:indexed-name'])) {
                $nameData = parseAuthorName(array('ce:indexed-name' => $authorData['ce:indexed-name']));
                $givenName = $nameData['first_name'] . ($nameData['middle_name'] ? ' ' . $nameData['middle_name'] : '');
                $surname = $nameData['last_name'];
            }
            
            $fullName = trim("$givenName $surname");
            
            if (!empty($fullName) && $fullName !== ' ') {
                // Extract affiliation
                $affiliation = 'N/A';
                $affiliationCity = '';
                $affiliationCountry = '';
                
                if (isset($authorData['affiliation']) && is_array($authorData['affiliation'])) {
                    $affData = $authorData['affiliation'];
                    if (isset($affData['organization'])) {
                        $affiliation = $affData['organization'];
                    } elseif (isset($affData[0]['organization'])) {
                        $affiliation = $affData[0]['organization'];
                    }
                    
                    // Extract city and country if available
                    if (isset($affData['city'])) {
                        $affiliationCity = $affData['city'];
                    } elseif (isset($affData[0]['city'])) {
                        $affiliationCity = $affData[0]['city'];
                    }
                    
                    if (isset($affData['country'])) {
                        $affiliationCountry = $affData['country'];
                    } elseif (isset($affData[0]['country'])) {
                        $affiliationCountry = $affData[0]['country'];
                    }
                }
                
                $nameParts = explode(' ', $fullName);
                $firstName = isset($nameParts[0]) ? $nameParts[0] : '';
                $lastName = count($nameParts) > 1 ? end($nameParts) : '';
                $middleName = count($nameParts) > 2 ? implode(' ', array_slice($nameParts, 1, -1)) : '';
                
                $authors[] = array(
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'full_name' => $fullName,
                    'display_name' => $fullName,
                    'orcid' => isset($authorData['orcid']) ? $authorData['orcid'] : null,
                    'affiliation' => $affiliation,
                    'affiliation_city' => $affiliationCity,
                    'affiliation_country' => $affiliationCountry,
                    'data_source' => 'scopus_detailed_fetch'
                );
            }
        }
    }
    
    // NO CACHE SAVE - akan disimpan di main author cache
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
            
            $citingDocumentsCount = 0;
            
            if (isset($authorInfo['coredata']['cited-by-count'])) {
                $citingDocumentsCount = (int)$authorInfo['coredata']['cited-by-count'];
            }
            
            if ($citingDocumentsCount == 0 && isset($authorInfo['author-profile']['cited-by-count'])) {
                $citingDocumentsCount = (int)$authorInfo['author-profile']['cited-by-count'];
            }
            
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
                'data_source' => 'scopus_author_api'
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
                if ($name !== 'Unknown Author' && $name !== 'Author Unknown') {
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
    }
    
    $totalPubs = count($publications);
    $threshold = max(1, $totalPubs * 0.5); // Lowered threshold
    
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

// Check cache with smart detection - no time-based expiry
$cacheFile = $cachePath . '/' . generateCacheFilename($authorId);
$cacheData = getFromCache($cacheFile);

// Use cache if available and force refresh is not requested
if (!$forceRefresh && $cacheData) {
    $output['cache_status'] = 'hit';
    $output['data_source'] = 'cache';
    $output['data'] = $cacheData['data'];
    $output['last_updated'] = $cacheData['last_updated'];
    $output['cache_hash'] = isset($cacheData['data_hash']) ? $cacheData['data_hash'] : 'legacy';
    $output['cache_timestamp'] = date('Y-m-d H:i:s', $cacheData['timestamp']);
    $output['performance'] = array(
        'processing_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
        'api_calls' => 0
    );
    
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}

// Fetch fresh data (cache miss or force refresh)
$output['cache_status'] = $cacheData ? 'force_refresh' : 'miss';
$output['data_source'] = 'api';

// Get author profile
$authorProfile = getAuthorProfile($authorId, $apiKey);
$apiCalls++;

// Get publications with enhanced author field
$allPublications = array();
$publicationsByYear = array();
$citationsByYear = array();
$abstractSources = array();
$start = 0;
$maxResults = 200;

do {
    // Enhanced field selection - include more author-related fields
    $docsUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId)&start=$start&count=$count&field=eid,dc:title,dc:description,dc:creator,prism:coverDate,citedby-count,prism:doi,prism:publicationName,author,prism:aggregationType,subtypeDescription,prism:volume,prism:issueIdentifier,prism:pageRange,openaccessFlag&sort=coverDate";
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
        
        // Enhanced author processing with OpenAlex work data
        $authorResult = processAuthors($doc, $doi, $title, $year);
        $authors = $authorResult['authors'];
        $openalexWork = $authorResult['openalex_work'];
        
        // Enhanced open access detection
        $openAccessInfo = detectOpenAccessStatus($doc, $doi, $title, $year, $openalexWork);
        
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
            'volume' => isset($doc['prism:volume']) ? $doc['prism:volume'] : 'N/A',
            'issue' => isset($doc['prism:issueIdentifier']) ? $doc['prism:issueIdentifier'] : 'N/A',
            'pages' => isset($doc['prism:pageRange']) ? $doc['prism:pageRange'] : 'N/A',
            'authors' => $authors,
            'authors_string' => !empty($authors) ? implode('; ', array_column($authors, 'display_name')) : 'Unknown Authors',
            'all_authors_count' => count($authors),
            'abstract_source' => $abstractResult['source'],
            'authors_data_source' => !empty($authors) ? $authors[0]['data_source'] : 'none',
            'publication_type' => isset($doc['prism:aggregationType']) ? $doc['prism:aggregationType'] : 'N/A',
            'subtype' => isset($doc['subtypeDescription']) ? $doc['subtypeDescription'] : 'N/A',
            'open_access' => $openAccessInfo['is_open_access'],
            'open_access_source' => $openAccessInfo['source'],
            'open_access_type' => $openAccessInfo['type'],
            'publication_date' => isset($doc['prism:coverDate']) ? $doc['prism:coverDate'] : 'N/A'
        );
        
        $allPublications[] = $publication;
    }

    $start += $count;
} while ($start < $maxResults && count($allPublications) < $maxResults);

// Calculate statistics
$totalCitations = array_sum(array_column($allPublications, 'citation_count'));
$avgCitations = count($allPublications) > 0 ? round($totalCitations / count($allPublications), 2) : 0;

// Get citing documents count with fallback methods
$citingDocumentsCount = 0;

if ($authorProfile && isset($authorProfile['citing_documents_count'])) {
    $citingDocumentsCount = $authorProfile['citing_documents_count'];
}

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
$openAccessSources = array();

foreach ($allPublications as $pub) {
    if ($pub['citation_count'] > 0) {
        $documentsWithCitations++;
    }
    
    if ($pub['open_access']) {
        $openAccessCount++;
        
        // Track open access sources
        $oaSource = $pub['open_access_source'];
        $openAccessSources[$oaSource] = isset($openAccessSources[$oaSource]) ? $openAccessSources[$oaSource] + 1 : 1;
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

// Smart identification with improved logic
if (!$authorProfile || empty($authorProfile['full_name']) || $authorProfile['full_name'] === 'Unknown Author') {
    $smartIdentified = identifyTargetAuthor($allPublications);
    
    if ($smartIdentified) {
        $authorProfile = array(
            'author_id' => $authorId,
            'first_name' => $smartIdentified['first_name'],
            'middle_name' => $smartIdentified['middle_name'],
            'last_name' => $smartIdentified['last_name'],
            'full_name' => $smartIdentified['full_name'],
            'display_name' => $smartIdentified['display_name'],
            'affiliation' => $smartIdentified['affiliation'],
            'affiliation_city' => isset($smartIdentified['affiliation_city']) ? $smartIdentified['affiliation_city'] : '',
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

// Enhanced fallback with better author name detection
if (!$authorProfile) {
    // Try to get the most common author name from publications
    $mostCommonAuthor = null;
    if (!empty($allPublications)) {
        $authorNameFreq = array();
        foreach ($allPublications as $pub) {
            if (!empty($pub['authors'])) {
                foreach ($pub['authors'] as $author) {
                    if ($author['full_name'] !== 'Unknown Author' && $author['full_name'] !== 'Author Unknown') {
                        $name = $author['full_name'];
                        if (!isset($authorNameFreq[$name])) {
                            $authorNameFreq[$name] = array('count' => 0, 'author' => $author);
                        }
                        $authorNameFreq[$name]['count']++;
                    }
                }
            }
        }
        
        if (!empty($authorNameFreq)) {
            $maxCount = 0;
            foreach ($authorNameFreq as $name => $data) {
                if ($data['count'] > $maxCount) {
                    $maxCount = $data['count'];
                    $mostCommonAuthor = $data['author'];
                }
            }
        }
    }
    
    if ($mostCommonAuthor) {
        $authorProfile = array(
            'author_id' => $authorId,
            'first_name' => $mostCommonAuthor['first_name'],
            'middle_name' => $mostCommonAuthor['middle_name'],
            'last_name' => $mostCommonAuthor['last_name'],
            'full_name' => $mostCommonAuthor['full_name'],
            'display_name' => $mostCommonAuthor['display_name'],
            'affiliation' => $mostCommonAuthor['affiliation'],
            'affiliation_city' => isset($mostCommonAuthor['affiliation_city']) ? $mostCommonAuthor['affiliation_city'] : '',
            'affiliation_country' => isset($mostCommonAuthor['affiliation_country']) ? $mostCommonAuthor['affiliation_country'] : '',
            'h_index' => $hIndexCalculated,
            'document_count' => count($allPublications),
            'citation_count' => $totalCitations,
            'citing_documents_count' => $citingDocumentsCount,
            'documents_cited' => $documentsWithCitations,
            'documents_not_cited' => count($allPublications) - $documentsWithCitations,
            'citations_per_document' => $avgCitations,
            'open_access_percentage' => count($allPublications) > 0 ? round(($openAccessCount / count($allPublications)) * 100, 1) : 0,
            'orcid' => isset($mostCommonAuthor['orcid']) ? $mostCommonAuthor['orcid'] : null,
            'scopus_id' => $authorId,
            'data_source' => 'common_author_extraction'
        );
    } else {
        // Final fallback
        $authorProfile = array(
            'author_id' => $authorId,
            'first_name' => 'Author',
            'middle_name' => '',
            'last_name' => 'ID ' . $authorId,
            'full_name' => 'Scopus Author ID ' . $authorId,
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
        'open_access_sources' => $openAccessSources,
        'publication_types' => $publicationTypes,
        'abstract_sources' => $abstractSources,
        'author_data_sources' => $authorDataSources,
        'h_index_calculated' => $hIndexCalculated,
        'citing_documents_count' => $citingDocumentsCount,
        'total_citations_received' => $totalCitations
    )
);

// Add debugging information for author parsing
$output['debug'] = array(
    'author_parsing_methods' => array(
        'scopus_author_api' => $authorProfile ? ($authorProfile['data_source'] === 'scopus_author_api') : false,
        'smart_identification' => $authorProfile ? ($authorProfile['data_source'] === 'smart_identification') : false,
        'common_author_extraction' => $authorProfile ? ($authorProfile['data_source'] === 'common_author_extraction') : false,
        'fallback_generated' => $authorProfile ? ($authorProfile['data_source'] === 'fallback_generated') : false
    ),
    'author_data_sources_distribution' => $authorDataSources,
    'publications_with_valid_authors' => count(array_filter($allPublications, function($pub) {
        return !empty($pub['authors']) && $pub['authors'][0]['full_name'] !== 'Unknown Author';
    }))
);

// Save to cache with smart detection - no time-based expiry
$newDataForCache = $output['data'];
$cacheUpdateInfo = shouldUpdateCache($cacheData, $newDataForCache);

if ($cacheUpdateInfo['should_update']) {
    if (saveToCache($cacheFile, $newDataForCache)) {
        $output['cache_saved'] = true;
        $output['cache_update_reason'] = $cacheUpdateInfo['reason'];
        $output['new_data_hash'] = generateDataHash($newDataForCache);
        
        if (isset($cacheUpdateInfo['old_hash'])) {
            $output['old_data_hash'] = $cacheUpdateInfo['old_hash'];
        }
    } else {
        $output['errors']['cache'] = 'Failed to save cache';
        $output['cache_saved'] = false;
    }
} else {
    $output['cache_saved'] = false;
    $output['cache_update_reason'] = $cacheUpdateInfo['reason'];
    $output['cache_message'] = 'No cache update needed - data unchanged';
    $output['current_hash'] = $cacheUpdateInfo['hash'];
}

// Performance metrics
$output['performance'] = array(
    'processing_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
    'api_calls' => $apiCalls,
    'optimizations_applied' => array(
        'enhanced_author_field_extraction',
        'improved_name_parsing',
        'better_affiliation_handling',
        'multi_author_complete_extraction',
        'smart_openalex_supplementation',
        'author_name_matching_algorithm',
        'prioritized_scopus_data_over_openalex',
        'detailed_eid_author_fetch',
        'dc_creator_parsing',
        'conditional_openalex_fallback',
        'enhanced_open_access_detection',
        'multi_source_oa_verification',
        'doi_pattern_oa_heuristics',
        'smart_cache_with_hash_detection',
        'data_change_based_updates_only',
        'no_time_based_cache_expiry'
    )
);

$output['last_updated'] = date('Y-m-d H:i:s');

echo json_encode($output, JSON_PRETTY_PRINT);
?>
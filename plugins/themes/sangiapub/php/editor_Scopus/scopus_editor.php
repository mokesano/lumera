<?php
/**
 * scopus_editor.php
 * Mencari artikel penulis yang terindeks Scopus dengan sistem caching
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Konfigurasi
$apiKey = "73e21cba2e777a3093e24a781e0ee1a9";
$authorId = isset($_GET['authorid']) ? $_GET['authorid'] : null;
$count = isset($_GET['count']) ? min((int)$_GET['count'], 25) : 10;
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

// Konfigurasi cache
$cachePath = __DIR__ . '/cache';
$cacheExpiry = 604800; // 1 minggu dalam detik (7 * 24 * 60 * 60)

if (!$authorId) {
    echo json_encode(['error' => 'Parameter authorid diperlukan'], JSON_PRETTY_PRINT);
    exit;
}

// Fungsi untuk menghasilkan nama file cache
function generateCacheFilename($authorId, $compressed = true) {
    $cacheCode = substr(md5($authorId), 0, 8);
    $filename = 'authorid_' . $cacheCode . '_' . $authorId;
    return $filename . ($compressed ? '.json.gz' : '.json');
}

// Fungsi untuk mengambil data dari cache (dengan dukungan gzip)
function getFromCache($cacheFile) {
    if (!file_exists($cacheFile)) {
        return null;
    }

    // Deteksi apakah file terkompresi
    $isCompressed = (substr($cacheFile, -3) === '.gz');

    try {
        $cacheData = $isCompressed ? gzdecode(file_get_contents($cacheFile)) : file_get_contents($cacheFile);
        $cacheObj = json_decode($cacheData, true);
        
        if (!$cacheObj || !isset($cacheObj['timestamp'])) {
            return null;
        }
        
        return $cacheObj;
    } catch (Exception $e) {
        return null;
    }
}

// Fungsi untuk menyimpan data ke cache (dengan kompresi gzip)
function saveToCache($cacheFile, $data) {
    if (!is_dir(dirname($cacheFile))) {
        mkdir(dirname($cacheFile), 0755, true);
    }
    
    $cacheObj = [
        'timestamp' => time(),
        'last_updated' => date('Y-m-d H:i:s'), // Tambahkan ini
        'data' => $data
    ];
    
    $jsonData = json_encode($cacheObj);
    $isCompressed = (substr($cacheFile, -3) === '.gz');
    
    if ($isCompressed) {
        file_put_contents($cacheFile, gzencode($jsonData, 9));
    } else {
        file_put_contents($cacheFile, $jsonData);
    }
}

// Fungsi untuk memanggil Scopus API
function callScopusApi($url, $apiKey) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => [
            "X-ELS-APIKey: $apiKey",
            "Accept: application/json"
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_VERBOSE => false
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode >= 400) {
        return ['error' => 'API error: HTTP ' . $httpCode . ($error ? ' - ' . $error : '')];
    }
    
    if (!$response) {
        return ['error' => 'Empty response' . ($error ? ' - ' . $error : '')];
    }
    
    $data = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'JSON decode error: ' . json_last_error_msg()];
    }
    
    return $data;
}

// Fungsi untuk memproses data author
function processAuthors($doc) {
    $authors = [];
    
    // Format 1: Data author dalam array terstruktur dari Scopus
    if (isset($doc['author']) && is_array($doc['author'])) {
        foreach ($doc['author'] as $author) {
            $givenName = isset($author['ce:given-name']) ? trim($author['ce:given-name']) : '';
            $surname = isset($author['ce:surname']) ? trim($author['ce:surname']) : '';
            
            // Gabungkan given name dan surname dengan urutan yang benar
            $fullName = trim("$givenName $surname");
            
            if (!empty($fullName)) {
                $authors[] = [
                    'name' => $fullName,  // Format: "Umar Tangke"
                    'given_name' => $givenName,
                    'surname' => $surname,
                    'authid' => isset($author['@auid']) ? $author['@auid'] : null,
                    'affiliation' => isset($author['affiliation']) ? $author['affiliation'] : null
                ];
            }
        }
    }
    
    // Format 2: Fallback untuk data author dalam string (dc:creator)
    if (empty($authors) && isset($doc['dc:creator'])) {
        if (is_string($doc['dc:creator'])) {
            $creatorNames = array_map('trim', explode(';', $doc['dc:creator']));
            foreach ($creatorNames as $name) {
                if ($name) $authors[] = ['name' => $name];
            }
        } elseif (is_array($doc['dc:creator'])) {
            foreach ($doc['dc:creator'] as $name) {
                if (is_string($name)) $authors[] = ['name' => trim($name)];
            }
        }
    }
    
    return $authors;
}

// Fungsi untuk mengambil detail lengkap publikasi
function fetchPublicationDetails($eid, $apiKey) {
    $cachePath = __DIR__ . '/cache';
    $cacheFile = $cachePath . '/pubdetails_' . substr(md5($eid), 0, 8) . '_' . $eid . '.json.gz';
    $cacheData = getFromCache($cacheFile);
    
    if ($cacheData && (time() - $cacheData['timestamp'] < 604800)) {
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
        'authors' => []
    ];
    
    if (isset($data['abstracts-retrieval-response'])) {
        $response = $data['abstracts-retrieval-response'];
        
        if (isset($response['coredata']['dc:description'])) {
            $details['abstract'] = $response['coredata']['dc:description'];
        }
        
        if (isset($data['abstracts-retrieval-response']['authors']['author'])) {
            $authors = $data['abstracts-retrieval-response']['authors']['author'];
            // Jika hanya ada 1 author, format bukan array
            if (isset($authors['@auid'])) {
                $authors = array($authors);
            }
            
            foreach ($authors as $author) {
                $givenName = isset($author['preferred-name']['given-name']) ? 
                            trim($author['preferred-name']['given-name']) : '';
                $surname = isset($author['preferred-name']['surname']) ? 
                          trim($author['preferred-name']['surname']) : '';
                
                // Gabungkan given name dan surname dengan urutan yang benar
                $fullName = trim("$givenName $surname");
                
                if (!empty($fullName)) {
                    $affiliation = 'N/A';
                    if (isset($author['affiliation']) && is_array($author['affiliation'])) {
                        if (isset($author['affiliation']['organization'])) {
                            $affiliation = $author['affiliation']['organization'];
                        } elseif (isset($author['affiliation'][0]['organization'])) {
                            $affiliation = $author['affiliation'][0]['organization'];
                        }
                    }
                    
                    $details['authors'][] = [
                        'name' => $fullName,  // Format: "Umar Tangke"
                        'given_name' => $givenName,
                        'surname' => $surname,
                        'authid' => isset($author['@auid']) ? $author['@auid'] : null,
                        'affiliation' => $affiliation
                    ];
                }
            }
        }
        
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

// Output structure
$output = [
    'author_id' => $authorId,
    'status' => 'success',
    'cache_status' => null,
    'cache_file' => null,
    'cache_expires' => null,
    'last_updated' => date('Y-m-d H:i:s'), // Tambahkan ini
    'data_source' => null,
    'data' => [
        'author' => null,
        'publications' => [],
        'total_citations' => 0,
        'publication_count' => 0
    ],
    'errors' => [],
    'cache_info' => 'hit=data dari cache; expired=cache ada tapi kadaluarsa; miss=tidak ada cache'
];

// Generate nama file cache dengan kompresi gzip
$cacheFile = $cachePath . '/' . generateCacheFilename($authorId, true);
$output['cache_file'] = basename($cacheFile);

// Check cache
$cacheData = getFromCache($cacheFile);
if ($cacheData) {
    $output['cache_expires'] = date('Y-m-d H:i:s', $cacheData['timestamp'] + $cacheExpiry);
}

// Gunakan cache jika tersedia dan belum kadaluarsa, kecuali force refresh
if (!$forceRefresh && $cacheData && (time() - $cacheData['timestamp'] < $cacheExpiry)) {
    $output['cache_status'] = 'hit';
    $output['data_source'] = 'cache';
    $output['data'] = $cacheData['data'];
    $output['last_updated'] = $cacheData['last_updated']; // Tambahkan ini
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}

// Jika sampai di sini, data akan diambil dari API
$output['cache_status'] = $cacheData ? 'expired' : 'miss';
$output['data_source'] = 'api';

// 1. Ambil semua publikasi dengan paginasi
$allPublications = [];
$start = 0;
$maxResults = 200;

do {
    $docsUrl = "https://api.elsevier.com/content/search/scopus?query=AU-ID($authorId)&start=$start&count=$count&field=eid,dc:title,prism:coverDate,citedby-count,prism:doi,prism:publicationName,author,dc:creator,prism:aggregationType,openaccessFlag,prism:volume,prism:issueIdentifier,prism:pageRange,subtypeDescription";
    $docsData = callScopusApi($docsUrl, $apiKey);

    if (isset($docsData['error'])) {
        $output['errors']['publications'] = $docsData['error'];
        break;
    }

    if (empty($docsData['search-results']['entry'])) break;

    foreach ($docsData['search-results']['entry'] as $doc) {
        $eid = isset($doc['eid']) ? $doc['eid'] : null;
        $pubDetails = $eid ? fetchPublicationDetails($eid, $apiKey) : [];
        
        $authors = isset($pubDetails['authors']) && !empty($pubDetails['authors']) ? 
                   $pubDetails['authors'] : processAuthors($doc);

        $publication = [
            'eid' => $eid ?: 'N/A',
            'title' => isset($doc['dc:title']) ? $doc['dc:title'] : 'N/A',
            'year' => isset($doc['prism:coverDate']) ? substr($doc['prism:coverDate'], 0, 4) : 'N/A',
            'citation_count' => isset($doc['citedby-count']) ? (int)$doc['citedby-count'] : 0,
            'doi' => isset($doc['prism:doi']) ? $doc['prism:doi'] : 'N/A',
            'source' => isset($doc['prism:publicationName']) ? $doc['prism:publicationName'] : 'N/A',
            'authors' => $authors,
            'authors_string' => !empty($authors) ? implode('; ', array_column($authors, 'name')) : 'N/A',
            'abstract' => isset($pubDetails['abstract']) ? $pubDetails['abstract'] : 'N/A',
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
} while ($start < $maxResults);

// 2. Hitung total kutipan
$totalCitations = 0;
foreach ($allPublications as $pub) {
    $totalCitations += $pub['citation_count'];
}

// 3. Isi data output
$output['data']['publications'] = $allPublications;
$output['data']['total_citations'] = $totalCitations;
$output['data']['publication_count'] = count($allPublications);

// 4. Ambil profil penulis jika tersedia
$authorProfileCache = $cachePath . '/profile_' . generateCacheFilename($authorId, true);
$profileCacheData = null;
$useProfileCache = false;

if (!$forceRefresh) {
    $profileCacheData = getFromCache($authorProfileCache);
    if ($profileCacheData && (time() - $profileCacheData['timestamp'] < $cacheExpiry)) {
        $output['data']['author'] = $profileCacheData['data'];
        $useProfileCache = true;
    }
}

if (!$useProfileCache) {
    $authorUrl = "https://api.elsevier.com/content/author/author_id/$authorId";
    $authorData = callScopusApi($authorUrl, $apiKey);

    if (!isset($authorData['error']) && isset($authorData['author-retrieval-response'][0])) {
        $profile = $authorData['author-retrieval-response'][0];
        $output['data']['author'] = [
            'name' => (isset($profile['author-profile']['preferred-name']['given-name']) ? 
                     $profile['author-profile']['preferred-name']['given-name'] . ' ' : '') .
                     (isset($profile['author-profile']['preferred-name']['surname']) ? 
                     $profile['author-profile']['preferred-name']['surname'] : ''),
            'affiliation' => isset($profile['author-profile']['affiliation-current']['affiliation']['organization']) ? 
                           $profile['author-profile']['affiliation-current']['affiliation']['organization'] : 'N/A',
            'h_index' => isset($profile['h-index']) ? (int)$profile['h-index'] : 0,
            'document_count' => isset($profile['document-count']) ? (int)$profile['document-count'] : 0
        ];
        
        saveToCache($authorProfileCache, $output['data']['author']);
    } else {
        if (isset($authorData['error']) && strpos($authorData['error'], 'HTTP 401') !== false) {
            $authorInfo = null;
            foreach ($allPublications as $pub) {
                foreach ($pub['authors'] as $author) {
                    if (isset($author['authid']) && $author['authid'] == $authorId) {
                        $authorInfo = [
                            'name' => $author['name'],
                            'affiliation' => isset($author['affiliation']) ? $author['affiliation'] : 'N/A',
                            'h_index' => 0,
                            'document_count' => count($allPublications)
                        ];
                        break 2;
                    }
                }
            }
            
            if ($authorInfo) {
                $output['data']['author'] = $authorInfo;
                saveToCache($authorProfileCache, $authorInfo);
            } else {
                $output['errors']['author_profile'] = 'Tidak dapat mengakses profil (HTTP 401) dan tidak dapat merekonstruksi dari publikasi';
            }
        } else {
            $output['errors']['author_profile'] = isset($authorData['error']) ? $authorData['error'] : 'Profil tidak tersedia';
        }
    }
}

// Simpan hasil ke cache
saveToCache($cacheFile, $output['data']);
$output['cache_expires'] = date('Y-m-d H:i:s', time() + $cacheExpiry);

echo json_encode($output, JSON_PRETTY_PRINT);
?>
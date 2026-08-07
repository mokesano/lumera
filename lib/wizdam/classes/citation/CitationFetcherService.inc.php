<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/citation/CitationFetcherService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class CitationFetcherService
 *
 * @brief Mengambil & menyimpan data kutipan (citation) untuk sebuah DOI.
 * Adaptasi dari skrip doi_citation.php mandiri menjadi class terintegrasi
 * aplikasi -- memakai DoiCredentialService untuk kredensial (bukan
 * hardcoded config array), dan cache file di lokasi standar aplikasi
 * (bukan folder relatif ke plugin).
 *
 * Cakupan: 5 sumber kutipan -- OpenCitations, Crossref Cited-by, OpenAlex,
 * Semantic Scholar, Dimensions -- mengadaptasi logika penuh dari
 * doi_citation.php yang diberikan pengguna, diporting jadi method class.
 */

import('lib.wizdam.classes.services.DoiCredentialService');

class CitationFetcherService {

    /** @var DoiCredentialService */
    private DoiCredentialService $credentials;

    /** @var int Timeout request HTTP (detik) */
    private int $requestTimeout = 15;

    /** @var int Timeout koneksi HTTP (detik) */
    private int $connectTimeout = 5;

    /**
     * Constructor.
     * @param object|null $journal Jurnal pemilik artikel -- dipakai untuk
     * resolusi kredensial (lihat DoiCredentialService::resolveForJournal()).
     */
    public function __construct($journal = null) {
        $this->credentials = DoiCredentialService::resolveForJournal($journal);
    }

    // =====================================================================
    // API PUBLIK
    // =====================================================================

    /**
     * Ambil daftar kutipan untuk sebuah DOI -- dari cache kalau masih segar
     * (7 hari, lihat isCacheStale()), atau fetch baru dari sumber eksternal
     * kalau cache basi/tidak ada.
     *
     * @param string $doi
     * @param int $limit Batas jumlah kutipan per sumber sebelum digabung & dedup.
     * @param bool $forceRefresh Abaikan cache, selalu fetch baru.
     * @return array ['citation_count'=>int, 'citing_articles'=>array, 'last_updated'=>int]
     */
    public function getCitations(string $doi, int $limit = 50, bool $forceRefresh = false): array {
        if (!$forceRefresh) {
            $cached = $this->_getFromCache($doi);
            if ($cached !== null && !$this->_isCacheStale($cached)) {
                return $cached;
            }
        }

        $all = array_merge(
            $this->_fetchFromOpenCitations($doi, $limit),
            $this->_fetchFromCrossrefCitedBy($doi, $limit),
            $this->_fetchFromOpenAlex($doi, $limit),
            $this->_fetchFromSemanticScholar($doi, $limit),
            $this->_fetchFromDimensions($doi, $limit)
        );

        $combined = $this->_combineCitations($all);
        $result = [
            'citation_count' => count($combined),
            'citing_articles' => array_slice($combined, 0, $limit),
            'last_updated' => time(),
        ];

        $this->_saveToCache($doi, $result);
        return $result;
    }

    /**
     * Versi ringan: cuma baca dari cache (kalau ada), TANPA memicu fetch
     * jaringan sama sekali. Dipakai tampilan (panel artikel, halaman
     * article metrics) supaya request halaman tidak pernah menunggu API
     * eksternal -- pengisian/refresh data murni tanggung jawab scheduled task.
     *
     * @param string $doi
     * @return array|null null kalau belum pernah di-cache sama sekali.
     */
    public function getCachedCitations(string $doi): ?array {
        return $this->_getFromCache($doi);
    }

    // =====================================================================
    // HELPER CACHE
    // =====================================================================
    /**
     * Get cache dir
     */
    private function _getCacheDir(): string {
        $dir = Core::getBaseDir() . '/cache/t_wizdam/citations';
        if (!file_exists($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Get cache file path
     */
    private function _getCacheFilePath(string $doi): string {
        return $this->_getCacheDir() . '/' . md5($doi) . '.json.gz';
    }

    /**
     * Get form cache
     * @return array|null
     */
    private function _getFromCache(string $doi): ?array {
        $cacheFile = $this->_getCacheFilePath($doi);
        if (!file_exists($cacheFile)) {
            return null;
        }
        try {
            $compressed = file_get_contents($cacheFile);
            if ($compressed === false) return null;
            $content = @gzdecode($compressed);
            if ($content === false) return null;
            $data = json_decode($content, true);
            if (!is_array($data) || !isset($data['citing_articles'])) return null;
            return $data;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Save to cache
     */
    private function _saveToCache(string $doi, array $data): bool {
        $cacheFile = $this->_getCacheFilePath($doi);
        $json = json_encode($data);
        if ($json === false) return false;
        $compressed = gzencode($json, 9);
        if ($compressed === false) return false;
        return file_put_contents($cacheFile, $compressed) !== false;
    }

    /**
     * Cache dianggap basi kalau sudah lebih dari 7 hari sejak last_updated.
     * [CATATAN] Ini TTL untuk cache mentah hasil API eksternal (supaya tidak
     * membebani API tiap request) -- BEDA dengan smart-hash detection di
     * CitationRefreshTask yang menentukan kapan citationCount di DB
     * (dipakai ranking Most Cited) perlu diperbarui.
     */
    private function _isCacheStale(array $cached): bool {
        $lastUpdated = (int) ($cached['last_updated'] ?? 0);
        return (time() - $lastUpdated) > (7 * 24 * 60 * 60);
    }

    // =====================================================================
    // SUMBER: OpenCitations (tidak butuh kredensial)
    // =====================================================================

    /**
     * Fetch from Open Citations
     */
    private function _fetchFromOpenCitations(string $doi, int $limit): array {
        $url = "https://opencitations.net/index/coci/api/v1/citations/" . urlencode($doi);
        $response = $this->_makeRequest($url);

        if (!$response || !$response['success'] || empty($response['data'])) {
            return [];
        }

        $citingDois = [];
        foreach ($response['data'] as $citation) {
            if (isset($citation['citing'])) {
                $citingDois[] = $citation['citing'];
            }
        }
        $citingDois = array_slice($citingDois, 0, $limit);

        $citations = [];
        foreach ($citingDois as $citingDoi) {
            $metadata = $this->_getMetadataFromCrossref($citingDoi);
            if ($metadata) {
                $citation = $this->_formatCitationFromCrossref($metadata);
                $citation['source'] = 'opencitations';
                $citations[] = $citation;
            }
        }
        return $citations;
    }

    // =====================================================================
    // SUMBER: Crossref Cited-by (butuh kredensial username/password)
    // =====================================================================

    /**
     * Fetch from Crossref CitedBy
     */
    private function _fetchFromCrossrefCitedBy(string $doi, int $limit): array {
        $username = $this->credentials->getCrossrefUsername();
        $password = $this->credentials->getCrossrefPassword();

        if ($username === '' || $password === '') {
            // Tidak ada kredensial -- lewati sumber ini, bukan error.
            return [];
        }

        $url = "https://doi.crossref.org/servlet/getForwardLinks"
             . "?doi=" . urlencode($doi)
             . "&usr=" . urlencode($username)
             . "&pwd=" . urlencode($password);

        $response = $this->_makeRequest($url);
        if (!$response || !$response['success'] || !isset($response['raw'])) {
            return [];
        }

        $citations = [];
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $loadSuccess = $doc->loadXML($response['raw']);
        libxml_clear_errors();

        if (!$loadSuccess) {
            return [];
        }

        $forwardLinks = $doc->getElementsByTagName('forward_link');
        $count = min($forwardLinks->length, $limit);

        for ($i = 0; $i < $count; $i++) {
            $link = $forwardLinks->item($i);
            $doiNodes = $link->getElementsByTagName('doi');
            $citingDoi = ($doiNodes->length > 0) ? $doiNodes->item(0)->nodeValue : null;
            if (!$citingDoi) continue;

            $metadata = $this->_getMetadataFromCrossref($citingDoi);
            if ($metadata) {
                $citation = $this->_formatCitationFromCrossref($metadata);
                $citation['source'] = 'crossref';
                $citations[] = $citation;
            }
        }

        return $citations;
    }

    // =====================================================================
    // HELPER: Crossref metadata & formatting
    // =====================================================================

    /**
     * Get Metadata from Crossref
     */
    private function _getMetadataFromCrossref(string $doi): ?array {
        $email = $this->credentials->getCrossrefEmail();
        $url = "https://api.crossref.org/works/" . urlencode($doi);
        if ($email !== '') {
            $url .= "?mailto=" . urlencode($email);
        }

        $response = $this->_makeRequest($url);
        if (!$response || !$response['success'] || empty($response['data']['message'])) {
            return null;
        }
        return $response['data']['message'];
    }

    /**
     * Format Citation from Crossref
     */
    private function _formatCitationFromCrossref(array $item): array {
        $title = 'Title not available';
        if (isset($item['title']) && is_array($item['title']) && !empty($item['title'])) {
            $title = $item['title'][0];
        }

        $pubType = (string) ($item['type'] ?? 'article-journal');

        $container = 'Source not available';
        if (isset($item['container-title']) && is_array($item['container-title']) && !empty($item['container-title'])) {
            $container = $item['container-title'][0];
        } elseif (isset($item['publisher'])) {
            $container = (string) $item['publisher'];
        }

        $year = null;
        foreach (['published-print', 'published-online', 'created', 'issued', 'published'] as $dateField) {
            if (isset($item[$dateField]['date-parts'][0][0])) {
                $year = $item[$dateField]['date-parts'][0][0];
                break;
            }
        }

        $authors = [];
        if (isset($item['author']) && is_array($item['author'])) {
            foreach ($item['author'] as $author) {
                if (isset($author['family'])) {
                    $authors[] = [
                        'given' => (string) ($author['given'] ?? ''),
                        'family' => (string) $author['family'],
                    ];
                }
            }
        }

        $doi = (string) ($item['DOI'] ?? '');

        return [
            'title' => $title,
            'doi' => $doi,
            'url' => $doi !== '' ? 'https://doi.org/' . $doi : null,
            'container' => $container,
            'type' => $pubType,
            'publisher' => isset($item['publisher']) ? (string) $item['publisher'] : null,
            'year' => $year,
            'volume' => isset($item['volume']) ? (string) $item['volume'] : null,
            'issue' => isset($item['issue']) ? (string) $item['issue'] : null,
            'page' => isset($item['page']) ? (string) $item['page'] : null,
            'authors' => $authors,
            'title_hash' => md5(strtolower(trim(strip_tags($title)))),
        ];
    }

    /**
     * Gabungkan hasil dari beberapa sumber & buang duplikat (berdasarkan DOI,
     * lalu title_hash sebagai cadangan kalau DOI tidak ada).
     * @param array $citations
     * @return array
     */
    private function _combineCitations(array $citations): array {
        $seenDois = [];
        $seenTitleHashes = [];
        $unique = [];

        foreach ($citations as $citation) {
            if (!is_array($citation)) continue;

            $doi = strtolower(trim((string) ($citation['doi'] ?? '')));
            if ($doi !== '') {
                if (in_array($doi, $seenDois, true)) continue;
                $seenDois[] = $doi;
            } else {
                $titleHash = (string) ($citation['title_hash'] ?? '');
                if ($titleHash !== '') {
                    if (in_array($titleHash, $seenTitleHashes, true)) continue;
                    $seenTitleHashes[] = $titleHash;
                }
            }

            $unique[] = $citation;
        }

        usort($unique, function ($a, $b) {
            $yearA = is_numeric($a['year'] ?? null) ? (int) $a['year'] : 0;
            $yearB = is_numeric($b['year'] ?? null) ? (int) $b['year'] : 0;
            return $yearB <=> $yearA;
        });

        return $unique;
    }

    // =====================================================================
    // SUMBER: OpenAlex (tidak butuh kredensial)
    // =====================================================================

    /**
     * Fetch from Open Alex
     */
    private function _fetchFromOpenAlex(string $doi, int $limit): array {
        $url = "https://api.openalex.org/works/doi:" . urlencode($doi);
        $response = $this->_makeRequest($url);
        if (!$response || !$response['success'] || empty($response['data']['id'])) {
            return [];
        }
        $openAlexId = $response['data']['id'];

        $citationsUrl = "https://api.openalex.org/works?filter=cites:" . urlencode($openAlexId) . "&per_page=$limit";
        $citationsResponse = $this->_makeRequest($citationsUrl);
        if (!$citationsResponse || !$citationsResponse['success'] || empty($citationsResponse['data']['results'])) {
            return [];
        }

        $citations = [];
        foreach ($citationsResponse['data']['results'] as $item) {
            $title = (string) ($item['title'] ?? 'Title not available');
            $pubType = (string) ($item['type'] ?? $item['type_crossref'] ?? 'article-journal');

            $container = null;
            if (isset($item['primary_location']['source']['display_name'])) {
                $container = $item['primary_location']['source']['display_name'];
            } elseif (isset($item['host_venue']['display_name']) && $item['host_venue']['display_name'] !== 'Publication') {
                $container = $item['host_venue']['display_name'];
            }
            if (empty($container)) {
                $container = 'Publication';
            }

            $year = $item['publication_year'] ?? null;
            $itemDoi = isset($item['doi']) ? str_replace('https://doi.org/', '', (string) $item['doi']) : '';

            $authors = [];
            if (isset($item['authorships']) && is_array($item['authorships'])) {
                foreach ($item['authorships'] as $authorship) {
                    $name = (string) ($authorship['author']['display_name'] ?? '');
                    if ($name === '') continue;
                    $parts = explode(' ', trim($name));
                    $family = count($parts) > 1 ? array_pop($parts) : $name;
                    $given = count($parts) > 0 ? implode(' ', $parts) : '';
                    $authors[] = ['given' => $given, 'family' => $family];
                }
            }

            $citations[] = [
                'title' => $title,
                'doi' => $itemDoi,
                'url' => $itemDoi !== '' ? 'https://doi.org/' . $itemDoi : ($item['id'] ?? null),
                'container' => $container,
                'type' => $pubType,
                'publisher' => null,
                'year' => $year,
                'volume' => $item['biblio']['volume'] ?? null,
                'issue' => $item['biblio']['issue'] ?? null,
                'page' => $item['biblio']['first_page'] ?? null,
                'authors' => $authors,
                'source' => 'openalex',
                'title_hash' => md5(strtolower(trim(strip_tags($title)))),
            ];
        }
        return $citations;
    }

    // =====================================================================
    // SUMBER: Semantic Scholar (kredensial opsional -- API key)
    // =====================================================================

    /**
     * Fetch from Semantic Scholar
     */
    private function _fetchFromSemanticScholar(string $doi, int $limit): array {
        $apiKey = $this->credentials->getSemanticScholarApiKey();
        $headers = $apiKey !== '' ? ['x-api-key: ' . $apiKey] : [];

        $url = "https://api.semanticscholar.org/graph/v1/paper/DOI:" . urlencode($doi);
        $response = $this->_makeRequest($url, $headers);
        if (!$response || !$response['success'] || empty($response['data']['paperId'])) {
            return [];
        }
        $paperId = $response['data']['paperId'];

        $citationsUrl = "https://api.semanticscholar.org/graph/v1/paper/" . urlencode($paperId)
            . "/citations?limit=$limit&fields=title,year,authors,venue,publicationTypes,externalIds";
        $citationsResponse = $this->_makeRequest($citationsUrl, $headers);
        if (!$citationsResponse || !$citationsResponse['success'] || empty($citationsResponse['data']['data'])) {
            return [];
        }

        $citations = [];
        foreach ($citationsResponse['data']['data'] as $item) {
            if (empty($item['citingPaper'])) continue;
            $citingPaper = $item['citingPaper'];

            $title = (string) ($citingPaper['title'] ?? '');
            if ($title === '') continue;

            $itemDoi = (string) ($citingPaper['externalIds']['DOI'] ?? '');
            $container = (string) ($citingPaper['venue'] ?? 'Publication');
            if ($container === '') $container = 'Publication';

            $authors = [];
            if (isset($citingPaper['authors']) && is_array($citingPaper['authors'])) {
                foreach ($citingPaper['authors'] as $author) {
                    $name = (string) ($author['name'] ?? '');
                    if ($name === '') continue;
                    if (strpos($name, ',') !== false) {
                        [$family, $given] = array_map('trim', explode(',', $name, 2));
                    } else {
                        $parts = explode(' ', trim($name));
                        $family = count($parts) > 1 ? array_pop($parts) : $name;
                        $given = implode(' ', $parts);
                    }
                    $authors[] = ['given' => $given, 'family' => $family];
                }
            }

            $citations[] = [
                'title' => $title,
                'doi' => $itemDoi,
                'url' => $itemDoi !== '' ? 'https://doi.org/' . $itemDoi : ('https://www.semanticscholar.org/paper/' . ($citingPaper['paperId'] ?? '')),
                'container' => $container,
                'type' => 'article-journal',
                'publisher' => null,
                'year' => $citingPaper['year'] ?? null,
                'volume' => null,
                'issue' => null,
                'page' => null,
                'authors' => $authors,
                'source' => 'semanticscholar',
                'title_hash' => md5(strtolower(trim(strip_tags($title)))),
            ];
        }
        return $citations;
    }

    // =====================================================================
    // SUMBER: Dimensions (via metrics-api resmi -- BUKAN scraping HTML
    // seperti versi asli doi_citation.php, karena scraping HTML rapuh dan
    // tidak bisa diverifikasi/diuji secara wajar. Kalau API key Dimensions
    // tidak diisi, sumber ini dilewati -- bukan error.)
    // =====================================================================

    /**
     * Fetch from Dimensions
     */
    private function _fetchFromDimensions(string $doi, int $limit): array {
        $apiKey = $this->credentials->getDimensionsApiKey();
        if ($apiKey === '') {
            return [];
        }

        // Dimensions metrics API resmi hanya memberi JUMLAH sitasi (bukan
        // daftar detail per-artikel yang mengutip) tanpa akses penuh Dimensions
        // Analytics API (berbayar terpisah, di luar cakupan kredensial API key
        // sederhana). Kita catat jumlahnya sebagai satu entri agregat supaya
        // ikut masuk hitungan citation_count, bukan mengarang detail artikel.
        $url = "https://metrics-api.dimensions.ai/doi/" . urlencode($doi);
        $response = $this->_makeRequest($url, ['Authorization: Bearer ' . $apiKey]);
        if (!$response || !$response['success']) {
            return [];
        }

        $timesCited = $response['data']['times_cited'] ?? ($response['data']['metrics']['times_cited'] ?? null);
        if ($timesCited === null || (int) $timesCited <= 0) {
            return [];
        }

        // [CATATAN] Tidak menghasilkan entri per-artikel (Dimensions metrics
        // API tidak menyediakan itu) -- jadi TIDAK ditambahkan ke
        // combineCitations() sebagai citing_articles individual (akan
        // dianggap 1 "kutipan" tanpa detail, bukan representasi akurat).
        // Dikembalikan kosong sengaja sampai ada akses Dimensions Analytics
        // API penuh untuk daftar per-artikel yang sesungguhnya.
        return [];
    }

    /**
     * Make request
     */
    private function _makeRequest(string $url, array $headers = []): array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Lumera/1.0 (mailto:' . $this->credentials->getCrossrefEmail() . ')');
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->requestTimeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        // [CATATAN] http_code disertakan di setiap hasil (termasuk gagal) --
        // dipakai pemanggil untuk mendeteksi rate-limit (429) di masa depan
        // (lihat poin 6 fallback kuota, belum diimplementasikan penuh).
        if ($response === false || $httpCode < 200 || $httpCode >= 300) {
            return ['success' => false, 'http_code' => $httpCode];
        }

        if (strpos((string) $contentType, 'application/json') !== false) {
            $data = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return ['success' => false, 'http_code' => $httpCode];
            }
            return ['success' => true, 'http_code' => $httpCode, 'data' => $data];
        }

        return ['success' => true, 'raw' => $response, 'content_type' => $contentType];
    }

}
?>
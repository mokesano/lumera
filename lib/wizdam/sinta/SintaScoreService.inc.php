<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/sinta/SintaScoreService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SintaScoreService
 * @ingroup wizdam_sinta
 *
 * @brief Service for scraping SINTA scores and grades based on journal ISSN.
 */

class SintaScoreService {

    /** URL base Sinta */
    private const SINTA_BASE_URL = 'https://sinta.kemdiktisaintek.go.id';

    /** Cold start retri cooldown */
    private const COLD_START_RETRY_COOLDOWN = 21600; // 6 hours

    /**
     * Ensures SINTA score exists for the journal.
     * Triggers immediate scraping if missing, respecting a 6-hour cooldown on failures 
     * to prevent server overload during cold starts.
     * @param object $journal
     * @return bool True if data was just fetched, false otherwise.
     */
    public static function ensureScoreExists($journal): bool {
        if (!$journal || !empty($journal->getSetting('sintaScore'))) {
            return false;
        }

        $lastAttempt = $journal->getSetting('sintaLastAttempt');
        if (!empty($lastAttempt) && (time() - (int) $lastAttempt) < self::COLD_START_RETRY_COOLDOWN) {
            return false;
        }

        $issn = trim((string) $journal->getSetting('onlineIssn'));
        if ($issn === '') {
            $issn = trim((string) $journal->getSetting('printIssn'));
        }
        if ($issn === '') {
            return false;
        }

        $journal->updateSetting('sintaLastAttempt', (string) time(), 'string');

        try {
            $service = new self();
            $result = $service->fetchScore($issn);
        } catch (Exception $e) {
            error_log('SintaScoreService cold start failed for journal ID ' . $journal->getId() . ': ' . $e->getMessage());
            return false;
        }

        if (empty($result['success'])) {
            error_log('SintaScoreService cold start: Journal ID ' . $journal->getId() . ' (ISSN ' . $issn . ') not found in SINTA.');
            return false;
        }

        $journal->updateSetting('sintaScore', $result['impact'] ?? '0.000', 'string');
        $journal->updateSetting('sintaGrade', $result['grade'] ?? null, 'string');
        $journal->updateSetting('sintaId', $result['sinta_id'] ?? null, 'string');
        $journal->updateSetting('sintaUrl', $result['sinta_url'] ?? null, 'string');
        $journal->updateSetting('sintaLastUpdate', date('Y-m-d H:i:s'), 'string');
        
        return true;
    }

    /**
     * Fetches SINTA score and grade for a given ISSN.
     * Attempts both normalized and dashed formats if the first fails.
     * @param string $rawIssn
     * @return array
     */
    public function fetchScore(string $rawIssn): array {
        $normalizedIssn = $this->_normalizeIssn($rawIssn);

        if (!preg_match('/^\d{7}[\dX]$/', $normalizedIssn)) {
            return [
                'success' => false,
                'error' => 'Invalid ISSN format. Expected: 1234-5678 or 12345678',
            ];
        }

        $result = $this->_scrapeSinta($normalizedIssn);
        if (!$result['success']) {
            $issnWithDash = $this->_formatIssnWithDash($normalizedIssn);
            $result = $this->_scrapeSinta($issnWithDash);
        }

        return $result;
    }

    //
    // ISSN helpers
    //

    /**
     * Normalizes ISSN by removing non-alphanumeric characters.
     * @param string $issn
     * @return string
     */
    private function _normalizeIssn(string $issn): string {
        return preg_replace('/[^0-9X]/', '', strtoupper(trim($issn)));
    }

    /**
     * Formats normalized ISSN with a dash (XXXX-XXXX).
     * @param string $normalizedIssn
     * @return string
     */
    private function _formatIssnWithDash(string $normalizedIssn): string {
        if (strlen($normalizedIssn) === 8) {
            return substr($normalizedIssn, 0, 4) . '-' . substr($normalizedIssn, 4, 4);
        }
        return $normalizedIssn;
    }

    /**
     * Scrapes journal profile and search results from the SINTA portal.
     * @param string $issn
     * @return array
     */
    private function _scrapeSinta(string $issn): array {
        $searchUrl = self::SINTA_BASE_URL . '/journals?q=' . urlencode($issn);
        $searchHtml = $this->_fetchWithRetry($searchUrl);

        $journalNameFromSearch = '';
        if (preg_match('/<div[^>]*class="(?:affil-name|journal-list-name)[^"]*"[^>]*>(?:<a[^>]*>)?([^<]+)(?:<\/a>)?/is', $searchHtml, $m)) {
            $journalNameFromSearch = trim(preg_replace('/<i[^>]*>.*?<\/i>/i', '', $m[1]));
        }

        $gradeFromSearch = null;
        if (preg_match('/Accredited<\/span><\/a><\/span>.*?<i class="el el-certificate"><\/i>\s*S(\d+)\s*<span/is', $searchHtml, $gm)
            || preg_match('/class="[^"]*num-stat accredited[^"]*"><a[^>]*><i[^>]*><\/i>\s*S(\d+)\s*<span/is', $searchHtml, $gm)) {
            $gradeFromSearch = $gm[1];
        }

        if (!preg_match('/<a[^>]*href=["\'](?:' . preg_quote(self::SINTA_BASE_URL, '/') . ')?\/journals\/profile\/(\d+)["\'][^>]*>/i', $searchHtml, $m)) {
            return [
                'success' => false,
                'issn' => $this->_formatIssnWithDash($issn),
                'error' => 'Journal not found in SINTA',
            ];
        }

        $journalId = $m[1];
        $profileUrl = self::SINTA_BASE_URL . "/journals/profile/$journalId";
        $profileHtml = $this->_fetchWithRetry($profileUrl);

        $data = [
            'success' => true,
            'issn' => $this->_formatIssnWithDash($issn),
            'sinta_id' => $journalId,
            'sinta_url' => $profileUrl,
        ];

        $journalTitle = '';
        $titlePatterns = [
            '/<div[^>]*class=["\']profile-name["\'][^>]*>([^<]+)<\/div>/is',
            '/<div[^>]*class=["\']affil-name["\'][^>]*>(?:<a[^>]*>)?([^<]+)(?:<\/a>)?/is',
            '/<h1[^>]*>([^<]+)<\/h1>/is',
            '/<h2[^>]*>([^<]+)<\/h2>/is',
            '/<meta\s+name=["\']citation_title["\'][^>]*content=["\']([^"\']+)["\'][^>]*>/is',
            '/<meta\s+property=["\']og:title["\'][^>]*content=["\']([^"\']+)["\'][^>]*>/is',
            '/<li[^>]*class=["\']active["\'][^>]*>([^<]+)<\/li>/is',
            '/<title>([^<|]+)(?:\s*\|[^<]*)?<\/title>/is',
        ];
        foreach ($titlePatterns as $pattern) {
            if (preg_match($pattern, $profileHtml, $tm)) {
                $candidate = trim($tm[1]);
                if ($candidate && $candidate !== 'SINTA' && $candidate !== 'SINTA - Science and Technology Index' && strlen($candidate) > 5) {
                    $journalTitle = $candidate;
                    break;
                }
            }
        }
        
        $data['title'] = ($journalTitle !== '' ? $journalTitle : $journalNameFromSearch) ?: "Journal #$journalId";

        $impact = 0.000;
        if (preg_match('/<div[^>]*class=["\'](?:stat-num|pr-num)["\'][^>]*>([\d\.,]+)<\/div>\s*<div[^>]*class=["\'](?:stat-text|pr-txt)["\'][^>]*>\s*Impact\s*<\/div>/is', $profileHtml, $im)) {
            $impact = (float) str_replace(',', '.', $im[1]);
        }
        $data['impact'] = number_format($impact, 3, '.', '');

        $grade = $gradeFromSearch;
        if ($grade === null) {
            $gradePatterns = [
                '/class="[^"]*num-stat accredited[^"]*"><a[^>]*><i[^>]*><\/i>\s*S(\d+)\s*<span/is',
                '/S(\d+)\s*<span[^>]*>Accredited<\/span>/is',
                '/Accreditation.*?S(?:INTA)?\s*(\d+)/is',
                '/Current\s+Accreditation.*?S(?:inta)?\s*(\d+)/i',
                '/<div[^>]*>S(\d+)<\/div>/i',
                '/<span[^>]*>S(\d+)<\/span>.*?Accredited/is',
                '/Accredited.*?<span[^>]*>S(\d+)<\/span>/is',
                '/<li[^>]*>.*?(?:Accreditation|Sinta).*?S?(\d+).*?<\/li>/is',
            ];
            foreach ($gradePatterns as $pattern) {
                if (preg_match($pattern, $profileHtml, $gm)) {
                    $grade = $gm[1];
                    break;
                }
            }
            if ($grade === null) {
                foreach (['1', '2', '3', '4', '5', '6'] as $g) {
                    if (strpos($profileHtml, "Sinta $g") !== false || strpos($profileHtml, "SINTA $g") !== false || strpos($profileHtml, "S$g Accredited") !== false) {
                        $grade = $g;
                        break;
                    }
                }
            }
        }
        
        if ($grade !== null) {
            $data['grade'] = $grade;
        }

        if (preg_match('/E-ISSN\s*:\s*([^\s|<]+)/is', $profileHtml, $em)) {
            $data['e_issn'] = trim(preg_replace('/[^0-9X-]/', '', $em[1]));
        }
        if (preg_match('/P-ISSN\s*:\s*([^\s|<]+)/is', $profileHtml, $pm)) {
            $data['p_issn'] = trim(preg_replace('/[^0-9X-]/', '', $pm[1]));
        }
        if (preg_match('/<i class="el el-address-book[^>]*><\/i>\s*([^<]+)<\/a>/is', $profileHtml, $pubm)) {
            $data['publisher'] = trim($pubm[1]);
        }

        return $data;
    }

    /**
     * Fetches URL content with cURL, including retry logic and timeout handling.
     * Optimized for batch processing to prevent PHP-FPM timeouts.
     * @param string $url
     * @param int $maxAttempts
     * @param int $timeout
     * @return string
     * @throws Exception
     */
    private function _fetchWithRetry(string $url, int $maxAttempts = 2, int $timeout = 12): string {
        $statusCode = 0;
        $error = '';
        
        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_CONNECTTIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.9,id;q=0.8',
                    'Cache-Control: no-cache',
                ],
            ]);
            
            $response = curl_exec($ch);
            $error = curl_error($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response && $statusCode >= 200 && $statusCode < 300) {
                return $response;
            }
            
            if ($attempt < $maxAttempts) {
                sleep(1);
            }
        }
        
        throw new Exception("Failed to access $url after $maxAttempts attempts. Status: $statusCode, Error: $error");
    }

}
?>
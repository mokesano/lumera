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
 * @brief [WIZDAM] Service untuk scraping skor & grade SINTA (Science and
 * Technology Index, Kemdiktisaintek RI) berdasarkan ISSN jurnal.
 *
 * [MIGRASI] Sebelumnya plugins/themes/{theme}/php/sinta_impact/
 * SintaScoreNEW.php -- skrip PHP mandiri diakses lewat AJAX client-side
 * (rewrite .htaccess /api/sinta_v2), dengan cache file sendiri + logic
 * access-count untuk menentukan TTL dinamis (supaya tidak scraping
 * berlebihan akibat banyak request bersamaan).
 *
 * [KOREKSI ARSITEKTUR] Pendekatan AJAX/.htaccess TERNYATA tidak diminta
 * dan tidak dibutuhkan -- AJAX di skrip lama cuma untuk manipulasi DOM
 * menempelkan hasil, padahal semuanya bisa dikerjakan di backend. Sekarang
 * mengikuti pola CrossrefInfoSender/CitationRefreshTask: SintaScoreTask
 * (ScheduledTask, lib/wizdam/classes/tasks/SintaScoreTask.inc.php) berjalan
 * mingguan, memanggil service ini untuk SETIAP jurnal, lalu menulis hasil
 * LANGSUNG ke journal_settings (sintaScore/sintaGrade/dst) -- bukan lagi
 * ke file cache terpisah. Halaman merender nilai itu dengan MEMBACA
 * journal_settings (lewat PKPTemplateManager::initialize(), assign
 * global), bukan fetch on-demand. Karena penulisan cuma terjadi mingguan
 * lewat SATU proses cron (bukan banyak request bersamaan), logic
 * access-count/TTL dinamis di skrip lama jadi TIDAK RELEVAN lagi -- sudah
 * dihapus, class ini sekarang murni scraping saja.
 */

class SintaScoreService {

    /** Sinta url base */
    private const SINTA_BASE_URL = 'https://sinta.kemdiktisaintek.go.id';

    /**
     * Scrape skor & grade SINTA untuk sebuah ISSN. Mencoba tanpa strip
     * (12345678) dan dengan strip (1234-5678) kalau yang pertama gagal --
     * sama seperti skrip lama.
     * @param string $rawIssn
     * @return array
     */
    public function fetchScore(string $rawIssn): array {
        $normalizedIssn = $this->_normalizeIssn($rawIssn);

        if (!preg_match('/^\d{7}[\dX]$/', $normalizedIssn)) {
            return [
                'success' => false,
                'error' => 'ISSN tidak valid. Format yang benar: 1234-5678 atau 12345678',
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
     * Normalize ISSN
     */
    private function _normalizeIssn(string $issn): string {
        return preg_replace('/[^0-9X]/', '', strtoupper(trim($issn)));
    }

    /**
     * Format ISSN with dash
     */
    private function _formatIssnWithDash(string $normalizedIssn): string {
        if (strlen($normalizedIssn) === 8) {
            return substr($normalizedIssn, 0, 4) . '-' . substr($normalizedIssn, 4, 4);
        }
        return $normalizedIssn;
    }

    //
    // Scraping SINTA -- logika ekstraksi HTML dipertahankan PERSIS sama
    // dengan findJournalInfo() di skrip lama, cuma dipindah jadi method.
    //

    /**
     * Scrape journal Sinta portal
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
                'error' => 'Jurnal tidak ditemukan di SINTA',
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
        if ($journalTitle === '' && $journalNameFromSearch !== '') {
            $journalTitle = $journalNameFromSearch;
        }
        $data['title'] = $journalTitle !== '' ? $journalTitle : "Journal #$journalId";

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
     * Fetch scrape with retry
     */
    private function _fetchWithRetry(string $url, int $maxAttempts = 2, int $timeout = 12): string {
        // [BUGFIX ROBUSTNESS] Sebelumnya maxAttempts=3, timeout=30, dengan
        // sleep($attempt*2) eskalasi antar percobaan -- diwarisi APA ADANYA
        // dari skrip lama yang dirancang untuk SATU fetch on-demand (AJAX).
        // Untuk SintaScoreTask yang mengiterasi BANYAK jurnal dalam SATU
        // eksekusi (dan berjalan lewat plugin acron, tunduk pada batas
        // timeout web server/PHP-FPM di hosting shared -- lihat catatan di
        // SintaScoreTask.inc.php), skenario terburuk SATU URL gagal total
        // sebelumnya bisa menghabiskan ~96 detik -- untuk SATU jurnal saja,
        // padahal tiap jurnal butuh hingga 2 URL (search + profile).
        // Diperketat jadi maxAttempts=2, timeout=12, sleep tetap 1 detik
        // (bukan eskalasi) -- skenario terburuk per URL sekarang ~25 detik.
        //
        // CATATAN JUJUR: pengecekan anggaran waktu di SintaScoreTask cuma
        // dievaluasi DI ANTARA jurnal, bukan di tengah satu fetch yang
        // sedang berjalan -- kalau SATU jurnal kebetulan mengalami
        // worst-case penuh (~50 detik untuk 2 URL), itu TETAP bisa
        // melampaui anggaran task secara keseluruhan untuk eksekusi
        // MINGGU itu saja. Ini bukan kegagalan, cuma batas wajar dari PHP
        // murni (tidak bisa menyela curl_exec() yang sedang berjalan) --
        // jurnal itu akan dicoba lagi otomatis minggu berikutnya (urutan
        // diacak, jadi tidak systematically selalu jurnal yang sama).
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
        throw new Exception("Gagal mengakses $url setelah $maxAttempts percobaan. Status: $statusCode, Error: $error");
    }

}
?>
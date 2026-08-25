<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/statistics/WizdamStats.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Codecanau Team
 * Distributed under the GNU GPL v3.
 *
 * @class WizdamStats
 * @ingroup Statistics
 * 
 * @brief Service Manager for Wizdam Statistics (Orchestration & Caching).
 */

import('lib.wizdam.statistics.WizdamStatsDAO');
import('lib.pkp.classes.core.Core');
import('lib.pkp.classes.config.Config');
import('classes.journal.JournalDAO');

class WizdamStats {

    /** Path direktori cache Stats All Sites */
    const SITE_CACHE_PATH = 'cache/t_wizdam/stats/site_stats.php';

    /**
     * Get Statistics
     * @param mixed $journalId
     */
    public static function getStats($journalId): array {
        $journalId = (int)$journalId;
        $cacheData = self::_getJournalStatsFromCache($journalId);
        if ($cacheData !== false) return $cacheData;
        return self::_calculateAndCacheStats($journalId);
    }
    
    /**
     * Mesin inti yang mengeksekusi seluruh query DAO, menyusun payload, melakukan zero-fill, dan menyimpannya ke cache.
     *
     * @param int $journalId ID jurnal.
     * @return array Array asosiatif berisi payload statistik lengkap yang siap digunakan.
     */
    private static function _calculateAndCacheStats(int $journalId): array {
        $dao = new WizdamStatsDAO();
        $currentYear = (int)date('Y');
        $journalTitle = "";
        $yearlyStats = [];
        
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($journalId);
        if (is_object($journal)) {
            $journalTitle = $journal->getLocalizedTitle();
        }

        // 0. Cek Struktur DB
        $dbStruct = $dao->checkDatabaseStructure();
        $metricsTableExistsStr = $dbStruct['metricsTableExists'] ? "Ya" : "Tidak";
        $metricsColumnsStr = $dbStruct['metricsTableExists'] ? implode(", ", $dbStruct['metricsColumns']) : "Tidak ditemukan";

        // 1. Core Stats & Yearly Views/Downloads
        // [FIX] Sekarang mengirim articleStatsExists/galleyStatsExists supaya
        // getCoreViewsDownloads() bisa fallback ke tabel lama kalau `metrics`
        // kosong -- lihat catatan [FIX] di WizdamStatsDAO::getCoreViewsDownloads().
        $core = $dao->getCoreViewsDownloads(
            $journalId,
            $dbStruct['metricsTableExists'],
            $dbStruct['articleStatsExists'] ?? false,
            $dbStruct['galleyStatsExists'] ?? false
        );
        $totalViews = $core['views'];
        $totalDownloads = $core['downloads'];

        $dateCol = $dao->getDateColumn($dbStruct['metricsColumns']);
        if (!empty($dateCol)) {
            foreach ($dao->getYearlyViewsDownloads($journalId, $dateCol) as $y => $d) {
                if ($y > 1990 && $y <= $currentYear + 1) {
                    if (!isset($yearlyStats[$y])) $yearlyStats[$y] = ['year' => $y];
                    if (isset($d['views'])) $yearlyStats[$y]['views'] = $d['views'];
                    if (isset($d['downloads'])) $yearlyStats[$y]['downloads'] = $d['downloads'];
                }
            }
        }

        // 2. Submissions & Publications
        foreach ($dao->getYearlySubmissions($journalId) as $y => $d) {
            if ($y > 1990 && $y <= $currentYear + 1) { if (!isset($yearlyStats[$y])) $yearlyStats[$y] = ['year' => $y]; $yearlyStats[$y]['submissions'] = $d['submissions']; }
        }
        foreach ($dao->getYearlyPublished($journalId) as $y => $d) {
            if ($y > 1990 && $y <= $currentYear + 1) { if (!isset($yearlyStats[$y])) $yearlyStats[$y] = ['year' => $y]; $yearlyStats[$y]['published'] = $d['published']; }
        }
        foreach ($yearlyStats as $y => $d) {
            $s = $d['submissions'] ?? 0; $p = $d['published'] ?? 0;
            if ($s > 0) $yearlyStats[$y]['acceptRate'] = round(($p / $s) * 100, 1);
        }

        // [FIX] Rekonsiliasi proporsional -- PERSIS logic getJournalStats.php
        // baris ~863-897 ("PERBAIKAN: Verifikasi konsistensi data"), yang
        // sebelumnya tidak ada sama sekali di sini. Kalau breakdown
        // per-tahun untuk views/downloads GAGAL total (jumlah semua tahun =
        // 0) padahal total keseluruhan ($totalViews/$totalDownloads) tidak
        // nol, distribusikan total itu ke tiap tahun secara proporsional
        // berdasarkan jumlah artikel terbit di tahun tsb. Tanpa ini, chart
        // Views & Downloads bisa tampil nol per-tahun walau angka total
        // jurnal sebenarnya besar.
        $totalViewsFromYears = 0;
        $totalDownloadsFromYears = 0;
        $totalPublishedFromYears = 0;
        foreach ($yearlyStats as $d) {
            $totalViewsFromYears += $d['views'] ?? 0;
            $totalDownloadsFromYears += $d['downloads'] ?? 0;
            $totalPublishedFromYears += $d['published'] ?? 0;
        }
        if (Config::getVar('debug', 'log_errors')) {
            error_log("Stats verification - Total views: $totalViews, Sum from years: $totalViewsFromYears");
            error_log("Stats verification - Total downloads: $totalDownloads, Sum from years: $totalDownloadsFromYears");
        }
        if ($totalPublishedFromYears > 0) {
            if ($totalViews > 0 && $totalViewsFromYears === 0) {
                foreach ($yearlyStats as $y => $d) {
                    if (($d['published'] ?? 0) > 0) {
                        $yearlyStats[$y]['views'] = (int) round($totalViews * ($d['published'] / $totalPublishedFromYears));
                    }
                }
            }
            if ($totalDownloads > 0 && $totalDownloadsFromYears === 0) {
                foreach ($yearlyStats as $y => $d) {
                    if (($d['published'] ?? 0) > 0) {
                        $yearlyStats[$y]['downloads'] = (int) round($totalDownloads * ($d['published'] / $totalPublishedFromYears));
                    }
                }
            }
        }

        // 3. Median All-Time
        $totals = $dao->getAcceptDeclineTotals($journalId, $currentYear);
        $acceptRate = ($totals['reviewed'] > 0) ? ($totals['accepted'] * 100 / $totals['reviewed']) : 0;
        $declineRate = ($totals['reviewed'] > 0) ? ($totals['declined'] * 100 / $totals['reviewed']) : 0;
        $hasEd = $dbStruct['editDecisionsExists'];

        $daysPerReview = self::_getMedian($dao->getDaysToReviewAllTime($journalId, $currentYear));
        $daysToPublication = self::_getMedian($dao->getDaysToPublicationAllTime($journalId, $currentYear));
        $subToFirst = self::_getMedian($dao->getFirstDecisionDaysAllTime($journalId, $currentYear, $hasEd));
        $subToAcc = self::_getMedian($dao->getSubmissionToAcceptanceDaysAllTime($journalId, $currentYear, $hasEd));
        $accToPub = self::_getMedian($dao->getAcceptanceToPublicationDaysAllTime($journalId, $currentYear, $hasEd));

        // 4. Yearly Timeline
        $startYear = $dao->getStartYear($journalId, $currentYear - 3);
        for ($y = $startYear; $y <= $currentYear; $y++) {
            $yd = $dao->getYearlyTimelineData($journalId, $y, $hasEd);
            if (!isset($yearlyStats[$y])) $yearlyStats[$y] = ['year' => $y];
            // [FIX] 5 key di bawah SEBELUMNYA dinamai ulang (daysToPublication,
            // reviewTime, firstDecision, submissionToAcceptance,
            // acceptanceToPublication) -- tidak cocok dengan yang dibaca
            // journal-stats.js (daysToPublish, daysPerReview,
            // daysToFirstDecision, daysToAcceptance,
            // daysAcceptanceToPublication) untuk KEY PER-TAHUN ini secara
            // spesifik. [CATATAN] ini BEDA dari key top-level di bawah
            // (baris ~186-187: 'daysPerReview', 'daysToPublication', dst)
            // yang MEMANG harus tetap seperti itu karena sudah dipakai
            // editorial-timeline.tpl -- sudah diverifikasi tidak ada
            // konsumen lain yang membaca isi per-tahun ini selain
            // journal-stats.js, jadi aman diselaraskan ke situ.
            $yearlyStats[$y]['daysToPublish'] = round(self::_getMedian($yd['publication']));
            $yearlyStats[$y]['daysPerReview'] = round(self::_getMedian($yd['review']));
            $yearlyStats[$y]['daysToFirstDecision'] = round(self::_getMedian($yd['firstDecision']));
            $yearlyStats[$y]['daysToAcceptance'] = round(self::_getMedian($yd['acceptance']));
            $yearlyStats[$y]['daysAcceptanceToPublication'] = round(self::_getMedian($yd['acceptanceToPub']));
        }

        // 5. Totals
        $pub = $dao->getTotalArticlesAndIssues($journalId);
        $lastPub = $dao->getLastPublicationData($journalId);
        $articlesPerIssue = ($pub['issues'] > 0) ? ($pub['articles'] / $pub['issues']) : 0;

        // 6. Zero-Fill
        if (!empty($yearlyStats)) {
            // [FIX] Disamakan ke nama key final yang benar (lihat catatan
            // di atas), dan 'published' TIDAK LAGI dipaksa jadi
            // 'publications' -- journal-stats.js membaca stat.published.
            $allKeys = ['year'=>0, 'views'=>0, 'downloads'=>0, 'submissions'=>0, 'published'=>0, 'acceptRate'=>0, 'daysToPublish'=>0, 'daysPerReview'=>0, 'daysToFirstDecision'=>0, 'daysToAcceptance'=>0, 'daysAcceptanceToPublication'=>0, 'accepted'=>0, 'declined'=>0];
            ksort($yearlyStats);
            foreach ($yearlyStats as $y => $d) {
                foreach ($allKeys as $k => $def) {
                    if (!isset($yearlyStats[$y][$k])) {
                        $yearlyStats[$y][$k] = ($k == 'year') ? $y : $def;
                    }
                }
            }
        }

        // 7. Final Payload
        $stats = [
            'journalId' => $journalId, 'journalTitle' => $journalTitle,
            'totalViews' => $totalViews, 'totalDownloads' => $totalDownloads,
            'journalTotalViews' => $totalViews, 'journalTotalDownloads' => $totalDownloads,
            'acceptRate' => round($acceptRate, 1), 'declineRate' => round($declineRate, 1),
            'daysPerReview' => round($daysPerReview), 'daysToPublication' => round($daysToPublication),
            'submissionToFirstDecision' => round($subToFirst), 'submissionToAcceptance' => round($subToAcc), 'acceptanceToPublication' => round($accToPub),
            'totalArticles' => $pub['articles'], 'totalIssues' => $pub['issues'], 'articlesPerIssue' => round($articlesPerIssue, 1),
            'lastPublicationYear' => $lastPub['year'], 'lastYearArticleCount' => $lastPub['count'],
            'metricsTableExists' => $metricsTableExistsStr, 'metricsColumns' => $metricsColumnsStr,
            'articleStatsExists' => $dbStruct['articleStatsExists'] ? "Ya" : "Tidak", 'galleyStatsExists' => $dbStruct['galleyStatsExists'] ? "Ya" : "Tidak",
            'calculationDate' => date('Y-m-d H:i:s'), 'lastUpdated' => date('Y-m-d H:i:s'),
            'yearlyStats' => array_values($yearlyStats)
        ];

        self::_cacheJournalStats($journalId, $stats);
        return $stats;
    }

    /**
     * Menghitung nilai median dari sebuah array numerik.
     *
     * @param array $arr Array numerik yang akan dihitung mediannya.
     * @return float Nilai median. Mengembalikan 0.0 jika array kosong.
     */
    /** [BUGFIX] Casting floor() ke (int) agar aman untuk indeks array di PHP 8+ */
    private static function _getMedian(array $arr): float {
        if (empty($arr)) return 0.0;
        sort($arr);
        $count = count($arr);
        $middle = (int) floor($count / 2);
        if ($count % 2 == 0) {
            if (isset($arr[$middle - 1]) && isset($arr[$middle])) return (float) (($arr[$middle - 1] + $arr[$middle]) / 2);
            return 0.0;
        } else {
            if (isset($arr[$middle])) return (float) $arr[$middle];
            return 0.0;
        }
    }

    /**
     * Membaca data statistik jurnal dari file cache PHP (Smart Cache).
     *
     * @param int $journalId ID jurnal.
     * @return array|false Array data statistik jika cache valid, atau false jika cache tidak ada/kedaluwarsa/rusak.
     */
    private static function _getJournalStatsFromCache(int $journalId) {
        $cacheDir = self::_getCacheDir();
        $cacheFile = $cacheDir . 'journal_' . $journalId . '_stats.php';
        $hashFile = $cacheFile . '.hash';
        if (file_exists($cacheFile) && file_exists($hashFile)) {
            $currentHash = self::_getJournalStatsDataHash($journalId);
            $cachedHash = trim((string)@file_get_contents($hashFile));
            if ($currentHash !== '' && $cachedHash !== '' && hash_equals($cachedHash, $currentHash)) {
                try {
                    $c = unserialize((string)@file_get_contents($cacheFile));
                    if (is_array($c)) return $c;
                } catch (Exception $e) {}
            }
        }
        return false;
    }

    /**
     * Menyimpan payload statistik ke dalam cache (format PHP serialize + Hash).
     *
     * [FIX] Sebelumnya juga menulis salinan '.json.gz' -- itu HANYA pernah
     * dibaca oleh fetch() sisi-browser di journal-stats.js (pola lama untuk
     * menghindari beban query N+1). getStats()/_getJournalStatsFromCache()
     * di file ini SENDIRI tidak pernah membaca file .json.gz itu, cuma file
     * '.php' (unserialize) -- jadi penulisan .json.gz murni kerja mubazir
     * (encode + gzip + I/O disk) sejak template sekarang menerima data
     * langsung dari handler, bukan lagi fetch file terpisah.
     *
     * @param int $journalId ID jurnal.
     * @param array $stats Payload data statistik yang akan di-cache.
     * @return bool True jika penyimpanan berhasil, false jika gagal.
     */
    private static function _cacheJournalStats(int $journalId, array $stats): bool {
        $cacheDir = self::_getCacheDir();
        if (!self::_ensureCacheDirExists($cacheDir)) return false;
        $cacheFile = $cacheDir . 'journal_' . $journalId . '_stats.php';
        $hashFile = $cacheFile . '.hash';
        try {
            $r1 = @file_put_contents($cacheFile, serialize($stats));
            $r2 = @file_put_contents($hashFile, self::_getJournalStatsDataHash($journalId));
            return ($r1 !== false && $r2 !== false);
        } catch (Exception $e) { return false; }
    }

    /**
     * Mendapatkan path absolut direktori penyimpanan cache statistik.
     *
     * @return string Path direktori cache (diakhiri dengan slash).
     */
    private static function _getCacheDir(): string {
        // [FIX] Sebelumnya '/public/wizdam_cache/stats/' -- cache internal
        // ini TIDAK PERNAH diakses langsung oleh browser (beda dengan file
        // JSON.gz yang dulu di-fetch client-side, yang sekarang sudah
        // dihapus total -- lihat AboutJournalHandler/IndexHandler). Jadi
        // aman dipindah ke luar public/, disamakan dengan SITE_CACHE_PATH
        // ('cache/t_wizdam/stats/...') yang sudah lebih dulu benar di
        // file ini.
        return Core::getBaseDir() . '/cache/t_wizdam/stats/';
    }

    /**
     * Memastikan direktori cache tersedia dan dapat ditulis (writable).
     *
     * @param string $dir Path direktori yang akan diperiksa/dibuat.
     * @return bool True jika direktori ada dan writable, false jika gagal dibuat/tidak writable.
     */
    private static function _ensureCacheDirExists(string $dir): bool { 
        if (!file_exists($dir)) return mkdir($dir, 0755, true); return is_writable($dir); 
    }

    /**
     * Membuat hash MD5 dari metrik dasar jurnal untuk mendeteksi perubahan data (Smart Cache invalidation).
     *
     * @param int $journalId ID jurnal.
     * @return string Hash MD5. Mengembalikan string kosong jika tidak ada data atau terjadi error.
     */
    private static function _getJournalStatsDataHash(int $journalId): string {
        try {
            $m = (new WizdamStatsDAO())->getHashMetrics($journalId);
            if (empty($m['total_articles']) && empty($m['total_views'])) return '';
            return md5(serialize($m));
        } catch (Exception $e) { return ''; }
    }

    /**
     * Mengambil statistik agregat (Site-Wide) dari seluruh jurnal yang ada di situs.
     * Metode ini akan menjumlahkan views, downloads, interaksi, dan authors dari semua jurnal.
     *
     * [SMART CACHE] Cache berbasis fingerprint data (bukan TTL waktu tetap).
     * Setiap pemanggilan mengecek fingerprint MURAH dulu; kalau sama dengan
     * yang tersimpan di cache, cache masih valid berapa pun lama waktu
     * berlalu. Kalau beda (ada data baru masuk), cache langsung dianggap
     * basi SEKETIKA dan dihitung ulang.
     *
     * @return array Array asosiatif berisi agregat statistik situs dan daftar statistik per jurnal (journalsStats).
     */
    public static function getSiteWideStats(): array {
        $cacheFile = Core::getBaseDir() . '/' . self::SITE_CACHE_PATH;
        $dao = new WizdamStatsDAO();

        // [SMART CACHE] Fingerprint -- jauh lebih ringan daripada
        // menghitung ulang agregat penuh lintas semua jurnal.
        $currentFingerprint = $dao->getSiteStatsFingerprint();

        if (file_exists($cacheFile)) {
            try {
                $cached = @unserialize((string) @file_get_contents($cacheFile));
                if (is_array($cached) && ($cached['_fingerprint'] ?? null) === $currentFingerprint) {
                    return $cached;
                }
            } catch (Exception $e) {}
        }

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journals = $journalDao->getJournals(true);
        
        $jStats = []; $vTot = 0; $dTot = 0; $iTot = 0; $aTot = 0;
        try {
            if ($journals) {
                $journalList = [];
                while ($j = $journals->next()) {
                    $journalList[(int) $j->getId()] = $j;
                }

                $statsBatch = $dao->getSiteJournalStatsBatch(array_keys($journalList));

                foreach ($journalList as $id => $j) {
                    $s = $statsBatch[$id] ?? ['views' => 0, 'downloads' => 0, 'authors' => 0];
                    $inter = $s['views'] + $s['downloads'];
                    if ($s['views'] > 0 || $s['downloads'] > 0 || $s['authors'] > 0) {
                        $vTot += $s['views']; $dTot += $s['downloads']; $iTot += $inter; $aTot += $s['authors'];
                        $jStats[] = ['id'=>$id, 'title'=>$j->getLocalizedTitle()?:$j->getPath(), 'path'=>$j->getPath(), 'views'=>$s['views'], 'downloads'=>$s['downloads'], 'totalInteractions'=>$inter, 'authors'=>$s['authors']];
                    }
                }
            }
            usort($jStats, fn($a, $b) => $b['views'] <=> $a['views']);
            $site = ['journalsStats'=>$jStats, 'allTotalViews'=>$vTot, 'allTotalDownloads'=>$dTot, 'allTotalInteractions'=>$iTot, 'allTotalAuthors'=>$aTot, '_fingerprint'=>$currentFingerprint];
            $dir = dirname($cacheFile); if (!file_exists($dir)) @mkdir($dir, 0755, true);
            @file_put_contents($cacheFile, serialize($site));
            return $site;
        } catch (Exception $e) { return ['error' => $e->getMessage()]; }
    }

}
?>
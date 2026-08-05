<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/statistics/WizdamStats.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Codecanau Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
        $core = $dao->getCoreViewsDownloads($journalId, $dbStruct['metricsTableExists']);
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
            if ($s > 0) $yearlyStats[$y]['acceptanceRate'] = round(($p / $s) * 100, 1);
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
            $yearlyStats[$y]['daysToPublication'] = round(self::_getMedian($yd['publication']));
            $yearlyStats[$y]['reviewTime'] = round(self::_getMedian($yd['review']));
            $yearlyStats[$y]['firstDecision'] = round(self::_getMedian($yd['firstDecision']));
            $yearlyStats[$y]['submissionToAcceptance'] = round(self::_getMedian($yd['acceptance']));
            $yearlyStats[$y]['acceptanceToPublication'] = round(self::_getMedian($yd['acceptanceToPub']));
        }

        // 5. Totals
        $pub = $dao->getTotalArticlesAndIssues($journalId);
        $lastPub = $dao->getLastPublicationData($journalId);
        $articlesPerIssue = ($pub['issues'] > 0) ? ($pub['articles'] / $pub['issues']) : 0;

        // 6. Zero-Fill
        if (!empty($yearlyStats)) {
            $allKeys = ['year'=>0, 'views'=>0, 'downloads'=>0, 'submissions'=>0, 'published'=>0, 'acceptanceRate'=>0, 'daysToPublication'=>0, 'reviewTime'=>0, 'firstDecision'=>0, 'submissionToAcceptance'=>0, 'acceptanceToPublication'=>0, 'accepted'=>0, 'declined'=>0];
            ksort($yearlyStats);
            foreach ($yearlyStats as $y => $d) {
                foreach ($allKeys as $k => $def) {
                    if (!isset($yearlyStats[$y][$k])) {
                        if ($k == 'published') $yearlyStats[$y]['publications'] = $def;
                        else $yearlyStats[$y][$k] = ($k == 'year') ? $y : $def;
                    }
                }
                if (isset($yearlyStats[$y]['published'])) unset($yearlyStats[$y]['published']);
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
     * Menyimpan payload statistik ke dalam cache (format PHP serialize, JSON gzip, dan Hash).
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
        $jsonCacheFile = $cacheDir . 'journal_' . $journalId . '_stats.json.gz';
        try {
            $r1 = @file_put_contents($cacheFile, serialize($stats));
            $r2 = false;
            $json = json_encode($stats);
            if ($json !== false) { $gz = gzencode($json, 9); if ($gz !== false) $r2 = @file_put_contents($jsonCacheFile, $gz); }
            $r3 = @file_put_contents($hashFile, self::_getJournalStatsDataHash($journalId));
            return ($r1 !== false && $r2 !== false && $r3 !== false);
        } catch (Exception $e) { return false; }
    }

    /**
     * Mendapatkan path absolut direktori penyimpanan cache statistik.
     *
     * @return string Path direktori cache (diakhiri dengan slash).
     */
    private static function _getCacheDir(): string {
        return Core::getBaseDir() . '/public/wizdam_cache/stats/'; 
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
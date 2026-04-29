<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/statistics/StatsManager.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StatsManager
 * @ingroup Statistics
 * 
 * @brief Service Layer untuk Kalkulasi dan Payload Statistik [WIZDAM EDITION]
 * @version 2.0 (Strict MVC & Micro-Payloads Compliant)
 */

import('lib.wizdam.statistics.JournalStatsDAO');

class StatsManager {

    /**
     * [WIZDAM] - Entry Point Utama untuk Controller / IndexHandler
     * Menggantikan eksekusi tag {php} di Smarty secara total.
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     */
    public static function assignWidgetPayload(TemplateManager $templateMgr, ?Journal $journal, bool $forceRefresh = false): void {
        $dao = new JournalStatsDAO();
        $dbStructure = $dao->checkDatabaseStructure();
        
        $metricsTableExists = $dbStructure['metricsTableExists'] ? 'Ya' : 'Tidak';
        $currentYear = (int) date('Y');

        if ($journal) {
            // ==========================================
            // LOGIKA LEVEL JURNAL
            // ==========================================
            $journalId = (int) $journal->getId();
            $cacheKey = "journal_{$journalId}_stats";

            $statsData = self::_getFromCache($cacheKey);

            if ($forceRefresh || $statsData === false) {
                // 1. Ambil Core Stats (Views, Downloads, Authors)
                $coreStats = $dao->getJournalCoreStats($journalId, $dbStructure);
                $authorsCount = $dao->getUniqueAuthorsCount($journalId);
                
                // 2. Ambil Rates (Accept/Decline)
                $totals = $dao->getAcceptDeclineTotals($journalId, $currentYear);
                $acceptRate = ($totals['reviewed'] > 0) ? round(($totals['accepted'] / $totals['reviewed']) * 100, 1) : 0.0;
                $declineRate = ($totals['reviewed'] > 0) ? round(($totals['declined'] / $totals['reviewed']) * 100, 1) : 0.0;

                // 3. Ambil Timeline Raw dan Hitung Median di PHP (Service Layer)
                $timelineRaw = $dao->getReviewTimelineRaw($journalId, $currentYear, $dbStructure['editDecisionsExists']);
                
                $statsData = [
                    'views' => $coreStats['views'],
                    'downloads' => $coreStats['downloads'],
                    'authors' => $authorsCount,
                    'acceptRate' => $acceptRate,
                    'declineRate' => $declineRate,
                    'daysPerReview' => self::_getMedian($timelineRaw['daysReview']),
                    'daysToPublication' => self::_getMedian($timelineRaw['daysPublication']),
                    'daysToFirstDecision' => self::_getMedian($timelineRaw['daysFirstDecision']),
                    'daysSubmissionToAcceptance' => self::_getMedian($timelineRaw['daysSubmissionToAcceptance']),
                    'daysAcceptanceToPublication' => self::_getMedian($timelineRaw['daysAcceptanceToPublication']),
                    'lastUpdated' => date('Y-m-d H:i:s')
                ];

                self::_saveToCache($cacheKey, $statsData);
            }

            // [WIZDAM] - Micro-Payload Injection (Skalar Murni)
            $templateMgr->assign([
                'isSiteLevel' => false,
                'currentJournalTitle' => (string) $journal->getLocalizedTitle(),
                'journalTotalViews' => (int) $statsData['views'],
                'journalTotalDownloads' => (int) $statsData['downloads'],
                'journalTotalAuthors' => (int) $statsData['authors'],
                'acceptRate' => (float) $statsData['acceptRate'],
                'declineRate' => (float) $statsData['declineRate'],
                'daysPerReview' => (int) $statsData['daysPerReview'],
                'daysToPublication' => (int) $statsData['daysToPublication']
            ]);

        } else {
            // ==========================================
            // LOGIKA LEVEL SITE (AGREGAT ROOT PUBLISHER)
            // Diadaptasi dari PKPWizdamStats::getSiteWideStats
            // ==========================================
            $cacheKey = "site_global_stats"; 
            $statsData = self::_getFromCache($cacheKey);

            if ($forceRefresh || $statsData === false) {
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $journals = $journalDao->getJournals(true);
                
                $allTotalViews = 0;
                $allTotalDownloads = 0;
                $allTotalInteractions = 0;
                $allTotalAuthors = 0;
                $journalsStatsList = [];

                if ($journals) {
                    while ($j = $journals->next()) {
                        $jId = (int) $j->getId();
                        
                        // Eksekusi kueri terpercaya
                        $cStats = $dao->getJournalCoreStats($jId, $dbStructure);
                        $cAuth = $dao->getUniqueAuthorsCount($jId);

                        $jViews = (int) $cStats['views'];
                        $jDownloads = (int) $cStats['downloads'];
                        $jAuthors = (int) $cAuth;
                        
                        // [WIZDAM] Kalkulasi Metrik Interaksi
                        $jInteractions = $jViews + $jDownloads;

                        // Filter murni dari kode Anda: hanya masukkan jurnal yang punya data
                        if ($jViews > 0 || $jDownloads > 0 || $jAuthors > 0) {
                            $allTotalViews += $jViews;
                            $allTotalDownloads += $jDownloads;
                            $allTotalInteractions += $jInteractions;
                            $allTotalAuthors += $jAuthors;

                            $journalsStatsList[] = [
                                'id' => $jId,
                                'title' => $j->getLocalizedTitle() ? (string) $j->getLocalizedTitle() : (string) $j->getPath(),
                                'path' => (string) $j->getPath(),
                                'views' => $jViews,
                                'downloads' => $jDownloads,
                                'totalInteractions' => $jInteractions,
                                'authors' => $jAuthors
                            ];
                        }
                    }
                }

                // Urutkan List Jurnal berdasarkan views tertinggi (Leaderboard)
                usort($journalsStatsList, function($a, $b) {
                    return $b['views'] <=> $a['views'];
                });

                $statsData = [
                    'allTotalViews' => $allTotalViews,
                    'allTotalDownloads' => $allTotalDownloads,
                    'allTotalInteractions' => $allTotalInteractions,
                    'allTotalAuthors' => $allTotalAuthors,
                    'journalsStats' => $journalsStatsList,
                    'lastUpdated' => date('Y-m-d H:i:s')
                ];

                self::_saveToCache($cacheKey, $statsData, 0); // Eksekusi ke t_wizdam/stats/
            }

            // Injeksi final ke Smarty IndexHandler
            $templateMgr->assign([
                'isSiteLevel' => true,
                'allTotalViews' => (int) ($statsData['allTotalViews'] ?? 0),
                'allTotalDownloads' => (int) ($statsData['allTotalDownloads'] ?? 0),
                'allTotalInteractions' => (int) ($statsData['allTotalInteractions'] ?? 0),
                'allTotalAuthors' => (int) ($statsData['allTotalAuthors'] ?? 0),
                'journalsStats' => (array) ($statsData['journalsStats'] ?? [])
            ]);
        }

        // [WIZDAM] - Variabel Global untuk Diagnostik
        $templateMgr->assign([
            'lastUpdated' => (string) $statsData['lastUpdated'],
            'metricsTableExists' => (string) $metricsTableExists,
            'showDiagnostics' => false // Ubah ke true jika butuh debugging di TPL
        ]);
    }

    /**
     * [WIZDAM] - Helper Matematika Internal
     * @param array $arr
     * @return float
     */
    private static function _getMedian(array $arr): float {
        if (empty($arr)) return 0.0;
        sort($arr);
        $count = count($arr);
        $middle = floor(($count - 1) / 2);
        return ($count % 2) ? (float) $arr[$middle] : (float) (($arr[$middle] + $arr[(int)$middle + 1]) / 2);
    }

    /**
     * [WIZDAM] - Helper Manajemen Cache Tersentralisasi
     * Masa berlaku cache ditetapkan 1 Hari (86400 detik) untuk menghemat load DB
     * @param string $cacheKey
     * @return array|false
     */
    private static function _getFromCache(string $cacheKey) {
        $cacheDir = Core::getBaseDir() . '/cache/wizdam_stats/';
        $cacheFile = $cacheDir . $cacheKey . '.php';

        // Validasi keberadaan file dan umur cache (Max 1 hari / 86400 detik)
        if (!file_exists($cacheFile) || (time() - filemtime($cacheFile)) > 86400) {
            return false;
        }

        $cacheContent = file_get_contents($cacheFile);
        if ($cacheContent === false) return false;

        $cacheContent = preg_replace('/^<\?php exit\(\); \?>/', '', $cacheContent);
        return unserialize($cacheContent);
    }

    /**
     * [WIZDAM] - Helper Manajemen Cache Tersentralisasi
     * @param string $cacheKey
     * @param array $data
     */
    private static function _saveToCache(string $cacheKey, array $data): void {
        $cacheDir = Core::getBaseDir() . '/cache/wizdam_stats/';
        
        if (!file_exists($cacheDir)) {
            @mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . $cacheKey . '.php';
        $cacheContent = '<?php exit(); ?>' . serialize($data);
        
        file_put_contents($cacheFile, $cacheContent);
    }
}
?>
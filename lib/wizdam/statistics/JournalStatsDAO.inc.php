<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/statistics/JournalStatsDAO.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class JournalStatsDAO
 * @ingroup Statistics
 * 
 * @brief Data Access Object for Journal Statistics [WIZDAM EDITION]
 */

import('classes.db.DAO');

// Pastikan konstanta OJS tersedia
if (!defined('ASSOC_TYPE_JOURNAL')) define('ASSOC_TYPE_JOURNAL', 256);
if (!defined('ASSOC_TYPE_ISSUE')) define('ASSOC_TYPE_ISSUE', 257);
if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);

class JournalStatsDAO extends DAO {

    /**
     * [HELPER] Mengambil satu nilai skalar dari query.
     * @param string $sql
     * @param array $params
     * @param string|null $key Nama kolom yang ingin diambil. Jika null, ambil kolom pertama.
     * @return mixed|null
     */
    protected function fetchScalar(string $sql, array $params = [], ?string $key = null) {
        $result = $this->retrieve($sql, $params);
        if ($result && !$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            $result->Close(); // Pastikan ditutup untuk menghemat koneksi DB
            
            if ($key !== null && isset($fields[$key])) {
                return $fields[$key];
            }
            return reset($fields); // Kembalikan elemen pertama jika $key tidak diisi
        }
        if ($result) $result->Close();
        return null;
    }

    /**
     * [HELPER] Mengambil baris saat ini dari result set sebagai array.
     * @param mixed $result
     */
    protected function fetchRow($result): ?array {
        if ($result && !$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            if (is_array($fields)) {
                return $fields;
            }
        }
        return null;
    }

    /**
     * Check the database structure for required tables and columns.
     */
    public function checkDatabaseStructure(): array {
        $info = [
            'metricsTableExists' => false,
            'metricsColumns' => [],
            'articleStatsExists' => false,
            'galleyStatsExists' => false,
            'authorsTableExists' => false,
            'editDecisionsExists' => false
        ];

        try {
            $result = $this->retrieve("SHOW TABLES LIKE 'metrics'");
            $info['metricsTableExists'] = ($result->RecordCount() > 0);
            $result->Close();

            if ($info['metricsTableExists']) {
                $result = $this->retrieve("SHOW COLUMNS FROM metrics");
                // Gunakan helper fetchRow untuk loop yang bersih
                while ($row = $this->fetchRow($result)) {
                    $info['metricsColumns'][] = (string) $row[0];
                    $result->MoveNext();
                }
                $result->Close();
            }

            $info['articleStatsExists'] = ($this->retrieve("SHOW TABLES LIKE 'article_view_stats'")->RecordCount() > 0);
            $info['galleyStatsExists'] = ($this->retrieve("SHOW TABLES LIKE 'article_galley_view_stats'")->RecordCount() > 0);
            $info['authorsTableExists'] = ($this->retrieve("SHOW TABLES LIKE 'authors'")->RecordCount() > 0);
            $info['editDecisionsExists'] = ($this->retrieve("SHOW TABLES LIKE 'edit_decisions'")->RecordCount() > 0);

        } catch (Exception $e) {
            error_log("[WIZDAM DAO] Error checking tables: " . $e->getMessage());
        }

        return $info;
    }

    /**
     * [PERF FIX] Versi batch dari getJournalCoreStats()+getUniqueAuthorsCount()
     * gabungan -- ambil views/downloads/authors utk BANYAK jurnal sekaligus
     * lewat tabel metrics modern, menghindari pola N+1 di
     * StatsManager::assignWidgetPayload() (level publisher/site).
     *
     * Menyertakan fallback batch (tanpa filter metric_type) untuk journal yang
     * masih 0 setelah filter spesifik -- pelajaran dari bug produksi di
     * WizdamStatsDAO::getSiteJournalStatsBatch() yang sempat kehilangan
     * fallback ini dan membuat downloads tampil 0 di semua jurnal.
     *
     * Catatan: TIDAK menyertakan fallback ke tabel legacy
     * (article_view_stats/article_galley_view_stats) -- itu tetap jadi
     * tanggung jawab pemanggil (lihat StatsManager::assignWidgetPayload())
     * yang jatuh ke getJournalCoreStats() penuh HANYA untuk journal yang
     * masih 0 setelah batch modern (dicek per-metrik, bukan "ketiganya nol").
     *
     * @param int[] $journalIds
     * @param array $dbStructure
     * @return array [$journalId => ['views'=>int,'downloads'=>int,'authors'=>int]]
     */
    public function getJournalCoreStatsBatch(array $journalIds, array $dbStructure): array {
        $journalIds = array_values(array_unique(array_map('intval', $journalIds)));
        $stats = [];
        foreach ($journalIds as $id) {
            $stats[$id] = ['views' => 0, 'downloads' => 0, 'authors' => 0];
        }
        if (empty($journalIds) || empty($dbStructure['metricsTableExists'])) {
            return $stats;
        }

        $this->_fillCoreStatsBatch(
            $stats, 'views', ASSOC_TYPE_ARTICLE,
            "(metric_type = 'ojs::counter::article' OR metric_type LIKE '%view%')"
        );
        $this->_fillCoreStatsBatch(
            $stats, 'downloads', ASSOC_TYPE_GALLEY,
            "(metric_type = 'ojs::counter::galley' OR metric_type LIKE '%download%')"
        );

        $placeholders = implode(',', array_fill(0, count($journalIds), '?'));
        $authorsResult = $this->retrieve(
            "SELECT art.journal_id AS jid, COUNT(DISTINCT a.author_id) AS t
             FROM authors a
             JOIN published_articles pa ON a.submission_id = pa.article_id
             JOIN articles art ON pa.article_id = art.article_id
             WHERE art.journal_id IN ($placeholders)
             GROUP BY art.journal_id",
            $journalIds
        );
        if ($authorsResult) {
            while (!$authorsResult->EOF) {
                $row = $authorsResult->GetRowAssoc(false);
                $stats[(int) $row['jid']]['authors'] = (int) $row['t'];
                $authorsResult->MoveNext();
            }
            $authorsResult->Close();
        }

        return $stats;
    }

    /**
     * [PERF FIX + BUGFIX] Helper batch untuk getJournalCoreStatsBatch(): isi
     * $stats[...][$field] lewat query batch dengan filter metric_type spesifik,
     * lalu jalankan SATU query batch fallback lagi (tanpa filter metric_type)
     * KHUSUS untuk journal yang masih 0 -- meniru pola fallback
     * getJournalCoreStats() aslinya, tapi tetap batched (bukan per-journal).
     */
    private function _fillCoreStatsBatch(array &$stats, string $field, int $assocType, string $metricTypeFilter): void {
        $journalIds = array_keys($stats);
        if (empty($journalIds)) return;

        $placeholders = implode(',', array_fill(0, count($journalIds), '?'));
        $result = $this->retrieve(
            "SELECT context_id, SUM(metric) AS t FROM metrics
             WHERE assoc_type = ? AND context_id IN ($placeholders) AND $metricTypeFilter
             GROUP BY context_id",
            array_merge([$assocType], $journalIds)
        );
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $stats[(int) $row['context_id']][$field] = (int) $row['t'];
                $result->MoveNext();
            }
            $result->Close();
        }

        $needFallback = [];
        foreach ($journalIds as $id) {
            if ($stats[$id][$field] === 0) {
                $needFallback[] = $id;
            }
        }
        if (empty($needFallback)) {
            return;
        }

        $fbPlaceholders = implode(',', array_fill(0, count($needFallback), '?'));
        $fbResult = $this->retrieve(
            "SELECT context_id, SUM(metric) AS t FROM metrics
             WHERE assoc_type = ? AND context_id IN ($fbPlaceholders)
             GROUP BY context_id",
            array_merge([$assocType], $needFallback)
        );
        if ($fbResult) {
            while (!$fbResult->EOF) {
                $row = $fbResult->GetRowAssoc(false);
                $stats[(int) $row['context_id']][$field] = (int) $row['t'];
                $fbResult->MoveNext();
            }
            $fbResult->Close();
        }
    }

    /**
     * Get core statistics for a journal, including total views and downloads.
     * @param int $journalId
     * @param array $dbStructure
     */
    public function getJournalCoreStats(int $journalId, array $dbStructure): array {
        $stats = ['views' => 0, 'downloads' => 0];

        // 1. Coba dari tabel metrics (Modern)
        if ($dbStructure['metricsTableExists']) {
            // VIEWS
            $views = $this->fetchScalar(
                "SELECT SUM(metric) AS total_views FROM metrics 
                 WHERE assoc_type = ? AND context_id = ? 
                 AND (metric_type = 'ojs::counter::article' OR metric_type LIKE '%view%')",
                [ASSOC_TYPE_ARTICLE, $journalId], 'total_views'
            );
            $stats['views'] = ($views !== null) ? (int) $views : 0;

            // Jika masih 0, coba tanpa filter metric_type
            if ($stats['views'] === 0) {
                $viewsFallback = $this->fetchScalar(
                    "SELECT SUM(metric) AS total_views FROM metrics WHERE assoc_type = ? AND context_id = ?",
                    [ASSOC_TYPE_ARTICLE, $journalId], 'total_views'
                );
                $stats['views'] = ($viewsFallback !== null) ? (int) $viewsFallback : 0;
            }

            // DOWNLOADS
            $downloads = $this->fetchScalar(
                "SELECT SUM(metric) AS total_downloads FROM metrics 
                 WHERE assoc_type = ? AND context_id = ? 
                 AND (metric_type = 'ojs::counter::galley' OR metric_type LIKE '%download%')",
                [ASSOC_TYPE_GALLEY, $journalId], 'total_downloads'
            );
            $stats['downloads'] = ($downloads !== null) ? (int) $downloads : 0;

            // Fallback downloads
            if ($stats['downloads'] === 0) {
                $downloadsFallback = $this->fetchScalar(
                    "SELECT SUM(metric) AS total_downloads FROM metrics WHERE assoc_type = ? AND context_id = ?",
                    [ASSOC_TYPE_GALLEY, $journalId], 'total_downloads'
                );
                $stats['downloads'] = ($downloadsFallback !== null) ? (int) $downloadsFallback : 0;
            }
        }

        // 2. Fallback ke Legacy Tables
        if ($stats['views'] === 0 && $dbStructure['articleStatsExists']) {
            $viewsLegacy = $this->fetchScalar(
                "SELECT SUM(avs.views) AS total_views FROM article_view_stats avs
                 JOIN articles a ON avs.article_id = a.article_id WHERE a.journal_id = ?",
                [$journalId], 'total_views'
            );
            $stats['views'] = ($viewsLegacy !== null) ? (int) $viewsLegacy : 0;
        }

        if ($stats['downloads'] === 0 && $dbStructure['galleyStatsExists']) {
            $downloadsLegacy = $this->fetchScalar(
                "SELECT SUM(agvs.views) AS total_downloads FROM article_galley_view_stats agvs
                 JOIN article_galleys ag ON agvs.galley_id = ag.galley_id
                 JOIN articles a ON ag.article_id = a.article_id WHERE a.journal_id = ?",
                [$journalId], 'total_downloads'
            );
            $stats['downloads'] = ($downloadsLegacy !== null) ? (int) $downloadsLegacy : 0;
        }

        return $stats;
    }

    /**
     * Get the count of unique authors for a journal.
     * @param int $journalId
     */
    public function getUniqueAuthorsCount(int $journalId): int {
        try {
            $count = $this->fetchScalar(
                "SELECT COUNT(DISTINCT a.author_id) AS total 
                 FROM authors a 
                 JOIN published_articles pa ON a.submission_id = pa.article_id
                 JOIN articles art ON pa.article_id = art.article_id
                 WHERE art.journal_id = ?", 
                [$journalId], 'total'
            );
            return ($count !== null) ? (int) $count : 0;
        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) {
                error_log("Wizdam DAO Error (Authors): " . $e->getMessage());
            }
            return 0;
        }
    }

    /**
     * Get publication counts for a journal, including articles and issues.
     * @param int $journalId
     */
    public function getPublicationCounts(int $journalId): array {
        $counts = ['totalArticles' => 0, 'totalIssues' => 0];

        $totalArticles = $this->fetchScalar(
            "SELECT COUNT(*) AS total FROM published_articles pa JOIN articles a ON pa.article_id = a.article_id WHERE a.journal_id = ?",
            [$journalId], 'total'
        );
        $counts['totalArticles'] = ($totalArticles !== null) ? (int) $totalArticles : 0;

        $totalIssues = $this->fetchScalar(
            "SELECT COUNT(*) AS total FROM issues WHERE journal_id = ? AND published = 1",
            [$journalId], 'total'
        );
        $counts['totalIssues'] = ($totalIssues !== null) ? (int) $totalIssues : 0;

        return $counts;
    }

    /**
     * Get acceptance and decline totals for a journal.
     * @param int $journalId
     * @param int $currentYear
     */
    public function getAcceptDeclineTotals(int $journalId, int $currentYear): array {
        $totals = ['reviewed' => 0, 'accepted' => 0, 'declined' => 0];

        $result = $this->retrieve(
            "SELECT status, COUNT(*) AS total FROM articles 
             WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status IN (2, 3, 4)
             GROUP BY status",
            [$journalId, $currentYear]
        );

        while ($row = $this->fetchRow($result)) {
            $status = (int) $row['status'];
            $count = (int) $row['total'];
            
            $totals['reviewed'] += $count;
            if ($status === 3) $totals['accepted'] = $count;
            if ($status === 4) $totals['declined'] = $count;

            $result->MoveNext();
        }
        if ($result) $result->Close();

        return $totals;
    }

    /**
     * Get the raw review timeline data for a journal.
     * @param int $journalId
     * @param int $currentYear
     * @param bool $hasEditDecisions
     */
    public function getReviewTimelineRaw(int $journalId, int $currentYear, bool $hasEditDecisions): array {
        $data = [
            'daysReview' => [],
            'daysPublication' => [],
            'daysFirstDecision' => [],
            'daysSubmissionToAcceptance' => [],
            'daysAcceptanceToPublication' => []
        ];

        // 1. Days to Review
        $resRev = $this->retrieve(
            "SELECT DATEDIFF(date_completed, date_notified) AS days 
             FROM review_assignments ra JOIN articles a ON ra.submission_id = a.article_id
             WHERE a.journal_id = ? AND ra.date_completed IS NOT NULL 
             AND ra.declined = 0 AND ra.cancelled = 0 AND YEAR(ra.date_notified) < ?",
            [$journalId, $currentYear]
        );
        while ($row = $this->fetchRow($resRev)) {
            if ((float)$row['days'] > 0) $data['daysReview'][] = (float)$row['days'];
            $resRev->MoveNext();
        }
        if ($resRev) $resRev->Close();

        // 2. Days to Publication
        $resPub = $this->retrieve(
            "SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days 
             FROM published_articles pa JOIN articles a ON pa.article_id = a.article_id 
             WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND pa.date_published IS NOT NULL",
            [$journalId, $currentYear]
        );
        while ($row = $this->fetchRow($resPub)) {
            if ((float)$row['days'] > 0) $data['daysPublication'][] = (float)$row['days'];
            $resPub->MoveNext();
        }
        if ($resPub) $resPub->Close();

        // 3. Edit Decisions (First Decision, Acceptance, dll)
        if ($hasEditDecisions) {
            $resDec = $this->retrieve(
                "SELECT a.article_id, a.date_submitted, pa.date_published, 
                        MIN(ed.date_decided) as first_decision, 
                        MAX(CASE WHEN ed.decision = 1 THEN ed.date_decided ELSE NULL END) as acceptance_date
                 FROM articles a
                 LEFT JOIN edit_decisions ed ON a.article_id = ed.article_id
                 LEFT JOIN published_articles pa ON a.article_id = pa.article_id
                 WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ?
                 GROUP BY a.article_id",
                [$journalId, $currentYear]
            );

            while ($row = $this->fetchRow($resDec)) {
                $subDate = strtotime((string) $row['date_submitted']);
                $firstDec = strtotime((string) $row['first_decision']);
                $accDate = strtotime((string) $row['acceptance_date']);
                $pubDate = strtotime((string) $row['date_published']);

                if ($subDate && $firstDec) {
                    $diff = round(($firstDec - $subDate) / 86400);
                    if ($diff > 0) $data['daysFirstDecision'][] = $diff;
                }
                if ($subDate && $accDate) {
                    $diff = round(($accDate - $subDate) / 86400);
                    if ($diff > 0) $data['daysSubmissionToAcceptance'][] = $diff;
                }
                if ($accDate && $pubDate) {
                    $diff = round(($pubDate - $accDate) / 86400);
                    if ($diff > 0) $data['daysAcceptanceToPublication'][] = $diff;
                }
                $resDec->MoveNext();
            }
            if ($resDec) $resDec->Close();
        }

        return $data;
    }
    
}
?>
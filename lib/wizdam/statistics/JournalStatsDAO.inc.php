<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/statistics/JournalStatsDAO.inc.php
 * 
 * Copyright (c) 2024-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalStatsDAO
 * @ingroup Statistics
 * 
 * @brief DAO Terpadu untuk seluruh Statistik Jurnal ScholarWizdam [WIZDAM EDITION]
 * @version 2.0 (Strict MVC & PHP 8+ Compliant)
 */

import('classes.db.DAO');

// Pastikan konstanta OJS tersedia
if (!defined('ASSOC_TYPE_JOURNAL')) define('ASSOC_TYPE_JOURNAL', 256);
if (!defined('ASSOC_TYPE_ISSUE')) define('ASSOC_TYPE_ISSUE', 257);
if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);

class JournalStatsDAO extends DAO {

    /**
     * [WIZDAM] - Diagnostik Skema Database
     * Mendeteksi keberadaan tabel modern (metrics) dan legacy (view_stats) dengan aman.
     * @return array 
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
                while (!$result->EOF) {
                    $info['metricsColumns'][] = (string) $result->fields[0];
                    $result->MoveNext();
                }
                $result->Close();
            }

            $result = $this->retrieve("SHOW TABLES LIKE 'article_view_stats'");
            $info['articleStatsExists'] = ($result->RecordCount() > 0);
            $result->Close();

            $result = $this->retrieve("SHOW TABLES LIKE 'article_galley_view_stats'");
            $info['galleyStatsExists'] = ($result->RecordCount() > 0);
            $result->Close();

            $result = $this->retrieve("SHOW TABLES LIKE 'authors'");
            $info['authorsTableExists'] = ($result->RecordCount() > 0);
            $result->Close();

            $result = $this->retrieve("SHOW TABLES LIKE 'edit_decisions'");
            $info['editDecisionsExists'] = ($result->RecordCount() > 0);
            $result->Close();

        } catch (Exception $e) {
            error_log("[WIZDAM DAO] Error checking tables: " . $e->getMessage());
        }

        return $info;
    }

    /**
     * [WIZDAM] - Pengganti getJournalStatsOptimized & getJournalStatsOriginal
     * Mengambil Views & Downloads dengan sistem Fallback berlapis dalam SQL.
     * @param int $journalId
     * @param array $dbStructure 
     * @return array
     */
    public function getJournalCoreStats(int $journalId, array $dbStructure): array {
        $stats = ['views' => 0, 'downloads' => 0];

        // 1. Coba dari tabel metrics (Modern)
        if ($dbStructure['metricsTableExists']) {
            // VIEWS
            $viewResult = $this->retrieve(
                "SELECT SUM(metric) AS total_views FROM metrics 
                 WHERE assoc_type = ? AND context_id = ? 
                 AND (metric_type = 'ojs::counter::article' OR metric_type LIKE '%view%')",
                [ASSOC_TYPE_ARTICLE, $journalId]
            );
            if ($viewResult && !$viewResult->EOF && $viewResult->fields['total_views']) {
                $stats['views'] = (int) $viewResult->fields['total_views'];
            }
            $viewResult->Close();

            // Jika masih 0, coba tanpa filter metric_type
            if ($stats['views'] === 0) {
                $viewFallback = $this->retrieve(
                    "SELECT SUM(metric) AS total_views FROM metrics WHERE assoc_type = ? AND context_id = ?",
                    [ASSOC_TYPE_ARTICLE, $journalId]
                );
                if ($viewFallback && !$viewFallback->EOF && $viewFallback->fields['total_views']) {
                    $stats['views'] = (int) $viewFallback->fields['total_views'];
                }
                $viewFallback->Close();
            }

            // DOWNLOADS
            $dlResult = $this->retrieve(
                "SELECT SUM(metric) AS total_downloads FROM metrics 
                 WHERE assoc_type = ? AND context_id = ? 
                 AND (metric_type = 'ojs::counter::galley' OR metric_type LIKE '%download%')",
                [ASSOC_TYPE_GALLEY, $journalId]
            );
            if ($dlResult && !$dlResult->EOF && $dlResult->fields['total_downloads']) {
                $stats['downloads'] = (int) $dlResult->fields['total_downloads'];
            }
            $dlResult->Close();

            // Fallback downloads
            if ($stats['downloads'] === 0) {
                $dlFallback = $this->retrieve(
                    "SELECT SUM(metric) AS total_downloads FROM metrics WHERE assoc_type = ? AND context_id = ?",
                    [ASSOC_TYPE_GALLEY, $journalId]
                );
                if ($dlFallback && !$dlFallback->EOF && $dlFallback->fields['total_downloads']) {
                    $stats['downloads'] = (int) $dlFallback->fields['total_downloads'];
                }
                $dlFallback->Close();
            }
        }

        // 2. Fallback ke Legacy Tables (Jika metrics gagal/kosong)
        if ($stats['views'] === 0 && $dbStructure['articleStatsExists']) {
            $result = $this->retrieve(
                "SELECT SUM(avs.views) AS total_views FROM article_view_stats avs
                 JOIN articles a ON avs.article_id = a.article_id WHERE a.journal_id = ?",
                [$journalId]
            );
            if ($result && !$result->EOF && $result->fields['total_views']) {
                $stats['views'] = (int) $result->fields['total_views'];
            }
            $result->Close();
        }

        if ($stats['downloads'] === 0 && $dbStructure['galleyStatsExists']) {
            $result = $this->retrieve(
                "SELECT SUM(agvs.views) AS total_downloads FROM article_galley_view_stats agvs
                 JOIN article_galleys ag ON agvs.galley_id = ag.galley_id
                 JOIN articles a ON ag.article_id = a.article_id WHERE a.journal_id = ?",
                [$journalId]
            );
            if ($result && !$result->EOF && $result->fields['total_downloads']) {
                $stats['downloads'] = (int) $result->fields['total_downloads'];
            }
            $result->Close();
        }

        return $stats;
    }

    /**
     * [WIZDAM] - Perombakan Total getJournalAuthorsCount!
     * Mengganti 3 lapis loop PHP dengan 1 kueri SQL yang sangat efisien.
     * @param int $journalId
     * @return int
     */
    public function getUniqueAuthorsCount(int $journalId): int {
        $count = 0;
        try {
            // Diadaptasi langsung dari PKPWizdamStats::getSiteWideStats
            $result = $this->retrieve(
                "SELECT COUNT(DISTINCT a.author_id) AS total 
                 FROM authors a 
                 JOIN published_articles pa ON a.submission_id = pa.article_id
                 JOIN articles art ON pa.article_id = art.article_id
                 WHERE art.journal_id = ?", 
                [$journalId]
            );

            if ($result !== false) {
                if (!$result->EOF && $result->fields['total'] !== null) {
                    $count = (int) $result->fields['total'];
                }
                $result->Close();
            }
        } catch (Exception $e) {
            if (Config::getVar('debug', 'log_errors')) {
                error_log("Wizdam DAO Error (Authors): " . $e->getMessage());
            }
        }
        return $count;
    }

    /**
     * [WIZDAM] - Menghitung jumlah Artikel dan Isu yang dipublikasikan
     * @param int $journalId
     * @return array
     */
    public function getPublicationCounts(int $journalId): array {
        $counts = ['totalArticles' => 0, 'totalIssues' => 0];

        $resArt = $this->retrieve(
            "SELECT COUNT(*) AS total FROM published_articles pa JOIN articles a ON pa.article_id = a.article_id WHERE a.journal_id = ?",
            [$journalId]
        );
        if ($resArt && !$resArt->EOF) $counts['totalArticles'] = (int) $resArt->fields['total'];
        $resArt->Close();

        $resIss = $this->retrieve(
            "SELECT COUNT(*) AS total FROM issues WHERE journal_id = ? AND published = 1",
            [$journalId]
        );
        if ($resIss && !$resIss->EOF) $counts['totalIssues'] = (int) $resIss->fields['total'];
        $resIss->Close();

        return $counts;
    }

    /**
     * [WIZDAM] - Metrik Penerimaan dan Penolakan (Accept/Decline Rates)
     * @param int $journalId
     * @param int $currentYear
     * @return array
     */
    public function getAcceptDeclineTotals(int $journalId, int $currentYear): array {
        $totals = ['reviewed' => 0, 'accepted' => 0, 'declined' => 0];

        $result = $this->retrieve(
            "SELECT status, COUNT(*) AS total FROM articles 
             WHERE journal_id = ? AND YEAR(date_submitted) < ? AND status IN (2, 3, 4)
             GROUP BY status",
            [$journalId, $currentYear]
        );

        while ($result && !$result->EOF) {
            $status = (int) $result->fields['status'];
            $count = (int) $result->fields['total'];
            
            $totals['reviewed'] += $count;
            if ($status === 3) $totals['accepted'] = $count;
            if ($status === 4) $totals['declined'] = $count;

            $result->MoveNext();
        }
        $result->Close();

        return $totals;
    }

    /**
     * [WIZDAM] - Mengambil Data Mentah Timeline untuk Dihitung Mediannya di Manager
     * Mengembalikan array berisi selisih hari.
     * @param int $journalId
     * @param int $currentYear
     * @param bool $hasEditDecisions
     * @return array
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
             FROM review_assignments ra
             JOIN articles a ON ra.submission_id = a.article_id
             WHERE a.journal_id = ? AND ra.date_completed IS NOT NULL 
             AND ra.declined = 0 AND ra.cancelled = 0 AND YEAR(ra.date_notified) < ?",
            [$journalId, $currentYear]
        );
        while ($resRev && !$resRev->EOF) {
            if ((float)$resRev->fields['days'] > 0) $data['daysReview'][] = (float)$resRev->fields['days'];
            $resRev->MoveNext();
        }
        $resRev->Close();

        // 2. Days to Publication
        $resPub = $this->retrieve(
            "SELECT DATEDIFF(pa.date_published, a.date_submitted) AS days 
             FROM published_articles pa JOIN articles a ON pa.article_id = a.article_id 
             WHERE a.journal_id = ? AND YEAR(a.date_submitted) < ? AND pa.date_published IS NOT NULL",
            [$journalId, $currentYear]
        );
        while ($resPub && !$resPub->EOF) {
            if ((float)$resPub->fields['days'] > 0) $data['daysPublication'][] = (float)$resPub->fields['days'];
            $resPub->MoveNext();
        }
        $resPub->Close();

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

            while ($resDec && !$resDec->EOF) {
                $subDate = strtotime((string) $resDec->fields['date_submitted']);
                $firstDec = strtotime((string) $resDec->fields['first_decision']);
                $accDate = strtotime((string) $resDec->fields['acceptance_date']);
                $pubDate = strtotime((string) $resDec->fields['date_published']);

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
            $resDec->Close();
        }

        return $data;
    }
}
?>
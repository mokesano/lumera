<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/hero/ArticleHeroDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ArticleHeroDAO
 * @ingroup wizdam_hero
 *
 * @brief [WIZDAM] Exclusive DAO untuk kandidat artikel Hero/Featured
 * homepage jurnal. Query mentah tetap dipakai (kebutuhan agregasi views+
 * downloads per-artikel, tidak tersedia langsung di DAO bawaan OJS), tapi
 * dibungkus rapi sebagai DAO -- pola sama persis dengan TrendsManagerDAO --
 * bukan lagi fungsi lepas dengan `$articleDao->retrieve()` dari luar DAO.
 *
 * [WIZDAM] Migrasi dari plugins/themes/{theme}/php/hero_futured/
 * article_hero.php (raw PHP di-include lewat {php} block Smarty) ke class
 * terintegrasi aplikasi, mengikuti pola integrasi yang sama dengan
 * TrendsManager/TrendsManagerDAO dan DoiCredentialService sebelumnya.
 */

import('lib.pkp.classes.db.DAO');
import('classes.article.Article');

if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);

class ArticleHeroDAO extends DAO {

    /**
     * Cek apakah tabel metrics + kolom tanggalnya tersedia -- dipakai untuk
     * menentukan apakah query bisa menyertakan agregasi views/downloads.
     * @return array{exists: bool, dateColumn: string}
     */
    private function _getMetricsAvailability(): array {
        $exists = false;
        try {
            $checkResult = $this->retrieve("SHOW TABLES LIKE 'metrics'");
            $exists = $checkResult && $checkResult->RecordCount() > 0;
            if ($checkResult) $checkResult->Close();
        } catch (Exception $e) {
            $exists = false;
        }

        $dateColumn = '';
        if ($exists) {
            $availableColumns = [];
            try {
                $columnsResult = $this->retrieve("SHOW COLUMNS FROM metrics");
                while ($columnsResult && !$columnsResult->EOF) {
                    $availableColumns[] = $columnsResult->fields[0]; // Expected type 'array|string|ArrayAccess'. Found 'bool'.
                    $columnsResult->MoveNext();
                }
                if ($columnsResult) $columnsResult->Close();
            } catch (Exception $e) {
                $availableColumns = [];
            }
            foreach (['day', 'load_time', 'entry_time', 'date'] as $candidate) {
                if (in_array($candidate, $availableColumns, true)) {
                    $dateColumn = $candidate;
                    break;
                }
            }
        }

        return ['exists' => $exists, 'dateColumn' => $dateColumn, 'columns' => $availableColumns ?? []];
    }

    /**
     * Kolom metrics untuk filter jurnal berbeda antar versi skema OJS --
     * 'context_id' di versi lebih baru, 'journal_id' di versi lama.
     * @param array $availableColumns
     * @return string
     */
    private function _getMetricsContextField(array $availableColumns): string {
        return in_array('context_id', $availableColumns, true) ? 'context_id' : 'journal_id';
    }

    /**
     * Hash ringan dari data artikel yang bisa berubah (untuk smart-cache
     * detection di ArticleHeroService) -- id, tanggal terbit, tanggal
     * modifikasi status, dan waktu update metrics terakhir.
     * @param int $journalId
     * @return string
     */
    public function getDataHash(int $journalId): string {
        $metrics = $this->_getMetricsAvailability();
        $hashData = [];

        if ($metrics['exists'] && $metrics['dateColumn'] !== '') {
            $dateColumn = $metrics['dateColumn'];
            $result = $this->retrieve(
                "SELECT a.article_id, a.date_status_modified, pa.date_published,
                        COALESCE(m.last_metric_update, '1970-01-01') as last_metric_update
                 FROM articles a
                 LEFT JOIN published_articles pa ON a.article_id = pa.article_id
                 LEFT JOIN issues i ON pa.issue_id = i.issue_id
                 LEFT JOIN (
                    SELECT assoc_id, MAX($dateColumn) as last_metric_update
                    FROM metrics
                    WHERE (assoc_type = ? OR assoc_type = ?) AND assoc_id IS NOT NULL
                    GROUP BY assoc_id
                 ) m ON a.article_id = m.assoc_id
                 WHERE a.journal_id = ?
                   AND a.status = ?
                   AND i.published = 1
                   AND pa.date_published IS NOT NULL
                 ORDER BY pa.date_published DESC
                 LIMIT 10",
                [(int) ASSOC_TYPE_ARTICLE, (int) ASSOC_TYPE_GALLEY, $journalId, (int) STATUS_PUBLISHED]
            );
        } else {
            $result = $this->retrieve(
                "SELECT a.article_id, a.date_status_modified, pa.date_published
                 FROM articles a
                 LEFT JOIN published_articles pa ON a.article_id = pa.article_id
                 LEFT JOIN issues i ON pa.issue_id = i.issue_id
                 WHERE a.journal_id = ?
                   AND a.status = ?
                   AND i.published = 1
                   AND pa.date_published IS NOT NULL
                 ORDER BY pa.date_published DESC
                 LIMIT 10",
                [$journalId, (int) STATUS_PUBLISHED]
            );
        }

        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $hashData[] = [
                    'id' => $row['article_id'],
                    'published' => $row['date_published'],
                    'modified' => $row['date_status_modified'],
                    'last_metric_update' => $row['last_metric_update'] ?? '1970-01-01',
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return md5(serialize($hashData));
    }

    /**
     * Ambil kandidat artikel dari VOLUME TERAKHIR jurnal (sudah termasuk
     * issue terakhir di dalamnya), lengkap dengan agregasi views+downloads.
     * @param int $journalId
     * @return array
     */
    public function getLatestVolumeArticles(int $journalId): array {
        $latestVolume = $this->_getLatestVolume($journalId);
        if ($latestVolume === null) {
            return [];
        }
        return $this->_getArticlesByVolumeFilter($journalId, 'i.volume = ?', [$latestVolume]);
    }

    /**
     * Ambil kandidat artikel dari 2 volume terakhir jurnal (dipakai kalau
     * volume terakhir saja kurang dari 5 artikel).
     * @param int $journalId
     * @return array
     */
    public function getMultipleVolumesArticles(int $journalId): array {
        $latestVolumes = $this->_getLatestVolumes($journalId, 2);
        if (empty($latestVolumes)) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($latestVolumes), '?'));
        return $this->_getArticlesByVolumeFilter($journalId, "i.volume IN ($placeholders)", $latestVolumes, 20);
    }

    //
    // HELPERS
    //
    /**
     * Get latest volume
     * @param int $journalId
     * @return int|string|null
     */
    private function _getLatestVolume(int $journalId) {
        $result = $this->retrieve(
            "SELECT i.volume
             FROM issues i
             WHERE i.journal_id = ? AND i.published = 1
             ORDER BY i.date_published DESC, i.volume DESC, i.number DESC
             LIMIT 1",
            [$journalId]
        );
        $volume = null;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $volume = $row['volume'];
            $result->Close();
        }
        return $volume;
    }

    /**
     * Get latest volumes
     * @param int $journalId
     * @param int $limit
     * @return array
     */
    private function _getLatestVolumes(int $journalId, int $limit): array {
        $result = $this->retrieve(
            "SELECT DISTINCT i.volume
             FROM issues i
             WHERE i.journal_id = ? AND i.published = 1
             ORDER BY i.volume DESC
             LIMIT $limit",
            [$journalId]
        );
        $volumes = [];
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $volumes[] = $row['volume'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $volumes;
    }

    /**
     * Query bersama untuk ambil artikel + agregasi views/downloads,
     * dengan filter volume yang fleksibel (satu volume atau beberapa).
     * @param int $journalId
     * @param string $volumeWhereClause Klausa WHERE untuk volume, mis. "i.volume = ?"
     * @param array $volumeParams Parameter untuk klausa volume
     * @param int|null $limit
     * @return array
     */
    private function _getArticlesByVolumeFilter(int $journalId, string $volumeWhereClause, array $volumeParams, ?int $limit = null): array {
        $metrics = $this->_getMetricsAvailability();
        $limitClause = $limit ? "LIMIT $limit" : '';

        if ($metrics['exists']) {
            $contextField = $this->_getMetricsContextField($metrics['columns']);
            $sql = "SELECT
                        a.article_id,
                        pa.date_published,
                        i.issue_id,
                        i.volume,
                        i.number,
                        COALESCE(views.total_views, 0) as total_views,
                        COALESCE(downloads.total_downloads, 0) as total_downloads
                    FROM articles a
                    JOIN published_articles pa ON a.article_id = pa.article_id
                    JOIN issues i ON pa.issue_id = i.issue_id
                    LEFT JOIN (
                        SELECT assoc_id, SUM(metric) as total_views
                        FROM metrics m
                        WHERE m.$contextField = ? AND m.assoc_type = ?
                        GROUP BY assoc_id
                    ) views ON a.article_id = views.assoc_id
                    LEFT JOIN (
                        SELECT ag.article_id, SUM(m.metric) as total_downloads
                        FROM metrics m
                        JOIN article_galleys ag ON m.assoc_id = ag.galley_id
                        WHERE m.$contextField = ? AND m.assoc_type = ?
                        GROUP BY ag.article_id
                    ) downloads ON a.article_id = downloads.article_id
                    WHERE a.journal_id = ?
                        AND a.status = ?
                        AND i.published = 1
                        AND pa.date_published IS NOT NULL
                        AND $volumeWhereClause
                    ORDER BY pa.date_published DESC, a.article_id DESC
                    $limitClause";

            $params = array_merge(
                [$journalId, (int) ASSOC_TYPE_ARTICLE, $journalId, (int) ASSOC_TYPE_GALLEY, $journalId, (int) STATUS_PUBLISHED],
                $volumeParams
            );
        } else {
            $sql = "SELECT
                        a.article_id,
                        pa.date_published,
                        i.issue_id,
                        i.volume,
                        i.number,
                        0 as total_views,
                        0 as total_downloads
                    FROM articles a
                    JOIN published_articles pa ON a.article_id = pa.article_id
                    JOIN issues i ON pa.issue_id = i.issue_id
                    WHERE a.journal_id = ?
                        AND a.status = ?
                        AND i.published = 1
                        AND pa.date_published IS NOT NULL
                        AND $volumeWhereClause
                    ORDER BY pa.date_published DESC, a.article_id DESC
                    $limitClause";

            $params = array_merge([$journalId, (int) STATUS_PUBLISHED], $volumeParams);
        }

        $result = $this->retrieve($sql, $params);

        $articles = [];
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $articles[] = [
                    'article_id' => (int) $row['article_id'],
                    'date_published' => $row['date_published'],
                    'issue_id' => (int) $row['issue_id'],
                    'volume' => $row['volume'],
                    'number' => $row['number'],
                    'total_views' => (int) $row['total_views'],
                    'total_downloads' => (int) $row['total_downloads'],
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $articles;
    }

}
?>
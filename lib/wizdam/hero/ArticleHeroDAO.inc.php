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
 * @brief DAO for featured/hero article candidates on the journal homepage.
 */

import('lib.pkp.classes.db.DAO');
import('classes.article.Article');

if (!defined('ASSOC_TYPE_ARTICLE')) {
    define('ASSOC_TYPE_ARTICLE', 259);
}
if (!defined('ASSOC_TYPE_GALLEY')) {
    define('ASSOC_TYPE_GALLEY', 258);
}

class ArticleHeroDAO extends DAO {

    /**
     * Check if metrics table and date column are available.
     * @return array{exists: bool, dateColumn: string, columns: array}
     */
    private function _getMetricsAvailability(): array {
        $exists = false;
        try {
            $checkResult = $this->retrieve("SHOW TABLES LIKE 'metrics'");
            $exists = $checkResult && $checkResult->RecordCount() > 0;
            if ($checkResult) {
                $checkResult->Close();
            }
        } catch (Exception $e) {
            $exists = false;
        }

        $dateColumn = '';
        $availableColumns = [];
        if ($exists) {
            try {
                $columnsResult = $this->retrieve("SHOW COLUMNS FROM metrics");
                if ($columnsResult) {
                    while (!$columnsResult->EOF) {
                        $row = $columnsResult->GetRowAssoc(false);
                        if (isset($row['Field'])) {
                            $availableColumns[] = strtolower((string) $row['Field']);
                        }
                        $columnsResult->MoveNext();
                    }
                    $columnsResult->Close();
                }
            } catch (Exception $e) {
                $availableColumns = [];
            }
            
            foreach (['day', 'load_time', 'entry_time', 'date'] as $candidate) {
                if (in_array(strtolower($candidate), $availableColumns, true)) {
                    $dateColumn = $candidate;
                    break;
                }
            }
        }

        return [
            'exists' => $exists, 
            'dateColumn' => $dateColumn, 
            'columns' => $availableColumns
        ];
    }

    /**
     * Get metrics context field name based on schema version.
     * @param array $availableColumns
     * @return string
     */
    private function _getMetricsContextField(array $availableColumns): string {
        $lowerColumns = array_map('strtolower', $availableColumns);

        if (in_array('context_id', $lowerColumns, true)) {
            return 'context_id';
        }

        if (in_array('journal_id', $lowerColumns, true)) {
            return 'journal_id';
        }

        return 'context_id';
    }

    /**
     * Generate a lightweight hash of article data for smart-cache detection.
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
                    'id' => (int) $row['article_id'],
                    'published' => (string) $row['date_published'],
                    'modified' => (string) $row['date_status_modified'],
                    'last_metric_update' => (string) ($row['last_metric_update'] ?? '1970-01-01'),
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return md5(serialize($hashData));
    }

    /**
     * Get candidate articles from the latest volume.
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
     * Get candidate articles from the latest 2 volumes.
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

    /**
     * Get latest volume.
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
     * Get latest volumes.
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
     * Fetch articles with views/downloads aggregation and flexible volume filter.
     * @param int $journalId
     * @param string $volumeWhereClause
     * @param array $volumeParams
     * @param int|null $limit
     * @return array
     */
    private function _getArticlesByVolumeFilter(int $journalId, string $volumeWhereClause, array $volumeParams, ?int $limit = null): array {
        $metrics = $this->_getMetricsAvailability();
        $limitClause = $limit !== null ? "LIMIT " . (int) $limit : '';

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
                    'date_published' => (string) $row['date_published'],
                    'issue_id' => (int) $row['issue_id'],
                    'volume' => (string) $row['volume'],
                    'number' => (string) $row['number'],
                    'total_views' => (int) $row['total_views'],
                    'total_downloads' => (int) $row['total_downloads'],
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $articles;
    }

    /**
     * Get cover page alt text for a given article.
     * @param int $articleId
     * @return string
     */
    public function getArticleCoverPageAltText(int $articleId): string {
        $result = $this->retrieve(
            'SELECT setting_value FROM article_settings WHERE article_id = ? AND setting_name = ? LIMIT 1',
            [$articleId, 'coverPageAltText']
        );
        $altText = '';
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $altText = isset($row['setting_value']) ? (string) $row['setting_value'] : '';
            $result->Close();
        }
        return $altText;
    }

}
?>
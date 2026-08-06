<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/trends/TrendsManagerDAO.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TrendsManagerDAO
 * @ingroup Statistics
 * 
 * @brief [LUMERA] - Exclusive DAO for Most Popular metrics.
 * Menangani pengambilan data statistik performa artikel dengan performa tinggi.
 */

import('lib.pkp.classes.db.DAO');
import('classes.article.Article');

if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);
if (!defined('ASSOC_TYPE_GALLEY')) define('ASSOC_TYPE_GALLEY', 258);

class TrendsManagerDAO extends DAO {

    /**
     * Mengambil artikel terpopuler dalam sebuah jurnal (Journal Level)
     * @param int $journalId
     * @param int $limit
     * @return array [$articleId => ['views' => int totalViews, 'date_published' => string]]
     */
    public function getMostPopularArticles(int $journalId, int $limit = 10): array {
        $sql = "SELECT a.article_id, SUM(m.metric) as total_views, pa.date_published
                FROM metrics m
                JOIN articles a ON m.assoc_id = a.article_id
                JOIN published_articles pa ON a.article_id = pa.article_id
                JOIN issues i ON pa.issue_id = i.issue_id
                WHERE m.assoc_type = ? 
                AND a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                GROUP BY a.article_id, pa.date_published
                HAVING SUM(m.metric) > 0
                ORDER BY total_views DESC, pa.date_published DESC
                LIMIT ?";
                
        $result = $this->retrieve($sql, [(int)ASSOC_TYPE_ARTICLE, $journalId, (int)STATUS_PUBLISHED, $limit * 2]);
        
        $viewsData = [];
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $viewsData[(int)$row['article_id']] = [
                    'views' => (int)$row['total_views'],
                    'date_published' => (string)$row['date_published']
                ];
                $result->MoveNext();
            }
            $result->Close();
        }
        
        return $viewsData;
    }

    /**
     * Mengambil artikel terpopuler dari top jurnal di sistem (Site Level)
     * @param int $journalLimit
     */
    public function getSiteLevelTopArticles(int $journalLimit = 4): array {
        // 1. Dapatkan Jurnal Terpopuler
        $sqlJournals = "SELECT a.journal_id, SUM(m.metric) as total_journal_views
                        FROM metrics m
                        JOIN articles a ON m.assoc_id = a.article_id
                        WHERE m.assoc_type = ? AND a.status = ?
                        GROUP BY a.journal_id
                        ORDER BY total_journal_views DESC
                        LIMIT ?";
                        
        $journalsResult = $this->retrieve($sqlJournals, [(int)ASSOC_TYPE_ARTICLE, (int)STATUS_PUBLISHED, $journalLimit]);
        
        $siteLevelArticles = [];
        
        if ($journalsResult && !$journalsResult->EOF) {
            while (!$journalsResult->EOF) {
                $row = $journalsResult->GetRowAssoc(false);
                $journalId = (int)$row['journal_id'];
                
                // 2. Ambil 1 artikel terpopuler dari jurnal ini
                $topArticleData = $this->getMostPopularArticles($journalId, 1);
                
                if (!empty($topArticleData)) {
                    $articleId = array_key_first($topArticleData); 
                    $siteLevelArticles[$articleId] = $topArticleData[$articleId];
                }
                
                $journalsResult->MoveNext();
            }
            $journalsResult->Close();
        }
        
        return $siteLevelArticles;
    }

    /**
     * Mengambil artikel dengan download terbanyak dalam sebuah jurnal (Journal Level).
     * Struktur hasil PERSIS sama dengan getMostPopularArticles() (key 'views')
     * supaya bisa dipakai ulang langsung oleh WizdamTrendsManager::_formatMicroPayload()
     * tanpa perubahan apapun -- di sini 'views' berisi ANGKA DOWNLOAD, bukan views asli.
     * @param int $journalId
     * @param int $limit
     * @return array [$articleId => ['views' => int totalDownloads, 'date_published' => string]]
     */
    public function getMostDownloadedArticles(int $journalId, int $limit = 10): array {
        $sql = "SELECT ag.article_id, SUM(m.metric) as total_downloads, pa.date_published
                FROM metrics m
                JOIN article_galleys ag ON m.assoc_id = ag.galley_id
                JOIN articles a ON ag.article_id = a.article_id
                JOIN published_articles pa ON a.article_id = pa.article_id
                JOIN issues i ON pa.issue_id = i.issue_id
                WHERE m.assoc_type = ?
                AND a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                GROUP BY ag.article_id, pa.date_published
                HAVING SUM(m.metric) > 0
                ORDER BY total_downloads DESC, pa.date_published DESC
                LIMIT ?";

        $result = $this->retrieve($sql, [(int)ASSOC_TYPE_GALLEY, $journalId, (int)STATUS_PUBLISHED, $limit * 2]);

        $downloadsData = [];
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $downloadsData[(int)$row['article_id']] = [
                    'views' => (int)$row['total_downloads'],
                    'date_published' => (string)$row['date_published']
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $downloadsData;
    }

    /**
     * Mengambil artikel dengan download terbanyak dari top jurnal di sistem (Site Level).
     * @param int $journalLimit
     * @return array [$articleId => ['views' => int totalDownloads, 'date_published' => string]]
     */
    public function getSiteLevelTopDownloadedArticles(int $journalLimit = 4): array {
        $sqlJournals = "SELECT ag.article_id, a.journal_id, SUM(m.metric) as total_journal_downloads
                        FROM metrics m
                        JOIN article_galleys ag ON m.assoc_id = ag.galley_id
                        JOIN articles a ON ag.article_id = a.article_id
                        WHERE m.assoc_type = ? AND a.status = ?
                        GROUP BY a.journal_id
                        ORDER BY total_journal_downloads DESC
                        LIMIT ?";

        $journalsResult = $this->retrieve($sqlJournals, [(int)ASSOC_TYPE_GALLEY, (int)STATUS_PUBLISHED, $journalLimit]);

        $siteLevelArticles = [];

        if ($journalsResult && !$journalsResult->EOF) {
            while (!$journalsResult->EOF) {
                $row = $journalsResult->GetRowAssoc(false);
                $journalId = (int)$row['journal_id'];

                $topArticleData = $this->getMostDownloadedArticles($journalId, 1);

                if (!empty($topArticleData)) {
                    $articleId = array_key_first($topArticleData);
                    $siteLevelArticles[$articleId] = $topArticleData[$articleId];
                }

                $journalsResult->MoveNext();
            }
            $journalsResult->Close();
        }

        return $siteLevelArticles;
    }

    /**
     * Mengambil artikel dengan sitasi terbanyak dalam sebuah jurnal (Journal Level).
     * Membaca angka yang SUDAH TERSIMPAN di article_settings (setting_name =
     * 'citationCount') -- diisi oleh scheduled task terpisah (belum dibangun),
     * BUKAN memanggil API sitasi eksternal secara langsung di sini. Struktur
     * hasil PERSIS sama dengan getMostPopularArticles() (key 'views') supaya
     * bisa dipakai ulang langsung oleh TrendsManager::_formatMicroPayload().
     * @param int $journalId
     * @param int $limit
     * @return array [$articleId => ['views' => int totalCitations, 'date_published' => string]]
     */
    public function getMostCitedArticles(int $journalId, int $limit = 10): array {
        $sql = "SELECT a.article_id, CAST(ast.setting_value AS UNSIGNED) as total_citations, pa.date_published
                FROM article_settings ast
                JOIN articles a ON ast.article_id = a.article_id
                JOIN published_articles pa ON a.article_id = pa.article_id
                JOIN issues i ON pa.issue_id = i.issue_id
                WHERE ast.setting_name = 'citationCount'
                AND a.journal_id = ?
                AND a.status = ?
                AND i.published = 1
                AND pa.date_published IS NOT NULL
                AND CAST(ast.setting_value AS UNSIGNED) > 0
                ORDER BY total_citations DESC, pa.date_published DESC
                LIMIT ?";

        $result = $this->retrieve($sql, [$journalId, (int)STATUS_PUBLISHED, $limit * 2]);

        $citationsData = [];
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $citationsData[(int)$row['article_id']] = [
                    'views' => (int)$row['total_citations'],
                    'date_published' => (string)$row['date_published']
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $citationsData;
    }

    /**
     * Mengambil artikel dengan sitasi terbanyak dari top jurnal di sistem (Site Level).
     * @param int $journalLimit
     * @return array [$articleId => ['views' => int totalCitations, 'date_published' => string]]
     */
    public function getSiteLevelTopCitedArticles(int $journalLimit = 4): array {
        $sqlJournals = "SELECT a.journal_id, SUM(CAST(ast.setting_value AS UNSIGNED)) as total_journal_citations
                        FROM article_settings ast
                        JOIN articles a ON ast.article_id = a.article_id
                        WHERE ast.setting_name = 'citationCount' AND a.status = ?
                        GROUP BY a.journal_id
                        ORDER BY total_journal_citations DESC
                        LIMIT ?";

        $journalsResult = $this->retrieve($sqlJournals, [(int)STATUS_PUBLISHED, $journalLimit]);

        $siteLevelArticles = [];

        if ($journalsResult && !$journalsResult->EOF) {
            while (!$journalsResult->EOF) {
                $row = $journalsResult->GetRowAssoc(false);
                $journalId = (int)$row['journal_id'];

                $topArticleData = $this->getMostCitedArticles($journalId, 1);

                if (!empty($topArticleData)) {
                    $articleId = array_key_first($topArticleData);
                    $siteLevelArticles[$articleId] = $topArticleData[$articleId];
                }

                $journalsResult->MoveNext();
            }
            $journalsResult->Close();
        }

        return $siteLevelArticles;
    }

}
?>
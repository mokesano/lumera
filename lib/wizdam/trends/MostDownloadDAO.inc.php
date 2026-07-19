<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/trends/MostDownloadDAO.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MostDownloadDAO
 * @ingroup Statistics
 * 
 * @brief [LUMERA] - Exclusive DAO for Most Download metrics.
 * Menangani pengambilan data statistik performa artikel dengan performa tinggi.
 */

import('lib.pkp.classes.db.DAO');
import('classes.article.Article');

if (!defined('ASSOC_TYPE_ARTICLE')) define('ASSOC_TYPE_ARTICLE', 259);

class MostDownloadDAO extends DAO {

    /**
     * Mengambil artikel download dalam sebuah jurnal (Journal Level)
     * @param int $journalId
     * @param int $limit
     */
    public function getMostDownloadArticles(int $journalId, int $limit = 10): array {
        $sql = "SELECT a.article_id, SUM(m.metric) as total_download, pa.date_published
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
                ORDER BY total_download DESC, pa.date_published DESC
                LIMIT ?";
                
        $result = $this->retrieve($sql, [(int)ASSOC_TYPE_ARTICLE, $journalId, (int)STATUS_PUBLISHED, $limit * 2]);
        
        $downloadData = [];
        if ($result && !$result->EOF) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $downloadData[(int)$row['article_id']] = [
                    'download' => (int)$row['total_download'],
                    'date_published' => (string)$row['date_published']
                ];
                $result->MoveNext();
            }
            $result->Close();
        }
        
        return $downloadData;
    }

    /**
     * Mengambil artikel download dari top jurnal di sistem (Site Level)
     * @param int $journalLimit
     */
    public function getSiteLevelTopArticles(int $journalLimit = 4): array {
        // 1. Dapatkan Jurnal Terpopuler
        $sqlJournals = "SELECT a.journal_id, SUM(m.metric) as total_journal_download
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
                $topArticleData = $this->getMostDownloadArticles($journalId, 1);
                
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
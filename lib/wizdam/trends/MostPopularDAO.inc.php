<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/trends/MostPopularDAO.inc.php
 *
 * [WIZDAM] - Exclusive DAO for Most Popular metrics.
 * Menangani pengambilan data statistik performa artikel dengan performa tinggi.
 */

import('lib.pkp.classes.db.DAO');

class MostPopularDAO extends DAO {

    /**
     * Mengambil artikel terpopuler dalam sebuah jurnal (Journal Level)
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
}
?>
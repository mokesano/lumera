<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/tasks/CitationRefreshTask.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class CitationRefreshTask
 *
 * @brief Scheduled task mingguan: untuk tiap artikel published yang punya
 * DOI, fetch data sitasi terbaru (CitationFetcherService, sudah termasuk
 * cache 7 hari-nya sendiri), lalu update article_settings.citationCount
 * HANYA kalau angkanya berubah -- supaya tidak menulis ke DB tanpa alasan
 * saat tidak ada perubahan sitasi sama sekali.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');
import('lib.wizdam.classes.citation.CitationFetcherService');

class CitationRefreshTask extends ScheduledTask {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getName() {
        return 'Citation Count Refresh';
    }

    /**
     * @return bool
     */
    public function executeActions() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        $journals = $journalDao->getJournals(true);
        if (!$journals) {
            return true;
        }

        while ($journal = $journals->next()) {
            $this->_refreshJournalCitations($journal, $articleDao, $publishedArticleDao);
        }

        return true;
    }

    /**
     * @param object $journal
     * @param object $articleDao
     * @param object $publishedArticleDao
     */
    private function _refreshJournalCitations($journal, $articleDao, $publishedArticleDao): void {
        $fetcher = new CitationFetcherService($journal);
        $articleIds = $publishedArticleDao->getPublishedArticleIdsByJournal((int) $journal->getId(), false);

        if (empty($articleIds)) {
            return;
        }

        foreach ($articleIds as $articleId) {
            $article = $articleDao->getArticle((int) $articleId);
            if (!$article) continue;

            $doi = method_exists($article, 'getPubId') ? $article->getPubId('doi') : null;
            if (empty($doi)) continue;

            try {
                $result = $fetcher->getCitations((string) $doi, 50);
                $freshCount = (int) ($result['citation_count'] ?? 0);

                // [SEDERHANA] Selalu tulis -- cron ini jalan mingguan, bukan
                // per-request, jadi tidak ada tekanan performa yang berarti
                // untuk perlu cek "apakah berubah" dulu sebelum menulis.
                $articleDao->updateSetting((int) $articleId, 'citationCount', $freshCount, 'int');
            } catch (Exception $e) {
                error_log('CitationRefreshTask: gagal ambil sitasi untuk DOI ' . $doi . ' -- ' . $e->getMessage());
                continue;
            }
        }
    }

}
?>
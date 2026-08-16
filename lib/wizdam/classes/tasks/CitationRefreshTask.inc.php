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
 * @brief Scheduled task to refresh citation counts for published articles with DOIs.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');
import('lib.wizdam.classes.citation.CitationFetcherService');

class CitationRefreshTask extends ScheduledTask {

    // [WIZDAM] Anggaran waktu aman untuk SATU eksekusi --
    private const TIME_BUDGET_SECONDS = 45;

    // Ditetapkan saat executeActions() mulai, dibaca lintas jurnal.
    /** @var int */
    private $startTime = 0;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get the name of the task.
     * @return string
     */
    public function getName() {
        return 'Citation Count Refresh';
    }

    /**
     * Execute the scheduled task.
     * @return bool
     */
    public function executeActions() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');

        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        $this->startTime = time();

        $journalsFactory = $journalDao->getJournals(true);
        if (!$journalsFactory) {
            return true;
        }

        $journals = [];
        while ($journal = $journalsFactory->next()) {
            $journals[] = $journal;
        }
        if (empty($journals)) {
            return true;
        }

        shuffle($journals);

        foreach ($journals as $journal) {
            if ((time() - $this->startTime) >= self::TIME_BUDGET_SECONDS) {
                error_log('CitationRefreshTask: anggaran waktu tercapai, sisa jurnal ditunda ke eksekusi berikutnya.');
                break;
            }
            $this->_refreshJournalCitations($journal, $articleDao, $publishedArticleDao);
        }

        return true;
    }

    /**
     * Refresh citations for a specific journal.
     * @param object $journal
     * @param object $articleDao
     * @param object $publishedArticleDao
     * @return void
     */
    private function _refreshJournalCitations($journal, $articleDao, $publishedArticleDao): void {
        $fetcher = new CitationFetcherService($journal);
        $articleIds = $publishedArticleDao->getPublishedArticleIdsByJournal((int) $journal->getId(), false);

        if (empty($articleIds)) {
            return;
        }

        $articleIds = array_values($articleIds);
        shuffle($articleIds);

        $processed = 0;
        foreach ($articleIds as $articleId) {
            if ((time() - $this->startTime) >= self::TIME_BUDGET_SECONDS) {
                error_log(sprintf(
                    'CitationRefreshTask: anggaran waktu tercapai di jurnal ID %d -- %d artikel diproses, sisanya ditunda.',
                    (int) $journal->getId(), $processed
                ));
                return;
            }
            $article = $articleDao->getArticle((int) $articleId);
            if (!$article) continue;

            $doi = method_exists($article, 'getPubId') ? $article->getPubId('doi') : null;
            if (empty($doi)) continue;

            try {
                $result = $fetcher->getCitations((string) $doi, 50);
                $freshCount = (int) ($result['citation_count'] ?? 0);
                $articleDao->updateSetting((int) $articleId, 'citationCount', $freshCount, 'int');
                $processed++;
            } catch (Exception $e) {
                error_log('CitationRefreshTask: gagal ambil sitasi untuk DOI ' . $doi . ' -- ' . $e->getMessage());
                continue;
            }
        }
    }

}
?>
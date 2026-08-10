<?php
declare(strict_types=1);

/**
 * File: pages/article/ArticleMetricsHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Menangani halaman "metrics" kustom untuk sebuah artikel:
 *        URL: article/view/<articleId>/metrics
 *
 * @class MetricsHandler
 * @extends ArticleHandler
 */

import('pages.article.ArticleHandler');
import('plugins.generic.usageStats.UsageStatsReportPlugin');

class ArticleMetricsHandler extends ArticleHandler {

    /** Jumlah hari default untuk data grafik */
    const CHART_RANGE_DAYS = 30;

    /**
     * Constructor
     * @param PKPRequest|null $request
     */
    public function __construct($request = null) {
        parent::__construct($request);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MetricsHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Titik masuk utama untuk URL: article/view/<articleId>/metrics
     * @param array $args
     * @param PKPRequest $request
     */
    public function metrics($args = [], $request = null) {
        if ($request === null) {
            $request = Application::get()->getRequest();
        }

        if (!$this->article) {
            $articleId = $args[0] ?? 0;
            $this->validate($request, $articleId);
        }

        $article = $this->article;
        $journal = $this->journal;
        $issue   = $this->issue;

        if (!$article) {
            $request->redirect(null, 'index');
            return;
        }

        $articleId = (int) $article->getId();
        $journalId = (int) $journal->getId();

        $this->setupTemplate($request);
        $templateMgr = TemplateManager::getManager($request);

        $templateMgr->assign([
            'article' => $article,
            'issue'   => $issue,
            'journal' => $journal,
            'doi'     => $article->getPubId('doi'),
        ]);

        // --- Ringkasan total ---
        [$totalViews, $totalDownloads] = $this->getMetricsSummary($articleId, $journalId);
        $templateMgr->assign([
            'totalViews'     => $totalViews,
            'totalDownloads' => $totalDownloads,
        ]);

        // --- Data grafik (N hari terakhir) ---
        $templateMgr->assign([
            'viewsChartData'     => $this->getDailyChartData($articleId, $journalId, ASSOC_TYPE_ARTICLE),
            'downloadsChartData' => $this->getDailyChartData($articleId, $journalId, ASSOC_TYPE_GALLEY),
        ]);

        // --- Daftar kutipan (cited-by) -- LENGKAP, tidak dibatasi 7 seperti
        // panel di halaman artikel. Pakai elemen yang SAMA (citedby_doi.tpl)
        // dengan halaman artikel -- baca cache saja, tidak memicu fetch
        // jaringan (itu tanggung jawab CitationRefreshTask mingguan).
        $citingArticles = [];
        $citationCount = 0;
        $doi = $article->getPubId('doi');
        if (!empty($doi)) {
            import('lib.wizdam.classes.citation.CitationFetcherService');
            $citationFetcher = new CitationFetcherService($journal);
            $citationData = $citationFetcher->getCachedCitations((string) $doi);
            if ($citationData !== null) {
                $citationCount = (int) ($citationData['citation_count'] ?? 0);
                $citingArticles = $citationData['citing_articles'] ?? [];
            }
        }
        $templateMgr->assign([
            'citingArticles' => $citingArticles,
            'citationCount'  => $citationCount,
        ]);

        $templateMgr->assign('statsLastUpdated', date('l, d M Y H:i:s T'));

        $templateMgr->display('article/metrics.tpl');
    }

    /**
     * Total abstract views & galley downloads sepanjang waktu untuk 1 artikel.
     * Memakai Application::getMetrics() -- API resmi, sama seperti yang
     * dipakai Journal::getMetrics() dan Application::getPrimaryMetricByAssoc().
     *
     * [WIZDAM BUGFIX] Sebelumnya filter memakai STATISTICS_DIMENSION_
     * SUBMISSION_ID untuk KEDUA jenis metrik (views ARTIKEL maupun
     * downloads GALLEY) -- ini SALAH untuk galley. Bandingkan dengan
     * Application::getPrimaryMetricByAssoc() (dipakai
     * PublishedArticle::getViews()/ArticleGalley::getViews(), sumber
     * angka yang tampil di halaman artikel sendiri lewat heading.tpl):
     * dimension yang benar adalah STATISTICS_DIMENSION_ASSOC_ID, BUKAN
     * SUBMISSION_ID. Untuk assoc_type ARTICLE, assoc_id KEBETULAN sama
     * dengan article_id (jadi views artikel sebelumnya masih terlihat
     * benar) -- tapi untuk assoc_type GALLEY, assoc_id adalah ID GALLEY
     * itu sendiri (BUKAN article_id), sehingga filter submission_id yang
     * lama tidak pernah cocok dengan data downloads yang sebenarnya.
     * Satu artikel bisa punya BEBERAPA galley (PDF, HTML, dst) -- semua
     * galley_id dikumpulkan dulu, lalu di-filter sekaligus lewat array
     * (getMetrics() mendukung ini, menghasilkan klausa SQL IN (...)),
     * supaya total downloads benar-benar menjumlahkan SEMUA galley
     * artikel, bukan cuma satu atau tidak ada sama sekali.
     * @param int $articleId
     * @param int $journalId
     * @return array [totalViews, totalDownloads]
     */
    protected function getMetricsSummary(int $articleId, int $journalId): array {
        $application = Application::get();

        $baseFilter = [
            STATISTICS_DIMENSION_CONTEXT_ID => $journalId,
        ];

        $viewsFilter = $baseFilter + [
            STATISTICS_DIMENSION_ASSOC_ID   => $articleId,
            STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_ARTICLE,
        ];
        $viewsData = $application->getMetrics(OJS_METRIC_TYPE_COUNTER, [], $viewsFilter);
        $totalViews = (is_array($viewsData) && isset($viewsData[0][STATISTICS_METRIC]))
            ? (int) $viewsData[0][STATISTICS_METRIC] : 0;

        $galleyIds = $this->_getGalleyIdsForArticle($articleId);
        $totalDownloads = 0;
        if (!empty($galleyIds)) {
            $downloadsFilter = $baseFilter + [
                STATISTICS_DIMENSION_ASSOC_ID   => $galleyIds,
                STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_GALLEY,
            ];
            $downloadsData = $application->getMetrics(OJS_METRIC_TYPE_COUNTER, [], $downloadsFilter);
            $totalDownloads = (is_array($downloadsData) && isset($downloadsData[0][STATISTICS_METRIC]))
                ? (int) $downloadsData[0][STATISTICS_METRIC] : 0;
        }

        return [$totalViews, $totalDownloads];
    }

    /**
     * Ambil semua ID galley milik sebuah artikel -- dipakai untuk filter
     * ASSOC_ID metrik downloads (satu artikel bisa punya banyak galley).
     * @param int $articleId
     * @return int[]
     */
    protected function _getGalleyIdsForArticle(int $articleId): array {
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleys = $galleyDao->getGalleysByArticle($articleId);

        $ids = [];
        if (is_array($galleys)) {
            foreach ($galleys as $galley) {
                $ids[] = (int) $galley->getId();
            }
        }
        return $ids;
    }

    /**
     * Deret waktu harian (untuk grafik) dalam N hari terakhir.
     *
     * [WIZDAM BUGFIX] Sama seperti getMetricsSummary() -- dimension yang
     * benar adalah ASSOC_ID (bukan SUBMISSION_ID), dan untuk galley
     * (downloads) perlu filter ASSOC_ID berupa array SEMUA galley
     * artikel, bukan article_id itu sendiri.
     * @param int $articleId
     * @param int $journalId
     * @param int $assocType ASSOC_TYPE_ARTICLE (views) atau ASSOC_TYPE_GALLEY (downloads)
     * @return array [['date' => 'YYYYMMDD', 'count' => int], ...]
     */
    protected function getDailyChartData(int $articleId, int $journalId, int $assocType): array {
        $application = Application::get();

        $assocIdFilterValue = ($assocType === ASSOC_TYPE_GALLEY)
            ? $this->_getGalleyIdsForArticle($articleId)
            : $articleId;

        if ($assocType === ASSOC_TYPE_GALLEY && empty($assocIdFilterValue)) {
            return [];
        }

        $filters = [
            STATISTICS_DIMENSION_CONTEXT_ID => $journalId,
            STATISTICS_DIMENSION_ASSOC_ID   => $assocIdFilterValue,
            STATISTICS_DIMENSION_ASSOC_TYPE => $assocType,
            STATISTICS_DIMENSION_DAY => [
                'from' => date('Ymd', strtotime('-' . self::CHART_RANGE_DAYS . ' days')),
                'to'   => date('Ymd'),
            ],
        ];

        $rows = $application->getMetrics(
            OJS_METRIC_TYPE_COUNTER,
            [STATISTICS_DIMENSION_DAY],
            $filters,
            [STATISTICS_DIMENSION_DAY => 'ASC']
        );

        $series = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                $series[] = [
                    'date'  => $row[STATISTICS_DIMENSION_DAY] ?? null,
                    'count' => isset($row[STATISTICS_METRIC]) ? (int) $row[STATISTICS_METRIC] : 0,
                ];
            }
        }
        return $series;
    }
    
}
?>
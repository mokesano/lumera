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

                // [WIZDAM] Sumber kutipan diteruskan ke template supaya
                // kalimat "Citation counts are provided by ..." TIDAK perlu
                // di-hardcode. Hanya sumber yang BENAR-BENAR menyumbang
                // (count > 0) yang disebut -- hasil kombinasi + deduplikasi
                // antar-API oleh CitationFetcherService.
                $rawSources = $citationData['citation_sources'] ?? [];
                $sourceLabels = [
                    'crossref_count'        => 'CrossRef',
                    'openalex_count'        => 'OpenAlex',
                    'dimensions_count'      => 'Dimensions',
                    'opencitations_count'   => 'OpenCitations',
                    'semanticscholar_count' => 'Semantic Scholar',
                ];
                // Dua bentuk disiapkan:
                //  - $activeSources        : "OpenAlex", "Dimensions"      (nama saja)
                //  - $activeSourcesDetail  : "OpenAlex (5)", "Dimensions (2)" (dengan angka)
                // Template memilih salah satu; keduanya sudah berupa STRING siap
                // cetak, sehingga tidak ada array yang tercetak sebagai "Array".
                $activeSources = [];
                $activeSourcesDetail = [];
                foreach ($sourceLabels as $key => $label) {
                    $n = (int) ($rawSources[$key] ?? 0);
                    if ($n > 0) {
                        $activeSources[] = $label;
                        $activeSourcesDetail[] = $label . ' (' . $n . ')';
                    }
                }
                // Rangkai jadi kalimat berbahasa Inggris yang benar:
                // 1 sumber  -> "OpenAlex"
                // 2 sumber  -> "OpenAlex and Dimensions"        (tanpa koma)
                // 3+ sumber -> "CrossRef, OpenAlex, and Dimensions" (koma Oxford)
                $joinList = function(array $items): string {
                    $n = count($items);
                    if ($n === 0) return '';
                    if ($n === 1) return $items[0];
                    if ($n === 2) return $items[0] . ' and ' . $items[1];
                    $last = array_pop($items);
                    return implode(', ', $items) . ', and ' . $last;
                };
                $citationSourceNames  = $joinList($activeSources);
                $citationSourceDetail = $joinList($activeSourcesDetail);
                $citingArticles = $citationData['citing_articles'] ?? [];
            }
        }
        $templateMgr->assign([
            'citingArticles' => $citingArticles,
            'citationCount'  => $citationCount,
            'citationSourceNames'  => $citationSourceNames ?? '',
            'citationSourceDetail' => $citationSourceDetail ?? '',
            'citationSourceCounts' => $rawSources ?? [],
        ]);

        import('lib.pkp.classes.scheduledTask.ScheduledTaskDAO');
        /** @var ScheduledTaskDAO $scheduledTaskDao */
        $scheduledTaskDao = DAORegistry::getDAO('ScheduledTaskDAO');
        $lastRunTimestamp = $scheduledTaskDao->getLastRunTime('plugins.generic.usageStats.UsageStatsLoader');
        $statsLastUpdated = $lastRunTimestamp > 0
            ? date('l, d M Y H:i:s T', $lastRunTimestamp)
            : null; // Belum pernah jalan sama sekali -- jangan pura-pura ada tanggal.
        $templateMgr->assign('statsLastUpdated', $statsLastUpdated);

        $templateMgr->display('article/metrics.tpl');
    }

    /**
     * Total abstract views & galley downloads sepanjang waktu untuk 1 artikel.
     * Memakai Application::getMetrics() -- API resmi, sama seperti yang
     * dipakai Journal::getMetrics() dan Application::getPrimaryMetricByAssoc().
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
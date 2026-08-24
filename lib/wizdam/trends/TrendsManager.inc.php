<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/trends/TrendsManager.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Teams
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class TrendsManager
 * @ingroup wizdam_trends
 *
 * @brief Service class untuk mempopulasi data Trends.
 * 
 * Memastikan assignment Smarty 100% presisi dengan legacy WIZDAM:
 * Termasuk Cover Image, Open Access, Keywords, dan Article Type.
 */

class TrendsManager {

    /** Nama class scheduled task yang mengisi tabel `metrics` (views & downloads). */
    private const TASK_USAGE_STATS = 'plugins.generic.usageStats.UsageStatsLoader';

    /** Nama class scheduled task yang mengisi citationCount di article_settings. */
    private const TASK_CITATION_REFRESH = 'lib.wizdam.classes.tasks.CitationRefreshTask';

    /**
     * [FIX] Waktu "last update" data trends TIDAK BOLEH memakai date('Y-m-d H:i:s')
     * (itu jam server saat request diproses, bukan kapan data cache-nya benar-benar
     * diperbarui). Cross-check dengan ArticleMetricsHandler::metrics() -- di sana
     * angka views/downloads berasal dari tabel `metrics` yang diisi scheduled task
     * UsageStatsLoader, dan waktu terakhir kali task itu berjalan diambil lewat
     * ScheduledTaskDAO::getLastRunTime(). Method ini memakai sumber yang SAMA
     * PERSIS supaya "Trends: Popular/Download" konsisten dengan halaman metrics
     * artikel.
     *
     * @param string $taskClassName Nama class scheduled task (lihat konstanta TASK_*).
     * @return int Unix timestamp, atau 0 kalau task belum pernah tercatat jalan.
     */
    private static function _getLastRunTimestamp(string $taskClassName): int {
        import('lib.pkp.classes.scheduledTask.ScheduledTaskDAO');
        /** @var ScheduledTaskDAO $scheduledTaskDao */
        $scheduledTaskDao = DAORegistry::getDAO('ScheduledTaskDAO');
        return (int) $scheduledTaskDao->getLastRunTime($taskClassName);
    }

    /**
     * [CATATAN FORMAT] ArticleMetricsHandler::metrics() mencetak
     * 'statsLastUpdated' langsung sebagai string ('l, d M Y H:i:s T'). Halaman
     * trends BEDA -- template-nya (most_popular.tpl dkk) memproses nilai ini
     * lewat modifier Smarty {$lastUpdateDate|date_format:...}, yang mem-parse
     * ulang via strtotime(). Singkatan zona waktu non-standar seperti "WIB"
     * tidak selalu dikenali strtotime(), jadi di sini kita pakai 'Y-m-d H:i:s'
     * (unambiguous) -- SUMBER angkanya tetap identik, hanya representasi
     * string-nya disesuaikan ke konsumennya.
     * @param int $timestamp Unix timestamp, atau 0/negatif untuk "tidak ada data".
     * @return string|null
     */
    private static function _formatLastUpdate(int $timestamp): ?string {
        return $timestamp > 0 ? date('Y-m-d H:i:s', $timestamp) : null;
    }

    /**
     * Waktu pemutakhiran terakhir untuk data Views & Downloads (Trends: Popular,
     * Trends: Download) -- sumbernya tabel `metrics`, diisi oleh UsageStatsLoader.
     * @return string|null
     */
    private static function _getStatsLastUpdated(): ?string {
        return self::_formatLastUpdate(self::_getLastRunTimestamp(self::TASK_USAGE_STATS));
    }

    /**
     * Waktu pemutakhiran terakhir untuk data Citation (Trends: Citation).
     *
     * [FIX] Sebelumnya HANYA mengandalkan ScheduledTaskDAO::getLastRunTime()
     * untuk CitationRefreshTask -- kalau task itu belum pernah tercatat jalan
     * lewat scheduler resmi (tools/runScheduledTasks.php / acron), hasilnya
     * SELALU 0 dan halaman jatuh ke "N/A", PADAHAL citationCount yang
     * ditampilkan di daftar Most Cited nyata berasal dari cache per-DOI yang
     * SUDAH terisi (mis. dipicu cold-start di halaman artikel).
     *
     * Sekarang meniru PERSIS pola yang dipakai ArticleHandler/ArticleMetricsHandler
     * untuk citation: kedua handler itu membaca 'last_updated' dari
     * CitationFetcherService::getCachedCitations() (lihat $citationTimestamp
     * di ArticleHandler::view(), dan $citationData di
     * ArticleMetricsHandler::metrics()) -- BUKAN dari tabel scheduled_tasks.
     * Jadi urutan sumber di sini:
     *   1. Waktu last-run resmi CitationRefreshTask (paling akurat kalau ada).
     *   2. FALLBACK: timestamp 'last_updated' TERBARU dari cache per-DOI di
     *      antara artikel yang tampil di halaman trends ini -- sumber & cara
     *      baca yang SAMA PERSIS dengan ArticleHandler/ArticleMetricsHandler,
     *      hanya diagregasi (MAX) karena di sini banyak artikel sekaligus.
     *
     * @param Journal|null $journal
     * @param array $articlesPayload Payload hasil _formatMicroPayload() (butuh key 'doi').
     * @return string|null
     */
    private static function _getCitationsLastUpdated(?Journal $journal, array $articlesPayload = []): ?string {
        $taskTimestamp = self::_getLastRunTimestamp(self::TASK_CITATION_REFRESH);
        if ($taskTimestamp > 0) {
            return self::_formatLastUpdate($taskTimestamp);
        }

        $cacheTimestamp = self::_getMostRecentCitationCacheTimestamp($journal, $articlesPayload);
        return self::_formatLastUpdate($cacheTimestamp);
    }

    /**
     * Ambil timestamp 'last_updated' TERBARU dari cache citation per-DOI
     * (CitationFetcherService) di antara artikel yang tampil pada payload.
     *
     * getCachedCitations() HANYA membaca file cache -- tidak pernah memicu
     * request jaringan -- jadi aman dipanggil berulang untuk tiap artikel
     * di halaman ini (persis prinsip yang sama dengan ArticleMetricsHandler:
     * "baca cache saja, tidak memicu fetch jaringan").
     *
     * @param Journal|null $journal Dipakai hanya untuk resolusi kredensial;
     *   tidak berpengaruh ke pembacaan cache (key cache = md5(doi), lintas jurnal).
     * @param array $articlesPayload
     * @return int Unix timestamp, 0 kalau tidak ada satupun cache citation.
     */
    private static function _getMostRecentCitationCacheTimestamp(?Journal $journal, array $articlesPayload): int {
        if (empty($articlesPayload)) {
            return 0;
        }

        import('lib.wizdam.classes.citation.CitationFetcherService');
        $citationFetcher = new CitationFetcherService($journal);

        $latest = 0;
        foreach ($articlesPayload as $item) {
            $doi = (string) ($item['doi'] ?? '');
            if ($doi === '') continue;

            $cached = $citationFetcher->getCachedCitations($doi);
            if ($cached === null) continue;

            $ts = (int) ($cached['last_updated'] ?? $cached['timestamp'] ?? 0);
            if ($ts > $latest) {
                $latest = $ts;
            }
        }

        return $latest;
    }

    /**
     * Assign data Most Popular Artciles to Smarty.
     *
     * [LUMERA] $issue (opsional) dipakai untuk menghitung flag
     * 'showMostPopularGrid' di backend -- lihat _shouldShowFeaturedGrid().
     * Kalau tidak diisi (mis. dipanggil dari halaman trends penuh
     * MostPopularHandler::popular()), grid class tidak akan pernah aktif
     * karena aturan mengharuskan ada issue berjalan.
     *
     * FIX: parameter $limit ditambahkan. Fungsi ini dipakai BERSAMA oleh
     * halaman trends penuh (MostPopularHandler::popular()) DAN widget
     * homepage (IndexHandler.inc.php) -- dua konteks yang butuh perilaku
     * limit BERBEDA. Default DIUBAH menjadi null (TANPA BATAS), karena
     * halaman trends penuh seharusnya menampilkan semua artikel, bukan
     * dibatasi seperti widget homepage. Pemanggil dari IndexHandler.inc.php
     * SEKARANG WAJIB mengirim nilai limit eksplisit (10 untuk journal-level,
     * 4 untuk site-level) supaya perilaku widget homepage TIDAK BERUBAH.
     *
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     * @param PKPRequest $request
     * @param Issue|null $issue Current issue jurnal (opsional)
     * @param int|null $limit null = tanpa batas (halaman trends penuh); isi angka untuk membatasi (widget homepage)
     */
    public static function assignMostPopularPayload(TemplateManager $templateMgr, ?Journal $journal, PKPRequest $request, $issue = null, ?int $limit = null): void {
        import('lib.wizdam.trends.TrendsManagerDAO');
        $popularDao = new TrendsManagerDAO();
        
        $articlesPayload = [];
        
        if ($journal) {
            $journalId = (int)$journal->getId();
            $rawViewsData = $popularDao->getMostPopularArticles($journalId, $limit);
            $articlesPayload = self::_formatMicroPayload($rawViewsData, $request);
            $templateMgr->assign('isSiteLevel', false);
        } else {
            $rawViewsData = $popularDao->getSiteLevelTopArticles($limit);
            $articlesPayload = self::_formatMicroPayload($rawViewsData, $request);
            $templateMgr->assign('isSiteLevel', true);
        }

        // [LUMERA] - Urutkan global berdasarkan views
        usort($articlesPayload, function($a, $b) {
            return $b['total_views'] <=> $a['total_views'];
        });

        // [LUMERA] - Smarty Payload Injection (100% Data Restored)
        $templateMgr->assign([
            'topArticle'           => array_slice($articlesPayload, 0, 1),
            'secondTierArticles'   => array_slice($articlesPayload, 1, 4),
            'thirdTierArticles'    => array_slice($articlesPayload, 5, 4),
            // FIX: sisa artikel di luar podium top-9. Hanya relevan/berefek di
            // halaman trends penuh (limit=null, lihat most_popular.tpl); untuk
            // widget homepage variabel ini diassign juga tapi diabaikan karena
            // template widget tidak merendernya.
            'remainingArticles'    => array_slice($articlesPayload, 9),
            'totalPopularArticles' => count($articlesPayload),
            'popularArticlesList'  => $articlesPayload,
            // [FIX] Bukan date('Y-m-d H:i:s') (jam server) -- diambil dari
            // waktu terakhir UsageStatsLoader jalan, sama seperti
            // ArticleMetricsHandler::metrics() (statsLastUpdated).
            'lastUpdateDate'       => self::_getStatsLastUpdated(),
            'cacheInfo'            => ['enabled' => true, 'hit' => false]
        ]);

        // [WIZDAM] Business rule (backend, bukan Smarty {math}):
        // Grid "app-reviews-row" hanya tampil jika jurnal punya LEBIH DARI 5
        // artikel terbit -- pola sama dengan widget Most Downloads,
        // lihat _shouldShowFeaturedGrid().
        $templateMgr->assign('showMostPopularGrid', self::_shouldShowFeaturedGrid($journal, $issue));
    }

    /**
     * Assign data Most Popular Artciles to Smarty.
     *
     * FIX: dipakai HANYA oleh halaman trends penuh (MostDownloadHandler::download()).
     * Sekarang memanggil _getMostDownloadedArticlesPayload() dengan limit=null
     * (TANPA BATAS) -- sebelumnya berbagi limit yang sama (10/4) dengan widget
     * homepage lewat helper privat yang sama.
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     * @param PKPRequest $request
     */
    public static function assignMostDownloadedPayload(TemplateManager $templateMgr, ?Journal $journal, PKPRequest $request): void {
        $articlesPayload = self::_getMostDownloadedArticlesPayload($journal, $request, null);
        $templateMgr->assign('isSiteLevel', !$journal);

        // [CATATAN] most_downloaded.tpl adalah salinan persis most_popular.tpl
        // (cuma beda judul teks) -- nama variabel Smarty-nya IDENTIK.
        $templateMgr->assign([
            'topArticle'           => array_slice($articlesPayload, 0, 1),
            'secondTierArticles'   => array_slice($articlesPayload, 1, 4),
            'thirdTierArticles'    => array_slice($articlesPayload, 5, 4),
            // FIX: sisa artikel di luar podium top-9, dirender di bagian daftar
            // penuh pada template (lihat most_downloaded.tpl) -- inilah yang
            // membuat "tanpa batas" benar-benar terlihat di halaman, bukan
            // cuma diambil dari DB lalu diabaikan begitu saja oleh template.
            'remainingArticles'    => array_slice($articlesPayload, 9),
            'totalPopularArticles' => count($articlesPayload),
            'popularArticlesList'  => $articlesPayload,
            // [FIX] Sama seperti assignMostPopularPayload() -- ambil dari
            // last-run UsageStatsLoader, konsisten dengan ArticleMetricsHandler.
            'lastUpdateDate'       => self::_getStatsLastUpdated(),
            'cacheInfo'            => ['enabled' => true, 'hit' => false]
        ]);
    }

    /**
     * [LUMERA] Assign data Most Downloaded Articles untuk widget homepage
     * (common/featured/mostDownloads.tpl, di-include dari index/journal.tpl).
     *
     * Berbeda dari assignMostDownloadedPayload() (dipakai oleh halaman penuh
     * trends/most_downloaded.tpl -- MostDownloadHandler::download()), method
     * ini memakai nama variabel Smarty yang khusus untuk widget (topDownloadedArticle,
     * dst) supaya tidak bentrok dengan payload "Most Popular" yang di-assign
     * di halaman homepage yang sama (IndexHandler::journal()).
     *
     * Method ini JUGA memindahkan keputusan tampil/tidaknya grid
     * "app-reviews-row" ke backend (lihat _shouldShowFeaturedGrid()), jadi
     * template tidak perlu lagi menghitung articleCount dengan {math}.
     *
     * FIX: SENGAJA tetap memakai limit 10 (journal-level) / 4 (site-level) --
     * widget homepage TIDAK BOLEH ikut jadi tanpa batas, cuma halaman trends
     * penuh yang berubah (lihat assignMostDownloadedPayload() di atas).
     *
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     * @param PKPRequest $request
     * @param Issue|null $issue Current issue jurnal (null jika belum ada issue)
     */
    public static function assignMostDownloadedHomepagePayload(TemplateManager $templateMgr, ?Journal $journal, PKPRequest $request, $issue = null): void {
        $limit = $journal ? 10 : 4;
        $articlesPayload = self::_getMostDownloadedArticlesPayload($journal, $request, $limit);

        $templateMgr->assign([
            'topDownloadedArticle'         => array_slice($articlesPayload, 0, 1),
            'secondTierDownloadedArticles' => array_slice($articlesPayload, 1, 4),
            'thirdTierDownloadedArticles'  => array_slice($articlesPayload, 5, 4),
            'totalDownloadedArticles'      => count($articlesPayload),
            // [FIX] Idem -- widget homepage juga pakai sumber yang sama.
            'lastUpdateDate'               => self::_getStatsLastUpdated(),
        ]);

        // [WIZDAM] Business rule (backend, bukan Smarty {math}):
        // Grid "app-reviews-row" hanya tampil jika jurnal punya LEBIH DARI 5
        // artikel terbit -- lihat _shouldShowFeaturedGrid().
        $templateMgr->assign('showMostDownloadsGrid', self::_shouldShowFeaturedGrid($journal, $issue));
    }

    /**
     * Ambil + format payload Most Downloaded Articles (fetch, format, sort).
     * Logika bersama untuk assignMostDownloadedPayload() (halaman trends penuh,
     * memanggil dengan limit=null) dan assignMostDownloadedHomepagePayload()
     * (widget homepage, memanggil dengan limit=10/4) supaya tidak duplikasi
     * query/format.
     * @param Journal|null $journal
     * @param PKPRequest $request
     * @param int|null $limit null = tanpa batas
     * @return array
     */
    private static function _getMostDownloadedArticlesPayload(?Journal $journal, PKPRequest $request, ?int $limit = null): array {
        import('lib.wizdam.trends.TrendsManagerDAO');
        $popularDao = new TrendsManagerDAO();

        if ($journal) {
            $rawDownloadsData = $popularDao->getMostDownloadedArticles((int) $journal->getId(), $limit);
        } else {
            $rawDownloadsData = $popularDao->getSiteLevelTopDownloadedArticles($limit);
        }
        $articlesPayload = self::_formatMicroPayload($rawDownloadsData, $request);

        // [LUMERA] - Urutkan global berdasarkan downloads (disimpan di key total_views,
        // lihat catatan di TrendsManagerDAO::getMostDownloadedArticles())
        usort($articlesPayload, function($a, $b) {
            return $b['total_views'] <=> $a['total_views'];
        });

        return $articlesPayload;
    }

    /**
     * [WIZDAM] Tentukan apakah grid "app-reviews-row" pada widget Most
     * Downloads (homepage) boleh tampil.
     *
     * Aturan bisnis:
     * - Jurnal harus punya issue berjalan ($issue tidak null).
     * - publishingMode jurnal bukan PUBLISHING_MODE_NONE.
     * - Jumlah artikel terbit jurnal LEBIH DARI 5 (6 ke atas tampil,
     *   5 ke bawah tidak tampil).
     *
     * @param Journal|null $journal
     * @param Issue|null $issue
     * @return bool
     */
    private static function _shouldShowFeaturedGrid(?Journal $journal, $issue): bool {
        if (!$journal || !$issue) {
            return false;
        }

        if ((int)$journal->getSetting('publishingMode') === PUBLISHING_MODE_NONE) {
            return false;
        }

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $articleCount = $publishedArticleDao->getPublishedArticleCountByJournalId((int)$journal->getId());

        return $articleCount > 5;
    }

    /**
     * Assign data Most Cited Articles to Smarty.
     * Dipakai oleh halaman penuh trends/most_cited.tpl (MostCitedHandler::cited()).
     *
     * FIX: sekarang memanggil _getMostCitedArticlesPayload() dengan limit=null
     * (TANPA BATAS) -- sebelumnya berbagi limit 10/4 yang sama dengan widget
     * homepage lewat helper privat yang sama.
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     * @param PKPRequest $request
     */
    public static function assignMostCitedPayload(TemplateManager $templateMgr, ?Journal $journal, PKPRequest $request): void {
        $articlesPayload = self::_getMostCitedArticlesPayload($journal, $request, null);
        $templateMgr->assign('isSiteLevel', !$journal);

        // [CATATAN] most_cited.tpl adalah salinan persis most_popular.tpl / most_downloaded.tpl
        // (cuma beda judul teks) -- nama variabel Smarty-nya IDENTIK.
        $templateMgr->assign([
            'topArticle'           => array_slice($articlesPayload, 0, 1),
            'secondTierArticles'   => array_slice($articlesPayload, 1, 4),
            'thirdTierArticles'    => array_slice($articlesPayload, 5, 4),
            // FIX: sisa artikel di luar podium top-9, lihat most_cited.tpl.
            'remainingArticles'    => array_slice($articlesPayload, 9),
            'totalPopularArticles' => count($articlesPayload),
            'popularArticlesList'  => $articlesPayload,
            // [FIX] Kutipan sumbernya BEDA dari views/downloads. Utamakan
            // last-run resmi CitationRefreshTask; kalau task itu belum
            // pernah tercatat jalan, JANGAN langsung "N/A" -- jatuhkan ke
            // timestamp cache per-DOI (persis pola $citationTimestamp di
            // ArticleHandler / $citationData di ArticleMetricsHandler).
            'lastUpdateDate'       => self::_getCitationsLastUpdated($journal, $articlesPayload),
            'cacheInfo'            => ['enabled' => true, 'hit' => false]
        ]);
    }

    /**
     * [LUMERA] Assign data Most Cited Articles untuk widget homepage
     * (common/featured/mostCitedArticles.tpl, di-include dari index/journal.tpl).
     *
     * Berbeda dari assignMostCitedPayload() (dipakai oleh halaman penuh
     * trends/most_cited.tpl -- MostCitedHandler::cited()), method ini
     * memakai nama variabel Smarty yang khusus untuk widget
     * (topCitedArticle, dst) supaya tidak bentrok dengan payload
     * "Most Popular" / "Most Downloaded" yang di-assign di halaman
     * homepage yang sama (IndexHandler::journal()).
     *
     * Method ini JUGA memindahkan keputusan tampil/tidaknya grid
     * "app-reviews-row" ke backend (lihat _shouldShowFeaturedGrid()), jadi
     * template tidak perlu lagi menghitung articleCount dengan {math}.
     *
     * FIX: SENGAJA tetap memakai limit 10 (journal-level) / 4 (site-level) --
     * widget homepage TIDAK BOLEH ikut jadi tanpa batas.
     *
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     * @param PKPRequest $request
     * @param Issue|null $issue Current issue jurnal (null jika belum ada issue)
     */
    public static function assignMostCitedHomepagePayload(TemplateManager $templateMgr, ?Journal $journal, PKPRequest $request, $issue = null): void {
        $limit = $journal ? 10 : 4;
        $articlesPayload = self::_getMostCitedArticlesPayload($journal, $request, $limit);

        $templateMgr->assign([
            'topCitedArticle'         => array_slice($articlesPayload, 0, 1),
            'secondTierCitedArticles' => array_slice($articlesPayload, 1, 4),
            'thirdTierCitedArticles'  => array_slice($articlesPayload, 5, 4),
            'totalCitedArticles'      => count($articlesPayload),
            // [FIX] Idem -- widget homepage juga pakai sumber + fallback yang sama.
            'lastUpdateDate'          => self::_getCitationsLastUpdated($journal, $articlesPayload),
        ]);

        // [WIZDAM] Business rule (backend, bukan Smarty {math}):
        // Grid "app-reviews-row" hanya tampil jika jurnal punya LEBIH DARI 5
        // artikel terbit -- pola sama dengan widget Most Downloads/Most Popular,
        // lihat _shouldShowFeaturedGrid().
        $templateMgr->assign('showMostCitedGrid', self::_shouldShowFeaturedGrid($journal, $issue));
    }

    /**
     * Ambil + format payload Most Cited Articles (fetch, format, sort).
     * Logika bersama untuk assignMostCitedPayload() (halaman trends penuh,
     * memanggil dengan limit=null) dan assignMostCitedHomepagePayload()
     * (widget homepage, memanggil dengan limit=10/4) supaya tidak duplikasi
     * query/format.
     * @param Journal|null $journal
     * @param PKPRequest $request
     * @param int|null $limit null = tanpa batas
     * @return array
     */
    private static function _getMostCitedArticlesPayload(?Journal $journal, PKPRequest $request, ?int $limit = null): array {
        import('lib.wizdam.trends.TrendsManagerDAO');
        $citedDao = new TrendsManagerDAO();

        if ($journal) {
            $rawCitationsData = $citedDao->getMostCitedArticles((int) $journal->getId(), $limit);
        } else {
            $rawCitationsData = $citedDao->getSiteLevelTopCitedArticles($limit);
        }
        $articlesPayload = self::_formatMicroPayload($rawCitationsData, $request);

        // [LUMERA] - Urutkan global berdasarkan sitasi (disimpan di key total_views,
        // lihat catatan di TrendsManagerDAO::getMostCitedArticles())
        usort($articlesPayload, function($a, $b) {
            return $b['total_views'] <=> $a['total_views'];
        });

        return $articlesPayload;
    }

    /**
     * Format Micro-Payload for Most Popular Articles.
     * Eksekusi Micro-Payload (Mengekstrak seluruh data ke tipe skalar murni).
     * @param array $rawViewsData
     * @param PKPRequest $request
     * @return array
     */
    private static function _formatMicroPayload(array $rawViewsData, PKPRequest $request): array {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        
        $payload = [];
        $journalCache = [];
        $sectionCache = [];
        foreach ($rawViewsData as $articleId => $data) {
            $article = $articleDao->getArticle((int)$articleId);
            if (!$article) continue;
            
            $journalId = (int)$article->getJournalId();
            
            if (!isset($journalCache[$journalId])) {
                $journalCache[$journalId] = $journalDao->getById($journalId);
            }
            $articleJournal = $journalCache[$journalId];
            $journalPath = $articleJournal ? $articleJournal->getPath() : null;

            // 1. Ekstrak Authors
            $authors = $authorDao->getAuthorsBySubmissionId($articleId);
            $authorList = [];
            if (is_array($authors)) {
                foreach ($authors as $author) {
                    $firstName = trim((string)$author->getFirstName());
                    $lastName = trim((string)$author->getLastName());
                    $fullName = trim($firstName . ' ' . (string)$author->getMiddleName() . ' ' . $lastName);
                    
                    if (empty($fullName)) {
                        $fullName = !empty($firstName) ? $firstName : (!empty($lastName) ? $lastName : 'Unknown Author');
                    }

                    $authorList[] = [
                        'first_name'  => $firstName,
                        'middle_name' => trim((string)$author->getMiddleName()),
                        'last_name'   => $lastName,
                        'full_name'   => $fullName,
                        'affiliation' => (string)$author->getLocalizedAffiliation(),
                        'email'       => (string)$author->getEmail()
                    ];
                }
            }

            // 2. Ekstrak Section / Article Type
            $sectionId = $article->getSectionId();
            if (!isset($sectionCache[$sectionId])) {
                $sectionCache[$sectionId] = $sectionDao->getSection($sectionId);
            }
            $section = $sectionCache[$sectionId];
            $articleType = $section ? (string)$section->getLocalizedTitle() : 'Article';

            // 3. Ekstrak Keywords
            $keywords = [];
            $keywordString = (string)$article->getLocalizedSubject();
            if (!empty($keywordString)) {
                $keywords = array_map('trim', explode(';', $keywordString));
                $keywords = array_filter($keywords, fn($kw) => !empty($kw));
                $keywords = array_values($keywords);
            }

            // 4. Konstruksi Micro-Payload Murni WIZDAM
            $payload[] = [
                'article_id'               => $articleId,
                'title'                    => (string)$article->getLocalizedTitle(),
                'abstract'                 => (string)$article->getLocalizedAbstract(),
                'authors'                  => $authorList,
                'total_views'              => (int)$data['views'],
                'date_published'           => (string)$data['date_published'],
                'date_published_formatted' => $data['date_published'] ? date('Y-m-d', strtotime($data['date_published'])) : '',
                'is_open_access'           => self::checkWizdamOpenAccessStatus($article, $journalId, $articleJournal),
                'article_type'             => $articleType,
                'cover_image' => self::findArticleCoverImage($article, $journalId, $request),
                'article_url' => $request->url($journalPath, 'article', 'view', $articleId),
                'keywords'                 => $keywords,
                'doi'                      => method_exists($article, 'getPubId') ? (string)$article->getPubId('doi') : ''
            ];
        }

        return $payload;
    }

    //
    // Helper Functions
    //

    /**
     * [WIZDAM] Public (sebelumnya private) -- dipakai ulang oleh
     * ArticleHeroService supaya logika pencarian cover image artikel
     * TIDAK perlu ditulis ulang dengan raw SQL. Satu-satunya sumber
     * kebenaran untuk "cari cover image artikel" di seluruh aplikasi.
     */
    /**
     * Find Article Cover Image with Multi-Locale Support.
     * @param int $journalId
     * @param mixed $article
     * @return array
     */
    public static function findArticleCoverImage($article, int $journalId, PKPRequest $request): array {
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $journalFilesPath = $publicFileManager->getJournalFilesPath($journalId);

        // Urutan locale yang dicoba: locale aktif saat ini, locale submission, lalu locale umum.
        $locales = array_values(array_unique(array_filter([
            AppLocale::getLocale(),
            $article->getLocale(),
            'en_US',
            'id_ID'
        ])));

        foreach ($locales as $locale) {
            $fileName = $article->getFileName($locale);
            if ($fileName === '' || !$article->getShowCoverPage($locale)) {
                continue;
            }

            $filePath = $journalFilesPath . '/' . $fileName;
            if (file_exists($filePath)) {
                return [
                    'file_exists' => true,
                    'file_url'    => $request->getBaseUrl() . '/' . $filePath,
                    'file_path'   => $filePath,
                    'locale'      => $locale
                ];
            }
        }

        return ['file_exists' => false, 'file_url' => null, 'file_path' => null];
    }

    /**
     * Check if an article is Open Access without using raw SQL (MVC Compliant).
     * This method checks multiple sources to determine the Open Access status of an article.
     *
     * [WIZDAM] Public (sebelumnya private) -- dipakai ulang oleh
     * ArticleHeroService, satu-satunya sumber kebenaran status open access
     * di seluruh aplikasi (5-method detection lewat DAO, bukan raw SQL).
     * @param Article $article
     * @param int $journalId
     * @return bool
     */
    public static function checkWizdamOpenAccessStatus(Article $article, int $journalId, ?Journal $journal = null): bool {
        // Method 1: Cek dari setting artikel langsung
        if (method_exists($article, 'getAccessStatus') && $article->getAccessStatus() == ARTICLE_ACCESS_OPEN) {
            return true;
        }

        // Method 2: Cek dari published_articles DAO (Menggantikan Raw SQL)
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        if ($publishedArticleDao) {
            $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($article->getId());
            if ($publishedArticle && method_exists($publishedArticle, 'getAccessStatus') && $publishedArticle->getAccessStatus() == 1) {
                return true;
            }
        }

        // Method 3: Cek dari issue level
        if (method_exists($article, 'getIssueId')) {
            $issueId = $article->getIssueId();
            if ($issueId) {
                /** @var IssueDAO $issueDao */
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issue = $issueDao->getIssueById($issueId);
                if ($issue) {
                    if (method_exists($issue, 'getAccessStatus') && $issue->getAccessStatus() == 1) {
                        return true;
                    }
                    if (method_exists($issue, 'getOpenAccessDate')) {
                        $openAccessDate = $issue->getOpenAccessDate();
                        if ($openAccessDate && strtotime((string)$openAccessDate) <= time()) {
                            return true;
                        }
                    }
                }
            }
        }

        // Method 4: Cek remote URL dari ArticleGalleys (Menggantikan Raw SQL)
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        if ($galleyDao) {
            $galleys = $galleyDao->getGalleysByArticle($article->getId());
            foreach ($galleys as $galley) {
                if (method_exists($galley, 'getRemoteURL') && !empty($galley->getRemoteURL())) {
                    return true;
                }
            }
        }

        // Method 5: Cek Default Journal Policy
        if (!$journal) {
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($journalId);
        }
        if ($journal && method_exists($journal, 'getSetting')) {
            $publishingMode = $journal->getSetting('publishingMode');
            if ($publishingMode == 0) { // 0 = Open Access
                return true;
            }
        }

        return false;
    }

}
?>
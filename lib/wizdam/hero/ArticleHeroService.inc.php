<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/hero/ArticleHeroService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ArticleHeroService
 * @ingroup wizdam_hero
 *
 * @brief [WIZDAM] Service class untuk seleksi & mempopulasi data Article
 * Hero + Featured homepage jurnal.
 *
 * [MIGRASI] Sebelumnya plugins/themes/{theme}/php/hero_futured/
 * article_hero.php -- file PHP prosedural (bukan class) yang di-include
 * lewat {php}...{/php} block di article_Hero.tpl, jalan dalam konteks
 * object Smarty ($this->assign()/$this->get_template_vars()), dan
 * menyimpan cache-nya sendiri langsung di folder tema
 * (plugins/themes/{theme}/php/hero_futured/cache/) -- rapuh terhadap
 * ganti tema dan tidak konsisten dengan pola cache aplikasi lain.
 *
 * Sekarang jadi class service terintegrasi, mengikuti pola integrasi
 * yang sama dengan TrendsManager/TrendsManagerDAO (Manager statis + DAO
 * terpisah untuk query mentah) dan CitationFetcherService (lokasi cache
 * standar Core::getBaseDir().'/cache/t_wizdam/...', bukan folder tema).
 * Dipanggil dari IndexHandler::journal() persis seperti pemanggilan
 * TrendsManager::assignMostPopularPayload() dkk -- lihat
 * ArticleHeroService::assignArticleHeroPayload().
 *
 * Logika seleksi hero (grace period mingguan + scoring views/downloads/
 * recency) dan strategi cache (smart hash-detection + TTL 7 hari)
 * dipertahankan PERSIS sama dengan skrip lama -- yang berubah cuma
 * arsitekturnya, bukan business logic-nya.
 */

import('lib.wizdam.hero.ArticleHeroDAO');
import('lib.wizdam.trends.TrendsManager');

class ArticleHeroService {

    private const CACHE_TTL_SECONDS = 604800; // 7 hari, sama seperti skrip lama.
    private const FEATURED_LIMIT = 4;

    /**
     * Titik masuk utama -- assign data Hero + Featured ke Smarty untuk
     * homepage jurnal. Dipanggil dari IndexHandler::journal(), persis
     * pola pemanggilan TrendsManager::assignMostPopularPayload() dkk.
     *
     * @param TemplateManager $templateMgr
     * @param Journal $journal
     * @param PKPRequest $request
     */
    public static function assignArticleHeroPayload(TemplateManager $templateMgr, Journal $journal, PKPRequest $request): void {
        $journalId = (int) $journal->getId();
        $dao = new ArticleHeroDAO();

        $currentHash = $dao->getDataHash($journalId);
        $cached = self::_getFromCache($journalId);

        if ($cached !== null && !self::_isCacheStale($cached, $currentHash)) {
            self::_assignFromPayload($templateMgr, $cached);
            return;
        }

        $payload = self::_buildPayload($journal, $dao, $request, $currentHash);
        self::_saveToCache($journalId, $payload);
        self::_assignFromPayload($templateMgr, $payload);
    }

    //
    // Payload construction
    //

    /**
     * Bangun payload lengkap (hero + featured + metadata) dari database.
     * @param Journal $journal
     * @param ArticleHeroDAO $dao
     * @param PKPRequest $request
     * @param string $dataHash
     * @return array
     */
    private static function _buildPayload(Journal $journal, ArticleHeroDAO $dao, PKPRequest $request, string $dataHash): array {
        $journalId = (int) $journal->getId();
        $candidates = $dao->getLatestVolumeArticles($journalId);

        if (count($candidates) < 5) {
            $candidates = $dao->getMultipleVolumesArticles($journalId);
        }

        if (count($candidates) < 5) {
            $selection = self::_selectForNewJournal($candidates);
        } else {
            $selection = self::_selectForMatureJournal($candidates);
        }

        $heroRow = $selection['hero'];
        $featuredRows = $selection['featured'];

        $heroFormatted = $heroRow ? self::_formatArticle($heroRow, $journal, $request) : null;
        $featuredFormatted = [];
        foreach ($featuredRows as $row) {
            $featuredFormatted[] = self::_formatArticle($row, $journal, $request);
        }

        $allArticles = $heroFormatted ? array_merge([$heroFormatted], $featuredFormatted) : $featuredFormatted;

        return [
            'hero' => $heroFormatted,
            'featured' => $featuredFormatted,
            'all_articles' => $allArticles,
            'selection_logic' => $selection['selection_logic'],
            'last_update' => date('Y-m-d H:i:s'),
            'generated_at' => time(),
            'data_hash' => $dataHash,
        ];
    }

    /**
     * Mode jurnal baru (< 5 kandidat) -- kronologis, artikel terbaru jadi
     * hero, sisanya featured. Sama seperti handleNewJournalMode() lama.
     * @param array $candidates
     * @return array{hero: array|null, featured: array, selection_logic: array}
     */
    private static function _selectForNewJournal(array $candidates): array {
        if (empty($candidates)) {
            return [
                'hero' => null,
                'featured' => [],
                'selection_logic' => [
                    'mode' => 'new_journal_empty',
                    'selection_method' => 'none',
                    'total_candidates' => 0,
                ],
            ];
        }

        $hero = $candidates[0];
        $featured = array_slice($candidates, 1, self::FEATURED_LIMIT);

        return [
            'hero' => $hero,
            'featured' => $featured,
            'selection_logic' => [
                'mode' => 'new_journal_chronological',
                'selection_method' => 'latest_article_as_hero',
                'total_candidates' => count($candidates),
                'hero_article_id' => $hero['article_id'],
            ],
        ];
    }

    /**
     * Mode jurnal matang (>= 5 kandidat) -- grace period mingguan +
     * scoring views/downloads/recency. Sama seperti handleMatureJournalMode()
     * + selectHeroWithWeeklyGrace() + selectFeaturedWithWeeklyGrace() lama.
     * @param array $candidates
     * @return array{hero: array|null, featured: array, selection_logic: array}
     */
    private static function _selectForMatureJournal(array $candidates): array {
        $oneWeekAgo = strtotime('-7 days');
        $latest = $candidates[0];
        $isInGracePeriod = strtotime((string) $latest['date_published']) > $oneWeekAgo;

        $scored = self::_scoreCandidates($candidates, $oneWeekAgo, true);

        if ($isInGracePeriod) {
            $heroIndex = 0;
            $hero = $latest;
            $selectionMethod = 'weekly_grace_period';
        } else {
            usort($scored, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
            $topCandidateId = $scored[0]['article_id'];
            $heroIndex = 0;
            $hero = $candidates[0];
            foreach ($candidates as $i => $c) {
                if ($c['article_id'] === $topCandidateId) {
                    $hero = $c;
                    $heroIndex = $i;
                    break;
                }
            }
            $selectionMethod = 'scoring_algorithm';
        }

        $remaining = [];
        foreach ($candidates as $i => $c) {
            if ($i !== $heroIndex) {
                $remaining[] = $c;
            }
        }

        $featured = self::_selectFeaturedWithGrace($remaining, $oneWeekAgo);

        return [
            'hero' => $hero,
            'featured' => $featured,
            'selection_logic' => [
                'mode' => 'mature_journal_advanced',
                'selection_method' => $selectionMethod,
                'grace_period_active' => $isInGracePeriod,
                'total_candidates' => count($candidates),
                'hero_article_id' => $hero['article_id'],
            ],
        ];
    }

    /**
     * Pilih featured articles dari sisa kandidat, prioritas grace period
     * dulu baru score. Sama seperti selectFeaturedWithWeeklyGrace() lama.
     * @param array $remaining
     * @param int $oneWeekAgo
     * @return array
     */
    private static function _selectFeaturedWithGrace(array $remaining, int $oneWeekAgo): array {
        if (empty($remaining)) {
            return [];
        }

        $scored = self::_scoreCandidates($remaining, $oneWeekAgo, false);

        usort($scored, function ($a, $b) {
            if ($a['is_in_grace_period'] !== $b['is_in_grace_period']) {
                return $a['is_in_grace_period'] ? -1 : 1;
            }
            return $b['total_score'] <=> $a['total_score'];
        });

        $selected = [];
        foreach (array_slice($scored, 0, self::FEATURED_LIMIT) as $candidate) {
            foreach ($remaining as $article) {
                if ($article['article_id'] === $candidate['article_id']) {
                    $selected[] = $article;
                    break;
                }
            }
        }

        return $selected;
    }

    /**
     * Hitung skor tiap kandidat: views + (downloads x 2) + recency +
     * bonus grace period yang menurun seiring waktu. Sama persis dengan
     * selectHeroWithWeeklyGrace()/selectFeaturedWithWeeklyGrace() lama,
     * cuma digabung jadi satu helper (parameter $isHeroScoring menentukan
     * bobot recency/bonus mana yang dipakai, sesuai skrip asli).
     * @param array $candidates
     * @param int $oneWeekAgo
     * @param bool $isHeroScoring
     * @return array
     */
    private static function _scoreCandidates(array $candidates, int $oneWeekAgo, bool $isHeroScoring): array {
        $recencyBase = $isHeroScoring ? 100 : 50;
        $recencyStep = $isHeroScoring ? 10 : 5;
        $graceBonusBase = $isHeroScoring ? 50 : 30;
        $graceBonusStep = $isHeroScoring ? 5 : 3;

        $scored = [];
        foreach ($candidates as $i => $article) {
            $publishTime = strtotime((string) $article['date_published']);
            $daysSincePublish = (time() - $publishTime) / (60 * 60 * 24);
            $isInGracePeriod = $publishTime > $oneWeekAgo;

            $viewsScore = (int) $article['total_views'];
            $downloadsScore = (int) $article['total_downloads'] * 2;
            $recencyScore = max(0, $recencyBase - ($i * $recencyStep));
            $graceBonus = $isInGracePeriod ? max(0, $graceBonusBase - ($daysSincePublish * $graceBonusStep)) : 0;

            $scored[] = [
                'article_id' => $article['article_id'],
                'total_views' => $viewsScore,
                'total_downloads' => (int) $article['total_downloads'],
                'is_in_grace_period' => $isInGracePeriod,
                'total_score' => $viewsScore + $downloadsScore + $recencyScore + $graceBonus,
            ];
        }

        return $scored;
    }

    //
    // Article formatting -- pakai DAO & helper TrendsManager yang sudah
    // MVC-compliant, BUKAN raw SQL seperti checkHeroOpenAccessStatus()/
    // findHeroCoverImage() di skrip lama.
    //

    /**
     * Format satu baris kandidat (dari ArticleHeroDAO) jadi array lengkap
     * siap pakai template -- title, authors, cover image, open access,
     * keywords, DOI, dst. Sama seperti processArticleResults() lama, tapi
     * open access & cover image sekarang pakai method MVC-compliant milik
     * TrendsManager (tidak ada raw SQL tersisa di sini).
     * @param array $row
     * @param Journal $journal
     * @param PKPRequest $request
     * @return array|null
     */
    private static function _formatArticle(array $row, Journal $journal, PKPRequest $request): ?array {
        $journalId = (int) $journal->getId();
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $article = $articleDao->getArticle($row['article_id']);
        if (!$article || (int) $article->getJournalId() !== $journalId) {
            return null;
        }

        /** @var AuthorDAO $authorDao */
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        $authors = $authorDao->getAuthorsBySubmissionId($row['article_id']);
        $authorList = [];
        if (is_array($authors)) {
            foreach ($authors as $author) {
                $firstName = trim((string) $author->getFirstName());
                $middleName = trim((string) $author->getMiddleName());
                $lastName = trim((string) $author->getLastName());
                $fullName = trim($firstName . ' ' . $middleName . ' ' . $lastName);
                if ($fullName === '') {
                    $fullName = $firstName !== '' ? $firstName : ($lastName !== '' ? $lastName : 'Unknown Author');
                }
                $authorList[] = [
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'full_name' => $fullName,
                    'affiliation' => $author->getLocalizedAffiliation(),
                    'email' => $author->getEmail(),
                ];
            }
        }

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($article->getSectionId());
        $articleType = $section ? $section->getLocalizedTitle() : 'Article';

        $keywords = [];
        $keywordString = $article->getLocalizedSubject();
        if (!empty($keywordString)) {
            $keywords = array_values(array_filter(array_map('trim', explode(';', (string) $keywordString))));
        }

        $doi = method_exists($article, 'getPubId') ? $article->getPubId('doi') : '';

        return [
            'article_id' => $row['article_id'],
            'title' => $article->getLocalizedTitle(),
            'abstract' => $article->getLocalizedAbstract(),
            'authors' => $authorList,
            'total_views' => (int) $row['total_views'],
            'total_downloads' => (int) $row['total_downloads'],
            'date_published' => $row['date_published'],
            'date_published_formatted' => $row['date_published'] ? date('Y-m-d', strtotime((string) $row['date_published'])) : '',
            'is_open_access' => TrendsManager::checkWizdamOpenAccessStatus($article, $journalId, $journal),
            'article_type' => $articleType,
            'cover_image' => TrendsManager::findArticleCoverImage($article, $journalId, $request),
            'article_url' => $request->url(null, 'article', 'view', $row['article_id']),
            'keywords' => $keywords,
            'doi' => $doi,
            'issue_id' => $row['issue_id'],
            'volume' => $row['volume'],
            'number' => $row['number'],
        ];
    }

    //
    // Template assignment
    //

    /**
     * Assign payload (dari cache ATAU baru dibangun) ke Smarty. Bentuk
     * variabel disamakan persis dengan yang dipakai article_Hero.tpl
     * sebelumnya (heroArticle sebagai array 1 elemen, latestArticles).
     * @param TemplateManager $templateMgr
     * @param array $payload
     */
    private static function _assignFromPayload(TemplateManager $templateMgr, array $payload): void {
        $templateMgr->assign([
            'heroArticle' => $payload['hero'] ? [$payload['hero']] : [],
            'latestArticles' => $payload['featured'],
            'totalLatestArticles' => count($payload['all_articles']),
            'allLatestArticles' => $payload['all_articles'],
            'lastUpdateDate' => $payload['last_update'],
            'heroSelectionInfo' => $payload['selection_logic'],
        ]);
    }

    //
    // Cache -- lokasi standar aplikasi (bukan folder tema seperti skrip
    // lama), pola sama persis dengan CitationFetcherService: smart hash-
    // detection + TTL. Cache di-invalidasi kalau SALAH SATU dari dua hal
    // berubah: (a) data artikel benar-benar berubah (hash beda), atau
    // (b) sudah lewat 7 hari (weekly update, sesuai nama fitur aslinya).
    //

    /**
     * Get cache dir
     */
    private static function _getCacheDir(): string {
        return Core::getBaseDir() . '/cache/t_wizdam/hero';
    }

    /**
     * Get cache file path
     */
    private static function _getCacheFilePath(int $journalId): string {
        return self::_getCacheDir() . '/' . $journalId . '.json.gz';
    }

    /**
     * Get from cache
     */
    private static function _getFromCache(int $journalId): ?array {
        $cacheFile = self::_getCacheFilePath($journalId);
        if (!file_exists($cacheFile)) {
            return null;
        }
        $compressed = file_get_contents($cacheFile);
        if ($compressed === false) {
            return null;
        }
        $json = @gzuncompress($compressed);
        if ($json === false) {
            return null;
        }
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    /**
     * Is cache stale
     */
    private static function _isCacheStale(array $cached, string $currentHash): bool {
        if (($cached['data_hash'] ?? null) !== $currentHash) {
            return true; // Smart detection: data berubah.
        }
        $generatedAt = (int) ($cached['generated_at'] ?? 0);
        return (time() - $generatedAt) > self::CACHE_TTL_SECONDS; // Weekly update.
    }

    /**
     * Savo to cache
     */
    private static function _saveToCache(int $journalId, array $payload): bool {
        $dir = self::_getCacheDir();
        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            error_log('ArticleHeroService: gagal membuat direktori cache: ' . $dir);
            return false;
        }
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            error_log('ArticleHeroService: json_encode gagal: ' . json_last_error_msg());
            return false;
        }
        $compressed = gzcompress($json, 9);
        if ($compressed === false) {
            error_log('ArticleHeroService: gzcompress gagal');
            return false;
        }
        return file_put_contents(self::_getCacheFilePath($journalId), $compressed) !== false;
    }

}
?>
<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/trends/WizdamTrendsManager.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Teams
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WizdamTrendsManager
 * @ingroup wizdam_trends
 *
 * @brief Service class untuk mempopulasi data Trends.
 * 
 * Memastikan assignment Smarty 100% presisi dengan legacy WIZDAM:
 * Termasuk Cover Image, Open Access, Keywords, dan Article Type.
 */

class WizdamTrendsManager {

    /**
     * Assign data Most Popular Artciles to Smarty.
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     * @param PKPRequest $request
     */
    public static function assignMostPopularPayload(TemplateManager $templateMgr, ?Journal $journal, PKPRequest $request): void {
        import('lib.wizdam.trends.MostPopularDAO');
        $popularDao = new MostPopularDAO();
        
        $articlesPayload = [];
        
        if ($journal) {
            $journalId = (int)$journal->getId();
            $rawViewsData = $popularDao->getMostPopularArticles($journalId, 10);
            $articlesPayload = self::_formatMicroPayload($rawViewsData, $request);
            $templateMgr->assign('isSiteLevel', false);
        } else {
            $rawViewsData = $popularDao->getSiteLevelTopArticles(4);
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
            'totalPopularArticles' => count($articlesPayload),
            'popularArticlesList'  => $articlesPayload,
            'lastUpdateDate'       => date('Y-m-d H:i:s'),
            'cacheInfo'            => ['enabled' => true, 'hit' => false]
        ]);
    }

    /**
     * Assign data Most Popular Artciles to Smarty.
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     * @param PKPRequest $request
     */
    public static function assignMostDownloadedPayload(TemplateManager $templateMgr, ?Journal $journal, PKPRequest $request): void {

    }

    /**
     * Assign data Most Popular Artciles to Smarty.
     * @param TemplateManager $templateMgr
     * @param Journal|null $journal
     * @param PKPRequest $request
     */
    public static function assignMostCitedPayload(TemplateManager $templateMgr, ?Journal $journal, PKPRequest $request): void {

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
                'is_open_access'           => self::_checkWizdamOpenAccessStatus($article, $journalId, $articleJournal),
                'article_type'             => $articleType,
                'cover_image' => self::_findArticleCoverImage($article, $journalId, $request),
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
     * Find Article Cover Image with Multi-Locale Support.
     * @param int $journalId
     * @param mixed $article
     * @return array
     */
    private static function _findArticleCoverImage($article, int $journalId, PKPRequest $request): array {
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
     * @param Article $article
     * @param int $journalId
     * @return bool
     */
    private static function _checkWizdamOpenAccessStatus(Article $article, int $journalId, ?Journal $journal = null): bool {
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
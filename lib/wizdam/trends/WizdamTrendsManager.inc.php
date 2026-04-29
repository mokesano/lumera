<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/trends/WizdamTrendsManager.inc.php
 *
 * [WIZDAM] - Service class untuk mempopulasi data Trends.
 * Memastikan assignment Smarty 100% presisi dengan legacy WIZDAM:
 * Termasuk Cover Image, Open Access, Keywords, dan Article Type.
 */

class WizdamTrendsManager {

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

        // [WIZDAM] - Urutkan global berdasarkan views
        usort($articlesPayload, function($a, $b) {
            return $b['total_views'] <=> $a['total_views'];
        });

        // [WIZDAM] - Smarty Payload Injection (100% Data Restored)
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
     * [WIZDAM] - Eksekusi Micro-Payload (Mengekstrak seluruh data ke tipe skalar murni)
     */
    private static function _formatMicroPayload(array $rawViewsData, PKPRequest $request): array {
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $authorDao = DAORegistry::getDAO('AuthorDAO');
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        
        $payload = [];

        foreach ($rawViewsData as $articleId => $data) {
            $article = $articleDao->getArticle((int)$articleId);
            if (!$article) continue;
            
            $journalId = (int)$article->getJournalId();
            
            $articleJournal = $journalDao->getById($journalId);
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
            $section = $sectionDao->getSection($article->getSectionId());
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
                'is_open_access'           => self::_checkWizdamOpenAccessStatus($article, $journalId),
                'article_type'             => $articleType,
                'cover_image'              => self::_findArticleCoverImage($journalId, $articleId),
                'article_url' => $request->url($journalPath, 'article', 'view', $articleId),
                'keywords'                 => $keywords,
                'doi'                      => method_exists($article, 'getPubId') ? (string)$article->getPubId('doi') : ''
            ];
        }

        return $payload;
    }

    /**
     * [WIZDAM] - Helper: Mencari Cover Image dengan dukungan Multi-Locale
     */
    private static function _findArticleCoverImage(int $journalId, int $articleId): array {
        $locales = ['en_US', 'id_ID', 'en', 'id'];
        $extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $protocol . '://' . $host . '/';

        foreach ($locales as $locale) {
            foreach ($extensions as $ext) {
                $coverImagePath = "public/journals/{$journalId}/cover_article_{$articleId}_{$locale}.{$ext}";
                if (file_exists($coverImagePath)) {
                    return [
                        'file_exists' => true,
                        'file_url'    => $baseUrl . $coverImagePath,
                        'file_path'   => $coverImagePath,
                        'locale'      => $locale,
                        'extension'   => $ext
                    ];
                }
            }
        }
        
        // Fallback tanpa locale
        foreach ($extensions as $ext) {
            $coverImagePath = "public/journals/{$journalId}/cover_article_{$articleId}.{$ext}";
            if (file_exists($coverImagePath)) {
                return [
                    'file_exists' => true,
                    'file_url'    => $baseUrl . $coverImagePath,
                    'file_path'   => $coverImagePath,
                    'locale'      => 'default',
                    'extension'   => $ext
                ];
            }
        }
        
        return ['file_exists' => false, 'file_url' => null, 'file_path' => null];
    }

    /**
     * [WIZDAM] - Helper: Deteksi Open Access tanpa Raw SQL (MVC Compliant)
     */
    private static function _checkWizdamOpenAccessStatus(Article $article, int $journalId): bool {
        // Method 1: Cek dari setting artikel langsung
        if (method_exists($article, 'getAccessStatus') && $article->getAccessStatus() == ARTICLE_ACCESS_OPEN) {
            return true;
        }

        // Method 2: Cek dari published_articles DAO (Menggantikan Raw SQL)
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
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($journalId);
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
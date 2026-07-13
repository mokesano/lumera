<?php
declare(strict_types=1);

/**
 * @file pages/sitemap/SitemapHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SitemapHandler
 * @ingroup pages_sitemap
 *
 * @brief Produce a sitemap in XML format for submitting to search engines.
 */

import('lib.pkp.classes.xml.XMLCustomWriter');
import('classes.handler.Handler');

define('SITEMAP_XSD_URL', 'http://www.sitemaps.org/schemas/sitemap/0.9');

class SitemapHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SitemapHandler() {
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
     * Generate an XML sitemap for webcrawlers.
     * Creates a sitemap index if in site context, else creates a sitemap.
     * 
     * [WIZDAM] SIGNATURE COMPATIBILITY: Tidak ada type hint di parameter 
     * agar 100% kompatibel dengan PKPHandler::index($args, $request)
     * 
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index($args = [], $request = null) {
        // Internal safety: Pastikan $request adalah objek PKPRequest
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();
        $router = $request->getRouter();

        $journal = $request->getJournal();
        
        // Strict comparison di dalam body (aman dan tidak melanggar signature)
        if ($request->getRequestedJournalPath() === 'index' || $journal === null) {
            $doc = $this->_createSitemapIndex($request, $router);
            $this->_sendXmlHeaders('sitemap_index.xml');
            XMLCustomWriter::printXML($doc);
        } else {
            $doc = $this->_createJournalSitemap($request, $router, $journal);
            $this->_sendXmlHeaders('sitemap.xml');
            XMLCustomWriter::printXML($doc);
        }
    }
      
    /**
     * Construct a sitemap index listing each journal's individual sitemap.
     * 
     * @param PKPRequest $request
     * @param PKPRouter $router
     * @return DOMDocument
     */
    protected function _createSitemapIndex($request, $router) {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        
        $doc = XMLCustomWriter::createDocument();
        $root = XMLCustomWriter::createElement($doc, 'sitemapindex');
        XMLCustomWriter::setAttribute($root, 'xmlns', SITEMAP_XSD_URL);

        $journals = $journalDao->getJournals(true);
        if ($journals) {
            while ($journal = $journals->next()) {
                // [WIZDAM] FIX: Gunakan $router->url() menggantikan $request->url() yang deprecated
                $sitemapUrl = $router->url($request, $journal->getPath(), 'sitemap');
                $sitemap = XMLCustomWriter::createElement($doc, 'sitemap');
                XMLCustomWriter::createChildWithText($doc, $sitemap, 'loc', $sitemapUrl, false);
                XMLCustomWriter::appendChild($root, $sitemap);
            }
        }
        
        XMLCustomWriter::appendChild($doc, $root);
        return $doc;
    }

    /**
     * Construct the sitemap for a specific journal.
     * 
     * @param PKPRequest $request
     * @param PKPRouter $router
     * @param Journal $journal
     * @return DOMDocument
     */
    protected function _createJournalSitemap($request, $router, $journal) {
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        
        $journalId = (int) $journal->getId();
        $journalPath = $journal->getPath();
        
        $doc = XMLCustomWriter::createDocument();
        $root = XMLCustomWriter::createElement($doc, 'urlset');
        XMLCustomWriter::setAttribute($root, 'xmlns', SITEMAP_XSD_URL);
        
        // [WIZDAM] DRY Principle: Closure untuk menghindari repetisi kode
        $addUrl = function($page, $op = 'index', $path = null, $date = null) use ($doc, $root, $router, $request, $journalPath) {
            $loc = $router->url($request, $journalPath, $page, $op, $path);
            XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $loc, $date));
        };

        // Journal home & About pages
        $addUrl('index', 'index');
        $addUrl('about');
        $addUrl('about', 'editorial-team');
        $addUrl('about', 'editorial-policies');
        $addUrl('about', 'submissions');
        $addUrl('about', 'siteMap');
        $addUrl('about', 'insights');
        
        // Search pages
        $addUrl('search');
        $addUrl('search', 'authors');
        $addUrl('search', 'titles');
        
        // Issues overview
        $addUrl('issue', 'current');
        $addUrl('volumes');
        
        // Published Issues & Articles
        $publishedIssues = $issueDao->getPublishedIssues($journalId);
        if ($publishedIssues) {
            while ($issue = $publishedIssues->next()) {
                $volumeId = $issue->getVolume();
                
                // [WIZDAM] Safe casting & fallback untuk mencegah slug kosong
                $issueNumber = $issue->getNumber() ?? (string) $issue->getId();
                $slug = PKPString::slugify((string) $issueNumber);
                $slug = $slug !== '' ? $slug : (string) $issue->getId();
                
                $loc = $request->getBaseUrl() . '/' . $journalPath . '/volumes/' . $volumeId . '/issue/' . $slug;
                
                $datePublished = $this->_formatDateForSitemap($issue->getDatePublished());
                XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $loc, $datePublished));
                
                // Articles for issue
                $articles = $publishedArticleDao->getPublishedArticles($issue->getId());
                if (is_array($articles)) {
                    foreach ($articles as $article) {
                        $articleDate = $this->_formatDateForSitemap(
                            $article->getLastModified() ?? $article->getDatePublished()
                        );

                        $articleId = (int) $article->getId();
                        
                        // Article abstract page
                        $addUrl('article', 'view', [$articleId], $articleDate);
                        
                        // Article galley pages
                        $galleys = $galleyDao->getGalleysByArticle($articleId);
                        if (is_array($galleys)) {
                            foreach ($galleys as $galley) {
                                $addUrl('article', 'view', [$articleId, (int) $galley->getId()], $articleDate);
                            }
                        }
                    }
                }
            }
        }
        
        XMLCustomWriter::appendChild($doc, $root);
        return $doc;
    }
    
    /**
     * Helper: Format date strictly for Sitemap (YYYY-MM-DD).
     * 
     * @param mixed $dateInput
     * @return string|null
     */
    protected function _formatDateForSitemap($dateInput) {
        if (empty($dateInput)) {
            return null;
        }
        
        $timestamp = strtotime((string) $dateInput);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return null;
    }

    /**
     * Helper: Send XML headers.
     * 
     * @param string $filename
     */
    protected function _sendXmlHeaders($filename) {
        header("Content-Type: application/xml; charset=UTF-8");
        header("Cache-Control: private, max-age=0, must-revalidate");
        header("Content-Disposition: inline; filename=\"" . (string) $filename . "\"");
    }

    /**
     * Create a url entry with children.
     * 
     * @param DOMDocument $doc
     * @param mixed $loc
     * @param mixed $lastmod
     * @param mixed $changefreq
     * @param mixed $priority
     * @return DOMElement
     */
    protected function _createUrlTree($doc, $loc, $lastmod = null, $changefreq = null, $priority = null) {        
        $url = XMLCustomWriter::createElement($doc, 'url');
        
        $safeLoc = (string) $loc;
        XMLCustomWriter::createChildWithText($doc, $url, 'loc', $safeLoc, false);
        
        if (!empty($lastmod)) {
            XMLCustomWriter::createChildWithText($doc, $url, 'lastmod', (string) $lastmod, false);
        }
        if (!empty($changefreq)) {
            XMLCustomWriter::createChildWithText($doc, $url, 'changefreq', (string) $changefreq, false);
        }
        if (!empty($priority)) {
            XMLCustomWriter::createChildWithText($doc, $url, 'priority', (string) $priority, false);
        }
        
        return $url;
    }
    
}
?>
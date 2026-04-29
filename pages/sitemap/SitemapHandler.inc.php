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
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance & Fix Method Signature
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
     * Generate an XML sitemap for webcrawlers
     * Creates a sitemap index if in site context, else creates a sitemap
     * * [WIZDAM FIX] Updated signature to match PKPHandler::index($args, $request)
     * @param array $args
     * @param PKPRequest $request
     */
    public function index($args = [], $request = null) {
        // [WIZDAM] Singleton Fallback if request is not passed
        if (!$request) $request = Application::get()->getRequest();

        // Validasi Request untuk memastikan path jurnal benar
        $journal = $request->getJournal();
        
        if ($request->getRequestedJournalPath() == 'index' || !$journal) {
            $doc = $this->_createSitemapIndex();
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: inline; filename=\"sitemap_index.xml\"");
            XMLCustomWriter::printXML($doc);
        } else {
            $doc = $this->_createJournalSitemap();
            header("Content-Type: application/xml");
            header("Cache-Control: private");
            header("Content-Disposition: inline; filename=\"sitemap.xml\"");
            XMLCustomWriter::printXML($doc);
        }
    }
      
    /**
     * Construct a sitemap index listing each journal's individual sitemap
     * @return XMLNode
     */
    public function _createSitemapIndex() {
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $request = Application::get()->getRequest();
        
        $doc = XMLCustomWriter::createDocument();
        $root = XMLCustomWriter::createElement($doc, 'sitemapindex');
        XMLCustomWriter::setAttribute($root, 'xmlns', SITEMAP_XSD_URL);

        $journals = $journalDao->getJournals(true);
        while ($journal = $journals->next()) {
            $sitemapUrl = $request->url($journal->getPath(), 'sitemap');
            $sitemap = XMLCustomWriter::createElement($doc, 'sitemap');
            XMLCustomWriter::createChildWithText($doc, $sitemap, 'loc', $sitemapUrl, false);
            XMLCustomWriter::appendChild($root, $sitemap);
            unset($journal);
        }
        
        XMLCustomWriter::appendChild($doc, $root);
        return $doc;
    }

     /**
     * Construct the sitemap
     * @return XMLNode
     */
    public function _createJournalSitemap() {
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        // [FIX 1] Fatal Error Handler
        // Jika tidak ada jurnal, kembalikan ke sitemap index (site-wide)
        // Jangan panggil parent::_createSiteSitemap karena method itu tidak ada di OJS 2 Handler
        if (!$journal) {
            return $this->_createSitemapIndex();
        }

        $journalId = $journal->getId();
        
        $doc = XMLCustomWriter::createDocument();
        $root = XMLCustomWriter::createElement($doc, 'urlset');
        XMLCustomWriter::setAttribute($root, 'xmlns', SITEMAP_XSD_URL);
        
        // Journal home
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(),'index','index')));
        
        // About page
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'about')));
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'about', 'editorial-team')));
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'about', 'editorial-policies')));
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'about', 'submissions')));
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'about', 'siteMap')));
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'about', 'insights')));
        
        // Search
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'search')));
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'search', 'authors')));
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'search', 'titles')));
        
        // Issues
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'issue', 'current')));
        XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'volumes')));
        
        // --- AWAL PERBAIKAN URL "NATIVE" ---
        $baseUrl = $request->getBaseUrl();
        $journalPath = $journal->getPath();
        
        $publishedIssues = $issueDao->getPublishedIssues($journalId);
        while ($issue = $publishedIssues->next()) {
            $volumeId = $issue->getVolume();
            $slug = PKPString::slugify($issue->getNumber());
            $loc = $baseUrl . '/' . $journalPath . '/volumes/' . $volumeId . '/issue/' . $slug;
            
            // [FIX 2] Date Formatting for GSC
            // Ambil tanggal, ubah ke format YYYY-MM-DD
            $datePublished = $issue->getDatePublished();
            if ($datePublished) {
                $datePublished = date('Y-m-d', strtotime($datePublished));
            }

            XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $loc, $datePublished));
            
            // Articles for issue
            $articles = $publishedArticleDao->getPublishedArticles($issue->getId());
            foreach($articles as $article) {
                // Artikel juga sebaiknya punya lastmod jika ada datanya
                // Di sini kita pakai lastModified atau datePublished
                $articleDate = $article->getLastModified() ? $article->getLastModified() : $article->getDatePublished();
                if ($articleDate) {
                    $articleDate = date('Y-m-d', strtotime($articleDate));
                }

                XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'article', 'view', [$article->getId()]), $articleDate));
                
                $galleys = $galleyDao->getGalleysByArticle($article->getId());
                foreach ($galleys as $galley) {
                    XMLCustomWriter::appendChild($root, $this->_createUrlTree($doc, $request->url($journal->getPath(), 'article', 'view', [$article->getId(), $galley->getId()]), $articleDate));
                }
            }
            unset($issue);
        }
        // --- AKHIR PERBAIKAN URL "NATIVE" ---
        
        XMLCustomWriter::appendChild($doc, $root);
        return $doc;
    }
    
    /**
     * Create a url entry with children
     * @param XMLNode $doc Reference to the XML document object
     * @param string $loc URL of page (required)
     * @param string|null $lastmod Last modification date of page (optional)
     * @param string|null $changefreq Frequency of page modifications (optional)
     * @param string|null $priority Subjective priority assesment of page (optional) 
     * @return XMLNode
     */
    public function _createUrlTree(&$doc, $loc, $lastmod = null, $changefreq = null, $priority = null) {        
        $url = XMLCustomWriter::createElement($doc, 'url');
        
        XMLCustomWriter::createChildWithText($doc, $url, 'loc', $loc, false);
        
        // [FIX 3] Prevent Empty Tags
        // Google Search Console akan error jika tag <lastmod> ada tapi isinya kosong
        // Kita cek if (!empty($var)) sebelum membuat child node.
        
        if (!empty($lastmod)) {
            XMLCustomWriter::createChildWithText($doc, $url, 'lastmod', $lastmod, false);
        }
        
        if (!empty($changefreq)) {
            XMLCustomWriter::createChildWithText($doc, $url, 'changefreq', $changefreq, false);
        }
        
        if (!empty($priority)) {
            XMLCustomWriter::createChildWithText($doc, $url, 'priority', $priority, false);
        }
        
        return $url;
    }
    
}
?>
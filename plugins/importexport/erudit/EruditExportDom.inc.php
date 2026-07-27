<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/erudit/EruditExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EruditExportDom
 * @ingroup plugins_importexport_erudit
 *
 * @brief Erudit plugin DOM functions for export
 */

import('lib.pkp.classes.xml.XMLCustomWriter');

class EruditExportDom {

    /**
     * Constructor
     */
    public function __construct() {
        // Static utility class
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EruditExportDom() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Generate article DOM element
     * @param DOMDocument $doc
     * @param Journal $journal
     * @param Issue $issue
     * @param PublishedArticle $article
     * @param ArticleGalley $galley
     * @return DOMElement
     */
    public static function generateArticleDom($doc, $journal, $issue, $article, $galley): DOMElement {
        $unavailableString = __('plugins.importexport.erudit.unavailable');

        $root = XMLCustomWriter::createElement($doc, 'article');
        XMLCustomWriter::setAttribute($root, 'idprop', (int) $journal->getId() . '-' . (int) $issue->getId() . '-' . (int) $article->getId() . '-' . (int) $galley->getId(), false);
        XMLCustomWriter::setAttribute($root, 'arttype', 'article');

        $lang = $article->getLanguage();
        XMLCustomWriter::setAttribute($root, 'lang', $lang ?: 'en');
        XMLCustomWriter::setAttribute($root, 'processing', 'cart');

        /* --- admin --- */
        $adminNode = XMLCustomWriter::createElement($doc, 'admin');
        XMLCustomWriter::appendChild($root, $adminNode);

        /* --- articleinfo --- */
        $articleInfoNode = XMLCustomWriter::createElement($doc, 'articleinfo');
        XMLCustomWriter::appendChild($adminNode, $articleInfoNode);

        // The first public ID should be a full URL to the article.
        // [WIZDAM FIX] Replace deprecated static Request:: call with Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $urlIdNode = XMLCustomWriter::createChildWithText(
            $doc, 
            $articleInfoNode, 
            'idpublic', 
            $request->url($journal->getPath(), 'article', 'view', [(int) $article->getId(), (int) $galley->getId()])
        );
        XMLCustomWriter::setAttribute($urlIdNode, 'scheme', 'sici');

        /* --- journal --- */
        $journalNode = XMLCustomWriter::createElement($doc, 'journal');
        XMLCustomWriter::appendChild($adminNode, $journalNode);
        XMLCustomWriter::setAttribute($journalNode, 'id', 'ojs-' . $journal->getPath());
        XMLCustomWriter::createChildWithText($doc, $journalNode, 'jtitle', (string) $journal->getTitle($journal->getPrimaryLocale()));
        XMLCustomWriter::createChildWithText($doc, $journalNode, 'jshorttitle', (string) $journal->getSetting('initials', $journal->getPrimaryLocale()), false);

        $printIssn = (string) $journal->getSetting('printIssn');
        if (empty($printIssn)) {
            $printIssn = $unavailableString;
        }
        XMLCustomWriter::createChildWithText($doc, $journalNode, 'idissn', $printIssn);

        $onlineIssn = (string) $journal->getSetting('onlineIssn');
        if (empty($onlineIssn)) {
            $onlineIssn = $unavailableString;
        }
        XMLCustomWriter::createChildWithText($doc, $journalNode, 'iddigissn', $onlineIssn);

        /* --- issue --- */
        $issueNode = XMLCustomWriter::createElement($doc, 'issue');
        XMLCustomWriter::appendChild($adminNode, $issueNode);
        XMLCustomWriter::setAttribute($issueNode, 'id', 'ojs-' . $issue->getBestIssueId());
        XMLCustomWriter::createChildWithText($doc, $issueNode, 'volume', (string) $issue->getVolume(), false);
        XMLCustomWriter::createChildWithText($doc, $issueNode, 'issueno', (string) $issue->getNumber(), false);

        $pubNode = XMLCustomWriter::createElement($doc, 'pub');
        XMLCustomWriter::appendChild($issueNode, $pubNode);
        XMLCustomWriter::createChildWithText($doc, $pubNode, 'year', (string) $issue->getYear());

        $digPubNode = XMLCustomWriter::createElement($doc, 'digpub');
        XMLCustomWriter::appendChild($issueNode, $digPubNode);
        $datePublished = $issue->getDatePublished();
        if (!empty($datePublished)) {
            XMLCustomWriter::createChildWithText($doc, $digPubNode, 'date', self::formatDate((string) $datePublished));
        }

        /* --- Publisher & DTD --- */
        $publisherInstitution = (string) $journal->getSetting('publisherInstitution');
        $publisherNode = XMLCustomWriter::createElement($doc, 'publisher');
        XMLCustomWriter::setAttribute($publisherNode, 'id', 'ojs-' . (int) $journal->getId() . '-' . (int) $issue->getId() . '-' . (int) $article->getId());
        XMLCustomWriter::appendChild($adminNode, $publisherNode);
        
        if (empty($publisherInstitution)) {
            $publisherInstitution = $unavailableString;
        }
        XMLCustomWriter::createChildWithText($doc, $publisherNode, 'orgname', $publisherInstitution);

        $digprodNode = XMLCustomWriter::createElement($doc, 'digprod');
        XMLCustomWriter::createChildWithText($doc, $digprodNode, 'orgname', $publisherInstitution);
        XMLCustomWriter::setAttribute($digprodNode, 'id', 'ojs-prod-' . (int) $journal->getId() . '-' . (int) $issue->getId() . '-' . (int) $article->getId());
        XMLCustomWriter::appendChild($adminNode, $digprodNode);

        $digdistNode = XMLCustomWriter::createElement($doc, 'digdist');
        XMLCustomWriter::createChildWithText($doc, $digdistNode, 'orgname', $publisherInstitution);
        XMLCustomWriter::setAttribute($digdistNode, 'id', 'ojs-dist-' . (int) $journal->getId() . '-' . (int) $issue->getId() . '-' . (int) $article->getId());
        XMLCustomWriter::appendChild($adminNode, $digdistNode);

        $dtdNode = XMLCustomWriter::createElement($doc, 'dtd');
        XMLCustomWriter::appendChild($adminNode, $dtdNode);
        XMLCustomWriter::setAttribute($dtdNode, 'name', 'Erudit Article');
        XMLCustomWriter::setAttribute($dtdNode, 'version', '3.0.0');

        /* --- copyright --- */
        $copyright = $journal->getSetting('copyrightNotice', $journal->getPrimaryLocale());
        XMLCustomWriter::createChildWithText($doc, $adminNode, 'copyright', empty($copyright) ? $unavailableString : (string) $copyright);

        /* --- frontmatter --- */
        $frontMatterNode = XMLCustomWriter::createElement($doc, 'frontmatter');
        XMLCustomWriter::appendChild($root, $frontMatterNode);

        $titleGroupNode = XMLCustomWriter::createElement($doc, 'titlegr');
        XMLCustomWriter::appendChild($frontMatterNode, $titleGroupNode);

        XMLCustomWriter::createChildWithText($doc, $titleGroupNode, 'title', strip_tags((string) $article->getTitle($article->getLocale())));

        /* --- authorgr --- */
        $authorGroupNode = XMLCustomWriter::createElement($doc, 'authorgr');
        XMLCustomWriter::appendChild($frontMatterNode, $authorGroupNode);
        $authorNum = 1;
        
        $authors = $article->getAuthors();
        if (is_array($authors)) {
            foreach ($authors as $author) {
                $authorNode = XMLCustomWriter::createElement($doc, 'author');
                XMLCustomWriter::appendChild($authorGroupNode, $authorNode);
                XMLCustomWriter::setAttribute($authorNode, 'id', 'ojs-' . (int) $journal->getId() . '-' . (int) $issue->getId() . '-' . (int) $article->getId() . '-' . (int) $galley->getId() . '-' . $authorNum);

                $persNameNode = XMLCustomWriter::createElement($doc, 'persname');
                XMLCustomWriter::appendChild($authorNode, $persNameNode);

                XMLCustomWriter::createChildWithText($doc, $persNameNode, 'firstname', (string) $author->getFirstName());
                XMLCustomWriter::createChildWithText($doc, $persNameNode, 'middlename', (string) $author->getMiddleName(), false);
                XMLCustomWriter::createChildWithText($doc, $persNameNode, 'familyname', (string) $author->getLastName());

                $affiliation = (string) $author->getAffiliation($article->getLocale());
                if ($affiliation !== '') {
                    $affiliationNode = XMLCustomWriter::createElement($doc, 'affiliation');
                    XMLCustomWriter::appendChild($authorNode, $affiliationNode);
                    XMLCustomWriter::createChildWithText($doc, $affiliationNode, 'blocktext', $affiliation, false);
                }

                $authorNum++;
            }
        }

        /* --- abstract and keywords --- */
        foreach ((array) $article->getAbstract(null) as $locale => $abstract) {
            $abstractNode = XMLCustomWriter::createElement($doc, 'abstract');
            XMLCustomWriter::setAttribute($abstractNode, 'lang', (string) $locale);
            XMLCustomWriter::appendChild($frontMatterNode, $abstractNode);
            XMLCustomWriter::createChildWithText($doc, $abstractNode, 'blocktext', strip_tags((string) $abstract));
        }

        $keywords = $article->getSubject($article->getLocale());
        if (!empty($keywords)) {
            $keywordGroupNode = XMLCustomWriter::createElement($doc, 'keywordgr');
            $language = $article->getLanguage() ?: 'en';
            XMLCustomWriter::setAttribute($keywordGroupNode, 'lang', $language);
            
            foreach (explode(';', (string) $keywords) as $keyword) {
                $trimmedKeyword = trim($keyword);
                if ($trimmedKeyword !== '') {
                    XMLCustomWriter::createChildWithText($doc, $keywordGroupNode, 'keyword', $trimmedKeyword, false);
                }
            }
            XMLCustomWriter::appendChild($frontMatterNode, $keywordGroupNode);
        }

        /* --- body --- */
        $bodyNode = XMLCustomWriter::createElement($doc, 'body');
        XMLCustomWriter::appendChild($root, $bodyNode);

        import('classes.file.ArticleFileManager');
        $articleFileManager = new ArticleFileManager((int) $article->getId());
        $file = $articleFileManager->getFile((int) $galley->getFileId());

        if ($file) {
            $parser = SearchFileParser::fromFile($file);
            if ($parser && $parser->open()) {
                // File supports text indexing.
                $textNode = XMLCustomWriter::createElement($doc, 'text');
                XMLCustomWriter::appendChild($bodyNode, $textNode);

                while (($line = $parser->read()) !== false) {
                    $line = trim((string) $line);
                    if ($line !== '') {
                        XMLCustomWriter::createChildWithText($doc, $textNode, 'blocktext', $line, false);
                    }
                }
                $parser->close();
            }
        }

        return $root;
    }

    /**
     * Format date
     * @param string|null $date
     * @return string|null
     */
    public static function formatDate($date): ?string {
        if ($date === '') {
            return null;
        }
        $timestamp = strtotime((string) $date);
        return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
    }

}
?>
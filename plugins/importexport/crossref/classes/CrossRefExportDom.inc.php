<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/classes/CrossRefExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CrossRefExportDom
 * @ingroup plugins_importexport_crossref_classes
 *
 * @brief CrossRef XML export format implementation.
 */

if (!class_exists('DOIExportDom')) {
    import('plugins.importexport.crossref.classes.DOIExportDom');
}

// XML attributes
define('CROSSREF_XMLNS_XSI' , 'http://www.w3.org/2001/XMLSchema-instance');
define('CROSSREF_XMLNS' , 'http://www.crossref.org/schema/4.3.6');
define('CROSSREF_VERSION' , '4.3.6');
define('CROSSREF_XSI_SCHEMAVERSION' , '4.3.6');
define('CROSSREF_XSI_SCHEMALOCATION' , 'http://www.crossref.org/schema/4.3.6 https://www.crossref.org/schemas/crossref4.3.6.xsd');

class CrossRefExportDom extends DOIExportDom {

    /**
     * Constructor
     * @param PKPRequest $request
     * @param DOIExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function __construct($request, $plugin, $journal, $objectCache) {
        parent::__construct($request, $plugin, $journal, $objectCache);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param PKPRequest $request
     * @param DOIExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function CrossRefExportDom($request, $plugin, $journal, $objectCache) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Public methods
    //
    /**
     * Generate the CrossRef XML document.
     * @param array $objects Array of objects to export
     * @return object XMLDocument
     * @see DOIExportDom::generate()
     */
    public function generate($objects) {
        $journal = $this->getJournal();

        // Create the XML document and its root element.
        $doc = $this->getDoc();
        $rootElement = $this->rootElement();
        XMLCustomWriter::appendChild($doc, $rootElement);

        // Create Head Node and all parts inside it
        $head = $this->_generateHeadDom($doc, $journal);
        XMLCustomWriter::appendChild($rootElement, $head);

        // The body node contains everything
        $bodyNode = XMLCustomWriter::createElement($doc, 'body');
        XMLCustomWriter::appendChild($rootElement, $bodyNode);

        foreach ($objects as $object) {
            // Retrieve required publication objects.
            $pubObjects = $this->retrievePublicationObjects($object);
            $issue = $pubObjects['issue'] ?? null;
            $article = $pubObjects['article'] ?? null;

            if ($object instanceof Issue && $issue) {
                $articlesByIssue = $pubObjects['articlesByIssue'] ?? [];
                foreach ($articlesByIssue as $articleItem) {
                    if ($articleItem->getPubId('doi')) {
                        $this->_appendArticleXML($doc, $journal, $issue, $articleItem, $bodyNode);
                    }
                }
            } elseif ($object instanceof PublishedArticle && $article) {
                if ($article->getPubId('doi')) {
                    $this->_appendArticleXML($doc, $journal, $issue, $article, $bodyNode);
                }
            }
        }

        return $doc;
    }

    //
    // Implementation of template methods from DOIExportDom
    //
    /**
     * Get the name of the root element.
     * @see DOIExportDom::getRootElementName()
     * @return string
     */
    public function getRootElementName(): string {
        return 'doi_batch';
    }

    /**
     * Get the XML namespace.
     * @see DOIExportDom::getNamespace()
     * @return string
     */
    public function getNamespace(): string {
        return CROSSREF_XMLNS;
    }

    /**
     * Get the XML schema version.
     * @see DOIExportDom::getXmlSchemaVersion()
     * @return string
     */
    public function getXmlSchemaVersion(): string {
        return CROSSREF_XSI_SCHEMAVERSION;
    }

    /**
     * Get the XML schema location.
     * @see DOIExportDom::getXmlSchemaLocation()
     * @return string
     */
    public function getXmlSchemaLocation(): string {
        return CROSSREF_XSI_SCHEMALOCATION;
    }

    /**
     * Retrieve the publication objects required for export.
     * @param Issue|PublishedArticle|ArticleGalley $object
     * @return array
     * @see DOIExportDom::retrievePublicationObjects()
     */
    public function retrievePublicationObjects($object): array {
        // Retrieve basic Lumera objects.
        $publicationObjects = parent::retrievePublicationObjects($object);

        // For issues: Retrieve all articles of the issue.
        if ($object instanceof Issue && isset($publicationObjects['issue'])) {
            $publicationObjects['articlesByIssue'] = $this->retrieveArticlesByIssue($publicationObjects['issue']);
        }

        return $publicationObjects;
    }

    //
    // Private helper methods
    //
    /**
     * Generate the <head> tag that accompanies each submission
     * @see DOIExportDom::generateHeadDom()
     * @param object $doc
     * @param Journal $journal
     * @return object
     */
    public function _generateHeadDom($doc, $journal) {
        $head = XMLCustomWriter::createElement($doc, 'head');

        // DOI batch ID is a simple tracking ID: initials + timestamp
        $initials = (string) $journal->getSetting('initials', $journal->getPrimaryLocale());
        XMLCustomWriter::createChildWithText($doc, $head, 'doi_batch_id', $initials . '_' . time());
        XMLCustomWriter::createChildWithText($doc, $head, 'timestamp', (string) time());

        $journalId = (int) $journal->getId();
        $plugin = $this->_plugin;

        /* Depositor defaults to the Journal's technical Contact */
        $depositorName = (string) $plugin->getSetting($journalId, 'depositorName');
        if ($depositorName === '') {
            $depositorName = (string) $journal->getSetting('supportName');
        }
        
        $depositorEmail = (string) $plugin->getSetting($journalId, 'depositorEmail');
        if ($depositorEmail === '') {
            $depositorEmail = (string) $journal->getSetting('supportEmail');
        }
        
        $depositorNode = $this->_generateDepositorDom($doc, $depositorName, $depositorEmail);
        XMLCustomWriter::appendChild($head, $depositorNode);

        /* The registrant is assumed to be the Publishing institution */
        $publisherInstitution = (string) $journal->getSetting('publisherInstitution');
        XMLCustomWriter::createChildWithText($doc, $head, 'registrant', $publisherInstitution);

        return $head;
    }

    /**
     * Generate depositor node
     * @see DOIExportDom::generateDepositorDom()
     * @param object $doc
     * @param string $name
     * @param string $email
     * @return object
     */
    public function _generateDepositorDom($doc, $name, $email) {
        $depositor = XMLCustomWriter::createElement($doc, 'depositor');
        XMLCustomWriter::createChildWithText($doc, $depositor, 'depositor_name', $name);
        XMLCustomWriter::createChildWithText($doc, $depositor, 'email_address', $email);

        return $depositor;
    }

    /**
     * Generate and append the XML per article
     * @see DOIExportDom::appendArticleXML()
     * @param object $doc
     * @param Journal $journal
     * @param Issue $issue
     * @param PublishedArticle $article
     * @param object $bodyNode
     */
    public function _appendArticleXML($doc, $journal, $issue, $article, $bodyNode) {
        $sectionId = (int) $article->getSectionId();
        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $section = $sectionDao->getSection($sectionId);

        // Create the journal node
        $journalNode = XMLCustomWriter::createElement($doc, 'journal');
        $journalMetadataNode = $this->_generateJournalMetadataDom($doc, $journal);
        XMLCustomWriter::appendChild($journalNode, $journalMetadataNode);

        // Create the journal_issue node
        $journalIssueNode = $this->_generateJournalIssueDom($doc, $journal, $issue, $section, $article);
        XMLCustomWriter::appendChild($journalNode, $journalIssueNode);

        // Create the article node
        $journalArticleNode = $this->_generateJournalArticleDom($doc, $journal, $issue, $section, $article);
        XMLCustomWriter::appendChild($journalNode, $journalArticleNode);
        XMLCustomWriter::appendChild($bodyNode, $journalNode);
    }

    /**
     * Generate metadata for journal - accompanies every article
     * @see DOIExportDom::generateJournalMetadataDom()
     * @param object $doc
     * @param Journal $journal
     * @return object
     */
    public function _generateJournalMetadataDom($doc, $journal) {
        $journalMetadataNode = XMLCustomWriter::createElement($doc, 'journal_metadata');

        /* Full Title of Journal */
        $journalTitle = (string) $journal->getTitle($journal->getPrimaryLocale());
        // Attempt a fall back, in case the localized name is not set.
        if ($journalTitle === '') {
            $journalTitle = (string) $journal->getSetting('abbreviation', $journal->getPrimaryLocale());
        }
        XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'full_title', $journalTitle);

        /* Abbreviated title - defaulting to initials if no abbreviation found */
        $abbreviation = (string) $journal->getSetting('abbreviation', $journal->getPrimaryLocale());
        if ($abbreviation !== '') {
            XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'abbrev_title', $abbreviation);
        } else {
            $initials = (string) $journal->getSetting('initials', $journal->getPrimaryLocale());
            XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'abbrev_title', $initials);
        }

        /* Both ISSNs are permitted for CrossRef, so sending whichever one (or both) */
        $onlineIssn = (string) $journal->getSetting('onlineIssn');
        if ($onlineIssn !== '') {
            $onlineISSNNode = XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'issn', $onlineIssn);
            XMLCustomWriter::setAttribute($onlineISSNNode, 'media_type', 'electronic');
        }

        $printIssn = (string) $journal->getSetting('printIssn');
        if ($printIssn !== '') {
            $printISSNNode = XMLCustomWriter::createChildWithText($doc, $journalMetadataNode, 'issn', $printIssn);
            XMLCustomWriter::setAttribute($printISSNNode, 'media_type', 'print');
        }

        return $journalMetadataNode;
    }

    /**
     * Generate journal issue tag to accompany every article
     * @see DOIExportDom::generateJournalIssueDom()
     * @param object $doc
     * @param Journal $journal
     * @param Issue $issue
     * @param Section $section
     * @param PublishedArticle $article
     * @return object
     */
    public function _generateJournalIssueDom($doc, $journal, $issue, $section, $article) {
        $journalIssueNode = XMLCustomWriter::createElement($doc, 'journal_issue');

        if ($issue->getDatePublished()) {
            $publicationDateNode = $this->_generatePublisherDateDom($doc, $issue->getDatePublished());
            XMLCustomWriter::appendChild($journalIssueNode, $publicationDateNode);
        }

        $volume = $issue->getVolume();
        if ($volume !== null && $volume !== '') {
            $journalVolumeNode = XMLCustomWriter::createElement($doc, 'journal_volume');
            XMLCustomWriter::appendChild($journalIssueNode, $journalVolumeNode);
            XMLCustomWriter::createChildWithText($doc, $journalVolumeNode, 'volume', (string) $volume);
        }
        
        $number = $issue->getNumber();
        if ($number !== null && $number !== '') {
            XMLCustomWriter::createChildWithText($doc, $journalIssueNode, 'issue', (string) $number);
        }

        $issueDoi = $issue->getPubId('doi');
        if ($issue->getDatePublished() && $issueDoi) {
            $request = $this->getRequest();
            $issueUrl = $request->url($journal->getPath(), 'issue', 'view', [$issue->getBestIssueId($journal)]);
            $issueDoiNode = $this->_generateDOIdataDom($doc, $issueDoi, $issueUrl);
            XMLCustomWriter::appendChild($journalIssueNode, $issueDoiNode);
        }

        return $journalIssueNode;
    }

    /**
     * Generate the journal_article node (the heart of the file).
     * @see DOIExportDom::generateJournalArticleDom()
     * @param object $doc
     * @param Journal $journal
     * @param Issue $issue
     * @param Section $section
     * @param PublishedArticle $article
     * @return object
     */
    public function _generateJournalArticleDom($doc, $journal, $issue, $section, $article) {
        // Create the base node
        $journalArticleNode = XMLCustomWriter::createElement($doc, 'journal_article');
        XMLCustomWriter::setAttribute($journalArticleNode, 'publication_type', 'full_text');
        XMLCustomWriter::setAttribute($journalArticleNode, 'metadata_distribution_opts', 'any');

        /* Titles */
        $titlesNode = XMLCustomWriter::createElement($doc, 'titles');
        XMLCustomWriter::createChildWithText($doc, $titlesNode, 'title', (string) $article->getTitle($article->getLocale()));
        XMLCustomWriter::appendChild($journalArticleNode, $titlesNode);

        /* AuthorList */
        $contributorsNode = XMLCustomWriter::createElement($doc, 'contributors');
        $isFirst = true;
        $authors = $article->getAuthors();
        if (is_array($authors)) {
            foreach ($authors as $author) {
                $authorNode = $this->_generateAuthorDom($doc, $author, $isFirst);
                $isFirst = false;
                XMLCustomWriter::appendChild($contributorsNode, $authorNode);
            }
        }
        XMLCustomWriter::appendChild($journalArticleNode, $contributorsNode);

        /* Abstracts */
        $abstract = $article->getAbstract($journal->getPrimaryLocale());
        if (!empty($abstract)) {
            $abstractNode = XMLCustomWriter::createElement($doc, 'jats:abstract');
            XMLCustomWriter::createChildWithText($doc, $abstractNode, 'jats:p', PKPString::html2utf(strip_tags((string) $abstract)));
            XMLCustomWriter::appendChild($journalArticleNode, $abstractNode);
        }

        /* publication date of article */
        $datePublished = $article->getDatePublished() ?: $issue->getDatePublished();
        if ($datePublished) {
            $publicationDateNode = $this->_generatePublisherDateDom($doc, $datePublished);
            XMLCustomWriter::appendChild($journalArticleNode, $publicationDateNode);
        }

        /* publisher_item is the article pages */
        $pages = $article->getPageArray();
        if (is_array($pages) && !empty($pages)) {
            $firstRange = array_shift($pages);
            $firstPage = (string) array_shift($firstRange);
            $lastPage = !empty($firstRange) ? (string) array_shift($firstRange) : '';
            
            // CrossRef accepts no punctuation in first_page or last_page
            if ((!empty($firstPage) || $firstPage === "0") && !preg_match('/[^[:alnum:]]/', $firstPage) && !preg_match('/[^[:alnum:]]/', $lastPage)) {
                $pageNode = XMLCustomWriter::createElement($doc, 'pages');
                XMLCustomWriter::createChildWithText($doc, $pageNode, 'first_page', $firstPage);
                if ($lastPage !== '') {
                    XMLCustomWriter::createChildWithText($doc, $pageNode, 'last_page', $lastPage);
                }
                $otherPages = '';
                foreach ($pages as $range) {
                    $otherPages .= ($otherPages !== '' ? ',' : '') . implode('-', $range);
                }
                if ($otherPages !== '') {
                    XMLCustomWriter::createChildWithText($doc, $pageNode, 'other_pages', $otherPages);
                }
                XMLCustomWriter::appendChild($journalArticleNode, $pageNode);
            }
        }

        /* License URL */
        $licenseUrl = $article->getLicenseUrl();
        if (!empty($licenseUrl)) {
            $licenseNode = XMLCustomWriter::createElement($doc, 'ai:program');
            XMLCustomWriter::setAttribute($licenseNode, 'name', 'AccessIndicators');
            XMLCustomWriter::createChildWithText($doc, $licenseNode, 'ai:license_ref', (string) $licenseUrl);
            XMLCustomWriter::appendChild($journalArticleNode, $licenseNode);
        }

        // DOI data node
        /** @var ArticleGalleyDAO $articleGalleyDao */
        $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $request = $this->getRequest();
        $articleUrl = $request->url($journal->getPath(), 'article', 'view', [$article->getBestArticleId()]);
        $galleys = $articleGalleyDao->getGalleysByArticle((int) $article->getId());
        
        $DOIdataNode = $this->_generateDOIdataDom($doc, (string) $article->getPubId('doi'), $articleUrl, $galleys);
        XMLCustomWriter::appendChild($journalArticleNode, $DOIdataNode);

        $componentListNode = $this->_generateComponentListDom($doc, $journal, $article);
        if ($componentListNode) {
            XMLCustomWriter::appendChild($journalArticleNode, $componentListNode);
        }

        return $journalArticleNode;
    }

    /**
     * Generate the component_list node (supplementary files).
     * @see DOIExportDom::generateComponentListDom()
     * @param object $doc
     * @param Journal $journal
     * @param PublishedArticle $article
     * @return object|null
     */
    public function _generateComponentListDom($doc, $journal, $article) {
        $suppFiles = $article->getSuppFiles();
        if (!is_array($suppFiles)) {
            return null;
        }

        $createComponentList = false;
        foreach ($suppFiles as $suppFile) {
            if ($suppFile->getPubId('doi')) {
                $createComponentList = true;
                break;
            }
        }
        
        if (!$createComponentList) {
            return null;
        }

        // Create the base node
        $componentListNode = XMLCustomWriter::createElement($doc, 'component_list');
        $request = $this->getRequest();

        // Run through supp files and add component nodes.
        foreach ($suppFiles as $suppFile) {
            if ($suppFile->getPubId('doi')) {
                $componentNode = XMLCustomWriter::createElement($doc, 'component');
                XMLCustomWriter::setAttribute($componentNode, 'parent_relation', 'isPartOf');

                /* Titles */
                $suppFileTitle = (string) $suppFile->getSuppFileTitle();
                if ($suppFileTitle !== '') {
                    $titlesNode = XMLCustomWriter::createElement($doc, 'titles');
                    XMLCustomWriter::createChildWithText($doc, $titlesNode, 'title', $suppFileTitle);
                    XMLCustomWriter::appendChild($componentNode, $titlesNode);
                }

                // DOI data node
                $suppFileUrl = $request->url(
                    $journal->getPath(), 'article', 'downloadSuppFile', 
                    [(int) $article->getId(), $suppFile->getBestSuppFileId($journal)]
                );
                $suppFileDoiNode = $this->_generateDOIdataDom($doc, (string) $suppFile->getPubId('doi'), $suppFileUrl);
                XMLCustomWriter::appendChild($componentNode, $suppFileDoiNode);
                XMLCustomWriter::appendChild($componentListNode, $componentNode);
            }
        }

        return $componentListNode;
    }

    /**
     * Generate doi_data element - this is what assigns the DOI
     * @param object $doc 
     * @param string $DOI
     * @param string $url
     * @param array|null $galleys
     * @return object
     */
    public function _generateDOIdataDom($doc, $DOI, $url, $galleys = null) {
        $journal = $this->getJournal();
        $request = $this->getRequest();
        $DOIdataNode = XMLCustomWriter::createElement($doc, 'doi_data');
        XMLCustomWriter::createChildWithText($doc, $DOIdataNode, 'doi', $DOI);
        XMLCustomWriter::createChildWithText($doc, $DOIdataNode, 'resource', $url);

        /* article galleys */
        if (is_array($galleys) && !empty($galleys)) {
            foreach ($galleys as $galley) {
                $collectionNode = XMLCustomWriter::createElement($doc, 'collection');
                XMLCustomWriter::setAttribute($collectionNode, 'property', 'crawler-based');
                XMLCustomWriter::appendChild($DOIdataNode, $collectionNode);
                
                $itemNode = XMLCustomWriter::createElement($doc, 'item');
                XMLCustomWriter::setAttribute($itemNode, 'crawler', 'iParadigms');
                XMLCustomWriter::appendChild($collectionNode, $itemNode);
                
                $resourceNode = XMLCustomWriter::createElement($doc, 'resource');
                XMLCustomWriter::appendChild($itemNode, $resourceNode);

                $galleyUrl = $request->url($journal->getPath(), 'article', 'viewFile', [(int) $galley->getArticleId(), $galley->getBestGalleyId($journal)]);
                $urlNode = XMLCustomWriter::createTextNode($doc, $galleyUrl);
                XMLCustomWriter::appendChild($resourceNode, $urlNode);
            }

            // text-mining collection element
            $collectionNode = XMLCustomWriter::createElement($doc, 'collection');
            XMLCustomWriter::setAttribute($collectionNode, 'property', 'text-mining');
            XMLCustomWriter::appendChild($DOIdataNode, $collectionNode);
            
            foreach ($galleys as $galley) {
                $itemNode = XMLCustomWriter::createElement($doc, 'item');
                XMLCustomWriter::appendChild($collectionNode, $itemNode);
                
                $resourceNode = XMLCustomWriter::createElement($doc, 'resource');
                XMLCustomWriter::appendChild($itemNode, $resourceNode);
                
                $remoteGalleyURL = $galley->getRemoteURL();
                if (empty($remoteGalleyURL)) {
                    XMLCustomWriter::setAttribute($resourceNode, 'mime_type', (string) $galley->getFileType());
                }

                $galleyUrl = $request->url($journal->getPath(), 'article', 'viewFile', [(int) $galley->getArticleId(), $galley->getBestGalleyId($journal)]);
                $urlNode = XMLCustomWriter::createTextNode($doc, $galleyUrl);
                XMLCustomWriter::appendChild($resourceNode, $urlNode);
            }
        }

        return $DOIdataNode;
    }

    /**
     * Generate author node
     * @param object $doc
     * @param Author $author
     * @param bool $isFirst
     * @return object
     */
    public function _generateAuthorDom($doc, $author, $isFirst = false) {
        $authorNode = XMLCustomWriter::createElement($doc, 'person_name');
        XMLCustomWriter::setAttribute($authorNode, 'contributor_role', 'author');
        if ($isFirst) {
            XMLCustomWriter::setAttribute($authorNode, 'sequence', 'first');
        } else {
            XMLCustomWriter::setAttribute($authorNode, 'sequence', 'additional');
        }

        $firstName = ucfirst((string) $author->getFirstName());
        $middleName = $author->getMiddleName() ? ' ' . ucfirst((string) $author->getMiddleName()) : '';
        XMLCustomWriter::createChildWithText($doc, $authorNode, 'given_name', $firstName . $middleName);
        XMLCustomWriter::createChildWithText($doc, $authorNode, 'surname', ucfirst((string) $author->getLastName()));
        
        $orcid = $author->getData('orcid');
        if (!empty($orcid)) {
            XMLCustomWriter::createChildWithText($doc, $authorNode, 'ORCID', (string) $orcid);
        }

        return $authorNode;
    }

    /**
     * Generate publisher date - order matters
     * @param object $doc
     * @param string $pubdate 
     * @return object
     */
    public function _generatePublisherDateDom($doc, $pubdate) {
        $publicationDateNode = XMLCustomWriter::createElement($doc, 'publication_date');
        XMLCustomWriter::setAttribute($publicationDateNode, 'media_type', 'online');

        $parsedPubdate = strtotime((string) $pubdate);
        if ($parsedPubdate !== false) {
            XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'month', date('m', $parsedPubdate), false);
            XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'day', date('d', $parsedPubdate), false);
            XMLCustomWriter::createChildWithText($doc, $publicationDateNode, 'year', date('Y', $parsedPubdate));
        }

        return $publicationDateNode;
    }
    
}
?>
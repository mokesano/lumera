<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/datacite/classes/DataciteExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataciteExportDom
 * @ingroup plugins_importexport_datacite_classes
 *
 * @brief DataCite XML export format implementation.
 */

if (!class_exists('DOIExportDom')) {
    import('plugins.importexport.datacite.classes.DOIExportDom');
}

// XML attributes
define('DATACITE_XMLNS' , 'http://datacite.org/schema/kernel-3');
define('DATACITE_XSI_SCHEMALOCATION' , 'http://datacite.org/schema/kernel-3 http://schema.datacite.org/meta/kernel-3/metadata.xsd');

// Date types
define('DATACITE_DATE_AVAILABLE', 'Available');
define('DATACITE_DATE_ISSUED', 'Issued');
define('DATACITE_DATE_SUBMITTED', 'Submitted');
define('DATACITE_DATE_ACCEPTED', 'Accepted');
define('DATACITE_DATE_CREATED', 'Created');
define('DATACITE_DATE_UPDATED', 'Updated');

// Identifier types
define('DATACITE_IDTYPE_PROPRIETARY', 'publisherId');
define('DATACITE_IDTYPE_EISSN', 'EISSN');
define('DATACITE_IDTYPE_ISSN', 'ISSN');
define('DATACITE_IDTYPE_DOI', 'DOI');

// Title types
define('DATACITE_TITLETYPE_TRANSLATED', 'TranslatedTitle');
define('DATACITE_TITLETYPE_ALTERNATIVE', 'AlternativeTitle');

// Relation types
define('DATACITE_RELTYPE_ISVARIANTFORMOF', 'IsVariantFormOf');
define('DATACITE_RELTYPE_HASPART', 'HasPart');
define('DATACITE_RELTYPE_ISPARTOF', 'IsPartOf');
define('DATACITE_RELTYPE_ISPREVIOUSVERSIONOF', 'IsPreviousVersionOf');
define('DATACITE_RELTYPE_ISNEWVERSIONOF', 'IsNewVersionOf');

// Description types
define('DATACITE_DESCTYPE_ABSTRACT', 'Abstract');
define('DATACITE_DESCTYPE_SERIESINFO', 'SeriesInformation');
define('DATACITE_DESCTYPE_TOC', 'TableOfContents');
define('DATACITE_DESCTYPE_OTHER', 'Other');

class DataciteExportDom extends DOIExportDom {

    //
    // Constructor (modern + compatibility shim)
    //
    /**
     * DataciteExportDom constructor.
     * @param PKPRequest $request
     * @param DOIExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function __construct($request, $plugin, $journal, $objectCache) {
        parent::__construct($request, $plugin, $journal, $objectCache);
    }

    /**
     * [SHIM] Backward-compatibility constructor.
     * @param PKPRequest $request
     * @param DOIExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function DataciteExportDom($request, $plugin, $journal, $objectCache) {
        if (class_exists('Config') && Config::getVar('debug', 'deprecation_warnings')) {
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
     * Generate the DataCite XML document for the given object.
     * @see DOIExportDom::generate()
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @return object|false An XML document or false if an error occurred.
     */
    public function generate($object) {
        $journal = $this->getJournal();
        
        $pubObjects = $this->retrievePublicationObjects($object);
        if (!is_array($pubObjects)) {
            return false;
        }

        // Explicit assignment replacing extract() for linter safety
        $issue = $pubObjects['issue'] ?? null;
        $article = $pubObjects['article'] ?? null;
        $galley = $pubObjects['galley'] ?? null;
        $suppFile = $pubObjects['suppFile'] ?? null;
        $articlesByIssue = $pubObjects['articlesByIssue'] ?? null;
        $galleysByArticle = $pubObjects['galleysByArticle'] ?? null;
        $suppFilesByArticle = $pubObjects['suppFilesByArticle'] ?? null;

        $articleFile = empty($suppFile) ? $galley : $suppFile;
        $objectLocalePrecedence = $this->getObjectLocalePrecedence($article, $galley, $suppFile);

        $publisher = ($object instanceof SuppFile) ? $object->getSuppFilePublisher() : null;
        if (empty($publisher)) {
            $publisher = $this->getPublisher($objectLocalePrecedence);
        }

        $publicationDate = ($article instanceof PublishedArticle) ? $article->getDatePublished() : null;
        if (empty($publicationDate) && $issue !== null) {
            $publicationDate = $issue->getDatePublished();
        }
        
        if (empty($publicationDate)) {
            return false;
        }

        $doc = $this->getDoc();
        $rootElement = $this->rootElement();
        XMLCustomWriter::appendChild($doc, $rootElement);

        $identifierElement = $this->_identifierElement($object);
        if ($identifierElement === false) {
            return false;
        }
        XMLCustomWriter::appendChild($rootElement, $identifierElement);

        XMLCustomWriter::appendChild($rootElement, $this->_creatorsElement($object, $objectLocalePrecedence, $publisher));
        XMLCustomWriter::appendChild($rootElement, $this->_titlesElement($object, $objectLocalePrecedence));
        XMLCustomWriter::createChildWithText($this->getDoc(), $rootElement, 'publisher', (string) $publisher);
        XMLCustomWriter::createChildWithText($this->getDoc(), $rootElement, 'publicationYear', date('Y', strtotime((string) $publicationDate)));

        if (!empty($suppFile)) {
            $this->_appendNonMandatoryChild($rootElement, $this->_subjectsElement($suppFile, $objectLocalePrecedence));
        } elseif (!empty($article)) {
            $this->_appendNonMandatoryChild($rootElement, $this->_subjectsElement($article, $objectLocalePrecedence));
        }

        XMLCustomWriter::appendChild($rootElement, $this->_datesElement($issue, $article, $articleFile, $suppFile, $publicationDate));
        XMLCustomWriter::createChildWithText($this->getDoc(), $rootElement, 'language', AppLocale::getIso1FromLocale($objectLocalePrecedence[0]));

        if (!($object instanceof SuppFile)) {
            $resourceTypeElement = $this->_resourceTypeElement($object);
            XMLCustomWriter::appendChild($rootElement, $resourceTypeElement);
        }

        $this->_appendNonMandatoryChild($rootElement, $this->_alternateIdentifiersElement($object, $issue, $article, $articleFile));
        $this->_appendNonMandatoryChild($rootElement, $this->_relatedIdentifiersElement($object, $articlesByIssue, $galleysByArticle, $suppFilesByArticle, $issue, $article));

        $sizesElement = $this->_sizesElement($object, $article);
        if ($sizesElement) {
            XMLCustomWriter::appendChild($rootElement, $sizesElement);
        }

        if (!empty($articleFile)) {
            $formatsElement = $this->_formatsElement($articleFile);
            if ($formatsElement) {
                XMLCustomWriter::appendChild($rootElement, $formatsElement);
            }
        }

        $rightsURL = $article ? $article->getLicenseURL() : $journal->getSetting('licenseURL');
        $rightsListElement = XMLCustomWriter::createElement($this->getDoc(), 'rightsList');
        $rightsElement = $this->createElementWithText('rights', strip_tags(Application::getCCLicenseBadge($rightsURL)), ['rightsURI' => (string) $rightsURL]);
        XMLCustomWriter::appendChild($rightsListElement, $rightsElement);
        XMLCustomWriter::appendChild($rootElement, $rightsListElement);

        $descriptionsElement = $this->_descriptionsElement($issue, $article, $suppFile, $objectLocalePrecedence, $articlesByIssue);
        if ($descriptionsElement) {
            XMLCustomWriter::appendChild($rootElement, $descriptionsElement);
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
    public function getRootElementName() {
        return 'resource';
    }

    /**
     * Get the DataCite XML namespace.
     * @see DOIExportDom::getNamespace()
     * @return string
     */    
    public function getNamespace() {
        return DATACITE_XMLNS;
    }

    /**
     * Get the XML schema location.
     * @see DOIExportDom::getXmlSchemaLocation()
     * @return string
     */
    public function getXmlSchemaLocation() {
        return DATACITE_XSI_SCHEMALOCATION;
    }

    /**
     * Retrieve all publication objects required for export of the given object.
     * @see DOIExportDom::retrievePublicationObjects()
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @return array|false
     */
    public function retrievePublicationObjects($object) {
        $journal = $this->getJournal();
        $cache = $this->getCache();

        $publicationObjects = parent::retrievePublicationObjects($object);
        if (!is_array($publicationObjects)) {
            return false;
        }

        if ($object instanceof SuppFile && isset($publicationObjects['article'])) {
            $cache->add($object, $publicationObjects['article']);
            $publicationObjects['suppFile'] = $object;
        }

        if ($object instanceof PublishedArticle && isset($publicationObjects['article'])) {
            $article = $publicationObjects['article'];
            $publicationObjects['galleysByArticle'] = $this->retrieveGalleysByArticle($article);
            $publicationObjects['suppFilesByArticle'] = $this->_retrieveSuppFilesByArticle($article);
        }

        if ($object instanceof Issue && isset($publicationObjects['issue'])) {
            $issue = $publicationObjects['issue'];
            $publicationObjects['articlesByIssue'] = $this->retrieveArticlesByIssue($issue);
        }

        return $publicationObjects;
    }

    /**
     * Get the locale precedence for the given objects.
     * @see DOIExportDom::getObjectLocalePrecedence()
     * @param PublishedArticle $article
     * @param ArticleGalley $galley
     * @param SuppFile|null $suppFile
     * @return array
     */
    public function getObjectLocalePrecedence($article, $galley, $suppFile = null) {
        $locales = [];
        if ($suppFile instanceof SuppFile) {
            $suppFileLocale = $this->translateLanguageToLocale((string) $suppFile->getLanguage());
            if ($suppFileLocale !== null) {
                $locales[] = $suppFileLocale;
            }
        }

        $parentLocales = parent::getObjectLocalePrecedence($article, $galley);
        return array_merge($locales, $parentLocales);
    }

    /**
     * Identify the publisher of the journal.
     * @param array $localePrecedence
     * @return string
     */
    public function getPublisher($localePrecedence) {
        $journal = $this->getJournal();
        $rawTitle = $journal->getTitle(null);
        $titleArray = is_array($rawTitle) ? $rawTitle : (is_string($rawTitle) ? [$journal->getPrimaryLocale() => $rawTitle] : []);
        
        $publisher = $this->getPrimaryTranslation($titleArray, $localePrecedence);
        return (string) ($publisher ?? $journal->getTitle($journal->getPrimaryLocale()));
    }

    //
    // Protected helper methods
    //
    /**
     * Retrieve all supp files for the given article and commit them to the cache.
     * @param PublishedArticle $article
     * @return array
     */
    protected function _retrieveSuppFilesByArticle($article) {
        $cache = $this->getCache();
        $articleId = (int) $article->getId();
        
        if (!$cache->isCached('suppFilesByArticle', $articleId)) {
			/** @var SuppFileDAO $suppFileDao */
            $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
            $suppFiles = $suppFileDao->getSuppFilesByArticle($articleId);
            if (!empty($suppFiles)) {
                foreach ($suppFiles as $suppFile) {
                    $cache->add($suppFile, $article);
                }
                $cache->markComplete('suppFilesByArticle', $articleId);
            }
        }
        
        $suppFilesByArticle = $cache->get('suppFilesByArticle', $articleId);
        return is_array($suppFilesByArticle) ? $suppFilesByArticle : [];
    }

    /**
     * Create an identifier element.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @return object|false
     */
    protected function _identifierElement($object) {
        $doi = $object->getPubId('doi');
        if (empty($doi)) {
            $this->_addError('plugins.importexport.common.export.error.noDoiAssigned', $object->getId());
            return false;
        }
        if ($this->getTestMode()) {
            $doi = PKPString::regexp_replace('#^[^/]+/#', DATACITE_API_TESTPREFIX . '/', $doi);
        }
        return $this->createElementWithText('identifier', $doi, ['identifierType' => 'DOI']);
    }

    /**
     * Create the creators element list.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param array $objectLocalePrecedence
     * @param string $publisher
     * @return object
     */
    protected function _creatorsElement($object, $objectLocalePrecedence, $publisher) {
        $cache = $this->getCache();
        $creators = [];

        switch (true) {
            case $object instanceof SuppFile:
                $rawCreator = $object->getCreator(null);
                $creatorArray = is_array($rawCreator) ? $rawCreator : (is_string($rawCreator) ? ['default' => $rawCreator] : []);
                $creator = $this->getPrimaryTranslation($creatorArray, $objectLocalePrecedence);
                if (!empty($creator)) {
                    $creators[] = $creator;
                    break;
                }

            case $object instanceof ArticleGalley:
                $article = $cache->get('articles', (int) $object->getArticleId());

            case $object instanceof PublishedArticle:
                if (!isset($article)) {
                    $article = $object;
                }
                $authors = $article->getAuthors();
                if (is_array($authors) && !empty($authors)) {
                    foreach ($authors as $author) {
                        $creators[] = $author->getFullName(true);
                    }
                }
                break;

            case $object instanceof Issue:
                $creators[] = $publisher;
                break;
        }

        $creatorsElement = XMLCustomWriter::createElement($this->getDoc(), 'creators');
        if (count($creators) >= 1) {
            foreach ($creators as $creator) {
                XMLCustomWriter::appendChild($creatorsElement, $this->_creatorElement($creator));
            }
        }
        return $creatorsElement;
    }

    /**
     * Create a single creator element.
     * @param string $creator
     * @return object
     */
    protected function _creatorElement($creator) {
        $creatorElement = XMLCustomWriter::createElement($this->getDoc(), 'creator');
        XMLCustomWriter::createChildWithText($this->getDoc(), $creatorElement, 'creatorName', $creator);
        return $creatorElement;
    }

    /**
     * Create the titles element list.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param array $objectLocalePrecedence
     * @return object
     */
    protected function _titlesElement($object, $objectLocalePrecedence) {
        $cache = $this->getCache();
        $alternativeTitle = null;
        $titles = [];

        switch (true) {
            case $object instanceof SuppFile:
                $rawTitles = $object->getTitle(null);
                $titles = is_array($rawTitles) ? $rawTitles : (is_string($rawTitles) ? ['default' => $rawTitles] : []);
                break;

            case $object instanceof ArticleGalley:
                $article = $cache->get('articles', (int) $object->getArticleId());

            case $object instanceof PublishedArticle:
                if (!isset($article)) {
                    $article = $object;
                }
                $rawTitles = $article->getTitle(null);
                $titles = is_array($rawTitles) ? $rawTitles : (is_string($rawTitles) ? [$article->getLocale() => $rawTitles] : []);
                break;

            case $object instanceof Issue:
                $titles = $this->_getIssueInformation($object);
                $rawAltTitle = $object->getTitle(null);
                $altTitleArray = is_array($rawAltTitle) ? $rawAltTitle : (is_string($rawAltTitle) ? [$this->getJournal()->getPrimaryLocale() => $rawAltTitle] : []);
                $alternativeTitle = $this->getPrimaryTranslation($altTitleArray, $objectLocalePrecedence);
                break;
        }

        $titles = $this->getTranslationsByPrecedence($titles, $objectLocalePrecedence);
        $titlesElement = XMLCustomWriter::createElement($this->getDoc(), 'titles');

        if (!empty($titles)) {
            $primaryTitle = array_shift($titles);
            XMLCustomWriter::appendChild($titlesElement, $this->_titleElement($primaryTitle));

            foreach ($titles as $locale => $title) {
                XMLCustomWriter::appendChild($titlesElement, $this->_titleElement($title, DATACITE_TITLETYPE_TRANSLATED));
            }
        }

        if (!empty($alternativeTitle)) {
            XMLCustomWriter::appendChild($titlesElement, $this->_titleElement($alternativeTitle, DATACITE_TITLETYPE_ALTERNATIVE));
        }

        return $titlesElement;
    }

    /**
     * Create a single title element.
     * @param string $title
     * @param string|null $titleType
     * @return object
     */
    protected function _titleElement($title, $titleType = null) {
        $titleElement = $this->createElementWithText('title', $title);
        if ($titleType !== null) {
            XMLCustomWriter::setAttribute($titleElement, 'titleType', $titleType);
        }
        return $titleElement;
    }

    /**
     * Create the subjects element list.
     * @param PublishedArticle|SuppFile $object
     * @param array $objectLocalePrecedence
     * @return object
     */
    protected function _subjectsElement($object, $objectLocalePrecedence) {
        $subjectsElement = XMLCustomWriter::createElement($this->getDoc(), 'subjects');
        
        if ($object instanceof SuppFile) {
            $rawSubject = $object->getSubject(null);
            $subjectArray = is_array($rawSubject) ? $rawSubject : (is_string($rawSubject) ? ['default' => $rawSubject] : []);
            $suppFileSubject = $this->getPrimaryTranslation($subjectArray, $objectLocalePrecedence);
            if (!empty($suppFileSubject)) {
                XMLCustomWriter::appendChild($subjectsElement, $this->_subjectElement($suppFileSubject));
            }
        } elseif ($object instanceof PublishedArticle) {
            $rawKeywords = $object->getSubject(null);
            $keywordsArray = is_array($rawKeywords) ? $rawKeywords : (is_string($rawKeywords) ? [$object->getLocale() => $rawKeywords] : []);
            $keywords = $this->getPrimaryTranslation($keywordsArray, $objectLocalePrecedence);
            if (!empty($keywords)) {
                XMLCustomWriter::appendChild($subjectsElement, $this->_subjectElement($keywords));
            }

            list($subjectSchemeName, $subjectCode) = $this->getSubjectClass($object, $objectLocalePrecedence);
            if (!empty($subjectSchemeName) && !empty($subjectCode)) {
                XMLCustomWriter::appendChild($subjectsElement, $this->_subjectElement($subjectCode, $subjectSchemeName));
            }
        }
        return $subjectsElement;
    }

    /**
     * Create a single subject element.
     * @param string $subject
     * @param string|null $subjectScheme
     * @return object
     */
    protected function _subjectElement($subject, $subjectScheme = null) {
        $subjectElement = $this->createElementWithText('subject', $subject);
        if (!empty($subjectScheme)) {
            XMLCustomWriter::setAttribute($subjectElement, 'subjectScheme', $subjectScheme);
        }
        return $subjectElement;
    }

    /**
     * Create a date element list.
     * @param Issue|null $issue
     * @param PublishedArticle|null $article
     * @param ArticleFile|null $articleFile
     * @param SuppFile|null $suppFile
     * @param string $publicationDate
     * @return object
     */
    protected function _datesElement($issue, $article, $articleFile, $suppFile, $publicationDate) {
        $datesElement = XMLCustomWriter::createElement($this->getDoc(), 'dates');
        $dates = [];

        if (!empty($suppFile)) {
            $createdDate = $suppFile->getDateCreated();
            if (!empty($createdDate)) {
                $dates[DATACITE_DATE_CREATED] = $createdDate;
            }
        }

        if (!empty($article)) {
            $submittedDate = $article->getDateSubmitted();
            if (!empty($submittedDate)) {
                $dates[DATACITE_DATE_SUBMITTED] = $submittedDate;
                $dates[DATACITE_DATE_ACCEPTED] = $submittedDate;
            }
        }

        if (!empty($suppFile)) {
            $submittedDate = $suppFile->getDateSubmitted();
            if (!empty($submittedDate)) {
                $dates[DATACITE_DATE_SUBMITTED] = $submittedDate;
            }
        }

        if (!empty($articleFile)) {
            $acceptedDate = $articleFile->getDateUploaded();
            if (!empty($acceptedDate)) {
                $dates[DATACITE_DATE_ACCEPTED] = $acceptedDate;
            }
        }

        $dates[DATACITE_DATE_ISSUED] = $publicationDate;

        $availableDate = $issue ? $issue->getOpenAccessDate() : null;
        if (!empty($availableDate)) {
            $dates[DATACITE_DATE_AVAILABLE] = $availableDate;
        }

        if (!empty($article) && empty($articleFile)) {
            $dates[DATACITE_DATE_UPDATED] = $article->getLastModified();
        }

        foreach ($dates as $dateType => $date) {
            if (!empty($date)) {
                XMLCustomWriter::appendChild($datesElement, $this->_dateElement($dateType, $date));
            }
        }

        return $datesElement;
    }

    /**
     * Create a single date element.
     * @param string $dateType
     * @param string $date
     * @return object
     */
    protected function _dateElement($dateType, $date) {
        $formattedDate = date('Y-m-d', strtotime((string) $date));
        return $this->createElementWithText('date', $formattedDate, ['dateType' => $dateType]);
    }

    /**
     * Create a resource type element.
     * @param Issue|PublishedArticle|ArticleGalley $object
     * @return object
     */
    protected function _resourceTypeElement($object) {
        if ($object instanceof Issue) {
            $resourceType = 'Journal Issue';
        } elseif ($object instanceof PublishedArticle || $object instanceof ArticleGalley) {
            $resourceType = 'Article';
        } else {
            throw new \BadMethodCallException('Unsupported object type for resource type.');
        }

        return $this->createElementWithText('resourceType', $resourceType, ['resourceTypeGeneral' => 'Text']);
    }

    /**
     * Generate alternate identifiers element list.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param Issue|null $issue
     * @param PublishedArticle|null $article
     * @param ArticleFile|null $articleFile
     * @return object
     */
    protected function _alternateIdentifiersElement($object, $issue, $article, $articleFile) {
        $journal = $this->getJournal();
        $alternateIdentifiersElement = XMLCustomWriter::createElement($this->getDoc(), 'alternateIdentifiers');

        $proprietaryId = $this->getProprietaryId($journal, $issue, $article, $articleFile);
        XMLCustomWriter::appendChild(
            $alternateIdentifiersElement,
            $this->createElementWithText(
                'alternateIdentifier', $proprietaryId,
                ['alternateIdentifierType' => DATACITE_IDTYPE_PROPRIETARY]
            )
        );

        if ($object instanceof Issue) {
            $onlineIssn = (string) $journal->getSetting('onlineIssn');
            if (!empty($onlineIssn)) {
                XMLCustomWriter::appendChild(
                    $alternateIdentifiersElement,
                    $this->createElementWithText(
                        'alternateIdentifier', $onlineIssn,
                        ['alternateIdentifierType' => DATACITE_IDTYPE_EISSN]
                    )
                );
            }

            $printIssn = (string) $journal->getSetting('printIssn');
            if (!empty($printIssn)) {
                XMLCustomWriter::appendChild(
                    $alternateIdentifiersElement,
                    $this->createElementWithText(
                        'alternateIdentifier', $printIssn,
                        ['alternateIdentifierType' => DATACITE_IDTYPE_ISSN]
                    )
                );
            }
        }

        return $alternateIdentifiersElement;
    }

    /**
     * Generate related identifiers element list.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param array|null $articlesByIssue
     * @param array|null $galleysByArticle
     * @param array|null $suppFilesByArticle
     * @param Issue|null $issue
     * @param PublishedArticle|null $article
     * @return object
     */
    protected function _relatedIdentifiersElement($object, $articlesByIssue, $galleysByArticle, $suppFilesByArticle, $issue, $article) {
        $relatedIdentifiersElement = XMLCustomWriter::createElement($this->getDoc(), 'relatedIdentifiers');

        switch (true) {
            case $object instanceof Issue:
                if (is_array($articlesByIssue)) {
                    foreach ($articlesByIssue as $articleInIssue) {
                        $doi = $this->_relatedIdentifierElement($articleInIssue, DATACITE_RELTYPE_HASPART);
                        if ($doi !== null) {
                            XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
                        }
                    }
                }
                break;

            case $object instanceof PublishedArticle:
                if ($issue instanceof Issue) {
                    $doi = $this->_relatedIdentifierElement($issue, DATACITE_RELTYPE_ISPARTOF);
                    if ($doi !== null) {
                        XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
                    }
                }

                if (is_array($galleysByArticle) && is_array($suppFilesByArticle)) {
                    $relType = DATACITE_RELTYPE_HASPART;
                    foreach ($galleysByArticle as $galleyInArticle) {
                        $doi = $this->_relatedIdentifierElement($galleyInArticle, $relType);
                        if ($doi !== null) {
                            XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
                        }
                    }
                    foreach ($suppFilesByArticle as $suppFileInArticle) {
                        $doi = $this->_relatedIdentifierElement($suppFileInArticle, $relType);
                        if ($doi !== null) {
                            XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
                        }
                    }
                }
                break;

            case $object instanceof ArticleFile:
                if ($article instanceof PublishedArticle) {
                    $doi = $this->_relatedIdentifierElement($article, DATACITE_RELTYPE_ISPARTOF);
                    if ($doi !== null) {
                        XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
                    }
                }
                break;
        }

        return $relatedIdentifiersElement;
    }

    /**
     * Create an identifier element with the object's DOI.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param string $relationType
     * @return object|null
     */
    protected function _relatedIdentifierElement($object, $relationType) {
        $id = $object->getPubId('doi');

        if (empty($id)) {
            return null;
        }
        if ($this->getTestMode()) {
            $id = PKPString::regexp_replace('#^[^/]+/#', DATACITE_API_TESTPREFIX . '/', $id);
        }

        return $this->createElementWithText(
            'relatedIdentifier', $id,
            [
                'relatedIdentifierType' => DATACITE_IDTYPE_DOI,
                'relationType' => $relationType
            ]
        );
    }

    /**
     * Create a sizes element list.
     * @param Issue|PublishedArticle|ArticleFile $object
     * @param PublishedArticle|null $article
     * @return object|null
     */
    protected function _sizesElement($object, $article) {
        $pages = '';
        $files = [];

        if ($object instanceof Issue) {
			/** @var IssueGalleyDAO $issueGalleyDao */
            $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
            $files = $issueGalleyDao->getGalleysByIssue((int) $object->getId());
        } elseif ($object instanceof PublishedArticle) {
            $pages = (string) $object->getPages();
        } elseif ($object instanceof ArticleFile) {
            if ($object instanceof ArticleGalley) {
                $pages = $article ? (string) $article->getPages() : '';
            }
            $files = [$object];
        } else {
            throw new \BadMethodCallException('Unsupported object type for sizes element.');
        }

        $sizes = [];
        if (!empty($pages)) {
            $sizes[] = $pages . ' ' . __('editor.issues.pages');
        }
        
        foreach ($files as $file) {
            $fileSize = round(((int) $file->getFileSize()) / 1024);
            if ($fileSize >= 1024) {
                $fileSize = round($fileSize / 1024, 2);
                $sizes[] = $fileSize . ' MB';
            } elseif ($fileSize >= 1) {
                $sizes[] = $fileSize . ' kB';
            }
        }

        if (!empty($sizes)) {
            $sizesElement = XMLCustomWriter::createElement($this->getDoc(), 'sizes');
            foreach ($sizes as $size) {
                XMLCustomWriter::createChildWithText($this->getDoc(), $sizesElement, 'size', $size);
            }
            return $sizesElement;
        }
        
        return null;
    }

    /**
     * Create a formats element list.
     * @param ArticleFile $articleFile
     * @return object|null
     */
    protected function _formatsElement($articleFile) {
        $format = $articleFile->getFileType();
        if (empty($format)) {
            return null;
        }

        $formatsElement = XMLCustomWriter::createElement($this->getDoc(), 'formats');
        XMLCustomWriter::createChildWithText($this->getDoc(), $formatsElement, 'format', $format);
        return $formatsElement;
    }

    /**
     * Create a descriptions element list.
     * @param Issue|null $issue
     * @param PublishedArticle|null $article
     * @param SuppFile|null $suppFile
     * @param array $objectLocalePrecedence
     * @param array|null $articlesByIssue
     * @return object|null
     */
    protected function _descriptionsElement($issue, $article, $suppFile, $objectLocalePrecedence, $articlesByIssue) {
        $descriptions = [];

        if (isset($article) && !isset($suppFile)) {
            $rawAbstract = $article->getAbstract(null);
            $abstractArray = is_array($rawAbstract) ? $rawAbstract : (is_string($rawAbstract) ? [$article->getLocale() => $rawAbstract] : []);
            $articleAbstract = $this->getPrimaryTranslation($abstractArray, $objectLocalePrecedence);
            if (!empty($articleAbstract)) {
                $descriptions[DATACITE_DESCTYPE_ABSTRACT] = $articleAbstract;
            }
        }

        if (isset($suppFile)) {
            $rawDesc = $suppFile->getDescription(null);
            $descArray = is_array($rawDesc) ? $rawDesc : (is_string($rawDesc) ? ['default' => $rawDesc] : []);
            $suppFileDesc = $this->getPrimaryTranslation($descArray, $objectLocalePrecedence);
            if (!empty($suppFileDesc)) {
                $descriptions[DATACITE_DESCTYPE_OTHER] = $suppFileDesc;
            }
        }

        if (isset($article)) {
            $descriptions[DATACITE_DESCTYPE_SERIESINFO] = $this->_getIssueInformation($issue, $objectLocalePrecedence);
        } else {
            $rawIssueDesc = $issue->getDescription(null);
            $issueDescArray = is_array($rawIssueDesc) ? $rawIssueDesc : (is_string($rawIssueDesc) ? [$this->getJournal()->getPrimaryLocale() => $rawIssueDesc] : []);
            $issueDesc = $this->getPrimaryTranslation($issueDescArray, $objectLocalePrecedence);
            
            if (!empty($issueDesc)) {
                $descriptions[DATACITE_DESCTYPE_OTHER] = $issueDesc;
            }
            $descriptions[DATACITE_DESCTYPE_TOC] = $this->_getIssueToc($articlesByIssue, $objectLocalePrecedence);
        }

        if (!empty($descriptions)) {
            $descriptionsElement = XMLCustomWriter::createElement($this->getDoc(), 'descriptions');
            foreach ($descriptions as $descType => $description) {
                XMLCustomWriter::appendChild(
                    $descriptionsElement,
                    $this->createElementWithText('description', $description, ['descriptionType' => $descType])
                );
            }
            return $descriptionsElement;
        }
        
        return null;
    }

    /**
     * Construct an issue title from the journal title and the issue identification.
     * @param Issue $issue
     * @param array|null $objectLocalePrecedence
     * @return array|string
     */
    protected function _getIssueInformation($issue, $objectLocalePrecedence = null) {
        $issueIdentification = $issue->getIssueIdentification();
        if (empty($issueIdentification)) {
            return is_array($objectLocalePrecedence) ? [] : '';
        }

        $journal = $this->getJournal();
        if ($objectLocalePrecedence === null) {
            $issueInfo = [];
            $rawJournalTitle = $journal->getTitle(null);
            $journalTitleArray = is_array($rawJournalTitle) ? $rawJournalTitle : (is_string($rawJournalTitle) ? [$journal->getPrimaryLocale() => $rawJournalTitle] : []);
            
            foreach ($journalTitleArray as $locale => $journalTitle) {
                $issueInfo[$locale] = "$journalTitle, $issueIdentification";
            }
            return $issueInfo;
        } else {
            $rawJournalTitle = $journal->getTitle(null);
            $journalTitleArray = is_array($rawJournalTitle) ? $rawJournalTitle : (is_string($rawJournalTitle) ? [$journal->getPrimaryLocale() => $rawJournalTitle] : []);
            
            $issueInfo = $this->getPrimaryTranslation($journalTitleArray, $objectLocalePrecedence);
            if (!empty($issueInfo)) {
                $issueInfo .= ', ';
            }
            $issueInfo .= $issueIdentification;
            return $issueInfo;
        }
    }

    /**
     * Construct a table of content from an article list.
     * @param array|null $articlesByIssue
     * @param array $objectLocalePrecedence
     * @return string
     */
    protected function _getIssueToc($articlesByIssue, $objectLocalePrecedence) {
        if (!is_array($articlesByIssue)) {
            return '';
        }
        
        $toc = '';
        foreach ($articlesByIssue as $articleInIssue) {
            $rawTitle = $articleInIssue->getTitle(null);
            $titleArray = is_array($rawTitle) ? $rawTitle : (is_string($rawTitle) ? [$articleInIssue->getLocale() => $rawTitle] : []);
            $currentEntry = $this->getPrimaryTranslation($titleArray, $objectLocalePrecedence);
            
            if (empty($currentEntry)) {
                continue;
            }
            
            $pages = $articleInIssue->getPages();
            if (!empty($pages)) {
                $currentEntry .= '...' . $pages;
            }
            $toc .= $currentEntry . "<br />";
        }
        return $toc;
    }

    /**
     * Datacite does not allow empty nodes. So we have to check nodes before we add them.
     * @param object $parentNode
     * @param object|null $child
     */
    protected function _appendNonMandatoryChild($parentNode, $child) {
        if ($child === null) {
            return;
        }
        
        if ($child instanceof XMLNode) {
            $childChildren = $child->getChildren();
            $childEmpty = empty($childChildren);
        } else {
            $childEmpty = !$child->hasChildNodes();
        }
        
        if ($childEmpty) {
            return;
        }
        
        XMLCustomWriter::appendChild($parentNode, $child);
    }

}
?>
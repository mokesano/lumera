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

if (!class_exists('DOIExportDom')) { // Bug #7848
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
	 * DataciteExportDom constructor (modern).
	 * @param Request $request
	 * @param DOIExportPlugin $plugin
	 * @param Journal $journal
	 * @param PubObjectCache $objectCache
	 */
	public function __construct($request, $plugin, $journal, $objectCache) {
		// Call parent constructor (modern usage).
		parent::__construct($request, $plugin, $journal, $objectCache);
	}

	/**
	 * [SHIM] Backward-compatibility constructor.
	 */
    public function DataciteExportDom() {
        if (class_exists('Config') && Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::DataciteExportDom(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }

        $args = func_get_args();
        return call_user_func_array(array($this, '__construct'), $args);
    }


	//
	// Public methods
	//
	/**
     * Generate the DataCite XML document for the given object.
	 * @see DOIExportDom::generate()
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @return XMLNode|DOMImplementation|false
     * @throws Exception
	 */
	public function generate($object) {
		// Declare variables that will contain publication objects.
		$journal = $this->getJournal();
		$issue = null; /* @var $issue Issue */
		$article = null; /* @var $article PublishedArticle */
		$galley = null; /* @var $galley ArticleGalley */
		$suppFile = null; /* @var $suppFile SuppFile */
		$articlesByIssue = null;
		$galleysByArticle = null;
		$suppFilesByArticle = null;

		// Retrieve required publication objects (depends on the object to be exported).
		$pubObjects = $this->retrievePublicationObjects($object);
		if (!is_array($pubObjects)) {
			return false;
		}
		extract($pubObjects);

		// Identify an object implementing an ArticleFile (if any).
		$articleFile = (empty($suppFile) ? $galley : $suppFile);

		// Identify the object locale.
		$objectLocalePrecedence = $this->getObjectLocalePrecedence($article, $galley, $suppFile);

		// The publisher is required.
		$publisher = ($object instanceof SuppFile ? $object->getSuppFilePublisher() : null);
		if (empty($publisher)) {
			$publisher = $this->getPublisher($objectLocalePrecedence);
		}

		// The publication date is required.
		$publicationDate = ($article instanceof PublishedArticle ? $article->getDatePublished() : null);
		if (empty($publicationDate) && $issue !== null) {
			$publicationDate = $issue->getDatePublished();
		}
		assert(!empty($publicationDate));

		// Create the XML document and its root element.
		$doc = $this->getDoc();
		$rootElement = $this->rootElement();
		XMLCustomWriter::appendChild($doc, $rootElement);

		// DOI (mandatory)
		$identifierElement = $this->_identifierElement($object);
		if ($identifierElement === false) {
			return false;
		}
		XMLCustomWriter::appendChild($rootElement, $identifierElement);

		// Creators (mandatory)
		XMLCustomWriter::appendChild($rootElement, $this->_creatorsElement($object, $objectLocalePrecedence, $publisher));

		// Title (mandatory)
		XMLCustomWriter::appendChild($rootElement, $this->_titlesElement($object, $objectLocalePrecedence));

		// Publisher (mandatory)
		XMLCustomWriter::createChildWithText($this->getDoc(), $rootElement, 'publisher', $publisher);

		// Publication Year (mandatory)
		XMLCustomWriter::createChildWithText($this->getDoc(), $rootElement, 'publicationYear', date('Y', strtotime($publicationDate)));

		// Subjects
		if (!empty($suppFile)) {
			$this->_appendNonMandatoryChild($rootElement, $this->_subjectsElement($suppFile, $objectLocalePrecedence));
		} elseif (!empty($article)) {
			$this->_appendNonMandatoryChild($rootElement, $this->_subjectsElement($article, $objectLocalePrecedence));
		}

		// Dates
		XMLCustomWriter::appendChild($rootElement, $this->_datesElement($issue, $article, $articleFile, $suppFile, $publicationDate));

		// Language
		XMLCustomWriter::createChildWithText($this->getDoc(), $rootElement, 'language', AppLocale::getIso1FromLocale($objectLocalePrecedence[0]));

		// Resource Type
		if (!($object instanceof SuppFile)) {
			$resourceTypeElement = $this->_resourceTypeElement($object);
			XMLCustomWriter::appendChild($rootElement, $resourceTypeElement);
		}

		// Alternate Identifiers
		$this->_appendNonMandatoryChild($rootElement, $this->_alternateIdentifiersElement($object, $issue, $article, $articleFile));

		// Related Identifiers
		$this->_appendNonMandatoryChild($rootElement, $this->_relatedIdentifiersElement($object, $articlesByIssue, $galleysByArticle, $suppFilesByArticle, $issue, $article));

		// Sizes
		$sizesElement = $this->_sizesElement($object, $article);
		if ($sizesElement) {
			XMLCustomWriter::appendChild($rootElement, $sizesElement);
		}

		// Formats
		if (!empty($articleFile)) {
			$formatsElement = $this->_formatsElement($articleFile);
			if ($formatsElement) {
				XMLCustomWriter::appendChild($rootElement, $formatsElement);
			}
		}

		// Rights
		$rightsURL = $article ? $article->getLicenseURL() : $journal->getSetting('licenseURL');
		$rightsListElement = XMLCustomWriter::createElement($this->getDoc(), 'rightsList');
		$rightsElement = $this->createElementWithText('rights', strip_tags(Application::getCCLicenseBadge($rightsURL)), array('rightsURI' => $rightsURL));
		XMLCustomWriter::appendChild($rightsListElement, $rightsElement);
		XMLCustomWriter::appendChild($rootElement, $rightsListElement);

		// Descriptions
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
     * @throws Exception
	 */
	public function retrievePublicationObjects($object) {
		// Initialize local variables.
		$journal = $this->getJournal();
		$cache = $this->getCache();

		// Retrieve basic OJS objects.
		$publicationObjects = parent::retrievePublicationObjects($object);
		if ($object instanceof SuppFile) {
			assert(isset($publicationObjects['article']));
			$cache->add($object, $publicationObjects['article']);
			$publicationObjects['suppFile'] = $object;
		}

		// Retrieve additional related objects.
		// For articles: Retrieve all galleys and supp files of the article:
		if ($object instanceof PublishedArticle) {
			$article = $publicationObjects['article'];
			$publicationObjects['galleysByArticle'] = $this->retrieveGalleysByArticle($article);
			$publicationObjects['suppFilesByArticle'] = $this->_retrieveSuppFilesByArticle($article);
		}

		// For issues: Retrieve all articles of the issue:
		if ($object instanceof Issue) {
			// Articles by issue.
			assert(isset($publicationObjects['issue']));
			$issue = $publicationObjects['issue'];
			$publicationObjects['articlesByIssue'] = $this->retrieveArticlesByIssue($issue);
		}

		return $publicationObjects;
	}

	/**
     * Get the locale precedence for the given objects.
	 * @see DOIExportDom::getObjectLocalePrecedence()
	 * @param $suppFile SuppFile
     * @param $article PublishedArticle
     * @param $galley ArticleGalley
     * @return array
	 */
	public function getObjectLocalePrecedence($article, $galley, $suppFile = null) {
		$locales = array();
		if ($suppFile instanceof SuppFile) {
			// Try to map the supp-file language to a PKP locale.
			$suppFileLocale = $this->translateLanguageToLocale($suppFile->getLanguage());
			if (!is_null($suppFileLocale)) {
				$locales[] = $suppFileLocale;
			}
		}

		// Retrieve further locales from the other objects.
		$locales = array_merge($locales, parent::getObjectLocalePrecedence($article, $galley));
		return $locales;
	}

	/**
	 * Identify the publisher of the journal (journal title for DataCite citation purposes).
	 * @param $localePrecedence array
	 * @return string
	 */
	public function getPublisher($localePrecedence) {
		$journal = $this->getJournal();
		$publisher = $this->getPrimaryTranslation($journal->getTitle(null), $localePrecedence);
		assert(!empty($publisher));
		return $publisher;
	}


	//
	// Protected helper methods
	//
	/**
	 * Retrieve all supp files for the given article
	 * and commit them to the cache.
	 * @param PublishedArticle $article
	 * @return array
	 */
	protected function _retrieveSuppFilesByArticle($article) {
		$suppFilesByArticle = array();
		$cache = $this->getCache();
		$articleId = $article->getId();
		if (!$cache->isCached('suppFilesByArticle', $articleId)) {
			$suppFileDao = DAORegistry::getDAO('SuppFileDAO'); /* @var $suppFileDao SuppFileDAO */
			$suppFiles = $suppFileDao->getSuppFilesByArticle($articleId);
			if (!empty($suppFiles)) {
				foreach ($suppFiles as $suppFile) {
					$cache->add($suppFile, $article);
					unset($suppFile);
				}
				$cache->markComplete('suppFilesByArticle', $articleId);
				$suppFilesByArticle = $cache->get('suppFilesByArticle', $articleId);
			}
		} else {
			$suppFilesByArticle = $cache->get('suppFilesByArticle', $articleId);
		}
		return $suppFilesByArticle;
	}

	/**
	 * Create an identifier element.
	 * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
	 * @return XMLNode|DOMImplementation|false
     * @throws Exception
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
		return $this->createElementWithText('identifier', $doi, array('identifierType' => 'DOI'));
	}

	/**
	 * Create the creators element list.
	 * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
	 * @param array $objectLocalePrecedence
	 * @param string $publisher
	 * @return XMLNode|DOMImplementation
	 */
	protected function _creatorsElement($object, $objectLocalePrecedence, $publisher) {
		$cache = $this->getCache();

		$creators = array();
		switch (true) {
			case $object instanceof SuppFile:
				// Check whether we have a supp file creator set...
				$creator = $this->getPrimaryTranslation($object->getCreator(null), $objectLocalePrecedence);
				if (!empty($creator)) {
					$creators[] = $creator;
					break;
				}
				// ...if not then go on by retrieving the article
				// authors.

			case $object instanceof ArticleGalley:
				// Retrieve the article of the supp file or galley...
				$article = $cache->get('articles', $object->getArticleId());
				// ...then go on by retrieving the article authors.

			case $object instanceof PublishedArticle:
				// Retrieve the article authors.
				if (!isset($article)) {
					$article = $object;
				}
				$authors = $article->getAuthors();
				assert(!empty($authors));
				foreach ($authors as $author) { /* @var $author Author */
					$creators[] = $author->getFullName(true);
				}
				break;

			case $object instanceof Issue:
				$creators[] = $publisher;
				break;
		}

		assert(count($creators) >= 1);
		$creatorsElement = XMLCustomWriter::createElement($this->getDoc(), 'creators');
		foreach ($creators as $creator) {
			XMLCustomWriter::appendChild($creatorsElement, $this->_creatorElement($creator));
		}
		return $creatorsElement;
	}

	/**
	 * Create a single creator element.
	 * @param string $creator
	 * @return XMLNode|DOMImplementation
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
	 * @return XMLNode|DOMImplementation
	 */
	protected function _titlesElement($object, $objectLocalePrecedence) {
		$cache = $this->getCache();

		// Get an array of localized titles.
		$alternativeTitle = null;
		switch (true) {
			case $object instanceof SuppFile:
				$titles = $object->getTitle(null);
				break;

			case $object instanceof ArticleGalley:
				// Retrieve the article of the galley...
				$article = $cache->get('articles', $object->getArticleId());
				// ...then go on by retrieving the article titles.

			case $object instanceof PublishedArticle:
				if (!isset($article)) {
					$article = $object;
				}
				$titles = $article->getTitle(null);
				break;

			case $object instanceof Issue:
				$titles = $this->_getIssueInformation($object);
				$alternativeTitle = $this->getPrimaryTranslation($object->getTitle(null), $objectLocalePrecedence);
				break;
		}

		// Order titles by locale precedence.
		$titles = $this->getTranslationsByPrecedence($titles, $objectLocalePrecedence);

		// We expect at least one title.
		assert(count($titles) >= 1);

		$titlesElement = XMLCustomWriter::createElement($this->getDoc(), 'titles');

		// Start with the primary object locale.
		$primaryTitle = array_shift($titles);
		XMLCustomWriter::appendChild($titlesElement, $this->_titleElement($primaryTitle));

		// Then let the translated titles follow.
		foreach ($titles as $locale => $title) {
			XMLCustomWriter::appendChild($titlesElement, $this->_titleElement($title, DATACITE_TITLETYPE_TRANSLATED));
		}

		// And finally the alternative title.
		if (!empty($alternativeTitle)) {
			XMLCustomWriter::appendChild($titlesElement, $this->_titleElement($alternativeTitle, DATACITE_TITLETYPE_ALTERNATIVE));
		}

		return $titlesElement;
	}

	/**
	 * Create a single title element.
	 * @param string $title
	 * @param string|null $titleType One of the DATACITE_TITLETYPE_* constants.
	 * @return XMLNode|DOMImplementation
	 */
	protected function _titleElement($title, $titleType = null) {
		$titleElement = $this->createElementWithText('title', $title);
		if (!is_null($titleType)) {
			XMLCustomWriter::setAttribute($titleElement, 'titleType', $titleType);
		}
		return $titleElement;
	}

	/**
	 * Create the subjects element list.
	 * @param PublishedArticle|SuppFile $object
	 * @param array $objectLocalePrecedence
	 * @return XMLNode|DOMImplementation
	 */
	protected function _subjectsElement($object, $objectLocalePrecedence) {
		$subjectsElement = XMLCustomWriter::createElement($this->getDoc(), 'subjects');
		if ($object instanceof SuppFile) {
			$suppFileSubject = $this->getPrimaryTranslation($object->getSubject(null), $objectLocalePrecedence);
			if (!empty($suppFileSubject)) {
				XMLCustomWriter::appendChild($subjectsElement, $this->_subjectElement($suppFileSubject));
			}
		} else {
			assert($object instanceof PublishedArticle);
			$keywords = $this->getPrimaryTranslation($object->getSubject(null), $objectLocalePrecedence);
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
	 * @return XMLNode|DOMImplementation
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
	 * @return XMLNode|DOMImplementation
	 */
	protected function _datesElement($issue, $article, $articleFile, $suppFile, $publicationDate) {
		$datesElement = XMLCustomWriter::createElement($this->getDoc(), 'dates');
		$dates = array();

		// Created date (for supp files only): supp file date created.
		if (!empty($suppFile)) {
			$createdDate = $suppFile->getDateCreated();
			if (!empty($createdDate)) {
				$dates[DATACITE_DATE_CREATED] = $createdDate;
			}
		}

		// Submitted date (for articles and galleys): article date submitted.
		if (!empty($article)) {
			$submittedDate = $article->getDateSubmitted();
			if (!empty($submittedDate)) {
				$dates[DATACITE_DATE_SUBMITTED] = $submittedDate;

				// Default accepted date: submitted date.
				$dates[DATACITE_DATE_ACCEPTED] = $submittedDate;
			}
		}

		// Submitted date (for supp files): supp file date submitted.
		if (!empty($suppFile)) {
			$submittedDate = $suppFile->getDateSubmitted();
			if (!empty($submittedDate)) {
				$dates[DATACITE_DATE_SUBMITTED] = $submittedDate;
			}
		}

		// Accepted date (for galleys and supp files): article file uploaded.
		if (!empty($articleFile)) {
			$acceptedDate = $articleFile->getDateUploaded();
			if (!empty($acceptedDate)) {
				$dates[DATACITE_DATE_ACCEPTED] = $acceptedDate;
			}
		}

		// Issued date: publication date.
		$dates[DATACITE_DATE_ISSUED] = $publicationDate;

		// Available date: issue open access date.
		$availableDate = $issue ? $issue->getOpenAccessDate() : null;
		if (!empty($availableDate)) {
			$dates[DATACITE_DATE_AVAILABLE] = $availableDate;
		}

		// Last modified date (for articles): last modified date.
		if (!empty($article) && empty($articleFile)) {
			$dates[DATACITE_DATE_UPDATED] = $article->getLastModified();
		}

		// Create the date elements for all dates.
		foreach ($dates as $dateType => $date) {
			XMLCustomWriter::appendChild($datesElement, $this->_dateElement($dateType, $date));
		}

		return $datesElement;
	}

	/**
	 * Create a single date element.
	 * @param string $dateType One of the DATACITE_DATE_* constants.
	 * @param string $date
	 * @return XMLNode|DOMImplementation
	 */
	protected function _dateElement($dateType, $date) {
		// Format the date.
		assert(!empty($date));
		$date = date('Y-m-d', strtotime($date));

		// Create the date element.
		return $this->createElementWithText('date', $date, array('dateType' => $dateType));
	}

	/**
	 * Create a resource type element.
	 * @param Issue|PublishedArticle|ArticleGalley $object
	 * @return XMLNode|DOMImplementation
	 */
	protected function _resourceTypeElement($object) {
		switch (true) {
			case $object instanceof Issue:
				$resourceType = 'Journal Issue';
				break;

			case $object instanceof PublishedArticle:
			case $object instanceof ArticleGalley:
				$resourceType = 'Article';
				break;

			default:
				assert(false);
		}

		// Create the resourceType element.
		return $this->createElementWithText('resourceType', $resourceType, array('resourceTypeGeneral' => 'Text'));
	}

	/**
	 * Generate alternate identifiers element list.
	 * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
	 * @param Issue|null $issue
	 * @param PublishedArticle|null $article
	 * @param ArticleFile|null $articleFile
	 * @return XMLNode|DOMImplementation
	 */
	protected function _alternateIdentifiersElement($object, $issue, $article, $articleFile) {
		$journal = $this->getJournal();
		$alternateIdentifiersElement = XMLCustomWriter::createElement($this->getDoc(), 'alternateIdentifiers');

		// Proprietary ID
		$proprietaryId = $this->getProprietaryId($journal, $issue, $article, $articleFile);
		XMLCustomWriter::appendChild(
			$alternateIdentifiersElement,
			$this->createElementWithText(
				'alternateIdentifier', $proprietaryId,
				array('alternateIdentifierType' => DATACITE_IDTYPE_PROPRIETARY)
			)
		);

		// ISSN - for issues only.
		if ($object instanceof Issue) {
			$onlineIssn = $journal->getSetting('onlineIssn');
			if (!empty($onlineIssn)) {
				XMLCustomWriter::appendChild(
					$alternateIdentifiersElement,
					$this->createElementWithText(
						'alternateIdentifier', $onlineIssn,
						array('alternateIdentifierType' => DATACITE_IDTYPE_EISSN)
					)
				);
			}

			$printIssn = $journal->getSetting('printIssn');
			if (!empty($printIssn)) {
				XMLCustomWriter::appendChild(
					$alternateIdentifiersElement,
					$this->createElementWithText(
						'alternateIdentifier', $printIssn,
						array('alternateIdentifierType' => DATACITE_IDTYPE_ISSN)
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
	 * @return XMLNode|DOMImplementation
	 */
	protected function _relatedIdentifiersElement($object, $articlesByIssue, $galleysByArticle, $suppFilesByArticle, $issue, $article) {
		$journal = $this->getJournal();
		$relatedIdentifiersElement = XMLCustomWriter::createElement($this->getDoc(), 'relatedIdentifiers');

		switch (true) {
			case $object instanceof Issue:
				// Parts: articles in this issue.
				assert(is_array($articlesByIssue));
				foreach ($articlesByIssue as $articleInIssue) {
					$doi = $this->_relatedIdentifierElement($articleInIssue, DATACITE_RELTYPE_HASPART);
					if (!is_null($doi)) XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
					unset($articleInIssue, $doi);
				}
				break;

			case $object instanceof PublishedArticle:
				// Part of: issue.
				assert($issue instanceof Issue);
				$doi = $this->_relatedIdentifierElement($issue, DATACITE_RELTYPE_ISPARTOF);
				if (!is_null($doi)) XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
				unset($doi);

				// Parts: galleys and supp files.
				assert(is_array($galleysByArticle) && is_array($suppFilesByArticle));
				$relType = DATACITE_RELTYPE_HASPART;
				foreach ($galleysByArticle as $galleyInArticle) {
					$doi = $this->_relatedIdentifierElement($galleyInArticle, $relType);
					if (!is_null($doi)) XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
					unset($galleyInArticle, $doi);
				}
				foreach ($suppFilesByArticle as $suppFileInArticle) {
					$doi = $this->_relatedIdentifierElement($suppFileInArticle, $relType);
					if (!is_null($doi)) XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
					unset($suppFileInArticle, $doi);
				}
				break;

			case $object instanceof ArticleFile:
				// Part of: article.
				assert($article instanceof Article);
				$doi = $this->_relatedIdentifierElement($article, DATACITE_RELTYPE_ISPARTOF);
				if (!is_null($doi)) XMLCustomWriter::appendChild($relatedIdentifiersElement, $doi);
				break;
		}

		return $relatedIdentifiersElement;
	}

	/**
	 * Create an identifier element with the object's DOI.
	 * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
	 * @param string $relationType One of the DATACITE_RELTYPE_* constants.
	 * @return XMLNode|DOMImplementation|null Can be null if the given ID Type
	 *  has not been assigned to the given object.
	 */
	protected function _relatedIdentifierElement($object, $relationType) {
		$nullVar = null;
		$id = $object->getPubId('doi');

		if (empty($id)) {
			return $nullVar;
		}
		if ($this->getTestMode()) {
			$id = PKPString::regexp_replace('#^[^/]+/#', DATACITE_API_TESTPREFIX . '/', $id);
		}

		return $this->createElementWithText(
			'relatedIdentifier', $id,
			array(
				'relatedIdentifierType' => DATACITE_IDTYPE_DOI,
				'relationType' => $relationType
			)
		);
	}

	/**
	 * Create a sizes element list.
	 * @param Issue|PublishedArticle|ArticleFile $object
	 * @param PublishedArticle|null $article
	 * @return XMLNode|DOMImplementation|null Can be null if a size
	 *  cannot be identified for the given object.
	 */
	protected function _sizesElement($object, $article) {
		switch (true) {
			case $object instanceof Issue:
				$issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO'); /* @var $issueGalleyDao IssueGalleyDAO */
				$files = $issueGalleyDao->getGalleysByIssue($object->getId());
				break;

			case $object instanceof PublishedArticle:
				$pages = $object->getPages();
				$files = array();
				break;

			case $object instanceof ArticleFile:
				if ($object instanceof ArticleGalley) {
					// The galley represents the article.
					$pages = $article->getPages();
				}
				$files = array($object);
				break;

			default:
				assert(false);
		}

		$sizes = array();
		if (!empty($pages)) {
			$sizes[] = $pages . ' ' . __('editor.issues.pages');
		}
		foreach ($files as $file) { /* @var $file PKPFile */
			$fileSize = round(((int)$file->getFileSize()) / 1024);
			if ($fileSize >= 1024) {
				$fileSize = round($fileSize / 1024, 2);
				$sizes[] = $fileSize . ' MB';
			} elseif ($fileSize >= 1) {
				$sizes[] = $fileSize . ' kB';
			}
			unset($file);
		}

		$sizesElement = null;
		if (!empty($sizes)) {
			$sizesElement = XMLCustomWriter::createElement($this->getDoc(), 'sizes');
			foreach ($sizes as $size) {
				XMLCustomWriter::createChildWithText($this->getDoc(), $sizesElement, 'size', $size);
			}
		}
		return $sizesElement;
	}

	/**
	 * Create a formats element list.
	 * @param ArticleFile $articleFile
	 * @return XMLNode|DOMImplementation|null Can be null if a format
	 *  cannot be identified for the given object.
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
	 * @return XMLNode|DOMImplementation|null Can be null if no descriptions
	 *  can be identified for the given object.
	 */
	protected function _descriptionsElement($issue, $article, $suppFile, $objectLocalePrecedence, $articlesByIssue) {
		$descriptions = array();

		if (isset($article) && !isset($suppFile)) {
			// Articles and galleys.
			$articleAbstract = $this->getPrimaryTranslation($article->getAbstract(null), $objectLocalePrecedence);
			if (!empty($articleAbstract)) $descriptions[DATACITE_DESCTYPE_ABSTRACT] = $articleAbstract;
		}

		if (isset($suppFile)) {
			// Supp files.
			$suppFileDesc = $this->getPrimaryTranslation($suppFile->getDescription(null), $objectLocalePrecedence);
			if (!empty($suppFileDesc)) $descriptions[DATACITE_DESCTYPE_OTHER] = $suppFileDesc;
		}

		if (isset($article)) {
			// Articles, galleys and supp files.
			$descriptions[DATACITE_DESCTYPE_SERIESINFO] = $this->_getIssueInformation($issue, $objectLocalePrecedence);
		} else {
			// Issues.
			$issueDesc = $this->getPrimaryTranslation($issue->getDescription(null), $objectLocalePrecedence);
			if (!empty($issueDesc)) $descriptions[DATACITE_DESCTYPE_OTHER] = $issueDesc;
			$descriptions[DATACITE_DESCTYPE_TOC] = $this->_getIssueToc($articlesByIssue, $objectLocalePrecedence);
		}

		$descriptionsElement = null;
		if (!empty($descriptions)) {
			$descriptionsElement = XMLCustomWriter::createElement($this->getDoc(), 'descriptions');
			foreach ($descriptions as $descType => $description) {
				XMLCustomWriter::appendChild(
					$descriptionsElement,
					$this->createElementWithText('description', $description, array('descriptionType' => $descType))
				);
			}
		}
		return $descriptionsElement;
	}

	/**
	 * Construct an issue title from the journal title
	 * and the issue identification.
	 * @param Issue $issue
	 * @param array|null $objectLocalePrecedence
	 * @return array|string An array of localized issue titles or a string if a locale has been given.
	 */
	protected function _getIssueInformation($issue, $objectLocalePrecedence = null) {
		$issueIdentification = $issue->getIssueIdentification();
		assert(!empty($issueIdentification));

		$journal = $this->getJournal();
		if (is_null($objectLocalePrecedence)) {
			$issueInfo = array();
			foreach ($journal->getTitle(null) as $locale => $journalTitle) {
				$issueInfo[$locale] = "$journalTitle, $issueIdentification";
			}
		} else {
			$issueInfo = $this->getPrimaryTranslation($journal->getTitle(null), $objectLocalePrecedence);
			if (!empty($issueInfo)) {
				$issueInfo .= ', ';
			}
			$issueInfo .= $issueIdentification;
		}
		return $issueInfo;
	}

	/**
	 * Construct a table of content from an article list.
	 * @param array $articlesByIssue
	 * @param array $objectLocalePrecedence
	 * @return string
	 */
	protected function _getIssueToc($articlesByIssue, $objectLocalePrecedence) {
		assert(is_array($articlesByIssue));
		$toc = '';
		foreach ($articlesByIssue as $articleInIssue) {
			$currentEntry = $this->getPrimaryTranslation($articleInIssue->getTitle(null), $objectLocalePrecedence);
			assert(!empty($currentEntry));
			$pages = $articleInIssue->getPages();
			if (!empty($pages)) {
				$currentEntry .= '...' . $pages;
			}
			$toc .= $currentEntry . "<br />";
			unset($articleInIssue);
		}
		return $toc;
	}

	/**
	 * Datacite does not allow empty nodes. So we have to
	 * check nodes before we add them.
	 * @param XmlNode|DOMElement $parentNode
	 * @param XmlNode|DOMElement|null $child
	 */
	protected function _appendNonMandatoryChild($parentNode, $child) {
		if ($child === null) return;
		if ($child instanceof XMLNode) {
			$childChildren = $child->getChildren();
			$childEmpty = empty($childChildren);
		} else {
			$childEmpty = !$child->hasChildNodes();
		}
		if ($childEmpty) return;
		XMLCustomWriter::appendChild($parentNode, $child);
	}
}

?>
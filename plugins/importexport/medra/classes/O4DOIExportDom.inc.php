<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/medra/classes/O4DOIExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class O4DOIExportDom
 * @ingroup plugins_importexport_medra_classes
 *
 * @brief Onix for DOI (O4DOI) XML export format implementation.
 */

if (!class_exists('DOIExportDom')) {
    import('plugins.importexport.medra.classes.DOIExportDom');
}

// XML attributes
define('O4DOI_XMLNS' , 'http://www.editeur.org/onix/DOIMetadata/2.0');
define('O4DOI_XSI_SCHEMALOCATION' , O4DOI_XMLNS . ' http://www.medra.org/schema/onix/DOIMetadata/2.0/ONIX_DOIMetadata_2.0.xsd');
define('O4DOI_XSI_SCHEMALOCATION_DEV' , O4DOI_XMLNS . ' http://medra.dev.cineca.it/schema/onix/DOIMetadata/2.0/ONIX_DOIMetadata_2.0.xsd');

// Notification types
define('O4DOI_NOTIFICATION_TYPE_NEW', '06');
define('O4DOI_NOTIFICATION_TYPE_UPDATE', '07');

// ID types
define('O4DOI_ID_TYPE_PROPRIETARY', '01');
define('O4DOI_ID_TYPE_DOI', '06');
define('O4DOI_ID_TYPE_ISSN', '07');

// Text formats
define('O4DOI_TEXTFORMAT_ASCII', '00');

// Title types
define('O4DOI_TITLE_TYPE_FULL', '01');
define('O4DOI_TITLE_TYPE_ISSUE', '07');

// Publishing roles
define('O4DOI_PUBLISHING_ROLE_PUBLISHER', '01');

// Product forms
define('O4DOI_PRODUCT_FORM_PRINT', 'JB');
define('O4DOI_PRODUCT_FORM_ELECTRONIC', 'JD');

// ePublication formats
define('O4DOI_EPUB_FORMAT_HTML', '01');

// Date formats
define('O4DOI_DATE_FORMAT_YYYY', '06');

// Extent types
define('O4DOI_EXTENT_TYPE_FILESIZE', '22');

// Extent units
define('O4DOI_EXTENT_UNIT_BYTES', '17');

// Contributor roles
define('O4DOI_CONTRIBUTOR_ROLE_ACTUAL_AUTHOR', 'A01');

// Language roles
define('O4DOI_LANGUAGE_ROLE_LANGUAGE_OF_TEXT', '01');

// Subject schemes
define('O4DOI_SUBJECT_SCHEME_PUBLISHER', '23');
define('O4DOI_SUBJECT_SCHEME_PROPRIETARY', '24');

// Text type codes
define('O4DOI_TEXT_TYPE_MAIN_DESCRIPTION', '01');

// Relation codes
define('O4DOI_RELATION_INCLUDES', '80');
define('O4DOI_RELATION_IS_PART_OF', '81');
define('O4DOI_RELATION_IS_A_NEW_VERSION_OF', '82');
define('O4DOI_RELATION_HAS_A_NEW_VERSION', '83');
define('O4DOI_RELATION_IS_A_DIFFERENT_FORM_OF', '84');
define('O4DOI_RELATION_IS_A_LANGUAGE_VERSION_OF', '85');
define('O4DOI_RELATION_IS_MANIFESTED_IN', '89');
define('O4DOI_RELATION_IS_A_MANIFESTATION_OF', '90');

// mEDRA test prefix.
define('MEDRA_WS_TESTPREFIX', '1749');

class O4DOIExportDom extends DOIExportDom {

    //
    // Private properties
    //
    /** @var int */
    protected int $_schema;

    /** @var array */
    protected array $_schemaInfo = [];

    /** @var PKPRequest */
    protected $_request;

    /** @var Journal */
    protected $_journal;

    /** @var PubObjectCache */
    protected $_cache;

    /** @var int|string One of the O4DOI_* schema constants */
    protected $_exportIssuesAs;

    //
    // Constructor
    //
    /**
     * Constructor
     * @param PKPRequest $request
     * @param DOIExportPlugin $plugin
     * @param int $schema
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     * @param int|string $exportIssuesAs
     */
    public function __construct($request, $plugin, $schema, $journal, $objectCache, $exportIssuesAs) {
        parent::__construct($request, $plugin, $journal, $objectCache);
        $this->_schema = (int) $schema;
        $this->_schemaInfo = $this->_setSchemaInfo($this->_getSchema());
        $this->_exportIssuesAs = $exportIssuesAs;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function O4DOIExportDom() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Deprecated constructor called in " . self::class, E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Getters
    //

    /**
     * Get the schema that this DOM will generate.
     * @return int One of the O4DOI_* schema types.
     */
    public function _getSchema(): int {
        return $this->_schema;
    }

    /**
     * Internal schema-specific configuration.
     * @param string $infoType
     * @return mixed
     */
    public function _getSchemaInfo($infoType) {
        return $this->_schemaInfo[$infoType] ?? null;
    }

    /**
     * The App object type represented by this DOM
     * @return string
     */
    public function _getObjectType(): string {
        return (string) $this->_getSchemaInfo('objectType');
    }

    /**
     * The DOM's payload element.
     * @return string
     */
    public function _getObjectElementName(): string {
        return (string) $this->_getSchemaInfo('objectElementName');
    }

    /**
     * Whether the DOM represents an object-as-work.
     * @return bool
     */
    public function _isWork(): bool {
        return (bool) $this->_getSchemaInfo('isWork');
    }

    /**
     * Whether the DOM represents a serial article.
     * @return bool
     */
    public function _isArticle(): bool {
        return (bool) $this->_getSchemaInfo('isArticle');
    }

    /**
     * Get the current request.
     * @return PKPRequest
     */
    public function getRequest() {
        return $this->_request;
    }

    /**
     * Get the journal (a.k.a. serial title) of this O4DOI message.
     * @return Journal
     */
    public function getJournal() {
        return $this->_journal;
    }

    /**
     * Get the object cache.
     * @return PubObjectCache
     */
    public function getCache() {
        return $this->_cache;
    }

    /**
     * Whether issues are exported as work.
     * @return bool
     */
    public function _exportIssuesAsWork(): bool {
        return $this->_exportIssuesAs === O4DOI_ISSUE_AS_WORK;
    }

    //
    // Public methods
    //
    /**
     * Generate the O4DOI XML document.
     * @see DOIExportDom::generate()
     * @param array $objects
     * @return DOMDocument|bool
     */
    public function generate($objects) {
        $doc = $this->getDoc();
        $rootElement = $this->rootElement();
        XMLCustomWriter::appendChild($doc, $rootElement);

        $headerElement = $this->_headerElement();
        if (!$headerElement) {
            return false;
        }
        XMLCustomWriter::appendChild($rootElement, $headerElement);

        foreach ($objects as $object) {
            $objectElement = $this->_objectElement($object);
            if (!$objectElement) {
                return false;
            }
            XMLCustomWriter::appendChild($rootElement, $objectElement);
        }

        return $doc;
    }

    //
    // Implement protected template methods from DOIExportDom
    //
    /**
     * Return the root element name for the current schema.
     * @see DOIExportDom::getRootElementName()
     * @return string
     */
    public function getRootElementName() {
        return $this->_getSchemaInfo('rootElementName');
    }

    /**
     * Return the namespace for the current schema.
     * @see DOIExportDom::getNamespace()
     * @return string
     */
    public function getNamespace() {
        return O4DOI_XMLNS;
    }

    /**
     * Return the XML schema location for the current schema.
     * @see DOIExportDom::getXmlSchemaLocation()
     * @return string
     */
    public function getXmlSchemaLocation() {
        return $this->getTestMode() ? O4DOI_XSI_SCHEMALOCATION_DEV : O4DOI_XSI_SCHEMALOCATION;
    }

    /**
     * Retrieve all the OJS publication objects containing the
     * data required to generate the given O4DOI schema.
     * @param Issue|PublishedArticle|ArticleGalley $object
     * @return array
     */
    public function retrievePublicationObjects($object): array {
        $publicationObjects = parent::retrievePublicationObjects($object);

        if ($object instanceof PublishedArticle || $object instanceof ArticleGalley) {
            if (isset($publicationObjects['article'])) {
                $publicationObjects['galleysByArticle'] = $this->retrieveGalleysByArticle($publicationObjects['article']);
            }
        }

        if ($object instanceof Issue) {
            if (isset($publicationObjects['issue'])) {
                $issue = $publicationObjects['issue'];
                $publicationObjects['articlesByIssue'] = $this->retrieveArticlesByIssue($issue);

                $issueId = $issue->getId();
                $cache = $this->getCache();
                if (!$cache->isCached('galleysByIssue', $issueId)) {
                    if (isset($publicationObjects['articlesByIssue']) && is_array($publicationObjects['articlesByIssue'])) {
                        foreach($publicationObjects['articlesByIssue'] as $article) {
                            $this->retrieveGalleysByArticle($article);
                        }
                    }
                    $cache->markComplete('galleysByIssue', $issueId);
                }
                $publicationObjects['galleysByIssue'] = $cache->get('galleysByIssue', $issueId);
            }
        }

        return $publicationObjects;
    }

    //
    // Private helper methods
    //
    /**
     * Return information about the given schema.
     * @param int $schema
     * @return array
     */
    public function _setSchemaInfo($schema): array {
        static $schemaInfos = [
            O4DOI_ISSUE_AS_WORK => [
                'rootElementName' => 'ONIXDOISerialIssueWorkRegistrationMessage',
                'objectElementName' => 'DOISerialIssueWork',
                'objectType' => 'Issue',
                'isWork' => true,
                'isArticle' => false
            ],
            O4DOI_ISSUE_AS_MANIFESTATION => [
                'rootElementName' => 'ONIXDOISerialIssueVersionRegistrationMessage',
                'objectElementName' => 'DOISerialIssueVersion',
                'objectType' => 'Issue',
                'isWork' => false,
                'isArticle' => false
            ],
            O4DOI_ARTICLE_AS_WORK => [
                'rootElementName' => 'ONIXDOISerialArticleWorkRegistrationMessage',
                'objectElementName' => 'DOISerialArticleWork',
                'objectType' => 'PublishedArticle',
                'isWork' => true,
                'isArticle' => true
            ],
            O4DOI_ARTICLE_AS_MANIFESTATION => [
                'rootElementName' => 'ONIXDOISerialArticleVersionRegistrationMessage',
                'objectElementName' => 'DOISerialArticleVersion',
                'objectType' => 'ArticleGalley',
                'isWork' => false,
                'isArticle' => true
            ]
        ];

        return $schemaInfos[$schema] ?? [];
    }

    /**
     * Generate the O4DOI header element.
     * @see DOIExportDom::_headerElement()
     * @return DOMElement|bool
     */
    public function _headerElement() {
        $headerElement = XMLCustomWriter::createElement($this->getDoc(), 'Header');

        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'FromCompany', $this->getPluginSetting('fromCompany'));
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'FromPerson', $this->getPluginSetting('fromName'));
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'FromEmail', $this->getPluginSetting('fromEmail'));
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'ToCompany', 'mEDRA');
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'SentDate', date('YmdHi'));

        $app = Application::getApplication();
        $version = $app->getCurrentVersion();
        $versionString = $version ? $version->getVersionString() : 'unknown';
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'MessageNote', "This dataset was exported with " . $app->getName() . ", version $versionString.");

        return $headerElement;
    }

    /**
     * Generate O4DOI object payload.
     * @param Issue|PublishedArticle|ArticleGalley $object
     * @return DOMElement|bool
     */
    public function _objectElement($object) {
        $journal = $this->getJournal();
        $expectedType = $this->_getObjectType();
        
        if (!is_a($object, $expectedType)) {
            return false;
        }

        $pubObjects = $this->retrievePublicationObjects($object);

        $issue = $pubObjects['issue'] ?? null;
        $article = $pubObjects['article'] ?? null;
        $galley = $pubObjects['galley'] ?? null;
        $articlesByIssue = $pubObjects['articlesByIssue'] ?? [];
        $galleysByArticle = $pubObjects['galleysByArticle'] ?? [];
        $galleysByIssue = $pubObjects['galleysByIssue'] ?? [];

        $objectElement = XMLCustomWriter::createElement($this->getDoc(), $this->_getObjectElementName());

        $doi = $this->_getDoi($object);
        if (empty($doi)) {
            $this->_addError('plugins.importexport.common.export.error.noDoiAssigned', $object->getId());
            return false;
        }

        $registeredDoi = $object->getData('medra::registeredDoi');
        $notificationType = empty($registeredDoi) || $registeredDoi === $doi ? O4DOI_NOTIFICATION_TYPE_NEW : O4DOI_NOTIFICATION_TYPE_UPDATE;
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'NotificationType', $notificationType);
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'DOI', $doi);

        $request = $this->getRequest();
        $router = $request->getRouter();
        $url = '';
        
        switch ($this->_getSchema()) {
            case O4DOI_ISSUE_AS_WORK:
            case O4DOI_ISSUE_AS_MANIFESTATION:
                if ($issue instanceof Issue) {
                    $url = $router->url($request, $journal->getPath(), 'issue', 'view', $issue->getBestIssueId($journal));
                }
                break;
            case O4DOI_ARTICLE_AS_WORK:
                if ($article instanceof PublishedArticle) {
                    $url = $router->url($request, $journal->getPath(), 'article', 'view', $article->getBestArticleId($journal));
                }
                break;
            case O4DOI_ARTICLE_AS_MANIFESTATION:
                if ($article instanceof PublishedArticle && $galley instanceof ArticleGalley) {
                    $url = $router->url($request, $journal->getPath(), 'article', 'view', [$article->getBestArticleId($journal), $galley->getBestGalleyId($journal)]);
                }
                break;
        }
        
        if (empty($url)) {
            return false;
        }
        
        if ($this->getTestMode()) {
            $url = PKPString::regexp_replace('#://[^\s]+/index.php#', '://example.com/index.php', $url);
        }
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'DOIWebsiteLink', $url);

        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'DOIStructuralType', $this->_isWork() ? 'Abstraction' : 'DigitalFixation');
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'RegistrantName', $this->getPluginSetting('registrantName'));
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'RegistrationAuthority', 'mEDRA');

        XMLCustomWriter::appendChild($objectElement, $this->_idElement($this->_isWork() ? 'Work' : 'Product', O4DOI_ID_TYPE_PROPRIETARY, $this->getProprietaryId($journal, $issue, $article, $galley)));

        $journalLocalePrecedence = $this->getObjectLocalePrecedence(null, null);
        XMLCustomWriter::appendChild($objectElement, $this->_serialPublicationElement($journalLocalePrecedence));
        XMLCustomWriter::appendChild($objectElement, $this->_journalIssueElement($issue, $journalLocalePrecedence));

        $objectLocalePrecedence = $this->getObjectLocalePrecedence($article, $galley);
        $finalElementsContainer = $this->_isArticle() && $article instanceof PublishedArticle ? $this->_contentItemElement($article, $galley, $objectLocalePrecedence) : $objectElement;
        
        if ($this->_isArticle() && $article instanceof PublishedArticle) {
            XMLCustomWriter::appendChild($objectElement, $finalElementsContainer);
        }

        $rawDescriptions = $this->_isArticle() && $article instanceof PublishedArticle ? $article->getAbstract(null) : ($issue instanceof Issue ? $issue->getDescription(null) : []);
        $descriptionsArray = is_array($rawDescriptions) ? $rawDescriptions : (is_string($rawDescriptions) ? [$this->_isArticle() ? $article->getLocale() : $journal->getPrimaryLocale() => $rawDescriptions] : []);
        $descriptions = $this->getTranslationsByPrecedence($descriptionsArray, $objectLocalePrecedence);
        
        foreach ($descriptions as $locale => $description) {
            XMLCustomWriter::appendChild($finalElementsContainer, $this->_otherTextElement($locale, $description));
        }

        if ($this->_isArticle() && $article instanceof PublishedArticle) {
            $datePublished = $article->getDatePublished();
            if (!empty($datePublished)) {
                XMLCustomWriter::appendChild($finalElementsContainer, $this->_publicationDateElement($datePublished));
            }

            $issueWorkOrProduct = $this->_exportIssuesAsWork() ? 'Work' : 'Product';
            $relatedIssueIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue)];
            $issueDoi = $issue instanceof Issue ? $this->_getDoi($issue) : null;
            if (!empty($issueDoi)) {
                $relatedIssueIds[O4DOI_ID_TYPE_DOI] = $issueDoi;
            }
            $relatedIssueElement = $this->_relationElement($issueWorkOrProduct, O4DOI_RELATION_IS_PART_OF, $relatedIssueIds);

            if ($this->_isWork()) {
                XMLCustomWriter::appendChild($finalElementsContainer, $relatedIssueElement);

                foreach($galleysByArticle as $relatedGalley) {
                    if ($relatedGalley instanceof ArticleGalley) {
                        $relatedGalleyIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $article, $relatedGalley)];
                        $galleyDoi = $this->_getDoi($relatedGalley);
                        if (!empty($galleyDoi)) {
                            $relatedGalleyIds[O4DOI_ID_TYPE_DOI] = $galleyDoi;
                        }
                        $relatedArticleElement = $this->_relationElement('Product', O4DOI_RELATION_IS_MANIFESTED_IN, $relatedGalleyIds);
                        XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                    }
                }
            } else {
                if ($issueWorkOrProduct === 'Work') {
                    XMLCustomWriter::appendChild($finalElementsContainer, $relatedIssueElement);
                }

                $relatedArticleIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $article)];
                $articleDoi = $this->_getDoi($article);
                if (!empty($articleDoi)) {
                    $relatedArticleIds[O4DOI_ID_TYPE_DOI] = $articleDoi;
                }
                $relatedArticleElement = $this->_relationElement('Work', O4DOI_RELATION_IS_A_MANIFESTATION_OF, $relatedArticleIds);
                XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);

                if ($issueWorkOrProduct === 'Product') {
                    XMLCustomWriter::appendChild($finalElementsContainer, $relatedIssueElement);
                }

                foreach($galleysByArticle as $relatedGalley) {
                    if ($galley instanceof ArticleGalley && $relatedGalley instanceof ArticleGalley) {
                        $relatedGalleyIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $article, $relatedGalley)];
                        $galleyDoi = $this->_getDoi($relatedGalley);
                        if (!empty($galleyDoi)) {
                            $relatedGalleyIds[O4DOI_ID_TYPE_DOI] = $galleyDoi;
                        }

                        if ($galley->getLocale() === $relatedGalley->getLocale() && $galley->getLabel() !== $relatedGalley->getLabel()) {
                            $diffFormElement = $this->_relationElement('Product', O4DOI_RELATION_IS_A_DIFFERENT_FORM_OF, $relatedGalleyIds);
                            XMLCustomWriter::appendChild($finalElementsContainer, $diffFormElement);
                        }

                        if ($galley->getLabel() === $relatedGalley->getLabel() && $galley->getLocale() !== $relatedGalley->getLocale()) {
                            $langVersionElement = $this->_relationElement('Product', O4DOI_RELATION_IS_A_LANGUAGE_VERSION_OF, $relatedGalleyIds);
                            XMLCustomWriter::appendChild($finalElementsContainer, $langVersionElement);
                        }
                    }
                }
            }
        } else {
            foreach ($articlesByIssue as $relatedArticle) {
                if ($relatedArticle instanceof PublishedArticle) {
                    $relatedArticleIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $relatedArticle)];
                    $relArticleDoi = $this->_getDoi($relatedArticle);
                    if (!empty($relArticleDoi)) {
                        $relatedArticleIds[O4DOI_ID_TYPE_DOI] = $relArticleDoi;
                    }
                    $relatedArticleElement = $this->_relationElement('Work', O4DOI_RELATION_INCLUDES, $relatedArticleIds);
                    XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                }
            }

            foreach($galleysByIssue as $relatedGalley) {
                if ($relatedGalley instanceof ArticleGalley) {
                    $relatedGalleyIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $relatedGalley, $relatedGalley)];
                    $relGalleyDoi = $this->_getDoi($relatedGalley);
                    if (!empty($relGalleyDoi)) {
                        $relatedGalleyIds[O4DOI_ID_TYPE_DOI] = $relGalleyDoi;
                    }
                    $relatedArticleElement = $this->_relationElement('Product', O4DOI_RELATION_INCLUDES, $relatedGalleyIds);
                    XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                }
            }
        }

        return $objectElement;
    }

    /**
     * Create a work or product id element.
     * @param string $workOrProduct
     * @param string $idType
     * @param string $id
     * @return DOMElement
     */
    public function _idElement($workOrProduct, $idType, $id) {
        $idElement = XMLCustomWriter::createElement($this->getDoc(), "${workOrProduct}Identifier");
        XMLCustomWriter::createChildWithText($this->getDoc(), $idElement, "${workOrProduct}IDType", $idType);
        XMLCustomWriter::createChildWithText($this->getDoc(), $idElement, 'IDValue', $id);
        return $idElement;
    }

    /**
     * Generate O4DOI serial publication.
     * @param array $journalLocalePrecedence
     * @return DOMElement
     */
    public function _serialPublicationElement($journalLocalePrecedence) {
        $journal = $this->getJournal();
        $serialElement = XMLCustomWriter::createElement($this->getDoc(), 'SerialPublication');

        XMLCustomWriter::appendChild($serialElement, $this->_serialWorkElement($journalLocalePrecedence));

        $onlineIssn = $journal->getSetting('onlineIssn');
        XMLCustomWriter::appendChild($serialElement, $this->_serialVersionElement($onlineIssn, O4DOI_PRODUCT_FORM_ELECTRONIC));

        $printIssn = $journal->getSetting('printIssn');
        if (!empty($printIssn) && $this->_isWork()) {
            XMLCustomWriter::appendChild($serialElement, $this->_serialVersionElement($printIssn, O4DOI_PRODUCT_FORM_PRINT));
        }

        return $serialElement;
    }

    /**
     * Generate O4DOI serial work.
     * @param array $journalLocalePrecedence
     * @return DOMElement
     */
    public function _serialWorkElement($journalLocalePrecedence) {
        $journal = $this->getJournal();
        $serialWorkElement = XMLCustomWriter::createElement($this->getDoc(), 'SerialWork');

        $rawTitles = $journal->getTitle(null);
        $journalTitles = is_array($rawTitles) ? $rawTitles : (is_string($rawTitles) ? [$journal->getPrimaryLocale() => $rawTitles] : []);
        $journalTitles = $this->getTranslationsByPrecedence($journalTitles, $journalLocalePrecedence);
        
        foreach($journalTitles as $locale => $journalTitle) {
            XMLCustomWriter::appendChild($serialWorkElement, $this->_titleElement($locale, $journalTitle, O4DOI_TITLE_TYPE_FULL));
        }

        XMLCustomWriter::appendChild($serialWorkElement, $this->_publisherElement($journalLocalePrecedence));
        XMLCustomWriter::createChildWithText($this->getDoc(), $serialWorkElement, 'CountryOfPublication', $this->getPluginSetting('publicationCountry'));

        return $serialWorkElement;
    }

    /**
     * Create a work or product id element.
     * @param string $locale e.g. 'en_US'
     * @param string $localizedTitle
     * @param string $titleType
     * @return DOMElement
     */
    public function _titleElement($locale, $localizedTitle, $titleType) {
        $titleElement = XMLCustomWriter::createElement($this->getDoc(), 'Title');
        XMLCustomWriter::setAttribute($titleElement, 'textformat', O4DOI_TEXTFORMAT_ASCII);
        
        $language = AppLocale::get3LetterIsoFromLocale($locale);
        if (!empty($language)) {
            XMLCustomWriter::setAttribute($titleElement, 'language', $language);
        }

        XMLCustomWriter::createChildWithText($this->getDoc(), $titleElement, 'TitleType', $titleType);
        XMLCustomWriter::createChildWithText($this->getDoc(), $titleElement, 'TitleText', PKPString::html2text($localizedTitle));

        return $titleElement;
    }

    /**
     * Create a publisher element.
     * @param array $journalLocalePrecedence
     * @return DOMElement
     */
    public function _publisherElement($journalLocalePrecedence) {
        $publisherElement = XMLCustomWriter::createElement($this->getDoc(), 'Publisher');
        XMLCustomWriter::createChildWithText($this->getDoc(), $publisherElement, 'PublishingRole', O4DOI_PUBLISHING_ROLE_PUBLISHER);
        XMLCustomWriter::createChildWithText($this->getDoc(), $publisherElement, 'PublisherName', $this->getPublisher($journalLocalePrecedence));
        return $publisherElement;
    }

    /**
     * Create a serial version element.
     * @param string|null $issn
     * @param string $productForm
     * @return DOMElement
     */
    public function _serialVersionElement($issn, $productForm) {
        $journal = $this->getJournal();
        $serialVersionElement = XMLCustomWriter::createElement($this->getDoc(), 'SerialVersion');

        if ($productForm === O4DOI_PRODUCT_FORM_ELECTRONIC) {
            XMLCustomWriter::appendChild($serialVersionElement, $this->_idElement('Product', O4DOI_ID_TYPE_PROPRIETARY, $this->getProprietaryID($journal)));
        }

        if (!empty($issn)) {
            $cleanIssn = PKPString::regexp_replace('/[^0-9xX]/', '', (string) $issn);
            XMLCustomWriter::appendChild($serialVersionElement, $this->_idElement('Product', O4DOI_ID_TYPE_ISSN, $cleanIssn));
        }

        XMLCustomWriter::createChildWithText($this->getDoc(), $serialVersionElement, 'ProductForm', $productForm);

        if ($productForm === O4DOI_PRODUCT_FORM_ELECTRONIC) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $serialVersionElement, 'EpubFormat', O4DOI_EPUB_FORMAT_HTML);
            XMLCustomWriter::createChildWithText($this->getDoc(), $serialVersionElement, 'EpubFormatDescription', 'Open Journal Systems (OJS)');
        }

        return $serialVersionElement;
    }

    /**
     * Create the journal issue element.
     * @param Issue|null $issue
     * @param array $journalLocalePrecedence
     * @return DOMElement
     */
    public function _journalIssueElement($issue, $journalLocalePrecedence) {
        $journalIssueElement = XMLCustomWriter::createElement($this->getDoc(), 'JournalIssue');

        if ($issue instanceof Issue) {
            $volume = $issue->getVolume();
            if (!empty($volume)) {
                XMLCustomWriter::createChildWithText($this->getDoc(), $journalIssueElement, 'JournalVolumeNumber', (string) $volume);
            }

            $number = $issue->getNumber();
            if (!empty($number)) {
                XMLCustomWriter::createChildWithText($this->getDoc(), $journalIssueElement, 'JournalIssueNumber', (string) $number);
            }

            $identification = $issue->getIssueIdentification();
            if (!empty($identification)) {
                XMLCustomWriter::createChildWithText($this->getDoc(), $journalIssueElement, 'JournalIssueDesignation', $identification);
            }

            $year = (string) $issue->getYear();
            $yearlen = strlen($year);
            if (!empty($year) && ($yearlen === 2 || $yearlen === 4)) {
                $issueDate = XMLCustomWriter::createElement($this->getDoc(), 'JournalIssueDate');
                XMLCustomWriter::createChildWithText($this->getDoc(), $issueDate, 'DateFormat', O4DOI_DATE_FORMAT_YYYY);

                if ($yearlen === 2) {
                    if ((int)$year <= (int)date('y') + 1) {
                        $year = '20' . $year;
                    } else {
                        $year = '19' . $year;
                    }
                }
                XMLCustomWriter::createChildWithText($this->getDoc(), $issueDate, 'Date', $year);
                XMLCustomWriter::appendChild($journalIssueElement, $issueDate);
            }

            if ($this->_getObjectType() === 'Issue') {
                $datePublished = $issue->getDatePublished();
                if (!empty($datePublished)) {
                    XMLCustomWriter::appendChild($journalIssueElement, $this->_publicationDateElement($datePublished));
                }

                $rawTitles = $issue->getTitle(null);
                $localizedTitles = is_array($rawTitles) ? $rawTitles : (is_string($rawTitles) ? [$this->getJournal()->getPrimaryLocale() => $rawTitles] : []);
                $localizedTitles = $this->getTranslationsByPrecedence($localizedTitles, $journalLocalePrecedence);
                
                $locale = $this->getJournal()->getPrimaryLocale();
                $localizedTitle = '';
                foreach($localizedTitles as $loc => $title) {
                    $locale = $loc;
                    $localizedTitle = $title;
                    break;
                }
                
                if (empty($localizedTitle)) {
                    $journalRawTitles = $this->getJournal()->getTitle(null);
                    $journalLocalizedTitles = is_array($journalRawTitles) ? $journalRawTitles : (is_string($journalRawTitles) ? [$this->getJournal()->getPrimaryLocale() => $journalRawTitles] : []);
                    $journalLocalizedTitles = $this->getTranslationsByPrecedence($journalLocalizedTitles, $journalLocalePrecedence);
                    
                    foreach($journalLocalizedTitles as $loc => $title) {
                        $locale = $loc;
                        $localizedTitle = $title;
                        break;
                    }
                    
                    if (!empty($localizedTitle)) {
                        $showTitle = $issue->getShowTitle();
                        $issue->setShowTitle(0);
                        $localizedTitle = $localizedTitle . ', ' . $issue->getIssueIdentification();
                        $issue->setShowTitle($showTitle);
                    }
                }
                
                if (!empty($localizedTitle)) {
                    XMLCustomWriter::appendChild($journalIssueElement, $this->_titleElement($locale, $localizedTitle, O4DOI_TITLE_TYPE_ISSUE));
                }

                if (!$this->_exportIssuesAsWork()) {
                    /** @var IssueGalleyDAO $issueGalleyDao */
                    $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
                    $issueGalleys = $issueGalleyDao->getGalleysByIssue($issue->getId());
                    if (!empty($issueGalleys)) {
                        foreach($issueGalleys as $issueGalley) {
                            XMLCustomWriter::appendChild($journalIssueElement, $this->_extentElement($issueGalley));
                        }
                    }
                }
            }
        }

        return $journalIssueElement;
    }

    /**
     * Create an extent element.
     * @param PKPFile $file
     * @return DOMElement
     */
    public function _extentElement($file) {
        $extentElement = XMLCustomWriter::createElement($this->getDoc(), 'Extent');
        XMLCustomWriter::createChildWithText($this->getDoc(), $extentElement, 'ExtentType', O4DOI_EXTENT_TYPE_FILESIZE);
        XMLCustomWriter::createChildWithText($this->getDoc(), $extentElement, 'ExtentValue', (string) $file->getFileSize());
        XMLCustomWriter::createChildWithText($this->getDoc(), $extentElement, 'ExtentUnit', O4DOI_EXTENT_UNIT_BYTES);
        return $extentElement;
    }

    /**
     * Create a publication date element.
     * @param string $datePublished
     * @return DOMElement
     */
    public function _publicationDateElement($datePublished) {
        $timestamp = strtotime((string) $datePublished);
        return $this->createElementWithText('PublicationDate', $timestamp !== false ? date('Ymd', $timestamp) : date('Ymd'));
    }

    /**
     * Create a content item element.
     * @param PublishedArticle $article
     * @param ArticleGalley|null $galley
     * @param array $objectLocalePrecedence
     * @return DOMElement
     */
    public function _contentItemElement($article, $galley, $objectLocalePrecedence) {
        $contentItemElement = XMLCustomWriter::createElement($this->getDoc(), 'ContentItem');

        $seq = $article->getSeq();
        if (!empty($seq)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $contentItemElement, 'SequenceNumber', (string) $seq);
        }

        $pages = $article->getPages();
        if (is_numeric($pages)) {
            $pages = (int) $pages;
        } else {
            if (preg_match("/([0-9]+)\s*-\s*([0-9]+)/i", (string) $pages, $matches)) {
                if (is_numeric($matches[1]) && is_numeric($matches[2])) {
                    $pages = (int) $matches[2] - (int) $matches[1] + 1;
                }
            }
        }
        if (is_int($pages)) {
            $textItemElement = XMLCustomWriter::createElement($this->getDoc(), 'TextItem');
            XMLCustomWriter::createChildWithText($this->getDoc(), $textItemElement, 'NumberOfPages', (string) $pages);
            XMLCustomWriter::appendChild($contentItemElement, $textItemElement);
        }

        if ($galley instanceof ArticleGalley && !$galley->getRemoteURL()) {
            XMLCustomWriter::appendChild($contentItemElement, $this->_extentElement($galley));
        }

        $rawTitles = $article->getTitle(null);
        $titlesArray = is_array($rawTitles) ? $rawTitles : (is_string($rawTitles) ? [$article->getLocale() => $rawTitles] : []);
        $titles = $this->getTranslationsByPrecedence($titlesArray, $objectLocalePrecedence);
        
        foreach ($titles as $locale => $title) {
            XMLCustomWriter::appendChild($contentItemElement, $this->_titleElement($locale, $title, O4DOI_TITLE_TYPE_FULL));
        }

        $authors = $article->getAuthors();
        if (is_array($authors)) {
            foreach ($authors as $author) {
                if ($author instanceof Author) {
                    XMLCustomWriter::appendChild($contentItemElement, $this->_contributorElement($author, $objectLocalePrecedence));
                }
            }
        }

        $languageCode = AppLocale::get3LetterIsoFromLocale($objectLocalePrecedence[0] ?? AppLocale::getLocale());
        if (!empty($languageCode)) {
            $languageElement = XMLCustomWriter::createElement($this->getDoc(), 'Language');
            XMLCustomWriter::createChildWithText($this->getDoc(), $languageElement, 'LanguageRole', O4DOI_LANGUAGE_ROLE_LANGUAGE_OF_TEXT);
            XMLCustomWriter::createChildWithText($this->getDoc(), $languageElement, 'LanguageCode', $languageCode);
            XMLCustomWriter::appendChild($contentItemElement, $languageElement);
        }

        $rawKeywords = $article->getSubject(null);
        $keywordsArray = is_array($rawKeywords) ? $rawKeywords : (is_string($rawKeywords) ? [$article->getLocale() => $rawKeywords] : []);
        $keywords = $this->getPrimaryTranslation($keywordsArray, $objectLocalePrecedence);
        if (!empty($keywords)) {
            XMLCustomWriter::appendChild($contentItemElement, $this->_subjectElement(O4DOI_SUBJECT_SCHEME_PUBLISHER, $keywords));
        }

        list($subjectSchemeName, $subjectCode) = $this->getSubjectClass($article, $objectLocalePrecedence);
        if (!empty($subjectSchemeName) && !empty($subjectCode)) {
            XMLCustomWriter::appendChild($contentItemElement, $this->_subjectElement(O4DOI_SUBJECT_SCHEME_PROPRIETARY, $subjectCode, $subjectSchemeName));
        }

        return $contentItemElement;
    }

    /**
     * Create a content item element.
     * @param Author $author
     * @param array $objectLocalePrecedence
     * @return DOMElement
     */
    public function _contributorElement($author, $objectLocalePrecedence) {
        $contributorElement = XMLCustomWriter::createElement($this->getDoc(), 'Contributor');

        $seq = $author->getSequence();
        if (!empty($seq)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'SequenceNumber', (string) $seq);
        }

        XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'ContributorRole', O4DOI_CONTRIBUTOR_ROLE_ACTUAL_AUTHOR);

        $personName = $author->getFullName();
        if (!empty($personName)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'PersonName', $personName);
        }

        $invertedPersonName = $author->getFullName(true);
        if (!empty($invertedPersonName)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'PersonNameInverted', $invertedPersonName);
        }

        $rawAffiliation = $author->getAffiliation(null);
        $affiliationArray = is_array($rawAffiliation) ? $rawAffiliation : (is_string($rawAffiliation) ? ['default' => $rawAffiliation] : []);
        $affiliation = $this->getPrimaryTranslation($affiliationArray, $objectLocalePrecedence);
        
        if (!empty($affiliation)) {
            $affiliationElement = XMLCustomWriter::createElement($this->getDoc(), 'ProfessionalAffiliation');
            XMLCustomWriter::createChildWithText($this->getDoc(), $affiliationElement, 'Affiliation', $affiliation);
            XMLCustomWriter::appendChild($contributorElement, $affiliationElement);
        }

        $rawBioNote = $author->getBiography(null);
        $bioNoteArray = is_array($rawBioNote) ? $rawBioNote : (is_string($rawBioNote) ? ['default' => $rawBioNote] : []);
        $bioNote = $this->getPrimaryTranslation($bioNoteArray, $objectLocalePrecedence);
        
        if (!empty($bioNote)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'BiographicalNote', PKPString::html2text($bioNote));
        }

        return $contributorElement;
    }

    /**
     * Create a subject element.
     * @param string $subjectSchemeId
     * @param string $subjectHeadingOrCode
     * @param string|null $subjectSchemeName
     * @return DOMElement
     */
    public function _subjectElement($subjectSchemeId, $subjectHeadingOrCode, $subjectSchemeName = null) {
        $subjectElement = XMLCustomWriter::createElement($this->getDoc(), 'Subject');
        XMLCustomWriter::createChildWithText($this->getDoc(), $subjectElement, 'SubjectSchemeIdentifier', $subjectSchemeId);

        if ($subjectSchemeName === null) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $subjectElement, 'SubjectHeadingText', $subjectHeadingOrCode);
        } else {
            XMLCustomWriter::createChildWithText($this->getDoc(), $subjectElement, 'SubjectSchemeName', $subjectSchemeName);
            XMLCustomWriter::createChildWithText($this->getDoc(), $subjectElement, 'SubjectCode', $subjectHeadingOrCode);
        }

        return $subjectElement;
    }

    /**
     * Create a description text element.
     * @param string $locale
     * @param string $description
     * @return DOMElement
     */
    public function _otherTextElement($locale, $description) {
        $otherTextElement = XMLCustomWriter::createElement($this->getDoc(), 'OtherText');
        XMLCustomWriter::createChildWithText($this->getDoc(), $otherTextElement, 'TextTypeCode', O4DOI_TEXT_TYPE_MAIN_DESCRIPTION);

        $language = AppLocale::get3LetterIsoFromLocale($locale);
        $attributes = ['textformat' => O4DOI_TEXTFORMAT_ASCII];
        if (!empty($language)) {
            $attributes['language'] = $language;
        }
        
        $textElement = $this->createElementWithText('Text', $description, $attributes);
        XMLCustomWriter::appendChild($otherTextElement, $textElement);

        return $otherTextElement;
    }

    /**
     * Create a description text element.
     * @param string $workOrProduct
     * @param string $relationCode
     * @param array $ids
     * @return DOMElement
     */
    public function _relationElement($workOrProduct, $relationCode, $ids) {
        $relationElement = XMLCustomWriter::createElement($this->getDoc(), "Related$workOrProduct");
        XMLCustomWriter::createChildWithText($this->getDoc(), $relationElement, 'RelationCode', $relationCode);

        foreach($ids as $idType => $id) {
            XMLCustomWriter::appendChild($relationElement, $this->_idElement($workOrProduct, $idType, $id));
        }

        return $relationElement;
    }

    /**
     * Retrieve the DOI of an object. The DOI will be
     * patched if we are in test mode.
     * @param Issue|PublishedArticle|ArticleGalley $object
     * @return string
     */
    public function _getDoi($object) {
        $doi = $object->getPubId('doi');
        if (!empty($doi) && $this->getTestMode()) {
            $doi = PKPString::regexp_replace('#^[^/]+/#', MEDRA_WS_TESTPREFIX . '/', $doi);
        }
        return $doi;
    }
    
}
?>
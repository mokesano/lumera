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
 * [WIZDAM EDITION] Refactored for PHP 7.4/8.0+ (Strict Types, DOMDocument Type Hints)
 */

if (!class_exists('DOIExportDom')) { // Bug #7848
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

    /** @var Request */
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
     * @param Request $request
     * @param DOIExportPlugin $plugin
     * @param int $schema One of the O4DOI_* schema constants.
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     * @param int|string $exportIssuesAs
     */
    public function __construct($request, $plugin, $schema, $journal, $objectCache, $exportIssuesAs) {
        // Configure the DOM.
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
     * The OJS object type represented by this DOM
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
     * @return Request
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
        return $this->_exportIssuesAs == O4DOI_ISSUE_AS_WORK;
    }


    //
    // Public methods
    //
    /**
     * Generate the O4DOI XML document.
     * @see DOIExportDom::generate()
     * @param array $objects An array of OJS objects to export.
     * @return DOMDocument|bool
     */
    public function generate($objects) {
        // Create the XML document and its root element.
        $doc = $this->getDoc();
        $rootElement = $this->rootElement();
        XMLCustomWriter::appendChild($doc, $rootElement);

        // Generate and add the O4DOI header.
        $headerElement = $this->_headerElement();
        if (!$headerElement) return false;
        XMLCustomWriter::appendChild($rootElement, $headerElement);

        // Generate and add the O4DOI payload.
        foreach ($objects as $object) {
            $objectElement = $this->_objectElement($object);
            if (!$objectElement) return false;
            XMLCustomWriter::appendChild($rootElement, $objectElement);
            unset($object, $objectElement);
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
        if ($this->getTestMode()) {
            return O4DOI_XSI_SCHEMALOCATION_DEV;
        } else {
            return O4DOI_XSI_SCHEMALOCATION;
        }
    }

    /**
     * Retrieve all the OJS publication objects containing the
     * data required to generate the given O4DOI schema.
     * @param Issue|PublishedArticle|ArticleGalley $object The object to export.
     * @return array An array with the required OJS objects.
     */
    public function retrievePublicationObjects($object): array {
        // Initialize local variables.
        $cache = $this->getCache();

        // Retrieve basic OJS objects.
        $publicationObjects = parent::retrievePublicationObjects($object);

        // Retrieve additional related objects.
        // For articles and galleys: Retrieve all galleys of the article:
        if ($object instanceof PublishedArticle || $object instanceof ArticleGalley) {
            assert(isset($publicationObjects['article']));
            $publicationObjects['galleysByArticle'] = $this->retrieveGalleysByArticle($publicationObjects['article']);
        }

        // For issues: Retrieve all articles and galleys of the issue:
        if ($object instanceof Issue) {
            // Articles by issue.
            assert(isset($publicationObjects['issue']));
            $issue = $publicationObjects['issue'];
            $publicationObjects['articlesByIssue'] = $this->retrieveArticlesByIssue($issue);

            // Galleys by issue.
            $issueId = $issue->getId();
            if (!$cache->isCached('galleysByIssue', $issueId)) {
                foreach($publicationObjects['articlesByIssue'] as $article) {
                    $this->retrieveGalleysByArticle($article);
                    unset($article);
                }
                $cache->markComplete('galleysByIssue', $issueId);
            }
            $publicationObjects['galleysByIssue'] = $cache->get('galleysByIssue', $issueId);
        }

        return $publicationObjects;
    }


    //
    // Private helper methods
    //
    /**
     * Return information about the given schema.
     * @param int $schema One of the O4DOI_* schema constants.
     * @return array An array with schema information.
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

        return $schemaInfos[$schema];
    }

    /**
     * Generate the O4DOI header element.
     * @see DOIExportDom::_headerElement()
     * @return DOMElement|bool
     */
    public function _headerElement() {
        $headerElement = XMLCustomWriter::createElement($this->getDoc(), 'Header');

        // Technical Contact
        $fromCompany = $this->getPluginSetting('fromCompany');
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'FromCompany', $fromCompany);
        $fromName = $this->getPluginSetting('fromName');
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'FromPerson', $fromName);
        $fromEmail = $this->getPluginSetting('fromEmail');
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'FromEmail', $fromEmail);

        // Addressee
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'ToCompany', 'mEDRA');

        // Timestamp
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'SentDate', date('YmdHi'));

        // Message note
        $app = Application::getApplication();
        $name = $app->getName();
        $version = $app->getCurrentVersion();
        $versionString = $version->getVersionString();
        XMLCustomWriter::createChildWithText($this->getDoc(), $headerElement, 'MessageNote', "This dataset was exported with $name, version $versionString.");

        return $headerElement;
    }

    /**
     * Generate O4DOI object payload.
     * @param Issue|PublishedArticle|ArticleGalley $object
     * @return DOMElement|bool
     */
    public function _objectElement($object) {
        $journal = $this->getJournal();

        // Make sure that the schema and the object combine.
        // [WIZDAM] Updated to strict class check
        $expectedType = $this->_getObjectType();
        if (!is_a($object, $expectedType)) {
            // Allow flexibility if type matches loosely (e.g. subclass), but strict check is preferred.
            assert(is_a($object, $expectedType));
        }

        // Declare variables that will contain publication objects.
        $issue = null; /* @var $issue Issue */
        $article = null; /* @var $article PublishedArticle */
        $galley = null; /* @var $galley ArticleGalley */
        $articlesByIssue = null;
        $galleysByArticle = null;
        $galleysByIssue = null;

        // Retrieve required publication objects (depends on the schema of this DOM).
        $pubObjects = $this->retrievePublicationObjects($object);
        extract($pubObjects);

        // Main object element.
        $objectElement = XMLCustomWriter::createElement($this->getDoc(), $this->_getObjectElementName());

        // Get the DOI.
        $doi = $this->_getDoi($object);
        if (empty($doi)) {
            $this->_addError('plugins.importexport.common.export.error.noDoiAssigned', $object->getId());
            return false;
        }

        // Notification type (mandatory)
        $registeredDoi = $object->getData('medra::registeredDoi');
        assert(empty($registeredDoi) || $registeredDoi == $doi);
        $notificationType = (empty($registeredDoi) ? O4DOI_NOTIFICATION_TYPE_NEW : O4DOI_NOTIFICATION_TYPE_UPDATE);
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'NotificationType', $notificationType);

        // DOI (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'DOI', $doi);

        // DOI URL (mandatory)
        $request = $this->getRequest();
        $router = $request->getRouter();
        $url = '';
        switch ($this->_getSchema()) {
            case O4DOI_ISSUE_AS_WORK:
            case O4DOI_ISSUE_AS_MANIFESTATION:
                $url = $router->url($request, $journal->getPath(), 'issue', 'view', $issue->getBestIssueId($journal));
                break;

            case O4DOI_ARTICLE_AS_WORK:
                $url = $router->url($request, $journal->getPath(), 'article', 'view', $article->getBestArticleId($journal));
                break;

            case O4DOI_ARTICLE_AS_MANIFESTATION:
                $url = $router->url($request, $journal->getPath(), 'article', 'view', [$article->getBestArticleId($journal), $galley->getBestGalleyId($journal)]);
                break;
        }
        assert(!empty($url));
        if ($this->getTestMode()) {
            // Change server domain for testing.
            $url = PKPString::regexp_replace('#://[^\s]+/index.php#', '://example.com/index.php', $url);
        }
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'DOIWebsiteLink', $url);

        // DOI strucural type
        if ($this->_isWork()) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'DOIStructuralType', 'Abstraction');
        } else {
            XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'DOIStructuralType', 'DigitalFixation');
        }

        // Registrant (mandatory)
        $registrantName = $this->getPluginSetting('registrantName');
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'RegistrantName', $registrantName);

        // Registration authority (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $objectElement, 'RegistrationAuthority', 'mEDRA');

        // Proprietary ID
        XMLCustomWriter::appendChild($objectElement, $this->_idElement($this->_isWork()?'Work':'Product', O4DOI_ID_TYPE_PROPRIETARY, $this->getProprietaryId($journal, $issue, $article, $galley)));

        // Issue/journal locale precedence.
        $nullVar = null;
        $journalLocalePrecedence = $this->getObjectLocalePrecedence($nullVar, $nullVar);

        // Serial Publication (mandatory)
        XMLCustomWriter::appendChild($objectElement, $this->_serialPublicationElement($issue, $journalLocalePrecedence));

        // Journal Issue (mandatory)
        XMLCustomWriter::appendChild($objectElement, $this->_journalIssueElement($issue, $journalLocalePrecedence));

        // Object locale precedence.
        $objectLocalePrecedence = $this->getObjectLocalePrecedence($article, $galley);

        if ($this->_isArticle()) {
            assert(!empty($article));

            // Content Item (mandatory for articles)
            $contentItemElement = $this->_contentItemElement($article, $galley, $objectLocalePrecedence);
            XMLCustomWriter::appendChild($objectElement, $contentItemElement);

            // For articles, final elements go into the ContentItem element.
            $finalElementsContainer = $contentItemElement;
        } else {
            // For issues, final elements go directly into the message payload element.
            $finalElementsContainer = $objectElement;
        }

        // Object Description
        if ($this->_isArticle()) {
            $descriptions = $article->getAbstract(null);
        } else {
            $descriptions = $issue->getDescription(null);
        }
        $descriptions = $this->getTranslationsByPrecedence($descriptions, $objectLocalePrecedence);
        foreach ($descriptions as $locale => $description) {
            XMLCustomWriter::appendChild($finalElementsContainer, $this->_otherTextElement($locale, $description));
        }

        if ($this->_isArticle()) {
            // Article Publication Date
            $datePublished = $article->getDatePublished();
            if (!empty($datePublished)) {
                XMLCustomWriter::appendChild($contentItemElement, $this->_publicationDateElement($datePublished));
            }

            // Relations
            // 1) article (as-work and as-manifestation):
            if ($this->_exportIssuesAsWork()) {
                // related work:
                // - is part of issue-as-work
                $issueWorkOrProduct = 'Work';
            } else {
                // related product:
                // - is part of issue-as-manifestation
                $issueWorkOrProduct = 'Product';
            }
            $relatedIssueIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue)];
            $doi = $this->_getDoi($issue);
            if (!empty($doi)) $relatedIssueIds[O4DOI_ID_TYPE_DOI] = $doi;
            $relatedIssueElement = $this->_relationElement($issueWorkOrProduct, O4DOI_RELATION_IS_PART_OF, $relatedIssueIds);

            // 2) article-as-work:
            if ($this->_isWork()) {
                XMLCustomWriter::appendChild($finalElementsContainer, $relatedIssueElement);

                // related products:
                // - is manifested in articles-as-manifestation
                foreach($galleysByArticle as $relatedGalley) {
                    $relatedGalleyIds = [
                        O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $article, $relatedGalley)
                    ];
                    $doi = $this->_getDoi($relatedGalley);
                    if (!empty($doi)) $relatedGalleyIds[O4DOI_ID_TYPE_DOI] = $doi;
                    $relatedArticleElement = $this->_relationElement('Product', O4DOI_RELATION_IS_MANIFESTED_IN, $relatedGalleyIds);
                    XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                    unset($relatedGalley, $relatedGalleyIds, $relatedArticleElement);
                }

            // 3) article-as-manifestation:
            } else {
                // Include issue-as-work before article-as-work.
                if ($issueWorkOrProduct == 'Work') XMLCustomWriter::appendChild($finalElementsContainer, $relatedIssueElement);

                // related work:
                // - is a manifestation of article-as-work
                $relatedArticleIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $article)];
                $doi = $this->_getDoi($article);
                if (!empty($doi)) $relatedArticleIds[O4DOI_ID_TYPE_DOI] = $doi;
                $relatedArticleElement = $this->_relationElement('Work', O4DOI_RELATION_IS_A_MANIFESTATION_OF, $relatedArticleIds);
                XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                unset($relatedArticleIds, $relatedArticleElement);

                // Include issue-as-manifestation after article-as-work.
                if ($issueWorkOrProduct == 'Product') XMLCustomWriter::appendChild($finalElementsContainer, $relatedIssueElement);

                // related products:
                foreach($galleysByArticle as $relatedGalley) {
                    $relatedGalleyIds = [
                        O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $article, $relatedGalley)
                    ];
                    $doi = $this->_getDoi($relatedGalley);
                    if (!empty($doi)) $relatedGalleyIds[O4DOI_ID_TYPE_DOI] = $doi;

                    // - is a different form of all other articles-as-manifestation
                    //   with the same article id and language but different form
                    if ($galley->getLocale() == $relatedGalley->getLocale() &&
                            $galley->getLabel() != $relatedGalley->getLabel()) {

                        $relatedArticleElement = $this->_relationElement('Product', O4DOI_RELATION_IS_A_DIFFERENT_FORM_OF, $relatedGalleyIds);
                        XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                        unset($relatedArticleElement);
                    }

                    // - is a different language version of all other articles-as-manifestation
                    //   with the same article id and form but different language
                    if ($galley->getLabel() == $relatedGalley->getLabel() &&
                            $galley->getLocale() != $relatedGalley->getLocale()) {

                        $relatedArticleElement = $this->_relationElement('Product', O4DOI_RELATION_IS_A_LANGUAGE_VERSION_OF, $relatedGalleyIds);
                        XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                        unset($relatedArticleElement);
                    }

                    unset($relatedGalley, $relatedGalleyIds);
                }
            }
            unset($relatedIssueIds, $relatedIssueElement);
        } else {
            // 4) issue (as-work and as-manifestation):
            // related works:
            // - includes articles-as-work
            foreach ($articlesByIssue as $relatedArticle) {
                $relatedArticleIds = [O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $relatedArticle)];
                $doi = $this->_getDoi($relatedArticle);
                if (!empty($doi)) $relatedArticleIds[O4DOI_ID_TYPE_DOI] = $doi;
                $relatedArticleElement = $this->_relationElement('Work', O4DOI_RELATION_INCLUDES, $relatedArticleIds);
                XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                unset($relatedArticle, $relatedArticleIds, $relatedArticleElement);
            }

            // related products:
            // - includes articles-as-manifestation
            foreach($galleysByIssue as $relatedGalley) {
                $relatedGalleyIds = [
                    O4DOI_ID_TYPE_PROPRIETARY => $this->getProprietaryId($journal, $issue, $relatedGalley, $relatedGalley)
                ];
                $doi = $this->_getDoi($relatedGalley);
                if (!empty($doi)) $relatedGalleyIds[O4DOI_ID_TYPE_DOI] = $doi;
                $relatedArticleElement = $this->_relationElement('Product', O4DOI_RELATION_INCLUDES, $relatedGalleyIds);
                XMLCustomWriter::appendChild($finalElementsContainer, $relatedArticleElement);
                unset($relatedGalley, $relatedGalleyIds, $relatedArticleElement);
            }
        }

        return $objectElement;
    }

    /**
     * Create a work or product id element.
     * @param string $workOrProduct "Work" or "Product"
     * @param string $idType One of the O4DOI_ID_TYPE_* constants
     * @param string $id The ID.
     * @return DOMElement
     */
    public function _idElement($workOrProduct, $idType, $id) {
        $idElement = XMLCustomWriter::createElement($this->getDoc(), "${workOrProduct}Identifier");

        // ID type (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $idElement, "${workOrProduct}IDType", $idType);

        // ID (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $idElement, 'IDValue', $id);

        return $idElement;
    }

    /**
     * Generate O4DOI serial publication.
     * @param Issue $issue
     * @param array $journalLocalePrecedence
     * @return DOMElement
     */
    public function _serialPublicationElement($issue, $journalLocalePrecedence) {
        $journal = $this->getJournal();
        $serialElement = XMLCustomWriter::createElement($this->getDoc(), 'SerialPublication');

        // Serial Work (mandatory)
        XMLCustomWriter::appendChild($serialElement, $this->_serialWorkElement($journalLocalePrecedence));

        // Electronic Serial Version
        $onlineIssn = $journal->getSetting('onlineIssn');
        XMLCustomWriter::appendChild($serialElement, $this->_serialVersionElement($onlineIssn, O4DOI_PRODUCT_FORM_ELECTRONIC));

        // Print Serial Version
        if (($printIssn = $journal->getSetting('printIssn')) && $this->_isWork()) {
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

        // Title (mandatory)
        $journalTitles = $this->getTranslationsByPrecedence($journal->getTitle(null), $journalLocalePrecedence);
        assert(!empty($journalTitles));
        foreach($journalTitles as $locale => $journalTitle) {
            XMLCustomWriter::appendChild($serialWorkElement, $this->_titleElement($locale, $journalTitle, O4DOI_TITLE_TYPE_FULL));
        }

        // Publisher
        XMLCustomWriter::appendChild($serialWorkElement, $this->_publisherElement($journalLocalePrecedence));

        // Country of Publication (mandatory)
        $publicationCountry = $this->getPluginSetting('publicationCountry');
        XMLCustomWriter::createChildWithText($this->getDoc(), $serialWorkElement, 'CountryOfPublication', $publicationCountry);

        return $serialWorkElement;
    }

    /**
     * Create a work or product id element.
     * @param string $locale e.g. 'en_US'
     * @param string $localizedTitle
     * @param string $titleType One of the O4DOI_TITLE_TYPE_* constants.
     * @return DOMElement
     */
    public function _titleElement($locale, $localizedTitle, $titleType) {
        $titleElement = XMLCustomWriter::createElement($this->getDoc(), 'Title');

        // Text format
        XMLCustomWriter::setAttribute($titleElement, 'textformat', O4DOI_TEXTFORMAT_ASCII);

        // Language
        $language = AppLocale::get3LetterIsoFromLocale($locale);
        assert(!empty($language));
        XMLCustomWriter::setAttribute($titleElement, 'language', $language);

        // Title type (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $titleElement, 'TitleType', $titleType);

        // Title text (mandatory)
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

        // Publishing role (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $publisherElement, 'PublishingRole', O4DOI_PUBLISHING_ROLE_PUBLISHER);

        // Publisher name (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $publisherElement, 'PublisherName', $this->getPublisher($journalLocalePrecedence));

        return $publisherElement;
    }

    /**
     * Create a serial version element.
     * @param string|null $issn
     * @param string $productForm One of the O4DOI_PRODUCT_FORM_* constants
     * @return DOMElement
     */
    public function _serialVersionElement($issn, $productForm) {
        $journal = $this->getJournal();
        $serialVersionElement = XMLCustomWriter::createElement($this->getDoc(), 'SerialVersion');

        // Proprietary Journal Identifier
        if ($productForm == O4DOI_PRODUCT_FORM_ELECTRONIC) {
            XMLCustomWriter::appendChild($serialVersionElement, $this->_idElement('Product', O4DOI_ID_TYPE_PROPRIETARY, $this->getProprietaryID($journal)));
        }

        // ISSN
        if (!empty($issn)) {
            $issn = PKPString::regexp_replace('/[^0-9xX]/', '', $issn);
            XMLCustomWriter::appendChild($serialVersionElement, $this->_idElement('Product', O4DOI_ID_TYPE_ISSN, $issn));
        }

        // Product Form
        XMLCustomWriter::createChildWithText($this->getDoc(), $serialVersionElement, 'ProductForm', $productForm);

        if ($productForm == O4DOI_PRODUCT_FORM_ELECTRONIC) {
            // ePublication Format
            XMLCustomWriter::createChildWithText($this->getDoc(), $serialVersionElement, 'EpubFormat', O4DOI_EPUB_FORMAT_HTML);

            // ePublication Format Description
            XMLCustomWriter::createChildWithText($this->getDoc(), $serialVersionElement, 'EpubFormatDescription', 'Open Journal Systems (OJS)');
        }

        return $serialVersionElement;
    }

    /**
     * Create the journal issue element.
     * @param Issue $issue
     * @param array $journalLocalePrecedence
     * @return DOMElement
     */
    public function _journalIssueElement($issue, $journalLocalePrecedence) {
        $journalIssueElement = XMLCustomWriter::createElement($this->getDoc(), 'JournalIssue');

        // Volume
        $volume = $issue->getVolume();
        if (!empty($volume)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $journalIssueElement, 'JournalVolumeNumber', $volume);
        }

        // Number
        $number = $issue->getNumber();
        if (!empty($number)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $journalIssueElement, 'JournalIssueNumber', $number);
        }

        // Identification
        $identification = $issue->getIssueIdentification();
        if (!empty($identification)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $journalIssueElement, 'JournalIssueDesignation', $identification);
        }

        assert(!(empty($number) && empty($identification)));

        // Nominal Year
        $year = (string)$issue->getYear();
        $yearlen = strlen($year);
        if (!empty($year) && ($yearlen == 2 || $yearlen == 4)) {
            $issueDate = XMLCustomWriter::createElement($this->getDoc(), 'JournalIssueDate');
            XMLCustomWriter::createChildWithText($this->getDoc(), $issueDate, 'DateFormat', O4DOI_DATE_FORMAT_YYYY);

            // Try to extend the year if necessary.
            if ($yearlen == 2) {
                // Assume that the issue date will never be
                // more than one year in the future.
                if ((int)$year <= (int)date('y')+1) {
                    $year = '20' . $year;
                } else {
                    $year = '19' . $year;
                }
            }
            XMLCustomWriter::createChildWithText($this->getDoc(), $issueDate, 'Date', $year);
            XMLCustomWriter::appendChild($journalIssueElement, $issueDate);
        }

        if ($this->_getObjectType() == 'Issue') {
            // Publication Date
            $datePublished = $issue->getDatePublished();
            if (!empty($datePublished)) {
                XMLCustomWriter::appendChild($journalIssueElement, $this->_publicationDateElement($datePublished));
            }

            // Issue Title (mandatory)
            $localizedTitles = $this->getTranslationsByPrecedence($issue->getTitle(null), $journalLocalePrecedence);
            // Retrieve the first key/value pair...
            foreach($localizedTitles as $locale => $localizedTitle) break;
            if (empty($localizedTitle)) {
                $journal = $this->getJournal();
                $localizedTitles = $this->getTranslationsByPrecedence($journal->getTitle(null), $journalLocalePrecedence);
                // Retrieve the first key/value pair...
                foreach($localizedTitles as $locale => $localizedTitle) break;
                assert(!empty($localizedTitle));

                // Hack to make sure that no untranslated title appears:
                $showTitle = $issue->getShowTitle();
                $issue->setShowTitle(0);
                $localizedTitle = $localizedTitle . ', ' . $issue->getIssueIdentification();
                $issue->setShowTitle($showTitle);
            }
            XMLCustomWriter::appendChild($journalIssueElement, $this->_titleElement($locale, $localizedTitle, O4DOI_TITLE_TYPE_ISSUE));

            // Extent (for issues-as-manifestation only)
            if (!$this->_exportIssuesAsWork()) {
                $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO'); /* @var $issueGalleyDao IssueGalleyDAO */
                $issueGalleys = $issueGalleyDao->getGalleysByIssue($issue->getId());
                if (!empty($issueGalleys)) {
                    foreach($issueGalleys as $issueGalley) {
                        XMLCustomWriter::appendChild($journalIssueElement, $this->_extentElement($issueGalley));
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

        // Extent type
        XMLCustomWriter::createChildWithText($this->getDoc(), $extentElement, 'ExtentType', O4DOI_EXTENT_TYPE_FILESIZE);

        // Extent value
        XMLCustomWriter::createChildWithText($this->getDoc(), $extentElement, 'ExtentValue', $file->getFileSize());

        // Extent unit
        XMLCustomWriter::createChildWithText($this->getDoc(), $extentElement, 'ExtentUnit', O4DOI_EXTENT_UNIT_BYTES);

        return $extentElement;
    }

    /**
     * Create a publication date element.
     * @param string $datePublished The publication timestamp.
     * @return DOMElement
     */
    public function _publicationDateElement($datePublished) {
        return $this->createElementWithText('PublicationDate', date('Ymd', strtotime($datePublished)));
    }

    /**
     * Create a content item element.
     * @param PublishedArticle $article
     * @param ArticleGalley|null $galley This will only be set in case we're transmitting an article-as-manifestation.
     * @param array $objectLocalePrecedence
     * @return DOMElement
     */
    public function _contentItemElement($article, $galley, $objectLocalePrecedence) {
        $contentItemElement = XMLCustomWriter::createElement($this->getDoc(), 'ContentItem');

        // Sequence number
        $seq = $article->getSeq();
        assert(!empty($seq));
        XMLCustomWriter::createChildWithText($this->getDoc(), $contentItemElement, 'SequenceNumber', $seq);

        // Number of pages
        $pages = $article->getPages();
        if (is_numeric($pages)) {
            $pages = (int)$pages;
        } else {
            // If the field is not numeric then try to parse it (eg. "pp. 3-8").
            if (preg_match("/([0-9]+)\s*-\s*([0-9]+)/i", $pages, $matches)) {
                if (is_numeric($matches[1]) && is_numeric($matches[2])) {
                    $firstPage = (int)$matches[1];
                    $lastPage = (int)$matches[2];
                    $pages = $lastPage - $firstPage + 1;
                }
            }
        }
        if (is_int($pages)) {
            $textItemElement = XMLCustomWriter::createElement($this->getDoc(), 'TextItem');
            XMLCustomWriter::createChildWithText($this->getDoc(), $textItemElement, 'NumberOfPages', $pages);
            XMLCustomWriter::appendChild($contentItemElement, $textItemElement);
        }

        // Extent (for article-as-manifestation only)
        if (($galley instanceof ArticleGalley) && !$galley->getRemoteURL()) {
            XMLCustomWriter::appendChild($contentItemElement, $this->_extentElement($galley));
        }

        // Article Title (mandatory)
        $titles = $this->getTranslationsByPrecedence($article->getTitle(null), $objectLocalePrecedence);
        assert(!empty($titles));
        foreach ($titles as $locale => $title) {
            XMLCustomWriter::appendChild($contentItemElement, $this->_titleElement($locale, $title, O4DOI_TITLE_TYPE_FULL));
        }

        // Contributors
        $authors = $article->getAuthors();
        assert(!empty($authors));
        foreach ($authors as $author) {
            XMLCustomWriter::appendChild($contentItemElement, $this->_contributorElement($author, $objectLocalePrecedence));
        }

        // Language
        $languageCode = AppLocale::get3LetterIsoFromLocale($objectLocalePrecedence[0]);
        assert(!empty($languageCode));
        $languageElement = XMLCustomWriter::createElement($this->getDoc(), 'Language');
        XMLCustomWriter::createChildWithText($this->getDoc(), $languageElement, 'LanguageRole', O4DOI_LANGUAGE_ROLE_LANGUAGE_OF_TEXT);
        XMLCustomWriter::createChildWithText($this->getDoc(), $languageElement, 'LanguageCode', $languageCode);
        XMLCustomWriter::appendChild($contentItemElement, $languageElement);

        // Article keywords
        $keywords = $this->getPrimaryTranslation($article->getSubject(null), $objectLocalePrecedence);
        if (!empty($keywords)) {
            XMLCustomWriter::appendChild($contentItemElement, $this->_subjectElement(O4DOI_SUBJECT_SCHEME_PUBLISHER, $keywords));
        }

        // Subject class
        list($subjectSchemeName, $subjectCode) = $this->getSubjectClass($article, $objectLocalePrecedence);
        if (!(empty($subjectSchemeName) || empty($subjectCode))) {
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

        // Sequence number
        $seq = $author->getSequence();
        assert(!empty($seq));
        XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'SequenceNumber', $seq);

        // Contributor role (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'ContributorRole', O4DOI_CONTRIBUTOR_ROLE_ACTUAL_AUTHOR);

        // Person name (mandatory)
        $personName = $author->getFullName();
        assert(!empty($personName));
        XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'PersonName', $personName);

        // Inverted person name
        $invertedPersonName = $author->getFullName(true);
        assert(!empty($invertedPersonName));
        XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'PersonNameInverted', $invertedPersonName);

        // Affiliation
        $affiliation = $this->getPrimaryTranslation($author->getAffiliation(null), $objectLocalePrecedence);
        if (!empty($affiliation)) {
            $affiliationElement = XMLCustomWriter::createElement($this->getDoc(), 'ProfessionalAffiliation');
            XMLCustomWriter::createChildWithText($this->getDoc(), $affiliationElement, 'Affiliation', $affiliation);
            XMLCustomWriter::appendChild($contributorElement, $affiliationElement);
        }

        // Biographical note
        $bioNote = $this->getPrimaryTranslation($author->getBiography(null), $objectLocalePrecedence);
        if (!empty($bioNote)) {
            XMLCustomWriter::createChildWithText($this->getDoc(), $contributorElement, 'BiographicalNote', PKPString::html2text($bioNote));
        }

        return $contributorElement;
    }

    /**
     * Create a subject element.
     * @param string $subjectSchemeId One of the O4DOI_SUBJECT_SCHEME_* constants.
     * @param string $subjectHeadingOrCode The subject.
     * @param string|null $subjectSchemeName A subject scheme name.
     * @return DOMElement
     */
    public function _subjectElement($subjectSchemeId, $subjectHeadingOrCode, $subjectSchemeName = null) {
        $subjectElement = XMLCustomWriter::createElement($this->getDoc(), 'Subject');

        // Subject Scheme Identifier
        XMLCustomWriter::createChildWithText($this->getDoc(), $subjectElement, 'SubjectSchemeIdentifier', $subjectSchemeId);

        if (is_null($subjectSchemeName)) {
            // Subject Heading
            XMLCustomWriter::createChildWithText($this->getDoc(), $subjectElement, 'SubjectHeadingText', $subjectHeadingOrCode);
        } else {
            // Subject Scheme Name
            XMLCustomWriter::createChildWithText($this->getDoc(), $subjectElement, 'SubjectSchemeName', $subjectSchemeName);

            // Subject Code
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

        // Text Type
        XMLCustomWriter::createChildWithText($this->getDoc(), $otherTextElement, 'TextTypeCode', O4DOI_TEXT_TYPE_MAIN_DESCRIPTION);

        // Text Language
        $language = AppLocale::get3LetterIsoFromLocale($locale);
        assert(!empty($language));

        // Text element and attributes
        $attributes = [
            'textformat' => O4DOI_TEXTFORMAT_ASCII,
            'language' => $language
        ];
        $textElement = $this->createElementWithText('Text', $description, $attributes);
        XMLCustomWriter::appendChild($otherTextElement, $textElement);

        return $otherTextElement;
    }

    /**
     * Create a description text element.
     * @param string $workOrProduct
     * @param string $relationCode One of the O4DOI_RELATION_* constants.
     * @param array $ids
     * @return DOMElement
     */
    public function _relationElement($workOrProduct, $relationCode, $ids) {
        $relationElement = XMLCustomWriter::createElement($this->getDoc(), "Related$workOrProduct");

        // Relation code (mandatory)
        XMLCustomWriter::createChildWithText($this->getDoc(), $relationElement, 'RelationCode', $relationCode);

        // Work/Product ID (mandatory)
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
<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/classes/DOIExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOIExportDom
 * @ingroup plugins_importexport_crossref_classes
 *
 * @brief Onix for DOI (O4DOI) XML export format implementation.
 */

import('lib.pkp.classes.xml.XMLCustomWriter');

define('DOI_EXPORT_FILETYPE_PDF', 'PDF');
define('DOI_EXPORT_FILETYPE_HTML', 'HTML');
define('DOI_EXPORT_FILETYPE_XML', 'XML');
define('DOI_EXPORT_FILETYPE_PS', 'PostScript');
define('DOI_EXPORT_XMLNS_XSI' , 'http://www.w3.org/2001/XMLSchema-instance');
define('DOI_EXPORT_XMLNS_JATS', 'http://www.ncbi.nlm.nih.gov/JATS1');
define('DOI_EXPORT_XMLNS_AI', 'http://www.crossref.org/AccessIndicators.xsd');

class DOIExportDom {

    //
    // Public properties
    //
    /** @var array */
    public array $_errors = [];

    /**
     * Retrieve export error details.
     * @return array
     */
    public function getErrors(): array {
        return $this->_errors;
    }

    /**
     * Add an error to the errors list.
     * @param string $errorTranslationKey
     * @param string|null $param
     */
    public function _addError($errorTranslationKey, $param = null): void {
        $this->_errors[] = [$errorTranslationKey, $param];
    }

    //
    // Protected properties
    //
    /** @var XMLNode|DOMImplementation */
    protected $_doc;

    /**
     * Get the XML document.
     * @return object XMLNode|DOMImplementation
     */
    public function getDoc() {
        return $this->_doc;
    }

    /** @var PKPRequest */
    protected $_request;

    /**
     * Get the current request.
     * @return PKPRequest
     */
    public function getRequest() {
        return $this->_request;
    }

    /**
     * Are we in test mode?
     * @return bool
     */
    public function getTestMode(): bool {
        $request = $this->getRequest();
        return ($request->getUserVar('testMode') === '1');
    }

    /** @var DOIExportPlugin */
    protected $_plugin;

    /**
     * Get a plug-in setting.
     * @param string $settingName
     * @return mixed
     */
    public function getPluginSetting($settingName) {
        $plugin = $this->_plugin;
        $journal = $this->getJournal();
        return $plugin->getSetting((int) $journal->getId(), $settingName);
    }

    /** @var Journal */
    protected $_journal;

    /**
     * Get the journal (a.k.a. serial title) of this message.
     * @return Journal
     */
    public function getJournal() {
        return $this->_journal;
    }

    /** @var PubObjectCache A cache for publication objects */
    protected $_cache;

    /**
     * Get the object cache.
     * @return PubObjectCache
     */
    public function getCache() {
        return $this->_cache;
    }

    //
    // Constructor
    //
    /**
     * Constructor
     * @param PKPRequest $request
     * @param DOIExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function __construct($request, $plugin, $journal, $objectCache) {
        $this->_doc = XMLCustomWriter::createDocument();
        $this->_request = $request;
        $this->_plugin = $plugin;
        $this->_journal = $journal;
        $this->_cache = $objectCache;
    }

    /**
     * [SHIM] Backward Compatibility
     * @param PKPRequest $request
     * @param DOIExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function DOIExportDom($request, $plugin, $journal, $objectCache) {
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
     * Generate the XML document.
     * @param array $objects
     * @return object|bool XMLNode|DOMImplementation|boolean
     */
    public function generate($objects) {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    //
    // Protected template methods
    //
    /**
     * The DOM's root element.
     * @return string
     */
    public function getRootElementName() {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Return the XML namespace.
     * @return string
     */
    public function getNamespace() {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Return the XML schema version.
     * @return string
     */
    public function getXmlSchemaVersion(): string {
        return '';
    }

    /**
     * Return the XML schema location.
     * @return string
     */
    public function getXmlSchemaLocation() {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    //
    // Protected helper methods
    //
    /**
     * Generate the XML root element.
     * @return object XMLNode|DOMImplementation
     */
    public function rootElement() {
        $rootElement = XMLCustomWriter::createElement($this->getDoc(), $this->getRootElementName());

        XMLCustomWriter::setAttribute($rootElement, 'xmlns', $this->getNamespace());
        XMLCustomWriter::setAttribute($rootElement, 'xmlns:xsi', DOI_EXPORT_XMLNS_XSI);
        if ($this->getXmlSchemaVersion() !== '') {
            XMLCustomWriter::setAttribute($rootElement, 'version', $this->getXmlSchemaVersion());
        }
        XMLCustomWriter::setAttribute($rootElement, 'xmlns:jats', DOI_EXPORT_XMLNS_JATS);
        XMLCustomWriter::setAttribute($rootElement, 'xmlns:ai', DOI_EXPORT_XMLNS_AI);
        XMLCustomWriter::setAttribute($rootElement, 'xsi:schemaLocation', $this->getXmlSchemaLocation());

        return $rootElement;
    }

    /**
     * Create an XML element with a text node.
     * @param string $name
     * @param string $value
     * @param array $attributes
     * @return object XMLNode|DOMImplementation
     */
    public function createElementWithText($name, $value, $attributes = []) {
        $element = XMLCustomWriter::createElement($this->getDoc(), $name);
        $elementContent = XMLCustomWriter::createTextNode($this->getDoc(), PKPString::html2text($value));
        XMLCustomWriter::appendChild($element, $elementContent);
        foreach ($attributes as $attributeName => $attributeValue) {
            XMLCustomWriter::setAttribute($element, $attributeName, (string) $attributeValue);
        }
        return $element;
    }

    /**
     * Retrieve all the OJS publication objects containing the data required to generate the given schema.
     * @param Issue|PublishedArticle|ArticleGalley|ArticleFile $object
     * @return array
     */
    public function retrievePublicationObjects($object): array {
        $journal = $this->getJournal();
        $cache = $this->getCache();
        $publicationObjects = [];

        switch (true) {
            case $object instanceof Issue:
                $cache->add($object, null);
                $publicationObjects['issue'] = $object;
                break;
            case $object instanceof PublishedArticle:
                $cache->add($object, null);
                $publicationObjects['article'] = $object;
                break;
            case $object instanceof ArticleGalley:
                $publicationObjects['galley'] = $object;
                break;
        }

        if ($object instanceof ArticleFile) {
            $articleId = (int) $object->getArticleId();
            $article = null;
            
            if ($cache->isCached('articles', $articleId)) {
                $article = $cache->get('articles', $articleId);
            } else {
                /** @var PublishedArticleDAO $articleDao */
                $articleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $article = $articleDao->getPublishedArticleByArticleId($articleId, (int) $journal->getId());
                if ($article instanceof PublishedArticle) {
                    $cache->add($article, null);
                }
            }
            
            if ($article instanceof PublishedArticle) {
                $cache->add($object, $article);
                $publicationObjects['article'] = $article;
            }
        }

        if (!isset($publicationObjects['issue']) && isset($publicationObjects['article'])) {
            $issueId = (int) $publicationObjects['article']->getIssueId();
            $issue = null;
            
            if ($cache->isCached('issues', $issueId)) {
                $issue = $cache->get('issues', $issueId);
            } else {
                /** @var IssueDAO $issueDao */
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issue = $issueDao->getIssueById($issueId, (int) $journal->getId());
                if ($issue instanceof Issue) {
                    $cache->add($issue, null);
                }
            }
            
            if ($issue instanceof Issue) {
                $publicationObjects['issue'] = $issue;
            }
        }

        return $publicationObjects;
    }

    /**
     * Retrieve all articles for the given issue and commit them to the cache.
     * @param Issue $issue
     * @return array
     */
    public function retrieveArticlesByIssue($issue): array {
        $cache = $this->getCache();
        $issueId = (int) $issue->getId();

        if (!$cache->isCached('articlesByIssue', $issueId)) {
            /** @var PublishedArticleDAO $articleDao */
            $articleDao = DAORegistry::getDAO('PublishedArticleDAO');
            $articles = $articleDao->getPublishedArticles($issueId);
            if (!empty($articles)) {
                foreach ($articles as $article) {
                    $cache->add($article, null);
                }
                $cache->markComplete('articlesByIssue', $issueId);
            }
        }
        
        $articlesByIssue = $cache->get('articlesByIssue', $issueId);
        return is_array($articlesByIssue) ? $articlesByIssue : [];
    }

    /**
     * Retrieve all galleys for the given article and commit them to the cache.
     * @param PublishedArticle $article
     * @return array
     */
    public function retrieveGalleysByArticle($article): array {
        $cache = $this->getCache();
        $articleId = (int) $article->getId();
        
        if (!$cache->isCached('galleysByArticle', $articleId)) {
            /** @var ArticleGalleyDAO $galleyDao */
            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
            $galleys = $galleyDao->getGalleysByArticle($articleId);
            if (!empty($galleys)) {
                foreach ($galleys as $galley) {
                    $cache->add($galley, $article);
                }
                $cache->markComplete('galleysByArticle', $articleId);
            }
        }
        
        $galleysByArticle = $cache->get('galleysByArticle', $articleId);
        return is_array($galleysByArticle) ? $galleysByArticle : [];
    }

    /**
     * Identify the locale precedence for this export.
     * @param PublishedArticle|Article $article
     * @param ArticleGalley $galley
     * @return array
     */
    public function getObjectLocalePrecedence($article, $galley): array {
        $locales = [];
        if ($galley instanceof ArticleGalley && AppLocale::isLocaleValid($galley->getLocale())) {
            $locales[] = $galley->getLocale();
        }
        if ($article instanceof Submission) {
            $articleLocale = $this->translateLanguageToLocale((string) $article->getLanguage());
            if ($articleLocale !== null) {
                $locales[] = $articleLocale;
            }

            if (AppLocale::isLocaleValid($article->getLocale())) {
                $locales[] = $article->getLocale();
            }
        }

        $journal = $this->getJournal();
        $locales[] = $journal->getPrimaryLocale();

        $formLocales = array_keys($journal->getSupportedFormLocaleNames());
        sort($formLocales);
        foreach ($formLocales as $formLocale) {
            if (!in_array($formLocale, $locales, true)) {
                $locales[] = $formLocale;
            }
        }

        return array_values(array_unique($locales));
    }

    /**
     * Try to translate an ISO language code to an OJS locale.
     * @param string $language
     * @return string|null
     */
    public function translateLanguageToLocale($language): ?string {
        $locale = null;
        if (strlen($language) === 2) {
            $language = AppLocale::get3LetterFrom2LetterIsoLanguage($language);
        }
        if (strlen($language) === 3) {
            $language = AppLocale::getLocaleFrom3LetterIso($language);
        }
        if (AppLocale::isLocaleValid($language)) {
            $locale = $language;
        }
        return $locale;
    }

    /**
     * Identify the primary translation from an array of localized data.
     * @param array|null $localizedData
     * @param array $localePrecedence
     * @return mixed|null
     */
    public function getPrimaryTranslation($localizedData, $localePrecedence) {
        if (!is_array($localizedData) || empty($localizedData)) {
            return null;
        }

        foreach ($localePrecedence as $locale) {
            if (isset($localizedData[$locale]) && !empty($localizedData[$locale])) {
                return $localizedData[$locale];
            }
        }

        ksort($localizedData);
        foreach ($localizedData as $locale => $value) {
            if (!empty($value)) {
                return $value;
            }
        }

        return null;
    }

    /**
     * Re-order localized data by locale precedence.
     * @param array|null $localizedData
     * @param array $localePrecedence
     * @return array
     */
    public function getTranslationsByPrecedence($localizedData, $localePrecedence): array {
        $reorderedLocalizedData = [];

        if (!is_array($localizedData) || empty($localizedData)) {
            return $reorderedLocalizedData;
        }

        foreach ($localePrecedence as $locale) {
            if (isset($localizedData[$locale]) && !empty($localizedData[$locale])) {
                $reorderedLocalizedData[$locale] = $localizedData[$locale];
            }
            unset($localizedData[$locale]);
        }

        ksort($localizedData);
        return array_merge($reorderedLocalizedData, $localizedData);
    }

    /**
     * Generate a proprietary ID for the given objects.
     * @param Journal $journal
     * @param Issue|null $issue
     * @param PublishedArticle|ArticleFile|null $articleOrArticleFile
     * @param ArticleGalley|SuppFile|null $articleFile
     * @return string
     */
    public function getProprietaryId($journal, $issue = null, $articleOrArticleFile = null, $articleFile = null): string {
        $proprietaryId = (string) $journal->getId();
        if ($issue instanceof Issue) {
            $proprietaryId .= '-' . $issue->getId();
        }
        
        if ($articleOrArticleFile) {
            $proprietaryId .= '-';
            if ($articleOrArticleFile instanceof PublishedArticle) {
                $proprietaryId .= $articleOrArticleFile->getId();
            } elseif ($articleOrArticleFile instanceof ArticleFile) {
                $proprietaryId .= $articleOrArticleFile->getArticleId();
            }
        }
        
        if ($articleFile) {
            $proprietaryId .= '-';
            if ($articleFile instanceof ArticleGalley) {
                $proprietaryId .= 'g';
            } elseif ($articleFile instanceof SuppFile) {
                $proprietaryId .= 's';
            }
            $proprietaryId .= $articleFile->getId();
        }
        return $proprietaryId;
    }

    /**
     * Identify the publisher of the journal.
     * @param array $localePrecedence
     * @return string
     */
    public function getPublisher($localePrecedence): string {
        $journal = $this->getJournal();
        $publisher = $journal->getSetting('publisherInstitution');
        
        if (empty($publisher)) {
            $rawTitle = $journal->getTitle(null);
            $titleArray = is_array($rawTitle) ? $rawTitle : (is_string($rawTitle) ? [$journal->getPrimaryLocale() => $rawTitle] : []);
            $publisher = $this->getPrimaryTranslation($titleArray, $localePrecedence);
        }
        
        return (string) ($publisher ?? $journal->getTitle($journal->getPrimaryLocale()));
    }

    /**
     * Identify the article subject class and code.
     * @param PublishedArticle|Article $article
     * @param array $objectLocalePrecedence
     * @return array
     */
    public function getSubjectClass($article, $objectLocalePrecedence): array {
        $journal = $this->getJournal();
        
        $rawSchemeTitle = $journal->getSetting('metaSubjectClassTitle', null);
        $schemeTitleArray = is_array($rawSchemeTitle) ? $rawSchemeTitle : (is_string($rawSchemeTitle) ? ['default' => $rawSchemeTitle] : []);
        $subjectSchemeTitle = $this->getPrimaryTranslation($schemeTitleArray, $objectLocalePrecedence);
        
        $rawSchemeUrl = $journal->getSetting('metaSubjectClassUrl', null);
        $schemeUrlArray = is_array($rawSchemeUrl) ? $rawSchemeUrl : (is_string($rawSchemeUrl) ? ['default' => $rawSchemeUrl] : []);
        $subjectSchemeUrl = $this->getPrimaryTranslation($schemeUrlArray, $objectLocalePrecedence);
        
        if (empty($subjectSchemeTitle)) {
            $subjectSchemeName = (string) $subjectSchemeUrl;
        } else {
            if (empty($subjectSchemeUrl)) {
                $subjectSchemeName = (string) $subjectSchemeTitle;
            } else {
                $subjectSchemeName = "$subjectSchemeTitle ($subjectSchemeUrl)";
            }
        }
        
        $rawSubjectCode = $article->getSubjectClass(null);
        $subjectCodeArray = is_array($rawSubjectCode) ? $rawSubjectCode : (is_string($rawSubjectCode) ? [$article->getLocale() => $rawSubjectCode] : []);
        $subjectCode = $this->getPrimaryTranslation($subjectCodeArray, $objectLocalePrecedence);
        
        return [(string) $subjectSchemeName, (string) ($subjectCode ?? '')];
    }

    /**
     * Try to identify the resource type of the given article file.
     * @param ArticleFile $articleFile
     * @return string|null
     */
    public function getFileType($articleFile): ?string {
        if ($articleFile instanceof ArticleXMLGalley) {
            return DOI_EXPORT_FILETYPE_XML;
        }
        if ($articleFile instanceof ArticleHTMLGalley) {
            return DOI_EXPORT_FILETYPE_HTML;
        }
        
        $fileType = $articleFile->getFileType();
        if (!empty($fileType)) {
            if (str_contains($fileType, 'html')) {
                return DOI_EXPORT_FILETYPE_HTML;
            }
            if (str_contains($fileType, 'pdf')) {
                return DOI_EXPORT_FILETYPE_PDF;
            }
            if (str_contains($fileType, 'postscript')) {
                return DOI_EXPORT_FILETYPE_PS;
            }
            if (str_contains($fileType, 'xml')) {
                return DOI_EXPORT_FILETYPE_XML;
            }
        }
        
        return null;
    }
    
}
?>
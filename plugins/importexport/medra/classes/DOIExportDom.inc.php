<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/.../classes/DOIExportDom.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DOIExportDom
 * @ingroup plugins_importexport_..._classes
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

    //
    // Protected properties
    //
    /** @var XMLNode|DOMImplementation */
    protected $_doc;

    /** @var Request */
    protected $_request;

    /** @var DOIExportPlugin */
    protected $_plugin;

    /** @var Journal */
    protected $_journal;

    /** @var PubObjectCache A cache for publication objects */
    protected $_cache;

    /**
     * Constructor
     * @param Request $request
     * @param DOIExportPlugin $plugin
     * @param Journal $journal
     * @param PubObjectCache $objectCache
     */
    public function __construct($request, $plugin, $journal, $objectCache) {
        // Configure the DOM.
        $this->_doc = XMLCustomWriter::createDocument();
        $this->_request = $request;
        $this->_plugin = $plugin;
        $this->_journal = $journal;
        $this->_cache = $objectCache;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DOIExportDom() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Retrieve export error details.
     * @return array
     */
    public function getErrors(): array {
        return $this->_errors;
    }

    /**
     * Add an error to the errors list.
     * @param string $errorTranslationKey An i18n key.
     * @param string|null $param An additional translation parameter.
     */
    public function _addError(string $errorTranslationKey, ?string $param = null): void {
        $this->_errors[] = [$errorTranslationKey, $param];
    }

    /**
     * Get the XML document.
     * @return object XMLNode|DOMImplementation
     */
    public function getDoc() {
        return $this->_doc;
    }

    /**
     * Get the current request.
     * @return Request
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
        return ($request->getUserVar('testMode') == '1');
    }

    /**
     * Get a plug-in setting.
     * @param string $settingName
     * @return mixed
     */
    public function getPluginSetting(string $settingName) {
        $plugin = $this->_plugin;
        $journal = $this->getJournal();
        $settingValue = $plugin->getSetting($journal->getId(), $settingName);
        assert(!empty($settingValue));
        return $settingValue;
    }

    /**
     * Get the journal (a.k.a. serial title) of this
     * O4DOI message.
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

    //
    // Public methods
    //
    /**
     * Generate the XML document.
     * This method either returns a fully validated XML document
     * containing all given objects for export or it returns a boolean
     * 'false' to indicate an error.
     *
     * If one or more errors occur then DOIExportDom::getErrors()
     * will return localized error details for display by the client.
     *
     * @param array $objects An array of issues, articles or galleys. The
     * array must not contain more than one object type.
     * @return object|bool XMLNode|DOMImplementation|boolean An XML document 
     * or 'false' if an error occurred.
     */
    public function generate(array $objects) {
        assert(false);
        return false;
    }

    //
    // Protected template methods
    //
    /**
     * The DOM's root element.
     * @return string
     */
    protected function getRootElementName() {
        assert(false);
    }

    /**
     * Return the XML namespace.
     * @return string
     */
    protected function getNamespace() {
        assert(false);
    }

    /**
     * Return the XML schema version.
     * @return string
     */
    protected function getXmlSchemaVersion(): string {
        return '';
    }

    /**
     * Return the XML schema location.
     * @return string
     */
    protected function getXmlSchemaLocation() {
        assert(false);
    }

    //
    // Protected helper methods
    //
    /**
     * Generate the XML root element.
     * @return object XMLNode|DOMImplementation
     */
    protected function rootElement() {
        // Create the root element and make it the document element of the document.
        $rootElement = XMLCustomWriter::createElement($this->getDoc(), $this->getRootElementName());

        // Add root-level attributes.
        XMLCustomWriter::setAttribute($rootElement, 'xmlns', $this->getNamespace());
        XMLCustomWriter::setAttribute($rootElement, 'xmlns:xsi', DOI_EXPORT_XMLNS_XSI);
        if ($this->getXmlSchemaVersion() != '') {
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
     * @param array $attributes An array with the attribute names as array
     * keys and attribute values as array values.
     * @return object XMLNode|DOMImplementation
     */
    protected function createElementWithText(string $name, string $value, array $attributes = []) {
        $element = XMLCustomWriter::createElement($this->getDoc(), $name);
        $elementContent = XMLCustomWriter::createTextNode($this->getDoc(), PKPString::html2text($value));
        XMLCustomWriter::appendChild($element, $elementContent);
        foreach($attributes as $attributeName => $attributeValue) {
            XMLCustomWriter::setAttribute($element, $attributeName, $attributeValue);
        }
        return $element;
    }

    /**
     * Retrieve all the OJS publication objects containing the
     * data required to generate the given O4DOI schema.
     * @param Issue|PublishedArticle|ArticleGalley $object The object to export.
     * @return array An array with the required OJS objects.
     */
    protected function retrievePublicationObjects($object): array {
        // Initialize local variables.
        $nullVar = null;
        $journal = $this->getJournal();
        $cache = $this->getCache();

        // Assign the object itself.
        $publicationObjects = [];
        switch (true) {
            case $object instanceof Issue:
                $cache->add($object, $nullVar);
                $publicationObjects['issue'] = $object;
                break;

            case $object instanceof PublishedArticle:
                $cache->add($object, $nullVar);
                $publicationObjects['article'] = $object;
                break;

            case $object instanceof ArticleGalley:
                $publicationObjects['galley'] = $object;
                break;
        }

        // Retrieve the article related to article files.
        if ($object instanceof ArticleFile) {
            $articleId = $object->getArticleId();
            if ($cache->isCached('articles', $articleId)) {
                $article = $cache->get('articles', $articleId);
            } else {
                $articleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $articleDao PublishedArticleDAO */
                $article = $articleDao->getPublishedArticleByArticleId($articleId, $journal->getId());
                if ($article) $cache->add($article, $nullVar);
            }
            assert($article instanceof PublishedArticle);
            $cache->add($object, $article);
            $publicationObjects['article'] = $article;
        }

        // Retrieve the issue if it's not yet there.
        if (!isset($publicationObjects['issue'])) {
            assert(isset($publicationObjects['article']));
            $issueId = $publicationObjects['article']->getIssueId();
            if ($cache->isCached('issues', $issueId)) {
                $issue = $cache->get('issues', $issueId);
            } else {
                $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
                $issue = $issueDao->getIssueById($issueId, $journal->getId());
                if ($issue) $cache->add($issue, $nullVar);
            }
            assert($issue instanceof Issue);
            $publicationObjects['issue'] = $issue;
        }

        return $publicationObjects;
    }

    /**
     * Retrieve all articles for the given issue
     * and commit them to the cache.
     * @param Issue $issue
     * @return array
     */
    protected function retrieveArticlesByIssue($issue): array {
        $articlesByIssue = [];
        $cache = $this->getCache();
        $issueId = $issue->getId();
        $nullVar = null;

        if (!$cache->isCached('articlesByIssue', $issueId)) {
            $articleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $articleDao PublishedArticleDAO */
            $articles = $articleDao->getPublishedArticles($issueId);
            if (!empty($articles)) {
                foreach ($articles as $article) {
                    $cache->add($article, $nullVar);
                    unset($article);
                }
                $cache->markComplete('articlesByIssue', $issueId);
                $articlesByIssue = $cache->get('articlesByIssue', $issueId);
            }
        } else {
            $articlesByIssue = $cache->get('articlesByIssue', $issueId);
        }
        return $articlesByIssue;
    }

    /**
     * Retrieve all galleys for the given article
     * and commit them to the cache.
     * @param PublishedArticle $article
     * @return array
     */
    protected function retrieveGalleysByArticle($article): array {
        $galleysByArticle = [];
        $cache = $this->getCache();
        $articleId = $article->getId();
        
        if (!$cache->isCached('galleysByArticle', $articleId)) {
            $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $galleyDao ArticleGalleyDAO */
            $galleys = $galleyDao->getGalleysByArticle($articleId);
            if (!empty($galleys)) {
                foreach($galleys as $galley) {
                    $cache->add($galley, $article);
                    unset($galley);
                }
                $cache->markComplete('galleysByArticle', $articleId);
                $galleysByArticle = $cache->get('galleysByArticle', $articleId);
            }
        } else {
            $galleysByArticle = $cache->get('galleysByArticle', $articleId);
        }
        return $galleysByArticle;
    }

    /**
     * Identify the locale precedence for this export.
     * @param PublishedArticle $article
     * @param ArticleGalley $galley
     * @return array A list of valid PKP locales in descending
     * order of priority.
     */
    protected function getObjectLocalePrecedence($article, $galley): array {
        $locales = [];
        if ($galley instanceof ArticleGalley && AppLocale::isLocaleValid($galley->getLocale())) {
            $locales[] = $galley->getLocale();
        }
        if ($article instanceof Submission) {
            // First try to translate the article language into a locale.
            $articleLocale = $this->translateLanguageToLocale($article->getLanguage());
            if (!is_null($articleLocale)) {
                $locales[] = $articleLocale;
            }

            // Use the article locale as fallback only
            // as this is the primary locale of article meta-data, not
            // necessarily of the article itself.
            if(AppLocale::isLocaleValid($article->getLocale())) {
                $locales[] = $article->getLocale();
            }
        }

        // Use the journal locale as fallback.
        $journal = $this->getJournal();
        $locales[] = $journal->getPrimaryLocale();

        // Use form locales as fallback.
        $formLocales = array_keys($journal->getSupportedFormLocaleNames());
        // Sort form locales alphabetically so that
        // we get a well-defined order.
        sort($formLocales);
        foreach($formLocales as $formLocale) {
            if (!in_array($formLocale, $locales)) $locales[] = $formLocale;
        }

        assert(!empty($locales));
        return $locales;
    }

    /**
     * Try to translate an ISO language code to an OJS locale.
     * @param string $language 2- or 3-letter ISO language code
     * @return string|null An OJS locale or null if no matching
     * locale could be found.
     */
    protected function translateLanguageToLocale(string $language): ?string {
        $locale = null;
        if (strlen($language) == 2) {
            $language = AppLocale::get3LetterFrom2LetterIsoLanguage($language);
        }
        if (strlen($language) == 3) {
            $language = AppLocale::getLocaleFrom3LetterIso($language);
        }
        if (AppLocale::isLocaleValid($language)) {
            $locale = $language;
        }
        return $locale;
    }

    /**
     * Identify the primary translation from an array of
     * localized data.
     * @param array $localizedData An array of localized
     * data (key: locale, value: localized data).
     * @param array $localePrecedence An array of locales
     * by descending priority.
     * @return mixed|null The value of the primary locale
     * or null if no primary translation could be found.
     */
    protected function getPrimaryTranslation(?array $localizedData, array $localePrecedence) {
        // Check whether we have localized data at all.
        if (!is_array($localizedData) || empty($localizedData)) return null;

        // Try all locales from the precedence list first.
        foreach($localePrecedence as $locale) {
            if (isset($localizedData[$locale]) && !empty($localizedData[$locale])) {
                return $localizedData[$locale];
            }
        }

        // As a fallback: use any translation by alphabetical
        // order of locales.
        ksort($localizedData);
        foreach($localizedData as $locale => $value) {
            if (!empty($value)) return $value;
        }

        // If we found nothing (how that?) return null.
        return null;
    }

    /**
     * Re-order localized data by locale precedence.
     * @param array $localizedData An array of localized
     * data (key: locale, value: localized data).
     * @param array $localePrecedence An array of locales
     * by descending priority.
     * @return array Re-ordered localized data.
     */
    protected function getTranslationsByPrecedence(?array $localizedData, array $localePrecedence): array {
        $reorderedLocalizedData = [];

        // Check whether we have localized data at all.
        if (!is_array($localizedData) || empty($localizedData)) return $reorderedLocalizedData;

        // Order by explicit locale precedence first.
        foreach($localePrecedence as $locale) {
            if (isset($localizedData[$locale]) && !empty($localizedData[$locale])) {
                $reorderedLocalizedData[$locale] = $localizedData[$locale];
            }
            unset($localizedData[$locale]);
        }

        // Order any remaining values alphabetically by locale
        // and amend the re-ordered array.
        ksort($localizedData);
        $reorderedLocalizedData = array_merge($reorderedLocalizedData, $localizedData);

        return $reorderedLocalizedData;
    }

    /**
     * Generate a proprietary ID for the given objects.
     *
     * The idea is to produce an idea that is globally unique within
     * an OJS installation so that we can uniquely identify the exported
     * object just by knowing the proprietary ID.
     *
     * We're using the internal ID rather than the "best ID" as the
     * "best ID" can be changed by the end user while the internal ID
     * is an automatically assigned database ID that cannot be changed
     * without DBA access.
     *
     * @param Journal $journal
     * @param Issue|null $issue
     * @param PublishedArticle|ArticleFile|null $articleOrArticleFile An object representing an article.
     * @param ArticleGalley|SuppFile|null $articleFile
     *
     * @return string The proprietary ID for the given objects.
     */
    protected function getProprietaryId($journal, $issue = null, $articleOrArticleFile = null, $articleFile = null): string {
        $proprietaryId = (string) $journal->getId();
        if ($issue) $proprietaryId .= '-' . $issue->getId();
        
        if ($articleOrArticleFile) {
            assert($issue);
            $proprietaryId .= '-';
            if ($articleOrArticleFile instanceof PublishedArticle) {
                $proprietaryId .= $articleOrArticleFile->getId();
            } else {
                assert($articleOrArticleFile instanceof ArticleFile);
                $proprietaryId .= $articleOrArticleFile->getArticleId();
            }
        }
        
        if ($articleFile) {
            assert($articleOrArticleFile);
            $proprietaryId .= '-';
            if ($articleFile instanceof ArticleGalley) {
                $proprietaryId .= 'g';
            } else {
                assert($articleFile instanceof SuppFile);
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
    protected function getPublisher(array $localePrecedence): string {
        $journal = $this->getJournal();
        $publisher = $journal->getSetting('publisherInstitution');
        if (empty($publisher)) {
            // Use the journal title if no publisher is set.
            // This corresponds to the logic implemented for OAI interfaces, too.
            $publisher = $this->getPrimaryTranslation($journal->getTitle(null), $localePrecedence);
        }
        assert(!empty($publisher));
        return (string) $publisher;
    }

    /**
     * Identify the article subject class and code.
     * @param PublishedArticle $article
     * @param array $objectLocalePrecedence
     * @return array The subject class and code.
     */
    protected function getSubjectClass($article, array $objectLocalePrecedence): array {
        $journal = $this->getJournal();
        $subjectSchemeTitle = $this->getPrimaryTranslation($journal->getSetting('metaSubjectClassTitle', null), $objectLocalePrecedence);
        $subjectSchemeUrl = $this->getPrimaryTranslation($journal->getSetting('metaSubjectClassUrl', null), $objectLocalePrecedence);
        
        if (empty($subjectSchemeTitle)) {
            $subjectSchemeName = (string) $subjectSchemeUrl;
        } else {
            if (empty($subjectSchemeUrl)) {
                $subjectSchemeName = (string) $subjectSchemeTitle;
            } else {
                $subjectSchemeName = "$subjectSchemeTitle ($subjectSchemeUrl)";
            }
        }
        $subjectCode = $this->getPrimaryTranslation($article->getSubjectClass(null), $objectLocalePrecedence);
        return [$subjectSchemeName, $subjectCode];
    }

    /**
     * Try to identify the resource type of the
     * given article file.
     * @param ArticleFile $articleFile
     * @return string|null One of the DOI_EXPORT_FILTYPE_* constants or null.
     */
    protected function getFileType($articleFile): ?string {
        // Identify the galley type.
        if ($articleFile instanceof ArticleXMLGalley) {
            return DOI_EXPORT_FILETYPE_XML;
        }
        if ($articleFile instanceof ArticleHTMLGalley) {
            return DOI_EXPORT_FILETYPE_HTML;
        }
        
        // Try to guess the resource type from the MIME type.
        $fileType = $articleFile->getFileType();
        if (!empty($fileType)) {
            if (strstr($fileType, 'html')) {
                return DOI_EXPORT_FILETYPE_HTML;
            }
            if (strstr($fileType, 'pdf')) {
                return DOI_EXPORT_FILETYPE_PDF;
            }
            if (strstr($fileType, 'postscript')) {
                return DOI_EXPORT_FILETYPE_PS;
            }
            if (strstr($fileType, 'xml')) {
                return DOI_EXPORT_FILETYPE_XML;
            }
        }
        
        return null;
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file plugins/generic/lucene/classes/SolrWebService.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SolrWebService
 * @ingroup plugins_generic_lucene_classes
 *
 * @brief Implements the communication protocol with the solr search server.
 */

define('SOLR_STATUS_ONLINE', 0x01);
define('SOLR_STATUS_OFFLINE', 0x02);
define('SOLR_INDEXINGSTATE_DIRTY', true);
define('SOLR_INDEXINGSTATE_CLEAN', false);
define('SOLR_AUTOSUGGEST_SUGGESTER', 0x01);
define('SOLR_AUTOSUGGEST_FACETING', 0x02);
define('SOLR_INDEXING_MAX_BATCHSIZE', 200);

import('lib.pkp.classes.webservice.WebServiceRequest');
import('lib.pkp.classes.webservice.XmlWebService');
import('lib.pkp.classes.xml.XMLCustomWriter');
import('plugins.generic.lucene.classes.SolrSearchRequest');
import('classes.search.ArticleSearch');

class SolrWebService extends XmlWebService {

    /** @var string */
    private $_solrSearchHandler;

    /** @var string */
    private $_solrCore;

    /** @var string */
    private $_solrServer;

    /** @var string */
    private $_instId;

    /** @var string */
    private $_serviceMessage = '';

    /** @var FileCache|null */
    private $_fieldCache;

    /** @var array */
    private $_journalCache = [];

    /** @var array */
    private $_issueCache = [];

    /** @var bool */
    private $_useProxySettings = false;

    /**
     * Constructor.
     * @param string $searchHandler
     * @param string $username
     * @param string $password
     * @param string $instId
     * @param bool $useProxy
     */
    public function __construct($searchHandler, $username, $password, $instId, $useProxy = false) {
        parent::__construct();

        $this->setAuthUsername($username);
        $this->setAuthPassword($password);

        assert(is_string($searchHandler) && !empty($searchHandler));
        $searchHandler = rtrim($searchHandler, '/');

        $searchHandlerParts = explode('/', $searchHandler);
        $this->_solrSearchHandler = array_pop($searchHandlerParts);
        $this->_solrCore = array_pop($searchHandlerParts);
        $this->_solrServer = implode('/', $searchHandlerParts) . '/';

        assert(is_string($instId) && !empty($instId));
        $this->_instId = $instId;
        $this->_useProxySettings = (bool) $useProxy;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $searchHandler
     * @param string $username
     * @param string $password
     * @param string $instId
     * @param bool $useProxy
     */
    public function SolrWebService($searchHandler, $username, $password, $instId, $useProxy = false) {
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
	// Getters and Setters
	//

    /**
     * Get the last service message.
     * @return string
     */
    public function getServiceMessage() {
        return (string) $this->_serviceMessage;
    }

    /**
     * Retrieve a journal (possibly from the cache).
     * @param int $journalId
     * @return Journal|null
     */
    public function _getJournal($journalId) {
        if (isset($this->_journalCache[$journalId])) {
            return $this->_journalCache[$journalId];
        }
        
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($journalId);
        $this->_journalCache[$journalId] = $journal;
        
        return $journal;
    }

    /**
     * Retrieve an issue (possibly from the cache).
     * @param int $issueId
     * @param int $journalId
     * @return Issue|null
     */
    public function _getIssue($issueId, $journalId) {
        if (isset($this->_issueCache[$issueId])) {
            return $this->_issueCache[$issueId];
        }
        
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getIssueById($issueId, $journalId, true);
        $this->_issueCache[$issueId] = $issue;
        
        return $issue;
    }

    /**
     * Mark a single article "changed".
     * @param int $articleId
     * @return void
     */
    public function markArticleChanged($articleId) {
        if (!is_numeric($articleId)) {
            assert(false);
            return;
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $articleDao->updateSetting($articleId, 'indexingState', SOLR_INDEXINGSTATE_DIRTY, 'bool');
    }

    /**
     * Mark the given journal for re-indexing.
     * @param int $journalId
     * @return int
     */
    public function markJournalChanged($journalId) {
        if (!is_numeric($journalId)) {
            assert(false);
            return 0;
        }

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $articles = $articleDao->getArticlesByJournalId($journalId);

        while (!$articles->eof()) {
            $article = $articles->next();
            $this->markArticleChanged($article->getId());
        }
        return $articles->getCount();
    }

    /**
     * (Re-)indexes all changed articles in Solr (push-indexing).
     * @param int $batchSize
     * @param int|null $journalId
     * @return int|null
     */
    public function pushChangedArticles($batchSize = SOLR_INDEXING_MAX_BATCHSIZE, $journalId = null) {
        return $this->_indexingTransaction([$this, '_pushIndexingCallback'], $batchSize, $journalId);
    }

    /**
     * Retrieves a batch of articles in XML format (pull-indexing).
     * @param callable $pullIndexingCallback
     * @param int $batchSize
     * @param int|null $journalId
     * @return int|null
     */
    public function pullChangedArticles($pullIndexingCallback, $batchSize = SOLR_INDEXING_MAX_BATCHSIZE, $journalId = null) {
        return $this->_indexingTransaction($pullIndexingCallback, $batchSize, $journalId);
    }

    /**
     * Deletes the given article from the Solr index.
     * @param int $articleId
     * @return bool
     */
    public function deleteArticleFromIndex($articleId) {
        $xml = '<id>' . $this->_instId . '-' . $articleId . '</id>';
        return $this->_deleteFromIndex($xml);
    }

    /**
     * Deletes all articles of a journal or installation from the Solr index.
     * @param int|null $journalId
     * @return bool
     */
    public function deleteArticlesFromIndex($journalId = null) {
        $journalQuery = '';
        if (is_numeric($journalId)) {
            $journalQuery = ' AND journal_id:' . $this->_instId . '-' . $journalId;
        }

        $xml = '<query>inst_id:' . $this->_instId . $journalQuery . '</query>';
        return $this->_deleteFromIndex($xml);
    }

    /**
     * Execute a search against the Solr search server.
     * @param SolrSearchRequest $searchRequest
     * @param int $totalResults
     * @return array|null
     */
    public function retrieveResults($searchRequest, &$totalResults) {
        $params = $this->_getSearchQueryParameters($searchRequest);
        if (!isset($params['q'])) {
            return [];
        }

        $itemsPerPage = $searchRequest->getItemsPerPage();
        $params['start'] = ($searchRequest->getPage() - 1) * $itemsPerPage;
        $params['rows'] = $itemsPerPage;
        $params['sort'] = $this->_getOrdering($searchRequest->getOrderBy(), $searchRequest->getOrderDir());

        if ($searchRequest->getHighlighting()) {
            $params['hl'] = 'on';
            $params['hl.fl'] = $this->_expandFieldList(['abstract', 'galleyFullText']);
        }

        $facetCategories = $searchRequest->getFacetCategories();
        if (!empty($facetCategories)) {
            $params['facet'] = 'on';
            $params['facet.field'] = [];
            $locale = AppLocale::getLocale();
            $facetFields = $this->_getFieldNames('facet');
            $enabledFields = 0;
            
            foreach ($facetFields['localized'] as $fieldName) {
                if (in_array($fieldName, $facetCategories, true)) {
                    $params['facet.field'][] = $fieldName . '_' . $locale . '_facet';
                    $enabledFields++;
                }
            }
            foreach ($facetFields['static'] as $categoryName => $fieldName) {
                if (in_array($categoryName, $facetCategories, true)) {
                    $params['facet.field'][] = $fieldName;
                    $enabledFields++;
                }
            }
            if (in_array('publicationDate', $facetCategories, true)) {
                $params['facet.range'] = 'publicationDate_dt';
                $params['facet.range.start'] = 'NOW/YEAR-50YEARS';
                $params['facet.range.end'] = 'NOW';
                $params['facet.range.gap'] = '+1YEAR';
                $params['facet.range.other'] = 'all';
                $enabledFields++;
            }
            assert($enabledFields === count($facetCategories));
        }

        $boostFactors = $searchRequest->getBoostFactors();
        foreach ($boostFactors as $field => $valueBoost) {
            foreach ($valueBoost as $value => $boostFactor) {
                if ($boostFactor == 0) {
                    if (!isset($params['fq'])) $params['fq'] = [];
                    $params['fq'][] = "-$field:$value";
                } elseif ($boostFactor > 0) {
                    if (!isset($params['boost'])) $params['boost'] = [];
                    $params['boost'][] = "map($field,$value,$value,$boostFactor,1)";
                }
            }
        }

        $url = $this->_getSearchUrl();
        $response = $this->_makeRequest($url, $params);
        if ($response === null) {
            return null;
        }

        $nodeList = $response->query('/response/result[@name="response"]/@numFound');
        assert($nodeList->length === 1);
        $resultNode = $nodeList->item(0);
        assert(is_numeric($resultNode->textContent));
        $totalResults = (int) $resultNode->textContent;

        $results = [];
        $docs = $response->query('/response/result/doc');
        foreach ($docs as $doc) {
            $currentDoc = [];
            foreach ($doc->childNodes as $docField) {
                $docFieldAtts = $docField->attributes;
                $fieldNameAtt = $docFieldAtts->getNamedItem('name');
                switch ($docField->nodeName) {
                    case 'float':
                        $currentDoc[$fieldNameAtt->value] = (float) $docField->textContent;
                        break;
                    case 'str':
                        $currentDoc[$fieldNameAtt->value] = $docField->textContent;
                        break;
                }
            }
            $results[] = $currentDoc;
        }

        $scoredResults = [];
        foreach ($results as $resultIndex => $result) {
            assert(isset($result['article_id']));
            $score = $itemsPerPage - $resultIndex;
            $articleId = $result['article_id'];
            
            if (strpos($articleId, $this->_instId . '-') !== 0) {
                continue;
            }
            $articleId = substr($articleId, strlen($this->_instId . '-'));
            if (!is_numeric($articleId)) {
                continue;
            }
            $scoredResults[$score] = (int) $articleId;
        }

        $spellingSuggestion = null;
        if ($searchRequest->getSpellcheck()) {
            $alternativeSpellingNodeList = $response->query('/response/lst[@name="spellcheck"]/lst[@name="suggestions"]/str[@name="collation"]');
            if ($alternativeSpellingNodeList->length === 1) {
                $alternativeSpellingNode = $alternativeSpellingNodeList->item(0);
                $spellingSuggestion = $alternativeSpellingNode->textContent;
                $spellingSuggestion = $this->_translateSearchPhrase($spellingSuggestion, true);
            }
        }

        $highligthedArticles = null;
        if ($searchRequest->getHighlighting()) {
            $highligthedArticles = [];
            $highlightingNodeList = $response->query('/response/lst[@name="highlighting"]/lst');
            foreach ($highlightingNodeList as $highlightingNode) {
                if ($highlightingNode->hasChildNodes()) {
                    $indexArticleId = $highlightingNode->attributes->getNamedItem('name')->nodeValue;
                    $articleIdParts = explode('-', $indexArticleId);
                    $articleId = array_pop($articleIdParts);
                    $excerpt = $highlightingNode->firstChild->firstChild->textContent;
                    if (is_numeric($articleId) && !empty($excerpt)) {
                        $highligthedArticles[$articleId] = $excerpt;
                    }
                }
            }
        }

        $facets = null;
        if (!empty($facetCategories)) {
            $facets = [];
            $facetsNodeList = $response->query('/response/lst[@name="facet_counts"]/lst[@name="facet_fields"]/lst');
            foreach ($facetsNodeList as $facetFieldNode) {
                $facetField = $facetFieldNode->attributes->getNamedItem('name')->nodeValue;
                $facetFieldParts = explode('_', $facetField);
                $facetCategory = array_shift($facetFieldParts);
                $facets[$facetCategory] = [];
                foreach ($facetFieldNode->childNodes as $facetNode) {
                    $facet = $facetNode->attributes->getNamedItem('name')->nodeValue;
                    $facetCount = (int) $facetNode->textContent;
                    if (!empty($facet) && $facetCount > 0 && $facetCount < $totalResults) {
                        $facets[$facetCategory][$facet] = $facetCount;
                    }
                }
            }

            $facetsNodeList = $response->query('/response/lst[@name="facet_counts"]/lst[@name="facet_ranges"]/lst');
            foreach ($facetsNodeList as $facetFieldNode) {
                $facetField = $facetFieldNode->attributes->getNamedItem('name')->nodeValue;
                $facetFieldParts = explode('_', $facetField);
                $facetCategory = array_shift($facetFieldParts);
                $facets[$facetCategory] = [];
                foreach ($facetFieldNode->childNodes as $rangeInfoNode) {
                    if ($rangeInfoNode->attributes->getNamedItem('name')->nodeValue === 'counts') {
                        foreach ($rangeInfoNode->childNodes as $facetNode) {
                            $facet = $facetNode->attributes->getNamedItem('name')->nodeValue;
                            $facet = date('Y', strtotime(substr($facet, 0, 10)));
                            $facetCount = (int) $facetNode->textContent;
                            if ($facetCount > 0 && $facetCount < $totalResults) {
                                $facets[$facetCategory][$facet] = $facetCount;
                            }
                        }
                        break;
                    }
                }
            }
        }

        return [
            'scoredResults' => $scoredResults,
            'spellingSuggestion' => $spellingSuggestion,
            'highlightedArticles' => $highligthedArticles,
            'facets' => $facets
        ];
    }

    /**
     * Retrieve auto-suggestions from the solr index.
     * @param SolrSearchRequest $searchRequest
     * @param string $fieldName
     * @param string $userInput
     * @param int $autosuggestType
     * @return array
     */
    public function getAutosuggestions($searchRequest, $fieldName, $userInput, $autosuggestType) {
        $allowedFieldNames = array_values(ArticleSearch::getIndexFieldMap());
        $allowedFieldNames[] = 'query';
        $allowedFieldNames[] = 'indexTerms';
        if (!in_array($fieldName, $allowedFieldNames, true)) {
            return [];
        }

        $autosuggestTypes = [SOLR_AUTOSUGGEST_SUGGESTER, SOLR_AUTOSUGGEST_FACETING];
        if (!in_array($autosuggestType, $autosuggestTypes, true)) {
            return [];
        }

        $url = $this->_getAutosuggestUrl($autosuggestType);
        if ($autosuggestType === SOLR_AUTOSUGGEST_SUGGESTER) {
            return $this->_getSuggesterAutosuggestions($url, $userInput, $fieldName);
        }
        return $this->_getFacetingAutosuggestions($url, $searchRequest, $userInput, $fieldName);
    }

    /**
     * Retrieve "interesting terms" from a document.
     * @param int $articleId
     * @return array|null
     */
    public function getInterestingTerms($articleId) {
        $url = $this->_getInterestingTermsUrl();
        $params = [
            'q' => $this->_instId . '-' . $articleId,
            'mlt.fl' => $this->_expandFieldList(['title', 'abstract'])
        ];
        $response = $this->_makeRequest($url, $params);
        if (!($response instanceof DOMXPath)) {
            return null;
        }

        $nodeList = $response->query('/response/result[@name="response"]/@numFound');
        if ($nodeList->length !== 1) {
            return [];
        }
        
        // [LUMERA FIX] Critical bug fix: was assignment (=), now strict comparison (===)
        $numFound = (int) $nodeList->item(0)->textContent;
        if ($numFound === 0) {
            return [];
        }

        $terms = [];
        $nodeList = $response->query('/response/arr[@name="interestingTerms"]/str');
        foreach ($nodeList as $node) {
            $term = $node->textContent;
            if (substr($term, 0, 3) === '#1;') {
                continue;
            }
            $terms[] = $term;
        }
        return $terms;
    }

    /**
     * Returns an array with all (dynamic) fields in the index.
     * @param string $fieldType
     * @return array
     */
    public function getAvailableFields($fieldType) {
        $cache = $this->_getCache();
        return $cache->get($fieldType);
    }

    /**
     * Flush the field cache.
     * @return void
     */
    public function flushFieldCache() {
        $cache = $this->_getCache();
        $cache->flush();
    }

    /**
     * Retrieve a document directly from the index.
     * @param int $articleId
     * @return array|false
     */
    public function getArticleFromIndex($articleId) {
        $url = $this->_getCoreAdminUrl() . 'luke';
        $params = ['id' => $this->_instId . '-' . $articleId];
        $response = $this->_makeRequest($url, $params);
        if (!($response instanceof DOMXPath)) {
            return false;
        }

        $doc = [];
        $nodeList = $response->query('/response/lst[@name="doc"]/doc[@name="solr"]/str');
        foreach ($nodeList as $node) {
            $fieldName = $node->attributes->getNamedItem('name')->value;
            $doc[$fieldName] = $node->textContent;
        }
        return $doc;
    }

    /**
     * Checks the solr server status.
     * @return int
     */
    public function getServerStatus() {
        $url = $this->_getAdminUrl() . 'cores';
        $params = [
            'action' => 'STATUS',
            'core' => $this->_solrCore
        ];
        $response = $this->_makeRequest($url, $params);

        if ($response === null) {
            return SOLR_STATUS_OFFLINE;
        }

        assert($response instanceof DOMXPath);
        $nodeList = $response->query('/response/lst[@name="status"]/lst[@name="ojs"]/lst[@name="index"]/int[@name="numDocs"]');

        if ($nodeList->length !== 1) {
            $this->_serviceMessage = __('plugins.generic.lucene.message.coreNotFound', ['core' => $this->_solrCore]);
            return SOLR_STATUS_OFFLINE;
        }

        $this->_serviceMessage = __('plugins.generic.lucene.message.indexOnline', ['numDocs' => $nodeList->item(0)->textContent]);
        return SOLR_STATUS_ONLINE;
    }

	//
	// Field cache implementation
	//
    /**
     * Refresh the cache from the solr server.
     * @param FileCache $cache
     * @param string $id
     * @return array|false
     */
    public function _cacheMiss($cache, $id) {
        assert(in_array($id, ['search', 'sort'], true));

        $fields = $this->_getFieldNames('all');
        $fieldCache = [];
        foreach (['search', 'sort'] as $fieldType) {
            $fieldCache[$fieldType] = [];
            foreach (['localized', 'multiformat', 'static'] as $fieldSubType) {
                if ($fieldSubType === 'static') {
                    foreach ($fields[$fieldType][$fieldSubType] as $fieldName => $dummy) {
                        $fieldCache[$fieldType][$fieldName] = [];
                    }
                } else {
                    foreach ($fields[$fieldType][$fieldSubType] as $fieldName) {
                        $fieldCache[$fieldType][$fieldName] = [];
                    }
                }
            }
        }

        $url = $this->_getCoreAdminUrl() . 'luke';
        $response = $this->_makeRequest($url);
        if (!($response instanceof DOMXPath)) {
            return false;
        }

        $nodeList = $response->query('/response/lst[@name="fields"]/lst/@name');
        foreach ($nodeList as $node) {
            $fieldName = $node->textContent;
            $fieldNameParts = explode('_', $fieldName);
            $fieldSuffix = array_pop($fieldNameParts);
            
            if (in_array($fieldSuffix, ['spell', 'facet'], true)) {
                continue;
            }
            
            if (strpos($fieldSuffix, 'sort') !== false) {
                $fieldType = 'sort';
                $fieldSuffix = array_pop($fieldNameParts);
            } else {
                $fieldType = 'search';
            }

            foreach ($fields[$fieldType]['static'] as $staticField => $fullFieldName) {
                if ($fieldName === $fullFieldName) {
                    $fieldCache[$fieldType][$staticField][] = $fullFieldName;
                    continue 2;
                }
            }

            $locale = $fieldSuffix;
            if ($locale !== 'txt') {
                $locale = array_pop($fieldNameParts) . '_' . $locale;
            }

            foreach ($fields[$fieldType]['localized'] as $localizedField) {
                if (strpos($fieldName, $localizedField) === 0) {
                    $fieldCache[$fieldType][$localizedField][] = $locale;
                }
            }

            foreach ($fields[$fieldType]['multiformat'] as $multiformatField) {
                if (strpos($fieldName, $multiformatField) === 0) {
                    $format = array_pop($fieldNameParts);
                    if (!isset($fieldCache[$fieldType][$multiformatField][$format])) {
                        $fieldCache[$fieldType][$multiformatField][$format] = [];
                    }
                    $fieldCache[$fieldType][$multiformatField][$format][] = $locale;
                    continue 2;
                }
            }
        }

        $cache->setEntireCache($fieldCache);
        return $fieldCache[$id];
    }

    /**
     * Get the field cache.
     * @return FileCache
     */
    private function _getCache() {
        if (!isset($this->_fieldCache)) {
            $cacheManager = CacheManager::getManager();
            $this->_fieldCache = $cacheManager->getFileCache(
                'plugins-lucene', 'fieldCache',
                [$this, '_cacheMiss']
            );

            $cacheTime = $this->_fieldCache->getCacheTime();
            if (!is_null($cacheTime) && $cacheTime < (time() - 24 * 60 * 60)) {
                $this->_fieldCache->flush();
            }
        }
        return $this->_fieldCache;
    }

	//
	// Private helper methods
	//
    /**
     * Returns the solr update endpoint.
     * @return string
     */
    private function _getUpdateUrl() {
        return $this->_solrServer . $this->_solrCore . '/update';
    }

    /**
     * Returns the solr DIH endpoint.
     * @return string
     */
    private function _getDihUrl() {
        return $this->_solrServer . $this->_solrCore . '/dih';
    }

    /**
     * Returns the solr search endpoint.
     * @return string
     */
    private function _getSearchUrl() {
        return $this->_solrServer . $this->_solrCore . '/' . $this->_solrSearchHandler;
    }

    /**
     * Returns the solr auto-suggestion endpoint.
     * @param int $autosuggestType
     * @return string|null
     */
    private function _getAutosuggestUrl($autosuggestType) {
        $autosuggestUrl = $this->_solrServer . $this->_solrCore;
        switch ($autosuggestType) {
            case SOLR_AUTOSUGGEST_SUGGESTER:
                return $autosuggestUrl . '/dictBasedSuggest';
            case SOLR_AUTOSUGGEST_FACETING:
                return $autosuggestUrl . '/facetBasedSuggest';
            default:
                assert(false);
                return null;
        }
    }

    /**
     * Returns the solr endpoint to retrieve "interesting terms".
     * @return string
     */
    private function _getInterestingTermsUrl() {
        return $this->_solrServer . $this->_solrCore . '/simdocs';
    }

    /**
     * Identifies the general solr admin endpoint.
     * @return string
     */
    private function _getAdminUrl() {
        return $this->_solrServer . 'admin/';
    }

    /**
     * Identifies the solr core-specific admin endpoint.
     * @return string
     */
    private function _getCoreAdminUrl() {
        return $this->_solrServer . $this->_solrCore . '/admin/';
    }

    /**
     * Make a request.
     * @param string $url
     * @param mixed $params
     * @param string $method
     * @return DOMXPath|null
     */
    private function _makeRequest($url, $params = [], $method = 'GET') {
        $webServiceRequest = new WebServiceRequest($url, $params, $method, $this->_useProxySettings);
        if ($method === 'POST') {
            $webServiceRequest->setHeader('Content-Type', 'text/xml; charset=utf-8');
        }
        $this->setReturnType(XSL_TRANSFORMER_DOCTYPE_DOM);
        $response = $this->call($webServiceRequest);

        if (!$response) {
            $this->_serviceMessage = __('plugins.generic.lucene.message.searchServiceOffline');
            return null;
        }

        $status = $this->getLastResponseStatus();
        if ($status !== WEBSERVICE_RESPONSE_OK) {
            $application = PKPApplication::getApplication();
            error_log($application->getName() . ' - Lucene plugin:' . "\nThe Lucene web service returned a status code $status and the message\n" . $response->saveXML());
            $this->_serviceMessage = __('plugins.generic.lucene.message.webServiceError');
            return null;
        }

        assert($response instanceof DOMDocument);
        return new DOMXPath($response);
    }

    /**
     * Return a list of all text fields that may occur in the index.
     * @param string $fieldType
     * @return array
     */
    private function _getFieldNames($fieldType) {
        $fieldNames = [
            'search' => [
                'localized' => ['title', 'abstract', 'discipline', 'subject', 'type', 'coverage', 'suppFiles'],
                'multiformat' => ['galleyFullText'],
                'static' => ['authors' => 'authors_txt', 'publicationDate' => 'publicationDate_dt']
            ],
            'sort' => [
                'localized' => ['title', 'journalTitle'],
                'multiformat' => [],
                'static' => ['authors' => 'authors_txtsort', 'publicationDate' => 'publicationDate_dtsort', 'issuePublicationDate' => 'issuePublicationDate_dtsort']
            ],
            'facet' => [
                'localized' => ['discipline', 'subject', 'type', 'coverage', 'journalTitle'],
                'multiformat' => [],
                'static' => ['authors' => 'authors_facet']
            ]
        ];
        
        if ($fieldType === 'all') {
            return $fieldNames;
        }
        assert(isset($fieldNames[$fieldType]));
        return $fieldNames[$fieldType];
    }

    /**
     * Identify all format/locale versions of the given field.
     * @param string $field
     * @return array
     */
    private function _getLocalesAndFormats($field) {
        $availableFields = $this->getAvailableFields('search');
        $fieldNames = $this->_getFieldNames('search');
        $indexFields = [];

        if (isset($availableFields[$field])) {
            if (in_array($field, $fieldNames['multiformat'], true)) {
                foreach ($availableFields[$field] as $format => $locales) {
                    foreach ($locales as $locale) {
                        $indexFields[] = $field . '_' . $format . '_' . $locale;
                    }
                }
            } elseif (in_array($field, $fieldNames['localized'], true)) {
                foreach ($availableFields[$field] as $locale) {
                    $indexFields[] = $field . '_' . $locale;
                }
            } else {
                assert(isset($fieldNames['static'][$field]));
                $indexFields[] = $fieldNames['static'][$field];
            }
        }
        return $indexFields;
    }

    /**
     * Expand the given list of fields.
     * @param array $fields
     * @return string
     */
    private function _expandFieldList($fields) {
        $expandedFields = [];
        foreach ($fields as $field) {
            $expandedFields = array_merge($expandedFields, $this->_getLocalesAndFormats($field));
        }
        return implode(' ', $expandedFields);
    }

    /**
     * Generate the ordering parameter of a search query.
     * @param string $field
     * @param bool $direction
     * @return string
     */
    private function _getOrdering($field, $direction) {
        $dirString = $direction ? ' asc' : ' desc';
        if ($field === 'score') {
            return $field . $dirString;
        }

        $defaultSort = 'score desc';
        $availableFields = $this->getAvailableFields('sort');
        if (!isset($availableFields[$field])) {
            return $defaultSort;
        }

        $fieldNames = $this->_getFieldNames('sort');
        if (isset($fieldNames['static'][$field])) {
            return $fieldNames['static'][$field] . $dirString . ',' . $defaultSort;
        }

        if (in_array($field, $fieldNames['localized'], true)) {
            $currentLocale = AppLocale::getLocale();
            if (in_array($currentLocale, $availableFields[$field], true)) {
                return $field . '_' . $currentLocale . '_txtsort' . $dirString . ',' . $defaultSort;
            }
        }
        return $defaultSort;
    }

    /**
     * Encapsulates an indexing transaction.
     * @param callable $sendXmlCallback
     * @param int $batchSize
     * @param int|null $journalId
     * @return int|null
     */
    private function _indexingTransaction($sendXmlCallback, $batchSize = SOLR_INDEXING_MAX_BATCHSIZE, $journalId = null) {
        import('lib.pkp.classes.db.DBResultRange');
        $range = new DBResultRange($batchSize);
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $changedArticlesIterator = $articleDao->getBySetting('indexingState', SOLR_INDEXINGSTATE_DIRTY, $journalId, $range);
        unset($range);

        $changedArticles = $changedArticlesIterator->toArray();
        $batchCount = count($changedArticles);
        $totalCount = $changedArticlesIterator->getCount();
        unset($changedArticlesIterator);

        $numDeleted = null;
        $articleXml = $this->_getArticleListXml($changedArticles, $totalCount, $numDeleted);
        $numProcessed = call_user_func_array($sendXmlCallback, [$articleXml, $batchCount, $numDeleted]);

        if (!is_numeric($numProcessed)) {
            return null;
        }
        $numProcessed = (int) $numProcessed;
        
        if ($numProcessed !== $batchCount) {
            $this->_serviceMessage = __('plugins.generic.lucene.message.indexingIncomplete', [
                'numProcessed' => $numProcessed, 
                'numDeleted' => $numDeleted, 
                'batchCount' => $batchCount
            ]);
            return null;
        }

        foreach ($changedArticles as $indexedArticle) {
            $indexedArticle->setData('indexingState', SOLR_INDEXINGSTATE_CLEAN);
            $articleDao->updateLocaleFields($indexedArticle);
        }

        return $numProcessed;
    }

    /**
     * Handle push indexing.
     * @param string $articleXml
     * @param int $batchCount
     * @param int $numDeleted
     * @return int|null
     */
    private function _pushIndexingCallback($articleXml, $batchCount, $numDeleted) {
        if ($batchCount > 0) {
            $url = $this->_getDihUrl() . '?command=full-import&clean=false';
            $result = $this->_makeRequest($url, $articleXml, 'POST');
            if ($result === null) {
                return null;
            }
            return $this->_getDocumentsProcessed($result);
        }
        return 0;
    }

    /**
     * Retrieve the XML for a batch of articles to be updated.
     * @param array $articles
     * @param int $totalCount
     * @param int|null $numDeleted
     * @return string
     */
    private function _getArticleListXml($articles, $totalCount, &$numDeleted) {
        $articleDoc = XMLCustomWriter::createDocument();
        assert($articleDoc instanceof DOMDocument);

        $articleList = XMLCustomWriter::createElement($articleDoc, 'articleList');
        XMLCustomWriter::appendChild($articleDoc, $articleList);

        $numDeleted = 0;
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        
        foreach ($articles as $article) {
            if (!($article instanceof PublishedArticle)) {
                $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($article->getId());
                if ($publishedArticle instanceof PublishedArticle) {
                    $article = $publishedArticle;
                }
            }
            $journal = $this->_getJournal($article->getJournalId());

            if ($this->_isArticleAccessAuthorized($article)) {
                $this->_addArticleXml($articleDoc, $article, $journal);
            } else {
                $numDeleted++;
                $this->_addArticleXml($articleDoc, $article, $journal, true);
            }
        }

        $hasMore = (count($articles) < $totalCount ? 'yes' : 'no');
        $articleDoc->documentElement->setAttribute('hasMore', $hasMore);

        return XMLCustomWriter::getXml($articleDoc);
    }

    /**
     * Add the metadata XML of a single article to an XML article list.
     * @param DOMDocument $articleDoc
     * @param Article $article
     * @param Journal $journal
     * @param bool $markToDelete
     * @return void
     */
    private function _addArticleXml($articleDoc, $article, $journal, $markToDelete = false) {
        assert($article instanceof Article);
        assert($articleDoc instanceof DOMDocument);
        $articleList = $articleDoc->documentElement;
        $articleNode = XMLCustomWriter::createElement($articleDoc, 'article');

        XMLCustomWriter::setAttribute($articleNode, 'id', $article->getId());
        XMLCustomWriter::setAttribute($articleNode, 'sectionId', $article->getSectionId());
        XMLCustomWriter::setAttribute($articleNode, 'journalId', $article->getJournalId());
        XMLCustomWriter::setAttribute($articleNode, 'instId', $this->_instId);
        XMLCustomWriter::setAttribute($articleNode, 'loadAction', $markToDelete ? 'delete' : 'replace');
        XMLCustomWriter::appendChild($articleList, $articleNode);

        if ($markToDelete) {
            return;
        }
        assert($article instanceof PublishedArticle);

        $authors = $article->getAuthors();
        if (!empty($authors)) {
            $authorList = XMLCustomWriter::createElement($articleDoc, 'authorList');
            foreach ($authors as $author) {
                XMLCustomWriter::createChildWithText($articleDoc, $authorList, 'author', $author->getFullName(true));
            }
            XMLCustomWriter::appendChild($articleNode, $authorList);
        }

        $request = PKPApplication::getRequest();
        $site = $request->getSite();
        $supportedLocales = array_unique(array_merge($site->getSupportedLocales(), array_keys($journal->getSupportedLocaleNames())));
        assert(!empty($supportedLocales));

        $titleList = XMLCustomWriter::createElement($articleDoc, 'titleList');
        foreach ($supportedLocales as $locale) {
            $localizedTitle = $article->getLocalizedTitle($locale);
            if (!is_null($localizedTitle)) {
                $titleNode = XMLCustomWriter::createChildWithText($articleDoc, $titleList, 'title', $localizedTitle);
                XMLCustomWriter::setAttribute($titleNode, 'locale', $locale);
                $title = $article->getTitle($locale);
                XMLCustomWriter::setAttribute($titleNode, 'sortOnly', empty($title) ? 'true' : 'false');
            }
        }
        XMLCustomWriter::appendChild($articleNode, $titleList);

        // [LUMERA FIX] Ensure iterable before foreach to prevent "Expected type 'iterable|object'. Found 'string'"
        $abstracts = $article->getAbstract(null);
        if (is_array($abstracts) && !empty($abstracts)) {
            $abstractList = XMLCustomWriter::createElement($articleDoc, 'abstractList');
            foreach ($abstracts as $locale => $abstract) {
                $abstractNode = XMLCustomWriter::createChildWithText($articleDoc, $abstractList, 'abstract', $abstract);
                XMLCustomWriter::setAttribute($abstractNode, 'locale', $locale);
            }
            XMLCustomWriter::appendChild($articleNode, $abstractList);
        }

        $disciplines = $article->getDiscipline(null);
        if (is_array($disciplines) && !empty($disciplines)) {
            $disciplineList = XMLCustomWriter::createElement($articleDoc, 'disciplineList');
            foreach ($disciplines as $locale => $discipline) {
                $disciplineNode = XMLCustomWriter::createChildWithText($articleDoc, $disciplineList, 'discipline', $discipline);
                XMLCustomWriter::setAttribute($disciplineNode, 'locale', $locale);
            }
            XMLCustomWriter::appendChild($articleNode, $disciplineList);
        }

        $subjectClasses = $article->getSubjectClass(null);
        $subjects = $article->getSubject(null);
        if (!empty($subjectClasses) || !empty($subjects)) {
            $subjectList = XMLCustomWriter::createElement($articleDoc, 'subjectList');
            if (!is_array($subjectClasses)) $subjectClasses = [];
            if (!is_array($subjects)) $subjects = [];
            $locales = array_unique(array_merge(array_keys($subjectClasses), array_keys($subjects)));
            foreach ($locales as $locale) {
                $subject = '';
                if (isset($subjectClasses[$locale])) $subject .= $subjectClasses[$locale];
                if (isset($subjects[$locale])) {
                    if (!empty($subject)) $subject .= ' ';
                    $subject .= $subjects[$locale];
                }
                $subjectNode = XMLCustomWriter::createChildWithText($articleDoc, $subjectList, 'subject', $subject);
                XMLCustomWriter::setAttribute($subjectNode, 'locale', $locale);
            }
            XMLCustomWriter::appendChild($articleNode, $subjectList);
        }

        $types = $article->getType(null);
        if (is_array($types) && !empty($types)) {
            $typeList = XMLCustomWriter::createElement($articleDoc, 'typeList');
            foreach ($types as $locale => $type) {
                $typeNode = XMLCustomWriter::createChildWithText($articleDoc, $typeList, 'type', $type);
                XMLCustomWriter::setAttribute($typeNode, 'locale', $locale);
            }
            XMLCustomWriter::appendChild($articleNode, $typeList);
        }

        $coverageGeo = $article->getCoverageGeo(null);
        $coverageChron = $article->getCoverageChron(null);
        $coverageSample = $article->getCoverageSample(null);
        if (!empty($coverageGeo) || !empty($coverageChron) || !empty($coverageSample)) {
            $coverageList = XMLCustomWriter::createElement($articleDoc, 'coverageList');
            if (!is_array($coverageGeo)) $coverageGeo = [];
            if (!is_array($coverageChron)) $coverageChron = [];
            if (!is_array($coverageSample)) $coverageSample = [];
            $locales = array_unique(array_merge(array_keys($coverageGeo), array_keys($coverageChron), array_keys($coverageSample)));
            foreach ($locales as $locale) {
                $coverage = '';
                if (isset($coverageGeo[$locale])) $coverage .= $coverageGeo[$locale];
                if (isset($coverageChron[$locale])) {
                    if (!empty($coverage)) $coverage .= '; ';
                    $coverage .= $coverageChron[$locale];
                }
                if (isset($coverageSample[$locale])) {
                    if (!empty($coverage)) $coverage .= '; ';
                    $coverage .= $coverageSample[$locale];
                }
                $coverageNode = XMLCustomWriter::createChildWithText($articleDoc, $coverageList, 'coverage', $coverage);
                XMLCustomWriter::setAttribute($coverageNode, 'locale', $locale);
            }
            XMLCustomWriter::appendChild($articleNode, $coverageList);
        }

        $journalTitleList = XMLCustomWriter::createElement($articleDoc, 'journalTitleList');
        foreach ($supportedLocales as $locale) {
            $localizedTitle = $journal->getLocalizedTitle($locale);
            if (!is_null($localizedTitle)) {
                $journalTitleNode = XMLCustomWriter::createChildWithText($articleDoc, $journalTitleList, 'journalTitle', $localizedTitle);
                XMLCustomWriter::setAttribute($journalTitleNode, 'locale', $locale);
                $journalTitle = $journal->getTitle($locale);
                XMLCustomWriter::setAttribute($journalTitleNode, 'sortOnly', empty($journalTitle) ? 'true' : 'false');
            }
        }
        XMLCustomWriter::appendChild($articleNode, $journalTitleList);

        $publicationDate = $article->getDatePublished();
        if (!empty($publicationDate)) {
            XMLCustomWriter::createChildWithText($articleDoc, $articleNode, 'publicationDate', $this->_convertDate($publicationDate));
        }

        $issueId = $article->getIssueId();
        if (is_numeric($issueId)) {
            /** @var IssueDAO $issueDao */
            $issueDao = DAORegistry::getDAO('IssueDAO');
            $issue = $issueDao->getIssueById($issueId);
            if ($issue instanceof Issue) {
                $issuePublicationDate = $issue->getDatePublished();
                if (!empty($issuePublicationDate)) {
                    XMLCustomWriter::createChildWithText($articleDoc, $articleNode, 'issuePublicationDate', $this->_convertDate($issuePublicationDate));
                }
            }
        }

        $router = $request->getRouter();
        /** @var ArticleGalleyDAO $fileDao */
        $fileDao = DAORegistry::getDAO('ArticleGalleyDAO');
        $galleys = $fileDao->getGalleysByArticle($article->getId());
        $galleyList = null;
        foreach ($galleys as $galley) {
            $locale = $galley->getLocale();
            $galleyUrl = $router->url($request, $journal->getPath(), 'article', 'download', [(int) $article->getId(), (int) $galley->getId()]);
            if (!empty($locale) && !empty($galleyUrl)) {
                if ($galleyList === null) {
                    $galleyList = XMLCustomWriter::createElement($articleDoc, 'galleyList');
                }
                $galleyNode = XMLCustomWriter::createElement($articleDoc, 'galley');
                XMLCustomWriter::setAttribute($galleyNode, 'locale', $locale);
                XMLCustomWriter::setAttribute($galleyNode, 'fileName', $galleyUrl);
                XMLCustomWriter::appendChild($galleyList, $galleyNode);
            }
        }

        if ($galleyList !== null) {
            $galleyXml = is_callable([$articleDoc, 'saveXml']) ? $articleDoc->saveXml($galleyList) : $galleyList->toXml();
            $galleyOuterNode = XMLCustomWriter::createElement($articleDoc, 'galley-xml');
            $cdataNode = is_callable([$articleDoc, 'createCDATASection']) ? $articleDoc->createCDATASection($galleyXml) : new XMLNode('<![CDATA[' . $galleyXml . ']]>');
            XMLCustomWriter::appendChild($galleyOuterNode, $cdataNode);
            XMLCustomWriter::appendChild($articleNode, $galleyOuterNode);
        }

        /** @var SuppFileDAO $suppFileDao */
        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $suppFiles = $suppFileDao->getSuppFilesByArticle($article->getId());
        $suppFileList = null;
        foreach ($suppFiles as $suppFile) {
            $locale = 'unknown';
            $language = $suppFile->getLanguage();
            if (strlen($language) === 2) {
                $language = AppLocale::get3LetterFrom2LetterIsoLanguage($language);
            }
            if (strlen($language) === 3) {
                $locale = AppLocale::getLocaleFrom3LetterIso($language);
            }
            if (!AppLocale::isLocaleValid($locale)) {
                $locale = 'unknown';
            }

            $suppFileUrl = $router->url($request, $journal->getPath(), 'article', 'downloadSuppFile', [(int) $article->getId(), (int) $suppFile->getId()]);

            if (!empty($locale) && !empty($suppFileUrl)) {
                if ($suppFileList === null) {
                    $suppFileList = XMLCustomWriter::createElement($articleDoc, 'suppFileList');
                }
                $suppFileNode = XMLCustomWriter::createElement($articleDoc, 'suppFile');
                XMLCustomWriter::setAttribute($suppFileNode, 'locale', $locale);
                XMLCustomWriter::setAttribute($suppFileNode, 'fileName', $suppFileUrl);
                XMLCustomWriter::appendChild($suppFileList, $suppFileNode);

                $suppFileMetadata = [
                    'title' => $suppFile->getTitle(null),
                    'creator' => $suppFile->getCreator(null),
                    'subject' => $suppFile->getSubject(null),
                    'typeOther' => $suppFile->getTypeOther(null),
                    'description' => $suppFile->getDescription(null),
                    'source' => $suppFile->getSource(null)
                ];
                foreach ($suppFileMetadata as $field => $data) {
                    if (!empty($data) && is_array($data)) {
                        $suppFileMDListNode = XMLCustomWriter::createElement($articleDoc, $field . 'List');
                        foreach ($data as $dataLocale => $value) {
                            $suppFileMDNode = XMLCustomWriter::createChildWithText($articleDoc, $suppFileMDListNode, $field, $value);
                            XMLCustomWriter::setAttribute($suppFileMDNode, 'locale', $dataLocale);
                        }
                        XMLCustomWriter::appendChild($suppFileNode, $suppFileMDListNode);
                    }
                }
            }
        }

        if ($suppFileList !== null) {
            $suppFileXml = is_callable([$articleDoc, 'saveXml']) ? $articleDoc->saveXml($suppFileList) : $suppFileList->toXml();
            $suppFileOuterNode = XMLCustomWriter::createElement($articleDoc, 'suppFile-xml');
            $cdataNode = is_callable([$articleDoc, 'createCDATASection']) ? $articleDoc->createCDATASection($suppFileXml) : new XMLNode('<![CDATA[' . $suppFileXml . ']]>');
            XMLCustomWriter::appendChild($suppFileOuterNode, $cdataNode);
            XMLCustomWriter::appendChild($articleNode, $suppFileOuterNode);
        }
    }

    /**
     * Convert a date to UTC time as understood by solr.
     * @param int|string $timestamp
     * @return string
     */
    private function _convertDate($timestamp) {
        if (is_numeric($timestamp)) {
            $timestamp = (int) $timestamp;
        } else {
            $timestamp = strtotime($timestamp);
        }
        return gmdate('Y-m-d\TH:i:s\Z', $timestamp);
    }

    /**
     * Delete documents from the index.
     * @param string $xml
     * @return bool
     */
    private function _deleteFromIndex($xml) {
        $xml = '<delete>' . $xml . '</delete>';
        $url = $this->_getUpdateUrl() . '?commit=true';
        $result = $this->_makeRequest($url, $xml, 'POST');
        if ($result === null) {
            return false;
        }

        $nodeList = $result->query('/response/lst[@name="responseHeader"]/int[@name="status"]');
        if ($nodeList->length !== 1) {
            return false;
        }
        $resultNode = $nodeList->item(0);
        return $resultNode->textContent === '0';
    }

    /**
     * Retrieve the number of indexed documents from a DIH response XML.
     * @param DOMXPath $result
     * @return int
     */
    private function _getDocumentsProcessed($result) {
        $nodeList = $result->query('/response/lst[@name="statusMessages"]/str[@name="Total Documents Processed"]');
        assert($nodeList->length === 1);
        $resultNode = $nodeList->item(0);
        assert(is_numeric($resultNode->textContent));
        return (int) $resultNode->textContent;
    }

    /**
     * Set the query parameters for a search query.
     * @param string $fieldList
     * @param string $searchPhrase
     * @param bool $spellcheck
     * @return array
     */
    private function _setQuery($fieldList, $searchPhrase, $spellcheck = false) {
        $fieldList = $this->_expandFieldList(explode('|', $fieldList));
        $params = [
            'defType' => 'edismax',
            'qf' => $fieldList,
            'mm' => '1'
        ];

        if (!empty($searchPhrase)) {
            $params['q'] = $searchPhrase;
        }
        if ($spellcheck) {
            $params['spellcheck'] = 'on';
        }
        return $params;
    }

    /**
     * Add a subquery to the search query.
     * @param string $fieldList
     * @param string $searchPhrase
     * @param array $params
     * @return array
     */
    private function _addSubquery($fieldList, $searchPhrase, $params) {
        $fields = explode('|', $fieldList);
        $fieldList = $this->_expandFieldList($fields);

        $fieldAlias = count($fields) === 1 ? array_pop($fields) : 'multi';
        $fieldAlias = "q.$fieldAlias";

        $fieldSuffix = '';
        while (isset($params[$fieldAlias . $fieldSuffix])) {
            $fieldSuffix = empty($fieldSuffix) ? 1 : $fieldSuffix + 1;
        }
        $fieldAlias = $fieldAlias . $fieldSuffix;

        $subQuery = "+_query_:\"{!edismax mm=1 qf='$fieldList' v=\$fieldAlias}\"";
        $params['q'] = (isset($params['q']) ? $params['q'] . ' ' : '') . $subQuery;
        $params[$fieldAlias] = $searchPhrase;
        return $params;
    }

    /**
     * Translate query keywords.
     * @param string $searchPhrase
     * @param bool $backwards
     * @return string
     */
    private function _translateSearchPhrase($searchPhrase, $backwards = false) {
        static $queryKeywords;
        if ($queryKeywords === null) {
            $queryKeywords = [
                PKPString::strtoupper(__('search.operator.not')) => 'NOT',
                PKPString::strtoupper(__('search.operator.and')) => 'AND',
                PKPString::strtoupper(__('search.operator.or')) => 'OR'
            ];
        }

        $translationTable = $backwards ? array_flip($queryKeywords) : $queryKeywords;
        foreach ($translationTable as $translateFrom => $translateTo) {
            $searchPhrase = PKPString::regexp_replace("/(^|\s)$translateFrom(\s|$)/i", "\\1$translateTo\\2", $searchPhrase);
        }
        return $searchPhrase;
    }

    /**
     * Create the edismax query parameters from a search request.
     * @param SolrSearchRequest $searchRequest
     * @return array|null
     */
    private function _getSearchQueryParameters($searchRequest) {
        $subQueries = [];
        foreach ($searchRequest->getQuery() as $fieldList => $searchPhrase) {
            if (empty($fieldList) || empty($searchPhrase)) {
                continue;
            }
            $subQueries[$fieldList] = $this->_translateSearchPhrase($searchPhrase);
        }

        $subQueryCount = count($subQueries);
        if ($subQueryCount === 1) {
            $fieldList = key($subQueries);
            $searchPhrase = current($subQueries);
            $params = $this->_setQuery($fieldList, $searchPhrase, $searchRequest->getSpellcheck());
        } elseif ($subQueryCount > 1) {
            $params = [];
            foreach ($subQueries as $fieldList => $searchPhrase) {
                $params = $this->_addSubquery($fieldList, $searchPhrase, $params);
            }
        } else {
            return null;
        }

        $params['fq'] = ['inst_id:"' . $this->_instId . '"'];

        $fromDate = $searchRequest->getFromDate();
        $toDate = $searchRequest->getToDate();
        if (!(empty($fromDate) && empty($toDate))) {
            $fromDate = empty($fromDate) ? '*' : $this->_convertDate($fromDate);
            $toDate = empty($toDate) ? '*' : $this->_convertDate($toDate);
            $params['fq'][] = "{!cache=false}publicationDate_dt:[$fromDate TO $toDate]";
        }

        $journal = $searchRequest->getJournal();
        if ($journal instanceof Journal) {
            $params['fq'][] = 'journal_id:"' . $this->_instId . '-' . $journal->getId() . '"';
        }
        return $params;
    }

    /**
     * Retrieve auto-suggestions from the suggester service.
     * @param string $url
     * @param string $userInput
     * @param string $fieldName
     * @return array
     */
    private function _getSuggesterAutosuggestions($url, $userInput, $fieldName) {
        $dictionary = $fieldName === 'query' ? 'all' : $fieldName;
        $params = [
            'q' => $userInput,
            'spellcheck.dictionary' => $dictionary
        ];

        $response = $this->_makeRequest($url, $params);
        if (!($response instanceof DOMXPath)) {
            return [];
        }

        $nodeList = $response->query('//lst[@name="suggestions"]/lst[last()]');
        if ($nodeList->length === 0) {
            return [];
        }
        
        $suggestionNode = $nodeList->item(0);
        $suggestions = []; // [LUMERA FIX] Initialize to prevent undefined variable warning
        $startOffset = 0;
        $endOffset = 0;

        foreach ($suggestionNode->childNodes as $childNode) {
            $nodeType = $childNode->attributes->getNamedItem('name')->value;
            switch ($nodeType) {
                case 'startOffset':
                    $startOffset = (int) $childNode->textContent;
                    break;
                case 'endOffset':
                    $endOffset = (int) $childNode->textContent;
                    break;
                case 'suggestion':
                    foreach ($childNode->childNodes as $suggestionChildNode) {
                        $suggestions[] = $suggestionChildNode->textContent;
                    }
                    break;
            }
        }

        if (!(isset($startOffset) && isset($endOffset) && PKPString::strlen($userInput) === $endOffset)) {
            return [];
        }

        foreach ($suggestions as &$suggestion) {
            $suggestion = $userInput . PKPString::substr($suggestion, $endOffset - $startOffset);
        }
        return $suggestions;
    }

    /**
     * Retrieve auto-suggestions from the faceting service.
     * @param string $url
     * @param SolrSearchRequest $searchRequest
     * @param string $userInput
     * @param string $fieldName
     * @return array
     */
    private function _getFacetingAutosuggestions($url, $searchRequest, $userInput, $fieldName) {
        $searchTerms = strtr($userInput, '"()+-|&!', '        ');
        $searchTerms = explode(' ', $searchTerms);
        $facetPrefix = array_pop($searchTerms);
        if (empty($facetPrefix)) {
            return [];
        }

        $userInput = PKPString::substr($userInput, 0, -PKPString::strlen($facetPrefix));
        switch ($fieldName) {
            case 'query':
                $solrFields = array_values(ArticleSearch::getIndexFieldMap());
                break;
            case 'indexTerms':
                $solrFields = ['discipline', 'subject', 'type', 'coverage'];
                break;
            default:
                $solrFields = [$fieldName];
        }
        $solrFieldString = implode('|', $solrFields);
        $searchRequest->addQueryFieldPhrase($solrFieldString, $userInput);

        $params = $this->_getSearchQueryParameters($searchRequest);
        if (!isset($params['q'])) {
            $params['q'] = '*:*';
        }
        $params['facet.field'] = $fieldName === 'query' ? 'default_spell' : $fieldName . '_spell';
        $params['facet.prefix'] = PKPString::strtolower($facetPrefix);

        $response = $this->_makeRequest($url, $params);
        if (!($response instanceof DOMXPath)) {
            return [];
        }

        $nodeList = $response->query('//lst[@name="facet_fields"]/lst/int/@name');
        if ($nodeList->length === 0) {
            return [];
        }
        
        $termSuggestions = [];
        foreach ($nodeList as $childNode) {
            // [LUMERA FIX] Use nodeValue instead of value for DOMAttr to satisfy linter
            $termSuggestions[] = $childNode->nodeValue;
        }

        $suggestions = [];
        $facetPrefixLc = PKPString::strtolower($facetPrefix);
        foreach ($termSuggestions as $termSuggestion) {
            if (strpos($termSuggestion, $facetPrefixLc) === 0) {
                $termSuggestion = $facetPrefix . PKPString::substr($termSuggestion, PKPString::strlen($facetPrefix));
            }
            $suggestions[] = $userInput . $termSuggestion;
        }
        return $suggestions;
    }

    /**
     * Check whether access to the given article is authorized.
     * @param Article $article
     * @return bool
     */
    private function _isArticleAccessAuthorized($article) {
        if (!($article instanceof PublishedArticle)) {
            return false;
        }

        $journal = $this->_getJournal($article->getJournalId());
        if (!($journal instanceof Journal)) {
            return false;
        }

        $issue = $this->_getIssue($article->getIssueId(), $journal->getId());
        if (!($issue instanceof Issue)) {
            return false;
        }

        if (!$issue->getPublished() || $article->getStatus() !== STATUS_PUBLISHED) {
            return false;
        }

        import('classes.issue.IssueAction');
        $subscriptionRequired = IssueAction::subscriptionRequired($issue, $journal);
        if ($subscriptionRequired) {
            $isSubscribedDomain = IssueAction::subscribedDomain($journal, $issue->getId(), $article->getId());
            if (!$isSubscribedDomain) {
                return false;
            }
        }
        return true;
    }
    
}
?>
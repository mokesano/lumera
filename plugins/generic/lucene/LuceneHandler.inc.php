<?php
declare(strict_types=1);

/**
 * @file plugins/generic/lucene/LuceneHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LuceneHandler
 * @ingroup plugins_generic_lucene
 *
 * @brief Handle lucene AJAX and XML requests (auto-completion, pull indexation, etc.)
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

import('classes.handler.Handler');
import('plugins.generic.lucene.classes.SolrWebService');
import('lib.pkp.classes.core.JSONMessage');
import('classes.search.ArticleSearch');

class LuceneHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct($request) {
        parent::__construct();
        $router = $request->getRouter();
        $journal = $router->getContext($request);
        
        // [PHP 8 FIX] Replaced deprecated create_function with anonymous Closure
        $this->addCheck(new HandlerValidatorCustom(
            $this, 
            false, 
            null, 
            null, 
            function($journal) { 
                return !$journal || $journal->getSetting('publishingMode') != PUBLISHING_MODE_NONE; 
            }, 
            [$journal]
        ));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LuceneHandler($request) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::LuceneHandler(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        // Forward calls to __construct
        $this->__construct($request);
    }


    //
    // Public operations
    //
    /**
     * AJAX request for search query auto-completion.
     * @param array $args
     * @param PKPRequest $request
     * @return string JSON
     */
    public function queryAutocomplete($args, $request) {
        $this->validate(null, $request);

        // Check whether auto-suggest is enabled.
        $suggestionList = [];
        $lucenePlugin = $this->_getLucenePlugin();
        $enabled = (bool)$lucenePlugin->getSetting(0, 'autosuggest');
        
        if ($enabled) {
            // Retrieve search criteria from the user input.
            $searchFilters = ArticleSearch::getSearchFilters($request);

            // Get the autosuggest input and remove it from the filter array.
            $searchField = $request->getUserVar('searchField');
            
            // [SECURITY FIX] Whitelisting:
            $allowedFields = ['title', 'abstract', 'fullText'];
            if (!in_array($searchField, $allowedFields)) {
                $searchField = 'title';
            }
            
            // [PHP 8 FIX] Define autosuggestField explicitly to avoid Undefined Variable
            $autosuggestField = $searchField;
            
            // Safe access using Null Coalescing Operator
            $userInput = $searchFilters[$autosuggestField] ?? '';
            
            if (isset($searchFilters[$autosuggestField])) {
                unset($searchFilters[$autosuggestField]);
            }

            // Instantiate a search request.
            $searchRequest = new SolrSearchRequest();
            $searchRequest->setJournal($searchFilters['searchJournal']);
            $searchRequest->setFromDate($searchFilters['fromDate']);
            $searchRequest->setToDate($searchFilters['toDate']);
            $keywords = ArticleSearch::getKeywordsFromSearchFilters($searchFilters);
            $searchRequest->addQueryFromKeywords($keywords);

            // Get the web service.
            $solrWebService = $lucenePlugin->getSolrWebService(); /* @var $solrWebService SolrWebService */
            $suggestions = $solrWebService->getAutosuggestions(
                $searchRequest, $autosuggestField, $userInput,
                (int)$lucenePlugin->getSetting(0, 'autosuggestType')
            );

            // Prepare a suggestion list as understood by the autocomplete JS handler.
            foreach($suggestions as $suggestion) {
                $suggestionList[] = ['label' => $suggestion, 'value' => $suggestion];
            }
        }

        // Return the suggestions as JSON message.
        $json = new JSONMessage(true, $suggestionList);
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * If pull-indexing is enabled then this handler returns
     * article metadata in a formate that can be consumed by
     * the Solr data import handler.
     * @param array $args
     * @param PKPRequest $request
     * @return string JSON
     */
    public function pullChangedArticles($args, $request) {
        $this->validate(null, $request);

        // Do not allow access to this operation from journal context.
        $router = $request->getRouter();
        $journal = $router->getContext($request);
        if (!is_null($journal)) {
            // Redirect to the index context.
            $request->redirect('index', 'lucene', 'pullChangedArticles');
        }

        // Die if pull indexing is disabled.
        $lucenePlugin = $this->_getLucenePlugin();
        if (!$lucenePlugin->getSetting(0, 'pullIndexing')) die(__('plugins.generic.lucene.message.pullIndexingDisabled'));

        // Execute the pull indexing transaction.
        $solrWebService = $lucenePlugin->getSolrWebService(); /* @var $solrWebService SolrWebService */
        $solrWebService->pullChangedArticles(
            [$this, 'pullIndexingCallback'], SOLR_INDEXING_MAX_BATCHSIZE
        );
    }

    /**
     * If the "similar documents" feature is enabled then this
     * handler redirects to a search query that shows documents
     * similar to the one identified by an article id in the
     * request.
     * @param array $args
     * @param PKPRequest $request
     */
    public function similarDocuments($args, $request) {
        $this->validate(null, $request);

        // Retrieve the ID of the article that we want similar documents for.
        $articleId = (int) $request->getUserVar('articleId');
        
        // [SECURITY FIX] Range validation:
        if ($articleId <= 0) {
            $articleId = false;
        }

        // Check error conditions.
        // - The "similar documents" feature is not enabled.
        // - We got a non-numeric article ID.
        $lucenePlugin = $this->_getLucenePlugin();
        if (!($lucenePlugin->getSetting(0, 'simdocs') && is_numeric($articleId))) {
            $request->redirect(null, 'search');
        }

        // Identify "interesting" terms of the given article.
        $solrWebService = $lucenePlugin->getSolrWebService(); /* @var $solrWebService SolrWebService */
        $searchTerms = $solrWebService->getInterestingTerms($articleId);
        if (empty($searchTerms)) {
            $request->redirect(null, 'search');
        }

        // Redirect to a search query with these terms.
        $searchParams = [
            'query' => implode(' ', $searchTerms),
        ];
        $request->redirect(null, 'search', 'search', null, $searchParams);
    }


    //
    // Public methods
    //
    /**
     * Return XML with index changes to the Solr server
     * where it will be stored for later processing.
     *
     * @param string $articleXml The XML with index changes
     * to be transferred to the Solr server.
     * @param int $batchCount The number of articles in
     * the XML list (i.e. the expected number of documents
     * to be indexed).
     * @param int $numDeleted The number of articles in
     * the XML list that are marked for deletion.
     *
     * @return int The number of articles processed.
     */
    public function pullIndexingCallback($articleXml, $batchCount, $numDeleted) {
        // Flush the XML to the Solr server to make sure it
        // arrives there before we commit our transaction.
        echo $articleXml;
        flush();

        // We assume that when the flush succeeds that
        // all changed documents will eventually be indexed.
        return $batchCount;
    }


    //
    // Private helper methods
    //
    /**
     * Get the lucene plugin object
     * @return LucenePlugin
     */
    protected function _getLucenePlugin() {
        $plugin = PluginRegistry::getPlugin('generic', LUCENE_PLUGIN_NAME);
        return $plugin;
    }
}

?>
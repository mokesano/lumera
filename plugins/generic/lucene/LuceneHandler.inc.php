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
 * @brief Handle Lucene AJAX and XML requests.
 */

import('classes.handler.Handler');
import('plugins.generic.lucene.classes.SolrWebService');
import('lib.pkp.classes.core.JSONMessage');
import('classes.search.ArticleSearch');

class LuceneHandler extends Handler {

    /**
     * Constructor.
     * @param PKPRequest $request
     */
    public function __construct($request) {
        parent::__construct();
        $router = $request->getRouter();
        $journal = $router->getContext($request);
        
        $this->addCheck(new HandlerValidatorCustom(
            $this, 
            false, 
            null, 
            null, 
            function($journal) { 
                return !$journal || $journal->getSetting('publishingMode') !== PUBLISHING_MODE_NONE; 
            }, 
            [$journal]
        ));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param PKPRequest $request
     */
    public function LuceneHandler($request) {
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
    // Public operations
    //

    /**
     * AJAX request for search query auto-completion.
     * @param array $args
     * @param PKPRequest $request
     * @return string
     */
    public function queryAutocomplete($args, $request) {
        $this->validate(null, $request);

        $suggestionList = [];
        $lucenePlugin = $this->_getLucenePlugin();
        $enabled = (bool) $lucenePlugin->getSetting(0, 'autosuggest');
        
        if ($enabled) {
            $searchFilters = ArticleSearch::getSearchFilters($request);
            $searchField = (string) $request->getUserVar('searchField');
            
            $allowedFields = ['title', 'abstract', 'fullText'];
            if (!in_array($searchField, $allowedFields, true)) {
                $searchField = 'title';
            }
            
            $userInput = $searchFilters[$searchField] ?? '';
            if (isset($searchFilters[$searchField])) {
                unset($searchFilters[$searchField]);
            }

            $searchRequest = new SolrSearchRequest();
            $searchRequest->setJournal($searchFilters['searchJournal'] ?? null);
            $searchRequest->setFromDate($searchFilters['fromDate'] ?? null);
            $searchRequest->setToDate($searchFilters['toDate'] ?? null);
            
            $keywords = ArticleSearch::getKeywordsFromSearchFilters($searchFilters);
            $searchRequest->addQueryFromKeywords($keywords);

            /** @var SolrWebService $solrWebService */
            $solrWebService = $lucenePlugin->getSolrWebService();
            $suggestions = $solrWebService->getAutosuggestions(
                $searchRequest, 
                $searchField, 
                $userInput,
                (int) $lucenePlugin->getSetting(0, 'autosuggestType')
            );

            foreach ($suggestions as $suggestion) {
                $suggestionList[] = ['label' => (string) $suggestion, 'value' => (string) $suggestion];
            }
        }

        $json = new JSONMessage(true, $suggestionList);
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Return article metadata for Solr data import handler (pull indexing).
     * @param array $args
     * @param PKPRequest $request
     */
    public function pullChangedArticles($args, $request) {
        $this->validate(null, $request);

        $router = $request->getRouter();
        $journal = $router->getContext($request);
        if ($journal !== null) {
            $request->redirect('index', 'lucene', 'pullChangedArticles');
        }

        $lucenePlugin = $this->_getLucenePlugin();
        if (!$lucenePlugin->getSetting(0, 'pullIndexing')) {
            die(__('plugins.generic.lucene.message.pullIndexingDisabled'));
        }

        /** @var SolrWebService $solrWebService */
        $solrWebService = $lucenePlugin->getSolrWebService();
        $solrWebService->pullChangedArticles(
            [$this, 'pullIndexingCallback'], 
            defined('SOLR_INDEXING_MAX_BATCHSIZE') ? SOLR_INDEXING_MAX_BATCHSIZE : 100
        );
    }

    /**
     * Redirect to a search query showing documents similar to the given article.
     * @param array $args
     * @param PKPRequest $request
     */
    public function similarDocuments($args, $request) {
        $this->validate(null, $request);

        $articleId = (int) $request->getUserVar('articleId');
        $lucenePlugin = $this->_getLucenePlugin();
        if ($articleId <= 0 || !$lucenePlugin->getSetting(0, 'simdocs')) {
            $request->redirect(null, 'search');
        }

        /** @var SolrWebService $solrWebService */
        $solrWebService = $lucenePlugin->getSolrWebService();
        $searchTerms = $solrWebService->getInterestingTerms($articleId);
        
        if (empty($searchTerms)) {
            $request->redirect(null, 'search');
        }

        $searchParams = [
            'query' => implode(' ', $searchTerms),
        ];
        $request->redirect(null, 'search', 'search', null, $searchParams);
    }

    //
    // Public methods
    //

    /**
     * Callback to return XML with index changes to the Solr server.
     * @param string $articleXml
     * @param int $batchCount
     * @param int $numDeleted
     * @return int
     */
    public function pullIndexingCallback($articleXml, $batchCount, $numDeleted) {
        echo $articleXml;
        flush();
        return (int) $batchCount;
    }

    //
    // Private helper methods
    //

    /**
     * Get the Lucene plugin object.
     * @return LucenePlugin|null
     */
    protected function _getLucenePlugin() {
        $plugin = PluginRegistry::getPlugin('generic', defined('LUCENE_PLUGIN_NAME') ? LUCENE_PLUGIN_NAME : 'lucene');
        return $plugin;
    }

}
?>
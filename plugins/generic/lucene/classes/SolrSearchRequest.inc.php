<?php
declare(strict_types=1);

/**
 * @file plugins/generic/lucene/classes/SolrSearchRequest.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SolrSearchRequest
 * @ingroup plugins_generic_lucene_classes
 *
 * @brief A value object containing all parameters of a solr search query.
 */

class SolrSearchRequest {

    /** @var Journal|null */
    protected $_journal = null;

    /** @var array */
    protected $_query = [];

    /** @var int */
    protected $_page = 1;

    /** @var int */
    protected $_itemsPerPage = 25;

    /** @var string|null */
    protected $_fromDate = null;

    /** @var string|null */
    protected $_toDate = null;

    /** @var string */
    protected $_orderBy = 'score';

    /** @var bool */
    protected $_orderDir = false;

    /** @var bool */
    protected $_spellcheck = false;

    /** @var bool */
    protected $_highlighting = false;

    /** @var array */
    protected $_facetCategories = [];

    /** @var array */
    protected $_boostFactors = [];

    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function SolrSearchRequest() {
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
     * Get the journal to be queried.
     * @return Journal|null
     */
    public function getJournal() {
        return $this->_journal;
    }

    /**
     * Set the journal to be queried.
     * @param Journal|null $journal
     */
    public function setJournal($journal) {
        $this->_journal = $journal;
    }

    /**
     * Get fieldwise search phrases.
     * @return array
     */
    public function getQuery() {
        return $this->_query;
    }

    /**
     * Set fieldwise search phrases.
     * @param array $query
     */
    public function setQuery($query) {
        $this->_query = is_array($query) ? $query : [];
    }

    /**
     * Set the search phrase for a field.
     * @param string $field
     * @param string $searchPhrase
     */
    public function addQueryFieldPhrase($field, $searchPhrase) {
        if ($searchPhrase === null || trim((string) $searchPhrase) === '') {
            return;
        }
        $this->_query[(string) $field] = (string) $searchPhrase;
    }

    /**
     * Get the page.
     * @return int
     */
    public function getPage() {
        return $this->_page;
    }

    /**
     * Set the page.
     * @param mixed $page
     */
    public function setPage($page) {
        $page = is_numeric($page) ? (int) $page : 1;
        $this->_page = $page < 0 ? 0 : $page;
    }

    /**
     * Get the items per page.
     * @return int
     */
    public function getItemsPerPage() {
        return $this->_itemsPerPage;
    }

    /**
     * Set the items per page.
     * @param mixed $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage) {
        $this->_itemsPerPage = is_numeric($itemsPerPage) ? (int) $itemsPerPage : 25;
    }

    /**
     * Get the first publication date.
     * @return string|null
     */
    public function getFromDate() {
        return $this->_fromDate;
    }

    /**
     * Set the first publication date.
     * @param string|null $fromDate
     */
    public function setFromDate($fromDate) {
        $this->_fromDate = $fromDate !== null ? (string) $fromDate : null;
    }

    /**
     * Get the last publication date.
     * @return string|null
     */
    public function getToDate() {
        return $this->_toDate;
    }

    /**
     * Set the last publication date.
     * @param string|null $toDate
     */
    public function setToDate($toDate) {
        $this->_toDate = $toDate !== null ? (string) $toDate : null;
    }

    /**
     * Get the result ordering criteria.
     * @return string
     */
    public function getOrderBy() {
        return $this->_orderBy;
    }

    /**
     * Set the result ordering criteria.
     * @param string $orderBy
     */
    public function setOrderBy($orderBy) {
        $this->_orderBy = (string) $orderBy;
    }

    /**
     * Get the result ordering direction.
     * @return bool
     */
    public function getOrderDir() {
        return $this->_orderDir;
    }

    /**
     * Set the result ordering direction.
     * @param bool $orderDir
     */
    public function setOrderDir($orderDir) {
        $this->_orderDir = (bool) $orderDir;
    }

    /**
     * Is spellchecking enabled?
     * @return bool
     */
    public function getSpellcheck() {
        return $this->_spellcheck;
    }

    /**
     * Set whether spellchecking should be enabled.
     * @param bool $spellcheck
     */
    public function setSpellcheck($spellcheck) {
        $this->_spellcheck = (bool) $spellcheck;
    }

    /**
     * Is highlighting enabled?
     * @return bool
     */
    public function getHighlighting() {
        return $this->_highlighting;
    }

    /**
     * Set whether highlighting should be enabled.
     * @param bool $highlighting
     */
    public function setHighlighting($highlighting) {
        $this->_highlighting = (bool) $highlighting;
    }

    /**
     * Get enabled facet categories.
     * @return array
     */
    public function getFacetCategories() {
        return $this->_facetCategories;
    }

    /**
     * Set the categories for which faceting should be enabled.
     * @param array $facetCategories
     */
    public function setFacetCategories($facetCategories) {
        $this->_facetCategories = is_array($facetCategories) ? $facetCategories : [];
    }

    /**
     * Get boost factors.
     * @return array
     */
    public function getBoostFactors() {
        return $this->_boostFactors;
    }

    /**
     * Set boost factors.
     * @param array $boostFactors
     */
    public function setBoostFactors($boostFactors) {
        $this->_boostFactors = is_array($boostFactors) ? $boostFactors : [];
    }

    /**
     * Set the boost factor for a field/value combination.
     * @param string $field
     * @param string $value
     * @param float $boostFactor
     */
    public function addBoostFactor($field, $value, $boostFactor) {
        if ($value === null || trim((string) $value) === '') {
            return;
        }

        $boostFactor = is_numeric($boostFactor) ? (float) $boostFactor : 1.0;
        
        // Safe strict comparison for float constant
        if ($boostFactor === (float) LUCENE_PLUGIN_DEFAULT_RANKING_BOOST) {
            return;
        }

        $field = (string) $field;
        if (!isset($this->_boostFactors[$field])) {
            $this->_boostFactors[$field] = [];
        }
        $this->_boostFactors[$field][(string) $value] = $boostFactor;
    }

    //
    // Public methods
    //

    /**
     * Configure the search request from a keywords array.
     * @param array $keywords
     */
    public function addQueryFromKeywords($keywords) {
        if (!is_array($keywords)) {
            return;
        }

        $indexFieldMap = ArticleSearch::getIndexFieldMap();

        foreach ($keywords as $searchFieldBitmap => $searchPhrase) {
            if (empty($searchFieldBitmap)) {
                $solrFields = array_values($indexFieldMap);
            } else {
                $solrFields = [];
                foreach ($indexFieldMap as $ojsField => $solrField) {
                    if ($searchFieldBitmap & $ojsField) {
                        $solrFields[] = $solrField;
                    }
                }
            }
            
            $solrFieldString = implode('|', $solrFields);
            $this->addQueryFieldPhrase($solrFieldString, $searchPhrase);
        }
    }
    
}
?>
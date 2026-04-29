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
 *
 * @edition Wizdam Edition (PHP 8.x Compatible)
 */

class SolrSearchRequest {

    /**
     * @var Journal The journal to be queried. All journals of
     * an OJS instance will be queried if no journal is given.
     */
    protected $_journal = null;

    /**
     * @var array A field->search phrase assignment defining fieldwise
     * search phrases.
     */
    protected $_query = [];

    /**
     * @var integer For paginated queries: The page to be returned.
     */
    protected $_page = 1;

    /**
     * @var integer For paginated queries: The items per page.
     */
    protected $_itemsPerPage = 25;

    /**
     * @var string Timestamp representing the first publication date to be
     * included in the result set. Null means: No limitation.
     */
    protected $_fromDate = null;

    /**
     * @var string Timestamp representing the last publication date to be
     * included in the result set. Null means: No limitation.
     */
    protected $_toDate = null;

    /**
     * @var string Result set ordering. Can be any index field or the pseudo-
     * field "score" for ordering by relevance.
     */
    protected $_orderBy = 'score';

    /**
     * @var boolean Result set ordering direction. Can be 'true' for ascending
     * or 'false' for descending order.
     */
    protected $_orderDir = false;

    /**
     * @var boolean Whether to enable spell checking.
     */
    protected $_spellcheck = false;

    /**
     * @var boolean Whether to enable highlighting.
     */
    protected $_highlighting = false;

    /**
     * @var boolean Enabled facet categories (none by default).
     */
    protected $_facetCategories = [];

    /**
     * @var array A field->value->boost factor assignment.
     */
    protected $_boostFactors = [];

    /**
     * Constructor
     */
    public function __construct() {
        // The constructor does nothing
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SolrSearchRequest() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::SolrSearchRequest(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }


    //
    // Getters and Setters
    //
    /**
     * Get the journal to be queried.
     * @return Journal
     */
    public function getJournal() {
        return $this->_journal;
    }

    /**
     * Set the journal to be queried
     * @param Journal $journal
     */
    public function setJournal($journal) {
        $this->_journal = $journal;
    }

    /**
     * Get fieldwise search phrases.
     * @return array A field -> search phrase assignment
     */
    public function getQuery() {
        return $this->_query;
    }

    /**
     * Set fieldwise search phrases.
     * @param array $query A field -> search phrase assignment
     */
    public function setQuery($query) {
        $this->_query = $query;
    }

    /**
     * Set the search phrase for a field.
     * @param string $field
     * @param string $searchPhrase
     */
    public function addQueryFieldPhrase($field, $searchPhrase) {
        // Ignore empty search phrases.
        if (empty($searchPhrase)) return;
        $this->_query[$field] = $searchPhrase;
    }

    /**
     * Get the page.
     * @return integer
     */
    public function getPage() {
        return $this->_page;
    }

    /**
     * Set the page
     * @param integer $page
     */
    public function setPage($page) {
        $page = (int) $page;
        if ($page < 0) $page = 0;
        $this->_page = $page;
    }

    /**
     * Get the items per page.
     * @return integer
     */
    public function getItemsPerPage() {
        return $this->_itemsPerPage;
    }

    /**
     * Set the items per page
     * @param integer $itemsPerPage
     */
    public function setItemsPerPage($itemsPerPage) {
        $this->_itemsPerPage = $itemsPerPage;
    }

    /**
     * Get the first publication date
     * @return string
     */
    public function getFromDate() {
        return $this->_fromDate;
    }

    /**
     * Set the first publication date
     * @param string $fromDate
     */
    public function setFromDate($fromDate) {
        $this->_fromDate = $fromDate;
    }

    /**
     * Get the last publication date
     * @return string
     */
    public function getToDate() {
        return $this->_toDate;
    }

    /**
     * Set the last publication date
     * @param string $toDate
     */
    public function setToDate($toDate) {
        $this->_toDate = $toDate;
    }

    /**
     * Get the result ordering criteria
     * @return string
     */
    public function getOrderBy() {
        return $this->_orderBy;
    }

    /**
     * Set the result ordering criteria
     * @param string $orderBy
     */
    public function setOrderBy($orderBy) {
        $this->_orderBy = $orderBy;
    }

    /**
     * Get the result ordering direction
     * @return boolean
     */
    public function getOrderDir() {
        return $this->_orderDir;
    }

    /**
     * Set the result ordering direction
     * @param boolean $orderDir
     */
    public function setOrderDir($orderDir) {
        $this->_orderDir = $orderDir;
    }

    /**
     * Is spellchecking enabled?
     * @return boolean
     */
    public function getSpellcheck() {
        return $this->_spellcheck;
    }

    /**
     * Set whether spellchecking should be enabled.
     * @param boolean $spellcheck
     */
    public function setSpellcheck($spellcheck) {
        $this->_spellcheck = $spellcheck;
    }

    /**
     * Is highlighting enabled?
     * @return boolean
     */
    public function getHighlighting() {
        return $this->_highlighting;
    }

    /**
     * Set whether highlighting should be enabled.
     * @param boolean $highlighting
     */
    public function setHighlighting($highlighting) {
        $this->_highlighting = $highlighting;
    }

    /**
     * For which categories should faceting
     * be enabled?
     * @return array
     */
    public function getFacetCategories() {
        return $this->_facetCategories;
    }

    /**
     * Set the categories for which faceting
     * should be enabled.
     * @param array $facetCategories
     */
    public function setFacetCategories($facetCategories) {
        $this->_facetCategories = $facetCategories;
    }

    /**
     * Get boost factors.
     * @return array A field -> value -> boost factor assignment
     */
    public function getBoostFactors() {
        return $this->_boostFactors;
    }

    /**
     * Set boost factors.
     * @param array $boostFactors A field -> value -> boost factor assignment
     */
    public function setBoostFactors($boostFactors) {
        $this->_boostFactors = $boostFactors;
    }

    /**
     * Set the boost factor for a field/value combination.
     * @param string $field
     * @param string $value
     * @param float $boostFactor
     */
    public function addBoostFactor($field, $value, $boostFactor) {
        // Ignore empty values.
        if (empty($value)) return;

        // Ignore neutral boost factors.
        $boostFactor = (float)$boostFactor;
        if ($boostFactor == LUCENE_PLUGIN_DEFAULT_RANKING_BOOST) return;

        // Save the boost factor.
        if (!isset($this->_boostFactors[$field])) {
            $this->_boostFactors[$field] = [];
        }
        $this->_boostFactors[$field][$value] = $boostFactor;
    }


    //
    // Public methods
    //
    /**
     * Configure the search request from a keywords
     * array as required by ArticleSearch::retrieveResults()
     *
     * @param array $keywords See ArticleSearch::retrieveResults()
     */
    public function addQueryFromKeywords($keywords) {
        // Get a mapping of OJS search fields bitmaps to index fields.
        $indexFieldMap = ArticleSearch::getIndexFieldMap();

        // The keywords list is indexed with a search field bitmap.
        foreach($keywords as $searchFieldBitmap => $searchPhrase) {
            // Translate the search field from OJS to solr nomenclature.
            if (empty($searchFieldBitmap)) {
                // An empty search field means "all fields".
                $solrFields = array_values($indexFieldMap);
            } else {
                $solrFields = [];
                foreach($indexFieldMap as $ojsField => $solrField) {
                    // The search field bitmap may stand for
                    // several actual index fields (e.g. the index terms
                    // field).
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
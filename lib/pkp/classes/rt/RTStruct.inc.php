<?php
declare(strict_types=1);

/**
 * @file classes/rt/RTStruct.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTVersion
 * @ingroup rt
 * @see RT
 *
 * @brief Data structures associated with the Reading Tools component.
 * * REFACTORED: Wizdam Edition (PHP 8 Compatibility, Visibility, No Reference Return)
 */

/**
 * RT Version entity.
 */
class RTVersion {

    /** @var int|null unique identifier */
    public $versionId;

    /** @var string key */
    public $key;

    /** @var string locale key */
    public $locale;

    /** @var string version title */
    public $title;

    /** @var string version description */
    public $description;

    /** @var array RTContext version contexts */
    public $contexts = array();


    /**
     * Add an RT Context to this version.
     * @param $context RTContext
     */
    public function addContext($context) {
        array_push($this->contexts, $context);
    }

    /**
     * Get contexts.
     * @return array
     */
    public function getContexts() {
        return $this->contexts;
    }

    /**
     * Set contexts.
     * @param $contexts array
     */
    public function setContexts($contexts) {
        $this->contexts = $contexts;
    }

    /**
     * Set Version ID.
     * @param $versionId int
     */
    public function setVersionId($versionId) {
        $this->versionId = $versionId;
    }

    /**
     * Get Version ID.
     * @return int|null
     */
    public function getVersionId() {
        return $this->versionId;
    }

    /**
     * Set Title.
     * @param $title string
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Get Title.
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set Locale.
     * @param $locale string
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * Get Locale.
     * @return string
     */
    public function getLocale() {
        return $this->locale;
    }

    /**
     * Set Key.
     * @param $key string
     */
    public function setKey($key) {
        $this->key = $key;
    }

    /**
     * Get Key.
     * @return string
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * Set Description.
     * @param $description string
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Get Description.
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }
}

/**
 * RT Context entity.
 */
class RTContext {

    /** @var int|null unique identifier */
    public $contextId;

    /** @var int|null unique version identifier */
    public $versionId;

    /** @var string context title */
    public $title;

    /** @var string context abbreviation */
    public $abbrev;

    /** @var string context description */
    public $description;

    /** @var bool default search terms to author names */
    public $authorTerms = false;

    /** @var bool default search terms to geo indexing data */
    public $geoTerms = false;

    /** @var bool default use as define terms context */
    public $defineTerms = false;

    /** @var bool default use as "cited by" context */
    public $citedBy = false;

    /** @var int ordering of this context within version */
    public $order = 0;

    /** @var array RTSearch context searches */
    public $searches = array();


    /**
     * Add an RT Search to this context.
     * @param $search RTSearch
     */
    public function addSearch($search) {
        array_push($this->searches, $search);
    }

    /**
     * Get searches.
     * @return array
     */
    public function getSearches() {
        return $this->searches;
    }

    /**
     * Set searches.
     * @param $searches array
     */
    public function setSearches($searches) {
        $this->searches = $searches;
    }

    /**
     * Set Context ID.
     * @param $contextId int
     */
    public function setContextId($contextId) {
        $this->contextId = $contextId;
    }

    /**
     * Get Context ID.
     * @return int|null
     */
    public function getContextId() {
        return $this->contextId;
    }

    /**
     * Set Version ID.
     * @param $versionId int
     */
    public function setVersionId($versionId) {
        $this->versionId = $versionId;
    }

    /**
     * Get Version ID.
     * @return int|null
     */
    public function getVersionId() {
        return $this->versionId;
    }

    /**
     * Set Title.
     * @param $title string
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Get Title.
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set Abbreviation.
     * @param $abbrev string
     */
    public function setAbbrev($abbrev) {
        $this->abbrev = $abbrev;
    }

    /**
     * Get Abbreviation.
     * @return string
     */
    public function getAbbrev() {
        return $this->abbrev;
    }

    /**
     * Set Description.
     * @param $description string
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Get Description.
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Get Cited By flag.
     * @return bool
     */
    public function getCitedBy() {
        return $this->citedBy;
    }

    /**
     * Set Cited By flag.
     * @param $citedBy bool
     */
    public function setCitedBy($citedBy) {
        $this->citedBy = $citedBy;
    }

    /**
     * Get Author Terms flag.
     * @return bool
     */
    public function getAuthorTerms() {
        return $this->authorTerms;
    }

    /**
     * Set Author Terms flag.
     * @param $authorTerms bool
     */
    public function setAuthorTerms($authorTerms) {
        $this->authorTerms = $authorTerms;
    }

    /**
     * Get Geo Terms flag.
     * @return bool
     */
    public function getGeoTerms() {
        return $this->geoTerms;
    }

    /**
     * Set Geo Terms flag.
     * @param $geoTerms bool
     */
    public function setGeoTerms($geoTerms) {
        $this->geoTerms = $geoTerms;
    }

    /**
     * Get Define Terms flag.
     * @return bool
     */
    public function getDefineTerms() {
        return $this->defineTerms;
    }

    /**
     * Set Define Terms flag.
     * @param $defineTerms bool
     */
    public function setDefineTerms($defineTerms) {
        $this->defineTerms = $defineTerms;
    }

    /**
     * Get Order.
     * @return int
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * Set Order.
     * @param $order int
     */
    public function setOrder($order) {
        $this->order = $order;
    }
}

/**
 * RT Search entity.
 */
class RTSearch {

    /** @var int|null unique identifier */
    public $searchId;

    /** @var int|null unique context identifier */
    public $contextId;

    /** @var string site title */
    public $title;

    /** @var string site description */
    public $description;

    /** @var string site URL */
    public $url;

    /** @var string search URL */
    public $searchUrl;

    /** @var string search POST body */
    public $searchPost;

    /** @var int ordering of this search within context */
    public $order = 0;

    /* Getter / Setter Functions */

    /**
     * Get Search ID.
     * @return int|null
     */
    public function getSearchId() {
        return $this->searchId;
    }

    /**
     * Set Search ID.
     * @param $searchId int
     */
    public function setSearchId($searchId) {
        $this->searchId = $searchId;
    }

    /**
     * Get Context ID.
     * @return int|null
     */
    public function getContextId() {
        return $this->contextId;
    }

    /**
     * Set Context ID.
     * @param $contextId int
     */
    public function setContextId($contextId) {
        $this->contextId = $contextId;
    }

    /**
     * Get Title.
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Set Title.
     * @param $title string
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Get Description.
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set Description.
     * @param $description string
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * Get URL.
     * @return string
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Set URL.
     * @param $url string
     */
    public function setUrl($url) {
        $this->url = $url;
    }

    /**
     * Get Search URL.
     * @return string
     */
    public function getSearchUrl() {
        return $this->searchUrl;
    }

    /**
     * Set Search URL.
     * @param $searchUrl string
     */
    public function setSearchUrl($searchUrl) {
        $this->searchUrl = $searchUrl;
    }

    /**
     * Get Search Post data.
     * @return string
     */
    public function getSearchPost() {
        return $this->searchPost;
    }

    /**
     * Set Search Post data.
     * @param $searchPost string
     */
    public function setSearchPost($searchPost) {
        $this->searchPost = $searchPost;
    }

    /**
     * Get Order.
     * @return int
     */
    public function getOrder() {
        return $this->order;
    }

    /**
     * Set Order.
     * @param $order int
     */
    public function setOrder($order) {
        $this->order = $order;
    }
}

?>
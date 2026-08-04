<?php
declare(strict_types=1);

/**
 * @file plugins/generic/externalFeed/ExternalFeed.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExternalFeed
 * @ingroup plugins_generic_externalFeed
 *
 * @brief Basic class describing an external feed.
 */

define('EXTERNAL_FEED_DISPLAY_BLOCK_NONE', 0);
define('EXTERNAL_FEED_DISPLAY_BLOCK_HOMEPAGE', 1);
define('EXTERNAL_FEED_DISPLAY_BLOCK_ALL', 2);

class ExternalFeed extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ExternalFeed() {
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
    // Get/set methods
    //

    /**
     * Get the ID of the external feed.
     * @return int
     */
    public function getId() {
        $id = $this->getData('feedId');
        return $id !== null ? (int) $id : 0;
    }

    /**
     * Set the ID of the external feed.
     * @param int $feedId
     */
    public function setId($feedId) {
        $this->setData('feedId', $feedId !== null ? (int) $feedId : 0);
    }

    /**
     * Get the journal ID of the external feed.
     * @return int
     */
    public function getJournalId() {
        $id = $this->getData('journalId');
        return $id !== null ? (int) $id : 0;
    }

    /**
     * Set the journal ID of the external feed.
     * @param int $journalId
     */
    public function setJournalId($journalId) {
        $this->setData('journalId', $journalId !== null ? (int) $journalId : 0);
    }

    /**
     * Get feed URL.
     * @return string
     */
    public function getUrl() {
        return (string) $this->getData('url');
    }

    /**
     * Set feed URL.
     * @param string $url
     */
    public function setUrl($url) {
        $this->setData('url', (string) $url);
    }

    /**
     * Get feed display sequence.
     * @return float
     */
    public function getSeq() {
        $seq = $this->getData('seq');
        return $seq !== null ? (float) $seq : 0.0;
    }

    /**
     * Set feed display sequence.
     * @param float $seq
     */
    public function setSeq($seq) {
        $this->setData('seq', $seq !== null ? (float) $seq : 0.0);
    }

    /**
     * Get homepage display of the external feed.
     * @return int
     */
    public function getDisplayHomepage() {
        $display = $this->getData('displayHomepage');
        return $display !== null ? (int) $display : 0;
    }

    /**
     * Set the homepage display of the external feed.
     * @param int $displayHomepage
     */
    public function setDisplayHomepage($displayHomepage) {
        $this->setData('displayHomepage', $displayHomepage !== null ? (int) $displayHomepage : 0);
    }

    /**
     * Get block display of the external feed.
     * @return int
     */
    public function getDisplayBlock() {
        $display = $this->getData('displayBlock');
        return $display !== null ? (int) $display : 0;
    }

    /**
     * Set the block display of the external feed.
     * @param int $displayBlock
     */
    public function setDisplayBlock($displayBlock) {
        $this->setData('displayBlock', $displayBlock !== null ? (int) $displayBlock : 0);
    }

    /**
     * Get limit items of the external feed.
     * @return int
     */
    public function getLimitItems() {
        $limit = $this->getData('limitItems');
        return $limit !== null ? (int) $limit : 0;
    }

    /**
     * Set limit items of the external feed.
     * @param int $limitItems
     */
    public function setLimitItems($limitItems) {
        $this->setData('limitItems', $limitItems !== null ? (int) $limitItems : 0);
    }

    /**
     * Get recent items of the external feed.
     * @return int
     */
    public function getRecentItems() {
        $recent = $this->getData('recentItems');
        return $recent !== null ? (int) $recent : 0;
    }

    /**
     * Set recent items of the external feed.
     * @param int $recentItems
     */
    public function setRecentItems($recentItems) {
        $this->setData('recentItems', $recentItems !== null ? (int) $recentItems : 0);
    }

    /**
     * Get the localized title.
     * @return string
     */
    public function getLocalizedTitle() {
        return (string) $this->getLocalizedData('title');
    }

    /**
     * Get feed title.
     * @param string $locale
     * @return string
     */
    public function getTitle($locale) {
        return (string) $this->getData('title', $locale);
    }

    /**
     * Set feed title.
     * @param string $title
     * @param string $locale
     */
    public function setTitle($title, $locale) {
        $this->setData('title', (string) $title, $locale);
    }
    
}
?>
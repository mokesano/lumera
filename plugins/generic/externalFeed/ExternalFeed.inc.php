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
 * * MODERNIZED FOR PHP 7.4+ & OJS FORK
 * - Implemented __construct.
 * - Added strict type casting (int/float) for data security.
 */

define('EXTERNAL_FEED_DISPLAY_BLOCK_NONE',         0);
define('EXTERNAL_FEED_DISPLAY_BLOCK_HOMEPAGE',     1);
define('EXTERNAL_FEED_DISPLAY_BLOCK_ALL',          2);

class ExternalFeed extends DataObject {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get the ID of the external feed.
     * @return int
     */
    public function getId() {
        return (int) $this->getData('feedId');
    }

    /**
     * Set the ID of the external feed.
     * @param $feedId int
     */
    public function setId($feedId) {
        return $this->setData('feedId', (int) $feedId);
    }

    /**
     * Get the journal ID of the external feed.
     * @return int
     */
    public function getJournalId() {
        return (int) $this->getData('journalId');
    }

    /**
     * Set the journal ID of the external feed.
     * @param $journalId int
     */
    public function setJournalId($journalId) {
        return $this->setData('journalId', (int) $journalId);
    }

    /**
     * Get feed URL.
     * @return string 
     */
    public function getUrl() {
        return $this->getData('url');
    }

    /**
     * Set feed URL.
     * @param $url string
     */
    public function setUrl($url) {
        return $this->setData('url', (string) $url);
    }

    /**
     * Get feed display sequence.
     * @return float
     */
    public function getSeq(): int {
        return (int) $this->getData('seq');
    }

    /**
     * Set feed display sequence
     * @param $sequence float
     */
    public function setSeq($seq): int {
        return (int) $this->setData('seq', (float) $seq);
    }

    /**
     * Get homepage display of the external feed.
     * @return int
     */
    public function getDisplayHomepage() {
        return (int) $this->getData('displayHomepage');
    }

    /**
     * Set the homepage display of the external feed.
     * @param $displayHomepage int
     */
    public function setDisplayHomepage($displayHomepage) {
        return $this->setData('displayHomepage', (int) $displayHomepage);
    }

    /**
     * Get block display of the external feed.
     * @return int
     */
    public function getDisplayBlock() {
        return (int) $this->getData('displayBlock');
    }

    /**
     * Set the block display of the external feed.
     * @param $displayBlock int
     */
    public function setDisplayBlock($displayBlock) {
        return $this->setData('displayBlock', (int) $displayBlock);
    }

    /**
     * Get limit items of the external feed.
     * @return int
     */
    public function getLimitItems() {
        return (int) $this->getData('limitItems');
    }

    /**
     * Set limit items of the external feed.
     * @param $limitItems int
     */
    public function setLimitItems($limitItems) {
        return $this->setData('limitItems', (int) $limitItems);
    }

    /**
     * Get recent items of the external feed.
     * @return int
     */
    public function getRecentItems() {
        return (int) $this->getData('recentItems');
    }

    /**
     * Set recent items of the external feed.
     * @param $recentItems int
     */
    public function setRecentItems($recentItems) {
        return $this->setData('recentItems', (int) $recentItems);
    }


    /**
     * Get the localized title
     * @return string
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedData('title');
    }

    /**
     * Get feed title
     * @param $locale string
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set feed title
     * @param $title string
     * @param $locale string
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', $title, $locale);
    }
}

?>
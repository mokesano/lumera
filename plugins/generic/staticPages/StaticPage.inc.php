<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPage.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.staticPages
 * @class StaticPage
 * * MODERNIZED FOR WIZDAM FORK
 */

class StaticPage extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function StaticPage() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::StaticPage(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get journal id
     * @return string
     */
    public function getJournalId(){
        return $this->getData('journalId');
    }

    /**
     * Set journal Id
     * @param $journalId int
     */
    public function setJournalId($journalId) {
        return $this->setData('journalId', $journalId);
    }


    /**
     * Set page title
     * @param string string
     * @param locale
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get page title
     * @param locale
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Get Localized page title
     * @return string
     */
    public function getStaticPageTitle() {
        return $this->getLocalizedData('title');
    }

    /**
     * Set page content
     * @param $content string
     * @param locale
     */
    public function setContent($content, $locale) {
        return $this->setData('content', $content, $locale);
    }

    /**
     * Get content
     * @param locale
     * @return string
     */
    public function getContent($locale) {
        return $this->getData('content', $locale);
    }

    /**
     * Get "localized" content
     * @return string
     */
    public function getStaticPageContent() {
        return $this->getLocalizedData('content');
    }

    /**
     * Get page path string
     * @return string
     */
    public function getPath() {
        return $this->getData('path');
    }

     /**
      * Set page path string
      * @param $path string
      */
    public function setPath($path) {
        return $this->setData('path', $path);
    }

    /**
     * Get ID of page.
     * @deprecated since OJS 2.x. Please use getId() instead.
     * @return int
     */
    public function getStaticPageId() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Function '" . get_class($this) . "::" . __FUNCTION__ . "()' is deprecated. Please use 'getId()' instead.", E_USER_DEPRECATED);
        }
        return $this->getId();
    }

    /**
     * Set ID of page.
     * @deprecated since OJS 2.x. Please use setId() instead.
     * @param $staticPageId int
     */
    public function setStaticPageId($staticPageId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Function '" . get_class($this) . "::" . __FUNCTION__ . "()' is deprecated. Please use 'setId()' instead.", E_USER_DEPRECATED);
        }
        return $this->setId($staticPageId);
    }
}

?>
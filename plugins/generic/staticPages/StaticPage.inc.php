<?php
declare(strict_types=1);

/**
 * @file plugins/generic/staticPages/StaticPage.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StaticPage
 * @ingroup plugins_generic_staticPages
 *
 * @brief Represents a static page.
 */

class StaticPage extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function StaticPage() {
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
     * Get journal ID.
     * @return int|null
     */
    public function getJournalId() {
        $id = $this->getData('journalId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set journal ID.
     * @param int $journalId
     */
    public function setJournalId($journalId) {
        $this->setData('journalId', (int) $journalId);
    }

    /**
     * Set page title.
     * @param string $title
     * @param string $locale
     */
    public function setTitle($title, $locale) {
        $this->setData('title', (string) $title, (string) $locale);
    }

    /**
     * Get page title.
     * @param string $locale
     * @return string|null
     */
    public function getTitle($locale) {
        $title = $this->getData('title', (string) $locale);
        return $title !== null ? (string) $title : null;
    }

    /**
     * Get localized page title.
     * @return string|null
     */
    public function getStaticPageTitle() {
        $title = $this->getLocalizedData('title');
        return $title !== null ? (string) $title : null;
    }

    /**
     * Set page content.
     * @param string $content
     * @param string $locale
     */
    public function setContent($content, $locale) {
        $this->setData('content', (string) $content, (string) $locale);
    }

    /**
     * Get page content.
     * @param string $locale
     * @return string|null
     */
    public function getContent($locale) {
        $content = $this->getData('content', (string) $locale);
        return $content !== null ? (string) $content : null;
    }

    /**
     * Get localized page content.
     * @return string|null
     */
    public function getStaticPageContent() {
        $content = $this->getLocalizedData('content');
        return $content !== null ? (string) $content : null;
    }

    /**
     * Get page path string.
     * @return string|null
     */
    public function getPath() {
        $path = $this->getData('path');
        return $path !== null ? (string) $path : null;
    }

    /**
     * Set page path string.
     * @param string $path
     */
    public function setPath($path) {
        $this->setData('path', (string) $path);
    }

    /**
     * Get ID of page.
     * @deprecated Please use getId() instead.
     * @return int|null
     */
    public function getStaticPageId() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Function '" . get_class($this) . "::" . __FUNCTION__ . "()' is deprecated. Please use 'getId()' instead.", E_USER_DEPRECATED);
        }
        $id = $this->getId();
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set ID of page.
     * @deprecated Please use setId() instead.
     * @param int $staticPageId
     */
    public function setStaticPageId($staticPageId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Function '" . get_class($this) . "::" . __FUNCTION__ . "()' is deprecated. Please use 'setId()' instead.", E_USER_DEPRECATED);
        }
        $this->setId((int) $staticPageId);
    }

}
?>
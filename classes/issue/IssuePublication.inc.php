<?php
declare(strict_types=1);

/**
 * @defgroup issue Issue
 */

/**
 * @file classes/issue/IssuePublication.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssuePublication
 * @ingroup issue
 * @see Issue
 *
 * @brief Trait for Issue Publication state, timestamps, and status.
 */

trait IssuePublication {

    /**
     * Get published
     * @return int
     */
    public function getPublished() {
        return $this->getData('published');
    }

    /**
     * Set published
     * @param $published int
     */
    public function setPublished($published) {
        return $this->setData('published', $published);
    }

    /**
     * Get current
     * @return int
     */
    public function getCurrent() {
        return $this->getData('current');
    }

    /**
     * Set current
     * @param $current int
     */
    public function setCurrent($current) {
        return $this->setData('current', $current);
    }

    /**
     * Get date published
     * @return string (date)
     */
    public function getDatePublished() {
        return $this->getData('datePublished');
    }

    /**
     * Set date published
     * @param $datePublished string (date)
     */
    public function setDatePublished($datePublished) {
        return $this->setData('datePublished', $datePublished);
    }

    /**
     * Get date the users were last notified
     * @return string (date)
     */
    public function getDateNotified() {
        return $this->getData('dateNotified');
    }

    /**
     * Set date the users were last notified
     * @param $dateNotified string (date)
     */
    public function setDateNotified($dateNotified) {
        return $this->setData('dateNotified', $dateNotified);
    }

    /**
     * Get date the issue was last modified
     * @return string (date)
     */
    public function getLastModified() {
        return $this->getData('lastModified');
    }

    /**
     * Set date the issue was last modified
     * @param $lastModified string (date)
     */
    public function setLastModified($lastModified) {
        return $this->setData('lastModified', $lastModified);
    }

    /**
     * Stamp the date of the last modification to the current time.
     */
    public function stampModified() {
        return $this->setLastModified(Core::getCurrentDate());
    }
}
?>
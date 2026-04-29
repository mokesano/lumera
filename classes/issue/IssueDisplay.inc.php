<?php
declare(strict_types=1);

/**
 * @defgroup issue Issue
 */

/**
 * @file classes/issue/IssueDisplay.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueDisplay
 * @ingroup issue
 * @see Issue
 *
 * @brief Trait for Issue Display preferences and custom styling.
 */

trait IssueDisplay {

    /**
     * Get show volume
     * @return int
     */
    public function getShowVolume() {
        return $this->getData('showVolume');
    }

    /**
     * Set show volume
     * @param $showVolume int
     */
    public function setShowVolume($showVolume) {
        return $this->setData('showVolume', $showVolume);
    }

    /**
     * Get show number
     * @return int
     */
    public function getShowNumber() {
        return $this->getData('showNumber');
    }

    /**
     * Set show number
     * @param $showNumber int
     */
    public function setShowNumber($showNumber) {
        return $this->setData('showNumber', $showNumber);
    }

    /**
     * Get show year
     * @return int
     */
    public function getShowYear() {
        return $this->getData('showYear');
    }

    /**
     * Set show year
     * @param $showYear int
     */
    public function setShowYear($showYear) {
        return $this->setData('showYear', $showYear);
    }

    /**
     * Get show title
     * @return int
     */
    public function getShowTitle() {
        return $this->getData('showTitle');
    }

    /**
     * Set show title
     * @param $showTitle int
     */
    public function setShowTitle($showTitle) {
        return $this->setData('showTitle', $showTitle);
    }

    /**
     * Get customized stylesheet filename
     * @return string
     */
    public function getStyleFileName() {
        return $this->getData('styleFileName');
    }

    /**
     * Set customized stylesheet filename
     * @param $styleFileName string
     */
    public function setStyleFileName($styleFileName) {
        return $this->setData('styleFileName', $styleFileName);
    }

    /**
     * Get original customized stylesheet filename
     * @return string
     */
    public function getOriginalStyleFileName() {
        return $this->getData('originalStyleFileName');
    }

    /**
     * Set original customized stylesheet filename
     * @param $originalStyleFileName string
     */
    public function setOriginalStyleFileName($originalStyleFileName) {
        return $this->setData('originalStyleFileName', $originalStyleFileName);
    }

}
?>
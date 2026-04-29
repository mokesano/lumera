<?php
declare(strict_types=1);

/**
 * @defgroup issue Issue
 */

/**
 * @file classes/issue/IssueCover.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueCover
 * @ingroup issue
 * @see Issue
 *
 * @brief Trait for Issue Cover properties.
 */

trait IssueCover {

    /**
     * Get the localized issue cover filename
     * @return string
     */
    public function getLocalizedFileName() {
        return $this->getLocalizedData('fileName');
    }

    public function getIssueFileName() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedFileName();
    }

    /**
     * Get issue cover image file name
     * @param $locale string
     * @return string
     */
    public function getFileName($locale) {
        return $this->getData('fileName', $locale);
    }

    /**
     * set file name
     * @param $fileName string
     * @param $locale string
     */
    public function setFileName($fileName, $locale) {
        return $this->setData('fileName', $fileName, $locale);
    }

    /**
     * Get the localized issue cover width
     * @return string
     */
    public function getLocalizedWidth() {
        return $this->getLocalizedData('width');
    }

    public function getIssueWidth() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedWidth();
    }

    /**
     * Get width of cover page image
     * @param $locale string
     * @return string
     */
    public function getWidth($locale) {
        return $this->getData('width', $locale);
    }

    /**
     * Set width of cover page image
     * @param $locale string
     * @param $width int
     */
    public function setWidth($width, $locale) {
        return $this->setData('width', $width, $locale);
    }

    /**
     * Get the localized issue cover height
     * @return string
     */
    public function getLocalizedHeight() {
        return $this->getLocalizedData('height');
    }

    public function getIssueHeight() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedHeight();
    }

    /**
     * Get height of cover page image
     * @param $locale string
     * @return string
     */
    public function getHeight($locale) {
        return $this->getData('height', $locale);
    }

    /**
     * Set height of cover page image
     * @param $locale string
     * @param $height int
     */
    public function setHeight($height, $locale) {
        return $this->setData('height', $height, $locale);
    }

    /**
     * Get the localized issue cover filename on the uploader's computer
     * @return string
     */
    public function getLocalizedOriginalFileName() {
        return $this->getLocalizedData('originalFileName');
    }

    public function getIssueOriginalFileName() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedOriginalFileName();
    }

    /**
     * Get original issue cover image file name
     * @param $locale string
     * @return string
     */
    public function getOriginalFileName($locale) {
        return $this->getData('originalFileName', $locale);
    }

    /**
     * Set original file name
     * @param $originalFileName string
     * @param $locale string
     */
    public function setOriginalFileName($originalFileName, $locale) {
        return $this->setData('originalFileName', $originalFileName, $locale);
    }

    /**
     * Get the localized issue cover alternate text
     * @return string
     */
    public function getLocalizedCoverPageAltText() {
        return $this->getLocalizedData('coverPageAltText');
    }

    public function getIssueCoverPageAltText() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedCoverPageAltText();
    }

    /**
     * Get issue cover image alternate text
     * @param $locale string
     * @return string
     */
    public function getCoverPageAltText($locale) {
        return $this->getData('coverPageAltText', $locale);
    }

    /**
     * Set issue cover image alternate text
     * @param $coverPageAltText string
     * @param $locale string
     */
    public function setCoverPageAltText($coverPageAltText, $locale) {
        return $this->setData('coverPageAltText', $coverPageAltText, $locale);
    }

    /**
     * Get the localized issue cover description
     * @return string
     */
    public function getLocalizedCoverPageDescription() {
        return $this->getLocalizedData('coverPageDescription');
    }

    public function getIssueCoverPageDescription() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedCoverPageDescription();
    }

    /**
     * Get cover page description
     * @param $locale string
     * @return string
     */
    public function getCoverPageDescription($locale) {
        return $this->getData('coverPageDescription', $locale);
    }

    /**
     * Set cover page description
     * @param $coverPageDescription string
     * @param $locale string
     */
    public function setCoverPageDescription($coverPageDescription, $locale) {
        return $this->setData('coverPageDescription', $coverPageDescription, $locale);
    }

    /**
     * Get the localized issue cover enable/disable flag
     * @return string
     */
    public function getLocalizedShowCoverPage() {
        return $this->getLocalizedData('showCoverPage');
    }

    public function getIssueShowCoverPage() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedShowCoverPage();
    }

    /**
     * Get show issue cover image flag
     * @param $locale string
     * @return int
     */
    public function getShowCoverPage($locale) {
        return $this->getData('showCoverPage', $locale);
    }

    /**
     * Set show issue cover image flag
     * @param $showCoverPage int
     * @param $locale string
     */
    public function setShowCoverPage($showCoverPage, $locale) {
        return $this->setData('showCoverPage', $showCoverPage, $locale);
    }

    /**
     * Get hide cover page in archives
     * @param $locale string
     * @return int
     */
    public function getHideCoverPageArchives($locale) {
        return $this->getData('hideCoverPageArchives', $locale);
    }

    /**
     * Set hide cover page in archives
     * @param $hideCoverPageArchives int
     * @param $locale string
     */
    public function setHideCoverPageArchives($hideCoverPageArchives, $locale) {
        return $this->setData('hideCoverPageArchives', $hideCoverPageArchives, $locale);
    }

    /**
     * Get hide cover page prior to ToC
     * @param $locale string
     * @return int
     */
    public function getHideCoverPageCover($locale) {
        return $this->getData('hideCoverPageCover', $locale);
    }

    /**
     * Set hide cover page prior to ToC
     * @param $hideCoverPageCover int
     * @param $locale string
     */
    public function setHideCoverPageCover($hideCoverPageCover, $locale) {
        return $this->setData('hideCoverPageCover', $hideCoverPageCover, $locale);
    }

}
?>
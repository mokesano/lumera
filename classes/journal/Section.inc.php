<?php
declare(strict_types=1);

/**
 * @file classes/journal/Section.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Section
 * @ingroup journal
 * @see SectionDAO
 *
 * @brief Describes basic section properties.
 */

class Section extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Legacy Constructor Shim.
     */
    public function Section() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Section(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get localized title of journal section.
     * @return string
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedData('title');
    }

    /**
     * DEPRECATED Get localized title of journal section.
     * @return string
     */
    public function getSectionTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedTitle();
    }

    /**
     * Get localized abbreviation of journal section.
     * @return string
     */
    public function getLocalizedAbbrev() {
        return $this->getLocalizedData('abbrev');
    }

    /**
     * DEPRECATED Get localized abbreviation of journal section.
     * @return string
     */
    public function getTrackAbbrev() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedAbbrev();
    }

    /**
     * Get URL-safe title for section detail page routing.
     * [WIZDAM] Menghasilkan kebab-case dari judul section
     * untuk digunakan sebagai op parameter di URL routing.
     * Algoritma identik dengan SectionHandler::__call() slug generator.
     * @return string
     */
    public function getSectionUrlTitle(): string {
        $title = (string) $this->getLocalizedTitle();
        $slug  = strtolower($title);
        $slug  = preg_replace('/[^a-z0-9]+/', '-', $slug);
        return trim($slug, '-');
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of section.
     * @return int
     */
    public function getSectionId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set ID of section.
     * @param $sectionId int
     */
    public function setSectionId($sectionId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($sectionId);
    }

    /**
     * Get ID of journal.
     * @return int
     */
    public function getJournalId() {
        return $this->getData('journalId');
    }

    /**
     * Set ID of journal.
     * @param $journalId int
     */
    public function setJournalId($journalId) {
        return $this->setData('journalId', $journalId);
    }

    /**
     * Get ID of primary review form.
     * @return int
     */
    public function getReviewFormId() {
        return $this->getData('reviewFormId');
    }

    /**
     * Set ID of primary review form.
     * @param $reviewFormId int
     */
    public function setReviewFormId($reviewFormId) {
        return $this->setData('reviewFormId', $reviewFormId);
    }

    /**
     * Get title of section.
     * @param $locale string
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set title of section.
     * @param $title string
     * @param $locale string
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get section title abbreviation.
     * @param $locale string
     * @return string
     */
    public function getAbbrev($locale) {
        return $this->getData('abbrev', $locale);
    }

    /**
     * Set section title abbreviation.
     * @param $abbrev string
     * @param $locale string
     */
    public function setAbbrev($abbrev, $locale) {
        return $this->setData('abbrev', $abbrev, $locale);
    }

    /**
     * Get abstract word count limit.
     * @return int
     */
    public function getAbstractWordCount() {
        return $this->getData('wordCount');
    }

    /**
     * Set abstract word count limit.
     * @param $wordCount int
     */
    public function setAbstractWordCount($wordCount) {
        return $this->setData('wordCount', $wordCount);
    }

    /**
     * Get sequence of section.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of section.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Get open archive setting of section.
     * @return boolean
     */
    public function getMetaIndexed() {
        return $this->getData('metaIndexed');
    }

    /**
     * Set open archive setting of section.
     * @param $metaIndexed boolean
     */
    public function setMetaIndexed($metaIndexed) {
        return $this->setData('metaIndexed', $metaIndexed);
    }

    /**
     * Get peer-reviewed setting of section.
     * @return boolean
     */
    public function getMetaReviewed() {
        return $this->getData('metaReviewed');
    }

    /**
     * Set peer-reviewed setting of section.
     * @param $metaReviewed boolean
     */
    public function setMetaReviewed($metaReviewed) {
        return $this->setData('metaReviewed', $metaReviewed);
    }

    /**
     * Get boolean indicating whether abstracts are required
     * @return boolean
     */
    public function getAbstractsNotRequired() {
        return $this->getData('abstractsNotRequired');
    }

    /**
     * Set boolean indicating whether abstracts are required
     * @param $abstractsNotRequired boolean
     */
    public function setAbstractsNotRequired($abstractsNotRequired) {
        return $this->setData('abstractsNotRequired', $abstractsNotRequired);
    }

    /**
     * Get localized string identifying type of items in this section.
     * @return string
     */
    public function getLocalizedIdentifyType() {
        return $this->getLocalizedData('identifyType');
    }

    /**
     * DEPRECATED 
     * Get localized string identifying type of items in this section.
     * @return string
     */
    public function getSectionIdentifyType() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedIdentifyType();
    }

    /**
     * Get string identifying type of items in this section.
     * @param $locale string
     * @return string
     */
    public function getIdentifyType($locale) {
        return $this->getData('identifyType', $locale);
    }

    /**
     * Set string identifying type of items in this section.
     * @param $identifyType string
     * @param $locale string
     */
    public function setIdentifyType($identifyType, $locale) {
        return $this->setData('identifyType', $identifyType, $locale);
    }

    /**
     * Return boolean indicating whether or not submissions are restricted to [section]Editors.
     * @return boolean
     */
    public function getEditorRestricted() {
        return $this->getData('editorRestricted');
    }

    /**
     * Set whether or not submissions are restricted to [section]Editors.
     * @param $editorRestricted boolean
     */
    public function setEditorRestricted($editorRestricted) {
        return $this->setData('editorRestricted', $editorRestricted);
    }

    /**
     * Return boolean indicating if title should be hidden in issue ToC.
     * @return boolean
     */
    public function getHideTitle() {
        return $this->getData('hideTitle');
    }

    /**
     * Set if title should be hidden in issue ToC.
     * @param $hideTitle boolean
     */
    public function setHideTitle($hideTitle) {
        return $this->setData('hideTitle', $hideTitle);
    }

    /**
     * Return boolean indicating if author should be hidden in issue ToC.
     * @return boolean
     */
    public function getHideAuthor() {
        return $this->getData('hideAuthor');
    }

    /**
     * Set if author should be hidden in issue ToC.
     * @param $hideAuthor boolean
     */
    public function setHideAuthor($hideAuthor) {
        return $this->setData('hideAuthor', $hideAuthor);
    }

    /**
     * Return boolean indicating if title should be hidden in About.
     * @return boolean
     */
    public function getHideAbout() {
        return $this->getData('hideAbout');
    }

    /**
     * Set if title should be hidden in About.
     * @param $hideAbout boolean
     */
    public function setHideAbout($hideAbout) {
        return $this->setData('hideAbout', $hideAbout);
    }

    /**
     * Return boolean indicating if RT comments should be disabled.
     * @return boolean
     */
    public function getDisableComments() {
        return $this->getData('disableComments');
    }

    /**
     * Set if RT comments should be disabled.
     * @param $disableComments boolean
     */
    public function setDisableComments($disableComments) {
        return $this->setData('disableComments', $disableComments);
    }

    /**
     * Get localized section policy.
     * @return string
     */
    public function getLocalizedPolicy() {
        return $this->getLocalizedData('policy');
    }

    /**
     * DEPRECATED Get localized section policy.
     * @return string
     */
    public function getSectionPolicy() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedPolicy();
    }

    /**
     * Get policy.
     * @param $locale string
     * @return string
     */
    public function getPolicy($locale) {
        return $this->getData('policy', $locale);
    }

    /**
     * Set policy.
     * @param $policy string
     * @param $locale string
     */
    public function setPolicy($policy, $locale) {
        return $this->setData('policy', $policy, $locale);
    }
}

?>
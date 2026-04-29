<?php
declare(strict_types=1);

/**
 * @file classes/article/SuppFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SuppFile
 * @ingroup article
 * @see SuppFileDAO
 *
 * @brief Supplementary file class.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor hierarchy fix)
 * - Null Safety
 * - Strict Typing
 */

import('classes.article.ArticleFile');

class SuppFile extends ArticleFile {

    /**
     * Constructor.
     */
    public function __construct() {
        // Fix: Call immediate parent (ArticleFile), not DataObject directly
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SuppFile() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class SuppFile uses deprecated constructor parent::SuppFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of supplementary file.
     * @return int
     */
    public function getSuppFileId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getId();
    }

    /**
     * Set ID of supplementary file.
     * @param int $suppFileId
     */
    public function setSuppFileId($suppFileId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setId($suppFileId);
    }

    /**
     * Get ID of article.
     * @return int
     */
    public function getArticleId() {
        return $this->getData('articleId');
    }

    /**
     * Set ID of article.
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        return $this->setData('articleId', $articleId);
    }

    /**
     * Get localized title
     * @return string
     */
    public function getSuppFileTitle() {
        return $this->getLocalizedData('title');
    }

    /**
     * Get title.
     * @param string $locale
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set title.
     * @param string $title
     * @param string $locale
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get localized creator
     * @return string
     */
    public function getSuppFileCreator() {
        return $this->getLocalizedData('creator');
    }

    /**
     * Get creator.
     * @param string $locale
     * @return string
     */
    public function getCreator($locale) {
        return $this->getData('creator', $locale);
    }

    /**
     * Set creator.
     * @param string $creator
     * @param string $locale
     */
    public function setCreator($creator, $locale) {
        return $this->setData('creator', $creator, $locale);
    }

    /**
     * Get localized subject
     * @return string
     */
    public function getSuppFileSubject() {
        return $this->getLocalizedData('subject');
    }

    /**
     * Get subject.
     * @param string $locale
     * @return string
     */
    public function getSubject($locale) {
        return $this->getData('subject', $locale);
    }

    /**
     * Set subject.
     * @param string $subject
     * @param string $locale
     */
    public function setSubject($subject, $locale) {
        return $this->setData('subject', $subject, $locale);
    }

    /**
     * Get type (method/approach).
     * @return string
     */
    public function getType() {
        return $this->getData('type');
    }

    /**
     * Set type (method/approach).
     * @param string $type
     */
    public function setType($type) {
        return $this->setData('type', $type);
    }

    /**
     * Get localized subject
     * @return string
     */
    public function getSuppFileTypeOther() {
        return $this->getLocalizedData('typeOther');
    }

    /**
     * Get custom type.
     * @param string $locale
     * @return string
     */
    public function getTypeOther($locale) {
        return $this->getData('typeOther', $locale);
    }

    /**
     * Set custom type.
     * @param string $typeOther
     * @param string $locale
     */
    public function setTypeOther($typeOther, $locale) {
        return $this->setData('typeOther', $typeOther, $locale);
    }

    /**
     * Get localized description
     * @return string
     */
    public function getSuppFileDescription() {
        return $this->getLocalizedData('description');
    }

    /**
     * Get file description.
     * @param string $locale
     * @return string
     */
    public function getDescription($locale) {
        return $this->getData('description', $locale);
    }

    /**
     * Set file description.
     * @param string $description
     * @param string $locale
     */
    public function setDescription($description, $locale) {
        return $this->setData('description', $description, $locale);
    }

    /**
     * Get localized publisher
     * @return string
     */
    public function getSuppFilePublisher() {
        return $this->getLocalizedData('publisher');
    }

    /**
     * Get publisher.
     * @param string $locale
     * @return string
     */
    public function getPublisher($locale) {
        return $this->getData('publisher', $locale);
    }

    /**
     * Set publisher.
     * @param string $publisher
     * @param string $locale
     */
    public function setPublisher($publisher, $locale) {
        return $this->setData('publisher', $publisher, $locale);
    }

    /**
     * Get localized sponsor
     * @return string
     */
    public function getSuppFileSponsor() {
        return $this->getLocalizedData('sponsor');
    }

    /**
     * Get sponsor.
     * @param string $locale
     * @return string
     */
    public function getSponsor($locale) {
        return $this->getData('sponsor', $locale);
    }

    /**
     * Set sponsor.
     * @param string $sponsor
     * @param string $locale
     */
    public function setSponsor($sponsor, $locale) {
        return $this->setData('sponsor', $sponsor, $locale);
    }

    /**
     * Get date created.
     * @return string
     */
    public function getDateCreated() {
        return $this->getData('dateCreated');
    }

    /**
     * Set date created.
     * @param string $dateCreated
     */
    public function setDateCreated($dateCreated) {
        return $this->setData('dateCreated', $dateCreated);
    }

    /**
     * Get localized source
     * @return string
     */
    public function getSuppFileSource() {
        return $this->getLocalizedData('source');
    }

    /**
     * Get source.
     * @param string $locale
     * @return string
     */
    public function getSource($locale) {
        return $this->getData('source', $locale);
    }

    /**
     * Set source.
     * @param string $source
     * @param string $locale
     */
    public function setSource($source, $locale) {
        return $this->setData('source', $source, $locale);
    }

    /**
     * Get language.
     * @return string
     */
    public function getLanguage() {
        return $this->getData('language');
    }

    /**
     * Set language.
     * @param string $language
     */
    public function setLanguage($language) {
        return $this->setData('language', $language);
    }

    /**
     * Check if file is available to peer reviewers.
     * @return boolean
     */
    public function getShowReviewers() {
        return $this->getData('showReviewers');
    }

    /**
     * Set if file is available to peer reviewers or not.
     * @param boolean $showReviewers
     */
    public function setShowReviewers($showReviewers) {
        return $this->setData('showReviewers', $showReviewers);
    }

    /**
     * Get date file was submitted.
     * @return string
     */
    public function getDateSubmitted() {
        return $this->getData('dateSubmitted');
    }

    /**
     * Set date file was submitted.
     * @param string $dateSubmitted
     */
    public function setDateSubmitted($dateSubmitted) {
        return $this->setData('dateSubmitted', $dateSubmitted);
    }

    /**
     * Get sequence order of supplementary file.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence order of supplementary file.
     * @param float $sequence
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

    /**
     * Set remote URL of supplementary file.
     * @param string $remoteURL
     */
    public function setRemoteURL($remoteURL) {
        return $this->setData('remoteURL', $remoteURL);
    }

    /**
     * Get remote URL of supplementary file.
     * @return string
     */
    public function getRemoteURL() {
        return $this->getData('remoteURL');
    }

    /**
     * Return the "best" supp file ID -- If a public ID is set,
     * use it; otherwise use the internal Id.
     * @param Journal|null $journal The journal this article is in
     * @return string
     */
    public function getBestSuppFileId($journal = null) {
        // Retrieve the journal, if necessary.
        if (!isset($journal)) {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $article = $articleDao->getArticle($this->getArticleId());
            
            // PHP 8 Safety: Handle orphan supp files
            if (!$article) return $this->getId();

            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($article->getJournalId());
        }

        if ($journal && $journal->getSetting('enablePublicSuppFileId')) {
            $publicSuppFileId = $this->getPubId('publisher-id');
            if (!empty($publicSuppFileId)) return $publicSuppFileId;
        }
        return $this->getId();
    }
}

?>
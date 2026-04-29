<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleGalley.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleGalley
 * @ingroup article
 * @see ArticleGalleyDAO
 *
 * @brief A galley is a final presentation version of the full-text of an article.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor hierarchy fix, Ref removal)
 * - Null Safety
 */

import('classes.article.ArticleFile');

class ArticleGalley extends ArticleFile {

    /**
     * Constructor.
     */
    public function __construct() {
        // Fix: Call direct parent constructor, not DataObject directly
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleGalley() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            // [CCTV] Untuk menangkap identitas Class Anak yang memanggil
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ArticleGalley(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Check if galley is an HTML galley.
     * @return boolean
     */
    public function isHTMLGalley() {
        return false;
    }

    /**
     * Check if galley is a PDF galley.
     * @return boolean
     */
    public function isPdfGalley() {
        switch ($this->getFileType()) {
            case 'application/pdf':
            case 'application/x-pdf':
            case 'text/pdf':
            case 'text/x-pdf':
                return true;
            default: return false;
        }
    }

    /**
     * Check if the specified file is a dependent file.
     * @param int $fileId
     * @return boolean
     */
    public function isDependentFile($fileId) {
        return false;
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of galley.
     * @return int
     */
    public function getGalleyId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getId();
    }

    /**
     * Set ID of galley.
     * @param int $galleyId
     */
    public function setGalleyId($galleyId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setId($galleyId);
    }

    /**
     * Get views count.
     * @return int
     */
    public function getViews() {
        $application = PKPApplication::getApplication();
        
        // [WIZDAM FIX] Casting ID ke Integer agar sesuai Strict Typing
        return $application->getPrimaryMetricByAssoc(
            ASSOC_TYPE_GALLEY, 
            (int) $this->getId()
        );
    }

    /**
     * Get the localized value of the galley label.
     * @return string
     */
    public function getGalleyLabel() {
        $label = $this->getLabel();
        if ($this->getLocale() != AppLocale::getLocale()) {
            $locales = AppLocale::getAllLocales();
            // PHP 8 Safety: Check if locale key exists
            if (isset($locales[$this->getLocale()])) {
                $label .= ' (' . $locales[$this->getLocale()] . ')';
            }
        }
        return $label;
    }

    /**
     * Get label/title.
     * @return string
     */
    public function getLabel() {
        return $this->getData('label');
    }

    /**
     * Set label/title.
     * @param string $label
     */
    public function setLabel($label) {
        return $this->setData('label', $label);
    }

    /**
     * Get locale.
     * @return string
     */
    public function getLocale() {
        return $this->getData('locale');
    }

    /**
     * Set locale.
     * @param string $locale
     */
    public function setLocale($locale) {
        return $this->setData('locale', $locale);
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
     * Return the "best" article ID -- If a public article ID is set,
     * use it; otherwise use the internal article Id.
     * @param Journal|null $journal The journal this galley is in
     * @return string
     */
    public function getBestGalleyId($journal = null) {
        // Guideline #2: Removed & from $journal
        if ($journal === null) {
            $articleDao = DAORegistry::getDAO('ArticleDAO');
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journalId = $articleDao->getArticleJournalId($this->getArticleId());
            $journal = $journalDao->getById($journalId);
        }

        // PHP 8 Safety: Ensure journal exists before calling methods
        if ($journal && $journal->getSetting('enablePublicGalleyId')) {
            $publicGalleyId = $this->getPubId('publisher-id');
            if (!empty($publicGalleyId)) return $publicGalleyId;
        }
        
        return $this->getId();
    }

    /**
     * Set remote URL of the galley.
     * @param string $remoteURL
     */
    public function setRemoteURL($remoteURL) {
        return $this->setData('remoteURL', $remoteURL);
    }

    /**
     * Get remote URL of the galley.
     * @return string
     */
    public function getRemoteURL() {
        return $this->getData('remoteURL');
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/article/Author.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Author
 * @ingroup article
 * @see AuthorDAO
 *
 * @brief Article author metadata class.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor hierarchy)
 * - Strict SHIM
 * - Visibility explicit
 */

import('lib.pkp.classes.submission.PKPAuthor');

class Author extends PKPAuthor {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Author() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class Author uses deprecated constructor parent::Author(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of article.
     * Deprecated in favor of getSubmissionId().
     * @return int
     */
    public function getArticleId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getSubmissionId();
    }

    /**
     * Set ID of article.
     * Deprecated in favor of setSubmissionId().
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setSubmissionId($articleId);
    }

    /**
     * Get the localized competing interests statement for this author
     * @return string
     */
    public function getLocalizedCompetingInterests() {
        return $this->getLocalizedData('competingInterests');
    }

    /**
     * Get author competing interests.
     * Deprecated in favor of getLocalizedCompetingInterests().
     * @deprecated
     * @return string
     */
    public function getAuthorCompetingInterests() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getLocalizedCompetingInterests();
    }

    /**
     * Get author competing interests.
     * @param string $locale
     * @return string
     */
    public function getCompetingInterests($locale) {
        return $this->getData('competingInterests', $locale);
    }

    /**
     * Set author competing interests.
     * @param string $competingInterests
     * @param string $locale
     */
    public function setCompetingInterests($competingInterests, $locale) {
        return $this->setData('competingInterests', $competingInterests, $locale);
    }
}

?>
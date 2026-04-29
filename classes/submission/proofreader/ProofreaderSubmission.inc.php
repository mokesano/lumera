<?php
declare(strict_types=1);

/**
 * @defgroup submission_proofreader
 */
 
/**
 * @file classes/submission/proofreader/ProofreaderSubmission.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofreaderSubmission
 * @ingroup submission_proofreader
 * @see ProofreaderSubmissionDAO
 *
 * @brief Describes a proofreader's view of a submission
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.article.Article');

class ProofreaderSubmission extends Article {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ProofreaderSubmission() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
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
     * Get most recent layout comment.
     * @return object|null ArticleComment
     */
    public function getMostRecentLayoutComment() {
        return $this->getData('mostRecentLayoutComment');
    }

    /**
     * Set most recent layout comment.
     * @param object $mostRecentLayoutComment ArticleComment
     */
    public function setMostRecentLayoutComment($mostRecentLayoutComment) {
        return $this->setData('mostRecentLayoutComment', $mostRecentLayoutComment);
    }

    /**
     * Get supplementary files for this article.
     * @return array|null SuppFiles
     */
    public function getSuppFiles() {
        return $this->getData('suppFiles');
    }

    /**
     * Set supplementary file for this article.
     * @param array $suppFiles SuppFiles
     */
    public function setSuppFiles($suppFiles) {
        return $this->setData('suppFiles', $suppFiles);
    }


    /**
     * Get the galleys for an article.
     * @return array|null ArticleGalley
     */
    public function getGalleys() {
        return $this->getData('galleys');
    }

    /**
     * Set the galleys for an article.
     * @param array $galleys ArticleGalley
     */
    public function setGalleys($galleys) {
        return $this->setData('galleys', $galleys);
    }

    /**
     * Get most recent proofread comment.
     * @return object|null ArticleComment
     */
    public function getMostRecentProofreadComment() {
        return $this->getData('mostRecentProofreadComment');
    }

    /**
     * Set most recent proofread comment.
     * @param object $mostRecentProofreadComment ArticleComment
     */
    public function setMostRecentProofreadComment($mostRecentProofreadComment) {
        return $this->setData('mostRecentProofreadComment', $mostRecentProofreadComment);
    }

    /**
     * Get edit assignments for this article.
     * @return array|null
     */
    public function getEditAssignments() {
        return $this->getData('editAssignments');
    }

    /**
     * Set edit assignments for this article.
     * @param array $editAssignments
     */
    public function setEditAssignments($editAssignments) {
        return $this->setData('editAssignments', $editAssignments);
    }
}

?>
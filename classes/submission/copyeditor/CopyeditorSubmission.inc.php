<?php
declare(strict_types=1);

/**
 * @file classes/submission/copyeditor/CopyeditorSubmission.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditorSubmission
 * @ingroup submission
 * @see CopyeditorSubmissionDAO
 *
 * @brief CopyeditorSubmission class.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.article.Article');

class CopyeditorSubmission extends Article {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CopyeditorSubmission() {
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
    // Editor
    //    

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

    //
    // Comments
    //

    /**
     * Get most recent copyedit comment.
     * @return object|null ArticleComment
     */
    public function getMostRecentCopyeditComment() {
        return $this->getData('mostRecentCopyeditComment');
    }

    /**
     * Set most recent copyedit comment.
     * @param object $mostRecentCopyeditComment ArticleComment
     */
    public function setMostRecentCopyeditComment($mostRecentCopyeditComment) {
        return $this->setData('mostRecentCopyeditComment', $mostRecentCopyeditComment);
    }

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
}
?>
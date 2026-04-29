<?php
declare(strict_types=1);

/**
 * @file classes/submission/author/AuthorSubmission.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmission
 * @ingroup submission
 * @see AuthorSubmissionDAO
 *
 * @brief AuthorSubmission class.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.article.Article');

class AuthorSubmission extends Article {

    /** @var array ReviewAssignments of this article */
    public $reviewAssignments = [];

    /** @var array the editor decisions of this article */
    public $editorDecisions = [];

    /** @var array the revisions of the author file */
    public $authorFileRevisions = [];

    /** @var array the revisions of the editor file */
    public $editorFileRevisions = [];

    /** @var array the revisions of the author copyedit file */
    public $copyeditFileRevisions = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->reviewAssignments = [];
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthorSubmission() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get/Set Methods.
     */

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

    /**
     * Add a review assignment for this article.
     * @param object $reviewAssignment ReviewAssignment
     */
    public function addReviewAssignment($reviewAssignment) {
        if ($reviewAssignment->getSubmissionId() == null) {
            $reviewAssignment->setSubmissionId($this->getArticleId());
        }

        $this->reviewAssignments[] = $reviewAssignment;
    }

    /**
     * Remove a review assignment.
     * @param int $reviewId ID of the review assignment to remove
     * @return boolean review assignment was removed
     */
    public function removeReviewAssignment($reviewId) {
        $reviewAssignments = [];
        $found = false;
        for ($i=0, $count=count($this->reviewAssignments); $i < $count; $i++) {
            if ($this->reviewAssignments[$i]->getReviewId() == $reviewId) {
                $found = true;
            } else {
                $reviewAssignments[] = $this->reviewAssignments[$i];
            }
        }
        $this->reviewAssignments = $reviewAssignments;

        return $found;
    }

    //
    // Review Assignments
    //

    /**
     * Get review assignments for this article.
     * @param int|null $round
     * @return array ReviewAssignments
     */
    public function getReviewAssignments($round = null) {
        if ($round == null) {
            // Return an array of arrays of review assignments
            return $this->reviewAssignments;
        } else {
            // Return an array of review assignments for the specified round
            return isset($this->reviewAssignments[$round]) ? $this->reviewAssignments[$round] : [];
        }
    }

    /**
     * Set review assignments for this article.
     * @param array $reviewAssignments ReviewAssignments
     * @param int $round
     */
    public function setReviewAssignments($reviewAssignments, $round) {
        return $this->reviewAssignments[$round] = $reviewAssignments;
    }

    //
    // Editor Decisions
    //

    /**
     * Get editor decisions.
     * @param int|null $round
     * @return array|null
     */
    public function getDecisions($round = null) {
        if ($round == null) {
            return $this->editorDecisions;
        } else {
            return isset($this->editorDecisions[$round]) ? $this->editorDecisions[$round] : null;
        }
    }

    /**
     * Set editor decisions.
     * @param array $editorDecisions
     * @param int $round
     */
    public function setDecisions($editorDecisions, $round) {
        return $this->editorDecisions[$round] = $editorDecisions;
    }

    /**
     * Get the submission status. Returns one of the defined constants
     * (STATUS_INCOMPLETE, STATUS_ARCHIVED, STATUS_PUBLISHED,
     * STATUS_DECLINED, STATUS_QUEUED_UNASSIGNED, STATUS_QUEUED_REVIEW,
     * or STATUS_QUEUED_EDITING). Note that this function never returns
     * a value of STATUS_QUEUED -- the three STATUS_QUEUED_... constants
     * indicate a queued submission. NOTE that this code is similar to
     * getSubmissionStatus in the SectionEditorSubmission class and
     * changes here should be propagated.
     * @return int
     */
    public function getSubmissionStatus() {
        $status = $this->getStatus();
        if ($status == STATUS_ARCHIVED || $status == STATUS_PUBLISHED ||
            $status == STATUS_DECLINED) return $status;

        // The submission is STATUS_QUEUED or the author's submission was STATUS_INCOMPLETE.
        if ($this->getSubmissionProgress()) return (STATUS_INCOMPLETE);

        // The submission is STATUS_QUEUED. Find out where it's queued.
        $editAssignments = $this->getEditAssignments();
        if (empty($editAssignments))
            return (STATUS_QUEUED_UNASSIGNED);

        $latestDecision = $this->getMostRecentDecision();
        if ($latestDecision) {
            if ($latestDecision == SUBMISSION_EDITOR_DECISION_ACCEPT) {
                return STATUS_QUEUED_EDITING;
            }
        }
        return STATUS_QUEUED_REVIEW;
    }

    /**
     * Get the most recent decision.
     * @return int|null SUBMISSION_EDITOR_DECISION_...
     */
    public function getMostRecentDecision() {
        $decisions = $this->getDecisions();
        if (empty($decisions)) return null;

        $decision = array_pop($decisions);
        if (!empty($decision)) {
            $latestDecision = array_pop($decision);
            if (isset($latestDecision['decision'])) return $latestDecision['decision'];
        }
        return null;
    }

    //
    // Files
    //

    /**
     * Get submission file for this article.
     * @return object|null ArticleFile
     */
    public function getSubmissionFile() {
        return $this->getData('submissionFile');
    }

    /**
     * Set submission file for this article.
     * @param object $submissionFile ArticleFile
     */
    public function setSubmissionFile($submissionFile) {
        return $this->setData('submissionFile', $submissionFile);
    }

    /**
     * Get revised file for this article.
     * @return object|null ArticleFile
     */
    public function getRevisedFile() {
        return $this->getData('revisedFile');
    }

    /**
     * Set revised file for this article.
     * @param object $revisedFile ArticleFile
     */
    public function setRevisedFile($revisedFile) {
        return $this->setData('revisedFile', $revisedFile);
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
     * Get all author file revisions.
     * @param int|null $round
     * @return array ArticleFiles
     */
    public function getAuthorFileRevisions($round = null) {
        if ($round == null) {
            return $this->authorFileRevisions;
        } else {
            return isset($this->authorFileRevisions[$round]) ? $this->authorFileRevisions[$round] : [];
        }
    }

    /**
     * Set all author file revisions.
     * @param array $authorFileRevisions ArticleFiles
     * @param int $round
     */
    public function setAuthorFileRevisions($authorFileRevisions, $round) {
        return $this->authorFileRevisions[$round] = $authorFileRevisions;
    }

    /**
     * Get all editor file revisions.
     * @param int|null $round
     * @return array ArticleFiles
     */
    public function getEditorFileRevisions($round = null) {
        if ($round == null) {
            return $this->editorFileRevisions;
        } else {
            return isset($this->editorFileRevisions[$round]) ? $this->editorFileRevisions[$round] : [];
        }
    }

    /**
     * Set all editor file revisions.
     * @param array $editorFileRevisions ArticleFiles
     * @param int $round
     */
    public function setEditorFileRevisions($editorFileRevisions, $round) {
        return $this->editorFileRevisions[$round] = $editorFileRevisions;
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

    //
    // Comments
    //

    /**
     * Get most recent editor decision comment.
     * @return object|null ArticleComment
     */
    public function getMostRecentEditorDecisionComment() {
        return $this->getData('mostRecentEditorDecisionComment');
    }

    /**
     * Set most recent editor decision comment.
     * @param object $mostRecentEditorDecisionComment ArticleComment
     */
    public function setMostRecentEditorDecisionComment($mostRecentEditorDecisionComment) {
        return $this->setData('mostRecentEditorDecisionComment', $mostRecentEditorDecisionComment);
    }

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
}
?>
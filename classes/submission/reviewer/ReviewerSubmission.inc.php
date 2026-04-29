<?php
declare(strict_types=1);

/**
 * @file classes/submission/reviewer/ReviewerSubmission.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmission
 * @ingroup submission
 * @see ReviewerSubmissionDAO
 *
 * @brief ReviewerSubmission class.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.article.Article');

class ReviewerSubmission extends Article {

    /** @var array ArticleFiles reviewer file revisions of this article */
    public $reviewerFileRevisions = [];

    /** @var array ArticleComments peer review comments of this article */
    public $peerReviewComments = [];

    /** @var array the editor decisions of this article */
    public $editorDecisions = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReviewerSubmission() {
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
     * Get the competing interests for this article.
     * @return string|null
     */
    public function getCompetingInterests() {
        return $this->getData('competingInterests');
    }

    /**
     * Set the competing interests statement.
     * @param string $competingInterests
     */
    public function setCompetingInterests($competingInterests) {
        return $this->setData('competingInterests', $competingInterests);
    }

    /**
     * Get ID of review assignment.
     * @return int
     */
    public function getReviewId() {
        return $this->getData('reviewId');
    }

    /**
     * Set ID of review assignment
     * @param int $reviewId
     */
    public function setReviewId($reviewId) {
        return $this->setData('reviewId', $reviewId);
    }

    /**
     * Get ID of reviewer.
     * @return int
     */
    public function getReviewerId() {
        return $this->getData('reviewerId');
    }

    /**
     * Set ID of reviewer.
     * @param int $reviewerId
     */
    public function setReviewerId($reviewerId) {
        return $this->setData('reviewerId', $reviewerId);
    }

    /**
     * Get full name of reviewer.
     * @return string
     */
    public function getReviewerFullName() {
        return $this->getData('reviewerFullName');
    }

    /**
     * Set full name of reviewer.
     * @param string $reviewerFullName
     */
    public function setReviewerFullName($reviewerFullName) {
        return $this->setData('reviewerFullName', $reviewerFullName);
    }

    /**
     * Get editor decisions.
     * @param int|null $round
     * @return array|null
     */
    public function getDecisions($round = null) {
        if ($round === null) {
            return $this->editorDecisions;
        }
        return $this->editorDecisions[$round] ?? null;
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
     * Get the most recent decision.
     * @return int|null SUBMISSION_EDITOR_DECISION_...
     */
    public function getMostRecentDecision() {
        $decisions = $this->getDecisions();
        if (empty($decisions)) return null;

        $decision = array_pop($decisions);
        if (!empty($decision)) {
            $latestDecision = array_pop($decision);
            return $latestDecision['decision'] ?? null;
        }
        return null;
    }

    /**
     * Get reviewer recommendation.
     * @return string|null
     */
    public function getRecommendation() {
        return $this->getData('recommendation');
    }

    /**
     * Set reviewer recommendation.
     * @param string $recommendation
     */
    public function setRecommendation($recommendation) {
        return $this->setData('recommendation', $recommendation);
    }

    /**
     * Get the reviewer's assigned date.
     * @return string|null
     */
    public function getDateAssigned() {
        return $this->getData('dateAssigned');
    }

    /**
     * Set the reviewer's assigned date.
     * @param string $dateAssigned
     */
    public function setDateAssigned($dateAssigned) {
        return $this->setData('dateAssigned', $dateAssigned);
    }

    /**
     * Get the reviewer's notified date.
     * @return string|null
     */
    public function getDateNotified() {
        return $this->getData('dateNotified');
    }

    /**
     * Set the reviewer's notified date.
     * @param string $dateNotified
     */
    public function setDateNotified($dateNotified) {
        return $this->setData('dateNotified', $dateNotified);
    }

    /**
     * Get the reviewer's confirmed date.
     * @return string|null
     */
    public function getDateConfirmed() {
        return $this->getData('dateConfirmed');
    }

    /**
     * Set the reviewer's confirmed date.
     * @param string $dateConfirmed
     */
    public function setDateConfirmed($dateConfirmed) {
        return $this->setData('dateConfirmed', $dateConfirmed);
    }

    /**
     * Get the reviewer's completed date.
     * @return string|null
     */
    public function getDateCompleted() {
        return $this->getData('dateCompleted');
    }

    /**
     * Set the reviewer's completed date.
     * @param string $dateCompleted
     */
    public function setDateCompleted($dateCompleted) {
        return $this->setData('dateCompleted', $dateCompleted);
    }

    /**
     * Get the reviewer's acknowledged date.
     * @return string|null
     */
    public function getDateAcknowledged() {
        return $this->getData('dateAcknowledged');
    }

    /**
     * Set the reviewer's acknowledged date.
     * @param string $dateAcknowledged
     */
    public function setDateAcknowledged($dateAcknowledged) {
        return $this->setData('dateAcknowledged', $dateAcknowledged);
    }

    /**
     * Get the reviewer's due date.
     * @return string|null
     */
    public function getDateDue() {
        return $this->getData('dateDue');
    }

    /**
     * Set the reviewer's due date.
     * @param string $dateDue
     */
    public function setDateDue($dateDue) {
        return $this->setData('dateDue', $dateDue);
    }

    /**
     * Get the declined value.
     * @return boolean
     */
    public function getDeclined() {
        return $this->getData('declined');
    }

    /**
     * Set the reviewer's declined value.
     * @param boolean $declined
     */
    public function setDeclined($declined) {
        return $this->setData('declined', $declined);
    }

    /**
     * Get the replaced value.
     * @return boolean
     */
    public function getReplaced() {
        return $this->getData('replaced');
    }

    /**
     * Set the reviewer's replaced value.
     * @param boolean $replaced
     */
    public function setReplaced($replaced) {
        return $this->setData('replaced', $replaced);
    }

    /**
     * Get the cancelled value.
     * @return boolean
     */
    public function getCancelled() {
        return $this->getData('cancelled');
    }

    /**
     * Set the reviewer's cancelled value.
     * @param boolean $cancelled
     */
    public function setCancelled($cancelled) {
        return $this->setData('cancelled', $cancelled);
    }

    /**
     * Get reviewer file id.
     * @return int
     */
    public function getReviewerFileId() {
        return $this->getData('reviewerFileId');
    }

    /**
     * Set reviewer file id.
     * @param int $reviewerFileId
     */
    public function setReviewerFileId($reviewerFileId) {
        return $this->setData('reviewerFileId', $reviewerFileId);
    }

    /**
     * Get quality.
     * @return int
     */
    public function getQuality() {
        return $this->getData('quality');
    }

    /**
     * Set quality.
     * @param int $quality
     */
    public function setQuality($quality) {
        return $this->setData('quality', $quality);
    }

    /**
     * Get round.
     * @return int
     */
    public function getRound() {
        return $this->getData('round');
    }

    /**
     * Set round.
     * @param int $round
     */
    public function setRound($round) {
        return $this->setData('round', $round);
    }

    /**
     * Get review file id.
     * @return int
     */
    public function getReviewFileId() {
        return $this->getData('reviewFileId');
    }

    /**
     * Set review file id.
     * @param int $reviewFileId
     */
    public function setReviewFileId($reviewFileId) {
        return $this->setData('reviewFileId', $reviewFileId);
    }

    /**
     * Get review revision.
     * @return int
     */
    public function getReviewRevision() {
        return $this->getData('reviewRevision');
    }

    /**
     * Set review revision.
     * @param int $reviewRevision
     */
    public function setReviewRevision($reviewRevision) {
        return $this->setData('reviewRevision', $reviewRevision);
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
     * Get review file.
     * @return object|null ArticleFile
     */
    public function getReviewFile() {
        return $this->getData('reviewFile');
    }

    /**
     * Set review file.
     * @param object $reviewFile ArticleFile
     */
    public function setReviewFile($reviewFile) {
        return $this->setData('reviewFile', $reviewFile);
    }

    /**
     * Get reviewer file.
     * @return object|null ArticleFile
     */
    public function getReviewerFile() {
        return $this->getData('reviewerFile');
    }

    /**
     * Set reviewer file.
     * @param object $reviewerFile ArticleFile
     */
    public function setReviewerFile($reviewerFile) {
        return $this->setData('reviewerFile', $reviewerFile);
    }

    /**
     * Get all reviewer file revisions.
     * @return array ArticleFiles
     */
    public function getReviewerFileRevisions() {
        return $this->reviewerFileRevisions;
    }

    /**
     * Set all reviewer file revisions.
     * @param array $reviewerFileRevisions ArticleFiles
     */
    public function setReviewerFileRevisions($reviewerFileRevisions) {
        return $this->reviewerFileRevisions = $reviewerFileRevisions;
    }

    //
    // Comments
    //

    /**
     * Get most recent peer review comment.
     * @return object|null ArticleComment
     */
    public function getMostRecentPeerReviewComment() {
        return $this->getData('peerReviewComment');
    }

    /**
     * Set most recent peer review comment.
     * @param object $peerReviewComment ArticleComment
     */
    public function setMostRecentPeerReviewComment($peerReviewComment) {
        return $this->setData('peerReviewComment', $peerReviewComment);
    }
}

?>
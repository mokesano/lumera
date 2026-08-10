<?php
declare(strict_types=1);

/**
 * @file classes/submission/sectionEditor/SectionEditorSubmission.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SectionEditorSubmission
 * @ingroup submission
 * @see SectionEditorSubmissionDAO
 *
 * @brief SectionEditorSubmission class.
 */

import('classes.article.Article');

class SectionEditorSubmission extends Article {

    /** @var array */
    protected $reviewAssignments = [];

    /** @var array */
    protected $removedReviewAssignments = [];

    /** @var array */
    protected $editorDecisions = [];

    /** @var array */
    protected $editorFileRevisions = [];

    /** @var array */
    protected $authorFileRevisions = [];

    /** @var array */
    protected $copyeditFileRevisions = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function SectionEditorSubmission() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Add a review assignment for this article.
     * @param object $reviewAssignment
     */
    public function addReviewAssignment($reviewAssignment) {
        if ($reviewAssignment->getSubmissionId() === null) {
            $reviewAssignment->setSubmissionId($this->getId());
        }

        $round = $reviewAssignment->getRound();
        if (!isset($this->reviewAssignments[$round])) {
            $this->reviewAssignments[$round] = [];
        }
        $this->reviewAssignments[$round][] = $reviewAssignment;
    }

    /**
     * Add an editorial decision for this article.
     * @param array $editorDecision
     * @param int $round
     */
    public function addDecision($editorDecision, $round) {
        if (isset($this->editorDecisions[$round]) && is_array($this->editorDecisions[$round])) {
            $this->editorDecisions[$round][] = $editorDecision;
        } else {
            $this->editorDecisions[$round] = [$editorDecision];
        }
    }

    /**
     * Remove a review assignment.
     * @param int $reviewId
     * @return bool
     */
    public function removeReviewAssignment($reviewId) {
        $reviewId = (int) $reviewId;

        foreach ($this->reviewAssignments as $round => $assignments) {
            foreach ($assignments as $index => $assignment) {
                if ($assignment->getReviewId() === $reviewId) {
                    $this->removedReviewAssignments[] = $reviewId;
                    unset($this->reviewAssignments[$round][$index]);
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Updates an existing review assignment.
     * @param object $reviewAssignment
     */
    public function updateReviewAssignment($reviewAssignment) {
        $round = $reviewAssignment->getRound();
        if (!isset($this->reviewAssignments[$round])) {
            return;
        }

        $reviewAssignments = [];
        foreach ($this->reviewAssignments[$round] as $assignment) {
            if ($assignment->getReviewId() === $reviewAssignment->getId()) {
                $reviewAssignments[] = $reviewAssignment;
            } else {
                $reviewAssignments[] = $assignment;
            }
        }
        $this->reviewAssignments[$round] = $reviewAssignments;
    }

    /**
     * Get the submission status.
     * @return int
     */
    public function getSubmissionStatus() {
        $status = $this->getStatus();
        if ($status === STATUS_ARCHIVED || $status === STATUS_PUBLISHED || $status === STATUS_DECLINED) {
            return $status;
        }

        if ($this->getSubmissionProgress()) {
            return STATUS_INCOMPLETE;
        }

        $editAssignments = $this->getEditAssignments();
        if (empty($editAssignments)) {
            return STATUS_QUEUED_UNASSIGNED;
        }

        $decisions = $this->getDecisions();
        if (is_array($decisions) && !empty($decisions)) {
            $decision = array_pop($decisions);
            if (!empty($decision)) {
                $latestDecision = array_pop($decision);
                if (isset($latestDecision['decision']) && $latestDecision['decision'] === SUBMISSION_EDITOR_DECISION_ACCEPT) {
                    return STATUS_QUEUED_EDITING;
                }
            }
        }
        
        return STATUS_QUEUED_REVIEW;
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
        $this->setData('editAssignments', $editAssignments);
    }

    //
    // Review Assignments
    //

    /**
     * Get review assignments for this article.
     * @param int|null $round
     * @return array
     */
    public function getReviewAssignments($round = null) {
        if ($round === null) {
            return $this->reviewAssignments;
        }
        return $this->reviewAssignments[$round] ?? [];
    }

    /**
     * Set review assignments for this article.
     * @param array $reviewAssignments
     * @param int $round
     */
    public function setReviewAssignments($reviewAssignments, $round) {
        $this->reviewAssignments[$round] = $reviewAssignments;
    }

    /**
     * Get the IDs of all review assignments removed.
     * @return array
     */
    public function getRemovedReviewAssignments() {
        return $this->removedReviewAssignments;
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
        $this->editorDecisions[$round] = $editorDecisions;
    }

    // 
    // Files
    //


    /**
     * Get submission file for this article.
     * @return object|null
     */
    public function getSubmissionFile() {
        return $this->getData('submissionFile');
    }

    /**
     * Set submission file for this article.
     * @param object $submissionFile
     */
    public function setSubmissionFile($submissionFile) {
        $this->setData('submissionFile', $submissionFile);
    }

    /**
     * Get revised file for this article.
     * @return object|null
     */
    public function getRevisedFile() {
        return $this->getData('revisedFile');
    }

    /**
     * Set revised file for this article.
     * @param object $revisedFile
     */
    public function setRevisedFile($revisedFile) {
        $this->setData('revisedFile', $revisedFile);
    }

    /**
     * Get supplementary files for this article.
     * @return array|null
     */
    public function getSuppFiles() {
        return $this->getData('suppFiles');
    }

    /**
     * Set supplementary file for this article.
     * @param array $suppFiles
     */
    public function setSuppFiles($suppFiles) {
        $this->setData('suppFiles', $suppFiles);
    }

    /**
     * Get review file.
     * @return object|null
     */
    public function getReviewFile() {
        return $this->getData('reviewFile');
    }

    /**
     * Set review file.
     * @param object $reviewFile
     */
    public function setReviewFile($reviewFile) {
        $this->setData('reviewFile', $reviewFile);
    }

    /**
     * Get all editor file revisions.
     * @param int|null $round
     * @return array
     */
    public function getEditorFileRevisions($round = null) {
        if ($round === null) {
            return $this->editorFileRevisions;
        }
        return $this->editorFileRevisions[$round] ?? [];
    }

    /**
     * Set all editor file revisions.
     * @param array $editorFileRevisions
     * @param int $round
     */
    public function setEditorFileRevisions($editorFileRevisions, $round) {
        $this->editorFileRevisions[$round] = $editorFileRevisions;
    }

    /**
     * Get all author file revisions.
     * @param int|null $round
     * @return array
     */
    public function getAuthorFileRevisions($round = null) {
        if ($round === null) {
            return $this->authorFileRevisions;
        }
        return $this->authorFileRevisions[$round] ?? [];
    }

    /**
     * Set all author file revisions.
     * @param array $authorFileRevisions
     * @param int $round
     */
    public function setAuthorFileRevisions($authorFileRevisions, $round) {
        $this->authorFileRevisions[$round] = $authorFileRevisions;
    }

    /**
     * Get post-review file.
     * @return object|null
     */
    public function getEditorFile() {
        return $this->getData('editorFile');
    }

    /**
     * Set post-review file.
     * @param object $editorFile
     */
    public function setEditorFile($editorFile) {
        $this->setData('editorFile', $editorFile);
    }

    //
    // Review Rounds
    //

    /**
     * Get review file revision.
     * @return int|null
     */
    public function getReviewRevision() {
        return $this->getData('reviewRevision');
    }

    /**
     * Set review file revision.
     * @param int $reviewRevision
     */
    public function setReviewRevision($reviewRevision) {
        $this->setData('reviewRevision', $reviewRevision);
    }

    //
    // Comments
    //

    /**
     * Get most recent editor decision comment.
     * @return object|null
     */
    public function getMostRecentEditorDecisionComment() {
        return $this->getData('mostRecentEditorDecisionComment');
    }

    /**
     * Set most recent editor decision comment.
     * @param object $mostRecentEditorDecisionComment
     */
    public function setMostRecentEditorDecisionComment($mostRecentEditorDecisionComment) {
        $this->setData('mostRecentEditorDecisionComment', $mostRecentEditorDecisionComment);
    }

    /**
     * Get most recent copyedit comment.
     * @return object|null
     */
    public function getMostRecentCopyeditComment() {
        return $this->getData('mostRecentCopyeditComment');
    }

    /**
     * Set most recent copyedit comment.
     * @param object $mostRecentCopyeditComment
     */
    public function setMostRecentCopyeditComment($mostRecentCopyeditComment) {
        $this->setData('mostRecentCopyeditComment', $mostRecentCopyeditComment);
    }

    /**
     * Get most recent layout comment.
     * @return object|null
     */
    public function getMostRecentLayoutComment() {
        return $this->getData('mostRecentLayoutComment');
    }

    /**
     * Set most recent layout comment.
     * @param object $mostRecentLayoutComment
     */
    public function setMostRecentLayoutComment($mostRecentLayoutComment) {
        $this->setData('mostRecentLayoutComment', $mostRecentLayoutComment);
    }

    /**
     * Get most recent proofread comment.
     * @return object|null
     */
    public function getMostRecentProofreadComment() {
        return $this->getData('mostRecentProofreadComment');
    }

    /**
     * Set most recent proofread comment.
     * @param object $mostRecentProofreadComment
     */
    public function setMostRecentProofreadComment($mostRecentProofreadComment) {
        $this->setData('mostRecentProofreadComment', $mostRecentProofreadComment);
    }

    /**
     * Get the galleys for an article.
     * @return array|null
     */
    public function getGalleys() {
        return $this->getData('galleys');
    }

    /**
     * Set the galleys for an article.
     * @param array $galleys
     */
    public function setGalleys($galleys) {
        $this->setData('galleys', $galleys);
    }

    /**
     * Return array mapping editor decision constants to their locale strings.
     * @return array
     */
    public static function getEditorDecisionOptions() {
        static $editorDecisionOptions = [
            '' => 'common.chooseOne',
            SUBMISSION_EDITOR_DECISION_ACCEPT => 'editor.article.decision.accept',
            SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS => 'editor.article.decision.pendingRevisions',
            SUBMISSION_EDITOR_DECISION_RESUBMIT => 'editor.article.decision.resubmit',
            SUBMISSION_EDITOR_DECISION_DECLINE => 'editor.article.decision.decline'
        ];
        return $editorDecisionOptions;
    }

    /**
     * Get the CSS class for highlighting this submission in a list, based on status.
     * @return string|null
     */
    public function getHighlightClass() {
        /** @var SignoffDAO $signoffDao */
        $signoffDao = DAORegistry::getDAO('SignoffDAO');
        $overdueSeconds = 60 * 60 * 24 * 14;

        if ($this->getStatus() !== STATUS_QUEUED) {
            return null;
        }

        $editAssignments = $this->getEditAssignments();
        if (empty($editAssignments)) {
            return 'highlight';
        }

        $journal = Application::get()->getRequest()->getJournal();
        if (!$journal || $journal->getId() !== $this->getJournalId()) {
            return null;
        }

        $inEditing = false;
        $decisionsEmpty = true;
        $lastDecisionDate = null;
        $decisions = $this->getDecisions();
        
        if (is_array($decisions) && !empty($decisions)) {
            $decision = array_pop($decisions);
            if (!empty($decision)) {
                $latestDecision = array_pop($decision);
                if (is_array($latestDecision)) {
                    if ($latestDecision['decision'] === SUBMISSION_EDITOR_DECISION_ACCEPT) {
                        $inEditing = true;
                    }
                    $decisionsEmpty = false;
                    $lastDecisionDate = strtotime($latestDecision['dateDecided']);
                }
            }
        }

        if ($inEditing) {
            // ---
            // --- Highlighting conditions for submissions in editing
            // ---

            // COPYEDITING
            // First round of copyediting
            $initialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_ARTICLE, $this->getId());
            $dateCopyeditorNotified = $initialSignoff->getDateNotified() ? strtotime($initialSignoff->getDateNotified()) : 0;
            $dateCopyeditorUnderway = $initialSignoff->getDateUnderway() ? strtotime($initialSignoff->getDateUnderway()) : 0;
            $dateCopyeditorCompleted = $initialSignoff->getDateCompleted() ? strtotime($initialSignoff->getDateCompleted()) : 0;
            $dateCopyeditorAcknowledged = $initialSignoff->getDateAcknowledged() ? strtotime($initialSignoff->getDateAcknowledged()) : 0;
            $dateLastCopyeditor = max($dateCopyeditorNotified, $dateCopyeditorUnderway);

            if (!$dateCopyeditorNotified) return 'highlightCopyediting';
            if ($dateLastCopyeditor && !$dateCopyeditorCompleted && $dateLastCopyeditor + $overdueSeconds < time()) return 'highlightCopyediting';
            if ($dateCopyeditorCompleted && !$dateCopyeditorAcknowledged) return 'highlightCopyediting';

            $authorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_ARTICLE, $this->getId());
            $dateCopyeditorAuthorNotified = $authorSignoff->getDateNotified() ? strtotime($authorSignoff->getDateNotified()) : 0;
            $dateCopyeditorAuthorUnderway = $authorSignoff->getDateUnderway() ? strtotime($authorSignoff->getDateUnderway()) : 0;
            $dateCopyeditorAuthorCompleted = $authorSignoff->getDateCompleted() ? strtotime($authorSignoff->getDateCompleted()) : 0;
            $dateCopyeditorAuthorAcknowledged = $authorSignoff->getDateAcknowledged() ? strtotime($authorSignoff->getDateAcknowledged()) : 0;
            $dateLastCopyeditorAuthor = max($dateCopyeditorAuthorNotified, $dateCopyeditorAuthorUnderway);

            if ($dateCopyeditorAcknowledged && !$dateCopyeditorAuthorNotified) return 'highlightCopyediting';
            if ($dateCopyeditorAuthorCompleted && !$dateCopyeditorAuthorAcknowledged) return 'highlightCopyediting';
            if ($dateLastCopyeditorAuthor && !$dateCopyeditorAuthorCompleted && $dateLastCopyeditorAuthor + $overdueSeconds < time()) return 'highlightCopyediting';

            $finalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_ARTICLE, $this->getId());
            $dateCopyeditorFinalNotified = $finalSignoff->getDateNotified() ? strtotime($finalSignoff->getDateNotified()) : 0;
            $dateCopyeditorFinalUnderway = $finalSignoff->getDateUnderway() ? strtotime($finalSignoff->getDateUnderway()) : 0;
            $dateCopyeditorFinalCompleted = $finalSignoff->getDateCompleted() ? strtotime($finalSignoff->getDateCompleted()) : 0;
            $dateCopyeditorFinalAcknowledged = $finalSignoff->getDateAcknowledged() ? strtotime($finalSignoff->getDateAcknowledged()) : 0;
            $dateLastCopyeditorFinal = max($dateCopyeditorFinalNotified, $dateCopyeditorFinalUnderway);

            if ($dateCopyeditorAuthorAcknowledged && !$dateCopyeditorFinalNotified) return 'highlightCopyediting';
            if ($dateLastCopyeditorFinal && !$dateCopyeditorFinalCompleted && $dateLastCopyeditorFinal + $overdueSeconds < time()) return 'highlightCopyediting';
            if ($dateCopyeditorFinalCompleted && !$dateCopyeditorFinalAcknowledged) return 'highlightCopyediting';

            $layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_ARTICLE, $this->getId());
            $dateLayoutNotified = $layoutSignoff->getDateNotified() ? strtotime($layoutSignoff->getDateNotified()) : 0;
            $dateLayoutUnderway = $layoutSignoff->getDateUnderway() ? strtotime($layoutSignoff->getDateUnderway()) : 0;
            $dateLayoutCompleted = $layoutSignoff->getDateCompleted() ? strtotime($layoutSignoff->getDateCompleted()) : 0;
            $dateLayoutAcknowledged = $layoutSignoff->getDateAcknowledged() ? strtotime($layoutSignoff->getDateAcknowledged()) : 0;
            $dateLastLayout = max($dateLayoutNotified, $dateLayoutUnderway);

            if ($dateLastCopyeditorFinal && !$dateLayoutNotified) return 'highlightLayoutEditing';
            if ($dateLastLayout && !$dateLayoutCompleted && $dateLastLayout + $overdueSeconds < time()) return 'highlightLayoutEditing';
            if ($dateLayoutCompleted && !$dateLayoutAcknowledged) return 'highlightLayoutEditing';

            // PROOFREADING
            // First round of proofreading
            $authorProofSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_AUTHOR', ASSOC_TYPE_ARTICLE, $this->getId());
            $dateAuthorNotified = $authorProofSignoff->getDateNotified() ? strtotime($authorProofSignoff->getDateNotified()) : 0;
            $dateAuthorUnderway = $authorProofSignoff->getDateUnderway() ? strtotime($authorProofSignoff->getDateUnderway()) : 0;
            $dateAuthorCompleted = $authorProofSignoff->getDateCompleted() ? strtotime($authorProofSignoff->getDateCompleted()) : 0;
            $dateAuthorAcknowledged = $authorProofSignoff->getDateAcknowledged() ? strtotime($authorProofSignoff->getDateAcknowledged()) : 0;
            $dateLastAuthor = max($dateLayoutNotified, $dateAuthorUnderway);

            if ($dateLayoutAcknowledged && !$dateAuthorNotified) return 'highlightProofreading';
            if ($dateLastAuthor && !$dateAuthorCompleted && $dateLastAuthor + $overdueSeconds < time()) return 'highlightProofreading';
            if ($dateAuthorCompleted && !$dateAuthorAcknowledged) return 'highlightProofreading';

            // Second round of proofreading
            $proofreaderSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_ARTICLE, $this->getId());
            $dateProofreaderNotified = $proofreaderSignoff->getDateNotified() ? strtotime($proofreaderSignoff->getDateNotified()) : 0;
            $dateProofreaderUnderway = $proofreaderSignoff->getDateUnderway() ? strtotime($proofreaderSignoff->getDateUnderway()) : 0;
            $dateProofreaderCompleted = $proofreaderSignoff->getDateCompleted() ? strtotime($proofreaderSignoff->getDateCompleted()) : 0;
            $dateProofreaderAcknowledged = $proofreaderSignoff->getDateAcknowledged() ? strtotime($proofreaderSignoff->getDateAcknowledged()) : 0;
            $dateLastProofreader = max($dateProofreaderNotified, $dateProofreaderUnderway);

            if ($dateAuthorAcknowledged && !$dateProofreaderNotified) return 'highlightProofreading';
            if ($dateProofreaderCompleted && !$dateProofreaderAcknowledged) return 'highlightProofreading';
            if ($dateLastProofreader && !$dateProofreaderCompleted && $dateLastProofreader + $overdueSeconds < time()) return 'highlightProofreading';

            // Third round of proofreading
            $layoutEditorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_ARTICLE, $this->getId());
            $dateLayoutEditorNotified = $layoutEditorSignoff->getDateNotified() ? strtotime($layoutEditorSignoff->getDateNotified()) : 0;
            $dateLayoutEditorUnderway = $layoutEditorSignoff->getDateUnderway() ? strtotime($layoutEditorSignoff->getDateUnderway()) : 0;
            $dateLayoutEditorCompleted = $layoutEditorSignoff->getDateCompleted() ? strtotime($layoutEditorSignoff->getDateCompleted()) : 0;
            $dateLayoutEditorAcknowledged = $layoutEditorSignoff->getDateAcknowledged() ? strtotime($layoutEditorSignoff->getDateAcknowledged()) : 0;

            $dateLastLayoutEditor = max($dateLayoutEditorNotified, $dateLayoutEditorUnderway);
            if ($dateProofreaderAcknowledged && !$dateLayoutEditorNotified) return 'highlightProofreading';
            if ($dateLastLayoutEditor && !$dateLayoutEditorCompleted && $dateLastLayoutEditor + $overdueSeconds < time()) return 'highlightProofreading';
            if ($dateLayoutEditorCompleted && !$dateLayoutEditorAcknowledged) return 'highlightProofreading';
        } else {
            // ---
            // --- Highlighting conditions for submissions in review
            // ---
            $reviewAssignments = $this->getReviewAssignments($this->getCurrentRound());
            if (is_array($reviewAssignments) && !empty($reviewAssignments)) {
                $allReviewsComplete = true;
                foreach ($reviewAssignments as $reviewAssignment) {
                    if ($reviewAssignment->getDateNotified() === null) {
                        return 'highlightReviewerNotNotified';
                    }

                    if (!$reviewAssignment->getCancelled() && !$reviewAssignment->getDeclined()) {
                        if (!$reviewAssignment->getDateCompleted()) {
                            $allReviewsComplete = false;
                        }

                        $dateReminded = $reviewAssignment->getDateReminded() ? strtotime($reviewAssignment->getDateReminded()) : 0;
                        $dateNotified = $reviewAssignment->getDateNotified() ? strtotime($reviewAssignment->getDateNotified()) : 0;
                        $dateConfirmed = $reviewAssignment->getDateConfirmed() ? strtotime($reviewAssignment->getDateConfirmed()) : 0;

                        if (!$reviewAssignment->getDateCompleted() && !$dateConfirmed && !$journal->getSetting('remindForInvite') && max($dateReminded, $dateNotified) + $overdueSeconds < time()) {
                            return 'highlightReviewerConfirmationOverdue';
                        }
                        if (!$reviewAssignment->getDateCompleted() && $dateConfirmed && !$journal->getSetting('remindForSubmit') && max($dateReminded, $dateConfirmed) + $overdueSeconds < time()) {
                            return 'highlightReviewerCompletionOverdue';
                        }
                    }
                }
                
                if ($allReviewsComplete && $decisionsEmpty) {
                    return 'highlightNoDecision';
                }

                $comment = $this->getMostRecentEditorDecisionComment();
                $commentDate = $comment ? strtotime($comment->getDatePosted()) : 0;
                $authorFileRevisions = $this->getAuthorFileRevisions($this->getCurrentRound());
                $authorFileDate = null;
                
                if (is_array($authorFileRevisions) && !empty($authorFileRevisions)) {
                    $authorFile = array_pop($authorFileRevisions);
                    $authorFileDate = strtotime($authorFile->getDateUploaded());
                }
                
                if (($lastDecisionDate || $commentDate) && $authorFileDate && $authorFileDate > max((int) $lastDecisionDate, $commentDate)) {
                    return 'highlightRevisedCopyUploaded';
                }
            }
        }
        return null;
    }

}
?>
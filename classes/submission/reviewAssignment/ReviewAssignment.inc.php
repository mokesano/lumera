<?php
declare(strict_types=1);

/**
 * @file classes/submission/reviewAssignment/ReviewAssignment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewAssignment
 * @ingroup submission
 * @see ReviewAssignmentDAO
 *
 * @brief Describes review assignment properties.
 */

import('lib.pkp.classes.submission.reviewAssignment.PKPReviewAssignment');

class ReviewAssignment extends PKPReviewAssignment {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReviewAssignment() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
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
     * Get ID of article.
     * @deprecated Use getSubmissionId() instead.
     * @return int
     */
    public function getArticleId() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return (int) $this->getSubmissionId();
    }

    /**
     * Set ID of article.
     * @deprecated Use setSubmissionId() instead.
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        $this->setSubmissionId((int) $articleId);
    }

    /**
     * Get the date the review was cancelled.
     * @return string|null
     */
    public function getDateCancelled() {
        $date = $this->getData('dateCancelled');
        return $date !== null ? (string) $date : null;
    }

    /**
     * Get the declined flag.
     * @return int|null
     */
    public function getDeclined() {
        $declined = $this->getData('declined');
        return $declined !== null ? (int) $declined : null;
    }

    /**
     * Get an associative array matching reviewer recommendation codes with locale strings.
     * (Includes default '' => "Choose One" string.)
     * @return array
     */
    public static function getReviewerRecommendationOptions() {
        import('classes.submission.reviewer.ReviewerSubmission');

        static $reviewerRecommendationOptions = [
            '' => 'common.chooseOne',
            SUBMISSION_REVIEWER_RECOMMENDATION_ACCEPT => 'reviewer.article.decision.accept',
            SUBMISSION_REVIEWER_RECOMMENDATION_PENDING_REVISIONS => 'reviewer.article.decision.pendingRevisions',
            SUBMISSION_REVIEWER_RECOMMENDATION_RESUBMIT_HERE => 'reviewer.article.decision.resubmitHere',
            SUBMISSION_REVIEWER_RECOMMENDATION_RESUBMIT_ELSEWHERE => 'reviewer.article.decision.resubmitElsewhere',
            SUBMISSION_REVIEWER_RECOMMENDATION_DECLINE => 'reviewer.article.decision.decline',
            SUBMISSION_REVIEWER_RECOMMENDATION_SEE_COMMENTS => 'reviewer.article.decision.seeComments'
        ];
        
        return $reviewerRecommendationOptions;
    }

    /**
     * Get an associative array matching reviewer rating codes with locale strings.
     * @return array
     */
    public static function getReviewerRatingOptions() {
        static $reviewerRatingOptions = [
            SUBMISSION_REVIEWER_RATING_VERY_GOOD => 'editor.article.reviewerRating.veryGood',
            SUBMISSION_REVIEWER_RATING_GOOD => 'editor.article.reviewerRating.good',
            SUBMISSION_REVIEWER_RATING_AVERAGE => 'editor.article.reviewerRating.average',
            SUBMISSION_REVIEWER_RATING_POOR => 'editor.article.reviewerRating.poor',
            SUBMISSION_REVIEWER_RATING_VERY_POOR => 'editor.article.reviewerRating.veryPoor'
        ];
        
        return $reviewerRatingOptions;
    }
    
}
?>
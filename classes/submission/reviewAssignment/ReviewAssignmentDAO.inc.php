<?php
declare(strict_types=1);

/**
 * @file classes/submission/reviewAssignment/ReviewAssignmentDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewAssignmentDAO
 * @ingroup submission
 * @see ReviewAssignment
 *
 * @brief Class for DAO relating reviewers to articles.
 */

import('classes.submission.reviewAssignment.ReviewAssignment');
import('lib.pkp.classes.submission.reviewAssignment.PKPReviewAssignmentDAO');
import('classes.article.ArticleFileDAO');
import('classes.article.SuppFileDAO');
import('classes.article.ArticleCommentDAO');
import('classes.user.UserDAO');

class ReviewAssignmentDAO extends PKPReviewAssignmentDAO {
    
    /** 
     * @var ArticleFileDAO 
     * @method ArticleFile _returnArticleFileFromRow(array $row)
     */
    public $articleFileDao;

    /** @var SuppFileDAO */
    public $suppFileDao;

    /** @var ArticleCommentDAO */
    public $articleCommentDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $this->suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $this->articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReviewAssignmentDAO() {
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
     * Retrieve the review file ID for a submission.
     * @param int $submissionId
     * @return int|null
     */
    public function _getSubmissionReviewFileId($submissionId) {
        $result = $this->retrieve(
            'SELECT review_file_id FROM articles WHERE article_id = ?',
            [(int) $submissionId]
        );
        
        $returner = null;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = isset($row['review_file_id']) ? (int) $row['review_file_id'] : null;
        }
        if ($result) {
            $result->Close();
        }
        
        return $returner;
    }

    /**
     * Retrieve a review assignment by review assignment ID.
     * @param int $reviewId
     * @return ReviewAssignment|null
     * @deprecated Use getById() instead.
     */
    public function getReviewAssignmentById($reviewId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getById((int) $reviewId);
    }

    /**
     * Retrieve all review assignments for a specific article.
     * @param int $articleId
     * @param int|null $round
     * @return array
     * @deprecated Use getBySubmissionId() instead.
     */
    public function getReviewAssignmentsByArticleId($articleId, $round = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getBySubmissionId((int) $articleId, $round);
    }

    /**
     * Retrieve all review assignments for a specific reviewer.
     * @param int $userId
     * @return array
     * @deprecated Use getByUserId() instead.
     */
    public function getReviewAssignmentsByUserId($userId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getByUserId((int) $userId);
    }

    /**
     * Retrieve all review assignments for a specific review form.
     * @param int $reviewFormId
     * @return array
     * @deprecated Use getByReviewFormId() instead.
     */
    public function getReviewAssignmentsByReviewFormId($reviewFormId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getByReviewFormId((int) $reviewFormId);
    }

    /**
     * Retrieve the review file for an article for each round.
     * @param int $articleId
     * @return array
     */
    public function getReviewFilesByRound($articleId) {
        $returner = [];

        $result = $this->retrieve(
            'SELECT f.*, r.round as round
            FROM    review_rounds r,
                article_files f,
                articles a
            WHERE   a.article_id = r.submission_id AND
                r.submission_id = ? AND
                r.submission_id = f.article_id AND
                f.file_id = a.review_file_id AND
                f.revision = r.review_revision',
            [(int) $articleId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                // @method annotation on property satisfies linter for protected legacy method
                $returner[(int) $row['round']] = $this->articleFileDao->_returnArticleFileFromRow($row);
                $result->MoveNext();
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve all author-viewable reviewer files for an article, grouped by round and reviewer.
     * @param int $articleId
     * @return array
     */
    public function getAuthorViewableFilesByRound($articleId) {
        $files = [];

        $result = $this->retrieve(
            'SELECT f.*, r.reviewer_id, r.review_id
            FROM    review_assignments r,
                article_files f
            WHERE   reviewer_file_id = file_id AND
                viewable = 1 AND
                r.submission_id = ?
            ORDER BY r.round, r.reviewer_id, r.review_id',
            [(int) $articleId]
        );

        $thisReviewerId = null;
        $reviewerIndex = 0;

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $round = (int) $row['round'];
                $reviewerId = (int) $row['reviewer_id'];
                $reviewId = (int) $row['review_id'];
                
                if (!isset($files[$round]) || !is_array($files[$round])) {
                    $files[$round] = [];
                    $thisReviewerId = $reviewerId;
                    $reviewerIndex = 0;
                } elseif ($thisReviewerId !== $reviewerId) {
                    $thisReviewerId = $reviewerId;
                    $reviewerIndex++;
                }

                $thisArticleFile = $this->articleFileDao->_returnArticleFileFromRow($row);
                
                if (!isset($files[$round][$reviewerIndex])) {
                    $files[$round][$reviewerIndex] = [];
                }
                if (!isset($files[$round][$reviewerIndex][$reviewId])) {
                    $files[$round][$reviewerIndex][$reviewId] = [];
                }
                $files[$round][$reviewerIndex][$reviewId][] = $thisArticleFile;
                
                $result->MoveNext();
            }
            $result->Close();
        }

        return $files;
    }

    /**
     * Delete review assignments by article.
     * @param int $articleId
     * @return bool
     * @deprecated Use deleteBySubmissionId() instead.
     */
    public function deleteReviewAssignmentsByArticle($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return (bool) $this->deleteBySubmissionId((int) $articleId);
    }

    /**
     * Retrieve the average quality ratings and the number of ratings for all reviewers in a journal.
     * @param int $journalId
     * @return array
     */
    public function getAverageQualityRatings($journalId) {
        $averageQualityRatings = [];
        $roleIdReviewer = defined('ROLE_ID_REVIEWER') ? ROLE_ID_REVIEWER : 4096;
        
        $initResult = $this->retrieve(
            'SELECT user_id FROM roles WHERE journal_id = ? AND role_id = ?',
            [(int) $journalId, (int) $roleIdReviewer]
        );
        
        if ($initResult) {
            while (!$initResult->EOF) {
                $row = $initResult->GetRowAssoc(false);
                $averageQualityRatings[(int) $row['user_id']] = ['average' => 0.0, 'count' => 0];
                $initResult->MoveNext();
            }
            $initResult->Close();
        }

        $result = $this->retrieve(
            'SELECT r.reviewer_id, AVG(r.quality) AS average, COUNT(r.quality) AS count
            FROM    review_assignments r, articles a
            WHERE   r.submission_id = a.article_id AND
                a.journal_id = ?
            GROUP BY r.reviewer_id',
            [(int) $journalId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $averageQualityRatings[(int) $row['reviewer_id']] = [
                    'average' => (float) $row['average'], 
                    'count' => (int) $row['count']
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $averageQualityRatings;
    }

    /**
     * Retrieve the number of completed reviews for all reviewers in a journal.
     * @param int $journalId
     * @return array
     */
    public function getCompletedReviewCounts($journalId) {
        $returner = [];
        $roleIdReviewer = defined('ROLE_ID_REVIEWER') ? ROLE_ID_REVIEWER : 4096;
        
        $initResult = $this->retrieve(
            'SELECT user_id FROM roles WHERE journal_id = ? AND role_id = ?',
            [(int) $journalId, (int) $roleIdReviewer]
        );
        
        if ($initResult) {
            while (!$initResult->EOF) {
                $row = $initResult->GetRowAssoc(false);
                $returner[(int) $row['user_id']] = 0;
                $initResult->MoveNext();
            }
            $initResult->Close();
        }

        $result = $this->retrieve(
            'SELECT r.reviewer_id, COUNT(r.review_id) AS count
            FROM    review_assignments r, articles a
            WHERE   r.submission_id = a.article_id AND
                a.journal_id = ? AND
                r.date_completed IS NOT NULL AND
                r.cancelled = 0
            GROUP BY r.reviewer_id',
            [(int) $journalId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $returner[(int) $row['reviewer_id']] = (int) $row['count'];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return ReviewAssignment
     */
    public function newDataObject() {
        $reviewAssignment = new ReviewAssignment();
        $reviewAssignment->setStageId(1);
        return $reviewAssignment;
    }

    /**
     * Internal function to return a ReviewAssignment object from a database row.
     * @param array $row
     * @return ReviewAssignment
     */
    public function _fromRow($row) {
        $reviewAssignment = parent::_fromRow($row);
        $reviewFileId = $this->_getSubmissionReviewFileId((int) $reviewAssignment->getSubmissionId());
        $reviewAssignment->setReviewFileId($reviewFileId);

        // Files
        $reviewAssignment->setReviewFile($this->articleFileDao->getArticleFile($reviewFileId, $row['review_revision'] ?? null));
        $reviewAssignment->setReviewerFile($this->articleFileDao->getArticleFile((int) ($row['reviewer_file_id'] ?? 0)));
        $reviewAssignment->setReviewerFileRevisions($this->articleFileDao->getArticleFileRevisions((int) ($row['reviewer_file_id'] ?? 0)));
        $reviewAssignment->setSuppFiles($this->suppFileDao->getSuppFilesByArticle((int) ($row['submission_id'] ?? 0)));

        // Comments
        $reviewAssignment->setMostRecentPeerReviewComment(
            $this->articleCommentDao->getMostRecentArticleComment(
                (int) ($row['submission_id'] ?? 0), 
                COMMENT_TYPE_PEER_REVIEW, 
                (int) ($row['review_id'] ?? 0)
            )
        );

        $tempAssignment = $reviewAssignment;
        $tempRow = $row;
        HookRegistry::dispatch('ReviewAssignmentDAO::_fromRow', [&$tempAssignment, &$tempRow]);
        
        return $reviewAssignment;
    }

    /**
     * Retrieve the JOIN clause for review rounds.
     * @return string
     */
    public function getReviewRoundJoin() {
        return 'r.submission_id = r2.submission_id AND r.round = r2.round';
    }
    
    /**
     * Retrieve detailed data of active reviewers for a specific article, excluding cancelled or declined assignments.
     * @param int $articleId
     * @return array
     */
    public function getReviewersWithDetails($articleId) {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $reviewersData = [];
        
        $reviewAssignments = $this->getBySubmissionId((int) $articleId);
        
        foreach ($reviewAssignments as $assignment) {
            // Filter: Exclude cancelled or declined assignments
            if ($assignment->getDateCancelled() || $assignment->getDeclined()) {
                continue; 
            }
    
            $reviewer = $userDao->getById((int) $assignment->getReviewerId());
            if ($reviewer instanceof User) {
                $assignment->setData('reviewerUser', $reviewer);
                $reviewersData[] = $assignment;
            }
        }
        return $reviewersData;
    }

}
?>
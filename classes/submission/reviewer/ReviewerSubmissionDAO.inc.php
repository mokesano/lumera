<?php
declare(strict_types=1);

/**
 * @file classes/submission/reviewer/ReviewerSubmissionDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerSubmissionDAO
 * @ingroup submission
 * @see ReviewerSubmission
 *
 * @brief Operations for retrieving and modifying ReviewerSubmission objects.
 */

import('classes.submission.reviewer.ReviewerSubmission');

class ReviewerSubmissionDAO extends DAO {
    
    /** 
     * @var ArticleDAO 
     * @method void _articleFromRow(ReviewerSubmission $reviewerSubmission, array $row)
     */
    protected $_articleDao;

    /** @var AuthorDAO */
    protected $_authorDao;

    /** @var UserDAO */
    protected $_userDao;

    /** @var ReviewAssignmentDAO */
    protected $_reviewAssignmentDao;

    /** 
     * @var EditAssignmentDAO 
     * @method ItemIterator getEditAssignmentsByArticleId(int $articleId)
     */
    protected $_editAssignmentDao;

    /** @var ArticleFileDAO */
    protected $_articleFileDao;

    /** @var SuppFileDAO */
    protected $_suppFileDao;

    /** @var ArticleCommentDAO */
    protected $_articleCommentDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_articleDao = DAORegistry::getDAO('ArticleDAO');
        $this->_authorDao = DAORegistry::getDAO('AuthorDAO');
        $this->_userDao = DAORegistry::getDAO('UserDAO');
        $this->_reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        $this->_editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        $this->_articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        $this->_suppFileDao = DAORegistry::getDAO('SuppFileDAO');
        $this->_articleCommentDao = DAORegistry::getDAO('ArticleCommentDAO');
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ReviewerSubmissionDAO() {
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
     * Retrieve a reviewer submission by review ID.
     * @param int $reviewId
     * @return ReviewerSubmission|null
     */
    public function getReviewerSubmission($reviewId) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        
        $result = $this->retrieve(
            'SELECT a.*,
                r.*,
                r2.review_revision,
                u.first_name, u.last_name,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM    articles a
                LEFT JOIN review_assignments r ON (a.article_id = r.submission_id)
                LEFT JOIN sections s ON (s.section_id = a.section_id)
                LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.round = r2.round)
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE   r.review_id = ?',
            [
                'title',
                $primaryLocale,
                'title',
                $locale,
                'abbrev',
                $primaryLocale,
                'abbrev',
                $locale,
                (int) $reviewId
            ]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = $this->_returnReviewerSubmissionFromRow($row);
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Internal function to return a ReviewerSubmission object from a row.
     * @param array $row
     * @return ReviewerSubmission
     */
    public function _returnReviewerSubmissionFromRow($row) {
        $reviewerSubmission = new ReviewerSubmission();

        // Editor Assignment
        // [WIZDAM FIX] @method annotation on property satisfies linter for this specific DAO method
        $editAssignments = $this->_editAssignmentDao->getEditAssignmentsByArticleId((int) $row['article_id']);
        $reviewerSubmission->setEditAssignments($editAssignments->toArray());

        // Files
        $reviewerSubmission->setSubmissionFile($this->_articleFileDao->getArticleFile((int) $row['submission_file_id']));
        $reviewerSubmission->setRevisedFile($this->_articleFileDao->getArticleFile((int) $row['revised_file_id']));
        $reviewerSubmission->setSuppFiles($this->_suppFileDao->getSuppFilesByArticle((int) $row['article_id']));
        $reviewerSubmission->setReviewFile($this->_articleFileDao->getArticleFile((int) $row['review_file_id']));
        $reviewerSubmission->setReviewerFile($this->_articleFileDao->getArticleFile((int) $row['reviewer_file_id']));
        $reviewerSubmission->setReviewerFileRevisions($this->_articleFileDao->getArticleFileRevisions((int) $row['reviewer_file_id']));

        // Comments
        $reviewerSubmission->setMostRecentPeerReviewComment(
            $this->_articleCommentDao->getMostRecentArticleComment(
                (int) $row['article_id'], 
                COMMENT_TYPE_PEER_REVIEW, 
                (int) $row['review_id']
            )
        );

        // Editor Decisions
        $currentRound = (int) $row['current_round'];
        for ($i = 1; $i <= $currentRound; $i++) {
            $reviewerSubmission->setDecisions($this->getEditorDecisions((int) $row['article_id'], $i), $i);
        }

        // Review Assignment
        $reviewerSubmission->setReviewId((int) $row['review_id']);
        $reviewerSubmission->setReviewerId((int) $row['reviewer_id']);
        $reviewerSubmission->setReviewerFullName((string) $row['first_name'] . ' ' . (string) $row['last_name']);
        $reviewerSubmission->setCompetingInterests((string) $row['competing_interests']);
        $reviewerSubmission->setRecommendation((string) $row['recommendation']);
        $reviewerSubmission->setDateAssigned($this->datetimeFromDB($row['date_assigned']));
        $reviewerSubmission->setDateNotified($this->datetimeFromDB($row['date_notified']));
        $reviewerSubmission->setDateConfirmed($this->datetimeFromDB($row['date_confirmed']));
        $reviewerSubmission->setDateCompleted($this->datetimeFromDB($row['date_completed']));
        $reviewerSubmission->setDateAcknowledged($this->datetimeFromDB($row['date_acknowledged']));
        $reviewerSubmission->setDateDue($this->datetimeFromDB($row['date_due']));

        // Strict Type Casting to prevent Logic Errors in View
        $reviewerSubmission->setDeclined((int) $row['declined']); 
        $reviewerSubmission->setReplaced((int) $row['replaced']);
        $reviewerSubmission->setCancelled(isset($row['cancelled']) && (int) $row['cancelled'] === 1 ? 1 : 0);
        
        $reviewerSubmission->setReviewerFileId((int) $row['reviewer_file_id']);
        $reviewerSubmission->setQuality((int) $row['quality']);
        $reviewerSubmission->setRound((int) $row['round']);
        $reviewerSubmission->setReviewFileId((int) $row['review_file_id']);
        $reviewerSubmission->setReviewRevision((int) $row['review_revision']);

        // Article attributes
        // [WIZDAM FIX] @method annotation on property satisfies linter for this protected legacy method
        $this->_articleDao->_articleFromRow($reviewerSubmission, $row);

        $tempSubmission = $reviewerSubmission;
        $tempRow = $row;
        HookRegistry::dispatch('ReviewerSubmissionDAO::_returnReviewerSubmissionFromRow', [&$tempSubmission, &$tempRow]);

        return $reviewerSubmission;
    }

    /**
     * Update an existing review submission.
     * @param ReviewerSubmission $reviewerSubmission
     * @return bool
     */
    public function updateReviewerSubmission($reviewerSubmission) {
        if (!($reviewerSubmission instanceof ReviewerSubmission)) {
            return false;
        }

        return (bool) $this->update(
            sprintf('UPDATE review_assignments
                SET submission_id = ?,
                    reviewer_id = ?,
                    round = ?,
                    competing_interests = ?,
                    recommendation = ?,
                    declined = ?,
                    replaced = ?,
                    cancelled = ?,
                    date_assigned = %s,
                    date_notified = %s,
                    date_confirmed = %s,
                    date_completed = %s,
                    date_acknowledged = %s,
                    date_due = %s,
                    reviewer_file_id = ?,
                    quality = ?
                WHERE   review_id = ?',
                $this->datetimeToDB($reviewerSubmission->getDateAssigned()),
                $this->datetimeToDB($reviewerSubmission->getDateNotified()),
                $this->datetimeToDB($reviewerSubmission->getDateConfirmed()),
                $this->datetimeToDB($reviewerSubmission->getDateCompleted()),
                $this->datetimeToDB($reviewerSubmission->getDateAcknowledged()),
                $this->datetimeToDB($reviewerSubmission->getDateDue())
            ),
            [
                (int) $reviewerSubmission->getId(),
                (int) $reviewerSubmission->getReviewerId(),
                (int) $reviewerSubmission->getRound(),
                (string) $reviewerSubmission->getCompetingInterests(),
                (string) $reviewerSubmission->getRecommendation(),
                (int) $reviewerSubmission->getDeclined(),
                (int) $reviewerSubmission->getReplaced(),
                (int) $reviewerSubmission->getCancelled(),
                (int) $reviewerSubmission->getReviewerFileId(),
                (int) $reviewerSubmission->getQuality(),
                (int) $reviewerSubmission->getReviewId()
            ]
        );
    }

    /**
     * Retrieve all submissions for a reviewer of a specific journal.
     * @param int $reviewerId
     * @param int $journalId
     * @param bool $active
     * @param mixed $rangeInfo
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory
     */
    public function getReviewerSubmissionsByReviewerId($reviewerId, $journalId, $active = true, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        $primaryLocale = AppLocale::getPrimaryLocale();
        $locale = AppLocale::getLocale();
        
        $sql = 'SELECT  a.*,
                r.*,
                r2.review_revision,
                u.first_name, u.last_name,
                COALESCE(atl.setting_value, atpl.setting_value) AS submission_title,
                COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
                COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
            FROM    articles a
                LEFT JOIN review_assignments r ON (a.article_id = r.submission_id)
                LEFT JOIN article_settings atpl ON (atpl.article_id = a.article_id AND atpl.setting_name = ? AND atpl.locale = a.locale)
                LEFT JOIN article_settings atl ON (atl.article_id = a.article_id AND atl.setting_name = ? AND atl.locale = ?)
                LEFT JOIN sections s ON (s.section_id = a.section_id)
                LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.round = r2.round)
                LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
                LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
                LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
                LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
            WHERE   a.journal_id = ? AND
                r.reviewer_id = ? AND
                r.date_notified IS NOT NULL';

        if ($active) {
            $sql .= ' AND r.date_completed IS NULL AND r.declined <> 1 AND (r.cancelled = 0 OR r.cancelled IS NULL) AND a.status = ' . STATUS_QUEUED;
        } else {
            $sql .= ' AND (r.date_completed IS NOT NULL OR r.cancelled = 1 OR r.declined = 1 OR a.status <> ' . STATUS_QUEUED . ')';
        }

        if ($sortBy !== null && $sortBy !== '') {
            $sql .= ' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection);
        }

        $result = $this->retrieveRange(
            $sql,
            [
                'cleanTitle', // Article title
                'cleanTitle',
                $locale,
                'title', // Section title
                $primaryLocale,
                'title',
                $locale,
                'abbrev', // Section abbreviation
                $primaryLocale,
                'abbrev',
                $locale,
                (int) $journalId,
                (int) $reviewerId
            ],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnReviewerSubmissionFromRow');
    }

    /**
     * Retrieve the count of active and complete assignments for a reviewer.
     * @param int $reviewerId
     * @param int $journalId
     * @return array
     */
    public function getSubmissionsCount($reviewerId, $journalId) {
        $submissionsCount = [0 => 0, 1 => 0];

        $sql = 'SELECT r.date_completed, r.declined, r.cancelled, a.status
            FROM    articles a
                LEFT JOIN review_assignments r ON (a.article_id = r.submission_id)
                LEFT JOIN sections s ON (s.section_id = a.section_id)
                LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.round = r2.round)
            WHERE   a.journal_id = ? AND
                r.reviewer_id = ? AND
                r.date_notified IS NOT NULL';

        $result = $this->retrieve($sql, [(int) $journalId, (int) $reviewerId]);

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                
                $isCompleted = $row['date_completed'] !== null;
                $isDeclined = (int) $row['declined'] === 1;
                $isCancelled = (int) $row['cancelled'] === 1;
                $isQueued = (int) $row['status'] === STATUS_QUEUED;

                if (!$isCompleted && !$isDeclined && !$isCancelled && $isQueued) {
                    $submissionsCount[0]++;
                } else {
                    $submissionsCount[1]++;
                }
                $result->MoveNext();
            }
            $result->Close();
        }

        return $submissionsCount;
    }

    /**
     * Retrieve the editor decisions for a review round of an article.
     * @param int $articleId
     * @param int|null $round
     * @return array
     */
    public function getEditorDecisions($articleId, $round = null) {
        $decisions = [];

        if ($round === null) {
            $result = $this->retrieve(
                'SELECT edit_decision_id, editor_id, decision, date_decided FROM edit_decisions WHERE article_id = ? ORDER BY date_decided ASC',
                [(int) $articleId]
            );
        } else {
            $result = $this->retrieve(
                'SELECT edit_decision_id, editor_id, decision, date_decided FROM edit_decisions WHERE article_id = ? AND round = ? ORDER BY date_decided ASC',
                [(int) $articleId, (int) $round]
            );
        }

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $decisions[] = [
                    'editDecisionId' => (int) $row['edit_decision_id'],
                    'editorId' => (int) $row['editor_id'],
                    'decision' => (int) $row['decision'],
                    'dateDecided' => $this->datetimeFromDB($row['date_decided'])
                ];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $decisions;
    }

    /**
     * Map a column heading value to a database value for sorting.
     * @param string $heading
     * @return string|null
     */
    public function getSortMapping($heading) {
        switch ($heading) {
            case 'id': return 'a.article_id';
            case 'assignDate': return 'r.date_assigned';
            case 'dueDate': return 'r.date_due';
            case 'section': return 'section_abbrev';
            case 'title': return 'submission_title';
            case 'round': return 'r.round';
            case 'review': return 'r.recommendation';
            default: return null;
        }
    }
    
}
?>
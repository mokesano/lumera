<?php
declare(strict_types=1);

/**
 * @file classes/submission/reviewAssignment/PKPReviewAssignmentDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPReviewAssignmentDAO
 * @ingroup submission
 * @see PKPReviewAssignment
 *
 * @brief Class for DAO relating reviewers to submissions.
 */

import('lib.pkp.classes.submission.reviewAssignment.PKPReviewAssignment');

class PKPReviewAssignmentDAO extends DAO {
    
    /** @var UserDAO */
    protected $_userDao;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_userDao = DAORegistry::getDAO('UserDAO');
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function PKPReviewAssignmentDAO() {
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
    // Template methods.
    //

    /**
     * Get the review_rounds join string. Must be implemented by subclasses.
     * @return string|false
     */
    public function getReviewRoundJoin() {
        return false;
    }

    //
    // Public methods.
    //

    /**
     * Retrieve a review assignment by reviewer and submission.
     * @param int $submissionId
     * @param int $reviewerId
     * @param int $round
     * @param int|null $stageId optional
     * @return PKPReviewAssignment|null
     */
    public function getReviewAssignment($submissionId, $reviewerId, $round, $stageId = null) {
        $params = [
            (int) $submissionId,
            (int) $reviewerId,
            (int) $round
        ];
        if ($stageId !== null) {
            $params[] = (int) $stageId;
        }

        $result = $this->retrieve(
            'SELECT r.*, r2.review_revision, u.first_name, u.last_name
            FROM    review_assignments r
                JOIN users u ON (r.reviewer_id = u.user_id)
                LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.round = r2.round)
            WHERE    r.submission_id = ? AND
                r.reviewer_id = ? AND
                r.cancelled <> 1 AND
                r.round = ?' . ($stageId !== null ? ' AND r.stage_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve a review assignment by review assignment ID.
     * @param int $reviewId
     * @return PKPReviewAssignment|null
     */
    public function getById($reviewId) {
        $reviewRoundJoinString = $this->getReviewRoundJoin();
        if ($reviewRoundJoinString) {
            $result = $this->retrieve(
                'SELECT    r.*, r2.review_revision, u.first_name, u.last_name
                FROM    review_assignments r
                    LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                    LEFT JOIN review_rounds r2 ON (' . $reviewRoundJoinString . ')
                WHERE    r.review_id = ?',
                [(int) $reviewId]
            );

            $returner = null;
            if ($result && !$result->EOF) {
                $returner = $this->_fromRow($result->GetRowAssoc(false));
            }
            if ($result) {
                $result->Close();
            }

            return $returner;
        }
        assert(false);
        return null;
    }

    /**
     * Retrieve all incomplete review assignments for all journals/conferences/presses.
     * @return array
     */
    public function getIncompleteReviewAssignments() {
        $reviewAssignments = [];
        $reviewRoundJoinString = $this->getReviewRoundJoin();
        
        if ($reviewRoundJoinString) {
            $result = $this->retrieve(
                'SELECT    r.*, r2.review_revision, u.first_name, u.last_name
                FROM    review_assignments r
                    LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                    LEFT JOIN review_rounds r2 ON (' . $reviewRoundJoinString . ')
                WHERE' . $this->getIncompleteReviewAssignmentsWhereString() .
                ' ORDER BY r.submission_id'
            );

            if ($result) {
                while (!$result->EOF) {
                    $reviewAssignments[] = $this->_fromRow($result->GetRowAssoc(false));
                    $result->MoveNext();
                }
                $result->Close();
            }
        } else {
            assert(false);
        }

        return $reviewAssignments;
    }

    /**
     * Get the WHERE SQL string to filter incomplete review assignments.
     * @return string
     */
    public function getIncompleteReviewAssignmentsWhereString() {
        return ' (r.cancelled IS NULL OR r.cancelled = 0) AND
        r.date_notified IS NOT NULL AND
        r.date_completed IS NULL AND
        r.declined <> 1';
    }

    /**
     * Retrieve all review assignments for a specific submission.
     * @param int $submissionId
     * @param int|null $round optional
     * @param int|null $stageId optional
     * @return array
     */
    public function getBySubmissionId($submissionId, $round = null, $stageId = null) {
        $reviewAssignments = [];

        $query = 'SELECT r.*, r2.review_revision, u.first_name, u.last_name
            FROM    review_assignments r
                LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                LEFT JOIN review_rounds r2 ON (r.submission_id = r2.submission_id AND r.round = r2.round AND r.stage_id = r2.stage_id)
            WHERE    r.submission_id = ?';

        $orderBy = ' ORDER BY review_id';
        $queryParams = [(int) $submissionId];

        if ($round !== null) {
            $query .= ' AND r.round = ?';
            $queryParams[] = (int) $round;
        } else {
            $orderBy .= ', r.round';
        }

        if ($stageId !== null) {
            $query .= ' AND r.stage_id = ?';
            $queryParams[] = (int) $stageId;
        } else {
            $orderBy .= ', r.stage_id';
        }

        $query .= $orderBy;

        $result = $this->retrieve($query, $queryParams);

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                // [WIZDAM FIX] Use $row['review_id'] instead of $result->fields['review_id'] to satisfy linter
                $reviewAssignments[(int) $row['review_id']] = $this->_fromRow($row);
                $result->MoveNext();
            }
            $result->Close();
        }

        return $reviewAssignments;
    }

    /**
     * Retrieve the IDs of all reviewers assigned to a specific submission.
     * @param int $submissionId
     * @param int|null $round optional
     * @param int|null $stageId optional
     * @return array
     */
    public function getReviewerIdsBySubmissionId($submissionId, $round = null, $stageId = null) {
        $query = 'SELECT r.reviewer_id
                FROM    review_assignments r
                WHERE r.submission_id = ?';

        $queryParams = [(int) $submissionId];

        if ($round !== null) {
            $query .= ' AND r.round = ?';
            $queryParams[] = (int) $round;
        }

        if ($stageId !== null) {
            $query .= ' AND r.stage_id = ?';
            $queryParams[] = (int) $stageId;
        }

        $result = $this->retrieve($query, $queryParams);

        $reviewerIds = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $reviewerIds[] = (int) $row['reviewer_id'];
                $result->MoveNext();
            }
            $result->Close();
        }

        return $reviewerIds;
    }

    /**
     * Retrieve all review assignments for a specific reviewer.
     * @param int $userId
     * @return array
     */
    public function getByUserId($userId) {
        $reviewAssignments = [];
        $reviewRoundJoinString = $this->getReviewRoundJoin();

        if ($reviewRoundJoinString) {
            $result = $this->retrieve(
                'SELECT    r.*, r2.review_revision, u.first_name, u.last_name
                FROM    review_assignments r
                    LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                    LEFT JOIN review_rounds r2 ON (' . $reviewRoundJoinString . ')
                WHERE    r.reviewer_id = ?
                ORDER BY round, review_id',
                [(int) $userId]
            );

            if ($result) {
                while (!$result->EOF) {
                    $reviewAssignments[] = $this->_fromRow($result->GetRowAssoc(false));
                    $result->MoveNext();
                }
                $result->Close();
            }
        } else {
            assert(false);
        }

        return $reviewAssignments;
    }

    /**
     * Retrieve all review assignments for a specific review form.
     * @param int $reviewFormId
     * @return array
     */
    public function getByReviewFormId($reviewFormId) {
        $reviewAssignments = [];
        $reviewRoundJoinString = $this->getReviewRoundJoin();

        if ($reviewRoundJoinString) {
            $result = $this->retrieve(
                'SELECT    r.*, r2.review_revision, u.first_name, u.last_name
                FROM    review_assignments r
                    LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                    LEFT JOIN review_rounds r2 ON (' . $reviewRoundJoinString . ')
                WHERE    r.review_form_id = ?
                ORDER BY round, review_id',
                [(int) $reviewFormId]
            );

            if ($result) {
                while (!$result->EOF) {
                    $reviewAssignments[] = $this->_fromRow($result->GetRowAssoc(false));
                    $result->MoveNext();
                }
                $result->Close();
            }
        } else {
            assert(false);
        }

        return $reviewAssignments;
    }

    /**
     * Retrieve all cancelled or declined review assignments for a specific submission.
     * @param int $submissionId
     * @return array
     */
    public function getCancelsAndRegrets($submissionId) {
        $reviewAssignments = [];
        $reviewRoundJoinString = $this->getReviewRoundJoin();

        if ($reviewRoundJoinString) {
            $result = $this->retrieve(
                'SELECT    r.*, r2.review_revision, u.first_name, u.last_name
                FROM    review_assignments r
                    LEFT JOIN users u ON (r.reviewer_id = u.user_id)
                    LEFT JOIN review_rounds r2 ON (' . $reviewRoundJoinString . ')
                WHERE    r.submission_id = ? AND
                    (r.cancelled = 1 OR r.declined = 1)
                ORDER BY round, review_id',
                [(int) $submissionId]
            );

            if ($result) {
                while (!$result->EOF) {
                    $reviewAssignments[] = $this->_fromRow($result->GetRowAssoc(false));
                    $result->MoveNext();
                }
                $result->Close();
            }
        } else {
            assert(false);
        }

        return $reviewAssignments;
    }

    /**
     * Determine the order of active reviews for the given round of the given submission.
     * @param int $submissionId
     * @param int $round
     * @return array Associating review ID with number
     */
    public function getReviewIndexesForRound($submissionId, $round) {
        $result = $this->retrieve(
            'SELECT    review_id
            FROM    review_assignments
            WHERE    submission_id = ? AND
                round = ? AND
                (cancelled = 0 OR cancelled IS NULL)
            ORDER BY review_id',
            [(int) $submissionId, (int) $round]
        );

        $index = 0;
        $returner = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $returner[(int) $row['review_id']] = $index++;
                $result->MoveNext();
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve the most recent last modified date for all review assignments for each round of a submission.
     * @param int $submissionId
     * @return array Associating round with most recent last modified date
     */
    public function getLastModifiedByRound($submissionId) {
        $returner = [];

        $result = $this->retrieve(
            'SELECT    round, MAX(last_modified) as last_modified
            FROM    review_assignments
            WHERE    submission_id = ?
            GROUP BY round',
            [(int) $submissionId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $returner[(int) $row['round']] = $this->datetimeFromDB($row['last_modified']);
                $result->MoveNext();
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve the first notified date from all review assignments for each round of a submission.
     * @param int $submissionId
     * @return array Associative array of ($round_num => $earliest_date_of_notification)
     */
    public function getEarliestNotificationByRound($submissionId) {
        $returner = [];

        $result = $this->retrieve(
            'SELECT    round, MIN(date_notified) as earliest_date
            FROM    review_assignments
            WHERE    submission_id = ?
            GROUP BY round',
            [(int) $submissionId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $returner[(int) $row['round']] = $this->datetimeFromDB($row['earliest_date']);
                $result->MoveNext();
            }
            $result->Close();
        }

        return $returner;
    }

    /**
     * Insert a new Review Assignment.
     * @deprecated Use insertObject() instead.
     * @param PKPReviewAssignment $reviewAssignment
     * @return int
     */
    public function insertReviewAssignment($reviewAssignment) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->insertObject($reviewAssignment);
    }

    /**
     * Insert a new Review Assignment.
     * @param PKPReviewAssignment $reviewAssignment
     * @return int
     */
    public function insertObject($reviewAssignment) {
        $this->update(
            sprintf('INSERT INTO review_assignments (
                submission_id,
                reviewer_id,
                stage_id,
                review_method,
                regret_message,
                round,
                competing_interests,
                recommendation,
                declined, replaced, cancelled,
                date_assigned, date_notified, date_confirmed,
                date_completed, date_acknowledged, date_due, date_response_due,
                reviewer_file_id,
                quality, date_rated,
                last_modified,
                date_reminded, reminder_was_automatic,
                review_form_id,
                review_round_id,
                unconsidered
                ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, %s, %s, %s, %s, %s, ?, ?, %s, %s, %s, ?, ?, ?, ?
                )',
                $this->datetimeToDB($reviewAssignment->getDateAssigned()), 
                $this->datetimeToDB($reviewAssignment->getDateNotified()), 
                $this->datetimeToDB($reviewAssignment->getDateConfirmed()), 
                $this->datetimeToDB($reviewAssignment->getDateCompleted()), 
                $this->datetimeToDB($reviewAssignment->getDateAcknowledged()), 
                $this->datetimeToDB($reviewAssignment->getDateDue()), 
                $this->datetimeToDB($reviewAssignment->getDateResponseDue()), 
                $this->datetimeToDB($reviewAssignment->getDateRated()), 
                $this->datetimeToDB($reviewAssignment->getLastModified()), 
                $this->datetimeToDB($reviewAssignment->getDateReminded())
            ),
            [
                (int) $reviewAssignment->getSubmissionId(),
                (int) $reviewAssignment->getReviewerId(),
                (int) $reviewAssignment->getStageId(),
                (int) $reviewAssignment->getReviewMethod(),
                (string) $reviewAssignment->getRegretMessage(),
                max((int) $reviewAssignment->getRound(), 1),
                (string) $reviewAssignment->getCompetingInterests(),
                (string) $reviewAssignment->getRecommendation(),
                (int) $reviewAssignment->getDeclined(),
                (int) $reviewAssignment->getReplaced(),
                (int) $reviewAssignment->getCancelled(),
                (int) $reviewAssignment->getReviewerFileId(),
                (int) $reviewAssignment->getQuality(),
                (int) $reviewAssignment->getReminderWasAutomatic(),
                (int) $reviewAssignment->getReviewFormId(),
                (int) $reviewAssignment->getReviewRoundId(),
                (int) $reviewAssignment->getUnconsidered(),
            ]
        );

        $reviewAssignment->setId((int) $this->getInsertReviewId());
        return $reviewAssignment->getId();
    }

    /**
     * Update an existing review assignment.
     * @param PKPReviewAssignment $reviewAssignment
     * @return bool
     */
    public function updateReviewAssignment($reviewAssignment) {
        return (bool) $this->update(
            sprintf('UPDATE review_assignments
                SET    submission_id = ?,
                    reviewer_id = ?,
                    stage_id = ?,
                    review_method = ?,
                    regret_message = ?,
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
                    date_response_due = %s,
                    reviewer_file_id = ?,
                    quality = ?,
                    date_rated = %s,
                    last_modified = %s,
                    date_reminded = %s,
                    reminder_was_automatic = ?,
                    review_form_id = ?,
                    review_round_id = ?,
                    unconsidered = ?
                WHERE review_id = ?',
                $this->datetimeToDB($reviewAssignment->getDateAssigned()), 
                $this->datetimeToDB($reviewAssignment->getDateNotified()), 
                $this->datetimeToDB($reviewAssignment->getDateConfirmed()), 
                $this->datetimeToDB($reviewAssignment->getDateCompleted()), 
                $this->datetimeToDB($reviewAssignment->getDateAcknowledged()), 
                $this->datetimeToDB($reviewAssignment->getDateDue()), 
                $this->datetimeToDB($reviewAssignment->getDateResponseDue()), 
                $this->datetimeToDB($reviewAssignment->getDateRated()), 
                $this->datetimeToDB($reviewAssignment->getLastModified()), 
                $this->datetimeToDB($reviewAssignment->getDateReminded())
            ),
            [
                (int) $reviewAssignment->getSubmissionId(),
                (int) $reviewAssignment->getReviewerId(),
                (int) $reviewAssignment->getStageId(),
                (int) $reviewAssignment->getReviewMethod(),
                (string) $reviewAssignment->getRegretMessage(),
                (int) $reviewAssignment->getRound(),
                (string) $reviewAssignment->getCompetingInterests(),
                (string) $reviewAssignment->getRecommendation(),
                (int) $reviewAssignment->getDeclined(),
                (int) $reviewAssignment->getReplaced(),
                (int) $reviewAssignment->getCancelled(),
                (int) $reviewAssignment->getReviewerFileId(),
                (int) $reviewAssignment->getQuality(),
                (int) $reviewAssignment->getReminderWasAutomatic(),
                (int) $reviewAssignment->getReviewFormId(),
                (int) $reviewAssignment->getReviewRoundId(),
                (int) $reviewAssignment->getUnconsidered(),
                (int) $reviewAssignment->getId()
            ]
        );
    }

    /**
     * Internal function to return a review assignment object from a row.
     * @param array $row
     * @return PKPReviewAssignment
     */
    public function _fromRow($row) {
        /** @var PKPReviewAssignment $reviewAssignment */
        $reviewAssignment = $this->newDataObject();

        $reviewAssignment->setId((int) $row['review_id']);
        $reviewAssignment->setSubmissionId((int) $row['submission_id']);
        $reviewAssignment->setReviewerId((int) $row['reviewer_id']);
        $reviewAssignment->setReviewerFullName((string) $row['first_name'] . ' ' . (string) $row['last_name']);
        $reviewAssignment->setCompetingInterests((string) $row['competing_interests']);
        $reviewAssignment->setRegretMessage((string) $row['regret_message']);
        $reviewAssignment->setRecommendation((string) $row['recommendation']);
        $reviewAssignment->setDateAssigned($this->datetimeFromDB($row['date_assigned']));
        $reviewAssignment->setDateNotified($this->datetimeFromDB($row['date_notified']));
        $reviewAssignment->setDateConfirmed($this->datetimeFromDB($row['date_confirmed']));
        $reviewAssignment->setDateCompleted($this->datetimeFromDB($row['date_completed']));
        $reviewAssignment->setDateAcknowledged($this->datetimeFromDB($row['date_acknowledged']));
        $reviewAssignment->setDateDue($this->datetimeFromDB($row['date_due']));
        $reviewAssignment->setDateResponseDue($this->datetimeFromDB($row['date_response_due']));
        $reviewAssignment->setLastModified($this->datetimeFromDB($row['last_modified']));
        $reviewAssignment->setDeclined((int) $row['declined']);
        $reviewAssignment->setReplaced((int) $row['replaced']);
        $reviewAssignment->setCancelled((int) $row['cancelled']);
        $reviewAssignment->setReviewerFileId((int) $row['reviewer_file_id']);
        $reviewAssignment->setQuality((int) $row['quality']);
        $reviewAssignment->setDateRated($this->datetimeFromDB($row['date_rated']));
        $reviewAssignment->setDateReminded($this->datetimeFromDB($row['date_reminded']));
        $reviewAssignment->setReminderWasAutomatic((int) $row['reminder_was_automatic']);
        $reviewAssignment->setRound((int) $row['round']);
        $reviewAssignment->setReviewRevision((int) $row['review_revision']);
        $reviewAssignment->setReviewFormId((int) $row['review_form_id']);
        $reviewAssignment->setReviewRoundId((int) $row['review_round_id']);
        $reviewAssignment->setReviewMethod((int) $row['review_method']);
        $reviewAssignment->setStageId((int) $row['stage_id']);
        $reviewAssignment->setUnconsidered((int) $row['unconsidered']);

        return $reviewAssignment;
    }

    /**
     * Return a new review assignment data object.
     * @return PKPReviewAssignment
     */
    public function newDataObject() {
        assert(false); // Should be implemented by subclasses
    }

    /**
     * Delete review assignment by ID.
     * @deprecated Use deleteById() instead.
     * @param int $reviewId
     * @return bool
     */
    public function deleteReviewAssignmentById($reviewId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return (bool) $this->deleteById((int) $reviewId);
    }

    /**
     * Delete review assignment.
     * @param int $reviewId
     * @return bool
     */
    public function deleteById($reviewId) {
        /** @var ReviewFormResponseDAO $reviewFormResponseDao */
        $reviewFormResponseDao = DAORegistry::getDAO('ReviewFormResponseDAO');
        $reviewFormResponseDao->deleteByReviewId((int) $reviewId);

        return (bool) $this->update(
            'DELETE FROM review_assignments WHERE review_id = ?',
            [(int) $reviewId]
        );
    }

    /**
     * Delete review assignments by submission ID.
     * @param int $submissionId
     * @return bool
     */
    public function deleteBySubmissionId($submissionId) {
        $returner = false;
        $result = $this->retrieve(
            'SELECT review_id FROM review_assignments WHERE submission_id = ?',
            [(int) $submissionId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $reviewId = (int) $row['review_id'];

                $this->update('DELETE FROM review_form_responses WHERE review_id = ?', [$reviewId]);
                $this->update('DELETE FROM review_assignments WHERE review_id = ?', [$reviewId]);

                $result->MoveNext();
                $returner = true;
            }
            $result->Close();
        }
        return $returner;
    }

    /**
     * Get the ID of the last inserted review assignment.
     * @return int
     */
    public function getInsertReviewId() {
        return (int) $this->getInsertId('review_assignments', 'review_id');
    }

}
?>
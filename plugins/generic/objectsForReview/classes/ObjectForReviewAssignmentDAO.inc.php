<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReviewAssignmentDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewAssignmentDAO
 * @ingroup plugins_generic_objectsForReview
 * @see ObjectForReviewAssignment
 *
 * @brief Operations for retrieving and modifying ObjectForReviewAssignment objects.
 */

/* These constants are used for user-selectable search fields. */
define('OFR_FIELD_TITLE', 'title');
define('OFR_FIELD_ABSTRACT', 'description');

class ObjectForReviewAssignmentDAO extends DAO {

    /** @var string Name of parent plugin */
    protected $_parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->_parentPluginName = (string) $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     */
    public function ObjectForReviewAssignmentDAO($parentPluginName) {
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
     * Retrieve assignment by ID.
     * @param int $assignmentId
     * @param int|null $objectId (optional)
     * @return ObjectForReviewAssignment|null
     */
    public function getById($assignmentId, $objectId = null) {
        $params = [(int) $assignmentId];
        if ($objectId !== null) {
            $params[] = (int) $objectId;
        }

        $result = $this->retrieve(
            'SELECT * FROM object_for_review_assignments WHERE assignment_id = ?'
            . ($objectId !== null ? ' AND object_id = ?' : ''),
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
     * Determine if the assignment exists.
     * @param int $objectId
     * @param int|null $userId (optional)
     * @param int|null $submissionId (optional)
     * @return bool
     */
    public function assignmentExists($objectId, $userId = null, $submissionId = null) {
        $params = [(int) $objectId];
        $sql = 'SELECT COUNT(*) AS count FROM object_for_review_assignments WHERE object_id = ?';
        
        if ($userId !== null) {
            $sql .= ' AND user_id = ?';
            $params[] = (int) $userId;
        }
        if ($submissionId !== null) {
            $sql .= ' AND submission_id = ?';
            $params[] = (int) $submissionId;
        }

        $result = $this->retrieve($sql, $params);

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return ObjectForReviewAssignment
     */
    public function newDataObject() {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ObjectForReviewAssignment');
        return new ObjectForReviewAssignment();
    }

    /**
     * Internal function to return an ObjectForReviewAssignment object from a row.
     * @param array $row
     * @return ObjectForReviewAssignment
     */
    public function _fromRow($row) {
        $assignment = $this->newDataObject();
        $assignment->setId((int) $row['assignment_id']);
        $assignment->setObjectId((int) $row['object_id']);
        $assignment->setUserId(isset($row['user_id']) ? (int) $row['user_id'] : null);
        $assignment->setSubmissionId(isset($row['submission_id']) ? (int) $row['submission_id'] : null);
        $assignment->setStatus((int) $row['status']);
        $assignment->setDateRequested($this->datetimeFromDB($row['date_requested']));
        $assignment->setDateAssigned($this->datetimeFromDB($row['date_assigned']));
        $assignment->setDateMailed($this->datetimeFromDB($row['date_mailed']));
        $assignment->setDateDue($this->datetimeFromDB($row['date_due']));
        $assignment->setDateRemindedBefore($this->datetimeFromDB($row['date_reminded_before']));
        $assignment->setDateRemindedAfter($this->datetimeFromDB($row['date_reminded_after']));
        $assignment->setNotes((string) ($row['notes'] ?? ''));

        // Note: HookRegistry dispatch with references is legacy but preserved for signature compatibility
        $tempAssignment = $assignment;
        $tempRow = $row;
        HookRegistry::dispatch('ObjectForReviewAssignmentDAO::_fromRow', [&$tempAssignment, &$tempRow]);

        return $assignment;
    }

    /**
     * Insert a new assignment.
     * @param ObjectForReviewAssignment $assignment
     * @return int
     */
    public function insertObject($assignment) {
        $this->update(
            sprintf(
                'INSERT INTO object_for_review_assignments
                (object_id, user_id, submission_id, status, date_requested, date_assigned, date_mailed, date_due, date_reminded_before, date_reminded_after, notes)
                VALUES
                (?, ?, ?, ?, %s, %s, %s, %s, %s, %s, ?)',
                $this->datetimeToDB($assignment->getDateRequested()),
                $this->datetimeToDB($assignment->getDateAssigned()),
                $this->datetimeToDB($assignment->getDateMailed()),
                $this->datetimeToDB($assignment->getDateDue()),
                $this->datetimeToDB($assignment->getDateRemindedBefore()),
                $this->datetimeToDB($assignment->getDateRemindedAfter())
            ),
            [
                (int) $assignment->getObjectId(),
                $assignment->getUserId() !== null ? (int) $assignment->getUserId() : null,
                $assignment->getSubmissionId() !== null ? (int) $assignment->getSubmissionId() : null,
                (int) $assignment->getStatus(),
                (string) $assignment->getNotes()
            ]
        );
        $assignment->setId((int) $this->getInsertId());
        return $assignment->getId();
    }

    /**
     * Update an existing assignment.
     * @param ObjectForReviewAssignment $assignment
     * @return bool
     */
    public function updateObject($assignment) {
        $returner = $this->update(
            sprintf(
                'UPDATE object_for_review_assignments
                SET object_id = ?,
                    user_id = ?,
                    submission_id = ?,
                    status = ?,
                    date_requested = %s,
                    date_assigned = %s,
                    date_mailed = %s,
                    date_due = %s,
                    date_reminded_before = %s,
                    date_reminded_after = %s,
                    notes = ?
                WHERE assignment_id = ?',
                $this->datetimeToDB($assignment->getDateRequested()),
                $this->datetimeToDB($assignment->getDateAssigned()),
                $this->datetimeToDB($assignment->getDateMailed()),
                $this->datetimeToDB($assignment->getDateDue()),
                $this->datetimeToDB($assignment->getDateRemindedBefore()),
                $this->datetimeToDB($assignment->getDateRemindedAfter())
            ),
            [
                (int) $assignment->getObjectId(),
                $assignment->getUserId() !== null ? (int) $assignment->getUserId() : null,
                $assignment->getSubmissionId() !== null ? (int) $assignment->getSubmissionId() : null,
                (int) $assignment->getStatus(),
                (string) $assignment->getNotes(),
                (int) $assignment->getId()
            ]
        );
        return (bool) $returner;
    }

    /**
     * Delete an assignment.
     * @param ObjectForReviewAssignment $assignment
     * @return bool
     */
    public function deleteObject($assignment) {
        return $this->deleteById((int) $assignment->getId(), (int) $assignment->getObjectId());
    }

    /**
     * Delete an assignment by ID.
     * @param int $assignmentId
     * @param int|null $objectId (optional)
     * @return bool
     */
    public function deleteById($assignmentId, $objectId = null) {
        $params = [(int) $assignmentId];
        if ($objectId !== null) {
            $params[] = (int) $objectId;
        }

        return (bool) $this->update(
            'DELETE FROM object_for_review_assignments WHERE assignment_id = ?'
            . ($objectId !== null ? ' AND object_id = ?' : ''),
            $params
        );
    }

    /**
     * Delete all assignments for an object.
     * @param int $objectId
     * @return bool
     */
    public function deleteAllByObjectId($objectId) {
        return (bool) $this->update(
            'DELETE FROM object_for_review_assignments WHERE object_id = ?',
            [(int) $objectId]
        );
    }

    /**
     * Retrieve the assignment matching the object and the user.
     * @param int $objectId
     * @param int $userId
     * @return ObjectForReviewAssignment|null
     */
    public function getByObjectAndUserId($objectId, $userId) {
        $params = [(int) $objectId, (int) $userId];
        $sql = 'SELECT * FROM object_for_review_assignments WHERE object_id = ? AND user_id = ?';
        $result = $this->retrieve($sql, $params);

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
     * Retrieve all assignments for an object for review.
     * @param int $objectId
     * @return array
     */
    public function getAllByObjectId($objectId) {
        return $this->_getAllInternally((int) $objectId);
    }

    /**
     * Retrieve all assignments for a user.
     * @param int $userId
     * @return array
     */
    public function getAllByUserId($userId) {
        return $this->_getAllInternally(null, (int) $userId);
    }

    /**
     * Retrieve all assignments for a submission.
     * @param int $submissionId
     * @return array
     */
    public function getAllBySubmissionId($submissionId) {
        return $this->_getAllInternally(null, null, (int) $submissionId);
    }

    /**
     * Retrieve all incomplete assignments matching a particular context ID.
     * @param int $contextId
     * @return array
     */
    public function getIncompleteAssignmentsByContextId($contextId) {
        $params = [(int) $contextId];
        $result = $this->retrieve(
            'SELECT ofra.*
            FROM object_for_review_assignments ofra
            JOIN objects_for_review ofr ON (ofra.object_id = ofr.object_id)
            WHERE ofr.context_id = ? AND ofra.submission_id IS NULL AND (ofra.date_assigned IS NOT NULL OR ofra.date_mailed IS NOT NULL)',
            $params
        );

        $incompleteAssignments = []; // [WIZDAM FIX] Corrected typo from 'incompleteAssignements'
        if ($result) {
            while (!$result->EOF) {
                $incompleteAssignments[] = $this->_fromRow($result->GetRowAssoc(false));
                $result->MoveNext();
            }
            $result->Close();
        }
        return $incompleteAssignments;
    }

    /**
     * Retrieve all assignments matching a particular context ID.
     * @param int $contextId
     * @param int|null $searchType
     * @param string|null $search
     * @param string|null $searchMatch
     * @param int|null $status
     * @param int|null $userId
     * @param int|null $editorId
     * @param int|null $filterType
     * @param mixed $rangeInfo DBResultRange
     * @param string|null $sortBy
     * @param int $sortDirection
     * @return DAOResultFactory
     */
    public function getAllByContextId($contextId, $searchType = null, $search = null, $searchMatch = null, $status = null, $userId = null, $editorId = null, $filterType = null, $rangeInfo = null, $sortBy = null, $sortDirection = SORT_DIRECTION_ASC) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ReviewObjectMetadata');
        $ofrPlugin->import('classes.ObjectForReviewAssignment');

        $params = [];
        $sortColumn = '';
        $sortSQL = '';
        
        switch ($sortBy) {
            case 'title':
                $sortColumn .= ', ofrs.setting_value AS ofr_title';
                $sortSQL .= ' LEFT JOIN object_for_review_settings ofrs ON (ofra.object_id = ofrs.object_id)
                        JOIN review_object_metadata rom ON (rom.metadata_id = ofrs.review_object_metadata_id AND rom.metadata_key = \'' . REVIEW_OBJECT_METADATA_KEY_TITLE . '\')';
                break;
            case 'type':
                $sortColumn .= ', COALESCE(rtl.setting_value, rtpl.setting_value) AS ofr_type_name';
                $sortSQL .= ' LEFT JOIN review_object_types rt ON (ofr.review_object_type_id = rt.type_id)
                        LEFT JOIN review_object_type_settings rtl ON (ofr.review_object_type_id = rtl.type_id AND rtl.setting_name = \'name\' AND rtl.locale = ?)
                        LEFT JOIN review_object_type_settings rtpl ON (ofr.review_object_type_id = rtpl.type_id AND rtpl.setting_name = \'name\' AND rtpl.locale = ?)';
                $params[] = AppLocale::getLocale();
                $params[] = AppLocale::getPrimaryLocale();
                // intentional fall-through to also join users if needed by other logic, though typically handled separately. Preserved from original.
            case 'reviewer':
                $sortSQL .= ' LEFT JOIN users u ON (ofra.user_id = u.user_id)';
                break;
            case 'editor':
                $sortColumn .= ', COALESCE(ue.initials, CONCAT(SUBSTRING(ue.first_name FROM 1 FOR 1), SUBSTRING(ue.last_name FROM 1 FOR 1))) AS ed_initials';
                $sortSQL .= ' LEFT JOIN users ue ON (ofr.editor_id = ue.user_id)';
                break;
        }

        $sql = "SELECT DISTINCT ofra.*$sortColumn
                FROM object_for_review_assignments ofra
                LEFT JOIN objects_for_review ofr ON (ofra.object_id = ofr.object_id)
                $sortSQL";

        switch ($searchType) {
            case OFR_FIELD_TITLE:
                if ($sortBy !== 'title') {
                    $sql .= ' LEFT JOIN object_for_review_settings ofrs ON (ofra.object_id = ofrs.object_id)
                            JOIN review_object_metadata rom ON (rom.metadata_id = ofrs.review_object_metadata_id AND rom.metadata_key = \'' . REVIEW_OBJECT_METADATA_KEY_TITLE . '\')';
                }
                $sql .= ' WHERE LOWER(ofrs.setting_value) ' . ($searchMatch === 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $searchMatch === 'is' ? (string) $search : '%' . (string) $search . '%';
                break;
            case OFR_FIELD_ABSTRACT:
                $sql .= ' LEFT JOIN object_for_review_settings ofrsa ON (ofra.object_id = ofrsa.object_id)
                    LEFT JOIN review_object_metadata rom ON rom.metadata_id = ofrsa.review_object_metadata_id AND rom.metadata_key = \'' . REVIEW_OBJECT_METADATA_KEY_ABSTRACT . '\'
                    WHERE LOWER(ofrsa.setting_value) ' . ($searchMatch === 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $params[] = $searchMatch === 'is' ? (string) $search : '%' . (string) $search . '%';
                break;
            default:
                $searchType = null;
        }

        if (empty($searchType)) {
            $sql .= ' WHERE';
        } else {
            $sql .= ' AND';
        }

        if ($status !== null && $status !== '') {
            $sql .= ' ofra.status = ? AND';
            $params[] = (int) $status;
        }

        if ($userId !== null && $userId !== '') {
            $sql .= ' ofra.user_id = ? AND';
            $params[] = (int) $userId;
        }

        if ($editorId !== null && $editorId !== '') {
            $sql .= ' ofr.editor_id = ? AND';
            $params[] = (int) $editorId;
        }

        if ($filterType !== null && $filterType !== '') {
            $sql .= ' ofr.review_object_type_id = ? AND';
            $params[] = (int) $filterType;
        }

        $sql .= " ofr.context_id = ?";
        $params[] = (int) $contextId;

        if ($sortBy) {
            $sql .= ' ORDER BY ' . $this->getSortMapping($sortBy) . ' ' . $this->getDirectionMapping($sortDirection);
        }

        $result = $this->retrieveRange($sql, $params, $rangeInfo);
        return new DAOResultFactory($result, $this, '_fromRow');
    }

    /**
     * Get all object IDs assigned to an author.
     * @param int $userId
     * @return array
     */
    public function getObjectIds($userId) {
        $result = $this->retrieve(
            'SELECT object_id FROM object_for_review_assignments WHERE user_id = ?',
            [(int) $userId]
        );

        $objectIds = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $objectIds[] = (int) $row['object_id'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $objectIds;
    }

    /**
     * Get all user IDs assigned to an object for review.
     * @param int $objectId
     * @return array
     */
    public function getUserIds($objectId) {
        $result = $this->retrieve(
            'SELECT user_id FROM object_for_review_assignments WHERE object_id = ?',
            [(int) $objectId]
        );

        $userIds = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $userIds[] = (int) $row['user_id'];
                $result->MoveNext();
            }
            $result->Close();
        }
        return $userIds;
    }

    /**
     * Transfer all existing assignments to another user.
     * @param int $oldUserId
     * @param int $newUserId
     * @return bool
     */
    public function transferAssignments($oldUserId, $newUserId) {
        return (bool) $this->update(
            'UPDATE object_for_review_assignments
            SET user_id = ?
            WHERE user_id = ?',
            [
                (int) $newUserId,
                (int) $oldUserId
            ]
        );
    }

    /**
     * Retrieve status counts for a particular context (and optionally user).
     * @param int $contextId
     * @param int|null $status
     * @param int|null $userId
     * @return int
     */
    public function getStatusCount($contextId, $status = null, $userId = null) {
        $paramArray = [(int) $contextId];
        $sql = 'SELECT COUNT(*) AS count
                FROM objects_for_review ofr
                LEFT JOIN object_for_review_assignments ofra ON ofr.object_id = ofra.object_id
                WHERE ofr.context_id = ?';

        if ($status !== null) {
            if ($status === OFR_STATUS_AVAILABLE) {
                $sql .= ' AND ofr.available = 1';
            } else {
                $sql .= ' AND ofra.status = ?';
                $paramArray[] = (int) $status;
            }
        }

        if ($userId !== null) {
            $sql .= ' AND ofra.user_id = ?';
            $paramArray[] = (int) $userId;
        }

        $result = $this->retrieve($sql, $paramArray);
        
        $count = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $count = (int) ($row['count'] ?? 0);
        }
        if ($result) {
            $result->Close();
        }
        return $count;
    }

    /**
     * Retrieve all status counts for a particular context (and optionally user).
     * @param int $contextId
     * @param int|null $userId (optional), user to match
     * @return array, status as index
     */
    public function getStatusCounts($contextId, $userId = null) {
        $ofrPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        $ofrPlugin->import('classes.ObjectForReviewAssignment');
        
        $counts = [
            OFR_STATUS_AVAILABLE => $this->getStatusCount($contextId, OFR_STATUS_AVAILABLE, $userId),
            OFR_STATUS_REQUESTED => $this->getStatusCount($contextId, OFR_STATUS_REQUESTED, $userId),
            OFR_STATUS_ASSIGNED => $this->getStatusCount($contextId, OFR_STATUS_ASSIGNED, $userId),
            OFR_STATUS_MAILED => $this->getStatusCount($contextId, OFR_STATUS_MAILED, $userId),
            OFR_STATUS_SUBMITTED => $this->getStatusCount($contextId, OFR_STATUS_SUBMITTED, $userId)
        ];
        
        return $counts;
    }

    /**
     * Change the status of the assignment.
     * @param int $objectId
     * @param int $userId
     * @param int $status OFR_STATUS_...
     * @return bool
     */
    public function changeStatus($objectId, $userId, $status) {
        return (bool) $this->update(
            'UPDATE object_for_review_assignments SET status = ? WHERE object_id = ? AND user_id = ?', 
            [(int) $status, (int) $objectId, (int) $userId]
        );
    }

    /**
     * Get the ID of the last inserted assignment.
     * @param string $table
     * @param string $id
     * @param bool $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return (int) parent::getInsertId('object_for_review_assignments', 'assignment_id', $callHooks);
    }

    /**
     * Map a column heading value to a database value for sorting.
     * @param string $heading
     * @return string|null
     */
    public static function getSortMapping($heading) {
        switch ($heading) {
            case 'title': return 'ofr_title';
            case 'due': return 'ofra.date_due';
            case 'editor': return 'ed_initials';
            case 'reviewer': return 'u.last_name';
            case 'status': return 'ofra.status';
            case 'type': return 'ofr_type_name';
            case 'submission': return 'ofra.submission_id';
            default: return null;
        }
    }

    //
    // Private helper methods.
    //

    /**
     * Retrieve all assignments matching the specified input parameters.
     * @param int|null $objectId (optional)
     * @param int|null $userId (optional)
     * @param int|null $submissionId (optional)
     * @return array
     */
    private function _getAllInternally($objectId = null, $userId = null, $submissionId = null) {
        $sql = 'SELECT * FROM object_for_review_assignments';
        $conditions = [];
        $params = [];

        if ($objectId !== null) {
            $conditions[] = 'object_id = ?';
            $params[] = (int) $objectId;
        }

        if ($userId !== null) {
            $conditions[] = 'user_id = ?';
            $params[] = (int) $userId;
        }

        if ($submissionId !== null) {
            $conditions[] = 'submission_id = ?';
            $params[] = (int) $submissionId;
        }

        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY assignment_id';
        $result = $this->retrieve($sql, $params);

        $assignments = [];
        if ($result) {
            while (!$result->EOF) {
                $assignments[] = $this->_fromRow($result->GetRowAssoc(false));
                $result->MoveNext();
            }
            $result->Close();
        }
        return $assignments;
    }

}
?>
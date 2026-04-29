<?php
declare(strict_types=1);

/**
 * @file plugins/generic/objectsForReview/classes/ObjectForReviewAssignment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ObjectForReviewAssignment
 * @ingroup plugins_generic_objectsForReview
 * @see ObjectForReviewAssignmentDAO
 *
 * @brief Basic class describing an object for review assignment.
 * * MODERNIZED FOR WIZDAM FORK
 */

define('OFR_STATUS_AVAILABLE',    0x01);
define('OFR_STATUS_REQUESTED',    0x02);
define('OFR_STATUS_ASSIGNED',     0x03);
define('OFR_STATUS_MAILED',       0x04);
define('OFR_STATUS_SUBMITTED',    0x05);


class ObjectForReviewAssignment extends DataObject {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ObjectForReviewAssignment() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ObjectForReviewAssignment(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //
    /**
     * get object id
     * @return int
     */
    public function getObjectId() {
        return $this->getData('objectId');
    }

    /**
     * set object id
     * @param $objectId int
     */
    public function setObjectId($objectId) {
        return $this->setData('objectId', $objectId);
    }

    /**
     * Get the associated object for review.
     * @return ObjectForReview
     */
    public function getObjectForReview() {
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        return $ofrDao->getById($this->getData('objectId'));
    }

    /**
     * Get user ID for this assignment.
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * Set user ID for this assignment.
     * @param $userId int
     */
    public function setUserId($userId) {
        return $this->setData('userId', $userId);
    }

    /**
     * Get the user assigned to the object for review.
     * @return User
     */
    public function getUser() {
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getById($this->getData('userId'));
    }

    /**
     * Get submission ID for this assignment.
     * @return int
     */
    public function getSubmissionId() {
        return $this->getData('submissionId');
    }

    /**
     * Set submission ID for this assignment.
     * @param $submissionId int
     */
    public function setSubmissionId($submissionId) {
        return $this->setData('submissionId', $submissionId);
    }

    /**
     * Get the article.
     * @return Article
     */
    public function getArticle() {
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        return $articleDao->getArticle($this->getSubmissionId());
    }

    /**
     * Get date requested.
     * @return string
     */
    public function getDateRequested() {
        return $this->getData('dateRequested');
    }

    /**
     * Set date requested.
     * @param $dateRequested string
     */
    public function setDateRequested($dateRequested) {
        return $this->setData('dateRequested', $dateRequested);
    }

    /**
     * Get date assigned.
     * @return string
     */
    public function getDateAssigned() {
        return $this->getData('dateAssigned');
    }

    /**
     * Set date assigned.
     * @param $dateAssigned string
     */
    public function setDateAssigned($dateAssigned) {
        return $this->setData('dateAssigned', $dateAssigned);
    }


    /**
     * Get date mailed.
     * @return string
     */
    public function getDateMailed() {
        return $this->getData('dateMailed');
    }

    /**
     * Set date mailed.
     * @param $dateMailed string
     */
    public function setDateMailed($dateMailed) {
        return $this->setData('dateMailed', $dateMailed);
    }

    /**
     * Get date due.
     * @return string
     */
    public function getDateDue() {
        return $this->getData('dateDue');
    }

    /**
     * Set date due.
     * @param $dateDue string
     */
    public function setDateDue($dateDue) {
        return $this->setData('dateDue', $dateDue);
    }

    /**
     * Check whether the review has past due date
     * @return boolean
     */
    public function isLate() {
        $dateDue = $this->getData('dateDue');
        if (!empty($dateDue)) {
            if (strtotime($dateDue) > time()) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Get date reminded, before the due date.
     * @return string
     */
    public function getDateRemindedBefore() {
        return $this->getData('dateRemindedBefore');
    }

    /**
     * Set date reminded, before the due date.
     * @param $dateRemindedBefore string
     */
    public function setDateRemindedBefore($dateRemindedBefore) {
        return $this->setData('dateRemindedBefore', $dateRemindedBefore);
    }

    /**
     * Get date reminded, after the due date.
     * @return string
     */
    public function getDateRemindedAfter() {
        return $this->getData('dateRemindedAfter');
    }

    /**
     * Set date reminded, after the due date.
     * @param $dateRemindedAfter string
     */
    public function setDateRemindedAfter($dateRemindedAfter) {
        return $this->setData('dateRemindedAfter', $dateRemindedAfter);
    }

    /**
     * Get status of the object for review assignment.
     * @return int OFR_STATUS_...
     */
    public function getStatus() {
        return $this->getData('status');
    }

    /**
     * Set status of the object for review assignment.
     * @param $status int OFR_STATUS_...
     */
    public function setStatus($status) {
        return $this->setData('status', $status);
    }

    /**
     * Get object for review assignment status locale key.
     * @return string
     */
    public function getStatusString() {
        switch ($this->getData('status')) {
            case OFR_STATUS_AVAILABLE:
                return 'plugins.generic.objectsForReview.objectForReviewAssignment.status.available';
            case OFR_STATUS_REQUESTED:
                return 'plugins.generic.objectsForReview.objectForReviewAssignment.status.requested';
            case OFR_STATUS_ASSIGNED:
                return 'plugins.generic.objectsForReview.objectForReviewAssignment.status.assigned';
            case OFR_STATUS_MAILED:
                return 'plugins.generic.objectsForReview.objectForReviewAssignment.status.mailed';
            case OFR_STATUS_SUBMITTED:
                return 'plugins.generic.objectsForReview.objectForReviewAssignment.status.submitted';
            default:
                return 'plugins.generic.objectsForReview.objectForReviewAssignment.status';
        }
    }

    /**
     * Get notes for the assignment.
     * @return string
     */
    public function getNotes() {
        return $this->getData('notes');
    }

    /**
     * Set notes for the assignment.
     * @param $notes string
     */
    public function setNotes($notes) {
        return $this->setData('notes', $notes);
    }

}

?>
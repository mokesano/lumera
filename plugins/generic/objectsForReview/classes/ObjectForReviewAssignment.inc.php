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
 */

define('OFR_STATUS_AVAILABLE', 0x01);
define('OFR_STATUS_REQUESTED', 0x02);
define('OFR_STATUS_ASSIGNED', 0x03);
define('OFR_STATUS_MAILED', 0x04);
define('OFR_STATUS_SUBMITTED', 0x05);

class ObjectForReviewAssignment extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ObjectForReviewAssignment() {
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
     * Get object ID.
     * @return int|null
     */
    public function getObjectId() {
        $id = $this->getData('objectId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set object ID.
     * @param int|null $objectId
     */
    public function setObjectId($objectId) {
        $this->setData('objectId', $objectId !== null ? (int) $objectId : null);
    }

    /**
     * Get the associated object for review.
     * @return ObjectForReview|null
     */
    public function getObjectForReview() {
        /** @var ObjectForReviewDAO $ofrDao */
        $ofrDao = DAORegistry::getDAO('ObjectForReviewDAO');
        return $ofrDao->getById($this->getObjectId());
    }

    /**
     * Get user ID for this assignment.
     * @return int|null
     */
    public function getUserId() {
        $id = $this->getData('userId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set user ID for this assignment.
     * @param int|null $userId
     */
    public function setUserId($userId) {
        $this->setData('userId', $userId !== null ? (int) $userId : null);
    }

    /**
     * Get the user assigned to the object for review.
     * @return User|null
     */
    public function getUser() {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getById($this->getUserId());
    }

    /**
     * Get submission ID for this assignment.
     * @return int|null
     */
    public function getSubmissionId() {
        $id = $this->getData('submissionId');
        return $id !== null ? (int) $id : null;
    }

    /**
     * Set submission ID for this assignment.
     * @param int|null $submissionId
     */
    public function setSubmissionId($submissionId) {
        $this->setData('submissionId', $submissionId !== null ? (int) $submissionId : null);
    }

    /**
     * Get the article.
     * @return Article|null
     */
    public function getArticle() {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        return $articleDao->getArticle($this->getSubmissionId());
    }

    /**
     * Get date requested.
     * @return string|null
     */
    public function getDateRequested() {
        $date = $this->getData('dateRequested');
        return $date !== null ? (string) $date : null;
    }

    /**
     * Set date requested.
     * @param string|null $dateRequested
     */
    public function setDateRequested($dateRequested) {
        $this->setData('dateRequested', $dateRequested !== null ? (string) $dateRequested : null);
    }

    /**
     * Get date assigned.
     * @return string|null
     */
    public function getDateAssigned() {
        $date = $this->getData('dateAssigned');
        return $date !== null ? (string) $date : null;
    }

    /**
     * Set date assigned.
     * @param string|null $dateAssigned
     */
    public function setDateAssigned($dateAssigned) {
        $this->setData('dateAssigned', $dateAssigned !== null ? (string) $dateAssigned : null);
    }

    /**
     * Get date mailed.
     * @return string|null
     */
    public function getDateMailed() {
        $date = $this->getData('dateMailed');
        return $date !== null ? (string) $date : null;
    }

    /**
     * Set date mailed.
     * @param string|null $dateMailed
     */
    public function setDateMailed($dateMailed) {
        $this->setData('dateMailed', $dateMailed !== null ? (string) $dateMailed : null);
    }

    /**
     * Get date due.
     * @return string|null
     */
    public function getDateDue() {
        $date = $this->getData('dateDue');
        return $date !== null ? (string) $date : null;
    }

    /**
     * Set date due.
     * @param string|null $dateDue
     */
    public function setDateDue($dateDue) {
        $this->setData('dateDue', $dateDue !== null ? (string) $dateDue : null);
    }

    /**
     * Check whether the review has past due date.
     * @return bool
     */
    public function isLate() {
        $dateDue = $this->getData('dateDue');
        if ($dateDue !== null && $dateDue !== '') {
            return strtotime((string) $dateDue) <= time();
        }
        return false;
    }

    /**
     * Get date reminded, before the due date.
     * @return string|null
     */
    public function getDateRemindedBefore() {
        $date = $this->getData('dateRemindedBefore');
        return $date !== null ? (string) $date : null;
    }

    /**
     * Set date reminded, before the due date.
     * @param string|null $dateRemindedBefore
     */
    public function setDateRemindedBefore($dateRemindedBefore) {
        $this->setData('dateRemindedBefore', $dateRemindedBefore !== null ? (string) $dateRemindedBefore : null);
    }

    /**
     * Get date reminded, after the due date.
     * @return string|null
     */
    public function getDateRemindedAfter() {
        $date = $this->getData('dateRemindedAfter');
        return $date !== null ? (string) $date : null;
    }

    /**
     * Set date reminded, after the due date.
     * @param string|null $dateRemindedAfter
     */
    public function setDateRemindedAfter($dateRemindedAfter) {
        $this->setData('dateRemindedAfter', $dateRemindedAfter !== null ? (string) $dateRemindedAfter : null);
    }

    /**
     * Get status of the object for review assignment.
     * @return int|null OFR_STATUS_...
     */
    public function getStatus() {
        $status = $this->getData('status');
        return $status !== null ? (int) $status : null;
    }

    /**
     * Set status of the object for review assignment.
     * @param int|null $status OFR_STATUS_...
     */
    public function setStatus($status) {
        $this->setData('status', $status !== null ? (int) $status : null);
    }

    /**
     * Get object for review assignment status locale key.
     * @return string
     */
    public function getStatusString() {
        $status = (int) $this->getData('status');
        switch ($status) {
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
     * @return string|null
     */
    public function getNotes() {
        $notes = $this->getData('notes');
        return $notes !== null ? (string) $notes : null;
    }

    /**
     * Set notes for the assignment.
     * @param string|null $notes
     */
    public function setNotes($notes) {
        $this->setData('notes', $notes !== null ? (string) $notes : null);
    }

}
?>
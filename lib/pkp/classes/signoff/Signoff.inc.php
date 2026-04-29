<?php
declare(strict_types=1);

/**
 * @defgroup signoff
 */

/**
 * @file classes/signoff/Signoff.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Signoff
 * @ingroup signoff
 * @see SignoffDAO
 *
 * @brief Basic class describing a signoff.
 */

class Signoff extends DataObject {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Signoff() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::Signoff(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * get assoc id
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * set assoc id
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get associated type.
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * Set associated type.
     * @param $assocType int
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * Get symbolic name.
     * @return string
     */
    public function getSymbolic() {
        return $this->getData('symbolic');
    }

    /**
     * Set symbolic name.
     * @param $symbolic string
     */
    public function setSymbolic($symbolic) {
        return $this->setData('symbolic', $symbolic);
    }

    /**
     * Get user ID for this signoff.
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * Set user ID for this signoff.
     * @param $userId int
     */
    public function setUserId($userId) {
        return $this->setData('userId', $userId);
    }

    /**
     * Get file ID for this signoff.
     * @return int
     */
    public function getFileId() {
        return $this->getData('fileId');
    }

    /**
     * Set file ID for this signoff.
     * @param $fileId int
     */
    public function setFileId($fileId) {
        return $this->setData('fileId', $fileId);
    }

    /**
     * Get file revision for this signoff.
     * @return int
     */
    public function getFileRevision() {
        return $this->getData('fileRevision');
    }

    /**
     * Set file revision for this signoff.
     * @param $fileRevision int
     */
    public function setFileRevision($fileRevision) {
        return $this->setData('fileRevision', $fileRevision);
    }

    /**
     * Get date notified.
     * @return string
     */
    public function getDateNotified() {
        return $this->getData('dateNotified');
    }

    /**
     * Set date notified.
     * @param $dateNotified string
     */
    public function setDateNotified($dateNotified) {
        return $this->setData('dateNotified', $dateNotified);
    }

    /**
     * Get date underway.
     * @return string
     */
    public function getDateUnderway() {
        return $this->getData('dateUnderway');
    }

    /**
     * Set date underway.
     * @param $dateUnderway string
     */
    public function setDateUnderway($dateUnderway) {
        return $this->setData('dateUnderway', $dateUnderway);
    }

    /**
     * Get date completed.
     * @return string
     */
    public function getDateCompleted() {
        return $this->getData('dateCompleted');
    }

    /**
     * Set date completed.
     * @param $dateCompleted string
     */
    public function setDateCompleted($dateCompleted) {
        return $this->setData('dateCompleted', $dateCompleted);
    }

    /**
     * Get date acknowledged.
     * @return string
     */
    public function getDateAcknowledged() {
        return $this->getData('dateAcknowledged');
    }

    /**
     * Set date acknowledged.
     * @param $dateAcknowledged string
     */
    public function setDateAcknowledged($dateAcknowledged) {
        return $this->setData('dateAcknowledged', $dateAcknowledged);
    }

    /**
     * Get id of user group the user is acting as.
     * @return string
     */
    public function getUserGroupId() {
        return $this->getData('userGroupId');
    }

    /**
     * Set id of user group the user is acting as.
     * @param $userGroupId string
     */
    public function setUserGroupId($userGroupId) {
        return $this->setData('userGroupId', $userGroupId);
    }
}

?>
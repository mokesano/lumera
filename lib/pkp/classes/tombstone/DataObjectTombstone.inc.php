<?php
declare(strict_types=1);

/**
 * @file classes/tombstone/DataObjectTombstone.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObjectTombstone
 * @ingroup tombstone
 *
 * @brief Base class for data object tombstones.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Visibility, Type Safety)
 */

class DataObjectTombstone extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataObjectTombstone() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::DataObjectTombstone(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * get data object id
     * @return int
     */
    public function getDataObjectId() {
        return $this->getData('dataObjectId');
    }

    /**
     * set data object id
     * @param $dataObjectId int
     */
    public function setDataObjectId($dataObjectId) {
        return $this->setData('dataObjectId', $dataObjectId);
    }

    /**
     * get date deleted
     * @return date
     */
    public function getDateDeleted() {
        return $this->getData('dateDeleted');
    }

    /**
     * set date deleted
     * @param $dateDeleted date
     */
    public function setDateDeleted($dateDeleted) {
        return $this->setData('dateDeleted', $dateDeleted);
    }

    /**
     * Stamp the date of the deletion to the current time.
     */
    public function stampDateDeleted() {
        return $this->setDateDeleted(Core::getCurrentDate());
    }

    /**
     * Get oai setSpec.
     * @return string
     */
    public function getSetSpec() {
        return $this->getData('setSpec');
    }

    /**
     * Set oai setSpec.
     * @param $setSpec string
     */
    public function setSetSpec($setSpec) {
        return $this->setData('setSpec', $setSpec);
    }

    /**
     * Get oai setName.
     * @return string
     */
    public function getSetName() {
        return $this->getData('setName');
    }

    /**
     * Set oai setName.
     * @param $setName string
     */
    public function setSetName($setName) {
        return $this->setData('setName', $setName);
    }

    /**
     * Get oai identifier.
     * @return string
     */
    public function getOAIIdentifier() {
        return $this->getData('oaiIdentifier');
    }

    /**
     * Set oai identifier.
     * @param $oaiIdentifier string
     */
    public function setOAIIdentifier($oaiIdentifier) {
        return $this->setData('oaiIdentifier', $oaiIdentifier);
    }

    /**
     * Get an specific object id that is part of
     * the OAI set of this tombstone.
     * @param $assocType int
     * @return int The object id.
     */
    public function getOAISetObjectId($assocType) {
        $setObjectsIds = $this->getOAISetObjectsIds();
        if (isset($setObjectsIds[$assocType])) {
            return $setObjectsIds[$assocType];
        } else {
            return null;
        }
    }

    /**
     * Set an specific object id that is part of
     * the OAI set of this tombstone.
     * @param $assocType int
     * @param $assocId int
     */
    public function setOAISetObjectId($assocType, $assocId) {
        $setObjectsIds = $this->getOAISetObjectsIds();
        // WIZDAM FIX: Initialize array if null to prevent auto-vivification warning
        if (!is_array($setObjectsIds)) {
            $setObjectsIds = array();
        }
        $setObjectsIds[$assocType] = $assocId;

        $this->setOAISetObjectsIds($setObjectsIds);
    }

    /**
     * Get all objects ids that are part of
     * the OAI set of this tombstone.
     * @return array assocType => assocId
     */
    public function getOAISetObjectsIds() {
        return $this->getData('OAISetObjectsIds');
    }

    /**
     * Set all objects ids that are part of
     * the OAI set of this tombstone.
     * @param $OAISetObjectsIds array assocType => assocId
     */
    public function setOAISetObjectsIds($OAISetObjectsIds) {
        $this->setData('OAISetObjectsIds', $OAISetObjectsIds);
    }
}

?>
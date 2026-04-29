<?php
declare(strict_types=1);

/**
 * @file classes/announcement/PKPAnnouncementTypeDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAnnouncementTypeDAO
 * @ingroup announcement
 * @see AnnouncementType, PKPAnnouncementType
 *
 * @brief Operations for retrieving and modifying AnnouncementType objects.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor, Ref removal)
 * - Strict Integer Casting
 * - Null Safety
 */

import('lib.pkp.classes.announcement.PKPAnnouncementType');

class PKPAnnouncementTypeDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAnnouncementTypeDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class PKPAnnouncementTypeDAO uses deprecated constructor. Please refactor to __construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Generate a new data object.
     * @return DataObject
     */
    public function newDataObject() {
        // assert(false); // To be implemented by subclasses
        return new PKPAnnouncementType();
    }

    /**
     * Retrieve an announcement type by announcement type ID.
     * @param int $typeId
     * @return AnnouncementType|null
     */
    public function getById($typeId) {
        $result = $this->retrieve(
            'SELECT * FROM announcement_types WHERE type_id = ?',
            (int) $typeId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnAnnouncementTypeFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve announcement type Assoc ID by announcement type ID.
     * @param int $typeId
     * @return int
     */
    public function getAnnouncementTypeAssocId($typeId) {
        $result = $this->retrieve(
            'SELECT assoc_id FROM announcement_types WHERE type_id = ?',
            (int) $typeId
        );

        return isset($result->fields[0]) ? (int)$result->fields[0] : 0;
    }

    /**
     * Retrieve announcement type name by ID.
     * @param int $typeId
     * @return string|false
     */
    public function getAnnouncementTypeName($typeId) {
        $result = $this->retrieve(
            'SELECT COALESCE(l.setting_value, p.setting_value) FROM announcement_type_settings p LEFT JOIN announcement_type_settings l ON (l.type_id = ? AND l.setting_name = ? AND l.locale = ?) WHERE p.type_id = ? AND p.setting_name = ? AND p.locale = ?',
            array(
                (int) $typeId, 'name', AppLocale::getLocale(),
                (int) $typeId, 'name', AppLocale::getPrimaryLocale()
            )
        );

        $returner = isset($result->fields[0]) ? $result->fields[0] : false;

        $result->Close();
        unset($result);

        return $returner;
    }


    /**
     * Check if a announcement type exists with the given type id for a assoc type/id pair.
     * @param int $typeId
     * @param int $assocType
     * @param int $assocId
     * @return boolean
     */
    public function announcementTypeExistsByTypeId($typeId, $assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT COUNT(*)
            FROM announcement_types
            WHERE type_id = ? AND
                assoc_type = ? AND
                assoc_id = ?',
            array(
                (int) $typeId,
                (int) $assocType,
                (int) $assocId
            )
        );
        $returner = isset($result->fields[0]) && $result->fields[0] != 0 ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Get locale field names
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), array('name'));
    }

    /**
     * Return announcement type ID based on a type name for an assoc type/id pair.
     * @param string $typeName
     * @param int $assocType
     * @param int $assocId
     * @return int
     */
    public function getByTypeName($typeName, $assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT ats.type_id
                FROM announcement_type_settings AS ats
                LEFT JOIN announcement_types at ON ats.type_id = at.type_id
                WHERE ats.setting_name = "name"
                AND ats.setting_value = ?
                AND at.assoc_type = ?
                AND at.assoc_id = ?',
            array(
                $typeName,
                (int) $assocType,
                (int) $assocId
            )
        );
        $returner = isset($result->fields[0]) ? (int)$result->fields[0] : 0;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Internal function to return an AnnouncementType object from a row.
     * @param array $row
     * @return AnnouncementType
     */
    public function _returnAnnouncementTypeFromRow($row) {
        $announcementType = $this->newDataObject();
        $announcementType->setId($row['type_id']);
        $announcementType->setAssocType($row['assoc_type']);
        $announcementType->setAssocId($row['assoc_id']);
        $this->getDataObjectSettings('announcement_type_settings', 'type_id', $row['type_id'], $announcementType);

        return $announcementType;
    }

    /**
     * Update the localized settings for this object
     * @param AnnouncementType $announcementType
     */
    public function updateLocaleFields($announcementType) {
        $this->updateDataObjectSettings('announcement_type_settings', $announcementType, array(
            'type_id' => (int) $announcementType->getId()
        ));
    }

    /**
     * Insert a new AnnouncementType.
     * @param AnnouncementType $announcementType
     * @return int
     */
    public function insertAnnouncementType($announcementType) {
        $this->update(
            sprintf('INSERT INTO announcement_types
                (assoc_type, assoc_id)
                VALUES
                (?, ?)'),
            array(
                (int) $announcementType->getAssocType(),
                (int) $announcementType->getAssocId()
            )
        );
        $announcementType->setId($this->getInsertTypeId());
        $this->updateLocaleFields($announcementType);
        return $announcementType->getId();
    }

    /**
     * Update an existing announcement type.
     * @param AnnouncementType $announcementType
     * @return boolean
     */
    public function updateObject($announcementType) {
        $returner = $this->update(
            'UPDATE announcement_types
            SET assoc_type = ?,
                assoc_id = ?
            WHERE type_id = ?',
            array(
                (int) $announcementType->getAssocType(),
                (int) $announcementType->getAssocId(),
                (int) $announcementType->getId()
            )
        );

        $this->updateLocaleFields($announcementType);
        return $returner;
    }

    /**
     * @see updateObject
     */
    public function updateAnnouncementType($announcementType) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->updateObject($announcementType);
    }

    /**
     * Delete an announcement type. Note that all announcements with this type are also
     * deleted.
     * @param AnnouncementType $announcementType
     * @return boolean
     */
    public function deleteObject($announcementType) {
        return $this->deleteById($announcementType->getId());
    }

    /**
     * @see deleteObject
     */
    public function deleteAnnouncementType($announcementType) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->deleteObject($announcementType);
    }

    /**
     * Delete an announcement type by announcement type ID. Note that all announcements with
     * this type ID are also deleted.
     * @param int $typeId
     * @return boolean
     */
    public function deleteById($typeId) {
        $this->update('DELETE FROM announcement_type_settings WHERE type_id = ?', (int) $typeId);
        $this->update('DELETE FROM announcement_types WHERE type_id = ?', (int) $typeId);

        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        $announcementDao->deleteByTypeId($typeId);
        return true;
    }

    /**
     * Delete announcement types by association.
     * @param int $assocType
     * @param int $assocId
     */
    public function deleteByAssoc($assocType, $assocId) {
        $types = $this->getByAssoc($assocType, $assocId);
        while (($type = $types->next())) {
            $this->deleteObject($type);
            unset($type);
        }
    }

    /**
     * Retrieve an array of announcement types matching a particular Assoc ID.
     * @param int $assocType
     * @param int $assocId
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory containing matching AnnouncementTypes
     */
    public function getByAssoc($assocType, $assocId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM announcement_types WHERE assoc_type = ? AND assoc_id = ? ORDER BY type_id',
            array((int) $assocType, (int) $assocId),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnAnnouncementTypeFromRow');
        return $returner;
    }

    /**
     * Get the ID of the last inserted announcement type.
     * @return int
     */
    public function getInsertTypeId() {
        return $this->getInsertId('announcement_types', 'type_id');
    }
}

?>
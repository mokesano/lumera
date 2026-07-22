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
     * @return PKPAnnouncementType
     */
    public function newDataObject() {
        return new PKPAnnouncementType();
    }

    /**
     * Retrieve an announcement type by announcement type ID.
     * @param mixed $typeId
     * @return PKPAnnouncementType|null
     */
    public function getById($typeId) {
        $result = $this->retrieve(
            'SELECT * FROM announcement_types WHERE type_id = ?',
            [(int) $typeId]
        );

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_returnAnnouncementTypeFromRow($result->getRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve announcement type Assoc ID by announcement type ID.
     * @param mixed $typeId
     * @return int
     */
    public function getAnnouncementTypeAssocId($typeId) {
        $result = $this->retrieve(
            'SELECT assoc_id FROM announcement_types WHERE type_id = ?',
            [(int) $typeId]
        );

        $returner = 0;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = (int) ($row['assoc_id'] ?? 0);
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve announcement type name by ID.
     * @param mixed $typeId
     * @return string|false
     */
    public function getAnnouncementTypeName($typeId) {
        $result = $this->retrieve(
            'SELECT COALESCE(l.setting_value, p.setting_value) AS setting_value 
             FROM announcement_type_settings p 
             LEFT JOIN announcement_type_settings l ON (l.type_id = ? AND l.setting_name = ? AND l.locale = ?) 
             WHERE p.type_id = ? AND p.setting_name = ? AND p.locale = ?',
            [
                (int) $typeId, 'name', AppLocale::getLocale(),
                (int) $typeId, 'name', AppLocale::getPrimaryLocale()
            ]
        );

        $returner = false;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = $row['setting_value'] ?? false;
        }
        $result->Close();
        return $returner;
    }

    /**
     * Check if an announcement type exists with the given type id for an assoc type/id pair.
     * @param mixed $typeId
     * @param mixed $assocType
     * @param mixed $assocId
     * @return bool
     */
    public function announcementTypeExistsByTypeId($typeId, $assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM announcement_types
            WHERE type_id = ? AND assoc_type = ? AND assoc_id = ?',
            [(int) $typeId, (int) $assocType, (int) $assocId]
        );
        
        $returner = false;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = (int) $row['count'] !== 0;
        }
        $result->Close();
        return $returner;
    }

    /**
     * Get locale field names
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), ['name']);
    }

    /**
     * Return announcement type ID based on a type name for an assoc type/id pair.
     * @param mixed $typeName
     * @param mixed $assocType
     * @param mixed $assocId
     * @return int
     */
    public function getByTypeName($typeName, $assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT ats.type_id
                FROM announcement_type_settings AS ats
                LEFT JOIN announcement_types at ON ats.type_id = at.type_id
                WHERE ats.setting_name = ?
                AND ats.setting_value = ?
                AND at.assoc_type = ?
                AND at.assoc_id = ?',
            ['name', $typeName, (int) $assocType, (int) $assocId]
        );
        
        $returner = 0;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = (int) ($row['type_id'] ?? 0);
        }
        $result->Close();
        return $returner;
    }

    /**
     * Internal function to return an AnnouncementType object from a row.
     * @param array $row
     * @return PKPAnnouncementType
     */
    public function _returnAnnouncementTypeFromRow($row) {
        $announcementType = $this->newDataObject();
        $announcementType->setId((int) $row['type_id']);
        $announcementType->setAssocType((int) $row['assoc_type']);
        $announcementType->setAssocId((int) $row['assoc_id']);
        $this->getDataObjectSettings('announcement_type_settings', 'type_id', (int) $row['type_id'], $announcementType);

        return $announcementType;
    }

    /**
     * Update the localized settings for this object
     * @param PKPAnnouncementType $announcementType
     */
    public function updateLocaleFields($announcementType) {
        $this->updateDataObjectSettings('announcement_type_settings', $announcementType, [
            'type_id' => (int) $announcementType->getId()
        ]);
    }

    /**
     * Insert a new AnnouncementType.
     * @param PKPAnnouncementType $announcementType
     * @return int
     */
    public function insertAnnouncementType($announcementType) {
        $this->update(
            'INSERT INTO announcement_types (assoc_type, assoc_id) VALUES (?, ?)',
            [
                (int) $announcementType->getAssocType(),
                (int) $announcementType->getAssocId()
            ]
        );
        $announcementType->setId($this->getInsertTypeId());
        $this->updateLocaleFields($announcementType);
        return $announcementType->getId();
    }

    /**
     * Update an existing announcement type.
     * @param PKPAnnouncementType $announcementType
     * @return bool
     */
    public function updateObject($announcementType) {
        $this->update(
            'UPDATE announcement_types SET assoc_type = ?, assoc_id = ? WHERE type_id = ?',
            [
                (int) $announcementType->getAssocType(),
                (int) $announcementType->getAssocId(),
                (int) $announcementType->getId()
            ]
        );

        $this->updateLocaleFields($announcementType);
        return true;
    }

    /**
     * [DEPRECATED] Update an existing announcement type.
     * @see updateObject
     * @param PKPAnnouncementType $announcementType
     */
    public function updateAnnouncementType($announcementType) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->updateObject($announcementType);
    }

    /**
     * Delete an announcement type.
     * Note that all announcements with this type are also deleted.
     * @param PKPAnnouncementType $announcementType
     * @return bool
     */
    public function deleteObject($announcementType) {
        return $this->deleteById($announcementType->getId());
    }

    /**
     * [DEPRECATED] Delete an announcement type.
     * @see deleteObject
     * @param PKPAnnouncementType $announcementType
     */
    public function deleteAnnouncementType($announcementType) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->deleteObject($announcementType);
    }

    /**
     * Delete an announcement type by announcement type ID. Note that all announcements with
     * this type ID are also deleted.
     * @param mixed $typeId
     * @return bool
     */
    public function deleteById($typeId) {
        $typeId = (int) $typeId;
        $this->update('DELETE FROM announcement_type_settings WHERE type_id = ?', $typeId);
        $this->update('DELETE FROM announcement_types WHERE type_id = ?', $typeId);

        /** @var AnnouncementDAO $announcementDao */
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        $announcementDao->deleteByTypeId($typeId);
        return true;
    }

    /**
     * Delete announcement types by association.
     * @param mixed $assocType
     * @param mixed $assocId
     */
    public function deleteByAssoc($assocType, $assocId) {
        $types = $this->getByAssoc($assocType, $assocId);
        while ($type = $types->next()) {
            $this->deleteObject($type);
        }
    }

    /**
     * Retrieve an array of announcement types matching a particular Assoc ID.
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getByAssoc($assocType, $assocId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM announcement_types WHERE assoc_type = ? AND assoc_id = ? ORDER BY type_id',
            [(int) $assocType, (int) $assocId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnAnnouncementTypeFromRow');
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
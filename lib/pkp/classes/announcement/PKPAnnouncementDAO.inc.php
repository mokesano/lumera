<?php
declare(strict_types=1);

/**
 * @file classes/announcement/PKPAnnouncementDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAnnouncementDAO
 * @ingroup announcement
 * @see Announcement, PKPAnnouncement
 *
 * @brief Operations for retrieving and modifying Announcement objects.
 */

import('lib.pkp.classes.announcement.PKPAnnouncement');

class PKPAnnouncementDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAnnouncementDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class PKPAnnouncementDAO uses deprecated constructor. Please refactor to __construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Retrieve an announcement by announcement ID.
     * @param mixed $announcementId
     * @return PKPAnnouncement|null
     */
    public function getById($announcementId) {
        $result = $this->retrieve(
            'SELECT * FROM announcements WHERE announcement_id = ?',
            [(int) $announcementId]
        );

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_returnAnnouncementFromRow($result->getRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * [DEPRECATED] Retrieve an announcement.
     * @see getById
     * @param mixed $announcementId
     */
    public function getAnnouncement($announcementId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getById($announcementId);
    }

    /**
     * Retrieve announcement Assoc ID by announcement ID.
     * @param mixed $announcementId
     * @return int
     */
    public function getAnnouncementAssocId($announcementId) {
        $result = $this->retrieve(
            'SELECT assoc_id FROM announcements WHERE announcement_id = ?',
            [(int) $announcementId]
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
     * Retrieve announcement Assoc Type by announcement ID.
     * @param mixed $announcementId
     * @return int
     */
    public function getAnnouncementAssocType($announcementId) {
        $result = $this->retrieve(
            'SELECT assoc_type FROM announcements WHERE announcement_id = ?',
            [(int) $announcementId]
        );

        $returner = 0;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = (int) ($row['assoc_type'] ?? 0);
        }
        $result->Close();
        return $returner;
    }

    /**
     * Get the list of localized field names for this table
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), ['title', 'descriptionShort', 'description']);
    }

    /**
     * Get a new data object.
     * @return PKPAnnouncement
     */
    public function newDataObject() {
        return new PKPAnnouncement();
    }

    /**
     * Internal function to return an Announcement object from a row.
     * @param array $row
     * @return PKPAnnouncement
     */
    public function _returnAnnouncementFromRow($row) {
        $announcement = $this->newDataObject();
        $announcement->setId((int) $row['announcement_id']);
        $announcement->setAssocType((int) $row['assoc_type']);
        $announcement->setAssocId((int) $row['assoc_id']);
        $announcement->setTypeId((int) $row['type_id']);
        $announcement->setDateExpire($this->datetimeFromDB($row['date_expire']));
        $announcement->setDatePosted($this->datetimeFromDB($row['date_posted']));

        $this->getDataObjectSettings('announcement_settings', 'announcement_id', (int) $row['announcement_id'], $announcement);

        return $announcement;
    }

    /**
     * Update the settings for this object
     * @param PKPAnnouncement $announcement
     */
    public function updateLocaleFields($announcement) {
        $this->updateDataObjectSettings('announcement_settings', $announcement, [
            'announcement_id' => (int) $announcement->getId()
        ]);
    }

    /**
     * Insert a new Announcement.
     * @param PKPAnnouncement $announcement
     * @return int
     */
    public function insertAnnouncement($announcement) {
        $this->update(
            'INSERT INTO announcements (assoc_type, assoc_id, type_id, date_expire, date_posted) VALUES (?, ?, ?, ?, ?)',
            [
                (int) $announcement->getAssocType(),
                (int) $announcement->getAssocId(),
                (int) $announcement->getTypeId(),
                $this->datetimeToDB($announcement->getDateExpire()),
                $this->datetimeToDB($announcement->getDatetimePosted())
            ]
        );
        $announcement->setId($this->getInsertAnnouncementId());
        $this->updateLocaleFields($announcement);
        return $announcement->getId();
    }

    /**
     * Update an existing announcement.
     * @param PKPAnnouncement $announcement
     * @return bool
     */
    public function updateObject($announcement) {
        $returner = $this->update(
            'UPDATE announcements SET assoc_type = ?, assoc_id = ?, type_id = ?, date_expire = ?, date_posted = ? WHERE announcement_id = ?',
            [
                (int) $announcement->getAssocType(),
                (int) $announcement->getAssocId(),
                (int) $announcement->getTypeId(),
                $this->datetimeToDB($announcement->getDateExpire()),
                $this->datetimeToDB($announcement->getDatetimePosted()),
                (int) $announcement->getId()
            ]
        );
        $this->updateLocaleFields($announcement);
        return $returner;
    }

    /**
     * [DEPRECATED] Update an existing announcement.
     * @see updateObject
     * @param PKPAnnouncement $announcement
     */
    public function updateAnnouncement($announcement) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->updateObject($announcement);
    }

    /**
     * Delete an announcement.
     * @param PKPAnnouncement $announcement
     * @return bool
     */
    public function deleteObject($announcement) {
        return $this->deleteById($announcement->getId());
    }

    /**
     * [DEPRECATED] Delete an announcement.
     * @see deleteObject
     * @param PKPAnnouncement $announcement
     */
    public function deleteAnnouncement($announcement) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->deleteObject($announcement);
    }

    /**
     * Delete an announcement by announcement ID.
     * @param mixed $announcementId
     * @return bool
     */
    public function deleteById($announcementId) {
        $announcementId = (int) $announcementId;
        $this->update('DELETE FROM announcement_settings WHERE announcement_id = ?', $announcementId);
        return $this->update('DELETE FROM announcements WHERE announcement_id = ?', $announcementId);
    }

    /**
     * [DEPRECATED] Delete an announcement by announcement ID.
     * @see deleteById
     * @param mixed $announcementId
     */
    public function deleteAnnouncementById($announcementId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->deleteById($announcementId);
    }

    /**
     * Delete announcements by announcement type ID.
     * @param mixed $typeId
     * @return bool
     */
    public function deleteByTypeId($typeId) {
        $announcements = $this->getByTypeId($typeId);
        while ($announcement = $announcements->next()) {
            $this->deleteObject($announcement);
        }
        return true;
    }

    /**
     * Delete announcements by Assoc ID
     * @param mixed $assocType
     * @param mixed $assocId
     * @return bool
     */
    public function deleteByAssoc($assocType, $assocId) {
        $announcements = $this->getByAssocId($assocType, $assocId);
        while ($announcement = $announcements->next()) {
            $this->deleteById($announcement->getId());
        }
        return true;
    }

    /**
     * [DEPRECATED] Delete announcements by Assoc ID
     * @see deleteByAssoc
     * @param mixed $assocType
     * @param mixed $assocId
     */
    public function deleteAnnouncementsByAssocId($assocType, $assocId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->deleteByAssoc($assocType, $assocId);
    }

    /**
     * [DEPRECATED] Delete announcements by Assoc ID (Shim for method alias)
     * @param mixed $assocType
     * @param mixed $assocId
     */
    public function deleteByAssocId($assocType, $assocId) {
        return $this->deleteByAssoc($assocType, $assocId);
    }

    /**
     * Retrieve an array of announcements matching a particular assoc ID.
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getByAssocId($assocType, $assocId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM announcements WHERE assoc_type = ? AND assoc_id = ? ORDER BY announcement_id DESC',
            [(int) $assocType, (int) $assocId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
    }

    /**
     * [DEPRECATED] Retrieve an array of announcements matching.
     * @see getByAssocId
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $rangeInfo
     */
    public function getAnnouncementsByAssocId($assocType, $assocId, $rangeInfo = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getByAssocId($assocType, $assocId, $rangeInfo);
    }

    /**
     * Retrieve an array of announcements matching a particular type ID.
     * @param mixed $typeId
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getByTypeId($typeId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM announcements WHERE type_id = ? ORDER BY announcement_id DESC',
            [(int) $typeId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
    }

    /**
     * [DEPRECATED] Retrieve an array of announcements matching.
     * @see getByTypeId
     * @param mixed $typeId
     * @param mixed $rangeInfo
     */
    public function getAnnouncementsByTypeId($typeId, $rangeInfo = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getByTypeId($typeId, $rangeInfo);
    }

    /**
     * Retrieve an array of numAnnouncements announcements matching a particular Assoc ID.
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $numAnnouncements
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getNumAnnouncementsByAssocId($assocType, $assocId, $numAnnouncements, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM announcements WHERE assoc_type = ? AND assoc_id = ? ORDER BY announcement_id DESC LIMIT ?',
            [(int) $assocType, (int) $assocId, (int) $numAnnouncements],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
    }

    /**
     * Retrieve an array of announcements with no/valid expiry date matching a particular Assoc ID.
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getAnnouncementsNotExpiredByAssocId($assocType, $assocId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM announcements
             WHERE assoc_type = ? AND assoc_id = ?
               AND (date_expire IS NULL OR DATE(date_expire) > CURRENT_DATE)
               AND (DATE(date_posted) <= CURRENT_DATE)
             ORDER BY announcement_id DESC',
            [(int) $assocType, (int) $assocId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
    }

    /**
     * Retrieve an array of numAnnouncements announcements with no/valid expiry date matching a particular Assoc ID.
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $numAnnouncements
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getNumAnnouncementsNotExpiredByAssocId($assocType, $assocId, $numAnnouncements, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM announcements
             WHERE assoc_type = ? AND assoc_id = ?
               AND (date_expire IS NULL OR DATE(date_expire) > CURRENT_DATE)
               AND (DATE(date_posted) <= CURRENT_DATE)
             ORDER BY announcement_id DESC LIMIT ?',
            [(int) $assocType, (int) $assocId, (int) $numAnnouncements],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
    }

    /**
     * Retrieve most recent published announcement by Assoc ID.
     * @param mixed $assocType
     * @param mixed $assocId
     * @return PKPAnnouncement|null
     */
    public function getMostRecentPublishedAnnouncementByAssocId($assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT * FROM announcements
             WHERE assoc_type = ? AND assoc_id = ?
               AND (date_expire IS NULL OR DATE(date_expire) > CURRENT_DATE)
               AND (DATE(date_posted) <= CURRENT_DATE)
             ORDER BY announcement_id DESC LIMIT 1',
            [(int) $assocType, (int) $assocId]
        );

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_returnAnnouncementFromRow($result->getRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Get the ID of the last inserted announcement.
     * @return int
     */
    public function getInsertAnnouncementId() {
        return $this->getInsertId('announcements', 'announcement_id');
    }

}
?>
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
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor, Ref removal)
 * - Strict Integer Casting
 * - Date Logic Safety
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
     * @param int $announcementId
     * @return Announcement|null
     */
    public function getById($announcementId) {
        $result = $this->retrieve(
            'SELECT * FROM announcements WHERE announcement_id = ?',
            (int) $announcementId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnAnnouncementFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * @see getById
     */
    public function getAnnouncement($announcementId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getById($announcementId);
    }

    /**
     * Retrieve announcement Assoc ID by announcement ID.
     * @param int $announcementId
     * @return int
     */
    public function getAnnouncementAssocId($announcementId) {
        $result = $this->retrieve(
            'SELECT assoc_id FROM announcements WHERE announcement_id = ?',
            (int) $announcementId
        );

        return isset($result->fields[0]) ? (int)$result->fields[0] : 0;
    }

    /**
     * Retrieve announcement Assoc ID by announcement ID.
     * @param int $announcementId
     * @return int
     */
    public function getAnnouncementAssocType($announcementId) {
        $result = $this->retrieve(
            'SELECT assoc_type FROM announcements WHERE announcement_id = ?',
            (int) $announcementId
        );

        return isset($result->fields[0]) ? (int)$result->fields[0] : 0;
    }

    /**
     * Get the list of localized field names for this table
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), array('title', 'descriptionShort', 'description'));
    }

    /**
     * Get a new data object.
     * @return DataObject
     */
    public function newDataObject() {
        // assert(false); // Removed for production safety, but should be overridden
        return new PKPAnnouncement();
    }

    /**
     * Internal function to return an Announcement object from a row.
     * @param array $row
     * @return Announcement
     */
    public function _returnAnnouncementFromRow($row) {
        $announcement = $this->newDataObject();
        $announcement->setId($row['announcement_id']);
        $announcement->setAssocType($row['assoc_type']);
        $announcement->setAssocId($row['assoc_id']);
        $announcement->setTypeId($row['type_id']);
        $announcement->setDateExpire($this->datetimeFromDB($row['date_expire']));
        $announcement->setDatePosted($this->datetimeFromDB($row['date_posted']));

        $this->getDataObjectSettings('announcement_settings', 'announcement_id', $row['announcement_id'], $announcement);

        return $announcement;
    }

    /**
     * Update the settings for this object
     * @param Announcement $announcement
     */
    public function updateLocaleFields($announcement) {
        $this->updateDataObjectSettings('announcement_settings', $announcement, array(
            'announcement_id' => $announcement->getId()
        ));
    }

    /**
     * Insert a new Announcement.
     * @param Announcement $announcement
     * @return int
     */
    public function insertAnnouncement($announcement) {
        $this->update(
            sprintf('INSERT INTO announcements
                (assoc_type, assoc_id, type_id, date_expire, date_posted)
                VALUES
                (?, ?, ?, %s, %s)',
                $this->datetimeToDB($announcement->getDateExpire()),
                $this->datetimeToDB($announcement->getDatetimePosted())
            ), array(
                (int) $announcement->getAssocType(),
                (int) $announcement->getAssocId(),
                (int) $announcement->getTypeId()
            )
        );
        $announcement->setId($this->getInsertAnnouncementId());
        $this->updateLocaleFields($announcement);
        return $announcement->getId();
    }

    /**
     * Update an existing announcement.
     * @param Announcement $announcement
     * @return boolean
     */
    public function updateObject($announcement) {
        $returner = $this->update(
            sprintf('UPDATE announcements
                SET
                    assoc_type = ?,
                    assoc_id = ?,
                    type_id = ?,
                    date_expire = %s,
                    date_posted = %s
                WHERE announcement_id = ?',
                $this->datetimeToDB($announcement->getDateExpire()),
                $this->datetimeToDB($announcement->getDatetimePosted())),
            array(
                (int) $announcement->getAssocType(),
                (int) $announcement->getAssocId(),
                (int) $announcement->getTypeId(),
                (int) $announcement->getId()
            )
        );
        $this->updateLocaleFields($announcement);
        return $returner;
    }

    /**
     * @see updateObject
     */
    public function updateAnnouncement($announcement) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->updateObject($announcement);
    }

    /**
     * Delete an announcement.
     * @param Announcement $announcement
     * @return boolean
     */
    public function deleteObject($announcement) {
        return $this->deleteById($announcement->getId());
    }

    /**
     * @see deleteObject
     */
    public function deleteAnnouncement($announcement) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->deleteObject($announcement);
    }

    /**
     * Delete an announcement by announcement ID.
     * @param int $announcementId
     * @return boolean
     */
    public function deleteById($announcementId) {
        $this->update('DELETE FROM announcement_settings WHERE announcement_id = ?', (int) $announcementId);
        return $this->update('DELETE FROM announcements WHERE announcement_id = ?', (int) $announcementId);
    }

    /**
     * @see deleteById
     */
    public function deleteAnnouncementById($announcementId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->deleteById($announcementId);
    }

    /**
     * Delete announcements by announcement type ID.
     * @param int $typeId
     * @return boolean
     */
    public function deleteByTypeId($typeId) {
        $announcements = $this->getByTypeId($typeId);
        while (($announcement = $announcements->next())) {
            $this->deleteObject($announcement);
            unset($announcement);
        }
        return true;
    }

    /**
     * Delete announcements by Assoc ID
     * @param int $assocType
     * @param int $assocId
     * @return boolean
     */
    public function deleteByAssoc($assocType, $assocId) {
        $announcements = $this->getByAssocId($assocType, $assocId);
        while (($announcement = $announcements->next())) {
            $this->deleteById($announcement->getId());
            unset($announcement);
        }
        return true;
    }

    /**
     * @see deleteByAssocId
     */
    public function deleteAnnouncementsByAssocId($assocType, $assocId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->deleteByAssocId($assocType, $assocId);
    }

    // Shim for method alias
    public function deleteByAssocId($assocType, $assocId) {
        return $this->deleteByAssoc($assocType, $assocId);
    }

    /**
     * Retrieve an array of announcements matching a particular assoc ID.
     * @param int $assocType
     * @param int $assocId
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory containing matching Announcements
     */
    public function getByAssocId($assocType, $assocId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT *
            FROM announcements
            WHERE assoc_type = ? AND assoc_id = ?
            ORDER BY announcement_id DESC',
            array((int) $assocType, (int) $assocId),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
        return $returner;
    }

    /**
     * @see getByAssocId
     */
    public function getAnnouncementsByAssocId($assocType, $assocId, $rangeInfo = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getByAssocId($assocType, $assocId, $rangeInfo);
    }

    /**
     * Retrieve an array of announcements matching a particular type ID.
     * @param int $typeId
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory containing matching Announcements
     */
    public function getByTypeId($typeId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM announcements WHERE type_id = ? ORDER BY announcement_id DESC',
            (int) $typeId,
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
        return $returner;
    }

    /**
     * @see getByTypeId
     */
    public function getAnnouncementsByTypeId($typeId, $rangeInfo = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getByTypeId($typeId, $rangeInfo);
    }

    /**
     * Retrieve an array of numAnnouncements announcements matching a particular Assoc ID.
     * @param int $assocType
     * @param int $assocId
     * @param int $numAnnouncements
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory containing matching Announcements
     */
    public function getNumAnnouncementsByAssocId($assocType, $assocId, $numAnnouncements, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT *
            FROM announcements
            WHERE assoc_type = ?
                AND assoc_id = ?
            ORDER BY announcement_id DESC LIMIT ?',
            array((int) $assocType, (int) $assocId, (int) $numAnnouncements),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
        return $returner;
    }

    /**
     * Retrieve an array of announcements with no/valid expiry date matching a particular Assoc ID.
     * @param int $assocType
     * @param int $assocId
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory containing matching Announcements
     */
    public function getAnnouncementsNotExpiredByAssocId($assocType, $assocId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT *
            FROM announcements
            WHERE assoc_type = ?
                AND assoc_id = ?
                AND (date_expire IS NULL OR DATE(date_expire) > CURRENT_DATE)
                AND (DATE(date_posted) <= CURRENT_DATE)
            ORDER BY announcement_id DESC',
            array((int) $assocType, (int) $assocId),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
        return $returner;
    }

    /**
     * Retrieve an array of numAnnouncements announcements with no/valid expiry date matching a particular Assoc ID.
     * @param int $assocType
     * @param int $assocId
     * @param int $numAnnouncements
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory containing matching Announcements
     */
    public function getNumAnnouncementsNotExpiredByAssocId($assocType, $assocId, $numAnnouncements, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT *
            FROM announcements
            WHERE assoc_type = ?
                AND assoc_id = ?
                AND (date_expire IS NULL OR DATE(date_expire) > CURRENT_DATE)
                AND (DATE(date_posted) <= CURRENT_DATE)
            ORDER BY announcement_id DESC LIMIT ?',
            array((int) $assocType, (int) $assocId, (int) $numAnnouncements),
            $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnAnnouncementFromRow');
        return $returner;
    }

    /**
     * Retrieve most recent published announcement by Assoc ID.
     * @param int $assocType
     * @param int $assocId
     * @return Announcement|null
     */
    public function getMostRecentPublishedAnnouncementByAssocId($assocType, $assocId) {
        $result = $this->retrieve(
            'SELECT *
            FROM announcements
            WHERE assoc_type = ?
                AND assoc_id = ?
                AND (date_expire IS NULL OR DATE(date_expire) > CURRENT_DATE)
                AND (DATE(date_posted) <= CURRENT_DATE)
            ORDER BY announcement_id DESC LIMIT 1',
            array((int) $assocType, (int) $assocId)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnAnnouncementFromRow($result->GetRowAssoc(false));
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
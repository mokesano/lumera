<?php
declare(strict_types=1);

/**
 * @file classes/signoff/PKPSignoffDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPSignoffDAO
 * @ingroup signoff
 * @see Signoff
 *
 * @brief Operations for retrieving and modifying Signoff objects.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructor, Ref removal)
 * - Strict Integer Casting
 * - Null Safety
 */

import('lib.pkp.classes.signoff.Signoff');

class PKPSignoffDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPSignoffDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class PKPSignoffDAO uses deprecated constructor parent::PKPSignoffDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Retrieve a signoff by ID.
     * @param int $signoffId
     * @param int|null $assocType
     * @param int|null $assocId
     * @return Signoff|null
     */
    public function getById($signoffId, $assocType = null, $assocId = null) {
        $params = array((int) $signoffId);
        if ($assocType !== null) $params[] = (int) $assocType;
        if ($assocId !== null) $params[] = (int) $assocId;
        
        $result = $this->retrieve(
            'SELECT * FROM signoffs WHERE signoff_id = ?'
            . ($assocType !== null ? ' AND assoc_type = ?' : '')
            . ($assocId !== null ? ' AND assoc_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Fetch a signoff by symbolic info, building it if needed.
     * @param string $symbolic
     * @param int $assocType
     * @param int $assocId
     * @param int|null $userId
     * @param int|null $userGroupId
     * @param int|null $fileId
     * @param int|null $fileRevision
     * @return Signoff
     */
    public function build($symbolic, $assocType, $assocId, $userId = null,
            $userGroupId = null, $fileId = null, $fileRevision = null) {

        // If one exists, fetch and return.
        $signoff = $this->getBySymbolic(
            $symbolic, $assocType, $assocId, $userId,
            $userGroupId, $fileId, $fileRevision
        );
        if ($signoff) return $signoff;

        // Otherwise, build one.
        $signoff = $this->newDataObject();
        $signoff->setSymbolic($symbolic);
        $signoff->setAssocType($assocType);
        $signoff->setAssocId($assocId);
        $signoff->setUserId($userId);
        $signoff->setUserGroupId($userGroupId);
        $signoff->setFileId($fileId);
        $signoff->setFileRevision($fileRevision);
        $this->insertObject($signoff);
        return $signoff;
    }

    /**
     * Determine if a signoff exists
     * @param string $symbolic
     * @param int $assocType
     * @param int $assocId
     * @param int|null $userId
     * @param int|null $userGroupId
     * @return boolean
     */
    public function signoffExists($symbolic, $assocType, $assocId, $userId = null, $userGroupId = null) {
        $sql = 'SELECT COUNT(*) FROM signoffs WHERE symbolic = ? AND assoc_type = ? AND assoc_id = ?';
        $params = array($symbolic, (int) $assocType, (int) $assocId);

        if ($userId) {
            $sql .= ' AND user_id = ?';
            $params[] = (int) $userId;
        }

        if ($userGroupId) {
            $sql .= ' AND user_group_id = ?';
            $params[] = (int) $userGroupId;
        }

        $result = $this->retrieve($sql, $params);

        $returner = isset($result->fields[0]) && $result->fields[0] > 0 ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return Signoff
     */
    public function newDataObject() {
        return new Signoff();
    }

    /**
     * Internal function to return an Signoff object from a row.
     * @param array $row
     * @return Signoff
     */
    public function _fromRow($row) {
        $signoff = $this->newDataObject();

        $signoff->setId($row['signoff_id']);
        $signoff->setAssocType($row['assoc_type']);
        $signoff->setAssocId($row['assoc_id']);
        $signoff->setSymbolic($row['symbolic']);
        $signoff->setUserId($row['user_id']);
        $signoff->setFileId($row['file_id']);
        $signoff->setFileRevision($row['file_revision']);
        $signoff->setDateNotified($this->datetimeFromDB($row['date_notified']));
        $signoff->setDateUnderway($this->datetimeFromDB($row['date_underway']));
        $signoff->setDateCompleted($this->datetimeFromDB($row['date_completed']));
        $signoff->setDateAcknowledged($this->datetimeFromDB($row['date_acknowledged']));
        $signoff->setUserGroupId($row['user_group_id']);

        return $signoff;
    }

    /**
     * Insert a new Signoff.
     * @param Signoff $signoff (No & needed)
     * @return int
     */
    public function insertObject($signoff) {
        $this->update(
            sprintf(
                'INSERT INTO signoffs
                (symbolic, assoc_type, assoc_id, user_id, user_group_id, file_id, file_revision, date_notified, date_underway, date_completed, date_acknowledged)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, %s, %s, %s, %s)',
                $this->datetimeToDB($signoff->getDateNotified()),
                $this->datetimeToDB($signoff->getDateUnderway()),
                $this->datetimeToDB($signoff->getDateCompleted()),
                $this->datetimeToDB($signoff->getDateAcknowledged())
            ),
            array(
                $signoff->getSymbolic(),
                (int) $signoff->getAssocType(),
                (int) $signoff->getAssocId(),
                (int) $signoff->getUserId(),
                $this->nullOrInt($signoff->getUserGroupId()),
                $this->nullOrInt($signoff->getFileId()),
                $this->nullOrInt($signoff->getFileRevision())
            )
        );
        $signoff->setId($this->getInsertId());
        return $signoff->getId();
    }

    /**
     * Update an existing signoff.
     * @param Signoff $signoff (No & needed)
     * @return boolean
     */
    public function updateObject($signoff) {
        $returner = $this->update(
            sprintf(
                'UPDATE signoffs
                SET symbolic = ?,
                    assoc_type = ?,
                    assoc_id = ?,
                    user_id = ?,
                    user_group_id = ?,
                    file_id = ?,
                    file_revision = ?,
                    date_notified = %s,
                    date_underway = %s,
                    date_completed = %s,
                    date_acknowledged = %s
                WHERE signoff_id = ?',
                $this->datetimeToDB($signoff->getDateNotified()),
                $this->datetimeToDB($signoff->getDateUnderway()),
                $this->datetimeToDB($signoff->getDateCompleted()),
                $this->datetimeToDB($signoff->getDateAcknowledged())
            ),
            array(
                $signoff->getSymbolic(),
                (int) $signoff->getAssocType(),
                (int) $signoff->getAssocId(),
                (int) $signoff->getUserId(),
                $this->nullOrInt($signoff->getUserGroupId()),
                $this->nullOrInt($signoff->getFileId()),
                $this->nullOrInt($signoff->getFileRevision()),
                (int) $signoff->getId()
            )
        );
        return $returner;
    }

    /**
     * Delete a signoff.
     * @param Signoff $signoff
     * @return boolean
     */
    public function deleteObject($signoff) {
        return $this->deleteObjectById($signoff->getId());
    }

    /**
     * Delete a signoff by ID.
     * @param int $signoffId
     * @return boolean
     */
    public function deleteObjectById($signoffId) {
        return $this->update('DELETE FROM signoffs WHERE signoff_id = ?', array((int) $signoffId));
    }

    /**
     * Retrieve the first signoff matching the specified symbolic name and assoc info.
     * @param string $symbolic
     * @param int $assocType
     * @param int $assocId
     * @param int|null $userId
     * @param int|null $userGroupId
     * @param int|null $fileId
     * @param int|null $fileRevision
     * @return Signoff|null
     */
    public function getBySymbolic($symbolic, $assocType, $assocId, $userId = null,
            $userGroupId = null, $fileId = null, $fileRevision = null) {

        $sql = 'SELECT * FROM signoffs WHERE symbolic = ? AND assoc_type = ? AND assoc_id = ?';
        $params = array($symbolic, (int) $assocType, (int) $assocId);

        if ($userId) {
            $sql .= ' AND user_id = ?';
            $params[] = (int) $userId;
        }

        if ($userGroupId) {
            $sql .= ' AND user_group_id = ?';
            $params[] = (int) $userGroupId;
        }

        if ($fileId) {
            $sql .= ' AND file_id = ?';
            $params[] = (int) $fileId;
        }

        if ($fileRevision) {
            $sql .= ' AND file_revision = ?';
            $params[] = (int) $fileRevision;
        }

        $result = $this->retrieve($sql, $params);

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_fromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve all signoffs matching the specified input parameters
     * @param string $symbolic
     * @param int|null $assocType
     * @param int|null $assocId
     * @param int|null $userId
     * @param int|null $userGroupId
     * @return DAOResultFactory
     */
    public function getAllBySymbolic($symbolic, $assocType = null, $assocId = null, $userId = null, $userGroupId = null) {
        return $this->_getAllInternally($symbolic, $assocType, $assocId, $userId, $userGroupId);
    }

    /**
     * Retrieve all signoffs matching the specified input parameters
     * @param int $assocType
     * @param int $assocId
     * @param string|null $symbolic
     * @param int|null $userId
     * @param int|null $userGroupId
     * @return DAOResultFactory
     */
    public function getAllByAssocType($assocType, $assocId, $symbolic = null, $userId = null, $userGroupId = null) {
        return $this->_getAllInternally($symbolic, $assocType, $assocId, $userId, $userGroupId);
    }

    /**
     * Retrieve an array of signoffs matching the specified user id
     * @param int $userId
     * @return DAOResultFactory
     */
    public function getByUserId($userId) {
        $sql = 'SELECT * FROM signoffs WHERE user_id = ?';
        $params = array((int) $userId);

        $result = $this->retrieve($sql, $params);

        $returner = new DAOResultFactory($result, $this, '_fromRow', array('id'));
        return $returner;
    }

    /**
     * Retrieve all signoffs for a given file.
     * @param int $fileId
     * @param int|null $revision
     * @return DAOResultFactory
     */
    public function getByFileRevision($fileId, $revision = null) {
        $sql = 'SELECT * FROM signoffs WHERE file_id = ?';
        $params = array((int)$fileId);
        if ($revision) {
            $sql .= ' AND file_revision = ?';
            $params[] = (int)$revision;
        }
        $result = $this->retrieve($sql, $params);
        $returner = new DAOResultFactory($result, $this, '_fromRow', array('id'));
        return $returner;
    }

    /**
     * Get all users assigned to a particular workflow stage
     * @param string $symbolic
     * @param int $assocType
     * @param int $assocId
     * @param int|null $userGroupId
     * @param boolean $unique
     * @return DAOResultFactory
     */
    public function getUsersBySymbolic($symbolic, $assocType, $assocId, $userGroupId = null, $unique = true) {
        $selectDistinct = $unique ? 'SELECT DISTINCT' : 'SELECT';

        $sql = $selectDistinct . ' u.* FROM users u, signoffs s
                WHERE u.user_id = s.user_id AND s.symbolic = ? AND s.assoc_type = ? AND s.assoc_id = ?';
        $params = array($symbolic, (int) $assocType, (int) $assocId);

        if ($userGroupId) {
            $sql .= ' AND s.user_group_id = ?';
            $params[] = (int) $userGroupId;
        }

        $result = $this->retrieve($sql, $params);

        $userDao = DAORegistry::getDAO('UserDAO');
        $returner = new DAOResultFactory($result, $userDao, '_returnUserFromRow');
        return $returner;
    }

    /**
     * Transfer all existing signoffs to another user.
     * @param int $oldUserId
     * @param int $newUserId
     */
    public function transferSignoffs($oldUserId, $newUserId) {
        return $this->update(
            'UPDATE signoffs
            SET user_id = ?
            WHERE user_id = ?',
            array(
                (int) $newUserId,
                (int) $oldUserId
            )
        );
    }

    /**
     * Get the ID of the last inserted signoff.
     * @param string $table
     * @param string $id
     * @param boolean $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        return parent::getInsertId('signoffs', 'signoff_id');
    }

    /**
     * Get an array map with all signoff symbolics.
     * @return array
     */
    public function getAllSymbolics() {
        return array(
            'SIGNOFF_COPYEDITING',
            'SIGNOFF_PROOFING',
            'SIGNOFF_FAIR_COPY',
            'SIGNOFF_REVIEW_REVISION',
            'SIGNOFF_SIGNOFF'
        );
    }


    //
    // Private helper methods.
    //
    /**
     * Retrieve all signoffs matching the specified input parameters
     * @param string|null $symbolic
     * @param int|null $assocType
     * @param int|null $assocId
     * @param int|null $userId
     * @param int|null $userGroupId
     * @return DAOResultFactory
     */
    public function _getAllInternally($symbolic = null, $assocType = null, $assocId = null, $userId = null, $userGroupId = null) {
        $sql = 'SELECT * FROM signoffs';
        $conditions = array();
        $params = array();

        if ($symbolic) {
            $conditions[] = 'symbolic = ?';
            $params[] = $symbolic;
        }

        if ($assocType) {
            $conditions[] = 'assoc_type = ?';
            $params[] = (int) $assocType;
        }

        if ($assocId) {
            $conditions[] = 'assoc_id = ?';
            $params[] = (int) $assocId;
        }

        if ($userId) {
            $conditions[] = 'user_id = ?';
            $params[] = (int) $userId;
        }

        if ($userGroupId) {
            $conditions[] = 'user_group_id = ?';
            $params[] = (int) $userGroupId;
        }

        if (count($conditions) > 0) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY signoff_id';

        $result = $this->retrieve($sql, $params);

        $returner = new DAOResultFactory($result, $this, '_fromRow', array('id'));
        return $returner;
    }
}

?>
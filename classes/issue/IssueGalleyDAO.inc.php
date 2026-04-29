<?php
declare(strict_types=1);

/**
 * @file classes/issue/IssueGalleyDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueGalleyDAO
 * @ingroup issue
 * @see IssueGalley
 *
 * @brief Operations for retrieving and modifying IssueGalley objects.
 */

import('classes.issue.IssueGalley');

class IssueGalleyDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Legacy Constructor Shim.
     */
    public function IssueGalleyDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::IssueGalleyDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Retrieve a galley by ID.
     * @param $galleyId int
     * @param $issueId int optional
     * @return IssueGalley
     */
    public function getGalley($galleyId, $issueId = null) {
        $params = array((int) $galleyId);
        if ($issueId !== null) $params[] = (int) $issueId;
        
        $sql = 'SELECT
                g.*,
                f.file_name,
                f.original_file_name,
                f.file_type,
                f.file_size,
                f.content_type,
                f.date_uploaded,
                f.date_modified
            FROM issue_galleys g
                LEFT JOIN issue_files f ON (g.file_id = f.file_id)
            WHERE g.galley_id = ?' .
            ($issueId !== null ? ' AND g.issue_id = ?' : '');

        $result = $this->retrieve($sql, $params);

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnGalleyFromRow($result->GetRowAssoc(false));
        } else {
            HookRegistry::dispatch('IssueGalleyDAO::getGalley', array(&$galleyId, &$issueId, &$returner));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Checks if public identifier exists (other than for the specified
     * galley ID, which is treated as an exception).
     * @param $pubIdType string
     * @param $pubId string
     * @param $galleyId int
     * @param $journalId int
     * @return boolean
     */
    public function pubIdExists($pubIdType, $pubId, $galleyId, $journalId) {
        $result = $this->retrieve(
            'SELECT COUNT(*)
            FROM issue_galley_settings igs
                INNER JOIN issue_galleys ig ON igs.galley_id = ig.galley_id
                INNER JOIN issues i ON ig.issue_id = i.issue_id
            WHERE igs.setting_name = ? AND igs.setting_value = ? AND igs.galley_id <> ? AND i.journal_id = ?',
            array(
                'pub-id::'.$pubIdType,
                $pubId,
                (int) $galleyId,
                (int) $journalId
            )
        );
        $returner = $result->fields[0] ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve a galley by ID.
     * @param $pubIdType string
     * @param $pubId string
     * @param $issueId int
     * @return IssueGalley
     */
    public function getGalleyByPubId($pubIdType, $pubId, $issueId) {
        $result = $this->retrieve(
            'SELECT
                g.*,
                f.file_name,
                f.original_file_name,
                f.file_type,
                f.file_size,
                f.content_type,
                f.date_uploaded,
                f.date_modified
            FROM issue_galleys g
                INNER JOIN issue_galley_settings gs ON g.galley_id = gs.galley_id
                LEFT JOIN issue_files f ON (g.file_id = f.file_id)
            WHERE    gs.setting_name = ? AND
                gs.setting_value = ? AND
                g.issue_id = ?',
            array('pub-id::'.$pubIdType, $pubId, (int) $issueId)
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnGalleyFromRow($result->GetRowAssoc(false));
        } else {
            // Note: Hook registry still uses references as per requirement
            $galleyId = null; // Placeholder as this function doesn't take galleyId
            HookRegistry::dispatch('IssueGalleyDAO::getGalleyByPubId', array(&$galleyId, &$issueId, &$returner));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve all galleys for an issue.
     * @param $issueId int
     * @return array IssueGalleys
     */
    public function getGalleysByIssue($issueId) {
        $galleys = array();

        $result = $this->retrieve(
            'SELECT
                g.*,
                f.file_name,
                f.original_file_name,
                f.file_type,
                f.file_size,
                f.content_type,
                f.date_uploaded,
                f.date_modified
            FROM issue_galleys g
                LEFT JOIN issue_files f ON (g.file_id = f.file_id)
            WHERE g.issue_id = ? ORDER BY g.seq',
            (int) $issueId
        );

        while (!$result->EOF) {
            $galleys[] = $this->_returnGalleyFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        HookRegistry::dispatch('IssueGalleyDAO::getGalleysByIssue', array(&$galleys, &$issueId));

        return $galleys;
    }

    /**
     * Retrieve issue galley by public galley id or, failing that,
     * internal galley ID; public galley ID takes precedence.
     * @param $galleyId string
     * @param $issueId int
     * @return IssueGalley
     */
    public function getGalleyByBestGalleyId($galleyId, $issueId) {
        $galley = null;
        if ($galleyId != '') $galley = $this->getGalleyByPubId('publisher-id', $galleyId, $issueId);
        if (!isset($galley) && ctype_digit("$galleyId")) $galley = $this->getGalley((int) $galleyId, $issueId);
        return $galley;
    }

    /**
     * Get the list of fields for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array();
    }

    /**
     * Get a list of additional fields that do not have
     * dedicated accessors.
     * @return array
     */
    public function getAdditionalFieldNames() {
        $additionalFields = parent::getAdditionalFieldNames();
        // FIXME: Move this to a PID plug-in.
        $additionalFields[] = 'pub-id::publisher-id';
        return $additionalFields;
    }

    /**
     * Update the localized fields for this galley.
     * @param $galley IssueGalley
     */
    public function updateLocaleFields($galley) {
        $this->updateDataObjectSettings('issue_galley_settings', $galley, array(
            'galley_id' => $galley->getId()
        ));
    }

    /**
     * Internal function to return an IssueGalley object from a row.
     * @param $row array
     * @return IssueGalley
     */
    public function _returnGalleyFromRow($row) {
        $galley = new IssueGalley();

        $galley->setId($row['galley_id']);
        $galley->setIssueId($row['issue_id']);
        $galley->setLocale($row['locale']);
        $galley->setFileId($row['file_id']);
        $galley->setLabel($row['label']);
        $galley->setSequence($row['seq']);

        // IssueFile set methods
        $galley->setFileName($row['file_name']);
        $galley->setOriginalFileName($row['original_file_name']);
        $galley->setFileType($row['file_type']);
        $galley->setFileSize($row['file_size']);
        $galley->setContentType($row['content_type']);
        $galley->setDateModified($this->datetimeFromDB($row['date_modified']));
        $galley->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));

        $this->getDataObjectSettings('issue_galley_settings', 'galley_id', $row['galley_id'], $galley);

        HookRegistry::dispatch('IssueGalleyDAO::_returnGalleyFromRow', array(&$galley, &$row));

        return $galley;
    }

    /**
     * Insert a new IssueGalley.
     * @param $galley IssueGalley
     * @return int
     */
    public function insertGalley($galley) {
        $this->update(
            'INSERT INTO issue_galleys
                (issue_id,
                file_id,
                label,
                locale,
                seq)
                VALUES
                (?, ?, ?, ?, ?)',
            array(
                (int) $galley->getIssueId(),
                (int) $galley->getFileId(),
                $galley->getLabel(),
                $galley->getLocale(),
                $galley->getSequence() == null ? $this->getNextGalleySequence($galley->getIssueId()) : $galley->getSequence()
            )
        );
        $galley->setId($this->getInsertGalleyId());
        $this->updateLocaleFields($galley);

        HookRegistry::dispatch('IssueGalleyDAO::insertGalley', array(&$galley, $galley->getId()));

        return $galley->getId();
    }

    /**
     * Update an existing IssueGalley.
     * @param $galley IssueGalley
     */
    public function updateGalley($galley) {
        $this->update(
            'UPDATE issue_galleys
                SET
                    file_id = ?,
                    label = ?,
                    locale = ?,
                    seq = ?
                WHERE galley_id = ?',
            array(
                (int) $galley->getFileId(),
                $galley->getLabel(),
                $galley->getLocale(),
                $galley->getSequence(),
                (int) $galley->getId()
            )
        );
        $this->updateLocaleFields($galley);
    }

    /**
     * Delete an IssueGalley.
     * @param $galley IssueGalley
     */
    public function deleteGalley($galley) {
        return $this->deleteGalleyById($galley->getId(), $galley->getIssueId());
    }

    /**
     * Delete a galley by ID.
     * @param $galleyId int
     * @param $issueId int optional
     */
    public function deleteGalleyById($galleyId, $issueId = null) {
        HookRegistry::dispatch('IssueGalleyDAO::deleteGalleyById', array(&$galleyId, &$issueId));

        if (isset($issueId)) {
            $this->update(
                'DELETE FROM issue_galleys WHERE galley_id = ? AND issue_id = ?',
                array((int) $galleyId, (int) $issueId)
            );
        } else {
            $this->update(
                'DELETE FROM issue_galleys WHERE galley_id = ?', (int) $galleyId
            );
        }
        if ($this->getAffectedRows()) {
            $this->update('DELETE FROM issue_galley_settings WHERE galley_id = ?', array((int) $galleyId));
        }
    }

    /**
     * Delete galleys by issue.
     * NOTE that this will not delete issue_file entities or the respective files.
     * @param $issueId int
     */
    public function deleteGalleysByIssue($issueId) {
        $galleys = $this->getGalleysByIssue($issueId);
        foreach ($galleys as $galley) {
            $this->deleteGalleyById($galley->getId(), $issueId);
        }
    }

    /**
     * Check if a galley exists with the associated file ID.
     * @param $issueId int
     * @param $fileId int
     * @return boolean
     */
    public function galleyExistsByFileId($issueId, $fileId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM issue_galleys
            WHERE issue_id = ? AND file_id = ?',
            array((int) $issueId, (int) $fileId)
        );

        $returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Increment the views count for a galley.
     * @param $galleyId int
     */
    public function incrementViews($galleyId) {
        if ( !HookRegistry::dispatch('IssueGalleyDAO::incrementViews', array(&$galleyId)) ) {
            return $this->update(
                'UPDATE issue_galleys SET views = views + 1 WHERE galley_id = ?',
                (int) $galleyId
            );
        } else return false;
    }

    /**
     * Sequentially renumber galleys for an issue in their sequence order.
     * @param $issueId int
     */
    public function resequenceGalleys($issueId) {
        $result = $this->retrieve(
            'SELECT galley_id FROM issue_galleys WHERE issue_id = ? ORDER BY seq',
            (int) $issueId
        );

        for ($i=1; !$result->EOF; $i++) {
            list($galleyId) = $result->fields;
            $this->update(
                'UPDATE issue_galleys SET seq = ? WHERE galley_id = ?',
                array($i, $galleyId)
            );
            $result->moveNext();
        }

        $result->close();
        unset($result);
    }

    /**
     * Get the the next sequence number for an issue's galleys (i.e., current max + 1).
     * @param $issueId int
     * @return int
     */
    public function getNextGalleySequence($issueId) {
        $result = $this->retrieve(
            'SELECT MAX(seq) + 1 FROM issue_galleys WHERE issue_id = ?',
            (int) $issueId
        );
        $returner = floor($result->fields[0]);

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Get the ID of the last inserted gallery.
     * @return int
     */
    public function getInsertGalleyId() {
        return $this->getInsertId('issue_galleys', 'galley_id');
    }
}

?>
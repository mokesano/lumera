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
     * [SHIM] Backward Compatibility.
     */
    public function IssueGalleyDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::IssueGalleyDAO(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Retrieve a galley by ID.
     * @param mixed $galleyId
     * @param mixed $issueId
     * @return IssueGalley|null
     */
    public function getGalley($galleyId, $issueId = null) {
        $params = [(int) $galleyId];
        if ($issueId !== null) {
            $params[] = (int) $issueId;
        }
        
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
        // [WIZDAM] Modernization: Use !$result->EOF and getRowAssoc
        if (!$result->EOF) {
            $returner = $this->_returnGalleyFromRow($result->getRowAssoc(false));
        } else {
            HookRegistry::dispatch('IssueGalleyDAO::getGalley', [&$galleyId, &$issueId, &$returner]);
        }

        $result->Close();
        return $returner;
    }

    /**
     * Checks if public identifier exists (other than for the specified
     * galley ID, which is treated as an exception).
     * @param string $pubIdType
     * @param string $pubId
     * @param mixed $galleyId
     * @param mixed $journalId
     * @return bool
     */
    public function pubIdExists($pubIdType, $pubId, $galleyId, $journalId) {
        // [WIZDAM] Added 'AS count' to safely access the result in PHP 8+
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count
            FROM issue_galley_settings igs
                INNER JOIN issue_galleys ig ON igs.galley_id = ig.galley_id
                INNER JOIN issues i ON ig.issue_id = i.issue_id
            WHERE igs.setting_name = ? AND igs.setting_value = ? AND igs.galley_id <> ? AND i.journal_id = ?',
            [
                'pub-id::' . $pubIdType,
                (string) $pubId,
                (int) $galleyId,
                (int) $journalId
            ]
        );
        
        $returner = false;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = (bool) $row['count'];
        }

        $result->Close();
        return $returner;
    }

    /**
     * Retrieve a galley by ID.
     * @param string $pubIdType
     * @param string $pubId
     * @param mixed $issueId
     * @return IssueGalley|null
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
            WHERE gs.setting_name = ? AND
                gs.setting_value = ? AND
                g.issue_id = ?',
            ['pub-id::' . $pubIdType, (string) $pubId, (int) $issueId]
        );

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_returnGalleyFromRow($result->getRowAssoc(false));
        } else {
            // Note: Hook registry still uses references as per requirement
            $galleyId = null; // Placeholder as this function doesn't take galleyId
            HookRegistry::dispatch('IssueGalleyDAO::getGalleyByPubId', [&$galleyId, &$issueId, &$returner]);
        }

        $result->Close();
        return $returner;
    }

    /**
     * Retrieve all galleys for an issue.
     * @param mixed $issueId
     * @return array
     */
    public function getGalleysByIssue($issueId) {
        $galleys = [];

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
            [(int) $issueId]
        );

        while (!$result->EOF) {
            $galleys[] = $this->_returnGalleyFromRow($result->getRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        HookRegistry::dispatch('IssueGalleyDAO::getGalleysByIssue', [&$galleys, &$issueId]);

        return $galleys;
    }

    /**
     * Retrieve issue galley by public galley id or, failing that,
     * internal galley ID; public galley ID takes precedence.
     * @param mixed $galleyId
     * @param mixed $issueId
     * @return IssueGalley|null
     */
    public function getGalleyByBestGalleyId($galleyId, $issueId) {
        $galley = null;
        if ((string) $galleyId !== '') {
            $galley = $this->getGalleyByPubId('publisher-id', (string) $galleyId, (int) $issueId);
        }
        if ($galley === null && ctype_digit((string) $galleyId)) {
            $galley = $this->getGalley((int) $galleyId, (int) $issueId);
        }
        return $galley;
    }

    /**
     * Get the list of fields for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return [];
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
     * @param IssueGalley $galley
     */
    public function updateLocaleFields($galley) {
        $this->updateDataObjectSettings('issue_galley_settings', $galley, [
            'galley_id' => (int) $galley->getId()
        ]);
    }

    /**
     * Internal function to return an IssueGalley object from a row.
     * @param array $row
     * @return IssueGalley
     */
    public function _returnGalleyFromRow($row) {
        $galley = new IssueGalley();

        $galley->setId((int) $row['galley_id']);
        $galley->setIssueId((int) $row['issue_id']);
        $galley->setLocale($row['locale']);
        $galley->setFileId((int) $row['file_id']);
        $galley->setLabel($row['label']);
        $galley->setSequence((int) $row['seq']);

        // IssueFile set methods
        $galley->setFileName($row['file_name']);
        $galley->setOriginalFileName($row['original_file_name']);
        $galley->setFileType($row['file_type']);
        $galley->setFileSize((int) $row['file_size']);
        $galley->setContentType($row['content_type']);
        $galley->setDateModified($this->datetimeFromDB($row['date_modified']));
        $galley->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));

        $this->getDataObjectSettings('issue_galley_settings', 'galley_id', (int) $row['galley_id'], $galley);

        HookRegistry::dispatch('IssueGalleyDAO::_returnGalleyFromRow', [&$galley, &$row]);

        return $galley;
    }

    /**
     * Insert a new IssueGalley.
     * @param IssueGalley $galley
     * @return int
     */
    public function insertGalley($galley) {
        $this->update(
            'INSERT INTO issue_galleys
                (issue_id, file_id, label, locale, seq)
            VALUES
                (?, ?, ?, ?, ?)',
            [
                (int) $galley->getIssueId(),
                (int) $galley->getFileId(),
                (string) $galley->getLabel(),
                (string) $galley->getLocale(),
                $galley->getSequence() === null ? $this->getNextGalleySequence((int) $galley->getIssueId()) : (int) $galley->getSequence()
            ]
        );
        $galley->setId($this->getInsertGalleyId());
        $this->updateLocaleFields($galley);

        HookRegistry::dispatch('IssueGalleyDAO::insertGalley', [&$galley, $galley->getId()]);

        return $galley->getId();
    }

    /**
     * Update an existing IssueGalley.
     * @param IssueGalley $galley
     */
    public function updateGalley($galley) {
        $this->update(
            'UPDATE issue_galleys
                SET file_id = ?,
                    label = ?,
                    locale = ?,
                    seq = ?
                WHERE galley_id = ?',
            [
                (int) $galley->getFileId(),
                (string) $galley->getLabel(),
                (string) $galley->getLocale(),
                (int) $galley->getSequence(),
                (int) $galley->getId()
            ]
        );
        $this->updateLocaleFields($galley);
    }

    /**
     * Delete an IssueGalley.
     * @param IssueGalley $galley
     * @return bool
     */
    public function deleteGalley($galley) {
        return $this->deleteGalleyById($galley->getId(), $galley->getIssueId());
    }

    /**
     * Delete a galley by ID.
     * @param mixed $galleyId
     * @param mixed $issueId optional
     * @return bool
     */
    public function deleteGalleyById($galleyId, $issueId = null) {
        HookRegistry::dispatch('IssueGalleyDAO::deleteGalleyById', [&$galleyId, &$issueId]);

        if ($issueId !== null) {
            $this->update(
                'DELETE FROM issue_galleys WHERE galley_id = ? AND issue_id = ?',
                [(int) $galleyId, (int) $issueId]
            );
        } else {
            $this->update(
                'DELETE FROM issue_galleys WHERE galley_id = ?', 
                [(int) $galleyId]
            );
        }
        
        if ($this->getAffectedRows()) {
            $this->update('DELETE FROM issue_galley_settings WHERE galley_id = ?', [(int) $galleyId]);
            return true;
        }
        return false;
    }

    /**
     * Delete galleys by issue.
     * NOTE that this will not delete issue_file entities or the respective files.
     * @param mixed $issueId
     */
    public function deleteGalleysByIssue($issueId) {
        $galleys = $this->getGalleysByIssue($issueId);
        foreach ($galleys as $galley) {
            $this->deleteGalleyById($galley->getId(), $issueId);
        }
    }

    /**
     * Check if a galley exists with the associated file ID.
     * @param mixed $issueId
     * @param mixed $fileId
     * @return bool
     */
    public function galleyExistsByFileId($issueId, $fileId) {
        // [WIZDAM] Added 'AS count' to safely access the result in PHP 8+
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM issue_galleys
            WHERE issue_id = ? AND file_id = ?',
            [(int) $issueId, (int) $fileId]
        );

        $returner = false;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = ((int) $row['count']) === 1;
        }

        $result->Close();
        return $returner;
    }

    /**
     * Increment the views count for a galley.
     * @param mixed $galleyId
     * @return bool
     */
    public function incrementViews($galleyId) {
        if (!HookRegistry::dispatch('IssueGalleyDAO::incrementViews', [&$galleyId])) {
            return $this->update(
                'UPDATE issue_galleys SET views = views + 1 WHERE galley_id = ?',
                [(int) $galleyId]
            );
        }
        return false;
    }

    /**
     * Sequentially renumber galleys for an issue in their sequence order.
     * @param mixed $issueId
     */
    public function resequenceGalleys($issueId) {
        $result = $this->retrieve(
            'SELECT galley_id FROM issue_galleys WHERE issue_id = ? ORDER BY seq',
            [(int) $issueId]
        );

        for ($i = 1; !$result->EOF; $i++) {
            $row = $result->getRowAssoc(false);
            $galleyId = (int) $row['galley_id'];
            
            $this->update(
                'UPDATE issue_galleys SET seq = ? WHERE galley_id = ?',
                [$i, $galleyId]
            );
            $result->MoveNext();
        }

        $result->Close();
    }

    /**
     * Get the the next sequence number for an issue's galleys (i.e., current max + 1).
     * @param mixed $issueId
     * @return int
     */
    public function getNextGalleySequence($issueId) {
        // [WIZDAM] Added 'AS max_seq' to safely access the result in PHP 8+
        $result = $this->retrieve(
            'SELECT MAX(seq) + 1 AS max_seq FROM issue_galleys WHERE issue_id = ?',
            [(int) $issueId]
        );
        
        $returner = 1;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $value = $row['max_seq'];
            $returner = (int) floor($value !== null ? (float) $value : 1);
        }

        $result->Close();
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
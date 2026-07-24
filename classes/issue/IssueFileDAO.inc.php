<?php
declare(strict_types=1);

/**
 * @file classes/issue/IssueFileDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueFileDAO
 * @ingroup issue
 * @see IssueFile
 *
 * @brief Operations for retrieving and modifying IssueFile objects.
 */

import('lib.pkp.classes.file.PKPFileDAO');
import('classes.issue.IssueFile');

class IssueFileDAO extends PKPFileDAO {

    /** 
     * @var array|null MIME types that can be displayed inline in a browser 
     */
    protected ?array $_inlineableTypes = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function IssueFileDao() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::IssueFileDao(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get inlineable file types.
     * @return array|null
     */
    public function getInlineableTypes() {
        return $this->_inlineableTypes;
    }

    /**
     * Set inlineable file types.
     * @param array|null $inlineableTypes
     */
    public function setInlineableTypes($inlineableTypes) {
        $this->_inlineableTypes = $inlineableTypes;
    }

    /**
     * Retrieve an issue file by ID.
     * @param mixed $fileId
     * @param mixed $issueId optional
     * @return IssueFile|null
     */
    public function getIssueFile($fileId, $issueId = null) {
        if ($fileId === null) {
            return null;
        }

        if ($issueId !== null) {
            $result = $this->retrieve(
                'SELECT f.* FROM issue_files f WHERE f.file_id = ? AND f.issue_id = ?',
                [(int) $fileId, (int) $issueId]
            );
        } else {
            $result = $this->retrieve(
                'SELECT f.* FROM issue_files f WHERE f.file_id = ?',
                [(int) $fileId]
            );
        }

        $returner = null;
        if (!$result->EOF) {
            $returner = $this->_returnIssueFileFromRow($result->getRowAssoc(false));
        }
        $result->Close();
        
        return $returner;
    }

    /**
     * Retrieve all issue files for an issue.
     * @param mixed $issueId
     * @return array
     */
    public function getIssueFilesByIssue($issueId) {
        $issueFiles = [];

        $result = $this->retrieve(
            'SELECT * FROM issue_files WHERE issue_id = ?',
            [(int) $issueId]
        );

        while (!$result->EOF) {
            $issueFiles[] = $this->_returnIssueFileFromRow($result->getRowAssoc(false));
            $result->MoveNext();
        }
        $result->Close();

        return $issueFiles;
    }

    /**
     * Internal function to return an IssueFile object from a row.
     * @param array $row
     * @return IssueFile
     */
    public function _returnIssueFileFromRow($row) {
        $issueFile = new IssueFile();
        $issueFile->setId((int) $row['file_id']);
        $issueFile->setIssueId((int) $row['issue_id']);
        $issueFile->setFileName($row['file_name']);
        $issueFile->setFileType($row['file_type']);
        $issueFile->setFileSize((int) $row['file_size']);
        $issueFile->setContentType($row['content_type']);
        $issueFile->setOriginalFileName($row['original_file_name']);
        $issueFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));
        $issueFile->setDateModified($this->datetimeFromDB($row['date_modified']));
        
        HookRegistry::dispatch('IssueFileDAO::_returnIssueFileFromRow', [&$issueFile, &$row]);
        
        return $issueFile;
    }

    /**
     * Insert a new IssueFile.
     * @param IssueFile $issueFile
     * @return int
     */
    public function insertIssueFile($issueFile) {
        $params = [
            (int) $issueFile->getIssueId(),
            $issueFile->getFileName(),
            $issueFile->getFileType(),
            (int) $issueFile->getFileSize(),
            $issueFile->getContentType(),
            $issueFile->getOriginalFileName()
        ];

        // Note: sprintf is kept here because datetimeToDB might return 'NOW()', 
        // which cannot be bound as a string parameter.
        $this->update(
            sprintf(
                'INSERT INTO issue_files
                    (issue_id, file_name, file_type, file_size, content_type, original_file_name, date_uploaded, date_modified)
                VALUES
                    (?, ?, ?, ?, ?, ?, %s, %s)',
                $this->datetimeToDB($issueFile->getDateUploaded()),
                $this->datetimeToDB($issueFile->getDateModified())
            ),
            $params
        );

        $issueFile->setId($this->getInsertIssueFileId());
        return $issueFile->getId();
    }

    /**
     * Update an existing issue file.
     * @param IssueFile $issueFile
     * @return int
     */
    public function updateIssueFile($issueFile) {
        $this->update(
            sprintf(
                'UPDATE issue_files
                SET issue_id = ?,
                    file_name = ?,
                    file_type = ?,
                    file_size = ?,
                    content_type = ?,
                    original_file_name = ?,
                    date_uploaded = %s,
                    date_modified = %s
                WHERE file_id = ?',
                $this->datetimeToDB($issueFile->getDateUploaded()),
                $this->datetimeToDB($issueFile->getDateModified())
            ),
            [
                (int) $issueFile->getIssueId(),
                $issueFile->getFileName(),
                $issueFile->getFileType(),
                (int) $issueFile->getFileSize(),
                $issueFile->getContentType(),
                $issueFile->getOriginalFileName(),
                (int) $issueFile->getId()
            ]
        );

        return $issueFile->getId();
    }

    /**
     * Delete an issue file.
     * @param IssueFile $issueFile
     * @return bool
     */
    public function deleteIssueFile($issueFile) {
        return $this->deleteIssueFileById($issueFile->getId());
    }

    /**
     * Delete an issue file by ID.
     * @param mixed $fileId
     * @return bool
     */
    public function deleteIssueFileById($fileId) {
        return $this->update(
            'DELETE FROM issue_files WHERE file_id = ?', 
            [(int) $fileId]
        );
    }

    /**
     * Delete all issue files for an issue.
     * @param mixed $issueId
     * @return bool
     */
    public function deleteIssueFiles($issueId) {
        return $this->update(
            'DELETE FROM issue_files WHERE issue_id = ?', 
            [(int) $issueId]
        );
    }

    /**
     * Get the ID of the last inserted issue file.
     * @return int
     */
    public function getInsertIssueFileId() {
        return $this->getInsertId('issue_files', 'file_id');
    }

}
?>
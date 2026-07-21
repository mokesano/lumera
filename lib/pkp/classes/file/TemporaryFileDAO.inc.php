<?php
declare(strict_types=1);

/**
 * @file classes/file/TemporaryFileDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemporaryFileDAO
 * @ingroup file
 * @see TemporaryFile
 *
 * @brief Operations for retrieving and modifying TemporaryFile objects.
 */

import('lib.pkp.classes.file.TemporaryFile');

class TemporaryFileDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TemporaryFileDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                'Class ' . get_class($this) . ' uses deprecated constructor parent::TemporaryFileDAO(). Please refactor to parent::__construct().',
                E_USER_DEPRECATED
            );
        }
        $this->__construct();
    }

    /**
     * Retrieve a temporary file by ID.
     * @param int $fileId
     * @param int $userId
     * @return TemporaryFile|null
     */
    public function getTemporaryFile($fileId, $userId) {
        $result = $this->retrieveLimit(
            'SELECT t.* FROM temporary_files t WHERE t.file_id = ? AND t.user_id = ?',
            [(int) $fileId, (int) $userId],
            1
        );

        $returner = null;

        if ($result && !$result->EOF) {
            $returner = $this->_returnTemporaryFileFromRow($result->GetRowAssoc(false));
        }

        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Instantiate and return a new data object.
     * @return TemporaryFile
     */
    public function newDataObject() {
        return new TemporaryFile();
    }

    /**
     * Internal function to return a TemporaryFile object from a row.
     * @param array $row
     * @return TemporaryFile
     */
    public function _returnTemporaryFileFromRow($row) {
        $temporaryFile = $this->newDataObject();
        $temporaryFile->setId((int) $row['file_id']);
        $temporaryFile->setFileName((string) $row['file_name']);
        $temporaryFile->setFileType((string) $row['file_type']);
        $temporaryFile->setFileSize((int) $row['file_size']);
        $temporaryFile->setUserId((int) $row['user_id']);
        $temporaryFile->setOriginalFileName((string) $row['original_file_name']);
        $temporaryFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));

        HookRegistry::dispatch('TemporaryFileDAO::_returnTemporaryFileFromRow', [$temporaryFile, &$row]);

        return $temporaryFile;
    }

    /**
     * Insert a new TemporaryFile.
     * @param TemporaryFile $temporaryFile
     * @return int
     */
    public function insertTemporaryFile($temporaryFile) {
        $this->update(
            sprintf('INSERT INTO temporary_files
                (user_id, file_name, file_type, file_size, original_file_name, date_uploaded)
                VALUES
                (?, ?, ?, ?, ?, %s)',
                $this->datetimeToDB($temporaryFile->getDateUploaded())),
            [
                (int) $temporaryFile->getUserId(),
                (string) $temporaryFile->getFileName(),
                (string) $temporaryFile->getFileType(),
                (int) $temporaryFile->getFileSize(),
                (string) $temporaryFile->getOriginalFileName()
            ]
        );

        $temporaryFile->setId($this->getInsertTemporaryFileId());
        return $temporaryFile->getId();
    }

    /**
     * Update an existing temporary file.
     * @param TemporaryFile $temporaryFile
     * @return int
     */
    public function updateObject($temporaryFile) {
        $this->update(
            sprintf('UPDATE temporary_files
                SET
                    file_name = ?,
                    file_type = ?,
                    file_size = ?,
                    user_id = ?,
                    original_file_name = ?,
                    date_uploaded = %s
                WHERE file_id = ?',
                $this->datetimeToDB($temporaryFile->getDateUploaded())),
            [
                (string) $temporaryFile->getFileName(),
                (string) $temporaryFile->getFileType(),
                (int) $temporaryFile->getFileSize(),
                (int) $temporaryFile->getUserId(),
                (string) $temporaryFile->getOriginalFileName(),
                (int) $temporaryFile->getId()
            ]
        );

        return $temporaryFile->getId();
    }

    /**
     * DEPRECATED: Update temporary file
     * @deprecated
     * @param TemporaryFile $temporaryFile
     */
    public function updateTemporaryFile($temporaryFile) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->updateObject($temporaryFile);
    }

    /**
     * Delete a temporary file by ID.
     * @param int $fileId
     * @param int $userId
     * @return bool
     */
    public function deleteTemporaryFileById($fileId, $userId) {
        return $this->update(
            'DELETE FROM temporary_files WHERE file_id = ? AND user_id = ?',
            [(int) $fileId, (int) $userId]
        );
    }

    /**
     * Delete temporary files by user ID.
     * @param int $userId
     * @return bool
     */
    public function deleteTemporaryFilesByUserId($userId) {
        return $this->update(
            'DELETE FROM temporary_files WHERE user_id = ?',
            [(int) $userId]
        );
    }

    /**
     * Get expired temporary files
     * @return array
     */
    public function getExpiredFiles() {
        // Files older than one day can be cleaned up.
        $expiryThresholdTimestamp = time() - (60 * 60 * 24);

        $temporaryFiles = [];

        $result = $this->retrieve(
            'SELECT * FROM temporary_files WHERE date_uploaded < ?',
            [$this->datetimeToDB($expiryThresholdTimestamp)]
        );

        if ($result) {
            while (!$result->EOF) {
                $temporaryFiles[] = $this->_returnTemporaryFileFromRow($result->GetRowAssoc(false));
                $result->MoveNext();
            }
            $result->Close();
        }

        return $temporaryFiles;
    }

    /**
     * Get the ID of the last inserted temporary file.
     * @return int
     */
    public function getInsertTemporaryFileId() {
        return $this->getInsertId('temporary_files', 'file_id');
    }

}
?>
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
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::TemporaryFileDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Retrieve a temporary file by ID.
     * @param $fileId int
     * @param $userId int
     * @return TemporaryFile
     */
    public function getTemporaryFile($fileId, $userId) {
        $result = $this->retrieveLimit(
            'SELECT t.* FROM temporary_files t WHERE t.file_id = ? and t.user_id = ?',
            array((int) $fileId, (int) $userId),
            1
        );

        $returner = null;
        if (isset($result) && $result->RecordCount() != 0) {
            $returner = $this->_returnTemporaryFileFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

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
     * @param $row array
     * @return TemporaryFile
     */
    public function _returnTemporaryFileFromRow($row) {
        $temporaryFile = $this->newDataObject();
        $temporaryFile->setId($row['file_id']);
        $temporaryFile->setFileName($row['file_name']);
        $temporaryFile->setFileType($row['file_type']);
        $temporaryFile->setFileSize($row['file_size']);
        $temporaryFile->setUserId($row['user_id']);
        $temporaryFile->setOriginalFileName($row['original_file_name']);
        $temporaryFile->setDateUploaded($this->datetimeFromDB($row['date_uploaded']));

        HookRegistry::call('TemporaryFileDAO::_returnTemporaryFileFromRow', array(&$temporaryFile, &$row));

        return $temporaryFile;
    }

    /**
     * Insert a new TemporaryFile.
     * @param $temporaryFile TemporaryFile
     * @return int
     */
    public function insertTemporaryFile($temporaryFile) {
        $this->update(
            sprintf('INSERT INTO temporary_files
                (user_id, file_name, file_type, file_size, original_file_name, date_uploaded)
                VALUES
                (?, ?, ?, ?, ?, %s)',
                $this->datetimeToDB($temporaryFile->getDateUploaded())),
            array(
                (int) $temporaryFile->getUserId(),
                $temporaryFile->getFileName(),
                $temporaryFile->getFileType(),
                (int) $temporaryFile->getFileSize(),
                $temporaryFile->getOriginalFileName()
            )
        );

        $temporaryFile->setId($this->getInsertTemporaryFileId());
        return $temporaryFile->getId();
    }

    /**
     * Update an existing temporary file.
     * @param $temporaryFile TemporaryFile
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
            array(
                $temporaryFile->getFileName(),
                $temporaryFile->getFileType(),
                (int) $temporaryFile->getFileSize(),
                (int) $temporaryFile->getUserId(),
                $temporaryFile->getOriginalFileName(),
                (int) $temporaryFile->getId()
            )
        );

        return $temporaryFile->getId();
    }

    /**
     * @deprecated
     */
    public function updateTemporaryFile($temporaryFile) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->updateObject($temporaryFile);
    }

    /**
     * Delete a temporary file by ID.
     * @param $fileId int
     * @param $userId int
     */
    public function deleteTemporaryFileById($fileId, $userId) {
        return $this->update(
            'DELETE FROM temporary_files WHERE file_id = ? AND user_id = ?',
            array((int) $fileId, (int) $userId)
        );
    }

    /**
     * Delete temporary files by user ID.
     * @param $userId int
     */
    public function deleteTemporaryFilesByUserId($userId) {
        return $this->update(
            'DELETE FROM temporary_files WHERE user_id = ?',
            array((int) $userId)
        );
    }

    /**
     * Get expired temporary files
     * @return array
     */
    public function getExpiredFiles() {
        // Files older than one day can be cleaned up.
        $expiryThresholdTimestamp = time() - (60 * 60 * 24);

        $temporaryFiles = array();

        $result = $this->retrieve(
            'SELECT * FROM temporary_files WHERE date_uploaded < ' . $this->datetimeToDB($expiryThresholdTimestamp)
        );

        while (!$result->EOF) {
            $temporaryFiles[] = $this->_returnTemporaryFileFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

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
<?php
declare(strict_types=1);

/**
 * @file classes/file/PKPTemporaryFileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPTemporaryFileManager
 * @ingroup file
 * @see TemporaryFileDAO
 *
 * @brief Class defining operations for temporary file management.
 */

import('lib.pkp.classes.file.PrivateFileManager');

class PKPTemporaryFileManager extends PrivateFileManager {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        $this->_performPeriodicCleanup();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPTemporaryFileManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPTemporaryFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Get the base path for temporary file storage.
     * @return string
     */
    public function getBasePath() {
        return parent::getBasePath() . '/temp/';
    }

    /**
     * Retrieve file information by file ID.
     * @param $fileId int
     * @param $userId int
     * @return TemporaryFile
     */
    public function getFile($fileId, $userId) {
        $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
        $temporaryFile = $temporaryFileDao->getTemporaryFile($fileId, $userId);
        return $temporaryFile;
    }

    /**
     * Read a file's contents.
     * @param $fileId int
     * @param $userId int
     * @param $output boolean output the file's contents instead of returning a string
     * @return boolean|string
     */
    public function readFile($fileId, $userId = null, $output = false) {
        $temporaryFile = $this->getFile($fileId, $userId);

        if (isset($temporaryFile)) {
            $filePath = $this->getBasePath() . $temporaryFile->getFileName();
            return parent::readFile($filePath, $output);
        } else {
            return false;
        }
    }

    /**
     * Delete a file by ID.
     * @param $fileId int
     * @param $userId int
     */
    public function deleteFile($fileId, $userId = null) {
        $temporaryFile = $this->getFile($fileId, $userId);

        if (isset($temporaryFile)) {
            parent::deleteFile($this->getBasePath() . $temporaryFile->getFileName());

            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            $temporaryFileDao->deleteTemporaryFileById($fileId, $userId);
        }
    }

    /**
     * Download a file.
     * Overrides parent::downloadFile but accepts different parameters logic internally.
     * NOTE: Signature mismatch fixed to support both usage patterns or corrected based on intent.
     * In OJS 2.x logic, usually downloadFile($fileId, $userId).
     * However, parent expects ($filePath, $mediaType...).
     * * @param $fileId int the file id of the file to download
     * @param $userId int|null
     * @param $inline boolean
     * @return boolean
     */
    public function downloadFile($fileId, $userId = null, $inline = false, $fileName = null) {
        $temporaryFile = $this->getFile($fileId, $userId);
        if (isset($temporaryFile)) {
            $filePath = $this->getBasePath() . $temporaryFile->getFileName();
            // Panggil parent dengan path fisik
            return parent::downloadFile($filePath, $temporaryFile->getFileType(), $inline, $temporaryFile->getOriginalFileName());
        } else {
            return false;
        }
    }

    /**
     * Upload the file and add it to the database.
     * @param $fileName string index into the $_FILES array
     * @param $userId int
     * @return object The new TemporaryFile or false on failure
     */
    public function handleUpload($fileName, $userId) {
        // Get the file extension, then rename the file.
        $fileExtension = $this->parseFileExtension($this->getUploadedFileName($fileName));

        if (!$this->fileExists($this->getBasePath(), 'dir')) {
            // Try to create destination directory
            $this->mkdirtree($this->getBasePath());
        }

        $newFileName = basename(tempnam($this->getBasePath(), $fileExtension));
        if (!$newFileName) return false;

        // Modernisasi: uploadFile sekarang strict, errorMsg optional
        $errorMsg = null;
        if ($this->uploadFile($fileName, $this->getBasePath() . $newFileName, $errorMsg)) {
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            $temporaryFile = $temporaryFileDao->newDataObject();

            $temporaryFile->setUserId($userId);
            $temporaryFile->setFileName($newFileName);
            // Gunakan metode helper MIME detection yang lebih aman jika ada
            $temporaryFile->setFileType($this->getUploadedFileType($fileName)); 
            $temporaryFile->setFileSize($_FILES[$fileName]['size']);
            $temporaryFile->setOriginalFileName($this->truncateFileName($_FILES[$fileName]['name'], 127));
            $temporaryFile->setDateUploaded(Core::getCurrentDate());

            $temporaryFileDao->insertTemporaryFile($temporaryFile);

            return $temporaryFile;

        } else {
            return false;
        }
    }

    /**
     * Perform periodic cleanup tasks. This is used to occasionally
     * remove expired temporary files.
     */
    public function _performPeriodicCleanup() {
        if (time() % 100 == 0) {
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            $expiredFiles = $temporaryFileDao->getExpiredFiles();
            foreach ($expiredFiles as $expiredFile) {
                $this->deleteFile($expiredFile->getId(), $expiredFile->getUserId());
            }
        }
    }
}

?>
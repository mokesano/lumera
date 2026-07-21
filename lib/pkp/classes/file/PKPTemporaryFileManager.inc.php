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
            trigger_error(
                'Class ' . get_class($this) . ' uses deprecated constructor parent::PKPTemporaryFileManager(). Please refactor to parent::__construct().',
                E_USER_DEPRECATED
            );
        }
        $this->__construct();
    }

    /**
     * Get the base path for temporary file storage.
     * @return string
     */
    public function getBasePath(): string {
        return parent::getBasePath() . '/temp/';
    }

    /**
     * Retrieve file information by file ID.
     * @param int $fileId
     * @param int|null $userId
     * @return TemporaryFile|null
     */
    public function getFile($fileId, $userId = null) {
        /** @var TemporaryFileDAO $temporaryFileDao */
        $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');

        return $temporaryFileDao->getTemporaryFile((int) $fileId, $userId !== null ? (int) $userId : null);
    }

    /**
     * Read a file's contents.
     * @param int $fileId
     * @param int|null $userId
     * @param bool $output
     * @return bool|string
     */
    public function readFile($fileId, $userId = null, $output = false) {
        $temporaryFile = $this->getFile((int) $fileId, $userId);

        if ($temporaryFile !== null) {
            $filePath = $this->getBasePath() . $temporaryFile->getFileName();
            return parent::readFile($filePath, $output);
        }
        return false;
    }

    /**
     * Delete a file by ID.
     * @param int $fileId
     * @param int|null $userId
     */
    public function deleteFile($fileId, $userId = null) {
        $temporaryFile = $this->getFile((int) $fileId, $userId);

        if ($temporaryFile !== null) {
            parent::deleteFile($this->getBasePath() . $temporaryFile->getFileName());

            /** @var TemporaryFileDAO $temporaryFileDao */
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            
            $temporaryFileDao->deleteTemporaryFileById((int) $fileId, $userId !== null ? (int) $userId : null);
        }
    }

    /**
     * Download a file.
     * @param int $fileId
     * @param int|null $userId
     * @param bool $inline
     * @param string|null $fileName
     * @return bool
     */
    public function downloadFile($fileId, $userId = null, $inline = false, $fileName = null) {
        $temporaryFile = $this->getFile((int) $fileId, $userId);
        
        if ($temporaryFile !== null) {
            $filePath = $this->getBasePath() . $temporaryFile->getFileName();
            return parent::downloadFile($filePath, $temporaryFile->getFileType(), $inline, $temporaryFile->getOriginalFileName());
        }
        return false;
    }

    /**
     * Upload the file and add it to the database.
     * @param string $fileName index into the $_FILES array
     * @param int $userId
     * @return TemporaryFile|bool
     */
    public function handleUpload($fileName, $userId) {
        if (!isset($_FILES[$fileName]) || !is_uploaded_file($_FILES[$fileName]['tmp_name'])) {
            return false;
        }

        $fileExtension = $this->parseFileExtension($this->getUploadedFileName((string) $fileName));
        $basePath = $this->getBasePath();

        if (!$this->fileExists($basePath, 'dir')) {
            $this->mkdirtree($basePath);
        }

        $newFileName = basename(tempnam($basePath, $fileExtension));
        if (!$newFileName) {
            return false;
        }

        $errorMsg = null;
        if ($this->uploadFile($fileName, $basePath . $newFileName, $errorMsg)) {
            /** @var TemporaryFileDAO $temporaryFileDao */
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            $temporaryFile = $temporaryFileDao->newDataObject();

            $temporaryFile->setUserId((int) $userId);
            $temporaryFile->setFileName($newFileName);
            $temporaryFile->setFileType((string) $this->getUploadedFileType($fileName));
            $temporaryFile->setFileSize((int) $_FILES[$fileName]['size']);
            $temporaryFile->setOriginalFileName($this->truncateFileName((string) $_FILES[$fileName]['name'], 127));
            $temporaryFile->setDateUploaded(Core::getCurrentDate());

            $temporaryFileDao->insertTemporaryFile($temporaryFile);

            return $temporaryFile;
        }
        
        return false;
    }

    /**
     * Perform periodic cleanup tasks. This is used to occasionally
     * remove expired temporary files.
     */
    public function _performPeriodicCleanup(): void {
        if (time() % 100 === 0) {
            /** @var TemporaryFileDAO $temporaryFileDao */
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            $expiredFiles = $temporaryFileDao->getExpiredFiles();

            if (is_array($expiredFiles) || $expiredFiles instanceof \Traversable) {
                foreach ($expiredFiles as $expiredFile) {
                    $this->deleteFile($expiredFile->getId(), $expiredFile->getUserId());
                }
            }
        }
    }

}
?>
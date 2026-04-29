<?php
declare(strict_types=1);

/**
 * @file classes/file/IssueFileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueFileManager
 * @ingroup file
 *
 * @brief Class defining operations for issue file management.
 *
 * Issue directory structure:
 * [issue id]/public
 */

import('lib.pkp.classes.file.FileManager');
import('classes.issue.IssueFile');

class IssueFileManager extends FileManager {

    /** @var string|null the path to location of the files */
    public $_filesDir = null;

    /** @var int|null the associated issue ID */
    public $_issueId = null;

    /**
     * Constructor.
     * Create a manager for handling issue file uploads.
     * @param $issueId int
     */
    public function __construct($issueId) {
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getIssueById($issueId);
        assert($issue);

        $this->setIssueId($issueId);
        $this->setFilesDir(Config::getVar('files', 'files_dir') . '/journals/' . $issue->getJournalId() . '/issues/' . $issueId . '/');

        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function IssueFileManager($issueId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::IssueFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($issueId);
    }

    /**
     * Get the issue files directory.
     * @return string|null
     */
    public function getFilesDir() {
        return $this->_filesDir;
    }

    /**
     * Set the issue files directory.
     * @param $filesDir string
     * @return void
     */
    public function setFilesDir($filesDir) {
        $this->_filesDir = $filesDir;
    }

    /**
     * Get the issue ID.
     * @return int|null
     */
    public function getIssueId() {
        return $this->_issueId;
    }

    /**
     * Set the issue ID.
     * @param $issueId int
     * @return void
     */
    public function setIssueId($issueId) {
        $this->_issueId = (int) $issueId;
    }

    /**
     * Upload a public issue file.
     * @param $fileName string the name of the file used in the POST form
     * @param $fileId int
     * @return int|boolean file ID
     */
    public function uploadPublicFile($fileName, $fileId = null) {
        return $this->_handleUpload($fileName, ISSUE_FILE_PUBLIC, $fileId);
    }

    /**
     * Delete an issue file by ID.
     * @param $fileId int
     * @return boolean if successful
     */
    public function deleteFile($fileId) {
        $issueFileDao = DAORegistry::getDAO('IssueFileDAO');
        $issueFile = $issueFileDao->getIssueFile($fileId);

        if ($issueFile && parent::deleteFile($this->getFilesDir() . $this->contentTypeToPath($issueFile->getContentType()) . '/' . $issueFile->getFileName())) {
            $issueFileDao->deleteIssueFileById($fileId);
            return true;
        }

        return false;
    }

    /**
     * Delete the entire tree of files belonging to an issue.
     * @return void
     */
    public function deleteIssueTree() {
        parent::rmtree($this->getFilesDir());
    }
    
    /**
     * Tampilkan file secara Inline (View) bukan Attachment (Download)
     * @param $fileId int
     * @return boolean
     */
    public function viewFile($fileId) {
        $issueFileDao = DAORegistry::getDAO('IssueFileDAO');
        $issueFile = $issueFileDao->getIssueFile((int)$fileId);

        if ($issueFile) {
            $subFolder = $this->contentTypeToPath($issueFile->getContentType());
            $filePath = $this->getFilesDir() . $subFolder . '/' . $issueFile->getFileName();

            if (file_exists($filePath)) {
                // Bersihkan buffer
                if (ob_get_level()) ob_end_clean();

                // Panggil parent downloadFile dengan parameter $inline = true
                // Signature parent: downloadFile($filePath, $mediaType, $inline, $fileName)
                return parent::downloadFile($filePath, $issueFile->getFileType(), true);
            }
        }
        return false;
    }

    /**
     * Download a file.
     * @param $fileId int the file id of the file to download
     * @param $inline print file as inline instead of attachment, optional
     * @return boolean
     */
    public function downloadFile($fileIdOrPath, $mediaType = NULL, $inline = false, $fileName = NULL) {
        if (is_numeric($fileIdOrPath)) {
            $issueFileDao = DAORegistry::getDAO('IssueFileDAO');
            $issueFile = $issueFileDao->getIssueFile((int)$fileIdOrPath);
    
            if ($issueFile) {
                $mediaType = $issueFile->getFileType();
                $subFolder = $this->contentTypeToPath($issueFile->getContentType());
                $filePath = $this->getFilesDir() . $subFolder . '/' . $issueFile->getFileName();
                $fileName = $issueFile->getFileName(); // Ambil nama file asli
            } else {
                return false;
            }
        } else {
            $filePath = $fileIdOrPath;
        }
    
        if (!file_exists($filePath)) return false;
    
        // Bersihkan buffer agar stream tidak rusak
        if (ob_get_level()) ob_end_clean();
    
        // Panggil parent dengan parameter lengkap
        // Jika $inline = false, parent::downloadFile akan mengirim header 'attachment'
        return parent::downloadFile($filePath, $mediaType, $inline, $fileName);
    }

    /**
     * Return directory path based on issue content type (used for naming files).
     * @param $contentType int
     * @return string
     */
    public function contentTypeToPath($contentType) {
        switch ($contentType) {
            case ISSUE_FILE_PUBLIC: return 'public';
        }
        return ''; // Default case added for safety
    }

    /**
     * Return abbreviation based on issue content type (used for naming files).
     * @param $contentType int
     * @return string
     */
    public function contentTypeToAbbrev($contentType) {
        switch ($contentType) {
            case ISSUE_FILE_PUBLIC: return 'PB';
        }
        return ''; // Default case added for safety
    }

    /**
     * PRIVATE routine to upload the file and add it to the database.
     * @param $fileName string index into the $_FILES array
     * @param $contentType int Issue file content type
     * @param $fileId int ID of an existing file to update
     * @param $overwrite boolean overwrite previous version of the file
     * @return int|boolean the file ID
     */
    public function _handleUpload($fileName, $contentType, $fileId = null, $overwrite = false) {
        $result = null;
        if (HookRegistry::dispatch('IssueFileManager::_handleUpload', array(&$fileName, &$contentType, &$fileId, &$overwrite, &$result))) return $result;

        $issueId = $this->getIssueId();
        $issueFileDao = DAORegistry::getDAO('IssueFileDAO');

        $contentTypePath = $this->contentTypeToPath($contentType);
        $dir = $this->getFilesDir() . $contentTypePath . '/';

        $issueFile = new IssueFile();
        $issueFile->setIssueId($issueId);
        $issueFile->setDateUploaded(Core::getCurrentDate());
        $issueFile->setDateModified(Core::getCurrentDate());
        $issueFile->setFileName('');
        $issueFile->setFileType($this->getUploadedFileType($fileName));
        $issueFile->setFileSize($_FILES[$fileName]['size']);
        $issueFile->setOriginalFileName($this->truncateFileName($_FILES[$fileName]['name'], 127));
        $issueFile->setContentType($contentType);

        // If this is a new issue file, add it to the db and get it's new file id
        if (!$fileId) {
            if (!$issueFileDao->insertIssueFile($issueFile)) return false;
        } else {
            $issueFile->setId($fileId);
        }

        $extension = $this->parseFileExtension($this->getUploadedFileName($fileName));
        $newFileName = $issueFile->getIssueId().'-'.$issueFile->getId().'-'.$this->contentTypeToAbbrev($contentType).'.'.$extension;
        $issueFile->setFileName($newFileName);

        // [PERBAIKAN KONTRAK] Inisialisasi $errorMsg
        $errorMsg = null;
        // Upload the actual file
        if (!$this->uploadFile($fileName, $dir.$newFileName, $errorMsg)) {
            // Upload failed. If this is a new file, remove newly added db record.
            if (!$fileId) $issueFileDao->deleteIssueFileById($issueFile->getId());
            return false;
        }

        // Upload succeeded. Update issue file record with new filename.
        $issueFileDao->updateIssueFile($issueFile);

        return $issueFile->getId();
    }
}

?>
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
     * @param int $issueId int
     */
    public function __construct($issueId) {
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issue = $issueDao->getIssueById($issueId);
        assert($issue);

        $this->setIssueId($issueId);
        $this->setFilesDir(Config::getVar('files', 'files_dir') . '/journals/' . $issue->getJournalId() . '/issues/' . $issueId . '/');

        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     * @param int $issueId int
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
     * @param string $filesDir string
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
     * @param int $issueId int
     * @return void
     */
    public function setIssueId($issueId) {
        $this->_issueId = (int) $issueId;
    }

    /**
     * Upload a public issue file.
     * @param string $fileName string
     * @param int $fileId int
     * @return int|boolean file ID
     */
    public function uploadPublicFile($fileName, $fileId = null) {
        return $this->_handleUpload($fileName, ISSUE_FILE_PUBLIC, $fileId);
    }

    /**
     * Delete an issue file by ID.
     * @param int $fileId int
     * @return boolean
     */
    public function deleteFile($fileId) {
        /** @var IssueFileDAO $issueFileDao */
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
     * @param int $fileId int
     * @return boolean
     */
    public function viewFile($fileId) {
        /** @var IssueFileDAO $issueFileDao */
        $issueFileDao = DAORegistry::getDAO('IssueFileDAO');
        $issueFile = $issueFileDao->getIssueFile((int)$fileId);

        if ($issueFile) {
            $subFolder = $this->contentTypeToPath($issueFile->getContentType());
            $filePath = $this->getFilesDir() . $subFolder . '/' . $issueFile->getFileName();

            if (file_exists($filePath)) {
                if (ob_get_level()) ob_end_clean();

                return parent::downloadFile($filePath, $issueFile->getFileType(), true);
            }
        }
        return false;
    }

    /**
     * Download a file.
     * @param mixed $fileIdOrPath
     * @param mixed $mediaType string
     * @param bool $inline print
     * @param mixed $fileName string
     * @return boolean
     */
    public function downloadFile($fileIdOrPath, $mediaType = NULL, $inline = false, $fileName = NULL) {
        if (is_numeric($fileIdOrPath)) {
            /** @var IssueFileDAO $issueFileDao */
            $issueFileDao = DAORegistry::getDAO('IssueFileDAO');
            $issueFile = $issueFileDao->getIssueFile((int)$fileIdOrPath);
    
            if ($issueFile) {
                $mediaType = $issueFile->getFileType();
                $subFolder = $this->contentTypeToPath($issueFile->getContentType());
                $filePath = $this->getFilesDir() . $subFolder . '/' . $issueFile->getFileName();
                $fileName = $issueFile->getFileName();
            } else {
                return false;
            }
        } else {
            $filePath = $fileIdOrPath;
        }
    
        if (!file_exists($filePath)) return false;
    
        if (ob_get_level()) ob_end_clean();

        return parent::downloadFile($filePath, $mediaType, $inline, $fileName);
    }

    /**
     * Return directory path based on issue content type (used for naming files).
     * @param int $contentType int
     * @return string
     */
    public function contentTypeToPath($contentType) {
        switch ($contentType) {
            case ISSUE_FILE_PUBLIC: return 'public';
        }
        return '';
    }

    /**
     * Return abbreviation based on issue content type (used for naming files).
     * @param int $contentType int
     * @return string
     */
    public function contentTypeToAbbrev($contentType) {
        switch ($contentType) {
            case ISSUE_FILE_PUBLIC: return 'PB';
        }
        return '';
    }

    /**
     * PRIVATE routine to upload the file and add it to the database.
     * @param string $fileName string index into the $_FILES array
     * @param int $contentType int
     * @param int $fileId int
     * @param bool $overwrite boolean
     * @return int|boolean the file ID
     */
    public function _handleUpload($fileName, $contentType, $fileId = null, $overwrite = false) {
        $result = null;
        if (HookRegistry::dispatch('IssueFileManager::_handleUpload', [&$fileName, &$contentType, &$fileId, &$overwrite, &$result])) return $result;

        $issueId = $this->getIssueId();
        /** @var IssueFileDAO $issueFileDao */
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

        if (!$fileId) {
            if (!$issueFileDao->insertIssueFile($issueFile)) return false;
        } else {
            $issueFile->setId($fileId);
        }

        $extension = $this->parseFileExtension($this->getUploadedFileName($fileName));
        $newFileName = $issueFile->getIssueId().'-'.$issueFile->getId().'-'.$this->contentTypeToAbbrev($contentType).'.'.$extension;
        $issueFile->setFileName($newFileName);

        $errorMsg = null;
        if (!$this->uploadFile($fileName, $dir.$newFileName, $errorMsg)) {
            if (!$fileId) $issueFileDao->deleteIssueFileById($issueFile->getId());
            return false;
        }

        $issueFileDao->updateIssueFile($issueFile);

        return $issueFile->getId();
    }

}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/file/PublicFileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicFileManager
 * @ingroup file
 *
 * @brief Wrapper class for uploading files to a site/journal's public directory.
 */

import('lib.pkp.classes.file.PKPPublicFileManager');

class PublicFileManager extends PKPPublicFileManager {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PublicFileManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PublicFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Get the path to a journal's public files directory.
     * @param int $journalId int
     * @return string
     */
    public function getJournalFilesPath($journalId) {
        return Config::getVar('files', 'public_files_dir') . '/journals/' . $journalId;
    }

    /**
     * Upload a file to a journals's public directory.
     * Uses strict security checks from FileManager::uploadFile
     * @param int $journalId int
     * @param string $fileName string
     * @param string $destFileName string
     * @return boolean
     */
    public function uploadJournalFile($journalId, $fileName, $destFileName) {
        return $this->uploadFile($fileName, $this->getJournalFilesPath($journalId) . '/' . $destFileName);
    }

    /**
     * Write a file to a journals's public directory.
     * @param int $journalId int
     * @param string $destFileName string
     * @param string $contents string
     * @return boolean
     */
    public function writeJournalFile($journalId, $destFileName, $contents) {
        return $this->writeFile($this->getJournalFilesPath($journalId) . '/' . $destFileName, $contents);
    }

    /**
     * Copy a file to a journals's public directory.
     * @param int $journalId int
     * @param string $sourceFile string
     * @param string $destFileName string
     * @return boolean
     */
    public function copyJournalFile($journalId, $sourceFile, $destFileName) {
        return $this->copyFile($sourceFile, $this->getJournalFilesPath($journalId) . '/' . $destFileName);
    }

    /**
     * Delete a file from a journal's public directory.
     * @param int $journalId int
     * @param string $fileName string
     * @return boolean
     */
    public function removeJournalFile($journalId, $fileName) {
        return $this->deleteFile($this->getJournalFilesPath($journalId) . '/' . $fileName);
    }

}
?>
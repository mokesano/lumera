<?php
declare(strict_types=1);

/**
 * @file classes/file/TemporaryFileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TemporaryFileManager
 * @ingroup file
 * @see TemporaryFileDAO
 *
 * @brief Class defining operations for temporary file management.
 */

import('lib.pkp.classes.file.PKPTemporaryFileManager');

class TemporaryFileManager extends PKPTemporaryFileManager {
    
    /**
     * Constructor.
     * Create a manager for handling temporary file uploads.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TemporaryFileManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::TemporaryFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Create a new temporary file from an article file.
     * @param $articleFile ArticleFile
     * @param $userId int
     * @return TemporaryFile|boolean The new TemporaryFile or false on failure
     */
    public function articleToTemporaryFile($articleFile, $userId) {
        // Get the file extension, then rename the file.
        $fileExtension = $this->parseFileExtension($articleFile->getFileName());

        if (!$this->fileExists($this->getBasePath(), 'dir')) {
            // Try to create destination directory
            $this->mkdirtree($this->getBasePath());
        }

        $newFileName = basename(tempnam($this->getBasePath(), $fileExtension));
        if (!$newFileName) return false;

        if (copy($articleFile->getFilePath(), $this->getBasePath() . $newFileName)) {
            $temporaryFileDao = DAORegistry::getDAO('TemporaryFileDAO');
            $temporaryFile = $temporaryFileDao->newDataObject();

            $temporaryFile->setUserId($userId);
            $temporaryFile->setFileName($newFileName);
            $temporaryFile->setFileType($articleFile->getFileType());
            $temporaryFile->setFileSize($articleFile->getFileSize());
            $temporaryFile->setOriginalFileName($articleFile->getOriginalFileName());
            $temporaryFile->setDateUploaded(Core::getCurrentDate());

            $temporaryFileDao->insertTemporaryFile($temporaryFile);

            return $temporaryFile;

        } else {
            return false;
        }
    }
}

?>
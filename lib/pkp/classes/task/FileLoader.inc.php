<?php
declare(strict_types=1);

/**
 * @file classes/task/FileLoader.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileLoader
 * @ingroup classes_task
 *
 * @brief Base scheduled task class to reliably handle files processing.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

define('FILE_LOADER_RETURN_TO_STAGING', 0x01);

define('FILE_LOADER_PATH_STAGING', 'stage');
define('FILE_LOADER_PATH_PROCESSING', 'processing');
define('FILE_LOADER_PATH_REJECT', 'reject');
define('FILE_LOADER_PATH_ARCHIVE', 'archive');

class FileLoader extends ScheduledTask {

    /** @var string|null The current claimed filename that the script is working on. */
    public $_claimedFilename = null;

    /** @var string|null Base directory path for the filesystem. */
    public $_basePath = null;

    /** @var string|null Stage directory path. */
    public $_stagePath = null;

    /** @var string|null Processing directory path. */
    public $_processingPath = null;

    /** @var string|null Archive directory path. */
    public $_archivePath = null;

    /** @var string|null Reject directory path. */
    public $_rejectPath = null;

    /** @var array List of staged back files after processing. */
    public $_stagedBackFiles = [];

    /** @var bool Whether to compress the archived files or not. */
    public $_compressArchives = false;

    /** @var string|null Admin Name */
    public $_adminName = null;

    /** @var string|null Admin Email */
    public $_adminEmail = null;

    /**
     * Constructor.
     */
    public function __construct($args) {
        parent::__construct($args);

        // Canonicalize the base path.
        $basePath = rtrim($args[0], DIRECTORY_SEPARATOR);
        $basePathFolder = basename($basePath);
        
        // We assume that the parent folder of the base path
        // does already exist and can be canonicalized.
        $basePathParent = realpath(dirname($basePath));
        
        if ($basePathParent === false) {
            $basePath = null;
        } else {
            $basePath = $basePathParent . DIRECTORY_SEPARATOR . $basePathFolder;
        }
        $this->_basePath = $basePath;

        // Configure paths.
        if ($basePath !== null) {
            $this->_stagePath = $basePath . DIRECTORY_SEPARATOR . FILE_LOADER_PATH_STAGING;
            $this->_archivePath = $basePath . DIRECTORY_SEPARATOR . FILE_LOADER_PATH_ARCHIVE;
            $this->_rejectPath = $basePath . DIRECTORY_SEPARATOR . FILE_LOADER_PATH_REJECT;
            $this->_processingPath = $basePath . DIRECTORY_SEPARATOR . FILE_LOADER_PATH_PROCESSING;
        }

        // Set admin email and name.
        // [WIZDAM FIX] Removed reference assignment
        $siteDao = DAORegistry::getDAO('SiteDAO'); /* @var $siteDao SiteDAO */
        $site = $siteDao->getSite(); /* @var $site Site */
        $this->_adminEmail = $site->getLocalizedContactEmail();
        $this->_adminName = $site->getLocalizedContactName();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FileLoader($args) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::FileLoader(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }


    //
    // Getters and setters.
    //
    /**
     * Return the staging path.
     * @return string|null
     */
    public function getStagePath() {
        return $this->_stagePath;
    }

    /**
     * Return the processing path.
     * @return string|null
     */
    public function getProcessingPath() {
        return $this->_processingPath;
    }

    /**
     * Return the reject path.
     * @return string|null
     */
    public function getRejectPath() {
        return $this->_rejectPath;
    }

    /**
     * Return the archive path.
     * @return string|null
     */
    public function getArchivePath() {
        return $this->_archivePath;
    }

    /**
     * Return whether the archives must be compressed or not.
     * @return bool
     */
    public function getCompressArchives() {
        return $this->_compressArchives;
    }

    /**
     * Set whether the archives must be compressed or not.
     * @param bool $compressArchives
     */
    public function setCompressArchives($compressArchives) {
        $this->_compressArchives = (bool) $compressArchives;
    }


    //
    // Public methods
    //
    /**
     * @see ScheduledTask::executeActions()
     */
    public function executeActions() {
        if (!$this->checkFolderStructure()) return false;

        $foundErrors = false;
        while (($filePath = $this->_claimNextFile()) !== null) {
            if ($filePath === false) {
                // Problem claiming the file.
                $foundErrors = true;
                break;
            }
            $errorMsg = null;
            $result = $this->processFile($filePath, $errorMsg);
            if ($result === false) {
                $foundErrors = true;
                $this->_rejectFile();
                $this->addExecutionLogEntry($errorMsg, SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
                continue;
            }

            if ($result === FILE_LOADER_RETURN_TO_STAGING) {
                $foundErrors = true;
                $this->_stageFile();
                $this->addExecutionLogEntry($errorMsg, SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
                // Let the script know what files were sent back to staging,
                // so it doesn't claim them again thereby entering an infinite loop.
                $this->_stagedBackFiles[] = $this->_claimedFilename;
            } else {
                $this->_archiveFile();
            }

            if ($result) {
                $this->addExecutionLogEntry(__('admin.fileLoader.fileProcessed',
                        ['filename' => $filePath]), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);
            }
        }
        return !$foundErrors;
    }

    /**
     * A public helper function that can be used to ensure
     * that the file structure has actually been installed.
     *
     * @param bool $install Set this parameter to true to
     * install the folder structure if it is missing.
     *
     * @return bool True if the folder structure exists,
     * otherwise false.
     */
    public function checkFolderStructure($install = false) {
        // Make sure that the base path is inside the private files dir.
        // The files dir has appropriate write permissions and is assumed
        // to be protected against information leak and symlink attacks.
        $filesDir = realpath(Config::getVar('files', 'files_dir'));
        if ($this->_basePath === null || strpos($this->_basePath, $filesDir) !== 0) {
            $this->addExecutionLogEntry(__('admin.fileLoader.wrongBasePathLocation',
                    ['path' => $this->_basePath]), SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
            return false;
        }

        // Check folder presence and readability.
        $pathsToCheck = [
            $this->_stagePath,
            $this->_archivePath,
            $this->_rejectPath,
            $this->_processingPath
        ];
        
        $fileManager = null;
        foreach($pathsToCheck as $path) {
            if (!(is_dir($path) && is_readable($path))) {
                if ($install) {
                    // Try installing the folder if it is missing.
                    if ($fileManager === null) {
                        import('lib.pkp.classes.file.FileManager');
                        $fileManager = new FileManager();
                    }
                    $fileManager->mkdirtree($path);
                }

                // Try again.
                if (!(is_dir($path) && is_readable($path))) {
                    // Give up...
                    $this->addExecutionLogEntry(__('admin.fileLoader.pathNotAccessible',
                            ['path' => $path]), SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
                    return false;
                }
            }
        }
        return true;
    }


    //
    // Protected methods.
    //
    /**
     * Abstract method that must be
     * implemented by subclasses to
     * process the passed file.
     * @param string $filePath
     * @param string|null $errorMsg Define a custom error message
     * to be used to notify the administrator about the error.
     * This message will be used if the return value is false.
     * @return mixed
     * @see FileLoader::execute to understand
     * the expected return values.
     */
    public function processFile($filePath, &$errorMsg) {
        assert(false);
        return false;
    }

    /**
     * @see ScheduledTask::getName()
     */
    public function getName() {
        return __('admin.fileLoader');
    }


    //
    // Private helper methods.
    //
    /**
     * Claim the first file that's inside the staging folder.
     * @return mixed The claimed file path or null if none, or false if
     * the claim was not successful.
     */
    public function _claimNextFile() {
        if (!is_dir($this->_stagePath)) return null;
        
        $stageDir = opendir($this->_stagePath);
        if (!$stageDir) return null;

        $processingFilePath = false;
        $filename = '';

        while(($file = readdir($stageDir)) !== false) {
            if ($file == '..' || $file == '.' ||
                in_array($file, $this->_stagedBackFiles)) continue;

            $filename = $file;
            $processingFilePath = $this->moveFile($this->_stagePath, $this->_processingPath, $filename);
            break;
        }
        
        // [WIZDAM FIX] Close directory handle to free resources
        closedir($stageDir);

        if ($processingFilePath && pathinfo((string)$processingFilePath, PATHINFO_EXTENSION) == 'gz') {
            $fileMgr = new FileManager();
            $errorMsg = null;
            $decompressedPath = $fileMgr->decompressFile($processingFilePath, $errorMsg);
            
            if ($decompressedPath) {
                $processingFilePath = $decompressedPath;
                $filename = pathinfo($processingFilePath, PATHINFO_BASENAME);
            } else {
                $this->moveFile($this->_processingPath, $this->_stagePath, $filename);
                $this->addExecutionLogEntry($errorMsg, SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
                return false;
            }
        }

        if ($processingFilePath) {
            $this->_claimedFilename = $filename;
            return $processingFilePath;
        } else {
            return null;
        }
    }

    /**
     * Reject the current claimed file.
     */
    public function _rejectFile() {
        $this->moveFile($this->_processingPath, $this->_rejectPath, $this->_claimedFilename);
    }

    /**
     * Archive the current claimed file.
     * @return bool
     */
    public function _archiveFile() {
        $this->moveFile($this->_processingPath, $this->_archivePath, $this->_claimedFilename);
        $filePath = $this->_archivePath . DIRECTORY_SEPARATOR . $this->_claimedFilename;
        $returner = true;
        if ($this->getCompressArchives()) {
            $fileMgr = new FileManager();
            $errorMsg = null;
            if (!$fileMgr->compressFile($filePath, $errorMsg)) {
                $returner = false;
                $this->addExecutionLogEntry($errorMsg, SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
            }
        }
        return $returner;
    }

    /**
     * Stage the current claimed file.
     */
    public function _stageFile() {
        $this->moveFile($this->_processingPath, $this->_stagePath, $this->_claimedFilename);
    }

    /**
     * Move file between filesystem directories.
     * @param string $sourceDir
     * @param string $destDir
     * @param string $filename
     * @return string The destination path of the moved file.
     */
    public function moveFile($sourceDir, $destDir, $filename) {
        $currentFilePath = $sourceDir . DIRECTORY_SEPARATOR . $filename;
        $destinationPath = $destDir . DIRECTORY_SEPARATOR . $filename;

        if (!rename($currentFilePath, $destinationPath)) {
            $message = __('admin.fileLoader.moveFileFailed', ['filename' => $filename,
                'currentFilePath' => $currentFilePath, 'destinationPath' => $destinationPath]);
            $this->addExecutionLogEntry($message, SCHEDULED_TASK_MESSAGE_TYPE_ERROR);

            // Script should always stop if it can't manipulate files inside
            // its own directory system.
            fatalError($message);
        }

        return $destinationPath;
    }
}

?>
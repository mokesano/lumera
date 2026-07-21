<?php
declare(strict_types=1);

/**
 * @file classes/file/FileArchive.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileArchive
 * @ingroup file
 *
 * @brief Class provides functionality for creating an archive of files.
 */

class FileArchive {

    /**
     * Constructor
     */
    public function __construct() {
        // No construct
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FileArchive() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::FileArchive(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct();
    }

    /**
     * Assembles an array of filenames into either a tar.gz or a .zip
     * file, based on what is available. Returns a string representing
     * the path to the archive on disk.
     * @param mixed $files
     * @param string $filesDir
     * @return string|false
     */
    public function create($files, $filesDir) {
        $filesDir = (string) $filesDir;
        
        // Create a temporary file.
        $archivePath = tempnam(sys_get_temp_dir(), 'sf-');
        if ($archivePath === false) {
            return false;
        }
        
        $archivePath = (string) $archivePath;

        // Attempt to use Zip first, if it is available. Otherwise fall back to tar CLI.
        if ($this->zipFunctional()) {
            $zip = new ZipArchive();
            // ZipArchive::open returns true on success, or an error code (int) on failure
            if ($zip->open($archivePath, ZipArchive::CREATE) === true) {

                if (is_array($files)) {
                    foreach ($files as $file) {
                        $fileStr = (string) $file;
                        $filePath = $filesDir . DIRECTORY_SEPARATOR . $fileStr;
                        if (file_exists($filePath) && is_file($filePath)) {
                            $zip->addFile($filePath, basename($fileStr));
                        }
                    }
                }
                $zip->close();
            }
        } else {
            // Fallback to tar CLI
            $tarBinary = Config::getVar('cli', 'tar');
            if (!empty($tarBinary) && is_string($tarBinary)) {
                $tarBinary = trim($tarBinary);

                $escapedFiles = [];
                if (is_array($files)) {
                    foreach ($files as $file) {
                        $escapedFiles[] = escapeshellarg((string) $file);
                    }
                }
                
                $command = escapeshellarg($tarBinary) . ' -c -z ' .
                           '-f ' . escapeshellarg($archivePath) . ' ' .
                           '-C ' . escapeshellarg($filesDir) . ' ' .
                           implode(' ', $escapedFiles);
                
                // Execute the command
                exec($command);
            }
        }

        return $archivePath;
    }

    /**
     * Return true if the ZipArchive class is available.
     * @return boolean
     */
    public function zipFunctional() {
        return class_exists('ZipArchive');
    }
    
}
?>
<?php
declare(strict_types=1);

/**
 * @defgroup file
 */

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
        self::__construct();
    }

    /**
     * Assembles an array of filenames into either a tar.gz or a .zip
     * file, based on what is available.  Returns a string representing
     * the path to the archive on disk.
     * @param array $files the files to add.
     * @param string $filesDir a path to the files on disk.
     * @return string the path to the archive.
     */
    public function create($files, $filesDir) {
        // Create a temporary file.
        // Modernisasi: Gunakan sys_get_temp_dir() agar portable di berbagai OS
        $archivePath = tempnam(sys_get_temp_dir(), 'sf-');

        // attempt to use Zip first, if it is available.  Otherwise
        // fall back to the tar CLI.
        $zipTest = false;
        if ($this->zipFunctional()) {
            $zipTest = true;
            $zip = new ZipArchive();
            if ($zip->open($archivePath, ZipArchive::CREATE) == true) {
                foreach ($files as $file) {
                    $filePath = $filesDir . '/' . $file;
                    // Validasi file fisik ada sebelum ditambahkan
                    if (file_exists($filePath)) {
                        $zip->addFile($filePath, basename($file));
                    }
                }
                $zip->close();
            }
        } else {
            // Create the archive and download the file.
            $tarBinary = Config::getVar('cli', 'tar');
            if (!empty($tarBinary)) {
                exec($tarBinary . ' -c -z ' .
                        '-f ' . escapeshellarg($archivePath) . ' ' .
                        '-C ' . escapeshellarg($filesDir) . ' ' .
                        implode(' ', array_map('escapeshellarg', $files))
                );
            }
        }

        return $archivePath;
    }

    /**
     * Return true if the ZipArchive class is available.
     * (Modernisasi: Menghapus cek versi PHP jadul, fokus ke ketersediaan Class)
     * @return boolean
     */
    public function zipFunctional() {
        return class_exists('ZipArchive');
    }
}
?>
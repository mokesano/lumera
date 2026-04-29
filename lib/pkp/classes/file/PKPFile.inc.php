<?php
declare(strict_types=1);

/**
 * @file classes/file/PKPFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPFile
 * @ingroup file
 *
 * @brief Base PKP file class.
 */

class PKPFile extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPFile() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPFile(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of file.
     * @return int
     */
    public function getFileId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set ID of file.
     * @param $fileId int
     */
    public function setFileId($fileId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($fileId);
    }

    /**
     * Get file name of the file.
     * @return string
     */
    public function getFileName() {
        return $this->getData('fileName');
    }

    /**
     * Set file name of the file.
     * @param $fileName string
     */
    public function setFileName($fileName) {
        return $this->setData('fileName', $fileName);
    }

    /**
     * Get original uploaded file name of the file.
     * @return string
     */
    public function getOriginalFileName() {
        return $this->getData('originalFileName');
    }

    /**
     * Set original uploaded file name of the file.
     * @param $originalFileName string
     */
    public function setOriginalFileName($originalFileName) {
        return $this->setData('originalFileName', $originalFileName);
    }

    /**
     * Get type of the file.
     * @return string
     */
    public function getFileType() {
        return $this->getData('filetype');
    }

    /**
     * Set type of the file.
     * @param $fileType string
     */
    public function setFileType($fileType) {
        return $this->setData('filetype', $fileType);
    }

    /**
     * Get uploaded date of file.
     * @return date
     */
    public function getDateUploaded() {
        return $this->getData('dateUploaded');
    }

    /**
     * Set uploaded date of file.
     * @param $dateUploaded date
     */
    public function setDateUploaded($dateUploaded) {
        return $this->setData('dateUploaded', $dateUploaded);
    }

    /**
     * Get file size of file.
     * @return int
     */
    public function getFileSize() {
        return $this->getData('fileSize');
    }

    /**
     * Set file size of file.
     * @param $fileSize int
     */
    public function setFileSize($fileSize) {
        return $this->setData('fileSize', $fileSize);
    }

    /**
     * Return pretty file size string (in B, KB, MB, or GB units).
     * Enhanced: High precision output (3 decimals for MB and GB).
     * @return string
     */
    public function getNiceFileSize() {
        $size = (float) $this->getData('fileSize');

        $kb = 1024;
        $mb = 1024 * $kb;
        $gb = 1024 * $mb;

        // Jika ukuran >= 1 GB (Tampilkan 3 desimal)
        if ($size >= $gb) {
            return number_format($size / $gb, 3, ',', '.') . ' GB';
        } 
        // Jika ukuran >= 1 MB (Tampilkan 3 desimal, misal: 1,263 MB atau 5,234 MB)
        elseif ($size >= $mb) {
            return number_format($size / $mb, 3, ',', '.') . ' MB';
        } 
        // Jika ukuran >= 1 KB (Tampilkan bulat tanpa desimal, misal: 892 KB)
        elseif ($size >= $kb) {
            return number_format($size / $kb, 0, ',', '.') . ' KB';
        } 
        // Jika ukuran di bawah 1 KB
        else {
            return number_format($size, 0, ',', '.') . ' B';
        }
    }

    //
    // Abstract template methods to be implemented by subclasses.
    //
    /**
     * Return absolute path to the file on the host filesystem.
     * @return string
     */
    public function getFilePath() {
        assert(false);
    }
}

?>
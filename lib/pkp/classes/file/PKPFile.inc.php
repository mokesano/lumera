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
        $this->__construct();
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of file.
     * @return int
     */
    public function getFileId() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return (int) $this->getId();
    }

    /**
     * Set ID of file.
     * @param int $fileId int
     */
    public function setFileId($fileId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        $this->setId((int) $fileId);
    }

    /**
     * Get file name of the file.
     * @return string
     */
    public function getFileName() {
        $data = $this->getData('fileName');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set file name of the file.
     * @param string $fileName string
     */
    public function setFileName($fileName) {
        $this->setData('fileName', (string) $fileName);
    }

    /**
     * Get original uploaded file name of the file.
     * @return string
     */
    public function getOriginalFileName() {
        $data = $this->getData('originalFileName');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set original uploaded file name of the file.
     * @param string $originalFileName string
     */
    public function setOriginalFileName($originalFileName) {
        $this->setData('originalFileName', (string) $originalFileName);
    }

    /**
     * Get type of the file.
     * @return string
     */
    public function getFileType() {
        $data = $this->getData('filetype');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set type of the file.
     * @param string $fileType string
     */
    public function setFileType($fileType) {
        $this->setData('filetype', (string) $fileType);
    }

    /**
     * Get uploaded date of file.
     * @return string
     */
    public function getDateUploaded() {
        $data = $this->getData('dateUploaded');
        return $data !== null ? (string) $data : '';
    }

    /**
     * Set uploaded date of file.
     * @param string $dateUploaded date
     */
    public function setDateUploaded($dateUploaded) {
        $this->setData('dateUploaded', (string) $dateUploaded);
    }

    /**
     * Get file size of file.
     * @return int
     */
    public function getFileSize() {
        $data = $this->getData('fileSize');
        return $data !== null ? (int) $data : 0;
    }

    /**
     * Set file size of file.
     * @param int $fileSize int
     */
    public function setFileSize($fileSize) {
        $this->setData('fileSize', (int) $fileSize);
    }

    /**
     * Return pretty file size string (in B, KB, MB, or GB units).
     * Enhanced: High precision output (3 decimals for MB and GB).
     * @return string
     */
    public function getNiceFileSize() {
        $size = (float) ($this->getData('fileSize') ?? 0);

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
        throw new \BadMethodCallException('Subclasses of PKPFile must implement the getFilePath() method.');
    }
    
}
?>
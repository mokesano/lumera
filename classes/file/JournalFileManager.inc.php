<?php
declare(strict_types=1);

/**
 * @file classes/file/JournalFileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalFileManager
 * @ingroup file
 *
 * @brief Class defining operations for private journal file management.
 */


import('lib.pkp.classes.file.FileManager');

class JournalFileManager extends FileManager {

    /** @var string the path to location of the files */
    public $filesDir;

    /** @var int the ID of the associated journal */
    public $journalId;

    /** @var Journal the associated article */
    public $journal;

    /**
     * Constructor.
     * Create a manager for handling journal file uploads.
     * @param $journal Journal
     */
    public function __construct($journal) {
        // Hapus '&' pada parameter objek
        $this->journalId = $journal->getId();
        $this->journal = $journal;
        $this->filesDir = Config::getVar('files', 'files_dir') . '/journals/' . $this->journalId . '/';

        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalFileManager($journal) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::JournalFileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($journal);
    }

    /**
     * Upload a file to the journal's private directory.
     * @param string $fileName The name of the file in the upload form
     * @param string $destFileName The destination file name (relative to journal files dir)
     * @param string|null $errorMsg
     * @return boolean
     */
    public function uploadFile($fileName, $destFileName, &$errorMsg = null) {
        // BUGFIX: Kode asli menggunakan variabel yang tidak konsisten ($dest vs $destFileName)
        // Kita meneruskan path lengkap ke parent::uploadFile
        return parent::uploadFile($fileName, $this->filesDir . $destFileName, $errorMsg);
    }

    /**
     * Download a file from the journal's private directory.
     * @param string $filePath Relative path/filename
     * @param string|null $mediaType MIME type
     * @param boolean $inline
     * @param string|null $fileName Download filename
     * @return boolean
     */
    public function downloadFile($filePath, $mediaType = null, $inline = false, $fileName = null) {
        // BUGFIX: Kode asli menggunakan variable $fileType yang tidak didefinisikan (seharusnya $mediaType)
        // BUGFIX: Kode asli lupa meneruskan $fileName (nama download) ke parent
        return parent::downloadFile($this->filesDir . $filePath, $mediaType, $inline, $fileName);
    }

    /**
     * Delete a file from the journal's private directory.
     * @param string $fileName
     * @return boolean
     */
    public function deleteFile($fileName) {
        return parent::deleteFile($this->filesDir . $fileName);
    }
}

?>
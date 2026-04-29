<?php
declare(strict_types=1);

/**
 * @file classes/file/EditableFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditableFile
 * @ingroup file
 *
 * @brief Hack-and-slash class to help with editing XML files without losing
 * formatting and comments (i.e. unparsed editing).
 */

class EditableFile {
    /** @var string Content of the file */
    public $contents;
    
    /** @var string Full path to the file */
    public $filename;

    /**
     * Constructor.
     * @param $filename string
     */
    public function __construct($filename) {
        import('lib.pkp.classes.file.FileWrapper');
        $this->filename = $filename;
        // Modernisasi: Hapus &
        $wrapper = FileWrapper::wrapper($this->filename);
        $this->setContents($wrapper->contents());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EditableFile($filename) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::EditableFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($filename);
    }

    /**
     * Check if file exists.
     * @return boolean
     */
    public function exists() {
        return file_exists($this->filename);
    }

    /**
     * Get file contents.
     * @return string
     */
    public function getContents() {
        return $this->contents;
    }

    /**
     * Set file contents.
     * @param $contents string
     */
    public function setContents($contents) {
        // Modernisasi: Hapus & assignment (PHP 7+ COW handles string efficiency)
        $this->contents = $contents;
    }

    /**
     * Write contents to file.
     * @return boolean
     */
    public function write() {
        $fp = fopen($this->filename, 'w+');
        if ($fp === false) return false;
        fwrite($fp, $this->getContents());
        fclose($fp);
        return true;
    }

    /**
     * Escape XML characters.
     * @param $value string
     * @return string
     */
    public function xmlEscape($value) {
        $escapedValue = XMLNode::xmlentities($value, ENT_NOQUOTES);
        if ($value !== $escapedValue) return "<![CDATA[$value]]>";
        return $value;
    }
}

?>
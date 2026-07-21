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
    public $contents = '';
    
    /** @var string Full path to the file */
    public $filename = '';

    /**
     * Constructor.
     * @param mixed $filename
     */
    public function __construct($filename) {
        import('lib.pkp.classes.file.FileWrapper');

        $this->filename = (string) $filename;
        
        $wrapper = FileWrapper::wrapper($this->filename);

        if (is_object($wrapper) && method_exists($wrapper, 'contents')) {
            $this->setContents($wrapper->contents());
        } else {
            $this->setContents('');
        }
    }

    /**
     * [SHIM] Backward Compatibility
     * @param mixed $filename
     */
    public function EditableFile($filename) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::EditableFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct($filename);
    }

    /**
     * Check if file exists.
     * @return boolean
     */
    public function exists() {
        return file_exists((string) $this->filename);
    }

    /**
     * Get file contents.
     * @return string
     */
    public function getContents() {
        return $this->contents !== null ? (string) $this->contents : '';
    }

    /**
     * Set file contents.
     * @param mixed $contents
     */
    public function setContents($contents) {
        $this->contents = (string) $contents;
    }

    /**
     * Write contents to file.
     * @return boolean
     */
    public function write() {
        $filePath = (string) $this->filename;

        $fp = @fopen($filePath, 'w+');
        if ($fp === false) {
            return false;
        }
        
        $contentsToWrite = $this->getContents();

        $writeResult = fwrite($fp, $contentsToWrite);
        fclose($fp);
        
        return ($writeResult !== false);
    }

    /**
     * Escape XML characters.
     * @param mixed $value
     * @return string
     */
    public function xmlEscape($value) {
        $stringValue = (string) $value;
        
        $escapedValue = XMLNode::xmlentities($stringValue, ENT_NOQUOTES);
        if ($stringValue !== $escapedValue) {
            return "<![CDATA[" . $stringValue . "]]>";
        }
        
        return $stringValue;
    }

}
?>
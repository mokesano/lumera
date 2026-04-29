<?php
declare(strict_types=1);

/**
 * @file classes/file/EditableEmailFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditableEmailFile
 * @ingroup file
 *
 * @brief This class supports updating for email XML files.
 *
 */

import('lib.pkp.classes.file.EditableFile');

class EditableEmailFile {
    /** @var string Locale code */
    public $locale;
    
    /** @var EditableFile */
    public $editableFile;

    /**
     * Constructor.
     * @param $locale string
     * @param $filename string
     */
    public function __construct($locale, $filename) {
        $this->locale = $locale;
        $this->editableFile = new EditableFile($filename);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EditableEmailFile($locale, $filename) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::EditableEmailFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($locale, $filename);
    }

    /**
     * Check if file exists.
     * @return boolean
     */
    public function exists() {
        return $this->editableFile->exists();
    }

    /**
     * Write contents to file.
     */
    public function write() {
        $this->editableFile->write();
    }

    /**
     * Get file contents.
     * @return string
     */
    public function getContents() {
        return $this->editableFile->getContents();
    }

    /**
     * Set file contents.
     * @param $contents string
     */
    public function setContents($contents) {
        $this->editableFile->setContents($contents);
    }

    /**
     * Update an email key.
     * @param $key string
     * @param $subject string
     * @param $body string
     * @param $description string
     * @return boolean
     */
    public function update($key, $subject, $body, $description) {
        $matches = null;
        $quotedKey = PKPString::regexp_quote($key);
        preg_match(
            "/<email_text[\W]+key=\"$quotedKey\">/",
            $this->getContents(),
            $matches,
            PREG_OFFSET_CAPTURE
        );
        if (!isset($matches[0])) return false;

        $offset = $matches[0][1];
        $closeOffset = strpos($this->getContents(), '</email_text>', $offset);
        if ($closeOffset === FALSE) return false;

        $newContents = substr($this->getContents(), 0, $offset);
        $newContents .= '<email_text key="' . $this->editableFile->xmlEscape($key) . '">
        <subject>' . $this->editableFile->xmlEscape($subject) . '</subject>
        <body>' . $this->editableFile->xmlEscape($body) . '</body>
        <description>' . $this->editableFile->xmlEscape($description) . '</description>
    ';
        $newContents .= substr($this->getContents(), $closeOffset);
        $this->setContents($newContents);
        return true;
    }

    /**
     * Delete an email key.
     * @param $key string
     * @return boolean
     */
    public function delete($key) {
        $matches = null;
        $quotedKey = PKPString::regexp_quote($key);
        preg_match(
            "/<email_text[\W]+key=\"$quotedKey\">/",
            $this->getContents(),
            $matches,
            PREG_OFFSET_CAPTURE
        );
        if (!isset($matches[0])) return false;
        $offset = $matches[0][1];

        preg_match("/<\/email_text>[ \t]*[\r]?\n/", $this->getContents(), $matches, PREG_OFFSET_CAPTURE, $offset);
        if (!isset($matches[0])) return false;
        $closeOffset = $matches[0][1] + strlen($matches[0][0]);

        $newContents = substr($this->getContents(), 0, $offset);
        $newContents .= substr($this->getContents(), $closeOffset);
        $this->setContents($newContents);
        return true;
    }

    /**
     * Insert a new email key.
     * @param $key string
     * @param $subject string
     * @param $body string
     * @param $description string
     * @return boolean
     */
    public function insert($key, $subject, $body, $description) {
        $offset = strrpos($this->getContents(), '</email_texts>');
        if ($offset === false) return false;
        
        $newContents = substr($this->getContents(), 0, $offset);
        $newContents .= '    <email_text key="' . $this->editableFile->xmlEscape($key) . '">
        <subject>' . $this->editableFile->xmlEscape($subject) . '</subject>
        <body>' . $this->editableFile->xmlEscape($body) . '</body>
        <description>' . $this->editableFile->xmlEscape($description) . '</description>
    </email_text>
';
        $newContents .= substr($this->getContents(), $offset);
        $this->setContents($newContents);
        return true;
    }
}

?>
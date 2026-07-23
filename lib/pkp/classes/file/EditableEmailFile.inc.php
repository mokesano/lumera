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
 */

import('lib.pkp.classes.file.EditableFile');

class EditableEmailFile {

    /** @var string Locale code */
    public $locale = '';
    
    /** @var EditableFile */
    public $editableFile;

    /**
     * Constructor.
     * @param mixed $locale
     * @param mixed $filename
     */
    public function __construct($locale, $filename) {
        // [LUMERA FIX] Casting di dalam body
        $this->locale = (string) $locale;
        $this->editableFile = new EditableFile((string) $filename);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param mixed $locale
     * @param mixed $filename
     */
    public function EditableEmailFile($locale, $filename) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::EditableEmailFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct($locale, $filename);
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
        return (string) $this->editableFile->getContents();
    }

    /**
     * Set file contents.
     * @param mixed $contents
     */
    public function setContents($contents) {
        $this->editableFile->setContents((string) $contents);
    }

    /**
     * Update an email key.
     * @param mixed $key
     * @param mixed $subject
     * @param mixed $body
     * @param mixed $description
     * @return boolean
     */
    public function update($key, $subject, $body, $description) {
        $key = (string) $key;
        $subject = (string) $subject;
        $body = (string) $body;
        $description = (string) $description;

        $contents = $this->getContents();

        $matches = null;
        $quotedKey = PKPString::regexp_quote($key);
        $pregResult = preg_match(
            "/<email_text[\W]+key=\"$quotedKey\">/",
            $contents,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if ($pregResult === false || !isset($matches[0]) || !isset($matches[0][1])) {
            return false;
        }

        $offset = (int) $matches[0][1];
        $closeOffset = strpos($contents, '</email_text>', $offset);
        if ($closeOffset === false) {
            return false;
        }

        $newContents = substr($contents, 0, $offset);
        $newContents .= '<email_text key="' . $this->editableFile->xmlEscape($key) . '">
        <subject>' . $this->editableFile->xmlEscape($subject) . '</subject>
        <body>' . $this->editableFile->xmlEscape($body) . '</body>
        <description>' . $this->editableFile->xmlEscape($description) . '</description>
    ';
        $newContents .= substr($contents, $closeOffset);
        $this->setContents($newContents);
        
        return true;
    }

    /**
     * Delete an email key.
     * @param mixed $key
     * @return boolean
     */
    public function delete($key) {
        $key = (string) $key;

        $contents = $this->getContents();

        $matches = null;
        $quotedKey = PKPString::regexp_quote($key);
        $pregResult = preg_match(
            "/<email_text[\W]+key=\"$quotedKey\">/",
            $contents,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if ($pregResult === false || !isset($matches[0]) || !isset($matches[0][1])) {
            return false;
        }
        $offset = (int) $matches[0][1];

        $pregResult2 = preg_match("/<\/email_text>[ \t]*[\r]?\n/", $contents, $matches, PREG_OFFSET_CAPTURE, $offset);
        if ($pregResult2 === false || !isset($matches[0]) || !isset($matches[0][1]) || !isset($matches[0][0])) {
            return false;
        }
        
        $closeOffset = (int) $matches[0][1] + strlen((string) $matches[0][0]);

        $newContents = substr($contents, 0, $offset);
        $newContents .= substr($contents, $closeOffset);
        $this->setContents($newContents);

        return true;
    }

    /**
     * Insert a new email key.
     * @param mixed $key
     * @param mixed $subject
     * @param mixed $body
     * @param mixed $description
     * @return boolean
     */
    public function insert($key, $subject, $body, $description) {
        $key = (string) $key;
        $subject = (string) $subject;
        $body = (string) $body;
        $description = (string) $description;

        $contents = $this->getContents();

        $offset = strrpos($contents, '</email_texts>');
        if ($offset === false) {
            return false;
        }
        
        $newContents = substr($contents, 0, $offset);
        $newContents .= '    <email_text key="' . $this->editableFile->xmlEscape($key) . '">
        <subject>' . $this->editableFile->xmlEscape($subject) . '</subject>
        <body>' . $this->editableFile->xmlEscape($body) . '</body>
        <description>' . $this->editableFile->xmlEscape($description) . '</description>
    </email_text>
';
        $newContents .= substr($contents, $offset);
        $this->setContents($newContents);
        
        return true;
    }

}
?>
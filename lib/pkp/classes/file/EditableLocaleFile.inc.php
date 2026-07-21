<?php
declare(strict_types=1);

/**
 * @file classes/file/EditableLocaleFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditableLocaleFile
 * @ingroup file
 *
 * @brief This extension of LocaleFile.inc.php supports updating.
 */

import('lib.pkp.classes.file.EditableFile');

class EditableLocaleFile extends LocaleFile {

    /** @var EditableFile */
    public $editableFile;

    /**
     * Constructor.
     * @param mixed $locale
     * @param mixed $filename
     */
    public function __construct($locale, $filename) {
        parent::__construct((string) $locale, (string) $filename);
        $this->editableFile = new EditableFile($this->filename);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param mixed $locale
     * @param mixed $filename
     */
    public function EditableLocaleFile($locale, $filename) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::EditableLocaleFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct($locale, $filename);
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
     * Update a key value in the locale file.
     * @param mixed $key
     * @param mixed $value
     * @return boolean
     */
    public function update($key, $value) {
        $key = (string) $key;
        $value = (string) $value;

        $contents = $this->getContents();

        $matches = null;
        $quotedKey = PKPString::regexp_quote($key);
        $pregResult = preg_match(
            "/<message[\W]+key=\"$quotedKey\">/",
            $contents,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if ($pregResult === false || !isset($matches[0]) || !isset($matches[0][1])) {
            return false;
        }

        $offset = (int) $matches[0][1];
        $closeOffset = strpos($contents, '</message>', $offset);
        if ($closeOffset === false) {
            return false;
        }

        $newContents = substr($contents, 0, $offset);
        $newContents .= "<message key=\"$key\">" . $this->editableFile->xmlEscape($value);
        $newContents .= substr($contents, $closeOffset);
        $this->setContents($newContents);
        
        return true;
    }

    /**
     * Delete a key from the locale file.
     * @param mixed $key
     * @return boolean
     */
    public function delete($key) {
        $key = (string) $key;

        $contents = $this->getContents();

        $matches = null;
        $quotedKey = PKPString::regexp_quote($key);
        $pregResult = preg_match(
            "/[ \t]*<message[\W]+key=\"$quotedKey\">/",
            $contents,
            $matches,
            PREG_OFFSET_CAPTURE
        );

        if ($pregResult === false || !isset($matches[0]) || !isset($matches[0][1])) {
            return false;
        }
        $offset = (int) $matches[0][1];

        $pregResult2 = preg_match(
            "/<\/message>[\W]*[\r]?\n/",
            $contents,
            $matches,
            PREG_OFFSET_CAPTURE,
            $offset
        );

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
     * Insert a new key into the locale file.
     * @param mixed $key
     * @param mixed $value
     * @return boolean
     */
    public function insert($key, $value) {
        $key = (string) $key;
        $value = (string) $value;

        $contents = $this->getContents();
        $offset = strrpos($contents, '</locale>');
        if ($offset === false) {
            return false;
        }
        
        $newContents = substr($contents, 0, $offset);
        $newContents .= "\t<message key=\"$key\">" . $this->editableFile->xmlEscape($value) . "</message>\n";
        $newContents .= substr($contents, $offset);
        $this->setContents($newContents);
        
        return true;
    }

}
?>
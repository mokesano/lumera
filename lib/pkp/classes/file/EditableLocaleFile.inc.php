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
 *
 */

import('lib.pkp.classes.file.EditableFile');

class EditableLocaleFile extends LocaleFile {
    /** @var EditableFile */
    public $editableFile;

    /**
     * Constructor.
     * @param $locale string
     * @param $filename string
     */
    public function __construct($locale, $filename) {
        parent::__construct($locale, $filename);
        $this->editableFile = new EditableFile($this->filename);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EditableLocaleFile($locale, $filename) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::EditableLocaleFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($locale, $filename);
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
     * Update a key value in the locale file.
     * @param $key string
     * @param $value string
     * @return boolean
     */
    public function update($key, $value) {
        $matches = null;
        $quotedKey = PKPString::regexp_quote($key);
        preg_match(
            "/<message[\W]+key=\"$quotedKey\">/",
            $this->getContents(),
            $matches,
            PREG_OFFSET_CAPTURE
        );
        if (!isset($matches[0])) return false;

        $offset = $matches[0][1];
        $closeOffset = strpos($this->getContents(), '</message>', $offset);
        if ($closeOffset === FALSE) return false;

        $newContents = substr($this->getContents(), 0, $offset);
        $newContents .= "<message key=\"$key\">" . $this->editableFile->xmlEscape($value);
        $newContents .= substr($this->getContents(), $closeOffset);
        $this->setContents($newContents);
        return true;
    }

    /**
     * Delete a key from the locale file.
     * @param $key string
     * @return boolean
     */
    public function delete($key) {
        $matches = null;
        $quotedKey = PKPString::regexp_quote($key);
        preg_match(
            "/[ \t]*<message[\W]+key=\"$quotedKey\">/",
            $this->getContents(),
            $matches,
            PREG_OFFSET_CAPTURE
        );
        if (!isset($matches[0])) return false;
        $offset = $matches[0][1];

        preg_match("/<\/message>[\W]*[\r]?\n/", $this->getContents(), $matches, PREG_OFFSET_CAPTURE, $offset);
        if (!isset($matches[0])) return false;
        $closeOffset = $matches[0][1] + strlen($matches[0][0]);

        $newContents = substr($this->getContents(), 0, $offset);
        $newContents .= substr($this->getContents(), $closeOffset);
        $this->setContents($newContents);
        return true;
    }

    /**
     * Insert a new key into the locale file.
     * @param $key string
     * @param $value string
     * @return boolean|void
     */
    public function insert($key, $value) {
        $offset = strrpos($this->getContents(), '</locale>');
        if ($offset === false) return false;
        
        $newContents = substr($this->getContents(), 0, $offset);
        $newContents .= "\t<message key=\"$key\">" . $this->editableFile->xmlEscape($value) . "</message>\n";
        $newContents .= substr($this->getContents(), $offset);
        $this->setContents($newContents);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/note/Note.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Note
 * @ingroup note
 * @see NoteDAO
 *
 * @brief Class for OJS Note.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.article.ArticleFile');
import('lib.pkp.classes.note.PKPNote');

class Note extends PKPNote {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Note() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::Note(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * get article note id
     * @return int
     */
    public function getNoteId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * set article note id
     * @param int $noteId
     */
    public function setNoteId($noteId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($noteId);
    }

    /**
     * get article id
     * @return int
     */
    public function getArticleId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getAssocId();
    }

    /**
     * set article id
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setAssocId($articleId);
    }

    /**
     * get note
     * @return string
     */
    public function getNote() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getContents();
    }

    /**
     * set note
     * @param string $note
     */
    public function setNote($note) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setContents($note);
    }

    /**
     * get file
     * @return ArticleFile|null
     */
    public function getFile() {
        return $this->getData('file');
    }

    /**
     * set file
     * @param ArticleFile $file
     */
    public function setFile($file) {
        return $this->setData('file', $file);
    }

    /**
     * Get original filename
     * @return string|null
     */
    public function getOriginalFileName() {
        $file = $this->getFile();
        // [WIZDAM FIX] Prevent Fatal Error if file is null
        if ($file instanceof ArticleFile) {
            return $file->getOriginalFileName();
        }
        return null;
    }
}
?>
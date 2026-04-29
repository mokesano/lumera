<?php
declare(strict_types=1);

/**
 * @file classes/note/NoteDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NoteDAO
 * @ingroup note
 * @see PKPNoteDAO
 *
 * @brief OJS extension of PKPNoteDAO
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.note.PKPNoteDAO');
import('classes.note.Note');

class NoteDAO extends PKPNoteDAO {
    /** @var ArticleFileDAO */
    public $articleFileDao;

    /**
     * Constructor
     */
    public function __construct() {
        $this->articleFileDao = DAORegistry::getDAO('ArticleFileDAO');
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NoteDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::NoteDAO(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return Note
     */
    public function newDataObject() {
        return new Note();
    }

    /**
     * Return a Note object from a row.
     * @param array $row
     * @return Note
     */
    public function _returnNoteFromRow($row) {
        $note = parent::_returnNoteFromRow($row);

        if ($fileId = $note->getFileId()) {
            $file = $this->articleFileDao->getArticleFile($fileId);
            $note->setFile($file);
        }

        return $note;
    }
}
?>
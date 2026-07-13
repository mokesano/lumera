<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleNoteDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleNoteDAO
 * @ingroup article
 * @see ArticleNote
 *
 * @brief Operations for retrieving and modifying ArticleNote objects.
 */

import('classes.article.ArticleNote');
import('classes.note.NoteDAO');

class ArticleNoteDAO extends NoteDAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleNoteDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ArticleNoteDAO uses deprecated constructor parent::ArticleNoteDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * [DEPRECATED] Retrieve Article Notes by article id.
     * Use getByAssoc()
     * @param int $articleId
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory containing ArticleNotes
     */
    public function getArticleNotes($articleId, $rangeInfo = NULL) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        
        $returner = $this->getByAssoc(ASSOC_TYPE_ARTICLE, (int) $articleId, $rangeInfo);
        return $returner;
    }

    /**
     * [DEPRECATED] Retrieve Article Notes by user id.
     * Use getByUserId()
     * @param int $userId
     * @param DBResultRange|null $rangeInfo
     * @return DAOResultFactory containing ArticleNotes
     */
    public function getArticleNotesByUserId($userId, $rangeInfo = NULL) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        
        $returner = $this->getByUserId((int) $userId, $rangeInfo);
        return $returner;
    }

    /**
     * [DEPRECATED] Retrieve Article Note by note id.
     * Use getById()
     * @param int $noteId
     * @return ArticleNote|null
     */
    public function getArticleNoteById($noteId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        
        $returner = $this->getById((int) $noteId);
        return $returner;
    }

    /**
     * [DEPRECATED] Inserts a new article note into notes table.
     * Use insertObject()
     * @param ArticleNote $articleNote
     * @return int Article Note Id
     */
    public function insertArticleNote($articleNote) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        
        $articleNote->setAssocType(ASSOC_TYPE_ARTICLE);
        return $this->insertObject($articleNote);
    }

    /**
     * [DEPRECATED] Get the ID of the last inserted article note.
     * Use getInsertNoteId()
     * @return int
     */
    public function getInsertArticleNoteId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        return $this->getInsertNoteId();
    }

    /**
     * [DEPRECATED] Removes an article note by id
     * Use deleteById()
     * @param int $noteId
     * @return boolean
     */
    public function deleteArticleNoteById($noteId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        return $this->deleteById((int) $noteId);
    }

    /**
     * [DEPRECATED] Updates an article note
     * Use updateObject()
     * @param ArticleNote $articleNote
     * @return boolean
     */
    public function updateArticleNote($articleNote) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        return $this->updateObject($articleNote);
    }

    /**
     * [DEPRECATED] Get all article note file ids
     * Use getAllFileIds()
     * @param int $articleId
     * @return array
     */
    public function getAllArticleNoteFileIds($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        return $this->getAllFileIds(ASSOC_TYPE_ARTICLE, (int) $articleId);
    }

    /**
     * [DEPRECATED] Clear all article notes
     * Use deleteByAssoc()
     * @param int $articleId
     * @return boolean
     */
    public function clearAllArticleNotes($articleId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function', E_USER_DEPRECATED);
        return $this->deleteByAssoc(ASSOC_TYPE_ARTICLE, (int) $articleId);
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleCommentDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleCommentDAO
 * @ingroup article
 * @see ArticleComment
 *
 * @brief Operations for retrieving and modifying ArticleComment objects.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Ref removal, Visibility)
 * - HookRegistry::dispatch
 * - Strict Integer Casting
 */

import('classes.article.ArticleComment');

class ArticleCommentDAO extends DAO {
    
    /**
     * Retrieve ArticleComments by article id
     * @param int $articleId
     * @param int|null $commentType
     * @param int|null $assocId
     * @return array ArticleComment objects
     */
    public function getArticleComments($articleId, $commentType = null, $assocId = null) {
        $articleComments = array();

        if ($commentType == null) {
            $result = $this->retrieve(
                'SELECT a.* FROM article_comments a WHERE article_id = ? ORDER BY date_posted', 
                (int) $articleId
            );
        } else {
            if ($assocId == null) {
                $result = $this->retrieve(
                    'SELECT a.* FROM article_comments a WHERE article_id = ? AND comment_type = ? ORDER BY date_posted', 
                    array((int) $articleId, (int) $commentType)
                );
            } else {
                $result = $this->retrieve(
                    'SELECT a.* FROM article_comments a WHERE article_id = ? AND comment_type = ? AND assoc_id = ? ORDER BY date_posted',
                    array((int) $articleId, (int) $commentType, (int) $assocId)
                );
            }
        }

        while (!$result->EOF) {
            $articleComments[] = $this->_returnArticleCommentFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $articleComments;
    }

    /**
     * Retrieve ArticleComments by user id
     * @param int $userId
     * @return array ArticleComment objects
     */
    public function getArticleCommentsByUserId($userId) {
        $articleComments = array();

        $result = $this->retrieve(
            'SELECT a.* FROM article_comments a WHERE author_id = ? ORDER BY date_posted', 
            (int) $userId
        );

        while (!$result->EOF) {
            $articleComments[] = $this->_returnArticleCommentFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $articleComments;
    }

    /**
     * Retrieve most recent ArticleComment
     * @param int $articleId
     * @param int|null $commentType
     * @param int|null $assocId
     * @return ArticleComment|null
     */
    public function getMostRecentArticleComment($articleId, $commentType = null, $assocId = null) {
        if ($commentType == null) {
            $result = $this->retrieveLimit(
                'SELECT a.* FROM article_comments a WHERE article_id = ? ORDER BY date_posted DESC',
                (int) $articleId,
                1
            );
        } else {
            if ($assocId == null) {
                $result = $this->retrieveLimit(
                    'SELECT a.* FROM article_comments a WHERE article_id = ? AND comment_type = ? ORDER BY date_posted DESC',
                    array((int) $articleId, (int) $commentType),
                    1
                );
            } else {
                $result = $this->retrieveLimit(
                    'SELECT a.* FROM article_comments a WHERE article_id = ? AND comment_type = ? AND assoc_id = ? ORDER BY date_posted DESC',
                    array((int) $articleId, (int) $commentType, (int) $assocId),
                    1
                );
            }
        }

        $returner = null;
        if (isset($result) && $result->RecordCount() != 0) {
            $returner = $this->_returnArticleCommentFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve Article Comment by comment id
     * @param int $commentId
     * @return ArticleComment|null
     */
    public function getArticleCommentById($commentId) {
        $result = $this->retrieve(
            'SELECT a.* FROM article_comments a WHERE comment_id = ?', 
            (int) $commentId
        );

        $articleComment = null;
        if ($result->RecordCount() != 0) {
            $articleComment = $this->_returnArticleCommentFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $articleComment;
    }

    /**
     * Creates and returns an article comment object from a row
     * @param array $row
     * @return ArticleComment object
     */
    public function _returnArticleCommentFromRow($row) {
        $articleComment = new ArticleComment();
        $articleComment->setId($row['comment_id']);
        $articleComment->setCommentType($row['comment_type']);
        $articleComment->setRoleId($row['role_id']);
        $articleComment->setArticleId($row['article_id']);
        $articleComment->setAssocId($row['assoc_id']);
        $articleComment->setAuthorId($row['author_id']);
        $articleComment->setCommentTitle($row['comment_title']);
        $articleComment->setComments($row['comments']);
        $articleComment->setDatePosted($this->datetimeFromDB($row['date_posted']));
        $articleComment->setDateModified($this->datetimeFromDB($row['date_modified']));
        $articleComment->setViewable($row['viewable']);

        // Guideline #3: Object by val, Primitive/Array by ref (if needed)
        HookRegistry::dispatch('ArticleCommentDAO::_returnArticleCommentFromRow', array($articleComment, &$row));

        return $articleComment;
    }

    /**
     * inserts a new article comment into article_comments table
     * @param ArticleComment $articleComment (No & needed)
     * @return int Article Comment Id
     */
    public function insertArticleComment($articleComment) {
        $this->update(
            sprintf('INSERT INTO article_comments
                (comment_type, role_id, article_id, assoc_id, author_id, date_posted, date_modified, comment_title, comments, viewable)
                VALUES
                (?, ?, ?, ?, ?, %s, %s, ?, ?, ?)',
                $this->datetimeToDB($articleComment->getDatePosted()), 
                $this->datetimeToDB($articleComment->getDateModified())
            ),
            array(
                (int) $articleComment->getCommentType(),
                (int) $articleComment->getRoleId(),
                (int) $articleComment->getArticleId(),
                (int) $articleComment->getAssocId(),
                (int) $articleComment->getAuthorId(),
                PKPString::substr($articleComment->getCommentTitle(), 0, 255),
                $articleComment->getComments(),
                $articleComment->getViewable() === null ? 0 : (int) $articleComment->getViewable()
            )
        );

        $articleComment->setId($this->getInsertArticleCommentId());
        return $articleComment->getId();
    }

    /**
     * Get the ID of the last inserted article comment.
     * @return int
     */
    public function getInsertArticleCommentId() {
        return $this->getInsertId('article_comments', 'comment_id');
    }

    /**
     * removes an article comment from article_comments table
     * @param ArticleComment $articleComment
     */
    public function deleteArticleComment($articleComment) {
        $this->deleteArticleCommentById($articleComment->getId());
    }

    /**
     * removes an article note by id
     * @param int $commentId
     */
    public function deleteArticleCommentById($commentId) {
        $this->update(
            'DELETE FROM article_comments WHERE comment_id = ?', 
            (int) $commentId
        );
    }

    /**
     * Delete all comments for an article.
     * @param int $articleId
     */
    public function deleteArticleComments($articleId) {
        return $this->update(
            'DELETE FROM article_comments WHERE article_id = ?', 
            (int) $articleId
        );
    }

    /**
     * updates an article comment
     * @param ArticleComment $articleComment
     */
    public function updateArticleComment($articleComment) {
        $this->update(
            sprintf('UPDATE article_comments
                SET
                    comment_type = ?,
                    role_id = ?,
                    article_id = ?,
                    assoc_id = ?,
                    author_id = ?,
                    date_posted = %s,
                    date_modified = %s,
                    comment_title = ?,
                    comments = ?,
                    viewable = ?
                WHERE comment_id = ?',
                $this->datetimeToDB($articleComment->getDatePosted()), 
                $this->datetimeToDB($articleComment->getDateModified())
            ),
            array(
                (int) $articleComment->getCommentType(),
                (int) $articleComment->getRoleId(),
                (int) $articleComment->getArticleId(),
                (int) $articleComment->getAssocId(),
                (int) $articleComment->getAuthorId(),
                PKPString::substr($articleComment->getCommentTitle(), 0, 255),
                $articleComment->getComments(),
                $articleComment->getViewable() === null ? 1 : (int) $articleComment->getViewable(),
                (int) $articleComment->getId()
            )
        );
    }
}

?>
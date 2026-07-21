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
 */

import('classes.article.ArticleComment');

class ArticleCommentDAO extends DAO {
    
    /**
     * Retrieve ArticleComments by article id
     * 
     * @param int $articleId
     * @param int|null $commentType
     * @param int|null $assocId
     * @return array ArticleComment objects
     */
    public function getArticleComments($articleId, $commentType = null, $assocId = null) {
        $articleComments = [];

        $sql = 'SELECT a.* FROM article_comments a WHERE article_id = ?';
        $params = [(int) $articleId];

        if ($commentType !== null) {
            $sql .= ' AND comment_type = ?';
            $params[] = (int) $commentType;

            if ($assocId !== null) {
                $sql .= ' AND assoc_id = ?';
                $params[] = (int) $assocId;
            }
        }

        $sql .= ' ORDER BY date_posted';

        $result = $this->retrieve($sql, $params);

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
     * 
     * @param int $userId
     * @return array ArticleComment objects
     */
    public function getArticleCommentsByUserId($userId) {
        $articleComments = [];

        $result = $this->retrieve(
            'SELECT a.* FROM article_comments a WHERE author_id = ? ORDER BY date_posted', 
            [(int) $userId]
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
     * 
     * @param int $articleId
     * @param int|null $commentType
     * @param int|null $assocId
     * @return ArticleComment|null
     */
    public function getMostRecentArticleComment($articleId, $commentType = null, $assocId = null) {
        $sql = 'SELECT a.* FROM article_comments a WHERE article_id = ?';
        $params = [(int) $articleId];

        if ($commentType !== null) {
            $sql .= ' AND comment_type = ?';
            $params[] = (int) $commentType;

            if ($assocId !== null) {
                $sql .= ' AND assoc_id = ?';
                $params[] = (int) $assocId;
            }
        }

        $sql .= ' ORDER BY date_posted DESC';

        $result = $this->retrieveLimit($sql, $params, 1);

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnArticleCommentFromRow($result->GetRowAssoc(false));
        }

        if ($result) {
            $result->Close();
        }
        unset($result);

        return $returner;
    }

    /**
     * Retrieve Article Comment by comment id
     * 
     * @param int $commentId
     * @return ArticleComment|null
     */
    public function getArticleCommentById($commentId) {
        $result = $this->retrieve(
            'SELECT a.* FROM article_comments a WHERE comment_id = ?', 
            [(int) $commentId]
        );

        $articleComment = null;
        if (!$result->EOF) {
            $articleComment = $this->_returnArticleCommentFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $articleComment;
    }

    /**
     * Creates and returns an article comment object from a row
     * 
     * @param array $row
     * @return ArticleComment
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

        HookRegistry::dispatch('ArticleCommentDAO::_returnArticleCommentFromRow', [$articleComment, &$row]);

        return $articleComment;
    }

    /**
     * Inserts a new article comment into article_comments table
     * 
     * @param ArticleComment $articleComment
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
            [
                (int) $articleComment->getCommentType(),
                (int) $articleComment->getRoleId(),
                (int) $articleComment->getArticleId(),
                (int) $articleComment->getAssocId(),
                (int) $articleComment->getAuthorId(),
                PKPString::substr($articleComment->getCommentTitle(), 0, 255),
                $articleComment->getComments(),
                $articleComment->getViewable() === null ? 0 : (int) $articleComment->getViewable()
            ]
        );

        $articleComment->setId($this->getInsertArticleCommentId());
        return $articleComment->getId();
    }

    /**
     * Get the ID of the last inserted article comment.
     * 
     * @return int
     */
    public function getInsertArticleCommentId() {
        return $this->getInsertId('article_comments', 'comment_id');
    }

    /**
     * Removes an article comment from article_comments table
     * 
     * @param ArticleComment $articleComment
     * @return bool
     */
    public function deleteArticleComment($articleComment) {
        return $this->deleteArticleCommentById($articleComment->getId());
    }

    /**
     * Removes an article note by id
     * 
     * @param int $commentId
     * @return bool
     */
    public function deleteArticleCommentById($commentId) {
        return $this->update(
            'DELETE FROM article_comments WHERE comment_id = ?', 
            [(int) $commentId]
        );
    }

    /**
     * Delete all comments for an article.
     * 
     * @param int $articleId
     * @return bool
     */
    public function deleteArticleComments($articleId) {
        return $this->update(
            'DELETE FROM article_comments WHERE article_id = ?', 
            [(int) $articleId]
        );
    }

    /**
     * Updates an article comment
     * 
     * @param ArticleComment $articleComment
     * @return bool
     */
    public function updateArticleComment($articleComment) {
        return $this->update(
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
            [
                (int) $articleComment->getCommentType(),
                (int) $articleComment->getRoleId(),
                (int) $articleComment->getArticleId(),
                (int) $articleComment->getAssocId(),
                (int) $articleComment->getAuthorId(),
                PKPString::substr($articleComment->getCommentTitle(), 0, 255),
                $articleComment->getComments(),
                $articleComment->getViewable() === null ? 1 : (int) $articleComment->getViewable(),
                (int) $articleComment->getId()
            ]
        );
    }
    
}
?>
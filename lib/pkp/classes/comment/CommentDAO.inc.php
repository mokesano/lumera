<?php
declare(strict_types=1);

/**
 * @file classes/comment/CommentDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CommentDAO
 * @ingroup comment
 * @see Comment
 *
 * @brief Operations for retrieving and modifying Comment objects.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.comment.Comment');

define ('SUBMISSION_COMMENT_RECURSE_ALL', -1);

// Comment system configuration constants
define ('COMMENTS_DISABLED', 0);    // All comments disabled
define ('COMMENTS_AUTHENTICATED', 1);    // Can be posted by authenticated users
define ('COMMENTS_ANONYMOUS', 2);    // Can be posted anonymously by authenticated users
define ('COMMENTS_UNAUTHENTICATED', 3);    // Can be posted anonymously by anyone

class CommentDAO extends DAO {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CommentDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Retrieve Comments by submission id
     * @param int $submissionId
     * @param int $childLevels optional
     * @return array Comment objects
     */
    public function getRootCommentsBySubmissionId($submissionId, $childLevels = 0) {
        $comments = [];

        $result = $this->retrieve(
            'SELECT *
            FROM comments
            WHERE submission_id = ? AND
                parent_comment_id IS NULL
            ORDER BY date_posted',
            (int) $submissionId
        );

        while (!$result->EOF) {
            $comments[] = $this->_returnCommentFromRow($result->GetRowAssoc(false), $childLevels);
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $comments;
    }

    /**
     * Retrieve Comments by parent comment id
     * @param int $parentId
     * @param int $childLevels
     * @return array Comment objects
     */
    public function getCommentsByParentId($parentId, $childLevels = 0) {
        $comments = [];

        $result = $this->retrieve('SELECT * FROM comments WHERE parent_comment_id = ? ORDER BY date_posted', (int) $parentId);

        while (!$result->EOF) {
            $comments[] = $this->_returnCommentFromRow($result->GetRowAssoc(false), $childLevels);
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $comments;
    }

    /**
     * Retrieve comments by user id
     * @param int $userId
     * @return array Comment objects
     */
    public function getByUserId($userId) {
        $comments = [];

        $result = $this->retrieve('SELECT * FROM comments WHERE user_id = ?', (int) $userId);

        while (!$result->EOF) {
            $comments[] = $this->_returnCommentFromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $comments;
    }

    /**
     * Check whether any reader comments are attributed to the user.
     * @param int $userId The ID of the user to check
     * @return bool
     */
    public function attributedCommentsExistForUser($userId) {
        $result = $this->retrieve('SELECT count(*) FROM comments WHERE user_id = ?', (int) $userId);
        $returner = $result->fields[0] ? true : false;
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve Comment by comment id
     * @param int $commentId
     * @param int $submissionId optional
     * @param int $childLevels optional
     * @return Comment|null
     */
    public function getById($commentId, $submissionId, $childLevels = 0) {
        $result = $this->retrieve(
            'SELECT * FROM comments WHERE comment_id = ? and submission_id = ?',
            [(int) $commentId, (int) $submissionId]
        );

        $comment = null;
        if ($result->RecordCount() != 0) {
            $comment = $this->_returnCommentFromRow($result->GetRowAssoc(false), $childLevels);
        }

        $result->Close();
        unset($result);

        return $comment;
    }

    /**
     * Instantiate and return a new data object.
     * @return Comment
     */
    public function newDataObject() {
        return new Comment();
    }

    /**
     * Creates and returns a submission comment object from a row
     * @param array $row
     * @param int $childLevels
     * @return Comment
     */
    public function _returnCommentFromRow($row, $childLevels = 0) {
        $userDao = DAORegistry::getDAO('UserDAO');

        $comment = $this->newDataObject();
        $comment->setId($row['comment_id']);
        $comment->setSubmissionId($row['submission_id']);
        $comment->setUser($userDao->getById($row['user_id']), true);
        $comment->setPosterIP($row['poster_ip']);
        $comment->setPosterName($row['poster_name']);
        $comment->setPosterEmail($row['poster_email']);
        $comment->setTitle($row['title']);
        $comment->setBody($row['body']);
        $comment->setDatePosted($this->datetimeFromDB($row['date_posted']));
        $comment->setDateModified($this->datetimeFromDB($row['date_modified']));
        $comment->setParentCommentId($row['parent_comment_id']);
        $comment->setChildCommentCount($row['num_children']);

        if (!HookRegistry::dispatch('CommentDAO::_returnCommentFromRow', [&$comment, &$row, &$childLevels])) {
            if ($childLevels > 0) {
                $comment->setChildren($this->getCommentsByParentId($row['comment_id'], $childLevels - 1));
            } elseif ($childLevels == SUBMISSION_COMMENT_RECURSE_ALL) {
                $comment->setChildren($this->getCommentsByParentId($row['comment_id'], SUBMISSION_COMMENT_RECURSE_ALL));
            }
        }

        return $comment;
    }

    /**
     * inserts a new submission comment into comments table
     * @param Comment $comment
     * @return int ID of new comment
     */
    public function insertComment($comment) {
        $comment->setDatePosted(Core::getCurrentDate());
        $comment->setDateModified($comment->getDatePosted());
        $user = $comment->getUser();
        $this->update(
            sprintf('INSERT INTO comments
                (submission_id, num_children, parent_comment_id, user_id, poster_ip, date_posted, date_modified, title, body, poster_name, poster_email)
                VALUES
                (?, ?, ?, ?, ?, %s, %s, ?, ?, ?, ?)',
                $this->datetimeToDB($comment->getDatePosted()), $this->datetimeToDB($comment->getDateModified())),
            [
                $comment->getSubmissionId(),
                $comment->getChildCommentCount(),
                $comment->getParentCommentId(),
                (isset($user) ? $user->getId() : null),
                $comment->getPosterIP(),
                PKPString::substr($comment->getTitle(), 0, 255),
                $comment->getBody(),
                PKPString::substr($comment->getPosterName(), 0, 90),
                PKPString::substr($comment->getPosterEmail(), 0, 90)
            ]
        );

        $comment->setId($this->getInsertCommentId());

        if ($comment->getParentCommentId()) {
            $this->incrementChildCount($comment->getParentCommentId());
        }

        return $comment->getId();
    }

    /**
     * Get the ID of the last inserted submission comment.
     * @return int
     */
    public function getInsertCommentId() {
        return $this->getInsertId('comments', 'comment_id');
    }

    /**
     * Increase the current count of child comments for the specified comment.
     * @param int $commentId
     */
    public function incrementChildCount($commentId) {
        $this->update('UPDATE comments SET num_children=num_children+1 WHERE comment_id = ?', (int) $commentId);
    }

    /**
     * Decrease the current count of child comments for the specified comment.
     * @param int $commentId
     */
    public function decrementChildCount($commentId) {
        $this->update('UPDATE comments SET num_children=num_children-1 WHERE comment_id = ?', (int) $commentId);
    }

    /**
     * Removes a submission comment from comments table
     * @param Comment $comment
     * @param bool $isRecursing
     */
    public function deleteComment($comment, $isRecursing = false) {
        $this->update('DELETE FROM comments WHERE comment_id = ?', (int) $comment->getId());
        if (!$isRecursing) {
            $this->decrementChildCount($comment->getParentCommentId());
        }
        $children = $comment->getChildren();
        if (is_array($children)) {
            foreach ($children as $child) {
                $this->deleteComment($child, true);
            }
        }
    }

    /**
     * Removes submission comments by submission ID
     * @param int $submissionId
     * @return bool
     */
    public function deleteBySubmissionId($submissionId) {
        return $this->update(
            'DELETE FROM comments WHERE submission_id = ?',
            (int) $submissionId
        );
    }

    /**
     * updates a comment
     * @param Comment $comment
     */
    public function updateComment($comment) {
        $comment->setDateModified(Core::getCurrentDate());
        $user = $comment->getUser();
        $this->update(
            sprintf('UPDATE comments
                SET
                    submission_id = ?,
                    num_children = ?,
                    parent_comment_id = ?,
                    user_id = ?,
                    poster_ip = ?,
                    date_posted = %s,
                    date_modified = %s,
                    title = ?,
                    body = ?,
                    poster_name = ?,
                    poster_email = ?
                WHERE comment_id = ?',
                $this->datetimeToDB($comment->getDatePosted()), $this->datetimeToDB($comment->getDateModified())),
            [
                $comment->getSubmissionId(),
                $comment->getChildCommentCount(),
                $comment->getParentCommentId(),
                (isset($user) ? $user->getId() : null),
                $comment->getPosterIP(),
                PKPString::substr($comment->getTitle(), 0, 255),
                $comment->getBody(),
                PKPString::substr($comment->getPosterName(), 0, 90),
                PKPString::substr($comment->getPosterEmail(), 0, 90),
                $comment->getId()
            ]
        );
    }
}

?>
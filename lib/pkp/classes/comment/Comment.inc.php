<?php
declare(strict_types=1);

/**
 * @defgroup comment
 */

/**
 * @file classes/comment/Comment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Comment
 * @ingroup comment
 * @see CommentDAO
 *
 * @brief Class for public Comment associated with submission.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */


class Comment extends DataObject {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->setPosterIP(Request::getRemoteAddr());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Comment() {
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
     * Get submission comment id
     * @return int
     */
    public function getCommentId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set submission comment id
     * @param int $commentId
     */
    public function setCommentId($commentId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($commentId);
    }

    /**
     * get number of child comments
     * @return int
     */
    public function getChildCommentCount() {
        return $this->getData('childCommentCount');
    }

    /**
     * set number of child comments
     * @param int $childCommentCount
     */
    public function setChildCommentCount($childCommentCount) {
        return $this->setData('childCommentCount', $childCommentCount);
    }

    /**
     * get parent comment id
     * @return int
     */
    public function getParentCommentId() {
        return $this->getData('parentCommentId');
    }

    /**
     * set parent comment id
     * @param int $parentCommentId
     */
    public function setParentCommentId($parentCommentId) {
        return $this->setData('parentCommentId', $parentCommentId);
    }

    /**
     * Get submission id
     * @return int
     */
    public function getSubmissionId() {
        return $this->getData('submissionId');
    }

    /**
     * Set submission id
     * @param int $submissionId
     */
    public function setSubmissionId($submissionId) {
        return $this->setData('submissionId', $submissionId);
    }

    /**
     * get user object
     * @return User|null
     */
    public function getUser() {
        return $this->getData('user');
    }

    /**
     * set user object
     * @param User $user
     */
    public function setUser($user) {
        return $this->setData('user', $user);
    }

    /**
     * get poster name
     * @return string
     */
    public function getPosterName() {
        return $this->getData('posterName');
    }

    /**
     * set poster name
     * @param string $posterName
     */
    public function setPosterName($posterName) {
        return $this->setData('posterName', $posterName);
    }

    /**
     * get poster email
     * @return string
     */
    public function getPosterEmail() {
        return $this->getData('posterEmail');
    }

    /**
     * set poster email
     * @param string $posterEmail
     */
    public function setPosterEmail($posterEmail) {
        return $this->setData('posterEmail', $posterEmail);
    }

    /**
     * get posterIP
     * @return string
     */
    public function getPosterIP() {
        return $this->getData('posterIP');
    }

    /**
     * set posterIP
     * @param string $posterIP
     */
    public function setPosterIP($posterIP) {
        return $this->setData('posterIP', $posterIP);
    }

    /**
     * get title
     * @return string
     */
    public function getTitle() {
        return $this->getData('title');
    }

    /**
     * set title
     * @param string $title
     */
    public function setTitle($title) {
        return $this->setData('title', $title);
    }

    /**
     * get comment body
     * @return string
     */
    public function getBody() {
        return $this->getData('body');
    }

    /**
     * set comment body
     * @param string $body
     */
    public function setBody($body) {
        return $this->setData('body', $body);
    }

    /**
     * get date posted
     * @return string
     */
    public function getDatePosted() {
        return $this->getData('datePosted');
    }

    /**
     * set date posted
     * @param string $datePosted
     */
    public function setDatePosted($datePosted) {
        return $this->setData('datePosted', $datePosted);
    }

    /**
     * get date modified
     * @return string
     */
    public function getDateModified() {
        return $this->getData('dateModified');
    }

    /**
     * set date modified
     * @param string $dateModified
     */
    public function setDateModified($dateModified) {
        return $this->setData('dateModified', $dateModified);
    }

    /**
     * get child comments (if fetched using recursive option)
     * @return array|null
     */
    public function getChildren() {
        return $this->getData('children');
    }

    /**
     * set child comments
     * @param array $children
     */
    public function setChildren($children) {
        $this->setData('children', $children);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleComment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArticleComment
 * @ingroup article
 * @see ArticleCommentDAO
 * @brief Class for ArticleComment.
 *
 * WIZDAM MODERNIZATION:
 * - PHP 8.x Compatibility (Constructors, Visibility)
 * - Strict SHIM Protocol
 * - Null Coalescing Operator
 */

/** Comment associative types. All types must be defined here. */
define('COMMENT_TYPE_PEER_REVIEW',    0x01);
define('COMMENT_TYPE_EDITOR_DECISION', 0x02);
define('COMMENT_TYPE_COPYEDIT',       0x03);
define('COMMENT_TYPE_LAYOUT',         0x04);
define('COMMENT_TYPE_PROOFREAD',      0x05);

class ArticleComment extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ArticleComment() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            // [CCTV] Gunakan get_class($this) agar menunjuk ke class PEMANGGIL
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ArticleComment(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get article comment id
     * Deprecated since 3.0.0. Use getId() instead.
     * @return int
     */
    public function getCommentId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getId();
    }

    /**
     * Set article comment id
     * Deprecated since 3.0.0. Use setId() instead.
     * @param int $commentId
     */
    public function setCommentId($commentId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setId($commentId);
    }

    /**
     * Get comment type
     * @return int
     */
    public function getCommentType() {
        return $this->getData('commentType');
    }

    /**
     * Set comment type
     * @param int $commentType
     */
    public function setCommentType($commentType) {
        return $this->setData('commentType', $commentType);
    }

    /**
     * Get role id
     * @return int
     */
    public function getRoleId() {
        return $this->getData('roleId');
    }

    /**
     * Set role id
     * @param int $roleId
     */
    public function setRoleId($roleId) {
        return $this->setData('roleId', $roleId);
    }

    /**
     * Get role name
     * @return string
     */
    public function getRoleName() {
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roleName = $roleDao->getRoleName($this->getData('roleId'));

        return $roleName;
    }

    /**
     * Get article id
     * @return int
     */
    public function getArticleId() {
        return $this->getData('articleId');
    }

    /**
     * Set article id
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        return $this->setData('articleId', $articleId);
    }

    /**
     * Get assoc id
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * Set assoc id
     * @param int $assocId
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get author id
     * @return int
     */
    public function getAuthorId() {
        return $this->getData('authorId');
    }

    /**
     * Set author id
     * @param int $authorId
     */
    public function setAuthorId($authorId) {
        return $this->setData('authorId', $authorId);
    }

    /**
     * Get author name
     * @return string
     */
    public function getAuthorName() {
        $authorFullName = $this->getData('authorFullName');

        if ($authorFullName === null) {
            $userDao = DAORegistry::getDAO('UserDAO');
            $authorFullName = $userDao->getUserFullName($this->getAuthorId(), true);
        }

        return $authorFullName ?? '';
    }

    /**
     * Get author email
     * @return string
     */
    public function getAuthorEmail() {
        $authorEmail = $this->getData('authorEmail');

        if ($authorEmail === null) {
            $userDao = DAORegistry::getDAO('UserDAO');
            $authorEmail = $userDao->getUserEmail($this->getAuthorId(), true);
        }

        return $authorEmail ?? '';
    }

    /**
     * Get comment title
     * @return string
     */
    public function getCommentTitle() {
        return $this->getData('commentTitle');
    }

    /**
     * Set comment title
     * @param string $commentTitle
     */
    public function setCommentTitle($commentTitle) {
        return $this->setData('commentTitle', $commentTitle);
    }

    /**
     * Get comments
     * @return string
     */
    public function getComments() {
        return $this->getData('comments');
    }

    /**
     * Set comments
     * @param string $comments
     */
    public function setComments($comments) {
        return $this->setData('comments', $comments);
    }

    /**
     * Get date posted
     * @return string
     */
    public function getDatePosted() {
        return $this->getData('datePosted');
    }

    /**
     * Set date posted
     * @param string $datePosted
     */
    public function setDatePosted($datePosted) {
        return $this->setData('datePosted', $datePosted);
    }

    /**
     * Get date modified
     * @return string
     */
    public function getDateModified() {
        return $this->getData('dateModified');
    }

    /**
     * Set date modified
     * @param string $dateModified
     */
    public function setDateModified($dateModified) {
        return $this->setData('dateModified', $dateModified);
    }

    /**
     * Get viewable
     * @return boolean
     */
    public function getViewable() {
        return $this->getData('viewable');
    }

    /**
     * Set viewable
     * @param boolean $viewable
     */
    public function setViewable($viewable) {
        return $this->setData('viewable', $viewable);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/note/PKPNote.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Note
 * @ingroup note
 * @see PKPNoteDAO
 * @brief Class for Note.
 */

class PKPNote extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPNote() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPNote(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * get user id of the note's author
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * set user id of the note's author
     * @param $userId int
     */
    public function setUserId($userId) {
        return $this->setData('userId', $userId);
    }

    /**
     * Return the user of the note's author.
     * @return User
     */
    public function getUser() {
        // Hapus '&'
        $userDao = DAORegistry::getDAO('UserDAO');
        return $userDao->getById($this->getUserId(), true);
    }

    /**
     * get date note was created
     * @return string (YYYY-MM-DD HH:MM:SS)
     */
    public function getDateCreated() {
        return $this->getData('dateCreated');
    }

    /**
     * set date note was created
     * @param $dateCreated string (YYYY-MM-DD HH:MM:SS)
     */
    public function setDateCreated($dateCreated) {
        return $this->setData('dateCreated', $dateCreated);
    }

    /**
     * get date note was modified
     * @return string (YYYY-MM-DD HH:MM:SS)
     */
    public function getDateModified() {
        return $this->getData('dateModified');
    }

    /**
     * set date note was modified
     * @param $dateModified string (YYYY-MM-DD HH:MM:SS)
     */
    public function setDateModified($dateModified) {
        return $this->setData('dateModified', $dateModified);
    }

    /**
     * get note contents
     * @return string
     */
    public function getContents() {
        return $this->getData('contents');
    }

    /**
     * set note contents
     * @param $contents string
     */
    public function setContents($contents) {
        return $this->setData('contents', $contents);
    }

    /**
     * get note title
     * @return string
     */
    public function getTitle() {
        return $this->getData('title');
    }

    /**
     * set note title
     * @param $title string
     */
    public function setTitle($title) {
        return $this->setData('title', $title);
    }

    /**
     * get note type
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * set note type
     * @param $assocType int
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * get note assoc id
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * set note assoc id
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * get file id
     * @return int
     */
    public function getFileId() {
        return $this->getData('fileId');
    }

    /**
     * set file id
     * @param $fileId int
     */
    public function setFileId($fileId) {
        return $this->setData('fileId', $fileId);
    }
}

?>
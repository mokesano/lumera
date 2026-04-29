<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/classes/BookForReviewAuthor.inc.php 
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookForReviewAuthor
 * @ingroup plugins_generic_booksForReview
 * @see BookForReviewAuthorDAO
 *
 * @brief Book for review author metadata class.
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

class BookForReviewAuthor extends DataObject {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->setId(0);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BookForReviewAuthor() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::BookForReviewAuthor(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the author's complete name.
     * Includes first name, middle name (if applicable), and last name.
     * @return string
     */
    public function getFullName() {
        return $this->getData('firstName') . ' ' . ($this->getData('middleName') != '' ? $this->getData('middleName') . ' ' : '') . $this->getData('lastName');
    }

    //
    // Get/set methods
    //

    /**
     * Get ID of author.
     * @return int
     */
    public function getId() {
        return $this->getData('authorId');
    }

    /**
     * Set ID of author.
     * @param $authorId int
     */
    public function setId($authorId) {
        return $this->setData('authorId', $authorId);
    }

    /**
     * Get ID of book.
     * @return int
     */
    public function getBookId() {
        return $this->getData('bookId');
    }

    /**
     * Set ID of book.
     * @param $bookId int
     */
    public function setBookId($bookId) {
        return $this->setData('bookId', $bookId);
    }

    /**
     * Get first name.
     * @return string
     */
    public function getFirstName() {
        return $this->getData('firstName');
    }

    /**
     * Set first name.
     * @param $firstName string
     */
    public function setFirstName($firstName) {
        return $this->setData('firstName', $firstName);
    }

    /**
     * Get middle name.
     * @return string
     */
    public function getMiddleName() {
        return $this->getData('middleName');
    }

    /**
     * Set middle name.
     * @param $middleName string
     */
    public function setMiddleName($middleName) {
        return $this->setData('middleName', $middleName);
    }

    /**
     * Get last name.
     * @return string
     */
    public function getLastName() {
        return $this->getData('lastName');
    }

    /**
     * Set last name.
     * @param $lastName string
     */
    public function setLastName($lastName) {
        return $this->setData('lastName', $lastName);
    }

    /**
     * Get sequence of author in book's author list.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence of author in book's author list.
     * @param $sequence float
     */
    public function setSequence($sequence) {
        return $this->setData('sequence', $sequence);
    }

}

?>
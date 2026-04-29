<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/classes/BookForReviewAuthorDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookForReviewAuthorDAO
 * @ingroup plugins_generic_booksForReview
 * @see BookForReviewAuthor
 *
 * @brief Operations for retrieving and modifying BookForReviewAuthor objects.
 * [WIZDAM EDITION] Modernized. PHP 8 Safe.
 */

class BookForReviewAuthorDAO extends DAO {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;

    /**
     * Constructor
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->parentPluginName = $parentPluginName;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BookForReviewAuthorDAO($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::BookForReviewAuthorDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName);
    }

    /**
     * Retrieve an author by ID.
     * @param $authorId int
     * @return BookForReviewAuthor|null
     */
    public function getAuthor($authorId) {
        $result = $this->retrieve(
            'SELECT * FROM books_for_review_authors WHERE author_id = ?', (int) $authorId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            // [MODERNISASI] Hapus referensi &
            $row = $result->GetRowAssoc(false);
            $returner = $this->_returnAuthorFromRow($row);
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve all authors for a book for review.
     * @param $bookId int
     * @return array BookForReviewAuthors ordered by sequence
     */
    public function getAuthorsByBookForReview($bookId) {
        $authors = array();

        $result = $this->retrieve(
            'SELECT * FROM books_for_review_authors WHERE book_id = ? ORDER BY seq',
            (int) $bookId
        );

        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $authors[] = $this->_returnAuthorFromRow($row);
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $authors;
    }

    /**
     * Retrieve the IDs of all authors for a book for review.
     * @param $bookId int
     * @return array int ordered by sequence
     */
    public function getAuthorIdsByBookForReview($bookId) {
        $authors = array();

        $result = $this->retrieve(
            'SELECT author_id FROM books_for_review_authors WHERE book_id = ? ORDER BY seq',
            (int) $bookId
        );

        while (!$result->EOF) {
            $authors[] = $result->fields[0];
            $result->moveNext();
        }

        $result->Close();
        unset($result);

        return $authors;
    }

    /**
     * Internal function to return a BookForReviewAuthor object from a row.
     * @param $row array
     * @return BookForReviewAuthor
     */
    public function _returnAuthorFromRow($row) {
        // [MODERNISASI] Hapus referensi & pada PluginRegistry
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReviewAuthor');

        $author = new BookForReviewAuthor();
        $author->setId($row['author_id']);
        $author->setBookId($row['book_id']);
        $author->setFirstName($row['first_name']);
        $author->setMiddleName($row['middle_name']);
        $author->setLastName($row['last_name']);
        $author->setSequence($row['seq']);

        // HookRegistry::call tetap membutuhkan array reference untuk objek yang dimodifikasi
        HookRegistry::call('BookForReviewAuthorDAO::_returnAuthorFromRow', array(&$author, &$row));

        return $author;
    }

    /**
     * Insert a new BookForReviewAuthor.
     * [MODERNISASI] Hapus referensi & pada parameter
     * @param $author BookForReviewAuthor
     * @return int
     */    
    public function insertAuthor($author) {
        $this->update(
            'INSERT INTO books_for_review_authors
                (book_id, first_name, middle_name, last_name, seq)
                VALUES
                (?, ?, ?, ?, ?)',
            array(
                (int) $author->getBookId(),
                $author->getFirstName(),
                $author->getMiddleName() . '', // make non-null
                $author->getLastName(),
                (float) $author->getSequence()
            )
        );

        $author->setId($this->getInsertAuthorId());
        return $author->getId();
    }

    /**
     * Update an existing BookForReviewAuthor.
     * @param $author BookForReviewAuthor
     */
    public function updateAuthor($author) {
        $returner = $this->update(
            'UPDATE books_for_review_authors
                SET
                    first_name = ?,
                    middle_name = ?,
                    last_name = ?,
                    seq = ?
                WHERE author_id = ?',
            array(
                $author->getFirstName(),
                $author->getMiddleName() . '', // make non-null
                $author->getLastName(),
                (float) $author->getSequence(),
                (int) $author->getId()
            )
        );
        return $returner;
    }

    /**
     * Delete an Author.
     * @param $author Author
     */
    public function deleteAuthor($author) {
        return $this->deleteAuthorById($author->getId());
    }

    /**
     * Delete an author by ID.
     * @param $authorId int
     * @param $bookId int optional
     */
    public function deleteAuthorById($authorId, $bookId = null) {
        $params = array((int) $authorId);
        if ($bookId) $params[] = (int) $bookId;
        return $this->update(
            'DELETE FROM books_for_review_authors WHERE author_id = ?' .
            ($bookId ? ' AND book_id = ?' : ''),
            $params
        );
    }

    /**
     * Delete authors by book for review.
     * @param $bookId int
     */
    public function deleteAuthorsByBookForReview($bookId) {
        $authors = $this->getAuthorsByBookForReview($bookId);
        foreach ($authors as $author) {
            $this->deleteAuthor($author);
        }
    }

    /**
     * Sequentially renumber a book for review's authors in their sequence order.
     * @param $bookId int
     */
    public function resequenceAuthors($bookId) {
        $result = $this->retrieve(
            'SELECT author_id FROM books_for_review_authors WHERE book_id = ? ORDER BY seq', (int) $bookId
        );

        for ($i=1; !$result->EOF; $i++) {
            list($authorId) = $result->fields;
            $this->update(
                'UPDATE books_for_review_authors SET seq = ? WHERE author_id = ?',
                array(
                    $i,
                    $authorId
                )
            );

            $result->moveNext();
        }

        $result->Close();
        unset($result);
    }

    /**
     * Get the ID of the last inserted author.
     * @return int
     */
    public function getInsertAuthorId() {
        return $this->getInsertId('books_for_review_authors', 'author_id');
    }
}

?>
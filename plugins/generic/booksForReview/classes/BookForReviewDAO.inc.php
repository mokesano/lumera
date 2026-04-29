<?php
declare(strict_types=1);

/**
 * @file plugins/generic/booksForReview/classes/BookForReviewDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BookForReviewDAO
 * @ingroup plugins_generic_booksForReview
 * @see BookForReview
 *
 * @brief Operations for retrieving and modifying BookForReview objects.
 * [WIZDAM EDITION] Modernized. PHP 8 Safe. No References.
 */

import('lib.pkp.classes.db.DAO');

/* These constants are used for user-selectable search fields. */
define('BFR_FIELD_PUBLISHER',    'publisher');
define('BFR_FIELD_YEAR',         'year');
define('BFR_FIELD_ISBN',         'isbn');
define('BFR_FIELD_TITLE',        'title');
define('BFR_FIELD_DESCRIPTION',  'description');
define('BFR_FIELD_NONE',         null);

class BookForReviewDAO extends DAO {
    
    /** @var string Name of parent plugin */
    public $parentPluginName;

    /** @var BookForReviewAuthorDAO */
    public $bookForReviewAuthorDao;

    /**
     * Constructor
     */
    public function __construct($parentPluginName) {
        parent::__construct();
        $this->parentPluginName = $parentPluginName;
        // [MODERNISASI] Hapus referensi &
        $this->bookForReviewAuthorDao = DAORegistry::getDAO('BookForReviewAuthorDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BookForReviewDAO($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::BookForReviewDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName);
    }

    /**
     * Get a list of field names for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array(
            'title',
            'description',
            'coverPageAltText',
            'originalFileName',
            'fileName',
            'width',
            'height'
        );
    }

    /**
     * Update the settings for this object
     * @param $book BookForReview
     */
    public function updateLocaleFields($book) {
        $this->updateDataObjectSettings('books_for_review_settings', $book, array(
            'book_id' => $book->getId()
        ));
    }
    
    /**
     * Retrieve a book for review by book ID.
     * @param $bookId int
     * @return BookForReview|null
     */
    public function getBookForReview($bookId) {
        $result = $this->retrieve(
            'SELECT * FROM books_for_review WHERE book_id = ?', (int) $bookId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);
            $returner = $this->_returnBookForReviewFromRow($row);
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve book for review journal ID by book ID.
     * @param $bookId int
     * @return int
     */
    public function getBookForReviewJournalId($bookId) {
        $result = $this->retrieve(
            'SELECT journal_id FROM books_for_review WHERE book_id = ?', (int) $bookId
        );

        return isset($result->fields[0]) ? (int) $result->fields[0] : 0;     
    }

    /**
     * Internal function to return a BookForReview object from a row.
     * @param $row array
     * @return BookForReview
     */
    public function _returnBookForReviewFromRow($row) {
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReview');

        $book = new BookForReview();
        $book->setId($row['book_id']);
        $book->setJournalId($row['journal_id']);
        $book->setStatus($row['status']);
        $book->setUserId($row['user_id']);
        $book->setEditorId($row['editor_id']);
        $book->setAuthorType($row['author_type']);
        $book->setPublisher($row['publisher']);
        $book->setUrl($row['url']);
        $book->setYear($row['year']);
        $book->setLanguage($row['language']);
        $book->setCopy($row['copy']);
        $book->setEdition($row['edition']);
        $book->setPages($row['pages']);
        $book->setISBN($row['isbn']);
        $book->setArticleId($row['article_id']);
        $book->setNotes($row['notes']);
        $book->setDateCreated($this->datetimeFromDB($row['date_created']));
        $book->setDateRequested($this->datetimeFromDB($row['date_requested']));
        $book->setDateAssigned($this->datetimeFromDB($row['date_assigned']));
        $book->setDateMailed($this->datetimeFromDB($row['date_mailed']));
        $book->setDateDue($this->datetimeFromDB($row['date_due']));
        $book->setDateSubmitted($this->datetimeFromDB($row['date_submitted']));

        $book->setAuthors($this->bookForReviewAuthorDao->getAuthorsByBookForReview($row['book_id']));

        $this->getDataObjectSettings('books_for_review_settings', 'book_id', $row['book_id'], $book);

        HookRegistry::call('BookForReviewDAO::_returnBookForReviewFromRow', array(&$book, &$row));

        return $book;
    }

    /**
     * Insert a new BookForReview.
     * @param $book BookForReview
     * @return int 
     */
    public function insertObject($book) {
        $this->update(
            sprintf('INSERT INTO books_for_review
                (journal_id,
                status,
                user_id,
                editor_id,
                author_type,
                publisher,
                url,
                year,
                language,
                copy,
                edition,
                pages,
                isbn,
                article_id,
                notes,
                date_created,
                date_requested,
                date_assigned,
                date_mailed,
                date_due,
                date_submitted)
                VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s, %s, %s, %s, %s)',
                $this->datetimeToDB($book->getDateCreated()),
                $this->datetimeToDB($book->getDateRequested()),
                $this->datetimeToDB($book->getDateAssigned()),
                $this->datetimeToDB($book->getDateMailed()),
                $this->datetimeToDB($book->getDateDue()),
                $this->datetimeToDB($book->getDateSubmitted())
            ),
            array(
                (int) $book->getJournalId(),
                (int) $book->getStatus(),
                $this->nullOrInt($book->getUserId()),
                $this->nullOrInt($book->getEditorId()),
                (int) $book->getAuthorType(),
                $book->getPublisher(),
                $book->getUrl(),
                $book->getYear(),
                $book->getLanguage(),
                $book->getCopy(),
                $this->nullOrInt($book->getEdition()),
                $this->nullOrInt($book->getPages()),
                $book->getISBN(),
                $this->nullOrInt($book->getArticleId()),
                $book->getNotes()
            )
        );
        
        $book->setId($this->getInsertBookForReviewId());
        $this->updateLocaleFields($book);

        // Insert authors for this book for review
        $authors = $book->getAuthors();
        for ($i=0, $count=count($authors); $i < $count; $i++) {
            $authors[$i]->setBookId($book->getId());
            $this->bookForReviewAuthorDao->insertAuthor($authors[$i]);
        }

        return $book->getId();
    }

    /**
     * Update an existing book for review.
     * @param $book BookForReview
     * @return boolean
     */
    public function updateObject($book) {
        $this->update(
            sprintf('UPDATE books_for_review
                SET
                    journal_id = ?,
                    status = ?,
                    user_id = ?,
                    editor_id = ?,
                    author_type = ?,
                    publisher = ?,
                    url = ?,
                    year = ?,
                    language = ?,
                    copy = ?,
                    edition = ?,
                    pages = ?,
                    isbn = ?,
                    article_id = ?,
                    notes = ?,
                    date_created = %s,
                    date_requested = %s,
                    date_assigned = %s,
                    date_mailed = %s,
                    date_due = %s,
                    date_submitted = %s
                WHERE book_id = ?',
                $this->datetimeToDB($book->getDateCreated()),
                $this->datetimeToDB($book->getDateRequested()),
                $this->datetimeToDB($book->getDateAssigned()),
                $this->datetimeToDB($book->getDateMailed()),
                $this->datetimeToDB($book->getDateDue()),
                $this->datetimeToDB($book->getDateSubmitted())
            ),
            array(
                (int) $book->getJournalId(),
                (int) $book->getStatus(),
                $this->nullOrInt($book->getUserId()),
                $this->nullOrInt($book->getEditorId()),
                (int) $book->getAuthorType(),
                $book->getPublisher(),
                $book->getUrl(),
                $book->getYear(),
                $book->getLanguage(),
                $book->getCopy(),
                $this->nullOrInt($book->getEdition()),
                $this->nullOrInt($book->getPages()),
                $book->getISBN(),
                $this->nullOrInt($book->getArticleId()),
                $book->getNotes(),
                (int) $book->getId()
            )
        );

        $this->updateLocaleFields($book);

        // Update authors for this book for review
        $authors = $book->getAuthors();
        for ($i=0, $count=count($authors); $i < $count; $i++) {
            if ($authors[$i]->getId() > 0) {
                $this->bookForReviewAuthorDao->updateAuthor($authors[$i]);
            } else {
                $this->bookForReviewAuthorDao->insertAuthor($authors[$i]);
            }
        }

        // Remove deleted authors
        $removedAuthors = $book->getRemovedAuthors();
        for ($i=0, $count=count($removedAuthors); $i < $count; $i++) {
            $this->bookForReviewAuthorDao->deleteAuthorById($removedAuthors[$i], $book->getId());
        }

        // Update author sequence numbers
        $this->bookForReviewAuthorDao->resequenceAuthors($book->getId());
        
        return true;
    }

    /**
     * Delete a book for review.
     * @param $book BookForReview
     */
    public function deleteObject($book) {
        $this->deleteBookForReviewById($book->getId());
    }

    /**
     * Delete a book for review by book ID.
     * @param $bookId int
     */
    public function deleteBookForReviewById($bookId) {
        $book = $this->getBookForReview($bookId);

        if ($book) {
            // Delete authors
            $this->bookForReviewAuthorDao->deleteAuthorsByBookForReview($bookId);

            // Delete cover image files (for all locales) from the filesystem
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            $locales = AppLocale::getSupportedLocales();
            
            // [MODERNISASI] Gunakan array_keys jika getSupportedLocales mengembalikan assoc array
            // Tapi di OJS 2 biasanya array('en_US', 'id_ID')
            foreach ($locales as $locale) {     
                $fileName = $book->getFileName($locale);
                if ($fileName) {
                    $publicFileManager->removeJournalFile($book->getJournalId(), $fileName);
                }
            }

            // Delete settings
            $this->update('DELETE FROM books_for_review_settings WHERE book_id = ?', (int) $bookId);

            // Delete book
            $this->update('DELETE FROM books_for_review WHERE book_id = ?', (int) $bookId);
        }
    }

    /**
     * Delete books for review by journal ID.
     * @param $journalId int
     */
    public function deleteBooksForReviewByJournal($journalId) {
        $books = $this->getBooksForReviewByJournalId($journalId);

        while (!$books->eof()) {
            $book = $books->next();
            $this->deleteBookForReviewById($book->getId());
        }
    }

    /**
     * Retrieve all books by review author for a particular journal.
     * @param $journalId int
     * @param $userId int, author to match
     * @param $rangeInfo object DBRangeInfo object describing range of results to return
     * @return DAOResultFactory containing matching BooksForReview
     */
    public function getBooksForReviewByAuthor($journalId, $userId, $rangeInfo = null) {
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReview');

        $sql = 'SELECT DISTINCT bfr.*
                FROM books_for_review bfr
                WHERE bfr.journal_id = ?
                AND bfr.user_id = ?
                ORDER BY bfr.book_id DESC';

        $paramArray = array(
            (int) $journalId,
            (int) $userId
        );

        $result = $this->retrieveRange($sql, $paramArray, $rangeInfo);
        $returner = new DAOResultFactory($result, $this, '_returnBookForReviewFromRow');
        return $returner;
    }

    /**
     * Retrieve all books assigned/mailed to an author for a particular journal.
     * @param $journalId int
     * @param $userId int, author to match
     * @param $rangeInfo object DBRangeInfo object describing range of results to return
     * @return DAOResultFactory containing matching BooksForReview 
     */
    public function getBooksForReviewAssignedByAuthor($journalId, $userId, $rangeInfo = null) {
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReview');

        $sql = 'SELECT DISTINCT bfr.*
                FROM books_for_review bfr
                WHERE (bfr.status = ? OR bfr.status = ?)
                AND bfr.journal_id = ?
                AND bfr.user_id = ?
                ORDER BY bfr.book_id DESC';

        $paramArray = array(
            BFR_STATUS_ASSIGNED,
            BFR_STATUS_MAILED,
            (int) $journalId,
            (int) $userId
        );

        $result = $this->retrieveRange($sql, $paramArray, $rangeInfo);
        $returner = new DAOResultFactory($result, $this, '_returnBookForReviewFromRow');
        return $returner;
    }

    /**
     * Retrieve all books assigned/mailed by date due for a particular journal.
     * @param $journalId int
     * @param $dateDue string 'YYYY-MM-DD'
     * @param $rangeInfo object DBRangeInfo object describing range of results to return
     * @return DAOResultFactory containing matching BooksForReview 
     */
    public function getBooksForReviewByDateDue($journalId, $dateDue, $rangeInfo = null) {
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReview');

        $sql = sprintf(
            'SELECT DISTINCT bfr.*
            FROM books_for_review bfr
            WHERE (bfr.status = ? OR bfr.status = ?)
            AND bfr.journal_id = ?
            AND DATE(bfr.date_due) = %s
            ORDER BY bfr.book_id',
            $this->dateToDB($dateDue));

        $paramArray = array(
            BFR_STATUS_ASSIGNED,
            BFR_STATUS_MAILED,
            (int) $journalId
        );

        $result = $this->retrieveRange($sql, $paramArray, $rangeInfo);
        $returner = new DAOResultFactory($result, $this, '_returnBookForReviewFromRow');
        return $returner;
    }

    /**
     * Retrieve all books for review matching a particular journal ID.
     */
    public function getBooksForReviewByJournalId($journalId, $searchType = null, $search = null, $searchMatch = null, $status = null, $userId = null, $editorId = null, $rangeInfo = null) {
        $sql = 'SELECT DISTINCT bfr.* FROM books_for_review bfr';
        $paramArray = array();

        // Join tables if necessary for search
        if ($searchType == BFR_FIELD_TITLE || $searchType == BFR_FIELD_DESCRIPTION) {
             $sql .= ', books_for_review_settings bfrs WHERE bfrs.book_id = bfr.book_id';
        } else {
             $sql .= ' WHERE 1=1';
        }

        switch ($searchType) {
            case BFR_FIELD_PUBLISHER:
                $sql .= ' AND LOWER(bfr.publisher) ' . ($searchMatch=='is'?'=':'LIKE') . ' LOWER(?)';
                $paramArray[] = $searchMatch == 'is' ? $search : "%$search%";
                break;
            case BFR_FIELD_YEAR:
                $sql .= ' AND bfr.year = ?';
                $paramArray[] = (int) $search;
                break;
            case BFR_FIELD_ISBN:
                $sql .= ' AND LOWER(bfr.isbn) ' . ($searchMatch=='is'?'=':'LIKE') . ' LOWER(?)';
                $paramArray[] = $searchMatch == 'is' ? $search : "%$search%";
                break;
            case BFR_FIELD_TITLE:
                $sql .= ' AND bfrs.setting_name = \'title\' AND LOWER(bfrs.setting_value) ' . ($searchMatch == 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $paramArray[] = $searchMatch == 'is' ? $search : "%$search%";
                break;
            case BFR_FIELD_DESCRIPTION:
                $sql .= ' AND bfrs.setting_name = \'description\' AND LOWER(bfrs.setting_value) ' . ($searchMatch == 'is' ? '=' : 'LIKE') . ' LOWER(?)';
                $paramArray[] = $searchMatch == 'is' ? $search : "%$search%";
                break;
        }

        if (!empty($status)) {
            $sql .= ' AND bfr.status = ?';
            $paramArray[] = (int) $status;
        }

        if (!empty($userId)) {
            $sql .= ' AND bfr.user_id = ?';
            $paramArray[] = (int) $userId;
        }

        if (!empty($editorId)) {
            $sql .= ' AND bfr.editor_id = ?';
            $paramArray[] = (int) $editorId;
        }

        $sql .= ' AND bfr.journal_id = ? ORDER BY bfr.book_id DESC';
        $paramArray[] = (int) $journalId;

        $result = $this->retrieveRange($sql, $paramArray, $rangeInfo);
        $returner = new DAOResultFactory($result, $this, '_returnBookForReviewFromRow');
        return $returner;
    }

    /**
     * Retrieve a submitted book for review for a journal by article ID.
     */
    public function getSubmittedBookForReviewByArticle($journalId, $articleId) {
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReview');

        $result = $this->retrieve(
            'SELECT *
            FROM books_for_review
            WHERE article_id = ?
            AND status = ?
            AND journal_id = ?',
            array(
                (int) $articleId,
                BFR_STATUS_SUBMITTED,
                (int) $journalId
            )
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);
            $returner = $this->_returnBookForReviewFromRow($row);
        }
        $result->Close();
        return $returner;
    }

    /**
     * Return a submitted book for review id for a given article and journal.
     */
    public function getSubmittedBookForReviewIdByArticle($journalId, $articleId) {
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReview');

        $result = $this->retrieve(
            'SELECT book_id 
                FROM books_for_review 
                WHERE article_id = ?
                AND status = ?
                AND journal_id = ?',
            array(
                (int) $articleId,
                BFR_STATUS_SUBMITTED,
                (int) $journalId
            )
        );

        $returner = isset($result->fields[0]) && $result->fields[0] != 0 ? (int) $result->fields[0] : null;

        $result->Close();
        return $returner;
    }

    /**
     * Retrieve status counts for a particular journal (and optionally user).
     */
    public function getBooksForReviewStatusCount($journalId, $status = null, $userId = null) {
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReview');

        $sql = 'SELECT COUNT(*)
                FROM books_for_review bfr
                WHERE bfr.journal_id = ?';
        $paramArray = array((int)$journalId);

        if ($status) {
            $sql .= ' AND bfr.status = ?';
            $paramArray[] = (int) $status;
        }

        if ($userId) {
            $sql .= ' AND bfr.user_id = ?';
            $paramArray[] = (int) $userId;
        }

        $result = $this->retrieve($sql, $paramArray);
        return isset($result->fields[0]) ? (int) $result->fields[0] : 0;     
    }

    /**
     * Retrieve all status counts for a particular journal (and optionally user).
     */
    public function getStatusCounts($journalId, $userId = null) {
        $bfrPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        $bfrPlugin->import('classes.BookForReview');
        $counts = array();

        $counts[BFR_STATUS_AVAILABLE] = $this->getBooksForReviewStatusCount($journalId, BFR_STATUS_AVAILABLE, $userId);
        $counts[BFR_STATUS_REQUESTED] = $this->getBooksForReviewStatusCount($journalId, BFR_STATUS_REQUESTED, $userId);
        $counts[BFR_STATUS_ASSIGNED] = $this->getBooksForReviewStatusCount($journalId, BFR_STATUS_ASSIGNED, $userId);
        $counts[BFR_STATUS_MAILED] = $this->getBooksForReviewStatusCount($journalId, BFR_STATUS_MAILED, $userId);
        $counts[BFR_STATUS_SUBMITTED] = $this->getBooksForReviewStatusCount($journalId, BFR_STATUS_SUBMITTED, $userId);

        return $counts;
    }

    /**
     * Remove the cover page image for the book for review
     */
    public function removeCoverPage($bookId, $locale) {
        $book = $this->getBookForReview($bookId);

        if ($book) {
            import('classes.file.PublicFileManager');
            $publicFileManager = new PublicFileManager();
            
            $fileName = $book->getFileName($locale);
            if ($fileName) {
                $publicFileManager->removeJournalFile($book->getJournalId(), $fileName);
            }

            $book->setFileName(null, $locale);
            $book->setWidth(null, $locale);
            $book->setHeight(null, $locale);
            $book->setOriginalFileName(null, $locale);
            $book->setCoverPageAltText(null, $locale);

            $this->updateObject($book);
        }
    }

    /**
     * Change the status of the book for review
     */
    public function changeBookForReviewStatus($bookId, $status) {
        $this->update(
            'UPDATE books_for_review SET status = ? WHERE book_id = ?', 
            array((int) $status, (int) $bookId)
        );
    }

    /**
     * Get the ID of the last inserted book for review.
     */
    public function getInsertBookForReviewId() {
        return $this->getInsertId('books_for_review', 'book_id');
    }
}

?>
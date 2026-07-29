<?php
/**
 * @file classes/submission/GenreDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class GenreDAO
 * @ingroup submission
 * @see Genre
 *
 * @brief Operations for retrieving and modifying Genre objects.
 */

import('lib.pkp.classes.submission.Genre');
import('lib.pkp.classes.db.DAO');

class GenreDAO extends DAO {
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Create a new Genre object.
     * @return Genre
     */
    public function newDataObject() {
        return new Genre();
    }

    /**
     * Insert a new Genre.
     * @param Genre $genre
     * @return int
     */
    public function insertObject($genre) {
        $this->update(
            sprintf(
                'INSERT INTO genres
                    (category, dependent, suppress, sort_order)
                VALUES
                    (?, ?, ?, ?)'
            ),
            [
                $genre->getCategory(),
                $genre->getDependent() ? 1 : 0,
                $genre->getSuppress() ? 1 : 0,
                $genre->getSortOrder()
            ]
        );
        $genreId = $this->getInsertId('genres', 'genre_id');
        $genre->setId($genreId);

        $this->updateLocaleFields($genre);
        return $genreId;
    }

    /**
     * Update an existing Genre.
     * @param Genre $genre
     */
    public function updateObject($genre) {
        $this->update(
            sprintf(
                'UPDATE genres
                SET
                    category = ?,
                    dependent = ?,
                    suppress = ?,
                    sort_order = ?
                WHERE genre_id = ?'
            ),
            [
                $genre->getCategory(),
                $genre->getDependent() ? 1 : 0,
                $genre->getSuppress() ? 1 : 0,
                $genre->getSortOrder(),
                (int) $genre->getId()
            ]
        );

        $this->updateLocaleFields($genre);
    }

    /**
     * Delete a Genre by ID.
     * @param int $genreId
     */
    public function deleteById($genreId) {
        $this->update('DELETE FROM genres WHERE genre_id = ?', [(int) $genreId]);
        $this->update('DELETE FROM genre_settings WHERE genre_id = ?', [(int) $genreId]);
    }

    /**
     * Retrieve a Genre by ID.
     * @param int $genreId
     * @return Genre|null
     */
    public function getById($genreId) {
        $result = $this->retrieve(
            'SELECT * FROM genres WHERE genre_id = ?',
            [(int) $genreId]
        );
        $row = $result ? $result->GetRowAssoc(false) : null;
        if ($result) {
            $result->Close();
        }
        return $row ? $this->_fromRow($row) : null;
    }

    /**
     * Retrieve genres by category.
     * @param string $category
     * @param int $rangeInfo
     * @return array
     */
    public function getByCategory($category, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM genres WHERE category = ? ORDER BY sort_order ASC',
            [$category],
            $rangeInfo
        );
        return $this->_returnFromResult($result);
    }

    /**
     * Retrieve all genres.
     * @param int $rangeInfo
     * @return array
     */
    public function getAll($rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM genres ORDER BY sort_order ASC',
            [],
            $rangeInfo
        );
        return $this->_returnFromResult($result);
    }

    /**
     * Retrieve genres by dependent flag.
     * @param bool $dependent
     * @param int $rangeInfo
     * @return array
     */
    public function getByDependent($dependent = false, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM genres WHERE dependent = ? ORDER BY sort_order ASC',
            [(int) $dependent],
            $rangeInfo
        );
        return $this->_returnFromResult($result);
    }

    /**
     * Get the mapping of genre categories to file implementations.
     * This is used by PKPSubmissionFileDAO to decide which delegate to use.
     * Override in subclass if needed.
     * @return array
     */
    public function getCategoryMapping() {
        return [
            'document' => 'submissionfile',
            'metadata' => 'submissionfile',
            'artwork'  => 'artworkfile',
            // Add more as needed.
        ];
    }

    /**
     * Internal helper to return a Genre object from a row.
     * @param array $row
     * @return Genre
     */
    protected function _fromRow($row) {
        $genre = $this->newDataObject();
        $genre->setId((int) $row['genre_id']);
        $genre->setCategory($row['category']);
        $genre->setDependent((bool) $row['dependent']);
        $genre->setSuppress((bool) $row['suppress']);
        $genre->setSortOrder((int) $row['sort_order']);

        $this->getDataObjectSettings('genre_settings', 'genre_id', $row['genre_id'], $genre);

        return $genre;
    }

    /**
     * Helper to return an array of genres from a DBResult.
     * @param mixed $result
     * @return array
     */
    protected function _returnFromResult($result) {
        $genres = [];
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $genres[] = $this->_fromRow($row);
            $result->MoveNext();
        }
        $result->Close();
        return $genres;
    }

    /**
     * Update the localized fields for a genre.
     * @param Genre $genre
     */
    protected function updateLocaleFields($genre) {
        $this->updateDataObjectSettings(
            'genre_settings',
            $genre,
            ['genre_id' => (int) $genre->getId()]
        );
    }

}
?>
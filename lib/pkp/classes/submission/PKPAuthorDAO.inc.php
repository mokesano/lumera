<?php
declare(strict_types=1);

/**
 * @file classes/submission/PKPAuthorDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPAuthorDAO
 * @ingroup submission
 * @see PKPAuthor
 *
 * @brief Operations for retrieving and modifying PKPAuthor objects.
 */

import('lib.pkp.classes.submission.PKPAuthor');

class PKPAuthorDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPAuthorDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPAuthorDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Retrieve an author by ID.
     * @param $authorId int
     * @param $submissionId int optional
     * @return Author
     */
    public function getAuthor($authorId, $submissionId = null) {
        $params = array((int) $authorId);
        if ($submissionId !== null) $params[] = (int) $submissionId;
        
        // Hapus '&'
        $result = $this->retrieve(
            'SELECT * FROM authors WHERE author_id = ?'
            . ($submissionId !== null?' AND submission_id = ?':''),
            $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            // Hapus '&'
            $returner = $this->_returnAuthorFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve all authors for a submission.
     * @param $submissionId int
     * @param $sortByAuthorId bool Use author Ids as indexes in the array
     * @return array Authors ordered by sequence
     */
    public function getAuthorsBySubmissionId($submissionId, $sortByAuthorId = false) {
        $authors = array();

        $result = $this->retrieve(
            'SELECT * FROM authors WHERE submission_id = ? ORDER BY seq',
            (int) $submissionId
        );

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            if ($sortByAuthorId) {
                $authorId = $row['author_id'];
                // Hapus '&'
                $authors[$authorId] = $this->_returnAuthorFromRow($row);
            } else {
                // Hapus '&'
                $authors[] = $this->_returnAuthorFromRow($row);
            }
            unset($row);
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $authors;
    }

    /**
     * Retrieve the number of authors assigned to a submission
     * @param $submissionId int
     * @return int
     */
    public function getAuthorCountBySubmissionId($submissionId) {
        $result = $this->retrieve(
            'SELECT count(*) FROM authors WHERE submission_id = ?',
            (int) $submissionId
        );

        $returner = $result->fields[0];

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Update the localized data for this object
     * @param $author object
     */
    public function updateLocaleFields($author) {
        $this->updateDataObjectSettings(
            'author_settings',
            $author,
            array(
                'author_id' => $author->getId()
            )
        );
    }
    
    /**
     * Get the localized country name for this author.
     * @return string
     */
    public function getCountryLocalized() {
        $countryDao = DAORegistry::getDAO('CountryDAO');
        $country = $this->getCountry(); // Mengambil kode negara (misal: 'ID') dari objek Author ini
        
        if ($country) {
            // Mengembalikan Nama Negara (misal: 'Indonesia')
            return $countryDao->getCountry($country);
        }
        return null;
    }

    /**
     * Internal function to return an Author object from a row.
     * @param $row array
     * @return Author
     */
    public function _returnAuthorFromRow($row) {
        $author = $this->newDataObject();
        $author->setId($row['author_id']);
        $author->setSubmissionId($row['submission_id']);
        $author->setFirstName($row['first_name']);
        $author->setMiddleName($row['middle_name']);
        $author->setLastName($row['last_name']);
        $author->setSuffix($row['suffix'] ?? null);
        $author->setCountry($row['country']);
        $author->setEmail($row['email']);
        $author->setUrl($row['url']);
        $author->setUserGroupId($row['user_group_id'] ?? null);
        $author->setPrimaryContact($row['primary_contact']);
        $author->setSequence($row['seq']);

        $this->getDataObjectSettings('author_settings', 'author_id', $row['author_id'], $author);

        // WIZDAM UPDATE: HookRegistry::dispatch
        HookRegistry::dispatch('AuthorDAO::_returnAuthorFromRow', array($author, &$row));
        
        return $author;
    }

    /**
     * Internal function to return an Author object from a row. Simplified
     * not to include object settings.
     * @param $row array
     * @return Author
     */
    public function _returnSimpleAuthorFromRow($row) {
        $author = $this->newDataObject();
        $author->setId($row['author_id']);
        $author->setSubmissionId($row['submission_id']);
        $author->setFirstName($row['first_name']);
        $author->setMiddleName($row['middle_name']);
        $author->setLastName($row['last_name']);
        $author->setSuffix($row['suffix'] ?? null);
        $author->setCountry($row['country']);
        $author->setEmail($row['email']);
        $author->setUrl($row['url']);
        $author->setUserGroupId($row['user_group_id'] ?? null);
        $author->setPrimaryContact($row['primary_contact']);
        $author->setSequence($row['seq']);
        $author->setAffiliation($row['affiliation_l'] ?? null, $row['locale'] ?? null);
        $author->setAffiliation($row['affiliation_pl'] ?? null, $row['primary_locale'] ?? null);

        HookRegistry::dispatch('AuthorDAO::_returnSimpleAuthorFromRow', array($author, &$row));
        
        return $author;
    }

    /**
     * Get a new data object
     * @return DataObject
     */
    public function newDataObject() {
        assert(false); // Should be overridden by child classes
    }

    /**
     * Get field names for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), array('biography', 'competingInterests', 'affiliation'));
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
     * @param $submissionId int optional
     */
    public function deleteAuthorById($authorId, $submissionId = null) {
        $params = array((int) $authorId);
        if ($submissionId) $params[] = (int) $submissionId;
        $returner = $this->update(
            'DELETE FROM authors WHERE author_id = ?' .
            ($submissionId?' AND submission_id = ?':''),
            $params
        );
        if ($returner) $this->update('DELETE FROM author_settings WHERE author_id = ?', array((int) $authorId));

        return $returner;
    }

    /**
     * Sequentially renumber a submission's authors in their sequence order.
     * @param $submissionId int
     */
    public function resequenceAuthors($submissionId) {
        $result = $this->retrieve(
            'SELECT author_id FROM authors WHERE submission_id = ? ORDER BY seq',
            (int) $submissionId
        );

        for ($i=1; !$result->EOF; $i++) {
            list($authorId) = $result->fields;
            $this->update(
                'UPDATE authors SET seq = ? WHERE author_id = ?',
                array(
                    $i,
                    $authorId
                )
            );

            $result->MoveNext();
        }

        $result->Close();
        unset($result);
    }

    /**
     * Retrieve the primary author for a submission.
     * @param $submissionId int
     * @return Author
     */
    public function getPrimaryContact($submissionId) {
        $result = $this->retrieve(
            'SELECT * FROM authors WHERE submission_id = ? AND primary_contact = 1',
            (int) $submissionId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnAuthorFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Remove other primary contacts from a submission and set to authorId
     * @param $authorId int
     * @param $submissionId int
     */
    public function resetPrimaryContact($authorId, $submissionId) {
        $this->update(
            'UPDATE authors SET primary_contact = 0 WHERE primary_contact = 1 AND submission_id = ?',
            (int) $submissionId
        );
        $this->update(
            'UPDATE authors SET primary_contact = 1 WHERE author_id = ? AND submission_id = ?',
            array((int) $authorId, (int) $submissionId)
        );
    }

    /**
     * Get the ID of the last inserted author.
     * @return int
     */
    public function getInsertAuthorId() {
        return $this->getInsertId('authors', 'author_id');
    }
}

?>
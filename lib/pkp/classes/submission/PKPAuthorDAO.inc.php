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
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function PKPAuthorDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Retrieve an author by ID.
     * @param int $authorId
     * @param int|null $submissionId optional
     * @return PKPAuthor|null
     */
    public function getAuthor($authorId, $submissionId = null) {
        $params = [(int) $authorId];
        if ($submissionId !== null) {
            $params[] = (int) $submissionId;
        }
        
        $result = $this->retrieve(
            'SELECT * FROM authors WHERE author_id = ?'
            . ($submissionId !== null ? ' AND submission_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnAuthorFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Retrieve all authors for a submission.
     * @param int $submissionId
     * @param bool $sortByAuthorId
     * @return array
     */
    public function getAuthorsBySubmissionId($submissionId, $sortByAuthorId = false) {
        $authors = [];

        $result = $this->retrieve(
            'SELECT * FROM authors WHERE submission_id = ? ORDER BY seq',
            [(int) $submissionId]
        );

        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                if ($sortByAuthorId) {
                    $authorId = (int) $row['author_id'];
                    $authors[$authorId] = $this->_returnAuthorFromRow($row);
                } else {
                    $authors[] = $this->_returnAuthorFromRow($row);
                }
                $result->MoveNext();
            }
            $result->Close();
        }

        return $authors;
    }

    /**
     * Retrieve the number of authors assigned to a submission.
     * @param int $submissionId
     * @return int
     */
    public function getAuthorCountBySubmissionId($submissionId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM authors WHERE submission_id = ?',
            [(int) $submissionId]
        );

        $returner = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = (int) ($row['count'] ?? 0);
        }
        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Update the localized data for this object.
     * @param PKPAuthor $author
     * @return void
     */
    public function updateLocaleFields($author) {
        $this->updateDataObjectSettings(
            'author_settings',
            $author,
            [
                'author_id' => (int) $author->getId()
            ]
        );
    }
    
    /**
     * Get the localized country name for this author.
     * Note: This method appears to be misplaced in the DAO. 
     * It is kept for backward compatibility but will return null 
     * unless overridden or called on an object that has getCountry().
     * @return string|null
     */
    public function getCountryLocalized() {
        if (method_exists($this, 'getCountry')) {
            $country = $this->getCountry();
            if ($country !== null && $country !== '') {
                /** @var CountryDAO $countryDao */
                $countryDao = DAORegistry::getDAO('CountryDAO');
                return $countryDao->getCountry((string) $country);
            }
        }
        return null;
    }

    /**
     * Internal function to return an Author object from a row.
     * @param array $row
     * @return PKPAuthor
     */
    public function _returnAuthorFromRow($row) {
        /** @var PKPAuthor $author */
        $author = $this->newDataObject();
        $author->setId((int) $row['author_id']);
        $author->setSubmissionId((int) $row['submission_id']);
        $author->setFirstName((string) $row['first_name']);
        $author->setMiddleName((string) $row['middle_name']);
        $author->setLastName((string) $row['last_name']);
        $author->setSuffix(isset($row['suffix']) ? (string) $row['suffix'] : null);
        $author->setCountry((string) $row['country']);
        $author->setEmail((string) $row['email']);
        $author->setUrl((string) $row['url']);
        $author->setUserGroupId(isset($row['user_group_id']) ? (int) $row['user_group_id'] : null);
        $author->setPrimaryContact((int) $row['primary_contact']);
        $author->setSequence((int) $row['seq']);

        $this->getDataObjectSettings('author_settings', 'author_id', (int) $row['author_id'], $author);

        // Note: HookRegistry dispatch with references is legacy but preserved for signature compatibility
        $tempAuthor = $author;
        $tempRow = $row;
        HookRegistry::dispatch('AuthorDAO::_returnAuthorFromRow', [&$tempAuthor, &$tempRow]);
        
        return $author;
    }

    /**
     * Internal function to return an Author object from a row. Simplified
     * not to include object settings.
     * @param array $row
     * @return PKPAuthor
     */
    public function _returnSimpleAuthorFromRow($row) {
        /** @var PKPAuthor $author */
        $author = $this->newDataObject();
        $author->setId((int) $row['author_id']);
        $author->setSubmissionId((int) $row['submission_id']);
        $author->setFirstName((string) $row['first_name']);
        $author->setMiddleName((string) $row['middle_name']);
        $author->setLastName((string) $row['last_name']);
        $author->setSuffix(isset($row['suffix']) ? (string) $row['suffix'] : null);
        $author->setCountry((string) $row['country']);
        $author->setEmail((string) $row['email']);
        $author->setUrl((string) $row['url']);
        $author->setUserGroupId(isset($row['user_group_id']) ? (int) $row['user_group_id'] : null);
        $author->setPrimaryContact((int) $row['primary_contact']);
        $author->setSequence((int) $row['seq']);
        
        $locale = isset($row['locale']) ? (string) $row['locale'] : null;
        $primaryLocale = isset($row['primary_locale']) ? (string) $row['primary_locale'] : null;
        
        if (isset($row['affiliation_l'])) {
            $author->setAffiliation((string) $row['affiliation_l'], $locale);
        }
        if (isset($row['affiliation_pl'])) {
            $author->setAffiliation((string) $row['affiliation_pl'], $primaryLocale);
        }
        if (isset($row['orcid'])) {
            $author->setData('orcid', (string) $row['orcid']);
        }

        $tempAuthor = $author;
        $tempRow = $row;
        HookRegistry::dispatch('AuthorDAO::_returnSimpleAuthorFromRow', [&$tempAuthor, &$tempRow]);
        
        return $author;
    }

    /**
     * Get a new data object.
     * @return DataObject
     */
    public function newDataObject() {
        assert(false); // Should be overridden by child classes
        return new DataObject();
    }

    /**
     * Get field names for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return array_merge(parent::getLocaleFieldNames(), ['biography', 'competingInterests', 'affiliation']);
    }

    /**
     * Delete an Author.
     * @param PKPAuthor $author
     * @return bool
     */
    public function deleteAuthor($author) {
        return $this->deleteAuthorById((int) $author->getId());
    }

    /**
     * Delete an author by ID.
     * @param int $authorId
     * @param int|null $submissionId optional
     * @return bool
     */
    public function deleteAuthorById($authorId, $submissionId = null) {
        $params = [(int) $authorId];
        $sql = 'DELETE FROM authors WHERE author_id = ?';
        
        if ($submissionId !== null) {
            $sql .= ' AND submission_id = ?';
            $params[] = (int) $submissionId;
        }
        
        $returner = (bool) $this->update($sql, $params);
        
        if ($returner) {
            $this->update('DELETE FROM author_settings WHERE author_id = ?', [(int) $authorId]);
        }

        return $returner;
    }

    /**
     * Sequentially renumber a submission's authors in their sequence order.
     * @param int $submissionId
     * @return void
     */
    public function resequenceAuthors($submissionId) {
        $result = $this->retrieve(
            'SELECT author_id FROM authors WHERE submission_id = ? ORDER BY seq',
            [(int) $submissionId]
        );

        if ($result) {
            for ($i = 1; !$result->EOF; $i++) {
                $row = $result->GetRowAssoc(false);
                $authorId = (int) $row['author_id'];
                $this->update(
                    'UPDATE authors SET seq = ? WHERE author_id = ?',
                    [$i, $authorId]
                );
                $result->MoveNext();
            }
            $result->Close();
        }
    }

    /**
     * Retrieve the primary author for a submission.
     * @param int $submissionId
     * @return PKPAuthor|null
     */
    public function getPrimaryContact($submissionId) {
        $result = $this->retrieve(
            'SELECT * FROM authors WHERE submission_id = ? AND primary_contact = 1',
            [(int) $submissionId]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnAuthorFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Remove other primary contacts from a submission and set to authorId.
     * @param int $authorId
     * @param int $submissionId
     * @return void
     */
    public function resetPrimaryContact($authorId, $submissionId) {
        $this->update(
            'UPDATE authors SET primary_contact = 0 WHERE primary_contact = 1 AND submission_id = ?',
            [(int) $submissionId]
        );
        $this->update(
            'UPDATE authors SET primary_contact = 1 WHERE author_id = ? AND submission_id = ?',
            [(int) $authorId, (int) $submissionId]
        );
    }

    /**
     * Get the ID of the last inserted author.
     * @return int
     */
    public function getInsertAuthorId() {
        return (int) $this->getInsertId('authors', 'author_id');
    }
    
}
?>
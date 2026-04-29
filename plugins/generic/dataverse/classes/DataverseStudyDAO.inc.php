<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/DataverseStudyDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataverseStudyDAO
 * @ingroup plugins_generic_dataverse
 *
 * @brief Operations for retrieving and modifying DataverseStudy objects.
 * [WIZDAM EDITION] Modernized for PHP 8.4, Strict Typing, and Null-Safety.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.dataverse.classes.DataverseStudy'); // [WIZDAM FIX] Import absolut

class DataverseStudyDAO extends DAO {
    
    /** @var string Name of parent plugin */
    public $_parentPluginName;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct(string $parentPluginName) {
        $this->_parentPluginName = $parentPluginName;
        parent::__construct();
    }       
    
    /**
     * [SHIM] Backward Compatibility
     * @param string $parentPluginName
     */
    public function DataverseStudyDAO(string $parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName);
    }

    /**
     * Retrieve study by study ID.
     * @param int $studyId
     * @return DataverseStudy|null
     */
    public function getStudy(int $studyId) {
        $result = $this->retrieve(
            'SELECT * FROM dataverse_studies WHERE study_id = ?', 
            [$studyId]
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnStudyFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }       

    /**
     * Get study by submission ID.
     * @param int $submissionId
     * @return DataverseStudy|null
     */
    public function getStudyBySubmissionId(int $submissionId) {
        $result = $this->retrieve(
            'SELECT * FROM dataverse_studies WHERE submission_id = ?', 
            [$submissionId]
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnStudyFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }       
    
    /**
     * Insert a new study.
     * @param DataverseStudy $study
     * @return int 
     */
    public function insertStudy($study): int {
        $this->update(
            'INSERT INTO dataverse_studies
                (submission_id, edit_uri, edit_media_uri, statement_uri, persistent_uri, data_citation)
                VALUES
                (?, ?, ?, ?, ?, ?)',
            [
                (int) $study->getSubmissionId(),
                (string) $study->getEditUri(),
                (string) $study->getEditMediaUri(),
                (string) $study->getStatementUri(),
                (string) $study->getPersistentUri(),
                (string) $study->getDataCitation()
            ]
        );
        $insertId = $this->getInsertStudyId();
        $study->setId($insertId);
        return $insertId;
    }
    
    /**
     * Update an existing study.
     * @param DataverseStudy $study
     * @return boolean
     */
    public function updateStudy($study): bool {
        return $this->update(
            'UPDATE dataverse_studies
                SET
                    edit_uri = ?,
                    edit_media_uri = ?,
                    statement_uri = ?,
                    persistent_uri = ?,
                    data_citation = ?
                WHERE study_id = ?',
            [
                (string) $study->getEditUri(),
                (string) $study->getEditMediaUri(),
                (string) $study->getStatementUri(),
                (string) $study->getPersistentUri(),
                (string) $study->getDataCitation(),
                (int) $study->getId()
            ]
        );
    }       
    
    /**
     * Get ID of last inserted study
     * @return int
     */
    public function getInsertStudyId(): int {
        return (int) $this->getInsertId('dataverse_studies', 'study_id');
    }       
    
    /**
     * Delete Dataverse study.
     * @param DataverseStudy $study
     * @return boolean
     */
    public function deleteStudy($study): bool {
        return $this->deleteStudyById((int) $study->getId());
    }

    /**
     * Delete Dataverse study by ID.
     * @param int $studyId
     * @return boolean
     */
    public function deleteStudyById(int $studyId): bool {
        // [WIZDAM FIX] Menambahkan 'return' yang tertinggal di kode asli
        return $this->update(
            'DELETE FROM dataverse_studies WHERE study_id = ?', 
            [$studyId]
        );
    }
    
    /**
     * Internal function to return DataverseStudy object from a row.
     * @param array $row
     * @return DataverseStudy
     */
    public function _returnStudyFromRow(array $row) {
        // [WIZDAM FIX] Tidak memanggil PluginRegistry untuk mencegah Null Pointer
        $study = new DataverseStudy();
        $study->setId((int) $row['study_id']);
        $study->setSubmissionId((int) $row['submission_id']);
        $study->setEditUri((string) $row['edit_uri']);
        $study->setEditMediaUri((string) $row['edit_media_uri']);        
        $study->setStatementUri((string) $row['statement_uri']);
        $study->setPersistentUri((string) $row['persistent_uri']);
        $study->setDataCitation((string) $row['data_citation']);
        
        return $study;
    }       
}
?>
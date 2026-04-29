<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/DataverseFileDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataverseFileDAO
 * @ingroup plugins_generic_dataverse
 *
 * @brief Operations for retrieving and modifying DataverseFile objects.
 * [WIZDAM EDITION] Modernized for PHP 8.4 Strict Typing and Null-Safety.
 */

import('lib.pkp.classes.db.DAO');
import('plugins.generic.dataverse.classes.DataverseFile'); // [WIZDAM FIX] Import absolut untuk mencegah Null Pointer

class DataverseFileDAO extends DAO {
    
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
    public function DataverseFileDAO(string $parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName);
    }
    
    /**
     * Insert a new Dataverse file.
     * @param DataverseFile $dvFile
     * @return int 
     */
    public function insertDataverseFile($dvFile): int {
        $this->update(
            'INSERT INTO dataverse_files
                (supp_id, submission_id, study_id, content_source_uri)
                VALUES
                (?, ?, ?, ?)',
            [
                (int) $dvFile->getSuppFileId(),
                (int) $dvFile->getSubmissionId(),
                $dvFile->getStudyId() ? (int) $dvFile->getStudyId() : 0,
                $dvFile->getContentSourceUri() ? (string) $dvFile->getContentSourceUri() : ''
            ]
        );
        $insertId = $this->getInsertDataverseFileId();
        $dvFile->setId($insertId);
        return $insertId;
    }
    
    /**
     * Update Dataverse file.
     * @param DataverseFile $dvFile
     * @return boolean 
     */
    public function updateDataverseFile($dvFile): bool {
        return $this->update(
            'UPDATE dataverse_files
                SET
                supp_id = ?,
                study_id = ?,
                submission_id = ?,
                content_source_uri = ?
                WHERE dvfile_id = ?',
            [
                (int) $dvFile->getSuppFileId(),
                (int) $dvFile->getStudyId(),
                (int) $dvFile->getSubmissionId(),
                (string) $dvFile->getContentSourceUri(),
                (int) $dvFile->getId()
            ]
        );
    }       
    
    /**
     * Get ID of the last inserted Dataverse file.
     * @return int
     */
    public function getInsertDataverseFileId(): int {
        return (int) $this->getInsertId('dataverse_files', 'dvfile_id');
    }       
    
    /**
     * Delete a Dataverse file.
     * @param DataverseFile $dvFile
     * @return boolean
     */
    public function deleteDataverseFile($dvFile): bool {
        return $this->deleteDataverseFileById((int) $dvFile->getId());
    }

    /**
     * Delete a Dataverse file by ID.
     * @param int $dvFileId
     * @param int|null $submissionId optional
     * @return boolean
     */
    public function deleteDataverseFileById(int $dvFileId, ?int $submissionId = null): bool {
        if ($submissionId !== null) {
            return $this->update(
                'DELETE FROM dataverse_files WHERE dvfile_id = ? AND submission_id = ?', 
                [$dvFileId, $submissionId]
            );
        }
        return $this->update('DELETE FROM dataverse_files WHERE dvfile_id = ?', [$dvFileId]);
    }
    
    /**
     * Delete Dataverse files associated with a study.
     * @param int $studyId
     */
    public function deleteDataverseFilesByStudyId(int $studyId): void {
        $dvFiles = $this->getDataverseFilesByStudyId($studyId);
        foreach ($dvFiles as $dvFile) {
            $this->deleteDataverseFile($dvFile);
        }
    }
    
    /**
     * Retrieve Dataverse file by supp id & optional submission.
     * @param int $suppFileId
     * @param int|null $submissionId
     * @return DataverseFile|null
     */
    public function getDataverseFileBySuppFileId(int $suppFileId, ?int $submissionId = null) {
        $params = [$suppFileId];
        $sql = 'SELECT * FROM dataverse_files WHERE supp_id = ?';
        
        if ($submissionId !== null) {
            $params[] = $submissionId;
            $sql .= ' AND submission_id = ?';
        }
        
        $result = $this->retrieve($sql, $params);
        $returner = null;
        
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnDataverseFileFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;          
    }
    
    /**
     * Retrieve Dataverse files for a submission.
     * @param int $submissionId
     * @return array
     */
    public function getDataverseFilesBySubmissionId(int $submissionId): array {
        $dvFiles = [];
        $result = $this->retrieve(
            'SELECT * FROM dataverse_files WHERE submission_id = ?',
            [$submissionId]
        );
        while (!$result->EOF) {
            $dvFiles[] = $this->_returnDataverseFileFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }
        $result->Close();
        return $dvFiles;
    }       
    
    /**
     * Retrieve Dataverse files for a study.
     * @param int $studyId
     * @return array
     */
    public function getDataverseFilesByStudyId(int $studyId): array {
        $dvFiles = [];
        $result = $this->retrieve(
            'SELECT * FROM dataverse_files WHERE study_id = ?',
            [$studyId]
        );
        while (!$result->EOF) {
            $dvFiles[] = $this->_returnDataverseFileFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }
        $result->Close();
        return $dvFiles;
    }
    
    /**
     * Internal function to return DataverseFile object from a row.
     * @param array $row
     * @return DataverseFile
     */
    public function _returnDataverseFileFromRow(array $row) {
        // [WIZDAM FIX] Tidak lagi memanggil PluginRegistry untuk import, melainkan langsung instansiasi
        $dvFile = new DataverseFile();
        $dvFile->setId((int) $row['dvfile_id']);        
        $dvFile->setSuppFileId((int) $row['supp_id']);                
        $dvFile->setStudyId((int) $row['study_id']);
        $dvFile->setSubmissionId((int) $row['submission_id']);
        $dvFile->setContentSourceUri((string) $row['content_source_uri']);
        return $dvFile;
    }          
    
    /**
     * Update the Dataverse deposit status of a supplementary file.
     * @param int $suppFileId
     * @param boolean $depositStatus
     */
    public function setDepositStatus(int $suppFileId, bool $depositStatus): void {
        $idFields = ['supp_id', 'locale', 'setting_name'];
        $updateArray = [
            'supp_id' => $suppFileId,
            'locale' => '',
            'setting_name' => 'dataverseDeposit',
            'setting_type' => 'bool',
            'setting_value' => $depositStatus ? '1' : '0' // [WIZDAM FIX] Database setting butuh string '1' atau '0'
        ];
        $this->replace('article_supp_file_settings', $updateArray, $idFields);
    }       

    /** 
     * Set content source URI of Dataverse file.
     * @param int $suppFileId
     * @param string $contentSourceUri
     */
    public function setContentSourceUri(int $suppFileId, string $contentSourceUri): void {
        $idFields = ['supp_id', 'locale', 'setting_name'];
        $updateArray = [
            'supp_id' => $suppFileId,
            'locale' => '',
            'setting_name' => 'dataverseContentSourceUri',
            'setting_type' => 'string',
            'setting_value' => $contentSourceUri
        ];
        $this->replace('article_supp_file_settings', $updateArray, $idFields);
    }
}
?>
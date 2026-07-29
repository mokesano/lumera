<?php
declare(strict_types=1);

/**
 * @file classes/submission/PKPSubmissionFileDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPSubmissionFileDAO
 * @ingroup submission
 * @see SubmissionFile
 * @see SubmissionFileDAODelegate
 *
 * @brief Abstract base class for retrieving and modifying SubmissionFile
 * objects and their descendants (e.g., MonographFile, ArtworkFile).
 *
 * This class provides access to all SubmissionFile implementations. It
 * instantiates and uses delegates internally to provide the right database
 * access behaviour depending on the type of the accessed file.
 */

import('lib.pkp.classes.file.PKPFileDAO');

class PKPSubmissionFileDAO extends PKPFileDAO {
    
    /** @var array A private list of delegates that provide operations for different SubmissionFile implementations. */
    protected $_delegates = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function PKPSubmissionFileDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Public methods
    //
    
    /**
     * Retrieve a specific revision of a file.
     * @param int $fileId
     * @param int $revision
     * @param int|null $fileStage
     * @param int|null $submissionId
     * @return SubmissionFile|null
     */
    public function getRevision($fileId, $revision, $fileStage = null, $submissionId = null) {
        if ($fileId === null || $fileId === '' || $revision === null || $revision === '') {
            return null;
        }
        $revisions = $this->_getInternally($submissionId, $fileStage, (int) $fileId, (int) $revision);
        return $this->_checkAndReturnRevision($revisions);
    }

    /**
     * Retrieve the latest revision of a file.
     * @param int $fileId
     * @param int|null $fileStage
     * @param int|null $submissionId
     * @return SubmissionFile|null
     */
    public function getLatestRevision($fileId, $fileStage = null, $submissionId = null) {
        if ($fileId === null || $fileId === '') {
            return null;
        }
        $revisions = $this->_getInternally($submissionId, $fileStage, (int) $fileId, null, null, null, null, null, null, null, null, true);
        return $this->_checkAndReturnRevision($revisions);
    }

    /**
     * Retrieve a list of current revisions.
     * @param int $submissionId
     * @param int|null $fileStage
     * @param mixed $rangeInfo
     * @return array|null
     */
    public function getLatestRevisions($submissionId, $fileStage = null, $rangeInfo = null) {
        if ($submissionId === null || $submissionId === '') {
            return null;
        }
        return $this->_getInternally((int) $submissionId, $fileStage, null, null, null, null, null, null, null, null, null, true, $rangeInfo);
    }

    /**
     * Retrieve all revisions of a submission file.
     * @param int $fileId
     * @param int|null $fileStage
     * @param int|null $submissionId
     * @param mixed $rangeInfo
     * @return array|null
     */
    public function getAllRevisions($fileId, $fileStage = null, $submissionId = null, $rangeInfo = null) {
        if ($fileId === null || $fileId === '') {
            return null;
        }
        return $this->_getInternally($submissionId, $fileStage, (int) $fileId, null, null, null, null, null, null, null, null, false, $rangeInfo);
    }

    /**
     * Retrieve the latest revision of all files associated to a certain object.
     * @param int $assocType
     * @param int $assocId
     * @param int|null $submissionId
     * @param int|null $fileStage
     * @param mixed $rangeInfo
     * @return array|null
     */
    public function getLatestRevisionsByAssocId($assocType, $assocId, $submissionId = null, $fileStage = null, $rangeInfo = null) {
        if ($assocType === null || $assocType === '' || $assocId === null || $assocId === '') {
            return null;
        }
        return $this->_getInternally($submissionId, $fileStage, null, null, (int) $assocType, (int) $assocId, null, null, null, null, null, true, $rangeInfo);
    }

    /**
     * Retrieve all files associated to a certain object.
     * @param int $assocType
     * @param int $assocId
     * @param int|null $fileStage
     * @param mixed $rangeInfo
     * @return array|null
     */
    public function getAllRevisionsByAssocId($assocType, $assocId, $fileStage = null, $rangeInfo = null) {
        if ($assocType === null || $assocType === '' || $assocId === null || $assocId === '') {
            return null;
        }
        return $this->_getInternally(null, $fileStage, null, null, (int) $assocType, (int) $assocId, null, null, null, null, null, false, $rangeInfo);
    }

    /**
     * Get all file revisions assigned to the given review round.
     * @param int $submissionId
     * @param int $stageId
     * @param int $round
     * @param int|null $fileStage
     * @param int|null $uploaderUserId
     * @param int|null $uploaderUserGroupId
     * @return array|null
     */
    public function getRevisionsByReviewRound($submissionId, $stageId, $round, $fileStage = null, $uploaderUserId = null, $uploaderUserGroupId = null) {
        if ($stageId === null || $stageId === '' || $round === null || $round === '') {
            return null;
        }
        return $this->_getInternally($submissionId, $fileStage, null, null, null, null, (int) $stageId, $uploaderUserId, $uploaderUserGroupId, (int) $round);
    }

    /**
     * Get all files that are in the current review round, but have later revisions.
     * @param int $submissionId
     * @param int $stageId
     * @param int $round
     * @param int|null $fileStage
     * @return array
     */
    public function getLatestNewRevisionsByReviewRound($submissionId, $stageId, $round, $fileStage = null) {
        if ($stageId === null || $stageId === '' || $round === null || $round === '') {
            return [];
        }
        return $this->_getInternally($submissionId, $fileStage, null, null, null, null, (int) $stageId, null, null, (int) $round, null, true);
    }

    /**
     * Retrieve the current revision number for a file.
     * @param int $fileId
     * @return int|null
     */
    public function getLatestRevisionNumber($fileId) {
        if ($fileId === null || $fileId === '') {
            return null;
        }

        $result = $this->retrieve(
            'SELECT MAX(revision) AS max_revision FROM '.$this->getSubmissionEntityName().'_files WHERE file_id = ?',
            [(int) $fileId]
        );
        
        $latestRevision = null;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $latestRevision = (int) ($row['max_revision'] ?? 0);
        }
        if ($result) {
            $result->Close();
        }

        return $latestRevision > 0 ? $latestRevision : null;
    }

    /**
     * Insert a new SubmissionFile.
     * @param SubmissionFile $submissionFile
     * @param string $sourceFile
     * @param bool $isUpload
     * @return SubmissionFile|null
     */
    public function insertObject($submissionFile, $sourceFile, $isUpload = false) {
        /** @var SubmissionFile $submissionFile */
        $submissionFile = $this->_castToGenre($submissionFile);

        $targetImplementation = strtolower($this->_getFileImplementationForGenreId($submissionFile->getGenreId()));
        $targetDaoDelegate = $this->_getDaoDelegate($targetImplementation);
        
        if ($targetDaoDelegate === null) {
            return null;
        }
        
        $insertedFile = $targetDaoDelegate->insertObject($submissionFile, $sourceFile, (bool) $isUpload);

        if (strtolower(get_class($insertedFile)) !== $targetImplementation) {
            $insertedFile = $this->_castToDatabase($insertedFile);
        }
        return $insertedFile;
    }

    /**
     * Update an existing submission file.
     * @param SubmissionFile $updatedFile
     * @param int|null $previousFileId
     * @param int|null $previousRevision
     * @return SubmissionFile|null
     */
    public function updateObject($updatedFile, $previousFileId = null, $previousRevision = null) {
        // [WIZDAM FIX] Type narrowing for linter to recognize SubmissionFile methods
        /** @var SubmissionFile $updatedFile */
        $updatedFile = $this->_castToGenre($updatedFile);

        $previousFileId = $previousFileId !== null ? (int) $previousFileId : (int) $updatedFile->getFileId();
        $previousRevision = $previousRevision !== null ? (int) $previousRevision : (int) $updatedFile->getRevision();

        $previousFile = $this->getRevision($previousFileId, $previousRevision);
        if ($previousFile === null) {
            return null;
        }

        $previousImplementation = strtolower(get_class($previousFile));
        
        // [WIZDAM FIX] Type narrowing for linter to recognize SubmissionFile methods
        /** @var SubmissionFile $updatedFile */
        $targetImplementation = strtolower($this->_getFileImplementationForGenreId($updatedFile->getGenreId()));
        $targetDaoDelegate = $this->_getDaoDelegate($targetImplementation);

        if ($targetDaoDelegate === null) {
            return null;
        }

        if ($previousImplementation !== $targetImplementation) {
            $previousFilePath = $previousFile->getFilePath();
            $targetFilePath = $updatedFile->getFilePath();
            
            import('lib.pkp.classes.file.FileManager');
            $fileManager = new FileManager();
            $fileManager->copyFile($previousFilePath, $targetFilePath);

            $sourceDaoDelegate = $this->_getDaoDelegate($previousImplementation);
            if ($sourceDaoDelegate !== null) {
                $sourceDaoDelegate->deleteObject($previousFile);
            }
            $targetDaoDelegate->insertObject($updatedFile, $targetFilePath);
        } else {
            if (!$targetDaoDelegate->updateObject($updatedFile, $previousFile)) {
                return null;
            }
        }

        if (strtolower(get_class($updatedFile)) !== $targetImplementation) {
            $updatedFile = $this->_castToDatabase($updatedFile);
        }

        return $updatedFile;
    }

    /**
     * Set the latest revision of a file as the latest revision of another file.
     * @param int $revisedFileId
     * @param int $newFileId
     * @param int $submissionId
     * @param int $fileStage
     * @return SubmissionFile|null
     */
    public function setAsLatestRevision($revisedFileId, $newFileId, $submissionId, $fileStage) {
        $revisedFileId = (int) $revisedFileId;
        $newFileId = (int) $newFileId;
        $submissionId = (int) $submissionId;
        $fileStage = (int) $fileStage;

        if ($revisedFileId === $newFileId) {
            return null;
        }

        $revisedFile = $this->getLatestRevision($revisedFileId, $fileStage, $submissionId);
        $newFile = $this->getLatestRevision($newFileId, $fileStage, $submissionId);
        
        if ($revisedFile === null || $newFile === null) {
            return null;
        }

        $previousFileId = $newFile->getFileId();
        $previousRevision = $newFile->getRevision();

        $newFile->setFileId($revisedFileId);
        $newFile->setRevision((int) $revisedFile->getRevision() + 1);
        $newFile->setGenreId($revisedFile->getGenreId());
        $newFile->setAssocType($revisedFile->getAssocType());
        $newFile->setAssocId($revisedFile->getAssocId());

        return $this->updateObject($newFile, $previousFileId, $previousRevision);
    }

    /**
     * Assign file to a review round.
     * @param int $fileId
     * @param int $revision
     * @param int $stageId
     * @param int $reviewRoundId
     * @param int $submissionId
     * @return bool
     */
    public function assignRevisionToReviewRound($fileId, $revision, $stageId, $reviewRoundId, $submissionId) {
        if (!is_numeric($fileId) || !is_numeric($revision)) {
            return false;
        }
        return (bool) $this->update(
            'INSERT INTO review_round_files ('.$this->getSubmissionEntityName().'_id, stage_id, review_round_id, file_id, revision) VALUES (?, ?, ?, ?, ?)',
            [(int) $submissionId, (int) $stageId, (int) $reviewRoundId, (int) $fileId, (int) $revision]
        );
    }

    /**
     * Delete a specific revision of a submission file.
     * @param SubmissionFile $submissionFile
     * @return int
     */
    public function deleteRevision($submissionFile) {
        return $this->deleteRevisionById(
            (int) $submissionFile->getFileId(), 
            (int) $submissionFile->getRevision(), 
            (int) $submissionFile->getFileStage(), 
            (int) $submissionFile->getSubmissionId()
        );
    }

    /**
     * Delete a specific revision of a submission file by id.
     * @param int $fileId
     * @param int $revision
     * @param int|null $fileStage
     * @param int|null $submissionId
     * @return int
     */
    public function deleteRevisionById($fileId, $revision, $fileStage = null, $submissionId = null) {
        return $this->_deleteInternally($submissionId, $fileStage, (int) $fileId, (int) $revision);
    }

    /**
     * Delete the latest revision of a submission file by id.
     * @param int $fileId
     * @param int|null $fileStage
     * @param int|null $submissionId
     * @return int
     */
    public function deleteLatestRevisionById($fileId, $fileStage = null, $submissionId = null) {
        return $this->_deleteInternally($submissionId, $fileStage, (int) $fileId, null, null, null, null, null, null, null, true);
    }

    /**
     * Delete all revisions of a file, optionally restricted to a given file stage.
     * @param int $fileId
     * @param int|null $fileStage
     * @param int|null $submissionId
     * @return int
     */
    public function deleteAllRevisionsById($fileId, $fileStage = null, $submissionId = null) {
        return $this->_deleteInternally($submissionId, $fileStage, (int) $fileId);
    }

    /**
     * Delete all revisions of all files of a submission.
     * @param int $submissionId
     * @param int|null $fileStage
     * @return int
     */
    public function deleteAllRevisionsBySubmissionId($submissionId, $fileStage = null) {
        return $this->_deleteInternally((int) $submissionId, $fileStage);
    }

    /**
     * Delete all revisions associated to a certain object.
     * @param int $assocType
     * @param int $assocId
     * @param int|null $fileStage
     * @return int
     */
    public function deleteAllRevisionsByAssocId($assocType, $assocId, $fileStage = null) {
        return $this->_deleteInternally(null, $fileStage, null, null, (int) $assocType, (int) $assocId);
    }

    /**
     * Remove all file assignments for the given review round.
     * @param int $submissionId
     * @param int $stageId
     * @param int $reviewRoundId
     * @return bool
     */
    public function deleteAllRevisionsByReviewRound($submissionId, $stageId, $reviewRoundId) {
        return (bool) $this->update(
            'DELETE FROM review_round_files WHERE '.$this->getSubmissionEntityName().'_id = ? AND stage_id = ? AND review_round_id = ?',
            [(int) $submissionId, (int) $stageId, (int) $reviewRoundId]
        );
    }

    /**
     * Remove a specific file assignment from a review round.
     * @param int $submissionId
     * @param int $stageId
     * @param int $fileId
     * @param int $revision
     * @return bool
     */
    public function deleteReviewRoundAssignment($submissionId, $stageId, $fileId, $revision) {
        return (bool) $this->update(
            'DELETE FROM review_round_files WHERE '.$this->getSubmissionEntityName().'_id = ? AND stage_id = ? AND file_id = ? AND revision = ?',
            [(int) $submissionId, (int) $stageId, (int) $fileId, (int) $revision]
        );
    }

    /**
     * Transfer the ownership of the submission files of one user to another.
     * @param int $oldUserId
     * @param int $newUserId
     * @return void
     */
    public function transferOwnership($oldUserId, $newUserId) {
        $submissionFiles = $this->_getInternally(null, null, null, null, null, null, null, (int) $oldUserId, null, null);
        foreach ($submissionFiles as $file) {
            $daoDelegate = $this->_getDaoDelegateForObject($file);
            if ($daoDelegate !== null) {
                $file->setUploaderUserId((int) $newUserId);
                $daoDelegate->updateObject($file, $file);
            }
        }
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @param int $genreId
     * @return SubmissionFile|null
     */
    public function newDataObjectByGenreId($genreId) {
        $daoDelegate = $this->_getDaoDelegateForGenreId((int) $genreId);
        if ($daoDelegate !== null) {
            return $daoDelegate->newDataObject();
        }
        return null;
    }

    //
    // Abstract template methods to be implemented by subclasses.
    //
    /**
     * Return the name of the base submission entity (i.e. 'monograph', 'paper', 'article', etc.)
     * @return string
     */
    public function getSubmissionEntityName() {
        assert(false);
    }

    /**
     * Return the available delegates mapped by lower case class names.
     * @return array
     */
    public function getDelegateClassNames() {
        assert(false);
    }

    /**
     * Return the mapping of genre categories to the lower case class name of file implementation.
     * @return array
     */
    public function getGenreCategoryMapping() {
        assert(false);
    }

    /**
     * Return the basic join over all file class tables.
     * @return string
     */
    public function baseQueryForFileSelection() {
        assert(false);
    }

    //
    // Protected helper methods
    //

    /**
     * Internal function to return a SubmissionFile object from a row.
     * @param array $row
     * @param string $fileImplementation
     * @return SubmissionFile
     */
    public function fromRow($row, $fileImplementation) {
        $daoDelegate = $this->_getDaoDelegate($fileImplementation);
        if ($daoDelegate !== null) {
            return $daoDelegate->fromRow($row);
        }
        return new DataObject();
    }

    //
    // Private helper methods
    //

    /**
     * Map a genre to the corresponding file implementation.
     * @param int $genreId
     * @return string|null
     */
    protected function _getFileImplementationForGenreId($genreId) {
        static $genreCache = [];
        $genreId = (int) $genreId;

        if (!isset($genreCache[$genreId])) {
            /** @var GenreDAO $genreDao */ // GenreDAO.inc.php (Documented type is not compatible with the inferred type.)
            $genreDao = DAORegistry::getDAO('GenreDAO');
            $genre = $genreDao->getById($genreId);

            if ($genre !== null) {
                $genreMapping = $this->getGenreCategoryMapping();
                $category = $genre->getCategory();
                if (isset($genreMapping[$category])) {
                    $genreCache[$genreId] = $genreMapping[$category];
                }
            }
        }

        return $genreCache[$genreId] ?? null;
    }

    /**
     * Instantiates an appropriate SubmissionFileDAODelegate based on the given genre identifier.
     * @param int $genreId
     * @return SubmissionFileDAODelegate|null
     */
    protected function _getDaoDelegateForGenreId($genreId) {
        $fileImplementation = $this->_getFileImplementationForGenreId($genreId);
        return $this->_getDaoDelegate($fileImplementation);
    }

    /**
     * Instantiates an appropriate SubmissionFileDAODelegate based on the given SubmissionFile.
     * @param SubmissionFile $object
     * @return SubmissionFileDAODelegate|null
     */
    protected function _getDaoDelegateForObject($object) {
        return $this->_getDaoDelegate(get_class($object));
    }

    /**
     * Return the requested SubmissionFileDAODelegate.
     * @param string $fileImplementation
     * @return SubmissionFileDAODelegate|null
     */
    protected function _getDaoDelegate($fileImplementation) {
        $fileImplementation = strtolower((string) $fileImplementation);

        if (!isset($this->_delegates[$fileImplementation])) {
            $delegateClasses = $this->getDelegateClassNames();
            if (isset($delegateClasses[$fileImplementation])) {
                $delegateClass = $delegateClasses[$fileImplementation];
                $this->_delegates[$fileImplementation] = instantiate($delegateClass, 'SubmissionFileDAODelegate', null, null, $this);
            }
        }

        return $this->_delegates[$fileImplementation] ?? null;
    }

    /**
     * Private method to retrieve submission file revisions according to the given filters.
     * @param int|null $submissionId
     * @param int|null $fileStage
     * @param int|null $fileId
     * @param int|null $revision
     * @param int|null $assocType
     * @param int|null $assocId
     * @param int|null $stageId
     * @param int|null $uploaderUserId
     * @param int|null $uploaderUserGroupId
     * @param int|null $round
     * @param int|null $reviewRoundId
     * @param bool $latestOnly
     * @param mixed $rangeInfo
     * @return array
     */
    protected function _getInternally($submissionId = null, $fileStage = null, $fileId = null, $revision = null, $assocType = null, $assocId = null, $stageId = null, $uploaderUserId = null, $uploaderUserGroupId = null, $round = null, $reviewRoundId = null, $latestOnly = false, $rangeInfo = null) {
        $latestOnly = (bool) $latestOnly;

        if ($reviewRoundId !== null && $reviewRoundId !== '' && $round !== null && $round !== '') {
            $round = null;
        }

        $sql = $this->baseQueryForFileSelection();
        $submissionEntity = $this->getSubmissionEntityName();
        
        if (($round !== null && $round !== '') || ($reviewRoundId !== null && $reviewRoundId !== '')) {
            $sql .= ' INNER JOIN review_round_files rrf ON sf.'.$submissionEntity.'_id = rrf.'.$submissionEntity.'_id AND sf.file_id = rrf.file_id ';
        }

        [$filterClause, $params] = $this->_buildFileSelectionFilter(
            $submissionId, $fileStage, $fileId, $revision, $assocType, $assocId, $stageId, $uploaderUserId, $uploaderUserGroupId, $round, $reviewRoundId
        );

        $filterClause = $filterClause !== '' ? $filterClause : '1=1';

        if ($latestOnly) {
            $sql .= ' LEFT JOIN '.$submissionEntity.'_files sf2 ON sf.file_id = sf2.file_id AND sf.revision < sf2.revision WHERE sf2.revision IS NULL AND ' . $filterClause;
        } else {
            $sql .= ' WHERE ' . $filterClause;
        }

        $sql .= ' ORDER BY sf.'.$submissionEntity.'_id ASC, sf.file_stage ASC, sf.file_id ASC, sf.revision DESC';

        if ($rangeInfo) {
            $result = $this->retrieveRange($sql, $params, $rangeInfo);
        } else {
            $result = $this->retrieve($sql, $params);
        }

        $submissionFiles = [];
        if ($result) {
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $currentFileId = (int) ($row['file_id'] ?? 0);
                $currentRevision = (int) ($row['revision'] ?? 0);
                $idAndRevision = $currentFileId . '-' . $currentRevision;

                $genreId = isset($row['genre_id']) ? (int) $row['genre_id'] : null;
                $fileImplementation = $genreId ? $this->_getFileImplementationForGenreId($genreId) : null;

                if ($fileImplementation) {
                    $delegate = $this->_getDaoDelegate($fileImplementation);
                    if ($delegate !== null) {
                        $submissionFiles[$idAndRevision] = $delegate->fromRow($row);
                    } else {
                        $submissionFiles[$idAndRevision] = $this->fromRow($row, $fileImplementation);
                    }
                } else {
                    $submissionFiles[$idAndRevision] = $this->fromRow($row, 'submissionfile');
                }
                
                $result->MoveNext();
            }
            $result->Close();
        }

        return $submissionFiles;
    }

    /**
     * Private method to delete submission file revisions according to the given filters.
     * @return int|bool
     */
    protected function _deleteInternally($submissionId = null, $fileStage = null, $fileId = null, $revision = null, $assocType = null, $assocId = null, $stageId = null, $uploaderUserId = null, $uploaderUserGroupId = null, $round = null, $latestOnly = false) {
        $deletedFiles = $this->_getInternally($submissionId, $fileStage, $fileId, $revision, $assocType, $assocId, $stageId, $uploaderUserId, $uploaderUserGroupId, $round, null, $latestOnly);
        
        if (empty($deletedFiles)) {
            return 0;
        }

        foreach ($deletedFiles as $deletedFile) {
            $daoDelegate = $this->_getDaoDelegateForObject($deletedFile);
            if ($daoDelegate === null || !$daoDelegate->deleteObject($deletedFile)) {
                return false;
            }
        }

        return count($deletedFiles);
    }

    /**
     * Build an SQL where clause to select submissions based on the given filter information.
     * @param mixed $submissionId
     * @param mixed $fileStage
     * @param mixed $fileId
     * @param mixed $revision
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $stageId
     * @param mixed $uploaderUserId
     * @param mixed $uploaderUserGroupId
     * @param mixed $round
     * @param mixed $reviewRoundId
     * @return array
     */
    protected function _buildFileSelectionFilter($submissionId, $fileStage, $fileId, $revision, $assocType, $assocId, $stageId, $uploaderUserId, $uploaderUserGroupId, $round, $reviewRoundId) {
        $submissionEntity = $this->getSubmissionEntityName();
        $filters = [
            'sf.'.$submissionEntity.'_id' => $submissionId,
            'sf.file_stage' => $fileStage,
            'sf.file_id' => $fileId,
            'sf.revision' => $revision,
            'sf.assoc_type' => $assocType,
            'sf.assoc_id' => $assocId,
            'sf.uploader_user_id' => $uploaderUserId,
            'sf.user_group_id' => $uploaderUserGroupId,
            'rrf.stage_id' => $stageId,
            'rrf.round' => $round,
            'rrf.review_round_id' => $reviewRoundId
        ];

        $filterClause = '';
        $params = [];
        $conjunction = '';
        
        foreach ($filters as $filteredColumn => $filteredId) {
            if ($filteredId !== null && $filteredId !== '') {
                $filterClause .= $conjunction . ' ' . $filteredColumn . ' = ?';
                $conjunction = ' AND';
                $params[] = (int) $filteredId;
            }
        }
        
        return [$filterClause, $params];
    }

    /**
     * Make sure that the genre of the file and its file implementation are compatible.
     * @param SubmissionFile $submissionFile
     * @return SubmissionFile
     */
    protected function _castToGenre($submissionFile) {
        // [WIZDAM FIX] Type narrowing for linter to recognize SubmissionFile methods
        /** @var SubmissionFile $submissionFile */
        $targetImplementation = strtolower($this->_getFileImplementationForGenreId($submissionFile->getGenreId()));

        if (!is_a($submissionFile, $targetImplementation)) {
            $targetDaoDelegate = $this->_getDaoDelegate($targetImplementation);
            if ($targetDaoDelegate !== null) {
                $targetFile = $targetDaoDelegate->newDataObject();
                $submissionFile = $submissionFile->upcastTo($targetFile);
            }
        }

        return $submissionFile;
    }

    /**
     * Make sure that a file's implementation corresponds to the way it is saved in the database.
     * @param SubmissionFile $submissionFile
     * @return SubmissionFile|null
     */
    protected function _castToDatabase($submissionFile) {
        $fileId = $submissionFile->getFileId();
        $revision = $submissionFile->getRevision();
        return $this->getRevision((int) $fileId, (int) $revision);
    }

    /**
     * Check whether the given array contains exactly zero or one revisions and return it.
     * @param array $revisions
     * @return SubmissionFile|null
     */
    protected function _checkAndReturnRevision($revisions) {
        if (empty($revisions)) {
            return null;
        }
        return array_pop($revisions);
    }
    
}
?>
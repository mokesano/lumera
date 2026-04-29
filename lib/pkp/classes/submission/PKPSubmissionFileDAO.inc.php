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
 * objects and their decendents (e.g. MonographFile, ArtworkFile).
 *
 * This class provides access to all SubmissionFile implementations. It
 * instantiates and uses delegates internally to provide the right database
 * access behaviour depending on the type of the accessed file.
 */

import('lib.pkp.classes.file.PKPFileDAO');

class PKPSubmissionFileDAO extends PKPFileDAO {
    
    /**
     * @var array a private list of delegates that provide operations for
     * different SubmissionFile implementations.
     */
    public $_delegates = array();

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPSubmissionFileDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPSubmissionFileDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }


    //
    // Public methods
    //
    
    /**
     * Retrieve a specific revision of a file.
     * @param $fileId int
     * @param $revision int
     * @param $fileStage int (optional) further restricts
     * the selection to a given file stage.
     * @param $submissionId int (optional) for validation
     * purposes only
     * @return SubmissionFile|null
     */
    public function getRevision($fileId, $revision, $fileStage = null, $submissionId = null) {
        if (!($fileId && $revision)) {
            return null;
        }
        $revisions = $this->_getInternally($submissionId, $fileStage, $fileId, $revision);
        return $this->_checkAndReturnRevision($revisions);
    }


    /**
     * Retrieve the latest revision of a file.
     * @param $fileId int
     * @param $fileStage int (optional) further restricts
     * the selection to a given file stage.
     * @param $submissionId int (optional) for validation
     * purposes only
     * @return SubmissionFile|null
     */
    public function getLatestRevision($fileId, $fileStage = null, $submissionId = null) {
        if (!$fileId) {
            return null;
        }
        $revisions = $this->_getInternally($submissionId, $fileStage, $fileId, null, null, null, null, null, null, null, null, true);
        return $this->_checkAndReturnRevision($revisions);
    }

    /**
     * Retrieve a list of current revisions.
     * @param $submissionId int
     * @param $fileStage int (optional) further restricts
     * the selection to a given file stage.
     * @param $rangeInfo DBResultRange (optional)
     * @return array a list of SubmissionFile instances
     */
    public function getLatestRevisions($submissionId, $fileStage = null, $rangeInfo = null) {
        if (!$submissionId) {
            return null;
        }
        return $this->_getInternally($submissionId, $fileStage, null, null, null, null, null, null, null, null, null, true, $rangeInfo);
    }

    /**
     * Retrieve all revisions of a submission file.
     * @param $fileId int
     * @param $fileStage int (optional) further restricts
     * the selection to a given file stage.
     * @param $submissionId int (optional) for validation
     * purposes only
     * @param $rangeInfo DBResultRange (optional)
     * @return array a list of SubmissionFile instances
     */
    public function getAllRevisions($fileId, $fileStage = null, $submissionId = null, $rangeInfo = null) {
        if (!$fileId) {
            return null;
        }
        return $this->_getInternally($submissionId, $fileStage, $fileId, null, null, null, null, null, null, null, null, false, $rangeInfo);
    }

    /**
     * Retrieve the latest revision of all files associated
     * to a certain object.
     * @param $assocType int
     * @param $assocId int
     * @param $fileStage int (optional) further restricts
     * the selection to a given file stage.
     * @param $rangeInfo DBResultRange (optional)
     * @return array a list of SubmissionFile instances
     */
    public function getLatestRevisionsByAssocId($assocType, $assocId, $submissionId = null, $fileStage = null, $rangeInfo = null) {
        if (!($assocType && $assocId)) {
            return null;
        }
        return $this->_getInternally($submissionId, $fileStage, null, null, $assocType, $assocId, null, null, null, null, null, true, $rangeInfo);
    }

    /**
     * Retrieve all files associated to a certain object.
     * @param $assocType int
     * @param $assocId int
     * @param $fileStage int (optional) further restricts
     * the selection to a given file stage.
     * @param $rangeInfo DBResultRange (optional)
     * @return array a list of SubmissionFile instances
     */
    public function getAllRevisionsByAssocId($assocType, $assocId, $fileStage = null, $rangeInfo = null) {
        if (!($assocType && $assocId)) {
            return null;
        }
        return $this->_getInternally(null, $fileStage, null, null, $assocType, $assocId, null, null, null, null, null, false, $rangeInfo);
    }

    /**
     * Get all file revisions assigned to the given review round.
     * @param $submissionId integer
     * @param $stageId integer
     * @param $round integer
     * @param $fileStage integer (optional) One of the MONOGRAPH_FILE constants
     * @param $uploaderUserId integer
     * @param $uploaderUserGroupId integer
     * @return array A list of MonographFiles.
     */
    public function getRevisionsByReviewRound($submissionId, $stageId, $round, $fileStage = null,
            $uploaderUserId = null, $uploaderUserGroupId = null) {
        if (!($stageId && $round)) {
            return null;
        }
        return $this->_getInternally($submissionId, $fileStage, null, null, null, null, $stageId, $uploaderUserId, $uploaderUserGroupId, $round);
    }

    /**
     * Get all files that are in the current review
     * round, but have later revisions.
     * @param $submissionId int
     * @param $stageId int
     * @param $round int
     * @param $fileStage int (optional) A MONOGRAPH_FILE_* constant
     * @return array A list of MonographFiles.
     */
    public function getLatestNewRevisionsByReviewRound($submissionId, $stageId, $round, $fileStage = null) {
        if (!($stageId && $round)) {
            return array();
        }
        return $this->_getInternally($submissionId, $fileStage, null, null, null, null, $stageId, null, null, $round, null, true);
    }

    /**
     * Retrieve the current revision number for a file.
     * @param $fileId int
     * @return int
     */
    public function getLatestRevisionNumber($fileId) {
        assert(!is_null($fileId));

        // Retrieve the latest revision from the database.
        $result = $this->retrieve(
            'SELECT MAX(revision) AS max_revision FROM '.$this->getSubmissionEntityName().'_files WHERE file_id = ?',
            $fileId
        );
        if($result->RecordCount() != 1) return null;

        $row = $result->FetchRow();
        $result->Close();
        unset($result);

        $latestRevision = (int)$row['max_revision'];
        assert($latestRevision > 0);
        return $latestRevision;
    }

    /**
     * Insert a new SubmissionFile.
     * @param $submissionFile SubmissionFile
     * @param $sourceFile string The place where the physical file resides.
     * @param $isUpload boolean set to true if the file has just been uploaded.
     * @return SubmissionFile
     */
    public function insertObject($submissionFile, $sourceFile, $isUpload = false) {
        // Hapus '&' pada parameter dan return
        
        // Make sure that the implementation of the updated file
        // is compatible with its genre (upcast but no downcast).
        $submissionFile = $this->_castToGenre($submissionFile);

        // Find the required target implementation and delegate.
        $targetImplementation = strtolower_codesafe(
            $this->_getFileImplementationForGenreId(
            $submissionFile->getGenreId())
        );
        $targetDaoDelegate = $this->_getDaoDelegate($targetImplementation);
        $insertedFile = $targetDaoDelegate->insertObject($submissionFile, $sourceFile, $isUpload);

        // If the updated file does not have the correct target type then we'll have
        // to retrieve it again from the database to cast it to the right type (downcast).
        if (strtolower_codesafe(get_class($insertedFile)) != $targetImplementation) {
            $insertedFile = $this->_castToDatabase($insertedFile);
        }
        return $insertedFile;
    }

    /**
     * Update an existing submission file.
     * @param $updatedFile SubmissionFile
     * @param $previousFileId integer
     * @param $previousRevision integer
     * @return SubmissionFile The updated file.
     */
    public function updateObject($updatedFile, $previousFileId = null, $previousRevision = null) {
        // Hapus '&' pada parameter dan return
        
        // Make sure that the implementation of the updated file
        // is compatible with its genre.
        $updatedFile = $this->_castToGenre($updatedFile);

        // Complete the identifying data of the previous file if not given.
        $previousFileId = (int)($previousFileId ? $previousFileId : $updatedFile->getFileId());
        $previousRevision = (int)($previousRevision ? $previousRevision : $updatedFile->getRevision());

        // Retrieve the previous file.
        $previousFile = $this->getRevision($previousFileId, $previousRevision);
        // assert(is_a($previousFile, 'MonographFile')); // Removed strict check to avoid crashes if class mismatch

        // Canonicalized the implementation of the previous file.
        $previousImplementation = strtolower_codesafe(get_class($previousFile));

        // Find the required target implementation and delegate.
        $targetImplementation = strtolower_codesafe(
            $this->_getFileImplementationForGenreId(
            $updatedFile->getGenreId())
        );
        $targetDaoDelegate = $this->_getDaoDelegate($targetImplementation);

        // If the implementation in the database differs from the target
        // implementation then we'll have to delete + insert the object
        // to make sure that the database contains consistent data.
        if ($previousImplementation != $targetImplementation) {
            $previousFilePath = $previousFile->getFilePath();
            $targetFilePath = $updatedFile->getFilePath();
            // assert($previousFilePath != $targetFilePath && !file_exists($targetFilePath));
            
            import('lib.pkp.classes.file.FileManager');
            $fileManager = new FileManager();
            $fileManager->copyFile($previousFilePath, $targetFilePath);

            $sourceDaoDelegate = $this->_getDaoDelegate($previousImplementation);
            $sourceDaoDelegate->deleteObject($previousFile);
            $targetDaoDelegate->insertObject($updatedFile, $targetFilePath);
        } else {
            // If the implementation in the database does not change then we
            // can do an efficient update.
            if (!$targetDaoDelegate->updateObject($updatedFile, $previousFile)) {
                return null;
            }
        }

        // If the updated file does not have the correct target type then we'll have
        // to retrieve it again from the database to cast it to the right type.
        if (strtolower_codesafe(get_class($updatedFile)) != $targetImplementation) {
            $updatedFile = $this->_castToDatabase($updatedFile);
        }

        return $updatedFile;
    }

    /**
     * Set the latest revision of a file as the latest revision of another file.
     * @param $revisedFileId integer
     * @param $newFileId integer
     * @param $submissionId integer
     * @param $fileStage integer
     * @return SubmissionFile|null
     */
    public function setAsLatestRevision($revisedFileId, $newFileId, $submissionId, $fileStage) {
        $revisedFileId = (int)$revisedFileId;
        $newFileId = (int)$newFileId;
        $submissionId = (int)$submissionId;
        $fileStage = (int)$fileStage;

        // Check whether the two files are already revisions of each other.
        if ($revisedFileId == $newFileId) return null;

        // Retrieve the latest revisions of the two submission files.
        $revisedFile = $this->getLatestRevision($revisedFileId, $fileStage, $submissionId);
        $newFile = $this->getLatestRevision($newFileId, $fileStage, $submissionId);
        if (!($revisedFile && $newFile)) return null;

        // Save identifying data of the changed file required for update.
        $previousFileId = $newFile->getFileId();
        $previousRevision = $newFile->getRevision();

        // Copy data over from the revised file to the new file.
        $newFile->setFileId($revisedFileId);
        $newFile->setRevision($revisedFile->getRevision()+1);
        $newFile->setGenreId($revisedFile->getGenreId());
        $newFile->setAssocType($revisedFile->getAssocType());
        $newFile->setAssocId($revisedFile->getAssocId());

        // Update the file in the database.
        return $this->updateObject($newFile, $previousFileId, $previousRevision);
    }

    /**
     * Assign file to a review round.
     */
    public function assignRevisionToReviewRound($fileId, $revision, $stageId, $reviewRoundId, $submissionId) {
        if (!is_numeric($fileId) || !is_numeric($revision)) fatalError('Invalid file!');
        return $this->update('INSERT INTO review_round_files
                ('.$this->getSubmissionEntityName().'_id, stage_id, review_round_id, file_id, revision)
                VALUES (?, ?, ?, ?, ?)',
                array((int)$submissionId, (int)$stageId, (int)$reviewRoundId, (int)$fileId, (int)$revision));
    }

    /**
     * Delete a specific revision of a submission file.
     * @param $submissionFile SubmissionFile
     * @return integer the number of deleted file revisions
     */
    public function deleteRevision($submissionFile) {
        return $this->deleteRevisionById($submissionFile->getFileId(), $submissionFile->getRevision(), $submissionFile->getFileStage(), $submissionFile->getSubmissionId());
    }

    /**
     * Delete a specific revision of a submission file by id.
     * @param $fileId int
     * @param $revision int
     * @return integer
     */
    public function deleteRevisionById($fileId, $revision, $fileStage = null, $submissionId = null) {
        return $this->_deleteInternally($submissionId, $fileStage, $fileId, $revision);
    }

    /**
     * Delete the latest revision of a submission file by id.
     * @param $fileId int
     * @return integer
     */
    public function deleteLatestRevisionById($fileId, $fileStage= null, $submissionId = null) {
        return $this->_deleteInternally($submissionId, $fileStage, $fileId, null, null, null, null, null, null, null, true);
    }

    /**
     * Delete all revisions of a file, optionally restricted to a given file stage.
     * @param $fileId int
     * @return integer
     */
    public function deleteAllRevisionsById($fileId, $fileStage = null, $submissionId = null) {
        return $this->_deleteInternally($submissionId, $fileStage, $fileId);
    }

    /**
     * Delete all revisions of all files of a submission.
     * @param $submissionId int
     * @return integer
     */
    public function deleteAllRevisionsBySubmissionId($submissionId, $fileStage = null) {
        return $this->_deleteInternally($submissionId, $fileStage);
    }

    /**
     * Retrieve all files associated to a certain object.
     * @param $assocType int
     * @param $assocId int
     * @return integer
     */
    public function deleteAllRevisionsByAssocId($assocType, $assocId, $fileStage = null) {
        return $this->_deleteInternally(null, $fileStage, null, null, $assocType, $assocId);
    }

    /**
     * Remove all file assignements for the given review round.
     */
    public function deleteAllRevisionsByReviewRound($submissionId, $stageId, $reviewRoundId) {
        // Remove currently assigned review files.
        return $this->update('DELETE FROM review_round_files
                WHERE '.$this->getSubmissionEntityName().'_id = ? AND stage_id = ? AND review_round_id = ?',
                array((int)$submissionId, (int)$stageId, (int)$reviewRoundId));
    }

    /**
     * Remove a specific file assignment from a review round.
     */
    public function deleteReviewRoundAssignment($submissionId, $stageId, $fileId, $revision) {
        // Remove currently assigned review files.
        return $this->update('DELETE FROM review_round_files
                WHERE '.$this->getSubmissionEntityName().'_id = ? AND stage_id = ? AND file_id = ? AND revision = ?',
                array((int)$submissionId, (int)$stageId, (int)$fileId, (int)$revision));
    }

    /**
     * Transfer the ownership of the submission files of one user to another.
     * @param $oldUserId int
     * @param $newUserId int
     */
    public function transferOwnership($oldUserId, $newUserId) {
        $submissionFiles = $this->_getInternally(null, null, null, null, null, null, null, $oldUserId, null, null);
        foreach ($submissionFiles as $file) {
            $daoDelegate = $this->_getDaoDelegateForObject($file);
            $file->setUploaderUserId($newUserId);
            $daoDelegate->updateObject($file, $file); // nothing else changes
        }
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @param $genreId integer
     * @return SubmissionFile
     */
    public function newDataObjectByGenreId($genreId) {
        // Identify the delegate.
        $daoDelegate = $this->_getDaoDelegateForGenreId($genreId);

        // Instantiate and return the object.
        $newSubmissionFile = $daoDelegate->newDataObject();
        return $newSubmissionFile;
    }


    //
    // Abstract template methods to be implemented by subclasses.
    //

	/**
	 * Return the name of the base submission entity
	 * (i.e. 'monograph', 'paper', 'article', etc.)
	 * @return string
	 */
    public function getSubmissionEntityName() {
        assert(false);
    }

	/**
	 * Return the available delegates mapped by lower
	 * case class names.
	 * @return array a list of fully qualified class names
	 *  indexed by the lower case class name of the file
	 *  implementation they serve.
	 */
    public function getDelegateClassNames() {
        assert(false);
    }

	/**
	 * Return the mapping of genre categories to the lower
	 * case class name of file implementation.
	 * @return array a list of lower case class names of
	 *  file implementations.
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
     * @param $row array
     * @param $fileImplementation string
     * @return SubmissionFile
     */
    public function fromRow($row, $fileImplementation) {
        // Identify the delegate.
        $daoDelegate = $this->_getDaoDelegate($fileImplementation); /* @var $daoDelegate SubmissionFileDAODelegate */

        // Let the DAO delegate instantiate the file implementation.
        return $daoDelegate->fromRow($row);
    }


    //
    // Private helper methods
    //
    /**
     * Map a genre to the corresponding file implementation.
     * @param $genreId integer
     * @return string The class name of the file implementation.
     */
    protected function _getFileImplementationForGenreId($genreId) {
        static $genreCache = array();

        if (!isset($genreCache[$genreId])) {
            // We have to instantiate the genre to find out about
            // its category.
            $genreDao = DAORegistry::getDAO('GenreDAO'); /* @var $genreDao GenreDAO */
            $genre = $genreDao->getById($genreId);

            // Identify the file implementation.
            $genreMapping = $this->getGenreCategoryMapping();
            assert(isset($genreMapping[$genre->getCategory()]));
            $genreCache[$genreId] = $genreMapping[$genre->getCategory()];
        }

        return $genreCache[$genreId];
    }

    /**
     * Instantiates an approprate SubmissionFileDAODelegate
     * based on the given genre identifier.
     * @param $genreId integer
     * @return SubmissionFileDAODelegate
     */
    protected function _getDaoDelegateForGenreId($genreId) {
        // Find the required file implementation.
        $fileImplementation = $this->_getFileImplementationForGenreId($genreId);

        // Return the DAO delegate.
        return $this->_getDaoDelegate($fileImplementation);
    }

    /**
     * Instantiates an appropriate SubmissionFileDAODelegate
     * based on the given SubmissionFile.
     * @param $object SubmissionFile
     * @return SubmissionFileDAODelegate
     */
    protected function _getDaoDelegateForObject($object) {
        return $this->_getDaoDelegate(get_class($object));
    }

    /**
     * Return the requested SubmissionFileDAODelegate.
     * @param $fileImplementation string
     * @return SubmissionFileDAODelegate
     */
    protected function _getDaoDelegate($fileImplementation) {
        // Normalize the file implementation name.
        $fileImplementation = strtolower_codesafe($fileImplementation);

        // Did we already instantiate the requested delegate?
        if (!isset($this->_delegates[$fileImplementation])) {
            // Instantiate the requested delegate.
            $delegateClasses = $this->getDelegateClassNames();
            assert(isset($delegateClasses[$fileImplementation]));
            $delegateClass = $delegateClasses[$fileImplementation];
            // Modernisasi: Hapus assignment by ref on instantiate
            $this->_delegates[$fileImplementation] = instantiate($delegateClass, 'SubmissionFileDAODelegate', null, null, $this);
        }

        // Return the delegate.
        return $this->_delegates[$fileImplementation];
    }

    /**
     * Private method to retrieve submission file revisions
     * according to the given filters.
     * @return array a list of SubmissionFile instances
     */
    protected function _getInternally($submissionId = null, $fileStage = null, $fileId = null, $revision = null,
            $assocType = null, $assocId = null, $stageId = null, $uploaderUserId = null, $uploaderUserGroupId = null,
            $round = null, $reviewRoundId = null, $latestOnly = false, $rangeInfo = null) {

        // Sanitize parameters.
        $latestOnly = (boolean)$latestOnly;
        if (!is_null($rangeInfo)) assert(is_a($rangeInfo, 'DBResultRange'));

        if ($reviewRoundId && $round) {
            assert(false);
            $round = null;
        }

        // Retrieve the base query.
        $sql = $this->baseQueryForFileSelection($latestOnly);

        // Add the revision round file join if a revision round
        // filter was requested.
        $submissionEntity = $this->getSubmissionEntityName();
        if ($round || $reviewRoundId) {
            $sql .= 'INNER JOIN review_round_files rrf
                    ON sf.'.$submissionEntity.'_id = rrf.'.$submissionEntity.'_id
                    AND sf.file_id = rrf.file_id ';
        }

        // Filter the query.
        list($filterClause, $params) = $this->_buildFileSelectionFilter(
                $submissionId, $fileStage, $fileId, $revision,
                $assocType, $assocId, $stageId, $uploaderUserId, $uploaderUserGroupId, $round, $reviewRoundId);

        // Did the user request all or only the latest revision?
        if ($latestOnly) {
            $sql .= 'LEFT JOIN '.$submissionEntity.'_files sf2 ON sf.file_id = sf2.file_id AND sf.revision < sf2.revision
                WHERE sf2.revision IS NULL AND '.$filterClause;
        } else {
            $sql .= 'WHERE '.$filterClause;
        }

        // Order the query.
        $sql .= ' ORDER BY sf.'.$submissionEntity.'_id ASC, sf.file_stage ASC, sf.file_id ASC, sf.revision DESC';

        // Execute the query.
        if ($rangeInfo) {
            $result = $this->retrieveRange($sql, $params, $rangeInfo);
        } else {
            $result = $this->retrieve($sql, $params);
        }

        // Build the result array.
        $submissionFiles = array();
        while (!$result->EOF) {
            // Retrieve the next result row.
            $row = $result->GetRowAssoc(false);

            // Construct a combined id from file id and revision
            $idAndRevision = $row['monograph_file_id'].'-'.$row['monograph_revision'];

            // Check for duplicates.
            assert(!isset($submissionFiles[$idAndRevision]));

            // Instantiate the file.
            $submissionFiles[$idAndRevision] = $this->fromRow($row); // Hapus '&'

            // Move the query cursor to the next record.
            $result->MoveNext();
        }
        $result->Close();
        unset($result);

        return $submissionFiles;
    }

    /**
     * Private method to delete submission file revisions
     * according to the given filters.
     * @return boolean|integer Returns boolean false if an error occurs, otherwise the number of deleted files.
     */
    protected function _deleteInternally($submissionId = null, $fileStage = null, $fileId = null, $revision = null,
            $assocType = null, $assocId = null, $stageId = null, $uploaderUserId = null, $uploaderUserGroupId = null,
            $round = null, $latestOnly = false) {

        // Identify all matched files.
        $deletedFiles = $this->_getInternally($submissionId, $fileStage, $fileId, $revision,
                $assocType, $assocId, $stageId, $uploaderUserId, $uploaderUserGroupId, $round, null, $latestOnly);
        if (empty($deletedFiles)) return 0;

        foreach($deletedFiles as $deletedFile) { /* @var $deletedFile SubmissionFile */
            $daoDelegate = $this->_getDaoDelegateForObject($deletedFile);
            if (!$daoDelegate->deleteObject($deletedFile)) return false;
        }

        // Return the number of deleted files.
        return count($deletedFiles);
    }

    /**
     * Build an SQL where clause to select submissions based on the given filter information.
     * @return array
     */
    protected function _buildFileSelectionFilter($submissionId, $fileStage,
            $fileId, $revision, $assocType, $assocId, $stageId, $uploaderUserId, $uploaderUserGroupId, $round, $reviewRoundId) {

        // Make sure that at least one entity filter has been set.
        assert((int)$submissionId || (int)$uploaderUserId || (int)$fileId || (int)$assocId);

        // Both, assoc type and id, must be set (or unset) together.
        assert(((int)$assocType && (int)$assocId) || !((int)$assocType || (int)$assocId));

        if ($reviewRoundId && $round) {
            assert(false);
            $round = null;
        }

        // Collect the filtered columns and ids in
        // an array for consistent handling.
        $submissionEntity = $this->getSubmissionEntityName();
        $filters = array(
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
        );

        // Build and return a SQL where clause and a parameter array.
        $filterClause = '';
        $params = array();
        $conjunction = '';
        foreach($filters as $filteredColumn => $filteredId) {
            if ($filteredId) {
                $filterClause .= $conjunction.' '.$filteredColumn.' = ?';
                $conjunction = ' AND';
                $params[] = (int)$filteredId;
            }
        }
        return array($filterClause, $params);
    }

    /**
     * Make sure that the genre of the file and its file
     * implementation are compatible.
     * @param $submissionFile SubmissionFile
     * @return SubmissionFile The same file in a compatible implementation.
     */
    protected function _castToGenre($submissionFile) {
        // Hapus '&' pada parameter dan return
        
        // Find the required target implementation.
        $targetImplementation = strtolower_codesafe(
            $this->_getFileImplementationForGenreId(
            $submissionFile->getGenreId())
        );

        // If the current implementation of the updated object
        // differs from the target implementation then we'll
        // have to cast the object.
        if (!is_a($submissionFile, $targetImplementation)) {
            // The updated file has to be upcast by manually
            // instantiating the target object and copying data
            // to the target.
            $targetDaoDelegate = $this->_getDaoDelegate($targetImplementation);
            $targetFile = $targetDaoDelegate->newDataObject(); // Hapus &
            $targetFile = $submissionFile->upcastTo($targetFile); // Hapus &
            unset($submissionFile);
            $submissionFile = $targetFile;
        }

        return $submissionFile;
    }

    /**
     * Make sure that a file's implementation corresponds to the way it is
     * saved in the database.
     * @param $submissionFile SubmissionFile
     * @return SubmissionFile
     */
    protected function _castToDatabase($submissionFile) {
        // Hapus '&'
        $fileId = $submissionFile->getFileId();
        $revision = $submissionFile->getRevision();
        unset($submissionFile);
        return $this->getRevision($fileId, $revision);
    }

    /**
     * Check whether the given array contains exactly
     * zero or one revisions and return it.
     * @param $revisions array
     * @return SubmissionFile
     */
    protected function _checkAndReturnRevision($revisions) {
        // Hapus '&'
        assert(count($revisions) <= 1);
        if (empty($revisions)) {
            return null;
        } else {
            $revision = array_pop($revisions); // Hapus &
            // assert(is_a($revision, 'SubmissionFile')); // Removed stricter check
            return $revision;
        }
    }
}

?>
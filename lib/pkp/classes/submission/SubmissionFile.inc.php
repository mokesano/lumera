<?php
declare(strict_types=1);

/**
 * @file classes/submission/SubmissionFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFile
 * @ingroup submission
 *
 * @brief Submission file class.
 */

import('lib.pkp.classes.file.PKPFile');

class SubmissionFile extends PKPFile {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function SubmissionFile() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::SubmissionFile(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }


    //
    // Getters and Setters
    //
    /**
     * Get ID of file.
     * @return int
     */
    public function getFileId() {
        // WARNING: Do not modernize getter/setters without considering
        // ID clash with subclasses ArticleGalley and ArticleNote!
        return $this->getData('fileId');
    }

    /**
     * Set ID of file.
     * @param int $fileId int
     */
    public function setFileId($fileId) {
        // WARNING: Do not modernize getter/setters without considering
        // ID clash with subclasses ArticleGalley and ArticleNote!
        return $this->setData('fileId', $fileId);
    }

    /**
     * Get source file ID of this file.
     * @return int
     */
    public function getSourceFileId() {
        return $this->getData('sourceFileId');
    }

    /**
     * Set source file ID of this file.
     * @param int $sourceFileId int
     */
    public function setSourceFileId($sourceFileId) {
        return $this->setData('sourceFileId', $sourceFileId);
    }

    /**
     * Get source revision of this file.
     * @return int
     */
    public function getSourceRevision() {
        return $this->getData('sourceRevision');
    }

    /**
     * Set source revision of this file.
     * @param int $sourceRevision int
     */
    public function setSourceRevision($sourceRevision) {
        return $this->setData('sourceRevision', $sourceRevision);
    }

    /**
     * Get associated ID of file. (Used, e.g., for email log attachments.)
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * Set associated ID of file. (Used, e.g., for email log attachments.)
     * @param int $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * Get revision number.
     * @return int
     */
    public function getRevision() {
        return $this->getData('revision');
    }

    /**
     * Get the combined key of the file
     * consisting of the file id and the revision.
     * @return string
     */
    public function getFileIdAndRevision() {
        $id = $this->getFileId();
        $revision = $this->getRevision();
        $idAndRevision = $id;
        if ($revision) {
            $idAndRevision .= '-'.$revision;
        }
        return $idAndRevision;
    }

    /**
     * Set revision number.
     * @param int $revision int
     */
    public function setRevision($revision) {
        return $this->setData('revision', $revision);
    }

    /**
     * Get ID of submission.
     * @return int
     */
    public function getSubmissionId() {
        return $this->getData('submissionId');
    }

    /**
     * Set ID of submission.
     * @param int $submissionId int
     */
    public function setSubmissionId($submissionId) {
        return $this->setData('submissionId', $submissionId);
    }

    /**
     * Get type of the file.
     * @return int
     */
    public function getType() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getFileStage();
    }

    /**
     * Set type of the file.
     * @param mixed $type int
     */
    public function setType($type) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setFileStage($type);
    }

    /**
     * Get the genre ID of this submission file.
     * @return int
     */
    public function getGenreId() {
        return (int) $this->getData('genreId');
    }

    /**
     * Set the genre ID of this submission file.
     * @param int $genreId
     */
    public function setGenreId($genreId) {
        $this->setData('genreId', (int) $genreId);
    }

    /**
     * Get the association type of this submission file.
     * @return int
     */
    public function getAssocType() {
        return (int) $this->getData('assocType');
    }

    /**
     * Set the association type of this submission file.
     * @param int $assocType
     */
    public function setAssocType($assocType) {
        $this->setData('assocType', (int) $assocType);
    }

    /**
     * Get file stage of the file.
     * @return int
     */
    public function getFileStage() {
        return $this->getData('fileStage');
    }

    /**
     * Set file stage of the file.
     * @param int $fileStage int
     */
    public function setFileStage($fileStage) {
        return $this->setData('fileStage', $fileStage);
    }

    /**
     * Get modified date of file.
     * @return string
     */
    public function getDateModified() {
        return $this->getData('dateModified');
    }

    /**
     * Set modified date of file.
     * @param string $dateModified string
     */
    public function setDateModified($dateModified) {
        return $this->SetData('dateModified', $dateModified);
    }

    /**
     * Get round.
     * @return int
     */
    public function getRound() {
        return $this->getData('round');
    }

    /**
     * Set round.
     * @param int $round int
     */
    public function setRound($round) {
        return $this->SetData('round', $round);
    }

    /**
     * Get viewable.
     * @return boolean
     */
    public function getViewable() {
        return $this->getData('viewable');
    }

    /**
     * Set viewable.
     * @param bool $viewable boolean
     */
    public function setViewable($viewable) {
        return $this->SetData('viewable', $viewable);
    }


    //
    // Public methods
    //
    /**
     * Check if the file may be displayed inline.
     * FIXME: Move to DAO to remove coupling of the domain
     * object to its DAO.
     * @return boolean
     */
    public function isInlineable() {
        /** @var SubmissionFileDAO $submissionFileDao */ // Documented type is not compatible with the inferred type.
        $submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
        return $submissionFileDao->isInlineable($this);
    }

}
?>
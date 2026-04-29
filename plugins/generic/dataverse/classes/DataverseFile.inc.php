<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/DataverseFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataverseFile
 * @ingroup plugins_generic_dataverse
 *
 * @brief DataverseFile object associates suppfile with a Dataverse study.
 * [WIZDAM EDITION] Modernized for PHP 8.4, Strict Typing, and Null-Safety.
 */

// [WIZDAM FIX] import('classes.article.SuppFile') dihapus karena tidak relevan dengan DataObject

class DataverseFile extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataverseFile() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }
    
    /**
     * Get suppfile ID.
     * @return int
     */
    public function getSuppFileId(): int {
        return (int) $this->getData('suppFileId');
    }
    
    /**
     * Set suppfile ID.
     * @param int $suppFileId
     */
    public function setSuppFileId(int $suppFileId): void {
        $this->setData('suppFileId', $suppFileId);
    }
    
    /**
     * Get Dataverse study ID.
     * @return int
     */
    public function getStudyId(): int {
        return (int) $this->getData('studyId');
    }
    
    /**
     * Set Dataverse study ID.
     * @param int $studyId
     */
    public function setStudyId(int $studyId): void {
        $this->setData('studyId', $studyId);
    }
    
    /**
     * Get submission ID.
     * @return int
     */
    public function getSubmissionId(): int {
        return (int) $this->getData('submissionId');
    }
    
    /**
     * Set submission ID.
     * @param int $submissionId
     */
    public function setSubmissionId(int $submissionId): void {
        $this->setData('submissionId', $submissionId);
    }
    
    /**
     * Get content source URI of Dataverse file.
     * @return string
     */
    public function getContentSourceUri(): string {
        return (string) $this->getData('contentSourceUri');
    }
    
    /**
     * Set content source URI of Dataverse file.
     * @param string $contentSourceUri
     */
    public function setContentSourceUri(string $contentSourceUri): void {
        $this->setData('contentSourceUri', $contentSourceUri);
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/submission/SubmissionFileDAODelegate.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileDAODelegate
 * @ingroup submission
 * @see SubmissionFile
 *
 * @brief Abstract class to support DAO delegates that provide operations
 * to retrieve and modify SubmissionFile objects.
 */

import('lib.pkp.classes.db.DAO');

class SubmissionFileDAODelegate extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function SubmissionFileDAODelegate() {
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
    // Abstract public methods to be implemented by subclasses.
    //

    /**
     * Return the name of the base submission entity (i.e. 'monograph', 'paper', 'article', etc.).
     * @return string
     */
    public function getSubmissionEntityName() {
        assert(false);
    }

    /**
     * Insert a new submission file.
     * @param SubmissionFile $submissionFile
     * @param string $sourceFile
     * @param bool $isUpload
     * @return SubmissionFile
     */
    public function insertObject($submissionFile, $sourceFile, $isUpload = false) {
        assert(false);
    }

    /**
     * Update a submission file.
     * @param SubmissionFile $submissionFile
     * @param SubmissionFile $previousFile
     * @return bool
     */
    public function updateObject($submissionFile, $previousFile) {
        assert(false);
    }

    /**
     * Delete a submission file from the database.
     * @param SubmissionFile $submissionFile
     * @return bool
     */
    public function deleteObject($submissionFile) {
        assert(false);
    }

    /**
     * Function to return a SubmissionFile object from a row.
     * @param array $row
     * @return SubmissionFile
     */
    public function fromRow($row) {
        assert(false);
    }

    /**
     * Construct a new data object corresponding to this DAO.
     * @return SubmissionFile
     */
    public function newDataObject() {
        assert(false);
    }

    //
    // Protected helper methods
    //

    /**
     * Get the list of fields for which data is localized.
     * @return array
     */
    public function getLocaleFieldNames() {
        return parent::getLocaleFieldNames();
    }

    /**
     * Update the localized fields for this submission file.
     * @param SubmissionFile $submissionFile
     * @return void
     */
    public function updateLocaleFields($submissionFile) {
        $this->updateDataObjectSettings(
            $this->getSubmissionEntityName() . '_file_settings',
            $submissionFile,
            [
                'file_id' => (int) $submissionFile->getFileId()
            ]
        );
    }

}
?>
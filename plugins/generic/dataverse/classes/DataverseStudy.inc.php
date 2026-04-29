<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/DataverseStudy.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataverseStudy
 * @ingroup plugins_generic_dataverse
 *
 * @brief Basic class describing a Dataverse study
 * [WIZDAM EDITION] Modernized for PHP 8.4, Strict Typing, and LSP Compliance.
 */

class DataverseStudy extends DataObject {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataverseStudy() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }
    
    /**
     * Get study ID.
     * [WIZDAM LSP RULE] Tidak menggunakan return type hint agar tidak bentrok dengan DataObject::getId()
     * @return int
     */
    public function getId() {
        return (int) $this->getData('studyId');
    }

    /**
     * Set study ID.
     * [WIZDAM LSP RULE] Tidak menggunakan type hint agar tidak bentrok dengan DataObject::setId()
     * @param mixed $studyId
     */
    public function setId($studyId) {
        $this->setData('studyId', (int) $studyId);
    }

    /**
     * Get ID of submission associated with study.
     * @return int
     */
    public function getSubmissionId(): int {
        return (int) $this->getData('submissionId');
    }

    /**
     * Set submission ID for study.
     * @param int $submissionId
     */
    public function setSubmissionId(int $submissionId): void {
        $this->setData('submissionId', $submissionId);
    }
    
    /**
     * Get study's edit URI.
     * @return string
     */
    public function getEditUri(): string {
        return (string) $this->getData('editUri');
    }

    /**
     * Set study's edit URI.
     * @param string $editUri
     */
    public function setEditUri(string $editUri): void {
        $this->setData('editUri', $editUri);
    }       
    
    /**
     * Get study's edit media URI.
     * @return string
     */
    public function getEditMediaUri(): string {
        return (string) $this->getData('editMediaUri');
    }

    /**
     * Set study's edit media URI.
     * @param string $editMediaUri
     */
    public function setEditMediaUri(string $editMediaUri): void {
        $this->setData('editMediaUri', $editMediaUri);
    }       

    /**
     * Get study's statement URI.
     * @return string
     */
    public function getStatementUri(): string {
        return (string) $this->getData('statementUri');
    }

    /**
     * Set study's statement URI.
     * @param string $statementUri
     */
    public function setStatementUri(string $statementUri): void {
        $this->setData('statementUri', $statementUri);
    } 
    
    /**
     * Get study's persistent URI.
     * @return string
     */
    public function getPersistentUri(): string {
        return (string) $this->getData('persistentUri');
    }
    
    /**
     * Set study's persistent URI.
     * @param string $persistentUri
     */
    public function setPersistentUri(string $persistentUri): void {
        $this->setData('persistentUri', $persistentUri);
    }
    
    /**
     * Get data citation. 
     * @return string
     */
    public function getDataCitation(): string {
        return (string) $this->getData('dataCitation');
    }
    
    /**
     * Set data citation.
     * @param string $dataCitation
     */
    public function setDataCitation(string $dataCitation): void {
        $this->setData('dataCitation', $dataCitation);
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @defgroup issue Issue
 */

/**
 * @file classes/issue/Issue.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Issue
 * @ingroup issue
 * @see IssueDAO
 *
 * @brief Class for Issue. Core entity representing a journal issue.
 */

import('classes.issue.IssueCover');
import('classes.issue.IssuePublication');
import('classes.issue.IssueAccess');
import('classes.issue.IssueDisplay');
import('classes.issue.IssuePubIdService');

// --- IDENTITY CONSTANTS (ISSUEACCESS) ---
define('ISSUE_ACCESS_OPEN', 1);
define('ISSUE_ACCESS_SUBSCRIPTION', 2);

class Issue extends DataObject {
    
    use IssueCover, IssuePublication, IssueAccess, IssueDisplay;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Deprecated constructor.
     */
    public function Issue() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        $this->__construct();
    }

    /**
     * Get issue id
     * @return int
     */
    public function getIssueId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set issue id
     * @param $issueId int
     */
    public function setIssueId($issueId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($issueId);
    }

    /**
     * Get journal id
     * @return int
     */
    public function getJournalId() {
        return $this->getData('journalId');
    }

    /**
     * Set journal id
     * @param $journalId int
     */
    public function setJournalId($journalId) {
        return $this->setData('journalId', $journalId);
    }

    /**
     * Get the localized title
     * @return string
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedData('title');
    }

    /**
     * Deprecated function to get the localized title
     * @return string
     */
    public function getIssueTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedTitle();
    }

    /**
     * Get title
     * @param $locale string
     * @return string
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set title
     * @param $title string
     * @param $locale string
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', $title, $locale);
    }

    /**
     * Get volume
     * @return int
     */
    public function getVolume() {
        return $this->getData('volume');
    }

    /**
     * Set volume
     * @param $volume int
     */
    public function setVolume($volume) {
        return $this->setData('volume', $volume);
    }

    /**
     * Get number
     * @return string
     */
    public function getNumber() {
        return $this->getData('number');
    }

    /**
     * Set number
     * @param $number string
     */
    public function setNumber($number) {
        return $this->setData('number', $number);
    }

    /**
     * Get year
     * @return int
     */
    public function getYear() {
        return $this->getData('year');
    }

    /**
     * Set year
     * @param $year int
     */
    public function setYear($year) {
        return $this->setData('year', $year);
    }

    /**
     * Get the localized description
     * @return string
     */
    public function getLocalizedDescription() {
        return $this->getLocalizedData('description');
    }

    /**
     * Deprecated function to get the localized description
     * @return string
     */
    public function getIssueDescription() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getLocalizedDescription();
    }

    /**
     * Get description
     * @param $locale string
     * @return string
     */
    public function getDescription($locale) {
        return $this->getData('description', $locale);
    }

    /**
     * Set description
     * @param $description string
     * @param $locale string
     */
    public function setDescription($description, $locale) {
        return $this->setData('description', $description, $locale);
    }

    /**
     * Return string of author names, separated by the specified token
     * @param $separator string
     * @return string
     */
    public function getAuthorString($separator = ', ') {
        $str = '';
        $articles = $this->getArticles();
        foreach ($articles as $article) {
            if (!empty($str)) {
                $str .= $separator;
            }
            $str .= $article->getAuthorString();
        }
        return $str;
    }

    /**
     * Return the string of the issue identification based label format
     * @return string
     */
    public function getIssueIdentification() {
        $displayOptions = [];
        
        if ($this->getShowVolume()) {
            $displayOptions[] = __('issue.vol') . ' ' . $this->getVolume();
        }
        if ($this->getShowNumber()) {
            $displayOptions[] = __('issue.no') . ' ' . $this->getNumber();
        }
        if ($this->getShowYear()) {
            $displayOptions[] = $this->getYear();
        }
        
        $identification = implode(', ', $displayOptions);
        
        if ($this->getShowTitle() && $this->getLocalizedTitle() !== null) {
            $separator = empty($identification) ? '' : ' - ';
            $identification .= $separator . $this->getLocalizedTitle();
        }
        
        return $identification;
    }

    // --- WIZDAM DOMAIN LOGIC ---

    /**
     * Get the issue progress status based on its publication date.
     * [WIZDAM EDITION] Refactored for pure MVC separation.
     * 
     * @return string Status identifier: 'in-progress', 'completed', 'halted', or 'unpublished'
     */
    public function getIssueProgressStatus(): string {
        // Asumsi: getDatePublished() tersedia dari trait IssuePublication
        $datePublished = $this->getDatePublished();
        
        if (empty($datePublished)) {
            return 'unpublished'; 
        }

        try {
            $currentDate = new \DateTime('now');
            $issueDate = new \DateTime($datePublished);
        } catch (\Exception $e) {
            return 'error';
        }

        $currentYear = (int) $currentDate->format('Y');
        $currentMonth = (int) $currentDate->format('m');
        
        $issueYear = (int) $issueDate->format('Y');
        $issueMonth = (int) $issueDate->format('m');

        if ($currentYear === $issueYear) {
            if ($currentMonth <= $issueMonth) {
                return 'in-progress';
            }
            return 'completed';
        }

        return 'halted';
    }

    // 
    // FULLY DECOUPLED LOGIC (NO MORE DAO OR PLUGIN REGISTRY CALLS)
    // 

    /**
     * Get the public ID of the issue.
     * REFACTORED: Delegated to IssuePubIdService to decouple from PluginRegistry.
     * @param $pubIdType string One of the NLM pub-id-type values
     * @return string|null
     */
    public function getPubId($pubIdType) {
        $storedPubId = $this->getStoredPubId($pubIdType);
        if ($storedPubId !== null) {
            return $storedPubId;
        }
        
        return IssuePubIdService::getPubId($this, $pubIdType);
    }

    /**
     * Get stored public ID of the issue.
     * @param $pubIdType string
     * @return string
     */
    public function getStoredPubId($pubIdType) {
        return $this->getData('pub-id::'.$pubIdType);
    }

    /**
     * Set stored public ID of the issue.
     * @param $pubIdType string
     * @param $pubId string
     */
    public function setStoredPubId($pubIdType, $pubId) {
        return $this->setData('pub-id::'.$pubIdType, $pubId);
    }

    /**
     * Return the "best" issue ID -- If a public issue ID is set,
     * use it; otherwise use the internal issue Id.
     * REFACTORED: Removed DAORegistry coupling. 
     * @param $journal Journal (Optional)
     * @return string
     */
    public function getBestIssueId($journal = null) {
        // Pastikan object $journal ada, dan fitur public ID memang diaktifkan
        if ($journal && $journal->getSetting('enablePublicIssueId')) {
            $publicIssueId = $this->getPubId('publisher-id');
            if (!empty($publicIssueId)) {
                return $publicIssueId;
            }
        }

        // Jika $journal null ATAU fitur Off, gunakan ID numerik internal
        return $this->getId();
    }

    /**
     * Get number of articles in this issue.
     * REFACTORED: Removed IssueDAO coupling. 
     * Expects value to be set by the Controller/DAO via setData('numArticles').
     * @return int
     */
    public function getNumArticles() {
        return $this->getData('numArticles') !== null ? $this->getData('numArticles') : 0;
    }

    /**
     * Validate if the core identifying data of the issue exists.
     * @return bool
     */
    public function isValid() {
        return !empty($this->getJournalId()) && !empty($this->getYear());
    }
}
?>
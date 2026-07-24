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

// IDENTITY CONSTANTS (ISSUEACCESS)
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
     * [SHIM] Backward Compatibility.
     */
    public function Issue() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::' . get_class($this) . '(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct();
    }

    /**
     * [DEPRECATED] Get issue id
     * @return int|null
     */
    public function getIssueId() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getId();
    }

    /**
     * [DEPRECATED] Set issue id
     * @param mixed $issueId
     */
    public function setIssueId($issueId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->setId((int) $issueId);
    }

    /**
     * Get journal id
     * @return int|null
     */
    public function getJournalId() {
        return $this->getData('journalId');
    }

    /**
     * Set journal id
     * @param mixed $journalId
     */
    public function setJournalId($journalId) {
        return $this->setData('journalId', (int) $journalId);
    }

    /**
     * Get the localized title
     * @return string|null
     */
    public function getLocalizedTitle() {
        return $this->getLocalizedData('title');
    }

    /**
     * [DEPRECATED] Deprecated function to get the localized title
     * @return string|null
     */
    public function getIssueTitle() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedTitle();
    }

    /**
     * Get title
     * @param string $locale
     * @return string|null
     */
    public function getTitle($locale) {
        return $this->getData('title', $locale);
    }

    /**
     * Set title
     * @param mixed $title
     * @param string $locale
     */
    public function setTitle($title, $locale) {
        return $this->setData('title', (string) $title, $locale);
    }

    /**
     * Get volume
     * @return int|null
     */
    public function getVolume() {
        return $this->getData('volume');
    }

    /**
     * Set volume
     * @param mixed $volume
     */
    public function setVolume($volume) {
        return $this->setData('volume', (int) $volume);
    }

    /**
     * Get number
     * @return string|null
     */
    public function getNumber() {
        return $this->getData('number');
    }

    /**
     * Set number
     * @param mixed $number
     */
    public function setNumber($number) {
        return $this->setData('number', (string) $number);
    }

    /**
     * Get year
     * @return int|null
     */
    public function getYear() {
        return $this->getData('year');
    }

    /**
     * Set year
     * @param mixed $year
     */
    public function setYear($year) {
        return $this->setData('year', (int) $year);
    }

    /**
     * Get the localized description
     * @return string|null
     */
    public function getLocalizedDescription() {
        return $this->getLocalizedData('description');
    }

    /**
     * [DEPRECATED] Deprecated function to get the localized description
     * @return string|null
     */
    public function getIssueDescription() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Deprecated function.', E_USER_DEPRECATED);
        }
        return $this->getLocalizedDescription();
    }

    /**
     * Get description
     * @param string $locale
     * @return string|null
     */
    public function getDescription($locale) {
        return $this->getData('description', $locale);
    }

    /**
     * Set description
     * @param mixed $description
     * @param string $locale
     */
    public function setDescription($description, $locale) {
        return $this->setData('description', (string) $description, $locale);
    }

    /**
     * Return string of author names, separated by the specified token
     * @param string $separator
     * @return string
     */
    public function getAuthorString($separator = ', ') {
        $str = '';
        $articles = method_exists($this, 'getArticles') ? $this->getArticles() : [];
        
        if (is_array($articles)) {
            foreach ($articles as $article) {
                if (!empty($str)) {
                    $str .= $separator;
                }
                if (is_object($article) && method_exists($article, 'getAuthorString')) {
                    $str .= $article->getAuthorString();
                }
            }
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
            $displayOptions[] = (string) $this->getYear();
        }
        
        $identification = implode(', ', $displayOptions);
        
        if ($this->getShowTitle() && $this->getLocalizedTitle() !== null) {
            $separator = $identification === '' ? '' : ' - ';
            $identification .= $separator . $this->getLocalizedTitle();
        }
        
        return $identification;
    }

    // DOMAIN LOGIC ---

    /**
     * Get the issue progress status based on its publication date.
     * [LUMERA] Refactored for pure MVC separation.
     * 
     * @return string Status identifier: 'in-progress', 'completed', 'halted', 'unpublished', or 'error'
     */
    public function getIssueProgressStatus(): string {
        $datePublished = $this->getDatePublished();
        
        if (empty($datePublished)) {
            return 'unpublished'; 
        }

        try {
            $currentDate = new \DateTime('now');
            $issueDate = new \DateTime((string) $datePublished);
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
     * @param string $pubIdType One of the NLM pub-id-type values
     * @return string|null
     */
    public function getPubId($pubIdType) {
        $storedPubId = $this->getStoredPubId($pubIdType);
        if ($storedPubId !== null && $storedPubId !== '') {
            return $storedPubId;
        }
        
        return IssuePubIdService::getPubId($this, $pubIdType);
    }

    /**
     * Get stored public ID of the issue.
     * @param string $pubIdType
     * @return mixed
     */
    public function getStoredPubId($pubIdType) {
        return $this->getData('pub-id::' . $pubIdType);
    }

    /**
     * Set stored public ID of the issue.
     * @param string $pubIdType
     * @param mixed $pubId
     */
    public function setStoredPubId($pubIdType, $pubId) {
        return $this->setData('pub-id::' . $pubIdType, (string) $pubId);
    }

    /**
     * Return the "best" issue ID -- If a public issue ID is set,
     * use it; otherwise use the internal issue Id.
     * REFACTORED: Removed DAORegistry coupling. 
     * @param mixed $journal (Optional)
     * @return string
     */
    public function getBestIssueId($journal = null) {
        if ($journal && $journal->getSetting('enablePublicIssueId')) {
            $publicIssueId = $this->getPubId('publisher-id');
            if (!empty($publicIssueId)) {
                return (string) $publicIssueId;
            }
        }

        // If $journal is null OR feature is Off, use internal numeric ID
        return (string) $this->getId();
    }

    /**
     * Get number of articles in this issue.
     * REFACTORED: Removed IssueDAO coupling. 
     * Expects value to be set by the Controller/DAO via setData('numArticles').
     * @return int
     */
    public function getNumArticles() {
        $num = $this->getData('numArticles');
        return $num !== null ? (int) $num : 0;
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
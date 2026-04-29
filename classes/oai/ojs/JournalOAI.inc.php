<?php
declare(strict_types=1);

/**
 * @defgroup oai_ojs
 */

/**
 * @file classes/oai/ojs/JournalOAI.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalOAI
 * @ingroup oai_ojs
 * @see OAIDAO
 *
 * @brief OJS-specific OAI interface.
 * Designed to support both a site-wide and journal-specific OAI interface
 * (based on where the request is directed).
 *
 * [WIZDAM EDITION] REFACTOR: PHP 8.1+ Compatibility, Strict Types, Structured Returns
 */

import('lib.pkp.classes.oai.OAI');
import('classes.oai.ojs.OAIDAO');

class JournalOAI extends OAI {
    /** @var Site $site Associated site object */
    public $site;

    /** @var Journal $journal Associated journal object */
    public $journal;

    /** @var int|null $journalId null if no journal */
    public $journalId;

    /** @var OAIDAO $dao DAO for retrieving OAI records/tokens from database */
    public $dao;

    /**
     * Constructor
     */
    public function __construct($config) {
        parent::__construct($config);

        $this->site = Request::getSite();
        $this->journal = Request::getJournal();
        $this->journalId = isset($this->journal) ? $this->journal->getId() : null;
        $this->dao = DAORegistry::getDAO('OAIDAO');
        $this->dao->setOAI($this);
    }

    /**
     * Return a list of ignorable GET parameters.
     * @return array
     */
    public function getNonPathInfoParams() {
        return ['journal', 'page'];
    }

    /**
     * Convert article ID to OAI identifier.
     * @param int $articleId
     * @return string
     */
    public function articleIdToIdentifier($articleId) {
        return 'oai:' . $this->config->repositoryId . ':' . 'article/' . $articleId;
    }

    /**
     * Convert OAI identifier to article ID.
     * @param string $identifier
     * @return int|false
     */
    public function identifierToArticleId($identifier) {
        $prefix = 'oai:' . $this->config->repositoryId . ':' . 'article/';
        if (strstr($identifier, $prefix)) {
            return (int) str_replace($prefix, '', $identifier);
        } else {
            return false;
        }
    }

    /**
     * Get the journal ID and section ID corresponding to a set specifier.
     * @param string $setSpec
     * @param int|null $journalId
     * @return array [journalId, sectionId]
     */
    public function setSpecToSectionId($setSpec, $journalId = null) {
        $tmpArray = explode(':', $setSpec);
        if (count($tmpArray) == 1) {
            [$journalSpec] = $tmpArray;
            $journalSpec = urldecode($journalSpec);
            $sectionSpec = null;
        } elseif (count($tmpArray) == 2) {
            [$journalSpec, $sectionSpec] = $tmpArray;
            $journalSpec = urldecode($journalSpec);
            $sectionSpec = urldecode($sectionSpec);
        } else {
            return [0, 0];
        }
        return $this->dao->getSetJournalSectionId($journalSpec, $sectionSpec, $this->journalId);
    }


    //
    // OAI interface functions
    //

    /**
     * @see OAI#repositoryInfo
     * @return OAIRepository
     */
    public function repositoryInfo() {
        $info = new OAIRepository();

        if (isset($this->journal)) {
            $info->repositoryName = $this->journal->getLocalizedTitle();
            $info->adminEmail = $this->journal->getSetting('contactEmail');
        } else {
            $info->repositoryName = $this->site->getLocalizedTitle();
            $info->adminEmail = $this->site->getLocalizedContactEmail();
        }

        $info->sampleIdentifier = $this->articleIdToIdentifier(1);
        $info->earliestDatestamp = $this->dao->getEarliestDatestamp([$this->journalId]);

        // [CUSTOM FORK FEATURE] Wizdam Toolkit Branding
        $info->toolkitTitle = 'Wizdam Publishing System';
        $versionDao = DAORegistry::getDAO('VersionDAO');
        $currentVersion = $versionDao->getCurrentVersion();
        $info->toolkitVersion = $currentVersion->getVersionString();
        $info->toolkitURL = 'https://wizdam.sangia.org/';

        return $info;
    }

    /**
     * @see OAI#validIdentifier
     * @param string $identifier
     * @return bool
     */
    public function validIdentifier($identifier) {
        return $this->identifierToArticleId($identifier) !== false;
    }

    /**
     * @see OAI#identifierExists
     * @param string $identifier
     * @return bool
     */
    public function identifierExists($identifier) {
        $recordExists = false;
        $articleId = $this->identifierToArticleId($identifier);
        if ($articleId) {
            $recordExists = $this->dao->recordExists($articleId, [$this->journalId]);
        }
        return $recordExists;
    }

    /**
     * @see OAI#record
     * @param string $identifier
     * @return OAIRecord|false
     */
    public function record($identifier) {
        $articleId = $this->identifierToArticleId($identifier);
        if ($articleId) {
            $record = $this->dao->getRecord($articleId, [$this->journalId]);
        }
        if (!isset($record)) {
            $record = false;
        }
        return $record;
    }

    /**
     * @see OAI#records
     * [MODERNIZED] Return structured array ['records' => ..., 'total' => ...]
     * @return array
     */
    public function records($metadataPrefix, $from, $until, $set, $offset, $limit) {
        $result = ['records' => [], 'total' => 0];
        
        // Hook now passes the result array container instead of individual references
        // [Wizdam] Removed & before $result (Object/Array passed by identifier in PHP 7+)
        if (!HookRegistry::dispatch('JournalOAI::records', [$this, $from, $until, $set, $offset, $limit, &$result])) {
            $sectionId = null;
            if (isset($set)) {
                [$journalId, $sectionId] = $this->setSpecToSectionId($set);
            } else {
                $journalId = $this->journalId;
            }
            
            // Call DAO (Expects DAO to be updated to return array)
            $result = $this->dao->getRecords([$journalId, $sectionId], $from, $until, $set, $offset, $limit);
        }
        return $result;
    }

    /**
     * @see OAI#identifiers
     * [MODERNIZED] Return structured array ['records' => ..., 'total' => ...]
     * @return array
     */
    public function identifiers($metadataPrefix, $from, $until, $set, $offset, $limit) {
        $result = ['records' => [], 'total' => 0];
        
        if (!HookRegistry::dispatch('JournalOAI::identifiers', [$this, $from, $until, $set, $offset, $limit, &$result])) {
            $sectionId = null;
            if (isset($set)) {
                [$journalId, $sectionId] = $this->setSpecToSectionId($set);
            } else {
                $journalId = $this->journalId;
            }
            
            // Call DAO (Expects DAO to be updated to return array)
            $result = $this->dao->getIdentifiers([$journalId, $sectionId], $from, $until, $set, $offset, $limit);
        }
        return $result;
    }

    /**
     * @see OAI#sets
     * [MODERNIZED] Return structured array ['data' => ..., 'total' => ...]
     * @return array
     */
    public function sets($offset, $limit) {
        $result = ['data' => [], 'total' => 0];
        
        if (!HookRegistry::dispatch('JournalOAI::sets', [$this, $offset, $limit, &$result])) {
            // Call DAO (Expects DAO to be updated to return array)
            $result = $this->dao->getJournalSets($this->journalId, $offset, $limit);
        }
        return $result;
    }

    /**
     * @see OAI#resumptionToken
     * @param string $tokenId
     * @return OAIResumptionToken|false
     */
    public function resumptionToken($tokenId) {
        $this->dao->clearTokens();
        $token = $this->dao->getToken($tokenId);
        if (!isset($token)) {
            $token = false;
        }
        return $token;
    }

    /**
     * @see OAI#saveResumptionToken
     * @param int $offset
     * @param array $params
     * @return OAIResumptionToken
     */
    public function saveResumptionToken($offset, $params) {
        $token = new OAIResumptionToken(null, $offset, $params, time() + $this->config->tokenLifetime);
        $this->dao->insertToken($token);
        return $token;
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/journal/JournalDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class JournalDAO
 * @ingroup journal
 * @see Journal
 *
 * @brief Operations for retrieving and modifying Journal objects.
 * [WIZDAM EDITION] PHP 7.4+ Compatible & Cleaned References
 */

import ('classes.journal.Journal');
import('lib.pkp.classes.metadata.MetadataTypeDescription');

define('JOURNAL_FIELD_TITLE', 1);
define('JOURNAL_FIELD_SEQUENCE', 2);

class JournalDAO extends DAO {
    
    /**
     * Constructor.
     * [MODERNISASI] Native Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function JournalDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::JournalDAO(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Retrieve a journal by ID.
     * [MODERNISASI] Removed & reference
     * @param $journalId int
     * @return Journal
     */
    public function getById($journalId) {
        $result = $this->retrieve(
            'SELECT * FROM journals WHERE journal_id = ?',
            (int) $journalId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnJournalFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Deprecated. @see JournalDAO::getById
     */
    public function getJournal($journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getById($journalId);
    }

    /**
     * Retrieve a journal by path.
     * [MODERNISASI] Removed & reference
     * @param $path string
     * @return Journal
     */
    public function getJournalByPath($path) {
        $returner = null;
        $result = $this->retrieve(
            'SELECT * FROM journals WHERE path = ?', $path
        );

        if ($result->RecordCount() != 0) {
            $returner = $this->_returnJournalFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Internal function to return a Journal object from a row.
     * [MODERNISASI] Factory method - Removed &
     * @param $row array
     * @return Journal
     */
    public function _returnJournalFromRow($row) {
        $journal = new Journal();
        $journal->setId($row['journal_id']);
        $journal->setPath($row['path']);
        $journal->setSequence($row['seq']);
        $journal->setEnabled($row['enabled']);
        $journal->setPrimaryLocale($row['primary_locale']);

        HookRegistry::dispatch('JournalDAO::_returnJournalFromRow', array(&$journal, &$row));

        return $journal;
    }

    /**
     * Insert a new journal.
     * @param $journal Journal
     */
    public function insertJournal($journal) {
        $this->update(
            'INSERT INTO journals
                (path, seq, enabled, primary_locale)
                VALUES
                (?, ?, ?, ?)',
            array(
                $journal->getPath(),
                $journal->getSequence() == null ? 0 : $journal->getSequence(),
                $journal->getEnabled() ? 1 : 0,
                $journal->getPrimaryLocale()
            )
        );

        $journal->setId($this->getInsertJournalId());
        return $journal->getId();
    }

    /**
     * Update an existing journal.
     * @param $journal Journal
     */
    public function updateJournal($journal) {
        return $this->update(
            'UPDATE journals
                SET
                    path = ?,
                    seq = ?,
                    enabled = ?,
                    primary_locale = ?
                WHERE journal_id = ?',
            array(
                $journal->getPath(),
                $journal->getSequence(),
                $journal->getEnabled() ? 1 : 0,
                $journal->getPrimaryLocale(),
                $journal->getId()
            )
        );
    }

    /**
     * Delete a journal, INCLUDING ALL DEPENDENT ITEMS.
     * @param $journal Journal
     */
    public function deleteJournal($journal) {
        return $this->deleteJournalById($journal->getId());
    }

    /**
     * Delete a journal by ID, INCLUDING ALL DEPENDENT ITEMS.
     * @param $journalId int
     */
    public function deleteJournalById($journalId) {
        if (HookRegistry::dispatch('JournalDAO::deleteJournalById', array(&$this, &$journalId))) return;

        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalSettingsDao->deleteSettingsByJournal($journalId);

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sectionDao->deleteSectionsByJournal($journalId);

        $issueDao = DAORegistry::getDAO('IssueDAO');
        $issueDao->deleteIssuesByJournal($journalId);

        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplateDao->deleteEmailTemplatesByJournal($journalId);

        $rtDao = DAORegistry::getDAO('RTDAO');
        $rtDao->deleteVersionsByJournal($journalId);

        $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        $subscriptionDao->deleteSubscriptionsByJournal($journalId);
        $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        $subscriptionDao->deleteSubscriptionsByJournal($journalId);

        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionTypeDao->deleteSubscriptionTypesByJournal($journalId);

        $giftDao = DAORegistry::getDAO('GiftDAO');
        $giftDao->deleteGiftsByAssocId(ASSOC_TYPE_JOURNAL, $journalId);

        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        $announcementDao->deleteByAssoc(ASSOC_TYPE_JOURNAL, $journalId);

        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
        $announcementTypeDao->deleteByAssoc(ASSOC_TYPE_JOURNAL, $journalId);

        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $articleDao->deleteArticlesByJournalId($journalId);

        $roleDao = DAORegistry::getDAO('RoleDAO');
        $roleDao->deleteRoleByJournalId($journalId);

        $groupDao = DAORegistry::getDAO('GroupDAO');
        $groupDao->deleteGroupsByAssocId(ASSOC_TYPE_JOURNAL, $journalId);

        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
        $pluginSettingsDao->deleteSettingsByJournalId($journalId);

        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        $reviewFormDao->deleteByAssocId(ASSOC_TYPE_JOURNAL, $journalId);

        return $this->update(
            'DELETE FROM journals WHERE journal_id = ?', $journalId
        );
    }

    /**
     * Retrieve all journals.
     * [MODERNISASI] Removed & reference
	 * @param $enabledOnly boolean True iff only enabled jourals wanted
	 * @param $rangeInfo object optional
	 * @param $sortBy JOURNAL_FIELD_... optional sorting parameter
	 * @param $searchField JOURNAL_FIELD_... optional filter parameter
	 * @param $searchMatch string 'is', 'contains', 'startsWith' optional
	 * @param $search string optional
	 * @return DAOResultFactory containing matching journals
	 */
    public function getJournals($enabledOnly = false, $rangeInfo = null, $sortBy = JOURNAL_FIELD_SEQUENCE, $searchField = null, $searchMatch = null, $search = null) {
        $joinSql = $whereSql = $orderBySql = '';
        $params = array();
        $needTitleJoin = false;

        // Handle sort conditions
        switch ($sortBy) {
            case JOURNAL_FIELD_TITLE:
                $needTitleJoin = true;
                $orderBySql = 'COALESCE(jsl.setting_value, jsl.setting_name)';
                break;
            case JOURNAL_FIELD_SEQUENCE:
                $orderBySql = 'j.seq';
                break;
        }

        // Handle search conditions
        switch ($searchField) {
            case JOURNAL_FIELD_TITLE:
                $needTitleJoin = true;
                $whereSql .= ($whereSql?' AND ':'') . ' COALESCE(jsl.setting_value, jsl.setting_name) ';
                switch ($searchMatch) {
                    case 'is':
                        $whereSql .= ' = ?';
                        $params[] = $search;
                        break;
                    case 'contains':
                        $whereSql .= ' LIKE ?';
                        $params[] = "%search%";
                        break;
                    default: // $searchMatch === 'startsWith'
                        $whereSql .= ' LIKE ?';
                        $params[] = "$search%";
                        break;
                }
                break;
        }

        // If we need to join on the journal title (for sort or filter),
        // include it.
        if ($needTitleJoin) {
            $joinSql .= ' LEFT JOIN journal_settings jspl ON (jspl.setting_name = ? AND jspl.locale = ? AND jspl.journal_id = j.journal_id) LEFT JOIN journal_settings jsl ON (jsl.setting_name = ? AND jsl.locale = ? AND jsl.journal_id = j.journal_id)';
            $params = array_merge(
                array(
                    'title',
                    AppLocale::getPrimaryLocale(),
                    'title',
                    AppLocale::getLocale()
                ),
                $params
            );
        }

        // Handle filtering conditions
        if ($enabledOnly) $whereSql .= ($whereSql?'AND ':'') . 'j.enabled=1 ';

        // Clean up SQL strings
        if ($whereSql) $whereSql = "WHERE $whereSql";
        if ($orderBySql) $orderBySql = "ORDER BY $orderBySql";
        
        $result = $this->retrieveRange(
            "SELECT    j.*
            FROM    journals j
                $joinSql
                $whereSql
                $orderBySql",
            $params, $rangeInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnJournalFromRow');
        return $returner;
    }

    /**
     * Retrieve all enabled journals
     * [MODERNISASI] Removed & reference
     * @return array Journals ordered by sequence
     */
    public function getEnabledJournals($rangeInfo = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getJournals(true, $rangeInfo);
    }

    /**
     * Retrieve the IDs and titles of all journals in an associative array.
     * [MODERNISASI] Removed & reference
     * @return array
     */
    public function getJournalTitles($enabledOnly = false) {
        $journals = array();

        $journalIterator = $this->getJournals($enabledOnly);
        while ($journal = $journalIterator->next()) {
            $journals[$journal->getId()] = $journal->getLocalizedTitle();
        }
        unset($journalIterator);

        return $journals;
    }

    /**
     * Retrieve enabled journal IDs and titles in an associative array
     * [MODERNISASI] Removed & reference
     * @return array
     */
    public function getEnabledJournalTitles() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getJournalTitles(true);
    }

    /**
     * Check if a journal exists with a specified path.
     * @param $path the path of the journal
     * @return boolean
     */
    public function journalExistsByPath($path) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM journals WHERE path = ?', $path
        );
        $returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

        $result->Close();
        return $returner;
    }

    /**
     * Delete the public IDs of all publishing objects in a journal.
     * 
	 * @param $journalId int
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 */
    public function deleteAllPubIds($journalId, $pubIdType) {
        $pubObjectDaos = array('IssueDAO', 'ArticleDAO', 'ArticleGalleyDAO', 'SuppFileDAO');
        foreach($pubObjectDaos as $daoName) {
            $dao = DAORegistry::getDAO($daoName);
            $dao->deleteAllPubIds($journalId, $pubIdType);
        }
    }

    /**
     * Check whether the given public ID exists for any publishing
     * object in a journal.
     * 
	 * @param $journalId int
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @param $pubId string
	 * @param $assocType int The object type of an object to be excluded from
	 *  the search. Identified by one of the ASSOC_TYPE_* constants.
	 * @param $assocId int The id of an object to be excluded from the search.
	 * @return boolean
     */
    public function anyPubIdExists($journalId, $pubIdType, $pubId, $assocType = ASSOC_TYPE_ANY, $assocId = 0) {
        $pubObjectDaos = array(
            ASSOC_TYPE_ISSUE => 'IssueDAO',
            ASSOC_TYPE_ARTICLE => 'ArticleDAO',
            ASSOC_TYPE_GALLEY => 'ArticleGalleyDAO',
            ASSOC_TYPE_ISSUE_GALLEY => 'IssueGalleyDAO',
            ASSOC_TYPE_SUPP_FILE => 'SuppFileDAO'
        );
        foreach($pubObjectDaos as $daoAssocType => $daoName) {
            $dao = DAORegistry::getDAO($daoName);
            if ($assocType == $daoAssocType) {
                $excludedId = $assocId;
            } else {
                $excludedId = 0;
            }
            if ($dao->pubIdExists($pubIdType, $pubId, $excludedId, $journalId)) return true;
        }
        return false;
    }

    /**
     * Sequentially renumber journals in their sequence order.
     */
    public function resequenceJournals() {
        $result = $this->retrieve(
            'SELECT journal_id FROM journals ORDER BY seq'
        );

        for ($i=1; !$result->EOF; $i++) {
            list($journalId) = $result->fields;
            $this->update(
                'UPDATE journals SET seq = ? WHERE journal_id = ?',
                array(
                    $i,
                    $journalId
                )
            );

            $result->moveNext();
        }

        $result->close();
    }

    /**
     * Get the ID of the last inserted journal.
     * @return int
     */
    public function getInsertJournalId() {
        return $this->getInsertId('journals', 'journal_id');
    }

    /**
     * Get journals by setting.
	 * @param $settingName string
	 * @param $settingValue mixed
	 * @param $contextId int
	 * @return DAOResultFactory
	 */
    public function getBySetting($settingName, $settingValue, $contextId = null) {
        $params = array($settingName, $settingValue);
        if ($contextId) $params[] = $contextId;

        $result = $this->retrieve(
            'SELECT * FROM journals AS c
            LEFT JOIN journal_settings AS cs
            ON c.journal_id = cs.journal_id'.
            ' WHERE cs.setting_name = ? AND cs.setting_value = ?' .
            ($contextId?' AND c.journal_id = ?':''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnJournalFromRow');
    }
}

?>
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
 */

import ('classes.journal.Journal');
import('lib.pkp.classes.metadata.MetadataTypeDescription');

define('JOURNAL_FIELD_TITLE', 1);
define('JOURNAL_FIELD_SEQUENCE', 2);

class JournalDAO extends DAO {
    
    /**
     * Constructor.
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
     * @param int $journalId
     * @return Journal|null The Journal object, or null if not found.
     */
    public function getById($journalId) {
        $result = $this->retrieve(
            'SELECT * FROM journals WHERE journal_id = ?',
            [(int) $journalId] 
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnJournalFromRow($result->GetRowAssoc(false));
        }
        $result->Close();

        return $returner;
    }

    /**
     * [DEPRICATED] Deprecated function. @see JournalDAO::getById
     * @param int $journalId
     * @return Journal|null
     */
    public function getJournal($journalId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getById($journalId);
    }

    /**
     * Retrieve a journal by path.
     * @param string $path
     * @return Journal|null The Journal object, or null if not found.
     */
    public function getJournalByPath($path) {
        $returner = null;
        $result = $this->retrieve(
            'SELECT * FROM journals WHERE path = ?', 
            [(string) $path] 
        );
        
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnJournalFromRow($result->GetRowAssoc(false));
        }
        $result->Close();

        return $returner;
    }

    /**
     * Internal function to return a Journal object from a row.
     * @param array $row
     * @return Journal
     */
    public function _returnJournalFromRow($row) {
        $journal = new Journal();
        $journal->setId($row['journal_id']);
        $journal->setPath($row['path']);
        $journal->setSequence($row['seq']);
        $journal->setEnabled($row['enabled']);
        $journal->setPrimaryLocale($row['primary_locale']);

        HookRegistry::dispatch('JournalDAO::_returnJournalFromRow', [&$journal, &$row]);

        return $journal;
    }

    /**
     * Insert a new journal.
     * @param Journal $journal
     * @return int The new journal ID
     */
    public function insertJournal($journal) {
        $this->update(
            'INSERT INTO journals
                (path, seq, enabled, primary_locale)
                VALUES
                (?, ?, ?, ?)',
            [
                (string) $journal->getPath(),
                (int) ($journal->getSequence() ?? 0), // [WIZDAM] Null coalescing & casting
                (int) $journal->getEnabled(),
                (string) $journal->getPrimaryLocale()
            ]
        );
        $journal->setId($this->getInsertJournalId());

        return $journal->getId();
    }

    /**
     * Update an existing journal.
     * @param Journal $journal
     * @return bool
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
            [
                (string) $journal->getPath(),
                (int) ($journal->getSequence() ?? 0),
                (int) $journal->getEnabled(),
                (string) $journal->getPrimaryLocale(),
                (int) $journal->getId()
            ]
        );
    }

    /**
     * Delete a journal, INCLUDING ALL DEPENDENT ITEMS.
     * @param Journal $journal
     * @return bool
     */
    public function deleteJournal($journal) {
        return $this->deleteJournalById($journal->getId());
    }

    /**
     * Delete a journal by ID, INCLUDING ALL DEPENDENT ITEMS.
     * @param int $journalId
     * @return bool
     */
    public function deleteJournalById($journalId) {
        if (HookRegistry::dispatch('JournalDAO::deleteJournalById', [&$this, &$journalId])) return false;

        $journalId = (int) $journalId;

        /** @var JournalSettingsDAO $journalSettingsDao */
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        if ($journalSettingsDao) $journalSettingsDao->deleteSettingsByJournal($journalId);

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        if ($sectionDao) $sectionDao->deleteSectionsByJournal($journalId);

        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        if ($issueDao) $issueDao->deleteIssuesByJournal($journalId);

        /** @var EmailTemplateDAO $emailTemplateDao */
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        if ($emailTemplateDao) $emailTemplateDao->deleteEmailTemplatesByJournal($journalId);

        /** @var RTDAO $rtDao */
        $rtDao = DAORegistry::getDAO('RTDAO');
        if ($rtDao) $rtDao->deleteVersionsByJournal($journalId);

        /** @var IndividualSubscriptionDAO $subscriptionDao */
        $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        if ($subscriptionDao) $subscriptionDao->deleteSubscriptionsByJournal($journalId);
        
        /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
        $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        if ($institutionalSubscriptionDao) $institutionalSubscriptionDao->deleteSubscriptionsByJournal($journalId);

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        if ($subscriptionTypeDao) $subscriptionTypeDao->deleteSubscriptionTypesByJournal($journalId);

        /** @var GiftDAO $giftDao */
        $giftDao = DAORegistry::getDAO('GiftDAO');
        if ($giftDao) $giftDao->deleteGiftsByAssocId(ASSOC_TYPE_JOURNAL, $journalId);

        /** @var AnnouncementDAO $announcementDao */
        $announcementDao = DAORegistry::getDAO('AnnouncementDAO');
        if ($announcementDao) $announcementDao->deleteByAssoc(ASSOC_TYPE_JOURNAL, $journalId);

        /** @var AnnouncementTypeDAO $announcementTypeDao */
        $announcementTypeDao = DAORegistry::getDAO('AnnouncementTypeDAO');
        if ($announcementTypeDao) $announcementTypeDao->deleteByAssoc(ASSOC_TYPE_JOURNAL, $journalId);

        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        if ($articleDao) $articleDao->deleteArticlesByJournalId($journalId);

        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        if ($roleDao) $roleDao->deleteRoleByJournalId($journalId);

        /** @var GroupDAO $groupDao */
        $groupDao = DAORegistry::getDAO('GroupDAO');
        if ($groupDao) $groupDao->deleteGroupsByAssocId(ASSOC_TYPE_JOURNAL, $journalId);

        /** @var PluginSettingsDAO $pluginSettingsDao */
        $pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
        if ($pluginSettingsDao) $pluginSettingsDao->deleteSettingsByJournalId($journalId);

        /** @var ReviewFormDAO $reviewFormDao */
        $reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
        if ($reviewFormDao) $reviewFormDao->deleteByAssocId(ASSOC_TYPE_JOURNAL, $journalId);

        return $this->update(
            'DELETE FROM journals WHERE journal_id = ?', 
            [$journalId]
        );
    }

    /**
     * Retrieve all journals.
	 * @param bool $enabledOnly True iff only enabled journals wanted
     * @param object|null $rangeInfo Optional DBResultRange
     * @param int $sortBy JOURNAL_FIELD_... optional sorting parameter
     * @param int|null $searchField JOURNAL_FIELD_... optional filter parameter
     * @param string|null $searchMatch 'is', 'contains', 'startsWith' optional
     * @param string|null $search optional
     * @return DAOResultFactory containing matching journals
	 */
    public function getJournals($enabledOnly = false, $rangeInfo = null, $sortBy = JOURNAL_FIELD_SEQUENCE, $searchField = null, $searchMatch = null, $search = null) {
        $joinSql = $whereSql = $orderBySql = '';
        $params = [];
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
                $whereSql .= ($whereSql ? ' AND ' : '') . ' COALESCE(jsl.setting_value, jsl.setting_name) ';
                switch ($searchMatch) {
                    case 'is':
                        $whereSql .= ' = ?';
                        $params[] = (string) $search;
                        break;
                    case 'contains':
                        $whereSql .= ' LIKE ?';
                        $params[] = "%" . $search . "%"; 
                        break;
                    default: // $searchMatch === 'startsWith'
                        $whereSql .= ' LIKE ?';
                        $params[] = $search . "%";
                        break;
                }
                break;
        }

        // If we need to join on the journal title (for sort or filter), include it.
        if ($needTitleJoin) {
            $joinSql .= ' LEFT JOIN journal_settings jspl ON (jspl.setting_name = ? AND jspl.locale = ? AND jspl.journal_id = j.journal_id) LEFT JOIN journal_settings jsl ON (jsl.setting_name = ? AND jsl.locale = ? AND jsl.journal_id = j.journal_id)';
            $params = array_merge(
                [
                    'title',
                    (string) AppLocale::getPrimaryLocale(),
                    'title',
                    (string) AppLocale::getLocale()
                ],
                $params
            );
        }

        // Handle filtering conditions
        if ($enabledOnly) $whereSql .= ($whereSql ? ' AND ' : '') . 'j.enabled = 1 ';

        // Clean up SQL strings
        if ($whereSql) $whereSql = "WHERE $whereSql";
        if ($orderBySql) $orderBySql = "ORDER BY $orderBySql";
        
        $result = $this->retrieveRange(
            "SELECT j.* FROM journals j $joinSql $whereSql $orderBySql",
            $params, 
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnJournalFromRow');
    }

    /**
     * [DEPRICATED] Retrieve all enabled journals
     * @param object|null $rangeInfo
     * @return DAOResultFactory
     */
    public function getEnabledJournals($rangeInfo = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getJournals(true, $rangeInfo);
    }

    /**
     * Retrieve the IDs and titles of all journals in an associative array.
     * @param bool $enabledOnly
     * @return array
     */
    public function getJournalTitles($enabledOnly = false) {
        $journals = [];
        $journalIterator = $this->getJournals($enabledOnly);
        
        if ($journalIterator) {
            while ($journal = $journalIterator->next()) {
                $journals[$journal->getId()] = $journal->getLocalizedTitle();
            }
        }

        return $journals;
    }

    /**
     * [DEPRICATED] Retrieve enabled journal IDs and titles in an associative array
     * @return array
     */
    public function getEnabledJournalTitles() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getJournalTitles(true);
    }

    /**
     * Check if a journal exists with a specified path.
     * @param mixed $path the path of the journal
     * @return boolean
     */
    public function journalExistsByPath($path) {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM journals WHERE path = ?', 
            [(string) $path] 
        );

        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = isset($fields[0]) && (int)$fields[0] > 0;
        $result->Close();

        return $returner;
    }

    /**
     * Delete the public IDs of all publishing objects in a journal.
	 * @param int $journalId
     * @param string $pubIdType One of the NLM pub-id-type values or 'other::something'
     * @return void
	 */
    public function deleteAllPubIds($journalId, $pubIdType) {
        $pubObjectDaos = ['IssueDAO', 'ArticleDAO', 'ArticleGalleyDAO', 'SuppFileDAO'];
        foreach ($pubObjectDaos as $daoName) {
            $dao = DAORegistry::getDAO($daoName);
            if ($dao && method_exists($dao, 'deleteAllPubIds')) {
                $dao->deleteAllPubIds((int) $journalId, (string) $pubIdType);
            }
        }
    }

    /**
     * Check whether the given public ID exists for any publishing object in a journal.
     * @param int $journalId
     * @param string $pubIdType One of the NLM pub-id-type values or 'other::something'
     * @param string $pubId
     * @param int $assocType The object type of an object to be excluded from the search.
     * @param int $assocId The id of an object to be excluded from the search.
     * @return bool
     */
    public function anyPubIdExists($journalId, $pubIdType, $pubId, $assocType = ASSOC_TYPE_ANY, $assocId = 0) {
        $pubObjectDaos = [
            ASSOC_TYPE_ISSUE => 'IssueDAO',
            ASSOC_TYPE_ARTICLE => 'ArticleDAO',
            ASSOC_TYPE_GALLEY => 'ArticleGalleyDAO',
            ASSOC_TYPE_ISSUE_GALLEY => 'IssueGalleyDAO',
            ASSOC_TYPE_SUPP_FILE => 'SuppFileDAO'
        ];
        
        foreach ($pubObjectDaos as $daoAssocType => $daoName) {
            $dao = DAORegistry::getDAO($daoName);
            if ($dao && method_exists($dao, 'pubIdExists')) {
                $excludedId = ($assocType == $daoAssocType) ? (int) $assocId : 0;
                if ($dao->pubIdExists((string) $pubIdType, (string) $pubId, $excludedId, (int) $journalId)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Sequentially renumber journals in their sequence order.
     * @return void
     */
    public function resequenceJournals() {
        $result = $this->retrieve(
            'SELECT journal_id FROM journals ORDER BY seq'
        );
        for ($i = 1; !$result->EOF; $i++) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            $journalId = (int) ($fields[0] ?? 0);

            $this->update(
                'UPDATE journals SET seq = ? WHERE journal_id = ?',
                [(int) $i, $journalId]
            );
            $result->moveNext();
        }
        if ($result) $result->close();
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
	 * @param string $settingName
     * @param mixed $settingValue
     * @param int|null $contextId
     * @return DAOResultFactory
	 */
    public function getBySetting($settingName, $settingValue, $contextId = null) {
        $params = [(string) $settingName, $settingValue];
        if ($contextId !== null) $params[] = (int) $contextId;

        $result = $this->retrieve(
            'SELECT * FROM journals AS c
            LEFT JOIN journal_settings AS cs ON c.journal_id = cs.journal_id
            WHERE cs.setting_name = ? AND cs.setting_value = ?' .
            ($contextId !== null ? ' AND c.journal_id = ?' : ''),
            $params
        );

        return new DAOResultFactory($result, $this, '_returnJournalFromRow');
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/issue/IssueDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IssueDAO
 * @ingroup issue
 * @see Issue
 *
 * @brief Operations for retrieving and modifying Issue objects.
 */

import('classes.issue.Issue');

class IssueDAO extends DAO {
    
    /** @var array<string, object> Cache storage */
    protected array $caches = [];

    /**
     * Cache Miss Callback
     * @param object $cache
     * @param string|int $id
     * @return Issue|null
     */
    public function _cacheMiss($cache, $id): ?Issue {
        if ($cache->getCacheId() === 'current') {
            $issue = $this->getCurrentIssue((int) $id, false);
        } else {
            $issue = $this->getIssueByBestIssueId((string) $id, null, false);
        }
        $cache->setCache($id, $issue);
        return $issue;
    }

    /**
     * Get Cache object
     * @param string $cacheId
     * @return object
     */
    protected function _getCache(string $cacheId): object {
        if (!isset($this->caches[$cacheId])) {
            $cacheManager = CacheManager::getManager();
            $this->caches[$cacheId] = $cacheManager->getObjectCache('issues', $cacheId, [$this, '_cacheMiss']);
        }
        return $this->caches[$cacheId];
    }

    /**
     * Retrieve Issue by issue id
     * @param int|string $issueId
     * @param int|string|null $journalId optional
     * @param bool $useCache optional
     * @return Issue|null
     */
    public function getIssueById(int|string $issueId, int|string|null $journalId = null, bool $useCache = false): ?Issue {
        $issueIdInt = (int) $issueId;
        $journalIdInt = $journalId !== null ? (int) $journalId : null;

        if ($useCache) {
            $cache = $this->_getCache('issues');
            $returner = $cache->get($issueIdInt);
            if ($returner && $journalIdInt !== null && $journalIdInt !== $returner->getJournalId()) {
                $returner = null;
            }
            return $returner;
        }

        if ($journalIdInt !== null) {
            $result = $this->retrieve(
                'SELECT i.* FROM issues i WHERE issue_id = ? AND journal_id = ?',
                [$issueIdInt, $journalIdInt]
            );
        } else {
            $result = $this->retrieve(
                'SELECT i.* FROM issues i WHERE issue_id = ?', 
                [$issueIdInt]
            );
        }

        $issue = null;
        if (!$result->EOF) {
            $issue = $this->_returnIssueFromRow($result->GetRowAssoc(false));
        }
        $result->Close();

        return $issue;
    }

    /**
     * Retrieve Issue by public issue id
     * @param string $pubIdType
     * @param string $pubId
     * @param int|string|null $journalId optional
     * @param bool $useCache optional
     * @return Issue|null
     */
    public function getIssueByPubId(string $pubIdType, string $pubId, int|string|null $journalId = null, bool $useCache = false): ?Issue {
        if ($useCache && $pubIdType === 'publisher-id') {
            $cache = $this->_getCache('issues');
            $returner = $cache->get($pubId);
            if ($returner && $journalId !== null && (int) $journalId !== $returner->getJournalId()) {
                $returner = null;
            }
            return $returner;
        }

        $issues = $this->getIssuesBySetting('pub-id::' . $pubIdType, $pubId, $journalId);
        if (empty($issues)) {
            return null;
        }
        
        return $issues[0];
    }

    /**
     * Find issues by querying issue settings.
     * @param string $settingName
     * @param mixed $settingValue
     * @param int|string|null $journalId optional
     * @return Issue[]
     */
    public function getIssuesBySetting(string $settingName, mixed $settingValue, int|string|null $journalId = null): array {
        $params = [$settingName];
        $sql = 'SELECT i.* FROM issues i ';
        
        if ($settingValue === null) {
            $sql .= 'LEFT JOIN issue_settings ist ON i.issue_id = ist.issue_id AND ist.setting_name = ?
                     WHERE (ist.setting_value IS NULL OR ist.setting_value = \'\')';
        } else {
            $params[] = $settingValue;
            $sql .= 'INNER JOIN issue_settings ist ON i.issue_id = ist.issue_id
                     WHERE ist.setting_name = ? AND ist.setting_value = ?';
        }
        
        if ($journalId !== null) {
            $params[] = (int) $journalId;
            $sql .= ' AND i.journal_id = ?';
        }
        
        $sql .= ' ORDER BY i.issue_id ASC';
        $result = $this->retrieve($sql, $params);

        $issues = [];
        while (!$result->EOF) {
            $issues[] = $this->_returnIssueFromRow($result->GetRowAssoc(false));
            $result->moveNext();
        }
        $result->Close();

        return $issues;
    }

    /**
     * Retrieve Issue by some combination of volume, number, and year
     * @param int|string $journalId
     * @param int|string|null $volume
     * @param string|null $number
     * @param int|string|null $year
     * @return DAOResultFactory
     */
    public function getPublishedIssuesByNumber(int|string $journalId, int|string|null $volume = null, ?string $number = null, int|string|null $year = null): DAOResultFactory {
        $journalIdInt = (int) $journalId;
        $sql = 'SELECT i.* FROM issues i WHERE i.published = 1 AND i.journal_id = ?';
        $params = [$journalIdInt];

        if ($volume !== null) {
            $sql .= ' AND i.volume = ?';
            $params[] = (int) $volume;
        }
        if ($number !== null) {
            $sql .= ' AND i.number = ?';
            $params[] = $number;
        }
        if ($year !== null) {
            $sql .= ' AND i.year = ?';
            $params[] = (int) $year;
        }

        $result = $this->retrieve($sql, $params);
        return new DAOResultFactory($result, $this, '_returnIssueFromRow');
    }

    /**
     * Retrieve Issue by "best" issue id
     * @param int|string $issueId
     * @param int|string|null $journalId optional
     * @param bool $useCache optional
     * @return Issue|null
     */
    public function getIssueByBestIssueId(int|string $issueId, int|string|null $journalId = null, bool $useCache = false): ?Issue {
        $issueIdStr = (string) $issueId;
        $issue = $this->getIssueByPubId('publisher-id', $issueIdStr, $journalId, $useCache);
        if ($issue === null && ctype_digit($issueIdStr)) {
            $issue = $this->getIssueById((int) $issueIdStr, $journalId, $useCache);
        }
        return $issue;
    }

    /**
     * Retrieve the last created issue
     * @param int|string $journalId
     * @return Issue|null
     */
    public function getLastCreatedIssue(int|string $journalId): ?Issue {
        $journalIdInt = (int) $journalId;
        $result = $this->retrieveLimit(
            'SELECT i.* FROM issues i WHERE journal_id = ? ORDER BY year DESC, volume DESC, number DESC',
            [$journalIdInt],
            1
        );

        $issue = null;
        if (!$result->EOF) {
            $issue = $this->_returnIssueFromRow($result->GetRowAssoc(false));
        }
        $result->Close();

        return $issue;
    }

    /**
     * Retrieve current issue
     * @param int|string $journalId
     * @param bool $useCache optional
     * @return Issue|null
     */
    public function getCurrentIssue(int|string $journalId, bool $useCache = false): ?Issue {
        $journalIdInt = (int) $journalId;
        if ($useCache) {
            $cache = $this->_getCache('current');
            return $cache->get($journalIdInt);
        }

        $result = $this->retrieve(
            'SELECT i.* FROM issues i WHERE journal_id = ? AND current = 1',
            [$journalIdInt]
        );

        $issue = null;
        if (!$result->EOF) {
            $issue = $this->_returnIssueFromRow($result->GetRowAssoc(false));
        }
        $result->Close();

        return $issue;
    }

    /**
     * Update current issue
     * @param int|string $journalId
     * @param Issue|null $issue
     */
    public function updateCurrentIssue(int|string $journalId, ?Issue $issue = null): void {
        $journalIdInt = (int) $journalId;
        $this->update(
            'UPDATE issues SET current = 0 WHERE journal_id = ? AND current = 1',
            [$journalIdInt]
        );
        if ($issue !== null) {
            $this->updateIssue($issue);
        }
        $this->flushCache();
    }

    /**
     * Change the public ID of an issue.
     * @param int|string $issueId
     * @param string $pubIdType
     * @param string $pubId
     */
    public function changePubId(int|string $issueId, string $pubIdType, string $pubId): void {
        $idFields = ['issue_id', 'locale', 'setting_name'];
        $updateArray = [
            'issue_id' => (int) $issueId,
            'locale' => '',
            'setting_name' => 'pub-id::' . $pubIdType,
            'setting_type' => 'string',
            'setting_value' => (string) $pubId
        ];
        $this->replace('issue_settings', $updateArray, $idFields);
        $this->flushCache();
    }

    /**
     * Creates and returns an issue object from a row
     * @param array<string, mixed> $row
     * @return Issue
     */
    public function _returnIssueFromRow(array $row): Issue {
        $issue = new Issue();
        $issue->setId((int) $row['issue_id']);
        $issue->setJournalId((int) $row['journal_id']);
        $issue->setVolume($row['volume'] !== null ? (int) $row['volume'] : null);
        $issue->setNumber($row['number']);
        $issue->setYear($row['year'] !== null ? (int) $row['year'] : null);
        $issue->setPublished((int) $row['published']);
        $issue->setCurrent((int) $row['current']);
        $issue->setDatePublished($this->datetimeFromDB($row['date_published']));
        $issue->setDateNotified($this->datetimeFromDB($row['date_notified']));
        $issue->setLastModified($this->datetimeFromDB($row['last_modified']));
        $issue->setAccessStatus((int) $row['access_status']);
        $issue->setOpenAccessDate($this->datetimeFromDB($row['open_access_date']));
        $issue->setShowVolume((int) $row['show_volume']);
        $issue->setShowNumber((int) $row['show_number']);
        $issue->setShowYear((int) $row['show_year']);
        $issue->setShowTitle((int) $row['show_title']);
        $issue->setData('numArticles', $this->getNumArticles((int) $row['issue_id']));
        $issue->setStyleFileName($row['style_file_name']);
        $issue->setOriginalStyleFileName($row['original_style_file_name']);

        $this->getDataObjectSettings('issue_settings', 'issue_id', (int) $row['issue_id'], $issue);

        HookRegistry::dispatch('IssueDAO::_returnIssueFromRow', [&$issue, &$row]);

        return $issue;
    }

    /**
     * Get a list of fields for which localized data is supported
     * @return string[]
     */
    public function getLocaleFieldNames(): array {
        return ['title', 'coverPageDescription', 'coverPageAltText', 'showCoverPage', 'hideCoverPageArchives', 'hideCoverPageCover', 'originalFileName', 'fileName', 'width', 'height', 'description'];
    }

    /**
     * Get a list of additional fields that do not have dedicated accessors.
     * @return string[]
     */
    public function getAdditionalFieldNames(): array {
        $additionalFields = parent::getAdditionalFieldNames();
        $additionalFields[] = 'pub-id::publisher-id';
        return $additionalFields;
    }

    /**
     * Update the localized fields for this object.
     * @param Issue $issue
     */
    public function updateLocaleFields(Issue $issue): void {
        $this->updateDataObjectSettings('issue_settings', $issue, [
            'issue_id' => $issue->getId()
        ]);
    }

    /**
     * Inserts a new issue into issues table
     * @param Issue $issue
     * @return int Issue Id
     */
    public function insertIssue(Issue $issue): int {
        $this->update(
            sprintf(
                'INSERT INTO issues
                (journal_id, volume, number, year, published, current, date_published, date_notified, last_modified, access_status, open_access_date, show_volume, show_number, show_year, show_title, style_file_name, original_style_file_name)
                VALUES (?, ?, ?, ?, ?, ?, %s, %s, %s, ?, %s, ?, ?, ?, ?, ?, ?)',
                $this->datetimeToDB($issue->getDatePublished()),
                $this->datetimeToDB($issue->getDateNotified()),
                $this->datetimeToDB($issue->getLastModified()),
                $this->datetimeToDB($issue->getOpenAccessDate())
            ),
            [
                (int) $issue->getJournalId(),
                (int) $issue->getVolume(),
                $issue->getNumber(),
                (int) $issue->getYear(),
                (int) $issue->getPublished(),
                (int) $issue->getCurrent(),
                (int) $issue->getAccessStatus(),
                (int) $issue->getShowVolume(),
                (int) $issue->getShowNumber(),
                (int) $issue->getShowYear(),
                (int) $issue->getShowTitle(),
                $issue->getStyleFileName(),
                $issue->getOriginalStyleFileName()
            ]
        );

        $issue->setId($this->getInsertIssueId());
        $this->updateLocaleFields($issue);

        if ($this->customIssueOrderingExists((int) $issue->getJournalId())) {
            $this->resequenceCustomIssueOrders((int) $issue->getJournalId());
        }

        return $issue->getId();
    }

    /**
     * Get the ID of the last inserted issue.
     * @return int
     */
    public function getInsertIssueId(): int {
        return (int) $this->getInsertId('issues', 'issue_id');
    }

    /**
     * Check if volume, number and year have already been issued
     * @param int|string $journalId
     * @param int|string $volume
     * @param string $number
     * @param int|string $year
     * @param int|string $issueId
     * @return bool
     */
    public function issueExists(int|string $journalId, int|string $volume, string $number, int|string $year, int|string $issueId): bool {
        $result = $this->retrieve(
            'SELECT i.* FROM issues i WHERE journal_id = ? AND volume = ? AND number = ? AND year = ? AND issue_id <> ?',
            [(int) $journalId, (int) $volume, $number, (int) $year, (int) $issueId]
        );
        $returner = !$result->EOF;
        $result->Close();

        return $returner;
    }

    /**
     * Updates an issue
     * @param Issue $issue
     */
    public function updateIssue(Issue $issue): void {
        $issue->stampModified();
        $this->update(
            sprintf(
                'UPDATE issues
                SET journal_id = ?, volume = ?, number = ?, year = ?, published = ?, current = ?,
                    date_published = %s, date_notified = %s, last_modified = %s, open_access_date = %s,
                    access_status = ?, show_volume = ?, show_number = ?, show_year = ?, show_title = ?,
                    style_file_name = ?, original_style_file_name = ?
                WHERE issue_id = ?',
                $this->datetimeToDB($issue->getDatePublished()),
                $this->datetimeToDB($issue->getDateNotified()),
                $this->datetimeToDB($issue->getLastModified()),
                $this->datetimeToDB($issue->getOpenAccessDate())
            ),
            [
                (int) $issue->getJournalId(),
                (int) $issue->getVolume(),
                $issue->getNumber(),
                (int) $issue->getYear(),
                (int) $issue->getPublished(),
                (int) $issue->getCurrent(),
                (int) $issue->getAccessStatus(),
                (int) $issue->getShowVolume(),
                (int) $issue->getShowNumber(),
                (int) $issue->getShowYear(),
                (int) $issue->getShowTitle(),
                $issue->getStyleFileName(),
                $issue->getOriginalStyleFileName(),
                (int) $issue->getId()
            ]
        );

        $this->updateLocaleFields($issue);

        if ($this->customIssueOrderingExists((int) $issue->getJournalId())) {
            $this->resequenceCustomIssueOrders((int) $issue->getJournalId());
        }

        $this->flushCache();
    }

    /**
     * Delete issue. Deletes associated issue galleys, cover pages, and published articles.
     * @param Issue $issue
     */
    public function deleteIssue(Issue $issue): void {
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();

        $fileNames = $issue->getFileName(null);
        if (is_array($fileNames)) {
            foreach ($fileNames as $fileName) {
                if ($fileName !== '') {
                    $publicFileManager->removeJournalFile($issue->getJournalId(), $fileName);
                }
            }
        }
        
        $styleFileName = $issue->getStyleFileName();
        if ($styleFileName !== '') {
            $publicFileManager->removeJournalFile($issue->getJournalId(), $styleFileName);
        }

        $issueId = (int) $issue->getId();
        $journalId = (int) $issue->getJournalId();

        /** @var SectionDAO $sectionDao */
        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sectionDao->deleteCustomSectionOrdering($issueId);

        /** @var IssueGalleyDAO $issueGalleyDao */
        $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
        $issueGalleyDao->deleteGalleysByIssue($issueId);

        /** @var IssueFileDAO $issueFileDao */
        $issueFileDao = DAORegistry::getDAO('IssueFileDAO');
        $issueFileDao->deleteIssueFiles($issueId);

        import('classes.file.IssueFileManager');
        $issueFileManager = new IssueFileManager($issueId);
        $issueFileManager->deleteIssueTree();

        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        $publishedArticleDao->deletePublishedArticlesByIssueId($issueId);

        $this->update('DELETE FROM issue_settings WHERE issue_id = ?', [$issueId]);
        $this->update('DELETE FROM issues WHERE issue_id = ?', [$issueId]);
        $this->update('DELETE FROM custom_issue_orders WHERE issue_id = ?', [$issueId]);
        
        $this->resequenceCustomIssueOrders($journalId);
        $this->flushCache();
    }

    /**
     * Delete issues by journal id. Deletes dependent entities.
     * @param int|string $journalId
     */
    public function deleteIssuesByJournal(int|string $journalId): void {
        $issues = $this->getIssues($journalId);
        while ($issue = $issues->next()) {
            $this->deleteIssue($issue);
        }
    }

    /**
     * Checks if issue exists
     * @param int|string $issueId
     * @param int|string $journalId
     * @return bool
     */
    public function issueIdExists(int|string $issueId, int|string $journalId): bool {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM issues WHERE issue_id = ? AND journal_id = ?',
            [(int) $issueId, (int) $journalId]
        );
        
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = !$result->EOF && isset($fields[0]) && (int) $fields[0] > 0;

        $result->Close();
        return $returner;
    }

    /**
     * Checks if public identifier exists
     * @param string $pubIdType
     * @param string $pubId
     * @param int|string $issueId An ID to be excluded from the search.
     * @param int|string $journalId
     * @return bool
     */
    public function pubIdExists(string $pubIdType, string $pubId, int|string $issueId, int|string $journalId): bool {
        $result = $this->retrieve(
            'SELECT COUNT(*)
             FROM issue_settings ist
             INNER JOIN issues i ON ist.issue_id = i.issue_id
             WHERE ist.setting_name = ? AND ist.setting_value = ? AND i.issue_id <> ? AND i.journal_id = ?',
            [
                'pub-id::' . $pubIdType,
                $pubId,
                (int) $issueId,
                (int) $journalId
            ]
        );
        
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = !$result->EOF && isset($fields[0]) && (int) $fields[0] > 0;
        
        $result->Close();
        return $returner;
    }

    /**
     * Get issue by article id
     * @param int|string $articleId
     * @param int|string|null $journalId optional
     * @return Issue|null
     */
    public function getIssueByArticleId(int|string $articleId, int|string|null $journalId = null): ?Issue {
        $articleIdInt = (int) $articleId;
        $journalIdInt = $journalId !== null ? (int) $journalId : null;

        $sql = 'SELECT i.*
            FROM issues i
            INNER JOIN published_articles pa ON i.issue_id = pa.issue_id
            INNER JOIN articles a ON pa.article_id = a.article_id
            WHERE pa.article_id = ?';
            
        $params = [$articleIdInt];
        
        if ($journalIdInt !== null) {
            $sql .= ' AND i.journal_id = ? AND a.journal_id = i.journal_id';
            $params[] = $journalIdInt;
        }

        $result = $this->retrieve($sql, $params);

        $issue = null;
        if (!$result->EOF) {
            $issue = $this->_returnIssueFromRow($result->GetRowAssoc(false));
        }
        $result->Close();

        return $issue;
    }

    /**
     * Get all issues organized by published date
     * @param int|string $journalId
     * @param mixed $rangeInfo DBResultRange (optional)
     * @return DAOResultFactory
     */
    public function getIssues(int|string $journalId, mixed $rangeInfo = null): DAOResultFactory {
        $result = $this->retrieveRange(
            'SELECT i.* FROM issues i WHERE journal_id = ? ORDER BY current DESC, date_published DESC',
            [(int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnIssueFromRow');
    }

    /**
     * Get published issues organized by published date
     * @param int|string $journalId
     * @param mixed $rangeInfo DBResultRange
     * @return DAOResultFactory
     */
    public function getPublishedIssues(int|string $journalId, mixed $rangeInfo = null): DAOResultFactory {
        $result = $this->retrieveRange(
            'SELECT i.* FROM issues i LEFT JOIN custom_issue_orders o ON (o.issue_id = i.issue_id) WHERE i.journal_id = ? AND i.published = 1 ORDER BY o.seq ASC, i.current DESC, i.date_published DESC',
            [(int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnIssueFromRow');
    }

    /**
     * Get unpublished issues organized by published date
     * @param int|string $journalId
     * @param mixed $rangeInfo DBResultRange
     * @return DAOResultFactory
     */
    public function getUnpublishedIssues(int|string $journalId, mixed $rangeInfo = null): DAOResultFactory {
        $result = $this->retrieveRange(
            'SELECT i.* FROM issues i WHERE journal_id = ? AND published = 0 ORDER BY year ASC, volume ASC, number ASC',
            [(int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnIssueFromRow');
    }

    /**
     * Return number of articles assigned to an issue.
     * @param int|string $issueId
     * @return int
     */
    public function getNumArticles(int|string $issueId): int {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM published_articles WHERE issue_id = ?', 
            [(int) $issueId]
        );
        
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = !$result->EOF && isset($fields[0]) ? (int) $fields[0] : 0;

        $result->Close();
        return $returner;
    }

    /**
     * Delete the custom ordering of a published issue.
     * @param int|string $journalId
     */
    public function deleteCustomIssueOrdering(int|string $journalId): void {
        $this->update(
            'DELETE FROM custom_issue_orders WHERE journal_id = ?', 
            [(int) $journalId]
        );
    }

    /**
     * Sequentially renumber custom issue orderings in their sequence order.
     * @param int|string $journalId
     */
    public function resequenceCustomIssueOrders(int|string $journalId): void {
        $journalIdInt = (int) $journalId;
        $result = $this->retrieve(
            'SELECT i.issue_id FROM issues i LEFT JOIN custom_issue_orders o ON (o.issue_id = i.issue_id) WHERE i.journal_id = ? ORDER BY o.seq ASC, i.date_published DESC',
            [$journalIdInt]
        );

        $seq = 1;
        while (!$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            $issueId = isset($fields[0]) ? (int) $fields[0] : 0;
            
            $checkResult = $this->retrieve(
                'SELECT issue_id FROM custom_issue_orders WHERE journal_id = ? AND issue_id = ?', 
                [$journalIdInt, $issueId]
            );
            
            /** @var array|bool $checkFields */
            $checkFields = $checkResult->fields;
            if (!$checkResult->EOF && isset($checkFields[0])) {
                $this->update(
                    'UPDATE custom_issue_orders SET seq = ? WHERE issue_id = ? AND journal_id = ?',
                    [$seq, $issueId, $journalIdInt]
                );
            } else {
                $this->insertCustomIssueOrder($journalIdInt, $issueId, $seq);
            }
            $checkResult->Close();
            
            $seq++;
            $result->moveNext();
        }
        $result->Close();
    }

    /**
     * Check if a journal has custom issue ordering.
     * @param int|string $journalId
     * @return bool
     */
    public function customIssueOrderingExists(int|string $journalId): bool {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM custom_issue_orders WHERE journal_id = ?',
            [(int) $journalId]
        );
        
        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = !$result->EOF && isset($fields[0]) && (int) $fields[0] > 0;

        $result->Close();
        return $returner;
    }

    /**
     * Get the custom issue order of a journal.
     * @param int|string $journalId
     * @param int|string $issueId
     * @return int|null
     */
    public function getCustomIssueOrder(int|string $journalId, int|string $issueId): ?int {
        $result = $this->retrieve(
            'SELECT seq FROM custom_issue_orders WHERE journal_id = ? AND issue_id = ?',
            [(int) $journalId, (int) $issueId]
        );

        /** @var array|bool $fields */
        $fields = $result->fields;
        $returner = !$result->EOF && isset($fields[0]) ? (int) $fields[0] : null;

        $result->Close();
        return $returner;
    }

    /**
     * Import the current issue orders into the specified journal as custom issue orderings.
     * @param int|string $journalId
     */
    public function setDefaultCustomIssueOrders(int|string $journalId): void {
        $publishedIssues = $this->getPublishedIssues($journalId);
        $seq = 1;
        while ($issue = $publishedIssues->next()) {
            $this->insertCustomIssueOrder((int) $journalId, (int) $issue->getId(), $seq);
            $seq++;
        }
    }

    /**
     * INTERNAL USE ONLY: Insert a custom issue ordering
     * @param int|string $journalId
     * @param int|string $issueId
     * @param int $seq
     */
    public function insertCustomIssueOrder(int|string $journalId, int|string $issueId, int $seq): void {
        $this->update(
            'INSERT INTO custom_issue_orders (issue_id, journal_id, seq) VALUES (?, ?, ?)',
            [(int) $issueId, (int) $journalId, $seq]
        );
    }

    /**
     * Move a custom issue ordering up or down, resequencing as necessary.
     * @param int|string $journalId
     * @param int|string $issueId
     * @param int $newPos The new position (0-based) of this section
     */
    public function moveCustomIssueOrder(int|string $journalId, int|string $issueId, int $newPos): void {
        $journalIdInt = (int) $journalId;
        $issueIdInt = (int) $issueId;
        
        $result = $this->retrieve(
            'SELECT issue_id FROM custom_issue_orders WHERE journal_id = ? AND issue_id = ?', 
            [$journalIdInt, $issueIdInt]
        );
        
        if (!$result->EOF) {
            $this->update(
                'UPDATE custom_issue_orders SET seq = ? WHERE journal_id = ? AND issue_id = ?',
                [$newPos, $journalIdInt, $issueIdInt]
            );
        } else {
            $this->insertCustomIssueOrder($journalIdInt, $issueIdInt, $newPos);
        }
        $result->Close();
        
        $this->resequenceCustomIssueOrders($journalIdInt);
    }

    /**
     * Delete the public IDs of all issues of a journal.
     * @param int|string $journalId
     * @param string $pubIdType
     */
    public function deleteAllPubIds(int|string $journalId, string $pubIdType): void {
        $settingName = 'pub-id::' . $pubIdType;
        $issues = $this->getIssues($journalId);
        
        while ($issue = $issues->next()) {
            $this->update(
                'DELETE FROM issue_settings WHERE setting_name = ? AND issue_id = ?',
                [$settingName, (int) $issue->getId()]
            );
        }
        $this->flushCache();
    }

    /**
     * Delete the public ID of an issue.
     * @param int|string $issueId
     * @param string $pubIdType
     */
    public function deletePubId(int|string $issueId, string $pubIdType): void {
        $settingName = 'pub-id::' . $pubIdType;
        $this->update(
            'DELETE FROM issue_settings WHERE setting_name = ? AND issue_id = ?',
            [$settingName, (int) $issueId]
        );
        $this->flushCache();
    }
	
    // ---------------------------------------------------------
    // LUMERA METHODS (MODERNIZED)
    // ---------------------------------------------------------

    /**
     * Mengambil semua issue yang terbit dalam satu volume.
     * @param int|string $journalId
     * @param int|string $volume
     * @return DAOResultFactory
     */
    public function getPublishedIssuesByVolume(int|string $journalId, int|string $volume): DAOResultFactory {
        $result = $this->retrieve(
            'SELECT i.* FROM issues i
             WHERE i.journal_id = ? AND i.volume = ? AND i.published = 1
             ORDER BY i.date_published DESC, i.number DESC',
            [(int) $journalId, (int) $volume]
        );
    
        return new DAOResultFactory($result, $this, '_returnIssueFromRow');
    }
    
    /**
     * Mengambil issue terbit SEBELUMNYA dan BERIKUTNYA berdasarkan tanggal terbit.
     * @param int|string $issueId ID issue saat ini
     * @param int|string $journalId ID jurnal saat ini
     * @return array{0: Issue|null, 1: Issue|null} [Prev, Next]
     */
    public function getSurroundingIssues(int|string $issueId, int|string $journalId): array {
        $issueIdInt = (int) $issueId;
        $journalIdInt = (int) $journalId;

        // 1. Dapatkan tanggal issue saat ini
        $result = $this->retrieve(
            'SELECT date_published FROM issues WHERE issue_id = ?',
            [$issueIdInt]
        );

        /** @var array|bool $fields */
        $fields = $result->fields;
        if ($result->EOF || !isset($fields[0])) {
            $result->Close();
            return [null, null];
        }
        
        $currentDate = $fields[0];
        $result->Close();
    
        // 2. Dapatkan Issue BERIKUTNYA (Lebih Baru: Date > Current)
        $resultNext = $this->retrieveLimit(
            'SELECT i.* FROM issues i
             WHERE i.journal_id = ? AND i.published = 1 AND i.date_published > ?
             ORDER BY i.date_published ASC',
            [$journalIdInt, $currentDate], 
            1 
        );
    
        $nextIssue = null;
        if (!$resultNext->EOF) {
            $nextIssue = $this->_returnIssueFromRow($resultNext->GetRowAssoc(false));
        }
        $resultNext->Close();
    
        // 3. Dapatkan Issue SEBELUMNYA (Lebih Lama: Date < Current)
        $resultPrev = $this->retrieveLimit(
            'SELECT i.* FROM issues i
             WHERE i.journal_id = ? AND i.published = 1 AND i.date_published < ?
             ORDER BY i.date_published DESC',
            [$journalIdInt, $currentDate], 
            1 
        );
    
        $prevIssue = null;
        if (!$resultPrev->EOF) {
            $prevIssue = $this->_returnIssueFromRow($resultPrev->GetRowAssoc(false));
        }
        $resultPrev->Close();
    
        return [$prevIssue, $nextIssue];
    }
    
    /**
     * Mendapatkan ID Volume terbit SEBELUMNYA dan BERIKUTNYA.
     * @param int|string $journalId
     * @param int|string $currentVolumeId
     * @return array{0: int|null, 1: int|null} [PrevID, NextID]
     */
    public function getSurroundingVolumeIds(int|string $journalId, int|string $currentVolumeId): array {
        $journalIdInt = (int) $journalId;
        $currentVolumeIdInt = (int) $currentVolumeId;

        // 1. Next Volume (Angka Volume Lebih Besar Terdekat) -> MIN(volume > current)
        $resultNext = $this->retrieve(
            'SELECT MIN(i.volume) FROM issues i
             WHERE i.journal_id = ? AND i.published = 1 AND i.volume > ?',
            [$journalIdInt, $currentVolumeIdInt]
        );
        
        /** @var array|bool $fieldsNext */
        $fieldsNext = $resultNext->fields;
        $nextVolumeId = !$resultNext->EOF && isset($fieldsNext[0]) && $fieldsNext[0] !== null ? (int) $fieldsNext[0] : null;
        $resultNext->Close();
    
        // 2. Prev Volume (Angka Volume Lebih Kecil Terdekat) -> MAX(volume < current)
        $resultPrev = $this->retrieve(
            'SELECT MAX(i.volume) FROM issues i
             WHERE i.journal_id = ? AND i.published = 1 AND i.volume < ?',
            [$journalIdInt, $currentVolumeIdInt]
        );
        
        /** @var array|bool $fieldsPrev */
        $fieldsPrev = $resultPrev->fields;
        $prevVolumeId = !$resultPrev->EOF && isset($fieldsPrev[0]) && $fieldsPrev[0] !== null ? (int) $fieldsPrev[0] : null;
        $resultPrev->Close();
    
        return [$prevVolumeId, $nextVolumeId];
    }
    
    /**
     * Mengambil satu issue berdasarkan Journal ID, Volume, dan Nomor.
     * @param int|string $journalId
     * @param int|string $volume
     * @param string $number (Bisa berupa '1', '1-2', 'SA')
     * @return Issue|null
     */
    public function getIssueByVolumeAndNumber(int|string $journalId, int|string $volume, string $number): ?Issue {
        $result = $this->retrieveLimit(
            'SELECT i.* FROM issues i
             WHERE i.journal_id = ? AND i.volume = ? AND i.number = ? AND i.published = 1',
            [(int) $journalId, (int) $volume, $number], 
            1 
        );
    
        $issue = null;
        if (!$result->EOF) {
            $issue = $this->_returnIssueFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        
        return $issue;
    }

    /**
     * Flush the issue cache.
     */
    public function flushCache(): void {
        $cache = $this->_getCache('issues');
        $cache->flush();
        
        $cache = $this->_getCache('current');
        $cache->flush();
    }
    
}
?>
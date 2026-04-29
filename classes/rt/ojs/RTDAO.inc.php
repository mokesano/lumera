<?php
declare(strict_types=1);

/**
 * @file classes/rt/ojs/RTDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RTDAO
 * @ingroup rt_ojs
 * @see RT
 *
 * @brief DAO operations for the OJS Reading Tools interface.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.rt.ojs.JournalRT');

class RTDAO extends DAO {
    
    /** @var object|null */
    public $versionCache;

    //
    // RT
    //

    /**
     * Retrieve an RT configuration.
     * @param Journal $journal
     * @return JournalRT
     */
    public function getJournalRTByJournal($journal) {
        $rt = new JournalRT($journal->getId());
        
        // [WIZDAM CLEANUP] Hanya mempertahankan pengaturan inti (Metadata, Abstract, Supp Files, Version, dan Capture Cite)
        $rt->setEnabled($journal->getSetting('rtEnabled') ? true : false);
        $rt->setVersion((int) $journal->getSetting('rtVersionId'));
        $rt->setAbstract($journal->getSetting('rtAbstract') ? true : false);
        $rt->setCaptureCite($journal->getSetting('rtCaptureCite') ? true : false);
        $rt->setViewMetadata($journal->getSetting('rtViewMetadata') ? true : false);
        $rt->setSupplementaryFiles($journal->getSetting('rtSupplementaryFiles') ? true : false);
        
        // FITUR USANG TELAH DIHAPUS DARI SINI (PrinterFriendly, DefineTerms, EmailAuthor, dll)

        return $rt;
    }

    /**
     * Update an existing RT configuration.
     * @param JournalRT $rt
     * @return bool
     */
    public function updateJournalRT($rt) {
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $journalId = $rt->getJournalId();
    
        // [WIZDAM CLEANUP] Hanya memperbarui pengaturan yang dipertahankan
        $journalSettingsDao->updateSetting($journalId, 'rtEnabled', $rt->getEnabled(), 'bool');
        $journalSettingsDao->updateSetting($journalId, 'rtVersionId', $rt->getVersion(), 'int');
        $journalSettingsDao->updateSetting($journalId, 'rtAbstract', $rt->getAbstract(), 'bool');
        $journalSettingsDao->updateSetting($journalId, 'rtCaptureCite', $rt->getCaptureCite(), 'bool');
        $journalSettingsDao->updateSetting($journalId, 'rtViewMetadata', $rt->getViewMetadata(), 'bool');
        $journalSettingsDao->updateSetting($journalId, 'rtSupplementaryFiles', $rt->getSupplementaryFiles(), 'bool');
        
        // FITUR USANG TIDAK LAGI DISIMPAN KE DATABASE

        return true;
    }

    /**
     * Insert a new RT configuration.
     * @param JournalRT $rt
     * @return bool
     */
    public function insertJournalRT($rt) {
        return $this->updateJournalRT($rt);
    }

    //
    // RT Versions
    //

    /**
     * Retrieve all RT versions for a journal.
     * @param int $journalId
     * @param DBResultRange|null $pagingInfo
     * @return DAOResultFactory
     */
    public function getVersions($journalId, $pagingInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM rt_versions WHERE journal_id = ? ORDER BY version_key',
            (int) $journalId,
            $pagingInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnVersionFromRow');
        return $returner;
    }

    /**
     * Version cache miss handler.
     * @param FileCache $cache
     * @param string $id
     * @return RTVersion|null
     */
    public function _versionCacheMiss($cache, $id) {
        $ids = explode('-', $id);
        $version = $this->getVersion($ids[0], $ids[1], false);
        $cache->setCache($id, $version);
        return $version;
    }

    /**
     * Get version cache
     * @return FileCache|object
     */
    public function getVersionCache() {
        if (!isset($this->versionCache)) {
            $cacheManager = CacheManager::getManager();
            $this->versionCache = $cacheManager->getObjectCache('rtVersions', 0, [$this, '_versionCacheMiss']);
        }
        return $this->versionCache;
    }

    /**
     * Retrieve a version.
     * @param int $versionId
     * @param int|null $journalId
     * @param bool|null $useCache
     * @return RTVersion|null
     */
    public function getVersion($versionId, $journalId = null, $useCache = null) {
        if ($useCache) {
            $cache = $this->getVersionCache();
            $returner = $cache->get((int) $versionId . '-' . (int) $journalId);
            return $returner;
        }

        $result = $this->retrieve(
            'SELECT * FROM rt_versions WHERE version_id = ? AND journal_id = ?',
            [(int) $versionId, (int) $journalId]
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnVersionFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Insert a new version.
     * @param int $journalId
     * @param RTVersion $version
     * @return int Insert ID
     */
    public function insertVersion($journalId, $version) {
        $this->update(
            'INSERT INTO rt_versions
            (journal_id, version_key, locale, title, description)
            VALUES
            (?, ?, ?, ?, ?)',
            [(int) $journalId, $version->key, $version->locale, $version->title, $version->description]
        );

        $version->versionId = $this->getInsertId('rt_versions', 'version_id');

        foreach ($version->contexts as $context) {
            $context->versionId = $version->versionId;
            $this->insertContext($context);
        }

        return $version->versionId;
    }

    /**
     * Update an existing version.
     * @param int $journalId
     * @param RTVersion $version
     */
    public function updateVersion($journalId, $version) {
        $this->update(
            'UPDATE rt_versions
            SET
                title = ?,
                description = ?,
                version_key = ?,
                locale = ?
            WHERE version_id = ? AND journal_id = ?',
            [
                $version->getTitle(),
                $version->getDescription(),
                $version->getKey(),
                $version->getLocale(),
                (int) $version->getVersionId(),
                (int) $journalId
            ]
        );

        $cache = $this->getVersionCache();
        $cache->flush();
    }

    /**
     * Delete all versions by journal ID.
     * @param int $journalId
     */
    public function deleteVersionsByJournalId($journalId) {
        $versions = $this->getVersions($journalId);
        foreach ($versions->toArray() as $version) {
            $this->deleteVersion($version->getVersionId(), $journalId);
        }
    }

    /**
     * Delete a version.
     * @param int $versionId
     * @param int $journalId
     */
    public function deleteVersion($versionId, $journalId) {
        $this->deleteContextsByVersionId($versionId);
        $this->update(
            'DELETE FROM rt_versions WHERE version_id = ? AND journal_id = ?',
            [(int) $versionId, (int) $journalId]
        );

        $cache = $this->getVersionCache();
        $cache->flush();
    }

    /**
     * Delete RT versions (and dependent entities) by journal ID.
     * @param int $journalId
     */
    public function deleteVersionsByJournal($journalId) {
        $versions = $this->getVersions($journalId);
        while (!$versions->eof()) {
            $version = $versions->next();
            $this->deleteVersion($version->getVersionId(), $journalId);
        }
    }

    /**
     * Return RTVersion object from database row.
     * @param array $row
     * @return RTVersion
     */
    public function _returnVersionFromRow($row) {
        $version = new RTVersion();
        $version->setVersionId($row['version_id']);
        $version->setKey($row['version_key']);
        $version->setLocale($row['locale']);
        $version->setTitle($row['title']);
        $version->setDescription($row['description']);

        if (!HookRegistry::dispatch('RTDAO::_returnVersionFromRow', [&$version, &$row])) {
            $contextsIterator = $this->getContexts($row['version_id']);
            $version->setContexts($contextsIterator->toArray());
        }

        return $version;
    }

    /**
     * Return RTSearch object from database row.
     * @param array $row
     * @return RTSearch
     */
    public function _returnSearchFromRow($row) {
        $search = new RTSearch();
        $search->setSearchId($row['search_id']);
        $search->setContextId($row['context_id']);
        $search->setTitle($row['title']);
        $search->setDescription($row['description']);
        $search->setUrl($row['url']);
        $search->setSearchUrl($row['search_url']);
        $search->setSearchPost($row['search_post']);
        $search->setOrder($row['seq']);

        HookRegistry::dispatch('RTDAO::_returnSearchFromRow', [&$search, &$row]);

        return $search;
    }

    //
    // RT Contexts
    //

    /**
     * Retrieve an RT context.
     * @param int $contextId
     * @return RTContext|null
     */
    public function getContext($contextId) {
        $result = $this->retrieve(
            'SELECT * FROM rt_contexts WHERE context_id = ?',
            [(int) $contextId]
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnContextFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve all RT contexts for a version (in order).
     * @param int $versionId
     * @param DBResultRange|null $pagingInfo
     * @return DAOResultFactory
     */
    public function getContexts($versionId, $pagingInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM rt_contexts WHERE version_id = ? ORDER BY seq',
            [(int) $versionId],
            $pagingInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnContextFromRow');
        return $returner;
    }

    /**
     * Insert a context.
     * @param RTContext $context
     * @return int Context ID
     */
    public function insertContext($context) {
        $this->update(
            'INSERT INTO rt_contexts
            (version_id, title, abbrev, description, cited_by, author_terms, geo_terms, define_terms, seq)
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $context->versionId,
                $context->title,
                $context->abbrev,
                $context->description,
                $context->citedBy ? 1 : 0,
                $context->authorTerms ? 1 : 0,
                $context->geoTerms ? 1 : 0,
                $context->defineTerms ? 1 : 0,
                (int) $context->order
            ]
        );

        $context->contextId = $this->getInsertId('rt_contexts', 'context_id');

        foreach ($context->searches as $search) {
            $search->contextId = $context->contextId;
            $this->insertSearch($search);
        }

        return $context->contextId;
    }

    /**
     * Update an existing context.
     * @param RTContext $context
     * @return bool
     */
    public function updateContext($context) {
        return $this->update(
            'UPDATE rt_contexts
            SET title = ?, abbrev = ?, description = ?, cited_by = ?, author_terms = ?, geo_terms = ?, define_terms = ?, seq = ?
            WHERE context_id = ? AND version_id = ?',
            [
                $context->title, 
                $context->abbrev, 
                $context->description, 
                $context->citedBy ? 1 : 0, 
                $context->authorTerms ? 1 : 0, 
                $context->geoTerms ? 1 : 0, 
                $context->defineTerms ? 1 : 0, 
                (int) $context->order, 
                (int) $context->contextId, 
                (int) $context->versionId
            ]
        );
    }

    /**
     * Delete all contexts by version ID.
     * @param int $versionId
     */
    public function deleteContextsByVersionId($versionId) {
        $contexts = $this->getContexts($versionId);
        foreach ($contexts->toArray() as $context) {
            $this->deleteContext(
                $context->getContextId(),
                $context->getVersionId()
            );
        }
    }

    /**
     * Delete a context.
     * @param int $contextId
     * @param int $versionId
     * @return bool
     */
    public function deleteContext($contextId, $versionId) {
        $result = $this->update(
            'DELETE FROM rt_contexts WHERE context_id = ? AND version_id = ?',
            [(int) $contextId, (int) $versionId]
        );
        if ($result) $this->deleteSearchesByContextId($contextId);
        return $result;
    }

    /**
     * Sequentially renumber contexts in their sequence order.
     * @param int $versionId
     */
    public function resequenceContexts($versionId) {
        $result = $this->retrieve(
            'SELECT context_id FROM rt_contexts WHERE version_id = ? ORDER BY seq',
            [(int) $versionId]
        );

        for ($i=1; !$result->EOF; $i++) {
            list($contextId) = $result->fields;
            $this->update(
                'UPDATE rt_contexts SET seq = ? WHERE context_id = ?',
                [$i, $contextId]
            );

            $result->moveNext();
        }

        $result->close();
        unset($result);
    }

    /**
     * Return RTContext object from database row.
     * @param array $row
     * @return RTContext
     */
    public function _returnContextFromRow($row) {
        $context = new RTContext();
        $context->setContextId($row['context_id']);
        $context->setVersionId($row['version_id']);
        $context->setTitle($row['title']);
        $context->setAbbrev($row['abbrev']);
        $context->setDescription($row['description']);
        $context->setCitedBy($row['cited_by']);
        $context->setAuthorTerms($row['author_terms']);
        $context->setGeoTerms($row['geo_terms']);
        $context->setDefineTerms($row['define_terms']);
        $context->setOrder($row['seq']);

        if (!HookRegistry::dispatch('RTDAO::_returnContextFromRow', [&$context, &$row])) {
            $searchesIterator = $this->getSearches($row['context_id']);
            $context->setSearches($searchesIterator->toArray());
        }

        return $context;
    }

    //
    // RT Searches
    //

    /**
     * Retrieve an RT search.
     * @param int $searchId
     * @return RTSearch|null
     */
    public function getSearch($searchId) {
        $result = $this->retrieve(
            'SELECT * FROM rt_searches WHERE search_id = ?',
            [(int) $searchId]
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnSearchFromRow($result->GetRowAssoc(false));
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve all RT searches for a context (in order).
     * @param int $contextId
     * @param DBResultRange|null $pagingInfo
     * @return DAOResultFactory
     */
    public function getSearches($contextId, $pagingInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM rt_searches WHERE context_id = ? ORDER BY seq',
            [(int) $contextId],
            $pagingInfo
        );

        $returner = new DAOResultFactory($result, $this, '_returnSearchFromRow');
        return $returner;
    }

    /**
     * Insert new search.
     * @param RTSearch $search
     * @return int Search ID
     */
    public function insertSearch($search) {
        $this->update(
            'INSERT INTO rt_searches
            (context_id, title, description, url, search_url, search_post, seq)
            VALUES
            (?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $search->getContextId(),
                $search->getTitle(),
                $search->getDescription(),
                $search->getUrl(),
                $search->getSearchUrl(),
                $search->getSearchPost(),
                (int) $search->getOrder()
            ]
        );

        $search->searchId = $this->getInsertId('rt_searches', 'search_id');
        return $search->searchId;
    }

    /**
     * Update an existing search.
     * @param RTSearch $search
     * @return bool
     */
    public function updateSearch($search) {
        return $this->update(
            'UPDATE rt_searches
            SET title = ?, description = ?, url = ?, search_url = ?, search_post = ?, seq = ?
            WHERE search_id = ? AND context_id = ?',
            [
                $search->getTitle(),
                $search->getDescription(),
                $search->getUrl(),
                $search->getSearchUrl(),
                $search->getSearchPost(),
                (int) $search->getOrder(),
                (int) $search->getSearchId(),
                (int) $search->getContextId()
            ]
        );
    }

    /**
     * Delete all searches by context ID.
     * @param int $contextId
     * @return bool
     */
    public function deleteSearchesByContextId($contextId) {
        return $this->update(
            'DELETE FROM rt_searches WHERE context_id = ?',
            [(int) $contextId]
        );
    }

    /**
     * Delete a search.
     * @param int $searchId
     * @param int $contextId
     * @return bool
     */
    public function deleteSearch($searchId, $contextId) {
        return $this->update(
            'DELETE FROM rt_searches WHERE search_id = ? AND context_id = ?',
            [(int) $searchId, (int) $contextId]
        );
    }

    /**
     * Sequentially renumber searches in their sequence order.
     * @param int $contextId
     */
    public function resequenceSearches($contextId) {
        $result = $this->retrieve(
            'SELECT search_id FROM rt_searches WHERE context_id = ? ORDER BY seq',
            [(int) $contextId]
        );

        for ($i=1; !$result->EOF; $i++) {
            list($searchId) = $result->fields;
            $this->update(
                'UPDATE rt_searches SET seq = ? WHERE search_id = ?',
                [$i, $searchId]
            );

            $result->moveNext();
        }

        $result->close();
        unset($result);
    }
}
?>
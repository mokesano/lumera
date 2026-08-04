<?php
declare(strict_types=1);

/**
 * @file plugins/generic/externalFeed/ExternalFeedDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ExternalFeedDAO
 * @ingroup plugins_generic_externalFeed
 *
 * @brief Operations for retrieving and modifying ExternalFeed objects.
 */

import('lib.pkp.classes.db.DAO');

class ExternalFeedDAO extends DAO {

    /** @var string Name of parent plugin */
    protected $_parentPluginName;
    
    /** @var FileCache|null Internal cache storage */
    protected $_externalFeedCache;

    /**
     * Constructor.
     * @param string $parentPluginName
     */
    public function __construct($parentPluginName) {
        $this->_parentPluginName = (string) $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $parentPluginName
     */
    public function ExternalFeedDAO($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Retrieve an ExternalFeed by ID.
     * @param int $feedId
     * @return ExternalFeed|null
     */
    public function getExternalFeed($feedId) {
        $result = $this->retrieve(
            'SELECT * FROM external_feeds WHERE feed_id = ?',
            [(int) $feedId]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnExternalFeedFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve external feed journal ID by feed ID.
     * @param int $feedId
     * @return int
     */
    public function getExternalFeedJournalId($feedId) {
        $result = $this->retrieve(
            'SELECT journal_id AS journal_id FROM external_feeds WHERE feed_id = ?',
            [(int) $feedId]
        );

        $journalId = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $journalId = (int) $row['journal_id'];
        }
        if ($result) {
            $result->Close();
        }
        return $journalId;
    }

    /**
     * Internal function to return ExternalFeed object from a row.
     * @param array $row
     * @return ExternalFeed
     */
    public function _returnExternalFeedFromRow($row) {
        $externalFeedPlugin = PluginRegistry::getPlugin('generic', $this->_parentPluginName);
        if ($externalFeedPlugin !== null) {
            $externalFeedPlugin->import('ExternalFeed');
        } else {
            import('plugins.generic.externalFeed.ExternalFeed');
        }

        $externalFeed = new ExternalFeed();
        $externalFeed->setId((int) $row['feed_id']);
        $externalFeed->setJournalId((int) $row['journal_id']);
        $externalFeed->setUrl((string) $row['url']);
        $externalFeed->setSeq((float) $row['seq']);
        $externalFeed->setDisplayHomepage((int) $row['display_homepage']);
        $externalFeed->setDisplayBlock((int) $row['display_block']);
        $externalFeed->setLimitItems((int) $row['limit_items']);
        $externalFeed->setRecentItems((int) $row['recent_items']);

        $this->getDataObjectSettings(
            'external_feed_settings',
            'feed_id',
            (int) $row['feed_id'],
            $externalFeed
        );

        return $externalFeed;
    }

    /**
     * Insert a new external feed.
     * @param ExternalFeed $externalFeed
     * @return int
     */
    public function insertExternalFeed($externalFeed) {
        $this->update(
            'INSERT INTO external_feeds
                (journal_id, url, seq, display_homepage, display_block, limit_items, recent_items)
            VALUES
                (?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $externalFeed->getJournalId(),
                (string) $externalFeed->getUrl(),
                (float) $externalFeed->getSeq(),
                (int) $externalFeed->getDisplayHomepage(),
                (int) $externalFeed->getDisplayBlock(),
                (int) $externalFeed->getLimitItems(),
                (int) $externalFeed->getRecentItems()
            ]
        );
        
        $externalFeed->setId($this->getInsertExternalFeedId());
        $this->updateLocaleFields($externalFeed);
        $this->_flushCache();

        return $externalFeed->getId();
    }

    /**
     * Get a list of fields for which localized data is supported.
     * @return array
     */
    public function getLocaleFieldNames() {
        return ['title'];
    }

    /**
     * Update the localized fields for this object.
     * @param ExternalFeed $externalFeed
     * @return void
     */
    public function updateLocaleFields($externalFeed) {
        $this->updateDataObjectSettings(
            'external_feed_settings', 
            $externalFeed, 
            ['feed_id' => (int) $externalFeed->getId()]
        );
    }

    /**
     * Update an existing external feed.
     * @param ExternalFeed $externalFeed
     * @return bool
     */
    public function updateExternalFeed($externalFeed) {
        $return = $this->update(
            'UPDATE external_feeds
                SET
                    journal_id = ?,
                    url = ?,
                    seq = ?,
                    display_homepage = ?,
                    display_block = ?,
                    limit_items = ?,
                    recent_items = ?
            WHERE feed_id = ?',
            [
                (int) $externalFeed->getJournalId(),
                (string) $externalFeed->getUrl(),
                (float) $externalFeed->getSeq(),
                (int) $externalFeed->getDisplayHomepage(),
                (int) $externalFeed->getDisplayBlock(),
                (int) $externalFeed->getLimitItems(),
                (int) $externalFeed->getRecentItems(),
                (int) $externalFeed->getId()
            ]
        );

        $this->updateLocaleFields($externalFeed);
        $this->_flushCache();

        return (bool) $return;
    }

    /**
     * Delete external feed.
     * @param ExternalFeed $externalFeed
     * @return bool
     */
    public function deleteExternalFeed($externalFeed) {
        return $this->deleteExternalFeedById((int) $externalFeed->getId());
    }

    /**
     * Delete external feed by ID.
     * @param int $feedId
     * @return bool
     */
    public function deleteExternalFeedById($feedId) {
        $feedId = (int) $feedId;
        $this->update(
            'DELETE FROM external_feed_settings WHERE feed_id = ?', 
            [$feedId]
        );

        $ret = $this->update(
            'DELETE FROM external_feeds WHERE feed_id = ?', 
            [$feedId]
        );
        
        $this->_flushCache();

        return (bool) $ret;
    }

    /**
     * Delete external feeds by journal ID.
     * @param int $journalId
     * @return void
     */
    public function deleteExternalFeedsByJournalId($journalId) {
        $feeds = $this->getExternalFeedsByJournalId((int) $journalId);
        while ($feed = $feeds->next()) {
            $this->deleteExternalFeedById((int) $feed->getId());
        }
    }

    /**
     * Retrieve external feeds matching a particular journal ID.
     * @param int $journalId
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getExternalFeedsByJournalId($journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM external_feeds WHERE journal_id = ? ORDER BY seq ASC',
            [(int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnExternalFeedFromRow');
    }

    /**
     * Sequentially renumber external feeds in their sequence order.
     * @param int $journalId
     * @return void
     */
    public function resequenceExternalFeeds($journalId) {
        $result = $this->retrieve(
            'SELECT feed_id AS feed_id FROM external_feeds WHERE journal_id = ? ORDER BY seq',
            [(int) $journalId]
        );

        if ($result) {
            $i = 1;
            while (!$result->EOF) {
                $row = $result->GetRowAssoc(false);
                $feedId = (int) $row['feed_id'];
                $this->update(
                    'UPDATE external_feeds SET seq = ? WHERE feed_id = ?',
                    [$i, $feedId]
                );
                $result->MoveNext();
                $i++;
            }
            $result->Close();
        }
        
        $this->_flushCache();
    }

    /**
     * Get the ID of the last inserted external feed.
     * @return int
     */
    public function getInsertExternalFeedId() {
        return (int) $this->getInsertId('external_feeds', 'feed_id');
    }
    
    //
    // Cache Management Helpers
    //

    /**
     * Flush the external feed cache.
     * @return void
     */
    public function _flushCache() {
        $cache = $this->_getCache();
        if ($cache !== null) {
            $cache->flush();
        }
    }

    /**
     * Get the external feed cache.
     * @return FileCache|null
     */
    public function _getCache() {
        if (!isset($this->_externalFeedCache)) {
            $cacheManager = CacheManager::getManager();
            $this->_externalFeedCache = $cacheManager->getFileCache(
                'externalFeed', 'journalId',
                [$this, '_cacheMiss']
            );
        }
        return $this->_externalFeedCache;
    }

    /**
     * Cache miss callback.
     * @param FileCache $cache
     * @param int $id
     * @return array
     */
    public function _cacheMiss($cache, $id) {
        $result = $this->retrieve(
            'SELECT * FROM external_feeds WHERE journal_id = ? ORDER BY seq ASC',
            [(int) $id]
        );
    
        $feeds = [];
        if ($result) {
            $factory = new DAOResultFactory($result, $this, '_returnExternalFeedFromRow');
            while ($feed = $factory->next()) {
                $feeds[(int) $feed->getId()] = $feed;
            }
            $result->Close();
        }
    
        $cache->setEntireCache($feeds);
        return $feeds;
    }
    
}
?>
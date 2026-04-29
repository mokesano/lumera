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
 * * MODERNIZED FOR PHP 8.x & OJS FORK (Wizdam Edition)
 * - Removed obsolete reference operators (&).
 * - Implemented strict Prepared Statements for SQLi protection.
 * - Added automatic cache flushing.
 * - Strict Constructor & Visibility.
 */

import('lib.pkp.classes.db.DAO');

class ExternalFeedDAO extends DAO {

    /** @var string Name of parent plugin */
    public $parentPluginName;
    
    /** @var object Internal cache storage */
    public $externalFeedCache;

    /**
     * Constructor
     */
    public function __construct($parentPluginName) {
        $this->parentPluginName = $parentPluginName;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ExternalFeedDAO($parentPluginName) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ExternalFeedDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($parentPluginName);
    }

    /**
     * Retrieve an ExternalFeed by ID.
     * Uses prepared statement for SQLi safety.
     * @param $feedId int
     * @return ExternalFeed|null
     */
    public function getExternalFeed($feedId) {
        $result = $this->retrieve(
            'SELECT * FROM external_feeds WHERE feed_id = ?',
            (int) $feedId
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $returner = $this->_returnExternalFeedFromRow($result->GetRowAssoc(false));
        }
        $result->Close();
        return $returner;
    }

    /**
     * Retrieve external feed journal ID by feed ID.
     * @param $feedId int
     * @return int
     */
    public function getExternalFeedJournalId($feedId) {
        $result = $this->retrieve(
            'SELECT journal_id FROM external_feeds WHERE feed_id = ?',
            (int) $feedId
        );

        $journalId = isset($result->fields[0]) ? (int) $result->fields[0] : 0;
        // FIX 1: Tutup result untuk mencegah memory leak
        $result->Close();
        return $journalId;  
    }

    /**
     * Internal function to return ExternalFeed object from a row.
     * Removed & reference (PHP Objects are passed by handle by default).
     * @param $row array
     * @return ExternalFeed
     */
    public function _returnExternalFeedFromRow($row) {
        // [WIZDAM FIX] PluginRegistry::getPlugin() bisa null saat dipanggil via
        // DAOResultFactory callback di PHP 7.4 — gunakan import() langsung
        $externalFeedPlugin = PluginRegistry::getPlugin('generic', $this->parentPluginName);
        if ($externalFeedPlugin !== null) {
            $externalFeedPlugin->import('ExternalFeed');
        } else {
            // Fallback: import langsung via path tanpa bergantung pada registry
            import('plugins.generic.externalFeed.ExternalFeed');
        }

        $externalFeed = new ExternalFeed();
        $externalFeed->setId($row['feed_id']);
        $externalFeed->setJournalId($row['journal_id']);
        $externalFeed->setUrl($row['url']);
        $externalFeed->setSeq($row['seq']);
        $externalFeed->setDisplayHomepage($row['display_homepage']);
        $externalFeed->setDisplayBlock($row['display_block']);
        $externalFeed->setLimitItems($row['limit_items']);
        $externalFeed->setRecentItems($row['recent_items']);

        $this->getDataObjectSettings(
            'external_feed_settings',
            'feed_id',
            $row['feed_id'],
            $externalFeed
        );

        return $externalFeed;
    }

    /**
     * Insert a new external feed.
     * SQLi Safe: Uses ? placeholders.
     * Modern: No & reference for object parameter.
     * @param $externalFeed ExternalFeed
     * @return int 
     */
    public function insertExternalFeed($externalFeed) {
        $this->update(
            'INSERT INTO external_feeds
                (journal_id, url, seq, display_homepage, display_block, limit_items, recent_items)
            VALUES
                (?, ?, ?, ?, ?, ?, ?)',
            array(
                (int) $externalFeed->getJournalId(),
                $externalFeed->getUrl(),
                (float) $externalFeed->getSeq(),
                (int) $externalFeed->getDisplayHomepage(),
                (int) $externalFeed->getDisplayBlock(),
                (int) $externalFeed->getLimitItems(),
                (int) $externalFeed->getRecentItems()
            )
        );
        
        $externalFeed->setId($this->getInsertExternalFeedId());
        $this->updateLocaleFields($externalFeed);

        // IMPORTANT: Clear cache to prevent "Zombie Data"
        $this->_flushCache();

        return $externalFeed->getId();
    }

    /**
     * Get a list of fields for which localized data is supported
     * @return array
     */
    public function getLocaleFieldNames() {
        return array('title');
    }

    /**
     * Update the localized fields for this object.
     * @param $externalFeed ExternalFeed
     */
    public function updateLocaleFields($externalFeed) {
        $this->updateDataObjectSettings(
            'external_feed_settings', 
            $externalFeed, 
            array('feed_id' => $externalFeed->getId())
        );
    }

    /**
     * Update an existing external feed.
     * SQLi Safe: Uses ? placeholders.
     * @param $externalFeed ExternalFeed
     * @return boolean
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
            array(
                (int) $externalFeed->getJournalId(),
                $externalFeed->getUrl(),
                (float) $externalFeed->getSeq(),
                (int) $externalFeed->getDisplayHomepage(),
                (int) $externalFeed->getDisplayBlock(),
                (int) $externalFeed->getLimitItems(),
                (int) $externalFeed->getRecentItems(),
                (int) $externalFeed->getId()
            )
        );

        $this->updateLocaleFields($externalFeed);
        
        // IMPORTANT: Clear cache to ensure updates are reflected immediately
        $this->_flushCache();

        return $return;
    }

    /**
     * Delete external feed.
     * @param $externalFeed ExternalFeed 
     * @return boolean
     */
    public function deleteExternalFeed($externalFeed) {
        return $this->deleteExternalFeedById($externalFeed->getId());
    }

    /**
     * Delete external feed by ID.
     * SQLi Safe: Uses ? placeholders.
     * @param $feedId int
     * @return boolean
     */
    public function deleteExternalFeedById($feedId) {
        // Delete settings first (Foreign Key Logic)
        $this->update(
            'DELETE FROM external_feed_settings WHERE feed_id = ?', 
            (int) $feedId
        );

        // Delete main record
        $ret = $this->update(
            'DELETE FROM external_feeds WHERE feed_id = ?', 
            (int) $feedId
        );
        
        // IMPORTANT: Clear cache to remove deleted items from memory
        $this->_flushCache();

        return $ret;
    }

    /**
     * Delete external_feed by journal ID.
     * @param $journalId int
     */
    public function deleteExternalFeedsByJournalId($journalId) {
        $feeds = $this->getExternalFeedsByJournalId($journalId);

        while ($feed = $feeds->next()) {
            $this->deleteExternalFeedById($feed->getId());
        }
    }

    /**
     * Retrieve external feeds matching a particular journal ID.
     * @param $journalId int
     * @param $rangeInfo object DBRangeInfo
     * @return object DAOResultFactory 
     */
    public function getExternalFeedsByJournalId($journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM external_feeds WHERE journal_id = ? ORDER BY seq ASC',
            array((int) $journalId),
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnExternalFeedFromRow');
    }

    /**
     * Sequentially renumber external feeds in their sequence order.
     * @param $journalId int
     */
    public function resequenceExternalFeeds($journalId) {
        $result = $this->retrieve(
            'SELECT feed_id FROM external_feeds WHERE journal_id = ? ORDER BY seq',
            array((int) $journalId)
        );

        for ($i=1; !$result->EOF; $i++) {
            list($feedId) = $result->fields;
            $this->update(
                'UPDATE external_feeds SET seq = ? WHERE feed_id = ?',
                array(
                    (int) $i,
                    (int) $feedId
                )
            );

            $result->moveNext();
        }

        $result->Close();
        unset($result);
        
        // Cache needs to be refreshed after reordering
        $this->_flushCache();
    }

    /**
     * Get the ID of the last inserted external feed.
     * @return int
     */
    public function getInsertExternalFeedId() {
        return $this->getInsertId('external_feeds', 'feed_id');
    }
    
    // -------------------------------------------------------------------------
    // CACHE MANAGEMENT HELPERS (Modern & Robust)
    // -------------------------------------------------------------------------

    /**
     * Flush the external feed cache.
     * This function is crucial for data consistency.
     */
    public function _flushCache() {
        $cache = $this->_getCache();
        $cache->flush();
    }

    /**
     * Get the external feed cache.
     * Modernized: Removed & reference return.
     * @return object FileCache
     */
    public function _getCache() {
        if (!isset($this->externalFeedCache)) {
            $cacheManager = CacheManager::getManager();
            $this->externalFeedCache = $cacheManager->getFileCache(
                'externalFeed', 'journalId',
                array($this, '_cacheMiss')
            );
        }
        return $this->externalFeedCache;
    }

    /**
     * Cache miss callback.
     * Modernized: Removed & reference from parameters.
     */
    public function _cacheMiss($cache, $id) {
        // FIX: Implementasi cache yang benar
        $result = $this->retrieve(
            'SELECT * FROM external_feeds WHERE journal_id = ? ORDER BY seq ASC',
            array((int) $id)
        );
    
        $feeds = array();
        $factory = new DAOResultFactory($result, $this, '_returnExternalFeedFromRow');
        while ($feed = $factory->next()) {
            $feeds[$feed->getId()] = $feed;
        }
    
        $cache->setEntireCache($feeds);
        return null;
    }
}
?>
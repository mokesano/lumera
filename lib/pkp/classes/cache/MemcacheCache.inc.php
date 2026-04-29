<?php
declare(strict_types=1);

/**
 * @file classes/cache/MemcacheCache.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MemcacheCache
 * @ingroup cache
 * @see GenericCache
 *
 * @brief Provides caching based on Memcache.
 * [WIZDAM EDITION] PHP 8 Safe & Modernized
 */

import('lib.pkp.classes.cache.GenericCache');

// FIXME This should use connection pooling
// WARNING: This cache MUST be loaded in batch, or else many cache
// misses will result.

// Pseudotypes used to represent false and null values in the cache
class memcache_false {
}
class memcache_null {
}

class MemcacheCache extends GenericCache {
    /**
     * Connection to use for caching.
     * @var Memcache
     */
    public $connection;

    /**
     * Flag (used by Memcache::set)
     * @var int
     */
    public $flag;

    /**
     * Expiry (used by Memcache::set)
     * @var int
     */
    public $expire;

    /**
     * Constructor
     * Instantiate a cache. 
     */
    public function __construct($context, $cacheId, $fallback, $hostname, $port) {
        parent::__construct($context, $cacheId, $fallback);
        
        // [WIZDAM] Safety check for PHP 8 where Memcache extension might be missing
        if (class_exists('Memcache')) {
            $this->connection = new Memcache;
            if (!$this->connection->connect($hostname, $port)) {
                $this->connection = null;
            }
        } else {
            // Extension missing - Fail silently or log error
            // error_log('Wizdam Warning: Memcache extension not loaded in PHP.');
            $this->connection = null;
        }

        $this->flag = null; // 0 or MEMCACHE_COMPRESSED
        $this->expire = 3600; // 1 hour default expiry
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MemcacheCache($context, $cacheId, $fallback, $hostname, $port) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            // [CCTV] Smart Error Log
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::MemcacheCache(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($context, $cacheId, $fallback, $hostname, $port);
    }

    /**
     * Set the flag (used in Memcache::set)
     */
    public function setFlag($flag) {
        $this->flag = $flag;
    }

    /**
     * Set the expiry time (used in Memcache::set)
     */
    public function setExpiry($expiry) {
        $this->expire = $expiry;
    }

    /**
     * Flush the cache.
     */
    public function flush() {
        if ($this->connection) {
            $this->connection->flush();
        }
    }

    /**
     * Get an object from the cache.
     * @param $id
     */
    public function getCache($id) {
        // [FIX] Guard: tolak null id
        if ($id === null || !$this->connection) return $this->cacheMiss;

        $result = $this->connection->get($this->getContext() . ':' . $this->getCacheId() . ':' . $id);
        
        if ($result === false) {
            return $this->cacheMiss;
        }

        // [WIZDAM] PHP 8 Safety: get_class throws fatal error if arg is not object
        if (is_object($result)) {
            switch (get_class($result)) {
                case 'memcache_false':
                    $result = false;
                    break; // [BUGFIX] Added break
                case 'memcache_null':
                    $result = null;
                    break; // [BUGFIX] Added break
            }
        }
        
        return $result;
    }

    /**
     * Set an object in the cache. This function should be overridden
     * by subclasses.
     * @param $id
     * @param $value
     */
    public function setCache($id, $value) {
        // [FIX] Guard: tolak null id
        if ($id === null || !$this->connection) return false;

        if ($value === false) {
            $value = new memcache_false;
        } elseif ($value === null) {
            $value = new memcache_null;
        }
        return ($this->connection->set($this->getContext() . ':' . $this->getCacheId() . ':' . $id, $value, $this->flag, $this->expire));
    }

    /**
     * [FIX] Tambah getContents() — sebelumnya tidak ada, 
     * menyebabkan Fatal Error
     */
    public function getContents() {
        if (!$this->connection) return [];

        // Coba ambil dari _contents key khusus
        $contentsKey = $this->getContext() . ':' . $this->getCacheId() . ':_contents';
        $result = $this->connection->get($contentsKey);

        if ($result !== false && is_array($result)) return $result;

        // [FIX] Fallback dengan cacheId yang benar (bukan null)
        if ($this->fallback) {
            $contents = call_user_func($this->fallback, $this, $this->cacheId);
            if (is_array($contents)) {
                $this->setEntireCache($contents);
                return $contents;
            }
        }

        return [];
    }


    /**
     * Get the time at which the data was cached.
     * Note that keys expire in memcache, which means
     * that it's possible that the date will disappear
     * before the data -- in this case we'll have to
     * assume the data is still good.
     */
    public function getCacheTime() {
        return null;
    }

    /**
     * Set the entire contents of the cache.
     * WARNING: THIS DOES NOT FLUSH THE CACHE FIRST!
     */
    public function setEntireCache($contents) {
        if (!$this->connection) return;

        // [FIX] Flush dahulu — WARNING lama dihapus karena sekarang sudah flush
        $this->flush();

        // Simpan seluruh contents dalam satu key khusus untuk getContents()
        $contentsKey = $this->getContext() . ':' . $this->getCacheId() . ':_contents';
        $this->connection->set($contentsKey, $contents, $this->flag, $this->expire);

        // Simpan juga per-key untuk getCache($id) individual
        foreach ($contents as $id => $value) {
            $this->setCache($id, $value);
        }
    }
    
    /**
     * Close the cache and free resources.
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
            unset ($this->connection);
        }
        $this->contextChecked = false;
    }
}

?>
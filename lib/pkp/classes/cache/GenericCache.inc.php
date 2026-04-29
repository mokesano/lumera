<?php
declare(strict_types=1);

/**
 * @file classes/cache/GenericCache.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GenericCache
 * @ingroup cache
 *
 * @brief Provides implementation-independent caching. Although this class is intended
 * to be overridden with a more specific implementation, it can be used as the
 * null cache.
 * [WIZDAM EDITION] PHP 7.4+ Compatible with Smart Error Shims
 */

// Pseudotype to represent a cache miss
class generic_cache_miss {
    // No properties or methods; this is just a marker class.
}

class GenericCache {
    
    /**
     * The unique string identifying the context of this cache.
     * Must be suitable for a filename.
     * @var string
     */
    public $context;

    /**
     * The ID of this particular cache within the context
     * @var string
     */
    public $cacheId;

    /**
     * Object representing a cache miss
     * @var generic_cache_miss
     */
    public $cacheMiss;

    /**
     * The getter fallback callback (for a cache miss)
     * @var callback
     */
    public $fallback;

    /**
     * Constructor.
     * Instantiate a cache.
     * @param $context string
     * @param $cacheId string 
     * @param $fallback callback 
     */
    public function __construct($context, $cacheId, $fallback) {
        $this->context = $context;
        $this->cacheId = $cacheId;
        $this->fallback = $fallback;
        $this->cacheMiss = new generic_cache_miss;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GenericCache($context, $cacheId, $fallback) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            // [CCTV] Agar log mencatat NAMA CLASS ANAK yang memanggil
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::GenericCache(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($context, $cacheId, $fallback);
    }

    /**
     * Get an object from cache, using the fallback if necessary.
     * [MODERNISASI] Optimized type checking and callback
     * @param $id
     * @return mixed
     */
    public function get($id) {
        $result = $this->getCache($id);
        
        // [WIZDAM] Menggunakan instanceof lebih cepat dan bersih daripada get_class
        if ($result instanceof generic_cache_miss) {
            if ($this->fallback) {
                // [MODERNISASI] Hapus & pada $this (PHP 7 objects are passed by handle)
                // call_user_func aman untuk berbagai jenis callback
                $result = call_user_func($this->fallback, $this, $id);
            }
        }
        return $result;
    }

    /**
     * Set an object in the cache. This function should be overridden
     * by subclasses.
     * @param $id
     * @param $value
     * @return mixed
     */
    public function set($id, $value) {
        return $this->setCache($id, $value);
    }

    /**
     * Flush the cache.
     * @return mixed
     */
    public function flush() {
        // Default implementation does nothing; override in subclasses as needed.
    }

    /**
     * Set the entire contents of the cache. May (should) be overridden
     * by subclasses.
     * [MODERNISASI] REMOVED REFERENCE (&) TO FIX PHP WARNING
     * @param $contents array of id -> value pairs
     * @return mixed
     */
    public function setEntireCache($contents) {
        $this->flush();
        if (is_array($contents)) {
            foreach ($contents as $id => $value) {
                $this->setCache($id, $value);
            }
        }
    }

    /**
     * Get an object from the cache. This function should be overridden
     * by subclasses.
     * @param $id
     */
    public function getCache($id) {
        return $this->cacheMiss;
    }

    /**
     * Set an object in the cache. This function should be overridden
     * by subclasses.
     * @param $id
     * @param $value
     */
    public function setCache($id, $value) {
        // Default implementation does nothing; override in subclasses as needed.
    }

    /**
     * Close the cache. (Optionally overridden by subclasses.)
     * @return mixed
     */
    public function close() {
        // Default implementation does nothing; override in subclasses as needed.
    }

    /**
     * Get the context.
     * @return string
     */
    public function getContext() {
        return $this->context;
    }

    /**
     * Get the cache ID within its context
     * @return string
     */
    public function getCacheId() {
        return $this->cacheId;
    }

    /**
     * Get the time at which the data was cached.
     * @return int Unix timestamp
     */
    public function getCacheTime() {
        // Since it's not really cached, we'll consider it to have been cached just now.
        return time();
    }
}

?>
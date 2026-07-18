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
 */

import('lib.pkp.classes.cache.GenericCacheMiss');

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
     * @var mixed $fallback
     */
    public $fallback;

    /**
     * Constructor.
     * Instantiate a cache.
     * @param string $context string
     * @param string $cacheId string 
     * @param mixed $fallback callback 
     */
    public function __construct($context, $cacheId, $fallback) {
        $this->context = $context;
        $this->cacheId = $cacheId;
        $this->fallback = $fallback;
        $this->cacheMiss = new generic_cache_miss();
    }

    /**
     * [SHIM] Backward Compatibility
     * @param string $context string
     * @param string $cacheId string 
     * @param mixed $fallback callback 
     */
    public function GenericCache($context, $cacheId, $fallback) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::GenericCache(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($context, $cacheId, $fallback);
    }

    /**
     * Get an object from cache, using the fallback if necessary.
     * Optimized type checking and callback
     * @param int|string $id
     * @return mixed
     */
    public function get($id) {
        $result = $this->getCache($id);
        if ($result instanceof generic_cache_miss) {
            if ($this->fallback) {
                $result = call_user_func($this->fallback, $this, $id);
            }
        }
        return $result;
    }

    /**
     * Set an object in the cache. This function should be overridden
     * by subclasses.
     * @param int|string $id
     * @param mixed $value
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
     * @param array $contents array of id -> value pairs
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
     * @param int|string $id
     * @return mixed
     */
    public function getCache($id) {
        return $this->cacheMiss;
    }

    /**
     * Set an object in the cache. This function should be overridden
     * by subclasses.
     * @param int|string $id
     * @param mixed $value
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
        return time();
    }

}
?>
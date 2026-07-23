<?php
declare(strict_types=1);

/**
 * @file classes/cache/APCuCache.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class APCuCache
 * @ingroup cache
 * @see GenericCache
 *
 * @brief Provides caching based on APCu's variable store.
 */

import('lib.pkp.classes.cache.GenericCache');

// [LUMERA] Stubs for  the APCu PHP extension.
if (!function_exists('apcu_fetch')) {
    function apcu_fetch($key, &$success = null) { return false; }
}
if (!function_exists('apcu_store')) {
    function apcu_store($key, $var = null, $ttl = 0) { return false; }
}
if (!function_exists('apcu_delete')) {
    function apcu_delete($key) { return false; }
}
if (!function_exists('apcu_cache_info')) {
    function apcu_cache_info($type = '') { return false; }
}

/**
 * Helper class to distinguish cached boolean false from APCu fetch failure.
 * APCu returns false on cache miss, so we wrap actual false values in this class.
 */
class apc_false {}

class APCuCache extends GenericCache {
    
    /**
     * Construct
     * @param mixed $context string
     * @param mixed $cacheId string
     * @param mixed $fallback callable
     */
    public function __construct($context, $cacheId, $fallback) {
        parent::__construct($context, $cacheId, $fallback);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param mixed $context string
     * @param mixed $cacheId string
     * @param mixed $fallback callable
     */
    public function APCuCache($context, $cacheId, $fallback) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::APCuCache(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($context, $cacheId, $fallback);
    }

    /**
     * Flush the cache.
     */
    public function flush() {
        $prefix = INDEX_FILE_LOCATION . ':' . $this->getContext() . ':' . $this->getCacheId();
        
        $info = apcu_cache_info();
        
        if (is_array($info) && isset($info['cache_list'])) {
            foreach ($info['cache_list'] as $entry) {
                if (isset($entry['info']) && strpos((string) $entry['info'], $prefix) === 0) {
                    apcu_delete($entry['info']);
                }
            }
        }
        
        // Also delete the _contents key to ensure complete flush
        apcu_delete($prefix . ':_contents');
    }

    /**
     * Get an object from the cache.
     * @param mixed $id
     * @return mixed
     */
    public function getCache($id) {
        if ($id === null) {
            return $this->cacheMiss;
        }
        
        $key = INDEX_FILE_LOCATION . ':' . $this->getContext() . ':' . $this->getCacheId() . ':' . $id;
        $result = apcu_fetch($key);
        
        if ($result === false) {
            return $this->cacheMiss;
        }
        
        if (!is_string($result)) {
            return $this->cacheMiss;
        }
        
        // Suppress unserialize warnings in case of corrupted cache data
        $returner = @unserialize($result);
        
        if ($returner === false) {
            return $this->cacheMiss;
        }
        
        // Handle boolean false wrapper
        if (is_object($returner) && get_class($returner) === 'apc_false') {
            return false;
        }
        
        return $returner;
    }

    /**
     * Set an object in the cache.
     * @param mixed $id
     * @param mixed $value
     */
    public function setCache($id, $value) {
        if ($id === null) {
            return;
        }
        
        $key = INDEX_FILE_LOCATION . ':' . $this->getContext() . ':' . $this->getCacheId() . ':' . $id;
        
        // Wrap boolean false to distinguish from APCu fetch failure
        if ($value === false) {
            $value = new apc_false();
        }
        
        apcu_store($key, serialize($value));
    }

    /**
     * Get the entire contents of the cache.
     * @return array
     */
    public function getContents() {
        $contentsKey = INDEX_FILE_LOCATION . ':' . $this->getContext() . ':' . $this->getCacheId() . ':_contents';
        $result = apcu_fetch($contentsKey);

        if ($result !== false && is_string($result)) {
            $contents = @unserialize($result);
            if (is_array($contents)) {
                return $contents;
            }
        }

        // Safety: Ensure fallback is callable before invoking
        if (is_callable($this->fallback)) {
            $contents = call_user_func($this->fallback, $this, $this->getCacheId());
            if (is_array($contents)) {
                $this->setEntireCache($contents);
                return $contents;
            }
        }

        return [];
    }
    
    /**
     * Get the time at which the data was cached.
     * Not implemented in this type of cache.
     * @return null
     */
    public function getCacheTime() {
        return null;
    }

    /**
     * Set the entire contents of the cache.
     * WARNING: THIS DOES NOT FLUSH THE CACHE FIRST!
     * @param array $contents
     */
    public function setEntireCache($contents) {
        if (!is_array($contents)) {
            return;
        }

        $contentsKey = INDEX_FILE_LOCATION . ':' . $this->getContext() . ':' . $this->getCacheId() . ':_contents';
        apcu_store($contentsKey, serialize($contents));

        // Also store per-key for individual getCache($id) lookups
        foreach ($contents as $id => $value) {
            $this->setCache($id, $value);
        }
    }

}
?>
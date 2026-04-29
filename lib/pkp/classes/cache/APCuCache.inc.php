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
 * [WIZDAM] Renamed from APCCache to APCuCache to reflect modern PHP usage.
 */

import('lib.pkp.classes.cache.GenericCache');

// Helper class untuk menyimpan nilai boolean false
// (Karena apcu_fetch mengembalikan false jika gagal, kita butuh cara membedakannya)
class apc_false {};

class APCuCache extends GenericCache {
    
    /**
     * Construct
     * Instantiate a cache.
     */
    public function __construct($context, $cacheId, $fallback) {
        parent::__construct($context, $cacheId, $fallback);
    }

    /**
     * [SHIM] Backward Compatibility for legacy instantiation
     */
    public function APCuCache($context, $cacheId, $fallback) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            // [CCTV] Smart Error Log
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
        
        // [MODERNISASI] APCu menggunakan apcu_cache_info (parameter 'user' sudah default/deprecated di apcu)
        $info = apcu_cache_info();
        
        if (isset($info['cache_list']) && is_array($info['cache_list'])) {
            foreach ($info['cache_list'] as $entry) {
                // Di APCu, key ada di 'info'
                if (isset($entry['info']) && substr($entry['info'], 0, strlen($prefix)) == $prefix) {
                    apcu_delete($entry['info']);
                }
            }
        }
        // [FIX] Hapus juga kunci _contents saat flush
        apcu_delete(INDEX_FILE_LOCATION . ':' . $this->getContext() . ':' . $this->getCacheId() . ':_contents');
    }

    /**
     * Get an object from the cache.
     * @param $id
     */
    public function getCache($id) {
        // [FIX] Guard: tolak null id agar tidak membuat kunci tidak valid
        if ($id === null) return $this->cacheMiss;
        
        $key = INDEX_FILE_LOCATION . ':'. $this->getContext() . ':' . $this->getCacheId() . ':' . $id;
        
        // [MODERNISASI] apcu_fetch
        $result = apcu_fetch($key);
        
        // Return cacheMiss if fetch failed
        if ($result === false) return $this->cacheMiss;
        
        $returner = unserialize($result);
        if ($returner === false) return $this->cacheMiss;
        
        // Handle boolean false wrapper
        if (is_object($returner) && get_class($returner) === 'apc_false') $returner = false;
        
        return $returner;
    }

    /**
     * Set an object in the cache. This function should be overridden
     * by subclasses.
     * @param $id
     * @param $value
     */
    public function setCache($id, $value) {
        // [FIX] Guard: tolak null id
        if ($id === null) return;
        
        $key = INDEX_FILE_LOCATION . ':'. $this->getContext() . ':' . $this->getCacheId() . ':' . $id;
        
        // Wrap boolean false
        if ($value === false) $value = new apc_false;
        
        // [MODERNISASI] apcu_store
        apcu_store($key, serialize($value));
    }

    /**
     * [FIX] Tambah getContents() — sebelumnya tidak ada, 
     * menyebabkan Fatal Error
     * Menggunakan _contents key untuk menyimpan seluruh data sekaligus
     */
    public function getContents() {
        $contentsKey = INDEX_FILE_LOCATION . ':' . $this->getContext() . ':' . $this->getCacheId() . ':_contents';
        $result = apcu_fetch($contentsKey);

        if ($result !== false) {
            $contents = unserialize($result);
            if (is_array($contents)) return $contents;
        }

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
     * Not implemented in this type of cache.
     */
    public function getCacheTime() {
        return null;
    }

    /**
     * Set the entire contents of the cache.
     * WARNING: THIS DOES NOT FLUSH THE CACHE FIRST!
     */
    public function setEntireCache($contents) {
        // Simpan seluruh contents dalam satu key khusus
        $contentsKey = INDEX_FILE_LOCATION . ':' . $this->getContext() . ':' . $this->getCacheId() . ':_contents';
        apcu_store($contentsKey, serialize($contents));

        // Simpan juga per-key untuk getCache($id) individual
        foreach ($contents as $id => $value) {
            $this->setCache($id, $value);
        }
    }
}

?>
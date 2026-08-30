<?php
declare(strict_types=1);

/**
 * @defgroup cache
 */

/**
 * @file classes/cache/FileCache.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class FileCache
 * @ingroup cache
 *
 * @brief Provides caching based on compressed binary files on the filesystem.
 * Serialized + GZIP Compressed Cache (.wiz)
 */

import('lib.pkp.classes.cache.GenericCache');

class FileCache extends GenericCache {
    
    /**
     * Connection to use for caching.
     * @var string
     */
    protected $filename;

    /**
     * The cached data
     * @var mixed
     */
    protected $cache;

    /**
     * Constructor.
     * 
     * Instantiate a cache.
     * NOTE: Loads existing cache data from disk if available.
     * Uses .wiz extension with gzdeflate compression for security and efficiency.
     *
     * @param string $context
     * @param string $cacheId
     * @param callable|null $fallback
     * @param string $path
     */
    public function __construct($context, $cacheId, $fallback, $path) {
        parent::__construct($context, $cacheId, $fallback);

        // [WIZDAM] Ubah ekstensi jadi .wiz (Wizdam Cache)
        // Format biner terkompresi, aman dari eksekusi langsung via browser.
        $this->filename = $path . DIRECTORY_SEPARATOR . "fc-$context-" . str_replace('/', '.', (string) $cacheId) . '.wiz';

        if (file_exists($this->filename)) {
            $content = @file_get_contents($this->filename);
            if ($content !== false && !empty($content)) {
                $uncompressed = @gzinflate($content);
                if ($uncompressed !== false) {
                    $this->cache = @unserialize($uncompressed);
                } else {
                    $this->cache = null;
                }
            } else {
                $this->cache = null;
            }
        } else {
            $this->cache = null;
        }
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $context
     * @param string $cacheId
     * @param callable|null $fallback
     * @param string $path
     */
    public function FileCache($context, $cacheId, $fallback, $path) {
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
     * Flush the cache.
     */
    public function flush() {
        $this->cache = null;
        if (file_exists($this->filename)) {
            @unlink($this->filename);
        }
    }

    /**
     * Get an object from the cache.
     * @param string $id
     * @return mixed
     */
    public function getCache($id) {
        if (!isset($this->cache)) {
            return $this->cacheMiss;
        }
        return isset($this->cache[$id]) ? $this->cache[$id] : null;
    }

    /**
     * Set an object in the cache.
     * 
     * NOTE: FileCache uses all-or-nothing invalidation; setting a single value
     * flushes the entire cache to be regenerated on demand by the fallback.
     *
     * @param string $id
     * @param mixed $value
     * @return bool
     */
    public function setCache($id, $value) {
        $this->flush();
        return true;
    }

    /**
     * Set the entire contents of the cache.
     * 
     * NOTE: Serializes and compresses data using gzdeflate level 9,
     * writes atomically with LOCK_EX to prevent race conditions.
     *
     * @param array $contents
     */
    public function setEntireCache($contents) {
        $newFile = !file_exists($this->filename);
        
        // [OPTIMASI] Serialize + Kompresi Level 9 (Max Compression)
        // Menggunakan gzdeflate (Raw Deflate) karena lebih ringkas tanpa header GZIP standar.
        $serialized = serialize($contents);
        $compressed = gzdeflate($serialized, 9);

        if (file_put_contents($this->filename, $compressed, LOCK_EX) !== false) {
            if ($newFile) {
                $umask = Config::getVar('files', 'umask');
                if ($umask) {
                    @chmod($this->filename, FILE_MODE_MASK & ~$umask);
                }
            }
        }

        $this->cache = $contents;
    }

    /**
     * Get the time at which the data was cached.
     * @return int|null
     */
    public function getCacheTime() {
        if (!file_exists($this->filename)) {
            return null;
        }
        $result = filemtime($this->filename);
        if ($result === false) {
            return null;
        }
        return (int) $result;
    }

    /**
     * Get the entire contents of the cache.
     * 
     * NOTE: Invokes fallback function if cache is empty, ensuring proper
     * cacheId is passed (not null) for locale-aware fallback handlers.
     *
     * @return array
     */
    public function getContents() {
        if (!isset($this->cache)) {
            if ($this->fallback) {
                $result = call_user_func($this->fallback, $this, $this->cacheId);
                if (!isset($this->cache) && is_array($result)) {
                    $this->setEntireCache($result);
                }
            }
        }
        return $this->cache ?? [];
    }
    
}
?>
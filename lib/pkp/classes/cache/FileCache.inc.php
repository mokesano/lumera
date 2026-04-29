<?php
declare(strict_types=1);

/**
 * @defgroup cache
 */

/**
 * @file classes/cache/FileCache.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileCache
 * @ingroup cache
 *
 * @brief Provides caching based on compressed binary files on the filesystem.
 * [WIZDAM EDITION] Serialized + GZIP Compressed Cache (.wiz)
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
     * Instantiate a cache.
     * @param $context string (e.g. 'site', 'journal', 'plugin').
     * @param $cacheId string (e.g. locale, plugin name).
     * @param $fallback callback
     * @param $path string 
     * [WIZDAM] Ubah ekstensi jadi .wiz (Wizdam Cache)
     * Format biner terkompresi, aman dari eksekusi langsung via browser.
     */
    public function __construct($context, $cacheId, $fallback, $path) {
        parent::__construct($context, $cacheId, $fallback);

        // [WIZDAM] Ubah ekstensi jadi .wiz (Wizdam Cache)
        // Format biner terkompresi, aman dari eksekusi langsung via browser.
        $this->filename = $path . DIRECTORY_SEPARATOR . "fc-$context-" . str_replace('/', '.', (string) $cacheId) . '.wiz';

        // Load the cache data if it exists.
        if (file_exists($this->filename)) {
            // [OPTIMASI] Baca file biner dan dekompresi dengan tanda @
            $content = @file_get_contents($this->filename);
            if ($content !== false && !empty($content)) {
                // gzinflate adalah pasangan dari gzdeflate
                // Gunakan @ untuk suppress error jika file korup saat proses write berbarengan
                $uncompressed = @gzinflate($content);
                if ($uncompressed !== false) {
                    $this->cache = @unserialize($uncompressed);
                } else {
                    $this->cache = null; // Gagal dekompresi (Corrupt file)
                }
            } else {
                $this->cache = null; // Gagal baca file atau kosong
            }
        } else {
            $this->cache = null;
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FileCache($context, $cacheId, $fallback, $path) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            // [CCTV] Smart Error Log
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::FileCache(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($context, $cacheId, $fallback, $path);
    }

    /**
     * Flush the cache
     */
    public function flush() {
        $this->cache = null;
        if (file_exists($this->filename)) {
            @unlink($this->filename);
        }
    }

    /**
     * Get an object from the cache.
     * @param $id
     */
    public function getCache($id) {
        if (!isset($this->cache)) return $this->cacheMiss;
        return (isset($this->cache[$id]) ? $this->cache[$id] : null);
    }

    /**
     * Set an object in the cache.
     * @param $id
     * @param $value
     */
    public function setCache($id, $value) {
        // Flush the cache; it will be regenerated on demand.
        // FileCache OJS biasanya "All-or-Nothing", jadi mengubah satu value 
        // mengharuskan invalidasi seluruh file agar digenerate ulang oleh Fallback.
        $this->flush();
    }

    /**
     * Set the entire contents of the cache.
     * [WIZDAM] Saving as Compressed Binary
     * @param $contents array
     */
    public function setEntireCache($contents) {
        $newFile = !file_exists($this->filename);
        
        // [OPTIMASI] Serialize + Kompresi Level 9 (Max Compression)
        // Menggunakan gzdeflate (Raw Deflate) karena lebih ringkas tanpa header GZIP standar.
        $serialized = serialize($contents);
        $compressed = gzdeflate($serialized, 9);

        // Tulis file menggunakan file_put_contents dengan LOCK_EX 
        // untuk mencegah Race Condition saat trafik tinggi.
        if (file_put_contents($this->filename, $compressed, LOCK_EX) !== false) {
            if ($newFile) {
                $umask = Config::getVar('files', 'umask');
                if ($umask) @chmod($this->filename, FILE_MODE_MASK & ~$umask);
            }
        }

        $this->cache = $contents;
    }

    /**
     * Get the time at which the data was cached.
     * @return int
     */
    public function getCacheTime() {
        if (!file_exists($this->filename)) return null;
        $result = filemtime($this->filename);
        if ($result === false) return null;
        return ((int) $result);
    }

    /**
     * Get the entire contents of the cache in an associative array.
     * @return array
     */
    public function getContents() {
        if (!isset($this->cache)) {
            // [FIX] Panggil fallback dengan cacheId yang benar (bukan null)
            // agar _countryCacheMiss menerima locale yang tepat, bukan null
            if ($this->fallback) {
                $result = call_user_func($this->fallback, $this, $this->cacheId);
                // Jika fallback tidak memanggil setEntireCache sendiri,
                // paksa set dari return value-nya
                if (!isset($this->cache) && is_array($result)) {
                    $this->setEntireCache($result);
                }
            }
        }
        return $this->cache ?? [];
    }
}

?>
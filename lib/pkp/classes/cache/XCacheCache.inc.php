<?php
declare(strict_types=1);

/**
 * @file classes/cache/XCacheCache.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class XCacheCache
 * @ingroup cache
 * @see GenericCache
 *
 * @brief [WIZDAM DEPRECATED] XCache is dead in PHP 7+.
 * This class is a "Zombie" stub that silently falls back to FileCache
 * to prevent Fatal Errors if selected in config.
 */

// Import FileCache sebagai pengganti
import('lib.pkp.classes.cache.FileCache');

class XCacheCache extends FileCache {
    
    /**
     * Instantiate a cache.
     * @param mixed $context
     * @param mixed $cacheId
     * @param mixed $fallback
     */
    public function __construct($context, $cacheId, $fallback) {
        // Berikan peringatan di log bahwa admin harus update config
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "DEPRECATED: 'XCache' is not supported in PHP 7+. " .
                "The system has automatically fallen back to FileCache. " .
                "ACTION: Please change 'cache = xcache' to 'cache = file' or 'cache = apcu' in config.inc.php.",
                E_USER_DEPRECATED
            );
        }

        // Panggil konstruktor FileCache (Fallback)
        // Kita gunakan path standar CacheManager
        // [CONFIRMED] Mewarisi getContents() yang sudah diperbaiki dari FileCache
        $path = Core::getBaseDir() . DIRECTORY_SEPARATOR . 'cache';
        parent::__construct($context, $cacheId, $fallback, $path);
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param mixed $context
     * @param mixed $cacheId
     * @param mixed $fallback
     */
    public function XCacheCache($context, $cacheId, $fallback) {
        self::__construct($context, $cacheId, $fallback);
    }

}
?>
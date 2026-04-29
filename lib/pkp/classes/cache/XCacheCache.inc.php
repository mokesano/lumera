<?php
declare(strict_types=1);

/**
 * @file classes/cache/XCacheCache.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
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
     * [SHIM] Backward Compatibility
     */
    public function XCacheCache($context, $cacheId, $fallback) {
        self::__construct($context, $cacheId, $fallback);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/cache/CacheManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup cache
 * @see GenericCache
 *
 * @brief Provides cache management functions with APCu support, and .wiz Binary Cache.
 * 
 */

import('lib.pkp.classes.cache.FileCache');

define('CACHE_TYPE_FILE', 1);
define('CACHE_TYPE_OBJECT', 2);

class CacheManager {
    
    /**
     * Get the static instance of the cache manager.
     * @return CacheManager
     */
    public static function getManager() {
        $manager = Registry::get('cacheManager', true, null);
        if ($manager === null) {
            $manager = new CacheManager();
            Registry::set('cacheManager', $manager);
        }
        return $manager;
    }

    /**
     * Get a file cache.
     * @param string $context string
     * @param string $cacheId string
     * @param mixed $fallback callback
     * @return FileCache
     */
    public function getFileCache($context, $cacheId, $fallback) {
        return new FileCache(
            $context, $cacheId, $fallback,
            $this->getFileCachePath()
        );
    }

    /**
     * Get object cache.
     * @param string $context string
     * @param string $cacheId string
     * @param mixed $fallback callback
     * @return GenericCache
     */
    public function getObjectCache($context, $cacheId, $fallback) {
        return $this->getCache($context, $cacheId, $fallback, CACHE_TYPE_OBJECT);
    }

    /**
     * Get cache implementation type.
     * @param string $type string Type of cache: CACHE_TYPE_...
     * @return string|null
     */
    public function getCacheImplementation($type) {
        switch ($type) {
            case CACHE_TYPE_FILE: return 'file';
            case CACHE_TYPE_OBJECT: return Config::getVar('cache', 'object_cache');
            default: return null;
        }
    }

    /**
     * Get a cache.
     * @param string $context string
     * @param string $cacheId string
     * @param mixed $fallback callback
     * @param string $type string Type of cache: CACHE_TYPE_...
     * @return GenericCache
     */
    public function getCache($context, $cacheId, $fallback, $type = CACHE_TYPE_FILE) {
        $implementation = $this->getCacheImplementation($type);
        
        switch ($implementation) {
            case 'xcache':
                // [WIZDAM] XCache is dead in PHP 7+. Fallback to file to prevent crash.
                $cache = $this->getFileCache($context, $cacheId, $fallback);
                break;
                
            case 'apc':
            case 'apcu': // [MODERNISASI] Support config 'apcu' secara eksplisit
                // Ensure e-rename file APCCache.inc.php to APCuCache.inc.php
                import('lib.pkp.classes.cache.APCuCache');
                $cache = new APCuCache($context, $cacheId, $fallback);
                break;
                
            case 'memcache':
                import('lib.pkp.classes.cache.MemcacheCache');
                $cache = new MemcacheCache(
                    $context, $cacheId, $fallback,
                    Config::getVar('cache','memcache_hostname'),
                    Config::getVar('cache','memcache_port')
                );
                break;
                
            case '': // Provide a default if not specified
            case 'file':
                $cache = $this->getFileCache($context, $cacheId, $fallback);
                break;
                
            case 'none':
                import('lib.pkp.classes.cache.GenericCache');
                $cache = new GenericCache($context, $cacheId, $fallback);
                break;
                
            default:
                // [LUMERA] Safe Fallback if config has typo
                $cache = new GenericCache($context, $cacheId, $fallback);
                break;
        }
        return $cache;
    }

    /**
     * Get the path in which file caches will be stored.
     * @return string The full path to the file cache directory
     */
    public static function getFileCachePath() {
        return Core::getBaseDir() . DIRECTORY_SEPARATOR . 'cache';
    }

    /**
     * Flush an entire context, if specified, or the whole cache.
     * [LUMERA] Updated flush logic to clean .wiz files and handle APCu
     * @param $context string The context to flush, if only one is to be flushed
     * @param string $type string The type of cache to flush
     */
    public function flush($context = null, $type = CACHE_TYPE_FILE) {
        $cacheImplementation = $this->getCacheImplementation($type);
        
        switch ($cacheImplementation) {
            case 'xcache': // Dead
            case 'apc':
            case 'apcu':   // Modern
            case 'memcache':
                $junkCache = $this->getCache($context, null, null);
                $junkCache->flush();
                break;
                
            case 'file':
                $filePath = $this->getFileCachePath();
                
                // [MODERNISASI] Hapus file .wiz (Format Baru Wizdam)
                // Pola: fc-{context}-*.wiz
                $wizFiles = glob($filePath . DIRECTORY_SEPARATOR . 'fc-' . (isset($context) ? $context . '-' : '') . '*.wiz');
                if (is_array($wizFiles)) {
                    foreach ($wizFiles as $file) {
                        @unlink($file);
                    }
                }

                // [CLEANUP] Hapus file .php (Format Legacy)
                // Membersihkan sisa-sisa cache lama agar disk space lega
                $phpFiles = glob($filePath . DIRECTORY_SEPARATOR . 'fc-' . (isset($context) ? $context . '-' : '') . '*.php');
                if (is_array($phpFiles)) {
                    foreach ($phpFiles as $file) {
                        @unlink($file);
                    }
                }
                break;
                
            case '':
            case 'none':
                // Nothing necessary.
                break;
                
            default:
                break;
        }
        
        // [LUMERA] Bersihkan juga Smarty compiled & cache saat full flush
        if ($context === null) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->clearTemplateCache(); // clear t_compile & t_cache
        }
    }

}
?>
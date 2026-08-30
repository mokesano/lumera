<?php
declare(strict_types=1);

/**
 * @file classes/cache/MemcacheCache.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class MemcacheCache
 * @ingroup cache
 * @see GenericCache
 *
 * @brief Provides caching based on Memcache extension.
 */

import('lib.pkp.classes.cache.GenericCache');

/**
 * @brief Wrapper to store boolean false in Memcache.
 * Pseudotype class to represent boolean false values in Memcache.
 * Memcache cannot distinguish between a stored false and a cache miss,
 * so this wrapper object is used for serialization.
 */
class memcache_false {
}

/**
 * @brief Wrapper to store null in Memcache.
 * Pseudotype class to represent null values in Memcache.
 * Similar to memcache_false, this wrapper prevents ambiguity with cache misses.
 */
class memcache_null {
}

class MemcacheCache extends GenericCache {
    
    /**
     * Connection to use for caching.
     * @var object|null Memcache
     */
    public $connection;

    /**
     * Flag (used by Memcache::set)
     * @var int|null
     */
    public $flag;

    /**
     * Expiry (used by Memcache::set)
     * @var int
     */
    public $expire;

    /** @var bool */
    public $contextChecked = false;

    /**
     * Constructor.
     * Instantiate a Memcache connection.
     * @param string $context
     * @param string $cacheId
     * @param callable|null $fallback
     * @param string $hostname
     * @param int $port
     */
    public function __construct($context, $cacheId, $fallback, $hostname, $port) {
        parent::__construct($context, $cacheId, $fallback);
        
        if (class_exists('Memcache')) {
            $this->connection = new Memcache();
            if (!$this->connection->connect($hostname, $port)) {
                $this->connection = null;
            }
        } else {
            $this->connection = null;
        }

        $this->flag = null;
        $this->expire = 3600;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $context
     * @param string $cacheId
     * @param callable|null $fallback
     * @param string $hostname
     * @param int $port
     */
    public function MemcacheCache($context, $cacheId, $fallback, $hostname, $port) {
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
     * Set the compression flag.
     * @param int $flag
     */
    public function setFlag($flag) {
        $this->flag = $flag;
    }

    /**
     * Set the expiry time.
     * @param int $expiry
     */
    public function setExpiry($expiry) {
        $this->expire = $expiry;
    }

    /**
     * Flush all items from cache.
     */
    public function flush() {
        if ($this->connection) {
            $this->connection->flush();
        }
    }

    /**
     * Retrieve an object from cache.
     * @param string $id
     * @return mixed
     */
    public function getCache($id) {
        if ($id === null || !$this->connection) {
            return $this->cacheMiss;
        }

        $result = $this->connection->get($this->getContext() . ':' . $this->getCacheId() . ':' . $id);
        
        if ($result === false) {
            return $this->cacheMiss;
        }

        if (is_object($result)) {
            switch (get_class($result)) {
                case 'memcache_false':
                    $result = false;
                    break;
                case 'memcache_null':
                    $result = null;
                    break;
            }
        }
        
        return $result;
    }

    /**
     * Store an object in cache.
     * @param string $id
     * @param mixed $value
     * @return bool
     */
    public function setCache($id, $value) {
        if ($id === null || !$this->connection) {
            return false;
        }

        if ($value === false) {
            $value = new memcache_false();
        } elseif ($value === null) {
            $value = new memcache_null();
        }
        
        return $this->connection->set(
            $this->getContext() . ':' . $this->getCacheId() . ':' . $id,
            $value,
            $this->flag,
            $this->expire
        );
    }

    /**
     * Retrieve all cached contents.
     * @return array
     */
    public function getContents() {
        if (!$this->connection) {
            return [];
        }

        $contentsKey = $this->getContext() . ':' . $this->getCacheId() . ':_contents';
        $result = $this->connection->get($contentsKey);

        if ($result !== false && is_array($result)) {
            return $result;
        }

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
     * Get cache timestamp.
     * @return int|null
     */
    public function getCacheTime() {
        return null;
    }

    /**
     * Set entire cache contents.
     * @param array $contents
     */
    public function setEntireCache($contents) {
        if (!$this->connection) {
            return;
        }

        $this->flush();

        $contentsKey = $this->getContext() . ':' . $this->getCacheId() . ':_contents';
        $this->connection->set($contentsKey, $contents, $this->flag, $this->expire);

        foreach ($contents as $id => $value) {
            $this->setCache($id, $value);
        }
    }
    
    /**
     * Close connection and free resources.
     */
    public function close() {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
        $this->contextChecked = false;
    }
    
}
?>
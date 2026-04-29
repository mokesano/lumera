<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/.../classes/PubObjectCache.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubObjectCache
 * @ingroup plugins_importexport_..._classes
 *
 * @brief A cache for publication objects required during export.
 */

class PubObjectCache {
    
    /** @var array */
    protected array $_objectCache = [];

    /**
     * Constructor
     */
    public function __construct() {
        // Constructor logic if needed
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PubObjectCache() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    //
    // Public API
    //
    /**
     * Add a publishing object to the cache.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param PublishedArticle|null $parent Only required when adding a galley.
     */
    public function add($object, $parent = null) {
        if ($object instanceof Issue) {
            $this->_insertInternally($object, 'issues', (int) $object->getId());
        }
        if ($object instanceof PublishedArticle) {
            $this->_insertInternally($object, 'articles', (int) $object->getId());
            $this->_insertInternally($object, 'articlesByIssue', (int) $object->getIssueId(), (int) $object->getId());
        }
        if ($object instanceof ArticleGalley) {
            assert($parent instanceof PublishedArticle);
            $this->_insertInternally($object, 'galleys', (int) $object->getId());
            $this->_insertInternally($object, 'galleysByArticle', (int) $object->getArticleId(), (int) $object->getId());
            $this->_insertInternally($object, 'galleysByIssue', (int) $parent->getIssueId(), (int) $object->getId());
        }
        if ($object instanceof SuppFile) {
            $this->_insertInternally($object, 'suppFiles', (int) $object->getId());
            $this->_insertInternally($object, 'suppFilesByArticle', (int) $object->getArticleId(), (int) $object->getId());
        }
    }

    /**
     * Marks the given cache id "complete", i.e. it
     * contains all child objects for the given object
     * id.
     *
     * @param string $cacheId
     * @param int $objectId
     */
    public function markComplete(string $cacheId, int $objectId): void {
        assert(isset($this->_objectCache[$cacheId][$objectId]) && is_array($this->_objectCache[$cacheId][$objectId]));
        $this->_objectCache[$cacheId][$objectId]['complete'] = true;

        // Order objects in the completed cache by ID.
        ksort($this->_objectCache[$cacheId][$objectId]);
    }

    /**
     * Retrieve (an) object(s) from the cache.
     * NB: You must check whether an object is in the cache
     * before you try to retrieve it with this method.
     *
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     * @return mixed
     */
    public function get(string $cacheId, int $id1, ?int $id2 = null) {
        assert($this->isCached($cacheId, $id1, $id2));
        if ($id2 === null) {
            $returner = $this->_objectCache[$cacheId][$id1];
            if (is_array($returner)) {
                unset($returner['complete']);
            }
            return $returner;
        } else {
            return $this->_objectCache[$cacheId][$id1][$id2];
        }
    }

    /**
     * Check whether a given object is in the cache.
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     * @return bool
     */
    public function isCached(string $cacheId, int $id1, ?int $id2 = null): bool {
        if (!isset($this->_objectCache[$cacheId])) return false;

        // $id1 is int via type hint
        if ($id2 === null) {
            if (!isset($this->_objectCache[$cacheId][$id1])) return false;
            if (is_array($this->_objectCache[$cacheId][$id1])) {
                return isset($this->_objectCache[$cacheId][$id1]['complete']);
            } else {
                return true;
            }
        } else {
            // $id2 is int via type hint
            return isset($this->_objectCache[$cacheId][$id1][$id2]);
        }
    }

    //
    // Private helper methods
    //
    /**
     * Insert an object into the cache.
     * @param object $object
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     */
    protected function _insertInternally($object, string $cacheId, int $id1, ?int $id2 = null): void {
        if ($this->isCached($cacheId, $id1, $id2)) return;

        if (!isset($this->_objectCache[$cacheId])) {
            $this->_objectCache[$cacheId] = [];
        }

        if ($id2 === null) {
            $this->_objectCache[$cacheId][$id1] = $object;
        } else {
            if (!isset($this->_objectCache[$cacheId][$id1])) {
                $this->_objectCache[$cacheId][$id1] = [];
            }
            $this->_objectCache[$cacheId][$id1][$id2] = $object;
        }
    }
}

?>
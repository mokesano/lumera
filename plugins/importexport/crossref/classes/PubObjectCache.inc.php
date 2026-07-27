<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/crossref/classes/PubObjectCache.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PubObjectCache
 * @ingroup plugins_importexport_crossref_classes
 *
 * @brief A cache for publication objects required during export.
 */

class PubObjectCache {

    /** 
     * @var array<string, array<int, mixed>|mixed> 
     */
    protected array $_objectCache = [];

    //
    // Public API
    //

    /**
     * Add a publishing object to the cache.
     * @param Issue|PublishedArticle|ArticleGalley|SuppFile $object
     * @param PublishedArticle|null $parent
     */
    public function add($object, $parent) {
        if ($object instanceof Issue) {
            $this->_insertInternally($object, 'issues', (int) $object->getId());
        }
        if ($object instanceof PublishedArticle) {
            $this->_insertInternally($object, 'articles', (int) $object->getId());
            $this->_insertInternally($object, 'articlesByIssue', (int) $object->getIssueId(), (int) $object->getId());
        }
        if ($object instanceof ArticleGalley) {
            // [WIZDAM FIX] Replace assert with explicit check to prevent fatal errors and satisfy linters
            if ($parent instanceof PublishedArticle) {
                $this->_insertInternally($object, 'galleys', (int) $object->getId());
                $this->_insertInternally($object, 'galleysByArticle', (int) $object->getArticleId(), (int) $object->getId());
                $this->_insertInternally($object, 'galleysByIssue', (int) $parent->getIssueId(), (int) $object->getId());
            }
        }
        if ($object instanceof SuppFile) {
            $this->_insertInternally($object, 'suppFiles', (int) $object->getId());
            $this->_insertInternally($object, 'suppFilesByArticle', (int) $object->getArticleId(), (int) $object->getId());
        }
    }

    /**
     * Mark a set of objects as completely cached.
     * @param string $cacheId
     * @param int $objectId
     */
    public function markComplete($cacheId, $objectId) {
        $objectId = (int) $objectId;
        if (isset($this->_objectCache[$cacheId][$objectId]) && is_array($this->_objectCache[$cacheId][$objectId])) {
            $this->_objectCache[$cacheId][$objectId]['complete'] = true;

            // Order objects in the completed cache by ID.
            ksort($this->_objectCache[$cacheId][$objectId]);
        }
    }

    /**
     * Retrieve a cached object.
     * 
     * NB: You should check whether an object is in the cache
     * with isCached() before you try to retrieve it with this method.
     *
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     * @return mixed
     */
    public function get($cacheId, $id1, $id2 = null) {
        $id1 = (int) $id1;
        
        if ($id2 === null) {
            if (isset($this->_objectCache[$cacheId][$id1])) {
                $returner = $this->_objectCache[$cacheId][$id1];
                if (is_array($returner)) {
                    unset($returner['complete']);
                }
                return $returner;
            }
            return null;
        } else {
            $id2 = (int) $id2;
            if (isset($this->_objectCache[$cacheId][$id1][$id2])) {
                return $this->_objectCache[$cacheId][$id1][$id2];
            }
            return null;
        }
    }

    /**
     * Check whether a given object is in the cache.
     *
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     * @return bool
     */
    public function isCached($cacheId, $id1, $id2 = null): bool {
        if (!isset($this->_objectCache[$cacheId])) {
            return false;
        }

        $id1 = (int) $id1;
        if ($id2 === null) {
            if (!isset($this->_objectCache[$cacheId][$id1])) {
                return false;
            }
            if (is_array($this->_objectCache[$cacheId][$id1])) {
                return isset($this->_objectCache[$cacheId][$id1]['complete']);
            }
            return true;
        } else {
            $id2 = (int) $id2;
            return isset($this->_objectCache[$cacheId][$id1][$id2]);
        }
    }

    //
    // Private helper methods
    //

    /**
     * Insert an object into the cache.
     *
     * @param mixed $object
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     */
    public function _insertInternally($object, $cacheId, $id1, $id2 = null) {
        if ($this->isCached($cacheId, $id1, $id2)) {
            return;
        }

        if (!isset($this->_objectCache[$cacheId])) {
            $this->_objectCache[$cacheId] = [];
        }

        $id1 = (int) $id1;
        if ($id2 === null) {
            $this->_objectCache[$cacheId][$id1] = $object;
        } else {
            $id2 = (int) $id2;
            if (!isset($this->_objectCache[$cacheId][$id1])) {
                $this->_objectCache[$cacheId][$id1] = [];
            }
            $this->_objectCache[$cacheId][$id1][$id2] = $object;
        }
    }
    
}
?>
<?php
declare(strict_types=1);

/**
 * @file plugins/importexport/medra/classes/O4DOIObjectCache.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class O4DOIObjectCache
 * @ingroup plugins_importexport_medra_classes
 *
 * @brief A cache for publication objects required during O4DOI export.
 */

class O4DOIObjectCache {
    /** @var array */
    protected $_objectCache = [];

    /**
     * Constructor
     */
    public function __construct() {
        // Constructor logic if needed
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function O4DOIObjectCache() {
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
     * @param Issue|PublishedArticle|ArticleGalley $object
     * @param PublishedArticle|null $parent Only required when adding a galley.
     */
    public function add($object, $parent) {
        if ($object instanceof Issue) {
            $this->_insertInternally($object, 'issues', $object->getId());
        }
        if ($object instanceof PublishedArticle) {
            $this->_insertInternally($object, 'articles', $object->getId());
            $this->_insertInternally($object, 'articlesByIssue', $object->getIssueId(), $object->getId());
        }
        if ($object instanceof ArticleGalley) {
            assert($parent instanceof PublishedArticle);
            $this->_insertInternally($object, 'galleys', $object->getId());
            $this->_insertInternally($object, 'galleysByArticle', $object->getArticleId(), $object->getId());
            $this->_insertInternally($object, 'galleysByIssue', $parent->getIssueId(), $object->getId());
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
    public function markComplete($cacheId, $objectId) {
        assert(isset($this->_objectCache[$cacheId][$objectId]) && is_array($this->_objectCache[$cacheId][$objectId]));
        $this->_objectCache[$cacheId][$objectId]['complete'] = true;

        // Order objects in the completed cache by ID.
        ksort($this->_objectCache[$cacheId][$objectId]);
    }

    /**
     * Retrieve (an) object(s) from the cache.
     *
     * NB: You must check whether an object is in the cache
     * before you try to retrieve it with this method.
     *
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     *
     * @return mixed
     */
    public function get($cacheId, $id1, $id2 = null) {
        assert($this->isCached($cacheId, $id1, $id2));
        if (is_null($id2)) {
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
     *
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     *
     * @return bool
     */
    public function isCached($cacheId, $id1, $id2 = null): bool {
        if (!isset($this->_objectCache[$cacheId])) return false;

        $id1 = (int)$id1;
        if (is_null($id2)) {
            if (!isset($this->_objectCache[$cacheId][$id1])) return false;
            if (is_array($this->_objectCache[$cacheId][$id1])) {
                return isset($this->_objectCache[$cacheId][$id1]['complete']);
            } else {
                return true;
            }
        } else {
            $id2 = (int)$id2;
            return isset($this->_objectCache[$cacheId][$id1][$id2]);
        }
    }

    //
    // Private helper methods
    //
    /**
     * Insert an object into the cache.
     *
     * @param object $object
     * @param string $cacheId
     * @param int $id1
     * @param int|null $id2
     */
    protected function _insertInternally($object, $cacheId, $id1, $id2 = null) {
        if ($this->isCached($cacheId, $id1, $id2)) return;

        if (!isset($this->_objectCache[$cacheId])) {
            $this->_objectCache[$cacheId] = [];
        }

        $id1 = (int)$id1;
        if (is_null($id2)) {
            $this->_objectCache[$cacheId][$id1] = $object;
        } else {
            $id2 = (int)$id2;
            if (!isset($this->_objectCache[$cacheId][$id1])) {
                $this->_objectCache[$cacheId][$id1] = [];
            }
            $this->_objectCache[$cacheId][$id1][$id2] = $object;
        }
    }
}

?>
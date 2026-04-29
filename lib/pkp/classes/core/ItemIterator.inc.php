<?php
declare(strict_types=1);

/**
 * @file classes/core/ItemIterator.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ItemIterator
 * @ingroup db
 *
 * @brief Generic iterator class; needs to be overloaded by subclasses
 * providing specific implementations.
 */


class ItemIterator {
    
    /**
     * Return the next item in the iterator.
     * @return mixed
     */
    public function next() {
        return null;
    }

    /**
     * Return the next item with key.
     * @return array ($key, $value);
     */
    public function nextWithKey() {
        return array(null, null);
    }

    /**
     * Determine whether this iterator represents the first page of a set.
     * @return boolean
     */
    public function atFirstPage() {
        return true;
    }

    /**
     * Determine whether this iterator represents the last page of a set.
     * @return boolean
     */
    public function atLastPage() {
        return true;
    }

    /**
     * Get the page number of a set that this iterator represents.
     * @return int
     */
    public function getPage() {
        return 1;
    }

    /**
     * Get the total number of items in the set.
     * @return int
     */
    public function getCount() {
        return 0;
    }

    /**
     * Get the total number of pages in the set.
     * @return int
     */
    public function getPageCount() {
        return 0;
    }

    /**
     * Return a boolean indicating whether or not we've reached the end of results
     * @return boolean
     */
    public function eof() {
        return true;
    }

    /**
     * Return a boolean indicating whether or not this iterator was empty from the beginning
     * @return boolean
     */
    public function wasEmpty() {
        return true;
    }

    /**
     * Convert this iterator to an array.
     * @return array
     */
    public function toArray() {
        return array();
    }
}

?>
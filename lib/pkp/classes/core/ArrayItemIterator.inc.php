<?php
declare(strict_types=1);

/**
 * @file classes/core/ArrayItemIterator.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArrayItemIterator
 * @ingroup db
 *
 * @brief Provides paging and iteration for arrays.
 */


import('lib.pkp.classes.core.ItemIterator');

class ArrayItemIterator extends ItemIterator {
    /** @var array The array of contents of this iterator. */
    public $theArray;

    /** @var int Number of items to iterate through on this page */
    public $itemsPerPage;

    /** @var int The current page. */
    public $page;

    /** @var int The total number of items. */
    public $count;

    /** @var bool Whether or not the iterator was empty from the start */
    public $wasEmpty;

    /**
     * Constructor.
     * [MODERNISASI] Native Constructor
     * @param $theArray array The array of items to iterate through
     * @param $page int the current page number
     * @param $itemsPerPage int Number of items to display per page
     */
    public function __construct($theArray, $page=-1, $itemsPerPage=-1) {
        if ($page>=1 && $itemsPerPage>=1) {
            $this->theArray = $this->array_slice_key($theArray, ($page-1) * $itemsPerPage, $itemsPerPage);
            $this->page = (int) $page;
        } else {
            $this->theArray = $theArray;
            $this->page = 1;
            $this->itemsPerPage = max(count($this->theArray),1);
        }
        $this->count = count($theArray);
        $this->itemsPerPage = (int) $itemsPerPage;
        $this->wasEmpty = count($this->theArray)==0;
        reset($this->theArray);
    }

    /**
     * Legacy Constructor Shim.
     */
    public function ArrayItemIterator($theArray, $page=-1, $itemsPerPage=-1) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor ArrayItemIterator(). Please refactor to use __construct().",
            E_USER_DEPRECATED
        );
        self::__construct($theArray, $page, $itemsPerPage);
    }

    /**
     * Static method: Generate an iterator from an array and rangeInfo object.
     * [MODERNISASI] Static public method, removed & reference
     * @param $theArray array
     * @param $theRange object
     */
    public static function fromRangeInfo($theArray, $theRange) {
        if ($theRange && $theRange->isValid()) {
            $theIterator = new ArrayItemIterator($theArray, $theRange->getPage(), $theRange->getCount());
        } else {
            $theIterator = new ArrayItemIterator($theArray);
        }
        return $theIterator;
    }

    /**
     * Return the next item in the iterator.
     * [MODERNISASI] Removed & reference
     * @return object
     */
    public function next() {
        if (!is_array($this->theArray)) {
            $value = null;
            return $value;
        }
        $value = current($this->theArray);
        if (next($this->theArray)===false) {
            $this->theArray = null;
        }
        return $value;
    }

    /**
     * Return the next item in the iterator, with key.
     * [MODERNISASI] Removed & reference
     * @return array (key, value)
     */
    public function nextWithKey() {
        $key = key($this->theArray);
        $value = $this->next();
        return array($key, $value);
    }

    /**
     * Determine whether or not this iterator represents the first page
     * @return boolean
     */
    public function atFirstPage() {
        return $this->page==1;
    }

    /**
     * Determine whether or not this iterator represents the last page
     * @return boolean
     */
    public function atLastPage() {
        return ($this->page * $this->itemsPerPage) + 1 > $this->count;
    }

    /**
     * Get the current page number
     * @return int
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Get the total count of items
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * Get the number of pages
     * @return int
     */
    public function getPageCount() {
        return max(1, ceil($this->count / $this->itemsPerPage));
    }

    /**
     * Return a boolean indicating whether or not we've reached the end of results
     * @return boolean
     */
    public function eof() {
        return (($this->theArray == null) || (count($this->theArray)==0));
    }

    /**
     * Return a boolean indicating whether or not this iterator was empty from the beginning
     * @return boolean
     */
    public function wasEmpty() {
        return $this->wasEmpty;
    }

    /**
     * Convert this iterator to an array
     * [MODERNISASI] Removed & reference
     * @return array
     */
    public function toArray() {
        return $this->theArray;
    }

    /**
     * Determine whether or not the iterator is within bounds.
     * @return boolean
     */
    public function isInBounds() {
        return ($this->getPageCount() >= $this->page);
    }

    /**
     * Get the range info representing the last page of results.
     * [MODERNISASI] Removed & reference
     * @return object DBResultRange
     */
    public function getLastPageRangeInfo() {
        import('lib.pkp.classes.db.DBResultRange');
        $returner = new DBResultRange(
            $this->itemsPerPage,
            $this->getPageCount()
        );
        return $returner;
    }

    /**
     * A version of array_slice that takes keys into account.
     * [MODERNISASI] Added public visibility
     * @see http://ca3.php.net/manual/en/function.array-slice.php
     */
    public function array_slice_key($array, $offset, $len=-1) {
        if (!is_array($array)) return false;

        $return = array();
        $length = $len >= 0? $len: count($array);
        $keys = array_slice(array_keys($array), $offset, $length);
        foreach($keys as $key) {
            $return[$key] = $array[$key];
        }

        return $return;
    }
}

?>
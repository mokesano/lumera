<?php
declare(strict_types=1);

/**
 * @file classes/core/VirtualArrayIterator.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class VirtualArrayIterator
 * @ingroup db
 *
 * @brief Provides paging and iteration for "virtual" arrays -- arrays for which only
 * the current "page" is available, but are much bigger in entirety.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

import('lib.pkp.classes.core.ItemIterator');

class VirtualArrayIterator extends ItemIterator {
    /** * @var array|null The array of contents of this iterator. 
     * [WIZDAM] Public for legacy access.
     */
    public ?array $theArray = [];

    /** * @var int Number of items to iterate through on this page */
    public int $itemsPerPage = 0;

    /** * @var int The current page. */
    public int $page = 0;

    /** * @var int The total number of items. */
    public int $count = 0;

    /** * @var bool Whether or not the iterator was empty from the start */
    public bool $wasEmpty = true;

    /**
     * Constructor.
     * @param array $theArray The array of items to iterate through
     * @param int $totalItems The total number of items in the virtual "larger" array
     * @param int $page the current page number
     * @param int $itemsPerPage Number of items to display per page
     */
    public function __construct(array $theArray, int $totalItems, int $page = -1, int $itemsPerPage = -1) {
        if ($page >= 1 && $itemsPerPage >= 1) {
            $this->page = $page;
            $this->itemsPerPage = $itemsPerPage;
        } else {
            $this->page = 1;
            // Prevent division by zero or negative logic later
            $this->itemsPerPage = max(count($theArray), 1);
        }
        
        $this->theArray = $theArray;
        $this->count = $totalItems;
        $this->wasEmpty = count($this->theArray) === 0;
        
        if (!empty($this->theArray)) {
            reset($this->theArray);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function VirtualArrayIterator($theArray, $totalItems, $page = -1, $itemsPerPage = -1) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
             trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $safeArray = is_array($theArray) ? $theArray : [];
        self::__construct($safeArray, (int)$totalItems, (int)$page, (int)$itemsPerPage);
    }

    /**
     * Factory Method.
     * @param array $wholeArray The whole array of items
     * @param object $rangeInfo The number of items per page (DBResultRange)
     * @return VirtualArrayIterator
     */
    public static function factory(array $wholeArray, $rangeInfo): VirtualArrayIterator {
        $slicedArray = $wholeArray;
        
        if (is_object($rangeInfo) && method_exists($rangeInfo, 'isValid') && $rangeInfo->isValid()) {
            $offset = $rangeInfo->getCount() * ($rangeInfo->getPage() - 1);
            $length = $rangeInfo->getCount();
            $slicedArray = array_slice($wholeArray, $offset, $length);
            
            return new VirtualArrayIterator(
                $slicedArray, 
                count($wholeArray), 
                $rangeInfo->getPage(), 
                $rangeInfo->getCount()
            );
        }

        return new VirtualArrayIterator($wholeArray, count($wholeArray));
    }

    /**
     * Return the next item in the iterator.
     * [WIZDAM] Removed reference (&) to match Parent ItemIterator.
     * @return mixed
     */
    public function next() {
        if (!is_array($this->theArray)) {
            return null;
        }
        
        $value = current($this->theArray);
        
        // Advance internal array pointer
        // If next() returns false AND key is null, we have reached the end.
        if (next($this->theArray) === false && key($this->theArray) === null) {
            $this->theArray = null;
        }
        
        return $value;
    }

    /**
     * Return the next item in the iterator, with key.
     * [WIZDAM] Removed reference (&) to match Parent ItemIterator.
     * @return array (key, value)
     */
    public function nextWithKey() {
        if (!is_array($this->theArray)) {
             return [null, null]; // Match parent signature return format
        }

        $key = key($this->theArray);
        $value = $this->next(); // This advances the pointer
        
        return [$key, $value];
    }

    /**
     * Check whether or not this iterator is for the first page of a sequence
     * @return bool
     */
    public function atFirstPage(): bool {
        return $this->page === 1;
    }

    /**
     * Check whether or not this iterator is for the last page of a sequence
     * @return bool
     */
    public function atLastPage(): bool {
        return ($this->page * $this->itemsPerPage) + 1 > $this->count;
    }

    /**
     * Get the page number that this iterator represents
     * @return int
     */
    public function getPage(): int {
        return $this->page;
    }

    /**
     * Get the total number of items in the virtual array
     * @return int
     */
    public function getCount(): int {
        return $this->count;
    }

    /**
     * Get the total number of pages in the virtual array
     * @return int
     */
    public function getPageCount(): int {
        if ($this->itemsPerPage === 0) return 1;
        return (int) max(1, ceil($this->count / $this->itemsPerPage));
    }

    /**
     * Return a boolean indicating whether or not we've reached the end of results
     * @return bool
     */
    public function eof(): bool {
        return (($this->theArray === null) || (count($this->theArray) === 0));
    }

    /**
     * Return a boolean indicating whether or not this iterator was empty from the beginning
     * @return bool
     */
    public function wasEmpty(): bool {
        return $this->wasEmpty;
    }

    /**
     * Convert the iterator into an array
     * [WIZDAM] Removed reference (&) to match Parent ItemIterator.
     * @return array
     */
    public function toArray() {
        // Return empty array if null to strictly match return type array
        return $this->theArray ?? [];
    }
}
?>
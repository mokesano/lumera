<?php
declare(strict_types=1);

/**
 * @file classes/db/DBResultRange.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DBResultRange
 * @ingroup db
 *
 * @brief Container class for range information when retrieving a result set.
 */

class DBResultRange {
    
    /** @var int The number of items to display */
    public $count;

    /** @var int The number of pages to skip */
    public $page;

    /**
     * Constructor.
     * Initialize the DBResultRange.
     * @param $count int
     * @param $page int
     */
    public function __construct($count, $page = 1) {
        $this->count = (int) $count;
        $this->page = (int) $page;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DBResultRange($count, $page = 1) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor DBResultRange(). Please refactor to use __construct().",
            E_USER_DEPRECATED
        );
        self::__construct($count, $page);
    }

    /**
     * Checks to see if the DBResultRange is valid.
     * @return boolean
     */
    public function isValid() {
        return (($this->count>0) && ($this->page>=0));
    }

    /**
     * Returns the count of pages to skip.
     * @return int
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Set the count of pages to skip.
     * @param $page int
     */
    public function setPage($page) {
        $this->page = (int) $page;
    }

    /**
     * Returns the count of items in this range to display.
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * Set the count of items in this range to display.
     * @param $count int
     */
    public function setCount($count) {
        $this->count = (int) $count;
    }
}

?>
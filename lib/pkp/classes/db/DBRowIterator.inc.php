<?php
declare(strict_types=1);

/**
 * @file classes/db/DBRowIterator.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DBRowIterator
 * @ingroup db
 *
 * @brief Wrapper around ADORecordSet providing "factory" features 
 * for generating objects from DAOs.
 */

import('lib.pkp.classes.core.ItemIterator');

class DBRowIterator extends ItemIterator {
    
    /** @var \ADORecordSet|null The ADORecordSet to be wrapped around */
    public $records;

    /** @var array An array of primary key field names that uniquely identify a result row */
    public $idFields;

    /** @var bool True iff the resultset was always empty */
    public $wasEmpty;

    /** @var bool */
    public $isFirst;

    /** @var bool */
    public $isLast;

    /** @var int */
    public $page;

    /** @var int */
    public $count;

    /** @var int */
    public $pageCount;

    /**
     * Constructor.
     * Initialize the DBRowIterator
     * 
     * @param \ADORecordSet|null $records ADO record set
     * @param array $idFields An array of primary key field names
     */
    public function __construct($records, $idFields = []) {
        $this->idFields = $idFields;

        if ($records === null || $records->EOF) {
            if ($records !== null) {
                $records->Close();
            }
            $this->records = null;
            $this->wasEmpty = true;
            $this->page = 1;
            $this->isFirst = true;
            $this->isLast = true;
            $this->count = 0;
            $this->pageCount = 1;
        } else {
            $this->records = $records;
            $this->wasEmpty = false;
            $this->page = $records->AbsolutePage();
            $this->isFirst = $records->atFirstPage();
            $this->isLast = $records->atLastPage();
            $this->count = $records->MaxRecordCount();
            $this->pageCount = $records->LastPageNo();
        }
    }

    /**
     * [SHIM] Backward Compatibility
     * 
     * @param \ADORecordSet|null $records
     * @param array $idFields
     */
    public function DBRowIterator($records, $idFields = []) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($records, $idFields);
    }

    /**
     * Return the object representing the next row.
     * 
     * @return array|null
     */
    public function next() {
        if ($this->records === null) {
            return null;
        }
        
        if (!$this->records->EOF) {
            $row = $this->records->getRowAssoc(false);
            if (!$this->records->MoveNext()) {
                $this->_cleanup();
            }
            return $row;
        } else {
            $this->_cleanup();
            return null;
        }
    }

    /**
     * Return the next row, with key.
     * 
     * @return array [$key, $value]
     */
    public function nextWithKey() {
        $result = $this->next();
        
        // Internal coercion / null safety: prevent fatal error if $result is null
        if ($result === null) {
            return [null, null];
        }

        if (empty($this->idFields)) {
            $key = null;
        } else {
            $key = '';
            foreach ($this->idFields as $idField) {
                $fieldValue = $result[$idField] ?? '';
                if ($key !== '') {
                    $key .= '-';
                }
                $key .= (string) $fieldValue;
            }
        }
        
        return [$key, $result];
    }

    /**
     * Determine whether this iterator represents the first page of a set.
     * 
     * @return bool
     */
    public function atFirstPage() {
        return $this->isFirst;
    }

    /**
     * Determine whether this iterator represents the last page of a set.
     * 
     * @return bool
     */
    public function atLastPage() {
        return $this->isLast;
    }

    /**
     * Get the page number of a set that this iterator represents.
     * 
     * @return int
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Get the total number of items in the set.
     * 
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * Get the total number of pages in the set.
     * 
     * @return int
     */
    public function getPageCount() {
        return $this->pageCount;
    }

    /**
     * Return a boolean indicating whether or not we've reached the end of results
     * 
     * @return bool
     */
    public function eof() {
        if ($this->records === null) {
            return true;
        }
        if ($this->records->EOF) {
            $this->_cleanup();
            return true;
        }
        return false;
    }

    /**
     * Return a boolean indicating whether or not this resultset was empty from the beginning
     * 
     * @return bool
     */
    public function wasEmpty() {
        return $this->wasEmpty;
    }

    /**
     * Clean up the record set to free resources.
     * This is called internally when the iterator reaches EOF.
     */
    protected function _cleanup() {
        if ($this->records !== null) {
            $this->records->Close();
            unset($this->records);
            $this->records = null;
        }
    }

    /**
     * Convert this iterator to an array.
     * 
     * @return array
     */
    public function toArray() {
        $returner = [];
        while (!$this->eof()) {
            $returner[] = $this->next();
        }
        return $returner;
    }

}
?>
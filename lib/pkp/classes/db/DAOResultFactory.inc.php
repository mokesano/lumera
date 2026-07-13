<?php
declare(strict_types=1);

/**
 * @file classes/db/DAOResultFactory.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DAOResultFactory
 * @ingroup db
 *
 * @brief Wrapper around ADORecordSet providing "factory" features for
 * generating objects from DAOs.
 */

import('lib.pkp.classes.core.ItemIterator');

class DAOResultFactory extends ItemIterator {
    
    /** @var DAO|null The DAO used to create objects */
    public $dao;

    /** @var string The name of the DAO's factory function */
    public $functionName;

    /** @var array An array of primary key field names that uniquely identify a result row */
    public $idFields;

    /** @var object|null The ADORecordSet to be wrapped around */
    public $records;

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
     * Initialize the DAOResultFactory
     * 
     * @param object|null $records ADO record set
     * @param DAO $dao DAO class for factory
     * @param string $functionName 
     * @param array $idFields An array of primary key field names
     */
    public function __construct($records, $dao, $functionName, $idFields = []) {
        $this->functionName = $functionName;
        $this->dao = $dao;
        $this->idFields = $idFields;

        if (!$records || $records->EOF) {
            if ($records) {
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
     * [SHIM] Backward compatibility.
     * @param object|null $records
     * @param DAO $dao DAO
     * @param string $functionName
     */
    public function DAOResultFactory($records, $dao, $functionName, $idFields = []) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($records, $dao, $functionName, $idFields);
    }

    /**
     * Advances the internal cursor to a specific row.
     * 
     * @param int $to
     * @return bool
     */
    public function move($to) {
        if ($this->records === null) {
            return false;
        }
        return (bool) $this->records->Move($to);
    }

    /**
     * Return the object representing the next row.
     * 
     * @return object|null
     */
    public function next() {
        if ($this->records === null) {
            return null;
        }
        
        if (!$this->records->EOF) {
            $functionName = $this->functionName;
            $dao = $this->dao;
            $row = $this->records->getRowAssoc(false);
            $result = $dao->$functionName($row);
            
            if (!$this->records->MoveNext()) {
                $this->_cleanup();
            }
            return $result;
        } else {
            $this->_cleanup();
            return null;
        }
    }

    /**
     * Return the next row, with key.
     * 
     * @param string|null $idField
     * @return array [$key, $value]
     */
    public function nextWithKey($idField = null) {
        $result = $this->next();
        
        if ($result === null) {
            return [null, null];
        }

        if ($idField !== null) {
            $key = $result->getData($idField);
        } elseif (empty($this->idFields)) {
            $key = null;
        } else {
            $key = '';
            foreach ($this->idFields as $field) {
                $fieldValue = $result->getData($field);
                if ($key !== '') {
                    $key .= '-';
                }
                // Internal coercion to string to prevent type errors
                $key .= (string) ($fieldValue ?? '');
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
     * PRIVATE function used internally to clean up the record set.
     * This is called aggressively because it can free resources.
     */
    public function _cleanup() {
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

    /**
     * Convert this iterator to an associative array by database ID.
     * 
     * @param string $idField
     * @return array
     */
    public function toAssociativeArray($idField = 'id') {
        $returner = [];
        while (!$this->eof()) {
            $result = $this->next();
            if ($result !== null && is_object($result) && method_exists($result, 'getData')) {
                $returner[$result->getData($idField)] = $result;
            }
        }
        return $returner;
    }

    /**
     * Determine whether or not this iterator is in the range of pages for the set it represents
     * 
     * @return bool
     */
    public function isInBounds() {
        return ($this->pageCount >= $this->page);
    }

    /**
     * Get the RangeInfo representing the last page in the set.
     * 
     * @return DBResultRange
     */
    public function getLastPageRangeInfo() {
        import('lib.pkp.classes.db.DBResultRange');
        return new DBResultRange($this->count, $this->pageCount);
    }

}
?>
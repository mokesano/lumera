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
 * @brief Wrapper around ADORecordSet providing "factory" features for generating
 * objects from DAOs.
 */

import('lib.pkp.classes.core.ItemIterator');

class DAOResultFactory extends ItemIterator {
    
    /** @var DAO The DAO used to create objects */
    public $dao;

    /** @var string The name of the DAO's factory function (to be called with an associative array of values) */
    public $functionName;

    /**
     * @var array an array of primary key field names that uniquely
     * identify a result row in the record set.
     */
    public $idFields;

    /** @var object The ADORecordSet to be wrapped around */
    public $records;

    /** @var bool True iff the resultset was always empty */
    public $wasEmpty;

    public $isFirst;
    public $isLast;
    public $page;
    public $count;
    public $pageCount;

    /**
     * Constructor.
     * Initialize the DAOResultFactory
     * @param $records object ADO record set
     * @param $dao object DAO class for factory
     * @param $functionName string 
     * @param $idFields array an array of primary key field names
     */
    public function __construct($records, $dao, $functionName, $idFields = array()) {
        $this->functionName = $functionName;
        $this->dao = $dao;
        $this->idFields = $idFields;

        if (!$records || $records->EOF) {
            if ($records) $records->Close();
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
     */
    public function DAOResultFactory($records, $dao, $functionName, $idFields = array()) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::DAOResultFactory(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($records, $dao, $functionName, $idFields);
    }

    /**
     * Advances the internal cursor to a specific row.
     * @param int $to
     * @return boolean
     */
    public function move($to) {
        if ($this->records == null) return false;
        if ($this->records->Move($to))
            return true;
        else
            return false;
    }

    /**
     * Return the object representing the next row.
     * @return object
     */
    public function next() {
        if ($this->records == null) return $this->records;
        if (!$this->records->EOF) {
            $functionName = $this->functionName;
            $dao = $this->dao;
            $row = $this->records->getRowAssoc(false);
            $result = $dao->$functionName($row);
            if (!$this->records->MoveNext()) $this->_cleanup();
            return $result;
        } else {
            $this->_cleanup();
            return null;
        }
    }

    /**
     * Return the next row, with key.
     * @return array ($key, $value)
     */
    public function nextWithKey($idField = null) {
        $result = $this->next();
        if($idField) {
            assert(is_a($result, 'DataObject'));
            $key = $result->getData($idField);
        } elseif (empty($this->idFields)) {
            $key = null;
        } else {
            assert(is_a($result, 'DataObject') && is_array($this->idFields));
            $key = '';
            foreach($this->idFields as $idField) {
                assert(!is_null($result->getData($idField)));
                if (!empty($key)) $key .= '-';
                $key .= (string)$result->getData($idField);
            }
        }
        $returner = array($key, $result);
        return $returner;
    }

    /**
     * Determine whether this iterator represents the first page of a set.
     * @return boolean
     */
    public function atFirstPage() {
        return $this->isFirst;
    }

    /**
     * Determine whether this iterator represents the last page of a set.
     * @return boolean
     */
    public function atLastPage() {
        return $this->isLast;
    }

    /**
     * Get the page number of a set that this iterator represents.
     * @return int
     */
    public function getPage() {
        return $this->page;
    }

    /**
     * Get the total number of items in the set.
     * @return int
     */
    public function getCount() {
        return $this->count;
    }

    /**
     * Get the total number of pages in the set.
     * @return int
     */
    public function getPageCount() {
        return $this->pageCount;
    }

    /**
     * Return a boolean indicating whether or not we've reached the end of results
     * @return boolean
     */
    public function eof() {
        if ($this->records == null) return true;
        if ($this->records->EOF) {
            $this->_cleanup();
            return true;
        }
        return false;
    }

    /**
     * Return a boolean indicating whether or not this resultset was empty from the beginning
     * @return boolean
     */
    public function wasEmpty() {
        return $this->wasEmpty;
    }

    /**
     * PRIVATE function used internally to clean up the record set.
     * This is called aggressively because it can free resources.
     */
    public function _cleanup() {
        if ($this->records) {
            $this->records->close();
            unset($this->records);
            $this->records = null;
        }
    }

    /**
     * Convert this iterator to an array.
     * @return array
     */
    public function toArray() {
        $returner = array();
        while (!$this->eof()) {
            $returner[] = $this->next();
        }
        return $returner;
    }

    /**
     * Convert this iterator to an associative array by database ID.
     * @return array
     */
    public function toAssociativeArray($idField = 'id') {
        $returner = array();
        while (!$this->eof()) {
            $result = $this->next();
            $returner[$result->getData($idField)] = $result;
            unset($result);
        }
        return $returner;
    }

    /**
     * Determine whether or not this iterator is in the range of pages for the set it represents
     * @return boolean
     */
    public function isInBounds() {
        return ($this->pageCount >= $this->page);
    }

    /**
     * Get the RangeInfo representing the last page in the set.
     * @return object
     */
    public function getLastPageRangeInfo() {
        import('lib.pkp.classes.db.DBResultRange');
        $returner = new DBResultRange($this->count, $this->pageCount);
        return $returner;
    }
}

?>
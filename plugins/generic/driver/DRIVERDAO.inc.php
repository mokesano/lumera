<?php

/**
 * @file plugins/generic/driver/DRIVERDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DRIVERDAO
 * @ingroup plugins_generic_driver
 *
 * @brief DAO operations for DRIVER.
 * * FIXED: Updated for PHP 7.4 Compatibility & Modernized OAI Core
 */

import('classes.oai.ojs.OAIDAO');

class DRIVERDAO extends OAIDAO {

    /** @var object Parent OAI object */
    public $oai;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DRIVERDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::DRIVERDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Set parent OAI object.
     * @param $oai JournalOAI
     */
    public function setOAI($oai) {
        $this->oai = $oai;
    }

    //
    // Records
    //

    /**
     * Return set of OAI records or identifiers matching specified parameters.
     * @param $setIds array Objects ids that specify an OAI set, in this case only journal ID.
     * @param $from int timestamp
     * @param $until int timestamp
     * @param $offset int
     * @param $limit int
     * @param $total int Output parameter for total count
     * @param $funcName string Function name to call for row processing (_returnRecordFromRow or _returnIdentifierFromRow)
     * @return array OAIRecord
     */
    public function getDRIVERRecordsOrIdentifiers($setIds, $from, $until, $offset, $limit, &$total, $funcName) {
        $records = array();

        // [FIXED] Manual SQL Construction because _getRecordsRecordSet was removed in Parent Refactor
        $params = $this->getOrderedRecordParams(null, $setIds, null);
        
        // Note: 'mutex' table usage here assumes specific OJS fork implementation as per original code.
        $sql = $this->getRecordSelectStatement() . ' FROM mutex m ' .
               $this->getRecordJoinClause(null, $setIds, null) . ' ' .
               $this->getAccessibleRecordWhereClause() . ' ' .
               $this->getDateRangeWhereClause($from, $until);

        $result = $this->retrieve($sql, $params);

        $total = $result->RecordCount();

        $result->Move($offset);
        for ($count = 0; $count < $limit && !$result->EOF; $count++) {
            $row = $result->GetRowAssoc(false);
            // Dynamic call to _returnRecordFromRow or _returnIdentifierFromRow
            $record = $this->$funcName($row);
            
            // Filter for DRIVER set
            if(in_array('driver', $record->sets)){
                $records[] = $record;
            }
            
            $result->moveNext();
            unset($record, $row);
        }

        $result->Close();
        unset($result);

        return $records;
    }

}
?>
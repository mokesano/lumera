<?php
declare(strict_types=1);

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
     * [SHIM] Backward Compatibility.
     */
    public function DRIVERDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
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
     * @param mixed $setIds array
     * @param int $from int timestamp
     * @param int $until int timestamp
     * @param int $offset int
     * @param int $limit int
     * @param int $total int Output parameter for total count
     * @param mixed $funcName string Function name to call for row processing (_returnRecordFromRow or _returnIdentifierFromRow)
     * @return array OAIRecord
     */
    public function getDRIVERRecordsOrIdentifiers($setIds, $from, $until, $offset, $limit, &$total, $funcName) {
        $records = [];

        $params = $this->getOrderedRecordParams(null, $setIds, null);
        $sql = $this->getRecordSelectStatement() . ' FROM mutex m ' .
               $this->getRecordJoinClause(null, $setIds, null) . ' ' .
               $this->getAccessibleRecordWhereClause() . ' ' .
               $this->getDateRangeWhereClause($from, $until);

        $result = $this->retrieve($sql, $params);

        $total = $result->RecordCount();

        $result->Move($offset);
        for ($count = 0; $count < $limit && !$result->EOF; $count++) {
            $row = $result->GetRowAssoc(false);
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
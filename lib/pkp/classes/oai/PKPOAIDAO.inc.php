<?php
declare(strict_types=1);

/**
 * @file classes/oai/PKPOAIDAO.inc.php
 * HIGH PERFORMANCE BASE: Enables Pre-loading for OAI
 *
 * Base DAO class for OAI operations in PKP/OJS.
 * This class provides high-performance bulk-loading hooks and a unified
 * structure for fetching OAI Records and Identifiers.
 * * REFACTORED: Wizdam Edition (PHP 7.4 - 8.x Modernization)
 */

import('lib.pkp.classes.oai.OAIStruct');
import('lib.pkp.classes.db.DBResultRange');

class PKPOAIDAO extends DAO {

    /** @var JournalOAI|PKPOAI Reference to the OAI handler */
    public $oai;

    /**
     * Constructor.
     *
     * Initializes the parent DAO, with no additional logic.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPOAIDAO() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPOAIDAO(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Assign the OAI handler object.
     *
     * @param mixed $oai OAI handler (JournalOAI or subclass)
     * @return void
     */
    public function setOAI($oai) {
        $this->oai = $oai;
    }

    // --- Records Fetching with TURBO HOOK ---

    /**
     * Fetch full OAI records (not just identifiers).
     *
     * Pipeline:
     * 1. Count total matching records.
     * 2. Retrieve raw rows using the configured SELECT and JOIN clauses.
     * 3. Buffer rows in an array.
     * 4. Trigger preloadData() so child classes can bulk-load dependencies
     * (authors, galleys, settings, etc.) for high performance.
     * 5. Convert rows into OAIRecord objects.
     *
     * @param array $setIds List of set IDs being filtered.
     * @param string|null $from ISO date lower bound.
     * @param string|null $until ISO date upper bound.
     * @param string|null $set Optional set spec.
     * @param int $offset Pagination offset.
     * @param int $limit Pagination limit.
     *
     * @return array {records: OAIRecord[], total: int}
     */
    public function getRecords($setIds, $from, $until, $set, $offset, $limit) {
        $records = array();
        $params = $this->getOrderedRecordParams(null, $setIds, $set);

        // (1) Count records
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM mutex m ' .
            $this->getRecordJoinClause(null, $setIds, $set) . ' ' .
            $this->getAccessibleRecordWhereClause() . ' ' .
            $this->getDateRangeWhereClause($from, $until),
            $params
        );
        $total = isset($result->fields[0]) ? (int)$result->fields[0] : 0;
        $result->Close();

        // (2) Fetch rows
        $result = $this->retrieveRange(
            $this->getRecordSelectStatement() . ' FROM mutex m ' .
            $this->getRecordJoinClause(null, $setIds, $set) . ' ' .
            $this->getAccessibleRecordWhereClause() . ' ' .
            $this->getDateRangeWhereClause($from, $until),
            $params,
            new DBResultRange($limit, null, $offset)
        );

        // (3) Buffer rows
        $rawRows = array();
        while (!$result->EOF) {
            $rawRows[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        $result->Close();

        // (4) Performance hook (child classes override)
        if (!empty($rawRows)) {
            $this->preloadData($rawRows);
        }

        // (5) Build objects
        foreach ($rawRows as $row) {
            $records[] = $this->_returnRecordFromRow($row);
        }

        return array('records' => $records, 'total' => $total);
    }

    /**
     * Fetch OAI identifiers (headers only).
     *
     * Uses the same pipeline as getRecords(), but produces OAIIdentifier objects.
     *
     * @param array $setIds
     * @param string|null $from
     * @param string|null $until
     * @param string|null $set
     * @param int $offset
     * @param int $limit
     *
     * @return array {records: OAIIdentifier[], total: int}
     */
    public function getIdentifiers($setIds, $from, $until, $set, $offset, $limit) {
        $records = array();
        $params = $this->getOrderedRecordParams(null, $setIds, $set);

        // Count
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM mutex m ' .
            $this->getRecordJoinClause(null, $setIds, $set) . ' ' .
            $this->getAccessibleRecordWhereClause() . ' ' .
            $this->getDateRangeWhereClause($from, $until),
            $params
        );
        $total = isset($result->fields[0]) ? (int)$result->fields[0] : 0;
        $result->Close();

        // Select
        $result = $this->retrieveRange(
            $this->getRecordSelectStatement() . ' FROM mutex m ' .
            $this->getRecordJoinClause(null, $setIds, $set) . ' ' .
            $this->getAccessibleRecordWhereClause() . ' ' .
            $this->getDateRangeWhereClause($from, $until),
            $params,
            new DBResultRange($limit, null, $offset)
        );

        // Buffer
        $rawRows = array();
        while (!$result->EOF) {
            $rawRows[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        $result->Close();

        // Preload
        if (!empty($rawRows)) {
            $this->preloadData($rawRows);
        }

        // Build identifiers
        foreach ($rawRows as $row) {
            $records[] = $this->_returnIdentifierFromRow($row);
        }

        return array('records' => $records, 'total' => $total);
    }

    /**
     * Preload related data for a set of rows.
     *
     * Child classes override this to load:
     * - submission settings
     * - authors
     * - galleys
     * - sections
     * - etc.
     *
     * Allows high-performance bulk querying.
     *
     * @param array $rows Raw database rows
     * @return void
     */
    public function preloadData($rows) {
        // Default: Do nothing
    }

    // --- Standard OAI Utility Methods ---

    /**
     * Return the earliest datestamp from a data set.
     *
     * @param string $selectStatement A SELECT field returning a datetime column
     * @param array $setIds Optional set filter
     *
     * @return int Unix timestamp
     */
    public function getEarliestDatestamp($selectStatement, $setIds = array()) {
        $params = $this->getOrderedRecordParams(null, $setIds);
        $result = $this->retrieve(
            $selectStatement . ' as the_date FROM mutex m ' .
            $this->getRecordJoinClause(null, $setIds) . ' ' .
            $this->getAccessibleRecordWhereClause() . ' ' .
            'ORDER BY the_date',
            $params
        );
        $timestamp = 0;
        if (isset($result->fields[0])) {
            $timestamp = strtotime($this->datetimeFromDB($result->fields[0]));
        }
        // MODERNIZATION: strtotime returns false, not -1 in PHP 7/8
        if ($timestamp === false || $timestamp === -1) $timestamp = 0;
        
        $result->Close();
        return $timestamp;
    }

    /**
     * Check if a record exists for given dataObjectId.
     *
     * @param int $dataObjectId
     * @param array $setIds
     *
     * @return bool
     */
    public function recordExists($dataObjectId, $setIds = array()) {
        $params = $this->getOrderedRecordParams($dataObjectId, $setIds);
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM mutex m ' .
            $this->getRecordJoinClause($dataObjectId, $setIds) . ' ' .
            $this->getAccessibleRecordWhereClause(),
            $params
        );
        $returner = $result->fields[0] == 1;
        $result->Close();
        return $returner;
    }

    /**
     * Fetch a single record by its object ID.
     *
     * @param int $dataObjectId
     * @param array $setIds
     *
     * @return OAIRecord|null
     */
    public function getRecord($dataObjectId, $setIds = array()) {
        $params = $this->getOrderedRecordParams($dataObjectId, $setIds);
        $result = $this->retrieve(
            $this->getRecordSelectStatement() . ' FROM mutex m ' .
            $this->getRecordJoinClause($dataObjectId, $setIds) . ' ' .
            $this->getAccessibleRecordWhereClause(),
            $params
        );
        $returner = null;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);
            $this->preloadData(array($row)); // Preload single
            $returner = $this->_returnRecordFromRow($row);
        }
        $result->Close();
        return $returner;
    }

    // --- Resumption Token Management ---

    /**
     * Remove expired resumption tokens.
     *
     * @return void
     */
    public function clearTokens() {
        $this->update('DELETE FROM oai_resumption_tokens WHERE expire < ?', array(time()));
    }

    /**
     * Retrieve a resumption token object by token string.
     *
     * @param string $tokenId
     * @return OAIResumptionToken|null
     */
    public function getToken($tokenId) {
        $result = $this->retrieve('SELECT * FROM oai_resumption_tokens WHERE token = ?', array($tokenId));
        $token = null;
        if ($result->RecordCount() != 0) {
            $row = $result->getRowAssoc(false);
            $token = new OAIResumptionToken($row['token'], $row['record_offset'], unserialize($row['params']), $row['expire']);
        }
        $result->Close();
        return $token;
    }

    /**
     * Insert a new resumption token with guaranteed uniqueness.
     *
     * @param OAIResumptionToken $token
     * @return OAIResumptionToken
     */
    public function insertToken($token) {
        do {
            $token->id = md5(uniqid((string) mt_rand(), true));
            $result = $this->retrieve('SELECT COUNT(*) FROM oai_resumption_tokens WHERE token = ?', array($token->id));
            $val = $result->fields[0];
            $result->Close();
        } while($val != 0);

        $this->update(
            'INSERT INTO oai_resumption_tokens (token, record_offset, params, expire) VALUES (?, ?, ?, ?)',
            array($token->id, (int)$token->offset, serialize($token->params), (int)$token->expire)
        );
        return $token;
    }

    // --- Parameter preparation and abstract interface definitions ---

    /**
     * Assemble ordered parameters for SQL queries depending on object ID,
     * set IDs, and optional set spec.
     *
     * @param int|null $dataObjectId
     * @param array $setIds
     * @param string|null $set
     *
     * @return array Parameter array for DB queries
     */
    public function getOrderedRecordParams($dataObjectId = null, $setIds = array(), $set = null) {
        $params = array();
        if (isset($dataObjectId)) $params[] = $dataObjectId;

        $notNullSetIds = array();
        if (is_array($setIds) && !empty($setIds)) {
            foreach($setIds as $id) {
                if (is_null($id)) continue;
                $notNullSetIds[] = (int) $id;
                $params[] = (int) $id;
            }
        }

        if (isset($dataObjectId)) $params[] = $dataObjectId;
        if (isset($set)) $params[] = $set;

        $params = array_merge($params, $notNullSetIds);
        return $params;
    }

    /**
     * Abstract SELECT statement provider.
     * Must be overridden by subclasses.
     *
     * @return string
     */
    public function getRecordSelectStatement() { assert(false); }

    /**
     * Abstract JOIN clause provider.
     * Must be implemented by subclasses.
     *
     * @return string
     */
    public function getRecordJoinClause($dataObjectId = null, $setIds = array(), $set = null) { assert(false); }

    /**
     * Get SQL WHERE clause for accessibility filtering.
     *
     * @return string
     */
    public function getAccessibleRecordWhereClause() { assert(false); }

    /**
     * Get date range filter clause for OAI "from" and "until".
     *
     * @param string|null $from
     * @param string|null $until
     * @return string
     */
    public function getDateRangeWhereClause($from, $until) { assert(false); }

    /**
     * Allows subclasses to set OAI-specific data on a record.
     *
     * @param OAIRecord|OAIIdentifier $record
     * @param array $row
     * @param bool $isRecord Whether this is a full record or identifier
     *
     * @return mixed
     */
    public function setOAIData($record, $row, $isRecord) { return $record; }

    // --- Row → Object conversion helpers ---

    /**
     * Convert row to OAIRecord.
     *
     * @param array $row
     * @return OAIRecord
     */
    public function _returnRecordFromRow($row) {
        $record = new OAIRecord();
        $record = $this->_doCommonOAIFromRowOperations($record, $row);
        // MODERNIZATION: HookRegistry::dispatch
        HookRegistry::dispatch('OAIDAO::_returnRecordFromRow', array($record, $row));
        return $record;
    }

    /**
     * Convert row to OAIIdentifier.
     *
     * @param array $row
     * @return OAIIdentifier
     */
    public function _returnIdentifierFromRow($row) {
        $record = new OAIIdentifier();
        $record = $this->_doCommonOAIFromRowOperations($record, $row);
        // MODERNIZATION: HookRegistry::dispatch
        HookRegistry::dispatch('OAIDAO::_returnIdentifierFromRow', array($record, $row));
        return $record;
    }

    /**
     * Shared logic for both OAIRecord and OAIIdentifier:
     * - datestamp
     * - deleted status
     * - set info
     * - row mapping for child data
     *
     * @param OAIRecord|OAIIdentifier $record
     * @param array $row
     *
     * @return mixed
     */
    public function _doCommonOAIFromRowOperations($record, $row) {
        $record->datestamp = OAIUtils::UTCDate(strtotime($this->datetimeFromDB($row['last_modified'])));

        // Deleted record?
        if (isset($row['tombstone_id'])) {
            $record->identifier = $row['oai_identifier'];
            $record->sets = array($row['set_spec']);
            $record->status = OAIRECORD_STATUS_DELETED;
        } else {
            $record->status = OAIRECORD_STATUS_ALIVE;
            // MODERNIZATION: Replaced is_a with instanceof
            $record = $this->setOAIData($record, $row, $record instanceof OAIRecord);
        }

        return $record;
    }
}
?>
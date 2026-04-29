<?php
declare(strict_types=1);

/**
 * @defgroup db
 */

/**
 * @file classes/db/DAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DAO
 * @ingroup db
 * @see DAORegistry
 *
 * @brief Operations for retrieving and modifying objects from a database.
 *
 * [MODERNISASI] Refactored for PHP 7.4+ Compatibility
 */

import('lib.pkp.classes.db.DBConnection');
import('lib.pkp.classes.db.DAOResultFactory');
import('lib.pkp.classes.core.DataObject');

define('SORT_DIRECTION_ASC', 0x00001);
define('SORT_DIRECTION_DESC', 0x00002);

class DAO {
    
    /**
     * @var object The database connection object 
     */
    protected $_dataSource;

    /**
     * Constructor
     */
    public function __construct($dataSource = null, $callHooks = true) {
        // [PENJELASAN] Hapus checkPhpVersion('4.3.0') karena redundant.
        if ($callHooks === true) {
            // Call hooks based on the object name.
            if (HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::_Constructor', array($this, $dataSource))) {
                return;
            }
        }

        if (!isset($dataSource)) {
            $this->setDataSource(DBConnection::getConn());
        } else {
            $this->setDataSource($dataSource);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DAO($dataSource = null, $callHooks = true) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::DAO(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct($dataSource, $callHooks);
    }

    /**
     * Get db conn.
     * @return ADONewConnection
     */
    public function getDataSource() {
        return $this->_dataSource;
    }

    /**
     * Set db conn.
     * @param $dataSource ADONewConnection
     */
    public function setDataSource($dataSource) {
        $this->_dataSource = $dataSource;
    }

    /**
     * Concatenation.
     */
    public function concat() {
        $args = func_get_args();
        return call_user_func_array(array($this->getDataSource(), 'Concat'), $args);
    }

    /**
     * Execute a SELECT SQL statement.
     * @param $sql string the SQL statement
     * @param $params array parameters for the SQL statement
     * @return ADORecordSet
     */
    public function retrieve($sql, $params = false, $callHooks = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            // [PENJELASAN] Menggunakan isset untuk mencegah warning jika stack trace tidak lengkap
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), array($sql, $params, $value))) {
                    return $value;
                }
            }
        }

        $start = Core::microtime();
        $dataSource = $this->getDataSource();
        
        // [MODERNISASI] Logic parameter diperbaiki agar lebih mudah dibaca
        $params = ($params !== false && !is_array($params)) ? array($params) : $params;
        
        $result = $dataSource->execute($sql, $params);
        
        DBConnection::logQuery($sql, $start, $params);
        
        if ($dataSource->errorNo()) {
            fatalError('DB Error: ' . $dataSource->errorMsg());
        }
        return $result;
    }

    /**
     * Execute a cached SELECT SQL statement.
     * @param $sql string the SQL statement
     * @param $params array parameters for the SQL statement
     * @param $secsToCache int number of seconds to cache the result
     * @return ADORecordSet
     */
    public function retrieveCached($sql, $params = false, $secsToCache = 3600, $callHooks = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), array($sql, $params, $secsToCache, $value))) {
                    return $value;
                }
            }
        }

        $this->setCacheDir();

        $start = Core::microtime();
        $dataSource = $this->getDataSource();
        
        $params = ($params !== false && !is_array($params)) ? array($params) : $params;
        
        $result = $dataSource->CacheExecute($secsToCache, $sql, $params);
        
        DBConnection::logQuery($sql, $start, $params);
        
        if ($dataSource->errorNo()) {
            fatalError('DB Error: ' . $dataSource->errorMsg());
        }
        
        // [WIZDAM FIX] CacheExecute melewati buffering normal mysqli.
        $mysqli = $dataSource->_connectionID ?? null;
        if ($mysqli instanceof mysqli && $mysqli->more_results()) {
            $mysqli->next_result();
        }

        return $result;
    }

    /**
     * Execute a SELECT SQL statement with LIMIT on the rows returned.
     * @param $sql string the SQL statement
     * @param $params array parameters for the SQL statement
     * @param $numRows int number of rows to return
     * @param $offset int the offset from which to return rows
     * @return ADORecordSet
     */
    public function retrieveLimit($sql, $params = false, $numRows = false, $offset = false, $callHooks = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), array($sql, $params, $numRows, $offset, $value))) {
                    return $value;
                }
            }
        }

        $start = Core::microtime();
        $dataSource = $this->getDataSource();
        
        $params = ($params !== false && !is_array($params)) ? array($params) : $params;
        
        // [PENJELASAN] Menggunakan ternary operator untuk nilai default -1 (semantik ADODB)
        $result = $dataSource->selectLimit($sql, $numRows === false ? -1 : $numRows, $offset === false ? -1 : $offset, $params);
        
        DBConnection::logQuery($sql, $start, $params);
        
        if ($dataSource->errorNo()) {
            fatalError('DB Error: ' . $dataSource->errorMsg());
        }
        return $result;
    }

    /**
     * Execute a SELECT SQL statment, returning rows in the range supplied.
     * @param $sql string the SQL statement
     * @param $params array parameters for the SQL statement
     * @param $dbResultRange DBResultRange the range of results to return
     * @return ADORecordSet
     */
    public function retrieveRange($sql, $params = false, $dbResultRange = null, $callHooks = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), array($sql, $params, $dbResultRange, $value))) {
                    return $value;
                }
            }
        }

        if (isset($dbResultRange) && $dbResultRange->isValid()) {
            $start = Core::microtime();
            $dataSource = $this->getDataSource();
            $result = $dataSource->PageExecute($sql, $dbResultRange->getCount(), $dbResultRange->getPage(), $params);
            DBConnection::logQuery($sql, $start, $params);
            if ($dataSource->errorNo()) {
                fatalError('DB Error: ' . $dataSource->errorMsg());
            }
        } else {
            $result = $this->retrieve($sql, $params, false);
        }
        return $result;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE SQL statement.
     * @param $sql string the SQL statement
     * @param $params array parameters for the SQL statement
     * @return boolean true 
     */
    public function update($sql, $params = false, $callHooks = true, $dieOnError = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), array($sql, $params, $value))) {
                    return $value;
                }
            }
        }

        $start = Core::microtime();
        $dataSource = $this->getDataSource();
        
        $params = ($params !== false && !is_array($params)) ? array($params) : $params;
        
        $dataSource->execute($sql, $params);
        
        DBConnection::logQuery($sql, $start, $params);
        
        if ($dieOnError && $dataSource->errorNo()) {
            fatalError('DB Error: ' . $dataSource->errorMsg());
        }
        return $dataSource->errorNo() == 0 ? true : false;
    }

    /**
     * Insert a row in a table, replacing an existing row if necessary.
     * @param $table string the table name
     * @param $arrFields array an associative array of field names and values
     * @param $keyCols array an array of field names that are the primary keys for the table
     */
    public function replace($table, $arrFields, $keyCols) {
        $dataSource = $this->getDataSource();
        $arrFields = array_map(array($dataSource, 'qstr'), $arrFields);
        $dataSource->Replace($table, $arrFields, $keyCols, false);
    }

    /**
     * Return the last ID inserted in an autonumbered field.
     * @param $table string the table name (optional, may be required by some DBMS)
     * @param $id string the name of the ID field (optional, may be required by some DBMS)
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        $dataSource = $this->getDataSource();
        return $dataSource->po_insert_id($table, $id);
    }

    /**
     * Return the number of affected rows by the last UPDATE or DELETE.
     */
    public function getAffectedRows() {
        $dataSource = $this->getDataSource();
        return $dataSource->Affected_Rows();
    }

    /**
     * Configure the caching directory for database results
     */
    public function setCacheDir() {
        static $cacheDir;
        if (!isset($cacheDir)) {
            global $ADODB_CACHE_DIR;
            $cacheDir = CacheManager::getFileCachePath() . '/_db';
            $ADODB_CACHE_DIR = $cacheDir;
        }
    }

    /**
     * Flush the system cache.
     */
    public function flushCache() {
        $this->setCacheDir();
        $dataSource = $this->getDataSource();
        $dataSource->CacheFlush();
    }

    /**
     * Return datetime formatted for DB insertion.
     * @param $dt string|int A datetime string or timestamp
     * @return string
     */
    public function datetimeToDB($dt) {
        $dataSource = $this->getDataSource();
        return $dataSource->DBTimeStamp($dt);
    }

    /**
     * Return date formatted for DB insertion.
     * @param $d string|int A date string or timestamp
     * @return string
     */
    public function dateToDB($d) {
        $dataSource = $this->getDataSource();
        return $dataSource->DBDate($d);
    }

    /**
     * Return datetime from DB as ISO datetime string.
     * @param $dt string A datetime string from the database
     * @return string|null An ISO datetime string, or null if the input was null
     */
    public function datetimeFromDB($dt) {
        if ($dt === null) return null;
        $dataSource = $this->getDataSource();
        return $dataSource->UserTimeStamp($dt, 'Y-m-d H:i:s');
    }
    
    /**
     * Return date from DB as ISO date string.
     * @param $d string A date string from the database
     * @return string|null An ISO date string, or null if the input was null
     */
    public function dateFromDB($d) {
        if ($d === null) return null;
        $dataSource = $this->getDataSource();
        return $dataSource->UserDate($d, 'Y-m-d');
    }

    /**
     * Convert a stored type from the database.
     * @param $value mixed The value to convert
     * @param $type string The type of the value
     * @return mixed The converted value
     */
    public function convertFromDB($value, $type) {
        switch ($type) {
            case 'bool':
                $value = (bool) $value;
                break;
            case 'int':
                $value = (int) $value;
                break;
            case 'float':
                $value = (float) $value;
                break;
            case 'object':
                $value = unserialize($value);
                break;
            case 'date':
                if ($value !== null) $value = strtotime($value);
                break;
            case 'string':
            default:
                // Nothing required.
                break;
        }
        return $value;
    }

    /**
     * Get the type of a value to be stored in the database.
     * @param $value mixed The value to check
     * @return string 
     */
    public function getType($value) {
        switch (gettype($value)) {
            case 'boolean':
            case 'bool':
                return 'bool';
            case 'integer':
            case 'int':
                return 'int';
            case 'double':
            case 'float':
                return 'float';
            case 'array':
            case 'object':
                return 'object';
            case 'string':
            default:
                return 'string';
        }
    }

    /**
     * Convert a PHP variable into a string to be stored in the DB.
     * @param $value mixed The value to convert
     * @param $type string 
     * @return string The converted value ready for database storage
     */
    public function convertToDB($value, &$type) {
        if ($type == null) {
            $type = $this->getType($value);
        }

        switch ($type) {
            case 'object':
                $value = serialize($value);
                break;
            case 'bool':
                // [PENJELASAN] Memastikan string 'false' dianggap bool false
                $value = ($value && $value !== 'false') ? 1 : 0;
                break;
            case 'int':
                $value = (int) $value;
                break;
            case 'float':
                $value = (float) $value;
                break;
            case 'date':
                if ($value !== null) {
                    if (!is_numeric($value)) $value = strtotime($value);
                    $value = strftime('%Y-%m-%d %H:%M:%S', $value);
                }
                break;
            case 'string':
            default:
                // do nothing.
        }

        return $value;
    }

    /**
     * Convert a value to an integer, or null if the value is empty.
     * @param $value mixed 
     * @return int|null 
     */
    public function nullOrInt($value) {
        return (empty($value) ? null : (int) $value);
    }

    /**
     * Get additional field names for a data object.
     */
    public function getAdditionalFieldNames() {
        $returner = array();
        HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::getAdditionalFieldNames', array($this, $returner));
        return $returner;
    }

    /**
     * Get locale field names for a data object.
     */
    public function getLocaleFieldNames() {
        $returner = array();
        HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::getLocaleFieldNames', array($this, $returner));
        return $returner;
    }

    /**
     * Update the settings table of a data object.
     * @param $tableName string The name of the settings table to update
     * @param $dataObject DataObject 
     * @param $idArray array
     */
    public function updateDataObjectSettings($tableName, $dataObject, $idArray) {
        // Initialize variables
        $idFields = array_keys($idArray);
        $idFields[] = 'locale';
        $idFields[] = 'setting_name';

        // Build a data structure that we can process efficiently.
        $translated = 1;
        $metadata = 1;
        $settings = 0; // Fixed from original logic: !$metadata
        
        $settingFields = array(
            // Translated data
            $translated => array(
                $settings => $this->getLocaleFieldNames(),
                $metadata => $dataObject->getLocaleMetadataFieldNames()
            ),
            // Shared data
            !$translated => array(
                $settings => $this->getAdditionalFieldNames(),
                $metadata => $dataObject->getAdditionalMetadataFieldNames()
            )
        );

        // Loop over all fields and update them in the settings table
        $updateArray = $idArray;
        $noLocale = 0;
        $staleMetadataSettings = array();
        
        foreach ($settingFields as $isTranslated => $fieldTypes) {
            foreach ($fieldTypes as $isMetadata => $fieldNames) {
                foreach ($fieldNames as $fieldName) {
                    if ($dataObject->hasData($fieldName)) {
                        if ($isTranslated) {
                            $values = $dataObject->getData($fieldName);
                            if (!is_array($values)) {
                                // Inconsistent data check
                                // assert(false); // [MODERNISASI] Removed assert to prevent fatal error in production
                                continue;
                            }
                        } else {
                            $values = array($noLocale => $dataObject->getData($fieldName));
                        }

                        foreach ($values as $locale => $value) {
                            $updateArray['locale'] = ($locale === $noLocale ? '' : $locale);
                            $updateArray['setting_name'] = $fieldName;
                            $updateArray['setting_type'] = null;
                            // Convert the data value and implicitly set the setting type.
                            $updateArray['setting_value'] = $this->convertToDB($value, $updateArray['setting_type']);
                            $this->replace($tableName, $updateArray, $idFields);
                        }
                    } else {
                        if ($isMetadata) $staleMetadataSettings[] = $fieldName;
                    }
                }
            }
        }

        // Remove stale meta-data
        if (count($staleMetadataSettings)) {
            $removeWhere = '';
            $removeParams = array();
            foreach ($idArray as $idField => $idValue) {
                if (!empty($removeWhere)) $removeWhere .= ' AND ';
                $removeWhere .= $idField.' = ?';
                $removeParams[] = $idValue;
            }
            $removeWhere .= rtrim(' AND setting_name IN ( '.str_repeat('? ,', count($staleMetadataSettings)), ',').')';
            $removeParams = array_merge($removeParams, $staleMetadataSettings);
            $removeSql = 'DELETE FROM '.$tableName.' WHERE '.$removeWhere;
            $this->update($removeSql, $removeParams);
        }
    }

    /**
     * Get the settings for a data object from the database and set them on the data object.
     * @param $tableName string The name of the settings table to query
     * @param $idFieldName string 
     * @param $idFieldValue mixed 
     * @param $dataObject DataObject 
     */
    public function getDataObjectSettings($tableName, $idFieldName, $idFieldValue, $dataObject) {
        if ($idFieldName !== null) {
            $sql = "SELECT * FROM $tableName WHERE $idFieldName = ?";
            $params = array($idFieldValue);
        } else {
            $sql = "SELECT * FROM $tableName";
            $params = false;
        }
        $result = $this->retrieve($sql, $params);

        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $dataObject->setData(
                $row['setting_name'],
                $this->convertFromDB(
                    $row['setting_value'],
                    $row['setting_type']
                ),
                empty($row['locale']) ? null : $row['locale']
            );
            $result->MoveNext();
        }
        $result->Close();
        // [PENJELASAN] Hapus unset($result) manual, biarkan Garbage Collector PHP yang bekerja.
    }

    /**
     * Get the driver for this connection.
     * @return string
     */
    public function getDriver() {
        $conn = DBConnection::getInstance();
        return $conn->getDriver();
    }

    /**
     * Get the driver for this connection.
     * @param $direction int 
     * @return string 
     */
    public function getDirectionMapping($direction) {
        switch ($direction) {
            case SORT_DIRECTION_ASC: return 'ASC';
            case SORT_DIRECTION_DESC: return 'DESC';
            default: return 'ASC';
        }
    }

    /**
     * Generate a JSON message with an event.
     * @param $elementId string|null 
     * @param $parentElementId string|null 
     * @return string A JSON message containing the event data
     */
    public function getDataChangedEvent($elementId = null, $parentElementId = null) {
        // Create the event data.
        $eventData = null;
        if ($elementId) {
            $eventData = array($elementId);
            if ($parentElementId) {
                $eventData['parentElementId'] = $parentElementId;
            }
        }

        // Create and render the JSON message
        import('lib.pkp.classes.core.JSONMessage');
        $json = new JSONMessage(true);
        $json->setEvent('dataChanged', $eventData);
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Format a passed date (in English textual datetime) to a format suitable for DB storage, with optional default number of weeks to add if no date is passed.
     * @param $date string|null The date to format (optional)
     * @param $defaultNumWeeks int|null 
     * @param $acceptPastDate bool
     * @return string A datetime string formatted for DB storage
     */
    public function formatDateToDB($date, $defaultNumWeeks = null, $acceptPastDate = true) {
        $today = getDate();
        $todayTimestamp = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
        
        if ($date != null) {
            $dueDateParts = explode('-', $date);

            // If we don't accept past dates...
            if (!$acceptPastDate && $todayTimestamp > strtotime($date)) {
                // ... return today.
                return date('Y-m-d H:i:s', $todayTimestamp);
            } else {
                // Return the passed date.
                return date('Y-m-d H:i:s', mktime(0, 0, 0, (int)$dueDateParts[1], (int)$dueDateParts[2], (int)$dueDateParts[0]));
            }
        } elseif (isset($defaultNumWeeks)) {
            // Add the equivalent of $numWeeks weeks.
            $numWeeks = max((int) $defaultNumWeeks, 2);
            $newDueDateTimestamp = $todayTimestamp + ($numWeeks * 7 * 24 * 60 * 60);
            return date('Y-m-d H:i:s', $newDueDateTimestamp);
        } else {
            // [MODERNISASI] Hapus assert(false) agar tidak fatal error. Return null saja.
            return null;
        }
    }
}

?>
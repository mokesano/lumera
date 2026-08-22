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
 */

import('lib.pkp.classes.db.DBConnection');
import('lib.pkp.classes.db.DAOResultFactory');
import('lib.pkp.classes.core.DataObject');

define('SORT_DIRECTION_ASC', 0x00001);
define('SORT_DIRECTION_DESC', 0x00002);

class DAO {
    
    /** @var \ADOConnection|null The database connection object */
    protected $_dataSource;

    /**
     * Constructor.
     * @param \ADOConnection|null $dataSource
     * @param bool $callHooks
     */
    public function __construct($dataSource = null, $callHooks = true) {
        if ($callHooks === true) {
            if (HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::_Constructor', [$this, $dataSource])) {
                return;
            }
        }

        if ($dataSource === null) {
            $this->setDataSource(DBConnection::getConn());
        } else {
            $this->setDataSource($dataSource);
        }
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function DAO($dataSource = null, $callHooks = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get db conn.
     * @return \ADOConnection|null
     */
    public function getDataSource() {
        return $this->_dataSource;
    }

    /**
     * Set db conn.
     * @param \ADOConnection|null $dataSource
     */
    public function setDataSource($dataSource) {
        $this->_dataSource = $dataSource;
    }

    /**
     * Concatenation.
     * @return string
     */
    public function concat() {
        $args = func_get_args();
        return call_user_func_array([$this->getDataSource(), 'Concat'], $args);
    }

    /**
     * Execute a SELECT SQL statement.
     * @param string $sql the SQL statement
     * @param array|mixed $params
     * @param bool $callHooks
     * @return \ADORecordSet|false
     */
    public function retrieve($sql, $params = [], $callHooks = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), [$sql, $params, &$value])) {
                    return $value;
                }
            }
        }

        $start = Core::microtime();
        $dataSource = $this->getDataSource();
        
        // Internal coercion: Pastikan $params selalu berupa array
        $params = is_array($params) ? $params : [$params];
        $result = $dataSource->execute($sql, $params);
        
        DBConnection::logQuery($sql, $start, $params);
        
        if ($dataSource->errorNo()) {
            fatalError('DB Error: ' . (string) $dataSource->errorMsg());
        }
        return $result;
    }

    /**
     * Execute a cached SELECT SQL statement.
     * @param string $sql the SQL statement
     * @param array|mixed $params parameters for the SQL statement
     * @param int $secsToCache number of seconds to cache the result
     * @param bool $callHooks
     * @return \ADORecordSet|false
     */
    public function retrieveCached($sql, $params = [], $secsToCache = 3600, $callHooks = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), [$sql, $params, $secsToCache, &$value])) {
                    return $value;
                }
            }
        }

        $this->setCacheDir();

        $start = Core::microtime();
        $dataSource = $this->getDataSource();
        
        $params = is_array($params) ? $params : [$params];
        $result = $dataSource->CacheExecute($secsToCache, $sql, $params);
        
        DBConnection::logQuery($sql, $start, $params);
        
        if ($dataSource->errorNo()) {
            fatalError('DB Error: ' . (string) $dataSource->errorMsg());
        }
        
        $mysqli = $dataSource->_connectionID ?? null;
        if ($mysqli instanceof mysqli && $mysqli->more_results()) {
            $mysqli->next_result();
        }

        return $result;
    }

    /**
     * Execute a SELECT SQL statement with LIMIT on the rows returned.
     * @param string $sql the SQL statement
     * @param array|mixed $params parameters for the SQL statement
     * @param int|false $numRows number of rows to return
     * @param int|false $offset the offset from which to return rows
     * @param bool $callHooks
     * @return \ADORecordSet|false
     */
    public function retrieveLimit($sql, $params = [], $numRows = false, $offset = false, $callHooks = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), [$sql, $params, $numRows, $offset, &$value])) {
                    return $value;
                }
            }
        }

        $start = Core::microtime();
        $dataSource = $this->getDataSource();
        
        $params = is_array($params) ? $params : [$params];
        
        $result = $dataSource->selectLimit($sql, $numRows === false ? -1 : $numRows, $offset === false ? -1 : $offset, $params);
        
        DBConnection::logQuery($sql, $start, $params);
        
        if ($dataSource->errorNo()) {
            fatalError('DB Error: ' . (string) $dataSource->errorMsg());
        }
        return $result;
    }

    /**
     * Execute a SELECT SQL statment, returning rows in the range supplied.
     * @param string $sql the SQL statement
     * @param array|mixed $params parameters for the SQL statement
     * @param DBResultRange|null $dbResultRange the range of results to return
     * @param bool $callHooks
     * @return \ADORecordSet|false
     */
    public function retrieveRange($sql, $params = [], $dbResultRange = null, $callHooks = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), [$sql, $params, $dbResultRange, &$value])) {
                    return $value;
                }
            }
        }

        if ($dbResultRange !== null && $dbResultRange->isValid()) {
            $start = Core::microtime();
            $dataSource = $this->getDataSource();
            $result = $dataSource->PageExecute($sql, $dbResultRange->getCount(), $dbResultRange->getPage(), $params);
            $logParams = is_array($params) ? $params : [];
            DBConnection::logQuery($sql, $start, $logParams);
            if ($dataSource->errorNo()) {
                fatalError('DB Error: ' . (string) $dataSource->errorMsg());
            }
        } else {
            $result = $this->retrieve($sql, $params, false);
        }
        return $result;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE SQL statement.
     * @param string $sql the SQL statement
     * @param array|mixed $params parameters for the SQL statement
     * @param bool $callHooks
     * @param bool $dieOnError
     * @return bool true on success
     */
    public function update($sql, $params = [], $callHooks = true, $dieOnError = true) {
        if ($callHooks === true) {
            $trace = debug_backtrace();
            $value = null;
            if (isset($trace[1]['class'])) {
                if (HookRegistry::dispatch(strtolower_codesafe($trace[1]['class'] . '::_' . $trace[1]['function']), [$sql, $params, &$value])) {
                    return $value;
                }
            }
        }

        $start = Core::microtime();
        $dataSource = $this->getDataSource();
        
        $params = is_array($params) ? $params : [$params];
        
        $dataSource->execute($sql, $params);
        
        DBConnection::logQuery($sql, $start, $params);
        
        if ($dieOnError && $dataSource->errorNo()) {
            fatalError('DB Error: ' . (string) $dataSource->errorMsg());
        }
        return $dataSource->errorNo() === 0;
    }

    /**
     * Insert a row in a table, replacing an existing row if necessary.
     * @param string $table the table name
     * @param array $arrFields an associative array of field names and values
     * @param array $keyCols an array of field names that are the primary keys for the table
     */
    public function replace($table, $arrFields, $keyCols) {
        $dataSource = $this->getDataSource();
        $arrFields = array_map([$dataSource, 'qstr'], $arrFields);
        $dataSource->Replace($table, $arrFields, $keyCols, false);
    }

    /**
     * Return the last ID inserted in an autonumbered field.
     * @param string $table the table name (optional)
     * @param string $id the name of the ID field (optional)
     * @param bool $callHooks
     * @return int
     */
    public function getInsertId($table = '', $id = '', $callHooks = true) {
        $dataSource = $this->getDataSource();
        return $dataSource->po_insert_id($table, $id);
    }

    /**
     * Return the number of affected rows by the last UPDATE or DELETE.
     * @return int
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
     * @param string|int $dt A datetime string or timestamp
     * @return string
     */
    public function datetimeToDB($dt) {
        $dataSource = $this->getDataSource();
        return $dataSource->DBTimeStamp($dt);
    }

    /**
     * Return date formatted for DB insertion.
     * @param string|int $d A date string or timestamp
     * @return string
     */
    public function dateToDB($d) {
        $dataSource = $this->getDataSource();
        return $dataSource->DBDate($d);
    }

    /**
     * Return datetime from DB as ISO datetime string.
     * @param string|null $dt A datetime string from the database
     * @return string|null An ISO datetime string, or null if the input was null
     */
    public function datetimeFromDB($dt) {
        if ($dt === null) return null;
        $dataSource = $this->getDataSource();
        return $dataSource->UserTimeStamp($dt, 'Y-m-d H:i:s');
    }
    
    /**
     * Return date from DB as ISO date string.
     * @param string|null $d A date string from the database
     * @return string|null An ISO date string, or null if the input was null
     */
    public function dateFromDB($d) {
        if ($d === null) return null;
        $dataSource = $this->getDataSource();
        return $dataSource->UserDate($d, 'Y-m-d');
    }

    /**
     * Convert a stored type from the database.
     * @param mixed $value The value to convert
     * @param string $type The type of the value
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
                break;
        }
        return $value;
    }

    /**
     * Get the type of a value to be stored in the database.
     * @param mixed $value The value to check
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
     * @param mixed $value The value to convert
     * @param string|null $type
     * @return string The converted value ready for database storage
     */
    public function convertToDB($value, &$type) {
        if ($type === null) {
            $type = $this->getType($value);
        }

        switch ($type) {
            case 'object':
                $value = serialize($value);
                break;
            case 'bool':
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
                break;
        }

        return $value;
    }

    /**
     * Convert a value to an integer, or null if the value is empty.
     * @param mixed $value
     * @return int|null 
     */
    public function nullOrInt($value) {
        return ($value === null || $value === '') ? null : (int) $value;
    }

    /**
     * Get additional field names for a data object.
     * @return array
     */
    public function getAdditionalFieldNames() {
        $returner = [];
        HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::getAdditionalFieldNames', [$this, &$returner]);
        return $returner;
    }

    /**
     * Get locale field names for a data object.
     * @return array
     */
    public function getLocaleFieldNames() {
        $returner = [];
        HookRegistry::dispatch(strtolower_codesafe(get_class($this)) . '::getLocaleFieldNames', [$this, &$returner]);
        return $returner;
    }

    /**
     * Update the settings table of a data object.
     * @param string $tableName The name of the settings table to update
     * @param DataObject $dataObject
     * @param array $idArray
     */
    public function updateDataObjectSettings($tableName, $dataObject, $idArray) {
        $idFields = array_keys($idArray);
        $idFields[] = 'locale';
        $idFields[] = 'setting_name';

        $translated = 1;
        $metadata = 1;
        $settings = 0;
        
        $settingFields = [
            $translated => [
                $settings => $this->getLocaleFieldNames(),
                $metadata => $dataObject->getLocaleMetadataFieldNames()
            ],
            !$translated => [
                $settings => $this->getAdditionalFieldNames(),
                $metadata => $dataObject->getAdditionalMetadataFieldNames()
            ]
        ];

        $updateArray = $idArray;
        $noLocale = 0;
        $staleMetadataSettings = [];
        
        foreach ($settingFields as $isTranslated => $fieldTypes) {
            foreach ($fieldTypes as $isMetadata => $fieldNames) {
                foreach ($fieldNames as $fieldName) {
                    if ($dataObject->hasData($fieldName)) {
                        if ($isTranslated) {
                            $values = $dataObject->getData($fieldName);
                            if (!is_array($values)) {
                                continue;
                            }
                        } else {
                            $values = [$noLocale => $dataObject->getData($fieldName)];
                        }

                        foreach ($values as $locale => $value) {
                            $updateArray['locale'] = ($locale === $noLocale ? '' : $locale);
                            $updateArray['setting_name'] = $fieldName;
                            $updateArray['setting_type'] = null;
                            $updateArray['setting_value'] = $this->convertToDB($value, $updateArray['setting_type']);
                            $this->replace($tableName, $updateArray, $idFields);
                        }
                    } else {
                        if ($isMetadata) $staleMetadataSettings[] = $fieldName;
                    }
                }
            }
        }

        if (count($staleMetadataSettings) > 0) {
            $removeWhere = '';
            $removeParams = [];
            foreach ($idArray as $idField => $idValue) {
                if ($removeWhere !== '') $removeWhere .= ' AND ';
                $removeWhere .= $idField . ' = ?';
                $removeParams[] = $idValue;
            }
            // Modernisasi: Menggunakan implode dan array_fill untuk placeholder yang lebih bersih
            $placeholders = implode(',', array_fill(0, count($staleMetadataSettings), '?'));
            $removeWhere .= ' AND setting_name IN (' . $placeholders . ')';
            $removeParams = array_merge($removeParams, $staleMetadataSettings);
            $removeSql = 'DELETE FROM ' . $tableName . ' WHERE ' . $removeWhere;
            $this->update($removeSql, $removeParams);
        }
    }

    /**
     * Get the settings for a data object from the database and set them on the data object.
     * @param string $tableName
     * @param string|null $idFieldName
     * @param mixed $idFieldValue
     * @param DataObject $dataObject
     */
    public function getDataObjectSettings($tableName, $idFieldName, $idFieldValue, $dataObject) {
        if ($idFieldName !== null) {
            $sql = "SELECT * FROM $tableName WHERE $idFieldName = ?";
            $params = [$idFieldValue];
        } else {
            $sql = "SELECT * FROM $tableName";
            $params = [];
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
     * Get the direction mapping for sorting.
     * @param int $direction
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
     * @param string|null $elementId 
     * @param string|null $parentElementId 
     * @return string A JSON message containing the event data
     */
    public function getDataChangedEvent($elementId = null, $parentElementId = null) {
        $eventData = null;
        if ($elementId) {
            $eventData = [$elementId];
            if ($parentElementId) {
                $eventData['parentElementId'] = $parentElementId;
            }
        }

        import('lib.pkp.classes.core.JSONMessage');
        $json = new JSONMessage(true);
        $json->setEvent('dataChanged', $eventData);
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Format a passed date to a format suitable for DB storage.
     * @param string|null $date The date to format (optional)
     * @param int|null $defaultNumWeeks 
     * @param bool $acceptPastDate
     * @return string|null A datetime string formatted for DB storage
     */
    public function formatDateToDB($date, $defaultNumWeeks = null, $acceptPastDate = true) {
        $today = getDate();
        $todayTimestamp = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
        
        if ($date !== null) {
            $dueDateParts = explode('-', $date);

            if (!$acceptPastDate && $todayTimestamp > strtotime($date)) {
                return date('Y-m-d H:i:s', $todayTimestamp);
            } else {
                return date('Y-m-d H:i:s', mktime(0, 0, 0, (int)$dueDateParts[1], (int)$dueDateParts[2], (int)$dueDateParts[0]));
            }
        } elseif ($defaultNumWeeks !== null) {
            $numWeeks = max((int) $defaultNumWeeks, 2);
            $newDueDateTimestamp = $todayTimestamp + ($numWeeks * 7 * 24 * 60 * 60);
            return date('Y-m-d H:i:s', $newDueDateTimestamp);
        } else {
            return null;
        }
    }
    
}
?>
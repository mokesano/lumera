<?php
declare(strict_types=1);

/**
 * @file classes/db/DBDataXMLParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DBDataXMLParser
 * @ingroup db
 *
 * @brief Class to import and export database data from an XML format.
 * See dbscripts/xml/dtd/xmldata.dtd for the XML schema used.
 */

import('lib.pkp.classes.xml.PKPXMLParser');

class DBDataXMLParser {

    /** @var PKPXMLParser the parser to use */
    public $parser;

    /** @var ADOConnection|null the underlying database connection */
    public $dbconn;

    /** @var array the array of parsed SQL statements */
    public $sql;

    /** @var string|null the error message from execution */
    public $errorMsg;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->parser = new PKPXMLParser();
        $this->sql = [];
        $this->errorMsg = null;
    }

    /**
     * [SHIM] Backward compatibility.
     */
    public function DBDataXMLParser() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . ". Please refactor to parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Set the database connection to use for executeData().
     * If the connection is not set, the default system database connection will be used.
     * 
     * @param ADOConnection $dbconn the database connection
     */
    public function setDBConn($dbconn) {
        $this->dbconn = $dbconn;
    }

    /**
     * Parse an XML data file into SQL statements.
     * 
     * @param string $file path to the XML file to parse
     * @return array the array of SQL statements parsed
     */
    public function parseData($file) {
        $this->sql = [];
        $tree = $this->parser->parse($file);
        
        if (!$this->dbconn) {
            $this->errorMsg = 'Database connection is not set.';
            return $this->sql;
        }

        $allTables = $this->dbconn->MetaTables() ?: [];
        $dbdict = null;

        if ($tree !== false) {
            foreach ($tree->getChildren() as $table) {
                if ($table->getName() === 'table') {
                    $fieldDefaultValues = [];

                    foreach ($table->getChildren() as $row) {
                        if ($row->getName() === 'field_default') {
                            [$fieldName, $value] = $this->_getFieldData($row);
                            $fieldDefaultValues[$fieldName] = $value;
                            
                        } elseif ($row->getName() === 'row') {
                            $fieldValues = [];

                            foreach ($row->getChildren() as $field) {
                                [$fieldName, $value] = $this->_getFieldData($field);
                                $fieldValues[$fieldName] = $value;
                            }

                            $fieldValues = array_merge($fieldDefaultValues, $fieldValues);

                            if (count($fieldValues) > 0) {
                                $this->sql[] = sprintf(
                                    'INSERT INTO %s (%s) VALUES (%s)',
                                    $table->getAttribute('name'),
                                    implode(', ', array_keys($fieldValues)),
                                    implode(', ', array_values($fieldValues))
                                );
                            }
                        } else {
                            throw new \UnexpectedValueException('Unexpected XML node in table: ' . $row->getName());
                        }
                    }

                } elseif ($table->getName() === 'sql') {
                    foreach ($table->getChildren() as $query) {
                        if ($query->getName() === 'drop') {
                            if ($dbdict === null) {
                                $dbdict = NewDataDictionary($this->dbconn);
                            }
                            $tableName = $query->getAttribute('table');
                            $column = $query->getAttribute('column');
                            
                            if ($column) {
                                $this->sql[] = $dbdict->DropColumnSql($tableName, $column);
                            } else {
                                $this->sql[] = $dbdict->DropTableSQL($tableName);
                            }

                        } elseif ($query->getName() === 'rename') {
                            if ($dbdict === null) {
                                $dbdict = NewDataDictionary($this->dbconn);
                            }
                            $tableName = $query->getAttribute('table');
                            $column = $query->getAttribute('column');
                            $to = $query->getAttribute('to');
                            
                            if ($column) {
                                $run = false;
                                if (in_array($tableName, $allTables, true)) {
                                    $columns = $this->dbconn->MetaColumns($tableName, true) ?: [];
                                    if (!isset($columns[strtoupper($to)])) {
                                        $run = true;
                                    }
                                } else {
                                    $run = true;
                                }

                                if ($run) {
                                    $colId = strtoupper($column);
                                    $flds = [];
                                    
                                    if (isset($columns[$colId])) {
                                        $col = $columns[$colId];
                                        $max_length = ((string) $col->max_length === '-1') ? '' : (string) $col->max_length;
                                        
                                        $fld = [
                                            'NAME' => $col->name,
                                            'TYPE' => $dbdict->MetaType($col),
                                            'SIZE' => $max_length
                                        ];
                                        
                                        if (!empty($col->primary_key)) $fld['KEY'] = 'KEY';
                                        if (!empty($col->auto_increment)) $fld['AUTOINCREMENT'] = 'AUTOINCREMENT';
                                        if (!empty($col->not_null)) $fld['NOTNULL'] = 'NOTNULL';
                                        if (!empty($col->has_default)) $fld['DEFAULT'] = $col->default_value;
                                        
                                        $flds = [$colId => $fld];
                                    } else {
                                        throw new \UnexpectedValueException("Column {$colId} not found in table {$tableName} during rename operation.");
                                    }
                                    
                                    $this->sql[] = $dbdict->RenameColumnSQL($tableName, $column, $to, $flds);
                                }
                            } else {
                                if (!in_array($to, $allTables, true)) {
                                    $this->sql[] = $dbdict->RenameTableSQL($tableName, $to);
                                }
                            }
                        } else {
                            $driver = $query->getAttribute('driver');
                            if (empty($driver) || $this->dbconn->databaseType === $driver) {
                                $this->sql[] = $query->getValue();
                            }
                        }
                    }
                }
            }
        }
        return $this->sql;
    }

    /**
     * Execute the parsed SQL statements.
     * 
     * @param bool $continueOnError continue to execute remaining statements if a failure occurs
     * @return bool success
     */
    public function executeData($continueOnError = false) {
        $this->errorMsg = null;
        $dbconn = $this->dbconn === null ? DBConnection::getConn() : $this->dbconn;
        
        if (!$dbconn) {
            $this->errorMsg = 'No database connection available for execution.';
            return false;
        }

        foreach ($this->sql as $stmt) {
            $dbconn->execute($stmt);
            if (!$continueOnError && $dbconn->errorNo() !== 0) {
                $this->errorMsg = $dbconn->errorMsg();
                return false;
            }
        }
        return true;
    }

    /**
     * Return the parsed SQL statements.
     * 
     * @return array
     */
    public function getSQL() {
        return $this->sql;
    }

    /**
     * Quote a string to appear as a value in an SQL INSERT statement.
     * 
     * @param string $str string to quote
     * @return string
     */
    public function quoteString($str) {
        return $this->dbconn->qstr($str);
    }

    /**
     * Perform required clean up for this object.
     */
    public function destroy() {
        $this->parser->destroy();
    }

    //
    // Private helper methods
    //
    
    /**
     * Retrieve a field name and value from a field node.
     * 
     * @param XMLNode $fieldNode
     * @return array an array with two entries: the field name and the field value
     */
    public function _getFieldData($fieldNode) {
        $fieldName = $fieldNode->getAttribute('name');
        $fieldValue = $fieldNode->getValue();

        $isEmpty = $fieldNode->getAttribute('null');
        if ($isEmpty !== null) {
            if ($isEmpty == 1) {
                $fieldValue = null;
            } elseif ($isEmpty == 0) {
                $fieldValue = '';
            }
        }

        if ($fieldValue === null) {
            $fieldValue = 'NULL';
        } elseif (!is_numeric($fieldValue)) {
            $fieldValue = $this->quoteString($fieldValue);
        }

        return [$fieldName, $fieldValue];
    }
    
}
?>
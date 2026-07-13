<?php
declare(strict_types=1);

/**
 * @file classes/db/SQLParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SQLParser
 * @ingroup db
 *
 * @brief Class for parsing and executing statements in SQL files.
 */

class SQLParser {

    /** @var string The database driver */
    public $driver;

    /** @var \ADOConnection The database connection object */
    public $dataSource;

    /** @var bool Enable debugging (print SQL statements as they are executed) */
    public $debug;

    /** @var array Error messages */
    public $errorMsg;

    /** @var string Delimiter for SQL comments used by the data source */
    public $commentDelim;

    /** @var string Delimiter for SQL statements used by the data source */
    public $statementDelim;

    /**
     * Constructor.
     * 
     * @param string $driver the database driver
     * @param \ADOConnection $dataSource the database connection object
     * @param bool $debug echo each statement as it's executed
     */
    public function __construct(string $driver, $dataSource, bool $debug = false) {
        $this->driver = $driver;
        $this->dataSource = $dataSource;
        $this->debug = $debug;
        $this->errorMsg = [];
        $this->commentDelim = '(\-\-|#)';
        $this->statementDelim = ';';
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $driver the database driver
     * @param \ADOConnection $dataSource the database connection object
     */
    public function SQLParser($driver, $dataSource, $debug = false) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct((string) $driver, $dataSource, (bool) $debug);
    }

    /**
     * Parse an SQL file and execute all SQL statements in it.
     * 
     * @param string $file full path to the file
     * @param bool $failOnError stop execution if an error is encountered
     * @return bool true if no errors occurred, false otherwise
     */
    public function executeFile(string $file, bool $failOnError = true): bool {
        if (!file_exists($file) || !is_readable($file)) {
            $this->errorMsg[] = "{$file} does not exist or is not readable!";
            return false;
        }

        // Read file and break up into SQL statements
        // Null safety: file() can return false on failure
        $fileContents = @file($file);
        if ($fileContents === false) {
            $this->errorMsg[] = "Failed to read file: {$file}";
            return false;
        }

        $sql = implode('', $fileContents);
        $sql = $this->stripComments($sql); 
        $statements = $this->parseStatements($sql);

        $hasError = false;

        // Execute each SQL statement
        foreach ($statements as $statement) {
            if ($this->debug) {
                echo 'Executing: ', $statement, "\n\n";
            }

            $this->dataSource->execute($statement);

            if ($this->dataSource->errorNo() !== 0) {
                // Internal coercion: Cast to string to prevent Intelephense P1006 from broken ADOdb PHPDoc
                $this->errorMsg[] = (string) $this->dataSource->errorMsg();

                if ($failOnError) {
                    return false;
                }
                $hasError = true;
            }
        }

        return !$hasError;
    }

    /**
     * Strip SQL comments from SQL string.
     * 
     * @param string $sql
     * @return string
     */
    public function stripComments($sql) {
        return trim(PKPString::regexp_replace(sprintf('/^\s*%s(.*)$/m', $this->commentDelim), '', $sql));
    }

    /**
     * Parse SQL content into individual SQL statements.
     * 
     * @param string $sql
     * @return array
     */
    public function parseStatements(string $sql): array {
        $statements = [];
        $statementsTmp = explode($this->statementDelim, $sql);

        $currentStatement = '';
        $numSingleQuotes = 0;
        $numEscapedSingleQuotes = 0;

        foreach ($statementsTmp as $part) {
            $numSingleQuotes += PKPString::substr_count($part, "'");
            $numEscapedSingleQuotes += PKPString::regexp_match_all("/(?<!\\\\)(\\\\\\\\)*\\\\'/", $part, $matches);

            $currentStatement .= $part;

            if (($numSingleQuotes - $numEscapedSingleQuotes) % 2 === 0) {
                $trimmedStatement = trim($currentStatement);
                if ($trimmedStatement !== '') {
                    $statements[] = $trimmedStatement;
                }
                $currentStatement = '';
                $numSingleQuotes = 0;
                $numEscapedSingleQuotes = 0;
            } else {
                $currentStatement .= $this->statementDelim;
            }
        }

        return $statements;
    }

    /**
     * Return the last error message that occurred in parsing.
     * 
     * @return string|null
     */
    public function getErrorMsg(): ?string {
        return empty($this->errorMsg) ? null : array_pop($this->errorMsg);
    }
    
}
?>
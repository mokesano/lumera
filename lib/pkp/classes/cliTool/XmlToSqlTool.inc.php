<?php
declare(strict_types=1);

/**
 * @file classes/cliTool/XmlToSqlTool.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class XmlToSqlTool
 * @ingroup tools
 *
 * @brief CLI tool to output the SQL statements corresponding to an XML database schema.
 * [WIZDAM EDITION] Modernized CLI Tool Base Class.
 */


import('lib.pkp.classes.db.DBDataXMLParser');
import('lib.pkp.classes.db.DBConnection'); // [WIZDAM] Explicitly import DBConnection

class XmlToSqlTool extends CommandLineTool {

    /** @var string type of file to parse (schema or data) */
    protected string $type = 'schema';

    /** @var string command to execute (print|execute|upgrade) */
    protected string $command = '';

    /** @var string XML file to parse */
    protected string $inputFile = '';

    /** @var string|null file to save SQL statements in */
    protected ?string $outputFile = null;

    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);
        
        $args = $this->argv;
        $argOffset = 0;

        // 1. Determine Type (-schema or -data)
        if (isset($args[0]) && in_array($args[0], ['-schema', '-data'])) {
            $this->type = substr($args[0], 1);
            $argOffset = 1;
        }

        // 2. Determine Command
        $supportedCommands = ['print', 'save', 'print_upgrade', 'save_upgrade', 'execute'];
        
        $command = $args[$argOffset] ?? null;

        if ($command === null || !in_array($command, $supportedCommands)) {
            $this->usage();
            exit(1);
        }
        $this->command = $command;

        // 3. Determine Input File
        $file = $args[$argOffset + 1] ?? DATABASE_XML_FILE;
        $file2 = PWD . '/' . $file;

        if (!file_exists($file) && !file_exists($file2)) {
            printf("Error: Input file \"%s\" does not exist!\n", $file);
            exit(1);
        }

        $this->inputFile = file_exists($file2) ? $file2 : $file;

        // 4. Determine Output File and Validate Write Permissions
        $this->outputFile = $args[$argOffset + 2] ?? null;
        $saveCommands = ['save', 'save_upgrade'];

        if (in_array($this->command, $saveCommands)) {
             if ($this->outputFile === null) {
                printf("Error: Output file is required for command \"%s\"!\n", $this->command);
                exit(1);
            }
            
            $outputFullPath = PWD . '/' . $this->outputFile;
            $this->outputFile = $outputFullPath;

            if (
                (file_exists($outputFullPath) && (is_dir($outputFullPath) || !is_writable($outputFullPath))) || 
                !is_writable(dirname($outputFullPath))
            ) {
                printf("Error: Invalid output file \"%s\"! Check permissions.\n", $outputFullPath);
                exit(1);
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function XmlToSqlTool($argv = []) {
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
     * Print command usage information.
     */
    public function usage(): void {
        echo "Script to convert and execute XML-formatted database schema and data files\n"
            . "Usage: {$this->scriptName} [-data|-schema] command [input_file] [output_file]\n"
            . "Supported commands:\n"
            . "    print - print SQL statements\n"
            . "    save - save SQL statements to output_file\n"
            . "    print_upgrade - print upgrade SQL statements for current database\n"
            . "    save_upgrade - save upgrade SQL statements to output_file\n"
            . "    execute - execute SQL statements on current database\n";
    }

    /**
     * Parse an XML database file and output the corresponding SQL statements.
     */
    public function execute(): void {
        require_once('./lib/pkp/lib/adodb/adodb-xmlschema.inc.php');
        
        $dbconn = null;
        if (in_array($this->command, ['print', 'save'])) {
            // [WIZDAM FIX] Don't connect to actual database for print/save mode.
            // Create a pseudo connection to initialize the DB driver context needed by adoSchema.
            $conn = new DBConnection(
                Config::getVar('database', 'driver'),
                '', // host
                '', // username
                '', // password
                '', // name
                true, // persistent
                Config::getVar('i18n', 'connection_charset')
            );
            $dbconn = $conn->getDBConn();

        } else {
            // [WIZDAM FIX] Connect to actual database for execute/upgrade commands
            $dbconn = DBConnection::getConn();
        }

        // Initialize schema object
        $schema = new adoSchema($dbconn);
        $dict = $schema->dict;
        $dict->SetCharSet(Config::getVar('i18n', 'database_charset'));

        if ($this->type == 'schema') {
            $schema->parseSchema($this->inputFile);

            switch ($this->command) {
                case 'execute':
                    $schema->ExecuteSchema();
                    break;
                case 'save':
                case 'save_upgrade':
                    // [WIZDAM] Check output file existence again before saving
                    if ($this->outputFile === null) break;
                    $schema->SaveSQL($this->outputFile);
                    break;
                case 'print':
                case 'print_upgrade':
                default:
                    // [WIZDAM] Remove @ error suppressor
                    $sqlOutput = $schema->PrintSQL('TEXT');
                    if ($sqlOutput) echo $sqlOutput . "\n";
                    break;
            }

        } else if ($this->type == 'data') {
            $dataXMLParser = new DBDataXMLParser();
            $dataXMLParser->setDBConn($dbconn);
            $sql = $dataXMLParser->parseData($this->inputFile);

            switch ($this->command) {
                case 'execute':
                    $schema->addSQL($sql);
                    $schema->ExecuteSchema();
                    break;
                case 'save':
                case 'save_upgrade':
                    // [WIZDAM] Check output file existence again before saving
                    if ($this->outputFile === null) break;
                    $schema->addSQL($sql);
                    $schema->SaveSQL($this->outputFile);
                    break;
                case 'print':
                case 'print_upgrade':
                default:
                    $schema->addSQL($sql);
                    // [WIZDAM] Remove @ error suppressor
                    $sqlOutput = $schema->PrintSQL('TEXT');
                    if ($sqlOutput) echo $sqlOutput . "\n";
                    break;
            }

            // Cleanup
            $schema->destroy();
            $dataXMLParser->destroy();
        }
    }
}
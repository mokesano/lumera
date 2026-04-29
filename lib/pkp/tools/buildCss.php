<?php
declare(strict_types=1);

/**
 * @file tools/buildCss.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class buildCss
 * @ingroup tools
 *
 * @brief CLI tool for processing CSS into a single compiled file using Less for PHP.
 */


require(dirname(dirname(dirname(dirname(__FILE__)))) . '/tools/bootstrap.inc.php');

define('APPLICATION_STYLES_DIR', 'styles');
define('APPLICATION_LESS_WRAPPER', 'index.less');
define('APPLICATION_CSS_WRAPPER', 'compiled.css');

class buildCss extends CommandLineTool {
    
    /** @var bool true to force recompilation */
    public bool $force;

    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct($argv = []) {
        parent::__construct($argv);

        array_shift($argv); // Flush the tool name from the argv list

        $this->force = false;

        while ($option = array_shift($argv)) {
            switch ($option) {
                case 'force':
                    $this->force = true;
                    break;
                default:
                    $this->usage();
                    exit(-1);
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function buildCss($argv = []) {
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
    public function usage() {
        echo "CSS Compilation tool\n"
            . "Use this tool to compile CSS into a single file.\n\n"
            . "Usage: {$this->scriptName}\n";
    }

    /**
     * Execute the build CSS command.
     */
    public function execute() {
        // Load the LESS compiler class.
        require_once('lib/pkp/lib/lessphp/lessc.inc.php');

        // Flush if necessary
        if ($this->force && file_exists(APPLICATION_STYLES_DIR . '/' . APPLICATION_CSS_WRAPPER)) {
            unlink(APPLICATION_STYLES_DIR . '/' . APPLICATION_CSS_WRAPPER);
        }

        // Perform the compile.
        try {
            // KLUDGE pending fix of https://github.com/leafo/lessphp/issues#issue/66
            // Once this issue is fixed, revisit paths and go back to using
            // lessc::ccompile to parse & compile.
            $less = new lessc(APPLICATION_STYLES_DIR . '/' . APPLICATION_LESS_WRAPPER);
            $less->importDir = './';
            file_put_contents(
                APPLICATION_STYLES_DIR . '/' . APPLICATION_CSS_WRAPPER,
                $less->parse()
            );
        } catch (Exception $ex) {
            echo "ERROR: " . $ex->getMessage() . "\n";
            exit(-1);
        }
        exit(0);
    }
}

$tool = new buildCss($argv ?? []);
$tool->execute();

?>
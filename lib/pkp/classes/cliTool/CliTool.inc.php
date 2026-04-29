<?php
declare(strict_types=1);

/**
 * @defgroup tools
 */

/**
 * @file classes/cliTool/CliTool.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CommandLineTool
 * @ingroup tools
 *
 * @brief Initialization code for command-line scripts.
 * [WIZDAM EDITION] CLI Base Class. Strict Types & SAPI Security.
 */

/** Initialization code */
// Set current working directory
define('PWD', getcwd());

// Ensure we are in the base directory defined by tools/bootstrap.inc.php
if (defined('INDEX_FILE_LOCATION')) {
    chdir(dirname(INDEX_FILE_LOCATION));
}

if (!defined('STDIN')) {
    define('STDIN', fopen('php://stdin','r'));
}

define('SESSION_DISABLE_INIT', 1);

// Load Core Bootstrap
// [WIZDAM] Standardized bootstrap loading
require('./lib/pkp/includes/bootstrap.inc.php');

class CommandLineTool {

    /** @var string|null the script being executed */
    protected ?string $scriptName = null;

    /** @var array Command-line arguments */
    protected array $argv = [];

    /**
     * Constructor.
     * @param array $argv
     */
    public function __construct(array $argv = []) {
        // [WIZDAM SECURITY] SAPI Check
        // Ensure this is truly running via CLI to prevent web-based invocation attacks.
        if (php_sapi_name() !== 'cli') {
            die('Access Denied: This script can only be executed from the command-line.');
        }

        // Initialize the request object
        // [WIZDAM] Modern Singleton Access
        $application = Application::get();
        $request = $application->getRequest();

        // [WIZDAM LEGACY SUPPORT]
        // Ideally we should use a CLIRouter, but legacy plugins expect a PageRouter context.
        // We maintain this for compatibility with the existing ecosystem.
        import('classes.core.PageRouter');
        $router = new PageRouter();
        $router->setApplication($application);
        $request->setRouter($router);

        // Initialize the locale and load generic plugins.
        AppLocale::initialize();
        PluginRegistry::loadCategory('generic');

        $this->argv = $argv;

        $this->scriptName = isset($this->argv[0]) ? array_shift($this->argv) : '';

        if (isset($this->argv[0]) && $this->argv[0] == '-h') {
            $this->usage();
            exit(0);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CommandLineTool($argv = []) {
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
        // To be overridden by subclasses
    }
}
<?php
declare(strict_types=1);

/**
 * @file tools/importExport.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class importExport
 * @ingroup tools
 *
 * @brief CLI tool to perform import/export tasks
 * [WIZDAM EDITION] Modernized CLI Dispatcher.
 */

require(__DIR__ . '/bootstrap.inc.php');

class importExport extends CommandLineTool {

    /** @var string|null */
    protected $command = '';

    /** @var array */
    protected $parameters = [];

    /**
     * Constructor.
     * @param array $argv command-line arguments (see usage)
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);
        // [WIZDAM] Safe array_shift with null coalescing
        $this->command = array_shift($this->argv) ?? '';
        $this->parameters = $this->argv;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function importExport() {
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
        echo "Command-line tool for import/export tasks\n"
            . "Usage:\n"
            . "\t{$this->scriptName} list: List available plugins\n"
            . "\t{$this->scriptName} [pluginName] usage: Display usage information for a plugin\n"
            . "\t{$this->scriptName} [pluginName] [params...]: Invoke a plugin\n";
    }

    /**
     * Parse and execute the import/export task.
     */
    public function execute(): void {
        $plugins = PluginRegistry::loadCategory('importexport');
        
        if ($this->command === 'list') {
            echo "Available plugins:\n";
            if (empty($plugins)) {
                echo "\t(None)\n";
            } else {
                foreach ($plugins as $plugin) {
                    echo "\t" . $plugin->getName() . "\n";
                }
            }
            return;
        }

        $plugin = null;
        if ($this->command !== '') {
            /** @var ImportExportPlugin $plugin */
            $plugin = PluginRegistry::getPlugin('importexport', $this->command);
        }

        if ($this->command == 'usage' || $this->command == 'help' || $this->command == '' || $plugin === null) {
            $this->usage();
            return;
        }

        $plugin->executeCLI($this->scriptName, $this->parameters);
    }

}

// [WIZDAM] Safe instantiation
$tool = new importExport($argv ?? []);
$tool->execute();
?>
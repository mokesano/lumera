<?php
declare(strict_types=1);

/**
 * @file classes/cliTool/ScheduledTaskTool.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ScheduledTaskTool
 * @ingroup tools
 *
 * @brief CLI tool to execute a set of scheduled tasks.
 * [WIZDAM EDITION] Modernized Task Runner.
 */

// Define registry file constant cautiously (Config might not be fully loaded yet in some envs)
if (!defined('TASKS_REGISTRY_FILE')) {
    define('TASKS_REGISTRY_FILE', Config::getVar('general', 'registry_dir') . '/scheduledTasks.xml');
}

import('lib.pkp.classes.scheduledTask.ScheduledTask');
import('lib.pkp.classes.scheduledTask.ScheduledTaskHelper');
import('lib.pkp.classes.scheduledTask.ScheduledTaskDAO');
import('lib.pkp.classes.xml.XMLParser'); // [WIZDAM] Explicit Import

class ScheduledTaskTool extends CommandLineTool {

    /** @var string the XML file listing the tasks to be executed */
    protected string $file = '';

    /** @var ScheduledTaskDAO|null the DAO object */
    protected ?ScheduledTaskDAO $taskDao = null;

    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);

        if (isset($this->argv[0])) {
            $this->file = $this->argv[0];
        } else {
            $this->file = TASKS_REGISTRY_FILE;
        }

        if (!file_exists($this->file) || !is_readable($this->file)) {
            printf("Error: Tasks file \"%s\" does not exist or is not readable!\n", $this->file);
            exit(1);
        }

        $this->taskDao = DAORegistry::getDAO('ScheduledTaskDAO');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ScheduledTaskTool($argv = []) {
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
        echo "Script to run a set of scheduled tasks\n"
            . "Usage: {$this->scriptName} [tasks_file]\n";
    }

    /**
     * Parse and execute the scheduled tasks.
     */
    public function execute(): void {
        $this->parseTasks($this->file);
    }

    /**
     * Parse and execute the scheduled tasks in the specified file.
     * @param string $file
     */
    public function parseTasks(string $file): void {
        $xmlParser = new XMLParser();
        $tree = $xmlParser->parse($file);

        if (!$tree) {
            $xmlParser->destroy();
            printf("Error: Unable to parse XML file \"%s\"!\n", $file);
            exit(1);
        }

        foreach ($tree->getChildren() as $task) {
            $className = $task->getAttribute('class');
            $frequency = $task->getChildByName('frequency');
            
            $canExecute = false;
            if (isset($frequency)) {
                $canExecute = ScheduledTaskHelper::checkFrequency($className, $frequency);
            } else {
                // Always execute if no frequency is specified
                $canExecute = true;
            }

            if ($canExecute) {
                $this->executeTask($className, ScheduledTaskHelper::getTaskArgs($task));
            }
        }

        $xmlParser->destroy();
    }

    /**
     * Execute the specified task.
     * @param string $className the class name to execute
     * @param array $args the array of arguments to pass to the class constructors
     */
    public function executeTask(string $className, array $args): void {
        // [WIZDAM] Safe Instantiation
        // We ensure the class is a ScheduledTask and has an execute method.
        // instantiate() function handles Namespaces if provided in XML.
        $task = instantiate($className, 'ScheduledTask', null, 'execute', $args);

        if (!is_object($task)) {
            fatalError("Cannot instantiate task class: $className. Ensure it exists and extends ScheduledTask.");
        }

        // [WIZDAM] Type Safety Check
        if (!($task instanceof ScheduledTask)) {
             fatalError("Class $className is not a valid ScheduledTask.");
        }

        if ($this->taskDao) {
            $this->taskDao->updateLastRunTime($className);
        }
        
        $task->execute();
    }
}
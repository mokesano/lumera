<?php
declare(strict_types=1);

/**
 * @file tools/runScheduledTasks.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class runScheduledTasks
 * @ingroup tools
 *
 * @brief CLI tool to execute a set of scheduled tasks.
 * [WIZDAM EDITION] Scheduled Task Runner Implementation.
 */

require(__DIR__ . '/bootstrap.inc.php');

import('lib.pkp.classes.cliTool.ScheduledTaskTool');

class runScheduledTasks extends ScheduledTaskTool {
    /**
     * Constructor.
     * @param array $argv command-line arguments
     * If specified, the first parameter should be the path to
     * a tasks XML descriptor file (other than the default)
     */
    public function __construct(array $argv = []) {
        // [WIZDAM FIX] Call parent::__construct which handles argument parsing and file validation.
        parent::__construct($argv);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function runScheduledTasks($argv = []) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }
}

// [WIZDAM] Safe instantiation
$tool = new runScheduledTasks($argv ?? []);
$tool->execute();
?>
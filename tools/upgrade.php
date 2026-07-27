<?php
declare(strict_types=1);

/**
 * @file tools/upgrade.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UpgradeTool
 * @ingroup tools
 *
 * @brief CLI tool for upgrading Sangia Lumera Apps.
 * [LUMERA] Upgrade Tool Implementation.
 */

require(__DIR__ . '/bootstrap.inc.php');

import('lib.pkp.classes.cliTool.UpgradeTool');

class AppUpgradeTool extends UpgradeTool {

    /**
     * Constructor
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AppUpgradeTool($argv = []) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

}

// [LUMERA] Safe instantiation
$tool = new AppUpgradeTool($argv ?? []);
$tool->execute();
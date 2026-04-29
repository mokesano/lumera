<?php
declare(strict_types=1);

/**
 * @file tools/install.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class installTool
 * @ingroup tools
 *
 * @brief CLI tool for installing OJS.
 * [WIZDAM EDITION] Modernized CLI Installer Child.
 */

require(__DIR__ . '/bootstrap.inc.php');

import('lib.pkp.classes.cliTool.InstallTool');

class OJSInstallTool extends InstallTool {
    /**
     * Constructor.
     * @param array $argv command-line arguments
     */
    public function __construct(array $argv = []) {
        parent::__construct($argv);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OJSInstallTool($argv = []) {
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
     * Read installation parameters from stdin.
     * [WIZDAM] Signature MUST match parent::readParams(): bool
     * @return bool
     */
    protected function readParams(): bool {
        // [WIZDAM] Application Component
        AppLocale::requireComponents(LOCALE_COMPONENT_PKP_INSTALLER, LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_USER);
        printf("%s\n", __('installer.ojsInstallation'));

        // Call Parent implementation to read all common parameters
        parent::readParams();

        // Read OJS-specific final parameter
        $this->readParamBoolean('install', 'installer.installApplication');

        // Parent expects a boolean return value to determine execution flow.
        return (bool) $this->params['install'];
    }
}

// [WIZDAM] Safe instantiation
$tool = new OJSInstallTool($argv ?? []);
$tool->execute();

?>
<?php
declare(strict_types=1);

/**
 * @defgroup index
 */

/**
 * @file includes/bootstrap.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup index
 *
 * @brief Core system initialization code, Modernized Kernel Bootstrap.
 */

/**
 * Basic initialization (pre-classloading).
 */

// [LUMERA] Use Native PHP Constants
// We map ENV_SEPARATOR to PATH_SEPARATOR for legacy compatibility throughout the codebase.
define('ENV_SEPARATOR', PATH_SEPARATOR);

// Define System Root
define('BASE_SYS_DIR', dirname(INDEX_FILE_LOCATION));
chdir(BASE_SYS_DIR);

// [LUMERA] Optimized Path Configuration
$includePaths = [
    '.',
    BASE_SYS_DIR . '/classes',
    BASE_SYS_DIR . '/pages',
    BASE_SYS_DIR . '/lib/pkp',
    BASE_SYS_DIR . '/lib/pkp/classes',
    BASE_SYS_DIR . '/lib/pkp/pages',
    BASE_SYS_DIR . '/lib/pkp/lib/adodb',
    BASE_SYS_DIR . '/lib/pkp/lib/phputf8',
    BASE_SYS_DIR . '/lib/pkp/lib/pqp/classes',
    BASE_SYS_DIR . '/lib/pkp/lib/smarty',
    BASE_SYS_DIR . '/lib/wizdam',
    ini_get('include_path') // Append existing system paths
];

ini_set('include_path', implode(ENV_SEPARATOR, $includePaths));

// System-wide functions (Global Helper Functions) Loads import(), String wrapper, etc.
require('./lib/pkp/includes/functions.inc.php');

// [LUMERA] Initialize the application environment.
import('classes.core.Application');

// [LUMERA] Instantiate the Application Singleton.
// This prepares system for the Application::get()->execute() call in index.php.
new Application();

?>
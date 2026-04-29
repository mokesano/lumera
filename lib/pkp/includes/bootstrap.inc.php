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
 * @brief Core system initialization code.
 * [WIZDAM EDITION] Modernized Kernel Bootstrap.
 */

/**
 * Basic initialization (pre-classloading).
 */

// [WIZDAM] Use Native PHP Constants
// PHP 7.4+ guarantees DIRECTORY_SEPARATOR and PATH_SEPARATOR exist.
// We map ENV_SEPARATOR to PATH_SEPARATOR for legacy compatibility throughout the codebase.
define('ENV_SEPARATOR', PATH_SEPARATOR);

// Define System Root
define('BASE_SYS_DIR', dirname(INDEX_FILE_LOCATION));
chdir(BASE_SYS_DIR);

// [WIZDAM] Optimized Path Configuration
// We define paths in an array for readability, then implode them.
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

// System-wide functions (Global Helper Functions)
// Loads import(), String wrapper, etc.
require('./lib/pkp/includes/functions.inc.php');

// Initialize the application environment
// [WIZDAM] We use the import function to load the core Application class.
import('classes.core.Application');

// [WIZDAM] Instantiate the Application Singleton.
// The constructor of Application registers itself to the Registry.
// This prepares system for the Application::get()->execute() call in index.php.
new Application();

?>
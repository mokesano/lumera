<?php
declare(strict_types=1);

/**
 * @file index.php
 *
 * Copyright (c) 2017-2026 Code Lumera Project / Rochmady
 * Based on legacy open source components (c) Simon Fraser University
 * Distributed under the GNU GPL v3.
 *
 * @ingroup index
 * @brief System Entry Point.
 *
 * This is the bootstrap loader for the Sangia Publisher Platform.
 * It initializes the core engine and dispatches the request to the
 * appropriate Service Handler based on Publisher Centric Routing.
 */

// Initialize global environment
define('INDEX_FILE_LOCATION', __FILE__);

// [LUMERA SAFETY] Critical Bootstrap Check
$bootstrap = './lib/pkp/includes/bootstrap.inc.php';
if (!file_exists($bootstrap)) {
    // Respon standar Enterprise untuk kegagalan sistem kritikal
    header('HTTP/1.1 503 Service Unavailable');
    die('Sangia Lumera Frontedge Engine Error: Core components are missing or corrupt. System halted.');
}
require($bootstrap);

// [LUMERA ARCHITECTURE] Fluent Execution
Application::get()->execute();
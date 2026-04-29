<?php

/**
 * @file index.php
 *
 * Copyright (c) Wizdam Project / Rochmady
 * Based on legacy open source components (c) Simon Fraser University
 * Distributed under the GNU GPL v2.
 *
 * @ingroup index
 * @brief System Entry Point.
 *
 * This is the bootstrap loader for the Wizdam Publisher Platform.
 * It initializes the core engine and dispatches the request to the
 * appropriate Service Handler based on Publisher Centric Routing.
 */

// Initialize global environment
define('INDEX_FILE_LOCATION', __FILE__);

// [WIZDAM SAFETY] Critical Bootstrap Check
$bootstrap = './lib/pkp/includes/bootstrap.inc.php';
if (!file_exists($bootstrap)) {
    // Respon standar Enterprise untuk kegagalan sistem kritikal
    header('HTTP/1.1 503 Service Unavailable');
    die('Wizdam Frontedge Engine Error: Core components are missing or corrupt. System halted.');
}
require($bootstrap);

// [WIZDAM ARCHITECTURE] Fluent Execution
Application::get()->execute();
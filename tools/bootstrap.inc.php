<?php
declare(strict_types=1);

/**
 * @file tools/bootstrap.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup tools
 *
 * @brief Application-specific configuration common to all tools.
 * [WIZDAM EDITION] CLI Bootstrap: Strict Types, Safety Checks & Modern Paths.
 */

// Define the index file location relative to this file
// Using __DIR__ is cleaner and faster than dirname(__FILE__) in PHP 7.4+
define('INDEX_FILE_LOCATION', dirname(__DIR__) . '/index.php');

// [WIZDAM SAFETY] Critical CLI Component Check
// Ensure the CLI Tool base class exists before loading.
// Unlike web requests, CLI tools write to STDERR on failure.
$cliToolPath = dirname(__DIR__) . '/lib/pkp/classes/cliTool/CliTool.inc.php';

if (!file_exists($cliToolPath)) {
    fwrite(STDERR, "Wizdam CLI Error: Core Tool Definitions are missing or corrupt.\nPath: $cliToolPath\n");
    exit(1);
}

require($cliToolPath);
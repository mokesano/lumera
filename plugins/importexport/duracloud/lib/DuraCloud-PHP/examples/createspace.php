<?php
declare(strict_types=1);

/**
 * @file createspace.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Connect to a DuraCloud server and create a new space.
 * [WIZDAM EDITION] Refactored for PHP 8.0+
 */

// Configure error display to maximum level
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Import DuraCloud PHP library
require_once('../DuraCloudPHP.inc.php');

// Check usage.
if (!isset($argv)) {
    echo "This is a command line example. You must use the PHP CLI tool to execute.\n";
    exit(-1);
}

if (count($argv) < 5 || count($argv) > 6) {
    echo "Usage:\n";
    echo array_shift($argv) . " [DuraCloud base URL] [username] [password] [spaceID] [(storeID)]\n";
    exit(-2);
}

// Get arguments.
$exampleName = array_shift($argv);
$baseUrl = array_shift($argv);
$username = array_shift($argv);
$password = array_shift($argv);
$spaceId = array_shift($argv);
$storeId = array_shift($argv); // Optional, returns null if not provided

// Try a connection.
$dcc = new DuraCloudConnection($baseUrl, $username, $password);
$ds = new DuraStore($dcc);

// Create space
// Note: $storeId is passed directly. If it is null (from array_shift), 
// it matches the default value in DuraStore::createSpace signature.
$location = $ds->createSpace(
    $spaceId,
    [DURACLOUD_SPACE_ACCESS => DURACLOUD_SPACE_ACCESS_OPEN],
    $storeId
);

if ($location !== false) {
    echo "\nThe new space was created as \"$location\".\n";
} else {
    echo "The new space could not be created. Check your credentials and space ID.\n";
    exit(-3);
}

?>
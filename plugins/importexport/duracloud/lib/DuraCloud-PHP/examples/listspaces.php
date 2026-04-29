<?php
declare(strict_types=1);

/**
 * @file listspaces.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @brief Connect to a DuraCloud server and get a list of spaces.
 */

// Configure error display to maximum level
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Import DuraCloud PHP library
require_once('../DuraCloudPHP.inc.php');

// Check usage.
if (!isset($argv)) {
    echo "This is a command line example. You must use the PHP CLI tool to execute.\n";
    exit(-1);
}

if (count($argv) < 4 || count($argv) > 5) {
    echo "Usage:\n";
    echo $argv[0] . " [DuraCloud base URL] [username] [password] [(storeID)]\n";
    exit(-2);
}

// Get arguments.
$exampleName = array_shift($argv);
$baseUrl = array_shift($argv);
$username = array_shift($argv);
$password = array_shift($argv);
$storeId = array_shift($argv); // Optional

// Try a connection.
$dcc = new DuraCloudConnection($baseUrl, $username, $password);
$ds = new DuraStore($dcc);
$spaces = $ds->getSpaces($storeId);

if ($spaces !== false) {
    echo "The list of spaces is:\n";
    if (is_array($spaces)) {
        foreach ($spaces as $space) {
            echo " - $space\n";
        }
    }
    echo "\n";
} else {
    echo "The list of spaces could not be fetched. Check your credentials.\n";
    exit(-3);
}

?>
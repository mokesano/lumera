<?php
declare(strict_types=1);

/**
 * @file public/health.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Codecanau Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 * 
 * @brief LUMERA System Status Endpoint
 * This script provides a simple JSON response indicating the system status,
 * the application name, the PHP version, and the current server time.
 *
 * Usage: Access this script via a web browser or HTTP client to get the status.
 */
http_response_code(200);
header('Content-Type: application/json');

$response = [
    'status' => 'up',
    'time' => date('Y-m-d H:i:s'),
    'message' => 'Endpoint is operational',
];
echo json_encode($response, JSON_PRETTY_PRINT);
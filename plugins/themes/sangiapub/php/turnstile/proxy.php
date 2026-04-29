<?php
/**
 * File: /plugins/themes/[nama_tema]/php/turnstile/proxy.php
 * 
 * AJAX endpoint untuk verifikasi Turnstile dari JavaScript
 */

session_start();

// Include dependencies
require_once('config.php');
require_once('TurnstileValidator.php');
require_once('helper.php');

// Set response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// CORS headers untuk development (opsional)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Hanya terima POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
    exit;
}

// Ambil input data (support JSON dan form-data)
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

$action = isset($input['action']) ? $input['action'] : '';

try {
    switch ($action) {
        case 'verify':
            // Verifikasi token Turnstile
            $token = isset($input['token']) ? $input['token'] : '';
            $remoteIp = TurnstileHelper::getUserIP();
            
            if (TURNSTILE_DEBUG) {
                error_log("Turnstile verification attempt from IP: " . $remoteIp);
            }
            
            $validator = new TurnstileValidator(TURNSTILE_SECRET_KEY, TURNSTILE_SITE_KEY);
            $result = $validator->verify($token, $remoteIp);
            
            if ($result['success']) {
                TurnstileHelper::setVerified();
                if (TURNSTILE_DEBUG) {
                    error_log("Turnstile verification successful");
                }
            } else {
                TurnstileHelper::resetVerification();
                if (TURNSTILE_DEBUG) {
                    error_log("Turnstile verification failed: " . $result['error']);
                }
            }
            
            echo json_encode($result);
            break;
            
        case 'check_status':
            // Cek status verifikasi saat ini
            $isVerified = TurnstileHelper::isVerified();
            echo json_encode(array(
                'success' => true,
                'verified' => $isVerified,
                'timestamp' => isset($_SESSION['turnstile_timestamp']) ? $_SESSION['turnstile_timestamp'] : null
            ));
            break;
            
        case 'get_site_key':
            // Ambil site key untuk JavaScript
            echo json_encode(array(
                'success' => true,
                'site_key' => TURNSTILE_SITE_KEY
            ));
            break;
            
        case 'reset':
            // Reset status verifikasi
            TurnstileHelper::resetVerification();
            echo json_encode(array('success' => true, 'message' => 'Verification reset'));
            break;
            
        default:
            http_response_code(400);
            echo json_encode(array('success' => false, 'error' => 'Invalid action'));
            break;
    }
} catch (Exception $e) {
    if (TURNSTILE_DEBUG) {
        error_log("Turnstile proxy error: " . $e->getMessage());
    }
    
    http_response_code(500);
    echo json_encode(array(
        'success' => false, 
        'error' => 'Internal server error'
    ));
}
?>
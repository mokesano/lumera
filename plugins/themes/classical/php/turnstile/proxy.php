<?php
// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Method not allowed'));
    exit;
}

// Get token
$input = json_decode(file_get_contents('php://input'), true);
$token = '';
if (isset($input['token'])) {
    $token = $input['token'];
} elseif (isset($_POST['cf-turnstile-response'])) {
    $token = $_POST['cf-turnstile-response'];
} elseif (isset($_POST['token'])) {
    $token = $_POST['token'];
}

if (empty($token)) {
    echo json_encode(array('success' => false, 'error' => 'Token required'));
    exit;
}

// Validate with Cloudflare
$secretKey = '0x4AAAAAAA7b4JHByoY2iX27'; // ⚠️ GANTI dengan secret key Anda
$postData = array(
    'secret' => $secretKey,
    'response' => $token,
    'remoteip' => $_SERVER['REMOTE_ADDR']
);

$ch = curl_init();
curl_setopt_array($ch, array(
    CURLOPT_URL => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($postData),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30
));

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 200) {
    echo $response;
} else {
    echo json_encode(array('success' => false, 'error' => 'Verification failed'));
}
?>
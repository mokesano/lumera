<?php
// Tentukan path absolut ke direktori root OJS
define('OJS_ROOT_PATH', '/home/sangiaor/public_html/journals/');

// Inisialisasi OJS
require_once(OJS_ROOT_PATH . 'index.php');

import('lib.pkp.classes.user.UserDAO');

// Fungsi untuk mendapatkan detail pengguna berdasarkan ORCID
function getUserByOrcid($orcid) {
    $userDao = DAORegistry::getDAO('UserDAO');
    if (!$userDao) {
        error_log('Error: UserDAO tidak berhasil diinisialisasi');
        return null;
    }

    $users = $userDao->getAll();
    foreach ($users->toArray() as $user) {
        $userOrcid = $user->getData('orcid');
        if (!empty($userOrcid) && strtolower($userOrcid) == strtolower($orcid)) {
            return $user;
        }
    }
    return null;
}

// Fungsi untuk mendapatkan URL dasar secara dinamis
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $basePath = dirname($_SERVER['SCRIPT_NAME']);
    return $protocol . $host . $basePath;
}

// Dapatkan parameter ORCID dari query parameter
$orcid = isset($_GET['orcid']) ? $_GET['orcid'] : '';

if ($orcid) {
    $user = getUserByOrcid($orcid);
    if ($user) {
        $profileImage = $user->getSetting('profileImage');
        $profilePictureUrl = $profileImage ? getBaseUrl() . '/public/site/' . $profileImage['uploadName'] : null;

        echo json_encode([
            'profilePicture' => $profilePictureUrl
        ]);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} else {
    echo json_encode(['error' => 'ORCID parameter is required']);
}
?>

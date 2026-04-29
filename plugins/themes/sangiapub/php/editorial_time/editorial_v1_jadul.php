<?php
/**
 * Editorial Timeline API - Simplified
 * File: plugins/themes/[theme-name]/php/editorial/editorial.php
 * Sistem untuk mengambil revisionDate dan acceptedDate saja
 * @author Rochmady and Wizdam Team approach - Simplified Editorial Data
 * @version 1.0 - Menggunakan akses database terpisah dengan sistem OJS
 * Last Update: 2025-05-26
 */
header('Content-Type: application/json');

// Debugging - Menampilkan kesalahan PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sertakan file konfigurasi basis data
include '/home/sangiaor/php_sangia/db_config.php';

// Ambil ID artikel dari URL
$articleId = isset($_GET['articleId']) ? (int) $_GET['articleId'] : null;

if (!$articleId) {
    echo json_encode(["error" => "No article ID provided."]);
    exit;
}

// Koneksi ke basis data
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_DATABASE);

// Periksa koneksi
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Fungsi untuk mengambil afiliasi dan foto profil dari tabel user_settings
function getUserSettings($conn, $userId) {
    $settings = [];
    $sql = "SELECT setting_name, setting_value, locale FROM user_settings WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (!isset($settings[$row['setting_name']])) {
                $settings[$row['setting_name']] = [];
            }
            $settings[$row['setting_name']][] = [
                'value' => $row['setting_value'],
                'locale' => $row['locale']
            ];
        }
        $stmt->close();
    }
    return $settings;
}

// Fungsi untuk mengambil data yang di-localized berdasarkan locale
function getLocalizedSetting($settings, $settingName, $locale) {
    if (isset($settings[$settingName])) {
        foreach ($settings[$settingName] as $setting) {
            if ($setting['locale'] == $locale) {
                return $setting['value'];
            }
        }
        // Jika tidak ada localized value, kembalikan value default
        return $settings[$settingName][0]['value'];
    }
    return '';
}

// Fungsi untuk menguraikan data serialized
function unserializeSetting($data) {
    $unserializedData = @unserialize($data);
    return $unserializedData !== false ? $unserializedData : null;
}

// Fungsi untuk mengambil semua minat dari database
function getAllInterests($conn) {
    $interests = [];
    $sql = "SELECT controlled_vocab_entry_id, setting_value AS interest 
            FROM controlled_vocab_entry_settings 
            WHERE setting_name = 'interest'";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $interests[$row['controlled_vocab_entry_id']] = $row['interest'];
        }
    }
    return $interests;
}

// Mengambil data minat pengguna
function getUserInterests($conn, $userId, $allInterests) {
    $userInterests = [];
    $sql = "SELECT controlled_vocab_entry_id FROM user_interests WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            if (isset($allInterests[$row['controlled_vocab_entry_id']])) {
                $userInterests[] = $allInterests[$row['controlled_vocab_entry_id']];
            }
        }
        $stmt->close();
    }
    return $userInterests;
}

// Mengambil data editor dari database
$sql = "SELECT editor_id FROM edit_assignments WHERE article_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die(json_encode(["error" => "Failed to prepare statement: " . $conn->error]));
}
$stmt->bind_param("i", $articleId);
$stmt->execute();
$result = $stmt->get_result();

$editAssignments = [];
$locale = 'en_US'; // Contoh locale, sesuaikan dengan kebutuhan

// Mengambil semua minat dari database
$allInterests = getAllInterests($conn);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $editorId = $row['editor_id'];

        // Mengambil data editor berdasarkan editor_id
        $editorSql = "SELECT user_id, first_name, middle_name, last_name, email, country, gender, url, phone, fax FROM users WHERE user_id = ?";
        $editorStmt = $conn->prepare($editorSql);
        if (!$editorStmt) {
            die(json_encode(["error" => "Failed to prepare editor statement: " . $conn->error]));
        }
        $editorStmt->bind_param("i", $editorId);
        $editorStmt->execute();
        $editorResult = $editorStmt->get_result();

        if ($editorResult->num_rows > 0) {
            $editorRow = $editorResult->fetch_assoc();
            $settings = getUserSettings($conn, $editorId);
            $editorRow['affiliation'] = getLocalizedSetting($settings, 'affiliation', $locale);
            $profilePictureData = getLocalizedSetting($settings, 'profileImage', $locale);
            $profilePicture = unserializeSetting($profilePictureData);
            $editorRow['profilePicture'] = isset($profilePicture['uploadName']) ? $profilePicture['uploadName'] : '';
            $editorRow['interests'] = getUserInterests($conn, $editorId, $allInterests);
            $editAssignments[] = $editorRow;
        }
        $editorStmt->close();
    }
}
$stmt->close();

// Mengambil data reviewer dari database
$reviewerSql = "SELECT reviewer_id FROM review_assignments WHERE submission_id = ?";
$reviewerStmt = $conn->prepare($reviewerSql);
if (!$reviewerStmt) {
    die(json_encode(["error" => "Failed to prepare statement: " . $conn->error]));
}
$reviewerStmt->bind_param("i", $articleId);
$reviewerStmt->execute();
$reviewerResult = $reviewerStmt->get_result();

$reviewAssignments = [];

if ($reviewerResult->num_rows > 0) {
    while ($row = $reviewerResult->fetch_assoc()) {
        $reviewerId = $row['reviewer_id'];

        // Mengambil data reviewer berdasarkan reviewer_id
        $reviewerDataSql = "SELECT user_id, first_name, middle_name, last_name, email, country, gender, url, phone, fax FROM users WHERE user_id = ?";
        $reviewerDataStmt = $conn->prepare($reviewerDataSql);
        if (!$reviewerDataStmt) {
            die(json_encode(["error" => "Failed to prepare reviewer statement: " . $conn->error]));
        }
        $reviewerDataStmt->bind_param("i", $reviewerId);
        $reviewerDataStmt->execute();
        $reviewerDataResult = $reviewerDataStmt->get_result();

        if ($reviewerDataResult->num_rows > 0) {
            $reviewerRow = $reviewerDataResult->fetch_assoc();
            $settings = getUserSettings($conn, $reviewerId);
            $reviewerRow['affiliation'] = getLocalizedSetting($settings, 'affiliation', $locale);
            $profilePictureData = getLocalizedSetting($settings, 'profileImage', $locale);
            $profilePicture = unserializeSetting($profilePictureData);
            $reviewerRow['profilePicture'] = isset($profilePicture['uploadName']) ? $profilePicture['uploadName'] : '';
            $reviewerRow['interests'] = getUserInterests($conn, $reviewerId, $allInterests);
            $reviewAssignments[] = $reviewerRow;
        }
        $reviewerDataStmt->close();
    }
}
$reviewerStmt->close();

// Mengambil tanggal revisi dan tanggal diterima dari tabel edit_decisions
$decisionSql = "SELECT decision, date_decided FROM edit_decisions WHERE article_id = ?";
$decisionStmt = $conn->prepare($decisionSql);

if (!$decisionStmt) {
    die(json_encode(["error" => "Failed to prepare decision statement: " . $conn->error]));
}

$decisionStmt->bind_param("i", $articleId);
$decisionStmt->execute();
$decisionResult = $decisionStmt->get_result();

$revisionDate = null;
$acceptedDate = null;

while ($decisionRow = $decisionResult->fetch_assoc()) {
    if ($decisionRow['decision'] == 2) {
        $revisionDate = $decisionRow['date_decided'];
    } elseif ($decisionRow['decision'] == 1) {
        $acceptedDate = $decisionRow['date_decided'];
    }
}

$decisionStmt->close();
$conn->close();

$response = [
    'editors' => $editAssignments,
    'reviewers' => $reviewAssignments,
    'article' => [
        'revisionDate' => $revisionDate,
        'acceptedDate' => $acceptedDate
    ]
];

echo json_encode($response);
?>

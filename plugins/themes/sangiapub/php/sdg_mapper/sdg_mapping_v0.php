<?php
/* 
 * SDGs Mapping v1.1.7
 *
 */

// ==============================================
// KONFIGURASI
// ==============================================
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$config = array(
    'crossref' => array(
        'user' => 'crossref_username',
        'pass' => 'crossref_password'
    ),
    'openalex' => array(
        'key' => 'your_openalex_api_key'
    )
);

// Daftar kata kunci SDGs (lengkap)
$sdgKeywords = array(
    "SDG1"  => array("poverty", "inequality", "social protection", "economic disparity", "basic needs"),
    "SDG2"  => array("hunger", "food security", "agriculture", "malnutrition", "sustainable farming"),
    "SDG3"  => array("health", "well-being", "disease", "vaccine", "mental health", "healthcare access"),
    "SDG4"  => array("education", "learning", "school", "literacy", "digital education"),
    "SDG5"  => array("gender equality", "women empowerment", "feminism", "gender bias"),
    "SDG6"  => array("clean water", "sanitation", "water scarcity", "hygiene"),
    "SDG7"  => array("renewable energy", "clean energy", "solar power", "energy efficiency"),
    "SDG8"  => array("economic growth", "employment", "decent work", "entrepreneurship"),
    "SDG9"  => array("infrastructure", "industrialization", "innovation", "technology"),
    "SDG10" => array("reduced inequalities", "social inclusion", "migration", "discrimination"),
    "SDG11" => array("sustainable cities", "urban planning", "green spaces", "housing"),
    "SDG12" => array("responsible consumption", "waste management", "recycling", "circular economy"),
    "SDG13" => array("climate change", "global warming", "carbon emissions", "climate action"),
    "SDG14" => array("life below water", "ocean conservation", "marine pollution", "fisheries"),
    "SDG15" => array("life on land", "biodiversity", "deforestation", "land degradation"),
    "SDG16" => array("peace and justice", "human rights", "corruption", "transparency"),
    "SDG17" => array("partnerships", "global cooperation", "foreign aid", "sustainable development")
);

// ==============================================
// FUNGSI UTAMA: PROSES REQUEST
// ==============================================
function processRequest() {
    global $config;
    
    // Method 1: Support GET request (e.g., ?orcid=...)
    if (isset($_GET['orcid'])) {
        $orcid = $_GET['orcid'];
        return getOrcidData($orcid);
    }
    
    // Method 2: Support POST JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        if (isset($input['type']) && isset($input['id'])) {
            return ($input['type'] === 'orcid') 
                ? getOrcidData($input['id']) 
                : getDoiData($input['id']);
        }
    }
    
    // Jika tidak ada parameter valid
    return array(
        'error' => 'Invalid request',
        'usage' => array(
            'GET' => 'Add ?orcid=YOUR_ORCID to URL',
            'POST' => 'Send JSON: {"type":"orcid":"0000-0002-5152-9727"}'
        )
    );
}

// ==============================================
// FUNGSI ORCID API
// ==============================================
function getOrcidData($orcid) {
    $url = "https://pub.orcid.org/v3.0/{$orcid}/works";
    $response = fetchApi($url, array('Accept: application/json'));
    
    if (!$response) {
        return array('error' => 'Failed to fetch ORCID data');
    }
    
    $data = json_decode($response, true);
    $works = isset($data['group']) ? $data['group'] : array();
    
    return array(
        'orcid' => $orcid,
        'works_count' => count($works),
        'works' => array_slice($works, 0, 3), // Contoh: tampilkan 3 karya pertama
        'timestamp' => time()
    );
}

// ==============================================
// FUNGSI BANTU: FETCH API
// ==============================================
function fetchApi($url, $headers = array(), $user = null, $pass = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Nonaktifkan verifikasi SSL untuk testing
    
    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }
    
    if ($user && $pass) {
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ($httpCode === 200) ? $response : false;
}

// ==============================================
// EKSEKUSI
// ==============================================
echo json_encode(processRequest());
?>
<?php
// Set headers to allow cross-origin requests and specify JSON content type
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Check if DOI parameter exists
if (!isset($_GET['doi']) || empty($_GET['doi'])) {
    echo json_encode(['error' => 'DOI parameter is required']);
    exit;
}

// Get the DOI from the request and clean it
$doi = $_GET['doi'];

// Remove any 'https://doi.org/' prefix if present
if (strpos($doi, 'https://doi.org/') === 0) {
    $doi = substr($doi, strlen('https://doi.org/'));
}

// Ensure the DOI is properly URL encoded for the API request
$encodedDoi = urlencode($doi);

// Dimensions API endpoint
$url = 'https://app.dimensions.ai/api/dsl.json';

// Dimensions API query using DSL to get Field Citation Ratio
$query = '
{
    "query": {
        "match": {
            "doi": "' . $encodedDoi . '"
        }
    },
    "return_type": "publications",
    "limit": 1,
    "fields": [
        "id",
        "title",
        "field_citation_ratio"
    ]
}';

// Initialize cURL session
$ch = curl_init($url);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    // You may need an API key/token header here depending on Dimensions API requirements
    // 'Authorization: Bearer YOUR_API_TOKEN'
]);

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    echo json_encode(['error' => 'cURL error: ' . curl_error($ch)]);
    exit;
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);

// Check if decoding was successful
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Failed to parse JSON response']);
    exit;
}

// Extract the Field Citation Ratio from the response
$fcr = 0;
if (isset($data['publications']) && count($data['publications']) > 0) {
    if (isset($data['publications'][0]['field_citation_ratio'])) {
        $fcr = $data['publications'][0]['field_citation_ratio'];
    }
}

// Return the Field Citation Ratio as JSON
echo json_encode(['field_citation_ratio' => $fcr]);
?>
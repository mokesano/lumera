<?php
function getGoogleScholarCitations($doi, $api_key) {
    $query = urlencode("doi:$doi");
    $url = "https://serpapi.com/search.json?engine=google_scholar&q=$query&api_key=$api_key";
    
    try {
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        if (isset($data['error'])) {
            return ['error' => $data['error']];
        }
        
        // Ambil jumlah kutipan
        $citations = $data['cited_by']['total'] ?? 0;
        $articles = $data['organic_results'] ?? [];
        
        return [
            'citations' => $citations,
            'articles' => $articles
        ];
    } catch (Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

// Contoh penggunaan
$doi = "10.29239/j.agrikan.6.1.1-9";
$api_key = "fb21cfded60bc6037c84c8ae65889ccf7696b6724341f17feacfae8ac27563f3"; // Ganti dengan API key SerpAPI
$result = getGoogleScholarCitations($doi, $api_key);

print_r($result);
?>
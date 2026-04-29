<?php
declare(strict_types=1);

/**
 * @file plugins/generic/dataverse/classes/api/DataverseApiClient.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class DataverseApiClient
 * @brief Klien Native REST API untuk Dataverse (Menggantikan SWORD API v2).
 * [WIZDAM EDITION] Implementasi murni PHP 8.4 menggunakan cURL tanpa dependensi eksternal.
 */

class DataverseApiClient {
    
    /** @var DataversePlugin */
    private $_plugin;

    /**
     * Constructor
     * @param $plugin DataversePlugin
     */
    public function __construct($plugin) {
        $this->_plugin = $plugin;
    }

    /**
     * Mesin Inti Pemrosesan HTTP Request ke Dataverse REST API
     * @param int $journalId
     * @param string $method (GET, POST, PUT, DELETE)
     * @param string $endpoint (contoh: '/info/version')
     * @param mixed|null $payload Data array untuk JSON atau array CURLFile untuk upload
     * @param bool $isMultipart True jika mengirim file fisik
     * @return array|null Response dalam bentuk array asosiastif, null jika gagal fatal
     */
    private function executeRequest(int $journalId, string $method, string $endpoint, mixed $payload = null, bool $isMultipart = false): ?array {
        // Bersihkan URI dari garis miring berlebih di akhir
        $dvnUri = rtrim((string) $this->_plugin->getSetting($journalId, 'dvnUri'), '/');
        
        // Di arsitektur REST WIZDAM, field 'password' digunakan untuk menyimpan API TOKEN
        $apiToken = (string) $this->_plugin->getSetting($journalId, 'password');
        
        // Native REST API selalu menggunakan prefix /api/v1
        $url = $dvnUri . '/api/v1' . $endpoint;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Matikan jika server Dataverse self-signed
        
        // Header wajib: Token API
        $headers = [
            'X-Dataverse-key: ' . $apiToken,
            'Accept: application/json'
        ];

        if ($payload !== null) {
            if ($isMultipart) {
                // Upload file (cURL otomatis mengatur multipart/form-data boundary)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            } else {
                // Kirim JSON
                try {
                    $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
                    $headers[] = 'Content-Type: application/json';
                } catch (JsonException $e) {
                    error_log('WIZDAM Dataverse REST JSON Error: ' . $e->getMessage());
                    return null;
                }
            }
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Dukungan Proxy (jika dikonfigurasi di config.inc.php OJS)
        if ($httpProxyHost = Config::getVar('proxy', 'http_host')) {
            curl_setopt($ch, CURLOPT_PROXY, $httpProxyHost);
            curl_setopt($ch, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            if ($username = Config::getVar('proxy', 'username')) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('WIZDAM Dataverse REST cURL Error: ' . $curlError);
            return null;
        }

        try {
            $decodedResponse = json_decode((string) $response, true, 512, JSON_THROW_ON_ERROR);
            $decodedResponse['_http_status'] = $httpCode; // Sisipkan HTTP status untuk kemudahan pengecekan
            return $decodedResponse;
        } catch (JsonException $e) {
            error_log('WIZDAM Dataverse REST Decode Error: API mengembalikan respons non-JSON. HTTP: ' . $httpCode);
            return ['status' => 'ERROR', '_http_status' => $httpCode, 'message' => 'Invalid JSON response from Dataverse.'];
        }
    }

    /**
     * PENGGANTI getServiceDocument & checkAPIVersion
     * Menguji koneksi dan mengambil versi Dataverse.
     * @param int $journalId
     * @return bool True jika koneksi berhasil dan token valid
     */
    public function testConnection(int $journalId): bool {
        // Endpoint REST untuk mengecek info server
        $response = $this->executeRequest($journalId, 'GET', '/info/version');
        
        if ($response && isset($response['status']) && $response['status'] === 'OK') {
            // Ambil versi dan simpan ke database
            if (isset($response['data']['version'])) {
                $this->_plugin->updateSetting($journalId, 'apiVersion', (string) $response['data']['version'], 'string');
            }
            return true;
        }
        return false;
    }

    /**
     * PENGGANTI depositAtomEntry
     * Membuat dataset baru menggunakan JSON.
     * @param int $journalId
     * @param string $dataverseAlias (Nama pendek/alias dari Dataverse tujuan)
     * @param array $jsonMetadata (Format Native REST JSON API)
     * @return array|null Mengembalikan data dataset yang baru dibuat (termasuk persistentId/DOI)
     */
    public function createDataset(int $journalId, string $dataverseAlias, array $jsonMetadata): ?array {
        $endpoint = '/dataverses/' . urlencode($dataverseAlias) . '/datasets';
        $response = $this->executeRequest($journalId, 'POST', $endpoint, $jsonMetadata);
        
        if ($response && isset($response['status']) && $response['status'] === 'OK' && $response['_http_status'] === 201) {
            return $response['data']; // Berisi 'id' dan 'persistentId' (DOI)
        }
        
        error_log('WIZDAM Dataverse Create Dataset Failed: ' . print_r($response, true));
        return null;
    }

    /**
     * PENGGANTI deposit (Upload File)
     * Mengunggah file tunggal ke Dataset menggunakan CURLFile.
     * @param int $journalId
     * @param string $persistentId (DOI dataset, contoh: doi:10.5072/FK2/J8SJZB)
     * @param string $filePath (Lokasi absolut file di server WIZDAM)
     * @return bool
     */
    public function uploadFile(int $journalId, string $persistentId, string $filePath): bool {
        if (!file_exists($filePath)) return false;

        $endpoint = '/datasets/:persistentId/add?persistentId=' . urlencode($persistentId);
        
        // Format multipart untuk Dataverse Native API
        $payload = [
            'file' => new CURLFile($filePath)
        ];

        $response = $this->executeRequest($journalId, 'POST', $endpoint, $payload, true);
        
        return ($response && isset($response['status']) && $response['status'] === 'OK');
    }

    /**
     * PENGGANTI releaseStudy
     * Mempublikasikan dataset (Release).
     * @param int $journalId
     * @param string $persistentId
     * @param string $versionType ('major' atau 'minor')
     * @return bool
     */
    public function publishDataset(int $journalId, string $persistentId, string $versionType = 'major'): bool {
        $endpoint = '/datasets/:persistentId/actions/:publish?persistentId=' . urlencode($persistentId) . '&type=' . urlencode($versionType);
        $response = $this->executeRequest($journalId, 'POST', $endpoint);
        
        return ($response && isset($response['status']) && $response['status'] === 'OK');
    }

    /**
     * PENGGANTI deleteContainer (Delete Study)
     * Menghapus dataset (Hanya bisa dilakukan jika dataset masih DRAFT).
     * @param int $journalId
     * @param string $persistentId
     * @return bool
     */
    public function deleteDataset(int $journalId, string $persistentId): bool {
        $endpoint = '/datasets/:persistentId?persistentId=' . urlencode($persistentId);
        $response = $this->executeRequest($journalId, 'DELETE', $endpoint);
        
        return ($response && isset($response['status']) && $response['status'] === 'OK');
    }

    /**
     * PENGGANTI deleteResourceContent (Delete File)
     * Menghapus file spesifik dari Dataverse berdasarkan ID file Dataverse.
     * @param int $journalId
     * @param int $dataverseFileId
     * @return bool
     */
    public function deleteFile(int $journalId, int $dataverseFileId): bool {
        $endpoint = '/files/' . $dataverseFileId;
        $response = $this->executeRequest($journalId, 'DELETE', $endpoint);
        
        return ($response && isset($response['status']) && $response['status'] === 'OK');
    }

    /**
     * Mengambil Terms of Use dari sebuah Dataverse.
     * Dataverse REST API memiliki cara berbeda mengambil metadata Dataverse induk.
     * @param int $journalId
     * @param string $dataverseAlias
     * @return string
     */
    public function getTermsOfUse(int $journalId, string $dataverseAlias): string {
        $endpoint = '/dataverses/' . urlencode($dataverseAlias);
        $response = $this->executeRequest($journalId, 'GET', $endpoint);
        
        if ($response && isset($response['status']) && $response['status'] === 'OK') {
            // [WIZDAM NOTE] Tergantung dari pengaturan metadata block Dataverse, 
            // TOU mungkin berada di lokasi JSON yang berbeda. 
            // Untuk amannya, kita kembalikan string kosong atau deskripsi default sementara.
            return isset($response['data']['description']) ? (string) $response['data']['description'] : '';
        }
        return '';
    }
}
?>
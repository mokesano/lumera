<?php
/**
 * File: /plugins/themes/[nama_tema]/php/turnstile/TurnstileValidator.php
 * 
 * Class utama untuk validasi Turnstile Cloudflare
 */

class TurnstileValidator {
    private $secretKey;
    private $siteKey;
    
    public function __construct($secretKey, $siteKey) {
        $this->secretKey = $secretKey;
        $this->siteKey = $siteKey;
    }
    
    /**
     * Verifikasi token Turnstile ke server Cloudflare
     * 
     * @param string $token Token dari widget Turnstile
     * @param string $remoteIp IP address pengguna (opsional)
     * @return array Result dengan status success dan pesan error
     */
    public function verify($token, $remoteIp = null) {
        if (empty($token)) {
            return array(
                'success' => false,
                'error' => 'Token Turnstile tidak ditemukan'
            );
        }
        
        $data = array(
            'secret' => $this->secretKey,
            'response' => $token
        );
        
        if ($remoteIp) {
            $data['remoteip'] = $remoteIp;
        }
        
        $result = $this->makeRequest('https://challenges.cloudflare.com/turnstile/v0/siteverify', $data);
        
        if ($result === false) {
            return array(
                'success' => false,
                'error' => 'Gagal menghubungi server verifikasi'
            );
        }
        
        $response = json_decode($result, true);
        
        if ($response === null) {
            return array(
                'success' => false,
                'error' => 'Response tidak valid dari server'
            );
        }
        
        return array(
            'success' => $response['success'],
            'error' => $response['success'] ? null : 'Verifikasi Turnstile gagal',
            'error_codes' => isset($response['error-codes']) ? $response['error-codes'] : array()
        );
    }
    
    /**
     * Membuat HTTP request ke server Cloudflare
     * Menggunakan cURL atau file_get_contents sebagai fallback
     * 
     * @param string $url URL endpoint Cloudflare
     * @param array $data Data POST yang akan dikirim
     * @return string|false Response dari server atau false jika gagal
     */
    private function makeRequest($url, $data) {
        $postData = http_build_query($data);
        
        // Coba gunakan cURL terlebih dahulu (lebih reliable)
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_USERAGENT, 'OJS-Turnstile/1.0');
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Log error jika debug mode aktif
            if (defined('TURNSTILE_DEBUG') && TURNSTILE_DEBUG && $error) {
                error_log("Turnstile cURL Error: " . $error);
            }
            
            if ($result !== false && $httpCode == 200) {
                return $result;
            }
        }
        
        // Fallback ke file_get_contents jika cURL tidak tersedia
        if (ini_get('allow_url_fopen')) {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-Type: application/x-www-form-urlencoded\r\n" .
                               "User-Agent: OJS-Turnstile/1.0\r\n",
                    'content' => $postData,
                    'timeout' => 30
                )
            ));
            
            $result = @file_get_contents($url, false, $context);
            
            if (defined('TURNSTILE_DEBUG') && TURNSTILE_DEBUG && $result === false) {
                error_log("Turnstile file_get_contents failed");
            }
            
            return $result;
        }
        
        return false;
    }
    
    /**
     * Mendapatkan Site Key yang digunakan
     * 
     * @return string Site Key
     */
    public function getSiteKey() {
        return $this->siteKey;
    }
}
?>
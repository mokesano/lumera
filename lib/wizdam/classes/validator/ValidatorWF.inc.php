<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/validator/ValidatorWF.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] - Frontedge Security
 * @class ValidatorWF
 * @brief Menangani Web Application Firewall (WAF) internal, Rate Limiting, dan Request Tracing.
 */

class ValidatorWF {

    // Konfigurasi Rate Limiting
    private const MAX_REQUESTS = 100; // Maksimal request
    private const TIME_WINDOW = 60;   // Dalam rentang waktu detik (misal: 60 detik)
    
    // Properti untuk menyimpan ID unik sesi ini
    private static ?string $frontedgeId = null;

    /**
     * Menghasilkan atau mengambil Frontedge ID untuk pelacakan request
     * @return string Frontedge ID
     */
    public static function getFrontedgeId(): string {
        if (self::$frontedgeId === null) {
            // Menghasilkan ID dengan awalan WF- (Contoh: WF-A1B2C3D4E5F6)
            self::$frontedgeId = 'WF-' . strtoupper(substr(bin2hex(random_bytes(16)), 0, 16));
        }
        return self::$frontedgeId;
    }

    /**
     * Mendapatkan IP asli klien, menembus proxy/Cloudflare
     * @return string IP klien
     */
    public static function getClientIp(): string {
        // Prioritas 1: Header khusus Cloudflare
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }
        // Prioritas 2: Header standar Proxy/Load Balancer
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }
        // Prioritas 3: Fallback ke remote address standar
        return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN_IP';
    }

    /**
     * Memeriksa apakah IP klien melakukan spamming
     * Mengembalikan TRUE jika aman, FALSE jika terdeteksi spamming/anomali
     * @return bool Hasil pemeriksaan rate limit
     */
    public static function checkRateLimit(): bool {
        $ip = self::getClientIp();
        
        // Jangan blokir IP internal/localhost jika diperlukan
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === 'UNKNOWN_IP') {
            return true;
        }

        // Gunakan direktori temporary sistem untuk menyimpan counter
        $tmpDir = sys_get_temp_dir();
        // Buat nama file unik berdasarkan hash IP
        $cacheFile = $tmpDir . DIRECTORY_SEPARATOR . 'wizdam_wf_' . md5($ip) . '.json';

        $currentTime = time();
        $requestData = ['count' => 0, 'startTime' => $currentTime];

        // Baca data rate limit sebelumnya jika ada
        if (file_exists($cacheFile)) {
            $fileContent = file_get_contents($cacheFile);
            if ($fileContent) {
                $decoded = json_decode($fileContent, true);
                if (is_array($decoded) && isset($decoded['count'], $decoded['startTime'])) {
                    $requestData = $decoded;
                }
            }
        }

        // Cek apakah waktu sudah melewati time window (reset counter)
        if ($currentTime - $requestData['startTime'] > self::TIME_WINDOW) {
            $requestData['count'] = 1;
            $requestData['startTime'] = $currentTime;
        } else {
            // Jika masih dalam time window, tambah counter
            $requestData['count']++;
        }

        // Tulis ulang counter ke cache
        file_put_contents($cacheFile, json_encode($requestData), LOCK_EX);

        // Evaluasi limit
        if ($requestData['count'] > self::MAX_REQUESTS) {
            // Mencatat log ke sistem dengan mencantumkan Frontedge ID
            error_log(sprintf('[Wizdam Frontedge] IP %s blocked. Exceeded %d requests in %d seconds. Frontedge ID: %s', $ip, self::MAX_REQUESTS, self::TIME_WINDOW, self::getFrontedgeId()));
            return false;
        }

        return true;
    }
}
?>
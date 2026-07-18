<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/security/WizdamSecurity.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class WizdamSecurity
 * @ingroup wizdam_security
 *
 * @brief Centralized security helper for Lumera/Wizdam infrastructure.
 */

class WizdamSecurity {

    /**
     * Cek apakah IP terkena Rate Limit
     * @param PKPRequest $request
     * @param string $cacheName Nama cache untuk pengelompokan (ex: 'login', 'search')
     * @param int $threshold Batas percobaan sebelum diblokir
     * @return bool
     */
    public static function isRateLimited($request, $cacheName = 'wizdam_rate_limit', $threshold = 5) {
        $ip = $request->getRemoteAddr();
        $cacheManager = CacheManager::getManager();
        
        // Menggunakan closure sebagai callback default jika cache belum ada
        $cache = $cacheManager->getObjectCache($cacheName, $ip, function($ip) { return 0; });
        
        $attempts = (int) $cache->getCache($ip);
        return ($attempts >= $threshold);
    }

    /**
     * Increment counter percobaan (untuk kasus gagal)
     * @param PKPRequest $request
     * @param string $cacheName
     */
    public static function incrementAttempts($request, $cacheName = 'wizdam_rate_limit') {
        $ip = $request->getRemoteAddr();
        $cacheManager = CacheManager::getManager();
        $cache = $cacheManager->getObjectCache($cacheName, $ip, function($ip) { return 0; });
        
        $attempts = (int) $cache->getCache($ip);
        $cache->setCache($ip, $attempts + 1);
    }

    /**
     * Reset counter ke 0 (untuk kasus sukses)
     * @param PKPRequest $request
     * @param string $cacheName
     */
    public static function resetAttempts($request, $cacheName = 'wizdam_rate_limit') {
        $ip = $request->getRemoteAddr();
        $cacheManager = CacheManager::getManager();
        $cache = $cacheManager->getObjectCache($cacheName, $ip, function($ip) { return 0; });
        $cache->setCache($ip, 0);
    }

    /**
     * Sanitasi data objek (Metadata Filter)
     * Hapus properti sensitif dari data yang akan dikirim ke template
     * @param array $data Data yang akan dibersihkan
     * @return array
     */
    public static function sanitizeObjectData($data) {
        $sensitive = array('user_id', 'password', 'path_abs', 'db_internal_id', 'auth_id');
        
        foreach ($data as $key => $value) {
            // Jika elemen adalah objek OJS (DataObjects), bersihkan propertinya
            if (is_object($value) && method_exists($value, 'setData')) {
                foreach ($sensitive as $s) {
                    $value->setData($s, null);
                }
            }
        }
        return $data;
    }

}
?>
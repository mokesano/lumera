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
     * Cek apakah IP terkena Rate Limit berdasarkan ambang batas global
     * @param PKPRequest $request
     * @param string $action Nama aksi (contoh: 'login', 'search')
     * @return bool
     */
    public static function isRateLimited($request, string $action): bool {
        $threshold = Config::getVar('security', 'rate_limit');
        
        if ($threshold === null || (int) $threshold === 0) {
            return false; 
        }

        $ip = $request->getRemoteAddr();
        $cacheManager = CacheManager::getManager();
        $cacheName = 'wizdam_rate_limit_' . $action;
        $cache = $cacheManager->getObjectCache($cacheName, $ip, function($cache, $id) { return null; });
        $data = $cache->get($ip);
        if (is_array($data) && isset($data['attempts']) && isset($data['expires_at'])) {
            if (time() > $data['expires_at']) {
                return false; // Pemblokiran sudah kedaluwarsa, izinkan masuk
            }
            return ($data['attempts'] >= (int) $threshold);
        }

        return false;
    }

    /**
     * Increment counter percobaan
     * @param PKPRequest $request
     * @param string $action
     */
    public static function incrementAttempts($request, string $action): void {
        $threshold = Config::getVar('security', 'rate_limit');
        if ($threshold === null || (int) $threshold === 0) {
            return;
        }

        $ip = $request->getRemoteAddr();
        $cacheManager = CacheManager::getManager();
        $cacheName = 'wizdam_rate_limit_' . $action;
        
        $cache = $cacheManager->getObjectCache($cacheName, $ip, function($cache, $id) { return null; });
        
        $data = $cache->get($ip);
        $currentTime = time();
        $waitPeriod = 300; // 5 menit dalam satuan detik (5 * 60)

        // Tentukan apakah kita menambah angka yang ada, atau mereset dari awal
        if (is_array($data) && isset($data['attempts']) && isset($data['expires_at']) && $currentTime <= $data['expires_at']) {
            $attempts = $data['attempts'] + 1;
        } else {
            $attempts = 1;
        }

        $cache->setCache($ip, [
            'attempts' => $attempts,
            'expires_at' => $currentTime + $waitPeriod
        ]);
    }

    /**
     * Reset counter ke 0
     * @param PKPRequest $request
     * @param string $action
     */
    public static function resetAttempts($request, string $action): void {
        $ip = $request->getRemoteAddr();
        $cacheManager = CacheManager::getManager();
        $cacheName = 'wizdam_rate_limit_' . $action;
        
        $cache = $cacheManager->getObjectCache($cacheName, $ip, function($cache, $id) { return null; });
        
        // Timpa dengan nilai kosong/0 ke masa lalu untuk mereset paksa
        $cache->setCache($ip, [
            'attempts' => 0,
            'expires_at' => 0
        ]);
    }

    /**
     * Sanitasi data objek (Metadata Filter)
     * @param array $data 
     * @return array
     */
    public static function sanitizeObjectData($data): array {
        $sensitive = array('user_id', 'password', 'path_abs', 'db_internal_id', 'auth_id');
        
        foreach ($data as $key => $value) {
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
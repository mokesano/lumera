<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/validator/ValidatorCSRF.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] - Enhanced
 * @class ValidatorCSRF
 * @brief Menangani pembuatan dan validasi token pencegah Cross-Site Request Forgery (CSRF).
 */

class ValidatorCSRF {

    // Kunci sesi untuk menyimpan token dan waktu pembuatan
    private const TOKEN_KEY = 'wizdam_csrf_token';
    private const TIME_KEY = 'wizdam_csrf_time';
    
    // Masa berlaku token dalam detik (contoh: 7200 detik = 2 jam)
    private const TOKEN_LIFETIME = 7200; 

    // Nama field input HTML yang disepakati (menggunakan hyphen sesuai preferensi)
    public const FIELD_NAME = 'csrf-token';

    /**
     * Menghasilkan atau mengambil token CSRF dari sesi saat ini
     * @return string Token CSRF yang valid
     */
    public static function generateToken(): string {
        $request = Application::get()->getRequest();
        $session = $request->getSession();
        
        $token = $session->getSessionVar(self::TOKEN_KEY);
        $time = $session->getSessionVar(self::TIME_KEY);

        // Regenerasi jika token tidak ada, atau sudah melewati batas waktu (kedaluwarsa)
        if (!$token || (!$time) || (time() - $time > self::TOKEN_LIFETIME)) {
            $token = bin2hex(random_bytes(32));
            $session->setSessionVar(self::TOKEN_KEY, $token);
            $session->setSessionVar(self::TIME_KEY, time());
        }
        
        return $token;
    }

    /**
     * Menghapus token saat ini untuk memaksa regenerasi 
     * (Panggil fungsi ini saat proses Login/Logout)
     * @return void
     */
    public static function refreshToken(): void {
        $request = Application::get()->getRequest();
        $session = $request->getSession();
        
        $session->unsetSessionVar(self::TOKEN_KEY);
        $session->unsetSessionVar(self::TIME_KEY);
    }

    /**
     * Memvalidasi token yang dikirimkan oleh klien
     * @param string|null $clientToken Token yang diterima dari input form
     * @return bool True jika valid, False jika tidak valid atau kedaluwarsa
     */
    public static function checkToken(?string $clientToken): bool {
        if (empty($clientToken)) {
            return false;
        }

        $request = Application::get()->getRequest();
        $session = $request->getSession();
        
        $serverToken = $session->getSessionVar(self::TOKEN_KEY);
        $serverTime = $session->getSessionVar(self::TIME_KEY);
        
        // Gagal jika token di server tidak ada/kosong
        if (empty($serverToken)) {
            return false;
        }

        // Gagal jika token sudah kedaluwarsa
        if (!$serverTime || (time() - $serverTime > self::TOKEN_LIFETIME)) {
            self::refreshToken(); // Bersihkan token yang usang agar sesi selalu bersih
            return false;
        }

        // hash_equals mencegah serangan berbasis waktu (Timing Attack)
        return hash_equals($serverToken, $clientToken);
    }

    /**
     * Helper untuk mencetak input hidden pada form HTML secara otomatis
     * @return string HTML input hidden dengan nama FIELD_NAME
     */
    public static function getHtmlInput(): string {
        $token = self::generateToken();
        
        // Render input HTML menggunakan konstanta FIELD_NAME secara dinamis
        return sprintf(
            '<input type="hidden" name="%s" value="%s" />', 
            self::FIELD_NAME, 
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file lib/pkp/classes/validation/ValidatorCSRF.inc.php
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

    /** @var string Kunci sesi untuk menyimpan Master Token kriptografi. */
    private const TOKEN_KEY = 'wizdam_csrf_master_token';

    /** @var string Kunci sesi untuk mencatat waktu pembuatan Master Token. */
    private const TIME_KEY = 'wizdam_csrf_master_time';

    /** @var string Kunci sesi untuk menyimpan daftar Nonce Blacklist. */
    private const USED_NONCES_KEY = 'wizdam_csrf_used_nonces';

    /** @var int Umur Master Token dalam detik (Default: 7200 detik / 2 Jam). */
    private const TOKEN_LIFETIME = 7200; 

    /** @var int Batas antrean Nonce di RAM sebelum terlama ditendang. */
    private const MAX_BLACKLIST_SIZE = 50;
    
    /** @var string Nama field */
    public const FIELD_NAME = 'csrfToken';

    /**
     * Memadatkan string biner tanpa kehilangan data (Lossless).
     * @param string $data String biner mentah.
     * @return string Karakter Base64 yang aman untuk URL.
     */
    private static function base64url_encode(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Mengembalikan karakter URL-Safe kembali menjadi string biner murni.
     * @param string $data Karakter Base64 dari UI.
     * @return string String biner mentah asli.
     */
    private static function base64url_decode(string $data): string {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT), true);
    }

    /**
     * Mengambil atau membuat Master Token baru jika belum ada atau expired.
     * Master Token ini nyawa dari HMAC yang disimpan aman di dalam server.
     * @return string Master Token biner (32 bytes).
     */
    private static function getMasterSecret(): string {
        $request = Application::get()->getRequest();
        $session = $request->getSession();
        
        $token = $session->getSessionVar(self::TOKEN_KEY);
        $time = $session->getSessionVar(self::TIME_KEY);
        $currentTime = time();

        // Regenerasi jika kosong atau melewati batas umur (LIFETIME)
        if (!$token || !$time || ($currentTime - $time > self::TOKEN_LIFETIME)) {
            $token = bin2hex(random_bytes(32)); 
            $session->setSessionVar(self::TOKEN_KEY, $token);
            $session->setSessionVar(self::TIME_KEY, $currentTime);
            // Reset blacklist karena Master Token baru membuat semua token lama invalid
            $session->setSessionVar(self::USED_NONCES_KEY, []); 
        }
        
        return $token;
    }

    /**
     * Memeriksa apakah Nonce (Nomor Acak) dari klien sudah pernah digunakan.
     * @param string $nonceBase64 Nonce dalam bentuk Base64.
     * @return bool True jika sudah terpakai (bahaya), False jika masih segar.
     */
    private static function isNonceUsed(string $nonceBase64): bool {
        $blacklist = Application::get()->getRequest()->getSession()->getSessionVar(self::USED_NONCES_KEY);
        return is_array($blacklist) && isset($blacklist[$nonceBase64]);
    }

    /**
     * Menambahkan Nonce yang baru saja dipakai ke dalam Blacklist.
     * Mencegah form di-submit dua kali (Replay Attack).
     * @param string $nonceBase64 Nonce yang akan dihanguskan.
     */
    private static function blacklistNonce(string $nonceBase64): void {
        $session = Application::get()->getRequest()->getSession();
        $blacklist = $session->getSessionVar(self::USED_NONCES_KEY) ?? [];
        
        $blacklist[$nonceBase64] = time();
        
        // Mencegah memory leak dengan membuang antrean lama jika melebih batas
        if (count($blacklist) > self::MAX_BLACKLIST_SIZE) {
            asort($blacklist);
            unset($blacklist[array_key_first($blacklist)]);
        }
        $session->setSessionVar(self::USED_NONCES_KEY, $blacklist);
    }

    /**
     * Menciptakan Digital Signature murni menggunakan HMAC-SHA256.
     * @param string $nonceBin Nonce biner (16 bytes).
     * @param string $context Konteks token (misal: 'global', 'LoginHandler', 'Article_123').
     * @param array $immutableData Data tambahan tidak boleh diubah oleh klien.
     * @return string Signature biner (32 bytes).
     */
    private static function signPayloadBin(string $nonceBin, string $context, array $immutableData = []): string {
        $masterToken = self::getMasterSecret();
        ksort($immutableData);
        
        // Membangun string payload secara konsisten menggunakan $context alih-alih $actionName kaku
        $payloadString = $context . '|' . bin2hex($nonceBin) . '|' . http_build_query($immutableData);
        
        return hash_hmac('sha256', $payloadString, $masterToken, true);
    }

    /**
     * Membangkitkan CSRF Token.
     * Menggabungkan 16 bytes Nonce + 32 bytes Signature = 48 bytes.
     * @param string $actionName Label untuk membatasi ruang lingkup token.
     * @return string Token sepanjang 64 karakter (Lossless Base64URL).
     */
    public static function generateToken(string $context = 'global'): string {
        $nonceBin = random_bytes(16); 
        $signatureBin = self::signPayloadBin($nonceBin, $context); 
        
        return self::base64url_encode($nonceBin . $signatureBin);
    }

    /**
     * Membangkitkan CSRF Token yang mengunci sekumpulan data statis.
     * Berguna jika ada parameter form yang haram diubah (misal: ID Artikel).
     * @param string $actionName Label aksi form.
     * @param array $immutableData Array key-value data yang diikat ke token.
     * @return string Token sepanjang 64 karakter (Lossless Base64URL).
     */
    public static function generateSignedToken(string $context, array $immutableData): string {
        $nonceBin = random_bytes(16);
        $signatureBin = self::signPayloadBin($nonceBin, $context, $immutableData);
        
        return self::base64url_encode($nonceBin . $signatureBin);
    }

    /**
     * Membongkar dan memverifikasi token yang dikirim dari klien.
     * @param string|null $clientToken Token mentah form input (64 karakter).
     * @param string $actionName Label aksi yang diharapkan.
     * @param array $immutableData Data statis yang diharapkan.
     * @param bool $singleUse Jika True, token dihanguskan setelah tervalidasi.
     * @return bool True jika token sah dan belum expired.
     */
    public static function checkToken(?string $clientToken, string $context = 'global', array $immutableData = [], bool $singleUse = false): bool {
        if (empty($clientToken) || strlen($clientToken) !== 64) {
            return false;
        }

        $rawBin = self::base64url_decode($clientToken);
        
        if ($rawBin === false || strlen($rawBin) !== 48) {
            return false;
        }

        $clientNonceBin = substr($rawBin, 0, 16);
        $clientSignatureBin = substr($rawBin, 16, 32);

        $nonceKey = self::base64url_encode($clientNonceBin);
        if (self::isNonceUsed($nonceKey)) {
            return false;
        }

        // Verifikasi HMAC menggunakan $context yang sama
        $expectedSignatureBin = self::signPayloadBin($clientNonceBin, $context, $immutableData);

        if (hash_equals($expectedSignatureBin, $clientSignatureBin)) {
            // Hanya hanguskan Nonce jika login berhasil atau aksi spesifik selesai (singleUse = true)
            if ($singleUse) {
                self::blacklistNonce($nonceKey);
            }
            return true;
        }

        return false;
    }

    /**
     * Alias untuk checkToken agar terbaca saat memvalidasi token ber-payload.
     */
    public static function checkSignedToken(?string $clientToken, string $actionName, array $immutableData = [], bool $singleUse = false): bool {
        return self::checkToken($clientToken, $actionName, $immutableData, $singleUse);
    }

    /**
     * Menghancurkan seluruh kunci sesi CSRF.
     */
    public static function clearTokens(): void {
        $session = Application::get()->getRequest()->getSession();
        $session->unsetSessionVar(self::TOKEN_KEY);
        $session->unsetSessionVar(self::TIME_KEY);
        $session->unsetSessionVar(self::USED_NONCES_KEY);
    }
}
?>
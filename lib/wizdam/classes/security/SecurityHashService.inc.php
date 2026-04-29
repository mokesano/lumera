<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/security/SecurityHashService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance
 * @class SecurityHashService
 * @brief Layanan terpusat untuk pembuatan dan validasi hash keamanan dokumen publik/URL.
 */

class SecurityHashService {
    
    /** @var string Salt rahasia untuk HMAC/Hashing */
    private string $secretSalt;

    /**
     * Constructor
     */
    public function __construct() {
        // Mengisolasi pemanggilan Config di satu tempat.
        // Jika suatu saat mekanisme config berubah, hanya mengubahnya di sini.
        $this->secretSalt = Config::getVar('security', 'salt') ?: 'Wizdam_F4llb4ck_S4lt_2026_!@#';
    }

    /**
     * Menghasilkan hash SHA-256 statis berdasarkan entitas
     * @param string $documentType (misal: 'invoice', 'loa', 'certificate')
     * @param int $documentId ID dari database
     * @return string 64-karakter string hex
     */
    public function generateHash(string $documentType, int $documentId): string {
        return hash('sha256', $documentType . $documentId . $this->secretSalt);
    }

    /**
     * Memvalidasi hash yang diberikan dengan hash yang seharusnya (Mencegah Timing Attack)
     * @param string $documentType
     * @param int $documentId
     * @param string $providedHash
     * @return bool True jika valid, False jika dimanipulasi
     */
    public function validateHash(string $documentType, int $documentId, string $providedHash): bool {
        $expectedHash = $this->generateHash($documentType, $documentId);
        return hash_equals($expectedHash, $providedHash);
    }
}
?>
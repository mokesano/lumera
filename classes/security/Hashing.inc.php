<?php
declare(strict_types=1);

/**
 * @file classes/security/Hashing.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Hashing
 * @ingroup security
 *
 * @brief Class providing password hashing operations.
 * [WIZDAM EDITION] PHP 7.4+ Native Bcrypt Implementation (No Polyfill needed)
 */

class Hashing {

    /**
     * Constructor
     */
    public function __construct() {
        // Tidak perlu inisialisasi library eksternal lagi
    }
    
    /**
     * [SHIM] Backward Compatibility
     */
    public function Hashing() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error("Class 'Hashing' uses deprecated constructor parent::Hashing().", E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Generate a hash for a string (password).
     * Menggunakan algoritma default PHP (saat ini Bcrypt).
     * * @param $string string Plain text password
     * @return string|false
     */
    public function getHash($string) {
        // PASSWORD_DEFAULT akan menggunakan Bcrypt di PHP 7.x
        // Ini otomatis menghasilkan salt yang aman.
        return password_hash($string, PASSWORD_DEFAULT);
    }

    /**
     * Verify that a password matches a hash.
     * * @param $password string Plain text password
     * @param $hash string The hash to verify against
     * @return boolean
     */
    public function isValid($password, $hash) {
        // Cek apakah hash kosong (untuk menghindari bypass pada akun rusak)
        if (empty($hash)) return false;

        return password_verify($password, $hash);
    }

    /**
     * Check if the password needs to be rehashed.
     * Ini PENTING untuk migrasi otomatis. Jika algoritma PHP diupdate di masa depan,
     * atau jika cost factor dinaikkan, user akan otomatis direhash saat login.
     * * @param $hash string
     * @return boolean
     */
    public function needsRehash($hash) {
        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    /**
     * Is password library supported?
     * Di PHP 5.5+ (termasuk 7.4), ini selalu TRUE.
     * * @return boolean
     */
    public function isSupported() {
        return true;
    }
}

?>
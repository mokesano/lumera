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
 */

class Hashing {

    /**
     * Constructor.
     */
    public function __construct() {
        // No external library initialization needed for PHP 7.4+ native Bcrypt
    }
    
    /**
     * [SHIM] Backward Compatibility.
     */
    public function Hashing() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Generate a hash for a string (password).
     * Uses PHP's default algorithm (currently Bcrypt).
     * @param string $string
     * @return string|false
     */
    public function getHash($string) {
        return password_hash((string) $string, PASSWORD_DEFAULT);
    }

    /**
     * Verify that a password matches a hash.
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function isValid($password, $hash) {
        // Prevent bypass on corrupted accounts with empty hashes
        if ($hash === null || $hash === '') {
            return false;
        }

        return password_verify((string) $password, (string) $hash);
    }

    /**
     * Check if the password needs to be rehashed.
     * Important for automatic migration. If the PHP algorithm is updated in the future,
     * or if the cost factor is increased, the user will be automatically rehashed upon login.
     * @param string $hash
     * @return bool
     */
    public function needsRehash($hash) {
        return password_needs_rehash((string) $hash, PASSWORD_DEFAULT);
    }

    /**
     * Check if the password library is supported.
     * In PHP 5.5+ (including 7.4+), this is always true.
     * @return bool
     */
    public function isSupported() {
        return true;
    }
    
}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorName.inc.php
 *
 * @class ValidatorName
 * @ingroup validation
 *
 * @brief Validation check for person names (Given Name / Surname).
 * Rejects dummy characters, repeating single characters, and universal dummy abbreviations.
 */

import('lib.pkp.classes.validation.Validator');

class ValidatorName extends Validator {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Check whether the given name is valid.
     * @param mixed $value the value to be checked
     * @return boolean
     */
    public function isValid($value): bool {
        // Pastikan input adalah string
        if (!is_string($value)) {
            return false;
        }

        $name = trim($value);

        // 1. Cek apakah mengandung huruf asli (Unicode support untuk memblokir "-", ".", angka murni)
        if (!preg_match('/\p{L}/u', $name)) {
            return false;
        }

        // 2. Cek apakah hanya 1 karakter yang diulang-ulang (misal: "xxx", "zzz", "aaa")
        if (preg_match('/^(.)\1+$/u', strtolower($name))) {
            return false;
        }

        // 3. Cek singkatan dummy universal
        $universalDummies = ['na', 'n/a', 'nil', 'none'];
        if (in_array(strtolower($name), $universalDummies, true)) {
            return false;
        }

        return true;
    }
}

?>
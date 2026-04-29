<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorUri.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorUri
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check for URIs.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Explicit Static Method)
 */

import('lib.pkp.classes.validation.ValidatorRegExp');

class ValidatorUri extends ValidatorRegExp {
    
    /**
     * Constructor.
     */
    public function __construct($allowedSchemes = null) {
        // WIZDAM FIX: getRegexp is explicitly static
        parent::__construct(self::getRegexp($allowedSchemes));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ValidatorUri($allowedSchemes = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ValidatorUri(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($allowedSchemes);
    }

    //
    // Implement abstract methods from Validator
    //
    /**
     * @see ValidatorRegExp::isValid()
     * @param $value mixed
     * @return boolean
     */
    public function isValid($value) {
        if(!parent::isValid($value)) return false;

        // Retrieve the matches from the regexp validator
        $matches = $this->getMatches();

        // Check IPv4 address validity
        if (!empty($matches[4])) {
            $parts = explode('.', $matches[4]);
            foreach ($parts as $part) {
                if ($part > 255) {
                    return false;
                }
            }
        }

        return true;
    }

    //
    // Public static methods
    //
    /**
     * Return the regex for an URI check. This can be called
     * statically.
     * @param $allowedSchemes
     * @return string
     */
    public static function getRegexp($allowedSchemes = null) {
        if (is_array($allowedSchemes)) {
            $schemesRegEx = '(?:(' . implode('|', $allowedSchemes) . '):)';
            $regEx = $schemesRegEx . substr(PCRE_URI, 24);
        } else {
            $regEx = PCRE_URI;
        }
        return '&^' . $regEx . '$&i';
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorISNI.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorISNI
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check for ISNIs.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Type Safety)
 */

import('lib.pkp.classes.validation.ValidatorRegExp');

class ValidatorISNI extends ValidatorRegExp {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(self::getRegexp());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ValidatorISNI() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ValidatorISNI(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Implement abstract methods from Validator
    //
    
    /**
     * @see Validator::isValid()
     * @param $value mixed
     * @return boolean
     */
    public function isValid($value) {
        if (!parent::isValid($value)) return false;

        $matches = $this->getMatches();
        $match = $matches[0];

        $total = 0;
        for ($i=0; $i<15; $i++) {
            // WIZDAM FIX: Explicit integer cast for arithmetic operations
            $total = ($total + (int)$match[$i]) * 2;
        }
        
        $remainder = $total % 11;
        $result = (12 - $remainder) % 11;
        
        $checkDigit = ($result == 10 ? 'X' : $result);

        // Loose comparison needed here as $checkDigit might be int, $match string
        return ($match[15] == $checkDigit);
    }

    //
    // Public static methods
    //
    
    /**
     * Return the regex for an ISNI check. This can be called
     * statically.
     * @return string
     */
    public static function getRegexp() {
        return '/^(\d{15}[0-9X])$/';
    }
}

?>
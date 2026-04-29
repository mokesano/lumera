<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorISSN.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorISSN
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check for ISSNs.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Type Safety, Visibility)
 */

import('lib.pkp.classes.validation.ValidatorRegExp');

class ValidatorISSN extends ValidatorRegExp {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(self::getRegexp());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ValidatorISSN() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ValidatorISSN(). Please refactor to use parent::__construct().",
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

        // Test the check digit
        $matches = $this->getMatches();
        $issn = $matches[1] . $matches[2];

        $check = 0;
        for ($i=0; $i<7; $i++) {
            // WIZDAM FIX: Explicit integer cast for arithmetic on string characters
            $check += (int)$issn[$i] * (8-$i);
        }
        
        $check = $check % 11;
        
        switch ($check) {
            case 0:
                $check = '0';
                break;
            case 1:
                $check = 'X';
                break;
            default:
                $check = (string) (11 - $check);
        }
        
        return ($issn[7] === $check);
    }

    //
    // Public static methods
    //
    /**
     * Return the regex for an ISSN check. This can be called
     * statically.
     * @return string
     */
    public static function getRegexp() {
        return '/^(\d{4})-(\d{3}[\dX])$/';
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @defgroup validation
 */

/**
 * @file classes/validation/Validator.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Validator
 * @ingroup validation
 *
 * @brief Abstract class that represents a validation check. This class and its
 * sub-classes can be used outside a form validation context which enables
 * re-use of complex validation code.
 * * * REFACTORED: Wizdam Edition (PHP 8 Constructor & Type Visibility)
 */

class Validator {
    
    /**
     * Constructor.
     */
    public function __construct() {
        // Empty constructor
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Validator() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Validator(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Check whether the given value is valid.
     * @param $value mixed the value to be checked
     * @return boolean
     */
    public function isValid($value) {
        // To be implemented by sub-classes
        // Note: Ideally this class should be abstract, but kept as is for legacy compatibility.
        assert(false);
        return false;
    }
}

?>
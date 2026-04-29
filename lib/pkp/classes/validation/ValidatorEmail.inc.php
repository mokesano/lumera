<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorEmail.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorEmail
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check for email addresses.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Explicit Static Method)
 */

import('lib.pkp.classes.validation.ValidatorRegExp');

class ValidatorEmail extends ValidatorRegExp {
    
    /**
     * Constructor.
     */
    public function __construct() {
        // WIZDAM FIX: getRegexp is now explicitly static
        parent::__construct(self::getRegexp());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ValidatorEmail() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ValidatorEmail(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    //
    // Public static methods
    //
    
    /**
     * Return the regex for an email check. This can be called
     * statically.
     * @return string
     */
    public static function getRegexp() {
        return '/^' . PCRE_EMAIL_ADDRESS . '$/i';
    }
}

?>
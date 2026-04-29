<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorRegExp.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorRegExp
 * @ingroup validation
 *
 * @brief Validation check using a regular expression.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor & Visibility)
 */

import ('lib.pkp.classes.validation.Validator');

class ValidatorRegExp extends Validator {

    /** @var The regular expression to match against the field value */
    public $_regExp;

    /** @var The matches for further (optional) processing by subclasses */
    public $_matches;

    /**
     * Constructor.
     * @param $regExp string the regular expression (PCRE form)
     */
    public function __construct($regExp) {
        parent::__construct();
        $this->_regExp = $regExp;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ValidatorRegExp($regExp) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ValidatorRegExp(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($regExp);
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
        return (boolean)PKPString::regexp_match_get($this->_regExp, $value, $this->_matches);
    }


    //
    // Protected methods for use by sub-classes
    //
    /**
     * Returns the reg-ex matches (if any) after isValid() was called.
     */
    public function getMatches() {
        return $this->_matches;
    }
}

?>
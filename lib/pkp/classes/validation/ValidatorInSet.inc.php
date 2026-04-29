<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorInSet.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorInSet
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check for known sets.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Type Hinting, Visibility)
 */

import('lib.pkp.classes.validation.Validator');

class ValidatorInSet extends Validator {

    /** @var array of all values accepted as valid */
    public $_acceptedValues;
    
    /**
     * Constructor.
     * @param $validSet array
     */
    public function __construct(array $validSet = []) {
        parent::__construct();
        $this->_acceptedValues = $validSet;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ValidatorInSet($validSet = array()) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ValidatorInSet(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($validSet);
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
        if (!is_array($this->_acceptedValues)) {
            return false;
        }
        return in_array($value, $this->_acceptedValues);
    }

}
?>
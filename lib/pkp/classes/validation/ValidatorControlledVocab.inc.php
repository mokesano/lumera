<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorControlledVocab.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorControlledVocab
 * @ingroup validation
 *
 * @brief Validation check that checks if value is within a certain set retrieved
 * from the database.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, No References, Visibility)
 */

import('lib.pkp.classes.validation.Validator');

class ValidatorControlledVocab extends Validator {
    /** @var array */
    public $_acceptedValues;

    /**
     * Constructor.
     * @param $symbolic string
     * @param $assocType int
     * @param $assocId int
     */
    public function __construct($symbolic, $assocType, $assocId) {
        parent::__construct();
        
        // WIZDAM FIX: Removed reference (&) assignment
        $controlledVocabDao = DAORegistry::getDAO('ControlledVocabDAO');
        
        $controlledVocab = $controlledVocabDao->getBySymbolic($symbolic, $assocType, $assocId);
        if ($controlledVocab) {
            $this->_acceptedValues = array_keys($controlledVocab->enumerate());
        } else {
            $this->_acceptedValues = array();
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ValidatorControlledVocab($symbolic, $assocType, $assocId) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ValidatorControlledVocab(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($symbolic, $assocType, $assocId);
    }

    //
    // Implement abstract methods from Validator
    //
    /**
     * @see Validator::isValid()
     * Value is valid if it is empty and optional or is in the set of accepted values.
     * @param $value mixed
     * @return boolean
     */
    public function isValid($value) {
        return in_array($value, $this->_acceptedValues);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/validation/ValidatorORCID.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ValidatorORCID
 * @ingroup validation
 * @see Validator
 *
 * @brief Validation check for ORCID iDs.
 * * REFACTORED: Wizdam Edition (PHP 8 Constructor, Visibility, Explicit Static)
 */

import('lib.pkp.classes.validation.ValidatorRegExp');

class ValidatorORCID extends ValidatorRegExp {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct(self::getRegexp());
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ValidatorORCID() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::ValidatorORCID(). Please refactor to use parent::__construct().",
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
        // ORCID is an extension of ISNI
        // http://support.orcid.org/knowledgebase/articles/116780-structure-of-the-orcid-identifier
        $matches = $this->getMatches();
        
        // Combine parts to form the 16-character string for checksum validation
        $orcid = $matches[1] . $matches[2] . $matches[3] . $matches[4];

        import('lib.pkp.classes.validation.ValidatorISNI');
        $validator = new ValidatorISNI();
        return $validator->isValid($orcid);
    }

    //
    // Public static methods
    //
    /**
     * Return the regex for an ORCID check. This can be called
     * statically.
     * @return string
     */
    public static function getRegexp() {
        return '/^http[s]?:\/\/orcid.org\/(\d{4})-(\d{4})-(\d{4})-(\d{3}[0-9X])$/';
    }
}

?>
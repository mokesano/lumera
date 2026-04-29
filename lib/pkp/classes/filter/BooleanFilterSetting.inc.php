<?php
declare(strict_types=1);

/**
 * @file classes/filter/BooleanFilterSetting.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BooleanFilterSetting
 * @ingroup classes_filter
 *
 * @brief Class that describes a configurable filter setting which must
 * be either true or false.
 */

import('lib.pkp.classes.filter.FilterSetting');
import('lib.pkp.classes.form.validation.FormValidatorBoolean');

class BooleanFilterSetting extends FilterSetting {
    
    /**
     * Constructor
     *
     * @param $name string
     * @param $displayName string
     * @param $validationMessage string
     */
    public function __construct($name, $displayName, $validationMessage) {
        parent::__construct($name, $displayName, $validationMessage, FORM_VALIDATOR_OPTIONAL_VALUE);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BooleanFilterSetting($name, $displayName, $validationMessage) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::BooleanFilterSetting(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($name, $displayName, $validationMessage);
    }


    //
    // Implement abstract template methods from FilterSetting
    //
    /**
     * @see FilterSetting::getCheck()
     */
    public function getCheck($form) {
        $check = new FormValidatorBoolean($form, $this->getName(), $this->getValidationMessage());
        return $check;
    }
}
?>
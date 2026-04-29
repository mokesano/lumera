<?php
declare(strict_types=1);

/**
 * @file classes/filter/EmailFilterSetting.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailFilterSetting
 * @ingroup classes_filter
 *
 * @brief Class that describes a configurable filter setting which
 * must be an email.
 */

import('lib.pkp.classes.filter.FilterSetting');
import('lib.pkp.classes.form.validation.FormValidatorEmail');

class EmailFilterSetting extends FilterSetting {
    
    /**
     * Constructor
     *
     * @param $name string
     * @param $displayName string
     * @param $validationMessage string
     * @param $required boolean
     */
    public function __construct($name, $displayName, $validationMessage, $required = FORM_VALIDATOR_REQUIRED_VALUE) {
        parent::__construct($name, $displayName, $validationMessage, $required);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmailFilterSetting($name, $displayName, $validationMessage, $required = FORM_VALIDATOR_REQUIRED_VALUE) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::EmailFilterSetting(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($name, $displayName, $validationMessage, $required);
    }

    //
    // Implement abstract template methods from FilterSetting
    //
    /**
     * @see FilterSetting::getCheck()
     */
    public function getCheck($form) {
        $check = new FormValidatorEmail($form, $this->getName(), $this->getRequired(), $this->getValidationMessage());
        return $check;
    }
}
?>
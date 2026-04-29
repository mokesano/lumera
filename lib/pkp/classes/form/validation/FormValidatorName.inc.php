<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorName.inc.php
 *
 * @class FormValidatorName
 * @ingroup form_validation
 *
 * @brief Form validation check for person names (Given Name / Surname).
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorName extends FormValidator {
    
    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures
     */
    public function __construct($form, $field, $type, $message) {
        import('lib.pkp.classes.validation.ValidatorName');
        $validator = new ValidatorName();
        
        // Meneruskan parameter secara utuh ke parent, termasuk $type!
        parent::__construct($form, $field, $type, $message, $validator);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorName($form, $field, $type, $message) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message);
    }
}

?>
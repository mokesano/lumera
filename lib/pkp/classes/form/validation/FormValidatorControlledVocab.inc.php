<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorControlledVocab.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorControlledVocab
 * @ingroup form_validation
 *
 * @brief Form validation check that checks if value is within a certain set.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorControlledVocab extends FormValidator {
    
    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $type the type of check, either "required" or "optional"
     * @param string $message the error message for validation failures (i18n key)
     * @param string $symbolic
     * @param int $assocType
     * @param int $assocId
     */
    public function __construct($form, $field, $type, $message, $symbolic, $assocType, $assocId) {
        import('lib.pkp.classes.validation.ValidatorControlledVocab');
        // [WIZDAM] PHP 8 Safety: Ensure IDs are integers
        $validator = new ValidatorControlledVocab($symbolic, (int)$assocType, (int)$assocId);
        parent::__construct($form, $field, $type, $message, $validator);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorControlledVocab($form, $field, $type, $message, $symbolic, $assocType, $assocId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $type, $message, $symbolic, $assocType, $assocId);
    }
}

?>
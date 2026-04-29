<?php
declare(strict_types=1);

/**
 * @file classes/form/validation/FormValidatorListbuilder.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FormValidatorListbuilder
 * @ingroup form_validation
 *
 * @brief Form validation check that checks if the JSON value submitted unpacks into something that
 * contains at least one valid user id.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.validation.FormValidator');

class FormValidatorListbuilder extends FormValidator {

    /* outcome of validation after callbacks */
    protected bool $_valid = false;

    /**
     * Constructor.
     * @param Form $form the associated form
     * @param string $field the name of the associated field
     * @param string $message the error message for validation failures (i18n key)
     */
    public function __construct($form, $field, $message) {
        parent::__construct($form, $field, FORM_VALIDATOR_OPTIONAL_VALUE, $message);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormValidatorListbuilder($form, $field, $message) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($form, $field, $message);
    }

    //
    // Public methods
    //
    /**
     * Check the number of lisbuilder rows. If it's equal to 0, return false.
     *
     * @see FormValidator::isValid()
     * @return boolean
     */
    public function isValid() {
        $value = $this->getFieldValue();
        import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
        
        // [WIZDAM] PHP 8 Fix: $request was undefined in original code.
        // Retrieve request from the Application context.
        $request = Application::get()->getRequest();
        
        // Note: ListbuilderHandler::unpack usually expects the handler/delegate as well, 
        // but strict adherence to original logic implies static unpacking or global state reliance.
        // Assuming unpack() logic triggers callbacks on this instance or handles JSON parsing internally.
        ListbuilderHandler::unpack($request, $value);
        
        return $this->_valid;
    }

    /**
     * Callback used by ListbuilderHandler during unpack (potentially)
     * [WIZDAM] Removed explicit reference & on $request object
     */
    public function deleteEntry($request, $rowId, $numberOfRows) {
        if ($numberOfRows > 0) {
            $this->_valid = true;
        } else {
            $this->_valid = false;
        }

        return true;
    }

    /**
     * Callback used by ListbuilderHandler
     * [WIZDAM] Removed explicit reference & on $request object
     */
    public function insertEntry($request, $rowId) {
        return true;
    }
}

?>
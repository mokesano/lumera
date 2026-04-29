<?php
declare(strict_types=1);

/**
 * @defgroup form
 */

/**
 * @file classes/form/FormError.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION v3.4]
 * - Refactored for PHP 8.1+ Strict Mode
 * - Modern Property Definitions (Initialized)
 * - Constructor Shim for Backward Compatibility
 *
 * @class FormError
 * @ingroup form
 *
 * @brief Class to represent a form validation error.
 */


class FormError {

    /** @var string The name of the field */
    public $field = '';

    /** @var string The error message */
    public $message = '';

    /**
     * Constructor.
     * @param string $field the name of the field
     * @param string $message the error message (i18n key)
     */
    public function __construct(string $field, string $message) {
        $this->field = $field;
        $this->message = $message;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FormError() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::FormError(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($field, $message);
    }

    /**
     * Get the field associated with the error.
     * @return string
     */
    public function getField(): string {
        return $this->field;
    }

    /**
     * Get the error message (i18n key).
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }
}

?>
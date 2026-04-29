<?php
declare(strict_types=1);

/**
 * @file classes/handler/validation/HandlerValidator.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HandlerValidator
 * @ingroup security
 *
 * @brief Class to represent a page validation check.
 */

class HandlerValidator {

    /** @var Handler The Handler associated with the check */
    public $handler;

    /** @var bool flag for redirecting **/
    public $redirectToLogin;

    /** @var string message for login screen **/
    public $message;

    /** @var array additional Args to pass in the URL **/
    public $additionalArgs;

    /**
     * Constructor.
     * @param $handler Handler the associated form
     * @param $redirectToLogin boolean
     * @param $message string the error message for validation failures (i18n key)
     * @param $additionalArgs array
     */
    public function __construct($handler, $redirectToLogin = false, $message = null, $additionalArgs = array()) {
        // All handler validators are deprecated and
        // only exist for backwards compatibility.
        // FIXME: Switch warning message on when handler validator re-factoring is complete:
        // if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');

        // Hapus '&' pada assignment
        $this->handler = $handler;
        $this->redirectToLogin = $redirectToLogin;
        $this->message = $message;
        $this->additionalArgs = $additionalArgs;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HandlerValidator($handler, $redirectToLogin = false, $message = null, $additionalArgs = array()) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::HandlerValidator(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($handler, $redirectToLogin, $message, $additionalArgs);
    }

    /**
     * Check if field value is valid.
     * Default check is that field is either optional or not empty.
     * @return boolean
     */
    public function isValid() {
        return true;
    }

    /**
     * Set the handler associated with this check. Used only for PHP4
     * compatibility when instantiating without =& (which is deprecated).
     * SHOULD NOT BE USED otherwise.
     * @param $handler Handler
     */
    public function _setHandler($handler) {
        // Hapus '&'
        $this->handler = $handler;
    }
}

?>
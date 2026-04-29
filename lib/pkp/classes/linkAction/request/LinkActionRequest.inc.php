<?php
declare(strict_types=1);

/**
 * @defgroup linkAction_request
 */

/**
 * @file classes/linkAction/request/LinkActionRequest.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LinkActionRequest
 * @ingroup linkAction_request
 *
 * @brief Abstract base class defining an action to be taken when a link action is activated.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

class LinkActionRequest {
    
    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LinkActionRequest() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Public methods
    //
    /**
     * Return the JavaScript controller that will
     * handle this request.
     * @return string
     * @throws BadMethodCallException if not overridden by child class.
     */
    public function getJSLinkActionRequest(): string {
        // [WIZDAM] Replaced assert(false) with explicit exception for strict typing compliance.
        throw new BadMethodCallException('LinkActionRequest::getJSLinkActionRequest must be overridden by a subclass.');
    }

    /**
     * Return the options to be passed on to the
     * JS action request handler.
     * @return array An array describing the dialog
     * options.
     */
    public function getLocalizedOptions(): array {
        return [];
    }
}

?>
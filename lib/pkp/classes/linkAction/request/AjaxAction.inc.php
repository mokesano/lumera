<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/AjaxAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AjaxAction
 * @ingroup linkAction_request
 *
 * @brief Class defining an AJAX action.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

define('AJAX_REQUEST_TYPE_GET', 'get');
define('AJAX_REQUEST_TYPE_POST', 'post');

import('lib.pkp.classes.linkAction.request.LinkActionRequest');

class AjaxAction extends LinkActionRequest {

    /** @var string */
    protected string $_remoteAction;

    /** @var string */
    protected string $_requestType;

    /**
     * Constructor
     * @param string $remoteAction The target URL.
     * @param string $requestType One of the AJAX_REQUEST_TYPE_* constants.
     */
    public function __construct(string $remoteAction, string $requestType = AJAX_REQUEST_TYPE_POST) {
        parent::__construct();
        $this->_remoteAction = $remoteAction;
        $this->_requestType = $requestType;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AjaxAction($remoteAction, $requestType = AJAX_REQUEST_TYPE_POST) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($remoteAction, $requestType);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the target URL.
     * @return string
     */
    public function getRemoteAction(): string {
        return $this->_remoteAction;
    }

    /**
     * Get the request type (get/post).
     * @return string
     */
    public function getRequestType(): string {
        return $this->_requestType;
    }


    //
    // Overridden protected methods from LinkActionRequest
    //
    /**
     * @see LinkActionRequest::getJSLinkActionRequest()
     */
    public function getJSLinkActionRequest(): string {
        return '$.pkp.classes.linkAction.AjaxRequest';
    }

    /**
     * @see LinkActionRequest::getLocalizedOptions()
     */
    public function getLocalizedOptions(): array {
        return array(
            'url' => $this->getRemoteAction(),
            'requestType' => $this->getRequestType()
        );
    }
}

?>
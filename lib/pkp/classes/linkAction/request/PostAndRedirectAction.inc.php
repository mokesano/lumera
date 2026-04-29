<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/PostAndRedirectAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PostAndRedirectAction
 * @ingroup linkAction_request
 *
 * @brief Class defining a post and redirect action. See PostAndRedirectRequest.js
 * to detailed description.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.linkAction.request.RedirectAction');

class PostAndRedirectAction extends RedirectAction {

    /** @var string The url to be used for posting data */
    protected string $_postUrl;

    /**
     * Constructor
     * @param string $postUrl The target URL to post data.
     * @param string $redirectUrl The target URL to redirect.
     */
    public function __construct(string $postUrl, string $redirectUrl) {
        parent::__construct($redirectUrl);
        $this->_postUrl = $postUrl;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PostAndRedirectAction($postUrl, $redirectUrl) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($postUrl, $redirectUrl);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the url to post data.
     * @return string
     */
    public function getPostUrl(): string {
        return $this->_postUrl;
    }


    //
    // Overridden protected methods from LinkActionRequest
    //
    /**
     * @see LinkActionRequest::getJSLinkActionRequest()
     * @return string
     */
    public function getJSLinkActionRequest(): string {
        return '$.pkp.classes.linkAction.PostAndRedirectRequest';
    }

    /**
     * @see LinkActionRequest::getLocalizedOptions()
     * @return array
     */
    public function getLocalizedOptions(): array {
        $options = parent::getLocalizedOptions();
        return array_merge($options, array(
            'postUrl' => $this->getPostUrl()
        ));
    }
}

?>
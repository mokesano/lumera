<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/AjaxModal.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AjaxModal
 * @ingroup linkAction_request
 *
 * @brief A modal that retrieves its content from via AJAX.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.linkAction.request.Modal');

class AjaxModal extends Modal {
    /** @var string The URL to be loaded into the modal. */
    protected string $_url;

    /**
     * Constructor
     * @param string $url The URL of the AJAX resource to load into the modal.
     * @param string|null $title (optional) The localized modal title.
     * @param string|null $titleIcon (optional) The icon to be used in the modal title bar.
     * @param bool $canClose (optional) Whether the modal will have a close button.
     */
    public function __construct(string $url, ?string $title = null, ?string $titleIcon = null, bool $canClose = true) {
        parent::__construct($title, $titleIcon, $canClose);
        $this->_url = $url;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AjaxModal($url, $title = null, $titleIcon = null, $canClose = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($url, $title, $titleIcon, $canClose);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the URL to be loaded into the modal.
     * @return string
     */
    public function getUrl(): string {
        return $this->_url;
    }


    //
    // Overridden methods from LinkActionRequest
    //
    /**
     * @see LinkActionRequest::getLocalizedOptions()
     */
    public function getLocalizedOptions(): array {
        return array_merge(parent::getLocalizedOptions(), array(
            'modalHandler' => '$.pkp.controllers.modal.AjaxModalHandler',
            'url' => $this->getUrl()
        ));
    }
}

?>
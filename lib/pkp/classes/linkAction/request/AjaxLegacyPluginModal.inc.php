<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/AjaxLegacyPluginModal.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AjaxLegacyPluginModal
 * @ingroup linkAction_request
 *
 * @brief An ajax modal to be used in plugins management. This is part of a
 * temporary solution, while we don't modernize the UI of the plugins. The
 * functionalities implemented here are not necessary anywhere else.
 * DON'T USE this handler if you are not showing legacy plugins management content.
 * FIXME After modernizing the UI of the plugins, remove this class.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.linkAction.request.AjaxModal');

class AjaxLegacyPluginModal extends AjaxModal {
    
    /**
     * Constructor
     * @param string $url The URL of the AJAX resource to load into the modal.
     * @param string|null $title (optional) The localized modal title.
     * @param string|null $titleIcon (optional) The icon to be used in the modal title bar.
     * @param bool $canClose (optional) Whether the modal will have a close button.
     */
    public function __construct(string $url, ?string $title = null, ?string $titleIcon = null, bool $canClose = true) {
        parent::__construct($url, $title, $titleIcon, $canClose);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AjaxLegacyPluginModal($url, $title = null, $titleIcon = null, $canClose = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($url, $title, $titleIcon, $canClose);
    }

    //
    // Overridden methods from LinkActionRequest
    //
    /**
     * @see LinkActionRequest::getLocalizedOptions()
     */
    public function getLocalizedOptions(): array {
        return array_merge(parent::getLocalizedOptions(), array(
            'modalHandler' => '$.pkp.controllers.modal.AjaxLegacyPluginModalHandler',
            'url' => $this->getUrl()
        ));
    }
}

?>
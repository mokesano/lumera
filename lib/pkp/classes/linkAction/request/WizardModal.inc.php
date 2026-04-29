<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/WizardModal.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WizardModal
 * @ingroup linkAction_request
 *
 * @brief A modal that contains a wizard retrieved via AJAX.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.linkAction.request.AjaxModal');

class WizardModal extends AjaxModal {
    
    /**
     * Constructor
     * @param string $url The URL of the AJAX resource to load into the wizard modal.
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
    public function WizardModal($url, $title = null, $titleIcon = null, $canClose = true) {
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
        $options = parent::getLocalizedOptions();
        $options['modalHandler'] = '$.pkp.controllers.modal.WizardModalHandler';
        return $options;
    }
}

?>
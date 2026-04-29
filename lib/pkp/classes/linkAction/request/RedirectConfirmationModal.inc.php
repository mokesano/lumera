<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/RedirectConfirmationModal.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RedirectConfirmationModal
 * @ingroup linkAction_request
 *
 * @brief Class defining a simple confirmation modal with a redirect url and ok/cancel buttons.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.linkAction.request.ConfirmationModal');

class RedirectConfirmationModal extends ConfirmationModal {
    /** @var string|null A URL to be redirected to when the confirmation button is clicked. */
    protected ?string $_remoteUrl;

    /**
     * Constructor
     * @param string $dialogText The localized text to appear in the dialog modal.
     * @param string|null $title (optional) The localized modal title.
     * @param string|null $remoteUrl (optional) A URL to be redirected to when the confirmation button is clicked.
     * @param string|null $titleIcon (optional) The icon to be used in the modal title bar.
     * @param string|null $okButton (optional) The localized text to appear on the confirmation button.
     * @param string|null $cancelButton (optional) The localized text to appear on the cancel button.
     * @param bool $canClose (optional) Whether the modal will have a close button.
     */
    public function __construct(
        string $dialogText, 
        ?string $title = null, 
        ?string $remoteUrl = null, 
        ?string $titleIcon = null, 
        ?string $okButton = null, 
        ?string $cancelButton = null, 
        bool $canClose = true
    ) {
        // Parent constructor signature: ($dialogText, $title, $titleIcon, $okButton, $cancelButton, $canClose, $width)
        // We pass the parameters correctly skipping remoteUrl which belongs to this class only.
        parent::__construct($dialogText, $title, $titleIcon, $okButton, $cancelButton, $canClose);

        $this->_remoteUrl = $remoteUrl;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RedirectConfirmationModal($dialogText, $title = null, $remoteUrl = null, $titleIcon = null, $okButton = null, $cancelButton = null, $canClose = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($dialogText, $title, $remoteUrl, $titleIcon, $okButton, $cancelButton, $canClose);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the remote url.
     * @return string|null
     */
    public function getRemoteUrl(): ?string {
        return $this->_remoteUrl;
    }

    //
    // Overridden methods from LinkActionRequest
    //
    /**
     * @see LinkActionRequest::getLocalizedOptions()
     */
    public function getLocalizedOptions(): array {
        $parentLocalizedOptions = parent::getLocalizedOptions();
        
        // override the modalHandler option.
        $parentLocalizedOptions['modalHandler'] = '$.pkp.controllers.modal.RedirectConfirmationModalHandler';
        $parentLocalizedOptions['remoteUrl'] = $this->getRemoteUrl();
        
        return $parentLocalizedOptions;
    }
}

?>
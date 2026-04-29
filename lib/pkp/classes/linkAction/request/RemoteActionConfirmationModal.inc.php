<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/RemoteActionConfirmationModal.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RemoteActionConfirmationModal
 * @ingroup linkAction_request
 *
 * @brief Class defining a simple confirmation modal with a remote action and ok/cancel buttons.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.linkAction.request.ConfirmationModal');

class RemoteActionConfirmationModal extends ConfirmationModal {
    /** @var string|null A URL to be called when the confirmation button is clicked. */
    protected ?string $_remoteAction;

    /**
     * Constructor
     * @param string $dialogText The localized text to appear in the dialog modal.
     * @param string|null $title (optional) The localized modal title.
     * @param string|null $remoteAction (optional) A URL to be called when the confirmation button is clicked.
     * @param string|null $titleIcon (optional) The icon to be used in the modal title bar.
     * @param string|null $okButton (optional) The localized text to appear on the confirmation button.
     * @param string|null $cancelButton (optional) The localized text to appear on the cancel button.
     * @param bool $canClose (optional) Whether the modal will have a close button.
     */
    public function __construct(
        string $dialogText, 
        ?string $title = null, 
        ?string $remoteAction = null, 
        ?string $titleIcon = null, 
        ?string $okButton = null, 
        ?string $cancelButton = null, 
        bool $canClose = true
    ) {
        // Pass parameters to parent ConfirmationModal.
        // Note: We intentionally skip $remoteAction here as it belongs to this child class.
        parent::__construct($dialogText, $title, $titleIcon, $okButton, $cancelButton, $canClose);

        $this->_remoteAction = $remoteAction;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function RemoteActionConfirmationModal($dialogText, $title = null, $remoteAction = null, $titleIcon = null, $okButton = null, $cancelButton = null, $canClose = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($dialogText, $title, $remoteAction, $titleIcon, $okButton, $cancelButton, $canClose);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the remote action.
     * @return string|null
     */
    public function getRemoteAction(): ?string {
        return $this->_remoteAction;
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
        $parentLocalizedOptions['modalHandler'] = '$.pkp.controllers.modal.RemoteActionConfirmationModalHandler';
        $parentLocalizedOptions['remoteAction'] = $this->getRemoteAction();
        
        return $parentLocalizedOptions;
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/ConfirmationModal.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ConfirmationModal
 * @ingroup linkAction_request
 *
 * @brief Class defining a simple confirmation modal either with remote action or not.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.linkAction.request.Modal');

class ConfirmationModal extends Modal {
    /**
     * @var string A translation key defining the text for the confirmation
     * button of the modal.
     */
    protected string $_okButton;

    /**
     * @var string a translation key defining the text for the cancel
     * button of the modal.
     */
    protected string $_cancelButton;

    /**
     * @var string a translation key defining the text for the dialog
     * text.
     */
    protected string $_dialogText;

    /**
     * Constructor
     * @param string $dialogText The localized text to appear in the dialog modal.
     * @param string|null $title (optional) The localized modal title.
     * @param string|null $titleIcon (optional) The icon to be used in the modal title bar.
     * @param string|null $okButton (optional) The localized text to appear on the confirmation button.
     * @param string|null $cancelButton (optional) The localized text to appear on the cancel button.
     * @param bool $canClose (optional) Whether the modal will have a close button.
     * @param int $width (optional) Override the default width of 'auto' for confirmation modals.
     */
    public function __construct(
        string $dialogText, 
        ?string $title = null, 
        ?string $titleIcon = 'modal_confirm', 
        ?string $okButton = null, 
        ?string $cancelButton = null, 
        bool $canClose = true, 
        int $width = MODAL_WIDTH_AUTO
    ) {
        // [WIZDAM] PHP 8 Null Coalescing for default translations
        $effectiveTitle = $title ?? __('common.confirm');
        
        parent::__construct($effectiveTitle, $titleIcon, $canClose, $width);

        $this->_okButton = $okButton ?? __('common.ok');
        $this->_cancelButton = $cancelButton ?? __('common.cancel');
        $this->_dialogText = $dialogText;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ConfirmationModal($dialogText, $title = null, $titleIcon = 'modal_confirm', $okButton = null, $cancelButton = null, $canClose = true, $width = MODAL_WIDTH_AUTO) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($dialogText, $title, $titleIcon, $okButton, $cancelButton, $canClose, $width);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the translation key for the confirmation
     * button text.
     * @return string
     */
    public function getOkButton(): string {
        return $this->_okButton;
    }

    /**
     * Get the translation key for the cancel
     * button text.
     * @return string
     */
    public function getCancelButton(): string {
        return $this->_cancelButton;
    }

    /**
     * Get the translation key for the dialog
     * text.
     * @return string
     */
    public function getDialogText(): string {
        return $this->_dialogText;
    }

    //
    // Overridden methods from LinkActionRequest
    //
    /**
     * @see LinkActionRequest::getLocalizedOptions()
     */
    public function getLocalizedOptions(): array {
        return array_merge(parent::getLocalizedOptions(), array(
            'modalHandler' => '$.pkp.controllers.modal.ConfirmationModalHandler',
            'okButton' => $this->getOkButton(),
            'cancelButton' => $this->getCancelButton(),
            'dialogText' => $this->getDialogText()
        ));
    }
}

?>
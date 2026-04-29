<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/request/Modal.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Modal
 * @ingroup linkAction_request
 *
 * @brief Abstract base class for all modal dialogs.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

define('MODAL_WIDTH_DEFAULT', '710');
define('MODAL_WIDTH_AUTO', 'auto');

import('lib.pkp.classes.linkAction.request.LinkActionRequest');

class Modal extends LinkActionRequest {
    /** @var string|null The localized title of the modal. */
    protected ?string $_title;

    /** @var string|null The icon to be displayed in the title bar. */
    protected ?string $_titleIcon;

    /** @var boolean Whether the modal has a close icon in the title bar. */
    protected bool $_canClose;

    /** @var string The width of the modal */
    protected string $_width;

    /**
     * Constructor
     * @param string|null $title (optional) The localized modal title.
     * @param string|null $titleIcon (optional) The icon to be used in the modal title bar.
     * @param bool $canClose (optional) Whether the modal will have a close button.
     * @param string $width (optional) Override the default width of 'auto' ('710' or 'auto')
     */
    public function __construct(?string $title = null, ?string $titleIcon = null, bool $canClose = true, string $width = MODAL_WIDTH_DEFAULT) {
        parent::__construct();
        $this->_title = $title;
        $this->_titleIcon = $titleIcon;
        $this->_canClose = $canClose;
        $this->_width = $width;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Modal($title = null, $titleIcon = null, $canClose = true, $width = MODAL_WIDTH_DEFAULT) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($title, $titleIcon, $canClose, $width);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the localized title.
     * @return string|null
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
     * Get the title bar icon.
     * @return string|null
     */
    public function getTitleIcon() {
        return $this->_titleIcon;
    }

    /**
     * Whether the modal has a close icon in the title bar.
     * @return boolean
     */
    public function getCanClose() {
        return $this->_canClose;
    }

    /**
     * Get the width of the modal.
     * @return string
     */
    public function getWidth() {
        return $this->_width;
    }


    //
    // Overridden methods from LinkActionRequest
    //
    /**
     * @see LinkActionRequest::getJSLinkActionRequest()
     * @return string
     */
    public function getJSLinkActionRequest(): string {
        return '$.pkp.classes.linkAction.ModalRequest';
    }

    /**
     * @see LinkActionRequest::getLocalizedOptions()
     * @return array
     */
    public function getLocalizedOptions(): array {
        return array(
            'title' => $this->getTitle(),
            'titleIcon' => $this->getTitleIcon(),
            'canClose' => ($this->getCanClose() ? '1' : '0'),
            'width' => $this->getWidth(),
        );
    }
}

?>
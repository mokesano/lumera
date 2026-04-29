<?php
declare(strict_types=1);

/**
 * @file classes/linkAction/LegacyLinkAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LegacyLinkAction
 * @ingroup linkAction
 *
 * @brief Base class defining an action that can be performed within a Grid
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

define('LINK_ACTION_MODE_MODAL', 1);
define('LINK_ACTION_MODE_LINK', 2);
define('LINK_ACTION_MODE_AJAX', 3);
define('LINK_ACTION_MODE_CONFIRM', 4);

// Action types for modal mode
define('LINK_ACTION_TYPE_NOTHING', 'nothing');
define('LINK_ACTION_TYPE_APPEND', 'append');
define('LINK_ACTION_TYPE_REPLACE', 'replace');
define('LINK_ACTION_TYPE_REMOVE', 'remove');
define('LINK_ACTION_TYPE_REDIRECT', 'redirect');

// Actions types for AJAX mode
define('LINK_ACTION_TYPE_GET', 'get');
define('LINK_ACTION_TYPE_POST', 'post');

class LegacyLinkAction {
    /** @var string the id of the action */
    protected string $_id;

    /** @var string url of the action */
    protected string $_url;

    /** @var integer the mode of the action (modal, ajax, link, etc) */
    protected int $_mode;

    /** @var string the type of action to be done on callback */
    protected string $_type;

    /** @var string|null optional, the title of the link, translated */
    protected ?string $_title;

    /** @var string|null optional, the title of the link, translated */
    protected ?string $_titleLocalized;

    /** @var string|null optional, the URL to the image to be linked to */
    protected ?string $_image;

    /** @var string|null optional, the locale key for a message to display in a confirm dialog */
    protected ?string $_confirmMessageLocalized;

    /**
     * @var string|null a specification of the target on which the action
     * should act, e.g. a selector when the view technology is HTML/jQuery.
     *
     * The default depends on the implementation of the action type
     * in the view.
     */
    protected ?string $_actOn;

    /**
     * Constructor
     * @param string $id
     * @param int $mode one of LINK_ACTION_MODE_*
     * @param string $type one of LINK_ACTION_TYPE_*
     * @param string $url
     * @param string|null $title (optional)
     * @param string|null $titleLocalized (optional)
     * @param string|null $image (optional)
     * @param string|null $confirmMessageLocalized (optional)
     * @param string|null $actOn (optional) a specification of the target object to act on
     */
    public function __construct(
        string $id, 
        int $mode, 
        string $type, 
        string $url, 
        ?string $title = null, 
        ?string $titleLocalized = null, 
        ?string $image = null, 
        ?string $confirmMessageLocalized = null, 
        ?string $actOn = null
    ) {
        $this->_id = $id;
        $this->_mode = $mode;
        $this->_type = $type;
        $this->_url = $url;
        $this->_title = $title;
        $this->_titleLocalized = $titleLocalized;
        $this->_image = $image;
        $this->_confirmMessageLocalized = $confirmMessageLocalized;
        $this->_actOn = $actOn;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LegacyLinkAction($id, $mode, $type, $url, $title = null, $titleLocalized = null, $image = null, $confirmMessageLocalized = null, $actOn = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($id, $mode, $type, $url, $title, $titleLocalized, $image, $confirmMessageLocalized, $actOn);
    }

    /**
     * Set the action id.
     * @param string $id
     */
    public function setId(string $id) {
        $this->_id = $id;
    }

    /**
     * Get the action id.
     * @return string
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Set the action mode.
     * @param int $mode
     */
    public function setMode(int $mode) {
        $this->_mode = $mode;
    }

    /**
     * Get the action mode.
     * @return int
     */
    public function getMode() {
        return $this->_mode;
    }

    /**
     * Set the action type.
     * @param string $type
     */
    public function setType(string $type) {
        $this->_type = $type;
    }

    /**
     * Get the action type.
     * @return string
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * Set the action URL.
     * @param string $url
     */
    public function setUrl(string $url) {
        $this->_url = $url;
    }

    /**
     * Get the action URL.
     * @return string
     */
    public function getUrl() {
        return $this->_url;
    }

    /**
     * Set the action title.
     * @param string|null $title
     */
    public function setTitle(?string $title) {
        $this->_title = $title;
    }

    /**
     * Get the action title.
     * @return string|null
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
     * Set the column title (already translated)
     * @param string|null $titleLocalized
     */
    public function setTitleTranslated(?string $titleLocalized) {
        $this->_titleLocalized = $titleLocalized;
    }

    /**
     * Get the translated column title
     * @return string
     */
    public function getLocalizedTitle() {
        if ($this->_titleLocalized) {
            return $this->_titleLocalized;
        }
        // [WIZDAM] Ensure title is a string before passing to translation
        return __((string) $this->_title);
    }

    /**
     * Set the action image.
     * @param string|null $image
     */
    public function setImage(?string $image) {
        $this->_image = $image;
    }

    /**
     * Get the action image.
     * @return string|null
     */
    public function getImage() {
        return $this->_image;
    }

    /**
     * Set the locale key to display in the confirm dialog
     * @param string|null $confirmMessageLocalized
     */
    public function setLocalizedConfirmMessage(?string $confirmMessageLocalized) {
        $this->_confirmMessageLocalized = $confirmMessageLocalized;
    }

    /**
     * Get the locale key to display in the confirm dialog
     * @return string|null
     */
    public function getLocalizedConfirmMessage() {
        return $this->_confirmMessageLocalized;
    }

    /**
     * Specify the target object of the action (if any).
     * @param string|null $actOn
     */
    public function setActOn(?string $actOn) {
        $this->_actOn = $actOn;
    }

    /**
     * Get the target object of the action (null if none configured).
     * @return string|null
     */
    public function getActOn() {
        return $this->_actOn;
    }
}

?>
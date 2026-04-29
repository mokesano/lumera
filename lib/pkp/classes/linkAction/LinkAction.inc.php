<?php
declare(strict_types=1);

/**
 * @defgroup linkAction
 */

/**
 * @file classes/linkAction/LinkAction.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LinkAction
 * @ingroup linkAction
 *
 * @brief Base class defining an action that can be performed by the user
 * in the user interface.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

class LinkAction {
    /** @var string the id of the action */
    protected string $_id;

    /** @var LinkActionRequest The action to be taken when the link action is activated */
    protected LinkActionRequest $_actionRequest;

    /** @var string|null The localized title of the action. */
    protected ?string $_title;

    /** @var string|null The name of an icon for the action. */
    protected ?string $_image;

    /**
     * Constructor
     * @param string $id
     * @param LinkActionRequest $actionRequest The action to be taken when the link action is activated.
     * @param string|null $title (optional) The localized title of the action.
     * @param string|null $image (optional) The name of an icon for the action.
     */
    public function __construct(string $id, LinkActionRequest $actionRequest, ?string $title = null, ?string $image = null) {
        $this->_id = $id;
        // [WIZDAM] Objects are passed by identifier in PHP 5+, references (&) removed.
        // Type hint in signature handles the assertion automatically.
        $this->_actionRequest = $actionRequest;
        $this->_title = $title;
        $this->_image = $image;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LinkAction($id, $actionRequest, $title = null, $image = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to __construct().", 
                E_USER_DEPRECATED
            );
        }
        // Ensure type compatibility for the shim if passed loosely
        if (!($actionRequest instanceof LinkActionRequest)) {
             fatalError('Invalid LinkActionRequest passed to LinkAction constructor.');
        }
        self::__construct($id, $actionRequest, $title, $image);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the action id.
     * @return string
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Get the action handler.
     * @return LinkActionRequest
     */
    public function getActionRequest() {
        return $this->_actionRequest;
    }

    /**
     * Get the localized action title.
     * @return string|null
     */
    public function getTitle() {
        return $this->_title;
    }

    /**
     * Get a title for display when a user hovers over the
     * link action.  Default to the regular title if it is set.
     * @return string
     */
    public function getHoverTitle() {
        // for the locale key, remove any unique ids from the id.
        $id = preg_replace('/([^-]+)\-.+$/', '$1', $this->_id);
        // [WIZDAM] Ensure return is string even if translation fails
        return (string) __('grid.action.' . $id);
    }

    /**
     * Get the action image.
     * @return string|null
     */
    public function getImage() {
        return $this->_image;
    }
}

?>
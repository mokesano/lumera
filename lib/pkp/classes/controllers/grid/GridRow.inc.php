<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/GridRow.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridRow
 * @ingroup controllers_grid
 *
 * @brief Class defining basic operations for handling HTML gridRows.
 *
 * NB: If you want row-level refresh then you must override the getData() method
 * so that it fetches data (e.g. from the database) when called. The data to be
 * fetched can be determined from the id (=row id) which is always set.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

define('GRID_ACTION_POSITION_ROW_CLICK', 'row-click');
define('GRID_ACTION_POSITION_ROW_LEFT', 'row-left');

import('lib.pkp.classes.controllers.grid.GridBodyElement');

class GridRow extends GridBodyElement {

    /** @var array|null */
    protected ?array $requestArgs = null;

    /** @var string|null the grid this row belongs to */
    protected ?string $gridId = null;

    /** @var mixed the row's data source */
    protected $data = null;

    /** @var bool true if the row has been modified */
    protected bool $isModified = false;

    /**
     * @var array row actions, the first key represents
     * the position of the action in the row template,
     * the second key represents the action id.
     */
    protected array $actions = [GRID_ACTION_POSITION_DEFAULT => []];

    /** @var string|null the row template */
    protected ?string $template = null;


    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->isModified = false;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridRow() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }


    //
    // Getters/Setters
    //
    
    /**
     * Set the grid id
     * @param string|null $gridId
     */
    public function setGridId($gridId) {
        $this->gridId = $gridId;
    }

    /**
     * Get the grid id
     * @return string|null
     */
    public function getGridId() {
        return $this->gridId;
    }

    /**
     * Set the grid request parameters.
     * @see GridHandler::getRequestArgs()
     * @param array $requestArgs
     */
    public function setRequestArgs($requestArgs) {
        $this->requestArgs = $requestArgs;
    }

    /**
     * Get the grid request parameters.
     * @see GridHandler::getRequestArgs()
     * @return array|null
     */
    public function getRequestArgs() {
        return $this->requestArgs;
    }

    /**
     * Set the data element(s) for this controller
     * @param mixed $data
     */
    public function setData($data) {
        $this->data = $data;
    }

    /**
     * Get the data element(s) for this controller
     * @return mixed
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Set the modified flag for the row
     * @param bool $isModified
     */
    public function setIsModified($isModified) {
        $this->isModified = (bool) $isModified;
    }

    /**
     * Get the modified flag for the row
     * @return bool
     */
    public function getIsModified(): bool {
        return $this->isModified;
    }

    /**
     * Get whether this row has any actions or not.
     * @return bool
     */
    public function hasActions(): bool {
        $allActions = [];
        foreach($this->actions as $actions) {
            $allActions = array_merge($allActions, $actions);
        }

        return !empty($allActions);
    }

    /**
     * Get all actions for a given position within the controller
     * @param string $position the position of the actions
     * @return array the LinkActions for the given position
     */
    public function getActions($position = GRID_ACTION_POSITION_DEFAULT): array {
        if(!isset($this->actions[$position])) return [];
        return $this->actions[$position];
    }

    /**
     * Add an action
     * @param LinkAction $action a single action
     * @param string $position the position of the action
     */
    public function addAction($action, $position = GRID_ACTION_POSITION_DEFAULT) {
        if (!isset($this->actions[$position])) $this->actions[$position] = [];
        $this->actions[$position][$action->getId()] = $action;
    }

    /**
     * Get the row template - override base
     * implementation to provide a sensible default.
     * @return string|null
     */
    public function getTemplate() {
        return $this->template;
    }

    /**
     * Set the controller template
     * @param string $template
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    //
    // Public methods
    //
    
    /**
     * Initialize a row instance.
     *
     * Subclasses can override this method.
     *
     * @param Request $request
     * @param string $template
     */
    public function initialize($request, $template = 'controllers/grid/gridRow.tpl') {
        // Set the template.
        $this->setTemplate($template);
    }
}

?>
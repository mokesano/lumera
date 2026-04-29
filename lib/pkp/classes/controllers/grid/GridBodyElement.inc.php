<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/GridBodyElement.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridBodyElement
 * @ingroup controllers_grid
 *
 * @brief Base class for grid body elements.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

class GridBodyElement {
    /**
     * @var string identifier of the element instance - must be unique
     * among all instances within a grid.
     */
    protected string $id;

    /**
     * @var array flags that can be set by the handler to trigger layout
     * options in the element or in cells inside of it.
     */
    protected array $flags;

    /** @var GridCellProvider|null a cell provider for cells inside this element */
    protected ?GridCellProvider $cellProvider = null;

    /**
     * Constructor
     * @param string $id
     * @param GridCellProvider|null $cellProvider
     * @param array $flags
     */
    public function __construct($id = '', $cellProvider = null, $flags = []) {
        $this->id = $id;
        $this->cellProvider = $cellProvider;
        $this->flags = $flags;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridBodyElement($id = '', $cellProvider = null, $flags = []) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($id, $cellProvider, $flags);
    }

    //
    // Setters/Getters
    //
    
    /**
     * Get the element id
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * Set the element id
     * @param string $id
     */
    public function setId($id) {
        $this->id = (string) $id;
    }

    /**
     * Get all layout flags
     * @return array
     */
    public function getFlags(): array {
        return $this->flags;
    }

    /**
     * Get a single layout flag
     * @param string $flag
     * @return mixed
     */
    public function getFlag($flag) {
        assert(isset($this->flags[$flag]));
        return $this->flags[$flag];
    }

    /**
     * Check whether a layout flag is set to true.
     * @param string $flag
     * @return bool
     */
    public function hasFlag($flag): bool {
        if (!isset($this->flags[$flag])) return false;
        return (bool)$this->flags[$flag];
    }

    /**
     * Add a layout flag
     * @param string $flag
     * @param mixed $value optional
     */
    public function addFlag($flag, $value = true) {
        $this->flags[$flag] = $value;
    }

    /**
     * Get the cell provider
     * @return GridCellProvider|null
     */
    public function getCellProvider() {
        return $this->cellProvider;
    }

    /**
     * Set the cell provider
     * @param GridCellProvider|null $cellProvider
     */
    public function setCellProvider($cellProvider) {
        $this->cellProvider = $cellProvider;
    }
}

?>
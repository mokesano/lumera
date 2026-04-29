<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/GridColumn.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridColumn
 * @ingroup controllers_grid
 *
 * @brief Represents a column within a grid. It is used to configure the way
 * cells within a column are displayed (cell provider) and can also be used
 * to configure a editing strategy (not yet implemented). Contains all column-
 * specific configuration (e.g. column title).
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

define('COLUMN_ALIGNMENT_LEFT', 'left');
define('COLUMN_ALIGNMENT_CENTER', 'center');
define('COLUMN_ALIGNMENT_RIGHT', 'right');

import('lib.pkp.classes.controllers.grid.GridBodyElement');

class GridColumn extends GridBodyElement {
    /** @var string|null the column title i18n key */
    protected ?string $title;

    /** @var string|null the column title (translated) */
    protected ?string $titleTranslated;

    /** @var string the controller template for the cells in this column */
    protected string $template;

    /**
     * Constructor
     * @param string $id
     * @param string|null $title
     * @param string|null $titleTranslated
     * @param string $template
     * @param GridCellProvider|null $cellProvider
     * @param array $flags
     */
    public function __construct($id = '', $title = null, $titleTranslated = null,
            $template = 'controllers/grid/gridCell.tpl', $cellProvider = null, $flags = []) {

        parent::__construct($id, $cellProvider, $flags);

        $this->title = $title;
        $this->titleTranslated = $titleTranslated;
        $this->template = $template;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridColumn($id = '', $title = null, $titleTranslated = null,
            $template = 'controllers/grid/gridCell.tpl', $cellProvider = null, $flags = []) {
        self::__construct($id, $title, $titleTranslated, $template, $cellProvider, $flags);
    }

    //
    // Setters/Getters
    //
    
    /**
     * Get the column title
     * @return string|null
     */
    public function getTitle(): ?string {
        return $this->title;
    }

    /**
     * Set the column title (already translated)
     * @param string|null $title
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Set the column title (already translated)
     * @param string|null $titleTranslated
     */
    public function setTitleTranslated($titleTranslated) {
        $this->titleTranslated = $titleTranslated;
    }

    /**
     * Get the translated column title
     * @return string
     */
    public function getLocalizedTitle(): string {
        if ($this->titleTranslated) return $this->titleTranslated;
        return __($this->title);
    }

    /**
     * get the column's cell template
     * @return string
     */
    public function getTemplate(): string {
        return $this->template;
    }

    /**
     * set the column's cell template
     * @param string $template
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    /**
     * @see GridBodyElement::getCellProvider()
     * @return GridCellProvider
     */
    public function getCellProvider() {
        $provider = parent::getCellProvider();
        
        // [WIZDAM] Lazy load default provider if none exists
        if ($provider === null) {
            import('lib.pkp.classes.controllers.grid.ArrayGridCellProvider');
            $provider = new ArrayGridCellProvider();
            $this->setCellProvider($provider);
        }

        return $provider;
    }

    /**
     * Get cell actions for this column.
     *
     * NB: Subclasses have to override this method to
     * actually provide cell-specific actions. The default
     * implementation returns an empty array.
     *
     * @param Request $request
     * @param GridRow $row The row for which actions are being requested.
     * @param string $position
     * @return array An array of LinkActions for the cell.
     */
    public function getCellActions($request, $row, $position = GRID_ACTION_POSITION_DEFAULT): array {
        // The default implementation returns an empty array
        return [];
    }
}

?>
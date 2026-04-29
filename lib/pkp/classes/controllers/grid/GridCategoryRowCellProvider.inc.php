<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/GridCategoryRowCellProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCategoryRowCellProvider
 * @ingroup controllers_grid
 *
 * @brief Default grid category row column's cell provider. This class will retrieve
 * the template variables from the category row instance.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class GridCategoryRowCellProvider extends GridCellProvider {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridCategoryRowCellProvider() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Implemented methods from GridCellProvider.
    //
    
    /**
     * @see GridCellProvider::getTemplateVarsFromRowColumn()
     * @param GridCategoryRow $row (Expected type, though signature must allow GridRow)
     * @param GridColumn $column
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column): array {
        // Default category rows will only have the first column
        // as label columns.
        if ($column->hasFlag('firstColumn')) {
            // [WIZDAM] Ensure the row has the specific method or use generic access
            // In strict context, we assume $row is GridCategoryRow here
            return ['label' => $row->getCategoryLabel()];
        } else {
            return ['label' => ''];
        }
    }

    /**
     * @see GridCellProvider::getCellActions()
     * @param Request $request
     * @param GridRow $row
     * @param GridColumn $column
     * @param string $position
     * @return array
     */
    public function getCellActions($request, $row, $column, $position = GRID_ACTION_POSITION_DEFAULT): array {
        // Get cell actions from the row, that are
        // positioned with the GRID_ACTION_POSITION_ROW_CLICK
        // constant.
        return $row->getActions(GRID_ACTION_POSITION_ROW_CLICK);
    }

    /**
     * @see GridCellProvider::render()
     * @param Request $request
     * @param GridRow $row
     * @param GridColumn $column
     * @return string
     */
    public function render($request, $row, $column): string {
        // Default category rows will only have the first column
        // as label columns.
        if ($column->hasFlag('firstColumn')) {
            // Store the current column template.
            $template = $column->getTemplate();

            // Reset to the default column template.
            $column->setTemplate('controllers/grid/gridCell.tpl');

            // Render the cell.
            $renderedCell = parent::render($request, $row, $column);

            // Restore the original column template.
            $column->setTemplate($template);

            return $renderedCell;
        } else {
            return '';
        }
    }
}

?>
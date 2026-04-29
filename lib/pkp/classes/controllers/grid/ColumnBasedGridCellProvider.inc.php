<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/ColumnBasedGridCellProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ColumnBasedGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief A cell provider that relies on the column implementation
 * to provide cell content. Use this cell provider if you have complex
 * column-specific content. If you want to provide simple labels then
 * use the ArrayGridCellProvider or DataObjectGridCellProvider.
 *
 * @see ArrayGridCellProvider
 * @see DataObjectGridCellProvider
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ColumnBasedGridCellProvider extends GridCellProvider {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ColumnBasedGridCellProvider() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }


    //
    // Implement protected template methods from GridCellProvider
    //
    
    /**
     * @see GridCellProvider::getTemplateVarsFromRowColumn()
     * @param GridRow $row
     * @param GridColumn $column
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column): array {
        // Delegate to the column to provide template variables.
        return $column->getTemplateVarsFromRow($row);
    }
}

?>
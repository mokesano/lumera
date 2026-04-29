<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/GridCellProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Base class for a grid column's cell provider
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

class GridCellProvider {
    
    /**
     * Constructor
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridCellProvider() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Public methods
    //

    /**
     * To be used by a GridRow to generate a rendered representation of
     * the element for the given column.
     * [WIZDAM] Removed reference (&) from parameters. Added return type.
     *
     * @param Request $request
     * @param GridRow $row
     * @param GridColumn $column
     * @return string the rendered representation of the element for the given column
     */
    public function render($request, $row, $column): string {
        $columnId = $column->getId();
        assert(!empty($columnId));

        // Construct a default cell id (null for "nonexistent" new rows)
        $rowId = $row->getId(); // Potentially null (indicating row not backed in the DB)
        $cellId = isset($rowId) ? $rowId . '-' . $columnId : null;

        // Assign values extracted from the element for the cell.
        $templateMgr = TemplateManager::getManager();
        $templateVars = $this->getTemplateVarsFromRowColumn($row, $column);
        
        foreach ($templateVars as $varName => $varValue) {
            $templateMgr->assign($varName, $varValue);
        }

        $templateMgr->assign('id', $cellId);
        
        // [WIZDAM] Changed assign_by_ref to assign (Objects are handled correctly in PHP 8/Smarty)
        $templateMgr->assign('column', $column);
        $templateMgr->assign('actions', $this->getCellActions($request, $row, $column));
        $templateMgr->assign('flags', $column->getFlags());
        
        $templateMgr->assign('formLocales', AppLocale::getSupportedFormLocales());
        
        $template = $column->getTemplate();
        assert(!empty($template));
        
        return $templateMgr->fetch($template);
    }

    //
    // Protected template methods
    //
    
    /**
     * Subclasses have to implement this method to extract variables
     * for a given column from a data element so that they may be assigned
     * to template before rendering.
     * [WIZDAM] Removed reference (&) from $row
     *
     * @param GridRow $row
     * @param GridColumn $column
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column): array {
        return [];
    }

    /**
     * Subclasses can override this template method to provide
     * cell specific actions.
     *
     * NB: The default implementation delegates to the grid column for
     * cell-specific actions.
     * [WIZDAM] Removed reference (&) from return and params
     *
     * @param Request $request
     * @param GridRow $row
     * @param GridColumn $column
     * @param string $position
     * @return array an array of LinkAction instances
     */
    public function getCellActions($request, $row, $column, $position = GRID_ACTION_POSITION_DEFAULT): array {
        // [WIZDAM] Clean delegation without reference
        return $column->getCellActions($request, $row, $position);
    }
}

?>
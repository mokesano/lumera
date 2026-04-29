<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/ArrayGridCellProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArrayGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Base class for a cell provider that can retrieve labels from arrays
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards & Performance.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class ArrayGridCellProvider extends GridCellProvider {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility for legacy calls
     */
    public function ArrayGridCellProvider() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Template methods from GridCellProvider
    //
    /**
     * This implementation assumes a simple data element array that
     * has column ids as keys.
     * @see GridCellProvider::getTemplateVarsFromRowColumn()
     * @param GridRow $row
     * @param GridColumn $column
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column): array {
        $element = $row->getData();
        $columnId = $column->getId();
        
        // [WIZDAM] Optimized: in_array(keys) is slow. 
        // Changed to array_key_exists/is_array check.
        assert(is_array($element) && array_key_exists($columnId, $element));
        
        return ['label' => $element[$columnId]];
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/NullGridCellProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NullGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Class to return null when render method is called by a grid handler.
 * Use this when you want to create a column with no content at all (for layout
 * purposes using flags, for example).
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class NullGridCellProvider extends GridCellProvider {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NullGridCellProvider() {
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
     * @see GridCellProvider::render()
     * @param Request $request
     * @param GridRow $row
     * @param GridColumn $column
     * @return string
     */
    public function render($request, $row, $column): string {
        return '';
    }
}

?>
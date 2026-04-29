<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/citation/PKPCitationGridCellProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPCitationGridCellProvider
 * @ingroup controllers_grid_citation
 *
 * @brief Grid cell provider for the citation editor grid.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class PKPCitationGridCellProvider extends DataObjectGridCellProvider {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPCitationGridCellProvider() {
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
     * @see GridCellProvider::getTemplateVarsFromRowColumn()
     * @param GridRow $row
     * @param GridColumn $column
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column): array {
        $templateVars = parent::getTemplateVarsFromRowColumn($row, $column);
        $element = $row->getData();
        
        // Ensure data integrity
        assert($element instanceof Citation);
        
        $templateVars['isApproved'] = ($element->getCitationState() == CITATION_APPROVED);
        $templateVars['isCurrentItem'] = $row->getIsCurrentItem();
        $templateVars['citationSeq'] = $element->getSeq();
        
        return $templateVars;
    }


    /**
     * @see GridCellProvider::getCellActions()
     *
     * @param Request $request
     * @param GridRow $row
     * @param GridColumn $column
     * @param int $position
     * @return array
     */
    public function getCellActions($request, $row, $column, $position = GRID_ACTION_POSITION_DEFAULT): array {
        // The citation grid retrieves actions from the row.
        // We delegate directly to the row instance.
        return $row->getCellActions($request, $column, $position);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/filter/FilterGridCellProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FilterGridCellProvider
 * @ingroup classes_controllers_grid_filter
 *
 * @brief Base class for a cell provider that can retrieve labels from DataObjects
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class FilterGridCellProvider extends GridCellProvider {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FilterGridCellProvider() {
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
     * This implementation assumes an element that is a
     * Filter. It will display the filter name and information
     * about filter parameters (if any).
     * @see GridCellProvider::getTemplateVarsFromRowColumn()
     * @param GridRow $row
     * @param GridColumn $column
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column): array {
        $filter = $row->getData();
        
        // Ensure strictly that we are dealing with a Filter object
        if (!($filter instanceof Filter)) {
            return ['label' => ''];
        }

        $label = '';

        switch($column->getId()) {
            case 'settings':
                foreach($filter->getSettings() as $filterSetting) {
                    $settingData = $filter->getData($filterSetting->getName());
                    
                    if ($filterSetting instanceof BooleanFilterSetting) {
                        if ($settingData) {
                            if (!empty($label)) $label .= ' | ';
                            $label .= __($filterSetting->getDisplayName());
                        }
                    } else {
                        if (!empty($settingData)) {
                            if (!empty($label)) $label .= ' | ';
                            $label .= __($filterSetting->getDisplayName()).': '.$settingData;
                        }
                    }
                }
                break;

            default:
                $label = (string) $filter->getData($column->getId());
        }
        
        return ['label' => $label];
    }
}

?>
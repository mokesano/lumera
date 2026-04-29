<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/DataObjectGridCellProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DataObjectGridCellProvider
 * @ingroup controllers_grid
 *
 * @brief Base class for a cell provider that can retrieve simple labels
 * from DataObjects. If you need more complex cell content then you may
 * be better off using a ColumnBasedGridCellProvider.
 *
 * @see ColumnBasedGridCellProvider
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class DataObjectGridCellProvider extends GridCellProvider {
    /** 
     * @var string|null the locale to be retrieved. 
     * [WIZDAM] Renamed from $_locale
     */
    protected ?string $locale = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DataObjectGridCellProvider() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Setters and Getters
    //
    
    /**
     * Set the locale
     * @param string|null $locale
     */
    public function setLocale($locale) {
        $this->locale = $locale;
    }

    /**
     * Get the locale
     * @return string|null
     */
    public function getLocale() {
        return $this->locale;
    }


    //
    // Template methods from GridCellProvider
    //
    
    /**
     * This implementation assumes an element that is a
     * DataObject. It will retrieve an element in the
     * configured locale.
     * @see GridCellProvider::getTemplateVarsFromRowColumn()
     * @param GridRow $row
     * @param GridColumn $column
     * @return array
     */
    public function getTemplateVarsFromRowColumn($row, $column): array {
        $element = $row->getData();
        $columnId = $column->getId();
        
        // [WIZDAM] Use instanceof instead of is_a
        assert($element instanceof DataObject && !empty($columnId));
        
        return ['label' => $element->getData($columnId, $this->getLocale())];
    }
}

?>
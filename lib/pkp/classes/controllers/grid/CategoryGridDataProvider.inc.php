<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/CategoryGridDataProvider.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryGridDataProvider
 * @ingroup classes_controllers_grid
 *
 * @brief Provide access to category grid data. Can optionally use a grid data
 * provider object that already provides access to data that the grid needs.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

// Import base class.
import('lib.pkp.classes.controllers.grid.GridDataProvider');

class CategoryGridDataProvider extends GridDataProvider {

    /** * @var GridDataProvider|null A grid data provider that can be
     * used by this category grid data provider to provide access
     * to common data.
     * [WIZDAM] Renamed from $_dataProvider and typed.
     */
    public ?GridDataProvider $dataProvider = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CategoryGridDataProvider() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Getters and setters.
    //
    
    /**
     * Get a grid data provider object.
     * [WIZDAM] Removed reference return (&)
     * @return GridDataProvider|null
     */
    public function getDataProvider(): ?GridDataProvider {
        return $this->dataProvider;
    }

    /**
     * Set a grid data provider object.
     * [WIZDAM] Removed reference (&) from parameter.
     * @param GridDataProvider $dataProvider
     */
    public function setDataProvider($dataProvider) {
        // [WIZDAM] Modernized check using instanceof
        if ($dataProvider instanceof CategoryGridDataProvider) {
            // A CategoryGridDataProvider cannot use another CategoryGridDataProvider
            assert(false);
            $dataProvider = null;
        }

        $this->dataProvider = $dataProvider;
    }


    //
    // Overriden methods from GridDataProvider
    //
    
    /**
     * @see GridDataProvider::setAuthorizedContext()
     * [WIZDAM] Removed reference (&) from parameter
     */
    public function setAuthorizedContext($authorizedContext) {
        // We need to pass the authorized context object to
        // the grid data provider object, if any.
        $dataProvider = $this->getDataProvider();
        if ($dataProvider) {
            $dataProvider->setAuthorizedContext($authorizedContext);
        }

        parent::setAuthorizedContext($authorizedContext);
    }


    //
    // Template methods to be implemented by subclasses
    //
    
    /**
     * Retrieve the category data to load into the grid.
     * [WIZDAM] Removed reference return (&)
     * @param mixed $categoryDataElement
     * @param array|null $filter
     * @return mixed
     */
    public function getCategoryData($categoryDataElement, $filter = null) {
        assert(false);
        return [];
    }
}

?>
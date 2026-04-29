<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/feature/OrderCategoryGridItemsFeature.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OrderCategoryGridItemsFeature
 * @ingroup controllers_grid_feature
 *
 * @brief Implements category grid ordering functionality.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.feature.OrderItemsFeature');

// Constants used for defining scope of ordering
define_exposed('ORDER_CATEGORY_GRID_CATEGORIES_ONLY', 0x01);
define_exposed('ORDER_CATEGORY_GRID_CATEGORIES_ROWS_ONLY', 0x02);
define_exposed('ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS', 0x03);

class OrderCategoryGridItemsFeature extends OrderItemsFeature {

    /**
     * Constructor.
     * @param int $typeOption Defines which grid elements will be orderable (categories and/or rows).
     * @param bool $overrideRowTemplate This feature uses row actions and it will force the usage of the gridRow.tpl.
     */
    public function __construct(int $typeOption = ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS, bool $overrideRowTemplate = true) {
        parent::__construct($overrideRowTemplate);
        $this->addOptions(['type' => $typeOption]);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OrderCategoryGridItemsFeature(int $typeOption = ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS, bool $overrideRowTemplate = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($typeOption, $overrideRowTemplate);
    }

    //
    // Getters and setters.
    //
    /**
     * Return this feature type.
     * @return int One of the ORDER_CATEGORY_GRID_... constants
     */
    public function getType(): int {
        $options = $this->getOptions();
        return (int) $options['type'];
    }


    //
    // Extended methods from GridFeature.
    //
    /**
     * @see GridFeature::getJSClass()
     */
    public function getJSClass(): string {
        return '$.pkp.classes.features.OrderCategoryGridItemsFeature';
    }


    //
    // Hooks implementation.
    //
    /**
     * @see OrderItemsFeature::getInitializedRowInstance()
     */
    public function getInitializedRowInstance($args) {
        if ($this->getType() != ORDER_CATEGORY_GRID_CATEGORIES_ONLY) {
            parent::getInitializedRowInstance($args);
        }
    }

    /**
     * @see GridFeature::getInitializedCategoryRowInstance()
     */
    public function getInitializedCategoryRowInstance($args) {
        if ($this->getType() != ORDER_CATEGORY_GRID_CATEGORIES_ROWS_ONLY) {
            $row = $args['row'];
            // Ensure $row is valid before adding action
            if ($row) {
                $this->addRowOrderAction($row);
            }
        }
    }

    /**
     * @see GridFeature::saveSequence()
     */
    public function saveSequence($args) {
        $request = $args['request'];
        $grid = $args['grid'];

        import('lib.pkp.classes.core.JSONManager');
        $jsonManager = new JSONManager();
        $data = $jsonManager->decode($request->getUserVar('data'));
        
        // Ensure data is array/iterable before proceeding
        if (!is_array($data)) {
            return;
        }

        $gridCategoryElements = $grid->getGridDataElements($request);

        if ($this->getType() != ORDER_CATEGORY_GRID_CATEGORIES_ROWS_ONLY) {
            $categoriesData = [];
            foreach($data as $categoryData) {
                $categoriesData[] = $categoryData->categoryId;
            }

            // Save categories sequence.
            // Reset expects a reference, gridCategoryElements is a variable here, so it is safe.
            if (!empty($gridCategoryElements)) {
                $firstElement = reset($gridCategoryElements);
                $firstSeqValue = $grid->getCategoryDataElementSequence($firstElement);
                
                foreach ($gridCategoryElements as $rowId => $element) {
                    $rowPosition = array_search($rowId, $categoriesData);
                    if ($rowPosition !== false) {
                        $newSequence = $firstSeqValue + $rowPosition;
                        $currentSequence = $grid->getCategoryDataElementSequence($element);
                        if ($newSequence != $currentSequence) {
                            $grid->saveCategoryDataElementSequence($element, $newSequence);
                        }
                    }
                }
            }
        }

        // Save rows sequence, if this grid has also orderable rows inside each category.
        $this->_saveRowsInCategoriesSequence($grid, $gridCategoryElements, $data);
    }


    //
    // Private helper methods.
    //
    /**
     * Save row elements sequence inside categories.
     * @param GridHandler $grid
     * @param array $gridCategoryElements
     * @param array $data
     */
    private function _saveRowsInCategoriesSequence($grid, array $gridCategoryElements, array $data): void {
        if ($this->getType() != ORDER_CATEGORY_GRID_CATEGORIES_ONLY) {
            foreach($gridCategoryElements as $categoryId => $element) {
                $gridRowElements = $grid->getCategoryData($element);
                if (empty($gridRowElements)) continue;

                // Get the correct rows sequence data.
                $rowsData = null;
                foreach ($data as $categoryData) {
                    if ($categoryData->categoryId == $categoryId) {
                        $rowsData = $categoryData->rowsId;
                        break;
                    }
                }

                if ($rowsData === null) continue;

                $firstRowElement = reset($gridRowElements);
                $firstSeqValue = $grid->getRowDataElementSequence($firstRowElement);
                
                foreach ($gridRowElements as $rowId => $element) {
                    $rowPosition = array_search($rowId, $rowsData);
                    if ($rowPosition !== false) {
                        $newSequence = $firstSeqValue + $rowPosition;
                        $currentSequence = $grid->getRowDataElementSequence($element);
                        if ($newSequence != $currentSequence) {
                            $grid->saveRowDataElementSequence($element, $categoryId, $newSequence);
                        }
                    }
                }
            }
        }
    }
}

?>
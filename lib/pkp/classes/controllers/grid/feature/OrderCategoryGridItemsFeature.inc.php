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
 */

import('lib.pkp.classes.controllers.grid.feature.OrderItemsFeature');

// Constants used for defining scope of ordering
define('ORDER_CATEGORY_GRID_CATEGORIES_ONLY', 0x01);
define('ORDER_CATEGORY_GRID_CATEGORIES_ROWS_ONLY', 0x02);
define('ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS', 0x03);

class OrderCategoryGridItemsFeature extends OrderItemsFeature {

    /**
     * Constructor.
     * @param int $typeOption
     * @param bool $overrideRowTemplate
     */
    public function __construct(int $typeOption = ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS, bool $overrideRowTemplate = true) {
        parent::__construct($overrideRowTemplate);
        $this->addOptions(['type' => $typeOption]);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param int $typeOption
     * @param bool $overrideRowTemplate
     */
    public function OrderCategoryGridItemsFeature(int $typeOption = ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS, bool $overrideRowTemplate = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        $this->__construct($typeOption, $overrideRowTemplate);
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
        return (int) ($options['type'] ?? ORDER_CATEGORY_GRID_CATEGORIES_AND_ROWS);
    }

    //
    // Extended methods from GridFeature.
    //
    /**
     * Get javascript class
     * @see GridFeature::getJSClass()
     */
    public function getJSClass(): string {
        return '$.pkp.classes.features.OrderCategoryGridItemsFeature';
    }

    //
    // Hooks implementation.
    //
    /**
     * Get initialized row instance
     * @see OrderItemsFeature::getInitializedRowInstance()
     * @param array $args
     */
    public function getInitializedRowInstance($args) {
        if ($this->getType() !== ORDER_CATEGORY_GRID_CATEGORIES_ONLY) {
            parent::getInitializedRowInstance($args);
        }
    }

    /**
     * Get initialized category row instance
     * @see GridFeature::getInitializedCategoryRowInstance()
     * @param array $args
     */
    public function getInitializedCategoryRowInstance($args) {
        if ($this->getType() !== ORDER_CATEGORY_GRID_CATEGORIES_ROWS_ONLY) {
            $row = $args['row'] ?? null;
            if ($row) {
                $this->addRowOrderAction($row);
            }
        }
    }

    /**
     * Save sequences
     * 
     * @see GridFeature::saveSequence()
     * @param array $args
     */
    public function saveSequence($args) {
        $request = $args['request'] ?? null;
        $grid = $args['grid'] ?? null;
        
        if (!$request || !$grid) {
            return;
        }

        import('lib.pkp.classes.core.JSONManager');
        $jsonManager = new JSONManager();

        $data = $jsonManager->decode((string) $request->getUserVar('data'));
        
        if (!is_array($data)) {
            return;
        }

        $gridCategoryElements = $grid->getGridDataElements($request);

        if ($this->getType() !== ORDER_CATEGORY_GRID_CATEGORIES_ROWS_ONLY) {
            $categoriesData = [];
            foreach ($data as $categoryData) {
                $catObj = is_object($categoryData) ? $categoryData : (object) $categoryData;
                if (isset($catObj->categoryId)) {
                    $categoriesData[] = (string) $catObj->categoryId;
                }
            }

            if (!empty($gridCategoryElements)) {
                $firstElement = reset($gridCategoryElements);
                if ($firstElement) {
                    $firstSeqValue = $grid->getCategoryDataElementSequence($firstElement);
                    
                    foreach ($gridCategoryElements as $rowId => $element) {
                        $rowPosition = array_search((string) $rowId, $categoriesData);
                        if ($rowPosition !== false) {
                            $newSequence = $firstSeqValue + $rowPosition;
                            $currentSequence = $grid->getCategoryDataElementSequence($element);
                            if ($newSequence !== $currentSequence) {
                                $grid->saveCategoryDataElementSequence($element, $newSequence);
                            }
                        }
                    }
                }
            }
        }

        $this->_saveRowsInCategoriesSequence($grid, $gridCategoryElements, $data, $request);
    }

    //
    // Private helper methods.
    //
    /**
     * Save row elements sequence inside categories.
     * 
     * @param CategoryGridHandler $grid
     * @param array $gridCategoryElements
     * @param array $data
     * @param PKPRequest $request
     */
    private function _saveRowsInCategoriesSequence(CategoryGridHandler $grid, array $gridCategoryElements, array $data, $request): void {
        if (!$grid instanceof CategoryGridHandler) {
            return;
        }

        if ($this->getType() !== ORDER_CATEGORY_GRID_CATEGORIES_ONLY) {
            foreach ($gridCategoryElements as $categoryId => $element) {
                $gridRowElements = $grid->getCategoryData($element);
                
                if (empty($gridRowElements)) {
                    continue;
                }

                $rowsData = null;
                foreach ($data as $categoryData) {
                    $catObj = is_object($categoryData) ? $categoryData : (object) $categoryData;
                    if (isset($catObj->categoryId) && (string) $catObj->categoryId === (string) $categoryId) {
                        $rowsData = $catObj->rowsId ?? null;
                        break;
                    }
                }

                if ($rowsData === null || !is_array($rowsData)) {
                    continue;
                }

                $firstRowElement = reset($gridRowElements);
                if ($firstRowElement) {
                    $firstSeqValue = $grid->getRowDataElementSequence($firstRowElement);

                    foreach ($gridRowElements as $rowId => $rowElement) { 
                        $rowPosition = array_search((string) $rowId, $rowsData);
                        if ($rowPosition !== false) {
                            $newSequence = $firstSeqValue + $rowPosition;
                            $currentSequence = $grid->getRowDataElementSequence($rowElement);
                            
                            if ($newSequence !== $currentSequence) {
                                $grid->saveRowDataElementSequence($request, $categoryId, $rowElement, $newSequence);
                            }
                        }
                    }
                }
            }
        }
    }
    
}
?>
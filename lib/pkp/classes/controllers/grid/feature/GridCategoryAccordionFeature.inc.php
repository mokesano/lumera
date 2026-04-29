<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/feature/GridCategoryAccordionFeature.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridCategoryAccordionFeature
 * @ingroup controllers_grid_feature
 *
 * @brief Transform default grid categories in accordions.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

import('lib.pkp.classes.controllers.grid.feature.GridFeature');
import('lib.pkp.classes.linkAction.request.NullAction');

class GridCategoryAccordionFeature extends GridFeature {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('categoryAccordion');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridCategoryAccordionFeature() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * @see GridFeature::getJSClass()
     * @return string
     */
    public function getJSClass(): string {
        return '$.pkp.classes.features.GridCategoryAccordionFeature';
    }


    //
    // Hooks implementation.
    //
    
    /**
     * @see GridFeature::gridInitialize()
     * @param array $args
     */
    public function gridInitialize($args) {
        $grid = $args['grid'];
        // Ensure we are working with a GridHandler (or subclass)
        // Note: Using object check instead of generic assert for better static analysis compatibility if needed, 
        // but explicit check is safer at runtime.
        if (method_exists($grid, 'addAction')) {
            $grid->addAction(
                new LinkAction(
                    'expandAll',
                    new NullAction(),
                    __('grid.action.extendAll'),
                    'expand_all'
                )
            );

            $grid->addAction(
                new LinkAction(
                    'collapseAll',
                    new NullAction(),
                    __('grid.action.collapseAll'),
                    'collapse_all'
                )
            );
        }
    }

    /**
     * @see GridFeature::getInitializedCategoryRowInstance()
     * @param array $args
     */
    public function getInitializedCategoryRowInstance($args) {
        $request = $args['request'];
        $grid = $args['grid'];
        $row = $args['row'];

        // Ensure objects are valid before proceeding
        if (!$row || !method_exists($grid, 'getCategoryData')) return;

        // Check if we have category data, if not, don't
        // add the accordion link actions.
        $data = $row->getData();
        $filter = $grid->getFilterSelectionData($request);
        $categoryData = $grid->getCategoryData($data, $filter);

        if (empty($categoryData)) return;

        $row->addAction(
            new LinkAction(
                'expand',
                new NullAction(),
                '',
                'expanded'
            ), GRID_ACTION_POSITION_ROW_LEFT
        );

        $row->addAction(
            new LinkAction(
                'collapse',
                new NullAction(),
                '',
                'collapsed'
            ), GRID_ACTION_POSITION_ROW_LEFT
        );
    }
}

?>
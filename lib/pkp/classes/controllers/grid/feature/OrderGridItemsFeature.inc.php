<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/feature/OrderGridItemsFeature.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OrderGridItemsFeature
 * @ingroup controllers_grid_feature
 *
 * @brief Implements grid ordering functionality.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.feature.OrderItemsFeature');

class OrderGridItemsFeature extends OrderItemsFeature {

    /**
     * Constructor.
     * @param bool $overrideRowTemplate This feature uses row actions and it will force the usage of the gridRow.tpl.
     * If you want to use a different grid row template file, set this flag to false.
     */
    public function __construct(bool $overrideRowTemplate = true) {
        parent::__construct($overrideRowTemplate);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OrderGridItemsFeature(bool $overrideRowTemplate = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($overrideRowTemplate);
    }


    //
    // Extended methods from GridFeature.
    //
    /**
     * @see GridFeature::getJSClass()
     */
    public function getJSClass(): string {
        return '$.pkp.classes.features.OrderGridItemsFeature';
    }


    //
    // Hooks implementation.
    //
    /**
     * @see GridFeature::saveSequence()
     */
    public function saveSequence($args) {
        $request = $args['request'];
        $grid = $args['grid'];

        import('lib.pkp.classes.core.JSONManager');
        $jsonManager = new JSONManager();
        $data = $jsonManager->decode($request->getUserVar('data'));

        // Validate data format
        if (!is_array($data)) {
            return;
        }

        $gridElements = $grid->getGridDataElements($request);
        
        // Prevent reset() on empty array/null
        if (empty($gridElements)) {
            return;
        }

        $firstElement = reset($gridElements);
        $firstSeqValue = $grid->getRowDataElementSequence($firstElement);

        foreach ($gridElements as $rowId => $element) {
            $rowPosition = array_search($rowId, $data);
            
            // Only proceed if the rowId exists in the submitted data
            if ($rowPosition !== false) {
                $newSequence = $firstSeqValue + $rowPosition;
                $currentSequence = $grid->getRowDataElementSequence($element);
                
                if ($newSequence != $currentSequence) {
                    $grid->saveRowDataElementSequence($request, $rowId, $element, $newSequence);
                }
            }
        }
    }
}

?>
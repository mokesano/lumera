<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/filter/PKPFilterGridRow.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPFilterGridRow
 * @ingroup classes_controllers_grid_filter
 *
 * @brief The filter grid row definition
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class PKPFilterGridRow extends GridRow {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPFilterGridRow() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Overridden methods from GridRow
    //
    
    /**
     * @see GridRow::initialize()
     * @param PKPRequest $request
     */
    public function initialize($request, $template = null) {
        // Do the default initialization
        parent::initialize($request);

        // Is this a new row or an existing row?
        $rowId = $this->getId();
        if (!empty($rowId) && is_numeric($rowId)) {
            // Only add row actions if this is an existing row
            $router = $request->getRouter();
            $actionArgs = [
                'filterId' => $rowId
            ];

            // Add row actions
            $filter = $this->getData();

            // Ensure strictly that we are dealing with a Filter object
            if ($filter instanceof Filter) {
                // Only add an edit action if the filter actually has
                // settings to be configured.
                if ($filter->hasSettings()) {
                    $this->addAction(
                        new LegacyLinkAction(
                            'editFilter',
                            LINK_ACTION_MODE_MODAL,
                            LINK_ACTION_TYPE_REPLACE,
                            $router->url($request, null, null, 'editFilter', null, $actionArgs),
                            'grid.action.edit',
                            null,
                            'edit'
                        )
                    );
                }

                $this->addAction(
                    new LegacyLinkAction(
                        'deleteFilter',
                        LINK_ACTION_MODE_CONFIRM,
                        LINK_ACTION_TYPE_REMOVE,
                        $router->url($request, null, null, 'deleteFilter', null, $actionArgs),
                        'grid.action.delete',
                        null,
                        'delete',
                        __('manager.setup.filter.grid.confirmDelete', ['filterName' => $filter->getDisplayName()])
                    )
                );
            }
        }
    }
}

?>
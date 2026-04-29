<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/feature/OrderListbuilderItemsFeature.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OrderListbuilderItemsFeature
 * @ingroup controllers_grid_feature
 *
 * @brief Implements listbuilder ordering functionality.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.feature.OrderItemsFeature');

class OrderListbuilderItemsFeature extends OrderItemsFeature {

    /**
     * Constructor.
     */
    public function __construct() {
        // Pass false to overrideRowTemplate because Listbuilders handle their own row templates
        parent::__construct(false);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function OrderListbuilderItemsFeature() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Extended methods from GridFeature.
    //
    /**
     * @see GridFeature::getJSClass()
     * @return string
     */
    public function getJSClass(): string {
        return '$.pkp.classes.features.OrderListbuilderItemsFeature';
    }
}

?>
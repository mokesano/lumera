<?php
declare(strict_types=1);

/**
 * @file lib/pkp/controllers/grid/filter/ParserFilterGridHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ParserFilterGridHandler
 * @ingroup controllers_grid_filter
 *
 * @brief Defines the filters that will be configured in this grid.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('classes.controllers.grid.filter.FilterGridHandler');

class ParserFilterGridHandler extends FilterGridHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ParserFilterGridHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * @see PKPHandler::initialize()
     * @param PKPRequest $request
     * @param array|null $args
     */
    public function initialize($request, $args = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Set the filter group defining the filters
        // configured in this grid.
        // Note: CITATION_PARSER_FILTER_GROUP must be defined in constants
        $this->setFilterGroupSymbolic(CITATION_PARSER_FILTER_GROUP);

        // Set the title of this grid
        $this->setTitle('manager.setup.filter.parser.grid.title');
        $this->setFormDescription('manager.setup.filter.parser.grid.description');

        parent::initialize($request, $args);
    }
}

?>
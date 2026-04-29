<?php
declare(strict_types=1);

/**
 * @file classes/controllers/listbuilder/ListbuilderGridColumn.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ListbuilderGridColumn
 * @ingroup controllers_listbuilder
 *
 * @brief Represents a column within a listbuilder.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.GridColumn');

class ListbuilderGridColumn extends GridColumn {

    /**
     * Constructor
     * @param ListbuilderHandler $listbuilder
     * @param string $id
     * @param string|null $title
     * @param string|null $titleTranslated
     * @param string|null $template
     * @param object|null $cellProvider
     * @param array $flags
     */
    public function __construct($listbuilder, $id = '', $title = null, $titleTranslated = null,
            $template = null, $cellProvider = null, $flags = []) {

        // Set this here so that callers using later optional parameters don't need to
        // duplicate it.
        if ($template === null) {
            $template = 'controllers/listbuilder/listbuilderGridCell.tpl';
        }

        // Make the listbuilder's source type available to the cell template as a flag
        // [WIZDAM] Strict Object Validation
        if (is_object($listbuilder) && method_exists($listbuilder, 'getSourceType')) {
            $flags['sourceType'] = $listbuilder->getSourceType();
        }

        parent::__construct($id, $title, $titleTranslated, $template, $cellProvider, $flags);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ListbuilderGridColumn($listbuilder, $id = '', $title = null, $titleTranslated = null,
            $template = null, $cellProvider = null, $flags = []) {
        
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        
        self::__construct($listbuilder, $id, $title, $titleTranslated, $template, $cellProvider, $flags);
    }
}

?>
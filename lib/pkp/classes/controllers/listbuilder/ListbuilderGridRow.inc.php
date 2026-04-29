<?php
declare(strict_types=1);

/**
 * @file classes/controllers/listbuilder/ListbuilderGridRow.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ListbuilderGridRow
 * @ingroup controllers_listbuilder
 *
 * @brief Handle list builder row requests.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class ListbuilderGridRow extends GridRow {

    /** @var bool */
    protected bool $_hasDeleteItemLink = true;

    /**
     * Constructor
     * @param bool $hasDeleteItemLink
     */
    public function __construct(bool $hasDeleteItemLink = true) {
        parent::__construct();

        $this->setHasDeleteItemLink($hasDeleteItemLink);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ListbuilderGridRow(bool $hasDeleteItemLink = true) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($hasDeleteItemLink);
    }

    /**
     * Add a delete item link action or not.
     * @param bool $hasDeleteItemLink
     */
    public function setHasDeleteItemLink(bool $hasDeleteItemLink): void {
        $this->_hasDeleteItemLink = $hasDeleteItemLink;
    }


    //
    // Overridden template methods
    //
    
    /**
     * @see GridRow::initialize()
     * @param PKPRequest $request
     * @param string|null $template
     */
    public function initialize($request, $template = 'controllers/listbuilder/listbuilderGridRow.tpl') {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        parent::initialize($request);

        // Set listbuilder row template
        $this->setTemplate($template);

        if ($this->_hasDeleteItemLink) {
            // Add deletion action (handled in JS-land)
            import('lib.pkp.classes.linkAction.request.NullAction');
            $this->addAction(
                new LinkAction(
                    'delete',
                    new NullAction(),
                    '',
                    'remove_item'
                )
            );
        }
    }

    /**
     * @see GridRow::addAction()
     * @param LinkAction $action
     * @param string|null $position Ignored in Listbuilder, forced to LEFT
     */
    public function addAction($action, $position = null) {
        return parent::addAction($action, GRID_ACTION_POSITION_ROW_LEFT);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/citation/PKPCitationGridRow.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPCitationGridRow
 * @ingroup classes_controllers_grid_citation
 *
 * @brief The citation grid row definition
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class PKPCitationGridRow extends GridRow {
    /** @var int */
    protected int $assocId = 0;

    /** @var bool */
    protected bool $isCurrentItem = false;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPCitationGridRow() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }


    //
    // Getters and Setters
    //
    
    /**
     * Set the assoc id
     * @param int $assocId
     */
    public function setAssocId($assocId) {
        $this->assocId = (int)$assocId;
    }

    /**
     * Get the assoc id
     * @return int
     */
    public function getAssocId(): int {
        return $this->assocId;
    }

    /**
     * Set the current item flag
     * @param bool $isCurrentItem
     */
    public function setIsCurrentItem($isCurrentItem) {
        $this->isCurrentItem = (bool)$isCurrentItem;
    }

    /**
     * Get the current item flag
     * @return bool
     */
    public function getIsCurrentItem(): bool {
        return $this->isCurrentItem;
    }


    //
    // Overridden methods from GridRow
    //
    
    /**
     * @see GridRow::initialize()
     * @param Request $request
     */
    public function initialize($request) {
        // Do the default initialization
        parent::initialize($request);

        // Retrieve the assoc id from the request
        $assocId = $request->getUserVar('assocId');
        assert(is_numeric($assocId));
        $this->setAssocId((int)$assocId);

        // Is this a new row or an existing row?
        $rowId = $this->getId();
        if (!empty($rowId) && is_numeric($rowId)) {
            // Only add row actions if this is an existing row
            $router = $request->getRouter();
            $this->addAction(
                new LegacyLinkAction(
                    'deleteCitation',
                    LINK_ACTION_MODE_CONFIRM,
                    LINK_ACTION_TYPE_REMOVE,
                    $router->url($request, null, null, 'deleteCitation', null,
                        ['assocId' => $assocId, 'citationId' => $rowId]),
                    'grid.action.delete', null, 'delete',
                    __('submission.citations.editor.citationlist.deleteCitationConfirmation')
                ),
                GRID_ACTION_POSITION_ROW_LEFT
            );
        }
    }

    /**
     * @see GridRow::getCellActions()
     * @param Request $request
     * @param GridColumn $column
     * @param int $position
     * @return array
     */
    public function getCellActions($request, $column, $position = GRID_ACTION_POSITION_DEFAULT): array {
        $cellActions = [];
        if ($position == GRID_ACTION_POSITION_DEFAULT) {
            // Is this a new row or an existing row?
            $rowId = $this->getId();
            if (!empty($rowId) && is_numeric($rowId)) {
                $citation = $this->getData();
                assert($citation instanceof Citation);

                // We should never present citations to the user that have
                // not been checked already.
                if ($citation->getCitationState() < CITATION_PARSED) fatalError('Invalid citation!');

                // Instantiate the cell action.
                $router = $request->getRouter();
                $cellActions = [
                    new LegacyLinkAction(
                        'editCitation',
                        LINK_ACTION_MODE_AJAX,
                        LINK_ACTION_TYPE_GET,
                        $router->url($request, null, null, 'editCitation', null,
                                ['assocId' => $this->getAssocId(), 'citationId' => $rowId]),
                        'submission.citations.editor.clickToEdit',
                        null, null, null,
                        'citationEditorDetailCanvas'
                    )
                ];
            }
        }
        return $cellActions;
    }
}

?>
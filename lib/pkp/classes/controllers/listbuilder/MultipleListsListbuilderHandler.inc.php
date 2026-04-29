<?php
declare(strict_types=1);

/**
 * @file classes/controllers/listbuilder/MultipleListsListbuilderHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MultipleListsListbuilderHandler
 * @ingroup controllers_listbuilder
 *
 * @brief Class defining basic operations for handling multiple lists listbuilder UI elements
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
import('lib.pkp.classes.controllers.listbuilder.ListbuilderList');

define_exposed('LISTBUILDER_SOURCE_TYPE_NONE', 3);

class MultipleListsListbuilderHandler extends ListbuilderHandler {

    /** @var array Set of ListbuilderList objects that this listbuilder will handle **/
    protected array $_lists = [];

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function MultipleListsListbuilderHandler() {
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
     * @see ListbuilderHandler::getTemplate()
     */
    public function getTemplate() {
        if ($this->_template === null) {
            $this->setTemplate('controllers/listbuilder/multipleListsListbuilder.tpl');
        }

        return $this->_template;
    }

    /**
     * Get an array with all listbuilder lists.
     * @return array of ListbuilderList objects.
     */
    public function getLists(): array {
        return $this->_lists;
    }


    //
    // Protected methods.
    //

    /**
     * Add a list to listbuilder.
     * @param ListbuilderList $list
     */
    public function addList($list) {
        if (!($list instanceof ListbuilderList)) {
            fatalError('Invalid ListbuilderList object passed to addList.');
        }

        $currentLists = $this->getLists();
        $currentLists[$list->getId()] = $list;
        $this->_setLists($currentLists);
    }

    /**
     * @see GridHandler::loadData($request, $filter)
     * You should not extend or override this method.
     * All the data loading for this component is done
     * using ListbuilderList objects.
     * [WIZDAM] Removed reference on $request
     */
    public function loadData($request, $filter) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Give a chance to subclasses set data
        // on their lists.
        $this->setListsData($request, $filter);

        $data = [];
        $lists = $this->getLists();

        foreach ($lists as $list) {
            $data[$list->getId()] = $list->getData();
        }

        return $data;
    }

    /**
     * @see ListbuilderHandler::initialize()
     * [WIZDAM] Removed reference on $request
     */
    public function initialize($request, $args = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Basic configuration.
        // Currently this component only works with
        // these configurations, but, if needed, it's
        // easy to adapt this class to work with the other
        // listbuilders configuration.
        parent::initialize($request, false);
        $this->setSourceType(LISTBUILDER_SOURCE_TYPE_NONE);
        $this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
    }

    /**
     * @see GridHandler::initFeatures()
     * [WIZDAM] Removed reference on $request
     */
    public function initFeatures($request, $args) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Multiple lists listbuilder always have orderable rows.
        // We don't have any other requirement for it.
        import('lib.pkp.classes.controllers.grid.feature.OrderMultipleListsItemsFeature');
        return [new OrderMultipleListsItemsFeature()];
    }

    /**
     * @see ListbuilderHandler::getRowInstance()
     * [WIZDAM] Removed reference return
     */
    protected function getRowInstance() {
        $row = parent::getRowInstance();

        // Currently we can't/don't need to delete a row inside multiple
        // lists listbuilder. If we need, we have to adapt this class
        // and its js handler.
        $row->setHasDeleteItemLink(false);
        return $row;
    }

    /**
     * @see GridHandler::_renderGridBodyPartsInternally()
     * [WIZDAM] Removed reference on $request
     */
    protected function _renderGridBodyPartsInternally($request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Render the rows.
        $listsRows = [];
        $gridData = $this->getGridDataElements($request);
        if (is_array($gridData)) {
            foreach ($gridData as $listId => $elements) {
                $listsRows[$listId] = $this->_renderRowsInternally($request, $elements);
            }
        }

        $templateMgr = TemplateManager::getManager($request);
        // [WIZDAM] Use assign instead of assign_by_ref for objects
        $templateMgr->assign('grid', $this);
        $templateMgr->assign('listsRows', $listsRows);

        // In listbuilders we don't use the grid body.
        return false;
    }


    //
    // Protected template methods.
    //
    
    /**
     * Implement to set data on each list. This
     * will be used by the loadData method to retrieve
     * the listbuilder data.
     * @param PKPRequest $request
     * @param mixed $filter
     */
    protected function setListsData($request, $filter) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        fatalError('ABSTRACT METHOD');
    }


    //
    // Publicly (remotely) available listbuilder functions
    //

    /**
     * Fetch the listbuilder.
     * @param array $args
     * @param PKPRequest $request
     */
    public function fetch($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager($request);
        // [WIZDAM] Use assign instead of assign_by_ref
        $templateMgr->assign('lists', $this->getLists());

        return parent::fetch($args, $request);
    }


    //
    // Private helper methods.
    //

    /**
     * Set the array with all listbuilder lists.
     * @param array $lists Array of ListbuilderList objects.
     */
    private function _setLists(array $lists) {
        $this->_lists = $lists;
    }
}

?>
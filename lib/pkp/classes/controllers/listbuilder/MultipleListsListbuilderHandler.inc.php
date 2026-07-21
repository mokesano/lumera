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
 * @brief Class defining basic operations for handling multiple lists listbuilder UI elements.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');
import('lib.pkp.classes.controllers.listbuilder.ListbuilderList');

define('LISTBUILDER_SOURCE_TYPE_NONE', 3);

class MultipleListsListbuilderHandler extends ListbuilderHandler {

    /** @var array Set of ListbuilderList objects that this listbuilder will handle **/
    protected $_lists = [];

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
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        $this->__construct();
    }


    //
    // Getters and Setters
    //
    
    /**
     * Get template
     * @see ListbuilderHandler::getTemplate()
     * @return string
     */
    public function getTemplate(): string {
        if ($this->template === null) {
            $this->setTemplate('controllers/listbuilder/multipleListsListbuilder.tpl');
        }
        return (string) $this->template;
    }

    /**
     * Get an array with all listbuilder lists.
     * @return array of ListbuilderList objects.
     */
    public function getLists(): array {
        return is_array($this->_lists) ? $this->_lists : [];
    }


    //
    // Protected methods.
    //

    /**
     * Add a list to listbuilder.
     * @param mixed $list
     */
    public function addList($list): void {
        if (!$list instanceof ListbuilderList) {
            throw new \InvalidArgumentException('Invalid ListbuilderList object passed to addList.');
        }

        $currentLists = $this->getLists();
        $currentLists[(string) $list->getId()] = $list;
        $this->_setLists($currentLists);
    }

    /**
     * @see GridHandler::loadData($request, $filter)
     * You should not extend or override this method.
     * All the data loading for this component is done
     * using ListbuilderList objects.
     * @param mixed $request
     * @param mixed $filter
     * @return array
     */
    public function loadData($request, $filter): array {
        if (!$request) {
            $app = Application::get();
            $request = $app ? $app->getRequest() : null;
        }

        $this->setListsData($request, $filter);

        $data = [];
        $lists = $this->getLists();

        if (is_array($lists)) {
            foreach ($lists as $list) {
                $listId = (string) $list->getId();
                $listData = $list->getData();
                $data[$listId] = is_array($listData) ? $listData : [];
            }
        }

        return $data;
    }

    /**
     * Initialize
     * @see ListbuilderHandler::initialize()
     * @param mixed $request
     * @param bool $addItemLink
     */
    public function initialize($request, $addItemLink = true): void {
        if (!$request) {
            $app = Application::get();
            $request = $app ? $app->getRequest() : null;
        }

        parent::initialize($request, false);
        $this->setSourceType(LISTBUILDER_SOURCE_TYPE_NONE);
        $this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
    }

    /**
     * Init features
     * @see GridHandler::initFeatures()
     * @param mixed $request
     * @param mixed $args
     * @return array
     */
    public function initFeatures($request, $args): array {
        if (!$request) {
            $app = Application::get();
            $request = $app ? $app->getRequest() : null;
        }

        import('lib.pkp.classes.controllers.grid.feature.OrderMultipleListsItemsFeature');
        return [new OrderMultipleListsItemsFeature()];
    }

    /**
     * Get row instance
     * @see ListbuilderHandler::getRowInstance()
     * @return ListbuilderGridRow
     */
    public function getRowInstance() {
        $row = parent::getRowInstance();

        if ($row) {
            $row->setHasDeleteItemLink(false);
        }
        return $row;
    }

    /**
     * Helper render grid body parts internally
     * @see GridHandler::_renderGridBodyPartsInternally()
     * @param mixed $request
     * @return array
     */
    public function _renderGridBodyPartsInternally($request): array {
        if (!$request) {
            $app = Application::get();
            $request = $app ? $app->getRequest() : null;
        }

        $listsRows = [];
        $gridData = $this->getGridDataElements($request);
        
        if (is_array($gridData)) {
            foreach ($gridData as $listId => $elements) {
                $safeListId = (string) $listId;
                if (is_array($elements)) {
                    $listsRows[$safeListId] = $this->_renderRowsInternally($request, $elements);
                } else {
                    $listsRows[$safeListId] = [];
                }
            }
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('grid', $this);
        $templateMgr->assign('listsRows', $listsRows);

        return [];
    }


    //
    // Protected template methods.
    //
    
    /**
     * Implement to set data on each list. This
     * will be used by the loadData method to retrieve
     * the listbuilder data.
     * @param mixed $request
     * @param mixed $filter
     */
    protected function setListsData($request, $filter): void {
        if (!$request) {
            $app = Application::get();
            $request = $app ? $app->getRequest() : null;
        }

        throw new \BadMethodCallException('Subclasses of MultipleListsListbuilderHandler must implement setListsData().');
    }


    //
    // Publicly (remotely) available listbuilder functions
    //

    /**
     * Fetch the listbuilder.
     * @param array $args
     * @param mixed $request
     */
    public function fetch($args, $request) {
        if (!$request) {
            $app = Application::get();
            $request = $app ? $app->getRequest() : null;
        }

        $templateMgr = TemplateManager::getManager($request);
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
    private function _setLists(array $lists): void {
        $this->_lists = $lists;
    }

}
?>
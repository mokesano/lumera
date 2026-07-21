<?php
declare(strict_types=1);

/**
 * @file classes/controllers/listbuilder/ListbuilderHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ListbuilderHandler
 * @ingroup controllers_listbuilder
 *
 * @brief Class defining basic operations for handling Listbuilder UI elements.
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.listbuilder.ListbuilderGridRow');
import('lib.pkp.classes.controllers.listbuilder.ListbuilderGridColumn');
import('lib.pkp.classes.controllers.listbuilder.MultilingualListbuilderGridColumn');

/* Listbuilder source types: text-based, pulldown, ... */
define('LISTBUILDER_SOURCE_TYPE_TEXT', 0);
define('LISTBUILDER_SOURCE_TYPE_SELECT', 1);

/* Listbuilder save types */
define('LISTBUILDER_SAVE_TYPE_EXTERNAL', 0); // Outside the listbuilder handler
define('LISTBUILDER_SAVE_TYPE_INTERNAL', 1); // Using ListbuilderHandler::save

/* String to identify optgroup in the returning options data. */
define('LISTBUILDER_OPTGROUP_LABEL', 'optGroupLabel');

class ListbuilderHandler extends GridHandler {

    /** @var int Definition of the type of source LISTBUILDER_SOURCE_TYPE_... **/
    protected $_sourceType = 0;

    /** @var int Constant indicating the save approach for the LB LISTBUILDER_SAVE_TYPE_... **/
    protected $_saveType = LISTBUILDER_SAVE_TYPE_INTERNAL;

    /** @var string|null Field for LISTBUILDER_SAVE_TYPE_EXTERNAL naming the field used to send the saved contents of the LB */
    protected $_saveFieldName = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ListbuilderHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        $this->__construct();
    }

    /**
     * @see GridHandler::initialize
     * @param mixed $request
     * @param bool $addItemLink
     */
    public function initialize($request, $addItemLink = true) {
        parent::initialize($request);

        if ($addItemLink) {
            import('lib.pkp.classes.linkAction.request.NullAction');
            $this->addAction(
                new LinkAction(
                    'addItem',
                    new NullAction(),
                    __('grid.action.addItem'),
                    'add_item'
                )
            );
        }
    }

    //
    // Getters and Setters
    //
    /**
     * Get the listbuilder template.
     * @return string
     */
    public function getTemplate(): string {
        if ($this->template === null) {
            $this->setTemplate('controllers/listbuilder/listbuilder.tpl');
        }
        return (string) $this->template;
    }

    /**
     * Set the type of source (Free text input, select from list, autocomplete)
     * @param mixed $sourceType LISTBUILDER_SOURCE_TYPE_...
     */
    public function setSourceType($sourceType) {
        $this->_sourceType = (int) $sourceType;
    }

    /**
     * Get the type of source (Free text input, select from list, autocomplete)
     * @return int LISTBUILDER_SOURCE_TYPE_...
     */
    public function getSourceType() {
        return (int) $this->_sourceType;
    }

    /**
     * Set the save type (using this handler or another external one)
     * @param mixed $saveType LISTBUILDER_SAVE_TYPE_...
     */
    public function setSaveType($saveType) {
        $this->_saveType = (int) $saveType;
    }

    /**
     * Get the save type (using this handler or another external one)
     * @return int LISTBUILDER_SAVE_TYPE_...
     */
    public function getSaveType() {
        return (int) $this->_saveType;
    }

    /**
     * Set the save field name for LISTBUILDER_SAVE_TYPE_EXTERNAL
     * @param mixed $fieldName
     */
    public function setSaveFieldName($fieldName) {
        $this->_saveFieldName = (string) $fieldName;
    }

    /**
     * Get the save field name for LISTBUILDER_SAVE_TYPE_EXTERNAL
     * @return string
     */
    public function getSaveFieldName() {
        if ($this->_saveFieldName === null) {
            fatalError('Save field name has not been set for this Listbuilder.');
        }
        return (string) $this->_saveFieldName;
    }

    /**
     * Get the new row ID from the request.
     * @param mixed $request
     * @return int
     */
    public function getNewRowId($request) {
        return (int) $request->getUserVar('newRowId');
    }

    /**
     * Delete an entry.
     * @param mixed $request
     * @param mixed $rowId ID of row to modify
     * @return boolean
     */
    public function deleteEntry($request, $rowId) {
        fatalError('ABSTRACT METHOD');
    }

    /**
     * Persist an update to an entry.
     * @param mixed $request
     * @param mixed $rowId ID of row to modify
     * @param mixed $newRowId ID of the new entry
     * @return boolean
     */
    public function updateEntry($request, $rowId, $newRowId) {
        if ($this->deleteEntry($request, $rowId) === false) {
            return false;
        }
        return $this->insertEntry($request, $newRowId);
    }

    /**
     * Persist a new entry insert.
     * @param mixed $request
     * @param mixed $newRowId ID of row to modify
     * @return mixed
     */
    public function insertEntry($request, $newRowId) {
        fatalError('ABSTRACT METHOD');
    }

    /**
     * Fetch the options for a LISTBUILDER_SOURCE_TYPE_SELECT LB
     * @param mixed $request
     * @return array
     */
    public function getOptions($request) {
        fatalError('ABSTRACT METHOD');
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
        return $this->fetchGrid($args, $request);
    }

    /**
     * Unpack data to save using an external handler.
     * @param mixed $request
     * @param mixed $data (the json encoded data from the listbuilder itself)
     * @param callable|null $deletionCallback
     * @param callable|null $insertionCallback
     * @param callable|null $updateCallback
     */
    public function unpack($request, $data, $deletionCallback = null, $insertionCallback = null, $updateCallback = null) {
        if (!$deletionCallback) {
            $deletionCallback = [$this, 'deleteEntry'];
        }
        if (!$insertionCallback) {
            $insertionCallback = [$this, 'insertEntry'];
        }
        if (!$updateCallback) {
            $updateCallback = [$this, 'updateEntry'];
        }

        import('lib.pkp.classes.core.JSONManager');
        $jsonManager = new JSONManager();
        $jsonString = (string) $data;
        $decodedData = $jsonManager->decode($jsonString);

        if (!is_object($decodedData) && !is_array($decodedData)) {
            return;
        }

        if (isset($decodedData->deletions)) {
            $deletions = explode(' ', trim((string) $decodedData->deletions));
            foreach ($deletions as $rowId) {
                $rowId = trim((string) $rowId);
                if ($rowId === '') {
                    continue;
                }
                call_user_func($deletionCallback, $request, $rowId);
            }
        }

        if (isset($decodedData->changes) && is_iterable($decodedData->changes)) {
            foreach ($decodedData->changes as $entry) {
                $rowId = isset($entry->rowId) ? (string) $entry->rowId : null;
                
                $changes = [];
                foreach ($entry as $key => $value) {
                    if ((string) $key === 'rowId') {
                        continue;
                    }

                    if (!preg_match('/^newRowId\[([a-zA-Z0-9_]+)\](\[([a-z][a-z]_[A-Z][A-Z])\])?$/', (string) $key, $matches)) {
                         continue;
                    }

                    $column = (string) $matches[1];
                    $locale = isset($matches[3]) ? (string) $matches[3] : null;

                    $safeValue = (string) $value;

                    if ($locale) {
                        $changes[$column][$locale] = $safeValue;
                    } else {
                        $changes[$column] = $safeValue;
                    }
                }

                if ($rowId === null || $rowId === '') {
                    call_user_func($insertionCallback, $request, $changes);
                } else {
                    call_user_func($updateCallback, $request, $rowId, $changes);
                }
            }
        }
    }

    /**
     * Save the listbuilder using the internal handler.
     * @param array $args
     * @param mixed $request
     */
    public function save($args, $request) {
        $jsonString = (string) $request->getUserVar('data');
        
        $this->unpack(
            $request, 
            $jsonString,
            [$this, 'deleteEntry'],
            [$this, 'insertEntry'],
            [$this, 'updateEntry']
        );
        
        return new JSONMessage(true);
    }

    /**
     * Load the set of options for a select list type listbuilder.
     * @param array $args
     * @param mixed $request
     */
    public function fetchOptions($args, $request) {
        $options = $this->getOptions($request);
        $json = new JSONMessage(true, $options);
        header('Content-Type: application/json');
        return $json->getString();
    }

    //
    // Overridden methods from GridHandler
    //
    /**
     * Get row instance
     * @see GridHandler::getRowInstance()
     * @return ListbuilderGridRow
     */
    public function getRowInstance() {
        return new ListbuilderGridRow();
    }

}
?>
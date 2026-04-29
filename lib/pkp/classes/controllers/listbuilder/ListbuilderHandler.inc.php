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
 * @brief Class defining basic operations for handling Listbuilder UI elements
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.listbuilder.ListbuilderGridRow');
import('lib.pkp.classes.controllers.listbuilder.ListbuilderGridColumn');
import('lib.pkp.classes.controllers.listbuilder.MultilingualListbuilderGridColumn');

/* Listbuilder source types: text-based, pulldown, ... */
define_exposed('LISTBUILDER_SOURCE_TYPE_TEXT', 0);
define_exposed('LISTBUILDER_SOURCE_TYPE_SELECT', 1);

/* Listbuilder save types */
define('LISTBUILDER_SAVE_TYPE_EXTERNAL', 0); // Outside the listbuilder handler
define('LISTBUILDER_SAVE_TYPE_INTERNAL', 1); // Using ListbuilderHandler::save

/* String to identify optgroup in the returning options data. */
define_exposed('LISTBUILDER_OPTGROUP_LABEL', 'optGroupLabel');

class ListbuilderHandler extends GridHandler {
    /** @var int Definition of the type of source LISTBUILDER_SOURCE_TYPE_... **/
    protected int $_sourceType;

    /** @var int Constant indicating the save approach for the LB LISTBUILDER_SAVE_TYPE_... **/
    protected int $_saveType = LISTBUILDER_SAVE_TYPE_INTERNAL;

    /** @var string|null Field for LISTBUILDER_SAVE_TYPE_EXTERNAL naming the field used to send the saved contents of the LB */
    protected ?string $_saveFieldName = null;

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
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * @see GridHandler::initialize
     * @param PKPRequest $request
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
    public function getTemplate() {
        if ($this->_template === null) {
            $this->setTemplate('controllers/listbuilder/listbuilder.tpl');
        }
        return $this->_template;
    }

    /**
     * Set the type of source (Free text input, select from list, autocomplete)
     * @param int $sourceType LISTBUILDER_SOURCE_TYPE_...
     */
    public function setSourceType(int $sourceType): void {
        $this->_sourceType = $sourceType;
    }

    /**
     * Get the type of source (Free text input, select from list, autocomplete)
     * @return int LISTBUILDER_SOURCE_TYPE_...
     */
    public function getSourceType(): int {
        return $this->_sourceType;
    }

    /**
     * Set the save type (using this handler or another external one)
     * @param int $saveType LISTBUILDER_SAVE_TYPE_...
     */
    public function setSaveType(int $saveType): void {
        $this->_saveType = $saveType;
    }

    /**
     * Get the save type (using this handler or another external one)
     * @return int LISTBUILDER_SAVE_TYPE_...
     */
    public function getSaveType(): int {
        return $this->_saveType;
    }

    /**
     * Set the save field name for LISTBUILDER_SAVE_TYPE_EXTERNAL
     * @param string $fieldName
     */
    public function setSaveFieldName(string $fieldName): void {
        $this->_saveFieldName = $fieldName;
    }

    /**
     * Get the save field name for LISTBUILDER_SAVE_TYPE_EXTERNAL
     * @return string
     */
    public function getSaveFieldName(): string {
        // [WIZDAM] Replaced assert with strict check
        if ($this->_saveFieldName === null) {
            fatalError('Save field name has not been set for this Listbuilder.');
        }
        return $this->_saveFieldName;
    }

    /**
     * Get the new row ID from the request.
     * @param PKPRequest $request
     * @return int
     */
    public function getNewRowId($request): int {
        return (int) $request->getUserVar('newRowId');
    }

    /**
     * Delete an entry.
     * @param PKPRequest $request
     * @param mixed $rowId ID of row to modify
     * @return boolean
     */
    public function deleteEntry($request, $rowId) {
        fatalError('ABSTRACT METHOD');
    }

    /**
     * Persist an update to an entry.
     * @param PKPRequest $request
     * @param mixed $rowId ID of row to modify
     * @param mixed $newRowId ID of the new entry
     * @return boolean
     */
    public function updateEntry($request, $rowId, $newRowId) {
        // This may well be overridden by a subclass to modify
        // an existing entry, e.g. to maintain referential integrity.
        // If not, we can simply delete and insert.
        if (!$this->deleteEntry($request, $rowId)) return false;
        return $this->insertEntry($request, $newRowId);
    }

    /**
     * Persist a new entry insert.
     * @param PKPRequest $request
     * @param mixed $newRowId ID of row to modify
     * @return mixed
     */
    public function insertEntry($request, $newRowId) {
        fatalError('ABSTRACT METHOD');
    }

    /**
     * Fetch the options for a LISTBUILDER_SOURCE_TYPE_SELECT LB
     * @param PKPRequest $request
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
     * @param PKPRequest $request
     */
    public function fetch($args, $request) {
        return $this->fetchGrid($args, $request);
    }

    /**
     * Unpack data to save using an external handler.
     * @param PKPRequest $request
     * @param string $data (the json encoded data from the listbuilder itself)
     * @param callable|null $deletionCallback
     * @param callable|null $insertionCallback
     * @param callable|null $updateCallback
     */
    public function unpack($request, $data, $deletionCallback = null, $insertionCallback = null, $updateCallback = null) {
        // Set some defaults using modern array syntax
        if (!$deletionCallback) $deletionCallback = [$this, 'deleteEntry'];
        if (!$insertionCallback) $insertionCallback = [$this, 'insertEntry'];
        if (!$updateCallback) $updateCallback = [$this, 'updateEntry'];

        import('lib.pkp.classes.core.JSONManager');
        $jsonManager = new JSONManager();
        
        // [WIZDAM] Critical Fix: Treat data as string for decoding.
        // We do NOT assume $data is safe yet, but we must decode it to process structure.
        $decodedData = $jsonManager->decode($data);

        if (!$decodedData) {
            // Decoding failed, possibly empty or invalid JSON
            return;
        }

        // Handle deletions
        if (isset($decodedData->deletions)) {
            $deletions = explode(' ', trim($decodedData->deletions));
            foreach ($deletions as $rowId) {
                if (empty($rowId)) continue;
                call_user_func($deletionCallback, $request, $rowId);
            }
        }

        // Handle changes and insertions
        if (isset($decodedData->changes) && is_iterable($decodedData->changes)) {
            foreach ($decodedData->changes as $entry) {
                // Get the row ID, if any, from submitted data
                $rowId = isset($entry->rowId) ? $entry->rowId : null;
                // Clean up entry object so it only contains data fields
                if (isset($entry->rowId)) unset($entry->rowId);

                // $entry should now contain only submitted modified or new rows.
                $changes = [];
                foreach ($entry as $key => $value) {
                    // Match the column name and localization data.
                    // Strict regex to ensure key safety.
                    if (!preg_match('/^newRowId\[([a-zA-Z0-9_]+)\](\[([a-z][a-z]_[A-Z][A-Z])\])?$/', $key, $matches)) {
                         // Skip invalid keys for security
                         continue;
                    }

                    // Get the column name
                    $column = $matches[1];

                    // If this is a multilingual input, fetch $locale; otherwise null
                    $locale = isset($matches[3]) ? $matches[3] : null;

                    // [WIZDAM] Sanitization Note:
                    // Values ($value) are passed raw here. 
                    // The 'insertEntry' and 'updateEntry' methods in subclasses 
                    // (or the DAO they call) are responsible for DB escaping/sanitization.
                    if ($locale) {
                        $changes[$column][$locale] = $value;
                    } else {
                        $changes[$column] = $value;
                    }
                }

                if ($rowId === null) {
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
     * @param PKPRequest $request
     */
    public function save($args, $request) {
        // The ListbuilderHandler will post a list of changed
        // data in the "data" post var.
        
        // [WIZDAM FIX]
        // 1. We retrieve the raw JSON string. 
        // 2. We do NOT cast to (array) or htmlspecialchars it here, 
        //    because that breaks the JSON structure for unpacking.
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
     * @param PKPRequest $request
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
     * @see GridHandler::getRowInstance()
     * @return ListbuilderGridRow
     */
    protected function getRowInstance() {
        return new ListbuilderGridRow();
    }
}

?>
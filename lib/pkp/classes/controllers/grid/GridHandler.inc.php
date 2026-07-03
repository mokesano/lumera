<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/GridHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GridHandler
 * @ingroup classes_controllers_grid
 *
 * @brief Class defining basic operations for handling HTML grids.
 */

// Import the base Handler.
import('lib.pkp.classes.handler.PKPHandler');

// Import action class.
import('lib.pkp.classes.linkAction.LinkAction');
import('lib.pkp.classes.linkAction.LegacyLinkAction');

// Import grid classes.
import('lib.pkp.classes.controllers.grid.GridColumn');
import('lib.pkp.classes.controllers.grid.GridRow');

// Import JSON class for use with all AJAX requests.
import('lib.pkp.classes.core.JSONMessage');

// Grid specific action positions.
define('GRID_ACTION_POSITION_DEFAULT', 'default');
define('GRID_ACTION_POSITION_ABOVE', 'above');
define('GRID_ACTION_POSITION_LASTCOL', 'lastcol');
define('GRID_ACTION_POSITION_BELOW', 'below');

class GridHandler extends PKPHandler {

    /** 
     * @var string grid title locale key 
     * [WIZDAM] Renamed from $_title
     */
    public string $title = '';

    /** 
     * @var string empty row locale key 
     * [WIZDAM] Renamed from $_emptyRowText
     */
    public string $emptyRowText = 'grid.noItems';

    /** 
     * @var GridDataProvider|null 
     * [WIZDAM] Renamed from $_dataProvider
     */
    public ?GridDataProvider $dataProvider = null;

    /**
     * @var array Grid actions.
     * [WIZDAM] Renamed from $_actions
     */
    public array $actions = [GRID_ACTION_POSITION_DEFAULT => []];

    /** 
     * @var array The GridColumns of this grid. 
     * [WIZDAM] Renamed from $_columns
     */
    public array $columns = [];

    /** 
     * @var array|null The grid's data source. 
     * [WIZDAM] Renamed from $_data
     */
    public ?array $data = null;

    /** 
     * @var string|null The grid template. 
     * [WIZDAM] Renamed from $_template
     */
    public ?string $template = null;

    /** 
     * @var array|null The urls that will be used in JS handler. 
     * [WIZDAM] Renamed from $_urls
     */
    public ?array $urls = null;

    /** 
     * @var array The grid features. 
     * [WIZDAM] Renamed from $_features
     */
    public array $features = [];

    /** 
     * @var string|null Grid instructions 
     * [WIZDAM] Renamed from $_instructions
     */
    public ?string $instructions = null;

    /** 
     * @var string|null Grid footnote 
     * [WIZDAM] Renamed from $_footNote
     */
    public ?string $footNote = null;


    /**
     * Constructor.
     * @param GridDataProvider|null $dataProvider
     */
    public function __construct($dataProvider = null) {
        $this->dataProvider = $dataProvider;
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GridHandler($dataProvider = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($dataProvider);
    }

    //
    // Getters and Setters
    //
    
    /**
     * Get the data provider.
     * @return GridDataProvider|null
     */
    public function getDataProvider() {
        return $this->dataProvider;
    }

    /**
     * Get the grid request parameters.
     * @return array
     */
    public function getRequestArgs(): array {
        $dataProvider = $this->getDataProvider();
        $requestArgs = [];
        if (is_a($dataProvider, 'GridDataProvider')) {
            $requestArgs = $dataProvider->getRequestArgs();
        }
        return $requestArgs;
    }

    /**
     * Get a single grid request parameter.
     * @param string $key
     * @return mixed
     */
    public function getRequestArg($key) {
        $requestArgs = $this->getRequestArgs();
        assert(isset($requestArgs[$key]));
        return $requestArgs[$key];
    }

    /**
     * Get the grid title.
     * @return string locale key
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * Set the grid title.
     * @param string $title locale key
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Get the no items locale key
     * @return string
     */
    public function getEmptyRowText(): string {
        return $this->emptyRowText;
    }

    /**
     * Set the no items locale key
     * @param string $emptyRowText
     */
    public function setEmptyRowText($emptyRowText) {
        $this->emptyRowText = $emptyRowText;
    }

    /**
     * Get the grid instructions.
     * @return string|null locale key
     */
    public function getInstructions() {
        return $this->instructions;
    }

    /**
     * Set the grid instructions.
     * @param string $instructions locale key
     */
    public function setInstructions($instructions) {
        $this->instructions = $instructions;
    }

    /**
     * Get the grid foot note.
     * @return string|null locale key
     */
    public function getFootNote() {
        return $this->footNote;
    }

    /**
     * Set the grid foot note.
     * @param string $footNote locale key
     */
    public function setFootNote($footNote) {
        $this->footNote = $footNote;
    }

    /**
     * Get all actions for a given position within the grid.
     * @param string $position
     * @return array
     */
    public function getActions($position = GRID_ACTION_POSITION_ABOVE): array {
        if (!isset($this->actions[$position])) return [];
        return $this->actions[$position];
    }

    /**
     * Add an action.
     * @param string $position
     * @param LinkAction $action
     */
    public function addAction($action, $position = GRID_ACTION_POSITION_ABOVE) {
        if (!isset($this->actions[$position])) $this->actions[$position] = [];
        $this->actions[$position][$action->getId()] = $action;
    }

    /**
     * Get all columns.
     * @return array An array of GridColumn instances.
     */
    public function getColumns(): array {
        return $this->columns;
    }

    /**
     * Retrieve a single column by id.
     * @param string $columnId
     * @return GridColumn
     */
    public function getColumn($columnId) {
        assert(isset($this->columns[$columnId]));
        return $this->columns[$columnId];
    }

    /**
     * Get columns by flag.
     * @param string $flag
     * @return array
     */
    public function getColumnsByFlag($flag): array {
        $columns = [];
        foreach ($this->getColumns() as $column) {
            if ($column->hasFlag($flag)) {
                $columns[$column->getId()] = $column;
            }
        }
        return $columns;
    }

    /**
     * Get columns number.
     * @param string $flag
     * @return int
     */
    public function getColumnsCount($flag): int {
        $count = 0;
        foreach ($this->getColumns() as $column) {
            if (!$column->hasFlag($flag)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Checks whether a column exists.
     * @param string $columnId
     * @return bool
     */
    public function hasColumn($columnId): bool {
        return isset($this->columns[$columnId]);
    }

    /**
     * Add a column.
     * @param GridColumn $column
     */
    public function addColumn($column) {
        assert(is_a($column, 'GridColumn'));
        $this->columns[$column->getId()] = $column;
    }

    /**
     * Get the grid data.
     * @param PKPRequest $request
     * @return array
     */
    public function getGridDataElements($request) {
        // Try to load data if it has not yet been loaded.
        if ($this->data === null) {
            $filter = $this->getFilterSelectionData($request);
            $data = $this->loadData($request, $filter);

            if ($data === null) {
                // Initialize data to an empty array.
                $data = [];
            }

            $this->setGridDataElements($data);
        }

        return $this->data;
    }

    /**
     * Check whether the grid has rows.
     * @param PKPRequest $request
     * @return bool
     */
    public function hasGridDataElements($request): bool {
        $data = $this->getGridDataElements($request);
        assert(is_array($data));
        return (bool) count($data);
    }

    /**
     * Set the grid data.
     * @param mixed $data an array or ItemIterator with element data
     */
    public function setGridDataElements($data) {
        // FIXME: We go to arrays for all types of iterators because
        // iterators cannot be re-used, see #6498.
        if (is_array($data)) {
            $this->data = $data;
        } elseif (is_a($data, 'DAOResultFactory')) {
            $this->data = $data->toAssociativeArray();
        } elseif (is_a($data, 'ItemIterator')) {
            $this->data = $data->toArray();
        } else {
            assert(false);
        }
    }

    /**
     * Get the grid template.
     * @return string
     */
    public function getTemplate(): string {
        if ($this->template === null) {
            $this->setTemplate('controllers/grid/grid.tpl');
        }
        return $this->template;
    }

    /**
     * Set the grid template.
     * @param string $template
     */
    public function setTemplate($template) {
        $this->template = $template;
    }

    /**
     * Return all grid urls that will be used in JS handler.
     * @return array|null
     */
    public function getUrls() {
        return $this->urls;
    }

    /**
     * Define the urls that will be used in JS handler.
     * @param PKPRequest $request
     * @param array $extraUrls
     */
    public function setUrls($request, $extraUrls = []) {
        $router = $request->getRouter();
        $urls = [
            'fetchGridUrl' => $router->url($request, null, null, 'fetchGrid', null, $this->getRequestArgs()),
            'fetchRowUrl' => $router->url($request, null, null, 'fetchRow', null, $this->getRequestArgs())
        ];
        $this->urls = array_merge($urls, $extraUrls);
    }

    /**
     * Override this method to return true if you want
     * to use the grid within another component.
     * @return bool
     */
    public function getIsSubcomponent() {
        return false;
    }

    /**
     * Get all grid attached features.
     * @return array
     */
    public function getFeatures(): array {
        return $this->features;
    }

    /**
     * Get "publish data changed" event list.
     * @return array
     */
    public function getPublishChangeEvents() {
        return [];
    }

    //
    // Overridden methods from PKPHandler
    //
    
    /**
     * Validate the grid handler.
     * @see PKPHandler::validate()
     * [WIZDAM] Adapter: Mencegat parameter shifting dari PKPComponentRouter
     * Router lama mengirimkan ($request, $args), sedangkan PKPHandler meminta ($requiredContexts, $request).
     * @param array|null $requiredContexts
     * @param PKPRequest|null $request
     * @return bool
     */
    public function validate($requiredContexts = null, $request = null) {
        
        // Jika parameter pertama adalah objek PKPRequest, berarti ini adalah panggilan legacy dari Router
        if (is_object($requiredContexts) && is_a($requiredContexts, 'PKPRequest')) {
            $request = $requiredContexts; // Pindahkan request ke tempat yang semestinya
            $requiredContexts = null;     // Argumen ke-2 ($args) dari router diabaikan (dijadikan null)
        }

        // Teruskan pemanggilan ke base class (PKPHandler) dengan parameter yang sudah bersih dan sesuai
        parent::validate($requiredContexts, $request);
    }
    
    /**
     * Authorize the grid handler.
     * @see PKPHandler::authorize()
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        $dataProvider = $this->getDataProvider();
        $hasDataProvider = is_a($dataProvider, 'GridDataProvider');
        if ($hasDataProvider) {
            $this->addPolicy($dataProvider->getAuthorizationPolicy($request, $args, $roleAssignments));
        }

        $success = parent::authorize($request, $args, $roleAssignments);
        if ($hasDataProvider && $success === true) {
            $dataProvider->setAuthorizedContext($this->getAuthorizedContext());
        }
        return $success;
    }

    /**
     * Initialize the grid handler.
     * @see PKPHandler::initialize()
     * @param PKPRequest $request
     * @param array $args
     */
    public function initialize($request, $args = null) {
        parent::initialize($request, $args);

        // Load grid-specific translations
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_GRID, LOCALE_COMPONENT_APPLICATION_COMMON);

        $this->_addFeatures($this->initFeatures($request, $args));
        // Note: passing $this by reference to hooks is deprecated in strict PHP 8,
        // but hooks architecture often relies on it. 
        // For internal method calls, we pass $this.
        $this->callFeaturesHook('gridInitialize', ['grid' => $this]);
    }


    //
    // Public handler methods
    //
    
    /**
     * Render the entire grid controller.
     * @param array $args
     * @param PKPRequest $request
     * @return string the serialized grid JSON message
     */
    public function fetchGrid($args, $request) {
        $this->setUrls($request);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('grid', $this);

        $renderedFilter = $this->renderFilter($request);
        $templateMgr->assign('gridFilterForm', $renderedFilter);

        $this->setFirstDataColumn();
        $columns = $this->getColumns();
        $templateMgr->assign('columns', $columns);

        $this->doSpecificFetchGridActions($args, $request, $templateMgr);
        $templateMgr->assign('gridRequestArgs', $this->getRequestArgs());

        $this->callFeaturesHook('fetchGrid', ['grid' => $this, 'request' => $request]);
        $templateMgr->assign('features', $this->getFeatures());

        $json = new JSONMessage(true, $templateMgr->fetch($this->getTemplate()));
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Render a row.
     * @param array $args
     * @param PKPRequest $request
     * @return string
     */
    public function fetchRow($args, $request) {
        $row = $this->getRequestedRow($request, $args);

        $json = new JSONMessage(true);
        if ($row === null) {
            $json->setAdditionalAttributes(['elementNotFound' => (int)$args['rowId']]);
        } else {
            $this->setFirstDataColumn();
            $json->setContent($this->renderRowInternally($request, $row));
        }

        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Render a cell.
     * @param array $args
     * @param PKPRequest $request
     * @return string
     */
    public function fetchCell($args, $request) {
        if (!isset($args['columnId'])) fatalError('Missing column id!');
        if (!$this->hasColumn($args['columnId'])) fatalError('Invalid column id!');
        
        $this->setFirstDataColumn();
        $column = $this->getColumn($args['columnId']);

        $row = $this->getRequestedRow($request, $args);
        if ($row === null) fatalError('Row not found!');

        $json = new JSONMessage(true, $this->_renderCellInternally($request, $row, $column));
        header('Content-Type: application/json');
        return $json->getString();
    }


    //
    // Protected methods to be overridden/used by subclasses
    //
    
    /**
     * Get a new instance of a grid row.
     * @return GridRow
     */
    public function getRowInstance() {
        return new GridRow();
    }

    /**
     * Get the js handler.
     * @return string
     */
    public function getJSHandler() {
        return '$.pkp.controllers.grid.GridHandler';
    }

    /**
     * Create a data element from a request.
     * @param PKPRequest $request
     * @param int|null $elementId
     * @return object
     */
    public function getDataElementFromRequest($request, $elementId) {
        fatalError('Grid does not support data element creation!');
    }

    /**
     * Get the range info for the grid.
     * @see PKPHandler::getRangeInfo()
     * @param string $rangeName
     * @param mixed $contextData
     * @return DBResultRange
     */
    public static function getRangeInfo($rangeName, $contextData = null) {
        import('lib.pkp.classes.db.DBResultRange');
        return new DBResultRange(-1, -1);
    }

    /**
     * Tries to identify the data element.
     * @param PKPRequest $request
     * @param array $args
     * @return GridRow|null
     */
    public function getRequestedRow($request, $args) {
        $isModified = isset($args['modify']);
        $elementId = null;
        $dataElement = null;

        if (isset($args['rowId']) && !$isModified) {
            $elementId = $args['rowId'];
            $dataElement = $this->getRowDataElement($request, $elementId);
            
            if ($dataElement === null) {
                return null;
            }
        } elseif ($isModified) {
            $dataElement = $this->getRowDataElement($request, null);
            if (isset($args['rowId'])) {
                $elementId = $args['rowId'];
            }
        }

        return $this->_getInitializedRowInstance($request, $elementId, $dataElement, $isModified);
    }

    /**
     * Retrieve a single data element.
     * @param PKPRequest $request
     * @param mixed $rowId
     * @return mixed
     */
    public function getRowDataElement($request, $rowId) {
        $elements = $this->getGridDataElements($request);
        assert(is_array($elements));
        
        if ($rowId !== null && !isset($elements[$rowId])) return null;
        if ($rowId !== null) return $elements[$rowId];
        
        return null; 
    }

    /**
     * Implement this method to load data into the grid.
     * @param PKPRequest $request
     * @param array $filter
     * @return mixed
     */
    public function loadData($request, $filter) {
        $gridData = null;
        $dataProvider = $this->getDataProvider();
        if (is_a($dataProvider, 'GridDataProvider')) {
            $gridData = $dataProvider->loadData();
        }
        return $gridData;
    }

    /**
     * Returns a Form object or the path name of a filter template.
     * @return mixed
     */
    public function getFilterForm() {
        return null;
    }

    /**
     * Extract the user's filter selection.
     * @param PKPRequest $request
     * @return array|null
     */
    public function getFilterSelectionData($request) {
        return null;
    }

    /**
     * Render the filter.
     * @param PKPRequest $request
     * @param array $filterData
     * @return string
     */
    public function renderFilter($request, $filterData = []) {
        $form = $this->getFilterForm();
        assert($form === null || is_a($form, 'Form') || is_string($form));

        $renderedForm = '';
        if (is_a($form, 'Form')) {
            $clientSubmit = (bool) $request->getUserVar('clientSubmit');
            if ($clientSubmit) {
                $form->readInputData();
                $form->validate();
            }
            $form->initData($filterData, $request);
            $renderedForm = $form->fetch($request);
        } elseif (is_string($form)) {
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('filterData', $filterData);
            $filterSelectionData = $this->getFilterSelectionData($request);
            $templateMgr->assign('filterSelectionData', $filterSelectionData);
            $renderedForm = $templateMgr->fetch($form);
        }

        return $renderedForm;
    }

    /**
     * Returns a common 'no matches' result.
     * @return string Serialized JSON object
     */
    public function noAutocompleteResults() {
        $returner = [];
        $returner[] = ['label' => __('common.noMatches'), 'value' => ''];
        $json = new JSONMessage(true, $returner);
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Save all data elements new sequence.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveSequence($args, $request) {
        $this->callFeaturesHook('saveSequence', ['request' => $request, 'grid' => $this]);
        return DAO::getDataChangedEvent();
    }

    /**
     * Get the row data element sequence value.
     * @param mixed $gridDataElement
     * @return int
     */
    public function getRowDataElementSequence($gridDataElement) {
        assert(false);
    }

    /**
     * Operation to save the row data element new sequence.
     * @param PKPRequest $request
     * @param mixed $rowId
     * @param mixed $gridDataElement
     * @param int $newSequence
     */
    public function saveRowDataElementSequence($request, $rowId, $gridDataElement, $newSequence) {
        assert(false);
    }

    /**
     * Override this method if your subclass needs to perform different actions.
     * @param array $args
     * @param PKPRequest $request
     * @param TemplateManager $templateMgr
     */
    public function doSpecificFetchGridActions($args, $request, $templateMgr) {
        $this->_fixColumnWidths();

        $gridBodyParts = $this->_renderGridBodyPartsInternally($request);
        $templateMgr->assign('gridBodyParts', $gridBodyParts);
    }

    /**
     * Define the first column that will contain grid data.
     */
    public function setFirstDataColumn() {
        $columns = $this->getColumns();
        if (!empty($columns)) {
            $firstColumn = reset($columns);
            $firstColumn->addFlag('firstColumn', true);
        }
    }

    /**
     * Override to init grid features.
     * @param PKPRequest $request
     * @param array $args
     * @return array
     */
    public function initFeatures($request, $args) {
        return [];
    }

    /**
     * Call the passed hook in all attached features.
     * @param string $hookName
     * @param array $args
     */
    public function callFeaturesHook($hookName, $args) {
        $features = $this->getFeatures();
        if (is_array($features)) {
            foreach ($features as $feature) {
                if (is_callable([$feature, $hookName])) {
                    $feature->$hookName($args);
                } else {
                    assert(false);
                }
            }
        }
    }

    /**
     * Method that renders a single row.
     * @param PKPRequest $request
     * @param GridRow $row
     * @return string the row HTML
     */
    public function renderRowInternally($request, $row) {
        $renderedCells = [];
        $columns = $this->getColumns();
        foreach ($columns as $column) {
            assert(is_a($column, 'GridColumn'));
            $renderedCells[] = $this->_renderCellInternally($request, $row, $column);
        }

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('grid', $this);
        $templateMgr->assign('columns', $columns);
        $templateMgr->assign('cells', $renderedCells);
        $templateMgr->assign('row', $row);
        return $templateMgr->fetch($row->getTemplate());
    }


    //
    // Private helper methods
    //
    
    /**
     * Instantiate a new row.
     * @param PKPRequest $request
     * @param mixed $elementId
     * @param mixed $element
     * @param bool $isModified
     * @return GridRow
     */
    public function _getInitializedRowInstance($request, $elementId, $element, $isModified = false) {
        $row = $this->getRowInstance();
        $row->setGridId($this->getId());
        $row->setId($elementId);
        $row->setData($element);
        $row->setRequestArgs($this->getRequestArgs());
        $row->setIsModified($isModified);

        $row->initialize($request);
        $this->callFeaturesHook('getInitializedRowInstance', ['row' => $row]);
        return $row;
    }

    /**
     * Method that renders tbodys.
     * @param PKPRequest $request
     * @return array
     */
    public function _renderGridBodyPartsInternally($request) {
        $elements = $this->getGridDataElements($request);
        $renderedRows = $this->_renderRowsInternally($request, $elements);

        $templateMgr = TemplateManager::getManager();
        $gridBodyParts = [];
        if (count($renderedRows) > 0) {
            $templateMgr->assign('grid', $this);
            $templateMgr->assign('rows', $renderedRows);
            $gridBodyParts[] = $templateMgr->fetch('controllers/grid/gridBodyPart.tpl');
        }
        return $gridBodyParts;
    }

    /**
     * Cycle through the data and get generate the row HTML.
     * @param PKPRequest $request
     * @param array $elements
     * @return array
     */
    public function _renderRowsInternally($request, $elements) {
        $renderedRows = [];
        foreach ($elements as $elementId => $element) {
            $row = $this->_getInitializedRowInstance($request, $elementId, $element);
            $renderedRows[] = $this->renderRowInternally($request, $row);
        }
        return $renderedRows;
    }

    /**
     * Method that renders a cell.
     * @param PKPRequest $request
     * @param GridRow $row
     * @param GridColumn $column
     * @return string
     */
    public function _renderCellInternally($request, $row, $column) {
        $element = $row->getData();
        if ($element === null && $row->getIsModified()) {
            import('lib.pkp.classes.controllers.grid.GridCellProvider');
            $cellProvider = new GridCellProvider();
            return $cellProvider->render($request, $row, $column);
        }

        $cellProvider = $row->getCellProvider();
        if (!is_a($cellProvider, 'GridCellProvider')) {
            unset($cellProvider);
            $cellProvider = $column->getCellProvider();
        }

        return $cellProvider->render($request, $row, $column);
    }

    /**
     * Method that grabs all the existing columns and makes sure the column widths add to exactly 100
     */
    public function _fixColumnWidths() {
        $columns = $this->getColumns();
        $width = 0;
        $noSpecifiedWidthCount = 0;
        
        foreach ($columns as $column) {
            if ($column->hasFlag('width')) {
                $width += $column->getFlag('width');
            } else {
                $noSpecifiedWidthCount++;
            }
        }

        if ($width < 100 && $noSpecifiedWidthCount > 0) {
            foreach ($columns as $column) {
                if (!$column->hasFlag('width')) {
                    // [WIZDAM] Optimized: No need to re-fetch column via getColumn($id)
                    // Objects are by reference naturally.
                    $column->addFlag('width', (int) round((100 - $width)/$noSpecifiedWidthCount));
                }
            }
        }
    }

    /**
     * Add grid features.
     * @param array $features
     */
    public function _addFeatures($features) {
        assert(is_array($features));
        foreach ($features as $feature) {
            assert(is_a($feature, 'GridFeature'));
            $this->features[$feature->getId()] = $feature;
        }
    }
}
?>
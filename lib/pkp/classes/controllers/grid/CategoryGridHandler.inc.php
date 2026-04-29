<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/CategoryGridHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryGridHandler
 * @ingroup controllers_grid
 *
 * @brief Class defining basic operations for handling HTML grids with categories.
 * [WIZDAM EDITION] Refactored for PHP 8.x Strict Standards & Security.
 */

// import grid classes
import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// empty category constant
define('GRID_CATEGORY_NONE', 'NONE');

class CategoryGridHandler extends GridHandler {

    /** @var string empty category row locale key */
    protected string $emptyCategoryRowText = 'grid.noItems';

    /** @var string|null The category id that this grid is currently rendering. */
    protected ?string $currentCategoryId = null;

    /**
     * Constructor.
     * @param GridDataProvider|null $dataProvider
     */
    public function __construct($dataProvider = null) {
        parent::__construct($dataProvider);

        import('lib.pkp.classes.controllers.grid.NullGridCellProvider');
        $this->addColumn(new GridColumn(
            'indent', 
            null, 
            null, 
            'controllers/grid/gridCell.tpl',
            new NullGridCellProvider(), 
            ['indent' => true]
        ));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CategoryGridHandler($dataProvider = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($dataProvider);
    }


    //
    // Getters and setters.
    //
    /**
     * Get the empty rows text for a category.
     * @return string
     */
    public function getEmptyCategoryRowText(): string {
        return $this->emptyCategoryRowText;
    }

    /**
     * Set the empty rows text for a category.
     * @param string $translationKey
     */
    public function setEmptyCategoryRowText($translationKey) {
        $this->emptyCategoryRowText = $translationKey;
    }


    //
    // Public handler methods
    //
    /**
     * Render a category with all the rows inside of it.
     * [WIZDAM] Removed references from args and request
     * @param array $args
     * @param Request $request
     * @return string the serialized row JSON message or a flag
     */
    public function fetchCategory($args, $request): string {
        // Instantiate the requested row (includes a validity check on the row id).
        $row = $this->getRequestedCategoryRow($request, $args);

        $json = new JSONMessage(true);
        if ($row === null) {
            // Inform the client that the category no longer exists.
            $json->setAdditionalAttributes(['elementNotFound' => (int)$args['rowId']]);
        } else {
            // Render the requested category
            $this->setFirstDataColumn();
            $json->setContent($this->_renderCategoryInternally($request, $row));
        }

        // Render and return the JSON message.
        return $json->getString();
    }


    //
    // Extended methods from GridHandler
    //
    
    /**
     * [WIZDAM] Removed reference (&)
     * @see GridHandler::initialize($request)
     * @param Request $request
     * @return void
     */
    public function initialize($request) {
        parent::initialize($request);

        // [SECURITY FIX] Secure 'rowCategoryId' with trim() and type casting
        $rowCategoryId = $request->getUserVar('rowCategoryId');
        if ($rowCategoryId !== null) {
            $this->currentCategoryId = (string) trim((string)$rowCategoryId);
        }
    }

    /**
     * @see GridHandler::getRequestArgs()
     * @return array
     */
    public function getRequestArgs(): array {
        $args = parent::getRequestArgs();

        // If grid is rendering grid rows inside category,
        // add current category id value so rows will also know
        // their parent category.
        if ($this->currentCategoryId !== null) {
            if ($this->getCategoryRowIdParameterName()) {
                $args[$this->getCategoryRowIdParameterName()] = $this->currentCategoryId;
            }
        }

        return $args;
    }


    /**
     * @see GridHandler::getJSHandler()
     * @return string
     */
    public function getJSHandler() {
        return '$.pkp.controllers.grid.CategoryGridHandler';
    }

    /**
     * @see GridHandler::setUrls()
     * @param Request $request
     * @return void
     */
    public function setUrls($request) {
        $router = $request->getRouter();
        $url = ['fetchCategoryUrl' => $router->url($request, null, null, 'fetchCategory', null, $this->getRequestArgs())];
        parent::setUrls($request, $url);
    }

    /**
     * @see GridHandler::doSpecificFetchGridActions($args, $request)
     * @param array $args
     * @param Request $request
     * @param TemplateManager $templateMgr
     */
    public function doSpecificFetchGridActions($args, $request, $templateMgr) {
        // Render the body elements (category groupings + rows inside a <tbody>)
        $gridBodyParts = $this->_renderCategoriesInternally($request);
        // [WIZDAM] Changed assign_by_ref to assign (Objects are by ref anyway in PHP 8)
        $templateMgr->assign('gridBodyParts', $gridBodyParts);
    }

    /**
     * @see GridHandler::getRowDataElement()
     * @param Request $request
     * @param string $rowId
     * @return mixed|null
     */
    protected function getRowDataElement($request, $rowId) {
        $rowData = parent::getRowDataElement($request, $rowId);
        
        // [SECURITY FIX] Secure 'rowCategoryId' with trim()
        $rowCategoryId = $request->getUserVar('rowCategoryId');
        $rowCategoryId = $rowCategoryId !== null ? trim((string)$rowCategoryId) : null;

        if ($rowData === null && $rowCategoryId !== null) {
            // Try to get row data inside category.
            $categoryRowData = parent::getRowDataElement($request, $rowCategoryId);
            if ($categoryRowData !== null) {
                $categoryElements = $this->getCategoryData($categoryRowData, null);

                assert(is_array($categoryElements));
                if (!isset($categoryElements[$rowId])) return null;

                // Let grid (and also rows) knowing the current category id.
                // This value will be published by the getRequestArgs method.
                $this->currentCategoryId = $rowCategoryId;

                return $categoryElements[$rowId];
            }
        } else {
            return $rowData;
        }
        return null; // Fallback
    }

    /**
     * @see GridHandler::setFirstDataColumn()
     * @return void
     */
    public function setFirstDataColumn() {
        $columns = $this->getColumns();
        reset($columns);
        // Category grids will always have indent column firstly,
        // so we need to consider the first column the second one.
        $secondColumn = next($columns); /* @var GridColumn $secondColumn */
        if ($secondColumn) {
            $secondColumn->addFlag('firstColumn', true);
        }
    }


    //
    // Protected methods to be overridden/used by subclasses
    //
    /**
     * Get a new instance of a category grid row.
     * [WIZDAM] Removed return reference (&)
     * @return CategoryGridRow
     */
    protected function getCategoryRowInstance() {
        // provide a sensible default category row definition
        return new GridCategoryRow();
    }

    /**
     * Get the category row id parameter name.
     * @return string|null
     */
    public function getCategoryRowIdParameterName() {
        // Must be implemented by subclasses.
        return null;
    }

    /**
     * Fetch the contents of a category.
     * [WIZDAM] Removed return reference and param reference
     * @param mixed $categoryDataElement
     * @param array|null $filter
     * @return array
     */
    public function getCategoryData($categoryDataElement, $filter = null): array {
        $gridData = [];
        $dataProvider = $this->getDataProvider();
        if ($dataProvider instanceof CategoryGridDataProvider) {
            // Populate the grid with data from the data provider.
            $gridData = $dataProvider->getCategoryData($categoryDataElement, $filter);
        }
        return $gridData;
    }

    /**
     * Tries to identify the data element in the grids data source.
     * [WIZDAM] Removed return reference (&)
     * @param Request $request
     * @param array $args
     * @return GridRow|null
     */
    public function getRequestedCategoryRow($request, $args) {
        if (isset($args['rowId'])) {
            // A row ID was specified. Fetch it
            $elementId = $args['rowId'];

            // Retrieve row data
            $dataElement = $this->getRowDataElement($request, $elementId);
            if ($dataElement === null) {
                return null;
            }
        } else {
             // If no rowId, we might be creating a new row, so elementId is null
             $elementId = null;
             $dataElement = null; 
        }

        // Instantiate a new row
        return $this->_getInitializedCategoryRowInstance($request, $elementId, $dataElement);
    }

    /**
     * Get the category data element sequence value.
     * @param mixed $gridDataElement
     * @return int
     */
    public function getCategoryDataElementSequence($gridDataElement) {
        assert(false);
        return 0;
    }

    /**
     * Operation to save the category data element new sequence.
     * @param mixed $gridDataElement
     * @param int $newSequence
     */
    public function saveCategoryDataElementSequence($gridDataElement, $newSequence) {
        assert(false);
    }

    /**
     * @see GridHandler::saveRowDataElementSequence()
     * @param mixed $gridDataElement
     * @param string $categoryId
     * @param int $newSequence
     * @return void
     */
    public function saveRowDataElementSequence($gridDataElement, $categoryId, $newSequence) {
        assert(false);
    }

    /**
     * @see GridHandler::renderRowInternally()
     * @param Request $request
     * @param GridRow $row
     * @return string HTML
     */
    public function renderRowInternally($request, $row) {
        if ($this->getCategoryRowIdParameterName()) {
            $param = $this->getRequestArg($this->getCategoryRowIdParameterName());
            $templateMgr = TemplateManager::getManager();
            $templateMgr->assign('categoryId', $param);
        }

        return parent::renderRowInternally($request, $row);
    }


    //
    // Private helper methods
    //
    /**
     * Instantiate a new row.
     * [WIZDAM] Removed return reference (&)
     * @param Request $request
     * @param string|null $elementId
     * @param mixed $element
     * @return GridRow
     */
    private function _getInitializedCategoryRowInstance($request, $elementId, $element) {
        // Instantiate a new row
        $row = $this->getCategoryRowInstance();
        $row->setGridId($this->getId());
        if ($elementId) $row->setId($elementId);
        $row->setData($element);
        $row->setRequestArgs($this->getRequestArgs());

        // Initialize the row before we render it
        $row->initialize($request);
        $this->callFeaturesHook('getInitializedCategoryRowInstance', [
            'request' => $request,
            'grid' => $this,
            'row' => $row
        ]);
        return $row;
    }

    /**
     * Render all the categories internally
     * [WIZDAM] Removed reference from request
     * @param Request $request
     * @return array
     */
    private function _renderCategoriesInternally($request): array {
        $renderedCategories = [];

        $elements = $this->getGridDataElements($request);
        if (is_iterable($elements)) {
            foreach($elements as $key => $element) {
                // Instantiate a new row
                $categoryRow = $this->_getInitializedCategoryRowInstance($request, $key, $element);

                // Render the row
                $renderedCategories[] = $this->_renderCategoryInternally($request, $categoryRow);
                unset($element);
            }
        }

        return $renderedCategories;
    }

    /**
     * Render a category row and its data.
     * [WIZDAM] Removed references everywhere
     * @param Request $request
     * @param GridCategoryRow $categoryRow
     * @return string HTML
     */
    private function _renderCategoryInternally($request, $categoryRow): string {
        // Prepare the template to render the category.
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('grid', $this);
        
        $columns = $this->getColumns();
        $templateMgr->assign('columns', $columns);

        $categoryDataElement = $categoryRow->getData();
        $filter = $this->getFilterSelectionData($request);
        $rowData = $this->getCategoryData($categoryDataElement, $filter);

        // Render the data rows
        $templateMgr->assign('categoryRow', $categoryRow);

        // Let grid (and also rows) knowing the current category id.
        $this->currentCategoryId = $categoryRow->getId();

        $renderedRows = $this->_renderRowsInternally($request, $rowData);
        $templateMgr->assign('rows', $renderedRows);

        $renderedCategoryRow = $this->renderRowInternally($request, $categoryRow);

        // Finished working with this category, erase the current id value.
        $this->currentCategoryId = null;

        $templateMgr->assign('renderedCategoryRow', $renderedCategoryRow);
        return $templateMgr->fetch('controllers/grid/gridBodyPartWithCategory.tpl');
    }
}

?>
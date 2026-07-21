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
 * [LUMERA] Modernized for PHP 8.x: strict body casting, defensive array access, replaced asserts with exceptions, and removed legacy unset.
 */

// import grid classes
import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.classes.controllers.grid.GridCategoryRow');

// empty category constant
define('GRID_CATEGORY_NONE', 'NONE');

class CategoryGridHandler extends GridHandler {

    /** @var string empty category row locale key */
    protected $emptyCategoryRowText = 'grid.noItems';

    /** @var string|null The category id that this grid is currently rendering. */
    protected $currentCategoryId = null;

    /**
     * Constructor.
     * @param mixed $dataProvider
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
     * @param mixed $dataProvider
     */
    public function CategoryGridHandler($dataProvider = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().", 
                E_USER_DEPRECATED
            );
        }
        // [LUMERA FIX] Gunakan $this alih-alih self untuk memanggil konstruktor non-statis
        $this->__construct($dataProvider);
    }


    //
    // Getters and setters.
    //
    /**
     * Get the empty rows text for a category.
     * @return string
     */
    public function getEmptyCategoryRowText() {
        // [LUMERA FIX] Casting di dalam body, signature tetap bersih
        return (string) $this->emptyCategoryRowText;
    }

    /**
     * Set the empty rows text for a category.
     * @param mixed $translationKey
     */
    public function setEmptyCategoryRowText($translationKey) {
        // [LUMERA FIX] Casting di dalam body
        $this->emptyCategoryRowText = (string) $translationKey;
    }


    //
    // Public handler methods
    //
    /**
     * Render a category with all the rows inside of it.
     * @param array $args
     * @param Request $request
     * @return string the serialized row JSON message or a flag
     */
    public function fetchCategory($args, $request) {
        // Instantiate the requested row (includes a validity check on the row id).
        $row = $this->getRequestedCategoryRow($request, $args);

        $json = new JSONMessage(true);
        if ($row === null) {
            // [LUMERA FIX] Casting eksplisit untuk array access
            $rowId = isset($args['rowId']) ? (int) $args['rowId'] : 0;
            $json->setAdditionalAttributes(['elementNotFound' => $rowId]);
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
     * Initialize the grid handler.
     * @see GridHandler::initialize($request)
     * @param Request $request
     * @param array|null $args
     */
    public function initialize($request, $args = null) {
        parent::initialize($request, $args);

        $rowCategoryId = $request->getUserVar('rowCategoryId');
        // [LUMERA FIX] Defensive null check sebelum casting
        if ($rowCategoryId !== null) {
            $this->currentCategoryId = trim((string) $rowCategoryId);
        }
    }

    /**
     * Get the grid request parameters.
     * @see GridHandler::getRequestArgs()
     * @return array
     */
    public function getRequestArgs(): array {
        $args = parent::getRequestArgs();

        if ($this->currentCategoryId !== null) {
            $paramName = $this->getCategoryRowIdParameterName();
            if ($paramName !== null) {
                $args[$paramName] = $this->currentCategoryId;
            }
        }

        return $args;
    }

    /**
     * Get the JavaScript handler for the grid.
     * @see GridHandler::getJSHandler()
     * @return string
     */
    public function getJSHandler() {
        return '$.pkp.controllers.grid.CategoryGridHandler';
    }

    /**
     * Set the URLs for the grid.
     * @see GridHandler::setUrls()
     * @param Request $request
     * @param array $extraUrls
     */
    public function setUrls($request, $extraUrls = []) {
        $router = $request->getRouter();
        $categoryUrl = ['fetchCategoryUrl' => $router->url($request, null, null, 'fetchCategory', null, $this->getRequestArgs())];
        $mergedUrls = array_merge($categoryUrl, $extraUrls);
        
        parent::setUrls($request, $mergedUrls);
    }

    /**
     * Render the grid actions for a specific category.
     * @see GridHandler::doSpecificFetchGridActions($args, $request)
     * @param array $args
     * @param Request $request
     * @param TemplateManager $templateMgr
     */
    public function doSpecificFetchGridActions($args, $request, $templateMgr) {
        $gridBodyParts = $this->_renderCategoriesInternally($request);
        $templateMgr->assign('gridBodyParts', $gridBodyParts);
    }

    /**
     * Get the data element for a specific row.
     * @see GridHandler::getRowDataElement()
     * @param Request $request
     * @param string $rowId
     * @return mixed|null
     */
    public function getRowDataElement($request, $rowId) {
        $rowData = parent::getRowDataElement($request, $rowId);
        
        $rowCategoryId = $request->getUserVar('rowCategoryId');
        $rowCategoryId = $rowCategoryId !== null ? trim((string) $rowCategoryId) : null;

        if ($rowData === null && $rowCategoryId !== null) {
            $categoryRowData = parent::getRowDataElement($request, $rowCategoryId);
            if ($categoryRowData !== null) {
                $categoryElements = $this->getCategoryData($categoryRowData, null);

                if (!is_array($categoryElements)) {
                    return null;
                }

                if (!isset($categoryElements[$rowId])) {
                    return null;
                }

                $this->currentCategoryId = $rowCategoryId;

                return $categoryElements[$rowId];
            }
        }
        
        return $rowData;
    }

    /**
     * Set the first data column.
     * @see GridHandler::setFirstDataColumn()
     * @return void
     */
    public function setFirstDataColumn() {
        $columns = $this->getColumns();
        
        if (!is_array($columns)) {
            return;
        }
        
        reset($columns);
        $secondColumn = next($columns);
        if ($secondColumn) {
            $secondColumn->addFlag('firstColumn', true);
        }
    }


    //
    // Protected methods to be overridden/used by subclasses
    //
    /**
     * Get a new instance of a category grid row.
     * @return GridCategoryRow
     */
    protected function getCategoryRowInstance() {
        return new GridCategoryRow();
    }

    /**
     * Get the category row id parameter name.
     * @return string|null
     */
    public function getCategoryRowIdParameterName() {
        return null;
    }

    /**
     * Fetch the contents of a category.
     * @param mixed $categoryDataElement
     * @param array|null $filter
     * @return array
     */
    public function getCategoryData($categoryDataElement, $filter = null) {
        $gridData = [];
        $dataProvider = $this->getDataProvider();
        if ($dataProvider instanceof CategoryGridDataProvider) {
            $gridData = $dataProvider->getCategoryData($categoryDataElement, $filter);
        }
        return is_array($gridData) ? $gridData : [];
    }

    /**
     * Tries to identify the data element in the grids data source.
     * @param Request $request
     * @param array $args
     * @return GridRow|null
     */
    public function getRequestedCategoryRow($request, $args) {
        $elementId = $args['rowId'] ?? null;

        if ($elementId !== null) {
            $dataElement = $this->getRowDataElement($request, $elementId);
            if ($dataElement === null) {
                return null;
            }
        } else {
             $dataElement = null; 
        }

        return $this->_getInitializedCategoryRowInstance($request, $elementId, $dataElement);
    }

    /**
     * Get the category data element sequence value.
     * @param mixed $gridDataElement
     * @return int
     */
    public function getCategoryDataElementSequence($gridDataElement) {
        throw new \BadMethodCallException('Subclasses of CategoryGridHandler must implement getCategoryDataElementSequence().');
    }

    /**
     * Operation to save the category data element new sequence.
     * @param mixed $gridDataElement
     * @param int $newSequence
     */
    public function saveCategoryDataElementSequence($gridDataElement, $newSequence) {
        throw new \BadMethodCallException('Subclasses of CategoryGridHandler must implement saveCategoryDataElementSequence().');
    }

    /**
     * Operation to save the row data element new sequence.
     * @see GridHandler::saveRowDataElementSequence()
     * @param Request $request
     * @param string $rowId
     * @param mixed $gridDataElement
     * @param int $newSequence
     */
    public function saveRowDataElementSequence($request, $rowId, $gridDataElement, $newSequence) {
        throw new \BadMethodCallException('Subclasses of CategoryGridHandler must implement saveRowDataElementSequence().');
    }

    /**
     * Render a row internally.
     * @see GridHandler::renderRowInternally()
     * @param Request $request
     * @param GridRow $row
     * @return string HTML
     */
    public function renderRowInternally($request, $row) {
        $paramName = $this->getCategoryRowIdParameterName();
        if ($paramName !== null) {
            $args = $this->getRequestArgs();
            $param = $args[$paramName] ?? null;
            if ($param !== null) {
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('categoryId', (string) $param);
            }
        }

        return parent::renderRowInternally($request, $row);
    }


    //
    // Private helper methods
    //
    /**
     * Instantiate a new row.
     * @param Request $request
     * @param string|null $elementId
     * @param mixed $element
     * @return GridRow
     */
    private function _getInitializedCategoryRowInstance($request, $elementId, $element) {
        $row = $this->getCategoryRowInstance();
        $row->setGridId($this->getId());

        if ($elementId !== null) {
            $row->setId((string) $elementId);
        }
        
        $row->setData($element);
        $row->setRequestArgs($this->getRequestArgs());

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
     * @param Request $request
     * @return array
     */
    private function _renderCategoriesInternally($request) {
        $renderedCategories = [];

        $elements = $this->getGridDataElements($request);

        if (is_array($elements)) {
            foreach ($elements as $key => $element) {
                $categoryRow = $this->_getInitializedCategoryRowInstance($request, (string) $key, $element);
                $renderedCategories[] = $this->_renderCategoryInternally($request, $categoryRow);
            }
        }

        return $renderedCategories;
    }

    /**
     * Render a category row and its data.
     * @param Request $request
     * @param GridCategoryRow $categoryRow
     * @return string HTML
     */
    private function _renderCategoryInternally($request, $categoryRow) {
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('grid', $this);
        
        $columns = $this->getColumns();
        $templateMgr->assign('columns', is_array($columns) ? $columns : []);

        $categoryDataElement = $categoryRow->getData();
        $filter = $this->getFilterSelectionData($request);
        $rowData = $this->getCategoryData($categoryDataElement, $filter);

        $templateMgr->assign('categoryRow', $categoryRow);

        $this->currentCategoryId = $categoryRow->getId() !== null ? (string) $categoryRow->getId() : null;

        $renderedRows = $this->_renderRowsInternally($request, $rowData);
        $templateMgr->assign('rows', is_array($renderedRows) ? $renderedRows : []);

        $renderedCategoryRow = $this->renderRowInternally($request, $categoryRow);

        $this->currentCategoryId = null;

        $templateMgr->assign('renderedCategoryRow', $renderedCategoryRow);
        return $templateMgr->fetch('controllers/grid/gridBodyPartWithCategory.tpl');
    }
    
}
?>
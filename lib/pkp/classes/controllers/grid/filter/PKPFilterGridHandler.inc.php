<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/filter/PKPFilterGridHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPFilterGridHandler
 * @ingroup classes_controllers_grid_filter
 *
 * @brief Manage filter administration and settings.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// import filter grid specific classes
import('lib.pkp.classes.controllers.grid.filter.PKPFilterGridRow');
import('lib.pkp.classes.controllers.grid.filter.FilterGridCellProvider');

// import metadata framework classes
import('lib.pkp.classes.metadata.MetadataDescription');

class PKPFilterGridHandler extends GridHandler {

    /** @var DataObject|null the context (journal, press, conference) for which we manage filters */
    protected ?DataObject $_context = null;

    /** @var string the description text to be displayed in the filter form */
    protected string $_formDescription = '';

    /** @var mixed the symbolic name of the filter group to be configured in this grid */
    protected $_filterGroupSymbolic;

    /**
     * Constructor
     */
    public function __construct() {
        // Instantiate the citation DAO which will implicitly
        // define the filter groups for parsers and lookup
        // database connectors.
        DAORegistry::getDAO('CitationDAO');

        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPFilterGridHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Getters/Setters
    //

    /**
     * Set the context that filters are being managed for.
     * This object must implement the getId() and getSettings() methods.
     * @param DataObject $context
     */
    public function setContext(DataObject $context): void {
        $this->_context = $context;
    }

    /**
     * Get the context that filters are being managed for.
     * @return DataObject|null
     */
    public function getContext(): ?DataObject {
        return $this->_context;
    }

    /**
     * Set the form description text
     * @param string $formDescription
     */
    public function setFormDescription(string $formDescription): void {
        $this->_formDescription = $formDescription;
    }

    /**
     * Get the form description text
     * @return string
     */
    public function getFormDescription(): string {
        return $this->_formDescription;
    }

    /**
     * Set the filter group symbol
     * @param mixed $filterGroupSymbolic
     */
    public function setFilterGroupSymbolic($filterGroupSymbolic): void {
        $this->_filterGroupSymbolic = $filterGroupSymbolic;
    }

    /**
     * Get the filter group symbol
     * @return mixed
     */
    public function getFilterGroupSymbolic() {
        return $this->_filterGroupSymbolic;
    }


    //
    // Overridden methods from PKPHandler
    //

    /**
     * Configure the grid
     * @see PKPHandler::initialize()
     */
    public function initialize($request, $args = null) {
        parent::initialize($request, $args);

        // Load manager-specific translations
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_MANAGER, LOCALE_COMPONENT_CORE_SUBMISSION);

        // Retrieve the filters to be displayed in the grid
        $router = $request->getRouter();
        $context = $router->getContext($request);
        
        // Strict Type Safety: Ensure contextId is integer
        $contextId = (is_null($context)) ? CONTEXT_ID_NONE : (int) $context->getId();
        
        $filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
        
        // Retrieve Objects. Note: This returns a DAOResultFactory or Array.
        $data = $filterDao->getObjectsByGroup($this->getFilterGroupSymbolic(), $contextId);
        
        // Safety Check for Empty Result to prevent Strict Type Crash later
        if (is_null($data)) {
            $data = [];
        }

        $this->setGridDataElements($data);

        // Grid action
        $router = $request->getRouter();
        $this->addAction(
            new LegacyLinkAction(
                'addFilter',
                LINK_ACTION_MODE_MODAL,
                LINK_ACTION_TYPE_APPEND,
                $router->url($request, null, null, 'addFilter'),
                'grid.action.addItem'
            )
        );

        // Columns
        $cellProvider = new FilterGridCellProvider();
        
        // FIX: Changed 'false' to 'null' for the 3rd argument (titleTranslated)
        $this->addColumn(
            new GridColumn(
                'displayName',
                'manager.setup.filter.grid.filterDisplayName',
                null, // <--- WAS false, NOW null
                'controllers/grid/gridCell.tpl',
                $cellProvider
            )
        );
        
        // FIX: Changed 'false' to 'null' for the 3rd argument (titleTranslated)
        $this->addColumn(
            new GridColumn(
                'settings',
                'manager.setup.filter.grid.filterSettings',
                null, // <--- WAS false, NOW null
                'controllers/grid/gridCell.tpl',
                $cellProvider
            )
        );
    }


    //
    // Overridden methods from GridHandler
    //

    /**
     * Get a row instance.
     * @see GridHandler::getRowInstance()
     * FIXED: Must be PUBLIC to match Parent Class definition
     */
    public function getRowInstance() {
        // Return a filter row
        return new PKPFilterGridRow();
    }


    //
    // Public Filter Grid Actions
    //

    /**
     * An action to manually add a new filter
     * @param array $args
     * @param PKPRequest $request
     */
    public function addFilter($args, $request) {
        // Calling editFilter() to edit a new filter.
        return $this->editFilter($args, $request, true);
    }

    /**
     * Edit a filter
     * @param array $args
     * @param PKPRequest $request
     * @param bool $newFilter
     */
    public function editFilter($args, $request, bool $newFilter = false) {
        // Identify the filter to be edited
        if ($newFilter) {
            $filter = null;
        } else {
            $filter = $this->getFilterFromArgs($request, $args, true);
        }

        // Form handling
        import('lib.pkp.classes.controllers.grid.filter.form.FilterForm');
        
        // Strict Type Safety: Ensure null is not passed where string is expected
        $formTitle = $this->getTitle() ?? ''; 
        
        $filterForm = new FilterForm(
            $filter, 
            (string) $formTitle, 
            $this->getFormDescription(), 
            $this->getFilterGroupSymbolic()
        );

        $filterForm->initData($this->getGridDataElements($request));

        $json = new JSONMessage(true, $filterForm->fetch($request));
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Update a filter
     * @param array $args
     * @param PKPRequest $request
     * @return string
     */
    public function updateFilter($args, $request) {
        if(!$request->isPost()) fatalError('Cannot update filter via GET request!');

        // Identify the citation to be updated
        $filter = $this->getFilterFromArgs($request, $args, true);

        // Form initialization
        import('lib.pkp.classes.controllers.grid.filter.form.FilterForm');
        
        // Type Safety
        $formTitle = $this->getTitle() ?? ''; 
        $nullVar = null; // Explicit null for constructor
        
        $filterForm = new FilterForm(
            $filter, 
            (string) $formTitle, 
            $this->getFormDescription(), 
            $nullVar
        ); // No filter group required here.
        
        $filterForm->readInputData();

        // Form validation
        if ($filterForm->validate()) {
            // Persist the filter.
            $filterForm->execute($request);

            // Render the updated filter row into
            // a JSON response
            $row = $this->getRowInstance();
            $row->setGridId($this->getId());
            $row->setId($filter->getId());
            $row->setData($filter);
            $row->initialize($request);
            $json = new JSONMessage(true, $this->renderRowInternally($request, $row));
        } else {
            // Re-display the filter form with error messages
            // so that the user can fix it.
            $json = new JSONMessage(false, $filterForm->fetch($request));
        }

        // Return the serialized JSON response
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Delete a filter
     * @param array $args
     * @param PKPRequest $request
     * @return string
     */
    public function deleteFilter($args, $request) {
        // Identify the filter to be deleted
        $filter = $this->getFilterFromArgs($request, $args);

        $filterDao = DAORegistry::getDAO('FilterDAO');
        $result = $filterDao->deleteObject($filter);

        if ($result) {
            $json = new JSONMessage(true);
        } else {
            $json = new JSONMessage(false, __('manager.setup.filter.grid.errorDeletingFilter'));
        }
        header('Content-Type: application/json');
        return $json->getString();
    }


    //
    // Protected helper functions
    //
    
    /**
     * This will retrieve a filter object from the grids data source based on the request arguments.
     * If no filter can be found then this will raise a fatal error.
     * @param PKPRequest $request
     * @param array $args
     * @param bool $mayBeTemplate whether filter templates should be considered.
     * @return Filter
     */
    protected function getFilterFromArgs($request, $args, bool $mayBeTemplate = false): Filter {
        $filter = null;
        if (isset($args['filterId'])) {
            // Identify the filter id and retrieve the
            // corresponding element from the grid's data source.
            $filter = $this->getRowDataElement($request, $args['filterId']);
            if (!($filter instanceof Filter)) fatalError('Invalid filter id!');
        } elseif ($mayBeTemplate && isset($args['filterTemplateId'])) {
            // We need to instantiate a new filter from a
            // filter template.
            $filterTemplateId = (int) $args['filterTemplateId'];
            $filterDao = DAORegistry::getDAO('FilterDAO');
            $filter = $filterDao->getObjectById($filterTemplateId);
            if (!($filter instanceof Filter)) fatalError('Invalid filter template id!');

            // Reset the filter id and template flag so that the
            // filter form correctly handles this filter as a new filter.
            $filter->setId(null);
            $filter->setIsTemplate(false);
        } else {
            fatalError('Missing filter id!');
        }
        
        return $filter;
    }
}
?>
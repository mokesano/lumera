<?php
declare(strict_types=1);

/**
 * @file classes/controllers/grid/filter/form/FilterForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FilterForm
 * @ingroup classes_controllers_grid_filter_form
 *
 * @brief Form for adding/editing a filter.
 * New filter instances are based on filter templates.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.Form');

class FilterForm extends Form {
    /** @var Filter|null the filter being edited */
    protected ?Filter $_filter;

    /** @var string a translation key for the filter form title */
    protected string $_title;

    /** @var string a translation key for the filter form description */
    protected string $_description;

    /** @var mixed the filter group to be configured in this form */
    protected $_filterGroupSymbolic;

    /**
     * Constructor.
     * @param Filter|null $filter
     * @param string $title
     * @param string $description
     * @param mixed $filterGroupSymbolic
     */
    public function __construct($filter, $title, $description, $filterGroupSymbolic) {
        parent::__construct('controllers/grid/filter/form/filterForm.tpl');

        // Initialize internal state.
        $this->_filter = $filter;
        $this->_title = $title;
        $this->_description = $description;
        $this->_filterGroupSymbolic = $filterGroupSymbolic;

        // Transport filter/template id.
        $this->readUserVars(['filterId', 'filterTemplateId']);

        // Validation check common to all requests.
        $this->addCheck(new FormValidatorPost($this));

        // Validation check for template selection.
        if (!is_null($filter) && !is_numeric($filter->getId())) {
            $this->addCheck(new FormValidator($this, 'filterTemplateId', 'required', 'manager.setup.filter.grid.filterTemplateRequired'));
        }

        // Add filter specific meta-data and checks.
        if ($filter instanceof Filter) {
            $this->setData('filterSettings', $filter->getSettings());
            foreach($filter->getSettings() as $filterSetting) {
                // Add check corresponding to filter setting.
                // [WIZDAM] $this passed as form instance
                $settingCheck = $filterSetting->getCheck($this);
                if (!is_null($settingCheck)) $this->addCheck($settingCheck);
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FilterForm($filter, $title, $description, $filterGroupSymbolic) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::'" . get_class($this) . "'. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($filter, $title, $description, $filterGroupSymbolic);
    }

    //
    // Getters and Setters
    //
    /**
     * Get the filter
     * @return Filter|null
     */
    public function getFilter(): ?Filter {
        return $this->_filter;
    }

    /**
     * Get the filter form title
     * @return string
     */
    public function getTitle(): string {
        return $this->_title;
    }

    /**
     * Get the filter form description
     * @return string
     */
    public function getDescription(): string {
        return $this->_description;
    }

    /**
     * Get the filter group symbol
     * @return mixed
     */
    public function getFilterGroupSymbolic() {
        return $this->_filterGroupSymbolic;
    }

    //
    // Template methods from Form
    //
    
    /**
     * Initialize form data.
     * @param array $alreadyInstantiatedFilters
     */
    public function initData($alreadyInstantiatedFilters) {
        // Transport filter/template id.
        $this->readUserVars(['filterId', 'filterTemplateId']);

        $filter = $this->getFilter();
        if ($filter instanceof Filter) {
            // A transformation has already been chosen
            // so identify the settings and edit them.

            // Add filter default settings as form data.
            foreach($filter->getSettings() as $filterSetting) {
                // Add filter setting data
                $settingName = $filterSetting->getName();
                $this->setData($settingName, $filter->getData($settingName));
            }
        } else {
            // The user did not yet choose a template
            // to base the transformation on.

            // Retrieve all compatible filter templates
            // from the database.
            $filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
            $filterTemplateObjects = $filterDao->getObjectsByGroup($this->getFilterGroupSymbolic(), 0, true);
            $filterTemplates = [];

            // Make a blacklist of filters that cannot be
            // instantiated again because they already
            // have been instantiated and cannot be parameterized.
            $filterClassBlacklist = [];
            
            // [WIZDAM] Ensure $alreadyInstantiatedFilters is iterable
            if (is_array($alreadyInstantiatedFilters)) {
                foreach($alreadyInstantiatedFilters as $alreadyInstantiatedFilter) {
                    if (!$alreadyInstantiatedFilter->hasSettings()) {
                        $filterClassBlacklist[] = $alreadyInstantiatedFilter->getClassName();
                    }
                }
            }

            foreach($filterTemplateObjects as $filterTemplateObject) {
                // Check whether the filter is on the blacklist.
                if (in_array($filterTemplateObject->getClassName(), $filterClassBlacklist)) continue;

                // The filter can still be added.
                $filterTemplates[$filterTemplateObject->getId()] = $filterTemplateObject->getDisplayName();
            }
            $this->setData('filterTemplates', $filterTemplates);

            // There are no more filter templates to
            // be chosen from.
            if (empty($filterTemplates)) $this->setData('noMoreTemplates', true);
        }
    }

    /**
     * Initialize form data from user submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['filterId', 'filterTemplateId']);
        // A value of -1 for the filter template means "nothing selected"
        if ($this->getData('filterTemplate') == '-1') $this->setData('filterTemplate', '');

        $filter = $this->getFilter();
        if ($filter instanceof Filter) {
            $userVars = [];
            foreach($filter->getSettings() as $filterSetting) {
                $userVars[] = $filterSetting->getName();
            }
            if (!empty($userVars)) {
                $this->readUserVars($userVars);
            }
        }
    }

    /**
     * @see Form::fetch()
     */
    public function fetch($request, $template = null, $display = false) {
        $templateMgr = TemplateManager::getManager($request);

        // The form description depends on the current state
        // of the selection process: do we select a filter template
        // or configure the settings of a selected template?
        $filter = $this->getFilter();
        if ($filter instanceof Filter) {
            $displayName = $filter->getDisplayName();
            $templateMgr->assign('filterDisplayName', $displayName);
            if (count($filter->getSettings())) {
                // We need a filter specific translation key so that we
                // can explain the filter's configuration options.
                // We use the display name to generate such a key as this
                // is probably easiest for translators to understand.
                // This also has the advantage that we can explain
                // composite filters individually.
                $filterKey = PKPString::regexp_replace('/[^a-zA-Z0-9]/', '', $displayName);
                $filterKey = strtolower(substr($filterKey, 0, 1)).substr($filterKey, 1);
                $formDescriptionKey = $this->getDescription().'.'.$filterKey;
            } else {
                $formDescriptionKey = $this->getDescription().'Confirm';
            }
        } else {
            $templateMgr->assign('filterDisplayName', '');
            $formDescriptionKey = $this->getDescription().'Template';
        }

        $templateMgr->assign('formTitle', $this->getTitle());
        $templateMgr->assign('formDescription', $formDescriptionKey);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Save filter
     * @param PKPRequest $request
     */
    public function execute($request) {
        $filter = $this->getFilter();
        
        // Ensure strictly that we are dealing with a Filter object
        if (!($filter instanceof Filter)) {
            fatalError('Attempting to execute filter form without a valid Filter object.');
        }

        // Configure the filter
        foreach($filter->getSettings() as $filterSetting) {
            $settingName = $filterSetting->getName();
            $filter->setData($settingName, $this->getData($settingName));
        }

        // Persist the filter
        $filterDao = DAORegistry::getDAO('FilterDAO');
        if (is_numeric($filter->getId())) {
            $filterDao->updateObject($filter);
        } else {
            $router = $request->getRouter();
            $context = $router->getContext($request);
            $contextId = (is_null($context) ? CONTEXT_ID_NONE : $context->getId());
            $filterDao->insertObject($filter, $contextId);
        }
        return true;
    }
}

?>
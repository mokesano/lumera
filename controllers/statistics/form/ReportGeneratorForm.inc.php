<?php
declare(strict_types=1);

/**
 * @file controllers/statistics/form/ReportGeneratorForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReportGeneratorForm
 * @ingroup controllers_statistics_form
 * @see Form
 *
 * @brief Form to generate custom statistics reports.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('lib.pkp.classes.form.Form');

define('TIME_FILTER_OPTION_YESTERDAY', 0);
define('TIME_FILTER_OPTION_CURRENT_MONTH', 1);
define('TIME_FILTER_OPTION_RANGE_DAY', 2);
define('TIME_FILTER_OPTION_RANGE_MONTH', 3);

class ReportGeneratorForm extends Form {

    /** @var array $_columns */
    public array $_columns;

    /** @var array $_optionalColumns */
    public array $_optionalColumns;

    /** @var array $_objects */
    public array $_objects;

    /** @var array $_fileTypes */
    public array $_fileTypes;

    /** @var string $_metricType */
    public string $_metricType;

    /** @var array $_defaultReportTemplates */
    public array $_defaultReportTemplates;

    /** @var int|null $_reportTemplateIndex */
    public ?int $_reportTemplateIndex;

    /**
     * Constructor.
     * @param array $columns Report column names.
     * @param array $optionalColumns Report column names that are optional.
     * @param array $objects Object types.
     * @param array $fileTypes File types.
     * @param string $metricType The default report metric type.
     * @param array $defaultReportTemplates Default report templates.
     * @param int|null $reportTemplateIndex Current report template index.
     */
    public function __construct($columns, $optionalColumns, $objects, $fileTypes, $metricType, $defaultReportTemplates, $reportTemplateIndex = null) {
        parent::__construct('controllers/statistics/form/reportGeneratorForm.tpl');

        $this->_columns = $columns;
        $this->_optionalColumns = $optionalColumns;
        $this->_objects = $objects;
        $this->_fileTypes = $fileTypes;
        $this->_metricType = $metricType;
        $this->_defaultReportTemplates = $defaultReportTemplates;
        $this->_reportTemplateIndex = isset($reportTemplateIndex) ? (int)$reportTemplateIndex : null;

        $this->addCheck(new FormValidatorArray($this, 'columns', 'required', 'manager.statistics.reports.form.columnsRequired'));
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReportGeneratorForm($columns, $optionalColumns, $objects, $fileTypes, $metricType, $defaultReportTemplates, $reportTemplateIndex = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($columns, $optionalColumns, $objects, $fileTypes, $metricType, $defaultReportTemplates, $reportTemplateIndex);
    }

    /**
     * Initialize the form from the current settings.
     * @param PKPRequest $request
     */
    public function fetch($request, $template = null, $display = false) {
        $router = $request->getRouter();
        $context = $router->getContext($request);
        $columns = $this->_columns;

        $availableMetricTypeStrings = StatisticsHelper::getAllMetricTypeStrings();
        if (count($availableMetricTypeStrings) > 1) {
            $this->setData('metricTypeOptions', $availableMetricTypeStrings);
        }

        $reportTemplateOptions = [];
        $reportTemplates = $this->_defaultReportTemplates;
        foreach($reportTemplates as $reportTemplate) {
            $reportTemplateOptions[] = __($reportTemplate['nameLocaleKey']);
        }

        if (!empty($reportTemplateOptions)) {
            $this->setData('reportTemplateOptions', $reportTemplateOptions);
        }

        $reportTemplateIndex = $this->_reportTemplateIndex;
        if (!is_null($reportTemplateIndex) && isset($reportTemplates[$reportTemplateIndex])) {
            $reportTemplate = $reportTemplates[$reportTemplateIndex];
            $reportColumns = $reportTemplate['columns'];
            
            // [WIZDAM] Ensure we don't skip the loop logic improperly inside `if` blocks
            if (is_array($reportColumns)) {
                $this->setData('columns', $reportColumns);
                $this->setData('reportTemplate', $reportTemplateIndex);
                
                if (isset($reportTemplate['aggregationColumns'])) {
                    $aggreationColumns = $reportTemplate['aggregationColumns'];
                    if (is_array($aggreationColumns)) {
                        $aggreationOptions = [];
                        foreach ($aggreationColumns as $column) {
                            $columnName = StatisticsHelper::getColumnNames($column);
                            if (!$columnName) continue;
                            $aggreationOptions[$column] = $columnName;
                        }
                        $this->setData('aggregationOptions', $aggreationOptions);
                        $this->setData('selectedAggregationOptions', array_intersect($aggreationColumns, $reportColumns));
                    }
                }

                if (isset($reportTemplate['filter']) && is_array($reportTemplate['filter'])) {
                    foreach ($reportTemplate['filter'] as $dimension => $filter) {
                        switch ($dimension) {
                            case STATISTICS_DIMENSION_ASSOC_TYPE:
                                $this->setData('objectTypes', $filter);
                                break;
                        }
                    }
                }
            }
        }

        $timeFilterSelectedOption = $request->getUserVar('timeFilterOption');
        if (is_null($timeFilterSelectedOption)) {
            $timeFilterSelectedOption = TIME_FILTER_OPTION_CURRENT_MONTH;
        } else {
            // Ensure integer comparison
            $timeFilterSelectedOption = (int) $timeFilterSelectedOption;
        }

        switch ($timeFilterSelectedOption) {
            case TIME_FILTER_OPTION_YESTERDAY:
                $this->setData('yesterday', true);
                break;
            case TIME_FILTER_OPTION_CURRENT_MONTH:
            default:
                $this->setData('currentMonth', true);
                break;
            case TIME_FILTER_OPTION_RANGE_DAY:
                $this->setData('byDay', true);
                break;
            case TIME_FILTER_OPTION_RANGE_MONTH:
                $this->setData('byMonth', true);
                break;
        }

        $startTime = $request->getUserDateVar('dateStart');
        $endTime = $request->getUserDateVar('dateEnd');
        if (!$startTime) $startTime = time();
        if (!$endTime) $endTime = time();

        $this->setData('dateStart', $startTime);
        $this->setData('dateEnd', $endTime);

        if (isset($columns[STATISTICS_DIMENSION_ISSUE_ID])) {
            $issueDao = DAORegistry::getDAO('IssueDAO'); 
            $issueFactory = $issueDao->getIssues($context->getId());
            $issueIdAndTitles = [];
            while ($issue = $issueFactory->next()) {
                $issueIdAndTitles[$issue->getId()] = $issue->getIssueIdentification();
            }
            $this->setData('issuesOptions', $issueIdAndTitles);
            $this->setData('showArticleInput', isset($columns[STATISTICS_DIMENSION_SUBMISSION_ID]));
        }

        if (isset($columns[STATISTICS_DIMENSION_COUNTRY])) {
            $geoLocationTool = StatisticsHelper::getGeoLocationTool();
            if ($geoLocationTool) {
                $countryCodes = $geoLocationTool->getAllCountryCodes();
                if (is_array($countryCodes)) {
                    $countryCodes = array_combine($countryCodes, $countryCodes);
                    $this->setData('countriesOptions', $countryCodes);
                }
            }

            $this->setData('showRegionInput', isset($columns[STATISTICS_DIMENSION_REGION]));
            $this->setData('showCityInput', isset($columns[STATISTICS_DIMENSION_CITY]));
        }

        $this->setData('showMonthInputs', isset($columns[STATISTICS_DIMENSION_MONTH]));
        $this->setData('showDayInputs', isset($columns[STATISTICS_DIMENSION_DAY]));

        $orderColumns = $this->_columns;
        $nonOrderableColumns = [
            STATISTICS_DIMENSION_ASSOC_TYPE,
            STATISTICS_DIMENSION_SUBMISSION_ID,
            STATISTICS_DIMENSION_ISSUE_ID,
            STATISTICS_DIMENSION_CONTEXT_ID,
            STATISTICS_DIMENSION_REGION,
            STATISTICS_DIMENSION_FILE_TYPE,
            STATISTICS_DIMENSION_METRIC_TYPE
        ];

        foreach($nonOrderableColumns as $column) {
            unset($orderColumns[$column]);
        }

        $this->setData('metricType', $this->_metricType);
        $this->setData('objectTypesOptions', $this->_objects);
        if ($this->_fileTypes) {
            $this->setData('fileTypesOptions', $this->_fileTypes);
        }
        $this->setData('fileAssocTypes', [ASSOC_TYPE_GALLEY, ASSOC_TYPE_ISSUE_GALLEY]);
        $this->setData('orderColumnsOptions', $orderColumns);
        $this->setData('orderDirectionsOptions', [
            STATISTICS_ORDER_ASC => __('manager.statistics.reports.orderDir.asc'),
            STATISTICS_ORDER_DESC => __('manager.statistics.reports.orderDir.desc')
        ]);

        $columnsOptions = $this->_columns;
        // Reports will always include this column.
        unset($columnsOptions[STATISTICS_METRIC]);
        $this->setData('columnsOptions', $columnsOptions);
        $this->setData('optionalColumns', $this->_optionalColumns);

        return parent::fetch($request, $template, $display);
    }

    /**
     * Assign user-submitted data to form.
     */
    public function readInputData() {
        $this->readUserVars([
            'columns', 'objectTypes', 'fileTypes', 'objectIds', 'issues',
            'articles', 'timeFilterOption', 'countries', 'regions', 'cityNames',
            'orderByColumn', 'orderByDirection'
        ]);
        parent::readInputData();
    }

    /**
     * @see Form::execute()
     * @param PKPRequest $request (No reference needed in PHP8 for objects)
     * @return string Report URL
     */
    public function execute($request) {
        parent::execute($request);
        $router = $request->getRouter();
        $context = $router->getContext($request);

        $columns = $this->getData('columns');
        $filter = [];
        
        if ($this->getData('objectTypes')) {
            $filter[STATISTICS_DIMENSION_ASSOC_TYPE] = $this->getData('objectTypes');
        }

        // [WIZDAM] Fixed logic: count($filter...) == 1 check was likely intended to check size of assoc types
        if ($this->getData('objectIds') && isset($filter[STATISTICS_DIMENSION_ASSOC_TYPE]) && count($filter[STATISTICS_DIMENSION_ASSOC_TYPE]) == 1) {
            $objectIds = explode(',', (string) $this->getData('objectIds'));
            $filter[STATISTICS_DIMENSION_ASSOC_ID] = $objectIds;
        }

        if ($this->getData('fileTypes')) {
            $filter[STATISTICS_DIMENSION_FILE_TYPE] = $this->getData('fileTypes');
        }

        $filter[STATISTICS_DIMENSION_CONTEXT_ID] = $context->getId();

        if ($this->getData('issues')) {
            $filter[STATISTICS_DIMENSION_ISSUE_ID] = $this->getData('issues');
        }

        if ($this->getData('articles')) {
            $filter[STATISTICS_DIMENSION_SUBMISSION_ID] = $this->getData('articles');
        }

        // Get the time filter data, if any.
        $startTime = $request->getUserDateVar('dateStart', 1, 1, 1, 23, 59, 59);
        $endTime = $request->getUserDateVar('dateEnd', 1, 1, 1, 23, 59, 59);
        
        // Initialize variables to avoid undefined warnings
        $startYear = $endYear = $startMonth = $endMonth = $startDay = $endDay = '';
        
        if ($startTime && $endTime) {
            $startYear = date('Y', $startTime);
            $endYear = date('Y', $endTime);
            $startMonth = date('m', $startTime);
            $endMonth = date('m', $endTime);
            $startDay = date('d', $startTime);
            $endDay = date('d', $endTime);
        }

        $timeFilterOption = (int) $this->getData('timeFilterOption');
        switch($timeFilterOption) {
            case TIME_FILTER_OPTION_YESTERDAY:
                $filter[STATISTICS_DIMENSION_DAY] = STATISTICS_YESTERDAY;
                break;
            case TIME_FILTER_OPTION_CURRENT_MONTH:
                $filter[STATISTICS_DIMENSION_MONTH] = STATISTICS_CURRENT_MONTH;
                break;
            case TIME_FILTER_OPTION_RANGE_DAY:
            case TIME_FILTER_OPTION_RANGE_MONTH:
                if ($timeFilterOption == TIME_FILTER_OPTION_RANGE_DAY) {
                    $startDate = $startYear . $startMonth . $startDay;
                    $endDate = $endYear . $endMonth . $endDay;
                } else {
                    $startDate = $startYear . $startMonth;
                    $endDate = $endYear . $endMonth;
                }

                if ($startTime == $endTime) {
                    // The start and end date are the same
                    $filter[STATISTICS_DIMENSION_MONTH] = $startDate;
                } else {
                    $filter[STATISTICS_DIMENSION_DAY]['from'] = $startDate;
                    $filter[STATISTICS_DIMENSION_DAY]['to'] = $endDate;
                }
                break;
            default:
                break;
        }

        if ($this->getData('countries')) {
            $filter[STATISTICS_DIMENSION_COUNTRY] = $this->getData('countries');
        }

        if ($this->getData('regions')) {
            $filter[STATISTICS_DIMENSION_REGION] = $this->getData('regions');
        }

        if ($this->getData('cityNames')) {
            $cityNames = explode(',', (string) $this->getData('cityNames'));
            $filter[STATISTICS_DIMENSION_CITY] = $cityNames;
        }

        $orderBy = [];
        if ($this->getData('orderByColumn') && $this->getData('orderByDirection')) {
            $orderByColumn = $this->getData('orderByColumn');
            $orderByDirection = $this->getData('orderByDirection');

            $columnIndex = 0;
            if (is_array($orderByColumn)) {
                foreach ($orderByColumn as $column) {
                    if ($column != '0' && !isset($orderBy[$column])) {
                        $orderByDir = $orderByDirection[$columnIndex] ?? null;
                        if ($orderByDir == STATISTICS_ORDER_ASC || $orderByDir == STATISTICS_ORDER_DESC) {
                            $orderBy[$column] = $orderByDir;
                        }
                    }
                    $columnIndex++;
                }
            }
        }

        return StatisticsHelper::getReportUrl($request, $this->_metricType, $columns, $filter, $orderBy);
    }
}

?>
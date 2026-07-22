<?php
declare(strict_types=1);

/**
 * @file classes/plugins/ReportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReportPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for report plugins
 */

import('classes.plugins.Plugin');

class ReportPlugin extends Plugin {

    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReportPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Retrieve a range of aggregate, filtered, ordered metric values, i.e.
     * a statistics report.
     * @see <http://pkp.sfu.ca/wiki/index.php/OJSdeStatisticsConcept#Input_and_Output_Formats_.28Aggregation.2C_Filters.2C_Metrics_Data.29>
     * for a full specification of the input and output format of this method.
     * @param null|string|array $metricType
     * @param string|array $columns
     * @param array $filters
     * @param array $orderBy order
     * @param null|DBResultRange $range
     * @return null|array
     */
    public function getMetrics($metricType = null, $columns = [], $filters = [], $orderBy = [], $range = null) {
        return null;
    }

    /**
     * Metric types available from this plug-in.
     * @return array 
     */
    public function getMetricTypes() {
        return [];
    }

    /**
     * Public metric type that will be displayed to end users.
     * @param string $metricType
     * @return null|string 
     */
    public function getMetricDisplayType($metricType) {
        return null;
    }

    /**
     * Full name of the metric type.
     * @param string $metricType
     * @return null|string
     */
    public function getMetricFullName($metricType) {
        return null;
    }

    /**
     * Get the columns used in reports by the passed metric type.
     * @param string $metricType
     * @return array 
     */
    public function getColumns($metricType) {
        return [];
    }

    /**
     * Get optional columns that are not required for this report
     * to implement the passed metric type.
     * @param string $metricType
     * @return array 
     */
    public function getOptionalColumns($metricType) {
        return [];
    }

    /**
     * Get the object types that the passed metric type
     * counts statistics for.
     * @param string $metricType
     * @return null|array 
     */
    public function getObjectTypes($metricType) {
        return [];
    }

    /**
     * Get the default report templates that each report
     * plugin can implement, with an string to represent it.
     * Subclasses can override this method to add/remove
     * default formats.
     * @param string $metricType
     * @return array
     */
    public function getDefaultReportTemplates($metricType) {
        $reports = [];

        // Define aggregation columns, the ones that
        // can be part of the reports not changing
        // it's main purpose.
        $aggregationColumns = [
            STATISTICS_DIMENSION_COUNTRY,
            STATISTICS_DIMENSION_REGION,
            STATISTICS_DIMENSION_CITY,
            STATISTICS_DIMENSION_MONTH,
            STATISTICS_DIMENSION_DAY
        ];

        // Articles file downloads.
        $columns = [
            STATISTICS_DIMENSION_ASSOC_TYPE,
            STATISTICS_DIMENSION_ISSUE_ID,
            STATISTICS_DIMENSION_SUBMISSION_ID,
            STATISTICS_DIMENSION_MONTH,
            STATISTICS_DIMENSION_COUNTRY
        ];
        $filter = [STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_GALLEY];
        $reports[] = [
            'nameLocaleKey' => 'manager.statistics.reports.defaultReport.articleDownloads',
            'metricType' => $metricType, 
            'columns' => $columns, 
            'filter' => $filter,
            'aggregationColumns' => $aggregationColumns
        ];

        // Articles abstract views.
        $filter = [STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_ARTICLE];
        $reports[] = [
            'nameLocaleKey' => 'manager.statistics.reports.defaultReport.articleAbstract',
            'metricType' => $metricType, 
            'columns' => $columns, 
            'filter' => $filter,
            'aggregationColumns' => $aggregationColumns
        ];

        // Issues file downloads.
        $columns = [
            STATISTICS_DIMENSION_ASSOC_TYPE,
            STATISTICS_DIMENSION_ISSUE_ID,
            STATISTICS_DIMENSION_MONTH,
            STATISTICS_DIMENSION_COUNTRY
        ];
        $filter = [STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_ISSUE_GALLEY];
        $reports[] = [
            'nameLocaleKey' => 'manager.statistics.reports.defaultReport.issueDownloads',
            'metricType' => $metricType, 
            'columns' => $columns, 
            'filter' => $filter,
            'aggregationColumns' => $aggregationColumns
        ];

        // Issue table of contents page views.
        $filter = [STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_ISSUE];
        $reports[] = [
            'nameLocaleKey' => 'manager.statistics.reports.defaultReport.issueTableOfContents',
            'metricType' => $metricType, 
            'columns' => $columns, 
            'filter' => $filter,
            'aggregationColumns' => $aggregationColumns
        ];

        // Journal index page views.
        $columns = [
            STATISTICS_DIMENSION_ASSOC_TYPE,
            STATISTICS_DIMENSION_CONTEXT_ID,
            STATISTICS_DIMENSION_MONTH,
            STATISTICS_DIMENSION_COUNTRY
        ];
        $filter = [STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_JOURNAL];
        $reports[] = [
            'nameLocaleKey' => 'manager.statistics.reports.defaultReport.journalIndexPageViews',
            'metricType' => $metricType, 
            'columns' => $columns, 
            'filter' => $filter,
            'aggregationColumns' => $aggregationColumns
        ];

        return $reports;
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items to append.
     * @see PKPPageRouter::url() for generating the URLs for the breadcrumbs.
     * @param array $crumbs
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($crumbs = [], $isSubclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                Request::url(null, 'user'),
                'navigation.user'
            ],
            [
                Request::url(null, 'manager'),
                'user.role.manager'
            ],
            [
                Request::url(null, 'manager', 'reports'),
                'manager.statistics.reports'
            ]
        ];
        if ($isSubclass) $pageCrumbs[] = [
            Request::url(null, 'manager', 'reports', ['plugin', $this->getName()]),
            $this->getDisplayName(),
            true
        ];

        $templateMgr->assign('pageHierarchy', array_merge($pageCrumbs, $crumbs));
    }

    /**
     * Base method to display the report plugin UI.
     * @see ReportPlugin::display() for the supported verbs.
     * @param array $args
     * @param PKPRequest $request
     */
    public function display($args, $request) {
        $templateManager = TemplateManager::getManager();
        $templateManager->register_function('plugin_url', [$this, 'smartyPluginUrl']);
    }

    /**
     * Display verbs for the management interface.
     * @see ReportPlugin::getManagementVerbs() for the supported verbs.
     * @param array $verbs
     * @param PKPRequest $request
     * @return array 
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        return [
            [
                'reports',
                __('manager.statistics.reports')
            ]
        ];
    }

    /**
     * Perform management functions
     * @see ReportPlugin::manage() for the supported verbs.
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param PKPRequest|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        if ($verb === 'reports') {
            Request::redirect(null, 'manager', 'report', $this->getName());
        }
        return false;
    }

    /**
     * Extend the {url ...} smarty to support reporting plugins.
     * @see PKPPageRouter::url() for the supported parameters.
     * @param array $params
     * @param $smarty Smarty
     * @return string
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = ['plugin', $this->getName()];
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [$params['path']]);
        } else {
            $params['path'] = $path;
        }
        return $smarty->smartyUrl($params, $smarty);
    }
    
}
?>
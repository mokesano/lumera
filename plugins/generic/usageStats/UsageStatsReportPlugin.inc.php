<?php
declare(strict_types=1);

/**
 * @file plugins/generic/usageStats/UsageStatsReportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsReportPlugin
 * @ingroup plugins_generic_usageStats
 *
 * @brief Default statistics report plugin (and metrics provider)
 */

import('classes.plugins.ReportPlugin');

define('OJS_METRIC_TYPE_COUNTER', 'ojs::counter');

class UsageStatsReportPlugin extends ReportPlugin {

    /**
     * Register the plugin.
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @return bool True on success
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin.
     * @see PKPPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'UsageStatsReportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.reports.usageStats.report.displayName');
    }

    /**
     * Get the description of this plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.reports.usageStats.report.description');
    }

    /**
     * Display the report.
     * @see ReportPlugin::display()
     * @param array $args
     * @param PKPRequest $request
     */
    public function display($args, $request) {
        parent::display($args, $request);
        // Removed & reference
        $journal = Request::getJournal();

        $reportArgs = array(
            'metricType' => OJS_METRIC_TYPE_COUNTER,
            'columns' => array(
                STATISTICS_DIMENSION_ASSOC_ID, STATISTICS_DIMENSION_ASSOC_TYPE, STATISTICS_DIMENSION_CONTEXT_ID,
                STATISTICS_DIMENSION_ISSUE_ID, STATISTICS_DIMENSION_MONTH, STATISTICS_DIMENSION_COUNTRY),
            'filters' => serialize(array(STATISTICS_DIMENSION_CONTEXT_ID => $journal->getId())),
            'orderBy' => serialize(array(STATISTICS_DIMENSION_MONTH => STATISTICS_ORDER_ASC))
        );
        Request::redirect(null, null, 'generateReport', null, $reportArgs);
    }

    /**
     * Get metrics data.
     * @see ReportPlugin::getMetrics()
     * @param string|array $metricType
     * @param array $columns
     * @param array $filters
     * @param array $orderBy
     * @param DBResultRange $range
     * @return DAOResultFactory|null
     */
    public function getMetrics($metricType = null, $columns = null, $filters = null, $orderBy = null, $range = null) {
        // Validate the metric type.
        if (!(is_scalar($metricType) || count($metricType) === 1)) return null;
        if (is_array($metricType)) $metricType = array_pop($metricType);
        if ($metricType !== OJS_METRIC_TYPE_COUNTER) return null;

        // This plug-in uses the MetricsDAO to store metrics. So we simply
        // delegate there.
        // Removed & reference
        $metricsDao = DAORegistry::getDAO('MetricsDAO'); /* @var $metricsDao MetricsDAO */
        return $metricsDao->getMetrics($metricType, $columns, $filters, $orderBy, $range);
    }

    /**
     * Get the available metric types.
     * @see ReportPlugin::getMetricTypes()
     * @return array
     */
    public function getMetricTypes() {
        return array(OJS_METRIC_TYPE_COUNTER);
    }

    /**
     * Get the display type of a metric.
     * @see ReportPlugin::getMetricDisplayType()
     * @param string $metricType
     * @return string|null
     */
    public function getMetricDisplayType($metricType) {
        if ($metricType !== OJS_METRIC_TYPE_COUNTER) return null;
        return __('plugins.reports.usageStats.metricType');
    }

    /**
     * Get the full name of a metric.
     * @see ReportPlugin::getMetricFullName()
     * @param string $metricType
     * @return string|null
     */
    public function getMetricFullName($metricType) {
        // Wizdam Fix: Typo OAS_METRIC_TYPE_COUNTER -> OJS_METRIC_TYPE_COUNTER
        if ($metricType !== OJS_METRIC_TYPE_COUNTER) return null;
        return __('plugins.reports.usageStats.metricType.full');
    }

    /**
     * Get the columns for a given metric type.
     * @see ReportPlugin::getColumns()
     * @param string $metricType
     * @return array
     */
    public function getColumns($metricType) {
        if ($metricType !== OJS_METRIC_TYPE_COUNTER) return array();
        return array(
            STATISTICS_DIMENSION_ASSOC_ID,
            STATISTICS_DIMENSION_ASSOC_TYPE,
            STATISTICS_DIMENSION_SUBMISSION_ID,
            STATISTICS_DIMENSION_ISSUE_ID,
            STATISTICS_DIMENSION_CONTEXT_ID,
            STATISTICS_DIMENSION_CITY,
            STATISTICS_DIMENSION_REGION,
            STATISTICS_DIMENSION_COUNTRY,
            STATISTICS_DIMENSION_DAY,
            STATISTICS_DIMENSION_MONTH,
            STATISTICS_DIMENSION_FILE_TYPE,
            STATISTICS_METRIC
        );
    }

    /**
     * Get the object types for a given metric type.
     * @see ReportPlugin::getObjectTypes()
     * @param string $metricType
     * @return array
     */
    public function getObjectTypes($metricType) {
        if ($metricType !== OJS_METRIC_TYPE_COUNTER) return array();
        return array(
            ASSOC_TYPE_JOURNAL,
            ASSOC_TYPE_ISSUE,
            ASSOC_TYPE_ISSUE_GALLEY,
            ASSOC_TYPE_ARTICLE,
            ASSOC_TYPE_GALLEY
        );
    }

    /**
     * Get the optional columns for a given metric type.
     * @see ReportPlugin::getOptionalColumns()
     * @param string $metricType
     * @return array
     */
    public function getOptionalColumns($metricType) {
        if ($metricType !== OJS_METRIC_TYPE_COUNTER) return array();
        return array(
            STATISTICS_DIMENSION_CITY,
            STATISTICS_DIMENSION_REGION
        );
    }
}

?>
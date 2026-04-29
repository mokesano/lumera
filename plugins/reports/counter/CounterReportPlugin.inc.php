<?php
declare(strict_types=1);

/**
 * @file plugins/reports/counter/CounterReportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CounterReportPlugin
 * @ingroup plugins_reports_counter
 *
 * @brief Counter report plugin
 */

define('OJS_METRIC_TYPE_LEGACY_COUNTER', 'ojs::legacyCounterPlugin');
define('COUNTER_CLASS_SUFFIX', '.inc.php');

import('classes.plugins.ReportPlugin');
import('plugins.reports.counter.classes.CounterReport');

class CounterReportPlugin extends ReportPlugin {

    /**
     * @see PKPPlugin::register($category, $path)
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);

        if($success) {
            $this->addLocaleData();
        }
        return $success;
    }

    /**
     * @see PKPPlugin::getLocaleFilename($locale)
     */
    public function getLocaleFilename($locale) {
        $localeFilenames = parent::getLocaleFilename($locale);

        // Add dynamic locale keys.
        foreach (glob($this->getPluginPath() . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . '*.xml') as $file) {
            if (!in_array($file, $localeFilenames)) {
                $localeFilenames[] = $file;
            }
        }

        return $localeFilenames;
    }

    /**
     * @see PKPPlugin::getName()
     */
    public function getName(): string {
        return 'CounterReportPlugin';
    }

    /**
     * @see PKPPlugin::getDisplayName()
     */
    public function getDisplayName(): string {
        return __('plugins.reports.counter');
    }

    /**
     * @see PKPPlugin::getDescription()
     */
    public function getDescription(): string {
        return __('plugins.reports.counter.description');
    }

    /**
     * @see PKPPlugin::getTemplatePath()
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath($inCore) . 'templates/';
    }      

    /**
     * Get the latest counter release
     * @return string
     */
    public function getCurrentRelease() {
        return '4.1';
    }
    
    /**
     * List the valid reports
     * Must exist in the report path as {Report}_r{release}.inc.php
     * @return array multidimentional array release => array( report => reportClassName )
     */
    public function getValidReports() {
        $reports = [];
        // COUNTER_CLASS_PREFIX is likely defined in CounterReport or a parent/bootstrap file. 
        // Assuming it's available or defined in imported files.
        // If it's a constant from CounterReport, it might need CounterReport::PREFIX, 
        // but sticking to legacy usage patterns for global constants if they exist.
        // Based on typical OJS counter plugin, this constant is usually 'CounterReport'.
        $prefix = $this->getReportPath() . DIRECTORY_SEPARATOR . COUNTER_CLASS_PREFIX;
        $suffix = COUNTER_CLASS_SUFFIX;
        foreach (glob($prefix.'*'.$suffix) as $file) {
            $report_name = substr($file, strlen($prefix), -strlen($suffix));
            $report_class_file = substr($file, strlen($prefix), -strlen(COUNTER_CLASS_SUFFIX));
            $reports[$report_name] = $report_class_file;
        }
        return $reports;
    }

    /**
     * Get a COUNTER Reporter Object
     * Must exist in the report path as {Report}_r{release}.inc.php
     * @param string $report Report name
     * @param string $release release identifier
     * @return object|bool
     */
    public function getReporter($report, $release) {
        $reportClass = COUNTER_CLASS_PREFIX . $report;
        $reportClasspath = 'plugins.reports.counter.classes.reports.';
        $reportPath = str_replace('.', DIRECTORY_SEPARATOR, $reportClasspath);
        if (file_exists($reportPath . $reportClass . COUNTER_CLASS_SUFFIX)) {
            import($reportPath . $reportClass);
            $reporter = new $reportClass('reports', $this->getName(), $release);
            return $reporter;
        }
        return false;
    }

    /**
     * Get classes path for this plugin.
     * @return string Path to plugin's classes
     */
    public function getClassPath() {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'classes';
    }
    

    /**
     * Return the report path
     * @return string
     */
    public function getReportPath() {
        return $this->getClassPath() . DIRECTORY_SEPARATOR . 'reports';
    }

    /**
     * @see ReportPlugin::setBreadcrumbs()
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
                Request::url(null, 'manager', 'statistics'),
                'manager.statistics'
            ]
        ];

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * @see ReportPlugin::display()
     */
    public function display($args, $request) {
        parent::display($args, $request);
        // We need these constants
        import('classes.statistics.StatisticsHelper');

        $this->setBreadcrumbs();
        $available = $this->getValidReports();
        $years = $this->_getYears();
        
        if ($request->getUserVar('type')) {
            $type = (string) $request->getUserVar('type');
            $errormessage = '';
            switch ($type) {
                case 'report':
                case 'reportxml':
                    // Legacy COUNTER Release 3
                    if (!Validation::isSiteAdmin()) {
                        // Legacy reports are site-wide
                        Validation::redirectLogin();
                    }
                    import('plugins.reports.counter.classes.LegacyJR1');
                    $r3jr1 = new LegacyJR1($this->getTemplatePath());
                    $r3jr1->display($request);
                    return;
                case 'fetch':
                    // Modern COUNTER Releases
                    // must provide a release, report, and year parameter
                    $release = (string) $request->getUserVar('release');
                    $report = (string) $request->getUserVar('report');
                    $year = (string) $request->getUserVar('year');
                    
                    if ($release && $report && $year) {
                        // release, report and year parameters must be sane
                        if ($release == $this->getCurrentRelease() && isset($available[$report]) && in_array($year, $years)) {
                            // try to get the report
                            $reporter = $this->getReporter($report, $release);
                            if ($reporter) {
                                // default report parameters with a yearlong range
                                $reportItems = $reporter->getReportItems([], [STATISTICS_DIMENSION_MONTH => ['from' => $year.'01', 'to' => $year.'12']]);
                                if ($reportItems) {
                                    $xmlResult = $reporter->createXML($reportItems);
                                    if ($xmlResult) {
                                        header('content-type: text/xml');
                                        header('content-disposition: attachment; filename=counter-'. $release . '-' . $report . '-' . date('Ymd') . '.xml');
                                        print $xmlResult;
                                        return;
                                    } else {
                                        $errormessage = __('plugins.reports.counter.error.noXML');
                                    }
                                } else {
                                    $errormessage = __('plugins.reports.counter.error.noResults');
                                }
                            }
                        }
                    }
                    // fall through to default case with error message
                    if (!$errormessage) {
                        $errormessage = __('plugins.reports.counter.error.badParameters');
                    }
                    // No break needed due to fall-through logic in legacy code, but modern standards prefer cleaner flow. 
                    // However, we preserve the "fall through" logic to the default case for error handling.
                    // goto default_error; // Or just let it fall through if logic dictates.
                    
                default:
                    if (!$errormessage) {
                        $errormessage = __('plugins.reports.counter.error.badRequest');
                    }
                    $user = Request::getUser();
                    import('classes.notification.NotificationManager');
                    $notificationManager = new NotificationManager();
                    $notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_ERROR, ['contents' => $errormessage]);
            }
        }
        $legacyYears = $this->_getYears(true);
        $templateManager = TemplateManager::getManager();
        krsort($available);
        $templateManager->assign('available', $available);
        $templateManager->assign('release', $this->getCurrentRelease());
        $templateManager->assign('years', $years);
        // legacy reports are site-wide, so only site admins have access
        $templateManager->assign('showLegacy', Validation::isSiteAdmin());
        if (!empty($legacyYears)) $templateManager->assign('legacyYears', $legacyYears);
        $templateManager->display($this->getTemplatePath() . 'index.tpl');
    }

    /**
     * Get the years for which log entries exist in the DB.
     * @param bool $useLegacyStats Use the old counter plugin data.
     * @return array
     */
    private function _getYears($useLegacyStats = false) {
        if ($useLegacyStats) {
            $metricType = OJS_METRIC_TYPE_LEGACY_COUNTER;
            $filter = [];
        } else {
            $metricType = OJS_METRIC_TYPE_COUNTER;
            $filter = [STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_GALLEY];
        }
        $metricsDao = DAORegistry::getDAO('MetricsDAO'); /* @var $metricsDao MetricsDAO */
        $results = $metricsDao->getMetrics($metricType, [STATISTICS_DIMENSION_MONTH], $filter);
        $years = [];
        foreach($results as $record) {
            $year = substr($record['month'], 0, 4);
            if (in_array($year, $years)) continue;
            $years[] = $year;
        }

        return $years;
    }

}
?>
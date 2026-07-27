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
 * @brief Counter report plugin.
 */

define('OJS_METRIC_TYPE_LEGACY_COUNTER', 'ojs::legacyCounterPlugin');
define('COUNTER_CLASS_SUFFIX', '.inc.php');

if (!defined('COUNTER_CLASS_PREFIX')) {
    define('COUNTER_CLASS_PREFIX', 'CounterReport');
}

import('classes.plugins.ReportPlugin');
import('plugins.reports.counter.classes.CounterReport');

class CounterReportPlugin extends ReportPlugin {

    /**
     * Register the plugin.
     * @see PKPPlugin::register()
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path);

        if ($success) {
            $this->addLocaleData();
        }
        return $success;
    }

    /**
     * Get the locale filename.
     * @see PKPPlugin::getLocaleFilename()
     * @param string $locale
     * @return array
     */
    public function getLocaleFilename($locale) {
        $localeFilenames = parent::getLocaleFilename($locale);

        // Add dynamic locale keys.
        $globPattern = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . '*.xml';
        $files = glob($globPattern);
        if (is_array($files)) {
            foreach ($files as $file) {
                if (!in_array($file, $localeFilenames, true)) {
                    $localeFilenames[] = $file;
                }
            }
        }

        return $localeFilenames;
    }

    /**
     * Get the name of this plugin.
     * @see PKPPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'CounterReportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.reports.counter');
    }

    /**
     * Get the description of this plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.reports.counter.description');
    }

    /**
     * Get the template path.
     * @see PKPPlugin::getTemplatePath()
     * @param bool $inCore
     * @return string
     */
    public function getTemplatePath($inCore = false): string {
        return parent::getTemplatePath() . 'templates/';
    }      

    /**
     * Get the latest counter release.
     * @return string
     */
    public function getCurrentRelease() {
        return '4.1';
    }
    
    /**
     * List the valid reports.
     * Must exist in the report path as {Report}_r{release}.inc.php
     * @return array
     */
    public function getValidReports() {
        $reports = [];
        $prefix = $this->getReportPath() . DIRECTORY_SEPARATOR . COUNTER_CLASS_PREFIX;
        $suffix = COUNTER_CLASS_SUFFIX;
        
        $globPattern = $prefix . '*' . $suffix;
        $files = glob($globPattern);
        if (is_array($files)) {
            foreach ($files as $file) {
                $reportName = substr($file, strlen($prefix), -strlen($suffix));
                $reportClassFile = substr($file, strlen($prefix), -strlen(COUNTER_CLASS_SUFFIX));
                $reports[$reportName] = $reportClassFile;
            }
        }
        return $reports;
    }

    /**
     * Get a COUNTER Reporter Object.
     * Must exist in the report path as {Report}_r{release}.inc.php
     * @param string $report
     * @param string $release
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
     * @return string
     */
    public function getClassPath() {
        return $this->getPluginPath() . DIRECTORY_SEPARATOR . 'classes';
    }
    
    /**
     * Return the report path.
     * @return string
     */
    public function getReportPath() {
        return $this->getClassPath() . DIRECTORY_SEPARATOR . 'reports';
    }

    /**
     * Set the page breadcrumbs.
     * @see ReportPlugin::setBreadcrumbs()
     * @param array $crumbs
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($crumbs = [], $isSubclass = false) {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user'
            ],
            [
                $request->url(null, 'manager'),
                'user.role.manager'
            ],
            [
                $request->url(null, 'manager', 'statistics'),
                'manager.statistics'
            ]
        ];

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display the plugin.
     * @see ReportPlugin::display()
     * @param array $args
     * @param mixed $request
     */
    public function display($args, $request) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        parent::display($args, $request);
        
        // We need these constants
        import('classes.statistics.StatisticsHelper');

        $this->setBreadcrumbs();
        $available = $this->getValidReports();
        $years = $this->_getYears();
        
        $type = $request->getUserVar('type');
        if ($type) {
            $type = (string) $type;
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
                    $release = (string) $request->getUserVar('release');
                    $report = (string) $request->getUserVar('report');
                    $year = (string) $request->getUserVar('year');
                    
                    if ($release !== '' && $report !== '' && $year !== '') {
                        // release, report and year parameters must be sane
                        if ($release === $this->getCurrentRelease() && isset($available[$report]) && in_array($year, $years, true)) {
                            // try to get the report
                            $reporter = $this->getReporter($report, $release);
                            if ($reporter) {
                                // default report parameters with a yearlong range
                                $reportItems = $reporter->getReportItems([], [STATISTICS_DIMENSION_MONTH => ['from' => $year . '01', 'to' => $year . '12']]);
                                if ($reportItems) {
                                    $xmlResult = $reporter->createXML($reportItems);
                                    if ($xmlResult) {
                                        header('Content-Type: text/xml');
                                        header('Content-Disposition: attachment; filename=counter-' . $release . '-' . $report . '-' . date('Ymd') . '.xml');
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
                    if ($errormessage === '') {
                        $errormessage = __('plugins.reports.counter.error.badParameters');
                    }
                    // Intentional fall-through to default to display error notification
                    // no break;
                default:
                    if ($errormessage === '') {
                        $errormessage = __('plugins.reports.counter.error.badRequest');
                    }
                    $user = $request->getUser();
                    if ($user) {
                        import('classes.notification.NotificationManager');
                        $notificationManager = new NotificationManager();
                        $notificationManager->createTrivialNotification((int) $user->getId(), NOTIFICATION_TYPE_ERROR, ['contents' => $errormessage]);
                    }
            }
        }
        $legacyYears = $this->_getYears(true);
        $templateManager = TemplateManager::getManager($request);
        krsort($available);
        $templateManager->assign('available', $available);
        $templateManager->assign('release', $this->getCurrentRelease());
        $templateManager->assign('years', $years);
        $templateManager->assign('showLegacy', Validation::isSiteAdmin());
        if (!empty($legacyYears)) {
            $templateManager->assign('legacyYears', $legacyYears);
        }
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
        /** @var MetricsDAO $metricsDao */
        $metricsDao = DAORegistry::getDAO('MetricsDAO');
        $results = $metricsDao->getMetrics($metricType, [STATISTICS_DIMENSION_MONTH], $filter);
        $years = [];
        if (is_array($results)) {
            foreach ($results as $record) {
                $year = substr((string) $record['month'], 0, 4);
                if (!in_array($year, $years, true)) {
                    $years[] = $year;
                }
            }
        }

        return $years;
    }

}
?>
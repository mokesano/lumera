<?php
declare(strict_types=1);

/**
 * @file plugins/reports/timedView/TimedViewReportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TimedViewReportPlugin
 * @ingroup plugins_reports_timedView
 *
 * @brief Timed View report plugin
 */

define('TIMED_VIEW_REPORT_YEAR_OFFSET_PAST', '-20');
define('TIMED_VIEW_REPORT_YEAR_OFFSET_FUTURE', '+0');
define('OJS_METRIC_TYPE_TIMED_VIEWS', 'ojs::timedViews');

import('classes.plugins.ReportPlugin');

class TimedViewReportPlugin extends ReportPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TimedViewReportPlugin() {
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
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path The path the plugin was found in
     * @param int|null $mainContextId
     * @return bool True if plugin initialized successfully; if false, the plugin will not be registered.
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);

        if($success) {
            $this->import('TimedViewReportForm');
            $this->addLocaleData();
        }
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'TimedViewReportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.reports.timedView.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.reports.timedView.description');
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items
     * to append.
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
                Request::url(null, 'manager', 'statistics'),
                'manager.statistics'
            ]
        ];

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display the report.
     * @param array $args
     * @param PKPRequest $request
     */
    public function display($args, $request) {
        parent::display($args, $request);
        $this->setBreadcrumbs();

        $form = new TimedViewReportForm($this);

        if ($request->getUserVar('generate')) {
            $form->readInputData();
            if ($form->validate()) {
                $form->execute($request);
            } else {
                $form->display();
            }
        } elseif ($request->getUserVar('clearLogs')) {
            $dateClear = date('Ymd', mktime(
                0, 0, 0, 
                (int) $request->getUserVar('dateClearMonth'), 
                (int) $request->getUserVar('dateClearDay'), 
                (int) $request->getUserVar('dateClearYear')
            ));
            $journal = $request->getJournal();
            $metricsDao = DAORegistry::getDAO('MetricsDAO'); /* @var $metricsDao MetricsDAO */
            $metricsDao->purgeRecords(OJS_METRIC_TYPE_TIMED_VIEWS, $dateClear);
            $form->display();
        } else {
            $form->initData();
            $form->display();
        }
    }
}

?>
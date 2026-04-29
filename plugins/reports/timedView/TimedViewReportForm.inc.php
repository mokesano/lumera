<?php
declare(strict_types=1);

/**
 * @file plugins/generic/timedView/TimedViewReportForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TimedViewReportForm
 */

import('lib.pkp.classes.form.Form');

class TimedViewReportForm extends Form {

    /**
     * Constructor
     * @param object $plugin
     */
    public function __construct($plugin) {
        parent::__construct($plugin->getTemplatePath() . 'timedViewReportForm.tpl');

        // Start date is provided and is valid
        $this->addCheck(new FormValidator($this, 'dateStartYear', 'required', 'plugins.reports.timedView.form.dateStartRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateStartYear', 
            'required', 
            'plugins.reports.timedView.form.dateStartValid', 
            function($dateStartYear) {
                $minYear = (int) date('Y') + TIMED_VIEW_REPORT_YEAR_OFFSET_PAST;
                $maxYear = (int) date('Y') + TIMED_VIEW_REPORT_YEAR_OFFSET_FUTURE;
                return ($dateStartYear >= $minYear && $dateStartYear <= $maxYear);
            }
        ));

        $this->addCheck(new FormValidator($this, 'dateStartMonth', 'required', 'plugins.reports.timedView.form.dateStartRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateStartMonth', 
            'required', 
            'plugins.reports.timedView.form.dateStartValid', 
            function($dateStartMonth) {
                return ($dateStartMonth >= 1 && $dateStartMonth <= 12);
            }
        ));

        $this->addCheck(new FormValidator($this, 'dateStartDay', 'required', 'plugins.reports.timedView.form.dateStartRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateStartDay', 
            'required', 
            'plugins.reports.timedView.form.dateStartValid', 
            function($dateStartDay) {
                return ($dateStartDay >= 1 && $dateStartDay <= 31);
            }
        ));

        // End date is provided and is valid
        $this->addCheck(new FormValidator($this, 'dateEndYear', 'required', 'plugins.reports.timedView.form.dateEndRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateEndYear', 
            'required', 
            'plugins.reports.timedView.form.dateEndValid', 
            function($dateEndYear) {
                $minYear = (int) date('Y') + TIMED_VIEW_REPORT_YEAR_OFFSET_PAST;
                $maxYear = (int) date('Y') + TIMED_VIEW_REPORT_YEAR_OFFSET_FUTURE;
                return ($dateEndYear >= $minYear && $dateEndYear <= $maxYear);
            }
        ));

        $this->addCheck(new FormValidator($this, 'dateEndMonth', 'required', 'plugins.reports.timedView.form.dateEndRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateEndMonth', 
            'required', 
            'plugins.reports.timedView.form.dateEndValid', 
            function($dateEndMonth) {
                return ($dateEndMonth >= 1 && $dateEndMonth <= 12);
            }
        ));

        $this->addCheck(new FormValidator($this, 'dateEndDay', 'required', 'plugins.reports.timedView.form.dateEndRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateEndDay', 
            'required', 
            'plugins.reports.timedView.form.dateEndValid', 
            function($dateEndDay) {
                return ($dateEndDay >= 1 && $dateEndDay <= 31);
            }
        ));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TimedViewReportForm($plugin) {
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
     * Display the form.
     * @param PKPRequest|null $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        
        $templateMgr->assign('yearOffsetPast', TIMED_VIEW_REPORT_YEAR_OFFSET_PAST);
        $templateMgr->assign('yearOffsetFuture', TIMED_VIEW_REPORT_YEAR_OFFSET_FUTURE);

        parent::display($request, $template);
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(['dateStartYear', 'dateStartMonth', 'dateStartDay', 'dateEndYear', 'dateEndMonth', 'dateEndDay', 'useTimedViewRecords']);

        $this->_data['dateStart'] = date('Ymd', mktime(0, 0, 0, (int) $this->_data['dateStartMonth'], (int) $this->_data['dateStartDay'], (int) $this->_data['dateStartYear']));
        $this->_data['dateEnd'] = date('Ymd', mktime(0, 0, 0, (int) $this->_data['dateEndMonth'], (int) $this->_data['dateEndDay'], (int) $this->_data['dateEndYear']));
    }

    /**
     * Save subscription.
     * @param object|null $object
     */
    public function execute($object = null) {
        $request = Application::get()->getRequest();
        $router = $request->getRouter();
        $journal = $router->getContext($request);
        $journalId = $journal->getId();

        $dateStart = $this->getData('dateStart');
        $dateEnd = $this->getData('dateEnd');
        if ($this->getData('useTimedViewRecords')) {
            $metricType = OJS_METRIC_TYPE_TIMED_VIEWS;
        } else {
            $metricType = OJS_METRIC_TYPE_COUNTER;
        }

        import('lib.pkp.classes.db.DBResultRange');
        $dbResultRange = new DBResultRange(STATISTICS_MAX_ROWS);

        $metricsDao = DAORegistry::getDAO('MetricsDAO'); /* @var $metricsDao MetricsDAO */
        $columns = [STATISTICS_DIMENSION_ASSOC_ID, STATISTICS_DIMENSION_ASSOC_TYPE, STATISTICS_DIMENSION_SUBMISSION_ID];
        $filter = [
            STATISTICS_DIMENSION_ASSOC_TYPE => [ASSOC_TYPE_ARTICLE, ASSOC_TYPE_GALLEY],
            STATISTICS_DIMENSION_CONTEXT_ID => $journalId
        ];
        if ($dateStart && $dateEnd) {
            $filter[STATISTICS_DIMENSION_DAY] = ['from' => $dateStart, 'to' => $dateEnd];
        }

        // Need to consider paging of stats records for databases with
        // large amount of statistics data.
        $allReportStats = [];

        // While we still have stats records about article abstract views,
        // keep adding them to the total.
        while (true) {
            $reportStats = $metricsDao->getMetrics($metricType, $columns, $filter,
                [
                    STATISTICS_DIMENSION_SUBMISSION_ID => STATISTICS_ORDER_ASC,
                    STATISTICS_DIMENSION_ASSOC_TYPE => STATISTICS_ORDER_ASC
                ],
                $dbResultRange
            );

            $allReportStats = array_merge($allReportStats, $reportStats);
            $dbResultRange->setPage($dbResultRange->getPage() + 1);

            // It means we don't have more pages to fetch.
            if (count($reportStats) < $dbResultRange->getCount()) break;
        }

        // Format stats and retrieve submission and galleys info.
        list($articleData, $galleyLabels, $galleyViews) = $this->_formatStats($allReportStats);
        $this->_buildReport($articleData, $galleyLabels, $galleyViews);
    }

    /**
     * Return report statistics already formatted in columns
     * to generate the report.
     * @param array $reportStats All metric records retrieved with MetricsDAO::getMetrics()
     * @return array
     */
    public function _formatStats($reportStats) {
        $articleData = $galleyLabels = $galleyViews = [];

        $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $publishedArticleDao PublishedArticleDAO */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $galleyDao ArticleGalleyDAO */

        // Use array_values to ensure numerical indexing for the for-loop
        $reportStats = array_values($reportStats);
        $count = count($reportStats);

        for ($i = 0; $i < $count; $i++) {
            $record = $reportStats[$i];
            $articleId = $record[STATISTICS_DIMENSION_SUBMISSION_ID];

            // Retrieve article and galleys data related to the
            // working article id.
            $assocType = $record[STATISTICS_DIMENSION_ASSOC_TYPE];

            // Retrieve article data, if it wasn't before.
            if (!isset($articleData[$articleId])) {
                $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($articleId, null, true);
                if (!$publishedArticle) continue;
                $issueId = $publishedArticle->getIssueId();
                $issue = $issueDao->getIssueById($issueId, null, true);

                if ($assocType == ASSOC_TYPE_ARTICLE) {
                    $abstractViews = $record[STATISTICS_METRIC];
                } else {
                    $abstractViews = '';
                }

                $articleData[$articleId] = [
                    'id' => $articleId,
                    'title' => $publishedArticle->getLocalizedTitle(),
                    'issue' => $issue->getIssueIdentification(),
                    'datePublished' => $publishedArticle->getDatePublished(),
                    'totalAbstractViews' => $abstractViews
                ];
            }

            // Retrieve galley data.
            if ($assocType == ASSOC_TYPE_GALLEY) {
                if (!isset($galleyViews[$articleId])) {
                    $galleyViews[$articleId] = [];
                }
                $galleyId = $record[STATISTICS_DIMENSION_ASSOC_ID];
                $galley = $galleyDao->getGalley($galleyId, null, true);
                if ($galley) {
                    $label = $galley->getLabel();
                    $idx = array_search($label, $galleyLabels);
                    if ($idx === false) {
                        $idx = count($galleyLabels);
                        $galleyLabels[] = $label;
                    }

                    // Make sure the array is the same size as in previous iterations
                    $galleyViews[$articleId] = array_pad($galleyViews[$articleId], count($galleyLabels), '');

                    $views = $record[STATISTICS_METRIC];
                    $galleyViews[$articleId][$idx] = $views;
                    
                    if (!isset($galleyViewTotal)) $galleyViewTotal = 0;
                    $galleyViewTotal += $views;
                }
            }

            // Peek next record to see if article changes
            $nextRecord = ($i + 1 < $count) ? $reportStats[$i + 1] : null;
            if ($nextRecord) {
                $nextArticleId = $nextRecord[STATISTICS_DIMENSION_SUBMISSION_ID];
            } else {
                $nextArticleId = null;
            }

            if ($nextArticleId != $articleId) {
                // Finished getting data for all objects related to the
                // working article id.
                // Add the galleys total downloads.
                if (isset($articleData[$articleId]) && isset($galleyViewTotal)) {
                    $articleData[$articleId]['galleyViews'] = $galleyViewTotal;
                }

                // Clean up.
                unset($galleyViewTotal);
            }
        }

        return [$articleData, $galleyLabels, $galleyViews];
    }

    /**
     * Build the report using the passed data.
     * @param array $articleData Title, journal, data, abstract views, etc.
     * @param array $galleyLabels All galley labels to be used as columns.
     * @param array $galleyViews All galley views per label.
     */
    public function _buildReport($articleData, $galleyLabels, $galleyViews) {
        header('content-type: text/comma-separated-values');
        header('content-disposition: attachment; filename=report.csv');
        $fp = fopen('php://output', 'wt');
        $reportColumns = [
            __('plugins.reports.timedView.report.articleId'),
            __('plugins.reports.timedView.report.articleTitle'),
            __('issue.issue'),
            __('plugins.reports.timedView.report.datePublished'),
            __('plugins.reports.timedView.report.abstractViews'),
            __('plugins.reports.timedView.report.galleyViews'),
        ];

        fputcsv($fp, array_merge($reportColumns, $galleyLabels));

        $dateFormatShort = Config::getVar('general', 'date_format_short');
        foreach ($articleData as $articleId => $article) {
            if (isset($galleyViews[$articleId])) {
                fputcsv($fp, array_merge($articleData[$articleId], $galleyViews[$articleId]));
            } else {
                fputcsv($fp, $articleData[$articleId]);
            }
        }

        fclose($fp);
    }
}
?>
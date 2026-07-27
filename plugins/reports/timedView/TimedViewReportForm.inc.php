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
 * @ingroup plugins_generic_timedView
 *
 * @brief Form for generating the timed view report.
 */

import('lib.pkp.classes.form.Form');

class TimedViewReportForm extends Form {

    /**
     * Constructor.
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
                return ((int) $dateStartYear >= $minYear && (int) $dateStartYear <= $maxYear);
            }
        ));

        $this->addCheck(new FormValidator($this, 'dateStartMonth', 'required', 'plugins.reports.timedView.form.dateStartRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateStartMonth', 
            'required', 
            'plugins.reports.timedView.form.dateStartValid', 
            function($dateStartMonth) {
                return ((int) $dateStartMonth >= 1 && (int) $dateStartMonth <= 12);
            }
        ));

        $this->addCheck(new FormValidator($this, 'dateStartDay', 'required', 'plugins.reports.timedView.form.dateStartRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateStartDay', 
            'required', 
            'plugins.reports.timedView.form.dateStartValid', 
            function($dateStartDay) {
                return ((int) $dateStartDay >= 1 && (int) $dateStartDay <= 31);
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
                return ((int) $dateEndYear >= $minYear && (int) $dateEndYear <= $maxYear);
            }
        ));

        $this->addCheck(new FormValidator($this, 'dateEndMonth', 'required', 'plugins.reports.timedView.form.dateEndRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateEndMonth', 
            'required', 
            'plugins.reports.timedView.form.dateEndValid', 
            function($dateEndMonth) {
                return ((int) $dateEndMonth >= 1 && (int) $dateEndMonth <= 12);
            }
        ));

        $this->addCheck(new FormValidator($this, 'dateEndDay', 'required', 'plugins.reports.timedView.form.dateEndRequired'));
        $this->addCheck(new FormValidatorCustom(
            $this, 
            'dateEndDay', 
            'required', 
            'plugins.reports.timedView.form.dateEndValid', 
            function($dateEndDay) {
                return ((int) $dateEndDay >= 1 && (int) $dateEndDay <= 31);
            }
        ));

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param object $plugin
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
     * @see Form::display()
     * @param mixed $request
     * @param string|null $template
     */
    public function display($request = null, $template = null) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('yearOffsetPast', TIMED_VIEW_REPORT_YEAR_OFFSET_PAST);
        $templateMgr->assign('yearOffsetFuture', TIMED_VIEW_REPORT_YEAR_OFFSET_FUTURE);

        parent::display($request, $template);
    }

    /**
     * Assign form data to user-submitted data.
     * @see Form::readInputData()
     */
    public function readInputData() {
        $this->readUserVars(['dateStartYear', 'dateStartMonth', 'dateStartDay', 'dateEndYear', 'dateEndMonth', 'dateEndDay', 'useTimedViewRecords']);

        $this->_data['dateStart'] = date('Ymd', mktime(0, 0, 0, (int) ($this->_data['dateStartMonth'] ?? 1), (int) ($this->_data['dateStartDay'] ?? 1), (int) ($this->_data['dateStartYear'] ?? date('Y'))));
        $this->_data['dateEnd'] = date('Ymd', mktime(0, 0, 0, (int) ($this->_data['dateEndMonth'] ?? 1), (int) ($this->_data['dateEndDay'] ?? 1), (int) ($this->_data['dateEndYear'] ?? date('Y'))));
    }

    /**
     * Save subscription and generate report.
     * @see Form::execute()
     * @param mixed $object Ignored.
     */
    public function execute($object = null) {
        // Lumera Singleton Fallback
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        if (!$journal) {
            return;
        }
        $journalId = (int) $journal->getId();

        $dateStart = (string) $this->getData('dateStart');
        $dateEnd = (string) $this->getData('dateEnd');
        $metricType = $this->getData('useTimedViewRecords') ? OJS_METRIC_TYPE_TIMED_VIEWS : OJS_METRIC_TYPE_COUNTER;

        import('lib.pkp.classes.db.DBResultRange');
        $dbResultRange = new DBResultRange(STATISTICS_MAX_ROWS);

        /** @var MetricsDAO $metricsDao */
        $metricsDao = DAORegistry::getDAO('MetricsDAO');
        $columns = [STATISTICS_DIMENSION_ASSOC_ID, STATISTICS_DIMENSION_ASSOC_TYPE, STATISTICS_DIMENSION_SUBMISSION_ID];
        $filter = [
            STATISTICS_DIMENSION_ASSOC_TYPE => [ASSOC_TYPE_ARTICLE, ASSOC_TYPE_GALLEY],
            STATISTICS_DIMENSION_CONTEXT_ID => $journalId
        ];
        if ($dateStart !== '' && $dateEnd !== '') {
            $filter[STATISTICS_DIMENSION_DAY] = ['from' => $dateStart, 'to' => $dateEnd];
        }

        $allReportStats = [];

        // Paginate stats records for databases with large amounts of statistics data.
        while (true) {
            $reportStats = $metricsDao->getMetrics(
                $metricType, 
                $columns, 
                $filter,
                [
                    STATISTICS_DIMENSION_SUBMISSION_ID => STATISTICS_ORDER_ASC,
                    STATISTICS_DIMENSION_ASSOC_TYPE => STATISTICS_ORDER_ASC
                ],
                $dbResultRange
            );

            if (empty($reportStats)) {
                break;
            }

            $allReportStats = array_merge($allReportStats, $reportStats);
            $dbResultRange->setPage($dbResultRange->getPage() + 1);

            if (count($reportStats) < $dbResultRange->getCount()) {
                break;
            }
        }

        // Format stats and retrieve submission and galleys info.
        [$articleData, $galleyLabels, $galleyViews] = $this->_formatStats($allReportStats);
        $this->_buildReport($articleData, $galleyLabels, $galleyViews);
    }

    /**
     * Return report statistics already formatted in columns to generate the report.
     * @param array $reportStats
     * @return array
     */
    public function _formatStats($reportStats) {
        $articleData = [];
        $galleyLabels = [];
        $galleyViews = [];

        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        /** @var ArticleGalleyDAO $galleyDao */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');

        $currentArticleId = null;
        $galleyViewTotal = 0;

        foreach ($reportStats as $record) {
            $articleId = (int) $record[STATISTICS_DIMENSION_SUBMISSION_ID];
            $assocType = (int) $record[STATISTICS_DIMENSION_ASSOC_TYPE];

            // Initialize data when encountering a new article
            if ($currentArticleId !== $articleId) {
                if ($currentArticleId !== null && isset($articleData[$currentArticleId])) {
                    $articleData[$currentArticleId]['galleyViews'] = $galleyViewTotal;
                }
                
                $currentArticleId = $articleId;
                $galleyViewTotal = 0;

                $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($articleId, null, true);
                if (!$publishedArticle) {
                    $currentArticleId = null; // Skip invalid records
                    continue;
                }
                
                $issueId = (int) $publishedArticle->getIssueId();
                $issue = $issueDao->getIssueById($issueId, null, true);

                $articleData[$articleId] = [
                    'id' => (string) $articleId,
                    'title' => (string) $publishedArticle->getLocalizedTitle(),
                    'issue' => $issue ? (string) $issue->getIssueIdentification() : '',
                    'datePublished' => (string) $publishedArticle->getDatePublished(),
                    'totalAbstractViews' => '',
                    'galleyViews' => 0
                ];
            }

            // Update abstract views if this record is for the article
            if ($assocType === ASSOC_TYPE_ARTICLE) {
                $articleData[$articleId]['totalAbstractViews'] = (int) $record[STATISTICS_METRIC];
            }

            // Retrieve galley data
            if ($assocType === ASSOC_TYPE_GALLEY) {
                $galleyId = (int) $record[STATISTICS_DIMENSION_ASSOC_ID];
                $galley = $galleyDao->getGalley($galleyId, null, true);
                
                if ($galley) {
                    $label = (string) $galley->getLabel();
                    $idx = array_search($label, $galleyLabels, true);
                    if ($idx === false) {
                        $idx = count($galleyLabels);
                        $galleyLabels[] = $label;
                    }

                    if (!isset($galleyViews[$articleId])) {
                        $galleyViews[$articleId] = [];
                    }
                    $galleyViews[$articleId] = array_pad($galleyViews[$articleId], count($galleyLabels), '');

                    $views = (int) $record[STATISTICS_METRIC];
                    $galleyViews[$articleId][$idx] = $views;
                    $galleyViewTotal += $views;
                }
            }
        }

        // Finalize the last article
        if ($currentArticleId !== null && isset($articleData[$currentArticleId])) {
            $articleData[$currentArticleId]['galleyViews'] = $galleyViewTotal;
        }

        return [$articleData, $galleyLabels, $galleyViews];
    }

    /**
     * Build the report using the passed data.
     * @param array $articleData
     * @param array $galleyLabels
     * @param array $galleyViews
     */
    public function _buildReport($articleData, $galleyLabels, $galleyViews) {
        header('Content-Type: text/comma-separated-values');
        header('Content-Disposition: attachment; filename=report.csv');
        
        $fp = fopen('php://output', 'wt');
        
        $reportColumns = [
            __('plugins.reports.timedView.report.articleId'),
            __('plugins.reports.timedView.report.articleTitle'),
            __('issue.issue'),
            __('plugins.reports.timedView.report.datePublished'),
            __('plugins.reports.timedView.report.abstractViews'),
            __('plugins.reports.timedView.report.galleyViews'),
        ];

        PKPString::fputcsv($fp, array_merge($reportColumns, $galleyLabels));

        $galleyCount = count($galleyLabels);
        foreach ($articleData as $articleId => $article) {
            $row = [
                $article['id'],
                $article['title'],
                $article['issue'],
                $article['datePublished'],
                $article['totalAbstractViews'],
                $article['galleyViews']
            ];

            if (isset($galleyViews[$articleId])) {
                $row = array_merge($row, $galleyViews[$articleId]);
            } else {
                // Pad with empty strings to maintain CSV column alignment
                $row = array_merge($row, array_fill(0, $galleyCount, ''));
            }
            
            PKPString::fputcsv($fp, $row);
        }

        fclose($fp);
    }

}
?>
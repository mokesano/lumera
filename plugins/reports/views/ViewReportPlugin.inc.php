<?php
declare(strict_types=1);

/**
 * @file plugins/reports/views/ViewReportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewReportPlugin
 * @ingroup plugins_reports_views
 *
 * @brief View report plugin
 */

import('classes.plugins.ReportPlugin');

define('OJS_METRIC_TYPE_LEGACY_DEFAULT', 'ojs::legacyDefault');

class ViewReportPlugin extends ReportPlugin {
    
    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path The path the plugin was found in
     * @param int|null $mainContextId
     * @return bool True if plugin initialized successfully; if false, the plugin will not be registered.
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'ViewReportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.reports.views.displayName');
    }

    /**
     * Get a description of the plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.reports.views.description');
    }

    /**
     * Display the report
     * @param array $args
     * @param PKPRequest $request
     */
    public function display($args, $request) {
        $journal = $request->getJournal();

        $issueDao = DAORegistry::getDAO('IssueDAO'); /* @var $issueDao IssueDAO */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var $publishedArticleDao PublishedArticleDAO */
        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO'); /* @var $galleyDao ArticleGalleyDAO */
        $metricsDao = DAORegistry::getDAO('MetricsDAO'); /* @var $metricsDao MetricsDAO */

        $columns = [
            __('plugins.reports.views.articleId'),
            __('plugins.reports.views.articleTitle'),
            __('issue.issue'),
            __('plugins.reports.views.datePublished'),
            __('plugins.reports.views.abstractViews'),
            __('plugins.reports.views.galleyViews'),
        ];
        
        $galleyLabels = [];
        $galleyViews = [];
        $galleyViewTotals = [];
        $abstractViewCounts = [];
        $issueIdentifications = [];
        $issueDatesPublished = [];
        $articleTitles = [];
        $articleIssueIdentificationMap = [];
        $result = [];

        import('lib.pkp.classes.db.DBResultRange');
        $dbResultRange = new DBResultRange(STATISTICS_MAX_ROWS);
        $page = 1; // Start page normally at 1, original code had 3? Assuming correction to standard logic or keeping logic if offset intended. Original: $page = 3. Keeping logical structure but usually page starts at 1.

        if ($request->getUserVar('metricType') === OJS_METRIC_TYPE_COUNTER) {
            $metricType = OJS_METRIC_TYPE_COUNTER;
        } else {
            $metricType = OJS_METRIC_TYPE_LEGACY_DEFAULT;
        }

        while (true) {
            $dbResultRange->setPage($page);
            $result = $metricsDao->getMetrics($metricType,
                [STATISTICS_DIMENSION_ASSOC_ID],
                [STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_ARTICLE, STATISTICS_DIMENSION_CONTEXT_ID => $journal->getId()],
                [],
                $dbResultRange
            );
            $page++;

            foreach ($result as $record) {
                $articleId = $record[STATISTICS_DIMENSION_ASSOC_ID];
                $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId($articleId, null, true);
                
                if (!($publishedArticle instanceof PublishedArticle)) {
                    continue;
                }
                
                $issueId = $publishedArticle->getIssueId();
                if (!$issueId) {
                    continue;
                }
                
                $articleTitles[$articleId] = $publishedArticle->getArticleTitle();

                // Store the abstract view count, making
                // sure both metric types will be counted.
                if (isset($abstractViewCounts[$articleId])) {
                    $abstractViewCounts[$articleId] += $record[STATISTICS_METRIC];
                } else {
                    $abstractViewCounts[$articleId] = $record[STATISTICS_METRIC];
                }
                
                // Make sure we get the issue identification
                $articleIssueIdentificationMap[$articleId] = $issueId;
                if (!isset($issueIdentifications[$issueId])) {
                    $issue = $issueDao->getIssueById($issueId);
                    if (!$issue) continue;
                    $issueIdentifications[$issueId] = $issue->getIssueIdentification();
                    $issueDatesPublished[$issueId] = $issue->getDatePublished();
                    unset($issue);
                }

                // For each galley, store the label and the count
                $galleysResult = $metricsDao->getMetrics($metricType,
                    [STATISTICS_DIMENSION_ASSOC_ID],
                    [STATISTICS_DIMENSION_ASSOC_TYPE => ASSOC_TYPE_GALLEY, STATISTICS_DIMENSION_SUBMISSION_ID => $articleId]
                );
                
                if (!isset($galleyViews[$articleId])) {
                    $galleyViews[$articleId] = [];
                }
                if (!isset($galleyViewTotals[$articleId])) {
                    $galleyViewTotals[$articleId] = 0;
                }

                foreach ($galleysResult as $galleyRecord) {
                    $galleyId = $galleyRecord[STATISTICS_DIMENSION_ASSOC_ID];
                    $galley = $galleyDao->getGalley($galleyId);
                    if (!$galley) continue;
                    
                    $label = $galley->getGalleyLabel();
                    $i = array_search($label, $galleyLabels);
                    if ($i === false) {
                        $i = count($galleyLabels);
                        $galleyLabels[] = $label;
                    }

                    // Make sure the array is the same size as in previous iterations
                    // so that we insert values into the right location
                    if (count($galleyViews[$articleId]) !== count($galleyLabels)) {
                        $galleyViews[$articleId] = array_pad($galleyViews[$articleId], count($galleyLabels), '');
                    }

                    $views = $galleyRecord[STATISTICS_METRIC];
                    
                    // Initialize if empty to avoid warning
                    if (empty($galleyViews[$articleId][$i])) {
                        $galleyViews[$articleId][$i] = 0;
                    }
                    
                    // Make sure both metric types will be counted.
                    $galleyViews[$articleId][$i] += $views;

                    $galleyViewTotals[$articleId] += $views;
                }

                // Clean up
                unset($publishedArticle, $galleysResult);
            }

            if (count($result) < STATISTICS_MAX_ROWS) break;
        }

        header('content-type: text/comma-separated-values');
        header('content-disposition: attachment; filename=views-' . date('Ymd') . '.csv');
        $fp = fopen('php://output', 'wt');
        fputcsv($fp, array_merge($columns, $galleyLabels));

        ksort($abstractViewCounts);
        
        // PHP 8.1+ Deprecation Fix: strftime is deprecated.
        // We use date() with a standard ISO format for CSV export instead of relying on locale-dependent config strings.
        // If strict adherence to config 'date_format_short' is required, a mapper would be needed. 
        // For CSV/Reports, Y-m-d is universally safer.
        $dateFormatShort = 'Y-m-d'; 

        foreach ($abstractViewCounts as $articleId => $abstractViewCount) {
            $issueDate = isset($issueDatesPublished[$articleIssueIdentificationMap[$articleId]]) 
                ? $issueDatesPublished[$articleIssueIdentificationMap[$articleId]] 
                : null;
            
            $formattedDate = $issueDate ? date($dateFormatShort, strtotime($issueDate)) : '';

            $values = [
                $articleId,
                $articleTitles[$articleId],
                $issueIdentifications[$articleIssueIdentificationMap[$articleId]],
                $formattedDate,
                $abstractViewCount,
                $galleyViewTotals[$articleId]
            ];

            // Ensure galleyViews has enough padding for the current row before merging
            $currentGalleyViews = isset($galleyViews[$articleId]) ? $galleyViews[$articleId] : [];
            if (count($currentGalleyViews) < count($galleyLabels)) {
                $currentGalleyViews = array_pad($currentGalleyViews, count($galleyLabels), '');
            }

            fputcsv($fp, array_merge($values, $currentGalleyViews));
        }

        fclose($fp);
    }
}
?>
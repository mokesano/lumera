<?php
declare(strict_types=1);

/**
 * @file plugins/reports/reviews/ReviewReportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * * @class ReviewReportPlugin
 * @ingroup plugins_reports_review
 * @see ReviewReportDAO
 *
 * @brief Review report plugin
 */

import('classes.plugins.ReportPlugin');

class ReviewReportPlugin extends ReportPlugin {
    
    /**
     * Called as a plugin is registered to the registry
     * @param string $category Name of category plugin was registered to
     * @param string $path The path the plugin was found in
     * @param int|null $mainContextId
     * @return bool True if plugin initialized successfully; if false, the plugin will not be registered.
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path, $mainContextId);
        if ($success && Config::getVar('general', 'installed')) {
            $this->import('ReviewReportDAO');
            $reviewReportDAO = new ReviewReportDAO();
            DAORegistry::registerDAO('ReviewReportDAO', $reviewReportDAO);
        }
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within
     * its category.
     * @return string name of plugin
     */
    public function getName(): string {
        return 'ReviewReportPlugin';
    }

    /**
     * Get the display name of this plugin.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.reports.reviews.displayName');
    }

    /**
     * Get the description of this plugin.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.reports.reviews.description');
    }

    /**
     * Display the report.
     * @param array $args
     * @param PKPRequest $request
     */
    public function display($args, $request) {
        $journal = $request->getJournal();

        header('content-type: text/comma-separated-values');
        header('content-disposition: attachment; filename=reviews-' . date('Ymd') . '.csv');
        AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);

        $reviewReportDao = DAORegistry::getDAO('ReviewReportDAO'); /* @var $reviewReportDao ReviewReportDAO */
        
        // Modern array destructuring instead of list()
        [$commentsIterator, $reviewsIterator] = $reviewReportDao->getReviewReport($journal->getId());

        $comments = [];
        while ($row = $commentsIterator->next()) {
            if (isset($comments[$row['article_id']][$row['author_id']])) {
                $comments[$row['article_id']][$row['author_id']] .= "; " . $row['comments'];
            } else {
                $comments[$row['article_id']][$row['author_id']] = $row['comments'];
            }
        }

        $yesnoMessages = [0 => __('common.no'), 1 => __('common.yes')];

        import('classes.submission.reviewAssignment.ReviewAssignment');
        $recommendations = ReviewAssignment::getReviewerRecommendationOptions();

        $columns = [
            'round' => __('plugins.reports.reviews.round'),
            'article' => __('article.articles'),
            'articleid' => __('article.submissionId'),
            'reviewerid' => __('plugins.reports.reviews.reviewerId'),
            'reviewer' => __('plugins.reports.reviews.reviewer'),
            'firstname' => __('user.firstName'),
            'middlename' => __('user.middleName'),
            'lastname' => __('user.lastName'),
            'dateassigned' => __('plugins.reports.reviews.dateAssigned'),
            'datenotified' => __('plugins.reports.reviews.dateNotified'),
            'dateconfirmed' => __('plugins.reports.reviews.dateConfirmed'),
            'datecompleted' => __('plugins.reports.reviews.dateCompleted'),
            'datereminded' => __('plugins.reports.reviews.dateReminded'),
            'declined' => __('submissions.declined'),
            'cancelled' => __('common.cancelled'),
            'recommendation' => __('reviewer.article.recommendation'),
            'comments' => __('comments.commentsOnArticle')
        ];
        $yesNoArray = ['declined', 'cancelled'];

        $fp = fopen('php://output', 'wt');
        PKPString::fputcsv($fp, array_values($columns));

        while ($row = $reviewsIterator->next()) {
            foreach ($columns as $index => $junk) {
                if (in_array($index, $yesNoArray)) {
                    $columns[$index] = $yesnoMessages[$row[$index]];
                } elseif ($index == "recommendation") {
                    $columns[$index] = (!isset($row[$index])) ? __('common.none') : __($recommendations[$row[$index]]);
                } elseif ($index == "comments") {
                    if (isset($comments[$row['articleid']][$row['reviewerid']])) {
                        $columns[$index] = $comments[$row['articleid']][$row['reviewerid']];
                    } else {
                        $columns[$index] = "";
                    }
                } else {
                    $columns[$index] = $row[$index];
                }
            }
            PKPString::fputcsv($fp, $columns);
            unset($row);
        }
        fclose($fp);
    }
}

?>
<?php
declare(strict_types=1);

/**
 * @file plugins/reports/reviews/ReviewReportPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class ReviewReportPlugin
 * @ingroup plugins_reports_review
 * @see ReviewReportDAO
 *
 * @brief Review report plugin.
 */

import('classes.plugins.ReportPlugin');

class ReviewReportPlugin extends ReportPlugin {
    
    /**
     * Called as a plugin is registered to the registry.
     * @param string $category
     * @param string $path
     * @param int|null $mainContextId
     * @return bool
     */
    public function register(string $category, string $path, $mainContextId = null): bool {
        $success = parent::register($category, $path);
        if ($success && Config::getVar('general', 'installed')) {
            $this->import('ReviewReportDAO');
            $reviewReportDAO = new ReviewReportDAO();
            DAORegistry::registerDAO('ReviewReportDAO', $reviewReportDAO);
        }
        $this->addLocaleData();
        return $success;
    }

    /**
     * Get the name of this plugin. The name must be unique within its category.
     * @return string
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
     * @param mixed $request
     */
    public function display($args, $request) {
        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        $journal = $request->getJournal();
        if (!$journal) {
            return;
        }

        header('Content-Type: text/comma-separated-values');
        header('Content-Disposition: attachment; filename=reviews-' . date('Ymd') . '.csv');
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_SUBMISSION);

        /** @var ReviewReportDAO $reviewReportDao */
        $reviewReportDao = DAORegistry::getDAO('ReviewReportDAO');
        
        // Modern array destructuring
        [$commentsIterator, $reviewsIterator] = $reviewReportDao->getReviewReport((int) $journal->getId());

        $comments = [];
        while ($row = $commentsIterator->next()) {
            $articleId = (int) $row['article_id'];
            $authorId = (int) $row['author_id'];
            $commentText = (string) ($row['comments'] ?? '');
            
            if (isset($comments[$articleId][$authorId])) {
                $comments[$articleId][$authorId] .= "; " . $commentText;
            } else {
                $comments[$articleId][$authorId] = $commentText;
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
                if (in_array($index, $yesNoArray, true)) {
                    $columns[$index] = $yesnoMessages[(int) $row[$index]] ?? '';
                } elseif ($index === 'recommendation') {
                    $recValue = $row[$index] ?? null;
                    $columns[$index] = ($recValue !== null && isset($recommendations[$recValue])) 
                        ? __($recommendations[$recValue]) 
                        : __('common.none');
                } elseif ($index === 'comments') {
                    $articleId = (int) $row['articleid'];
                    $reviewerId = (int) $row['reviewerid'];
                    $columns[$index] = $comments[$articleId][$reviewerId] ?? '';
                } else {
                    $columns[$index] = (string) ($row[$index] ?? '');
                }
            }
            PKPString::fputcsv($fp, $columns);
        }
        fclose($fp);
    }

}
?>
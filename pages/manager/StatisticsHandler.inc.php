<?php
declare(strict_types=1);

/**
 * @file pages/manager/StatisticsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StatisticsHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for statistics functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('pages.manager.ManagerHandler');

class StatisticsHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function StatisticsHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display a list of journal statistics.
     * WARNING: This implementation should be kept roughly synchronized
     * with the reader's statistics view in the About pages.
     * @param array $args
     * @param PKPRequest $request
     */
    public function statistics($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        $templateMgr = TemplateManager::getManager($request);

        // Get the statistics year
        // [SECURITY FIX] Amankan 'statisticsYear' dengan trim() dan (int)
        $statisticsYear = (int) trim((string) $request->getUserVar('statisticsYear'));

        // Ensure that the requested statistics year is within a sane range
        $journalStatisticsDao = DAORegistry::getDAO('JournalStatisticsDAO');
        $lastYear = strftime('%Y');
        $firstDate = $journalStatisticsDao->getFirstActivityDate($journal->getId());
        if (!$firstDate) $firstYear = $lastYear;
        else $firstYear = strftime('%Y', $firstDate);
        if ($statisticsYear < $firstYear || $statisticsYear > $lastYear) {
            // Request out of range; redirect to the current year's statistics
            return $request->redirect(null, null, null, null, ['statisticsYear' => strftime('%Y')]);
        }

        $templateMgr->assign('statisticsYear', $statisticsYear);
        $templateMgr->assign('firstYear', $firstYear);
        $templateMgr->assign('lastYear', $lastYear);

        $sectionIds = $journal->getSetting('statisticsSectionIds');
        if (!is_array($sectionIds)) $sectionIds = [];
        $templateMgr->assign('sectionIds', $sectionIds);

        foreach ($this->_getPublicStatisticsNames() as $name) {
            $templateMgr->assign($name, $journal->getSetting($name));
        }
        $templateMgr->assign('statViews', $journal->getSetting('statViews'));

        $fromDate = mktime(0, 0, 0, 1, 1, $statisticsYear);
        $toDate = mktime(23, 59, 59, 12, 31, $statisticsYear);

        $articleStatistics = $journalStatisticsDao->getArticleStatistics($journal->getId(), null, $fromDate, $toDate);
        $templateMgr->assign('articleStatistics', $articleStatistics);

        $limitedArticleStatistics = $journalStatisticsDao->getArticleStatistics($journal->getId(), $sectionIds, $fromDate, $toDate);
        $templateMgr->assign('limitedArticleStatistics', $limitedArticleStatistics);

        $sectionDao = DAORegistry::getDAO('SectionDAO');
        $sections = $sectionDao->getJournalSections($journal->getId());
        $templateMgr->assign('sections', $sections->toArray());

        $issueStatistics = $journalStatisticsDao->getIssueStatistics($journal->getId(), $fromDate, $toDate);
        $templateMgr->assign('issueStatistics', $issueStatistics);

        $reviewerStatistics = $journalStatisticsDao->getReviewerStatistics($journal->getId(), $sectionIds, $fromDate, $toDate);
        $templateMgr->assign('reviewerStatistics', $reviewerStatistics);

        $allUserStatistics = $journalStatisticsDao->getUserStatistics($journal->getId(), null, $toDate);
        $templateMgr->assign('allUserStatistics', $allUserStatistics);

        $userStatistics = $journalStatisticsDao->getUserStatistics($journal->getId(), $fromDate, $toDate);
        $templateMgr->assign('userStatistics', $userStatistics);

        if ($journal->getSetting('publishingMode') == PUBLISHING_MODE_SUBSCRIPTION) {
            $allSubscriptionStatistics = $journalStatisticsDao->getSubscriptionStatistics($journal->getId(), null, $toDate);
            $templateMgr->assign('allSubscriptionStatistics', $allSubscriptionStatistics);

            $subscriptionStatistics = $journalStatisticsDao->getSubscriptionStatistics($journal->getId(), $fromDate, $toDate);
            $templateMgr->assign('subscriptionStatistics', $subscriptionStatistics);
        }

        $reportPlugins = PluginRegistry::loadCategory('reports');
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('reportPlugins', $reportPlugins);

        $templateMgr->assign('defaultMetricType', $journal->getSetting('defaultMetricType'));
        $templateMgr->assign('availableMetricTypes', $journal->getMetricTypes(true));

        $templateMgr->assign('helpTopicId', 'journal.managementPages.statsAndReports');

        $templateMgr->display('manager/statistics/index.tpl');
    }

    /**
     * Save statistics settings.
     */
    public function saveStatisticsSettings() {
        $this->validate();

        $request = Application::get()->getRequest();
        $journal = $request->getJournal();

        // [SECURITY FIX] Amankan 'sectionIds'. Jika string tunggal, trim()
        $sectionIds = $request->getUserVar('sectionIds');
        if (!is_array($sectionIds)) {
            if (empty($sectionIds)) {
                $sectionIds = [];
            } else {
                // Input string tunggal harus diamankan.
                $sectionIds = [(int) trim((string) $sectionIds)];
            }
        } else {
            // Jika sudah array, bersihkan setiap elemennya (ID integer)
            foreach ($sectionIds as $key => $id) {
                $sectionIds[$key] = (int) trim((string) $id);
            }
        }
        $journal->updateSetting('statisticsSectionIds', $sectionIds);

        // [SECURITY FIX] Amankan 'defaultMetricType' (string key) dengan trim()
        $defaultMetricType = trim((string) $request->getUserVar('defaultMetricType'));
        $journal->updateSetting('defaultMetricType', $defaultMetricType);

        // [SECURITY FIX] Amankan 'statisticsYear' dengan (int) trim()
        $statisticsYear = (int) trim((string) $request->getUserVar('statisticsYear'));
        $request->redirect(null, null, 'statistics', null, ['statisticsYear' => $statisticsYear]);
    }

    /**
     * Save public statistics list.
     */
    public function savePublicStatisticsList() {
        $this->validate();
        $request = Application::get()->getRequest();
        $journal = $request->getJournal();
        
        foreach ($this->_getPublicStatisticsNames() as $name) {
            // Input dari getUserVar($name) harus diamankan
            $flagValue = (int) trim((string) $request->getUserVar($name));
            $journal->updateSetting($name, $flagValue ? true : false);
        }
        
        // [SECURITY FIX] Amankan 'statViews' (flag boolean) dengan (int) trim()
        $statViews = (int) trim((string) $request->getUserVar('statViews'));
        $journal->updateSetting('statViews', $statViews ? true : false);
        
        // [SECURITY FIX] Amankan 'statisticsYear' dengan (int) trim()
        $statisticsYear = (int) trim((string) $request->getUserVar('statisticsYear'));
        $request->redirect(null, null, 'statistics', null, ['statisticsYear' => $statisticsYear]);
    }

    /**
     * Delegates to plugins operations related to report generation.
     * @param array $args
     * @param PKPRequest $request
     */
    public function report($args, $request) {
        $this->validate();
        $this->setupTemplate();

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();

        $pluginName = array_shift($args);
        $reportPlugins = PluginRegistry::loadCategory('reports');

        if ($pluginName == '' || !isset($reportPlugins[$pluginName])) {
            $request->redirect(null, null, 'statistics');
        }

        $plugin = $reportPlugins[$pluginName];
        $plugin->display($args, $request);
    }

    /**
     * Display page to generate custom reports.
     * @param array $args
     * @param PKPRequest $request
     */
    public function reportGenerator($args, $request) {
        $this->validate();
        $this->setupTemplate();

        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_SUBMISSION, 
            LOCALE_COMPONENT_APP_EDITOR
        );

        $templateMgr = TemplateManager::getManager();
        $templateMgr->display('manager/statistics/reportGenerator.tpl');
    }

    /**
     * Generate statistics reports from passed request arguments.
     * @param array $args
     * @param PKPRequest $request
     */
    public function generateReport($args, $request) {
        $this->validate();
        $this->setupTemplate(true);
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_SUBMISSION, 
            LOCALE_COMPONENT_APP_EDITOR
        );

        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $router = $request->getRouter();
        $context = $router->getContext($request);

        // [SECURITY FIX] Amankan 'metricType' (string key) dengan trim()
        $metricType = trim((string) $request->getUserVar('metricType'));
        if ($metricType === null || empty($metricType)) {
            $metricType = $context->getDefaultMetricType();
        }

        // Generates only one metric type report at a time.
        if (is_array($metricType)) $metricType = current($metricType);
        if (!is_scalar($metricType)) $metricType = null;

        $reportPlugin = StatisticsHelper::getReportPluginByMetricType($metricType);
        if (!$reportPlugin || $metricType === null) {
            $request->redirect(null, null, 'statistics');
        }

        // [SECURITY FIX] Amankan 'columns' (string) dengan trim()
        $columns = (array) $request->getUserVar('columns');
        // Ensure columns is array if it was expected to be one, or handle logic appropriately. 
        // Original code: $columns = trim($request->getUserVar('columns')); suggests it's a string or array?
        // Actually, $columns in getMetrics is typically an array of column IDs.
        // If it comes from checkboxes, it's an array. If string, convert.
        if (!is_array($columns)) $columns = [$columns];
        
        // --- Filters ---
        $filterInput = $request->getUserVar('filters');
        
        // Coba unserialize.
        $filters = @unserialize($filterInput); 
        
        // [SECURITY FIX] Jika unserialize gagal, gunakan input mentah yang sudah diamankan
        if ($filters === false && $filterInput !== 'b:0;') $filters = trim((string) $filterInput); 

        // --- OrderBy ---
        $orderByInput = $request->getUserVar('orderBy'); // Input mentah
        
        if (!empty($orderByInput)) {
            // Coba unserialize
            $orderBy = @unserialize($orderByInput); 
            
            // [SECURITY FIX] Jika unserialize gagal, gunakan input mentah yang sudah diamankan
            if ($orderBy === false && $orderByInput !== 'b:0;') $orderBy = trim((string) $orderByInput);
        } else {
            $orderBy = [];
        }

        $metrics = $reportPlugin->getMetrics($metricType, $columns, $filters, $orderBy);

        $allColumnNames = StatisticsHelper::getColumnNames();
        $columnOrder = array_keys($allColumnNames);
        $columnNames = [];

        foreach ($columnOrder as $column) {
            if (in_array($column, $columns)) {
                $columnNames[$column] = $allColumnNames[$column];
            }

            if ($column == STATISTICS_DIMENSION_ASSOC_TYPE && in_array(STATISTICS_DIMENSION_ASSOC_ID, $columns)) {
                $columnNames['common.title'] = __('common.title');
            }
        }

        // Make sure the metric column will always be present.
        if (!in_array(STATISTICS_METRIC, $columnNames)) $columnNames[STATISTICS_METRIC] = $allColumnNames[STATISTICS_METRIC];

        header('content-type: text/comma-separated-values');
        header('content-disposition: attachment; filename=statistics-' . date('Ymd') . '.csv');
        $fp = fopen('php://output', 'wt');
        fputcsv($fp, [$reportPlugin->getDisplayName()]);
        fputcsv($fp, [$reportPlugin->getDescription()]);
        fputcsv($fp, [__('common.metric') . ': ' . $metricType]);
        fputcsv($fp, [__('manager.statistics.reports.reportUrl') . ': ' . $request->getCompleteUrl()]);
        fputcsv($fp, ['']);

        // Just for better displaying.
        $columnNames = array_merge([''], $columnNames);

        fputcsv($fp, $columnNames);
        foreach ($metrics as $record) {
            $row = [];
            foreach ($columnNames as $key => $name) {
                if (empty($name)) {
                    // Column just for better displaying.
                    $row[] = '';
                    continue;
                }
                switch ($key) {
                    case 'common.title':
                        $assocId = $record[STATISTICS_DIMENSION_ASSOC_ID];
                        $assocType = $record[STATISTICS_DIMENSION_ASSOC_TYPE];
                        $row[] = $this->_getObjectTitle($assocId, $assocType);
                        break;
                    case STATISTICS_DIMENSION_ASSOC_TYPE:
                        $assocType = $record[STATISTICS_DIMENSION_ASSOC_TYPE];
                        $row[] = StatisticsHelper::getObjectTypeString($assocType);
                        break;
                    case STATISTICS_DIMENSION_CONTEXT_ID:
                        $assocId = $record[STATISTICS_DIMENSION_CONTEXT_ID];
                        $assocType = ASSOC_TYPE_JOURNAL;
                        $row[] = $this->_getObjectTitle($assocId, $assocType);
                        break;
                    case STATISTICS_DIMENSION_ISSUE_ID:
                        if (isset($record[STATISTICS_DIMENSION_ISSUE_ID])) {
                            $assocId = $record[STATISTICS_DIMENSION_ISSUE_ID];
                            $assocType = ASSOC_TYPE_ISSUE;
                            $row[] = $this->_getObjectTitle($assocId, $assocType);
                        } else {
                            $row[] = '';
                        }
                        break;
                    case STATISTICS_DIMENSION_SUBMISSION_ID:
                        if (isset($record[STATISTICS_DIMENSION_SUBMISSION_ID])) {
                            $assocId = $record[STATISTICS_DIMENSION_SUBMISSION_ID];
                            $assocType = ASSOC_TYPE_ARTICLE;
                            $row[] = $this->_getObjectTitle($assocId, $assocType);
                        } else {
                            $row[] = '';
                        }
                        break;
                    case STATISTICS_DIMENSION_REGION:
                        if (isset($record[STATISTICS_DIMENSION_REGION]) && isset($record[STATISTICS_DIMENSION_COUNTRY])) {
                            $geoLocationTool = StatisticsHelper::getGeoLocationTool();
                            if ($geoLocationTool) {
                                $regions = $geoLocationTool->getRegions($record[STATISTICS_DIMENSION_COUNTRY]);
                                $regionId = $record[STATISTICS_DIMENSION_REGION];
                                if (strlen((string) $regionId) == 1) $regionId = '0' . $regionId;
                                if (isset($regions[$regionId])) {
                                    $row[] = $regions[$regionId];
                                    break;
                                }
                            }
                        }
                        $row[] = '';
                        break;
                    default:
                        $row[] = $record[$key];
                        break;
                }
            }
            fputcsv($fp, $row);
        }
        fclose($fp);
    }


    //
    // Private helper methods.
    //
    /**
     * Get public statistics names.
     * @return array
     */
    protected function _getPublicStatisticsNames() {
        return [
            'statNumPublishedIssues',
            'statItemsPublished',
            'statNumSubmissions',
            'statPeerReviewed',
            'statCountAccept',
            'statCountDecline',
            'statCountRevise',
            'statDaysPerReview',
            'statDaysToPublication',
            'statRegisteredUsers',
            'statRegisteredReaders',
            'statSubscriptions',
        ];
    }

    /**
     * Get data object title based on passed
     * assoc type and id. If no object, return
     * a default title.
     * @param int $assocId
     * @param int $assocType
     * @return string
     */
    protected function _getObjectTitle($assocId, $assocType) {
        switch ($assocType) {
            case ASSOC_TYPE_JOURNAL:
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $journal = $journalDao->getJournal($assocId);
                if (!$journal) break;
                return $journal->getLocalizedTitle();
            case ASSOC_TYPE_ISSUE:
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issue = $issueDao->getIssueById($assocId, null, true);
                if (!$issue) break;
                $title = $issue->getLocalizedTitle();
                if (!$title) {
                    $title = $issue->getIssueIdentification();
                }
                return $title;
            case ASSOC_TYPE_ISSUE_GALLEY:
                $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
                $issueGalley = $issueGalleyDao->getGalley($assocId);
                if (!$issueGalley) break;
                return $issueGalley->getFileName();
            case ASSOC_TYPE_ARTICLE:
                $articleDao = DAORegistry::getDAO('ArticleDAO');
                $article = $articleDao->getArticle($assocId, null, true);
                if (!$article) break;
                return $article->getLocalizedTitle();
            case ASSOC_TYPE_GALLEY:
                $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
                $galley = $articleGalleyDao->getGalley($assocId);
                if (!$galley) break;
                return $galley->getFileName();
            default:
                // assert(false); // Removed assert
                break;
        }

        return __('manager.statistics.reports.objectNotFound');
    }
}
?>
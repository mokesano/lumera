<?php
declare(strict_types=1);

/**
 * @file controllers/statistics/ReportGeneratorHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReportGeneratorHandler
 * @ingroup controllers_statistics
 *
 * @brief Handle requests for report generator functions.
 * [WIZDAM EDITION] Refactored for PHP 8.x
 */

import('classes.handler.Handler');
import('lib.pkp.classes.core.JSONMessage');

class ReportGeneratorHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->addRoleAssignment(
            [ROLE_ID_JOURNAL_MANAGER],
            ['fetchReportGenerator', 'saveReportGenerator', 'fetchArticlesInfo', 'fetchRegions']
        );
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ReportGeneratorHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor. Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Fetch form to generate custom reports.
     * @param array $args
     * @param PKPRequest $request
     */
    public function fetchReportGenerator($args, $request) {
        $this->setupTemplate($request);
        $reportGeneratorForm = $this->_getReportGeneratorForm($request);
        $reportGeneratorForm->initData(); // [WIZDAM] Fixed: initData usually doesn't take request, check parent form if issues arise

        $formContent = $reportGeneratorForm->fetch($request);

        $json = new JSONMessage(true);
        // [SECURITY FIX] Amankan flag boolean 'refreshForm'
        if ((int) trim((string)$request->getUserVar('refreshForm'))) {
            $json->setEvent('refreshForm', $formContent);
        } else {
            $json->setContent($formContent);
        }

        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Save form to generate custom reports.
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveReportGenerator($args, $request) {
        $this->setupTemplate($request);

        $reportGeneratorForm = $this->_getReportGeneratorForm($request);
        $reportGeneratorForm->readInputData();
        
        $json = new JSONMessage(true);
        if ($reportGeneratorForm->validate()) {
            $reportUrl = $reportGeneratorForm->execute($request);
            $json->setAdditionalAttributes(['reportUrl' => $reportUrl]);
        } else {
            $json->setStatus(false);
        }

        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Fetch articles title and id from the passed request variable issue id.
     * @param array $args
     * @param PKPRequest $request
     * @return string JSON response
     */
    public function fetchArticlesInfo($args, $request) {
        $this->validate();

        // [SECURITY FIX] Amankan 'issueId' dengan trim() dan (int)
        $issueId = (int) trim((string)$request->getUserVar('issueId'));
        
        $json = new JSONMessage();

        if (!$issueId) {
            $json->setStatus(false);
        } else {
            $articleDao = DAORegistry::getDAO('PublishedArticleDAO'); /* @var PublishedArticleDAO $articleDao */
            $articles = $articleDao->getPublishedArticles($issueId);
            $articlesInfo = [];
            foreach ($articles as $article) {
                $articlesInfo[] = ['id' => $article->getId(), 'title' => $article->getLocalizedTitle()];
            }

            $json->setContent($articlesInfo);
        }

        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Fetch regions from the passed request variable country id.
     * @param array $args
     * @param PKPRequest $request
     * @return string JSON response
     */
    public function fetchRegions($args, $request) {
        $this->validate();

        // [SECURITY FIX] Amankan 'countryId' dengan trim()
        $countryId = trim((string)$request->getUserVar('countryId'));
        
        $json = new JSONMessage(false);

        if ($countryId) {
            $geoLocationTool = StatisticsHelper::getGeoLocationTool();
            if ($geoLocationTool) {
                $regions = $geoLocationTool->getRegions($countryId);
                if (!empty($regions) && is_array($regions)) {
                    $regionsData = [];
                    foreach ($regions as $id => $name) {
                        $regionsData[] = ['id' => $id, 'name' => $name];
                    }
                    $json->setStatus(true);
                    $json->setContent($regionsData);
                }
            }
        }

        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * @see PKPHandler::setupTemplate()
     */
    public function setupTemplate($request = null) {
        parent::setupTemplate($request);
        AppLocale::requireComponents(
            LOCALE_COMPONENT_PKP_MANAGER, 
            LOCALE_COMPONENT_OJS_MANAGER,
            LOCALE_COMPONENT_OJS_EDITOR, 
            LOCALE_COMPONENT_PKP_SUBMISSION
        );
    }

    //
    // Private helper methods.
    //
    
    /**
     * Get report generator form object.
     * [WIZDAM] Removed reference return '&' for PHP 8 compatibility with 'new' objects
     * @param PKPRequest $request
     * @return ReportGeneratorForm
     */
    protected function _getReportGeneratorForm($request) {
        $router = $request->getRouter();
        $journal = $router->getContext($request);

        // [SECURITY FIX] Amankan 'metricType' dengan trim()
        $metricType = trim((string)$request->getUserVar('metricType'));
        if (!$metricType) {
            $metricType = $journal->getDefaultMetricType();
        }

        $reportPlugin = StatisticsHelper::getReportPluginByMetricType($metricType);
        if (!is_scalar($metricType) || !$reportPlugin) {
            fatalError('Invalid metric type.');
        }

        $columns = $reportPlugin->getColumns($metricType);
        $columns = array_flip(array_intersect(array_flip(StatisticsHelper::getColumnNames()), $columns));

        $optionalColumns = $reportPlugin->getOptionalColumns($metricType);
        $optionalColumns = array_flip(array_intersect(array_flip(StatisticsHelper::getColumnNames()), $optionalColumns));

        $objects = $reportPlugin->getObjectTypes($metricType);
        $objects = array_flip(array_intersect(array_flip(StatisticsHelper::getObjectTypeString()), $objects));

        $defaultReportTemplates = $reportPlugin->getDefaultReportTemplates($metricType);

        // If the report plugin doesn't works with the file type column,
        // don't load file types.
        if (isset($columns[STATISTICS_DIMENSION_FILE_TYPE])) {
            $fileTypes = StatisticsHelper::getFileTypeString();
        } else {
            $fileTypes = []; // [WIZDAM] Type safety: empty array instead of null if array expected
        }

        // Metric type will be presented in header, remove if any.
        if (isset($columns[STATISTICS_DIMENSION_METRIC_TYPE])) unset($columns[STATISTICS_DIMENSION_METRIC_TYPE]);

        // [SECURITY FIX] Amankan 'reportTemplate' dengan trim()
        $reportTemplate = trim((string)$request->getUserVar('reportTemplate'));
        
        // Pass null if empty string to match constructor expectation
        if ($reportTemplate === '') {
            $reportTemplate = null;
        }

        import('controllers.statistics.form.ReportGeneratorForm');
        $reportGeneratorForm = new ReportGeneratorForm(
            $columns, 
            $optionalColumns,
            $objects, 
            $fileTypes, 
            $metricType, 
            $defaultReportTemplates, 
            $reportTemplate
        );

        return $reportGeneratorForm;
    }
}

?>
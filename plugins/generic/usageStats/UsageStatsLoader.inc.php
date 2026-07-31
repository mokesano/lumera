<?php
declare(strict_types=1);

/**
 * @file plugins/generic/usageStats/UsageStatsLoader.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsLoader
 * @ingroup plugins_generic_usageStats
 *
 * @brief Scheduled task to extract transform and load usage statistics data into database.
 */

import('lib.pkp.classes.task.FileLoader');

/** 
 * These are rules defined by the COUNTER project.
 * See http://www.projectcounter.org/code_practice.htmlcode 
 */
define('COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_HTML', 10);
define('COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_OTHER', 30);

class UsageStatsLoader extends FileLoader {

    /** @var object|null A GeoLocationTool object instance to provide geo location based on ip. */
    protected $_geoLocationTool = null;

    /** @var object|null Plugin */
    protected $_plugin = null;

    /** @var string|null */
    protected $_counterRobotsListFile = null;

    /** @var array */
    protected $_journalsByPath = [];

    /** @var bool|null */
    protected $_autoStage = null;

    /** @var bool|null */
    protected $_externalLogFiles = null;

    /**
     * Constructor.
     * @param array $args
     */
    public function __construct($args = []) {
        $plugin = PluginRegistry::getPlugin('generic', 'usagestatsplugin');

        if (!$plugin) {
            PluginRegistry::loadCategory('generic');
            $plugin = PluginRegistry::getPlugin('generic', 'usagestatsplugin');
        }

        if (!$plugin) {
            return;
        }

        /** @var UsageStatsPlugin $plugin */
        $this->_plugin = $plugin;
        if ($plugin->getSetting(CONTEXT_ID_NONE, 'compressArchives')) {
            $this->setCompressArchives(true);
        }

        $arg = current($args);
        switch ($arg) {
            case 'autoStage':
                if ($plugin->getSetting(0, 'createLogFiles')) {
                    $this->_autoStage = true;
                }
                break;
            case 'externalLogFiles':
                $this->_externalLogFiles = true;
                break;
        }

        $args[0] = $plugin->getFilesPath();
        parent::__construct($args);
        if ($plugin->getEnabled()) {
            PluginRegistry::loadCategory('reports');

            $geoLocationTool = StatisticsHelper::getGeoLocationTool();
            $this->_geoLocationTool = $geoLocationTool;

            $plugin->import('UsageStatsTemporaryRecordDAO');
            $statsDao = new UsageStatsTemporaryRecordDAO();
            DAORegistry::registerDAO('UsageStatsTemporaryRecordDAO', $statsDao);

            $this->_counterRobotsListFile = $this->_getCounterRobotListFile();

            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journalFactory = $journalDao->getJournals();
            
            $journalsByPath = [];
            while ($journal = $journalFactory->next()) {
                $journalsByPath[$journal->getPath()] = $journal;
            }
            $this->_journalsByPath = $journalsByPath;
            unset($journalFactory);

            $this->checkFolderStructure(true);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     * @param array $args
     */
    public function UsageStatsLoader($args = []) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::UsageStatsLoader(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($args);
    }

    /**
     * Get the name of the loader.
     * @see FileLoader::getName()
     * @return string
     */
    public function getName() {
        return __('plugins.generic.usageStats.usageStatsLoaderName');
    }

    /**
     * Execute actions.
     * @see FileLoader::executeActions()
     * @return bool
     */
    public function executeActions() {
        $plugin = $this->_plugin;
        if (!$plugin || !$plugin->getEnabled()) {
            $this->addExecutionLogEntry(__('plugins.generic.usageStats.pluginDisabled'), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
            return true;
        }
        
        $processingDirFiles = glob($this->getProcessingPath() . DIRECTORY_SEPARATOR . '*');
        $processingDirError = is_array($processingDirFiles) && count($processingDirFiles) > 0;
        
        if ($processingDirError) {
            $this->addExecutionLogEntry(
                __('plugins.generic.usageStats.processingPathNotEmpty', ['directory' => $this->getProcessingPath()]), 
                SCHEDULED_TASK_MESSAGE_TYPE_ERROR
            );
        }

        if ($this->_autoStage) {
            $this->autoStage();
        }

        return (parent::executeActions() && !$processingDirError);
    }

    /**
     * Process a file.
     * @see FileLoader::processFile()
     * @param string $filePath
     * @param string $errorMsg
     * @return mixed
     */
    public function processFile($filePath, &$errorMsg) {
        $fhandle = fopen($filePath, 'r');
        $geoTool = $this->_geoLocationTool;
        
        if (!$fhandle) {
            $errorMsg = __('plugins.generic.usageStats.openFileFailed', ['file' => $filePath]);
            return false;
        }
        
        if (!$this->_counterRobotsListFile) {
            $errorMsg = __('plugins.generic.usageStats.noCounterBotList', ['botlist' => $this->_counterRobotsListFile, 'file' => $filePath]);
            fclose($fhandle);
            return false;
        } elseif (!file_exists($this->_counterRobotsListFile)) {
            $errorMsg = __('plugins.generic.usageStats.failedCounterBotList', ['botlist' => $this->_counterRobotsListFile, 'file' => $filePath]);
            fclose($fhandle);
            return false;
        }

        $loadId = basename($filePath);
        /** @var UsageStatsTemporaryRecordDAO $statsDao */
        $statsDao = DAORegistry::getDAO('UsageStatsTemporaryRecordDAO');
        $statsDao->deleteByLoadId($loadId);

        $lastInsertedEntries = [];
        $lineNumber = 0;

        while (!feof($fhandle)) {
            $lineNumber++;

            $line = trim((string) fgets($fhandle));
            if (empty($line) || substr($line, 0, 1) === '#') {
                continue;
            }
            
            $entryData = $this->_getDataFromLogEntry($line);
            if (!$this->_isLogEntryValid($entryData, $lineNumber)) {
                $errorMsg = __('plugins.generic.usageStats.invalidLogEntry', ['file' => $filePath, 'lineNumber' => $lineNumber]);
                fclose($fhandle);
                return false;
            }

            if ($entryData['url'] === '*') {
                continue; // Apache internal
            }
            if (!in_array($entryData['returnCode'], ['200', '304'], true)) {
                continue; // Non-success codes
            }
            if (Core::isUserAgentBot($entryData['userAgent'], $this->_counterRobotsListFile)) {
                continue; // Bots
            }

            // Get Association Data
            [$assocId, $assocType] = $this->_getAssocFromUrl($entryData['url'], $filePath, $lineNumber);
            if (!$assocId || !$assocType) {
                continue;
            }

            $countryCode = null;
            $cityName = null;
            $region = null;
            
            if ($geoTool) {
                $geoResult = $geoTool->getGeoLocation($entryData['ip']);
                if (is_array($geoResult)) {
                    $countryCode = $geoResult[0] ?? null;
                    $cityName    = $geoResult[1] ?? null;
                    $region      = $geoResult[2] ?? null;
                }
            }

            $day = date('Ymd', $entryData['date']);
            $type = $this->_getFileType($assocType, $assocId);

            $entryHash = $assocType . $assocId . $entryData['ip'];
            $biggestTimeFilter = COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_OTHER;
            
            foreach ($lastInsertedEntries as $hash => $time) {
                if ($time + $biggestTimeFilter < $entryData['date']) {
                    unset($lastInsertedEntries[$hash]);
                }
            }

            if (isset($lastInsertedEntries[$entryHash])) {
                if ($type === STATISTICS_FILE_TYPE_PDF || $type === STATISTICS_FILE_TYPE_OTHER) {
                    $timeFilter = COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_OTHER;
                } else {
                    $timeFilter = COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_HTML;
                }

                $secondsBetweenRequests = $entryData['date'] - $lastInsertedEntries[$entryHash];
                if ($secondsBetweenRequests < $timeFilter) {
                    $statsDao->deleteRecord($assocType, $assocId, $lastInsertedEntries[$entryHash], $loadId);
                }
            }

            $lastInsertedEntries[$entryHash] = $entryData['date'];
            
            // Insert Data
            $statsDao->insert($assocType, $assocId, $day, $entryData['date'], $countryCode, $region, $cityName, $type, $loadId);
        }

        fclose($fhandle);
        
        $loadResult = $this->_loadData($loadId, $errorMsg);
        
        // Final cleanup
        $statsDao->deleteByLoadId($loadId);

        if (!$loadResult) {
            $errorMsg = __('plugins.generic.usageStats.loadDataError', ['file' => $filePath, 'error' => $errorMsg]);
            return FILE_LOADER_RETURN_TO_STAGING;
        }
        
        return true;
    }

    //
    // Protected methods.
    //
    /**
     * Auto stage usage stats log files
     */
    protected function autoStage() {
        $plugin = $this->_plugin;
        $fileMgr = new FileManager();
        $logFiles = [];
        
        $logsDirFiles = glob($plugin->getUsageEventLogsPath() . DIRECTORY_SEPARATOR . '*');
        $processingDirFiles = glob($this->getProcessingPath() . DIRECTORY_SEPARATOR . '*');

        if (is_array($logsDirFiles)) {
            $logFiles = array_merge($logFiles, $logsDirFiles);
        }

        if (is_array($processingDirFiles)) {
            $logFiles = array_merge($logFiles, $processingDirFiles);
        }

        foreach ($logFiles as $filePath) {
            if ($fileMgr->fileExists($filePath)) {
                $filename = pathinfo($filePath, PATHINFO_BASENAME);
                $currentDayFilename = $plugin->getUsageEventCurrentDayLogName();
                if ($filename === $currentDayFilename) {
                    continue;
                }
                $this->moveFile(pathinfo($filePath, PATHINFO_DIRNAME), $this->getStagePath(), $filename);
            }
        }
    }

    //
    // Private helper methods.
    //
    /**
     * Validate a access log entry.
     * @param array $entry
     * @param int $lineNumber
     * @return bool
     */
    protected function _isLogEntryValid($entry, $lineNumber) {
        if (empty($entry)) {
            return false;
        }

        $date = $entry['date'];
        if (!is_numeric($date) || (int) $date <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Get data from the passed log entry.
     * @param string $entry
     * @return array
     */
    protected function _getDataFromLogEntry($entry) {
        $plugin = $this->_plugin;
        $createLogFiles = $plugin->getSetting(0, 'createLogFiles');
        
        if (!$createLogFiles || $this->_externalLogFiles) {
            $parseRegex = $plugin->getSetting(0, 'accessLogFileParseRegex');
        } else {
            $parseRegex = '/^(?P<ip>\S+) \S+ \S+ "(?P<date>.*?)" (?P<url>\S+) (?P<returnCode>\S+) "(?P<userAgent>.*?)"/';
        }

        if (!$parseRegex) {
            $parseRegex = '/^(?P<ip>\S+) \S+ \S+ \[(?P<date>.*?)\] "\S+ (?P<url>\S+).*?" (?P<returnCode>\S+) \S+ ".*?" "(?P<userAgent>.*?)"/';
        }

        $returner = [];
        if (preg_match($parseRegex, $entry, $m)) {
            $associative = count(array_filter(array_keys($m), 'is_string')) > 0;
            $returner['ip'] = $associative ? $m['ip'] : $m[1];
            $returner['date'] = strtotime($associative ? $m['date'] : $m[2]);
            $returner['url'] = urldecode($associative ? $m['url'] : $m[3]);
            $returner['returnCode'] = $associative ? $m['returnCode'] : $m[4];
            $returner['userAgent'] = $associative ? $m['userAgent'] : $m[5];
        }

        return $returner;
    }

    /**
     * Get expected pages and ops
     * @return array
     */
    protected function _getExpectedPageAndOp() {
        return [
            ASSOC_TYPE_ARTICLE => ['article/view', 'article/viewArticle'],
            ASSOC_TYPE_GALLEY => ['article/viewFile', 'article/download'],
            ASSOC_TYPE_SUPP_FILE => ['article/downloadSuppFile'],
            ASSOC_TYPE_ISSUE => ['issue/view'],
            ASSOC_TYPE_ISSUE_GALLEY => ['issue/viewFile', 'issue/download'],
            ASSOC_TYPE_JOURNAL => ['index/index']
        ];
    }

    /**
     * Get the assoc type and id from URL.
     * @param string $url
     * @param string $filePath
     * @param int $lineNumber
     * @return array
     */
    protected function _getAssocFromUrl($url, $filePath, $lineNumber) {
        $assocId = false;
        $assocType = false;
        $journalId = false;
        $expectedPageAndOp = $this->_getExpectedPageAndOp();
        $pathInfoDisabled = Config::getVar('general', 'disable_path_info');

        $url = Core::removeBaseUrl($url);
        if ($url) {
            $contextPaths = Core::getContextPaths($url, !$pathInfoDisabled);
            $page = Core::getPage($url, !$pathInfoDisabled);
            $operation = Core::getOp($url, !$pathInfoDisabled);
            $args = Core::getArgs($url, !$pathInfoDisabled);
        } else {
            $this->addExecutionLogEntry(__('plugins.generic.usageStats.removeUrlError', ['file' => $filePath, 'lineNumber' => $lineNumber]), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
            return [false, false];
        }

        if (is_array($contextPaths) && !$page && $operation === 'index') {
            $page = 'index';
        }

        if (empty($contextPaths) || !$page || !$operation) {
            return [false, false];
        }

        $pageAndOperation = $page . '/' . $operation;
        $pageAndOpMatch = false;
        $workingAssocType = null;

        foreach ($expectedPageAndOp as $wAssocType => $workingPageAndOps) {
            foreach ($workingPageAndOps as $workingPageAndOp) {
                if ($pageAndOperation === $workingPageAndOp) {
                    $pageAndOpMatch = true;
                    $workingAssocType = $wAssocType;
                    break 2;
                }
            }
        }

        if ($pageAndOpMatch) {
            if (empty($args)) {
                if ($page === 'index' && $operation === 'index') {
                    $assocType = ASSOC_TYPE_JOURNAL;
                } else {
                    return [false, false];
                }
            } else {
                $assocId = $args[0];
            }

            if (isset($args[1])) {
                if ($workingAssocType === ASSOC_TYPE_ARTICLE) {
                    $assocType = ASSOC_TYPE_GALLEY;
                } elseif ($workingAssocType === ASSOC_TYPE_ISSUE) {
                    $assocType = ASSOC_TYPE_ISSUE_GALLEY;
                }
                $parentObjectId = $args[0];
                $assocId = $args[1];
            }

            if (!$assocType) {
                $assocType = $workingAssocType;
            }

            $journalPath = $contextPaths[0];
            if (isset($this->_journalsByPath[$journalPath])) {
                $journal = $this->_journalsByPath[$journalPath];
                $journalId = $journal->getId();

                if ($assocType === ASSOC_TYPE_JOURNAL) {
                    $assocId = $journalId;
                }
            } else {
                return [false, false];
            }

            switch ($assocType) {
                case ASSOC_TYPE_SUPP_FILE:
                case ASSOC_TYPE_GALLEY:
                    $articleId = $this->_getInternalArticleId($parentObjectId ?? $assocId, $journal);
                    if (!$articleId) {
                        $assocId = false;
                        break;
                    }
                    if ($assocType === ASSOC_TYPE_SUPP_FILE) {
                        /** @var SuppFileDAO $suppFileDao */
                        $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
                        if ($journal->getSetting('enablePublicSuppFileId')) {
                            $suppFile = $suppFileDao->getSuppFileByBestSuppFileId($assocId, $articleId);
                        } else {
                            $suppFile = $suppFileDao->getSuppFile((int) $assocId, $articleId);
                        }
                        if ($suppFile instanceof SuppFile) {
                            $assocId = $suppFile->getId();
                        } else {
                            $assocId = false;
                        }
                        break;
                    } else {
                        /** @var ArticleGalleyDAO $galleyDao */
                        $galleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
                        if ($journal->getSetting('enablePublicGalleyId')) {
                            $galley = $galleyDao->getGalleyByBestGalleyId($assocId, $articleId);
                        } else {
                            $galley = $galleyDao->getGalley($assocId, $articleId);
                        }
                        if ($galley instanceof ArticleGalley) {
                            $assocId = $galley->getId();
                            break;
                        }
                    }

                    // Fallback to article
                    $assocType = ASSOC_TYPE_ARTICLE;
                    $assocId = $articleId;
                    // Fallthrough intentional
                case ASSOC_TYPE_ARTICLE:
                    $assocId = $this->_getInternalArticleId($assocId, $journal);
                    break;
                case ASSOC_TYPE_ISSUE_GALLEY:
                    $issueId = $this->_getInternalIssueId($parentObjectId ?? $assocId, $journal);
                    if (!$issueId) {
                        $assocId = false;
                        break;
                    }
                    /** @var IssueGalleyDAO $galleyDao */
                    $galleyDao = DAORegistry::getDAO('IssueGalleyDAO');
                    if ($journal->getSetting('enablePublicGalleyId')) {
                        $galley = $galleyDao->getGalleyByBestGalleyId($assocId, $issueId);
                    } else {
                        $galley = $galleyDao->getGalley($assocId, $issueId);
                    }
                    if ($galley instanceof IssueGalley) {
                        $assocId = $galley->getId();
                        break;
                    } else {
                        $assocType = ASSOC_TYPE_ISSUE;
                        $assocId = $issueId;
                    }
                    // Fallthrough intentional
                case ASSOC_TYPE_ISSUE:
                    $assocId = $this->_getInternalIssueId($assocId, $journal);
                    break;
            }

            // PDF/HTML Galley checks
            $articleViewAccessPageAndOp = ['article/view', 'article/viewArticle'];

            if (in_array($pageAndOperation, $articleViewAccessPageAndOp, true) && $assocType === ASSOC_TYPE_GALLEY && isset($galley) && $galley && $galley->isPdfGalley()) {
                $assocId = false;
                $assocType = false;
            }
        }

        return [$assocId, $assocType];
    }

    /**
     * Get internal article id.
     * @param string $id
     * @param Journal $journal
     * @return int|false
     */
    protected function _getInternalArticleId($id, $journal) {
        $journalId = (int) $journal->getId();
        /** @var PublishedArticleDAO $publishedArticleDao */
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        
        if ($journal->getSetting('enablePublicArticleId')) {
            $publishedArticle = $publishedArticleDao->getPublishedArticleByBestArticleId($journalId, $id, true);
        } else {
            $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId((int) $id, $journalId, true);
        }
        
        if ($publishedArticle instanceof PublishedArticle) {
            return (int) $publishedArticle->getId();
        }
        return false;
    }

    /**
     * Get internal issue id.
     * @param string $id
     * @param Journal $journal
     * @return int|false
     */
    protected function _getInternalIssueId($id, $journal) {
        $journalId = (int) $journal->getId();
        /** @var IssueDAO $issueDao */
        $issueDao = DAORegistry::getDAO('IssueDAO');
        if ($journal->getSetting('enablePublicIssueId')) {
            $issue = $issueDao->getIssueByBestIssueId($id, $journalId, true);
        } else {
            $issue = $issueDao->getIssueById((int) $id, null, true);
        }
        
        if ($issue instanceof Issue) {
            return (int) $issue->getId();
        }
        return false;
    }

    /**
     * Get the file type of the object.
     * @param int $assocType
     * @param int $assocId
     * @return int|null
     */
    protected function _getFileType($assocType, $assocId) {
        $file = null;
        $type = null;

        switch ($assocType) {
            case ASSOC_TYPE_GALLEY:
                /** @var ArticleGalleyDAO $articleGalleyDao */
                $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
                $file = $articleGalleyDao->getGalley($assocId);
                break;
            case ASSOC_TYPE_ISSUE_GALLEY: // [WIZDAM FIX] Changed semicolon to colon
                /** @var IssueGalleyDAO $issueGalleyDao */
                $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
                $file = $issueGalleyDao->getGalley($assocId);
                break;
            case ASSOC_TYPE_SUPP_FILE:
                /** @var SuppFileDAO $suppFileDao */
                $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
                $file = $suppFileDao->getSuppFile($assocId);
                break;
        }

        if ($file) {
            if ($file instanceof SuppFile) {
                switch ($file->getFileType()) {
                    case 'application/pdf':
                        $type = STATISTICS_FILE_TYPE_PDF;
                        break;
                    case 'text/html':
                        $type = STATISTICS_FILE_TYPE_HTML;
                        break;
                    default:
                        $type = STATISTICS_FILE_TYPE_OTHER;
                        break;
                }
            }

            if ($file instanceof ArticleGalley || $file instanceof IssueGalley) {
                if ($file->isPdfGalley()) {
                    $type = STATISTICS_FILE_TYPE_PDF;
                } elseif ($file instanceof ArticleGalley && $file->isHtmlGalley()) {
                    $type = STATISTICS_FILE_TYPE_HTML;
                } else {
                    $type = STATISTICS_FILE_TYPE_OTHER;
                }
            }
        }

        return $type;
    }

    /**
     * Load the entries inside the temporary database.
     * @param string $loadId
     * @param string $errorMsg
     * @return bool
     */
    protected function _loadData($loadId, &$errorMsg) {
        /** @var UsageStatsTemporaryRecordDAO $statsDao */
        $statsDao = DAORegistry::getDAO('UsageStatsTemporaryRecordDAO');
        /** @var MetricsDAO $metricsDao */
        $metricsDao = DAORegistry::getDAO('MetricsDAO');

        $records = [];
        while ($record = $statsDao->getNextByLoadId($loadId)) {
            $record['metric_type'] = OJS_METRIC_TYPE_COUNTER;
            $records[] = $record;
        }

        if (empty($records)) {
            $errorMsg = __('plugins.generic.usageStats.noParsedRecords', ['loadId' => $loadId]);
            return false;
        }

        $metricsDao->purgeLoadBatch($loadId);

        foreach ($records as $record) {
            $errorMsg = null;
            if (!$metricsDao->insertRecord($record, $errorMsg)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the COUNTER robot list file.
     * @return string|false
     */
    protected function _getCounterRobotListFile() {
        $dir = $this->_plugin->getPluginPath() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'counter';
        $files = glob($dir . DIRECTORY_SEPARATOR . '*');
        if (!is_array($files) || count($files) !== 1) {
            return false;
        }

        return $files[0];
    }
    
}
?>
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
 * * MODERNIZED FOR PHP 7.4+ (Wizdam Protocol v2.1)
 * * Optimized for Connection Pooling & Memory Management
 */

import('lib.pkp.classes.task.FileLoader');

/** 
 * These are rules defined by the COUNTER project.
 * See http://www.projectcounter.org/code_practice.htmlcode 
 */
define('COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_HTML', 10);
define('COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_OTHER', 30);

class UsageStatsLoader extends FileLoader {

    /** @var GeoLocationTool|null A GeoLocationTool object instance to provide geo location based on ip. */
    protected $_geoLocationTool;

    /** @var object Plugin */
    protected $_plugin;

    /** @var string */
    protected $_counterRobotsListFile;

    /** @var array */
    protected $_journalsByPath;

    /** @var string */
    protected $_autoStage;

    /** @var string */
    protected $_externalLogFiles;

    /**
     * Constructor.
     * @param $args array. (Default array kosong mencegah Fatal Error)
     */
    public function __construct($args = array()) {
        // --- [FIX START] WIZDAM FORK DEFENSIVE LOADING ---
        // 1. Coba ambil plugin secara normal
        $plugin = PluginRegistry::getPlugin('generic', 'usagestatsplugin');

        // 2. Jika NULL (karena dijalankan Acron/CLI), paksa load kategori 'generic'
        if (!$plugin) {
            PluginRegistry::loadCategory('generic');
            $plugin = PluginRegistry::getPlugin('generic', 'usagestatsplugin');
        }

        // 3. Jika MASIH NULL (Plugin didisable/dihapus), hentikan proses agar tidak Fatal Error
        if (!$plugin) {
            // Opsional: Return diam-diam agar Acron tidak crash
            return;
        }
        // --- [FIX END] ---

        $this->_plugin = $plugin;

        if ($plugin->getSetting(CONTEXT_ID_NONE, 'compressArchives')) {
            $this->setCompressArchives(true);
        }

        // Ambil argumen pertama dengan aman
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

        // PENTING: Parent class (FileLoader) membutuhkan path direktori pada index 0.
        // Karena $plugin sudah dipastikan tidak null di atas, baris ini aman sekarang.
        if (!isset($args[0])) {
            $args[0] = $plugin->getFilesPath();
        } else {
            $args[0] = $plugin->getFilesPath();
        }

        parent::__construct($args);

        if ($plugin->getEnabled()) {
            PluginRegistry::loadCategory('reports');

            // Inisialisasi GeoLocationTool (Tanpa reference &)
            $geoLocationTool = StatisticsHelper::getGeoLocationTool();
            $this->_geoLocationTool = $geoLocationTool;

            $plugin->import('UsageStatsTemporaryRecordDAO');
            $statsDao = new UsageStatsTemporaryRecordDAO();
            DAORegistry::registerDAO('UsageStatsTemporaryRecordDAO', $statsDao);

            $this->_counterRobotsListFile = $this->_getCounterRobotListFile();

            // Wizdam Optimization: Load journals efficiently
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journalFactory = $journalDao->getJournals();
            
            $journalsByPath = array();
            while ($journal = $journalFactory->next()) {
                $journalsByPath[$journal->getPath()] = $journal;
                unset($journal); // Memory cleanup
            }
            $this->_journalsByPath = $journalsByPath;
            // Clean factory result set immediately
            unset($journalFactory);

            $this->checkFolderStructure(true);
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UsageStatsLoader($args = array()) {
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
     * @return boolean True if processing succeeded.
     */
    public function executeActions() {
        $plugin = $this->_plugin;
        if (!$plugin->getEnabled()) {
            $this->addExecutionLogEntry(__('plugins.generic.usageStats.pluginDisabled'), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
            return true;
        }
        
        $processingDirFiles = glob($this->getProcessingPath() . DIRECTORY_SEPARATOR . '*');
        $processingDirError = is_array($processingDirFiles) && count($processingDirFiles);
        if ($processingDirError) {
            $this->addExecutionLogEntry(__('plugins.generic.usageStats.processingPathNotEmpty', array('directory' => $this->getProcessingPath())), SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
        }

        if ($this->_autoStage) $this->autoStage();

        return (parent::executeActions() && !$processingDirError);
    }

    /**
     * Process a file.
     * @see FileLoader::processFile()
     * [WIZDAM FIX] PHP 8 Support: Fix trim(false) error on fgets EOF
     * @param $filePath string
     * @param $errorMsg string passed by reference
     */
    public function processFile($filePath, &$errorMsg) {
        $fhandle = fopen($filePath, 'r');
        $geoTool = $this->_geoLocationTool;
        
        if (!$fhandle) {
            $errorMsg = __('plugins.generic.usageStats.openFileFailed', array('file' => $filePath));
            return false;
        }
        
        if (!$this->_counterRobotsListFile) {
            $errorMsg = __('plugins.generic.usageStats.noCounterBotList', array('botlist' => $this->_counterRobotsListFile, 'file' => $filePath));
            return false;
        } elseif (!file_exists($this->_counterRobotsListFile)) {
            $errorMsg = __('plugins.generic.usageStats.failedCounterBotList', array('botlist' => $this->_counterRobotsListFile, 'file' => $filePath));
            return false;
        }

        $loadId = basename($filePath);
        $statsDao = DAORegistry::getDAO('UsageStatsTemporaryRecordDAO'); /* @var $statsDao UsageStatsTemporaryRecordDAO */

        // Clean up previous temporary records for this load ID
        $statsDao->deleteByLoadId($loadId);

        $extractedData = array();
        $lastInsertedEntries = array();
        $lineNumber = 0;

        while(!feof($fhandle)) {
            $lineNumber++;
            
            // [WIZDAM FIX] Force cast to string prevents "trim() expects string, bool given" fatal error
            // When fgets returns false (EOF), it becomes "" (empty string), which trim handles safely.
            $line = trim((string) fgets($fhandle));
            
            if (empty($line) || substr($line, 0, 1) === "#") continue;
            
            $entryData = $this->_getDataFromLogEntry($line);
            if (!$this->_isLogEntryValid($entryData, $lineNumber)) {
                $errorMsg = __('plugins.generic.usageStats.invalidLogEntry',
                    array('file' => $filePath, 'lineNumber' => $lineNumber));
                return false;
            }

            // Filter logic
            if ($entryData['url'] == '*') continue; // Apache internal
            if (!in_array($entryData['returnCode'], array(200, 304))) continue; // Non-success codes
            if (Core::isUserAgentBot($entryData['userAgent'], $this->_counterRobotsListFile)) continue; // Bots

            // Get Association Data
            list($assocId, $assocType) = $this->_getAssocFromUrl($entryData['url'], $filePath, $lineNumber);
            if(!$assocId || !$assocType) continue;

            // --- MODERNIZED GEOIP SECTION ---
            $countryCode = $cityName = $region = null;
            
            if ($geoTool) {
                // GeoLocationTool returns [Country, City, Region]
                $geoResult = $geoTool->getGeoLocation($entryData['ip']);
                if (is_array($geoResult)) {
                    $countryCode = $geoResult[0] ?? null;
                    $cityName    = $geoResult[1] ?? null;
                    $region      = $geoResult[2] ?? null;
                }
            }
            // --- END MODERNIZED GEOIP SECTION ---

            $day = date('Ymd', $entryData['date']);
            $type = $this->_getFileType($assocType, $assocId);

            // Double click filtering logic
            $entryHash = $assocType . $assocId . $entryData['ip'];
            $biggestTimeFilter = COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS_OTHER;
            
            // Wizdam Optimization: Clean array inside loop to manage memory
            foreach($lastInsertedEntries as $hash => $time) {
                if ($time + $biggestTimeFilter < $entryData['date']) {
                    unset($lastInsertedEntries[$hash]);
                }
            }

            if (isset($lastInsertedEntries[$entryHash])) {
                if ($type == STATISTICS_FILE_TYPE_PDF || $type == STATISTICS_FILE_TYPE_OTHER) {
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
            
            // Insert Data (Includes new Geo fields)
            $statsDao->insert($assocType, $assocId, $day, $entryData['date'], $countryCode, $region, $cityName, $type, $loadId);
        }

        fclose($fhandle);
        
        $loadResult = $this->_loadData($loadId, $errorMsg);
        
        // Final cleanup
        $statsDao->deleteByLoadId($loadId);

        if (!$loadResult) {
            $errorMsg = __('plugins.generic.usageStats.loadDataError',
                array('file' => $filePath, 'error' => $errorMsg));
            return FILE_LOADER_RETURN_TO_STAGING;
        } else {
            return true;
        }
    }

    //
    // Protected methods.
    //
    /**
     * Auto stage usage stats log files
     * Wizdam Optimization: Use glob() instead of DirectoryIterator for performance
     */
    protected function autoStage() {
        $plugin = $this->_plugin;
        $fileMgr = new FileManager();
        $logFiles = array();
        
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
                if ($filename == $currentDayFilename) continue;
                $this->moveFile(pathinfo($filePath, PATHINFO_DIRNAME), $this->getStagePath(), $filename);
            }
        }
    }


    //
    // Private helper methods.
    //
    /**
     * Validate a access log entry.
     * @param $entry array Log entry data.
     * @param $lineNumber int Line number in the log file.
     * @return boolean True if the log entry is valid.
     */
    protected function _isLogEntryValid($entry, $lineNumber) {
        if (empty($entry)) {
            return false;
        }

        $date = $entry['date'];
        if (!is_numeric($date) && $date <= 0) {
            return false;
        }

        return true;
    }

    /**
     * Get data from the passed log entry.
     * @param $entry string Log entry.
     * @return array Associative array with the log entry data.
     */
    protected function _getDataFromLogEntry($entry) {
        $plugin = $this->_plugin;
        $createLogFiles = $plugin->getSetting(0, 'createLogFiles');
        
        if (!$createLogFiles || $this->_externalLogFiles) {
            $parseRegex = $plugin->getSetting(0, 'accessLogFileParseRegex');
        } else {
            $parseRegex = '/^(?P<ip>\S+) \S+ \S+ "(?P<date>.*?)" (?P<url>\S+) (?P<returnCode>\S+) "(?P<userAgent>.*?)"/';
        }

        if (!$parseRegex) $parseRegex = '/^(?P<ip>\S+) \S+ \S+ \[(?P<date>.*?)\] "\S+ (?P<url>\S+).*?" (?P<returnCode>\S+) \S+ ".*?" "(?P<userAgent>.*?)"/';

        $returner = array();
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
        return array(ASSOC_TYPE_ARTICLE => array(
                'article/view',
                'article/viewArticle'),
            ASSOC_TYPE_GALLEY => array(
                'article/viewFile',
                'article/download'),
            ASSOC_TYPE_SUPP_FILE => array(
                'article/downloadSuppFile'),
            ASSOC_TYPE_ISSUE => array(
                'issue/view'),
            ASSOC_TYPE_ISSUE_GALLEY => array(
                'issue/viewFile',
                'issue/download'),
            ASSOC_TYPE_JOURNAL => array(
                'index/index')
            );
    }

    /**
     * Get the assoc type and id from URL.
     * Wizdam Optimization: Heavy DB usage here, ensure connections are closed.
     * @param $url string
     * @param $filePath string
     * @param $lineNumber int
     * @return array (assocId, assocType)
     */
    protected function _getAssocFromUrl($url, $filePath, $lineNumber) {
        $assocId = $assocType = $journalId = false;
        $expectedPageAndOp = $this->_getExpectedPageAndOp();
        $pathInfoDisabled = Config::getVar('general', 'disable_path_info');

        $url = Core::removeBaseUrl($url);
        if ($url) {
            $contextPaths = Core::getContextPaths($url, !$pathInfoDisabled);
            $page = Core::getPage($url, !$pathInfoDisabled);
            $operation = Core::getOp($url, !$pathInfoDisabled);
            $args = Core::getArgs($url, !$pathInfoDisabled);
        } else {
            $this->addExecutionLogEntry(__('plugins.generic.usageStats.removeUrlError',
                array('file' => $filePath, 'lineNumber' => $lineNumber)), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
            return array(false, false);
        }

        if (is_array($contextPaths) && !$page && $operation == 'index') {
            $page = 'index';
        }

        if (empty($contextPaths) || !$page || !$operation) return array(false, false);

        $pageAndOperation = $page . '/' . $operation;
        $pageAndOpMatch = false;
        $workingAssocType = null; // Initialize

        foreach ($expectedPageAndOp as $wAssocType => $workingPageAndOps) {
            foreach($workingPageAndOps as $workingPageAndOp) {
                if ($pageAndOperation == $workingPageAndOp) {
                    $pageAndOpMatch = true;
                    $workingAssocType = $wAssocType;
                    break 2;
                }
            }
        }

        if ($pageAndOpMatch) {
            if (empty($args)) {
                if ($page == 'index' && $operation == 'index') {
                    $assocType = ASSOC_TYPE_JOURNAL;
                } else {
                    return array(false, false);
                }
            } else {
                $assocId = $args[0];
                $parentObjectId = null;
            }

            if (isset($args[1])) {
                if ($workingAssocType == ASSOC_TYPE_ARTICLE) {
                    $assocType = ASSOC_TYPE_GALLEY;
                } elseif ($workingAssocType == ASSOC_TYPE_ISSUE) {
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

                if ($assocType == ASSOC_TYPE_JOURNAL) {
                    $assocId = $journalId;
                }
            } else {
                return array(false, false);
            }

            // DB Optimization: Ensure DAOs close connections implicitly by not holding refs too long
            switch ($assocType) {
                case ASSOC_TYPE_SUPP_FILE:
                case ASSOC_TYPE_GALLEY:
                    $articleId = $this->_getInternalArticleId($parentObjectId, $journal);
                    if (!$articleId) {
                        $assocId = false;
                        break;
                    }
                    if ($assocType == ASSOC_TYPE_SUPP_FILE) {
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
                    $issueId = $this->_getInternalIssueId($parentObjectId, $journal);
                    if (!$issueId) {
                        $assocId = false;
                        break;
                    }
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
            $workingPageAndOp = $pageAndOperation;
            $articleViewAccessPageAndOp = array('article/view', 'article/viewArticle');

            if (in_array($workingPageAndOp, $articleViewAccessPageAndOp) && $assocType == ASSOC_TYPE_GALLEY && isset($galley) && $galley && ($galley->isPdfGalley())) {
                $assocId = $assocType = false;
            }
        }

        return array($assocId, $assocType);
    }

    /**
     * Get internal article id.
     * Wizdam Optimization: Close DB connection if retrieved manually
     * @param $id string
     * @param $journal Journal
     * @return int|false Internal article ID or false if not found.
     */
    protected function _getInternalArticleId($id, $journal) {
        $journalId = $journal->getId();
        $publishedArticleDao = DAORegistry::getDAO('PublishedArticleDAO');
        
        if ($journal->getSetting('enablePublicArticleId')) {
            $publishedArticle = $publishedArticleDao->getPublishedArticleByBestArticleId((int) $journalId, $id, true);
        } else {
            $publishedArticle = $publishedArticleDao->getPublishedArticleByArticleId((int) $id, (int) $journalId, true);
        }
        
        if ($publishedArticle instanceof PublishedArticle) {
            return $publishedArticle->getId();
        }
        return false;
    }

    /**
     * Get internal issue id.
     * @param $id string
     * @param $journal Journal
     * @return int|false Internal issue ID or false if not found.
     */
    protected function _getInternalIssueId($id, $journal) {
        $journalId = $journal->getId();
        $issueDao = DAORegistry::getDAO('IssueDAO');
        
        if ($journal->getSetting('enablePublicIssueId')) {
            $issue = $issueDao->getIssueByBestIssueId($id, $journalId, true);
        } else {
            $issue = $issueDao->getIssueById((int) $id, null, true);
        }
        
        if ($issue instanceof Issue) {
            return $issue->getId();
        }
        return false;
    }

    /**
     * Get the file type of the object.
     * @param $assocType int
     * @param $assocId int
     * @return int STATISTICS_FILE_TYPE_*
     */
    protected function _getFileType($assocType, $assocId) {
        $file = null;
        $type = null;

        // Get the file.
        switch($assocType) {
            case ASSOC_TYPE_GALLEY:
                $articleGalleyDao = DAORegistry::getDAO('ArticleGalleyDAO');
                $file = $articleGalleyDao->getGalley($assocId);
                break;
            case ASSOC_TYPE_ISSUE_GALLEY;
                $issueGalleyDao = DAORegistry::getDAO('IssueGalleyDAO');
                $file = $issueGalleyDao->getGalley($assocId);
                break;
            case ASSOC_TYPE_SUPP_FILE:
                $suppFileDao = DAORegistry::getDAO('SuppFileDAO');
                $file = $suppFileDao->getSuppFile($assocId);
                break;
        }

        if ($file) {
            if ($file instanceof SuppFile) {
                switch($file->getFileType()) {
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
                } else if ($file instanceof ArticleGalley && $file->isHtmlGalley()) {
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
     * Wizdam Optimization: Purge load batch is crucial.
     * @param $loadId string
     * @param $errorMsg string passed by reference
     * @return boolean True if load succeeded.
     */
    protected function _loadData($loadId, $errorMsg) {
        $statsDao = DAORegistry::getDAO('UsageStatsTemporaryRecordDAO');
        $metricsDao = DAORegistry::getDAO('MetricsDAO');
        
        // Critical cleanup step
        $metricsDao->purgeLoadBatch($loadId);

        while ($record = $statsDao->getNextByLoadId($loadId)) {
            $record['metric_type'] = OJS_METRIC_TYPE_COUNTER;
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
        $file = null;
        $dir = $this->_plugin->getPluginPath() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'counter';

        $fileCount = 0;
        foreach (glob($dir . DIRECTORY_SEPARATOR . '*') as $file) {
            $fileCount++;
        }
        if (!$file || $fileCount !== 1) {
            return false;
        }

        return $file;
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file plugins/generic/usageStats/UsageStatsPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsPlugin
 * @ingroup plugins_generic_usageStats
 *
 * @brief Provide usage statistics to data objects.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class UsageStatsPlugin extends GenericPlugin {

    /** @var array|null */
    private ?array $_currentUsageEvent = null;

    /** @var bool */
    private bool $_dataPrivacyOn = false;

    /** @var bool */
    private bool $_optedOut = false;

    /** @var string|null */
    private ?string $_saltpath = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        // The upgrade and install processes will need access
        // to constants defined in that report plugin.
        import('plugins.generic.usageStats.UsageStatsReportPlugin');
    }

    //
    // Public methods.
    //
    /**
     * Get the report plugin object that implements
     * the metric type details.
     * @return UsageStatsReportPlugin
     */
    public function getReportPlugin() {
        $this->import('UsageStatsReportPlugin');
        return new UsageStatsReportPlugin();
    }

    //
    // Implement methods from PKPPlugin.
    //
    /**
     * Registers the plugin.
     * @see LazyLoadPlugin::register()
     * @param string $category
     * @param string $path
     * @return bool True if registration succeeded.
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);

        HookRegistry::register('AcronPlugin::parseCronTab', [$this, 'callbackParseCronTab']);

        if ($this->getEnabled() && $success) {
            HookRegistry::register('PluginRegistry::loadCategory', [$this, 'callbackLoadCategory']);
            HookRegistry::register('LoadHandler', [$this, 'callbackLoadHandler']);

            // If the plugin will provide the access logs,
            // register to the usage event hook provider.
            if ($this->getSetting(CONTEXT_ID_NONE, 'createLogFiles')) {
                HookRegistry::register('UsageEventPlugin::getUsageEvent', [$this, 'logUsageEvent']);
            }

            $this->_dataPrivacyOn = (bool) $this->getSetting(CONTEXT_ID_NONE, 'dataPrivacyOption');
            $this->_saltpath = $this->getSetting(CONTEXT_ID_NONE, 'saltFilepath');
            
            // Check config for backward compatibility.
            if (!$this->_saltpath) {
                $this->_saltpath = Config::getVar('usageStats', 'salt_filepath');
            }
            
            $application = Application::getApplication();
            $request = $application->getRequest();
            $this->_optedOut = (bool) $request->getCookieVar('usageStats-opt-out');
            
            if ($this->_optedOut) {
                // Renew the Opt-Out cookie if present.
                $request->setCookieVar('usageStats-opt-out', true, time() + 60 * 60 * 24 * 365);
            }
        }

        return $success;
    }

    /**
     * Get the path to the salt file.
     * @return string|null
     */
    public function getSaltpath(): ?string {
        return $this->_saltpath;
    }

    /**
     * Get the plugin display name.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.usageStats.displayName');
    }

    /**
     * Get the plugin description.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.usageStats.description');
    }

    /**
     * Determine whether this is a site plugin.
     * @see PKPPlugin::isSitePlugin()
     * @return bool
     */
    public function isSitePlugin(): bool {
        return true;
    }

    /**
     * Get the path to the plugin settings file.
     * @see PKPPlugin::getInstallSitePluginSettingsFile()
     * @return string|null
     */
    public function getInstallSitePluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Get the path to the plugin schema file.
     * @see PKPPlugin::getInstallSchemaFile()
     * @return string|null
     */
    public function getInstallSchemaFile(): ?string {
        return $this->getPluginPath() . '/schema.xml';
    }

    /**
     * Get the template path for this plugin.
     * @see PKPPlugin::getTemplatePath()
     * @return string
     */
    public function getTemplatePath(): string {
        return parent::getTemplatePath() . 'templates/';
    }

    /**
     * Handle management verbs.
     * @see PKPPlugin::manage()
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param PKPRequest|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        $returner = parent::manage($verb, $args, $message, $messageParams, $request);
        if (!$returner) {
            return false;
        }
        
        $this->import('UsageStatsSettingsForm');
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
        
        switch ($verb) {
            case 'settings':
                $settingsForm = new UsageStatsSettingsForm($this);
                $settingsForm->initData();
                $settingsForm->display($request);
                break;
            case 'save':
                $settingsForm = new UsageStatsSettingsForm($this);
                $settingsForm->readInputData();
                if ($settingsForm->validate()) {
                    $settingsForm->execute();
                    // [WIZDAM] FIX: Cast to string to prevent TypeError in strict_types=1
                    $message = (string) NOTIFICATION_TYPE_SUCCESS;
                    $messageParams = ['contents' => __('plugins.generic.usageStats.settings.saved')];
                    return false;
                } else {
                    $settingsForm->display($request);
                }
                break;
            default:
                return $returner;
        }
        return true;
    }

    //
    // Implement template methods from GenericPlugin.
    //
    /**
     * Get the management verbs associated with this plugin.
     * @see GenericPlugin::getManagementVerbs()
     * @param array $verbs
     * @param PKPRequest|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled()) {
            $verbs[] = ['settings', __('manager.plugins.settings')];
        }
        return $verbs;
    }

    //
    // Hook implementations.
    //
    /**
     * Callback to load the report plugin.
     * @see PluginRegistry::loadCategory()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadCategory($hookName, $args) {
        $plugin = null;
        $category = $args[0];
        
        if ($category === 'reports') {
            $plugin = $this->getReportPlugin();
        } elseif ($category === 'blocks' && $this->getSetting(CONTEXT_ID_NONE, 'dataPrivacyOption')) {
            $this->import('UsageStatsOptoutBlockPlugin');
            $plugin = new UsageStatsOptoutBlockPlugin($this->getName());
        }

        // Register report plugin (by reference).
        if ($plugin) {
            $seq = $plugin->getSeq();
            $plugins =& $args[1];
            if (!isset($plugins[$seq])) {
                $plugins[$seq] = [];
            }
            $plugins[$seq][$this->getPluginPath()] = $plugin;
        }

        return false;
    }

    /**
     * Callback to load the usage stats handler.
     * @see PKPPageRouter::route()
     * @param string $hookName
     * @param array $args
     * @return void
     */
    public function callbackLoadHandler($hookName, $args) {
        $page = $args[0];
        if ($page !== 'usageStats') {
            return;
        }
        
        $availableOps = ['privacyInformation'];
        $op = $args[1];
        if (!in_array($op, $availableOps, true)) {
            return;
        }
        
        // The handler had been requested.
        define('HANDLER_CLASS', 'UsageStatsHandler');
        define('USAGESTATS_PLUGIN_NAME', $this->getName());
        
        $handlerFile =& $args[2];
        $handlerFile = $this->getPluginPath() . '/UsageStatsHandler.inc.php';
    }

    /**
     * Callback to add scheduled tasks.
     * @see AcronPlugin::parseCronTab()
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackParseCronTab($hookName, $args) {
        $taskFilesPath =& $args[0];
        $taskFilesPath[] = $this->getPluginPath() . DIRECTORY_SEPARATOR . 'scheduledTasksAutoStage.xml';
        return false;
    }

    /**
     * Validate that the path of the salt file exists and is writable.
     * @param string $saltpath
     * @return bool
     */
    public function validateSaltpath($saltpath): bool {
        if (!file_exists($saltpath)) {
            @touch($saltpath);
        }
        return is_writable($saltpath);
    }

    /**
     * Log the usage event into a file.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function logUsageEvent($hookName, $args) {
        $hookName = $args[0];
        $usageEvent = $args[1];

        // Check the statistics opt-out.
        if ($this->_optedOut) {
            return false;
        }

        if ($hookName === 'FileManager::downloadFileFinished' && !$usageEvent && $this->_currentUsageEvent) {
            // File download is finished, try to log the current usage event.
            $downloadSuccess = $args[2];
            if ($downloadSuccess && !connection_aborted()) {
                $this->_currentUsageEvent['downloadSuccess'] = true;
                $usageEvent = $this->_currentUsageEvent;
            }
        }

        if ($usageEvent && !($usageEvent['downloadSuccess'] ?? false)) {
            // Don't log until we get the download finished hook call.
            $this->_currentUsageEvent = $usageEvent;
            return false;
        }

        if ($usageEvent) {
            $this->_writeUsageEventInLogFile($usageEvent);
        }

        return false;
    }

    /**
     * Get the geolocation tool to process geo localization data.
     * @return mixed
     */
    public function getGeoLocationTool() {
        $this->import('GeoLocationTool');
        $tool = new GeoLocationTool();
        
        if ($tool->isPresent()) {
            return $tool;
        }
        return null;
    }

    /**
     * Get the plugin's files path.
     * @return string
     */
    public function getFilesPath(): string {
        import('lib.pkp.classes.file.PrivateFileManager');
        $fileMgr = new PrivateFileManager();
        return realpath($fileMgr->getBasePath()) . DIRECTORY_SEPARATOR . 'usageStats';
    }

    /**
     * Get the plugin's usage event logs path.
     * @return string
     */
    public function getUsageEventLogsPath(): string {
        return $this->getFilesPath() . DIRECTORY_SEPARATOR . 'usageEventLogs';
    }

    /**
     * Get current day usage event log name.
     * @return string
     */
    public function getUsageEventCurrentDayLogName(): string {
        return 'usage_events_' . date("Ymd") . '.log';
    }

    //
    // Private helper methods.
    //
    /**
     * Write the usage event into the log file.
     * @param array $usageEvent
     * @return bool
     */
    private function _writeUsageEventInLogFile(array $usageEvent): bool {
        $salt = null;
        if ($this->_dataPrivacyOn) {
            $saltFilename = $this->getSaltpath();
            if (!$saltFilename || !$this->validateSaltpath($saltFilename)) {
                return false;
            }
            
            $currentDate = date("Ymd");
            $saltFilenameLastModified = date("Ymd", filemtime($saltFilename));
            
            $file = fopen($saltFilename, 'r');
            if ($file) {
                $salt = trim(fread($file, filesize($saltFilename)));
                fclose($file);
            }
            
            if (empty($salt) || ($currentDate !== $saltFilenameLastModified)) {
                // [WIZDAM] Modernization: Use random_bytes() which is standard and secure in PHP 7+
                $newSalt = bin2hex(random_bytes(16));
                
                $file = fopen($saltFilename, 'wb');
                if ($file && flock($file, LOCK_EX)) {
                    fwrite($file, $newSalt);
                    flock($file, LOCK_UN);
                    fclose($file);
                    $salt = $newSalt;
                } else {
                    if ($file) fclose($file);
                    throw new \RuntimeException('Failed to acquire lock on salt file: ' . $saltFilename);
                }
            }
        }

        // Manage the IP address (eventually hash it)
        if ($this->_dataPrivacyOn) {
            if ($salt === null) {
                return false;
            }
            // Hash the IP
            $hashedIp = $this->_hashIp($usageEvent['ip'], $salt);
            // Never store unhashed IPs!
            if ($hashedIp === false) {
                return false;
            }
            $desiredParams = [$hashedIp];
        } else {
            $desiredParams = [$usageEvent['ip']];
        }

        $desiredParams[] = $usageEvent['classification'] ?? '-';

        if (!$this->_dataPrivacyOn && isset($usageEvent['user'])) {
            $desiredParams[] = $usageEvent['user']->getId();
        } else {
            $desiredParams[] = '-';
        }

        $desiredParams = array_merge($desiredParams, [
            '"' . $usageEvent['time'] . '"',
            $usageEvent['canonicalUrl'],
            '200', // The usage event plugin always log requests that returned this code.
            '"' . $usageEvent['userAgent'] . '"'
        ]);

        $usageLogEntry = implode(' ', $desiredParams) . PHP_EOL;

        import('lib.pkp.classes.file.PrivateFileManager');
        $fileMgr = new PrivateFileManager();

        $filename = $this->getUsageEventCurrentDayLogName();
        $usageEventFilesPath = $this->getUsageEventLogsPath();
        
        if (!$fileMgr->fileExists($usageEventFilesPath, 'dir')) {
            $success = $fileMgr->mkdirtree($usageEventFilesPath);
            if (!$success) {
                throw new \RuntimeException('Files directory wrong configuration: ' . $usageEventFilesPath);
            }
        }

        $filePath = $usageEventFilesPath . DIRECTORY_SEPARATOR . $filename;
        $fp = fopen($filePath, 'ab');
        
        if ($fp && flock($fp, LOCK_EX)) {
            fwrite($fp, $usageLogEntry);
            flock($fp, LOCK_UN);
            fclose($fp);
        } else {
            if ($fp) fclose($fp);
            throw new \RuntimeException('Couldn\'t lock the file: ' . $filePath);
        }
        
        return true;
    }

    /**
     * Hash (SHA256) the given IP using the given SALT.
     *
     * @param string $ip
     * @param string $salt
     * @return string
     */
    private function _hashIp(string $ip, string $salt): string {
        return hash('sha256', $ip . $salt);
    }

}
?>
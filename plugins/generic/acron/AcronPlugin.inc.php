<?php
declare(strict_types=1);

/**
 * @file plugins/generic/acron/AcronPlugin.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcronPlugin
 * @ingroup plugins_generic_acron
 *
 * @brief Removes dependency on 'cron' for scheduled tasks.
 */

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.scheduledTask.ScheduledTaskHelper');

class AcronPlugin extends GenericPlugin {

    /** @var string|null */
    protected $_workingDir = null;

    /** @var array */
    protected $_tasksToRun = [];
    
    /** @var mixed */
    protected $_preservedApplication = null;

    /** @var mixed */
    protected $_preservedRequest = null;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function AcronPlugin() {
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
     * Plugin registration. Registers hooks and load locale data.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        
        HookRegistry::register('Installer::postInstall', [$this, 'callbackPostInstall']);

        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return $success;
        }
        
        if ($success) {
            $this->addLocaleData();
            HookRegistry::register('LoadHandler', [$this, 'callbackLoadHandler']);
            HookRegistry::register('PluginHandler::plugin', [$this, 'callbackManage']);
        }
        return $success;
    }

    /**
     * Plugin is a site plugin.
     * @see PKPPlugin::isSitePlugin()
     * @return bool
     */
    public function isSitePlugin(): bool {
        return true;
    }

    /**
     * Unique name of this plugin.
     * @see LazyLoadPlugin::getName()
     * @return string
     */
    public function getName(): string {
        return 'acronPlugin';
    }

    /**
     * Display name of this plugin.
     * @see PKPPlugin::getDisplayName()
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.acron.name');
    }

    /**
     * Description of the plugin.
     * @see PKPPlugin::getDescription()
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.acron.description');
    }

    /**
     * Install site plugin settings file.
     * @see PKPPlugin::getInstallSitePluginSettingsFile()
     * @return string|null
     */
    public function getInstallSitePluginSettingsFile(): ?string {
        return $this->getPluginPath() . '/settings.xml';
    }

    /**
     * Management verbs for enable/disable and reload actions.
     * @see GenericPlugin::getManagementVerbs()
     * @param array $verbs
     * @param object|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $isEnabled = $this->getEnabled($request);

        $verbs = []; 
        $verbs[] = [
            ($isEnabled ? 'disable' : 'enable'),
            __($isEnabled ? 'manager.plugins.disable' : 'manager.plugins.enable')
        ];
        $verbs[] = [
            'reload', __('plugins.generic.acron.reload')
        ];
        return $verbs;
    }

    /**
     * Manage plugin actions: enable, disable, reload.
     * @see GenericPlugin::manage()
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param object|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        switch ($verb) {
            case 'enable':
                $this->updateSetting(0, 'enabled', true);
                import('classes.notification.NotificationManager');
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_SUCCESS,
                    ['contents' => __('plugins.generic.acron.enabled')]
                );
                break;

            case 'disable':
                $this->updateSetting(0, 'enabled', false);
                import('classes.notification.NotificationManager');
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_SUCCESS,
                    ['contents' => __('plugins.generic.acron.disabled')]
                );
                break;

            case 'reload':
                $this->_parseCrontab();
                /** @var ScheduledTaskDAO $taskDao */
                $taskDao = DAORegistry::getDAO('ScheduledTaskDAO');
                $repairedCount = $taskDao->repairInvalidLastRunTimes();

                import('classes.notification.NotificationManager');
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_SUCCESS,
                    ['contents' => __('plugins.generic.acron.reloaded', ['count' => $repairedCount])]
                );
                break;
        }
        return false;
    }

    /**
     * Post install hook to flag cron tab reload on every install/upgrade.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackPostInstall($hookName, $args) {
        $this->_parseCrontab();
        return false;
    }

    /**
     * Load handler hook to check for tasks to run.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadHandler($hookName, $args) {
        $throttleRatio = (int) Config::getVar('general', 'acron_throttle', 100);

        if (mt_rand(1, $throttleRatio) !== 1) {
            return false;
        }

        $tasksToRun = $this->_getTasksToRun();

        if (!empty($tasksToRun)) {
            $this->_workingDir = getcwd();
            $this->_tasksToRun = $tasksToRun;

            ob_start();
            
            $this->_preservedApplication = Registry::get('application');
            $this->_preservedRequest = Registry::get('request');
            
            register_shutdown_function([$this, 'shutdownFunction']);
        }

        return false;
    }

    /**
     * Syncronize crontab with lazy load plugins management.
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackManage($hookName, $args) {
        $verb = $args[0] ?? '';
        $plugin = $args[4] ?? null;

        if (!($plugin instanceof LazyLoadPlugin)) {
            return false;
        }

        if ($verb !== 'enable' && $verb !== 'disable') {
            return false;
        }

        $hooks = HookRegistry::getHooks();
        $hookNameCheck = 'AcronPlugin::parseCronTab';
        if (!isset($hooks[$hookNameCheck])) {
            return false;
        }

        foreach ($hooks[$hookNameCheck] as $index => $callback) {
            if ($callback[0] === $plugin) {
                $this->_parseCrontab();
                break;
            }
        }

        return false;
    }

    /**
     * Shutdown callback.
     */
    public function shutdownFunction() {
        // 1. INFRASTRUKTUR: Bangkitkan kembali Registry (Life-Support)
        if (Registry::get('application') === null && $this->_preservedApplication !== null) {
            Registry::set('application', $this->_preservedApplication);
        }
        if (Registry::get('request') === null && $this->_preservedRequest !== null) {
            Registry::set('request', $this->_preservedRequest);
        }
        
        // 2. INFRASTRUKTUR: Tutup koneksi ke browser pengguna secara absolut (Backgrounding)
        $this->_closeHttpConnectionGracefully();
        
        // 3. INFRASTRUKTUR: Siapkan environment server untuk proses panjang
        set_time_limit(0);
        if ($this->_workingDir) {
            chdir($this->_workingDir);
        }

        // 4. DOMAIN LOGIC: Eksekusi daftar tugas secara modular
        if (!empty($this->_tasksToRun)) {
            $this->_executeScheduledTasks($this->_tasksToRun);
        }
    }

    /**
     * Handle graceful HTTP connection closure to allow background processing.
     */
    protected function _closeHttpConnectionGracefully(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            return;
        }

        if (!headers_sent()) {
            header("Connection: close");
            header("Content-Encoding: none");
            header("Content-Length: " . (string) ob_get_length());
        }

        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }

    //
    // MODULAR HELPER Protected
    //
    /**
     * Arrange task execution flow and delegate to single task executor.
     * @param array $tasksToRun
     */
    protected function _executeScheduledTasks(array $tasksToRun): void {
        /** @var ScheduledTaskDAO $taskDao */
        $taskDao = DAORegistry::getDAO('ScheduledTaskDAO');
        $currentTasksToRun = $this->_getTasksToRun();
        
        foreach ($tasksToRun as $task) {
            $this->_executeSingleTask($task, $currentTasksToRun, $taskDao);
        }
    }

    /**
     * Handle the execution of a single task.
     * @param array $task
     * @param array $currentTasksToRun
     * @param ScheduledTaskDAO $taskDao
     */
    protected function _executeSingleTask(array $task, array $currentTasksToRun, $taskDao): void {
        $className = $task['className'];
        $pos = strrpos($className, '.');
        $baseClassName = ($pos === false) ? $className : substr($className, $pos + 1);
        $taskArgs = $task['args'] ?? [];

        DBConnection::ensureConnection();

        $updateResult = 0;
        if (in_array($task, $currentTasksToRun, true)) {
            $updateResult = $taskDao->updateLastRunTime($className, time());
        }

        if ($updateResult === false || $updateResult === 1) {
            try {
                import($className);
                $taskInstance = new $baseClassName($taskArgs);
                $taskInstance->execute();
            } catch (Throwable $e) {
                error_log('Acron: task "' . $className . '" failed with exception: ' . $e->getMessage());
            }
        }
    }

    /**
     * Parse all scheduled tasks files and save the result object in database.
     */
    public function _parseCrontab() {
        $taskFilesPath = [];
        PluginRegistry::loadAllPlugins();
        HookRegistry::dispatch('AcronPlugin::parseCronTab', [&$taskFilesPath]);
        $taskFilesPath[] = Config::getVar('general', 'registry_dir') . '/scheduledTasks.xml';

        $xmlParser = new PKPXMLParser();
        $tasks = [];
        
        foreach ($taskFilesPath as $filePath) {
            $parsedTasks = $this->_extractTasksFromXml($filePath, $xmlParser);
            $tasks = array_merge($tasks, $parsedTasks);
        }
        $xmlParser->destroy();

        $this->updateSetting(0, 'crontab', $tasks, 'object');
    }

    /**
     * Extract tasks from a specific XML file.
     * @param string $filePath
     * @param PKPXMLParser $xmlParser
     * @return array
     */
    protected function _extractTasksFromXml(string $filePath, PKPXMLParser $xmlParser): array {
        $tree = $xmlParser->parse($filePath);

        if (!$tree) {
            error_log('Wizdam Acron Error: Error parsing scheduled tasks XML file: ' . $filePath);
            return []; 
        }

        $extractedTasks = [];
        foreach ($tree->getChildren() as $taskNode) {
            $extractedTasks[] = $this->_buildTaskDataArray($taskNode);
        }

        return $extractedTasks;
    }

    /**
     * Build a standardized task data array from an XML node.
     * @param XMLNode $taskNode
     * @return array
     */
    protected function _buildTaskDataArray($taskNode): array {
        $frequency = $taskNode->getChildByName('frequency');
        $args = ScheduledTaskHelper::getTaskArgs($taskNode);

        $minHoursRunPeriod = 24;
        $setDefaultFrequency = true;
        $frequencyAttributes = []; 

        if ($frequency) {
            $frequencyAttributes = $frequency->getAttributes();
            if (is_array($frequencyAttributes)) {
                foreach ($frequencyAttributes as $key => $value) {
                    if ($value != 0) {
                        $setDefaultFrequency = false;
                        break;
                    }
                }
            }
        }

        return [
            'className' => $taskNode->getAttribute('class'),
            'frequency' => $setDefaultFrequency ? ['hour' => $minHoursRunPeriod] : $frequencyAttributes,
            'args' => $args
        ];
    }

    /**
     * Get all scheduled tasks that needs to be executed.
     * @return array
     */
    public function _getTasksToRun() {
        if (!$this->getSetting(0, 'enabled')) {
            return [];
        }

        $scheduledTasks = $this->_loadMasterCrontab();
        $tasksToRun = [];

        if (is_array($scheduledTasks)) {
            foreach ($scheduledTasks as $task) {
                if ($this->_isTaskReadyToExecute($task)) {
                    $tasksToRun[] = $task;
                }
            }
        }

        return $tasksToRun;
    }

    /**
     * Load the master crontab from database, or trigger parsing if not available.
     * @return array
     */
    protected function _loadMasterCrontab(): array {
        $scheduledTasks = $this->getSetting(0, 'crontab');
        if ($scheduledTasks === null) {
            $this->_parseCrontab();
            $scheduledTasks = $this->getSetting(0, 'crontab');
        }
        
        return is_array($scheduledTasks) ? $scheduledTasks : [];
    }

    /**
     * Evaluate if a task is ready to be executed based on its frequency.
     * @param array $task
     * @return bool
     */
    protected function _isTaskReadyToExecute(array $task): bool {
        if (!isset($task['frequency']) || !is_array($task['frequency'])) {
            return false;
        }

        $key = key($task['frequency']);
        if (!$key) {
            return false;
        }

        $frequencyNode = new XMLNode();
        $frequencyNode->setAttribute($key, current($task['frequency']));
        
        return ScheduledTaskHelper::checkFrequency($task['className'], $frequencyNode);
    }

}
?>
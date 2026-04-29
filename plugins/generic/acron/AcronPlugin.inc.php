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
 * REFACTORED: Wizdam Edition (Throttling + Strict Standards)
 */

import('lib.pkp.classes.plugins.GenericPlugin');
import('lib.pkp.classes.scheduledTask.ScheduledTaskHelper');

class AcronPlugin extends GenericPlugin {

    /** @var string */
    public $_workingDir;

    /** @var array */
    public $_tasksToRun;
    
    // Penunjang Kehidupan Singleton di Fase Shutdown
    protected $_preservedApplication = null;
    protected $_preservedRequest = null;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AcronPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::AcronPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        $this->__construct();
    }

    /**
     * Plugin registration. Registers hooks and load locale data.
     * @see LazyLoadPlugin::register()
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        
        // [MODERNISASI] Hapus referensi & pada $this
        HookRegistry::register('Installer::postInstall', array($this, 'callbackPostInstall'));

        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return $success;
        
        if ($success) {
            $this->addLocaleData();
            HookRegistry::register('LoadHandler', array($this, 'callbackLoadHandler'));
            // Need to reload cron tab on possible enable or disable generic plugin actions.
            HookRegistry::register('PluginHandler::plugin', array($this, 'callbackManage'));
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
     * @param Request|null $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $isEnabled = $this->getEnabled($request);

        $verbs = array(); 
        $verbs[] = array(
            ($isEnabled ? 'disable' : 'enable'),
            __($isEnabled ? 'manager.plugins.disable' : 'manager.plugins.enable')
        );
        $verbs[] = array(
            'reload', __('plugins.generic.acron.reload')
        );
        return $verbs;
    }

    /**
     * Manage plugin actions: enable, disable, reload.
     * @see GenericPlugin::manage()
     * [WIZDAM PROTOCOL] Modernized: Used NotificationManager
     * @param string $verb
     * @param array $args
     * @param string $message
     * @param array|null $messageParams
     * @param Request|null $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message, array $messageParams = null, $request = null): bool {
        switch ($verb) {
            case 'enable':
                $this->updateSetting(0, 'enabled', true);
                
                // [WIZDAM] Gunakan NotificationManager
                import('classes.notification.NotificationManager');
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_SUCCESS,
                    array('contents' => __('plugins.generic.acron.enabled'))
                );
                break;

            case 'disable':
                $this->updateSetting(0, 'enabled', false);
                
                // [WIZDAM] Gunakan NotificationManager
                import('classes.notification.NotificationManager');
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_SUCCESS,
                    array('contents' => __('plugins.generic.acron.disabled'))
                );
                break;

            case 'reload':
                $this->_parseCrontab();
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
     * [WIZDAM FIX] IMPLEMENTED THROTTLING TO PREVENT DB OVERLOAD
     * @param string $hookName
     * @param array $args
     * @return bool
     */
    public function callbackLoadHandler($hookName, $args) {
        // [WIZDAM FIX] Probability Gate, Pastikan tipe data integer untuk mt_rand
        // Default: 100 (Artinya hanya 1 dari 100 request yang akan memicu cek database)
        $throttleRatio = (int) Config::getVar('general', 'acron_throttle', 100);

        // Generate angka acak 1 sampai 100. Jika bukan 1, batalkan proses.
        if (mt_rand(1, $throttleRatio) !== 1) {
            return false;
        }

        // Jika lolos gate (1% chance), baru jalankan logika berat
        $tasksToRun = $this->_getTasksToRun();

        if (!empty($tasksToRun)) {
            $this->_workingDir = getcwd();
            $this->_tasksToRun = $tasksToRun;

            ob_start();
            
            // Simpan (Strong Reference) objek inti dari Registry 
            // SEBELUM Garbage Collection PHP 8 menghancurkannya.
            $this->_preservedApplication = Registry::get('application');
            $this->_preservedRequest = Registry::get('request');
            
            register_shutdown_function(array($this, 'shutdownFunction'));
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
        // [WIZDAM FIX] Cegah Undefined array key warning
        $verb = $args[0] ?? '';
        $plugin = $args[4] ?? null; /* @var $plugin LazyLoadPlugin */

        // Only interested in plugins that can be enabled/disabled.
        // [WIZDAM FIX] Replaced is_a with instanceof
        if (!($plugin instanceof LazyLoadPlugin)) return false;

        // Only interested in enable/disable actions.
        if ($verb !== 'enable' && $verb !== 'disable') return false;

        // Check if the plugin wants to add its own scheduled task into the cron tab.
        $hooks = HookRegistry::getHooks();
        $hookName = 'AcronPlugin::parseCronTab';
        if (!isset($hooks[$hookName])) return false;

        foreach($hooks[$hookName] as $index => $callback) {
            if ($callback[0] == $plugin) {
                $this->_parseCrontab();
                break;
            }
        }

        return false;
    }

    /**
     * Shutdown callback.
     * Mempertahankan semantik infrastruktur background process, 
     * mendelegasikan domain logic eksekusi task agar terisolasi.
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
     * Compatible with both Nginx/PHP-FPM and Apache mod_php.
     * @param Request|null $request
     */
    protected function _closeHttpConnectionGracefully(): void {
        // Lepaskan lock session agar user bisa lanjut browsing di tab lain
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // [WIZDAM MODERN COMPATIBILITY] Eksekusi absolut untuk PHP-FPM
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
            return;
        }

        // Fallback untuk server Legacy (Apache mod_php)
        if (!headers_sent()) {
            header("Connection: close");
            header("Content-Encoding: none");
            header("Content-Length: " . (string) ob_get_length());
        }

        // Kuras output buffer secara aman
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }

    // --- MODULAR HELPER METHODS --- //

    /**
     * Arrange task execution flow and delegate to single task executor.
     * @param array $tasksToRun
     * @param ScheduledTaskDAO $taskDao 
     * @param array $currentTasksToRun
     */
    protected function _executeScheduledTasks(array $tasksToRun): void {
        $taskDao = DAORegistry::getDAO('ScheduledTaskDAO');
        $currentTasksToRun = $this->_getTasksToRun(); // Refresh state untuk race condition
        
        foreach ($tasksToRun as $task) {
            $this->_executeSingleTask($task, $currentTasksToRun, $taskDao);
        }
    }

    /**
     * Handle the execution of a single task: parsing class,
     * race condition lock, and execution.
     * @param array $task
     * @param array $currentTasksToRun
     * @param ScheduledTaskDAO $taskDao
     */
    protected function _executeSingleTask(array $task, array $currentTasksToRun, $taskDao): void {
        $className = $task['className'];
        $pos = strrpos($className, '.');
        $baseClassName = ($pos === false) ? $className : substr($className, $pos + 1);
        $taskArgs = isset($task['args']) ? $task['args'] : array();

        // Race condition handling
        $updateResult = 0;
        if (in_array($task, $currentTasksToRun)) { 
            $updateResult = $taskDao->updateLastRunTime($className, time());
        }

        // Jika berhasil mengambil alih eksekusi (lock), jalankan
        if ($updateResult === false || $updateResult === 1) {
            import($className);
            $taskInstance = new $baseClassName($taskArgs);
            $taskInstance->execute();
        }
    }


    //
    // Private helper methods.
    //
    
    /**
     * Parse all scheduled tasks files and save the result object in database.
     * (Orchestrator: Mengatur alur penemuan file dan penyimpanan)
     */
    public function _parseCrontab() {
        // 1. Kumpulkan semua file crontab (dari core dan plugins)
        $taskFilesPath = array();
        PluginRegistry::loadAllPlugins();
        HookRegistry::dispatch('AcronPlugin::parseCronTab', array(&$taskFilesPath));
        $taskFilesPath[] = Config::getVar('general', 'registry_dir') . '/scheduledTasks.xml';

        // 2. Ekstrak data tugas dari setiap file
        $xmlParser = new XMLParser();
        $tasks = array();
        
        foreach ($taskFilesPath as $filePath) {
            $parsedTasks = $this->_extractTasksFromXml($filePath, $xmlParser);
            $tasks = array_merge($tasks, $parsedTasks);
        }
        $xmlParser->destroy();

        // 3. Simpan hasil kompilasi ke database
        $this->updateSetting(0, 'crontab', $tasks, 'object');
    }

    // --- HELPER UNTUK PARSING CRONTAB --- //

    /**
     * Extract tasks from a specific XML file.
     * @param string $filePath
     * @param XMLParser $xmlParser
     * @return array Array tugas yang diekstrak, atau array kosong salah parsing
     */
    protected function _extractTasksFromXml(string $filePath, XMLParser $xmlParser): array {
        $tree = $xmlParser->parse($filePath);

        if (!$tree) {
            error_log('Wizdam Acron Error: Error parsing scheduled tasks XML file: ' . $filePath);
            return array(); 
        }

        $extractedTasks = array();
        foreach ($tree->getChildren() as $taskNode) {
            $extractedTasks[] = $this->_buildTaskDataArray($taskNode);
        }

        return $extractedTasks;
    }

    /**
     * Build a standardized task data array from an XML node.
     * Transform a single XML node into a standardized task data array.
     * @param XMLNode $taskNode
     * @return array Array dengan keys: 'className', 'frequency', 'args'.
     */
    protected function _buildTaskDataArray($taskNode): array {
        $frequency = $taskNode->getChildByName('frequency');
        $args = ScheduledTaskHelper::getTaskArgs($taskNode);

        $minHoursRunPeriod = 24;
        $setDefaultFrequency = true;
        $frequencyAttributes = array(); 

        if ($frequency) {
            $frequencyAttributes = $frequency->getAttributes();
            if (is_array($frequencyAttributes)) {
                foreach($frequencyAttributes as $key => $value) {
                    if ($value != 0) {
                        $setDefaultFrequency = false;
                        break;
                    }
                }
            }
        }

        return array(
            'className' => $taskNode->getAttribute('class'),
            'frequency' => $setDefaultFrequency ? array('hour' => $minHoursRunPeriod) : $frequencyAttributes,
            'args' => $args
        );
    }

    /**
     * Get all scheduled tasks that needs to be executed.
     * (Orchestrator: Mengambil daftar master, memfilter yang siap dieksekusi)
     * @return array Array ready run with keys: 'className', 'frequency', 'args'
     */
    public function _getTasksToRun() {
        if (!$this->getSetting(0, 'enabled')) {
            return array();
        }

        $scheduledTasks = $this->_loadMasterCrontab();
        $tasksToRun = array();

        if (is_array($scheduledTasks)) {
            foreach ($scheduledTasks as $task) {
                // Isolasi logika frekuensi yang rumit
                if ($this->_isTaskReadyToExecute($task)) {
                    $tasksToRun[] = $task;
                }
            }
        }

        return $tasksToRun;
    }

    // --- HELPER UNTUK EVALUASI TUGAS --- //

    /**
     * Load the master crontab from database, or 
     * trigger parsing if not available.
     * @return array Array tugas yang tersimpan di database, atau array kosong.
     */
    protected function _loadMasterCrontab(): array {
        $scheduledTasks = $this->getSetting(0, 'crontab');
        if (is_null($scheduledTasks)) {
            $this->_parseCrontab();
            $scheduledTasks = $this->getSetting(0, 'crontab');
        }
        
        // Pastikan selalu mengembalikan array meskipun database gagal
        return is_array($scheduledTasks) ? $scheduledTasks : array();
    }

    /**
     * Evaluate if a task is ready to be executed based on its frequency.
     * @param array $task Array dengan keys: 'className', 'frequency', 'args'.
     * @return bool True jika tugas siap dieksekusi, false jika tidak.
     */
    protected function _isTaskReadyToExecute(array $task): bool {
        if (!isset($task['frequency']) || !is_array($task['frequency'])) {
            return false;
        }

        $key = key($task['frequency']);
        if (!$key) {
            return false;
        }

        // Rekonstruksi XMLNode (Legacy OJS requirement untuk checkFrequency)
        $frequencyNode = new XMLNode();
        $frequencyNode->setAttribute($key, current($task['frequency']));
        
        return ScheduledTaskHelper::checkFrequency($task['className'], $frequencyNode);
    }
}
?>
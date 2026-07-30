<?php
declare(strict_types=1);

/**
 * @file classes/scheduledTask/ScheduledTask.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ScheduledTask
 * @ingroup scheduledTask
 * @see ScheduledTaskDAO
 *
 * @brief Base class for executing scheduled tasks.
 * All scheduled task classes must extend this class and implement execute().
 */

import('lib.pkp.classes.scheduledTask.ScheduledTaskHelper');

class ScheduledTask {

    /** @var array Task arguments */
    protected $_args = [];

    /** @var string|null This process id. */
    protected $_processId = null;

    /** @var string|null File path in which execution log messages will be written. */
    protected $_executionLogFile = null;

    /** @var ScheduledTaskHelper|null */
    protected $_helper = null;


    /**
     * Constructor.
     * @param array $args
     */
    public function __construct($args = []) {
        $this->_args = is_array($args) ? $args : [];
        $this->_processId = (string) uniqid();

        // Ensure common locale keys are available
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_ADMIN, LOCALE_COMPONENT_CORE_COMMON);
        
        // Check the scheduled task execution log folder.
        import('lib.pkp.classes.file.PrivateFileManager');
        $fileMgr = new PrivateFileManager();

        $scheduledTaskFilesPath = realpath($fileMgr->getBasePath()) . DIRECTORY_SEPARATOR . SCHEDULED_TASK_EXECUTION_LOG_DIR;
        $this->_executionLogFile = $scheduledTaskFilesPath . DIRECTORY_SEPARATOR . str_replace(' ', '', (string) $this->getName()) . 
            '-' . $this->getProcessId() . '-' . date('Ymd') . '.log';
        
        if (!$fileMgr->fileExists($scheduledTaskFilesPath, 'dir')) {
            $success = $fileMgr->mkdirtree($scheduledTaskFilesPath);
            if (!$success) {
                // files directory wrong configuration?
                // [LUMERA] Fatal Error yang lebih informatif daripada assert(false)
                fatalError("Scheduled Task Log Directory is missing and cannot be created: " . $scheduledTaskFilesPath);
                $this->_executionLogFile = null;
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param array $args
     */
    public function ScheduledTask($args = []) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }


    //
    // Protected methods.
    //

    /**
     * Get this process id.
     * @return string
     */
    public function getProcessId() {
        return (string) $this->_processId;
    }

    /**
     * Get scheduled task helper object.
     * @return ScheduledTaskHelper
     */
    public function getHelper() {
        if ($this->_helper === null) {
            $this->_helper = new ScheduledTaskHelper();
        }
        return $this->_helper;
    }

    /**
     * Get the scheduled task name. Override to define a custom task name.
     * @return string
     */
    public function getName() {
        return (string) __('admin.scheduledTask');
    }

    /**
     * Add an entry into the execution log.
     * @param string $message
     * @param string|null $type
     * @return void
     */
    public function addExecutionLogEntry($message, $type = null) {
        $logFile = $this->_executionLogFile;

        if ($message === null || $message === '') {
            return;
        }

        if ($type !== null && $type !== '') {
            $log = '[' . Core::getCurrentDate() . '] ' . '[' . __((string) $type) . '] ' . (string) $message;
        } else {
            $log = (string) $message;
        }

        // [LUMERA] Modern File Write
        // Menggunakan file_put_contents dengan LOCK_EX (Exclusive Lock) untuk thread safety.
        // FILE_APPEND agar log tidak menimpa data sebelumnya.
        if ($logFile !== null) {
            if (file_put_contents($logFile, $log . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
                // Jika gagal (misal disk penuh), log ke error log server sebagai cadangan
                error_log("Lumera ScheduledTask Error: Could not write to log file: " . $logFile);
            }
        }
    }


    //
    // Protected abstract methods.
    //

    /**
     * Implement this method to execute the task actions.
     * @return bool
     */
    public function executeActions() {
        // In case task does not implement it.
        fatalError('ScheduledTask does not implement executeActions()!');
    }


    //
    // Public methods.
    //
    
    /**
     * Make sure the execution process follow the required steps.
     * @return bool
     */
    public function execute() {
        $this->addExecutionLogEntry((string) Config::getVar('general', 'base_url'));
        $this->addExecutionLogEntry(__('admin.scheduledTask.startTime'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);

        $result = (bool) $this->executeActions();

        $this->addExecutionLogEntry(__('admin.scheduledTask.stopTime'), SCHEDULED_TASK_MESSAGE_TYPE_NOTICE);

        $helper = $this->getHelper();
        $helper->notifyExecutionResult((string) $this->_processId, (string) $this->getName(), $result, $this->_executionLogFile);

        return $result;
    }
    
}
?>
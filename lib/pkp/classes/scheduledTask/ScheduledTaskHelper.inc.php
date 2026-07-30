<?php
declare(strict_types=1);

/**
 * @file classes/scheduledTask/ScheduledTaskHelper.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ScheduledTaskHelper
 * @ingroup scheduledTask
 *
 * @brief Helper class for common scheduled tasks operations.
 */

define('SCHEDULED_TASK_MESSAGE_TYPE_COMPLETED', 'common.completed');
define('SCHEDULED_TASK_MESSAGE_TYPE_ERROR', 'common.error');
define('SCHEDULED_TASK_MESSAGE_TYPE_WARNING', 'common.warning');
define('SCHEDULED_TASK_MESSAGE_TYPE_NOTICE', 'common.notice');
define('SCHEDULED_TASK_EXECUTION_LOG_DIR', 'scheduledTaskLogs');

class ScheduledTaskHelper {

    /** @var string Contact email. */
    protected $_contactEmail = '';

    /** @var string Contact name. */
    protected $_contactName = '';

    /**
     * Constructor.
     * @param string $email (optional)
     * @param string $contactName (optional)
     */
    public function __construct($email = '', $contactName = '') {
        if ($email === '' || $contactName === '') {
            /** @var SiteDAO $siteDao */
            $siteDao = DAORegistry::getDAO('SiteDAO');
            /** @var Site $site */
            $site = $siteDao->getSite();
            $email = (string) $site->getLocalizedContactEmail();
            $contactName = (string) $site->getLocalizedContactName();
        }

        $this->_contactEmail = (string) $email;
        $this->_contactName = (string) $contactName;
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param string $email
     * @param string $contactName
     */
    public function ScheduledTaskHelper($email = '', $contactName = '') {
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
     * Get mail object.
     * @return Mail
     */
    public function getMail() {
        import('lib.pkp.classes.mail.Mail');
        return new Mail();
    }

    /**
     * Get the arguments for a task from the parsed XML.
     * @param mixed $task
     * @return array
     */
    public static function getTaskArgs($task) {
        $args = [];
        $index = 0;

        while (($arg = $task->getChildByName('arg', $index)) !== null) {
            $args[] = $arg->getValue();
            $index++;
        }

        return $args;
    }

    /**
     * Check if the specified task should be executed according to the specified
     * frequency and its last run time.
     * @param string $className
     * @param mixed $frequency XMLNode
     * @return bool
     */
    public static function checkFrequency($className, $frequency) {
        $isValid = true;
        /** @var ScheduledTaskDAO $taskDao */
        $taskDao = DAORegistry::getDAO('ScheduledTaskDAO');
        $lastRunTime = $taskDao->getLastRunTime((string) $className);

        // Check day of week
        $dayOfWeek = $frequency->getAttribute('dayofweek');
        if ($dayOfWeek !== null) {
            $isValid = self::_isInRange($dayOfWeek, (int) date('w'), $lastRunTime, 'day', strtotime('-1 week'));
        }

        if ($isValid) {
            // Check month
            $month = $frequency->getAttribute('month');
            if ($month !== null) {
                $isValid = self::_isInRange($month, (int) date('n'), $lastRunTime, 'month', strtotime('-1 year'));
            }
        }

        if ($isValid) {
            // Check day
            $day = $frequency->getAttribute('day');
            if ($day !== null) {
                $isValid = self::_isInRange($day, (int) date('j'), $lastRunTime, 'day', strtotime('-1 month'));
            }
        }

        if ($isValid) {
            // Check hour
            $hour = $frequency->getAttribute('hour');
            if ($hour !== null) {
                $isValid = self::_isInRange($hour, (int) date('G'), $lastRunTime, 'hour', strtotime('-1 day'));
            }
        }

        if ($isValid) {
            // Check minute
            $minute = $frequency->getAttribute('minute');
            if ($minute !== null) {
                $isValid = self::_isInRange($minute, (int) date('i'), $lastRunTime, 'min', strtotime('-1 hour'));
            }
        }

        return $isValid;
    }

    /**
     * Notifies site administrator about the task execution result.
     * @param int|string $id
     * @param string $name
     * @param bool $result
     * @param string $executionLogFile Task execution log file path
     * @return bool
     */
    public function notifyExecutionResult($id, $name, $result, $executionLogFile = '') {
        $reportErrorOnly = (bool) Config::getVar('general', 'scheduled_tasks_report_error_only', true);

        if (!$result || !$reportErrorOnly) {
            $message = $this->getMessage((string) $executionLogFile);
            
            if ($result) {
                // Success.
                $type = SCHEDULED_TASK_MESSAGE_TYPE_COMPLETED;
            } else {
                // Error.
                $type = SCHEDULED_TASK_MESSAGE_TYPE_ERROR;
            }

            $subject = (string) $name . ' - ' . (string) $id . ' - ' . __((string) $type);
            return $this->_sendEmail($message, $subject);
        }

        return false;
    }

    /**
     * Get execution log email message.
     * @param string $executionLogFile
     * @return string
     */
    public function getMessage($executionLogFile) {
        if ($executionLogFile === null || $executionLogFile === '') {
            return __('admin.scheduledTask.noLog');
        }
        
        $application = Application::getApplication();
        $request = $application->getRequest();
        $router = $request->getRouter();
        $downloadLogUrl = $router->url($request, 'index', 'admin', 'downloadScheduledTaskLogFile', null, ['file' => basename((string) $executionLogFile)]);
        return __('admin.scheduledTask.downloadLog', ['url' => $downloadLogUrl]);
    }

    //
    // Static methods.
    //

    /**
     * Clear tasks execution log files.
     * @return void
     */
    public static function clearExecutionLogs() {
        import('lib.pkp.classes.file.PrivateFileManager');
        $fileMgr = new PrivateFileManager();
    
        $fileMgr->rmtree($fileMgr->getBasePath() . DIRECTORY_SEPARATOR . SCHEDULED_TASK_EXECUTION_LOG_DIR);    
    }

    /**
     * Download execution log file.
     * @param string $file
     * @return void
     */
    public function downloadExecutionLog($file) {
        import('lib.pkp.classes.file.PrivateFileManager');
        $fileMgr = new PrivateFileManager();

        $fileMgr->downloadFile($fileMgr->getBasePath() . DIRECTORY_SEPARATOR . SCHEDULED_TASK_EXECUTION_LOG_DIR . DIRECTORY_SEPARATOR . (string) $file);    
    }

    //
    // Private helper methods.
    //

    /**
     * Send email to the site administrator.
     * @param string $message
     * @param string $subject
     * @return bool
     */
    protected function _sendEmail($message, $subject) {
        $mail = $this->getMail();
        $mail->addRecipient((string) $this->_contactEmail, (string) $this->_contactName);
        $mail->setSubject((string) $subject);
        $mail->setBody((string) $message);

        return $mail->send();
    }

    /**
     * Check if a value is within the specified range.
     * @param string $rangeStr the range (e.g., 0, 1-5, *, etc.)
     * @param int $currentValue value to check if its in the range
     * @param int $lastTimestamp the last time the task was executed
     * @param string $timeCompareStr value to use in strtotime("-X $timeCompareStr")
     * @param int $cutoffTimestamp value will be considered valid if older than this
     * @return bool
     */
    public static function _isInRange($rangeStr, $currentValue, $lastTimestamp, $timeCompareStr, $cutoffTimestamp) {
        $isValid = false;
        $rangeArray = explode(',', (string) $rangeStr);

        if ((int) $cutoffTimestamp > (int) $lastTimestamp) {
            // Execute immediately if the cutoff time period has past since the task was last run
            $isValid = true;
        }

        for ($i = 0, $count = count($rangeArray); !$isValid && ($i < $count); $i++) {
            if ($rangeArray[$i] === '*') {
                // Is wildcard
                $isValid = true;
            } elseif (is_numeric($rangeArray[$i])) {
                // Is just a value
                $isValid = ((int) $currentValue === (int) $rangeArray[$i]);
            } elseif (preg_match('/^(\d*)\-(\d*)$/', $rangeArray[$i], $matches)) {
                // Is a range
                $isValid = self::_isInNumericRange((int) $currentValue, (int) $matches[1], (int) $matches[2]);
            } elseif (preg_match('/^(.+)\/(\d+)$/', $rangeArray[$i], $matches)) {
                // Is a range with a skip factor
                $skipRangeStr = $matches[1];
                $skipFactor = (int) $matches[2];

                if ($skipRangeStr === '*') {
                    $isValid = true;
                } elseif (preg_match('/^(\d*)\-(\d*)$/', $skipRangeStr, $matches)) {
                    $isValid = self::_isInNumericRange((int) $currentValue, (int) $matches[1], (int) $matches[2]);
                }

                if ($isValid) {
                    // Check against skip factor
                    $isValid = (strtotime("-$skipFactor $timeCompareStr") > (int) $lastTimestamp);
                }
            }
        }

        return $isValid;
    }

    /**
     * Check if a numeric value is within the specified range.
     * @param int $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function _isInNumericRange($value, $min, $max) {
        return ((int) $value >= (int) $min && (int) $value <= (int) $max);
    }

}
?>
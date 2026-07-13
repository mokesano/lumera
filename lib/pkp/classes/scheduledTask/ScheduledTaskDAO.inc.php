<?php
declare(strict_types=1);

/**
 * @file classes/scheduledTask/ScheduledTaskDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ScheduledTaskDAO
 * @ingroup scheduledTask
 * @see ScheduledTask
 *
 * @brief Operations for retrieving and modifying Scheduled Task data.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

class ScheduledTaskDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ScheduledTaskDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . ". Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the last time a scheduled task was executed.
     * 
     * @param string $className The class name of the scheduled task.
     * @return int The Unix timestamp of the last run, or 0 if never run.
     */
    public function getLastRunTime(string $className): int {
        $result = $this->retrieve(
            'SELECT last_run FROM scheduled_tasks WHERE class_name = ?',
            [$className]
        );

        $returner = 0;
        if (!$result->EOF) {
            /** @var array|bool $fields */
            $fields = $result->fields;
            $dbDate = isset($fields[0]) ? (string) $fields[0] : '';
            
            if ($dbDate !== '') {
                $parsedDate = strtotime((string) $this->datetimeFromDB($dbDate));
                if ($parsedDate !== false) {
                    $returner = $parsedDate;
                }
            }
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Update a scheduled task's last run time.
     * 
     * @param string $className The class name of the scheduled task.
     * @param int|null $timestamp Optional Unix timestamp. If null, current time (NOW()) is used.
     * @return int The number of affected rows.
     */
    public function updateLastRunTime(string $className, ?int $timestamp = null): int {
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM scheduled_tasks WHERE class_name = ?',
            [$className]
        );

        /** @var array|bool $fields */
        $fields = $result->fields;
        $exists = isset($fields[0]) && (int) $fields[0] > 0;
        $result->Close();

        if ($exists) {
            if ($timestamp !== null) {
                $this->update(
                    'UPDATE scheduled_tasks SET last_run = ? WHERE class_name = ?',
                    [$this->datetimeToDB($timestamp), $className]
                );
            } else {
                $this->update(
                    'UPDATE scheduled_tasks SET last_run = NOW() WHERE class_name = ?',
                    [$className]
                );
            }
        } else {
            if ($timestamp !== null) {
                $this->update(
                    'INSERT INTO scheduled_tasks (class_name, last_run) VALUES (?, ?)',
                    [$className, $this->datetimeToDB($timestamp)]
                );
            } else {
                $this->update(
                    'INSERT INTO scheduled_tasks (class_name, last_run) VALUES (?, NOW())',
                    [$className]
                );
            }
        }

        return $this->getAffectedRows();
    }
    
}
?>
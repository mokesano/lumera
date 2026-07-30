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
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function ScheduledTaskDAO() {
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
     * Get the last time a scheduled task was executed.
     * @param string $className
     * @return int
     */
    public function getLastRunTime(string $className): int {
        $result = $this->retrieve(
            'SELECT last_run FROM scheduled_tasks WHERE class_name = ?',
            [$className]
        );

        $returner = 0;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $dbDate = isset($row['last_run']) ? (string) $row['last_run'] : '';
            
            if ($dbDate !== '') {
                $parsedDate = strtotime((string) $this->datetimeFromDB($dbDate));
                if ($parsedDate !== false) {
                    $returner = (int) $parsedDate;
                }
            }
        }

        if ($result) {
            $result->Close();
        }

        return $returner;
    }

    /**
     * Update a scheduled task's last run time.
     * @param string $className
     * @param int|null $timestamp
     * @return int
     */
    public function updateLastRunTime(string $className, ?int $timestamp = null): int {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM scheduled_tasks WHERE class_name = ?',
            [$className]
        );

        $exists = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $exists = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }

        $dbTimestamp = date('Y-m-d H:i:s', $timestamp ?? time());
        if ($exists) {
            $this->update(
                'UPDATE scheduled_tasks SET last_run = ? WHERE class_name = ?',
                [$dbTimestamp, $className]
            );
        } else {
            $this->update(
                'INSERT INTO scheduled_tasks (class_name, last_run) VALUES (?, ?)',
                [$className, $dbTimestamp]
            );
        }

        return (int) $this->getAffectedRows();
    }

    /**
     * Repair rows whose last_run was corrupted into the invalid
     * '0000-00-00 00:00:00' zero-date (e.g. by a bound-parameter bug in
     * updateLastRunTime()), by resetting them to the current time so their
     * tasks become schedulable again.
     * @return int Number of rows repaired.
     */
    public function repairInvalidLastRunTimes(): int {
        $this->update(
            "UPDATE scheduled_tasks SET last_run = ? WHERE last_run = '0000-00-00 00:00:00'",
            [date('Y-m-d H:i:s')]
        );
        return $this->getAffectedRows();
    }
    
}
?>
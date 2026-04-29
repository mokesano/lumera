<?php
declare(strict_types=1);

/**
 * @defgroup scheduledTask
 */

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
 * [WIZDAM EDITION] Database-backed Task Registry (PHP 7.4/8.x Compatible)
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
            // [CCTV] Smart Error Log
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ScheduledTaskDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the last time a scheduled task was executed.
     * @param $className string
     * @return int
     */
    public function getLastRunTime($className) {
        // [MODERNISASI] Hapus referensi & pada return value method retrieve
        $result = $this->retrieve(
            'SELECT last_run FROM scheduled_tasks WHERE class_name = ?',
            array($className)
        );

        if ($result->RecordCount() == 0) {
            $returner = 0;
        } else {
            $returner = strtotime($this->datetimeFromDB($result->fields[0]));
        }

        $result->Close();
        unset($result);

        return (int) $returner;
    }

    /**
     * Update a scheduled task's last run time.
     * @param $className string
     * @param $timestamp int optional, if omitted the current time is used.
     * @return int
     */
    public function updateLastRunTime($className, $timestamp = null) {
        // [MODERNISASI] Hapus referensi &
        $result = $this->retrieve(
            'SELECT COUNT(*) FROM scheduled_tasks WHERE class_name = ?',
            array($className)
        );

        // [WIZDAM] Optimasi pengecekan logic
        $exists = (isset($result->fields[0]) && $result->fields[0] != 0);
        $result->Close(); // Close result set early
        
        if ($exists) {
            if (isset($timestamp)) {
                $this->update(
                    'UPDATE scheduled_tasks SET last_run = ' . $this->datetimeToDB($timestamp) . ' WHERE class_name = ?',
                    array($className)
                );
            } else {
                $this->update(
                    'UPDATE scheduled_tasks SET last_run = NOW() WHERE class_name = ?',
                    array($className)
                );
            }

        } else {
            if (isset($timestamp)) {
                $this->update(
                    sprintf('INSERT INTO scheduled_tasks (class_name, last_run)
                    VALUES (?, %s)', $this->datetimeToDB($timestamp)),
                    array($className)
                );
            } else {
                $this->update(
                    'INSERT INTO scheduled_tasks (class_name, last_run)
                    VALUES (?, NOW())',
                    array($className)
                );
            }
        }

        return $this->getAffectedRows();
    }
}

?>
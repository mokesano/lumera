<?php
declare(strict_types=1);

/**
 * @file classes/log/EventLogDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EventLogDAO
 * @ingroup log
 * @see EventLogEntry
 *
 * @brief Class for inserting/accessing event log entries.
 */

import ('lib.pkp.classes.log.EventLogEntry');

class EventLogDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EventLogDAO() {
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
     * Retrieve a log entry by ID.
     * @param int $logId
     * @param int|null $assocType optional
     * @param int|null $assocId optional
     * @return EventLogEntry|null
     */
    public function getById($logId, $assocType = null, $assocId = null) {
        $params = [(int) $logId];
        if (isset($assocType)) {
            $params[] = (int) $assocType;
            $params[] = (int) $assocId;
        }

        $result = $this->retrieve(
            'SELECT * FROM event_log WHERE log_id = ?' .
            (isset($assocType) ? ' AND assoc_type = ? AND assoc_id = ?' : ''),
            $params
        );

        $returner = null;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);
            $returner = $this->build($row);
        }

        $result->Close();
        unset($result);

        return $returner;
    }

    /**
     * Retrieve all log entries matching the specified association.
     * @param int $assocType
     * @param int $assocId
     * @param object|null $rangeInfo optional
     * @return DAOResultFactory containing matching EventLogEntry ordered by sequence
     */
    public function getByAssoc($assocType, $assocId, $rangeInfo = null) {
        $params = [(int) $assocType, (int) $assocId];

        $result = $this->retrieveRange(
            'SELECT * FROM event_log WHERE assoc_type = ? AND assoc_id = ? ORDER BY log_id DESC',
            $params, $rangeInfo
        );

        return new DAOResultFactory($result, $this, 'build');
    }

    /**
     * Instantiate a new data object
     * @return EventLogEntry
     */
    public function newDataObject() {
        return new EventLogEntry();
    }

    /**
     * Internal function to return an EventLogEntry object from a row.
     * @param array $row
     * @return EventLogEntry
     */
    public function build($row) {
        $entry = $this->newDataObject();
        $entry->setId($row['log_id']);
        $entry->setUserId($row['user_id']);
        $entry->setDateLogged($this->datetimeFromDB($row['date_logged']));
        $entry->setIPAddress($row['ip_address']);
        $entry->setEventType($row['event_type']);
        $entry->setAssocType($row['assoc_type']);
        $entry->setAssocId($row['assoc_id']);
        $entry->setMessage($row['message']);
        $entry->setIsTranslated($row['is_translated']);

        $result = $this->retrieve('SELECT * FROM event_log_settings WHERE log_id = ?', [(int) $entry->getId()]);
        $params = [];
        while (!$result->EOF) {
            $r = $result->getRowAssoc(false);
            $params[$r['setting_name']] = $this->convertFromDB(
                $r['setting_value'],
                $r['setting_type']
            );
            $result->MoveNext();
        }
        $result->Close();
        unset($result);
        $entry->setParams($params);

        HookRegistry::dispatch('EventLogDAO::build', [&$entry, &$row]);

        return $entry;
    }

    /**
     * Insert a new log entry.
     * @param EventLogEntry $entry
     * @return int
     */
    public function insertObject($entry) {
        $this->update(
            sprintf('INSERT INTO event_log
                (user_id, date_logged, ip_address, event_type, assoc_type, assoc_id, message, is_translated)
                VALUES
                (?, %s, ?, ?, ?, ?, ?, ?)',
                $this->datetimeToDB($entry->getDateLogged())),
            [
                (int) $entry->getUserId(),
                $entry->getIPAddress(),
                (int) $entry->getEventType(),
                (int) $entry->getAssocType(),
                (int) $entry->getAssocId(),
                $entry->getMessage(),
                (int) $entry->getIsTranslated()
            ]
        );
        $entry->setId($this->getInsertLogId());

        // Add name => value entries into the settings table
        $params = $entry->getParams();
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $type = null;
                $value = $this->convertToDB($value, $type);
                $this->update(
                    'INSERT INTO event_log_settings (log_id, setting_name, setting_value, setting_type) VALUES (?, ?, ?, ?)',
                    [
                        (int) $entry->getId(),
                        $key, 
                        $value, 
                        $type
                    ]
                );
            }
        }

        return $entry->getId();
    }

    /**
     * Delete a single log entry (and associated settings).
     * @param int $logId
     * @param int|null $assocType optional
     * @param int|null $assocId optional
     */
    public function deleteObject($logId, $assocType = null, $assocId = null) {
        $params = [(int) $logId];
        if ($assocType !== null) {
            $params[] = (int) $assocType;
            $params[] = (int) $assocId;
        }
        $this->update(
            'DELETE FROM event_log WHERE log_id = ?' .
            ($assocType !== null ? ' AND assoc_type = ? AND assoc_id = ?' : ''),
            $params
        );
        if ($this->getAffectedRows()) {
            $this->update('DELETE FROM event_log_settings WHERE log_id = ?', [(int) $logId]);
        }
    }

    /**
     * Delete all log entries for an object.
     * @param int $assocType
     * @param int $assocId
     */
    public function deleteByAssoc($assocType, $assocId) {
        $entries = $this->getByAssoc($assocType, $assocId);
        while ($entry = $entries->next()) {
            $this->deleteObject($entry->getId());
        }
    }

    /**
     * Transfer all log entries to another user.
     * @param int $oldUserId
     * @param int $newUserId
     * @return bool
     */
    public function changeUser($oldUserId, $newUserId) {
        return (bool) $this->update(
            'UPDATE event_log SET user_id = ? WHERE user_id = ?',
            [(int) $newUserId, (int) $oldUserId]
        );
    }

    /**
     * Get the ID of the last inserted log entry.
     * @return int
     */
    public function getInsertLogId() {
        return $this->getInsertId('event_log', 'log_id');
    }
}
?>
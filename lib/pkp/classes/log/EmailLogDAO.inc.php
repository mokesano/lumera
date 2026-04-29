<?php
declare(strict_types=1);

/**
 * @file classes/log/EmailLogDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailLogDAO
 * @ingroup log
 * @see EmailLogEntry, Log
 *
 * @brief Class for inserting/accessing email log entries.
 */

import ('lib.pkp.classes.log.EmailLogEntry');

class EmailLogDAO extends DAO {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function EmailLogDAO() {
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
     * @return EmailLogEntry|null
     */
    public function getById($logId, $assocType = null, $assocId = null) {
        $params = [(int) $logId];
        if (isset($assocType)) {
            $params[] = (int) $assocType;
            $params[] = (int) $assocId;
        }

        $result = $this->retrieve(
            'SELECT * FROM email_log WHERE log_id = ?' .
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
     * Retrieve a log entry by event type.
     * @param int $assocType
     * @param int $assocId
     * @param int $eventType
     * @param int|null $userId optional
     * @param object|null $rangeInfo optional
     * @return DAOResultFactory
     */
    public function getByEventType($assocType, $assocId, $eventType, $userId = null, $rangeInfo = null) {
        $params = [
            (int) $assocType,
            (int) $assocId,
            (int) $eventType
        ];
        if ($userId) $params[] = (int) $userId;

        $result = $this->retrieveRange(
            'SELECT e.*
            FROM email_log e' .
            ($userId ? ' LEFT JOIN email_log_users u ON e.log_id = u.email_log_id' : '') .
            ' WHERE e.assoc_type = ? AND
                e.assoc_id = ? AND
                e.event_type = ?' .
                ($userId ? ' AND u.user_id = ?' : ''),
            $params,
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, 'build');
    }

    /**
     * Retrieve all log entries for an object matching the specified association.
     * @param int $assocType
     * @param int $assocId
     * @param object|null $rangeInfo optional
     * @return DAOResultFactory containing matching EventLogEntry ordered by sequence
     */
    public function getByAssoc($assocType = null, $assocId = null, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT *
            FROM email_log
            WHERE assoc_type = ?
                AND assoc_id = ?
            ORDER BY log_id DESC',
            [(int) $assocType, (int) $assocId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, 'build');
    }

    /**
     * Internal function to return an EmailLogEntry object from a row.
     * @param array $row
     * @return EmailLogEntry
     */
    public function build($row) {
        $entry = $this->newDataObject();
        $entry->setId($row['log_id']);
        $entry->setAssocType($row['assoc_type']);
        $entry->setAssocId($row['assoc_id']);
        $entry->setSenderId($row['sender_id']);
        $entry->setDateSent($this->datetimeFromDB($row['date_sent']));
        $entry->setIPAddress($row['ip_address']);
        $entry->setEventType($row['event_type']);
        $entry->setFrom($row['from_address']);
        $entry->setRecipients($row['recipients']);
        $entry->setCcs($row['cc_recipients']);
        $entry->setBccs($row['bcc_recipients']);
        $entry->setSubject($row['subject']);
        $entry->setBody($row['body']);

        HookRegistry::dispatch('EmailLogDAO::build', [&$entry, &$row]);

        return $entry;
    }

    /**
     * Insert a new log entry.
     * @param EmailLogEntry $entry
     * @return int
     */
    public function insertObject($entry) {
        $this->update(
            sprintf('INSERT INTO email_log
                (sender_id, date_sent, ip_address, event_type, assoc_type, assoc_id, from_address, recipients, cc_recipients, bcc_recipients, subject, body)
                VALUES
                (?, %s, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                $this->datetimeToDB($entry->getDateSent())),
            [
                (int) $entry->getSenderId(),
                $entry->getIPAddress(),
                (int) $entry->getEventType(),
                (int) $entry->getAssocType(),
                (int) $entry->getAssocId(),
                $entry->getFrom(),
                $entry->getRecipients(),
                $entry->getCcs(),
                $entry->getBccs(),
                $entry->getSubject(),
                $entry->getBody()
            ]
        );

        $entry->setId($this->getInsertLogId());
        $this->_insertLogUserIds($entry);

        return $entry->getId();
    }

    /**
     * Delete a single log entry for an object.
     * @param int $logId
     * @param int|null $assocType optional
     * @param int|null $assocId optional
     * @return bool
     */
    public function deleteObject($logId, $assocType = null, $assocId = null) {
        $params = [(int) $logId];
        if (isset($assocType)) {
            $params[] = (int) $assocType;
            $params[] = (int) $assocId;
        }
        return (bool) $this->update(
            'DELETE FROM email_log WHERE log_id = ?' .
            (isset($assocType) ? ' AND assoc_type = ? AND assoc_id = ?' : ''),
            $params
        );
    }

    /**
     * Delete all log entries for an object.
     * @param int $assocType
     * @param int $assocId
     * @return bool
     */
    public function deleteByAssoc($assocType, $assocId) {
        return (bool) $this->update(
            'DELETE FROM email_log WHERE assoc_type = ? AND assoc_id = ?',
            [(int) $assocType, (int) $assocId]
        );
    }

    /**
     * Transfer all log entries to another user.
     * @param int $oldUserId
     * @param int $newUserId
     * @return bool
     */
    public function changeUser($oldUserId, $newUserId) {
        return (bool) $this->update(
            'UPDATE email_log SET sender_id = ? WHERE sender_id = ?',
            [(int) $newUserId, (int) $oldUserId]
        );
    }

    /**
     * Get the ID of the last inserted log entry.
     * @return int
     */
    public function getInsertLogId() {
        return $this->getInsertId('email_log', 'log_id');
    }


    //
    // Private helper methods.
    //
    /**
     * Stores the correspondent user ids of the all recipient emails.
     * @param EmailLogEntry $entry
     */
    protected function _insertLogUserIds($entry) {
        $recipients = $entry->getRecipients();

        // We can use a simple regex to get emails, since we don't want to validate it.
        $pattern = '/(?<=\<)[^\>]*(?=\>)/';
        preg_match_all($pattern, $recipients, $matches);
        if (!isset($matches[0])) return;

        $userDao = DAORegistry::getDAO('UserDAO'); /* @var $userDao UserDAO */
        foreach ($matches[0] as $emailAddress) {
            $user = $userDao->getUserByEmail($emailAddress);
            if ($user instanceof User) {
                // We use replace here to avoid inserting duplicated entries
                // in table (sometimes the recipients can have the same email twice).
                $this->replace('email_log_users',
                    ['email_log_id' => (int) $entry->getId(), 'user_id' => (int) $user->getId()],
                    ['email_log_id', 'user_id']
                );
            }
        }
    }
}
?>
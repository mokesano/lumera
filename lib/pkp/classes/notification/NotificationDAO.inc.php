<?php
declare(strict_types=1);

/**
 * @file classes/notification/NotificationDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationDAO
 * @ingroup notification
 * @see Notification
 *
 * @brief Operations for retrieving and modifying Notification objects.
 */

import('classes.notification.Notification');

class NotificationDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NotificationDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::NotificationDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Retrieve Notification by notification id
     * @param mixed $notificationId
     * @return Notification|null
     */
    public function getById($notificationId) {
        $result = $this->retrieve(
            'SELECT * FROM notifications WHERE notification_id = ?',
            [(int) $notificationId]
        );

        $notification = null;
        if (!$result->EOF) {
            $notification = $this->_returnNotificationFromRow($result->getRowAssoc(false));
        }
        $result->Close();
        return $notification;
    }

    /**
     * Retrieve Notifications by user id
     * Note that this method will not return fully-fledged notification objects. Use
     * NotificationManager::getNotificationsForUser() to get notifications with URL, and contents
     * @param mixed $userId
     * @param mixed $level
     * @param mixed $type
     * @param mixed $contextId
     * @param mixed $rangeInfo
     * @return DAOResultFactory
     */
    public function getByUserId($userId, $level = NOTIFICATION_LEVEL_NORMAL, $type = null, $contextId = null, $rangeInfo = null) {
        $params = [(int) $userId, (int) $level];
        $sql = 'SELECT * FROM notifications WHERE user_id = ? AND level = ?';
        
        if ($type !== null) {
            $sql .= ' AND type = ?';
            $params[] = (int) $type;
        }
        if ($contextId !== null) {
            $sql .= ' AND context_id = ?';
            $params[] = (int) $contextId;
        }
        $sql .= ' ORDER BY date_created DESC';

        $result = $this->retrieveRange($sql, $params, $rangeInfo);
        return new DAOResultFactory($result, $this, '_returnNotificationFromRow');
    }

    /**
     * Retrieve Notifications by assoc.
     * Note that this method will not return fully-fledged notification objects. Use
     * NotificationManager::getNotificationsForUser() to get notifications with URL, and contents
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $userId
     * @param mixed $type
     * @param mixed $contextId
     * @return DAOResultFactory
     */
    public function getByAssoc($assocType, $assocId, $userId = null, $type = null, $contextId = null) {
        $params = [(int) $assocType, (int) $assocId];
        $sql = 'SELECT * FROM notifications WHERE assoc_type = ? AND assoc_id = ?';
        
        if ($userId !== null) {
            $sql .= ' AND user_id = ?';
            $params[] = (int) $userId;
        }
        if ($contextId !== null) {
            $sql .= ' AND context_id = ?';
            $params[] = (int) $contextId;
        }
        if ($type !== null) {
            $sql .= ' AND type = ?';
            $params[] = (int) $type;
        }
        $sql .= ' ORDER BY date_created DESC';

        $result = $this->retrieveRange($sql, $params);
        return new DAOResultFactory($result, $this, '_returnNotificationFromRow');
    }

    /**
     * Retrieve Notifications by notification id
     * @param mixed $notificationId
     * @param string|null $dateRead
     * @return string
     */
    public function setDateRead($notificationId, $dateRead = null) {
        $dateRead = $dateRead ?? Core::getCurrentDate();

        $this->update(
            'UPDATE notifications SET date_read = ? WHERE notification_id = ?',
            [$this->datetimeToDB($dateRead), (int) $notificationId]
        );

        return $dateRead;
    }

    /**
     * Instantiate and return a new data object.
     * @return Notification
     */
    public function newDataObject() {
        return new Notification();
    }

    /**
     * Creates and returns an notification object from a row
     * @param array $row
     * @return Notification
     */
    public function _returnNotificationFromRow($row) {
        $notification = $this->newDataObject();
        $notification->setId((int) $row['notification_id']);
        $notification->setUserId((int) $row['user_id']);
        $notification->setLevel((int) $row['level']);
        $notification->setDateCreated($this->datetimeFromDB($row['date_created']));
        $notification->setDateRead($this->datetimeFromDB($row['date_read']));
        $notification->setContextId((int) $row['context_id']);
        $notification->setType((int) $row['type']);
        $notification->setAssocType((int) $row['assoc_type']);
        $notification->setAssocId((int) $row['assoc_id']);

        HookRegistry::dispatch('NotificationDAO::_returnNotificationFromRow', [$notification, &$row]);

        return $notification;
    }

    /**
     * Inserts a new notification into notifications table
     * @param Notification $notification
     * @return int Notification Id
     */
    public function insertObject($notification) {
        $this->update(
            'INSERT INTO notifications (user_id, level, date_created, context_id, type, assoc_type, assoc_id) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                (int) $notification->getUserId(),
                (int) $notification->getLevel(),
                $this->datetimeToDB(Core::getCurrentDate()),
                (int) $notification->getContextId(),
                (int) $notification->getType(),
                (int) $notification->getAssocType(),
                (int) $notification->getAssocId()
            ]
        );
        $notification->setId($this->getInsertNotificationId());

        return $notification->getId();
    }

    /**
     * Inserts or update a notification into notifications table.
     * @param Notification $notification
     * @return void
     */
    public function build($notification) {
        $this->update(
            'DELETE FROM notifications WHERE context_id = ? AND level = ? AND type = ? AND user_id = ? AND assoc_type = ? AND assoc_id = ?',
            [
                (int) $notification->getContextId(),
                (int) $notification->getLevel(),
                (int) $notification->getType(),
                (int) $notification->getUserId(),
                (int) $notification->getAssocType(),
                (int) $notification->getAssocId()
            ]
        );
        $this->insertObject($notification);
    }

    /**
     * Delete Notification by notification id
     * @param mixed $notificationId
     * @param mixed $userId
     * @return bool
     */
    public function deleteById($notificationId, $userId = null) {
        $params = [(int) $notificationId];
        $sql = 'DELETE FROM notifications WHERE notification_id = ?';
        
        if ($userId !== null) {
            $sql .= ' AND user_id = ?';
            $params[] = (int) $userId;
        }
        
        $this->update($sql, $params);
        
        if ($this->getAffectedRows()) {
            /** @var NotificationSettingsDAO $notificationSettingsDao */
            $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
            $notificationSettingsDao->deleteSettingsByNotificationId($notificationId);
            return true;
        }
        return false;
    }

    /**
     * Delete Notification
     * @param Notification $notification
     * @return bool
     */
    public function deleteObject($notification) {
        return $this->deleteById($notification->getId());
    }

    /**
     * Delete notification(s) by association
     * @param mixed $assocType
     * @param mixed $assocId
     * @param mixed $userId
     * @param mixed $type
     * @param mixed $contextId
     * @return void
     */
    public function deleteByAssoc($assocType, $assocId, $userId = null, $type = null, $contextId = null) {
        $notificationsFactory = $this->getByAssoc($assocType, $assocId, $userId, $type, $contextId);
        while ($notification = $notificationsFactory->next()) {
            $this->deleteObject($notification);
        }
    }

    /**
     * Get the ID of the last inserted notification
     * @return int
     */
    public function getInsertNotificationId() {
        return $this->getInsertId('notifications', 'notification_id');
    }

    /**
     * Get the number of unread messages for a user
     * @param mixed $userId
     * @param mixed $contextId
     * @param mixed $level
     * @param bool $read
     * @return int
     */
    public function getNotificationCount($userId, $contextId = null, $level = NOTIFICATION_LEVEL_NORMAL, $read = true) {
        $params = [(int) $userId, (int) $level];
        $sql = 'SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND date_read IS' . ($read ? ' NOT' : '') . ' NULL AND level = ?';
        
        if ($contextId !== null) {
            $sql .= ' AND context_id = ?';
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve($sql, $params);
        
        $returner = 0;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $returner = (int) $row['count'];
        }
        $result->Close();

        return $returner;
    }

    /**
     * Transfer the notifications for a user.
     * @param mixed $oldUserId
     * @param mixed $newUserId
     * @return void
     */
    public function transferNotifications($oldUserId, $newUserId) {
        $this->update(
            'UPDATE notifications SET user_id = ? WHERE user_id = ?',
            [(int) $newUserId, (int) $oldUserId]
        );
    }

}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/notification/PKPNotification.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Notification
 * @ingroup notification
 * @see NotificationDAO
 * @brief Class for Notification.
 */

import('lib.pkp.classes.notification.NotificationDAO');

define('UNSUBSCRIBED_USER_NOTIFICATION',            0);

/** Notification levels.  Determines notification behavior **/
define('NOTIFICATION_LEVEL_TRIVIAL',                0x0000001);
define('NOTIFICATION_LEVEL_NORMAL',                 0x0000002);
define('NOTIFICATION_LEVEL_TASK',                   0x0000003);

/** Notification types.  Determines what text and URL to display for notification */
define('NOTIFICATION_TYPE_SUCCESS',                 0x0000001);
define('NOTIFICATION_TYPE_WARNING',                 0x0000002);
define('NOTIFICATION_TYPE_ERROR',                   0x0000003);
define('NOTIFICATION_TYPE_FORBIDDEN',               0x0000004);
define('NOTIFICATION_TYPE_INFORMATION',             0x0000005);
define('NOTIFICATION_TYPE_HELP',                    0x0000006);
define('NOTIFICATION_TYPE_FORM_ERROR',              0x0000007);
define('NOTIFICATION_TYPE_NEW_ANNOUNCEMENT',        0x0000008);

define('NOTIFICATION_TYPE_LOCALE_INSTALLED',        0x4000001);

define('NOTIFICATION_TYPE_PLUGIN_ENABLED',          0x5000001);
define('NOTIFICATION_TYPE_PLUGIN_DISABLED',         0x5000002);

define('NOTIFICATION_TYPE_PLUGIN_BASE',             0x6000001);

class PKPNotification extends DataObject {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPNotification() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPNotification(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * get notification id
     * @return int
     */
    public function getNotificationId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->getId();
    }

    /**
     * set notification id
     * @param $notificationId int
     */
    public function setNotificationId($notificationId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.', E_USER_DEPRECATED);
        return $this->setId($notificationId);
    }

    /**
     * get user id associated with this notification
     * @return int
     */
    public function getUserId() {
        return $this->getData('userId');
    }

    /**
     * set user id associated with this notification
     * @param $userId int
     */
    public function setUserId($userId) {
        return $this->setData('userId', $userId);
    }

    /**
     * Get the level (NOTIFICATION_LEVEL_...) for this notification
     * @return int
     */
    public function getLevel() {
        return $this->getData('level');
    }

    /**
     * Set the level (NOTIFICATION_LEVEL_...) for this notification
     * @param $level int
     */
    public function setLevel($level) {
        return $this->setData('level', $level);
    }

    /**
     * get date notification was created
     * @return string (YYYY-MM-DD HH:MM:SS)
     */
    public function getDateCreated() {
        return $this->getData('dateCreated');
    }

    /**
     * set date notification was created
     * @param $dateCreated string (YYYY-MM-DD HH:MM:SS)
     */
    public function setDateCreated($dateCreated) {
        return $this->setData('dateCreated', $dateCreated);
    }

    /**
     * get date notification is read by user
     * @return string (YYYY-MM-DD HH:MM:SS)
     */
    public function getDateRead() {
        return $this->getData('dateRead');
    }

    /**
     * set date notification is read by user
     * @param $dateRead string (YYYY-MM-DD HH:MM:SS)
     */
    public function setDateRead($dateRead) {
        return $this->setData('dateRead', $dateRead);
    }

    /**
     * get notification type
     * @return int
     */
    public function getType() {
        return $this->getData('type');
    }

    /**
     * set notification type
     * @param $type int
     */
    public function setType($type) {
        return $this->setData('type', $type);
    }

    /**
     * get notification assoc type
     * @return int
     */
    public function getAssocType() {
        return $this->getData('assocType');
    }

    /**
     * set notification assoc type
     * @param $assocType int
     */
    public function setAssocType($assocType) {
        return $this->setData('assocType', $assocType);
    }

    /**
     * get notification assoc id
     * @return int
     */
    public function getAssocId() {
        return $this->getData('assocId');
    }

    /**
     * set notification assoc id
     * @param $assocId int
     */
    public function setAssocId($assocId) {
        return $this->setData('assocId', $assocId);
    }

    /**
     * get context id
     * @return int
     */
    public function getContextId() {
        return $this->getData('context_id');
    }

    /**
     * set context id
     * @param $contextId int
     */
    public function setContextId($contextId) {
        return $this->setData('context_id', $contextId);
    }
}

?>
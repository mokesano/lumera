<?php
declare(strict_types=1);

/**
 * @file classes/notification/NotificationMailListDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationMailListDAO
 * @ingroup notification
 * @see Notification
 *
 * @brief Operations for getting and setting subscriptions to the non-user notification mailing list
 */

class NotificationMailListDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NotificationMailListDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::NotificationMailListDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Generates an access key for the guest user and adds them to the notification_mail_list table
     * @param string $email
     * @param mixed $contextId
     * @return string|false
     */
    public function subscribeGuest($email, $contextId) {
        $token = bin2hex(random_bytes(16));

        if ($this->getMailListIdByToken($token, $contextId) !== 0) {
            return $this->subscribeGuest($email, $contextId);
        }

        // Check that the email doesn't already exist
        $result = $this->retrieve(
            'SELECT * FROM notification_mail_list WHERE email = ? AND context = ?',
            [(string) $email, (int) $contextId]
        );
        
        if (!$result->EOF) {
            $result->Close();
            return false;
        }
        $result->Close();

        $this->update(
            'INSERT INTO notification_mail_list (email, context, token) VALUES (?, ?, ?)',
            [(string) $email, (int) $contextId, $token]
        );

        return $token;
    }

    /**
     * Gets a mailing list subscription id by a token value
     * @param string $token
     * @param mixed $contextId
     * @return int
     */
    public function getMailListIdByToken($token, $contextId) {
        $result = $this->retrieve(
            'SELECT notification_mail_list_id FROM notification_mail_list WHERE token = ? AND context = ?',
            [(string) $token, (int) $contextId]
        );

        $notificationMailListId = 0;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $notificationMailListId = (int) $row['notification_mail_list_id'];
        }
        $result->Close();

        return $notificationMailListId;
    }

    /**
     * Removes an email address from email notifications
     * @param string $token
     * @param mixed $contextId
     * @return bool
     */
    public function unsubscribeGuest($token, $contextId) {
        $notificationMailListId = $this->getMailListIdByToken($token, $contextId);

        if ($notificationMailListId !== 0) {
            return $this->update(
                'DELETE FROM notification_mail_list WHERE notification_mail_list_id = ?',
                [(int) $notificationMailListId]
            );
        }
        return false;
    }

    /**
     * Confirm the mailing list subscription
     * @param mixed $notificationMailListId
     * @return bool
     */
    public function confirmMailListSubscription($notificationMailListId) {
        return $this->update(
            'UPDATE notification_mail_list SET confirmed = 1 WHERE notification_mail_list_id = ?',
            [(int) $notificationMailListId]
        );
    }

    /**
     * Gets a list of email addresses of users subscribed to the mailing list
     * @param mixed $contextId
     * @return array
     */
    public function getMailList($contextId) {
        $result = $this->retrieve(
            'SELECT email, token FROM notification_mail_list WHERE context = ?',
            [(int) $contextId]
        );

        $mailList = [];
        while (!$result->EOF) {
            $mailList[] = $result->getRowAssoc(false);
            $result->MoveNext();
        }
        $result->Close();

        return $mailList;
    }

    /**
     * Get the ID of the last inserted notification mail list entry
     * @return int
     */
    public function getInsertNotificationMailListId() {
        return $this->getInsertId('notification_mail_list', 'notification_mail_list_id');
    }

}
?>
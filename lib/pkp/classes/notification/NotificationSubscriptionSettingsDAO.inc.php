<?php
declare(strict_types=1);

/**
 * @file classes/notification/NotificationSubscriptionSettingsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationSubscriptionSettingsDAO
 * @ingroup notification
 * @see Notification
 *
 * @brief Operations for retrieving and modifying user's notification settings.
 * 
 * This class stores user settings that determine how notifications should be
 * delivered to them.
 */

class NotificationSubscriptionSettingsDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NotificationSubscriptionSettingsDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::NotificationSubscriptionSettingsDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Delete a notification setting by setting name
     * @param mixed $notificationId
     * @param mixed $userId
     * @param string|null $settingName optional
     * @return bool
     */
    public function deleteNotificationSubscriptionSettings($notificationId, $userId, $settingName = null) {
        $params = [(int) $notificationId, (int) $userId];
        $sql = 'DELETE FROM notification_subscription_settings WHERE notification_id= ? AND user_id = ?';
        
        if ($settingName !== null) {
            $sql .= ' AND setting_name = ?';
            $params[] = $settingName;
        }

        return $this->update($sql, $params);
    }

    /**
     * Retrieve Notification subscription settings by user id
     * @param string $settingName
     * @param mixed $userId
     * @param mixed $contextId
     * @return array
     */
    public function getNotificationSubscriptionSettings($settingName, $userId, $contextId) {
        $result = $this->retrieve(
            'SELECT setting_value FROM notification_subscription_settings WHERE user_id = ? AND setting_name = ? AND context = ?',
            [(int) $userId, (string) $settingName, (int) $contextId]
        );

        $settings = [];
        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $settings[] = (int) $row['setting_value'];
            $result->MoveNext();
        }

        $result->Close();
        return $settings;
    }

    /**
     * Update a user's notification subscription settings
     * @param string $settingName
     * @param array|null $settings
     * @param mixed $userId
     * @param mixed $contextId
     */
    public function updateNotificationSubscriptionSettings($settingName, $settings, $userId, $contextId) {
        $this->update(
            'DELETE FROM notification_subscription_settings WHERE user_id = ? AND setting_name = ? AND context = ?',
            [(int) $userId, (string) $settingName, (int) $contextId]
        );

        if (is_array($settings)) {
            foreach ($settings as $setting) {
                $this->update(
                    'INSERT INTO notification_subscription_settings
                        (setting_name, setting_value, user_id, context, setting_type)
                        VALUES
                        (?, ?, ?, ?, ?)',
                    [
                        (string) $settingName,
                        (int) $setting,
                        (int) $userId,
                        (int) $contextId,
                        'int'
                    ]
                );
            }
        }
    }

    /**
     * Gets a user id by an RSS token value
     * @param string $token
     * @param mixed $contextId
     * @return int
     */
    public function getUserIdByRSSToken($token, $contextId) {
        $result = $this->retrieve(
            'SELECT user_id FROM notification_subscription_settings WHERE setting_value = ? AND setting_name = ? AND context = ?',
            [(string) $token, 'token', (int) $contextId]
        );

        $userId = 0;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $userId = (int) $row['user_id'];
        }

        $result->Close();
        return $userId;
    }

    /**
     * Gets an RSS token for a user id
     * @param mixed $userId
     * @param mixed $contextId
     * @return string|null
     */
    public function getRSSTokenByUserId($userId, $contextId) {
        $result = $this->retrieve(
            'SELECT setting_value FROM notification_subscription_settings WHERE user_id = ? AND setting_name = ? AND context = ?',
            [(int) $userId, 'token', (int) $contextId]
        );

        $tokenId = null;
        if (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $tokenId = (string) $row['setting_value'];
        }

        $result->Close();
        return $tokenId;
    }

    /**
     * Generates and inserts a new token for a user's RSS feed
     * @param mixed $userId
     * @param mixed $contextId
     * @return string
     */
    public function insertNewRSSToken($userId, $contextId) {
        $token = bin2hex(random_bytes(16));
        if ($this->getUserIdByRSSToken($token, $contextId) !== 0) {
            return $this->insertNewRSSToken($userId, $contextId);
        }

        $this->update(
            'INSERT INTO notification_subscription_settings
                (setting_name, setting_value, user_id, context, setting_type)
                VALUES
                (?, ?, ?, ?, ?)',
            [
                'token',
                $token,
                (int) $userId,
                (int) $contextId,
                'string'
            ]
        );

        return $token;
    }

}
?>
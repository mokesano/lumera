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
     * @param $notificationId int
     * @param $userId int
     * @param $settingName string optional
     * @return boolean
     */
    public function deleteNotificationSubscriptionSettings($notificationId, $userId, $settingName = null) {
        $params = array((int) $notificationId, (int) $userId);
        $sql = 'DELETE FROM notification_subscription_settings WHERE notification_id= ? AND user_id = ?';
        
        if ($settingName !== null) {
            $sql .= ' AND setting_name = ?';
            $params[] = $settingName;
        }

        return $this->update($sql, $params);
    }

    /**
     * Retrieve Notification subscription settings by user id
     * @param $settingName string
     * @param $userId int
     * @param $contextId int
     * @return array
     */
    public function getNotificationSubscriptionSettings($settingName, $userId, $contextId) {
        // Hapus '&'
        $result = $this->retrieve(
            'SELECT setting_value FROM notification_subscription_settings WHERE user_id = ? AND setting_name = ? AND context = ?',
            array((int) $userId, $settingName, (int) $contextId)
        );

        $settings = array();
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $settings[] = (int) $row['setting_value'];
            $result->MoveNext();
        }

        $result->Close();
        unset($result);

        return $settings;
    }

    /**
     * Update a user's notification subscription settings
     * @param $settingName string
     * @param $settings array
     * @param $userId int
     * @param $contextId int
     */
    public function updateNotificationSubscriptionSettings($settingName, $settings, $userId, $contextId) {
        // Delete old settings first, then insert new settings
        $this->update('DELETE FROM notification_subscription_settings WHERE user_id = ? AND setting_name = ? AND context = ?',
            array((int) $userId, $settingName, (int) $contextId));

        if (is_array($settings)) {
            foreach ($settings as $setting) {
                $this->update(
                    'INSERT INTO notification_subscription_settings
                        (setting_name, setting_value, user_id, context, setting_type)
                        VALUES
                        (?, ?, ?, ?, ?)',
                    array(
                        $settingName,
                        (int) $setting,
                        (int) $userId,
                        (int) $contextId,
                        'int'
                    )
                );
            }
        }
    }

    /**
     * Gets a user id by an RSS token value
     * @param $token string
     * @param $contextId int
     * @return int|0
     */
    public function getUserIdByRSSToken($token, $contextId) {
        $result = $this->retrieve(
            'SELECT user_id FROM notification_subscription_settings WHERE setting_value = ? AND setting_name = ? AND context = ?',
                array($token, 'token', (int) $contextId)
        );

        // PHP 8 Fix: Handle empty result safety
        $userId = 0;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);
            $userId = (int) $row['user_id'];
        }

        $result->Close();
        unset($result);

        return $userId;
    }

    /**
     * Gets an RSS token for a user id
     * @param $userId int
     * @param $contextId int
     * @return string|null
     */
    public function getRSSTokenByUserId($userId, $contextId) {
        $result = $this->retrieve(
            'SELECT setting_value FROM notification_subscription_settings WHERE user_id = ? AND setting_name = ? AND context = ?',
                array((int) $userId, 'token', (int) $contextId)
        );

        // PHP 8 Fix: Handle empty result safety
        $tokenId = null;
        if ($result->RecordCount() != 0) {
            $row = $result->GetRowAssoc(false);
            $tokenId = $row['setting_value'];
        }

        $result->Close();
        unset($result);

        return $tokenId;
    }

    /**
     * Generates and inserts a new token for a user's RSS feed
     * @param $userId int
     * @param $contextId int
     * @return string
     */
    public function insertNewRSSToken($userId, $contextId) {
        // Entropy fix: rand() -> mt_rand()
        $token = uniqid(mt_rand());

        // Recurse if this token already exists
        if($this->getUserIdByRSSToken($token, $contextId)) return $this->insertNewRSSToken($userId, $contextId);

        $this->update(
            'INSERT INTO notification_subscription_settings
                (setting_name, setting_value, user_id, context, setting_type)
                VALUES
                (?, ?, ?, ?, ?)',
            array(
                'token',
                $token,
                (int) $userId,
                (int) $contextId,
                'string'
            )
        );

        return $token;
    }

}

?>
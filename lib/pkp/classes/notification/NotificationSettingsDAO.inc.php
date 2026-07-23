<?php
declare(strict_types=1);

/**
 * @file classes/notification/NotificationSettingsDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationSettingsDAO
 * @ingroup notification
 * @see Notification
 *
 * @brief Operations for retrieving and modifying Notification metadata.
 */

import('classes.notification.Notification');

class NotificationSettingsDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function NotificationSettingsDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::NotificationSettingsDAO(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Retrieve a notification's metadata
     * @param mixed $notificationId
     * @return array
     */
    public function getNotificationSettings($notificationId) {
        $result = $this->retrieve(
            'SELECT * FROM notification_settings WHERE notification_id = ?',
            [(int) $notificationId]
        );

        $params = [];
        while (!$result->EOF) {
            $row = $result->getRowAssoc(false);
            $name = $row['setting_name'];
            $value = $this->convertFromDB($row['setting_value'], $row['setting_type']);
            $locale = $row['locale'];

            if ($locale === '') {
                $params[$name] = $value;
            } else {
                $params[$name][$locale] = $value;
            }
            $result->MoveNext();
        }

        $result->Close();
        return $params;
    }

    /**
     * Store a notification's metadata
     * @param mixed $notificationId
     * @param string $name
     * @param mixed $value
     * @param bool $isLocalized
     * @param string|null $type
     */
    public function updateNotificationSetting($notificationId, $name, $value, $isLocalized = false, $type = null) {
        $keyFields = ['setting_name', 'locale', 'notification_id'];
        
        if (!$isLocalized) {
            $dbValue = $this->convertToDB($value, $type);
            $this->replace('notification_settings',
                [
                    'notification_id' => (int) $notificationId,
                    'setting_name'    => (string) $name,
                    'setting_value'   => $dbValue,
                    'setting_type'    => $type,
                    'locale'          => ''
                ],
                $keyFields
            );
        } else {
            if (is_array($value)) {
                foreach ($value as $locale => $localeValue) {
                    $this->update(
                        'DELETE FROM notification_settings WHERE notification_id = ? AND setting_name = ? AND locale = ?', 
                        [(int) $notificationId, (string) $name, (string) $locale]
                    );
                    
                    if (empty($localeValue)) {
                        continue;
                    }
                    
                    $dbLocaleValue = $this->convertToDB($localeValue, $type);
                    $this->update(
                        'INSERT INTO notification_settings (notification_id, setting_name, setting_value, setting_type, locale) VALUES (?, ?, ?, ?, ?)',
                        [
                            (int) $notificationId, 
                            (string) $name, 
                            $dbLocaleValue, 
                            $type, 
                            (string) $locale
                        ]
                    );
                }
            }
        }
    }

    /**
     * Delete all settings for a notification
     * @param mixed $notificationId
     * @return bool
     */
    public function deleteSettingsByNotificationId($notificationId) {
        return $this->update('DELETE FROM notification_settings WHERE notification_id = ?', (int) $notificationId);
    }

}
?>
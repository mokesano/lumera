<?php
declare(strict_types=1);

/**
 * @file classes/notification/PKPNotificationManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPNotificationManager
 * @ingroup notification
 * @see NotificationDAO
 * @see Notification
 * @brief Class for Notification Manager.
 */

import('classes.notification.Notification');

class PKPNotificationManager {
    
    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPNotificationManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPNotificationManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Construct a set of notifications and return them as a formatted string
     * @param $request PKPRequest
     * @param $userId int
     * @param $level int optional
     * @param $contextId int optional
     * @param $rangeInfo object optional
     * @param $notificationTemplate string optional Template to use for constructing an individual notification for display
     * @return string
     */
    public function getFormattedNotificationsForUser($request, $userId, $level = NOTIFICATION_LEVEL_NORMAL, $contextId = null, $rangeInfo = null, $notificationTemplate = 'notification/notification.tpl') {
        $notificationDao = DAORegistry::getDAO('NotificationDAO');
        $notifications = $notificationDao->getByUserId($userId, $level, null, $contextId, $rangeInfo);

        return $this->formatNotifications($request, $notifications, $notificationTemplate);
    }

    /*
     * Return a string of formatted notifications for display
     * @param $request PKPRequest
     * @param $notifications object DAOResultFactory
     * @param $notificationTemplate string optional Template to use for constructing an individual notification for display
     * @return string
     */
    public function formatNotifications($request, $notifications, $notificationTemplate = 'notification/notification.tpl') {
        $notificationString = '';

        // Build out the notifications based on their associated objects and format into a string
        // Hapus '&' pada while loop
        while($notification = $notifications->next()) {
            $notificationString .= $this->formatNotification($request, $notification, $notificationTemplate);
            unset($notification);
        }

        return $notificationString;
    }

    /**
     * Return a fully formatted notification for display
     * @param $request PKPRequest
     * @param $notification object Notification
     * @return string
     */
    public function formatNotification($request, $notification, $notificationTemplate = 'notification/notification.tpl') {
        $templateMgr = TemplateManager::getManager();

        // Set the date read if it isn't already set
        if (!$notification->getDateRead()) {
            $notificationDao = DAORegistry::getDAO('NotificationDAO');
            $dateRead = $notificationDao->setDateRead($notification->getId());
            $notification->setDateRead($dateRead);
        }

        $templateMgr->assign('notificationDateCreated', $notification->getDateCreated());
        $templateMgr->assign('notificationId', $notification->getId());
        $templateMgr->assign('notificationContents',$this->getNotificationContents($request, $notification));
        $templateMgr->assign('notificationTitle',$this->getNotificationTitle($notification));
        $templateMgr->assign('notificationStyleClass', $this->getStyleClass($notification));
        $templateMgr->assign('notificationIconClass', $this->getIconClass($notification));
        $templateMgr->assign('notificationDateRead', $notification->getDateRead());
        if($notification->getLevel() != NOTIFICATION_LEVEL_TRIVIAL) {
            $templateMgr->assign('notificationUrl', $this->getNotificationUrl($request, $notification));
        }

        $user = $request->getUser();
        $templateMgr->assign('isUserLoggedIn', $user);

        return $templateMgr->fetch($notificationTemplate);
    }

    /**
     * Construct a URL for the notification based on its type and associated object
     * @param $request PKPRequest
     * @param $notification Notification
     * @return string|false
     */
    public function getNotificationUrl($request, $notification) {
        return false;
    }

    /**
     * Return a message string for the notification based on its type
     * and associated object.
     * @param $request PKPRequest
     * @param $notification Notification
     * @return string|null
     */
    public function getNotificationMessage($request, $notification) {
        $type = $notification->getType();
        assert(isset($type));

        switch ($type) {
            case NOTIFICATION_TYPE_SUCCESS:
            case NOTIFICATION_TYPE_ERROR:
            case NOTIFICATION_TYPE_WARNING:
                if ($this->getNotificationSettings($notification->getId()) !== null) {
                    $notificationSettings = $this->getNotificationSettings($notification->getId());
                    return $notificationSettings['contents'];
                } else {
                    return __('common.changesSaved');
                }
            case NOTIFICATION_TYPE_FORM_ERROR:
            case NOTIFICATION_TYPE_ERROR:
                $notificationSettings = $this->getNotificationSettings($notification->getId());
                assert(isset($notificationSettings['contents']));
                return $notificationSettings['contents'];
            case NOTIFICATION_TYPE_PLUGIN_ENABLED:
                return $this->_getTranslatedKeyWithParameters('common.pluginEnabled', $notification->getId());
            case NOTIFICATION_TYPE_PLUGIN_DISABLED:
                return $this->_getTranslatedKeyWithParameters('common.pluginDisabled', $notification->getId());
            case NOTIFICATION_TYPE_LOCALE_INSTALLED:
                return $this->_getTranslatedKeyWithParameters('admin.languages.localeInstalled', $notification->getId());
            case NOTIFICATION_TYPE_NEW_ANNOUNCEMENT:
                assert($notification->getAssocType() == ASSOC_TYPE_ANNOUNCEMENT);
                return __('notification.type.newAnnouncement');
            default:
                return null;
        }
    }

    /**
     * Using the notification message, construct, if needed, any additional
     * content for the notification body.
     * @param $request Request
     * @param $notification Notification
     * @return String
     */
    public function getNotificationContents($request, $notification) {
        $type = $notification->getType();
        assert(isset($type));
        $notificationMessage = $this->getNotificationMessage($request, $notification);

        switch ($type) {
            case NOTIFICATION_TYPE_FORM_ERROR:
                $templateMgr = TemplateManager::getManager();
                $templateMgr->assign('errors', $notificationMessage);
                return $templateMgr->fetch('controllers/notification/formErrorNotificationContent.tpl');
            case NOTIFICATION_TYPE_ERROR:
                if (is_array($notificationMessage)) {
                    $templateMgr = TemplateManager::getManager();
                    $templateMgr->assign('errors', $notificationMessage);
                    return $templateMgr->fetch('controllers/notification/errorNotificationContent.tpl');
                } else {
                    return $notificationMessage;
                }
            default:
                return $notificationMessage;
        }
    }

    /**
     * Helper function to get a translated string from a notification with parameters
     * @param $key string
     * @param $notificationId int
     * @return String
     */
    public function _getTranslatedKeyWithParameters($key, $notificationId) {
        $params = $this->getNotificationSettings($notificationId);
        return __($key, $this->getParamsForCurrentLocale($params));
    }

    /**
     * Return notification settings.
     * @param $notificationId int
     * @return Array|null
     */
    public function getNotificationSettings($notificationId) {
        $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO'); /* @var $notificationSettingsDao NotificationSettingsDAO */
        $notificationSettings = $notificationSettingsDao->getNotificationSettings($notificationId);
        if (empty($notificationSettings)) {
            return null;
        } else {
            return $notificationSettings;
        }
    }

    /**
     * Get the notification's title value
     * @param $notification
     * @return string
     */
    public function getNotificationTitle($notification) {
        $type = $notification->getType();
        assert(isset($type));

        switch ($type) {
            case NOTIFICATION_TYPE_FORM_ERROR:
                return __('form.errorsOccurred');
            default:
                return __('notification.notification');
        }
    }


    /**
     * Iterate through the localized params for a notification's locale key.
     * @param $params array
     * @return array
     */
    public function getParamsForCurrentLocale($params) {
        $locale = AppLocale::getLocale();
        $primaryLocale = AppLocale::getPrimaryLocale();

        $localizedParams = array();
        if (is_array($params)) {
            foreach ($params as $name => $value) {
                if (!is_array($value)) {
                    // Non-localized text
                    $localizedParams[$name] = $value;
                } elseif (isset($value[$locale])) {
                    // Check if the parameter is in the user's current locale
                    $localizedParams[$name] = $value[$locale];
                } elseif (isset($value[$primaryLocale])) {
                    // Check if the parameter is in the default site locale
                    $localizedParams[$name] = $value[$primaryLocale];
                } else {
                    // Otherwise, iterate over all supported locales and return the first match
                    $locales = AppLocale::getSupportedLocales();
                    foreach ($locales as $localeKey) {
                        if (isset($value[$localeKey])) {
                            $localizedParams[$name] = $value[$localeKey];
                            break; // added break for efficiency
                        }
                    }
                }
            }
        }

        return $localizedParams;
    }

    /**
     * get notification style class
     * @param $notification Notification
     * @return string
     */
    public function getStyleClass($notification) {
        switch ($notification->getType()) {
            case NOTIFICATION_TYPE_SUCCESS: return 'notifySuccess';
            case NOTIFICATION_TYPE_WARNING: return 'notifyWarning';
            case NOTIFICATION_TYPE_ERROR: return 'notifyError';
            case NOTIFICATION_TYPE_INFORMATION: return 'notifyInfo';
            case NOTIFICATION_TYPE_FORBIDDEN: return 'notifyForbidden';
            case NOTIFICATION_TYPE_HELP: return 'notifyHelp';
            case NOTIFICATION_TYPE_FORM_ERROR: return 'notifyFormError';
            default: return ''; // Added default return
        }
    }

    /**
     * get notification icon style class
     * @param $notification Notification
     * @return string
     */
    public function getIconClass($notification) {
        switch ($notification->getType()) {
            case NOTIFICATION_TYPE_SUCCESS: return 'notifyIconSuccess';
            case NOTIFICATION_TYPE_WARNING: return 'notifyIconWarning';
            case NOTIFICATION_TYPE_ERROR: return 'notifyIconError';
            case NOTIFICATION_TYPE_INFORMATION: return 'notifyIconInfo';
            case NOTIFICATION_TYPE_FORBIDDEN: return 'notifyIconForbidden';
            case NOTIFICATION_TYPE_HELP: return 'notifyIconHelp';
            default: return 'notifyIconPageAlert';
        }
    }

    /**
     * Return all notification types that don't need a userId
     * to be created or fetched (all users can see them).
     * @return array
     */
    public function getAllUsersNotificationTypes() {
        return array();
    }

    /**
     * Create a new notification with the specified arguments and insert into DB
     * @param $request PKPRequest
     * @param $userId int (optional)
     * @param $notificationType int
     * @param $contextId int
     * @param $assocType int
     * @param $assocId int
     * @param $level int
     * @param $params array
     * @return Notification object
     */
    public function createNotification($request, $userId = null, $notificationType, $contextId = null, $assocType = null, $assocId = null, $level = NOTIFICATION_LEVEL_NORMAL, $params = null) {
        // Get set of notifications user does not want to be notified of
        $notificationSubscriptionSettingsDao = DAORegistry::getDAO('NotificationSubscriptionSettingsDAO');
        $blockedNotifications = $notificationSubscriptionSettingsDao->getNotificationSubscriptionSettings('blocked_notification', $userId, (int) $contextId);

        if(!in_array($notificationType, $blockedNotifications)) {
            $notificationDao = DAORegistry::getDAO('NotificationDAO');
            $notification = $notificationDao->newDataObject();
            $notification->setUserId((int) $userId);
            $notification->setType((int) $notificationType);
            $notification->setContextId((int) $contextId);
            $notification->setAssocType((int) $assocType);
            $notification->setAssocId((int) $assocId);
            $notification->setLevel((int) $level);

            $notificationId = $notificationDao->insertObject($notification);

            // Send notification emails
            if ($notification->getLevel() != NOTIFICATION_LEVEL_TRIVIAL) {
                $notificationSubscriptionSettingsDao = DAORegistry::getDAO('NotificationSubscriptionSettingsDAO');
                $notificationEmailSettings = $notificationSubscriptionSettingsDao->getNotificationSubscriptionSettings('emailed_notification', $userId, (int) $contextId);

                if(in_array($notificationType, $notificationEmailSettings)) {
                    $this->sendNotificationEmail($request, $notification);
                }
            }

            if ($params) {
                $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
                foreach($params as $name => $value) {
                    $notificationSettingsDao->updateNotificationSetting($notificationId, $name, $value);
                }
            }

            return $notification;
        }
        return null; // Added return for strict path
    }

    /**
     * Create a new notification with the specified arguments and insert into DB
     * @param $userId int
     * @param $notificationType int
     * @param $params array
     * @return Notification object
     */
    public function createTrivialNotification($userId, $notificationType = NOTIFICATION_TYPE_SUCCESS, $params = null) {
        $notificationDao = DAORegistry::getDAO('NotificationDAO');
        $notification = $notificationDao->newDataObject();
        $notification->setUserId($userId);
        $notification->setContextId(CONTEXT_ID_NONE);
        $notification->setType($notificationType);
        $notification->setLevel(NOTIFICATION_LEVEL_TRIVIAL);

        $notificationId = $notificationDao->insertObject($notification);

        if ($params) {
            $notificationSettingsDao = DAORegistry::getDAO('NotificationSettingsDAO');
            foreach($params as $name => $value) {
                $notificationSettingsDao->updateNotificationSetting($notificationId, $name, $value);
            }
        }

        return $notification;
    }

    /**
     * Deletes trivial notifications from database.
     * @param array $notifications
     */
    public function deleteTrivialNotifications($notifications) {
        $notificationDao = DAORegistry::getDAO('NotificationDAO');
        foreach($notifications as $notification) {
            // Delete only trivial notifications.
            if($notification->getLevel() == NOTIFICATION_LEVEL_TRIVIAL) {
                $notificationDao->deleteById($notification->getId(), $notification->getUserId());
            }
        }
    }

    /**
     * General notification data formating.
     * @param $request PKPRequest
     * @param array $notifications
     * @return array
     */
    public function formatToGeneralNotification($request, $notifications) {
        $formattedNotificationsData = array();
        foreach ($notifications as $notification) { /* @var $notification Notification */
            $formattedNotificationsData[$notification->getLevel()][$notification->getId()] = array(
                'pnotify_title' => $this->getNotificationTitle($notification),
                'pnotify_text' => $this->getNotificationContents($request, $notification),
                'pnotify_addclass' => $this->getStyleClass($notification),
                'pnotify_notice_icon' => $this->getIconClass($notification)
            );
        }

        return $formattedNotificationsData;
    }

    /**
     * In place notification data formating.
     * @param $request PKPRequest
     * @param $notifications array
     * @return array
     */
    public function formatToInPlaceNotification($request, $notifications) {
        $formattedNotificationsData = null;

        if (!empty($notifications)) {
            $templateMgr = TemplateManager::getManager();
            foreach ((array)$notifications as $notification) {
                $formattedNotificationsData[$notification->getLevel()][$notification->getId()] = $this->formatNotification($request, $notification, 'controllers/notification/inPlaceNotificationContent.tpl');
            }
        }

        return $formattedNotificationsData;
    }

    /**
     * Send an email to a user regarding the notification
     * @param $request PKPRequest
     * @param $notification object Notification
     */
    public function sendNotificationEmail($request, $notification) {
        $userId = $notification->getUserId();
        $userDao = DAORegistry::getDAO('UserDAO');
        $user = $userDao->getById($userId);
        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);

        import('classes.mail.MailTemplate');
        $site = $request->getSite();
        $mail = new MailTemplate('NOTIFICATION', null, null, null, false, true);
        $mail->setReplyTo($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
        $mail->assignParams(array(
            'notificationContents' => $this->getNotificationContents($request, $notification),
            'url' => $this->getNotificationUrl($request, $notification),
            'siteTitle' => $site->getLocalizedTitle()
        ));
        $mail->addRecipient($user->getEmail(), $user->getFullName());
        
        // WIZDAM UPDATE: Gunakan dispatch.
        // Parameter adalah objek ($notification), jadi tidak perlu '&'.
        // Jika hook mengembalikan nilai true, email dibatalkan.
        if (!HookRegistry::dispatch('PKPNotificationManager::sendNotificationEmail', array($notification))) {
            $mail->send();
        }
    }

    /**
     * Send an update to all users on the mailing list
     * @param $request PKPRequest
     * @param $notification object Notification
     */
    public function sendToMailingList($request, $notification) {
        $notificationMailListDao = DAORegistry::getDAO('NotificationMailListDAO');
        $mailList = $notificationMailListDao->getMailList($notification->getContextId());
        AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);

        foreach ($mailList as $recipient) {
            import('classes.mail.MailTemplate');
            $context = $request->getContext();
            $site = $request->getSite();
            $router = $request->getRouter();
            $dispatcher = $router->getDispatcher();

            $mail = new MailTemplate('NOTIFICATION_MAILLIST');
            $mail->setReplyTo($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
            $mail->assignParams(array(
                'notificationContents' => $this->getNotificationContents($request, $notification),
                'url' => $this->getNotificationUrl($request, $notification),
                'siteTitle' => $context->getLocalizedTitle(),
                'unsubscribeLink' => $dispatcher->url($request, ROUTE_PAGE, null, 'notification', 'unsubscribeMailList', $recipient['token'])
            ));
            $mail->addRecipient($recipient['email']);
            $mail->send();
        }
    }

    /**
     * Static function to send an email to a mailing list user e.g. regarding signup
     * @param $request PKPRequest
     * @param $email string
     * @param $token string the user's token (for confirming and unsubscribing)
     * @param $template string The mail template to use
     */
    public function sendMailingListEmail($request, $email, $token, $template) {
        import('classes.mail.MailTemplate');
        $site = $request->getSite();
        $router = $request->getRouter();
        $dispatcher = $router->getDispatcher();

        $params = array(
            'siteTitle' => $site->getLocalizedTitle(),
            'unsubscribeLink' => $dispatcher->url($request, ROUTE_PAGE, null, 'notification', 'unsubscribeMailList', array($token))
        );

        if ($template == 'NOTIFICATION_MAILLIST_WELCOME') {
            $router = $request->getRouter();
            $dispatcher = $router->getDispatcher();

            $confirmLink = $dispatcher->url($request, ROUTE_PAGE, null, 'notification', 'confirmMailListSubscription', array($token));
            $params["confirmLink"] = $confirmLink;
        }

        $mail = new MailTemplate($template);
        $mail->setReplyTo($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
        $mail->assignParams($params);
        $mail->addRecipient($email);
        $mail->send();
    }
}

?>
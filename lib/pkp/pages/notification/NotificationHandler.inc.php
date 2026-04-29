<?php
declare(strict_types=1);

/**
 * @file pages/notification/NotificationHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NotificationHandler
 * @ingroup pages_help
 *
 * @brief Handle requests for viewing notifications.
 *
 * [WIZDAM EDITION] PHP 8.1+ Compatibility, Strict Types, Security Hardening
 */

import('classes.handler.Handler');
import('classes.notification.Notification');

class NotificationHandler extends Handler {

    /**
     * Display notification index page.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index($args = [], $request = null) {
        // [Wizdam] Singleton Fallback
        if ($request === null) {
            $request = Application::get()->getRequest();
        }

        $this->validate();
        $this->setupTemplate();
        
        $templateMgr = TemplateManager::getManager();
        $router = $request->getRouter();

        $user = $request->getUser();
        if ($user) {
            $userId = (int) $user->getId();
            $templateMgr->assign('isUserLoggedIn', true);
        } else {
            $userId = 0;
            $templateMgr->assign('emailUrl', $router->url($request, null, 'notification', 'subscribeMailList'));
            $templateMgr->assign('isUserLoggedIn', false);
        }
        
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : null;

        $notificationManager = new NotificationManager();
        $notificationDao = DAORegistry::getDAO('NotificationDAO');

        $rangeInfo = Handler::getRangeInfo('notifications');

        // Construct the formatted notification string to display in the template
        $formattedNotifications = $notificationManager->getFormattedNotificationsForUser($request, $userId, NOTIFICATION_LEVEL_NORMAL, $contextId, $rangeInfo);

        // Get the same notifications used for the string so we can paginate
        $notifications = $notificationDao->getByUserId($userId, NOTIFICATION_LEVEL_NORMAL, null, $contextId, $rangeInfo);

        $templateMgr->assign('formattedNotifications', $formattedNotifications);
        $templateMgr->assign('notifications', $notifications);
        $templateMgr->assign('unread', $notificationDao->getNotificationCount(false, $userId, $contextId));
        $templateMgr->assign('read', $notificationDao->getNotificationCount(true, $userId, $contextId));
        $templateMgr->assign('url', $router->url($request, null, 'notification', 'settings'));
        
        $templateMgr->display('notification/index.tpl');
    }

    /**
     * Delete a notification
     * @param array $args
     * @param PKPRequest $request
     */
    public function delete($args, $request) {
        $this->validate();

        $notificationId = (int) array_shift($args);
        $isAjax = (isset($args[0]) && $args[0] == 'ajax');

        $user = $request->getUser();
        if ($user) {
            $userId = (int) $user->getId();
            $notificationDao = DAORegistry::getDAO('NotificationDAO');
            $notificationDao->deleteById($notificationId, $userId);
        }

        if (!$isAjax) {
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, 'notification'));
        }
    }

    /**
     * View and modify notification settings
     * @param array $args
     * @param PKPRequest $request
     */
    public function settings($args, $request) {
        $this->validate();
        $this->setupTemplate();

        $user = $request->getUser();
        if ($user) {
            import('classes.notification.form.NotificationSettingsForm');
            $notificationSettingsForm = new NotificationSettingsForm();
            $notificationSettingsForm->display($request);
        } else {
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, 'notification'));
        }
    }

    /**
     * Save user notification settings
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveSettings($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        import('classes.notification.form.NotificationSettingsForm');

        $notificationSettingsForm = new NotificationSettingsForm();
        $notificationSettingsForm->readInputData();

        if ($notificationSettingsForm->validate()) {
            $notificationSettingsForm->execute($request);
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, 'notification', 'settings'));
        } else {
            $notificationSettingsForm->display($request);
        }
    }

    /**
     * Fetch the existing or create a new URL for the user's RSS feed
     * @param array $args
     * @param PKPRequest $request
     */
    public function getNotificationFeedUrl($args, $request) {
        $user = $request->getUser();
        $router = $request->getRouter();
        $context = $router->getContext($request);
        
        // [Wizdam Fix] Pastikan context tidak null
        if (!$context) {
            $request->redirectUrl($router->url($request, null, 'index'));
            return;
        }

        $userId = $user ? (int) $user->getId() : 0;

        $notificationSubscriptionSettingsDao = DAORegistry::getDAO('NotificationSubscriptionSettingsDAO');
        $feedType = array_shift($args);

        $token = $notificationSubscriptionSettingsDao->getRSSTokenByUserId($userId, $context->getId());

        if ($token) {
            $request->redirectUrl($router->url($request, null, 'notification', 'notificationFeed', [$feedType, $token]));
        } else {
            $token = $notificationSubscriptionSettingsDao->insertNewRSSToken($userId, $context->getId());
            $request->redirectUrl($router->url($request, null, 'notification', 'notificationFeed', [$feedType, $token]));
        }
    }

    /**
     * Fetch the actual RSS feed
     * @param array $args
     * @param PKPRequest $request
     * @return bool
     */
    public function notificationFeed($args, $request) {
        if (isset($args[0]) && isset($args[1])) {
            $type = $args[0];
            $token = $args[1];
        } else {
            return false;
        }

        $this->setupTemplate(true);

        $application = PKPApplication::getApplication();
        $appName = $application->getNameKey();

        $site = $request->getSite();
        $siteTitle = $site->getLocalizedTitle();

        $notificationSubscriptionSettingsDao = DAORegistry::getDAO('NotificationSubscriptionSettingsDAO');
        $context = $request->getContext();
        
        // [Wizdam Safety] Ensure context exists for feed
        if (!$context) return false;

        $userId = $notificationSubscriptionSettingsDao->getUserIdByRSSToken($token, $context->getId());

        // Make sure the feed type is specified and valid
        $typeMap = [
            'rss' => 'rss.tpl',
            'rss2' => 'rss2.tpl',
            'atom' => 'atom.tpl'
        ];
        $contentTypeMap = [
            'rss' => 'rssContent.tpl',
            'rss2' => 'rss2Content.tpl',
            'atom' => 'atomContent.tpl'
        ];
        $mimeTypeMap = [
            'rss' => 'application/rdf+xml',
            'rss2' => 'application/rss+xml',
            'atom' => 'application/atom+xml'
        ];
        
        if (!isset($typeMap[$type])) return false;

        $notificationManager = new NotificationManager();
        $notifications = $notificationManager->getFormattedNotificationsForUser($request, $userId, NOTIFICATION_LEVEL_NORMAL, $context->getId(), null, 'notification/' . $contentTypeMap[$type]);

        $versionDao = DAORegistry::getDAO('VersionDAO');
        $version = $versionDao->getCurrentVersion();

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('version', $version->getVersionString());
        $templateMgr->assign('selfUrl', $request->getCompleteUrl());
        $templateMgr->assign('locale', AppLocale::getPrimaryLocale());
        $templateMgr->assign('appName', $appName);
        $templateMgr->assign('siteTitle', $siteTitle);
        $templateMgr->assign('formattedNotifications', $notifications); // assign_by_ref removed

        $templateMgr->display('notification/' . $typeMap[$type], $mimeTypeMap[$type]);

        return true;
    }

    /**
     * Display the public notification email subscription form
     * @param array $args
     * @param PKPRequest $request
     */
    public function subscribeMailList($args, $request) {
        $this->setupTemplate();

        $user = $request->getUser();

        if (!$user) {
            // [WIZDAM SECURITY] Lempar Variabel Keamanan ke Template Form
            $templateMgr = TemplateManager::getManager();
            $this->_assignSecurityVariables($templateMgr);

            import('lib.pkp.classes.notification.form.NotificationMailingListForm');
            $notificationMailingListForm = new NotificationMailingListForm();
            $notificationMailingListForm->display($request);
        } else {
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, 'notification'));
        }
    }

    /**
     * Save the public notification email subscription form
     * @param array $args
     * @param PKPRequest $request
     */
    public function saveSubscribeMailList($args, $request) {
        $this->validate();
        $this->setupTemplate(true);

        // [WIZDAM SECURITY] Validasi Token Ganda Sebelum Proses Simpan
        if (!$this->_validateSecurityTokens($request)) {
            $templateMgr = TemplateManager::getManager();
            $this->_assignSecurityVariables($templateMgr);
            $templateMgr->assign('error', 'common.captchaField.badCaptcha');
            
            import('lib.pkp.classes.notification.form.NotificationMailingListForm');
            $notificationMailingListForm = new NotificationMailingListForm();
            $notificationMailingListForm->readInputData();
            $notificationMailingListForm->display($request);
            return; // Hentikan eksekusi di sini
        }

        import('lib.pkp.classes.notification.form.NotificationMailingListForm');

        $notificationMailingListForm = new NotificationMailingListForm();
        $notificationMailingListForm->readInputData();

        if ($notificationMailingListForm->validate()) {
            $notificationMailingListForm->execute($request);
            $router = $request->getRouter();
            $request->redirectUrl($router->url($request, null, 'notification', 'mailListSubscribed', ['success']));
        } else {
            $notificationMailingListForm->display($request);
        }
    }

    /**
     * Display a success or error message if the user was subscribed
     * @param array $args
     * @param PKPRequest $request
     */
    public function mailListSubscribed($args, $request) {
        $this->setupTemplate();
        $status = array_shift($args);
        $templateMgr = TemplateManager::getManager();

        if ($status == 'success') {
            $templateMgr->assign('status', 'subscribeSuccess');
        } else {
            $templateMgr->assign('status', 'subscribeError');
            $templateMgr->assign('error', true);
        }

        $templateMgr->display('notification/maillistSubscribed.tpl');
    }

    /**
     * Confirm the subscription (accessed via emailed link)
     * @param array $args
     * @param PKPRequest $request
     */
    public function confirmMailListSubscription($args, $request) {
        $this->setupTemplate();
        $userToken = array_shift($args);

        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('confirm', true);

        $context = $request->getContext();
        $notificationMailListDao = DAORegistry::getDAO('NotificationMailListDAO');
        
        // [Wizdam Safety] Ensure context exists
        $contextId = $context ? $context->getId() : 0;
        
        $settingId = $notificationMailListDao->getMailListIdByToken($userToken, $contextId);

        if ($settingId) {
            $notificationMailListDao->confirmMailListSubscription($settingId);
            $templateMgr->assign('status', 'confirmSuccess');
        } else {
            $templateMgr->assign('status', 'confirmError');
            $templateMgr->assign('error', true);
        }

        $templateMgr->display('notification/maillistSubscribed.tpl');
    }

    /**
     * Save the maillist unsubscribe form
     * @param array $args
     * @param PKPRequest $request
     */
    public function unsubscribeMailList($args, $request) {
        $context = $request->getContext();
        $contextId = $context ? $context->getId() : 0;

        $this->setupTemplate();
        $templateMgr = TemplateManager::getManager();

        $userToken = array_shift($args);

        $notificationMailListDao = DAORegistry::getDAO('NotificationMailListDAO');
        if (isset($userToken)) {
            if ($notificationMailListDao->unsubscribeGuest($userToken, $contextId)) {
                $templateMgr->assign('status', "unsubscribeSuccess");
                $templateMgr->display('notification/maillistSubscribed.tpl');
            } else {
                $templateMgr->assign('status', "unsubscribeError");
                $templateMgr->assign('error', true);
                $templateMgr->display('notification/maillistSubscribed.tpl');
            }
        }
    }

    /**
     * Return formatted notification data using Json.
     * @param array $args
     * @param PKPRequest $request
     * @return string JSON
     */
    public function fetchNotification($args, $request) {
        $this->setupTemplate();
        $user = $request->getUser();
        $context = $request->getContext();
        $notificationDao = DAORegistry::getDAO('NotificationDAO');
        $notifications = [];

        // Get the notification options from request.
        // [SECURITY FIX] Sanitasi requestOptions (bukan $requestOptions yg undefined)
        $notificationOptions = $request->getUserVar('requestOptions');
        
        $userId = $user ? $user->getId() : 0;
        $contextId = $context ? $context->getId() : 0;

        if (is_array($notificationOptions)) {
            // Retrieve the notifications.
            $notifications = $this->_getNotificationsByOptions($notificationOptions, $contextId, $userId);
        } else {
            // No options, get only TRIVIAL notifications.
            if ($user) {
                $notifications = $notificationDao->getByUserId($user->getId(), NOTIFICATION_LEVEL_TRIVIAL);
                $notifications = $notifications->toArray();
            }
        }

        import('lib.pkp.classes.core.JSONMessage');
        $json = new JSONMessage();

        if (is_array($notifications) && !empty($notifications)) {
            $formattedNotificationsData = [];
            $notificationManager = new NotificationManager();

            // Format in place notifications.
            $formattedNotificationsData['inPlace'] = $notificationManager->formatToInPlaceNotification($request, $notifications);

            // Format general notifications.
            $formattedNotificationsData['general'] = $notificationManager->formatToGeneralNotification($request, $notifications);

            // Delete trivial notifications from database.
            $notificationManager->deleteTrivialNotifications($notifications);

            $json->setContent($formattedNotificationsData);
        }

        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Get the notifications using options.
     * @param array $notificationOptions
     * @param int $contextId
     * @param int|null $userId
     * @return array
     */
    public function _getNotificationsByOptions($notificationOptions, $contextId, $userId = null) {
        $notificationDao = DAORegistry::getDAO('NotificationDAO');
        $notificationsArray = [];
        $notificationMgr = new NotificationManager();
        $allUsersNotificationTypes = $notificationMgr->getAllUsersNotificationTypes();

        foreach ($notificationOptions as $level => $levelOptions) {
            if ($levelOptions) {
                foreach ($levelOptions as $type => $typeOptions) {
                    if ($typeOptions) {
                        $workingUserId = in_array($type, $allUsersNotificationTypes) ? null : $userId;
                        
                        $notificationsResultFactory = $notificationDao->getByAssoc(
                            $typeOptions['assocType'], 
                            $typeOptions['assocId'], 
                            $workingUserId, 
                            $type, 
                            $contextId
                        );
                        $notificationsArray = $this->_addNotificationsToArray($notificationsResultFactory, $notificationsArray);
                    } else {
                        if ($userId) {
                            $notificationsResultFactory = $notificationDao->getByUserId($userId, $level, $type, $contextId);
                            $notificationsArray = $this->_addNotificationsToArray($notificationsResultFactory, $notificationsArray);
                        }
                    }
                }
            } else {
                if ($userId) {
                    $notificationsResultFactory = $notificationDao->getByUserId($userId, $level, null, $contextId);
                    $notificationsArray = $this->_addNotificationsToArray($notificationsResultFactory, $notificationsArray);
                }
            }
            // Cleanup factory
            unset($notificationsResultFactory);
        }

        return $notificationsArray;
    }

    /**
     * Add notifications from a result factory to an array of existing notifications.
     * @param DAOResultFactory $resultFactory
     * @param array $notificationArray
     * @return array
     */
    public function _addNotificationsToArray($resultFactory, $notificationArray) {
        if (!$resultFactory->wasEmpty()) {
            $notificationArray = array_merge($notificationArray, $resultFactory->toArray());
        }

        return $notificationArray;
    }

    /**
     * Override setupTemplate() so we can load other locale components.
     * @param bool $subclass
     */
    public function setupTemplate($subclass = false) {
        AppLocale::requireComponents(LOCALE_COMPONENT_CORE_GRID, LOCALE_COMPONENT_CORE_SUBMISSION);
        parent::setupTemplate($subclass);
    }
}
?>
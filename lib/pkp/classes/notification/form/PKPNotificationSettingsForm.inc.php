<?php
declare(strict_types=1);

/**
 * @defgroup notification_form
 */

/**
 * @file classes/notification/form/PKPNotificationSettingsForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPNotificationSettingsForm
 * @ingroup notification_form
 *
 * @brief Form to edit notification settings.
 */

import('lib.pkp.classes.form.Form');

class PKPNotificationSettingsForm extends Form {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct('notification/settings.tpl');

        // Validation checks for this form
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPNotificationSettingsForm() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::PKPNotificationSettingsForm(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Display the form.
     * @param $request PKPRequest
     * @param $template string
     */
    public function display($request = null, $template = null) {
        $context = $request->getContext();
        $user = $request->getUser();
        $userId = $user->getId();

        // Hapus '&'
        $notificationSubscriptionSettingsDao = DAORegistry::getDAO('NotificationSubscriptionSettingsDAO');
        $blockedNotifications = $notificationSubscriptionSettingsDao->getNotificationSubscriptionSettings('blocked_notification', $userId, $context->getId());
        $emailSettings = $notificationSubscriptionSettingsDao->getNotificationSubscriptionSettings('emailed_notification', $userId, $context->getId());

        // Hapus '&'
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('blockedNotifications', $blockedNotifications);
        $templateMgr->assign('emailSettings', $emailSettings);
        $templateMgr->assign('titleVar', __('common.title'));
        $templateMgr->assign('userVar', __('common.user'));
        
        return parent::display($request, $template);
    }
}

?>
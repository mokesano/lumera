<?php
declare(strict_types=1);

/**
 * @file pages/user/UserGiftHandler.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class UserGiftHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user gifts and redemptions.
 * [WIZDAM EDITION] Extracted from UserHandler.
 */

import('pages.user.UserHandler');

class UserGiftHandler extends UserHandler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display user gifts page
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function gifts($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'user');

        // Ensure gift payments are enabled
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptGiftPayments = $paymentManager->acceptGiftPayments();
        if (!$acceptGiftPayments) $request->redirect(null, 'user');

        $acceptGiftSubscriptionPayments = $paymentManager->acceptGiftSubscriptionPayments();
        $journalId = $journal->getId();
        $user = $request->getUser();
        $userId = $user->getId();

        // Get user's redeemed and unreedemed gift subscriptions
        $giftDao = DAORegistry::getDAO('GiftDAO');
        $giftSubscriptions = $giftDao->getGiftsByTypeAndRecipient(
            ASSOC_TYPE_JOURNAL,
            $journalId,
            GIFT_TYPE_SUBSCRIPTION,
            $userId
        );

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('journalTitle', $journal->getLocalizedTitle());
        $templateMgr->assign('journalPath', $journal->getPath());
        $templateMgr->assign('acceptGiftSubscriptionPayments', $acceptGiftSubscriptionPayments);
        $templateMgr->assign('giftSubscriptions', $giftSubscriptions);
        $templateMgr->display('user/gifts.tpl');
    }

    /**
     * User redeems a gift
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function redeemGift($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        if (empty($args)) $request->redirect(null, 'user');

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'user');

        // Ensure gift payments are enabled
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptGiftPayments = $paymentManager->acceptGiftPayments();
        if (!$acceptGiftPayments) $request->redirect(null, 'user');

        $journalId = $journal->getId();
        $user = $request->getUser();
        $userId = $user->getId();
        $giftId = isset($args[0]) ? (int) $args[0] : 0;

        // Try to redeem the gift
        $giftDao = DAORegistry::getDAO('GiftDAO');
        $status = $giftDao->redeemGift(
            ASSOC_TYPE_JOURNAL,
            $journalId,
            $userId,
            $giftId
        );

        // Report redeem status to user
        import('classes.notification.NotificationManager');
        $notificationManager = new NotificationManager();

        switch ($status) {
            case GIFT_REDEEM_STATUS_SUCCESS:
                $notificationType = NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_SUCCESS;
                break;
            case GIFT_REDEEM_STATUS_ERROR_NO_GIFT_TO_REDEEM:
                $notificationType = NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_NO_GIFT_TO_REDEEM;
                break;
            case GIFT_REDEEM_STATUS_ERROR_GIFT_ALREADY_REDEEMED:
                $notificationType = NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_GIFT_ALREADY_REDEEMED;
                break;
            case GIFT_REDEEM_STATUS_ERROR_GIFT_INVALID:
                $notificationType = NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_GIFT_INVALID;
                break;
            case GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_TYPE_INVALID:
                $notificationType = NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_TYPE_INVALID;
                break;
            case GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_NON_EXPIRING:
                $notificationType = NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR_SUBSCRIPTION_NON_EXPIRING;
                break;
            default:
                $notificationType = NOTIFICATION_TYPE_NO_GIFT_TO_REDEEM;
        }

        $user = $request->getUser();

        $notificationManager->createTrivialNotification($user->getId(), $notificationType);
        $request->redirect(null, 'user', 'gifts');
    }
}
?>
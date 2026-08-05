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
 */

import('pages.user.UserHandler');

class UserGiftHandler extends UserHandler {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display user gifts page.
     * @param array $args
     * @param object|null $request
     * @return void
     */
    public function gifts($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        $journal = $request->getJournal();
        if ($journal === null) {
            $request->redirect(null, 'user');
            return;
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptGiftPayments = (bool) $paymentManager->acceptGiftPayments();
        if (!$acceptGiftPayments) {
            $request->redirect(null, 'user');
            return;
        }

        $acceptGiftSubscriptionPayments = (bool) $paymentManager->acceptGiftSubscriptionPayments();
        $journalId = (int) $journal->getId();
        $user = $request->getUser();
        if ($user === null) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = (int) $user->getId();

        /** @var GiftDAO $giftDao */
        $giftDao = DAORegistry::getDAO('GiftDAO');
        $giftSubscriptions = $giftDao->getGiftsByTypeAndRecipient(
            ASSOC_TYPE_JOURNAL,
            $journalId,
            GIFT_TYPE_SUBSCRIPTION,
            $userId
        );

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('journalTitle', (string) $journal->getLocalizedTitle());
        $templateMgr->assign('journalPath', (string) $journal->getPath());
        $templateMgr->assign('acceptGiftSubscriptionPayments', $acceptGiftSubscriptionPayments);
        $templateMgr->assign('giftSubscriptions', $giftSubscriptions);
        $templateMgr->display('user/gifts.tpl');
    }

    /**
     * User redeems a gift.
     * @param array $args
     * @param object|null $request
     * @return void
     */
    public function redeemGift($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        if (empty($args)) {
            $request->redirect(null, 'user');
            return;
        }

        $journal = $request->getJournal();
        if ($journal === null) {
            $request->redirect(null, 'user');
            return;
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptGiftPayments = (bool) $paymentManager->acceptGiftPayments();
        if (!$acceptGiftPayments) {
            $request->redirect(null, 'user');
            return;
        }

        $journalId = (int) $journal->getId();
        $user = $request->getUser();
        if ($user === null) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = (int) $user->getId();
        $giftId = !empty($args[0]) ? (int) $args[0] : 0;

        /** @var GiftDAO $giftDao */
        $giftDao = DAORegistry::getDAO('GiftDAO');
        $status = $giftDao->redeemGift(
            ASSOC_TYPE_JOURNAL,
            $journalId,
            $userId,
            $giftId
        );

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
                $notificationType = defined('NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR') 
                    ? NOTIFICATION_TYPE_GIFT_REDEEM_STATUS_ERROR 
                    : NOTIFICATION_TYPE_ERROR;
        }

        $notificationManager->createTrivialNotification($userId, $notificationType);
        $request->redirect(null, 'user', 'gifts');
    }

}
?>
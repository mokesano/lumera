<?php
declare(strict_types=1);

/**
 * @defgroup gifts
 */

/**
 * @file pages/gifts/GiftsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GiftsHandler
 * @ingroup gifts
 *
 * @brief Handle requests to buy gifts
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.handler.Handler');

class GiftsHandler extends Handler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GiftsHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::GiftsHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Display payment form for buying a gift subscription
     * @param array $args
     * @param PKPRequest $request
     */
    public function purchaseGiftSubscription($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'index');

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = $paymentManager->acceptGiftSubscriptionPayments();
        if (!$acceptSubscriptionPayments) $request->redirect(null, 'index');

        $this->setupTemplate();

        import('classes.subscription.form.GiftIndividualSubscriptionForm');
        $giftSubscriptionForm = new GiftIndividualSubscriptionForm($request);
        $giftSubscriptionForm->initData();
        $giftSubscriptionForm->display();
    }

    /**
     * Process payment form for buying a gift subscription
     * @param array $args
     * @param PKPRequest $request
     */
    public function payPurchaseGiftSubscription($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'index');

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = $paymentManager->acceptGiftSubscriptionPayments();
        if (!$acceptSubscriptionPayments) $request->redirect(null, 'index');

        $this->setupTemplate();
        $user = $request->getUser();

        // If buyer is logged in, save buyer user id as part of gift details
        $buyerUserId = $user ? (int) $user->getId() : null;

        import('classes.subscription.form.GiftIndividualSubscriptionForm');
        $giftSubscriptionForm = new GiftIndividualSubscriptionForm($request, $buyerUserId);
        $giftSubscriptionForm->readInputData();

        if ($giftSubscriptionForm->validate()) {
            $giftSubscriptionForm->execute();
        } else {
            $giftSubscriptionForm->display();
        }
    }

    /**
     * Display generic thank you message following payment
     * @param array $args
     * @param PKPRequest $request
     */
    public function thankYou($args, $request) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $templateMgr = TemplateManager::getManager();
        $this->setupTemplate();
        $journal = $request->getJournal();

        $templateMgr->assign([
            'currentUrl' => $request->url(null, null, 'gifts'),
            'pageTitle' => 'gifts.thankYou',
            'journalName' => $journal->getLocalizedTitle(),
            'message' => 'gifts.thankYouMessage'
        ]);
        $templateMgr->display('common/message.tpl');
    }
}
?>
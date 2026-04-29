<?php
declare(strict_types=1);

/**
 * @file pages/user/UserSubscriptionHandler.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class UserSubscriptionHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user subscriptions and memberships.
 * [WIZDAM EDITION] Extracted from bloated UserHandler for Single Responsibility Principle.
 */

import('pages.user.UserHandler');

class UserSubscriptionHandler extends UserHandler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display subscriptions page
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function subscriptions($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'user');
        if ($journal->getSetting('publishingMode') !=  PUBLISHING_MODE_SUBSCRIPTION) $request->redirect(null, 'user');

        $journalId = $journal->getId();
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $individualSubscriptionTypesExist = $subscriptionTypeDao->subscriptionTypesExistByInstitutional($journalId, false);
        $institutionalSubscriptionTypesExist = $subscriptionTypeDao->subscriptionTypesExistByInstitutional($journalId, true);
        if (!$individualSubscriptionTypesExist && !$institutionalSubscriptionTypesExist) $request->redirect(null, 'user');

        $user = $request->getUser();
        $userId = $user->getId();

        // Subscriptions contact and additional information
        $subscriptionName = $journal->getSetting('subscriptionName');
        $subscriptionEmail = $journal->getSetting('subscriptionEmail');
        $subscriptionPhone = $journal->getSetting('subscriptionPhone');
        $subscriptionFax = $journal->getSetting('subscriptionFax');
        $subscriptionMailingAddress = $journal->getSetting('subscriptionMailingAddress');
        $subscriptionAdditionalInformation = $journal->getLocalizedSetting('subscriptionAdditionalInformation');
        
        // Get subscriptions and options for current journal
        $userIndividualSubscription = null;
        $userInstitutionalSubscriptions = null;

        if ($individualSubscriptionTypesExist) {
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
            $userIndividualSubscription = $subscriptionDao->getSubscriptionByUserForJournal($userId, $journalId);
        }

        if ($institutionalSubscriptionTypesExist) {
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
            $userInstitutionalSubscriptions = $subscriptionDao->getSubscriptionsByUserForJournal($userId, $journalId);
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = $paymentManager->acceptSubscriptionPayments();

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('subscriptionName', $subscriptionName);
        $templateMgr->assign('subscriptionEmail', $subscriptionEmail);
        $templateMgr->assign('subscriptionPhone', $subscriptionPhone);
        $templateMgr->assign('subscriptionFax', $subscriptionFax);
        $templateMgr->assign('subscriptionMailingAddress', $subscriptionMailingAddress);
        $templateMgr->assign('subscriptionAdditionalInformation', $subscriptionAdditionalInformation);
        $templateMgr->assign('journalTitle', $journal->getLocalizedTitle());
        $templateMgr->assign('journalPath', $journal->getPath());
        $templateMgr->assign('acceptSubscriptionPayments', $acceptSubscriptionPayments);
        $templateMgr->assign('individualSubscriptionTypesExist', $individualSubscriptionTypesExist);
        $templateMgr->assign('institutionalSubscriptionTypesExist', $institutionalSubscriptionTypesExist);
        $templateMgr->assign('userIndividualSubscription', $userIndividualSubscription);
        $templateMgr->assign('userInstitutionalSubscriptions', $userInstitutionalSubscriptions);
        $templateMgr->display('user/subscriptions.tpl');
    }

    //
    // Payments
    //
    /**
     * Purchase a subscription.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function purchaseSubscription($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        if (empty($args)) $request->redirect(null, 'user');

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'user');
        if ($journal->getSetting('publishingMode') != PUBLISHING_MODE_SUBSCRIPTION) $request->redirect(null, 'user');

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = $paymentManager->acceptSubscriptionPayments();
        if (!$acceptSubscriptionPayments) $request->redirect(null, 'user');

        $this->setupTemplate($request, true);
        $user = $request->getUser();
        $userId = $user->getId();
        $journalId = $journal->getId();

        $institutional = array_shift($args);
        $subscriptionId = null;
        if (!empty($args)) {
            $subscriptionId = (int) array_shift($args);
        }

        if ($institutional == 'institutional') {
            $institutional = true;
            import('classes.subscription.form.UserInstitutionalSubscriptionForm');
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            $institutional = false;
            import('classes.subscription.form.UserIndividualSubscriptionForm');
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }

        if (isset($subscriptionId)) {
            // Ensure subscription to be updated is for this user
            if (!$subscriptionDao->subscriptionExistsByUser($subscriptionId, $userId)) {
                $request->redirect(null, 'user');
            }

            // Ensure subscription can be updated
            $subscription = $subscriptionDao->getSubscription($subscriptionId);
            $subscriptionStatus = $subscription->getStatus();
            import('classes.subscription.Subscription');
            $validStatus = [
                SUBSCRIPTION_STATUS_ACTIVE,
                SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT,
                SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT
            ];

            if (!in_array($subscriptionStatus, $validStatus)) $request->redirect(null, 'user');

            if ($institutional) {
                $subscriptionForm = new UserInstitutionalSubscriptionForm($request, $userId, $subscriptionId);
            } else {
                $subscriptionForm = new UserIndividualSubscriptionForm($request, $userId, $subscriptionId);
            }

        } else {
            if ($institutional) {
                $subscriptionForm = new UserInstitutionalSubscriptionForm($request, $userId);
            } else {
                // Ensure user does not already have an individual subscription
                if ($subscriptionDao->subscriptionExistsByUserForJournal($userId, $journalId)) {
                    $request->redirect(null, 'user');
                }
                $subscriptionForm = new UserIndividualSubscriptionForm($request, $userId);
            }
        }

        $subscriptionForm->initData();
        $subscriptionForm->display();
    }

    /**
     * Pay for a subscription purchase.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function payPurchaseSubscription($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        if (empty($args)) $request->redirect(null, 'user');

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'user');
        if ($journal->getSetting('publishingMode') != PUBLISHING_MODE_SUBSCRIPTION) $request->redirect(null, 'user');

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = $paymentManager->acceptSubscriptionPayments();
        if (!$acceptSubscriptionPayments) $request->redirect(null, 'user');

        $this->setupTemplate($request, true);
        $user = $request->getUser();
        $userId = $user->getId();
        $journalId = $journal->getId();

        $institutional = array_shift($args);
        $subscriptionId = null;
        if (!empty($args)) {
            $subscriptionId = (int) array_shift($args);
        }

        if ($institutional == 'institutional') {
            $institutional = true;
            import('classes.subscription.form.UserInstitutionalSubscriptionForm');
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            $institutional = false;
            import('classes.subscription.form.UserIndividualSubscriptionForm');
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }

        if (isset($subscriptionId)) {
            // Ensure subscription to be updated is for this user
            if (!$subscriptionDao->subscriptionExistsByUser($subscriptionId, $userId)) {
                $request->redirect(null, 'user');
            }

            // Ensure subscription can be updated
            $subscription = $subscriptionDao->getSubscription($subscriptionId);
            $subscriptionStatus = $subscription->getStatus();
            import('classes.subscription.Subscription');
            $validStatus = [
                SUBSCRIPTION_STATUS_ACTIVE,
                SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT,
                SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT
            ];

            if (!in_array($subscriptionStatus, $validStatus)) $request->redirect(null, 'user');

            if ($institutional) {
                $subscriptionForm = new UserInstitutionalSubscriptionForm($request, $userId, $subscriptionId);
            } else {
                $subscriptionForm = new UserIndividualSubscriptionForm($request, $userId, $subscriptionId);
            }

        } else {
            if ($institutional) {
                $subscriptionForm = new UserInstitutionalSubscriptionForm($request, $userId);
            } else {
                // Ensure user does not already have an individual subscription
                if ($subscriptionDao->subscriptionExistsByUserForJournal($userId, $journalId)) {
                    $request->redirect(null, 'user');
                }
                $subscriptionForm = new UserIndividualSubscriptionForm($request, $userId);
            }
        }

        $subscriptionForm->readInputData();

        // Check for any special cases before trying to save
        $editData = false;
        if ((int) $request->getUserVar('addIpRange')) {
            $editData = true;
            $ipRanges = $subscriptionForm->getData('ipRanges');
            $ipRanges[] = '';
            $subscriptionForm->setData('ipRanges', $ipRanges);

        } else if (($delIpRange = $request->getUserVar('delIpRange')) && count($delIpRange) == 1) {
            $editData = true;
            list($delIpRangeIndex) = array_keys($delIpRange);
            $delIpRangeIndex = (int) $delIpRangeIndex;
            $ipRanges = $subscriptionForm->getData('ipRanges');
            array_splice($ipRanges, $delIpRangeIndex, 1);
            $subscriptionForm->setData('ipRanges', $ipRanges);
        }

        if ($editData) {
            $subscriptionForm->display();
        } else {
            if ($subscriptionForm->validate()) {
                $subscriptionForm->execute();
            } else {
                $subscriptionForm->display();
            }
        }
    }

    /**
     * Complete the purchase subscription process.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function completePurchaseSubscription($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        if (count($args) != 2) $request->redirect(null, 'user');

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'user');
        if ($journal->getSetting('publishingMode') != PUBLISHING_MODE_SUBSCRIPTION) $request->redirect(null, 'user');

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = $paymentManager->acceptSubscriptionPayments();
        if (!$acceptSubscriptionPayments) $request->redirect(null, 'user');

        $this->setupTemplate($request, true);
        $user = $request->getUser();
        $userId = $user->getId();
        $journalId = $journal->getId();

        $institutional = array_shift($args);
        $subscriptionId = (int) array_shift($args);

        if ($institutional == 'institutional') {
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }

        if (!$subscriptionDao->subscriptionExistsByUser($subscriptionId, $userId)) $request->redirect(null, 'user');

        $subscription = $subscriptionDao->getSubscription($subscriptionId);
        $subscriptionStatus = $subscription->getStatus();
        import('classes.subscription.Subscription');
        $validStatus = [SUBSCRIPTION_STATUS_ACTIVE, SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT];

        if (!in_array($subscriptionStatus, $validStatus)) $request->redirect(null, 'user');

        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType($subscription->getTypeId());

        $queuedPayment = $paymentManager->createQueuedPayment($journal->getId(), PAYMENT_TYPE_PURCHASE_SUBSCRIPTION, $user->getId(), $subscriptionId, $subscriptionType->getCost(), $subscriptionType->getCurrencyCodeAlpha());
        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }

    /**
     * Pay the "renew subscription" fee.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function payRenewSubscription($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        if (count($args) != 2) $request->redirect(null, 'user');

        $journal = $request->getJournal();
        if (!$journal) $request->redirect(null, 'user');
        if ($journal->getSetting('publishingMode') != PUBLISHING_MODE_SUBSCRIPTION) $request->redirect(null, 'user');

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = $paymentManager->acceptSubscriptionPayments();
        if (!$acceptSubscriptionPayments) $request->redirect(null, 'user');

        $this->setupTemplate($request, true);
        $user = $request->getUser();
        $userId = $user->getId();
        $journalId = $journal->getId();

        $institutional = array_shift($args);
        $subscriptionId = (int) array_shift($args);

        if ($institutional == 'institutional') {
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }

        if (!$subscriptionDao->subscriptionExistsByUser($subscriptionId, $userId)) $request->redirect(null, 'user');

        $subscription = $subscriptionDao->getSubscription($subscriptionId);

        if ($subscription->isNonExpiring()) $request->redirect(null, 'user');

        import('classes.subscription.Subscription');
        $subscriptionStatus = $subscription->getStatus();
        $validStatus = [
            SUBSCRIPTION_STATUS_ACTIVE,
            SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT,
            SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT
        ];

        if (!in_array($subscriptionStatus, $validStatus)) $request->redirect(null, 'user');

        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType($subscription->getTypeId());

        $queuedPayment = $paymentManager->createQueuedPayment($journal->getId(), PAYMENT_TYPE_RENEW_SUBSCRIPTION, $user->getId(), $subscriptionId, $subscriptionType->getCost(), $subscriptionType->getCurrencyCodeAlpha());
        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }

    /**
     * Pay for a membership.
     * @param array $args
     * @param object|null $request PKPRequest
     */
    public function payMembership($args, $request = null) {
        // [WIZDAM] Strict Type Guard
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request);

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);

        $journal = $request->getJournal();
        $user = $request->getUser();

        $queuedPayment = $paymentManager->createQueuedPayment($journal->getId(), PAYMENT_TYPE_MEMBERSHIP, $user->getId(), null,  $journal->getSetting('membershipFee'));
        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }
}
?>
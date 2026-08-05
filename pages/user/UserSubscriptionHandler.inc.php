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
 */

import('pages.user.UserHandler');

class UserSubscriptionHandler extends UserHandler {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Display subscriptions page.
     * @param array $args
     * @param Request|null $request
     * @return void
     */
    public function subscriptions($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        $journal = $request->getJournal();
        if ($journal === null) {
            $request->redirect(null, 'user');
            return;
        }
        
        $publishingMode = (int) $journal->getSetting('publishingMode');
        if ($publishingMode !== PUBLISHING_MODE_SUBSCRIPTION) {
            $request->redirect(null, 'user');
            return;
        }

        $journalId = (int) $journal->getId();
        
        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $individualSubscriptionTypesExist = (bool) $subscriptionTypeDao->subscriptionTypesExistByInstitutional($journalId, false);
        $institutionalSubscriptionTypesExist = (bool) $subscriptionTypeDao->subscriptionTypesExistByInstitutional($journalId, true);
        
        if (!$individualSubscriptionTypesExist && !$institutionalSubscriptionTypesExist) {
            $request->redirect(null, 'user');
            return;
        }

        $user = $request->getUser();
        if ($user === null) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = (int) $user->getId();

        $subscriptionName = (string) $journal->getSetting('subscriptionName');
        $subscriptionEmail = (string) $journal->getSetting('subscriptionEmail');
        $subscriptionPhone = (string) $journal->getSetting('subscriptionPhone');
        $subscriptionFax = (string) $journal->getSetting('subscriptionFax');
        $subscriptionMailingAddress = (string) $journal->getSetting('subscriptionMailingAddress');
        $subscriptionAdditionalInformation = (string) $journal->getLocalizedSetting('subscriptionAdditionalInformation');
        
        $userIndividualSubscription = null;
        $userInstitutionalSubscriptions = null;

        if ($individualSubscriptionTypesExist) {
            /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
            $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
            $userIndividualSubscription = $individualSubscriptionDao->getSubscriptionByUserForJournal($userId, $journalId);
        }

        if ($institutionalSubscriptionTypesExist) {
            /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
            $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
            $userInstitutionalSubscriptions = $institutionalSubscriptionDao->getSubscriptionsByUserForJournal($userId, $journalId);
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = (bool) $paymentManager->acceptSubscriptionPayments();

        $this->setupTemplate($request, true);
        $templateMgr = TemplateManager::getManager();

        $templateMgr->assign('subscriptionName', $subscriptionName);
        $templateMgr->assign('subscriptionEmail', $subscriptionEmail);
        $templateMgr->assign('subscriptionPhone', $subscriptionPhone);
        $templateMgr->assign('subscriptionFax', $subscriptionFax);
        $templateMgr->assign('subscriptionMailingAddress', $subscriptionMailingAddress);
        $templateMgr->assign('subscriptionAdditionalInformation', $subscriptionAdditionalInformation);
        $templateMgr->assign('journalTitle', (string) $journal->getLocalizedTitle());
        $templateMgr->assign('journalPath', (string) $journal->getPath());
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
     * @param object|null $request
     * @return void
     */
    public function purchaseSubscription($args, $request = null) {
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
        
        $publishingMode = (int) $journal->getSetting('publishingMode');
        if ($publishingMode !== PUBLISHING_MODE_SUBSCRIPTION) {
            $request->redirect(null, 'user');
            return;
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = (bool) $paymentManager->acceptSubscriptionPayments();
        if (!$acceptSubscriptionPayments) {
            $request->redirect(null, 'user');
            return;
        }

        $this->setupTemplate($request, true);
        $user = $request->getUser();
        if ($user === null) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = (int) $user->getId();
        $journalId = (int) $journal->getId();

        $institutionalArg = (string) array_shift($args);
        $subscriptionId = null;
        if (!empty($args)) {
            $subscriptionId = (int) array_shift($args);
        }

        $isInstitutional = ($institutionalArg === 'institutional');
        
        if ($isInstitutional) {
            import('classes.subscription.form.UserInstitutionalSubscriptionForm');
            /** @var InstitutionalSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            import('classes.subscription.form.UserIndividualSubscriptionForm');
            /** @var IndividualSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }

        if ($subscriptionId !== null) {
            if (!$subscriptionDao->subscriptionExistsByUser($subscriptionId, $userId)) {
                $request->redirect(null, 'user');
                return;
            }

            $subscription = $subscriptionDao->getSubscription($subscriptionId);
            if ($subscription === null) {
                $request->redirect(null, 'user');
                return;
            }
            
            $subscriptionStatus = (int) $subscription->getStatus();
            import('classes.subscription.Subscription');
            $validStatus = [
                SUBSCRIPTION_STATUS_ACTIVE,
                SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT,
                SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT
            ];

            if (!in_array($subscriptionStatus, $validStatus, true)) {
                $request->redirect(null, 'user');
                return;
            }

            if ($isInstitutional) {
                $subscriptionForm = new UserInstitutionalSubscriptionForm($request, $userId, $subscriptionId); // Undefined type 'UserInstitutionalSubscriptionForm'.
            } else {
                $subscriptionForm = new UserIndividualSubscriptionForm($request, $userId, $subscriptionId); // Undefined type 'UserIndividualSubscriptionForm'.
            }

        } else {
            if ($isInstitutional) {
                $subscriptionForm = new UserInstitutionalSubscriptionForm($request, $userId); // Undefined type 'UserInstitutionalSubscriptionForm'.
            } else {
                if ($subscriptionDao->subscriptionExistsByUserForJournal($userId, $journalId)) {
                    $request->redirect(null, 'user');
                    return;
                }
                $subscriptionForm = new UserIndividualSubscriptionForm($request, $userId); // Undefined type 'UserIndividualSubscriptionForm'.
            }
        }

        $subscriptionForm->initData();
        $subscriptionForm->display();
    }

    /**
     * Pay for a subscription purchase.
     * @param array $args
     * @param object|null $request
     * @return void
     */
    public function payPurchaseSubscription($args, $request = null) {
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
        
        $publishingMode = (int) $journal->getSetting('publishingMode');
        if ($publishingMode !== PUBLISHING_MODE_SUBSCRIPTION) {
            $request->redirect(null, 'user');
            return;
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = (bool) $paymentManager->acceptSubscriptionPayments();
        if (!$acceptSubscriptionPayments) {
            $request->redirect(null, 'user');
            return;
        }

        $this->setupTemplate($request, true);
        $user = $request->getUser();
        if ($user === null) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = (int) $user->getId();
        $journalId = (int) $journal->getId();

        $institutionalArg = (string) array_shift($args);
        $subscriptionId = null;
        if (!empty($args)) {
            $subscriptionId = (int) array_shift($args);
        }

        $isInstitutional = ($institutionalArg === 'institutional');
        
        if ($isInstitutional) {
            import('classes.subscription.form.UserInstitutionalSubscriptionForm');
            /** @var InstitutionalSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            import('classes.subscription.form.UserIndividualSubscriptionForm');
            /** @var IndividualSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }

        if ($subscriptionId !== null) {
            if (!$subscriptionDao->subscriptionExistsByUser($subscriptionId, $userId)) {
                $request->redirect(null, 'user');
                return;
            }

            $subscription = $subscriptionDao->getSubscription($subscriptionId);
            if ($subscription === null) {
                $request->redirect(null, 'user');
                return;
            }
            
            $subscriptionStatus = (int) $subscription->getStatus();
            import('classes.subscription.Subscription');
            $validStatus = [
                SUBSCRIPTION_STATUS_ACTIVE,
                SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT,
                SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT
            ];

            if (!in_array($subscriptionStatus, $validStatus, true)) {
                $request->redirect(null, 'user');
                return;
            }

            if ($isInstitutional) {
                $subscriptionForm = new UserInstitutionalSubscriptionForm($request, $userId, $subscriptionId); // Undefined type 'UserInstitutionalSubscriptionForm'.
            } else {
                $subscriptionForm = new UserIndividualSubscriptionForm($request, $userId, $subscriptionId); // Undefined type 'UserIndividualSubscriptionForm'.
            }

        } else {
            if ($isInstitutional) {
                $subscriptionForm = new UserInstitutionalSubscriptionForm($request, $userId); // Undefined type 'UserInstitutionalSubscriptionForm'.
            } else {
                if ($subscriptionDao->subscriptionExistsByUserForJournal($userId, $journalId)) {
                    $request->redirect(null, 'user');
                    return;
                }
                $subscriptionForm = new UserIndividualSubscriptionForm($request, $userId); // Undefined type 'UserIndividualSubscriptionForm'.
            }
        }

        $subscriptionForm->readInputData();

        $editData = false;
        $addIpRange = $request->getUserVar('addIpRange');
        if ($addIpRange !== null && (int) $addIpRange > 0) {
            $editData = true;
            $ipRanges = $subscriptionForm->getData('ipRanges');
            if (!is_array($ipRanges)) {
                $ipRanges = [];
            }
            $ipRanges[] = '';
            $subscriptionForm->setData('ipRanges', $ipRanges);

        } else {
            $delIpRange = $request->getUserVar('delIpRange');
            if (is_array($delIpRange) && count($delIpRange) === 1) {
                $editData = true;
                $delIpRangeKeys = array_keys($delIpRange);
                $delIpRangeIndex = (int) $delIpRangeKeys[0];
                $ipRanges = $subscriptionForm->getData('ipRanges');
                if (is_array($ipRanges)) {
                    array_splice($ipRanges, $delIpRangeIndex, 1);
                    $subscriptionForm->setData('ipRanges', $ipRanges);
                }
            }
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
     * @param object|null $request
     * @return void
     */
    public function completePurchaseSubscription($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        if (count($args) !== 2) {
            $request->redirect(null, 'user');
            return;
        }

        $journal = $request->getJournal();
        if ($journal === null) {
            $request->redirect(null, 'user');
            return;
        }
        
        $publishingMode = (int) $journal->getSetting('publishingMode');
        if ($publishingMode !== PUBLISHING_MODE_SUBSCRIPTION) {
            $request->redirect(null, 'user');
            return;
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = (bool) $paymentManager->acceptSubscriptionPayments();
        if (!$acceptSubscriptionPayments) {
            $request->redirect(null, 'user');
            return;
        }

        $this->setupTemplate($request, true);
        $user = $request->getUser();
        if ($user === null) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = (int) $user->getId();
        $journalId = (int) $journal->getId();

        $institutionalArg = (string) array_shift($args);
        $subscriptionId = (int) array_shift($args);

        $isInstitutional = ($institutionalArg === 'institutional');
        
        if ($isInstitutional) {
            /** @var InstitutionalSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            /** @var IndividualSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }

        if (!$subscriptionDao->subscriptionExistsByUser($subscriptionId, $userId)) {
            $request->redirect(null, 'user');
            return;
        }

        $subscription = $subscriptionDao->getSubscription($subscriptionId);
        if ($subscription === null) {
            $request->redirect(null, 'user');
            return;
        }
        
        $subscriptionStatus = (int) $subscription->getStatus();
        import('classes.subscription.Subscription');
        $validStatus = [SUBSCRIPTION_STATUS_ACTIVE, SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT];

        if (!in_array($subscriptionStatus, $validStatus, true)) {
            $request->redirect(null, 'user');
            return;
        }

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $subscription->getTypeId());
        
        if ($subscriptionType === null) {
            $request->redirect(null, 'user');
            return;
        }

        $queuedPayment = $paymentManager->createQueuedPayment(
            $journalId, 
            PAYMENT_TYPE_PURCHASE_SUBSCRIPTION, 
            $userId, 
            $subscriptionId, 
            (float) $subscriptionType->getCost(), 
            (string) $subscriptionType->getCurrencyCodeAlpha()
        );

        if (method_exists($queuedPayment, 'getInvoiceId') && $queuedPayment->getInvoiceId() > 0) {
            import('lib.wizdam.classes.security.SecurityHashService');
            $hashService = new SecurityHashService();
            $invoiceId = (int) $queuedPayment->getInvoiceId();
            $hash = $hashService->generateHash('invoice', $invoiceId);
            $request->redirect(null, 'billing', 'invoice', ["{$hash}-{$invoiceId}"]);
            return;
        }

        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);
        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }

    /**
     * Pay the "renew subscription" fee.
     * @param array $args
     * @param object|null $request
     * @return void
     */
    public function payRenewSubscription($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();

        if (count($args) !== 2) {
            $request->redirect(null, 'user');
            return;
        }

        $journal = $request->getJournal();
        if ($journal === null) {
            $request->redirect(null, 'user');
            return;
        }
        
        $publishingMode = (int) $journal->getSetting('publishingMode');
        if ($publishingMode !== PUBLISHING_MODE_SUBSCRIPTION) {
            $request->redirect(null, 'user');
            return;
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);
        $acceptSubscriptionPayments = (bool) $paymentManager->acceptSubscriptionPayments();
        if (!$acceptSubscriptionPayments) {
            $request->redirect(null, 'user');
            return;
        }

        $this->setupTemplate($request, true);
        $user = $request->getUser();
        if ($user === null) {
            $request->redirect(null, 'login');
            return;
        }
        $userId = (int) $user->getId();
        $journalId = (int) $journal->getId();

        $institutionalArg = (string) array_shift($args);
        $subscriptionId = (int) array_shift($args);

        $isInstitutional = ($institutionalArg === 'institutional');
        
        if ($isInstitutional) {
            /** @var InstitutionalSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        } else {
            /** @var IndividualSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        }

        if (!$subscriptionDao->subscriptionExistsByUser($subscriptionId, $userId)) {
            $request->redirect(null, 'user');
            return;
        }

        $subscription = $subscriptionDao->getSubscription($subscriptionId);
        if ($subscription === null) {
            $request->redirect(null, 'user');
            return;
        }

        if ($subscription->isNonExpiring()) {
            $request->redirect(null, 'user');
            return;
        }

        import('classes.subscription.Subscription');
        $subscriptionStatus = (int) $subscription->getStatus();
        $validStatus = [
            SUBSCRIPTION_STATUS_ACTIVE,
            SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT,
            SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT
        ];

        if (!in_array($subscriptionStatus, $validStatus, true)) {
            $request->redirect(null, 'user');
            return;
        }

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $subscription->getTypeId());
        
        if ($subscriptionType === null) {
            $request->redirect(null, 'user');
            return;
        }

        $queuedPayment = $paymentManager->createQueuedPayment(
            $journalId, 
            PAYMENT_TYPE_RENEW_SUBSCRIPTION, 
            $userId, 
            $subscriptionId, 
            (float) $subscriptionType->getCost(), 
            (string) $subscriptionType->getCurrencyCodeAlpha()
        );

        if (method_exists($queuedPayment, 'getInvoiceId') && $queuedPayment->getInvoiceId() > 0) {
            import('lib.wizdam.classes.security.SecurityHashService');
            $hashService = new SecurityHashService();
            $invoiceId = (int) $queuedPayment->getInvoiceId();
            $hash = $hashService->generateHash('invoice', $invoiceId);
            $request->redirect(null, 'billing', 'invoice', ["{$hash}-{$invoiceId}"]);
            return;
        }

        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);
        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }

    /**
     * Pay for a membership.
     * @param array $args
     * @param object|null $request
     * @return void
     */
    public function payMembership($args, $request = null) {
        $request = $request instanceof PKPRequest ? $request : Application::get()->getRequest();

        $this->validate();
        $this->setupTemplate($request);

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($request);

        $journal = $request->getJournal();
        $user = $request->getUser();
        
        if ($journal === null || $user === null) {
            $request->redirect(null, 'user');
            return;
        }

        $journalId = (int) $journal->getId();
        $userId = (int) $user->getId();
        $membershipFee = (float) $journal->getSetting('membershipFee');

        $queuedPayment = $paymentManager->createQueuedPayment(
            $journalId, 
            PAYMENT_TYPE_MEMBERSHIP, 
            $userId, 
            null,  
            $membershipFee
        );

        if (method_exists($queuedPayment, 'getInvoiceId') && $queuedPayment->getInvoiceId() > 0) {
            import('lib.wizdam.classes.security.SecurityHashService');
            $hashService = new SecurityHashService();
            $invoiceId = (int) $queuedPayment->getInvoiceId();
            $hash = $hashService->generateHash('invoice', $invoiceId);
            $request->redirect(null, 'billing', 'invoice', ["{$hash}-{$invoiceId}"]);
            return;
        }

        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);
        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }
    
}
?>
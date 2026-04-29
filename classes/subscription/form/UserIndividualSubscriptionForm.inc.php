<?php

/**
 * @defgroup subscription_form
 */
 
/**
 * @file classes/subscription/form/UserIndividualSubscriptionForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserIndividualSubscriptionForm
 * @ingroup subscription_form
 *
 * @brief Form class for user purchase of individual subscription.
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.form.Form');

class UserIndividualSubscriptionForm extends Form {
    /** @var $request PKPRequest */
    public $request;

    /** @var userId int the user associated with the subscription */
    public $userId;

    /** @var subscription the subscription being purchased */
    public $subscription;

    /** @var subscriptionTypes Array subscription types */
    public $subscriptionTypes;

    /**
     * Constructor
     * @param $request PKPRequest
     * @param $userId int
     * @param $subscriptionId int
     */
    public function __construct($request, $userId = null, $subscriptionId = null) {
        parent::__construct('subscription/userIndividualSubscriptionForm.tpl');

        $this->userId = isset($userId) ? (int) $userId : null;
        $this->subscription = null;
        $this->request = $request;

        $subscriptionId = isset($subscriptionId) ? (int) $subscriptionId : null;

        if (isset($subscriptionId)) {
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO'); 
            if ($subscriptionDao->subscriptionExists($subscriptionId)) {
                $this->subscription = $subscriptionDao->getSubscription($subscriptionId);
            }
        }

        $journal = $this->request->getJournal();
        $journalId = $journal->getId();

        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, false, false);
        $this->subscriptionTypes = $subscriptionTypes->toArray();

        // Ensure subscription type is valid
        // [WIZDAM FIX] Replaced create_function with Closure and use()
        $this->addCheck(new FormValidatorCustom($this, 'typeId', 'required', 'user.subscriptions.form.typeIdValid', function($typeId) use ($journalId) {
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            return ($subscriptionTypeDao->subscriptionTypeExistsByTypeId($typeId, $journalId) && 
                    $subscriptionTypeDao->getSubscriptionTypeInstitutional($typeId) == 0) && 
                    $subscriptionTypeDao->getSubscriptionTypeDisablePublicDisplay($typeId) == 0;
        }));

        // Ensure that user does not already have a subscription for this journal
        if (!isset($subscriptionId)) {
            $this->addCheck(new FormValidatorCustom($this, 'userId', 'required', 'user.subscriptions.form.subscriptionExists', array(DAORegistry::getDAO('IndividualSubscriptionDAO'), 'subscriptionExistsByUserForJournal'), array($journalId), true));
        } else {
            // [WIZDAM FIX] Replaced create_function with Closure and use()
            $this->addCheck(new FormValidatorCustom($this, 'userId', 'required', 'user.subscriptions.form.subscriptionExists', function($userId) use ($journalId, $subscriptionId) {
                $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                $checkId = $subscriptionDao->getSubscriptionIdByUser($userId, $journalId);
                return ($checkId == 0 || $checkId == $subscriptionId) ? true : false;
            }));
        }

        // Form was POSTed
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function UserIndividualSubscriptionForm($request, $userId = null, $subscriptionId = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::UserIndividualSubscriptionForm(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request, $userId, $subscriptionId);
    }

    /**
     * Initialize form data from current subscription.
     */
    public function initData() {
        if (isset($this->subscription)) {
            $subscription = $this->subscription;

            $this->_data = array(
                'typeId' => $subscription->getTypeId(),
                'membership' => $subscription->getMembership()
            );
        }
    }

    /**
     * Display the form.
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        if (isset($this->subscription)) {
            $subscriptionId = $this->subscription->getId();
        } else {
            $subscriptionId = null;
        }

        $templateMgr->assign('subscriptionId', $subscriptionId);
        $templateMgr->assign('subscriptionTypes', $this->subscriptionTypes);
        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array('typeId', 'membership')); 

        // If subscription type requires it, membership is provided
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $needMembership = $subscriptionTypeDao->getSubscriptionTypeMembership($this->getData('typeId'));

        if ($needMembership) { 
            $this->addCheck(new FormValidator($this, 'membership', 'required', 'user.subscriptions.form.membershipRequired'));
        }
    }

    /**
     * Create/update individual subscription. 
     */
    public function execute($object = null) {
        $journal = $this->request->getJournal();
        $journalId = $journal->getId();
        $typeId = $this->getData('typeId');
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $nonExpiring = $subscriptionTypeDao->getSubscriptionTypeNonExpiring($typeId);
        $today = date('Y-m-d');
        $insert = false;

        if (!isset($this->subscription)) {
            import('classes.subscription.IndividualSubscription');
            $subscription = new IndividualSubscription();
            $subscription->setJournalId($journalId);
            $subscription->setUserId($this->userId);
            $subscription->setReferenceNumber(null);
            $subscription->setNotes(null);

            $insert = true;
        } else {
            $subscription = $this->subscription;
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($this->request);
        $paymentPlugin = $paymentManager->getPaymentPlugin();
        
        if ($paymentPlugin->getName() == 'ManualPayment') {
            $subscription->setStatus(SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT);
        } else {
            $subscription->setStatus(SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT);
        }

        $subscription->setTypeId($typeId);
        $subscription->setMembership($this->getData('membership') ? $this->getData('membership') : null);
        $subscription->setDateStart($nonExpiring ? null : $today);
        $subscription->setDateEnd($nonExpiring ? null : $today);

        $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        if ($insert) {
            $individualSubscriptionDao->insertSubscription($subscription);
        } else {
            $individualSubscriptionDao->updateSubscription($subscription);
        }

        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType($this->getData('typeId'));

        $queuedPayment = $paymentManager->createQueuedPayment($journalId, PAYMENT_TYPE_PURCHASE_SUBSCRIPTION, $this->userId, $subscription->getId(), $subscriptionType->getCost(), $subscriptionType->getCurrencyCodeAlpha());
        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }
}

?>
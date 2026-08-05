<?php
declare(strict_types=1);

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
 */

import('lib.pkp.classes.form.Form');

class UserIndividualSubscriptionForm extends Form {

    /** @var PKPRequest */
    protected $_request;

    /** @var int|null */
    protected $_userId;

    /** @var IndividualSubscription|null */
    protected $_subscription;

    /** @var array */
    protected $_subscriptionTypes;

    /**
     * Constructor.
     * @param PKPRequest $request
     * @param int|null $userId
     * @param int|null $subscriptionId
     */
    public function __construct($request, $userId = null, $subscriptionId = null) {
        parent::__construct('subscription/userIndividualSubscriptionForm.tpl');

        $this->_userId = $userId !== null ? (int) $userId : null;
        $this->_subscription = null;
        $this->_request = $request;

        $subId = $subscriptionId !== null ? (int) $subscriptionId : null;

        if ($subId !== null) {
            /** @var IndividualSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO'); 
            if ($subscriptionDao->subscriptionExists($subId)) {
                $this->_subscription = $subscriptionDao->getSubscription($subId);
            }
        }

        $journal = $this->_request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : 0;

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, false, false);
        
        $this->_subscriptionTypes = is_object($subscriptionTypes) && method_exists($subscriptionTypes, 'toArray') 
            ? $subscriptionTypes->toArray() 
            : (is_array($subscriptionTypes) ? $subscriptionTypes : []);

        $this->addCheck(new FormValidatorCustom($this, 'typeId', 'required', 'user.subscriptions.form.typeIdValid', function($typeId) use ($journalId) {
            /** @var SubscriptionTypeDAO $subscriptionTypeDao */
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            $typeIdInt = (int) $typeId;
            return $subscriptionTypeDao->subscriptionTypeExistsByTypeId($typeIdInt, $journalId) && 
                   $subscriptionTypeDao->getSubscriptionTypeInstitutional($typeIdInt) === 0 && 
                   $subscriptionTypeDao->getSubscriptionTypeDisablePublicDisplay($typeIdInt) === 0;
        }));

        if ($subId === null) {
            $this->addCheck(new FormValidatorCustom($this, 'userId', 'required', 'user.subscriptions.form.subscriptionExists', [DAORegistry::getDAO('IndividualSubscriptionDAO'), 'subscriptionExistsByUserForJournal'], [$journalId], true));
        } else {
            $this->addCheck(new FormValidatorCustom($this, 'userId', 'required', 'user.subscriptions.form.subscriptionExists', function($userId) use ($journalId, $subId) {
                /** @var IndividualSubscriptionDAO $subscriptionDao */
                $subscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                $checkId = $subscriptionDao->getSubscriptionIdByUser((int) $userId, $journalId);
                return $checkId === 0 || $checkId === $subId;
            }));
        }

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param PKPRequest $request
     * @param int|null $userId
     * @param int|null $subscriptionId
     */
    public function UserIndividualSubscriptionForm($request, $userId = null, $subscriptionId = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Initialize form data from current subscription.
     * @return void
     */
    public function initData() {
        if ($this->_subscription !== null) {
            $this->_data = [
                'typeId' => (int) $this->_subscription->getTypeId(),
                'membership' => (string) $this->_subscription->getMembership()
            ];
        }
    }

    /**
     * Display the form.
     * @param object|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $templateMgr = TemplateManager::getManager();
        $subscriptionId = $this->_subscription !== null ? $this->_subscription->getId() : null;

        $templateMgr->assign('subscriptionId', $subscriptionId);
        $templateMgr->assign('subscriptionTypes', $this->_subscriptionTypes);
        parent::display($request, $template);
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars(['typeId', 'membership']); 

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $needMembership = $subscriptionTypeDao->getSubscriptionTypeMembership((int) $this->getData('typeId'));

        if ($needMembership) { 
            $this->addCheck(new FormValidator($this, 'membership', 'required', 'user.subscriptions.form.membershipRequired'));
        }
    }

    /**
     * Create/update individual subscription.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        $journal = $this->_request->getJournal();
        if ($journal === null) {
            return;
        }
        
        $journalId = (int) $journal->getId();
        $typeId = (int) $this->getData('typeId');
        
        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $nonExpiring = (bool) $subscriptionTypeDao->getSubscriptionTypeNonExpiring($typeId);
        $today = date('Y-m-d');
        $insert = false;

        if ($this->_subscription === null) {
            import('classes.subscription.IndividualSubscription');
            $subscription = new IndividualSubscription();
            $subscription->setJournalId($journalId);
            $subscription->setUserId($this->_userId);
            $subscription->setReferenceNumber(null);
            $subscription->setNotes(null);
            $insert = true;
        } else {
            $subscription = $this->_subscription;
        }

        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($this->_request);
        $paymentPlugin = $paymentManager->getPaymentPlugin();
        
        if ($paymentPlugin !== null && $paymentPlugin->getName() === 'ManualPayment') {
            $subscription->setStatus(SUBSCRIPTION_STATUS_AWAITING_MANUAL_PAYMENT);
        } else {
            $subscription->setStatus(SUBSCRIPTION_STATUS_AWAITING_ONLINE_PAYMENT);
        }

        $subscription->setTypeId($typeId);
        $membership = $this->getData('membership');
        $subscription->setMembership($membership !== null && $membership !== '' ? (string) $membership : null);
        $subscription->setDateStart($nonExpiring ? null : $today);
        $subscription->setDateEnd($nonExpiring ? null : $today);

        /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
        $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
        if ($insert) {
            $individualSubscriptionDao->insertSubscription($subscription);
        } else {
            $individualSubscriptionDao->updateSubscription($subscription);
        }

        $subscriptionType = $subscriptionTypeDao->getSubscriptionType($typeId);
        if ($subscriptionType === null) {
            return;
        }

        $queuedPayment = $paymentManager->createQueuedPayment(
            $journalId, 
            PAYMENT_TYPE_PURCHASE_SUBSCRIPTION, 
            $this->_userId, 
            (int) $subscription->getId(), 
            (float) $subscriptionType->getCost(), 
            (string) $subscriptionType->getCurrencyCodeAlpha()
        );
        
        if (method_exists($queuedPayment, 'getInvoiceId') && $queuedPayment->getInvoiceId() > 0) {
            import('lib.wizdam.classes.security.SecurityHashService');
            $hashService = new SecurityHashService();
            $invoiceId = (int) $queuedPayment->getInvoiceId();
            $hash = $hashService->generateHash('invoice', $invoiceId);
            $this->_request->redirect(null, 'billing', 'invoice', ["{$hash}-{$invoiceId}"]);
            return;
        }

        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);
        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }

}
?>
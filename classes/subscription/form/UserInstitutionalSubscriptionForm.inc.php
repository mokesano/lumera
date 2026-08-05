<?php
declare(strict_types=1);

/**
 * @defgroup subscription_form
 */

/**
 * @file classes/subscription/form/UserInstitutionalSubscriptionForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserInstitutionalSubscriptionForm
 * @ingroup subscription_form
 *
 * @brief Form class for user purchase of institutional subscription.
 */

import('lib.pkp.classes.form.Form');

class UserInstitutionalSubscriptionForm extends Form {

    /** @var PKPRequest */
    protected $_request;

    /** @var int|null */
    protected $_userId;

    /** @var InstitutionalSubscription|null */
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
        parent::__construct('subscription/userInstitutionalSubscriptionForm.tpl');

        $this->_userId = $userId !== null ? (int) $userId : null;
        $this->_subscription = null;
        $this->_request = $request;

        $subId = $subscriptionId !== null ? (int) $subscriptionId : null;

        if ($subId !== null) {
            /** @var InstitutionalSubscriptionDAO $subscriptionDao */
            $subscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO'); 
            if ($subscriptionDao->subscriptionExists($subId)) {
                $this->_subscription = $subscriptionDao->getSubscription($subId);
            }
        }

        $journal = $this->_request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : 0;

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, true, false);
        
        $this->_subscriptionTypes = is_object($subscriptionTypes) && method_exists($subscriptionTypes, 'toArray') 
            ? $subscriptionTypes->toArray() 
            : (is_array($subscriptionTypes) ? $subscriptionTypes : []);

        $this->addCheck(new FormValidatorCustom($this, 'typeId', 'required', 'user.subscriptions.form.typeIdValid', function($typeId) use ($journalId) {
            /** @var SubscriptionTypeDAO $subscriptionTypeDao */
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            $typeIdInt = (int) $typeId;
            return $subscriptionTypeDao->subscriptionTypeExistsByTypeId($typeIdInt, $journalId) && 
                   $subscriptionTypeDao->getSubscriptionTypeInstitutional($typeIdInt) === 1 && 
                   $subscriptionTypeDao->getSubscriptionTypeDisablePublicDisplay($typeIdInt) === 0;
        }));

        $this->addCheck(new FormValidator($this, 'institutionName', 'required', 'user.subscriptions.form.institutionNameRequired'));

        $this->addCheck(new FormValidatorRegExp($this, 'domain', 'optional', 'user.subscriptions.form.domainValid', '/^' .
                '[A-Z0-9]+([\-_\.][A-Z0-9]+)*' .
                '\.' .
                '[A-Z]{2,4}' .
            '$/i'));
            
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param PKPRequest $request
     * @param int|null $userId
     * @param int|null $subscriptionId
     */
    public function UserInstitutionalSubscriptionForm($request, $userId = null, $subscriptionId = null) {
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
                'institutionName' => (string) $this->_subscription->getInstitutionName(),
                'institutionMailingAddress' => (string) $this->_subscription->getInstitutionMailingAddress(),
                'domain' => (string) $this->_subscription->getDomain(),
                'ipRanges' => $this->_subscription->getIPRanges()
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
        $this->readUserVars(['typeId', 'membership', 'institutionName', 'institutionMailingAddress', 'domain', 'ipRanges']); 

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $needMembership = $subscriptionTypeDao->getSubscriptionTypeMembership((int) $this->getData('typeId'));

        if ($needMembership) { 
            $this->addCheck(new FormValidator($this, 'membership', 'required', 'user.subscriptions.form.membershipRequired'));
        }

        $ipRanges = $this->getData('ipRanges');
        $ipRangeProvided = false;
        if (is_array($ipRanges)) {
            foreach ($ipRanges as $ipRange) {
                if ($ipRange !== '') {
                    $ipRangeProvided = true;
                    break;
                }
            }
        }

        $this->addCheck(new FormValidatorCustom($this, 'domain', 'required', 'user.subscriptions.form.domainIPRangeRequired', function($domain) use ($ipRangeProvided) {
            return $domain !== '' || $ipRangeProvided;
        }));

        if ($ipRangeProvided) {    
            import('classes.subscription.InstitutionalSubscription');
            $this->addCheck(new FormValidatorArrayCustom($this, 'ipRanges', 'required', 'user.subscriptions.form.ipRangeValid', function($ipRange, $regExp) {
                return PKPString::regexp_match($regExp, (string) $ipRange);
            },
                [
                    '/^' .
                    // IP4 address (with or w/o wildcards) or IP4 address range (with or w/o wildcards) or CIDR IP4 address
                    '((([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]|[' . SUBSCRIPTION_IP_RANGE_WILDCARD . '])([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]|[' . SUBSCRIPTION_IP_RANGE_WILDCARD . '])){3}((\s)*[' . SUBSCRIPTION_IP_RANGE_RANGE . '](\s)*([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]|[' . SUBSCRIPTION_IP_RANGE_WILDCARD . '])([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5]|[' . SUBSCRIPTION_IP_RANGE_WILDCARD . '])){3}){0,1})|(([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5])([.]([0-9]|[1-9][0-9]|[1][0-9]{2}|[2][0-4][0-9]|[2][5][0-5])){3}([\/](([3][0-2]{0,1})|([1-2]{0,1}[0-9])))))' .
                    '$/i'
                ],
                false,
                [],
                false        
            ));
        }
    }

    /**
     * Create/update institutional subscription.
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
            import('classes.subscription.InstitutionalSubscription');
            $subscription = new InstitutionalSubscription();
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
        $subscription->setInstitutionName((string) $this->getData('institutionName'));
        $subscription->setInstitutionMailingAddress((string) $this->getData('institutionMailingAddress'));
        $subscription->setDomain((string) $this->getData('domain'));
        
        $ipRanges = $this->getData('ipRanges');
        $subscription->setIPRanges(is_array($ipRanges) ? $ipRanges : []);

        /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
        $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
        if ($insert) {
            $institutionalSubscriptionDao->insertSubscription($subscription);
        } else {
            $institutionalSubscriptionDao->updateSubscription($subscription);
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
<?php
declare(strict_types=1);

/**
 * @defgroup subscription_form
 */

/**
 * @file classes/subscription/form/GiftIndividualSubscriptionForm.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GiftIndividualSubscriptionForm
 * @ingroup subscription_form
 *
 * @brief Form class for purchase of individual subscription gift.
 */

import('lib.pkp.classes.form.Form');

class GiftIndividualSubscriptionForm extends Form {

    /** @var PKPRequest */
    protected $_request;

    /** @var int|null */
    protected $_buyerUserId;

    /** @var array */
    protected $_subscriptionTypes;

    /**
     * Constructor.
     * @param PKPRequest $request
     * @param int|null $buyerUserId
     */
    public function __construct($request, $buyerUserId = null) {
        parent::__construct('subscription/giftIndividualSubscriptionForm.tpl');

        $this->_buyerUserId = $buyerUserId !== null ? (int) $buyerUserId : null;
        $this->_request = $request;
        
        $journal = $this->_request->getJournal();
        $journalId = $journal !== null ? (int) $journal->getId() : 0;

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, false, false);
        $this->_subscriptionTypes = is_object($subscriptionTypes) ? $subscriptionTypes->toArray() : [];

        $this->addCheck(new FormValidator($this, 'buyerFirstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'buyerLastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'buyerEmail', 'required', 'user.profile.form.emailRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'buyerEmail', 'required', 'user.register.form.emailsDoNotMatch', function($buyerEmail) {
            return $buyerEmail === $this->getData('confirmBuyerEmail');
        }));
        $this->addCheck(new FormValidator($this, 'recipientFirstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'recipientLastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'recipientEmail', 'required', 'user.profile.form.emailRequired'));
        $this->addCheck(new FormValidatorCustom($this, 'recipientEmail', 'required', 'user.register.form.emailsDoNotMatch', function($recipientEmail) {
            return $recipientEmail === $this->getData('confirmRecipientEmail');
        }));

        $this->addCheck(new FormValidator($this, 'giftNoteTitle', 'required', 'gifts.noteTitleRequired'));
        $this->addCheck(new FormValidator($this, 'giftNote', 'required', 'gifts.noteRequired'));

        $this->addCheck(new FormValidatorCustom($this, 'typeId', 'required', 'user.subscriptions.form.typeIdValid', function($typeId) use ($journalId) {
            /** @var SubscriptionTypeDAO $subscriptionTypeDao */
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            $typeIdInt = (int) $typeId;
            return $subscriptionTypeDao->subscriptionTypeExistsByTypeId($typeIdInt, $journalId) && 
                   $subscriptionTypeDao->getSubscriptionTypeInstitutional($typeIdInt) === 0 && 
                   $subscriptionTypeDao->getSubscriptionTypeDisablePublicDisplay($typeIdInt) === 0;
        }));

        $this->addCheck(
            new FormValidatorCustom($this, 'giftLocale', 'required', 'gifts.localeRequired',
                function($giftLocale) use ($journal) {
                    return in_array((string) $giftLocale, array_keys($journal->getSupportedLocaleNames()), true);
                }
            )
        );

        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility.
     * @param PKPRequest $request
     * @param int|null $buyerUserId
     */
    public function GiftIndividualSubscriptionForm($request, $buyerUserId = null) {
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
     * Display the form.
     * @param object|null $request
     * @param string|null $template
     * @return void
     */
    public function display($request = null, $template = null) {
        $journal = $this->_request->getJournal();
        $templateMgr = TemplateManager::getManager();
        
        if ($journal !== null) {
            $templateMgr->assign('supportedLocales', $journal->getSupportedLocaleNames());
        }
        $templateMgr->assign('subscriptionTypes', $this->_subscriptionTypes);
        
        parent::display($request, $template);
    }

    /**
     * Assign form data to user-submitted data.
     * @return void
     */
    public function readInputData() {
        $this->readUserVars([
            'buyerFirstName',
            'buyerMiddleName',
            'buyerLastName',
            'buyerEmail',
            'confirmBuyerEmail',
            'recipientFirstName',
            'recipientMiddleName',
            'recipientLastName',
            'recipientEmail',
            'confirmRecipientEmail',
            'giftLocale',
            'giftNoteTitle',
            'giftNote',
            'typeId'
        ]);
    }

    /**
     * Queue payment and save gift details.
     * @param mixed $object
     * @return void
     */
    public function execute($object = null) {
        $journal = $this->_request->getJournal();
        if ($journal === null) {
            return;
        }
        $journalId = (int) $journal->getId();

        import('classes.gift.Gift');
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($this->_request);
        $paymentPlugin = $paymentManager->getPaymentPlugin();

        $gift = new Gift();
        if ($paymentPlugin !== null && $paymentPlugin->getName() === 'ManualPayment') {
            $gift->setStatus(GIFT_STATUS_AWAITING_MANUAL_PAYMENT);
        } else {
            $gift->setStatus(GIFT_STATUS_AWAITING_ONLINE_PAYMENT);
        }

        $gift->setAssocType(ASSOC_TYPE_JOURNAL);
        $gift->setAssocId($journalId);
        $gift->setGiftType(GIFT_TYPE_SUBSCRIPTION);
        $gift->setGiftAssocId((int) $this->getData('typeId'));
        $gift->setBuyerFirstName((string) $this->getData('buyerFirstName'));
        $gift->setBuyerMiddleName((string) $this->getData('buyerMiddleName'));
        $gift->setBuyerLastName((string) $this->getData('buyerLastName'));
        $gift->setBuyerEmail((string) $this->getData('buyerEmail'));
        $gift->setBuyerUserId($this->_buyerUserId);
        $gift->setRecipientFirstName((string) $this->getData('recipientFirstName'));
        $gift->setRecipientMiddleName((string) $this->getData('recipientMiddleName'));
        $gift->setRecipientLastName((string) $this->getData('recipientLastName'));
        $gift->setRecipientEmail((string) $this->getData('recipientEmail'));
        $gift->setRecipientUserId(null);
        $gift->setLocale((string) $this->getData('giftLocale'));
        $gift->setGiftNoteTitle((string) $this->getData('giftNoteTitle'));
        $gift->setGiftNote((string) $this->getData('giftNote'));

        /** @var GiftDAO $giftDao */
        $giftDao = DAORegistry::getDAO('GiftDAO');
        $giftId = $giftDao->insertObject($gift);

        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $this->getData('typeId'));

        if ($subscriptionType === null) {
            return;
        }

        $queuedPayment = $paymentManager->createQueuedPayment(
            $journalId, 
            PAYMENT_TYPE_GIFT, 
            $this->_buyerUserId, 
            $giftId, 
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
<?php

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
 * * MODERNIZED FOR WIZDAM FORK
 */

import('lib.pkp.classes.form.Form');

class GiftIndividualSubscriptionForm extends Form {
    /** @var $request PKPRequest */
    public $request;

    /** @var userId int the buyer associated with the gift purchase */
    public $buyerUserId;

    /** @var subscriptionTypes Array subscription types */
    public $subscriptionTypes;

    /**
     * Constructor
     * @param buyerUserId int
     */
    public function __construct($request, $buyerUserId = null) {
        parent::__construct('subscription/giftIndividualSubscriptionForm.tpl');

        $this->buyerUserId = isset($buyerUserId) ? (int) $buyerUserId : null;
        $this->request = $request;
        $journal = $this->request->getJournal();
        $journalId = $journal->getId();

        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionTypes = $subscriptionTypeDao->getSubscriptionTypesByInstitutional($journalId, false, false);
        $this->subscriptionTypes = $subscriptionTypes->toArray();

        // Require buyer and recipient names and emails
        $this->addCheck(new FormValidator($this, 'buyerFirstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'buyerLastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'buyerEmail', 'required', 'user.profile.form.emailRequired'));
        
        // [WIZDAM FIX] Ganti create_function dengan Closure
        $this->addCheck(new FormValidatorCustom($this, 'buyerEmail', 'required', 'user.register.form.emailsDoNotMatch', function($buyerEmail) {
            return $buyerEmail == $this->getData('confirmBuyerEmail');
        }));

        $this->addCheck(new FormValidator($this, 'recipientFirstName', 'required', 'user.profile.form.firstNameRequired'));
        $this->addCheck(new FormValidator($this, 'recipientLastName', 'required', 'user.profile.form.lastNameRequired'));
        $this->addCheck(new FormValidatorEmail($this, 'recipientEmail', 'required', 'user.profile.form.emailRequired'));
        
        // [WIZDAM FIX] Ganti create_function dengan Closure
        $this->addCheck(new FormValidatorCustom($this, 'recipientEmail', 'required', 'user.register.form.emailsDoNotMatch', function($recipientEmail) {
            return $recipientEmail == $this->getData('confirmRecipientEmail');
        }));

        // Require gift note title and note from buyer
        $this->addCheck(new FormValidator($this, 'giftNoteTitle', 'required', 'gifts.noteTitleRequired'));
        $this->addCheck(new FormValidator($this, 'giftNote', 'required', 'gifts.noteRequired'));

        // Ensure subscription type is valid
        // [WIZDAM FIX] Ganti create_function dengan Closure
        $this->addCheck(new FormValidatorCustom($this, 'typeId', 'required', 'user.subscriptions.form.typeIdValid', function($typeId) use ($journalId) {
            $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
            return ($subscriptionTypeDao->subscriptionTypeExistsByTypeId($typeId, $journalId) && 
                    $subscriptionTypeDao->getSubscriptionTypeInstitutional($typeId) == 0) && 
                    $subscriptionTypeDao->getSubscriptionTypeDisablePublicDisplay($typeId) == 0;
        }));

        // Ensure a locale is provided and valid
        $this->addCheck(
            new FormValidator(
                $this,
                'giftLocale',
                'required',
                'gifts.localeRequired'
            ),
            // [WIZDAM FIX] Ganti create_function dengan Closure
            function($giftLocale) use ($journal) {
                return in_array($giftLocale, array_keys($journal->getSupportedLocaleNames()));
            }
        );

        // Form was POSTed
        $this->addCheck(new FormValidatorPost($this));
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GiftIndividualSubscriptionForm($request, $buyerUserId = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::GiftIndividualSubscriptionForm(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($request, $buyerUserId);
    }

    /**
     * Display the form.
     */
    public function display($request = NULL, $template = NULL) {
        $journal = $this->request->getJournal();
        $templateMgr = TemplateManager::getManager();
        $templateMgr->assign('supportedLocales', $journal->getSupportedLocaleNames());
        $templateMgr->assign('subscriptionTypes', $this->subscriptionTypes);
        parent::display();
    }

    /**
     * Assign form data to user-submitted data.
     */
    public function readInputData() {
        $this->readUserVars(array(
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
        ));
    }

    /**
     * Queue payment and save gift details.
     */
    public function execute($object = NULL) {
        $journal = $this->request->getJournal();
        $journalId = $journal->getId();

        // Create new gift and save details
        import('classes.gift.Gift');
        import('classes.payment.ojs.OJSPaymentManager');
        $paymentManager = new OJSPaymentManager($this->request);
        $paymentPlugin = $paymentManager->getPaymentPlugin();

        $gift = new Gift();
        if ($paymentPlugin->getName() == 'ManualPayment') {
            $gift->setStatus(GIFT_STATUS_AWAITING_MANUAL_PAYMENT);
        } else {
            $gift->setStatus(GIFT_STATUS_AWAITING_ONLINE_PAYMENT);
        }

        $gift->setAssocType(ASSOC_TYPE_JOURNAL);
        $gift->setAssocId($journalId);
        $gift->setGiftType(GIFT_TYPE_SUBSCRIPTION);
        $gift->setGiftAssocId($this->getData('typeId'));
        $gift->setBuyerFirstName($this->getData('buyerFirstName'));
        $gift->setBuyerMiddleName($this->getData('buyerMiddleName'));
        $gift->setBuyerLastName($this->getData('buyerLastName'));
        $gift->setBuyerEmail($this->getData('buyerEmail'));
        $gift->setBuyerUserId($this->buyerUserId ? $this->buyerUserId : null);
        $gift->setRecipientFirstName($this->getData('recipientFirstName'));
        $gift->setRecipientMiddleName($this->getData('recipientMiddleName'));
        $gift->setRecipientLastName($this->getData('recipientLastName'));
        $gift->setRecipientEmail($this->getData('recipientEmail'));
        $gift->setRecipientUserId(null);
        $gift->setLocale($this->getData('giftLocale'));
        $gift->setGiftNoteTitle($this->getData('giftNoteTitle'));
        $gift->setGiftNote($this->getData('giftNote'));

        $giftDao = DAORegistry::getDAO('GiftDAO');
        $giftId = $giftDao->insertObject($gift);

        // Create new queued payment
        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
        $subscriptionType = $subscriptionTypeDao->getSubscriptionType($this->getData('typeId'));

        $queuedPayment = $paymentManager->createQueuedPayment($journalId, PAYMENT_TYPE_GIFT, null, $giftId, $subscriptionType->getCost(), $subscriptionType->getCurrencyCodeAlpha());
        $queuedPaymentId = $paymentManager->queuePayment($queuedPayment);

        $paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
    }
}

?>
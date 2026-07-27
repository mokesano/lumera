<?php
declare(strict_types=1);

/**
 * @file classes/payment/ojs/OJSPaymentManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OJSPaymentManager
 * @ingroup payment
 * @see OJSQueuedPayment
 *
 * @brief Provides payment management functions.
 */

import('classes.payment.ojs.OJSQueuedPayment');
import('lib.pkp.classes.payment.PaymentManager');

define('PAYMENT_TYPE_MEMBERSHIP',         0x000000001);
define('PAYMENT_TYPE_RENEW_SUBSCRIPTION', 0x000000002);
define('PAYMENT_TYPE_PURCHASE_ARTICLE',   0x000000003);
define('PAYMENT_TYPE_DONATION',           0x000000004);
define('PAYMENT_TYPE_SUBMISSION',         0x000000005);
define('PAYMENT_TYPE_FASTTRACK',          0x000000006);
define('PAYMENT_TYPE_PUBLICATION',        0x000000007);
define('PAYMENT_TYPE_PURCHASE_SUBSCRIPTION', 0x000000008);
define('PAYMENT_TYPE_PURCHASE_ISSUE',     0x000000009);
define('PAYMENT_TYPE_GIFT',               0x000000010);

class OJSPaymentManager extends PaymentManager {
    
    /**
     * Constructor
     * @param PKPRequest $request
     */
    public function __construct($request) {
        parent::__construct($request);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param PKPRequest $request
     */
    public function OJSPaymentManager($request) {
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
     * Determine whether the payment system is configured.
     * @return bool true iff configured
     */
    public function isConfigured() {
        $journal = $this->request->getJournal();
        return $journal && parent::isConfigured() && (bool) $journal->getSetting('journalPaymentsEnabled');
    }

    /**
     * Create a queued payment.
     * @param int $journalId
     * @param int $type
     * @param int $userId
     * @param int $assocId
     * @param numeric $amount
     * @param string $currencyCode
     * @return OJSQueuedPayment
     */
    public function createQueuedPayment($journalId, $type, $userId, $assocId, $amount, $currencyCode = null) {
        /** @var JournalSettingsDAO $journalSettingsDao */
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        if (is_null($currencyCode)) $currencyCode = $journalSettingsDao->getSetting($journalId, 'currency');

        $invoiceId = 0;

        if (in_array($type, array(PAYMENT_TYPE_SUBMISSION, PAYMENT_TYPE_FASTTRACK, PAYMENT_TYPE_PUBLICATION))) {
            $articleId = $assocId;
            $journalDao = DAORegistry::getDAO('JournalDAO'); /** @var JournalDAO $journalDao */
            $journal = $journalDao->getById($journalId);

            if ($journal) {
                $settingTaxRate = (float) $journal->getSetting('paymentTax');
                $taxRate = $settingTaxRate > 0 ? ($settingTaxRate / 100) : 0.00;
                $isTaxInclusive = (bool) $journal->getSetting('paymentTaxInclusive');
                $discount = (float) $journal->getSetting('paymentDiscount');

                $subtotal = $amount;
                $taxableAmount = $subtotal - $discount;
                if ($taxableAmount < 0) $taxableAmount = 0;

                if ($isTaxInclusive) {
                    $finalAmount = $taxableAmount;
                } else {
                    $taxAmount = $taxableAmount * $taxRate;
                    $finalAmount = $taxableAmount + $taxAmount;
                }
                $amount = $finalAmount;

                $issnRaw = $journal->getSetting('onlineIssn') ? $journal->getSetting('onlineIssn') : ($journal->getSetting('printIssn') ? $journal->getSetting('printIssn') : '00000000');
                $issnClean = str_pad(str_replace('-', '', $issnRaw), 8, '0', STR_PAD_RIGHT);
                $invoiceNumber = substr($issnClean, 0, 4) . '-' . $articleId . '-' . substr($issnClean, -4);
                $invoiceCode = 'Manuscript#' . str_pad((string)$articleId, 7, '0', STR_PAD_LEFT);

                $feeType = 'PUBLICATION';
                if ($type == PAYMENT_TYPE_SUBMISSION) $feeType = 'SUBMISSION';
                if ($type == PAYMENT_TYPE_FASTTRACK) $feeType = 'FAST_TRACK';

                // [FIX] Sebelumnya: cek invoice yang sudah ada lalu BUANG hasilnya.
                // Sekarang: TANGKAP invoice_id-nya, entah baru dibuat atau sudah ada.
                /** @var InvoiceDAO $invoiceDao */
                $invoiceDao = DAORegistry::getDAO('InvoiceDAO');
                $invoiceId = $invoiceDao->findOrCreateUnpaidInvoiceId(
                    (int) $journalId, (int) $userId, (int) $articleId, $feeType,
                    (float) $amount, (string) $currencyCode, $invoiceNumber, $invoiceCode
                );
            }
        }

        $payment = new OJSQueuedPayment($amount, $currencyCode, $userId, $assocId);
        $payment->setJournalId($journalId);
        $payment->setType($type);
        if ($invoiceId > 0) {
            $payment->setInvoiceId($invoiceId); // <- INI kuncinya: menautkan queued payment ke invoice ASLI
        }

        switch ($type) {
            case PAYMENT_TYPE_PURCHASE_ARTICLE:
                $payment->setRequestUrl($this->request->url(null, 'article', 'view', $assocId));
                break;
            case PAYMENT_TYPE_PURCHASE_ISSUE:
                $payment->setRequestUrl($this->request->url(null, 'issue', 'view', $assocId));
                break;
            case PAYMENT_TYPE_MEMBERSHIP:
                $payment->setRequestUrl($this->request->url(null, 'user'));
                break;
            case PAYMENT_TYPE_PURCHASE_SUBSCRIPTION:
            case PAYMENT_TYPE_RENEW_SUBSCRIPTION:
                $payment->setRequestUrl($this->request->url(null, 'user', 'subscriptions'));
                break;
            case PAYMENT_TYPE_DONATION:
                $payment->setRequestUrl($this->request->url(null, 'donations', 'thankYou'));
                break;
            case PAYMENT_TYPE_FASTTRACK:
            case PAYMENT_TYPE_PUBLICATION:
            case PAYMENT_TYPE_SUBMISSION:
                /** @var AuthorSubmissionDAO $authorSubmissionDao */
                $authorSubmissionDao = DAORegistry::getDAO('AuthorSubmissionDAO');
                $authorSubmission = $authorSubmissionDao->getAuthorSubmission($assocId);
                if ($authorSubmission->getSubmissionProgress()!=0) {
                    $payment->setRequestUrl($this->request->url(null, 'author', 'submit', $authorSubmission->getSubmissionProgress(), ['articleId' => $assocId]));
                } else {
                    $payment->setRequestUrl($this->request->url(null, 'author'));
                }
                break;
            case PAYMENT_TYPE_GIFT:
                $payment->setRequestUrl($this->request->url(null, 'gifts', 'thankYou'));
                break;
            default:
                assert(false);
                break;
        }

        return $payment;
    }

    /**
     * Create a completed payment from a queued payment.
     * @param OJSQueuedPayment $queuedPayment
     * @param string $payMethod
     * @return OJSCompletedPayment
     */
    public function createCompletedPayment($queuedPayment, $payMethod) {
        import('classes.payment.ojs.OJSCompletedPayment');
        $payment = new OJSCompletedPayment();
        $payment->setJournalId((int) $queuedPayment->getJournalId());
        $payment->setType((int) $queuedPayment->getType());
        $payment->setAmount((float) $queuedPayment->getAmount());
        $payment->setCurrencyCode((string) $queuedPayment->getCurrencyCode());
        $payment->setUserId((int) $queuedPayment->getUserId());
        $payment->setAssocId((int) $queuedPayment->getAssocId());
        $payment->setPayMethodPluginName((string) $payMethod);

        return $payment;
    }

    /**
     * Determine whether donations are enabled.
     * @return bool
     */
    public function donationEnabled() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('donationFeeEnabled');
    }

    /**
     * Determine whether submission fees are enabled.
     * @return bool
     */
    public function submissionEnabled() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('submissionFeeEnabled') && (float) $journal->getSetting('submissionFee') > 0;
    }

    /**
     * Determine whether fast track fees are enabled.
     * @return bool
     */
    public function fastTrackEnabled() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('fastTrackFeeEnabled') && (float) $journal->getSetting('fastTrackFee') > 0;
    }

    /**
     * Determine whether publication fees are enabled.
     * @return bool
     */
    public function publicationEnabled() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('publicationFeeEnabled') && (float) $journal->getSetting('publicationFee') > 0;
    }

    /**
     * Determine whether membership fees are enabled.
     * @return bool
     */
    public function membershipEnabled() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('membershipFeeEnabled') && (float) $journal->getSetting('membershipFee') > 0;
    }

    /**
     * Determine whether article purchase fees are enabled.
     * @return bool
     */
    public function purchaseArticleEnabled() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('purchaseArticleFeeEnabled') && (float) $journal->getSetting('purchaseArticleFee') > 0;
    }

    /**
     * Determine whether issue purchase fees are enabled.
     * @return bool
     */
    public function purchaseIssueEnabled() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('purchaseIssueFeeEnabled') && (float) $journal->getSetting('purchaseIssueFee') > 0;
    }

    /**
     * Determine whether PDF-only article purchase fees are enabled.
     * @return bool
     */
    public function onlyPdfEnabled() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('restrictOnlyPdf');
    }

    /**
     * Determine whether subscription fees are enabled.
     * @return bool
     */
    public function acceptSubscriptionPayments() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('acceptSubscriptionPayments');
    }

    /**
     * Determine whether gift payments are enabled.
     * @return bool
     */
    public function acceptGiftPayments() {
        return $this->acceptGiftSubscriptionPayments();
    }

    /**
     * Determine whether gift subscription payments are enabled.
     * @return bool
     */
    public function acceptGiftSubscriptionPayments() {
        $journal = $this->request->getJournal();
        return $journal && $this->isConfigured() && (bool) $journal->getSetting('acceptGiftSubscriptionPayments');
    }

    /**
     * Get the payment plugin.
     * @return PaymethodPlugin|null
     */
    public function getPaymentPlugin() {
        $journal = $this->request->getJournal();
        if (!$journal) return null;
        
        $paymentMethodPluginName = $journal->getSetting('paymentMethodPluginName');
        $paymentMethodPlugin = null;
        if (!empty($paymentMethodPluginName)) {
            $plugins = PluginRegistry::loadCategory('paymethod');
            if (is_array($plugins) && isset($plugins[$paymentMethodPluginName])) {
                $paymentMethodPlugin = $plugins[$paymentMethodPluginName];
            }
        }
        return $paymentMethodPlugin;
    }

    /**
     * Fulfill a queued payment.
     * @param OJSQueuedPayment $queuedPayment
     * @param string|null $payMethodPluginName 
     * @return bool
     */
    public function fulfillQueuedPayment($queuedPayment, $payMethodPluginName = null) {
        /** @var OJSQueuedPayment $queuedPayment */
        $returner = false;
        
        if ($queuedPayment) {
            switch ($queuedPayment->getType()) {
                case PAYMENT_TYPE_MEMBERSHIP:
                    $userDao = DAORegistry::getDAO('UserDAO'); /** @var UserDAO $userDao */
                    $user = $userDao->getUser((int) $queuedPayment->getUserId());
                    if ($user) {
                        $userDao->renewMembership($user);
                    }
                    $returner = true;
                    break;
                    
                case PAYMENT_TYPE_PURCHASE_SUBSCRIPTION:
                    $subscriptionId = (int) $queuedPayment->getAssocId();
                    /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
                    $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
                    /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
                    $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                    
                    if ($institutionalSubscriptionDao->subscriptionExists($subscriptionId)) {
                        $subscription = $institutionalSubscriptionDao->getSubscription($subscriptionId);
                        $institutional = true;
                    } else {
                        $subscription = $individualSubscriptionDao->getSubscription($subscriptionId);
                        $institutional = false;
                    }
                    
                    if (!$subscription || (int) $subscription->getUserId() !== (int) $queuedPayment->getUserId() || (int) $subscription->getJournalId() !== (int) $queuedPayment->getJournalId()) {
                        error_log(print_r($subscription, true));
                        return false;
                    }
                    
                    if ($institutional) {
                        import('classes.subscription.InstitutionalSubscription');
                        $subscription->setStatus(SUBSCRIPTION_STATUS_NEEDS_APPROVAL);
                        if ($subscription->isNonExpiring()) {
                            $institutionalSubscriptionDao->updateSubscription($subscription);
                        } else {
                            $institutionalSubscriptionDao->renewSubscription($subscription);
                        }

                        /** @var JournalSettingsDAO $journalSettingsDao */
                        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                        if ($journalSettingsDao->getSetting((int) $subscription->getJournalId(), 'enableSubscriptionOnlinePaymentNotificationPurchaseInstitutional')) {
                            import('classes.subscription.SubscriptionAction');
                            $subscriptionAction = new SubscriptionAction();
                            $subscriptionAction->sendOnlinePaymentNotificationEmail($subscription, 'SUBSCRIPTION_PURCHASE_INSTL');
                        }
                    } else {
                        import('classes.subscription.IndividualSubscription');
                        $subscription->setStatus(SUBSCRIPTION_STATUS_ACTIVE);
                        if ($subscription->isNonExpiring()) {
                            $individualSubscriptionDao->updateSubscription($subscription);
                        } else {
                            $individualSubscriptionDao->renewSubscription($subscription);
                        }
                        
                        /** @var JournalSettingsDAO $journalSettingsDao */
                        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                        if ($journalSettingsDao->getSetting((int) $subscription->getJournalId(), 'enableSubscriptionOnlinePaymentNotificationPurchaseIndividual')) {
                            import('classes.subscription.SubscriptionAction');
                            $subscriptionAction = new SubscriptionAction();
                            $subscriptionAction->sendOnlinePaymentNotificationEmail($subscription, 'SUBSCRIPTION_PURCHASE_INDL');
                        }
                    }
                    $returner = true;
                    break;
                    
                case PAYMENT_TYPE_RENEW_SUBSCRIPTION:
                    $subscriptionId = (int) $queuedPayment->getAssocId();
                    /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
                    $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
                    /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
                    $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                    
                    if ($institutionalSubscriptionDao->subscriptionExists($subscriptionId)) {
                        $subscription = $institutionalSubscriptionDao->getSubscription($subscriptionId);
                        $institutional = true;
                    } else {
                        $subscription = $individualSubscriptionDao->getSubscription($subscriptionId);
                        $institutional = false;
                    }
                    
                    if (!$subscription || (int) $subscription->getUserId() !== (int) $queuedPayment->getUserId() || (int) $subscription->getJournalId() !== (int) $queuedPayment->getJournalId()) {
                        error_log(print_r($subscription, true));
                        return false;
                    }
                    
                    if ($institutional) {
                        $institutionalSubscriptionDao->renewSubscription($subscription);
                        /** @var JournalSettingsDAO $journalSettingsDao */
                        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                        if ($journalSettingsDao->getSetting((int) $subscription->getJournalId(), 'enableSubscriptionOnlinePaymentNotificationRenewInstitutional')) {
                            import('classes.subscription.SubscriptionAction');
                            $subscriptionAction = new SubscriptionAction();
                            $subscriptionAction->sendOnlinePaymentNotificationEmail($subscription, 'SUBSCRIPTION_RENEW_INSTL');
                        }
                    } else {
                        $individualSubscriptionDao->renewSubscription($subscription);
                        /** @var JournalSettingsDAO $journalSettingsDao */
                        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
                        if ($journalSettingsDao->getSetting((int) $subscription->getJournalId(), 'enableSubscriptionOnlinePaymentNotificationRenewIndividual')) {
                            import('classes.subscription.SubscriptionAction');
                            $subscriptionAction = new SubscriptionAction();
                            $subscriptionAction->sendOnlinePaymentNotificationEmail($subscription, 'SUBSCRIPTION_RENEW_INDL');
                        }
                    }
                    $returner = true;
                    break;
                    
                case PAYMENT_TYPE_FASTTRACK:
                    $articleDao = DAORegistry::getDAO('ArticleDAO'); /** @var ArticleDAO $articleDao */
                    $article = $articleDao->getArticle((int) $queuedPayment->getAssocId(), (int) $queuedPayment->getJournalId());
                    if ($article) {
                        $article->setFastTracked(true);
                        $articleDao->updateArticle($article);
                    }
                    $returner = true;
                    break;
                    
                case PAYMENT_TYPE_GIFT:
                    $giftId = (int) $queuedPayment->getAssocId();
                    $giftDao = DAORegistry::getDAO('GiftDAO'); /** @var GiftDAO $giftDao */
                    $gift = $giftDao->getGift($giftId);
                    if (!$gift) return false;

                    $journalDao = DAORegistry::getDAO('JournalDAO'); /** @var JournalDAO $journalDao */
                    $journalId = (int) $gift->getAssocId();
                    $journal = $journalDao->getById($journalId);
                    if (!$journal) return false;

                    /** @var UserDAO $userDao */
                    $userDao = DAORegistry::getDAO('UserDAO');
                    /** @var RoleDAO $roleDao */
                    $roleDao = DAORegistry::getDAO('RoleDAO');
                    $recipientFirstName = (string) $gift->getRecipientFirstName();
                    $recipientEmail = (string) $gift->getRecipientEmail();

                    $newUserAccount = false;
                    $password = ''; // [WIZDAM FIX] Initialize to prevent undefined variable warning

                    if ($userDao->userExistsByEmail($recipientEmail)) {
                        $user = $userDao->getUserByEmail($recipientEmail);
                        if ($user) {
                            $userId = (int) $user->getId();
                            if (!$roleDao->userHasRole($journalId, $userId, ROLE_ID_READER)) {
                                $role = new Role();
                                $role->setJournalId($journalId);
                                $role->setUserId($userId);
                                $role->setRoleId(ROLE_ID_READER);
                                $roleDao->insertRole($role);
                            }
                        }
                    } else {
                        $recipientLastName = (string) $gift->getRecipientLastName();
                        $username = Validation::suggestUsername($recipientFirstName, $recipientLastName);
                        $password = Validation::generatePassword();

                        $user = new User();
                        $user->setUsername($username);
                        $user->setPassword(Validation::encryptCredentials($username, $password));
                        $user->setFirstName($recipientFirstName);
                        $user->setMiddleName((string) $gift->getRecipientMiddleName());
                        $user->setLastName($recipientLastName);
                        $user->setEmail($recipientEmail);
                        $user->setDateRegistered(Core::getCurrentDate());

                        $userDao->insertUser($user);
                        $userId = (int) $user->getId();

                        $role = new Role();
                        $role->setJournalId($journalId);
                        $role->setUserId($userId);
                        $role->setRoleId(ROLE_ID_READER);
                        $roleDao->insertRole($role);

                        $newUserAccount = true;
                    }

                    import('classes.gift.Gift');
                    $gift->setStatus(GIFT_STATUS_NOT_REDEEMED);
                    $gift->setRecipientUserId($userId ?? 0);
                    $giftDao->updateObject($gift);

                    $giftNoteTitle = (string) $gift->getGiftNoteTitle();
                    $buyerFullName = (string) $gift->getBuyerFullName();
                    $giftNote = (string) $gift->getGiftNote();
                    $giftLocale = (string) $gift->getLocale();

                    AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON, $giftLocale);
                    $giftDetails = (string) $gift->getGiftName($giftLocale);
                    $giftJournalName = (string) $journal->getTitle($giftLocale);
                    $giftContactSignature = (string) $journal->getSetting('contactName');

                    import('classes.mail.MailTemplate');
                    $mail = new MailTemplate('GIFT_AVAILABLE', $giftLocale);
                    $mail->setFrom((string) $journal->getSetting('contactEmail'), $giftContactSignature);
                    $mail->assignParams([
                        'giftJournalName' => $giftJournalName,
                        'giftNoteTitle' => $giftNoteTitle,
                        'recipientFirstName' => $recipientFirstName,
                        'buyerFullName' => $buyerFullName,
                        'giftDetails' => $giftDetails,
                        'giftNote' => $giftNote,
                        'giftContactSignature' => $giftContactSignature
                    ]);
                    $mail->addRecipient($recipientEmail, $user ? $user->getFullName() : $recipientFirstName);
                    $mail->addCc((string) $gift->getBuyerEmail(), $buyerFullName);
                    $mail->send();

                    $params = [
                        'giftJournalName' => $giftJournalName,
                        'recipientFirstName' => $recipientFirstName,
                        'buyerFullName' => $buyerFullName,
                        'giftDetails' => $giftDetails,
                        'giftUrl' => $this->request->url($journal->getPath(), 'user', 'gifts'),
                        'username' => $user ? $user->getUsername() : '',
                        'giftContactSignature' => $giftContactSignature
                    ];

                    if ($newUserAccount) {
                        $mail = new MailTemplate('GIFT_USER_REGISTER', $giftLocale);
                        $params['password'] = $password;
                    } else {
                        $mail = new MailTemplate('GIFT_USER_LOGIN', $giftLocale);
                    }

                    $mail->setFrom((string) $journal->getSetting('contactEmail'), $giftContactSignature);
                    $mail->assignParams($params);
                    $mail->addRecipient($recipientEmail, $user ? $user->getFullName() : $recipientFirstName);
                    $mail->send();

                    $returner = true;
                    break;
                    
                case PAYMENT_TYPE_PURCHASE_ARTICLE:
                case PAYMENT_TYPE_PURCHASE_ISSUE:
                case PAYMENT_TYPE_DONATION:
                case PAYMENT_TYPE_SUBMISSION:
                case PAYMENT_TYPE_PUBLICATION:
                    $returner = true;
                    break;
                    
                default:
                    throw new \BadMethodCallException('Invalid payment type');
            }
        }
        
        // [FIX] Kalau payment ini tertaut ke invoice WIZDAM, tandai invoice ASLI itu sebagai lunas -- 
        // JANGAN buat entri terpisah di completed_payments lagi.
        if (method_exists($queuedPayment, 'getInvoiceId') && $queuedPayment->getInvoiceId() > 0) {
            import('lib.wizdam.classes.services.InvoiceService');
            $invoiceService = new InvoiceService();
            $invoiceService->markAsPaid($queuedPayment->getInvoiceId(), (string) $payMethodPluginName);
        } else {
            /** @var OJSCompletedPaymentDAO $completedPaymentDao */
            $completedPaymentDao = DAORegistry::getDAO('OJSCompletedPaymentDAO');
            $completedPayment = $this->createCompletedPayment($queuedPayment, $payMethodPluginName);
            $completedPaymentDao->insertCompletedPayment($completedPayment);
        }

        /** @var QueuedPaymentDAO $queuedPaymentDao */
        $queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
        $queuedPaymentDao->deleteQueuedPayment((int) $queuedPayment->getId());

        return $returner;
    }

}
?>
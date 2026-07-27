<?php
declare(strict_types=1);

/**
 * @file classes/payment/ojs/OJSQueuedPayment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OJSQueuedPayment
 * @ingroup payment
 *
 * @brief Queued payment data structure for Lumera App
 */

import('lib.pkp.classes.payment.QueuedPayment');

class OJSQueuedPayment extends QueuedPayment {
    
    /** @var QueuedPaymentDAO */
    private QueuedPaymentDAO $queuedPaymentDao;

    /** @var int journal ID this payment applies to */
    public $journalId;

    /** @var int PAYMENT_TYPE_... */
    public $type;

    /** @var string URL associated with this payment */
    public $requestUrl;

    // [LUMERA] ARSITEKTUR ---
    /** @var array Checkout Payload (Cart Items, Promo, Billing) */
    public $checkoutPayload = [];

    /** @var int ID invoice WIZDAM yang terkait payment ini, 0 jika tidak ada */
    public $invoiceId = 0;

    /**
     * Set Checkout Payload
     * @param array $payload
     */
    public function setCheckoutPayload(array $payload): void {
        $this->checkoutPayload = $payload;
    }

    /**
     * Get Checkout Payload
     * @return array
     */
    public function getCheckoutPayload(): array {
        return $this->checkoutPayload;
    }

    /**
     * Set invoice ID
     */
    public function setInvoiceId(int $invoiceId): void {
        $this->invoiceId = $invoiceId;
    }

    /**
     * Get invoices ID
     */
    public function getInvoiceId(): int {
        return $this->invoiceId;
    }
    // --- END [LUMERA] ---

    /**
     * Constructor
     * @param float $amount
     * @param string $currencyCode
     * @param int $userId
     * @param int $assocId
     */
    public function __construct($amount, $currencyCode, $userId, $assocId) {
        parent::__construct($amount, $currencyCode, $userId, $assocId);
    }

    /**
     * [SHIM] Backward Compatibility
     * @param float $amount
     * @param string $currencyCode
     * @param int $userId
     * @param int $assocId
     */
    public function OJSQueuedPayment($amount, $currencyCode, $userId, $assocId) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Get the journal ID of the payment.
     * @return int
     */
    public function getJournalId() {
        return $this->journalId;
    }

    /**
     * Set the journal ID of the payment.
     * @param int $journalId
     * @return int
     */
    public function setJournalId($journalId) {
        return $this->journalId = $journalId;
    }

    /**
     * Set the type for this payment (PAYMENT_TYPE_...)
     * @param int $type
     * @return int
     */
    public function setType($type) {
        return $this->type = $type;
    }

    /**
     * Get the type of this payment (PAYMENT_TYPE_...)
     * @return int
     */
    public function getType() {
        return $this->type;
    }

    /**
     * Returns the name of the QueuedPayment.
     * Pulled from Journal Settings if present, or from locale file
     * otherwise. For subscriptions, pulls subscription type name.
     * @return string
     */
    public function getName() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($this->getJournalId());

        switch ($this->type) {
            case PAYMENT_TYPE_PURCHASE_SUBSCRIPTION:
            case PAYMENT_TYPE_RENEW_SUBSCRIPTION:
                /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
                $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
                if ($institutionalSubscriptionDao->subscriptionExists($this->assocId)) {
                    $subscription = $institutionalSubscriptionDao->getSubscription($this->assocId);
                } else {
                    /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
                    $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                    $subscription = $individualSubscriptionDao->getSubscription($this->assocId);
                }
                if (!$subscription) return __('payment.type.subscription');
                /** @var SubscriptionTypeDAO $subscriptionTypeDao */
                $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
                $subscriptionType = $subscriptionTypeDao->getSubscriptionType($subscription->getTypeId());

                return __('payment.type.subscription') . ' (' . $subscriptionType->getSubscriptionTypeName() . ')';
            case PAYMENT_TYPE_DONATION:
                if ($journal->getLocalizedSetting('donationFeeName') != '') {
                    return $journal->getLocalizedSetting('donationFeeName');
                } else {
                    return __('payment.type.donation');
                }
            case PAYMENT_TYPE_MEMBERSHIP:
                if ($journal->getLocalizedSetting('membershipFeeName') != '') {
                    return $journal->getLocalizedSetting('membershipFeeName');
                } else {
                    return __('payment.type.membership');
                }
            case PAYMENT_TYPE_PURCHASE_ARTICLE:
                if ($journal->getLocalizedSetting('purchaseArticleFeeName') != '') {
                    return $journal->getLocalizedSetting('purchaseArticleFeeName');
                } else {
                    return __('payment.type.purchaseArticle');
                }
            case PAYMENT_TYPE_PURCHASE_ISSUE:
                if ($journal->getLocalizedSetting('purchaseIssueFeeName') != '') {
                    return $journal->getLocalizedSetting('purchaseIssueFeeName');
                } else {
                    return __('payment.type.purchaseIssue');
                }
            case PAYMENT_TYPE_SUBMISSION:
                if ($journal->getLocalizedSetting('submissionFeeName') != '') {
                    return $journal->getLocalizedSetting('submissionFeeName');
                } else {
                    return __('payment.type.submission');
                }
            case PAYMENT_TYPE_FASTTRACK:
                if ($journal->getLocalizedSetting('fastTrackFeeName') != '') {
                    return $journal->getLocalizedSetting('fastTrackFeeName');
                } else {
                    return __('payment.type.fastTrack');
                }
            case PAYMENT_TYPE_PUBLICATION:
                if ($journal->getLocalizedSetting('publicationFeeName') != '') {
                    return $journal->getLocalizedSetting('publicationFeeName');
                } else {
                    return __('payment.type.publication');
                }
            case PAYMENT_TYPE_GIFT:
                /** @var GiftDAO $giftDao */
                $giftDao = DAORegistry::getDAO('GiftDAO');
                $gift = $giftDao->getGift($this->assocId);

                // Try to return gift details in name
                if ($gift) {
                    return $gift->getGiftName();
                }

                // Otherwise, generic gift name
                return __('payment.type.gift');
            default:
                // Invalid payment type
                assert(false);
        }
    }

    /**
     * Returns the description of the QueuedPayment.
     * Pulled from Journal Settings if present, or from locale file otherwise.
     * For subscriptions, pulls subscription type name.
     * @return string
     */
    public function getDescription() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($this->getJournalId());

        switch ($this->type) {
            case PAYMENT_TYPE_PURCHASE_SUBSCRIPTION:
            case PAYMENT_TYPE_RENEW_SUBSCRIPTION:
                /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
                $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
                if ($institutionalSubscriptionDao->subscriptionExists($this->assocId)) {
                    $subscription = $institutionalSubscriptionDao->getSubscription($this->assocId);
                } else {
                    /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
                    $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                    $subscription = $individualSubscriptionDao->getSubscription($this->assocId);
                }
                if (!$subscription) return __('payment.type.subscription');

                /** @var SubscriptionTypeDAO $subscriptionTypeDao */
                $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
                $subscriptionType = $subscriptionTypeDao->getSubscriptionType($subscription->getTypeId());
                return $subscriptionType->getSubscriptionTypeDescription();
            case PAYMENT_TYPE_DONATION:
                if ($journal->getLocalizedSetting('donationFeeDescription') != '') {
                    return $journal->getLocalizedSetting('donationFeeDescription');
                } else {
                    return __('payment.type.donation');
                }
            case PAYMENT_TYPE_MEMBERSHIP:
                if ($journal->getLocalizedSetting('membershipFeeDescription') != '') {
                    return $journal->getLocalizedSetting('membershipFeeDescription');
                } else {
                    return __('payment.type.membership');
                }
            case PAYMENT_TYPE_PURCHASE_ARTICLE:
                if ($journal->getLocalizedSetting('purchaseArticleFeeDescription') != '') {
                    return $journal->getLocalizedSetting('purchaseArticleFeeDescription');
                } else {
                    return __('payment.type.purchaseArticle');
                }
            case PAYMENT_TYPE_PURCHASE_ISSUE:
                if ($journal->getLocalizedSetting('purchaseIssueFeeDescription') != '') {
                    return $journal->getLocalizedSetting('purchaseIssueFeeDescription');
                } else {
                    return __('payment.type.purchaseIssue');
                }
            case PAYMENT_TYPE_SUBMISSION:
                if ($journal->getLocalizedSetting('submissionFeeDescription') != '') {
                    return $journal->getLocalizedSetting('submissionFeeDescription');
                } else {
                    return __('payment.type.submission');
                }
            case PAYMENT_TYPE_FASTTRACK:
                if ($journal->getLocalizedSetting('fastTrackFeeDescription') != '') {
                    return $journal->getLocalizedSetting('fastTrackFeeDescription');
                } else {
                    return __('payment.type.fastTrack');
                }
            case PAYMENT_TYPE_PUBLICATION:
                if ($journal->getLocalizedSetting('publicationFeeDescription') != '') {
                    return $journal->getLocalizedSetting('publicationFeeDescription');
                } else {
                    return __('payment.type.publication');
                }
            case PAYMENT_TYPE_GIFT:
                /** @var GiftDAO $giftDao */
                $giftDao = DAORegistry::getDAO('GiftDAO');
                $gift = $giftDao->getGift($this->assocId);

                // Try to return gift details in description
                if ($gift) {
                    import('classes.gift.Gift');

                    if ($gift->getGiftType() == GIFT_TYPE_SUBSCRIPTION) {
                        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
                        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
                        $subscriptionType = $subscriptionTypeDao->getSubscriptionType($gift->getAssocId());

                        if ($subscriptionType) {
                            return $subscriptionType->getSubscriptionTypeDescription();    
                        } else {
                            return __('payment.type.gift') . ' ' . __('payment.type.gift.subscription');                                
                        }
                    }
                }

                // Otherwise, generic gift name
                return __('payment.type.gift');
            default:
                // Invalid payment type
                assert(false);
        }
    }

    /**
     * Set the request URL.
     * @param string $url
     * @return string
     */
    public function setRequestUrl($url) {
        return $this->requestUrl = $url;
    }

    /**
     * Get the request URL.
     * @return string
     */
    public function getRequestUrl() {
        return $this->requestUrl;
    }

}
?>
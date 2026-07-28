<?php
declare(strict_types=1);

/**
 * @file classes/payment/ojs/OJSCompletedPayment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin, MJ Suhonos
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OJSCompletedPayment
 * @ingroup payment_ojs
 * @see OJSCompletedPaymentDAO
 *
 * @brief Class describing a payment ready to be in the database.
 */

import('lib.pkp.classes.payment.Payment');

class OJSCompletedPayment extends Payment {

    /** @var int|null */
    protected $_journalId;

    /** @var int|null */
    protected $_paperId;

    /** @var int|null */
    protected $_type;

    /** @var string|null */
    protected $_timestamp;

    /** @var string|null */
    protected $_payMethod;

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function OJSCompletedPayment() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    //
    // Get/set methods
    //

    /**
     * Set the ID of the payment.
     * @param int|null $queuedPaymentId
     */
    public function setCompletedPaymentId($queuedPaymentId) {
        parent::setPaymentId($queuedPaymentId !== null ? (int) $queuedPaymentId : null);
    }

    /**
     * Get the ID of the payment.
     * @return int|null
     */
    public function getCompletedPaymentId() {
        $id = parent::getId();
        return $id !== null ? (int) $id : null;
    }

    /**
     * Get the journal ID of the payment.
     * @return int|null
     */
    public function getJournalId() {
        return $this->_journalId !== null ? (int) $this->_journalId : null;
    }

    /**
     * Set the journal ID of the payment.
     * @param int|null $journalId
     */
    public function setJournalId($journalId) {
        $this->_journalId = $journalId !== null ? (int) $journalId : null;
    }

    /**
     * Set the Payment Type.
     * @param int|null $type
     */
    public function setType($type) {
        $this->_type = $type !== null ? (int) $type : null;
    }

    /**
     * Get the Payment Type.
     * @return int|null
     */
    public function getType() {
        return $this->_type !== null ? (int) $this->_type : null;
    }

    /**
     * Returns the name of the CompletedPayment.
     * Pulled from Journal Settings if present, or from locale file otherwise.
     * For subscriptions, pulls subscription type name.
     * @return string|null
     */
    public function getName() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($this->getJournalId());
        if (!$journal) {
            return null;
        }

        $type = $this->getType();
        $assocId = $this->getAssocId();

        switch ($type) {
            case PAYMENT_TYPE_PURCHASE_SUBSCRIPTION:
            case PAYMENT_TYPE_RENEW_SUBSCRIPTION:
                /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
                $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
                if ($institutionalSubscriptionDao->subscriptionExists($assocId)) {
                    $subscription = $institutionalSubscriptionDao->getSubscription($assocId);
                } else {
                    /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
                    $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                    $subscription = $individualSubscriptionDao->getSubscription($assocId);
                }
                if ($subscription === null) {
                    return __('payment.type.subscription');
                }

                /** @var SubscriptionTypeDAO $subscriptionTypeDao */
                $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
                $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $subscription->getTypeId());
                
                if ($subscriptionType === null) {
                    return __('payment.type.subscription');
                }

                return __('payment.type.subscription') . ' (' . $subscriptionType->getSubscriptionTypeName() . ')';
                
            case PAYMENT_TYPE_DONATION:
                $feeName = $journal->getLocalizedSetting('donationFeeName');
                return $feeName !== '' ? (string) $feeName : __('payment.type.donation');
                
            case PAYMENT_TYPE_MEMBERSHIP:
                $feeName = $journal->getLocalizedSetting('membershipFeeName');
                return $feeName !== '' ? (string) $feeName : __('payment.type.membership');
                
            case PAYMENT_TYPE_PURCHASE_ARTICLE:
                $feeName = $journal->getLocalizedSetting('purchaseArticleFeeName');
                return $feeName !== '' ? (string) $feeName : __('payment.type.purchaseArticle');
                
            case PAYMENT_TYPE_PURCHASE_ISSUE:
                $feeName = $journal->getLocalizedSetting('purchaseIssueFeeName');
                return $feeName !== '' ? (string) $feeName : __('payment.type.purchaseIssue');
                
            case PAYMENT_TYPE_SUBMISSION:
                $feeName = $journal->getLocalizedSetting('submissionFeeName');
                return $feeName !== '' ? (string) $feeName : __('payment.type.submission');
                
            case PAYMENT_TYPE_FASTTRACK:
                $feeName = $journal->getLocalizedSetting('fastTrackFeeName');
                return $feeName !== '' ? (string) $feeName : __('payment.type.fastTrack');
                
            case PAYMENT_TYPE_PUBLICATION:
                $feeName = $journal->getLocalizedSetting('publicationFeeName');
                return $feeName !== '' ? (string) $feeName : __('payment.type.publication');
                
            case PAYMENT_TYPE_GIFT:
                /** @var GiftDAO $giftDao */
                $giftDao = DAORegistry::getDAO('GiftDAO');
                $gift = $giftDao->getGift($assocId);

                if ($gift !== null) {
                    return (string) $gift->getGiftName();
                }
                return __('payment.type.gift');
                
            default:
                return null;
        }
    }

    /**
     * Returns the description of the CompletedPayment.
     * Pulled from Journal Settings if present, or from locale file otherwise.
     * For subscriptions, pulls subscription type description.
     * @return string|null
     */
    public function getDescription() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($this->getJournalId());
        if (!$journal) {
            return null;
        }

        $type = $this->getType();
        $assocId = $this->getAssocId();

        switch ($type) {
            case PAYMENT_TYPE_PURCHASE_SUBSCRIPTION:
            case PAYMENT_TYPE_RENEW_SUBSCRIPTION:
                /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
                $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
                if ($institutionalSubscriptionDao->subscriptionExists($assocId)) {
                    $subscription = $institutionalSubscriptionDao->getSubscription($assocId);
                } else {
                    /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
                    $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                    $subscription = $individualSubscriptionDao->getSubscription($assocId);
                }
                if ($subscription === null) {
                    return __('payment.type.subscription');
                }

                /** @var SubscriptionTypeDAO $subscriptionTypeDao */
                $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
                $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $subscription->getTypeId());
                
                if ($subscriptionType === null) {
                    return __('payment.type.subscription');
                }

                return (string) $subscriptionType->getSubscriptionTypeDescription();
                
            case PAYMENT_TYPE_DONATION:
                $feeDesc = $journal->getLocalizedSetting('donationFeeDescription');
                return $feeDesc !== '' ? (string) $feeDesc : __('payment.type.donation');
                
            case PAYMENT_TYPE_MEMBERSHIP:
                $feeDesc = $journal->getLocalizedSetting('membershipFeeDescription');
                return $feeDesc !== '' ? (string) $feeDesc : __('payment.type.membership');
                
            case PAYMENT_TYPE_PURCHASE_ARTICLE:
                $feeDesc = $journal->getLocalizedSetting('purchaseArticleFeeDescription');
                return $feeDesc !== '' ? (string) $feeDesc : __('payment.type.purchaseArticle');
                
            case PAYMENT_TYPE_PURCHASE_ISSUE:
                $feeDesc = $journal->getLocalizedSetting('purchaseIssueFeeDescription');
                return $feeDesc !== '' ? (string) $feeDesc : __('payment.type.purchaseIssue');
                
            case PAYMENT_TYPE_SUBMISSION:
                $feeDesc = $journal->getLocalizedSetting('submissionFeeDescription');
                return $feeDesc !== '' ? (string) $feeDesc : __('payment.type.submission');
                
            case PAYMENT_TYPE_FASTTRACK:
                $feeDesc = $journal->getLocalizedSetting('fastTrackFeeDescription');
                return $feeDesc !== '' ? (string) $feeDesc : __('payment.type.fastTrack');
                
            case PAYMENT_TYPE_PUBLICATION:
                $feeDesc = $journal->getLocalizedSetting('publicationFeeDescription');
                return $feeDesc !== '' ? (string) $feeDesc : __('payment.type.publication');
                
            case PAYMENT_TYPE_GIFT:
                /** @var GiftDAO $giftDao */
                $giftDao = DAORegistry::getDAO('GiftDAO');
                $gift = $giftDao->getGift($assocId);

                if ($gift !== null) {
                    import('classes.gift.Gift');
                    if ((int) $gift->getGiftType() === GIFT_TYPE_SUBSCRIPTION) {
                        /** @var SubscriptionTypeDAO $subscriptionTypeDao */
                        $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
                        $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $gift->getAssocId());

                        if ($subscriptionType !== null) {
                            return (string) $subscriptionType->getSubscriptionTypeDescription();
                        }
                        return __('payment.type.gift') . ' ' . __('payment.type.gift.subscription');
                    }
                }
                return __('payment.type.gift');
                
            default:
                return null;
        }
    }

    /**
     * Get the timestamp of the payment.
     * @return string|null
     */
    public function getTimestamp() {
        return $this->_timestamp !== null ? (string) $this->_timestamp : null;
    }

    /**
     * Set the timestamp of the payment.
     * @param string|int|null $timestamp Unix timestamp or ISO datetime string
     */
    public function setTimestamp($timestamp) {
        $this->_timestamp = $timestamp !== null ? (string) $timestamp : null;
    }

    /**
     * Get the method of payment.
     * @return string|null
     */
    public function getPayMethodPluginName() {
        return $this->_payMethod !== null ? (string) $this->_payMethod : null;
    }

    /**
     * Set the method of payment.
     * @param string|null $payMethod
     */
    public function setPayMethodPluginName($payMethod) {
        $this->_payMethod = $payMethod !== null ? (string) $payMethod : null;
    }

    //
    // Display-related get Methods
    //

    /**
     * Check if the type is a membership.
     * @return bool
     */
    public function isMembership(): bool {
        return $this->getType() === PAYMENT_TYPE_MEMBERSHIP;
    }

    /**
     * Check if the type is a subscription.
     * @return bool
     */
    public function isSubscription(): bool {
        $type = $this->getType();
        return $type === PAYMENT_TYPE_RENEW_SUBSCRIPTION || $type === PAYMENT_TYPE_PURCHASE_SUBSCRIPTION;
    }

    /**
     * Get some information about the assocId for display.
     * @return string|false
     */
    public function getAssocDescription() {
        $assocId = $this->getAssocId();
        if ($assocId === null || (int) $assocId === 0) {
            return false;
        }

        $type = $this->getType();
        $journalId = $this->getJournalId();

        switch ($type) {
            case PAYMENT_TYPE_PURCHASE_SUBSCRIPTION:
            case PAYMENT_TYPE_RENEW_SUBSCRIPTION:
                /** @var InstitutionalSubscriptionDAO $institutionalSubscriptionDao */
                $institutionalSubscriptionDao = DAORegistry::getDAO('InstitutionalSubscriptionDAO');
                if ($institutionalSubscriptionDao->subscriptionExists($assocId)) {
                    $subscription = $institutionalSubscriptionDao->getSubscription($assocId);
                } else {
                    /** @var IndividualSubscriptionDAO $individualSubscriptionDao */
                    $individualSubscriptionDao = DAORegistry::getDAO('IndividualSubscriptionDAO');
                    $subscription = $individualSubscriptionDao->getSubscription($assocId);
                }
                if ($subscription === null) {
                    return __('manager.payment.notFound');
                }

                /** @var SubscriptionTypeDAO $subscriptionTypeDao */
                $subscriptionTypeDao = DAORegistry::getDAO('SubscriptionTypeDAO');
                $subscriptionType = $subscriptionTypeDao->getSubscriptionType((int) $subscription->getTypeId());
                
                if ($subscriptionType === null) {
                    return __('manager.payment.notFound');
                }

                $membership = $subscription->getMembership();
                $typeName = $subscriptionType->getSubscriptionTypeName();
                if ($membership !== null && $membership !== '') {
                    return $typeName . ' (' . $membership . ')';
                }
                return $typeName;
                
            case PAYMENT_TYPE_SUBMISSION:
            case PAYMENT_TYPE_FASTTRACK:
            case PAYMENT_TYPE_PUBLICATION:
            case PAYMENT_TYPE_PURCHASE_ARTICLE:
                // All article-related payments should output the article title
                /** @var ArticleDAO $articleDao */
                $articleDao = DAORegistry::getDAO('ArticleDAO');
                $article = $articleDao->getArticle((int) $assocId, $journalId);
                if ($article === null) {
                    return __('manager.payment.notFound');
                }
                return (string) $article->getLocalizedTitle();
                
            case PAYMENT_TYPE_PURCHASE_ISSUE:
                // Purchase issue payment should output the issue title
                /** @var IssueDAO $issueDao */
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $issue = $issueDao->getIssueById((int) $assocId, $journalId);
                if ($issue === null) {
                    return __('manager.payment.notFound');
                }
                return (string) $issue->getIssueIdentification();
                
            case PAYMENT_TYPE_GIFT:
                /** @var GiftDAO $giftDao */
                $giftDao = DAORegistry::getDAO('GiftDAO');
                $gift = $giftDao->getGift((int) $assocId);

                if ($gift !== null) {
                    return __('gifts.buyer') . ': ' . (string) $gift->getBuyerFullName() . ' (' . (string) $gift->getBuyerEmail() . ') ' . 
                           __('gifts.recipient') . ': ' . (string) $gift->getRecipientFullName() . ' (' . (string) $gift->getRecipientEmail() . ')';
                }
                return false;
                
            case PAYMENT_TYPE_MEMBERSHIP:
            case PAYMENT_TYPE_DONATION:
                return false;
                
            default:
                return false;
        }
    }
    
}
?>
<?php
declare(strict_types=1);

/**
 * @defgroup payment
 */

/**
 * @file classes/payment/Payment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Payment
 * @ingroup payment
 *
 * @brief Abstract class for payments.
 *
 */

/** DOES NOT inherit from DataObject for the sake of concise serialization */

class Payment {
    
    /** @var int payment id */
    public $paymentId;

    /** @var float|int amount of payment in $currencyCode units */
    public $amount;

    /** @var string ISO 4217 alpha currency code */
    public $currencyCode;

    /** @var int user ID of customer making payment */
    public $userId;

    /** @var int association ID for payment */
    public $assocId;

    /**
     * Constructor
     * @param $amount float|int
     * @param $currencyCode string
     * @param $userId int
     * @param $assocId int
     */
    public function __construct($amount = null, $currencyCode = null, $userId = null, $assocId = null) {
        $this->amount = $amount;
        $this->currencyCode = $currencyCode;
        $this->userId = $userId;
        $this->assocId = $assocId;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Payment($amount = null, $currencyCode = null, $userId = null, $assocId = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::Payment(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($amount, $currencyCode, $userId, $assocId);
    }

    /**
     * Get the row id of the payment.
     * @return int
     */
    public function getId() {
        return $this->paymentId;
    }

    /**
     * @deprecated
     */
    public function getPaymentId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->getId();
    }

    /**
     * Set the id of payment
     * @param $paymentId int
     * @return int new payment id
     */
    public function setId($paymentId) {
        return $this->paymentId = $paymentId;
    }

    /**
     * @deprecated
     */
    public function setPaymentId($paymentId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        return $this->setId($paymentId);
    }

    /**
     * Set the payment amount
     * @param $amount numeric
     * @return numeric new amount
     */
    public function setAmount($amount) {
        return $this->amount = $amount;
    }

    /**
     * Get the payment amount
     * @return numeric
     */
    public function getAmount() {
        return $this->amount;
    }

    /**
     * Set the currency code for the transaction (ISO 4217)
     * @param $currencyCode string
     * @return string new currency code
     */
    public function setCurrencyCode($currencyCode) {
        return $this->currencyCode = $currencyCode;
    }

    /**
     * Get the currency code for the transaction (ISO 4217)
     * @return string
     */
    public function getCurrencyCode() {
        return $this->currencyCode;
    }

    /**
     * Get the name of the transaction.
     * @return string
     */
    public function getName() {
        // must be implemented by sub-classes
        assert(false);
    }

    /**
     * Get a description of the transaction.
     * @return string
     */
    public function getDescription() {
        // must be implemented by sub-classes
        assert(false);
    }

    /**
     * Set the user ID of the customer.
     * @param $userId int
     * @return int New user ID
     */
    public function setUserId($userId) {
        return $this->userId = $userId;
    }

    /**
     * Get the user ID of the customer.
     * @return int
     */
    public function getUserId() {
        return $this->userId;
    }

    /**
     * Set the association ID for the payment.
     * @param $assocId int
     * @return int New association ID
     */
    public function setAssocId($assocId) {
        return $this->assocId = $assocId;
    }

    /**
     * Get the association ID for the payment.
     * @return int
     */
    public function getAssocId() {
        return $this->assocId;
    }
}
?>
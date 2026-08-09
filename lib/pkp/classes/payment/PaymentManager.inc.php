<?php
declare(strict_types=1);

/**
 * @file classes/payment/PaymentManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymentManager
 * @ingroup payment
 * @see Payment
 *
 * @brief Provides payment management functions.
 *
 */

class PaymentManager {
    
    /** @var PKPRequest */
    public $request;

    /**
     * Constructor
     * @param PKPRequest $request
     */
    public function __construct($request) {
        $this->request = $request;
    }

    /**
     * [SHIM] Backward Compatibility
     * @param PKPRequest $request
     */
    public function PaymentManager($request) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Queue a payment for receipt.
     * @param QueuedPayment $queuedPayment
     * @param $expiryDate date optional
     * @return mixed Queued payment ID for new payment, or false if fails
     */
    public function queuePayment($queuedPayment, $expiryDate = null) {
        if (!$this->isConfigured()) return false;

        /** @var QueuedPaymentDAO $queuedPaymentDao */
        $queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
        $queuedPaymentId = $queuedPaymentDao->insertQueuedPayment($queuedPayment, $expiryDate);

        // Perform periodic cleanup
        if (time() % 100 == 0) $queuedPaymentDao->deleteExpiredQueuedPayments();

        return $queuedPaymentId;
    }

    /**
     * Abstract method for fetching the payment plugin
     * @return object
     */
    public function getPaymentPlugin() {
        // Abstract method; subclasses should implement.
        assert(false);
    }

    /**
     * Check if there is a payment plugin and if is configured
     * @return bool
     */
    public function isConfigured() {
        $paymentPlugin = $this->getPaymentPlugin();
        if ($paymentPlugin !== null) return $paymentPlugin->isConfigured(PKPApplication::getRequest());
        return false;
    }

    /**
     * Call the payment plugin's display method
     * @param int $queuedPaymentId
     * @param QueuedPayment $queuedPayment
     * @return bool
     */
    public function displayPaymentForm($queuedPaymentId, $queuedPayment) {
        $paymentPlugin = $this->getPaymentPlugin();
        if ($paymentPlugin !== null && $paymentPlugin->isConfigured()) {
            return $paymentPlugin->displayPaymentForm($queuedPaymentId, $queuedPayment, $this->request);
        }
        return false;
    }

    /**
     * Call the payment plugin's settings display method
     * @return boolean
     */
    public function displayConfigurationForm() {
        $paymentPlugin = $this->getPaymentPlugin();
        if ($paymentPlugin !== null && $paymentPlugin->isConfigured()) return $paymentPlugin->displayConfigurationForm();
        return false;
    }

    /**
     * Fetch a queued payment
     * @param int $queuedPaymentId
     * @return QueuedPayment
     */
    public function getQueuedPayment($queuedPaymentId) {
        /** @var QueuedPaymentDAO $queuedPaymentDao */
        $queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
        $queuedPayment = $queuedPaymentDao->getQueuedPayment($queuedPaymentId);
        return $queuedPayment;
    }

    /**
     * Fulfill a queued payment
     * @param $queuedPayment QueuedPayment
     * @return boolean success/failure
     */
    public function fulfillQueuedPayment($queuedPayment) {
        // must be implemented by sub-classes
        assert(false);
    }
    
}
?>
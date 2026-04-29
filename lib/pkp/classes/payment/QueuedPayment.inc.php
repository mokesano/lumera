<?php
declare(strict_types=1);

/**
 * @file classes/payment/QueuedPayment.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class QueuedPayment
 * @ingroup payment
 * @see QueuedPaymentDAO
 *
 * @brief Queued (unfulfilled) payment data structure
 *
 */

import('lib.pkp.classes.payment.Payment');

class QueuedPayment extends Payment {
    
    /**
     * Constructor
     * @param $amount float
     * @param $currencyCode string
     * @param $userId int
     * @param $assocId int
     */
    public function __construct($amount, $currencyCode, $userId = null, $assocId = null) {
        parent::__construct($amount, $currencyCode, $userId, $assocId);
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function QueuedPayment($amount, $currencyCode, $userId = null, $assocId = null) {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::QueuedPayment(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct($amount, $currencyCode, $userId, $assocId);
    }

    /**
     * Set the queued payment ID
     * @param $queuedPaymentId int
     * @return int
     */
    public function setQueuedPaymentId($queuedPaymentId) {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        // Modernization: Call the modern setId instead of the deprecated setPaymentId
        return $this->setId($queuedPaymentId);
    }

    /**
     * Get the queued payment ID
     * @return int
     */
    public function getQueuedPaymentId() {
        if (Config::getVar('debug', 'deprecation_warnings')) trigger_error('Deprecated function.');
        // Modernization: Call the modern getId instead of the deprecated getPaymentId
        return $this->getId();
    }
}
?>
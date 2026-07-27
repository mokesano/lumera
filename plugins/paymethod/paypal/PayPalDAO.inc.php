<?php
declare(strict_types=1);

/**
 * @file plugins/paymethod/paypal/PayPalDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin, MJ Suhonos
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PayPalDAO
 * @ingroup plugins_paymethod_paypal
 *
 * @brief Operations for retrieving and modifying Transactions objects.
 */

import('lib.pkp.classes.db.DAO');

class PayPalDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function PayPalDAO() {
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
     * Insert a payment into the payments table.
     * @param string $txnId
     * @param string $txnType
     * @param string $payerEmail
     * @param string $receiverEmail
     * @param string $itemNumber
     * @param string $paymentDate
     * @param string $payerId
     * @param string $receiverId
     * @return bool
     */
    public function insertTransaction($txnId, $txnType, $payerEmail, $receiverEmail, $itemNumber, $paymentDate, $payerId, $receiverId): bool {
        $this->update(
            sprintf(
                'INSERT INTO paypal_transactions (
                    txn_id,
                    txn_type,
                    payer_email,
                    receiver_email,
                    item_number,
                    payment_date,
                    payer_id,
                    receiver_id
                ) VALUES (
                    ?, ?, ?, ?, ?, %s, ?, ?
                )',
                $this->datetimeToDB($paymentDate)
            ),
            [
                (string) $txnId,
                (string) $txnType,
                (string) $payerEmail,
                (string) $receiverEmail,
                (string) $itemNumber,
                (string) $payerId,
                (string) $receiverId
            ]
        );

        return true;
    }

    /**
     * Check whether a given transaction exists.
     * @param string $txnId
     * @return bool
     */
    public function transactionExists($txnId): bool {
        $result = $this->retrieve(
            'SELECT count(*) AS count FROM paypal_transactions WHERE txn_id = ?',
            [(string) $txnId]
        );

        $returner = false;

        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0) >= 1);
            $result->Close();
        }
        
        return $returner;
    }
    
}
?>
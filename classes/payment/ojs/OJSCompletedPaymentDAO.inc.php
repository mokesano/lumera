<?php
declare(strict_types=1);

/**
 * @file classes/payment/ojs/OJSCompletedPaymentDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OJSCompletedPaymentDAO
 * @ingroup payment
 * @see OJSCompletedPayment
 * @see Payment
 *
 * @brief Operations for retrieving and querying past payments.
 */

import('classes.payment.ojs.OJSCompletedPayment');

class OJSCompletedPaymentDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility.
     */
    public function OJSCompletedPaymentDAO() {
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
     * Retrieve a CompletedPayment by its ID.
     * @param int $completedPaymentId
     * @param int|null $journalId (optional)
     * @return OJSCompletedPayment|null
     */
    public function getCompletedPayment($completedPaymentId, $journalId = null) {
        $params = [(int) $completedPaymentId];
        if ($journalId !== null) {
            $params[] = (int) $journalId;
        }

        $result = $this->retrieve(
            'SELECT * FROM completed_payments WHERE completed_payment_id = ?' . ($journalId !== null ? ' AND journal_id = ?' : ''),
            $params
        );

        $returner = null;
        // [SCHOLARWIZDAM LUMERA STANDARD] Using $result && !$result->EOF
        if ($result && !$result->EOF) {
            $returner = $this->_returnPaymentFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Insert a new completed payment.
     * @param OJSCompletedPayment $completedPayment
     * @return int
     */
    public function insertCompletedPayment($completedPayment) {
        $this->update(
            sprintf('INSERT INTO completed_payments
                (timestamp, payment_type, journal_id, user_id, assoc_id, amount, currency_code_alpha, payment_method_plugin_name)
                VALUES
                (%s, ?, ?, ?, ?, ?, ?, ?)',
                $this->datetimeToDB(Core::getCurrentDate())),
            [
                (int) $completedPayment->getType(),
                (int) $completedPayment->getJournalId(),
                (int) $completedPayment->getUserId(),
                (int) $completedPayment->getAssocId(),
                (float) $completedPayment->getAmount(),
                (string) $completedPayment->getCurrencyCode(),
                (string) $completedPayment->getPayMethodPluginName()
            ]
        );

        return $this->getInsertCompletedPaymentId();
    }

    /**
     * Update an existing completed payment.
     * @param OJSCompletedPayment $completedPayment
     * @return bool
     */
    public function updateObject($completedPayment) {
        $returner = $this->update(
            sprintf('UPDATE completed_payments
            SET
                timestamp = %s,
                payment_type = ?,
                journal_id = ?,
                user_id = ?,
                assoc_id = ?,
                amount = ?,
                currency_code_alpha = ?,
                payment_method_plugin_name = ? 
            WHERE completed_payment_id = ?',
            $this->datetimeToDB($completedPayment->getTimestamp())),
            [
                (int) $completedPayment->getType(),
                (int) $completedPayment->getJournalId(),
                (int) $completedPayment->getUserId(),
                (int) $completedPayment->getAssocId(),
                (float) $completedPayment->getAmount(),
                (string) $completedPayment->getCurrencyCode(),
                (string) $completedPayment->getPayMethodPluginName(),
                (int) $completedPayment->getCompletedPaymentId()
            ]
        );

        return (bool) $returner;
    }

    /**
     * Get the ID of the last inserted completed payment.
     * @return int
     */
    public function getInsertCompletedPaymentId() {
        return (int) $this->getInsertId('completed_payments', 'completed_payment_id');
    }

    /**
     * Look for a completed PURCHASE_ARTICLE payment matching the article ID.
     * @param int $userId
     * @param int $articleId
     * @return bool
     */
    public function hasPaidPurchaseArticle($userId, $articleId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM completed_payments WHERE payment_type = ? AND user_id = ? AND assoc_id = ?',
            [
                PAYMENT_TYPE_PURCHASE_ARTICLE,
                (int) $userId,
                (int) $articleId
            ]
        );

        $returner = false;
        // [SCHOLARWIZDAM LUMERA STANDARD] Using $result && !$result->EOF instead of $result->fields[0]
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Look for a completed PURCHASE_ISSUE payment matching the journal and issue IDs.
     * @param int $userId
     * @param int $issueId
     * @return bool
     */
    public function hasPaidPurchaseIssue($userId, $issueId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM completed_payments WHERE payment_type = ? AND user_id = ? AND assoc_id = ?',
            [
                PAYMENT_TYPE_PURCHASE_ISSUE,
                (int) $userId,
                (int) $issueId
            ]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Look for a completed SUBMISSION payment matching the journal and article IDs.
     * @param int $journalId
     * @param int $articleId
     * @return bool
     */
    public function hasPaidSubmission($journalId, $articleId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM completed_payments WHERE payment_type = ? AND journal_id = ? AND assoc_id = ?',
            [
                (int) PAYMENT_TYPE_SUBMISSION,
                (int) $journalId,
                (int) $articleId
            ]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Get a CompletedPayment for a SUBMISSION type payment matching the journal and article IDs.
     * @param int $journalId
     * @param int $articleId
     * @return OJSCompletedPayment|null
     */
    public function getSubmissionCompletedPayment($journalId, $articleId) {
        $result = $this->retrieve(
            'SELECT * FROM completed_payments WHERE payment_type = ? AND journal_id = ? AND assoc_id = ?',
            [
                (int) PAYMENT_TYPE_SUBMISSION,
                (int) $journalId,
                (int) $articleId
            ]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnPaymentFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Look for a completed FASTTRACK payment matching the journal and article IDs.
     * @param int $journalId
     * @param int $articleId
     * @return bool
     */
    public function hasPaidFastTrack($journalId, $articleId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM completed_payments WHERE payment_type = ? AND journal_id = ? AND assoc_id = ?',
            [
                (int) PAYMENT_TYPE_FASTTRACK,
                (int) $journalId,
                (int) $articleId
            ]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Get a CompletedPayment for a FASTTRACK type payment matching the journal and article IDs.
     * @param int $journalId
     * @param int $articleId
     * @return OJSCompletedPayment|null
     */
    public function getFastTrackCompletedPayment($journalId, $articleId) {
        $result = $this->retrieve(
            'SELECT * FROM completed_payments WHERE payment_type = ? AND journal_id = ? AND assoc_id = ?',
            [
                (int) PAYMENT_TYPE_FASTTRACK,
                (int) $journalId,
                (int) $articleId
            ]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnPaymentFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Look for a completed payment matching the publication type and article ID.
     * @param int $journalId
     * @param int $articleId
     * @return bool
     */
    public function hasPaidPublication($journalId, $articleId) {
        $result = $this->retrieve(
            'SELECT COUNT(*) AS count FROM completed_payments WHERE payment_type = ? AND journal_id = ? AND assoc_id = ?',
            [
                (int) PAYMENT_TYPE_PUBLICATION,
                (int) $journalId,
                (int) $articleId
            ]
        );

        $returner = false;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $returner = ((int) ($row['count'] ?? 0)) > 0;
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Get a CompletedPayment for a PUBLICATION type payment matching the journal and article IDs.
     * @param int $journalId
     * @param int $articleId
     * @return OJSCompletedPayment|null
     */
    public function getPublicationCompletedPayment($journalId, $articleId) {
        $result = $this->retrieve(
            'SELECT * FROM completed_payments WHERE payment_type = ? AND journal_id = ? AND assoc_id = ?',
            [
                (int) PAYMENT_TYPE_PUBLICATION,
                (int) $journalId,
                (int) $articleId
            ]
        );

        $returner = null;
        if ($result && !$result->EOF) {
            $returner = $this->_returnPaymentFromRow($result->GetRowAssoc(false));
        }
        if ($result) {
            $result->Close();
        }
        return $returner;
    }

    /**
     * Retrieve an array of payments for a particular journal ID.
     * @param int $journalId
     * @param mixed $rangeInfo (optional)
     * @return DAOResultFactory containing matching payments
     */
    public function getPaymentsByJournalId($journalId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM completed_payments WHERE journal_id = ? ORDER BY timestamp DESC',
            [(int) $journalId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnPaymentFromRow');
    }

    /**
     * Retrieve an array of payments for a particular user ID.
     * @param int $userId
     * @param mixed $rangeInfo (optional)
     * @return DAOResultFactory containing matching payments
     */
    public function getByUserId($userId, $rangeInfo = null) {
        $result = $this->retrieveRange(
            'SELECT * FROM completed_payments WHERE user_id = ? ORDER BY timestamp DESC',
            [(int) $userId],
            $rangeInfo
        );

        return new DAOResultFactory($result, $this, '_returnPaymentFromRow');
    }

    /**
     * Return a new data object.
     * @return OJSCompletedPayment
     */
    public function newDataObject() {
        return new OJSCompletedPayment();
    }

    /**
     * Internal function to return a OJSCompletedPayment object from a row.
     * @param array $row
     * @return OJSCompletedPayment
     */
    public function _returnPaymentFromRow($row) {
        $payment = $this->newDataObject();
        $payment->setTimestamp($this->datetimeFromDB($row['timestamp']));
        $payment->setId((int) $row['completed_payment_id']);
        $payment->setType((int) $row['payment_type']);
        $payment->setJournalId((int) $row['journal_id']);
        $payment->setAmount((float) $row['amount']);
        $payment->setCurrencyCode((string) $row['currency_code_alpha']);
        $payment->setUserId((int) $row['user_id']);
        $payment->setAssocId((int) $row['assoc_id']);
        $payment->setPayMethodPluginName((string) $row['payment_method_plugin_name']);

        return $payment;
    }
    
}
?>
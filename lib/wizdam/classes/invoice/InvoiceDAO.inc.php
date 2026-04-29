<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/invoice/InvoiceDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * @class InvoiceDAO
 * @brief Operations for retrieving and modifying Invoice objects. 
 * Memiliki fitur Legacy Bridge ke tabel completed_payments bawaan OJS.
 */

import('lib.pkp.classes.db.DAO');
import('lib.pkp.classes.db.DBResultRange');
import('lib.wizdam.classes.invoice.Invoice');
import('classes.payment.ojs.OJSQueuedPayment');
import('classes.payment.QueuedPayment');

class InvoiceDAO extends DAO {

    /**
     * Internal function to return an Invoice object from a DB row.
     * @param array $row
     * @return Invoice
     * @deprecated Use getById() or getByUserId() instead.
     */
    public function _fromRow($row) {
        $invoice = new Invoice();
        $invoice->setData('invoiceId', $row['invoice_id']);
        $invoice->setData('journalId', $row['journal_id']);
        $invoice->setData('userId', $row['user_id']);
        $invoice->setData('submissionId', $row['submission_id']);
        $invoice->setData('invoiceNumber', $row['invoice_number'] ?? null);
        $invoice->setData('invoiceCode', $row['invoice_code'] ?? null);
        $invoice->setFeeType($row['fee_type']);
        $invoice->setData('amount', $row['amount']);
        $invoice->setData('currencyCode', $row['currency_code']);
        $invoice->setData('status', $row['status']);
        $invoice->setData('paymentMethod', $row['payment_method']);
        $invoice->setData('dateBilled', $this->datetimeFromDB($row['date_billed']));
        $invoice->setData('datePaid', $this->datetimeFromDB($row['date_paid']));
        return $invoice;
    }

    /**
     * Internal function to create an Invoice object from a legacy completed_payments row.
     * @param array $row
     * @return Invoice
     * @deprecated Use getById() or getByUserId() instead.
     */
    public function _fromLegacyRow(array $row): Invoice {
        $invoice = new Invoice();
        $invoice->setData('invoiceId', (int) $row['completed_payment_id']);
        $invoice->setUserId((int) $row['user_id']);
        $invoice->setData('journalId', (int) $row['journal_id']);
        $invoice->setData('submissionId', (int) $row['assoc_id']); 
        $invoice->setData('feeType', 'LEGACY_FEE');
        $invoice->setData('amount', (float) $row['amount']);
        $invoice->setData('currencyCode', $row['currency_code_alpha'] ?? 'USD');
        $invoice->setData('status', 'PAID'); 
        $invoice->setData('paymentMethod', $row['payment_method_plugin_name']);
        $invoice->setData('dateBilled', $this->datetimeFromDB($row['timestamp']));
        $invoice->setData('datePaid', $this->datetimeFromDB($row['timestamp']));
        $invoice->setData('isLegacy', true); 
        return $invoice;
    }

    /**
     * Retrieve an Invoice by its ID. Will check both invoices and completed_payments tables.
     * @param int $invoiceId
     * @return Invoice|null
     */
    public function getById(int $invoiceId): ?Invoice {
        $result = $this->retrieve('SELECT * FROM invoices WHERE invoice_id = ?', [(int) $invoiceId]);
        if ($result && $result->RecordCount() > 0) {
            $invoice = $this->_fromRow($result->GetRowAssoc(false));
            $result->Close();
            return $invoice;
        }
        if ($result) $result->Close();

        $legacyResult = $this->retrieve('SELECT * FROM completed_payments WHERE completed_payment_id = ?', [(int) $invoiceId]);
        if ($legacyResult && $legacyResult->RecordCount() > 0) {
            $invoice = $this->_fromLegacyRow($legacyResult->GetRowAssoc(false));
            $legacyResult->Close();
            return $invoice;
        }
        if ($legacyResult) $legacyResult->Close();
        return null;
    }

    /**
     * Retrieve all Invoices for a given user ID, including both current and legacy invoices.
     * @param int $userId
     * @return Invoice[]
     */
    public function getByUserId(int $userId): array {
        $invoices = [];
        $result = $this->retrieve('SELECT * FROM invoices WHERE user_id = ? ORDER BY date_billed DESC', [(int) $userId]);
        while ($result && !$result->EOF) {
            $invoices[] = $this->_fromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }
        if ($result) $result->Close();

        $legacyResult = $this->retrieve('SELECT * FROM completed_payments WHERE user_id = ? ORDER BY timestamp DESC', [(int) $userId]);
        while ($legacyResult && !$legacyResult->EOF) {
            $invoices[] = $this->_fromLegacyRow($legacyResult->GetRowAssoc(false));
            $legacyResult->MoveNext();
        }
        if ($legacyResult) $legacyResult->Close();
        return $invoices;
    }

    /**
     * Insert a new Invoice into the database. Returns the new invoice ID.
     * @param Invoice $invoice
     * @return int
     */
    public function insertObject(Invoice $invoice): int {
        $success = $this->update(
            sprintf(
                'INSERT INTO invoices (journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, date_billed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, %s)',
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            [
                $invoice->getData('journalId'),
                $invoice->getUserId(),
                $invoice->getData('submissionId'),
                $invoice->getData('invoiceNumber'),
                $invoice->getData('invoiceCode'),
                $invoice->getFeeType(),
                $invoice->getAmount(),
                $invoice->getCurrencyCode(),
                $invoice->getStatus()
            ]
        );

        if (!$success) {
            throw new \Exception("Gagal melakukan INSERT ke invoices. Periksa log database MySQL Anda.");
        }

        $invoiceId = (int) $this->getInsertId();
        $invoice->setData('invoiceId', $invoiceId);
        return $invoiceId;
    }

    /**
     * Update an existing Invoice in the database. Will not update legacy invoices.
     * @param Invoice $invoice
     */
    public function updateObject(Invoice $invoice): void {
        if ($invoice->isLegacy()) return;

        $success = $this->update(
            sprintf(
                'UPDATE invoices SET status = ?, payment_method = ?, date_paid = %s WHERE invoice_id = ?',
                $invoice->getData('datePaid') ? $this->datetimeToDB($invoice->getData('datePaid')) : 'NULL'
            ),
            [$invoice->getStatus(), $invoice->getData('paymentMethod'), $invoice->getInvoiceId()]
        );

        if (!$success) {
            throw new \Exception("Gagal melakukan UPDATE pada invoices ID: " . $invoice->getInvoiceId());
        }
    }
    
    /**
     * Menghapus tagihan berdasarkan ID.
     * Murni ranah DAO yang bersentuhan dengan SQL.
     * @param int $invoiceId
     * @return bool
     */
    public function deleteInvoiceById(int $invoiceId): bool {
        return $this->update(
            'DELETE FROM invoices WHERE invoice_id = ?',
            [(int) $invoiceId]
        );
    }

    /**
     * Mengekstrak invoice_number dan invoice_code lama dari article_settings
     * @param int|null $articleId
     * @return array
     */
    private function _getLegacyInvoiceIdentity(?int $articleId): array {
        $identity = ['number' => null, 'code' => null];
        if (!$articleId || $articleId <= 0) return $identity;

        $result = $this->retrieve(
            "SELECT setting_name, setting_value FROM article_settings WHERE article_id = ? AND setting_name IN ('wizdam_invoice_number', 'wizdam_invoice_code')",
            [(int) $articleId]
        );

        while ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if ($row['setting_name'] === 'wizdam_invoice_number') $identity['number'] = $row['setting_value'];
            if ($row['setting_name'] === 'wizdam_invoice_code') $identity['code'] = $row['setting_value'];
            $result->MoveNext();
        }
        if ($result) $result->Close();

        return $identity;
    }

    /**
     * Migration function to transfer legacy queued payments from the queued_payments table to invoices.
     * Processes in batches and returns logs for each batch.
     * @param int $limit
     * @param int $offset
     * @return array ['is_done' => bool, 'processed' => int, 'logs' => array]
     */
    public function migrateLegacyQueuedPayments(int $limit, int $offset): array {
        $logs = [];
        $processed = 0;
        
        $result = $this->retrieveLimit('SELECT * FROM queued_payments ORDER BY queued_payment_id ASC', [], $limit, $offset);
        $rows = [];
        while ($result && !$result->EOF) {
            $rows[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        if ($result) $result->Close();

        if (empty($rows)) return ['is_done' => true, 'processed' => 0, 'logs' => []];

        foreach ($rows as $row) {
            $paymentObj = @unserialize($row['payment_data']);
            $qId = $row['queued_payment_id'];
            
            if (!$paymentObj) continue;

            $userId = method_exists($paymentObj, 'getUserId') ? (int) $paymentObj->getUserId() : 0;
            $journalId = method_exists($paymentObj, 'getContextId') ? (int) $paymentObj->getContextId() : (method_exists($paymentObj, 'getJournalId') ? (int) $paymentObj->getJournalId() : 0);
            $assocId = method_exists($paymentObj, 'getAssocId') ? (int) $paymentObj->getAssocId() : null;
            $amount = method_exists($paymentObj, 'getAmount') ? (float) $paymentObj->getAmount() : 0;
            $currencyCode = method_exists($paymentObj, 'getCurrencyCode') ? $paymentObj->getCurrencyCode() : null;
            $legacyType = method_exists($paymentObj, 'getType') ? $paymentObj->getType() : null;

            if ($userId === 0 || $journalId === 0) continue;

            $feeType = $this->mapLegacyFeeType($legacyType);
            $dateBilled = $row['date_created'];

            $check = $this->retrieve(
                sprintf('SELECT invoice_id FROM invoices WHERE journal_id = ? AND user_id = ? AND fee_type = ? AND status = ? AND date_billed = %s', $this->datetimeToDB($dateBilled)),
                [$journalId, $userId, $feeType, 'QUEUED']
            );
            
            if ($check && $check->RecordCount() > 0) {
                $logs[] = ['type' => 'skip', 'msg' => "[SKIP] Queued ID {$qId}: Sudah dimigrasi sebelumnya."];
                continue;
            }

            try {
                // [WIZDAM] Ekstrak identitas faktur dari article_settings
                $identity = $this->_getLegacyInvoiceIdentity($assocId);
                
                // JEBAKAN ADODB: Cek apakah update mengembalikan false
                $insertSuccess = $this->update(
                    sprintf(
                        'INSERT INTO invoices (journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, date_billed, date_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, %s, NULL)',
                        $this->datetimeToDB($dateBilled)
                    ),
                    [$journalId, $userId, $assocId, $identity['number'], $identity['code'], $feeType, $amount, $currencyCode, 'QUEUED']
                );

                if (!$insertSuccess) {
                    throw new \Exception("Query ditolak oleh MySQL.");
                }

                $processed++;
                $logs[] = ['type' => 'success', 'msg' => "[OK] Queued ID {$qId} -> Berhasil Migrasi."];
            } catch (\Throwable $e) {
                $logs[] = ['type' => 'error', 'msg' => "[FATAL] DB Error (Queued ID {$qId}): " . $e->getMessage()];
            }
        }
        return ['is_done' => false, 'processed' => $processed, 'logs' => $logs];
    }

    /**
     * Migration function to transfer legacy completed payments from the completed_payments table to invoices.
     * Processes in batches and returns logs for each batch.
     * @param int $limit
     * @param int $offset
     * @return array ['is_done' => bool, 'processed' => int, 'logs' => array]
     */
    public function migrateLegacyCompletedPayments(int $limit, int $offset): array {
        $logs = [];
        $processed = 0;
        
        $result = $this->retrieveLimit('SELECT * FROM completed_payments ORDER BY completed_payment_id ASC', [], $limit, $offset);
        $rows = [];
        while ($result && !$result->EOF) {
            $rows[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        if ($result) $result->Close();

        if (empty($rows)) return ['is_done' => true, 'processed' => 0, 'logs' => []];

        foreach ($rows as $row) {
            $journalId = (int) $row['journal_id'];
            $userId = (int) $row['user_id'];
            $feeType = $this->mapLegacyFeeType($row['payment_type']);
            $dateBilled = $row['timestamp'];
            $cId = $row['completed_payment_id'];

            if ($userId === 0 || $journalId === 0) continue;

            $check = $this->retrieve(
                sprintf('SELECT invoice_id FROM invoices WHERE journal_id = ? AND user_id = ? AND fee_type = ? AND status = ? AND date_billed = %s', $this->datetimeToDB($dateBilled)),
                [$journalId, $userId, $feeType, 'PAID']
            );
            
            if ($check && $check->RecordCount() > 0) {
                $logs[] = ['type' => 'skip', 'msg' => "[SKIP] Completed ID {$cId}: Sudah dimigrasi sebelumnya."];
                continue;
            }

            try {
                // [WIZDAM] Ekstrak identitas faktur dari article_settings
                $assocId = (int)$row['assoc_id'];
                $identity = $this->_getLegacyInvoiceIdentity($assocId);
                
                $insertSuccess = $this->update(
                    sprintf(
                        'INSERT INTO invoices (journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, payment_method, date_billed, date_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s)',
                        $this->datetimeToDB($dateBilled), $this->datetimeToDB($dateBilled)
                    ),
                    [
                        $journalId, $userId, (int)$row['assoc_id'], $identity['number'], $identity['code'], $feeType, (float)$row['amount'], 
                        $row['currency_code_alpha'], 'PAID', $row['payment_method_plugin_name']
                    ]
                );

                if (!$insertSuccess) {
                    throw new \Exception("Query ditolak oleh MySQL.");
                }

                $processed++;
                $logs[] = ['type' => 'success', 'msg' => "[OK] Completed ID {$cId} -> Berhasil Migrasi."];
            } catch (\Throwable $e) {
                $logs[] = ['type' => 'error', 'msg' => "[FATAL] DB Error (Completed ID {$cId}): " . $e->getMessage()];
            }
        }
        return ['is_done' => false, 'processed' => $processed, 'logs' => $logs];
    }

    /**
     * Helper function to map legacy payment types to current fee types.
     * @param mixed $legacyType
     * @return string
     */
    private function mapLegacyFeeType($legacyType): string {
        switch ((int)$legacyType) {
            case 1: return 'PUBLICATION';
            case 2: return 'FAST_TRACK';
            case 3: return 'MEMBERSHIP';
            case 4: return 'DONATION';
            case 5: return 'SUBMISSION';
            default: return (is_string($legacyType) && !empty($legacyType)) ? strtoupper($legacyType) : 'OTHER_FEE';
        }
    }
}
?>
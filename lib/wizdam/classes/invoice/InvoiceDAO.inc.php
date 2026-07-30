<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/invoice/InvoiceDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class InvoiceDAO
 * 
 * @brief Operations for retrieving and modifying Invoice objects.
 * Memiliki fitur Legacy Bridge ke completed_payments untuk baris yang BELUM
 * dimigrasi (dibaca lewat _fromLegacyRow(), bagian dari jalur tampilan yang
 * aktif). Logika PEMINDAHAN data legacy sendiri (copy/move) TIDAK ADA di sini
 * -- lihat InvoiceLegacyMigrationDAO.
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
        $invoice->setData('legacySourceTable', $row['legacy_source_table'] ?? null);
        $invoice->setData('legacySourceId', $row['legacy_source_id'] ?? null);
        // [BARU] Kode referensi transfer bank + nama bank yang dipakai
        // (Tahap Konfirmasi Transfer) -- ditampilkan di daftar konfirmasi
        // staf/Journal Manager untuk dicocokkan dengan mutasi rekening.
        $invoice->setData('transferReference', $row['transfer_reference'] ?? null);
        $invoice->setData('transferBank', $row['transfer_bank'] ?? null);
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

        $legacyResult = $this->retrieve(
            "SELECT cp.* FROM completed_payments cp
             LEFT JOIN invoices i ON i.legacy_source_table = 'completed_payments' AND i.legacy_source_id = cp.completed_payment_id
             WHERE cp.completed_payment_id = ? AND i.invoice_id IS NULL",
            [(int) $invoiceId]
        );
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

        $legacyResult = $this->retrieve(
            "SELECT cp.* FROM completed_payments cp
             LEFT JOIN invoices i ON i.legacy_source_table = 'completed_payments' AND i.legacy_source_id = cp.completed_payment_id
             WHERE cp.user_id = ? AND i.invoice_id IS NULL
             ORDER BY cp.timestamp DESC",
            [(int) $userId]
        );
        while ($legacyResult && !$legacyResult->EOF) {
            $invoices[] = $this->_fromLegacyRow($legacyResult->GetRowAssoc(false));
            $legacyResult->MoveNext();
        }
        if ($legacyResult) $legacyResult->Close();
        return $invoices;
    }

    /**
     * Get invoice by submission id. Pola pengecualian legacy sama seperti getByUserId().
     * @param int $submissionId
     * @return Invoice[]
     */
    public function getBySubmissionId(int $submissionId): array {
        $invoices = [];
        $result = $this->retrieve('SELECT * FROM invoices WHERE submission_id = ? ORDER BY date_billed DESC', [(int) $submissionId]);
        while ($result && !$result->EOF) {
            $invoices[] = $this->_fromRow($result->GetRowAssoc(false));
            $result->MoveNext();
        }
        if ($result) $result->Close();

        $legacyResult = $this->retrieve(
            "SELECT cp.* FROM completed_payments cp
             LEFT JOIN invoices i ON i.legacy_source_table = 'completed_payments' AND i.legacy_source_id = cp.completed_payment_id
             WHERE cp.assoc_id = ? AND i.invoice_id IS NULL
             ORDER BY cp.timestamp DESC",
            [(int) $submissionId]
        );
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
                'INSERT INTO invoices (journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, legacy_source_table, legacy_source_id, date_billed) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s)',
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
                $invoice->getStatus(),
                $invoice->getData('legacySourceTable'),
                $invoice->getData('legacySourceId')
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
     * Find or Create Unpaid InvoiceId. Dipakai OJSPaymentManager::createQueuedPayment()
     * untuk SEMUA 10 tipe pembayaran.
     *
     * [PRINSIP] TIDAK PERNAH memakai journal_id untuk mencocokkan baris -- fork ini
     * sengaja tidak mengurung aktivitas pengguna dalam konteks jurnal.
     * - Jika assocId > 0 (identifier unik global): cocokkan lewat submission_id + fee_type.
     * - Jika assocId = 0 (MEMBERSHIP/DONATION): cocokkan lewat user_id + fee_type.
     */
    public function findOrCreateUnpaidInvoiceId(int $journalId, int $userId, int $articleId, string $feeType, float $amount, string $currencyCode, string $invoiceNumber, string $invoiceCode): int {
        if ($articleId > 0) {
            $result = $this->retrieve(
                'SELECT invoice_id, amount FROM invoices WHERE submission_id = ? AND fee_type = ? AND status = ?',
                [$articleId, $feeType, Invoice::STATUS_UNPAID]
            );
        } else {
            $result = $this->retrieve(
                'SELECT invoice_id, amount FROM invoices WHERE user_id = ? AND (submission_id IS NULL OR submission_id = 0) AND fee_type = ? AND status = ? AND legacy_source_table IS NULL',
                [$userId, $feeType, Invoice::STATUS_UNPAID]
            );
        }

        if ($result && $result->RecordCount() > 0) {
            $row = $result->GetRowAssoc(false);
            $result->Close();
            $existingInvoiceId = (int) $row['invoice_id'];

            if (abs((float) $row['amount'] - $amount) > 0.0001) {
                $this->update('UPDATE invoices SET amount = ? WHERE invoice_id = ?', [$amount, $existingInvoiceId]);
            }

            return $existingInvoiceId;
        }
        if ($result) $result->Close();

        $invoice = new Invoice();
        $invoice->setData('journalId', $journalId);
        $invoice->setUserId($userId);
        $invoice->setData('submissionId', $articleId);
        $invoice->setData('invoiceNumber', $invoiceNumber);
        $invoice->setData('invoiceCode', $invoiceCode);
        $invoice->setData('feeType', $feeType);
        $invoice->setData('amount', $amount);
        $invoice->setData('currencyCode', $currencyCode);
        $invoice->setData('status', Invoice::STATUS_UNPAID);

        return $this->insertObject($invoice);
    }

    /**
     * [BARU] Cek cepat apakah kode referensi transfer sudah dipakai --
     * untuk validasi awal yang ramah pengguna. BUKAN satu-satunya jaminan
     * keunikan; itu tetap tanggung jawab UNIQUE INDEX di database
     * (uniq_invoices_transfer_reference), menutup celah race condition
     * kalau dua orang submit kode identik nyaris bersamaan.
     * @param string $referenceCode
     * @return bool
     */
    public function isTransferReferenceUsed(string $referenceCode): bool {
        $result = $this->retrieve('SELECT invoice_id FROM invoices WHERE transfer_reference = ?', [$referenceCode]);
        $found = ($result && $result->RecordCount() > 0);
        if ($result) $result->Close();
        return $found;
    }

    /**
     * [BARU] Menyimpan kode referensi transfer bank + nama bank yang dipakai
     * (Tahap Konfirmasi Transfer). Keunikan kode DIJAMIN oleh UNIQUE INDEX
     * database -- pengecekan isTransferReferenceUsed() di atas cuma UX
     * cepat, bukan satu-satunya penjamin.
     * @param int $invoiceId
     * @param string $referenceCode
     * @param string $bankName Nama bank yang dipilih pengguna (opsional, informasional)
     * @return bool false kalau kode sudah dipakai invoice lain, invoice
     * tidak UNPAID, atau constraint database gagal.
     */
    public function saveTransferReference(int $invoiceId, string $referenceCode, string $bankName = ''): bool {
        if ($this->isTransferReferenceUsed($referenceCode)) {
            return false;
        }

        try {
            $success = $this->update(
                'UPDATE invoices SET transfer_reference = ?, transfer_bank = ?, payment_method = ? WHERE invoice_id = ? AND status = ?',
                [$referenceCode, $bankName, 'BankTransferPending', $invoiceId, Invoice::STATUS_UNPAID]
            );
            return (bool) $success;
        } catch (\Throwable $e) {
            // Constraint UNIQUE gagal di database (race condition -- dua
            // submit nyaris bersamaan lolos pengecekan awal). Ini KEGAGALAN
            // YANG DIHARAPKAN (kode memang sudah dipakai), bukan error
            // tak terduga yang perlu merambat lebih jauh.
            error_log('WIZDAM saveTransferReference: ' . $e->getMessage());
            return false;
        }
    }
    
}
?>
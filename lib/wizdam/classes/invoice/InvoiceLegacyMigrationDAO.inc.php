<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/invoice/InvoiceLegacyMigrationDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class InvoiceLegacyMigrationDAO
 * 
 * @brief Menerjemahkan data legacy (queued_payments/completed_payments) ke
 * tabel invoices. Dipakai HANYA oleh tools/copyMigrateInvoices.php,
 * tools/moveMigrateInvoices.php, dan tools/migrateInvoicesAjax.php.
 */

import('lib.pkp.classes.db.DAO');
import('lib.pkp.classes.db.DBResultRange');
import('lib.wizdam.classes.invoice.Invoice');
import('classes.payment.ojs.OJSQueuedPayment');
import('classes.payment.QueuedPayment');

class InvoiceLegacyMigrationDAO extends DAO {

    /**
     * [LUMERA] Pastikan kolom legacy_source_table/legacy_source_id sudah ada
     * di tabel invoices sebelum migrasi/wipe dijalankan. Tanpa pengecekan
     * ini, kegagalan tampil sebagai error MySQL mentah ("Unknown column")
     * yang membingungkan -- sekarang tampil sebagai pesan jelas berisi
     * ALTER TABLE yang perlu dijalankan.
     * @throws \Exception
     */
    public function ensureSchemaReady(): void {
        $result = $this->retrieve("SHOW COLUMNS FROM invoices LIKE 'legacy_source_table'");
        $hasColumn = ($result && $result->RecordCount() > 0);
        if ($result) $result->Close();

        if (!$hasColumn) {
            throw new \Exception(
                "Kolom 'legacy_source_table'/'legacy_source_id' belum ada di tabel invoices. " .
                "Jalankan ALTER TABLE berikut sebelum migrasi:\n" .
                "ALTER TABLE invoices ADD COLUMN legacy_source_table VARCHAR(64) DEFAULT NULL, " .
                "ADD COLUMN legacy_source_id BIGINT(20) DEFAULT NULL, " .
                "ADD UNIQUE INDEX uniq_invoices_legacy_source (legacy_source_table, legacy_source_id);"
            );
        }
    }

    /**
     * Cek apakah baris legacy ini sudah pernah dimigrasi.
     */
    private function _isMigrated(string $sourceTable, int $sourceId): bool {
        $result = $this->retrieve(
            'SELECT invoice_id FROM invoices WHERE legacy_source_table = ? AND legacy_source_id = ?',
            [$sourceTable, $sourceId]
        );
        $found = ($result && $result->RecordCount() > 0);
        if ($result) $result->Close();
        return $found;
    }

    /**
     * Mengekstrak invoice_number dan invoice_code lama dari article_settings.
     * Hanya berlaku untuk tipe yang punya assocId berupa articleId (SUBMISSION/
     * FAST_TRACK/PUBLICATION) -- tipe lain akan selalu mendapat null dari sini
     * dan ditangani oleh _generateLegacyStableNumber() di bawah.
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
     * [LUMERA] Generate nomor invoice STABIL untuk tipe yang tidak punya
     * skema fallback stabil di InvoiceService::getInvoiceSummary() (yaitu
     * SEMUA tipe KECUALI SUBMISSION/FAST_TRACK/PUBLICATION, yang sudah punya
     * fallback berbasis ISSN+articleId yang tidak berubah-ubah).
     *
     * Tanpa ini, invoice_number tersimpan NULL selamanya, dan setiap kali
     * invoice ditampilkan, nomornya DIHITUNG ULANG dari tanggal HARI ITU
     * (date('ymd') di InvoiceService::generateInvoiceNumber()) -- bukan
     * tanggal transaksi asli. Nomor jadi berbeda setiap hari dilihat.
     *
     * Di sini kita pakai TANGGAL TRANSAKSI ASLI ($dateBilled), bukan hari
     * ini, supaya nomornya bermakna dan sekali dipersist, tidak berubah lagi.
     * @param string $feeType
     * @param int $userId
     * @param string $dateBilled Tanggal transaksi asli dari data legacy
     * @return array ['number' => string, 'code' => string]
     */
    private function _generateLegacyStableNumber(string $feeType, int $userId, string $dateBilled, int $sourceId): array {
        $prefixMap = [
            'MEMBERSHIP'            => 'MBR',
            'RENEW_SUBSCRIPTION'    => 'SUB',
            'PURCHASE_ARTICLE'      => 'ART',
            'DONATION'              => 'DON',
            'PURCHASE_SUBSCRIPTION' => 'PSB',
            'PURCHASE_ISSUE'        => 'ISS',
            'GIFT'                  => 'GFT',
        ];
        $prefix = $prefixMap[strtoupper($feeType)] ?? 'INV';
        $datePart = date('ymd', strtotime($dateBilled));

        return [
            'number' => $prefix . '-' . $userId . '-' . $datePart . '-' . $sourceId,
            'code'   => $prefix . '#' . str_pad((string) $userId, 7, '0', STR_PAD_LEFT) . '-' . $sourceId,
        ];
    }

    /**
     * Helper terpusat: ambil identitas nomor invoice untuk satu baris migrasi.
     * Mengembalikan identity dari article_settings kalau tersedia (SUBMISSION/
     * FAST_TRACK/PUBLICATION), atau nomor stabil buatan sendiri untuk tipe
     * lain yang tidak punya fallback stabil di tampilan.
     */
    private function _resolveInvoiceIdentity(?int $assocId, string $feeType, int $userId, string $dateBilled, int $sourceId): array {
        $identity = $this->_getLegacyInvoiceIdentity($assocId);

        $isCoreArticleType = in_array(strtoupper($feeType), ['SUBMISSION', 'FAST_TRACK', 'PUBLICATION'], true);

        if (empty($identity['number']) && !$isCoreArticleType) {
            $identity = $this->_generateLegacyStableNumber($feeType, $userId, $dateBilled, $sourceId);
        }

        return $identity;
    }

    /**
     * Helper function to map legacy payment types to current fee types.
     * @param mixed $legacyType
     * @return string
     */
    private function mapLegacyFeeType($legacyType): string {
        switch ((int)$legacyType) {
            case 1:  return 'MEMBERSHIP';
            case 2:  return 'RENEW_SUBSCRIPTION';
            case 3:  return 'PURCHASE_ARTICLE';
            case 4:  return 'DONATION';
            case 5:  return 'SUBMISSION';
            case 6:  return 'FAST_TRACK';
            case 7:  return 'PUBLICATION';
            case 8:  return 'PURCHASE_SUBSCRIPTION';
            case 9:  return 'PURCHASE_ISSUE';
            case 16: return 'GIFT';
            default: return (is_string($legacyType) && !empty($legacyType) && !is_numeric($legacyType)) ? strtoupper($legacyType) : 'OTHER_FEE';
        }
    }

    /**
     * [LUMERA COPY] Menyalin legacy queued payments ke invoices. 
     * TIDAK PERNAH menghapus baris sumber.
     * @param int $limit
     * @param int $afterId
     * @return array ['is_done' => bool, 'processed' => int, 'logs' => array, 'next_cursor' => int]
     */
    public function copyLegacyQueuedPayments(int $limit, int $afterId): array {
        $this->ensureSchemaReady();

        $logs = [];
        $processed = 0;
        $lastSeenId = $afterId;

        $result = $this->retrieveLimit(
            'SELECT * FROM queued_payments WHERE queued_payment_id > ? ORDER BY queued_payment_id ASC',
            [$afterId], $limit, 0
        );
        $rows = [];
        while ($result && !$result->EOF) {
            $rows[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        if ($result) $result->Close();

        if (empty($rows)) return ['is_done' => true, 'processed' => 0, 'logs' => [], 'next_cursor' => $afterId];

        foreach ($rows as $row) {
            $qId = (int) $row['queued_payment_id'];
            $lastSeenId = max($lastSeenId, $qId);

            if ($this->_isMigrated('queued_payments', $qId)) {
                $logs[] = ['type' => 'skip', 'msg' => "[SKIP] Queued ID {$qId}: sudah punya salinan di invoices."];
                continue;
            }

            $paymentObj = @unserialize($row['payment_data']);
            if (!$paymentObj) {
                $logs[] = ['type' => 'error', 'msg' => "[SKIP-CORRUPT] Queued ID {$qId}: payment_data tidak bisa dibaca."];
                continue;
            }

            if (method_exists($paymentObj, 'getCheckoutPayload') && !empty($paymentObj->getCheckoutPayload())) {
                $logs[] = ['type' => 'skip', 'msg' => "[SKIP] Queued ID {$qId}: sesi checkout aktif, bukan payment legacy."];
                continue;
            }

            $userId = method_exists($paymentObj, 'getUserId') ? (int) $paymentObj->getUserId() : 0;
            $journalId = method_exists($paymentObj, 'getContextId') ? (int) $paymentObj->getContextId() : (method_exists($paymentObj, 'getJournalId') ? (int) $paymentObj->getJournalId() : 0);
            $assocId = method_exists($paymentObj, 'getAssocId') ? (int) $paymentObj->getAssocId() : null;
            $amount = method_exists($paymentObj, 'getAmount') ? (float) $paymentObj->getAmount() : 0;
            $currencyCode = method_exists($paymentObj, 'getCurrencyCode') ? $paymentObj->getCurrencyCode() : null;
            $legacyType = method_exists($paymentObj, 'getType') ? $paymentObj->getType() : null;

            if ($userId === 0 || $journalId === 0) {
                $logs[] = ['type' => 'error', 'msg' => "[SKIP-INVALID] Queued ID {$qId}: userId/journalId tidak valid."];
                continue;
            }

            $feeType = $this->mapLegacyFeeType($legacyType);
            $dateBilled = $row['date_created'];

            try {
                $identity = $this->_resolveInvoiceIdentity($assocId, $feeType, $userId, $dateBilled, $qId);

                $insertSuccess = $this->update(
                    sprintf(
                        'INSERT INTO invoices (journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, legacy_source_table, legacy_source_id, date_billed, date_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s, NULL)',
                        $this->datetimeToDB($dateBilled)
                    ),
                    [$journalId, $userId, $assocId, $identity['number'], $identity['code'], $feeType, $amount, $currencyCode, Invoice::STATUS_UNPAID, 'queued_payments', $qId]
                );
                if (!$insertSuccess) throw new \Exception("Query ditolak oleh MySQL.");

                $newInvoiceId = (int) $this->getInsertId();
                $processed++;
                $logs[] = ['type' => 'success', 'msg' => "[OK] Queued ID {$qId} -> Invoice #{$newInvoiceId} (baris asal TETAP ADA)."];
            } catch (\Throwable $e) {
                $logs[] = ['type' => 'error', 'msg' => "[FATAL] DB Error (Queued ID {$qId}): " . $e->getMessage()];
            }
        }

        return ['is_done' => count($rows) < $limit, 'processed' => $processed, 'logs' => $logs, 'next_cursor' => $lastSeenId];
    }

    /**
     * [LUMERA COPY] Menyalin legacy completed payments ke invoices. 
     * TIDAK PERNAH menghapus baris sumber.
     * @param int $limit
     * @param int $afterId
     * @return array ['is_done' => bool, 'processed' => int, 'logs' => array, 'next_cursor' => int]
     */
    public function copyLegacyCompletedPayments(int $limit, int $afterId): array {
        $this->ensureSchemaReady();

        $logs = [];
        $processed = 0;
        $lastSeenId = $afterId;

        $result = $this->retrieveLimit(
            'SELECT * FROM completed_payments WHERE completed_payment_id > ? ORDER BY completed_payment_id ASC',
            [$afterId], $limit, 0
        );
        $rows = [];
        while ($result && !$result->EOF) {
            $rows[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        if ($result) $result->Close();

        if (empty($rows)) return ['is_done' => true, 'processed' => 0, 'logs' => [], 'next_cursor' => $afterId];

        foreach ($rows as $row) {
            $cId = (int) $row['completed_payment_id'];
            $lastSeenId = max($lastSeenId, $cId);

            if ($this->_isMigrated('completed_payments', $cId)) {
                $logs[] = ['type' => 'skip', 'msg' => "[SKIP] Completed ID {$cId}: sudah punya salinan di invoices."];
                continue;
            }

            $journalId = (int) $row['journal_id'];
            $userId = (int) $row['user_id'];

            if ($userId === 0 || $journalId === 0) {
                $logs[] = ['type' => 'error', 'msg' => "[SKIP-INVALID] Completed ID {$cId}: userId/journalId tidak valid."];
                continue;
            }

            $feeType = $this->mapLegacyFeeType($row['payment_type']);
            $dateBilled = $row['timestamp'];

            try {
                $assocId = (int) $row['assoc_id'];
                $identity = $this->_resolveInvoiceIdentity($assocId, $feeType, $userId, $dateBilled, $cId);

                $insertSuccess = $this->update(
                    sprintf(
                        'INSERT INTO invoices (journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, payment_method, legacy_source_table, legacy_source_id, date_billed, date_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s)',
                        $this->datetimeToDB($dateBilled), $this->datetimeToDB($dateBilled)
                    ),
                    [
                        $journalId, $userId, $assocId, $identity['number'], $identity['code'], $feeType, (float) $row['amount'],
                        $row['currency_code_alpha'], Invoice::STATUS_PAID, $row['payment_method_plugin_name'], 'completed_payments', $cId
                    ]
                );
                if (!$insertSuccess) throw new \Exception("Query ditolak oleh MySQL.");

                $newInvoiceId = (int) $this->getInsertId();
                $processed++;
                $logs[] = ['type' => 'success', 'msg' => "[OK] Completed ID {$cId} -> Invoice #{$newInvoiceId} (baris asal TETAP ADA)."];
            } catch (\Throwable $e) {
                $logs[] = ['type' => 'error', 'msg' => "[FATAL] DB Error (Completed ID {$cId}): " . $e->getMessage()];
            }
        }

        return ['is_done' => count($rows) < $limit, 'processed' => $processed, 'logs' => $logs, 'next_cursor' => $lastSeenId];
    }

    /**
     * [LUMERA MOVE] Memindahkan legacy queued payments ke invoices. 
     * Baris sumber DIHAPUS setelah berhasil disalin. DESTRUKTIF.
     * @param int $limit
     * @param int $afterId
     * @return array ['is_done' => bool, 'processed' => int, 'logs' => array, 'next_cursor' => int]
     */
    public function moveLegacyQueuedPayments(int $limit, int $afterId): array {
        $this->ensureSchemaReady();

        $logs = [];
        $processed = 0;
        $lastSeenId = $afterId;

        $result = $this->retrieveLimit(
            'SELECT * FROM queued_payments WHERE queued_payment_id > ? ORDER BY queued_payment_id ASC',
            [$afterId], $limit, 0
        );
        $rows = [];
        while ($result && !$result->EOF) {
            $rows[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        if ($result) $result->Close();

        if (empty($rows)) return ['is_done' => true, 'processed' => 0, 'logs' => [], 'next_cursor' => $afterId];

        foreach ($rows as $row) {
            $qId = (int) $row['queued_payment_id'];
            $lastSeenId = max($lastSeenId, $qId);

            if ($this->_isMigrated('queued_payments', $qId)) {
                $this->update('DELETE FROM queued_payments WHERE queued_payment_id = ?', [$qId]);
                $logs[] = ['type' => 'skip', 'msg' => "[SKIP] Queued ID {$qId}: sudah pernah dipindah, baris sisa dibersihkan."];
                continue;
            }

            $paymentObj = @unserialize($row['payment_data']);
            if (!$paymentObj) {
                $logs[] = ['type' => 'error', 'msg' => "[SKIP-CORRUPT] Queued ID {$qId}: payment_data tidak bisa dibaca, dilewati TANPA dihapus."];
                continue;
            }

            if (method_exists($paymentObj, 'getCheckoutPayload') && !empty($paymentObj->getCheckoutPayload())) {
                $logs[] = ['type' => 'skip', 'msg' => "[SKIP] Queued ID {$qId}: sesi checkout aktif, dilewati TANPA dihapus."];
                continue;
            }

            $userId = method_exists($paymentObj, 'getUserId') ? (int) $paymentObj->getUserId() : 0;
            $journalId = method_exists($paymentObj, 'getContextId') ? (int) $paymentObj->getContextId() : (method_exists($paymentObj, 'getJournalId') ? (int) $paymentObj->getJournalId() : 0);
            $assocId = method_exists($paymentObj, 'getAssocId') ? (int) $paymentObj->getAssocId() : null;
            $amount = method_exists($paymentObj, 'getAmount') ? (float) $paymentObj->getAmount() : 0;
            $currencyCode = method_exists($paymentObj, 'getCurrencyCode') ? $paymentObj->getCurrencyCode() : null;
            $legacyType = method_exists($paymentObj, 'getType') ? $paymentObj->getType() : null;

            if ($userId === 0 || $journalId === 0) {
                $logs[] = ['type' => 'error', 'msg' => "[SKIP-INVALID] Queued ID {$qId}: userId/journalId tidak valid, dilewati TANPA dihapus."];
                continue;
            }

            $feeType = $this->mapLegacyFeeType($legacyType);
            $dateBilled = $row['date_created'];

            try {
                $identity = $this->_resolveInvoiceIdentity($assocId, $feeType, $userId, $dateBilled, $qId);

                $insertSuccess = $this->update(
                    sprintf(
                        'INSERT INTO invoices (journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, legacy_source_table, legacy_source_id, date_billed, date_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s, NULL)',
                        $this->datetimeToDB($dateBilled)
                    ),
                    [$journalId, $userId, $assocId, $identity['number'], $identity['code'], $feeType, $amount, $currencyCode, Invoice::STATUS_UNPAID, 'queued_payments', $qId]
                );
                if (!$insertSuccess) throw new \Exception("Query ditolak oleh MySQL.");

                $newInvoiceId = (int) $this->getInsertId();
                $this->update('DELETE FROM queued_payments WHERE queued_payment_id = ?', [$qId]);

                $processed++;
                $logs[] = ['type' => 'success', 'msg' => "[OK] Queued ID {$qId} -> Invoice #{$newInvoiceId} (baris asal DIHAPUS)."];
            } catch (\Throwable $e) {
                $logs[] = ['type' => 'error', 'msg' => "[FATAL] DB Error (Queued ID {$qId}): " . $e->getMessage()];
            }
        }

        return ['is_done' => count($rows) < $limit, 'processed' => $processed, 'logs' => $logs, 'next_cursor' => $lastSeenId];
    }

    /**
     * [LUMERA MOVE] Memindahkan legacy completed payments ke invoices. 
     * Baris sumber DIHAPUS setelah berhasil disalin. DESTRUKTIF.
     * @param int $limit
     * @param int $afterId
     * @return array ['is_done' => bool, 'processed' => int, 'logs' => array, 'next_cursor' => int]
     */
    public function moveLegacyCompletedPayments(int $limit, int $afterId): array {
        $this->ensureSchemaReady();

        $logs = [];
        $processed = 0;
        $lastSeenId = $afterId;

        $result = $this->retrieveLimit(
            'SELECT * FROM completed_payments WHERE completed_payment_id > ? ORDER BY completed_payment_id ASC',
            [$afterId], $limit, 0
        );
        $rows = [];
        while ($result && !$result->EOF) {
            $rows[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        if ($result) $result->Close();

        if (empty($rows)) return ['is_done' => true, 'processed' => 0, 'logs' => [], 'next_cursor' => $afterId];

        foreach ($rows as $row) {
            $cId = (int) $row['completed_payment_id'];
            $lastSeenId = max($lastSeenId, $cId);

            if ($this->_isMigrated('completed_payments', $cId)) {
                $this->update('DELETE FROM completed_payments WHERE completed_payment_id = ?', [$cId]);
                $logs[] = ['type' => 'skip', 'msg' => "[SKIP] Completed ID {$cId}: sudah pernah dipindah, baris sisa dibersihkan."];
                continue;
            }

            $journalId = (int) $row['journal_id'];
            $userId = (int) $row['user_id'];

            if ($userId === 0 || $journalId === 0) {
                $logs[] = ['type' => 'error', 'msg' => "[SKIP-INVALID] Completed ID {$cId}: userId/journalId tidak valid, dilewati TANPA dihapus."];
                continue;
            }

            $feeType = $this->mapLegacyFeeType($row['payment_type']);
            $dateBilled = $row['timestamp'];

            try {
                $assocId = (int) $row['assoc_id'];
                $identity = $this->_resolveInvoiceIdentity($assocId, $feeType, $userId, $dateBilled, $cId);

                $insertSuccess = $this->update(
                    sprintf(
                        'INSERT INTO invoices (journal_id, user_id, submission_id, invoice_number, invoice_code, fee_type, amount, currency_code, status, payment_method, legacy_source_table, legacy_source_id, date_billed, date_paid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, %s, %s)',
                        $this->datetimeToDB($dateBilled), $this->datetimeToDB($dateBilled)
                    ),
                    [
                        $journalId, $userId, $assocId, $identity['number'], $identity['code'], $feeType, (float) $row['amount'],
                        $row['currency_code_alpha'], Invoice::STATUS_PAID, $row['payment_method_plugin_name'], 'completed_payments', $cId
                    ]
                );
                if (!$insertSuccess) throw new \Exception("Query ditolak oleh MySQL.");

                $newInvoiceId = (int) $this->getInsertId();
                $this->update('DELETE FROM completed_payments WHERE completed_payment_id = ?', [$cId]);

                $processed++;
                $logs[] = ['type' => 'success', 'msg' => "[OK] Completed ID {$cId} -> Invoice #{$newInvoiceId} (baris asal DIHAPUS)."];
            } catch (\Throwable $e) {
                $logs[] = ['type' => 'error', 'msg' => "[FATAL] DB Error (Completed ID {$cId}): " . $e->getMessage()];
            }
        }

        return ['is_done' => count($rows) < $limit, 'processed' => $processed, 'logs' => $logs, 'next_cursor' => $lastSeenId];
    }

}
?>
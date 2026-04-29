<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/LoAService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * @class LoAService
 * @brief Jantung verifikasi LoA. Berhubungan erat dengan InvoiceService untuk
 * memastikan LoA hanya terbit dan terverifikasi jika APC telah LUNAS.
 */

import('lib.wizdam.classes.services.InvoiceService');

class LoAService {
    
    private InvoiceService $invoiceService;

    /**
     * Constructor
     */
    public function __construct() {
        $this->invoiceService = new InvoiceService();
    }

    /**
     * Mendapatkan data LoA untuk publik / verifikasi QR Code.
     * Mengembalikan status yang aman untuk dirender oleh VerifyHandler.
     * @param int $submissionId
     * @return array
     */
    public function getPublicLoASummary(int $submissionId): array {
        $submissionDao = DAORegistry::getDAO('SubmissionDAO');
        $submission = $submissionDao->getById($submissionId);

        if (!$submission) {
            return ['status' => 'NOT_FOUND'];
        }

        // 1. Verifikasi Pelunasan Tagihan
        $paidInvoice = $this->getPaidInvoiceForSubmission($submissionId);

        if (!$paidInvoice) {
            return ['status' => 'PENDING_PAYMENT'];
        }

        // 2. Ambil Tanggal Otentik Keputusan "Accepted" dari Editor
        $dateAccepted = $this->getDateAccepted($submissionId);

        // Fallback (Fail-safe): Jika tidak ada rekam jejak keputusan, gunakan tanggal submit
        if (!$dateAccepted) {
            $dateAccepted = $submission->getDateSubmitted() ?? Core::getCurrentDate();
        }

        // 3. Rakit data otentik LoA
        return [
            'status' => 'VERIFIED',
            'submissionId' => $submission->getId(),
            'title' => $submission->getLocalizedTitle(),
            'abstract' => $submission->getLocalizedAbstract(),
            'authors' => $submission->getAuthorString(),
            'dateAccepted' => $dateAccepted, 
            'journalTitle' => $this->getJournalTitle($submission->getContextId())
        ];
    }

    /**
     * [WIZDAM HELPER] Mengekstrak Tanggal "Accept Submission" secara presisi.
     * Mengurai kerumitan tabel edit_decisions menjadi satu fungsi bersih.
     */
    private function getDateAccepted(int $submissionId): ?string {
        $editDecisionDao = DAORegistry::getDAO('EditDecisionDAO');
        
        // Ambil seluruh riwayat keputusan editor untuk naskah ini
        $decisions = $editDecisionDao->getEditorDecisions($submissionId);
        
        // Konstanta SUBMISSION_EDITOR_DECISION_ACCEPT bernilai 1
        $acceptValue = defined('SUBMISSION_EDITOR_DECISION_ACCEPT') ? SUBMISSION_EDITOR_DECISION_ACCEPT : 1;

        if (is_array($decisions)) {
            // Loop mundur untuk menemukan keputusan Accept
            foreach ($decisions as $decision) {
                if (isset($decision['decision']) && (int) $decision['decision'] === $acceptValue) {
                    return $decision['dateDecided'];
                }
            }
        }

        return null;
    }

    /**
     * Mencari objek Invoice yang berstatus PAID untuk sebuah naskah.
     * @param int $submissionId
     * @return object|null Mengembalikan objek Invoice jika lunas, atau null.
     */
    private function getPaidInvoiceForSubmission(int $submissionId): ?object {
        $invoiceDao = DAORegistry::getDAO('InvoiceDAO');
        $assocType = 256; // ASSOC_TYPE_SUBMISSION

        $invoices = $invoiceDao->getByAssocId($assocType, $submissionId);

        if (!$invoices) return null;

        if (is_array($invoices)) {
            foreach ($invoices as $invoice) {
                if ($invoice->getStatus() === 'PAID') return $invoice;
            }
        } elseif (is_object($invoices) && is_a($invoices, 'DAOResultFactory')) {
            while ($invoice = $invoices->next()) {
                if ($invoice->getStatus() === 'PAID') return $invoice;
            }
        } elseif (is_object($invoices) && method_exists($invoices, 'getStatus')) {
            if ($invoices->getStatus() === 'PAID') return $invoices;
        }

        return null; 
    }

    /**
     * Mendapatkan nama jurnal berdasarkan contextId.
     * @param int $contextId
     * @return string
     */
    private function getJournalTitle(int $contextId): string {
        $contextDao = DAORegistry::getDAO('JournalDAO');
        $journal = $contextDao->getById($contextId);
        
        return $journal ? $journal->getLocalizedName() : 'Wizdam Frontedge Publisher';
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/LoAService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class LoAService
 * 
 * @brief Jantung verifikasi LoA. Berhubungan erat dengan InvoiceService untuk
 * memastikan LoA hanya terbit dan terverifikasi jika APC telah LUNAS.
 */

import('lib.wizdam.classes.services.InvoiceService');
import('lib.wizdam.classes.services.JournalManagerResolver');
import('lib.wizdam.classes.services.PublisherProfileService');

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
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $submission = $articleDao->getArticle($submissionId);

        if (!$submission) {
            return ['status' => 'NOT_FOUND'];
        }

        $paidInvoice = $this->getPaidInvoiceForSubmission($submissionId);
        if (!$paidInvoice) {
            return ['status' => 'PENDING_PAYMENT'];
        }

        $timeline = $articleDao->getEditorialTimeline($submissionId);
        $dateAccepted = $timeline['acceptedDate'] ?? $submission->getDateSubmitted() ?? Core::getCurrentDate();

        [$authorsList, $correspondingAuthor] = $this->buildAuthorsList($submission->getAuthors());

        // Fallback: jika tidak ada penulis yang ditandai primary contact, gunakan submiter naskah
        if (!$correspondingAuthor) {
            $correspondingAuthor = $this->getSubmitterAsCorrespondingAuthor((int) $submission->getUserId());
        }

        $journalId = (int) $submission->getJournalId();

        return [
            'status' => 'VERIFIED',
            'submissionId' => $submission->getId(),
            'title' => $submission->getLocalizedTitle(),
            'abstract' => $submission->getLocalizedAbstract(),
            'sectionTitle' => $submission->getSectionTitle(),
            'dateSubmitted' => $submission->getDateSubmitted(),
            'dateAccepted' => $dateAccepted,

            'authors' => $submission->getAuthorString(),
            'authorsList' => $authorsList,
            'correspondingAuthorName' => $correspondingAuthor['fullName'] ?? '',
            'correspondingAuthorEmail' => $correspondingAuthor['email'] ?? '',

            // [BARU] Editor yang menangani naskah + Journal Manager penanda
            // tangan -- tampil di LoA privat/publik/PDF bersama QR Code.
            'editorNames' => $this->_getHandlingEditorNames($submissionId),
            'managerNames' => (new JournalManagerResolver())->resolve($journalId)['names'],

            'journalTitle' => $this->getJournalTitle($journalId)
        ];
    }

    /**
     * Menyusun daftar penulis terstruktur. Tidak melakukan fallback di sini —
     * fallback ke submiter naskah ditangani oleh caller.
     * @param array $authors Array of Author objects
     * @return array{0: array, 1: array|null} [authorsList, correspondingAuthor]
     */
    private function buildAuthorsList(array $authors): array {
        $authorsList = [];
        $correspondingAuthor = null;

        foreach ($authors as $author) {
            $authorData = [
                'fullName' => $author->getFullName(),
                'email' => $author->getEmail(),
                'affiliation' => $author->getLocalizedAffiliation(),
                'country' => $author->getCountry(),
                'isCorresponding' => (bool) $author->getPrimaryContact(),
            ];
            $authorsList[] = $authorData;

            if ($author->getPrimaryContact()) {
                $correspondingAuthor = $authorData;
            }
        }

        return [$authorsList, $correspondingAuthor];
    }

    /**
     * Mengambil data submiter naskah sebagai fallback corresponding author,
     * ketika tidak ada penulis yang secara eksplisit ditandai primary contact.
     * @param int $userId
     * @return array|null
     */
    private function getSubmitterAsCorrespondingAuthor(int $userId): ?array {
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');
        $submitter = $userDao->getById($userId);
        if (!$submitter) return null;

        return [
            'fullName' => $submitter->getFullName(),
            'email' => $submitter->getEmail(),
            'affiliation' => $submitter->getLocalizedAffiliation(),
            'country' => $submitter->getCountry(),
            'isCorresponding' => true,
        ];
    }

    /**
     * Mencari objek Invoice yang berstatus PAID untuk sebuah naskah.
     * @param int $submissionId
     * @return object|null
     */
    private function getPaidInvoiceForSubmission(int $submissionId): ?object {
        $invoices = $this->invoiceService->getInvoicesBySubmissionId($submissionId);
        foreach ($invoices as $invoice) {
            if ($invoice->getStatus() === 'PAID') return $invoice;
        }
        return null;
    }

    /**
     * [BARU] Mengambil nama editor yang menangani naskah ini (handling/section
     * editor, BUKAN reviewer). Bisa lebih dari satu jika ada co-editor.
     * @param int $submissionId
     * @return string[]
     */
    private function _getHandlingEditorNames(int $submissionId): array {
        /** @var EditAssignmentDAO $editAssignmentDao */
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $assignments = $editAssignmentDao->getEditingSectionEditorAssignmentsByArticleId($submissionId);

        $names = [];
        while ($assignment = $assignments->next()) {
            $editor = $userDao->getById((int) $assignment->getEditorId());
            if ($editor) {
                $names[] = $editor->getFullName();
            }
        }
        return $names;
    }

    /**
     * Mendapatkan nama jurnal berdasarkan contextId.
     * @param int $journalId
     * @return string
     */
    private function getJournalTitle(int $journalId): string {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($journalId);

        if ($journal) {
            return $journal->getLocalizedSetting('name');
        }

        // [FIX] Fallback sebelumnya hardcode "Sangia Frontedge Publisher" --
        // diganti memakai identitas Penerbit yang sudah dikonfigurasi.
        return (new PublisherProfileService())->getProfile()['name'];
    }

    /**
     * [BARU] Daftar semua naskah milik pengguna yang sudah punya LoA (invoice
     * lunas) -- untuk halaman indeks.
     * [SEMENTARA] Status memakai 3 tahap yang SUDAH TERVERIFIKASI solid
     * (Accepted / In Production / Published). Pemisahan Layout vs
     * Proofreading BELUM ditambahkan -- menunggu keputusan Anda.
     * @param int $userId
     * @return array
     */
    public function getLoAIndexForUser(int $userId): array {
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        $submissions = $articleDao->getArticlesByUserId($userId);

        $entries = [];
        foreach ($submissions as $submission) {
            $paidInvoice = $this->getPaidInvoiceForSubmission((int) $submission->getId());
            if (!$paidInvoice) continue;

            $entries[] = [
                'submissionId' => (int) $submission->getId(),
                'title' => $submission->getLocalizedTitle(),
                'statusLabel' => $this->_resolveArticleStageLabel($submission),
                'statusDate' => $this->_resolveArticleStageDate($submission),
            ];
        }

        usort($entries, fn($a, $b) => strtotime($b['statusDate']) <=> strtotime($a['statusDate']));

        return $entries;
    }

    /**
     * HELPER
     * @param object $submission
     * @return string
     */
    private function _resolveArticleStageLabel($submission): string {
        if ($submission->getStatus() == STATUS_PUBLISHED) {
            return __('billing.loa.status.published');
        }
        $copyeditSignoff = $submission->getSignoff('SIGNOFF_COPYEDITING_INITIAL');
        if ($copyeditSignoff && $copyeditSignoff->getDateCompleted()) {
            return __('billing.loa.status.inProduction');
        }
        return __('billing.loa.status.accepted');
    }

    /**
     * HELPER
     * @param object $submission
     * @return string
     */
    private function _resolveArticleStageDate($submission): string {
        if ($submission->getStatus() == STATUS_PUBLISHED && $submission->getDatePublished()) {
            return $submission->getDatePublished();
        }
        $copyeditSignoff = $submission->getSignoff('SIGNOFF_COPYEDITING_INITIAL');
        if ($copyeditSignoff && $copyeditSignoff->getDateCompleted()) {
            return $copyeditSignoff->getDateCompleted();
        }
        return $submission->getDateSubmitted();
    }

}
?>
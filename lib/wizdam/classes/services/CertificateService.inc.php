<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/CertificateService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @class CertificateService
 * 
 * @brief Layanan penyedia data untuk penerbitan Sertifikat (Reviewer, Editor).
 * TIDAK untuk Author -- Author memakai LoA (LoAService).
 */

import('lib.wizdam.classes.services.JournalManagerResolver');

class CertificateService {

    /**
     * Mengambil data lengkap Sertifikat Reviewer berdasarkan ID Penugasan.
     * @param int $reviewId
     * @return array
     * @throws \Exception
     */
    public function getReviewerCertificateData(int $reviewId): array {
        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $reviewAssignment = $reviewAssignmentDao->getReviewAssignmentById($reviewId);

        if (!$reviewAssignment) {
            throw new \Exception('NOT_FOUND');
        }

        // Sertifikat hanya bisa diterbitkan jika review sudah selesai
        if (!$reviewAssignment->getDateCompleted()) {
            throw new \Exception('INCOMPLETE_REVIEW');
        }

        $article = $articleDao->getArticle($reviewAssignment->getSubmissionId());
        $journal = $journalDao->getById($article->getJournalId());
        $reviewer = $userDao->getById($reviewAssignment->getReviewerId());

        // [BARU] Nama Journal Manager penanda tangan -- lihat aturan
        // 1/2/>2 manager di JournalManagerResolver.
        $signatory = (new JournalManagerResolver())->resolve((int) $journal->getId());

        return [
            'type' => 'REVIEWER_CERTIFICATE',
            'reviewId' => $reviewId,
            'submissionId' => $article->getId(),
            'reviewerName' => $reviewer->getFullName(),
            'reviewerAffiliation' => $reviewer->getLocalizedAffiliation(),
            'articleTitle' => $article->getLocalizedTitle(),
            'journalTitle' => $journal->getLocalizedTitle(),
            'dateCompleted' => $reviewAssignment->getDateCompleted(),
            'signatoryNames' => $signatory['names'],
            // Nomor Sertifikat (Bisa di-generate kustom, ini contoh format dinamis)
            'certificateNumber' => 'CERT-REV-' . date('Y', strtotime($reviewAssignment->getDateCompleted())) . '-' . str_pad((string)$reviewId, 5, '0', STR_PAD_LEFT)
        ];
    }

    /**
     * Mengambil data lengkap Sertifikat Editor berdasarkan ID Penugasan Edit.
     * Catatan: edit_assignments tidak punya kolom "selesai" seperti
     * review_assignments (getDateCompleted()) -- di sini dipakai date_notified
     * sebagai syarat minimal keterlibatan aktif yang sudah tercatat resmi.
     * @param int $editId
     * @return array
     * @throws \Exception
     */
    public function getEditorCertificateData(int $editId): array {
        /** @var EditAssignmentDAO $editAssignmentDao */
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        /** @var UserDAO $userDao */
        $userDao = DAORegistry::getDAO('UserDAO');

        $editAssignment = $editAssignmentDao->getEditAssignment($editId);

        if (!$editAssignment) {
            throw new \Exception('NOT_FOUND');
        }

        if (!$editAssignment->getDateNotified()) {
            throw new \Exception('INCOMPLETE_ASSIGNMENT');
        }

        $article = $articleDao->getArticle($editAssignment->getArticleId());
        if (!$article) {
            throw new \Exception('NOT_FOUND');
        }

        $journal = $journalDao->getById($article->getJournalId());
        $editor = $userDao->getById($editAssignment->getEditorId());

        $dateRef = $editAssignment->getDateAssigned() ?: $editAssignment->getDateNotified();

        // [BARU] Nama Journal Manager penanda tangan.
        $signatory = (new JournalManagerResolver())->resolve((int) $journal->getId());

        return [
            'type' => 'EDITOR_CERTIFICATE',
            'editId' => $editId,
            'submissionId' => $article->getId(),
            'editorName' => $editor->getFullName(),
            'editorAffiliation' => $editor->getLocalizedAffiliation(),
            'articleTitle' => $article->getLocalizedTitle(),
            'journalTitle' => $journal->getLocalizedTitle(),
            'dateAssigned' => $dateRef,
            'signatoryNames' => $signatory['names'],
            'certificateNumber' => 'CERT-EDT-' . date('Y', strtotime($dateRef)) . '-' . str_pad((string) $editId, 5, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * [BARU] Daftar semua Sertifikat (Reviewer + Editor) milik pengguna --
     * untuk halaman indeks. Digabung satu daftar, ditandai 'type'.
     * @param int $userId
     * @return array setiap elemen: ['type','id','articleTitle','statusLabel','dateCompleted']
     */
    public function getCertificateIndexForUser(int $userId): array {
        $entries = [];

        /** @var ReviewAssignmentDAO $reviewAssignmentDao */
        $reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
        /** @var EditAssignmentDAO $editAssignmentDao */
        $editAssignmentDao = DAORegistry::getDAO('EditAssignmentDAO');
        /** @var ArticleDAO $articleDao */
        $articleDao = DAORegistry::getDAO('ArticleDAO');

        $reviewAssignments = $reviewAssignmentDao->getByUserId($userId);
        foreach ($reviewAssignments as $reviewAssignment) {
            if (!$reviewAssignment->getDateCompleted()) continue;
            $article = $articleDao->getArticle((int) $reviewAssignment->getSubmissionId());
            if (!$article) continue;
            $entries[] = [
                'type' => 'reviewer',
                'id' => (int) $reviewAssignment->getId(),
                'articleTitle' => $article->getLocalizedTitle(),
                'statusLabel' => __('document.cert.status.reviewCompleted'),
                'dateCompleted' => $reviewAssignment->getDateCompleted(),
            ];
        }

        $editAssignmentResult = $editAssignmentDao->getEditAssignmentsByUserId($userId);
        while ($editAssignment = $editAssignmentResult->next()) {
            if (!$editAssignment->getDateNotified()) continue;
            $article = $articleDao->getArticle((int) $editAssignment->getArticleId());
            if (!$article) continue;
            $entries[] = [
                'type' => 'editor',
                'id' => (int) $editAssignment->getEditId(),
                'articleTitle' => $article->getLocalizedTitle(),
                'statusLabel' => __('document.cert.status.editorialAssignment'),
                'dateCompleted' => $editAssignment->getDateAssigned() ?: $editAssignment->getDateNotified(),
            ];
        }

        usort($entries, fn($a, $b) => strtotime($b['dateCompleted']) <=> strtotime($a['dateCompleted']));

        return $entries;
    }
    
}
?>
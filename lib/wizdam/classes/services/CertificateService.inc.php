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
 * @brief Layanan penyedia data untuk penerbitan Sertifikat (Reviewer, Author, dll).
 */

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

        $reviewAssignment = $reviewAssignmentDao->getById($reviewId);

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

        return [
            'type' => 'REVIEWER_CERTIFICATE',
            'reviewId' => $reviewId,
            'submissionId' => $article->getId(),
            'reviewerName' => $reviewer->getFullName(),
            'reviewerAffiliation' => $reviewer->getLocalizedAffiliation(),
            'articleTitle' => $article->getLocalizedTitle(),
            'journalTitle' => $journal->getLocalizedTitle(),
            'dateCompleted' => $reviewAssignment->getDateCompleted(),
            // Nomor Sertifikat (Bisa di-generate kustom, ini contoh format dinamis)
            'certificateNumber' => 'CERT-REV-' . date('Y', strtotime($reviewAssignment->getDateCompleted())) . '-' . str_pad((string)$reviewId, 5, '0', STR_PAD_LEFT)
        ];
    }
    
}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleType.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class ArticleType
 * @ingroup article
 *
 * @brief [WIZDAM] Tipe artikel BAKU -- kode standar JATS (NISO Journal
 * Article Tag Suite, standar internasional yang juga dipakai Crossref
 * untuk atribut article-type). SAMA untuk semua jurnal, karena itu
 * murni constant di sini, TIDAK perlu tabel database (berbeda dari
 * tipe KUSTOM per-jurnal, lihat ArticleTypeCustom.inc.php +
 * ArticleTypeCustomDAO.inc.php).
 *
 * INI BUKAN pengganti Section -- Section tetap untuk topik/Mini
 * Jurnal (konsep terpisah, tidak disentuh sama sekali oleh
 * pekerjaan ini). INI JUGA BUKAN pengganti field 'type' bebas teks
 * lama (Submission::getType()/setType(), dikontrol setting
 * 'metaType') -- field itu tetap ada berdampingan, TIDAK dihapus.
 *
 * Kode-kode ini SENGAJA memakai format persis JATS (huruf kecil,
 * dipisah tanda hubung) supaya nilai yang tersimpan LANGSUNG valid
 * dipakai sebagai atribut article-type saat export JATS XML/Crossref
 * di masa depan, tanpa perlu tabel pemetaan tambahan.
 */

define('ARTICLE_TYPE_RESEARCH_ARTICLE', 'research-article');
define('ARTICLE_TYPE_REVIEW_ARTICLE', 'review-article');
define('ARTICLE_TYPE_CASE_REPORT', 'case-report');
define('ARTICLE_TYPE_BRIEF_REPORT', 'brief-report');
define('ARTICLE_TYPE_EDITORIAL', 'editorial');
define('ARTICLE_TYPE_LETTER', 'letter');
define('ARTICLE_TYPE_BOOK_REVIEW', 'book-review');
define('ARTICLE_TYPE_ARTICLE_COMMENTARY', 'article-commentary');
define('ARTICLE_TYPE_SYSTEMATIC_REVIEW', 'systematic-review');
define('ARTICLE_TYPE_CORRECTION', 'correction');
define('ARTICLE_TYPE_RETRACTION', 'retraction');

class ArticleType {

    /**
     * Daftar lengkap kode tipe baku, urut sesuai kemunculan paling
     * umum di alur kerja jurnal (riset asli dulu, koreksi/retraksi
     * di akhir).
     * @return string[]
     */
    public static function getAllStandardTypes(): array {
        return [
            ARTICLE_TYPE_RESEARCH_ARTICLE,
            ARTICLE_TYPE_REVIEW_ARTICLE,
            ARTICLE_TYPE_SYSTEMATIC_REVIEW,
            ARTICLE_TYPE_CASE_REPORT,
            ARTICLE_TYPE_BRIEF_REPORT,
            ARTICLE_TYPE_ARTICLE_COMMENTARY,
            ARTICLE_TYPE_EDITORIAL,
            ARTICLE_TYPE_LETTER,
            ARTICLE_TYPE_BOOK_REVIEW,
            ARTICLE_TYPE_CORRECTION,
            ARTICLE_TYPE_RETRACTION,
        ];
    }

    /**
     * Cek apakah sebuah kode adalah tipe BAKU yang dikenal (bukan
     * tipe kustom).
     * @param string $code
     * @return bool
     */
    public static function isStandardType(string $code): bool {
        return in_array($code, self::getAllStandardTypes(), true);
    }

}
?>
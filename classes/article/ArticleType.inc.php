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
 * untuk atribut article-type), plus beberapa kode ekstensi yang sudah
 * jadi konvensi umum banyak penerbit (mis. gaya Frontiers) walau
 * bukan bagian core JATS. SAMA untuk semua jurnal, karena itu murni
 * constant di sini, TIDAK perlu tabel database (berbeda dari tipe
 * KUSTOM per-jurnal, lihat ArticleTypeCustom.inc.php +
 * ArticleTypeCustomDAO.inc.php).
 *
 * INI BUKAN pengganti Section -- Section tetap untuk topik/Mini
 * Jurnal (konsep terpisah, tidak disentuh sama sekali oleh
 * pekerjaan ini). INI JUGA BUKAN pengganti field 'type' bebas teks
 * lama (Submission::getType()/setType(), dikontrol setting
 * 'metaType') -- field itu tetap ada berdampingan, TIDAK dihapus.
 *
 * [WIZDAM] VISIBILITAS -- setiap tipe punya klasifikasi PUBLIK atau
 * EDITORIAL-ONLY:
 *   - PUBLIK: bisa dipilih penulis saat submit naskah baru.
 *   - EDITORIAL-ONLY: HANYA bisa dipilih/di-assign Journal Manager,
 *     Editor, atau Section Editor -- TIDAK muncul di pilihan penulis
 *     saat submit. Ini untuk tipe yang secara alami adalah TINDAKAN
 *     EDITORIAL terhadap artikel yang SUDAH TERBIT (Erratum,
 *     Corrigendum, Correction, Retraction) -- bukan sesuatu yang
 *     penulis "submit" sebagai naskah baru dari nol.
 */

// --- Tipe PUBLIK (bisa dipilih penulis saat submit) ---
define('ARTICLE_TYPE_RESEARCH_ARTICLE', 'research-article');
define('ARTICLE_TYPE_REVIEW_ARTICLE', 'review-article');
define('ARTICLE_TYPE_SYSTEMATIC_REVIEW', 'systematic-review');
define('ARTICLE_TYPE_MINI_REVIEW', 'mini-review');
define('ARTICLE_TYPE_CASE_REPORT', 'case-report');
define('ARTICLE_TYPE_SHORT_COMMUNICATION', 'short-communication');
define('ARTICLE_TYPE_BRIEF_REPORT', 'brief-report');
define('ARTICLE_TYPE_ARTICLE_COMMENTARY', 'article-commentary');
define('ARTICLE_TYPE_PERSPECTIVE', 'perspective');
define('ARTICLE_TYPE_HYPOTHESIS_AND_THEORY', 'hypothesis-and-theory');
define('ARTICLE_TYPE_CONCEPTUAL_ANALYSIS', 'conceptual-analysis');
define('ARTICLE_TYPE_METHODS', 'methods');
define('ARTICLE_TYPE_DATA_REPORT', 'data-report');
define('ARTICLE_TYPE_EDITORIAL', 'editorial');
define('ARTICLE_TYPE_LETTER', 'letter');
define('ARTICLE_TYPE_BOOK_REVIEW', 'book-review');

// --- Tipe EDITORIAL-ONLY (hanya JM/Editor/Section Editor) ---
define('ARTICLE_TYPE_ERRATUM', 'erratum');
define('ARTICLE_TYPE_CORRIGENDUM', 'corrigendum');
define('ARTICLE_TYPE_CORRECTION', 'correction');
define('ARTICLE_TYPE_RETRACTION', 'retraction');

class ArticleType {

    /**
     * Tipe yang bisa dipilih PENULIS saat submit naskah baru.
     * @return string[]
     */
    public static function getPublicTypes(): array {
        return [
            ARTICLE_TYPE_RESEARCH_ARTICLE,
            ARTICLE_TYPE_REVIEW_ARTICLE,
            ARTICLE_TYPE_SYSTEMATIC_REVIEW,
            ARTICLE_TYPE_MINI_REVIEW,
            ARTICLE_TYPE_CASE_REPORT,
            ARTICLE_TYPE_SHORT_COMMUNICATION,
            ARTICLE_TYPE_BRIEF_REPORT,
            ARTICLE_TYPE_ARTICLE_COMMENTARY,
            ARTICLE_TYPE_PERSPECTIVE,
            ARTICLE_TYPE_HYPOTHESIS_AND_THEORY,
            ARTICLE_TYPE_CONCEPTUAL_ANALYSIS,
            ARTICLE_TYPE_METHODS,
            ARTICLE_TYPE_DATA_REPORT,
            ARTICLE_TYPE_EDITORIAL,
            ARTICLE_TYPE_LETTER,
            ARTICLE_TYPE_BOOK_REVIEW,
        ];
    }

    /**
     * Tipe yang HANYA bisa dipilih/di-assign Journal Manager, Editor,
     * atau Section Editor -- TIDAK PERNAH muncul di pilihan penulis
     * saat submit naskah baru.
     * @return string[]
     */
    public static function getEditorialOnlyTypes(): array {
        return [
            ARTICLE_TYPE_ERRATUM,
            ARTICLE_TYPE_CORRIGENDUM,
            ARTICLE_TYPE_CORRECTION,
            ARTICLE_TYPE_RETRACTION,
        ];
    }

    /**
     * Daftar LENGKAP seluruh kode tipe baku (publik + editorial-only
     * digabung) -- dipakai di halaman yang memang boleh menampilkan
     * semuanya (mis. form metadata editorial).
     * @return string[]
     */
    public static function getAllStandardTypes(): array {
        return array_merge(self::getPublicTypes(), self::getEditorialOnlyTypes());
    }

    /**
     * Cek apakah sebuah kode adalah tipe BAKU yang dikenal (publik
     * ATAU editorial-only, bukan tipe kustom).
     * @param string $code
     * @return bool
     */
    public static function isStandardType(string $code): bool {
        return in_array($code, self::getAllStandardTypes(), true);
    }

    /**
     * Cek apakah sebuah kode adalah tipe PUBLIK (boleh dipilih
     * penulis saat submit).
     * @param string $code
     * @return bool
     */
    public static function isPublicType(string $code): bool {
        return in_array($code, self::getPublicTypes(), true);
    }

    /**
     * Cek apakah sebuah kode adalah tipe EDITORIAL-ONLY (HANYA boleh
     * dipilih/di-assign JM/Editor/Section Editor).
     * @param string $code
     * @return bool
     */
    public static function isEditorialOnlyType(string $code): bool {
        return in_array($code, self::getEditorialOnlyTypes(), true);
    }

}
?>
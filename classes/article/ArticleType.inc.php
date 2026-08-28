<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleType.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
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

    /**
     * [WIZDAM] Bangun opsi <select> GABUNGAN (tipe BAKU yang efektif
     * aktif + tipe KUSTOM milik jurnal) -- SATU sumber kebenaran yang
     * dipakai bersama oleh wizard submit penulis (AuthorSubmitStep1Form),
     * form metadata editorial (MetadataForm), dan QuickSubmit
     * (QuickSubmitForm), supaya ketiganya TIDAK saling menyimpang kalau
     * logikanya berubah nanti -- ini pelajaran langsung dari bug funder/
     * credit role sebelumnya yang jadi tidak konsisten karena logikanya
     * disalin manual di banyak tempat.
     *
     * Value dikodekan "std:<code>" untuk tipe baku atau "custom:<id>"
     * untuk tipe kustom, supaya SATU <select> tunggal bisa membedakan
     * keduanya tanpa perlu dua form terpisah/toggle radio + JS (lihat
     * parseTypeChoice()/toChoiceValue() sebagai pasangannya).
     * @param int $journalId
     * @param int|null $sectionId Kalau diisi, dipersempit ke tipe yang
     *   efektif aktif untuk SECTION itu (gabungan pembatasan level
     *   jurnal + level section). Kalau null, tampilkan yang aktif di
     *   level JURNAL saja (dipakai saat section belum/tidak diketahui,
     *   mis. render pertama wizard submit sebelum Section dipilih).
     * @param bool $includeEditorialOnly Sertakan tipe EDITORIAL-ONLY --
     *   HANYA untuk form yang diakses Journal Manager/Editor/Section
     *   Editor, TIDAK PERNAH untuk form penulis submit naskah baru.
     * @return array value => label (localized), siap untuk {html_options}
     */
    public static function buildTypeOptions($journalId, $sectionId = null, $includeEditorialOnly = false) {
        /** @var ArticleTypeAvailabilityDAO $availabilityDao */
        $availabilityDao = DAORegistry::getDAO('ArticleTypeAvailabilityDAO');
        $standardCodes = $sectionId
            ? $availabilityDao->getEffectiveEnabledTypesForSection((int) $journalId, (int) $sectionId)
            : $availabilityDao->getEnabledTypesForJournal((int) $journalId);

        $options = [];
        foreach ($standardCodes as $code) {
            $options['std:' . $code] = __('article.type.standard.' . $code);
        }

        if ($includeEditorialOnly) {
            foreach (self::getEditorialOnlyTypes() as $code) {
                $options['std:' . $code] = __('article.type.standard.' . $code);
            }
        }

        import('classes.article.ArticleTypeCustomDAO');
        /** @var ArticleTypeCustomDAO $customDao */
        $customDao = DAORegistry::getDAO('ArticleTypeCustomDAO');
        foreach ($customDao->getByJournalId((int) $journalId)->toArray() as $customType) {
            $options['custom:' . $customType->getId()] = $customType->getLocalizedName();
        }

        return $options;
    }

    /**
     * [WIZDAM] Pecah value gabungan "std:<code>" / "custom:<id>" hasil
     * buildTypeOptions() menjadi [articleTypeCode, articleTypeCustomId] --
     * TEPAT SATU yang terisi, satunya lagi null (lihat penjelasan lengkap
     * di Article::getArticleTypeCode()/getArticleTypeCustomId()). Kode
     * yang tidak dikenal/id tidak valid diperlakukan sama seperti "tidak
     * memilih apa-apa" (silently ignored), BUKAN error -- konsisten
     * dengan pola guard di ArticleTypeAvailabilityDAO.
     * @param string|null $choice
     * @return array{0: string|null, 1: int|null}
     */
    public static function parseTypeChoice($choice) {
        $choice = (string) $choice;
        if ($choice === '') return [null, null];
        if (strpos($choice, 'std:') === 0) {
            $code = substr($choice, 4);
            return self::isStandardType($code) ? [$code, null] : [null, null];
        }
        if (strpos($choice, 'custom:') === 0) {
            $id = (int) substr($choice, 7);
            return $id > 0 ? [null, $id] : [null, null];
        }
        return [null, null];
    }

    /**
     * [WIZDAM] Kebalikan parseTypeChoice() -- bangun kembali value
     * gabungan dari state Article saat ini, dipakai template untuk
     * menentukan opsi <select> mana yang harus ter-"selected".
     * @param string|null $articleTypeCode
     * @param int|null $articleTypeCustomId
     * @return string
     */
    public static function toChoiceValue($articleTypeCode, $articleTypeCustomId) {
        if ($articleTypeCustomId) return 'custom:' . (int) $articleTypeCustomId;
        if ($articleTypeCode) return 'std:' . $articleTypeCode;
        return '';
    }

}
?>
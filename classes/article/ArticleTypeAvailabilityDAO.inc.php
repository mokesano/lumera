<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleTypeAvailabilityDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class ArticleTypeAvailabilityDAO
 * @ingroup article
 *
 * @brief [WIZDAM] Mengelola ketersediaan tipe artikel BAKU (publik) --
 * level JURNAL (dikontrol Journal Manager) dan level SECTION
 * (dikontrol Section Editor, HANYA bisa mempersempit dari yang sudah
 * diizinkan level jurnal, TIDAK bisa mengaktifkan kembali yang sudah
 * dinonaktifkan JM).
 *
 * Pola "blacklist" -- KEHADIRAN baris di article_type_journal_disabled
 * / article_type_section_disabled berarti DINONAKTIFKAN, KETIADAAN
 * baris berarti AKTIF secara default (sesuai keputusan eksplisit:
 * semua tipe publik tercentang aktif dari awal, JM/SE tinggal
 * mengecualikan yang tidak diinginkan).
 *
 * INI TERPISAH dari ArticleTypeCustomDAO (yang murni CRUD tipe
 * KUSTOM) -- class ini soal ON/OFF tipe BAKU, konsep berbeda.
 */

import('lib.pkp.classes.db.DAO');
import('classes.article.ArticleType');

class ArticleTypeAvailabilityDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    // ================== LEVEL JURNAL (Journal Manager) ==================

    /**
     * Daftar kode tipe yang DINONAKTIFKAN untuk satu jurnal.
     * @param int $journalId
     * @return string[]
     */
    public function getDisabledTypesForJournal($journalId) {
        $result = $this->retrieve(
            'SELECT type_code FROM article_type_journal_disabled WHERE journal_id = ?',
            [(int) $journalId]
        );
        $codes = [];
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $codes[] = $row['type_code'];
            $result->MoveNext();
        }
        $result->Close();
        return $codes;
    }

    /**
     * Cek apakah satu kode tipe dinonaktifkan untuk satu jurnal.
     * @param int $journalId
     * @param string $typeCode
     * @return bool
     */
    public function isTypeDisabledForJournal($journalId, $typeCode) {
        return in_array($typeCode, $this->getDisabledTypesForJournal($journalId), true);
    }

    /**
     * Ganti SELURUH daftar tipe yang dinonaktifkan untuk satu jurnal
     * (dipakai form save -- hapus semua baris lama, masukkan yang baru).
     * @param int $journalId
     * @param string[] $disabledTypeCodes
     */
    public function setDisabledTypesForJournal($journalId, $disabledTypeCodes) {
        $this->update('DELETE FROM article_type_journal_disabled WHERE journal_id = ?', [(int) $journalId]);
        foreach ($disabledTypeCodes as $typeCode) {
            if (!ArticleType::isPublicType($typeCode)) {
                // [WIZDAM BUGFIX-GUARD] Cegah penyimpanan kode yang
                // bukan tipe publik yang dikenal (mis. hasil manipulasi
                // form) -- silently skip, bukan error, supaya tidak
                // mengganggu penyimpanan tipe lain yang valid.
                continue;
            }
            $this->update(
                'INSERT INTO article_type_journal_disabled (journal_id, type_code) VALUES (?, ?)',
                [(int) $journalId, $typeCode]
            );
        }
    }

    // ================== LEVEL SECTION (Section Editor) ==================

    /**
     * Daftar kode tipe yang DINONAKTIFKAN untuk satu section.
     * @param int $sectionId
     * @return string[]
     */
    public function getDisabledTypesForSection($sectionId) {
        $result = $this->retrieve(
            'SELECT type_code FROM article_type_section_disabled WHERE section_id = ?',
            [(int) $sectionId]
        );
        $codes = [];
        while (!$result->EOF) {
            $row = $result->GetRowAssoc(false);
            $codes[] = $row['type_code'];
            $result->MoveNext();
        }
        $result->Close();
        return $codes;
    }

    /**
     * Cek apakah satu kode tipe dinonaktifkan untuk satu section.
     * @param int $sectionId
     * @param string $typeCode
     * @return bool
     */
    public function isTypeDisabledForSection($sectionId, $typeCode) {
        return in_array($typeCode, $this->getDisabledTypesForSection($sectionId), true);
    }

    /**
     * Ganti SELURUH daftar tipe yang dinonaktifkan untuk satu section
     * (dipakai form save Section Editor). Kode yang SUDAH dinonaktifkan
     * di level jurnal TIDAK disimpan di sini (tidak perlu -- sudah
     * tidak tersedia lewat jalur manapun), mencegah data ganda/rancu.
     * @param int $sectionId
     * @param int $journalId
     * @param string[] $disabledTypeCodes
     */
    public function setDisabledTypesForSection($sectionId, $journalId, $disabledTypeCodes) {
        $this->update('DELETE FROM article_type_section_disabled WHERE section_id = ?', [(int) $sectionId]);
        $journalDisabled = $this->getDisabledTypesForJournal($journalId);
        foreach ($disabledTypeCodes as $typeCode) {
            if (!ArticleType::isPublicType($typeCode)) continue;
            if (in_array($typeCode, $journalDisabled, true)) continue; // SE tidak bisa buka kembali yang JM tutup
            $this->update(
                'INSERT INTO article_type_section_disabled (section_id, type_code) VALUES (?, ?)',
                [(int) $sectionId, $typeCode]
            );
        }
    }

    // ================== GABUNGAN (dipakai wizard submit) ==================

    /**
     * Daftar kode tipe publik yang BENAR-BENAR tersedia untuk SATU
     * section tertentu -- gabungan level jurnal + level section
     * (SEMUA tipe publik, DIKURANGI yang dinonaktifkan di SALAH SATU
     * level). Inilah method yang dipakai form submit penulis untuk
     * menampilkan pilihan Tipe Artikel setelah Section dipilih.
     * @param int $journalId
     * @param int $sectionId
     * @return string[]
     */
    public function getEffectiveEnabledTypesForSection($journalId, $sectionId) {
        $disabled = array_unique(array_merge(
            $this->getDisabledTypesForJournal($journalId),
            $this->getDisabledTypesForSection($sectionId)
        ));
        return array_values(array_diff(ArticleType::getPublicTypes(), $disabled));
    }

    /**
     * Daftar kode tipe publik yang tersedia di level JURNAL saja
     * (belum mempertimbangkan section) -- dipakai halaman Journal
     * Manager (articleTypes.tpl) dan halaman Section Editor untuk
     * menampilkan status centang / kandidat checkbox.
     * @param int $journalId
     * @return string[]
     */
    public function getEnabledTypesForJournal($journalId) {
        $disabled = $this->getDisabledTypesForJournal($journalId);
        return array_values(array_diff(ArticleType::getPublicTypes(), $disabled));
    }

}
?>
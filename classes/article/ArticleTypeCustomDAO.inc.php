<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleTypeCustomDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class ArticleTypeCustomDAO
 * @ingroup article
 * @see ArticleTypeCustom
 *
 * @brief [WIZDAM] Operasi penyimpanan/pengambilan ArticleTypeCustom --
 * daftar tipe artikel kustom milik satu jurnal, dikelola Journal
 * Manager. Nama (localized) disimpan lewat mekanisme generic settings
 * (updateDataObjectSettings/getDataObjectSettings) -- pola SAMA
 * seperti AuthorDAO/ArticleDAO, BUKAN pola title_alt1/alt2 milik
 * SectionDAO.
 */

import('lib.pkp.classes.db.DAO');
import('classes.article.ArticleTypeCustom');

class ArticleTypeCustomDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Buat instance ArticleTypeCustom baru (kosong).
     * @return ArticleTypeCustom
     */
    public function newDataObject() {
        return new ArticleTypeCustom();
    }

    /**
     * Nama field yang di-localize, disimpan ke
     * article_type_custom_settings -- dipakai internal oleh
     * updateDataObjectSettings()/getDataObjectSettings().
     * @return string[]
     */
    public function getLocaleFieldNames() {
        return ['name'];
    }

    /**
     * Ambil satu ArticleTypeCustom berdasarkan ID-nya, dengan
     * verifikasi kepemilikan jurnal (mencegah akses/edit tipe kustom
     * milik jurnal lain).
     * @param int $customTypeId
     * @param int|null $journalId
     * @return ArticleTypeCustom|null
     */
    public function getById($customTypeId, $journalId = null) {
        $params = [(int) $customTypeId];
        $sql = 'SELECT * FROM article_type_custom WHERE custom_type_id = ?';
        if ($journalId !== null) {
            $sql .= ' AND journal_id = ?';
            $params[] = (int) $journalId;
        }
        $result = $this->retrieve($sql, $params);

        $returner = null;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (is_array($row)) {
                $returner = $this->_returnCustomTypeFromRow($row);
            }
        }
        $result->Close();
        return $returner;
    }

    /**
     * Ambil seluruh ArticleTypeCustom milik satu jurnal, terurut seq.
     * @param int $journalId
     * @return DAOResultFactory
     */
    public function getByJournalId($journalId) {
        $result = $this->retrieve(
            'SELECT * FROM article_type_custom WHERE journal_id = ? ORDER BY seq, custom_type_id',
            [(int) $journalId]
        );
        return new DAOResultFactory($result, $this, '_returnCustomTypeFromRow');
    }

    /**
     * Simpan ArticleTypeCustom baru.
     * @param ArticleTypeCustom $customType
     * @return int custom_type_id yang baru dibuat
     */
    public function insertCustomType($customType) {
        $this->update(
            'INSERT INTO article_type_custom (journal_id, seq) VALUES (?, ?)',
            [(int) $customType->getJournalId(), (float) $customType->getSequence()]
        );
        $customType->setId($this->getInsertCustomTypeId());
        $this->updateLocaleFields($customType);
        return $customType->getId();
    }

    /**
     * Perbarui ArticleTypeCustom yang sudah ada.
     * @param ArticleTypeCustom $customType
     * @return bool
     */
    public function updateCustomType($customType) {
        $result = $this->update(
            'UPDATE article_type_custom SET seq = ? WHERE custom_type_id = ? AND journal_id = ?',
            [
                (float) $customType->getSequence(),
                (int) $customType->getId(),
                (int) $customType->getJournalId(),
            ]
        );
        $this->updateLocaleFields($customType);
        return $result;
    }

    /**
     * Simpan nama (localized) ke article_type_custom_settings.
     * @param ArticleTypeCustom $customType
     */
    public function updateLocaleFields($customType) {
        $this->updateDataObjectSettings('article_type_custom_settings', $customType, [
            'custom_type_id' => $customType->getId()
        ]);
    }

    /**
     * Hapus satu ArticleTypeCustom, dengan verifikasi kepemilikan
     * jurnal.
     * @param int $customTypeId
     * @param int $journalId
     * @return bool
     */
    public function deleteById($customTypeId, $journalId) {
        $this->update(
            'DELETE FROM article_type_custom_settings WHERE custom_type_id = ?',
            [(int) $customTypeId]
        );
        return $this->update(
            'DELETE FROM article_type_custom WHERE custom_type_id = ? AND journal_id = ?',
            [(int) $customTypeId, (int) $journalId]
        );
    }

    /**
     * Bangun objek ArticleTypeCustom dari satu baris hasil query
     * (TERMASUK memuat nama localized dari settings table).
     * @param array $row
     * @return ArticleTypeCustom
     */
    public function _returnCustomTypeFromRow($row) {
        $customType = $this->newDataObject();
        $customType->setId((int) $row['custom_type_id']);
        $customType->setJournalId((int) $row['journal_id']);
        $customType->setSequence((float) $row['seq']);
        $this->getDataObjectSettings('article_type_custom_settings', 'custom_type_id', (int) $row['custom_type_id'], $customType);
        return $customType;
    }

    /**
     * ID auto-increment dari insert terakhir.
     * @return int
     */
    public function getInsertCustomTypeId() {
        return $this->getInsertId('article_type_custom', 'custom_type_id');
    }

}
?>
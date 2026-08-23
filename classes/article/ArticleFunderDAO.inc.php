<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleFunderDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class ArticleFunderDAO
 * @ingroup article
 * @see ArticleFunder
 *
 * @brief [WIZDAM] Operasi penyimpanan/pengambilan ArticleFunder --
 * mendukung BEBERAPA funder per artikel, urutan sesuai seq. Pola
 * method mengikuti persis AuthorDAO (insert/update/delete/getByArticle).
 *
 * PENTING soal penulisan tanggal: kelas ini TIDAK PERNAH memakai
 * $this->datetimeToDB() sebagai nilai parameter terikat (?) -- tabel
 * article_funders tidak punya kolom tanggal sama sekali, jadi masalah
 * itu (lihat NotificationDAO/ArticleFileDAO/PublishedArticleDAO/
 * PKPAnnouncementDAO) tidak relevan di sini. Dicatat di sini semata
 * supaya siapa pun yang menambah kolom tanggal ke tabel ini di masa
 * depan sadar akan jebakan itu.
 */

import('lib.pkp.classes.db.DAO');
import('classes.article.ArticleFunder');

class ArticleFunderDAO extends DAO {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Buat instance ArticleFunder baru (kosong).
     * @return ArticleFunder
     */
    public function newDataObject() {
        return new ArticleFunder();
    }

    /**
     * Ambil satu ArticleFunder berdasarkan ID-nya, dengan verifikasi
     * kepemilikan artikel (mencegah akses/edit funder milik artikel lain).
     * @param int $funderId
     * @param int|null $articleId
     * @return ArticleFunder|null
     */
    public function getById($funderId, $articleId = null) {
        $params = [(int) $funderId];
        $sql = 'SELECT * FROM article_funders WHERE funder_id = ?';
        if ($articleId !== null) {
            $sql .= ' AND article_id = ?';
            $params[] = (int) $articleId;
        }
        $result = $this->retrieve($sql, $params);

        $returner = null;
        if ($result && !$result->EOF) {
            $row = $result->GetRowAssoc(false);
            if (is_array($row)) {
                $returner = $this->_returnArticleFunderFromRow($row);
            }
        }
        $result->Close();
        return $returner;
    }

    /**
     * Ambil seluruh ArticleFunder milik satu artikel, terurut seq.
     * @param int $articleId
     * @return DAOResultFactory
     */
    public function getByArticleId($articleId) {
        $result = $this->retrieve(
            'SELECT * FROM article_funders WHERE article_id = ? ORDER BY seq, funder_id',
            [(int) $articleId]
        );
        return new DAOResultFactory($result, $this, '_returnArticleFunderFromRow');
    }

    /**
     * Simpan ArticleFunder baru.
     * @param ArticleFunder $articleFunder
     * @return int funder_id yang baru dibuat
     */
    public function insertArticleFunder($articleFunder) {
        $this->update(
            'INSERT INTO article_funders (article_id, seq, funder_name, award_number) VALUES (?, ?, ?, ?)',
            [
                (int) $articleFunder->getArticleId(),
                (float) $articleFunder->getSequence(),
                $articleFunder->getFunderName(),
                $articleFunder->getAwardNumber() ?: null,
            ]
        );
        $articleFunder->setId($this->getInsertArticleFunderId());
        return $articleFunder->getId();
    }

    /**
     * Perbarui ArticleFunder yang sudah ada.
     * @param ArticleFunder $articleFunder
     * @return bool
     */
    public function updateArticleFunder($articleFunder) {
        return $this->update(
            'UPDATE article_funders SET seq = ?, funder_name = ?, award_number = ? WHERE funder_id = ? AND article_id = ?',
            [
                (float) $articleFunder->getSequence(),
                $articleFunder->getFunderName(),
                $articleFunder->getAwardNumber() ?: null,
                (int) $articleFunder->getId(),
                (int) $articleFunder->getArticleId(),
            ]
        );
    }

    /**
     * Hapus satu ArticleFunder, dengan verifikasi kepemilikan artikel.
     * @param int $funderId
     * @param int $articleId
     * @return bool
     */
    public function deleteById($funderId, $articleId) {
        return $this->update(
            'DELETE FROM article_funders WHERE funder_id = ? AND article_id = ?',
            [(int) $funderId, (int) $articleId]
        );
    }

    /**
     * Hapus SELURUH funder milik satu artikel (dipakai saat menyimpan
     * ulang seluruh daftar funder dari form -- pola sama seperti
     * penanganan daftar penulis di AuthorSubmitStep3Form).
     * @param int $articleId
     */
    public function deleteByArticleId($articleId) {
        $this->update(
            'DELETE FROM article_funders WHERE article_id = ?',
            [(int) $articleId]
        );
    }

    /**
     * Bangun objek ArticleFunder dari satu baris hasil query.
     * @param array $row
     * @return ArticleFunder
     */
    public function _returnArticleFunderFromRow($row) {
        $articleFunder = $this->newDataObject();
        $articleFunder->setId((int) $row['funder_id']);
        $articleFunder->setArticleId((int) $row['article_id']);
        $articleFunder->setSequence((float) $row['seq']);
        $articleFunder->setFunderName($row['funder_name']);
        $articleFunder->setAwardNumber($row['award_number']);
        return $articleFunder;
    }

    /**
     * ID auto-increment dari insert terakhir.
     * @return int
     */
    public function getInsertArticleFunderId() {
        return $this->getInsertId('article_funders', 'funder_id');
    }

}
?>
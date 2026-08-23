<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleFunder.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Distributed under the GNU GPL v3.
 *
 * @class ArticleFunder
 * @ingroup article
 *
 * @brief [WIZDAM] Data pendanaan/hibah (funder_name, award_number)
 * untuk satu artikel -- mendukung BEBERAPA funder per artikel
 * (dibedakan lewat seq, mengikuti pola Author/Citation). Dipakai
 * untuk pengayaan deposit Crossref (elemen fr:program/FundRef).
 */

import('lib.pkp.classes.core.DataObject');

class ArticleFunder extends DataObject {

    /**
     * Get article ID.
     * @return int
     */
    public function getArticleId() {
        return $this->getData('articleId');
    }

    /**
     * Set article ID.
     * @param int $articleId
     */
    public function setArticleId($articleId) {
        $this->setData('articleId', $articleId);
    }

    /**
     * Get sequence (urutan tampil, mengikuti pola Author::getSequence()).
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set sequence.
     * @param float $sequence
     */
    public function setSequence($sequence) {
        $this->setData('sequence', $sequence);
    }

    /**
     * Get funder name.
     * @return string
     */
    public function getFunderName() {
        return $this->getData('funderName');
    }

    /**
     * Set funder name.
     * @param string $funderName
     */
    public function setFunderName($funderName) {
        $this->setData('funderName', $funderName);
    }

    /**
     * Get award/grant number. Opsional -- tidak semua pengakuan
     * pendanaan menyertakan nomor hibah spesifik.
     * @return string|null
     */
    public function getAwardNumber() {
        return $this->getData('awardNumber');
    }

    /**
     * Set award/grant number.
     * @param string|null $awardNumber
     */
    public function setAwardNumber($awardNumber) {
        $this->setData('awardNumber', $awardNumber);
    }

}
?>
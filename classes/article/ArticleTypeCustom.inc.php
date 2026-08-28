<?php
declare(strict_types=1);

/**
 * @file classes/article/ArticleTypeCustom.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class ArticleTypeCustom
 * @ingroup article
 *
 * @brief [WIZDAM] Data object untuk SATU tipe artikel KUSTOM
 * milik jurnal tertentu -- mendampingi tipe BAKU (lihat
 * ArticleType.inc.php). Dikelola Journal Manager, DAO-nya lihat
 * ArticleTypeCustomDAO.inc.php.
 */

import('lib.pkp.classes.core.DataObject');

class ArticleTypeCustom extends DataObject {

    /**
     * Get the journal ID this custom type belongs to.
     * @return int
     */
    public function getJournalId() {
        return $this->getData('journalId');
    }

    /**
     * Set the journal ID.
     * @param int $journalId
     */
    public function setJournalId($journalId) {
        $this->setData('journalId', $journalId);
    }

    /**
     * Get the display sequence.
     * @return float
     */
    public function getSequence() {
        return $this->getData('sequence');
    }

    /**
     * Set the display sequence.
     * @param float $sequence
     */
    public function setSequence($sequence) {
        $this->setData('sequence', $sequence);
    }

    /**
     * Get the localized name of this custom type.
     * @param string|null $locale
     * @return string|array
     */
    public function getName($locale = null) {
        return $this->getData('name', $locale);
    }

    /**
     * Set the localized name of this custom type.
     * @param string|array $name
     * @param string|null $locale
     */
    public function setName($name, $locale = null) {
        $this->setData('name', $name, $locale);
    }

    /**
     * Get the localized name for the current locale.
     * @return string
     */
    public function getLocalizedName() {
        return $this->getLocalizedData('name');
    }

}
?>
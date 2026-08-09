<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/tasks/SintaScoreTask.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class SintaScoreTask
 *
 * @brief Scheduled task mingguan: untuk setiap jurnal yang punya ISSN
 * (online atau cetak), scrape skor & grade SINTA (SintaScoreService),
 * lalu tulis hasilnya LANGSUNG ke journal_settings (sintaScore,
 * sintaGrade, sintaLastUpdate, dst) -- dibaca kembali secara instan lewat
 * PKPTemplateManager::initialize() (assign global), tanpa AJAX, tanpa
 * cache file terpisah, tanpa scraping saat halaman diakses.
 *
 * [KOREKSI ARSITEKTUR] Menggantikan pendekatan sebelumnya (AJAX client-
 * side + endpoint /api/sinta lewat .htaccess) yang TIDAK diminta dan
 * tidak dibutuhkan -- mengikuti pola CrossrefInfoSender/CitationRefreshTask:
 * pemeriksaan berkala di backend, hasilnya tersedia langsung saat halaman
 * dirender.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');
import('lib.wizdam.sinta.SintaScoreService');

class SintaScoreTask extends ScheduledTask {

    public function __construct() {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getName() {
        return 'SINTA Score Refresh';
    }

    /**
     * @return bool
     */
    public function executeActions() {
        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');

        $journals = $journalDao->getJournals(true);
        if (!$journals) {
            return true;
        }

        $service = new SintaScoreService();

        while ($journal = $journals->next()) {
            $this->_refreshJournalScore($journal, $service);
            sleep(1); // Jeda antar-jurnal, sopan terhadap server SINTA (dipertahankan dari skrip lama).
        }

        return true;
    }

    /**
     * @param Journal $journal
     * @param SintaScoreService $service
     */
    private function _refreshJournalScore($journal, SintaScoreService $service): void {
        $issn = trim((string) $journal->getSetting('onlineIssn'));
        if ($issn === '') {
            $issn = trim((string) $journal->getSetting('printIssn'));
        }
        if ($issn === '') {
            return; // Jurnal belum punya ISSN sama sekali -- lewati.
        }

        try {
            $result = $service->fetchScore($issn);
        } catch (Exception $e) {
            error_log('SintaScoreTask: gagal ambil skor untuk jurnal ID ' . $journal->getId() . ' (ISSN ' . $issn . ') -- ' . $e->getMessage());
            return;
        }

        if (empty($result['success'])) {
            error_log('SintaScoreTask: SINTA tidak menemukan jurnal ID ' . $journal->getId() . ' (ISSN ' . $issn . ') -- ' . ($result['error'] ?? 'unknown'));
            return;
        }

        $journal->updateSetting('sintaScore', $result['impact'] ?? '0.000', 'string');
        $journal->updateSetting('sintaGrade', $result['grade'] ?? null, 'string');
        $journal->updateSetting('sintaId', $result['sinta_id'] ?? null, 'string');
        $journal->updateSetting('sintaUrl', $result['sinta_url'] ?? null, 'string');
        $journal->updateSetting('sintaLastUpdate', date('Y-m-d H:i:s'), 'string');
    }

}
?>
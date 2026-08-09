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
 * [BUGFIX ROBUSTNESS] Ditemukan lewat investigasi: plugin acron (yang
 * memicu scheduled task di lingkungan ini) menjalankan task lewat HOOK
 * 'LoadHandler' -- artinya task jalan SEBAGAI BAGIAN dari request web
 * biasa, BUKAN proses CLI cron independen. Meski acron memanggil
 * set_time_limit(0), itu TIDAK bisa mengalahkan batas timeout di level
 * web server/PHP-FPM pada hosting shared (mis. Apache Timeout,
 * mod_fcgid/PHP-FPM request_terminate_timeout) -- batas itu di luar
 * kendali kode PHP sama sekali. Task lama (iterasi SEMUA jurnal dengan
 * scraping lambat: hingga 3 percobaan x 30 detik timeout x 2 URL per
 * jurnal, plus sleep 1 detik antar-jurnal) sangat mungkin terhenti paksa
 * di tengah jalan sebelum menjangkau sebagian besar jurnal -- persis
 * yang menyebabkan sintaScore/sintaGrade gagal tampil. Diperkuat dengan:
 * (a) anggaran waktu dinding internal -- task berhenti AMAN begitu
 * anggaran habis, bukan menunggu dibunuh paksa server;
 * (b) urutan jurnal diacak tiap eksekusi -- kalau anggaran tetap
 * terlampaui suatu minggu, jurnal yang TERLEWAT bukan selalu yang sama
 * (self-healing lintas minggu, bukan sebagian jurnal permanen tidak
 * pernah ter-update);
 * (c) parameter retry/timeout/jeda jauh lebih hemat, disesuaikan untuk
 * konteks latar belakang -- bukan lagi meniru skrip lama yang dirancang
 * untuk SATU fetch on-demand, bukan iterasi banyak jurnal sekaligus.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');
import('lib.wizdam.sinta.SintaScoreService');

class SintaScoreTask extends ScheduledTask {

    // Anggaran waktu aman untuk SATU eksekusi task -- di bawah batas
    // timeout web server/PHP-FPM yang umum di hosting shared (biasanya
    // 30-90 detik), dengan margin lebar. Task berhenti memulai jurnal
    // BARU begitu anggaran ini terlampaui -- jurnal yang sudah diproses
    // SEBELUM itu tetap tersimpan (updateSetting per-jurnal, bukan batch
    // di akhir), jurnal yang terlewat akan dicoba lagi minggu berikutnya.
    private const TIME_BUDGET_SECONDS = 40;

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
        // Jaring pengaman tambahan -- acron SUDAH memanggil set_time_limit(0)
        // sebelum menjalankan task, tapi dipanggil lagi di sini supaya task
        // ini tetap benar kalau suatu saat dipicu lewat jalur lain (CLI cron
        // sungguhan, mis.) yang belum tentu melakukan hal yang sama.
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');

        $journalsFactory = $journalDao->getJournals(true);
        if (!$journalsFactory) {
            return true;
        }

        $journals = [];
        while ($journal = $journalsFactory->next()) {
            $journals[] = $journal;
        }
        if (empty($journals)) {
            return true;
        }

        // [ROBUSTNESS] Acak urutan tiap eksekusi -- kalau anggaran waktu
        // tetap terlampaui minggu ini, jurnal yang TIDAK terjangkau bukan
        // selalu yang sama (mis. selalu jurnal dengan ID besar/di akhir
        // urutan default) -- lintas beberapa minggu, semua jurnal tetap
        // kebagian giliran ter-update.
        shuffle($journals);

        $service = new SintaScoreService();
        $startTime = time();
        $processedCount = 0;
        $skippedByBudget = 0;
        $timeBudget = self::TIME_BUDGET_SECONDS;

        foreach ($journals as $journal) {
            if ((time() - $startTime) >= $timeBudget) {
                $skippedByBudget++;
                continue; // Anggaran habis -- jangan mulai jurnal baru, tapi tetap hitung sisanya untuk log.
            }

            $this->_refreshJournalScore($journal, $service);
            $processedCount++;
            usleep(300000); // 0.3 detik -- tetap sopan terhadap server SINTA, tanpa menghabiskan anggaran waktu secara berlebihan.
        }

        if ($skippedByBudget > 0) {
            error_log("SintaScoreTask: anggaran waktu ({$timeBudget}s) tercapai -- $processedCount jurnal diproses, $skippedByBudget dilewati (akan dicoba lagi minggu depan).");
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
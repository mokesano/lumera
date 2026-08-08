<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/JournalOwnershipService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class JournalOwnershipService
 *
 * @brief SATU-SATUNYA titik penentu status Ownership/Partnership jurnal --
 * dipakai bersama oleh logika Payment, DOI, dan Publisher Institution
 * supaya tidak ada lagi pengecekan `!$journal->getSetting('publisherPartnerships')`
 * yang ditulis ulang tersebar di banyak file dengan risiko salah satu lupa
 * menangani kasus NULL dengan benar.
 *
 * [BUGFIX KRITIS] `!$journal->getSetting('publisherPartnerships')` yang
 * ditulis tersebar sebelumnya SALAH untuk jurnal yang BELUM PERNAH disimpan
 * sama sekali (baris journal_settings tidak ada -- getSetting() balikin
 * null). PHP: `!null` == true -- jadi jurnal yang belum pernah disentuh
 * SAMA SEKALI otomatis dianggap Ownership (TERKUNCI), padahal semestinya
 * dianggap Partnership (BEBAS, seperti perilaku OJS sebelum fitur ini ada)
 * sampai Site Admin BENAR-BENAR secara eksplisit menyimpan status Ownership
 * untuk jurnal itu. Kelas ini menegakkan aturan itu di SATU tempat.
 */

class JournalOwnershipService {

    /**
     * Apakah jurnal ini Ownership (bagian kepemilikan penerbit -- payment,
     * DOI, dan publisher institution dikelola terpusat, TERKUNCI dari JM)?
     *
     * ATURAN: HANYA true kalau ADA nilai publisherPartnerships tersimpan
     * SECARA EKSPLISIT dan nilainya falsy (0/false/''). Kalau BELUM PERNAH
     * tersimpan sama sekali (null), SELALU dianggap Partnership (false) --
     * default yang aman, supaya jurnal yang belum pernah dikonfigurasi
     * TIDAK tiba-tiba kehilangan akses ke pengaturan yang sudah mereka
     * pakai bertahun-tahun.
     *
     * @param object|null $journal
     * @return bool
     */
    public static function isOwnership($journal): bool {
        if (!$journal) {
            return false;
        }
        $value = $journal->getSetting('publisherPartnerships');
        if ($value === null) {
            return false;
        }
        return !$value;
    }

    /**
     * Kebalikan dari isOwnership() -- jurnal Partnership (JM kelola sendiri).
     * @param object|null $journal
     * @return bool
     */
    public static function isPartnership($journal): bool {
        return !self::isOwnership($journal);
    }

}
?>
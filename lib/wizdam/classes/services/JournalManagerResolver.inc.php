<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/JournalManagerResolver.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class JournalManagerResolver
 *
 * @brief Menentukan nama Journal Manager yang tampil sebagai penanda
 * tangan Sertifikat/LoA. Aturan:
 * - 1 manager  -> tampilkan nama itu.
 * - 2 manager  -> tampilkan keduanya.
 * - >2 manager -> WAJIB pilihan eksplisit (journal setting
 *   'certificateSignatoryUserId', diatur Site Admin lewat form edit jurnal
 *   -- BUKAN oleh Journal Manager sendiri). Kalau belum pernah dipilih,
 *   fallback ke manager pertama (urutan role), ditandai isFallback=true.
 */

class JournalManagerResolver {

    /**
     * @param int $journalId
     * @return array ['names' => string[], 'count' => int, 'isFallback' => bool]
     */
    public function resolve(int $journalId): array {
        $managers = $this->_getManagers($journalId);
        $count = count($managers);

        if ($count <= 2) {
            return [
                'names' => array_map(fn($u) => $u->getFullName(), $managers),
                'count' => $count,
                'isFallback' => false,
            ];
        }

        /** @var JournalDAO $journalDao */
        $journalDao = DAORegistry::getDAO('JournalDAO');
        $journal = $journalDao->getById($journalId);
        $chosenUserId = $journal ? (int) $journal->getSetting('certificateSignatoryUserId') : 0;

        foreach ($managers as $manager) {
            if ((int) $manager->getId() === $chosenUserId) {
                return ['names' => [$manager->getFullName()], 'count' => $count, 'isFallback' => false];
            }
        }

        return ['names' => [$managers[0]->getFullName()], 'count' => $count, 'isFallback' => true];
    }

    /**
     * @param int $journalId
     * @return array daftar kandidat lengkap untuk UI seleksi Site Admin:
     * [['id', 'name', 'username', 'email', 'affiliation', 'photoUrl'], ...]
     * Username dijamin unik di seluruh sistem -- disambiguator utama kalau
     * dua atau lebih Journal Manager kebetulan punya nama sama persis.
     * photoUrl otomatis fallback ke Gravatar kalau pengguna belum unggah
     * foto profil sendiri (lihat User::getProfileImageUrl()).
     */
    public function getCandidates(int $journalId): array {
        $managers = $this->_getManagers($journalId);
        return array_map(function ($u) {
            return [
                'id' => (int) $u->getId(),
                'name' => $u->getFullName(),
                'username' => $u->getUsername(),
                'email' => $u->getEmail(),
                'affiliation' => $u->getLocalizedAffiliation(),
                'photoUrl' => $u->getProfileImageUrl(),
            ];
        }, $managers);
    }

    /**
     * @param int $journalId
     * @return array<object> daftar objek User dengan peran Journal Manager
     */
    private function _getManagers(int $journalId): array {
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');
        $result = $roleDao->getUsersByRoleId(ROLE_ID_JOURNAL_MANAGER, $journalId);

        $managers = [];
        if ($result) {
            while ($user = $result->next()) {
                $managers[] = $user;
            }
        }
        return $managers;
    }

}
?>
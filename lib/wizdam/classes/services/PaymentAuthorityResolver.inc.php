<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/PaymentAuthorityResolver.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class PaymentAuthorityResolver
 *
 * @brief Menentukan SIAPA yang berwenang mengkonfirmasi Transfer Bank
 * untuk sebuah jurnal -- "siapa yang atur kredensial pembayaran, dialah
 * yang berwenang konfirmasi":
 * - Jurnal Ownership (publisherPartnerships=false): HANYA Admin Publisher.
 * - Jurnal Partnership (publisherPartnerships=true): Journal Manager jurnal itu.
 */

class PaymentAuthorityResolver {

    /**
     * @param object|null $journal
     * @return array<object> daftar User yang berwenang konfirmasi & perlu diberi tahu
     */
    public function getAuthorities($journal): array {
        /** @var RoleDAO $roleDao */
        $roleDao = DAORegistry::getDAO('RoleDAO');

        if ($this->isPartnerManaged($journal)) {
            $result = $roleDao->getUsersByRoleId(ROLE_ID_JOURNAL_MANAGER, (int) $journal->getId());
        } else {
            $result = $roleDao->getUsersByRoleId(ROLE_ID_SITE_ADMIN, null);
        }

        $users = [];
        if ($result) {
            while ($user = $result->next()) {
                $users[] = $user;
            }
        }
        return $users;
    }

    /**
     * @param object|null $journal
     * @return bool true kalau jurnal ini mengelola konfirmasi transfernya sendiri (Partnership)
     */
    public function isPartnerManaged($journal): bool {
        return (bool) ($journal && $journal->getSetting('publisherPartnerships'));
    }

}
?>
<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/redeem/RewardPointDAO.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance
 * @class RewardPointDAO
 * @brief Data Access Object untuk tabel reward_points. Menggunakan pendekatan Ledger.
 */

import('classes.db.DAO');

class RewardPointDAO extends DAO {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Menghitung total saldo poin (SUM of amounts)
     */
    public function getBalanceByUserId(int $userId): int {
        $result = $this->retrieve(
            'SELECT SUM(amount) as balance FROM reward_points WHERE user_id = ?',
            [$userId]
        );

        $row = $result->GetRowAssoc(false);
        $balance = $row['balance'] !== null ? (int) $row['balance'] : 0;
        $result->Close();

        return $balance;
    }

    /**
     * Mengambil riwayat mutasi poin pengguna
     */
    public function getHistoryByUserId(int $userId): array {
        $result = $this->retrieve(
            'SELECT * FROM reward_points WHERE user_id = ? ORDER BY date_added DESC',
            [$userId]
        );

        $history = [];
        while (!$result->EOF) {
            $history[] = $result->GetRowAssoc(false);
            $result->MoveNext();
        }
        $result->Close();

        return $history;
    }

    /**
     * Mencatat transaksi poin baru (positif atau negatif)
     */
    public function insertTransaction(int $userId, int $amount, string $transactionType, int $referenceId = 0): bool {
        return (bool) $this->update(
            'INSERT INTO reward_points 
            (user_id, amount, transaction_type, reference_id, date_added) 
            VALUES (?, ?, ?, ?, '.$this->datetimeToDB(Core::getCurrentDate()).')',
            [$userId, $amount, $transactionType, $referenceId]
        );
    }
}
?>
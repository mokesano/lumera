<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/RedeemService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance
 * @class RedeemService
 * @brief Layanan pengelola dompet loyalti dan penukaran poin.
 */

import('lib.wizdam.classes.redeem.RewardPointDAO');

class RedeemService {

    private RewardPointDAO $rewardDao;

    public function __construct() {
        $this->rewardDao = new RewardPointDAO();
    }

    public function getUserBalance(int $userId): int {
        return $this->rewardDao->getBalanceByUserId($userId);
    }

    public function getUserHistory(int $userId): array {
        return $this->rewardDao->getHistoryByUserId($userId);
    }

    /**
     * Logika Bisnis: Menukarkan poin menjadi Diskon/Voucher.
     * Mengamankan agar pengguna tidak bisa menukar poin melebihi saldo.
     */
    public function exchangePoints(int $userId, int $pointsToRedeem, int $invoiceId = 0): bool {
        if ($pointsToRedeem <= 0) {
            return false;
        }

        $currentBalance = $this->getUserBalance($userId);

        // Keamanan Lapis 1: Mencegah saldo minus
        if ($currentBalance < $pointsToRedeem) {
            throw new \Exception('Insufficient balance.');
        }

        // Catat transaksi sebagai angka negatif (pengurangan saldo)
        $negativeAmount = -$pointsToRedeem;
        
        return $this->rewardDao->insertTransaction($userId, $negativeAmount, 'redeemed_discount', $invoiceId);
    }

    /**
     * Disediakan untuk CartService (Integrasi Lintas Domain yang kita buat sebelumnya)
     */
    public function calculateApplicableDiscount(int $userId, float $subtotal): float {
        // Konversi poin ke nominal mata uang. Misal: 1 Poin = Rp 1.000 / $0.1
        $conversionRate = (float) Config::getVar('billing', 'point_conversion_rate') ?: 1000.0;
        
        // Asumsi user mencentang "Gunakan Saldo Poin" di sesi keranjang (bisa ditarik dari CartService)
        // Untuk contoh ini, kita batasi stub.
        $discountAmount = 0.0; 
        
        return min($discountAmount, $subtotal);
    }
}
?>
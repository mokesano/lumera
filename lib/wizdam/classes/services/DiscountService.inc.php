<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/DiscountService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance
 * @class DiscountService
 * @brief Layanan pengelola potongan harga/diskon berdasarkan pengaturan Jurnal.
 */

class DiscountService {

    /**
     * Mengambil nilai nominal diskon tetap dari pengaturan jurnal.
     * @param int $journalId
     * @return float
     */
    public function getFixedDiscount(int $journalId): float {
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $discount = (float) $journalSettingsDao->getSetting($journalId, 'paymentDiscount');
        return $discount > 0 ? $discount : 0.00;
    }

    /**
     * Menghitung nilai yang bisa dikenakan pajak (Taxable Amount) setelah didiskon.
     * Sesuai logika App: subtotal - diskon (dan tidak boleh di bawah 0).
     * @param float $subtotal
     * @param float $discount
     * @return float
     */
    public function calculateTaxableAmount(float $subtotal, float $discount): float {
        $taxableAmount = $subtotal - $discount;
        return $taxableAmount < 0 ? 0.00 : $taxableAmount;
    }
}
?>
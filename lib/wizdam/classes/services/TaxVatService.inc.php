<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/services/TaxVatService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance
 * @class TaxVatService
 * @brief Layanan pengelola kalkulasi Pajak (Inklusif/Eksklusif) sesuai standar.
 */

class TaxVatService {

    /**
     * Menghitung nilai pajak dan grand total berdasarkan pengaturan jurnal.
     * @param int $journalId
     * @param float $taxableAmount Nilai subtotal yang sudah dikurangi diskon
     * @return array Array asosiatif berisi rincian kalkulasi pajak
     */
    public function calculateTaxAndTotal(int $journalId, float $taxableAmount): array {
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        
        // Mengambil pengaturan persentase dan mode inklusif
        $settingTaxRate = (float) $journalSettingsDao->getSetting($journalId, 'paymentTax');
        $isTaxInclusive = (bool) $journalSettingsDao->getSetting($journalId, 'paymentTaxInclusive');

        $taxRate = $settingTaxRate > 0 ? ($settingTaxRate / 100) : 0.00;

        $taxAmount = 0.00;
        $finalAmount = $taxableAmount;

        if ($taxRate > 0) {
            if ($isTaxInclusive) {
                // Jika Inklusif: Harga akhir sama dengan taxableAmount. 
                // Pajaknya di-ekstrak dari dalam untuk keperluan pelaporan invoice.
                // Rumus akuntansi: Pajak = Harga Total - (Harga Total / (1 + Persentase))
                $taxAmount = $taxableAmount - ($taxableAmount / (1 + $taxRate));
                $finalAmount = $taxableAmount;
            } else {
                // Jika Eksklusif (Standar): Harga akhir adalah taxableAmount + beban pajak
                $taxAmount = $taxableAmount * $taxRate;
                $finalAmount = $taxableAmount + $taxAmount;
            }
        }

        return [
            'tax_rate_percent' => $settingTaxRate,
            'is_inclusive' => $isTaxInclusive,
            'tax_amount' => $taxAmount,
            'final_amount' => $finalAmount
        ];
    }
}
?>
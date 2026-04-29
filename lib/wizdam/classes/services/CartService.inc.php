<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/CartService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @class CartService
 * @brief Layanan pengelola keranjang belanja (B2C) yang mengorkestrasi logika 
 * database dan kalkulasi finansial melalui Service Layer terkait.
 */

import('lib.wizdam.classes.cart.CartItemDAO');
import('lib.wizdam.classes.services.TaxVatService');
import('lib.wizdam.classes.services.RedeemService');
import('lib.wizdam.classes.services.DiscountService');

class CartService {
    
    private CartItemDAO $cartDao;
    private TaxVatService $taxVatService;
    private RedeemService $redeemService;
    private DiscountService $discountService;

    /**
     * Constructor CartService
     */
    public function __construct() {
        // Instansiasi seluruh dependensi layanan dan DAO
        $this->cartDao = new CartItemDAO();
        $this->taxVatService = new TaxVatService();
        $this->redeemService = new RedeemService();
        $this->discountService = new DiscountService();
    }

    /**
     * Menambahkan item ke dalam keranjang pengguna.
     * Mencegah duplikasi dengan mengecek database terlebih dahulu.
     */
    public function addItem(int $userId, string $itemType, int $itemReferenceId, string $itemTitle, float $unitPrice, int $quantity = 1): bool {
        $existingItem = $this->cartDao->checkItemExists($userId, $itemType, $itemReferenceId);

        if ($existingItem !== null) {
            $newQuantity = (int)$existingItem['quantity'] + $quantity;
            return $this->cartDao->updateQuantity((int)$existingItem['cart_item_id'], $newQuantity);
        }

        return $this->cartDao->insertCartItem($userId, $itemType, $itemReferenceId, $itemTitle, $unitPrice, $quantity);
    }

    /**
     * Mengambil seluruh isi keranjang milik seorang pengguna.
     */
    public function getUserCart(int $userId): array {
        return $this->cartDao->getItemsByUserId($userId);
    }

    /**
     * Menghapus satu baris item dari keranjang.
     */
    public function removeItem(int $userId, int $cartItemId): bool {
        return $this->cartDao->deleteItem($userId, $cartItemId);
    }

    /**
     * Mengosongkan seluruh keranjang (Dijalankan pasca-checkout).
     */
    public function clearCart(int $userId): bool {
        return $this->cartDao->deleteItemsByUserId($userId);
    }

    /**
     * Menghitung ringkasan keranjang dengan mendelegasikan tugas finansial ke service terkait,
     * serta menggunakan format Locale dinamis.
     */
    public function calculateSummary(array $cartItems, int $journalId): array {
        $subtotal = 0.0;
        
        foreach ($cartItems as $item) {
            $subtotal += ((float) $item['unit_price'] * (int) $item['quantity']);
        }
    
        // 1. Terapkan Diskon melalui DiscountService
        $discount = $this->discountService->getFixedDiscount($journalId);
        $taxableSubtotal = $this->discountService->calculateTaxableAmount($subtotal, $discount);
    
        // 2. Kalkulasi Pajak & Grand Total melalui TaxVatService
        $taxResult = $this->taxVatService->calculateTaxAndTotal($journalId, $taxableSubtotal);
    
        // 3. Persiapkan Currency
        $journalSettingsDao = DAORegistry::getDAO('JournalSettingsDAO');
        $currencyCode = $journalSettingsDao->getSetting($journalId, 'currency') ?: 'USD';
    
        return [
            'subtotal'         => $subtotal,
            'discount_amount'  => $discount,
            'taxable_subtotal' => $taxableSubtotal,
            'tax_percentage'   => $taxResult['tax_rate_percent'],
            'is_tax_inclusive' => $taxResult['is_inclusive'],
            'tax_amount'       => $taxResult['tax_amount'],
            'total'            => $taxResult['final_amount'],
            'currency'         => $currencyCode,
            'total_formatted'  => $this->_formatCurrency($taxResult['final_amount'], $currencyCode, AppLocale::getLocale())
        ];
    }

    /**
     * Helper Privat: Memformat angka sesuai dengan standar Locale global yang aktif.
     */
    private function _formatCurrency(float $amount, string $currencyCode, string $locale): string {
        // 1. Prioritas Utama: Gunakan ekstensi Intl PHP (Standar Emas Globalisasi)
        if (extension_loaded('intl') && class_exists('NumberFormatter')) {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($amount, $currencyCode);
            if ($formatted !== false) {
                return $formatted;
            }
        }

        // 2. Fallback Global: Jika Intl tidak aktif di server (Dinamis berdasarkan keluarga bahasa)
        // Ekstrak 2 huruf pertama dari kode locale (misal: 'id_ID' -> 'id', 'fr_CA' -> 'fr')
        $langCode = strtolower(substr($locale, 0, 2));

        // Daftar kode bahasa ISO-639-1 yang secara umum menggunakan koma (,) sebagai desimal
        // (Mencakup Eropa Daratan, Amerika Latin, Indonesia, Vietnam, dll)
        $commaDecimalLanguages = [
            'id', 'de', 'fr', 'es', 'it', 'nl', 'pt', 'ru', 'tr', 
            'vi', 'pl', 'sv', 'da', 'fi', 'no', 'cs', 'hu', 'ro', 'el'
        ];

        if (in_array($langCode, $commaDecimalLanguages)) {
            // Format Continental/European: 1.000.000,00
            return $currencyCode . ' ' . number_format($amount, 2, ',', '.');
        } else {
            // Format Anglo-Saxon/Internasional default (en, zh, ja, ar, dll): 1,000,000.00
            return $currencyCode . ' ' . number_format($amount, 2, '.', ',');
        }
    }
}
?>
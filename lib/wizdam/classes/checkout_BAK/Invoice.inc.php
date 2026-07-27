<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/Invoice.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * @class Invoice
 * @brief Data Object yang merepresentasikan satu entitas Tagihan (Global Level).
 */

import('lib.pkp.classes.core.DataObject');

class Invoice extends DataObject {
    
    // Konstanta Semantik Wizdam
    const FEE_TYPE_MEMBERSHIP = 'MEMBERSHIP';
    const FEE_TYPE_RENEW_SUBSCRIPTION = 'RENEW_SUBSCRIPTION';
    const FEE_TYPE_PURCHASE_ARTICLE = 'PURCHASE_ARTICLE';
    const FEE_TYPE_DONATION = 'DONATION';
    const FEE_TYPE_SUBMISSION = 'SUBMISSION';
    const FEE_TYPE_FAST_TRACK = 'FAST_TRACK';
    const FEE_TYPE_PUBLICATION = 'PUBLICATION';
    const FEE_TYPE_PURCHASE_SUBSCRIPTION = 'PURCHASE_SUBSCRIPTION';
    const FEE_TYPE_PURCHASE_ISSUE = 'PURCHASE_ISSUE';
    const FEE_TYPE_GIFT = 'GIFT';

    const STATUS_UNPAID = 'UNPAID';
    const STATUS_PAID = 'PAID';
    const STATUS_CANCELLED = 'CANCELLED';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    // --- GETTERS ---
    /**
     * Mendapatkan ID tagihan
     * @return int ID tagihan
     */
    public function getInvoiceId(): int {
        return (int) $this->getData('invoiceId');
    }

    /**
     * Mendapatkan ID pengguna
     * @return int ID pengguna
     */
    public function getUserId(): int {
        return (int) $this->getData('userId');
    }

    /**
     * Mendapatkan tipe asosiasi
     * @return int tipe asosiasi
     */
    public function getAssocType(): int {
        return (int) $this->getData('assocType');
    }

    /**
     * Mendapatkan ID asosiasi
     * @return int ID asosiasi
     */
    public function getAssocId(): int {
        return (int) $this->getData('assocId');
    }

    /**
     * Mendapatkan tipe biaya
     * @return string tipe biaya
     */

    public function getFeeType(): string {
        return (string) $this->getData('feeType');
    }

    /**
     * Mendapatkan jumlah tagihan
     * @return float jumlah tagihan
     */
    public function getAmount(): float {
        return (float) $this->getData('amount');
    }

    /**
     * Mendapatkan kode mata uang
     * @return string kode mata uang
     */
    public function getCurrencyCode(): string {
        return (string) $this->getData('currencyCode');
    }

    /**
     * Mendapatkan status tagihan
     * @return string status tagihan
     */
    public function getStatus(): string {
        return (string) $this->getData('status');
    }

    /**
     * Mendapatkan metode pembayaran
     * @return string|null metode pembayaran, null jika tidak ditentukan
     */
    public function getPaymentMethod(): ?string {
        return $this->getData('paymentMethod');
    }

    /**
     * Memeriksa apakah tagihan adalah legacy
     * @return bool true jika tagihan ini adalah legacy
     */
    public function isLegacy(): bool {
        return (bool) $this->getData('isLegacy');
    }

    // --- SETTERS ---
    /**
     * Menetapkan tipe biaya
     * @param string $feeType tipe biaya
     */
    public function setFeeType($feeType) {
        $this->setData('feeType', $feeType);
    }

    /**
     * Menetapkan ID pengguna
     * @param int $userId ID pengguna
     */
    public function setUserId(int $userId): void {
        $this->setData('userId', $userId);
    }

    /**
     * Menetapkan tipe asosiasi
     * @param int $assocType tipe asosiasi
     */
    public function setAssocType(int $assocType): void {
        $this->setData('assocType', $assocType);
    }

    /**
     * Menetapkan ID asosiasi
     * @param int $assocId ID asosiasi
     */
    public function setAssocId(int $assocId): void {
        $this->setData('assocId', $assocId);
    }

    /**
     * Memeriksa apakah tagihan sudah dibayar lunas
     * @return bool true jika tagihan sudah dibayar lunas
     */
    public function isPaid(): bool {
        // Jika ini adalah legacy payment dari OJS, biasanya dianggap lunas (karena masuk ke completed_payments)
        if ($this->isLegacy()) {
            return true; 
        }
        return $this->getStatus() === 'PAID';
    }
}
?>
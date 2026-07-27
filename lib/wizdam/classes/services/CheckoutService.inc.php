<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/CheckoutService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class CheckoutService
 * 
 * @brief Domain-Driven Design: Menangani siklus 3-Tahap Checkout sebelum Tagihan (Invoice) resmi diterbitkan.
 * Menggunakan tabel 'queued_payments' sebagai pangkalan data keranjang sementara (Stateful Cart).
 */

import('lib.wizdam.classes.checkout.CheckoutSession');
import('lib.wizdam.classes.checkout.CheckoutSessionDAO');
import('lib.wizdam.classes.services.InvoiceService');

class CheckoutService {

    /** @var CheckoutSessionDAO */
    private CheckoutSessionDAO $sessionDao;

    /** @var InvoiceService */
    private InvoiceService $invoiceService;

    /**
     * Constructor
     */
    public function __construct() {
        $this->sessionDao = DAORegistry::getDAO('CheckoutSessionDAO');
        $this->invoiceService = new InvoiceService();
    }

    // 
    // TAHAP 1: INISIALISASI KERANJANG (CART)
    // 

    /**
     * Membuat atau mengambil keranjang sementara di tabel queued_payments.
     * @param int $publisherOrJournalId
     * @param int $userId
     * @param int $articleId
     * @param string $baseFeeType
     * @param float $baseAmount
     * @param string $currencyCode
     * @return int
     */
    public function initCart(int $publisherOrJournalId, int $userId, int $articleId, string $baseFeeType, float $baseAmount, string $currencyCode): int {
        $request = Application::get()->getRequest();
        $session = $request->getSession();

        $cartKey = "checkout_{$baseFeeType}_{$articleId}";
        $sessionId = (int) $session->getSessionVar($cartKey);

        if ($sessionId > 0) {
            $existing = $this->sessionDao->getSession($sessionId);
            if ($existing) return $sessionId;
        }

        $checkoutSession = new CheckoutSession($baseAmount, $currencyCode, $userId, $articleId);
        $checkoutSession->setJournalId($publisherOrJournalId);
        $checkoutSession->setType($baseFeeType);
        $checkoutSession->setCheckoutPayload([
            'base_item' => ['type' => $baseFeeType, 'amount' => $baseAmount],
            'additional_items' => [],
            'promo_code' => null,
            'discount_amount' => 0.0,
            'billing_address' => []
        ]);

        $newSessionId = $this->sessionDao->insertSession($checkoutSession);
        $session->setSessionVar($cartKey, $newSessionId);

        return $newSessionId;
    }

    /**
     * Memperbarui rincian di dalam keranjang (Exp: Fast-Track or Promo)
     * @param int $queuedPaymentId
     * @param array $additionalItems
     * @param string|null $promoCode
     * @param float $discountAmount
     * @return bool
     */
    public function updateCartItems(int $queuedPaymentId, array $additionalItems, ?string $promoCode, float $discountAmount): bool {
        $checkoutSession = $this->sessionDao->getSession($queuedPaymentId);
        if (!$checkoutSession) return false;

        $payload = $checkoutSession->getCheckoutPayload();
        $payload['additional_items'] = $additionalItems;
        $payload['promo_code'] = $promoCode;
        $payload['discount_amount'] = $discountAmount;

        $checkoutSession->setCheckoutPayload($payload);
        $this->sessionDao->updateSession($queuedPaymentId, $checkoutSession);
        return true;
    }

    // 
    // TAHAP 2: ALAMAT PENAGIHAN (BILLING ADDRESS)
    // 

    /**
     * Menyimpan alamat penagihan institusi/pribadi dalam payload keranjang.
     * @param int $queuedPaymentId
     * @param array $billingData
     * @return bool
     */
    public function updateBillingAddress(int $queuedPaymentId, array $billingData): bool {
        $checkoutSession = $this->sessionDao->getSession($queuedPaymentId);
        if (!$checkoutSession) return false;

        $payload = $checkoutSession->getCheckoutPayload();
        $payload['billing_address'] = [
            'name'        => $billingData['name'] ?? '',
            'institution' => $billingData['institution'] ?? '',
            'address'     => $billingData['address'] ?? '',
            'city'        => $billingData['city'] ?? '',
            'country'     => $billingData['country'] ?? '',
            'postal_code' => $billingData['postal_code'] ?? '',
        ];

        $checkoutSession->setCheckoutPayload($payload);
        $this->sessionDao->updateSession($queuedPaymentId, $checkoutSession);
        return true;
    }

    // 
    // HELPER: KALKULASI REAL-TIME (Tampil di View)
    // 

    /**
     * Menghitung total keranjang secara real-time berdasarkan isi payload.
     * @param int $queuedPaymentId
     * @return array
     */
    public function calculateCartSummary(int $queuedPaymentId): array {
        $checkoutSession = $this->sessionDao->getSession($queuedPaymentId);
        if (!$checkoutSession) throw new \Exception('Keranjang tidak ditemukan.');

        $payload = $checkoutSession->getCheckoutPayload();
        $baseAmount = (float) ($payload['base_item']['amount'] ?? $checkoutSession->getAmount());
        $additionalAmount = 0.0;
        foreach (($payload['additional_items'] ?? []) as $item) {
            $additionalAmount += (float) ($item['amount'] ?? 0);
        }

        $subtotal = $baseAmount + $additionalAmount;
        $discount = (float) ($payload['discount_amount'] ?? 0);
        $taxableAmount = max(0, $subtotal - $discount);

        $settingTaxRate = 0;
        $isTaxInclusive = false;
        if ($checkoutSession->getJournalId() > 0) {
            /** @var JournalDAO $journalDao */
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($checkoutSession->getJournalId());
            if ($journal) {
                $settingTaxRate = (float) $journal->getSetting('paymentTax');
                $isTaxInclusive = (bool) $journal->getSetting('paymentTaxInclusive');
            }
        }
        $taxRate = $settingTaxRate > 0 ? ($settingTaxRate / 100) : 0.00;

        if ($isTaxInclusive) {
            $baseForVat = $taxableAmount / (1 + $taxRate);
            $taxAmount = $taxableAmount - $baseForVat;
            $grandTotal = $taxableAmount;
        } else {
            $taxAmount  = round($taxableAmount * $taxRate, 2);
            $grandTotal = round($taxableAmount + $taxAmount, 2);
        }

        return [
            'base_amount'      => $baseAmount,
            'additional_items' => $payload['additional_items'] ?? [],
            'subtotal'         => $subtotal,
            'discount'         => $discount,
            'promo_code'       => $payload['promo_code'] ?? null,
            'tax_amount'       => $taxAmount,
            'tax_rate'         => $settingTaxRate,
            'is_tax_inclusive' => $isTaxInclusive,
            'grand_total'      => $grandTotal,
            'currency'         => $checkoutSession->getCurrencyCode(),
            'billing_address'  => $payload['billing_address'] ?? []
        ];
    }

    // 
    // TAHAP 3: FINALISASI & EKSEKUSI (DATABASE HIT KE INVOICES)
    // 

    /**
     * Krusial! Mengubah status keranjang sementara menjadi Invoice Resmi.
     * Memanggil InvoiceService untuk mencetak Invoice Number permanen.
     * @param int $queuedPaymentId
     * @return \Invoice
     */
    public function finalizeCheckout(int $queuedPaymentId): \Invoice {
        $checkoutSession = $this->sessionDao->getSession($queuedPaymentId);
        if (!$checkoutSession) throw new \Exception(__('checkout.error.cartNotFound'));

        $summary = $this->calculateCartSummary($queuedPaymentId);
        $payload = $checkoutSession->getCheckoutPayload();
        $hasAdditional = !empty($payload['additional_items']);
        $finalFeeType = $hasAdditional ? 'BUNDLE_PAYMENT' : $checkoutSession->getType();

        $invoice = $this->invoiceService->generateInvoice(
            (int) $checkoutSession->getJournalId(),
            (int) $checkoutSession->getUserId(),
            (int) $checkoutSession->getAssocId(),
            $finalFeeType,
            (float) $summary['grand_total'],
            $summary['currency']
        );

        $this->sessionDao->deleteSession($queuedPaymentId);

        $request = Application::get()->getRequest();
        $request->getSession()->unsetSessionVar("checkout_{$checkoutSession->getType()}_{$checkoutSession->getAssocId()}");

        return $invoice;
    }

}
?>
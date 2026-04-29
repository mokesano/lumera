<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/services/CheckoutService.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CheckoutService
 * 
 * @brief Domain-Driven Design: Menangani siklus 3-Tahap Checkout sebelum Tagihan (Invoice) resmi diterbitkan.
 * 
 * Menggunakan tabel 'queued_payments' sebagai pangkalan data keranjang sementara (Stateful Cart).
 */

import('classes.payment.ojs.OJSQueuedPayment');
import('lib.wizdam.classes.services.InvoiceService');

class CheckoutService {

    /** @var QueuedPaymentDAO */
    private $queuedPaymentDao;

    /** @var InvoiceService */
    private InvoiceService $invoiceService;

    /**
     * Constructor
     */
    public function __construct() {
        $this->queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
        $this->invoiceService = new InvoiceService();
    }

    // 
    // TAHAP 1: INISIALISASI KERANJANG (CART)
    // 

    /**
     * Membuat atau mengambil keranjang sementara di tabel queued_payments.
     * @param int $journalId ID jurnal terkait
     * @param int $userId ID pengguna yang melakukan checkout
     * @param int $articleId ID naskah/artikel yang sedang diproses
     * @param string $baseFeeType Tipe biaya dasar (misal: 'SUBMISSION_FEE', 'PUBLICATION_FEE')
     * @param float $baseAmount Jumlah biaya dasar
     * @param string $currencyCode Kode mata uang (misal: 'USD', 'IDR')
     * @return int ID dari keranjang yang sudah dibuat atau ditemukan
     */
    public function initCart(
        int $publisherOrJournalId, 
        int $userId, 
        int $articleId, 
        string $baseFeeType, 
        float $baseAmount, 
        string $currencyCode
    ): int {
        $request = Application::get()->getRequest();
        $session = $request->getSession();
        
        // Tracking ID keranjang user untuk sesi ini
        $cartKey = "wizdam_cart_{$baseFeeType}_{$articleId}";
        $queuedPaymentId = (int) $session->getSessionVar($cartKey);

        // 1. Validasi keranjang yang menggantung (Abandoned Cart) di Database
        if ($queuedPaymentId > 0) {
            $existingPayment = $this->queuedPaymentDao->getQueuedPayment($queuedPaymentId);
            if ($existingPayment) {
                return $queuedPaymentId; // Gunakan keranjang yang sudah ada di DB
            }
        }

        // 2. Buat Record Baru menggunakan Objek yang sudah kita modifikasi
        $payment = new OJSQueuedPayment($baseAmount, $currencyCode, $userId, $articleId);
        
        $payment->setJournalId($publisherOrJournalId);
        $payment->setType($baseFeeType);
        
        // 3. Susun rincian 3-Tahap
        $payload = [
            'base_item' => [
                'type' => $baseFeeType,
                'amount' => $baseAmount
            ],
            'additional_items' => [], 
            'promo_code' => null,
            'discount_amount' => 0.0,
            'billing_address' => []   
        ];
        
        // Suntikkan array ke properti baru yang kita buat di OJSQueuedPayment
        $payment->setCheckoutPayload($payload);
        
        // 4. Simpan ke database (DAO akan melakukan serialize seluruh objek termasuk payload)
        $newQueuedPaymentId = (int) $this->queuedPaymentDao->insertQueuedPayment($payment);

        // 5. Catat kunci pelacakan
        $session->setSessionVar($cartKey, $newQueuedPaymentId);

        return $newQueuedPaymentId;
    }

    /**
     * Memperbarui rincian di dalam keranjang (Exp: Fast-Track or Promo)
     * @param int $queuedPaymentId ID keranjang yang ingin diperbarui
     * @param array $additionalItems Array item tambahan yang ingin ditambahkan
     * @param string|null $promoCode Kode promo yang diterapkan (jika ada)
     * @param float $discountAmount Jumlah diskon yang diterapkan (jika ada)
     * @return bool True pembaruan berhasil, False keranjang tidak ditemukan
     */
    public function updateCartItems(int $queuedPaymentId, array $additionalItems, ?string $promoCode, float $discountAmount): bool {
        $payment = $this->queuedPaymentDao->getQueuedPayment($queuedPaymentId);
        if (!$payment) return false;

        $payload = $payment->getCheckoutPayload();
        $payload['additional_items'] = $additionalItems;
        $payload['promo_code'] = $promoCode;
        $payload['discount_amount'] = $discountAmount;

        $payment->setCheckoutPayload($payload);
        $this->queuedPaymentDao->updateQueuedPayment($queuedPaymentId, $payment);
        return true;
    }

    // 
    // TAHAP 2: ALAMAT PENAGIHAN (BILLING ADDRESS)
    // 

    /**
     * Menyimpan alamat penagihan institusi/pribadi dalam payload keranjang.
     * @param int $queuedPaymentId ID keranjang yang ingin diperbarui
     * @param array $billingData Array data alamat penagihan (format: ['name
     * @param string $name Nama lengkap untuk tagihan
     * @param string $institution Nama institusi (jika ada)
     * @param string $address Alamat lengkap
     * @param string $city Kota
     * @param string $country Negara
     * @param string $postal_code Kode pos
     * @return bool True jika pembaruan berhasil, False jika keranjang tidak ditemukan
     */
    public function updateBillingAddress(int $queuedPaymentId, array $billingData): bool {
        $payment = $this->queuedPaymentDao->getQueuedPayment($queuedPaymentId);
        if (!$payment) return false;

        $payload = $payment->getCheckoutPayload();
        $payload['billing_address'] = [
            'name'        => $billingData['name'] ?? '',
            'institution' => $billingData['institution'] ?? '',
            'address'     => $billingData['address'] ?? '',
            'city'        => $billingData['city'] ?? '',
            'country'     => $billingData['country'] ?? '',
            'postal_code' => $billingData['postal_code'] ?? '',
        ];

        $payment->setCheckoutPayload($payload);
        $this->queuedPaymentDao->updateQueuedPayment($queuedPaymentId, $payment);
        return true;
    }

    // 
    // HELPER: KALKULASI REAL-TIME (Tampil di View)
    // 

    /**
     * Menghitung total keranjang secara real-time berdasarkan isi payload.
     * @param int $queuedPaymentId ID keranjang yang ingin dihitung
     * @return array Rincian subtotal, diskon, pajak, dan total akhir
     */
    public function calculateCartSummary(int $queuedPaymentId): array {
        $payment = $this->queuedPaymentDao->getQueuedPayment($queuedPaymentId);
        if (!$payment) throw new \Exception('Keranjang tidak ditemukan.');

        $payload = $payment->getCheckoutPayload();
        $baseAmount = (float) ($payload['base_item']['amount'] ?? $payment->getAmount());
        $additionalAmount = 0.0;
        
        foreach (($payload['additional_items'] ?? []) as $item) {
            $additionalAmount += (float) ($item['amount'] ?? 0);
        }

        $subtotal = $baseAmount + $additionalAmount;
        $discount = (float) ($payload['discount_amount'] ?? 0);
        $taxableAmount = max(0, $subtotal - $discount);
        
        $settingTaxRate = 0;
        $isTaxInclusive = false;

        if ($payment->getJournalId() > 0) {
            $journalDao = DAORegistry::getDAO('JournalDAO');
            $journal = $journalDao->getById($payment->getJournalId());
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
            'currency'         => $payment->getCurrencyCode(),
            'billing_address'  => $payload['billing_address'] ?? []
        ];
    }

    // 
    // TAHAP 3: FINALISASI & EKSEKUSI (DATABASE HIT KE INVOICES)
    // 

    /**
     * Krusial! Mengubah status keranjang sementara menjadi Invoice Resmi.
     * Memanggil InvoiceService untuk mencetak Invoice Number permanen.
     * @param int $queuedPaymentId ID keranjang yang akan difinalisasi
     * @return \Invoice Objek Invoice yang sudah tercetak
     */
    public function finalizeCheckout(int $queuedPaymentId): \Invoice {
        $payment = $this->queuedPaymentDao->getQueuedPayment($queuedPaymentId);
        if (!$payment) throw new \Exception(__('checkout.error.cartNotFound'));

        $summary = $this->calculateCartSummary($queuedPaymentId);
        $payload = $payment->getCheckoutPayload();
        
        $hasAdditional = !empty($payload['additional_items']);
        $finalFeeType = $hasAdditional ? 'BUNDLE_PAYMENT' : $payment->getType();

        // 1. DATABASE HIT: Cetak Invoice Permanen WIZDAM
        $invoice = $this->invoiceService->generateInvoice(
            (int) $payment->getJournalId(),
            (int) $payment->getUserId(),
            (int) $payment->getAssocId(),
            $finalFeeType,
            (float) $summary['grand_total'],
            $summary['currency']
        );
        
        // 2. Bersihkan antrean karena transaksi sudah final
        $this->queuedPaymentDao->deleteQueuedPayment($queuedPaymentId);
        
        $request = Application::get()->getRequest();
        $request->getSession()->unsetSessionVar("wizdam_cart_{$payment->getType()}_{$payment->getAssocId()}");

        return $invoice;
    }
}
?>
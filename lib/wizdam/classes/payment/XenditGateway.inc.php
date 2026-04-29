<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/payment/XenditGateway.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @class XenditGateway
 * @brief Adapter spesifik untuk Xendit PHP Library (v7.0.0+)
 */

require_once(Core::getBaseDir() . '/lib/wizdam/library/autoload.php');

import('lib.wizdam.classes.payment.PaymentGatewayInterface');
import('lib.wizdam.classes.invoice.Invoice');

use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;

class XenditGateway implements PaymentGatewayInterface {
    
    // [SECURITY SHIELD] Simpan instance API dan webhook token secara privat
    private InvoiceApi $apiInstance;
    private string $webhookToken; // Token dari dashboard Xendit untuk verifikasi

    /**
     * Constructor: Inisialisasi API Key dan Webhook Token
     * @param string $apiKey API Key dari Xendit Dashboard
     * @param string $webhookToken (Optional)
     * @throws \RuntimeException
     */
    public function __construct(string $apiKey, string $webhookToken = '') {
        Configuration::getDefaultConfiguration()->setApiKey('apikey', $apiKey);
        $this->apiInstance = new InvoiceApi();
        $this->webhookToken = $webhookToken;
    }

    /**
     * Buat URL pembayaran untuk invoice tertentu
     * @param Invoice $invoice
     * @param array $customerData (Optional) Data tambahan seperti email, nama depan, dll.
     * @return string URL pembayaran yang dapat digunakan pelanggan
     * @throws \RuntimeException
     */
    public function getPaymentCheckoutData(Invoice $invoice, array $customerData = [], string $paymentType = 'all'): array {
        $invoiceData = [
            'external_id' => 'WIZDAM-X-' . $invoice->getInvoiceId() . '-' . time(),
            'amount' => (float) $invoice->getAmount(),
            'description' => 'Wizdam Billing: ' . $invoice->getFeeType(),
            'payer_email' => $customerData['email'] ?? 'no-reply@wizdam.com',
            'customer' => [
                'given_names' => $customerData['first_name'] ?? 'User',
            ],
            'currency' => $invoice->getCurrencyCode() ?: 'IDR'
        ];

        // [WIZDAM UX] Kunci Iframe Xendit hanya pada metode yang dipilih user
        if ($paymentType === 'qris') {
            $invoiceData['payment_methods'] = ['QRIS'];
        } elseif ($paymentType === 'bank_transfer') {
            $invoiceData['payment_methods'] = ['BCA', 'BNI', 'BRI', 'MANDIRI', 'PERMATA', 'BSI'];
        }

        $createInvoiceRequest = new CreateInvoiceRequest($invoiceData);

        try {
            $result = $this->apiInstance->createInvoice($createInvoiceRequest);
            return [
                'gateway' => 'xendit',
                'token' => '',
                'url' => $result->getInvoiceUrl()
            ];
        } catch (\Exception $e) {
            error_log("WIZDAM Xendit Error: " . $e->getMessage());
            throw new \RuntimeException("Gagal membuat link pembayaran Xendit.");
        }
    }

    /**
     * Proses webhook callback dari Xendit
     * @param array $payload Data mentah dari webhook
     * @return array|null Array dengan 'invoiceId', 'status', dan 'method' atau null jika invalid
     */
    public function processWebhook(array $payload): ?array {
        // [SECURITY SHIELD] Validasi Xendit Callback Token dari HTTP Header
        $incomingToken = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '';
        if ($this->webhookToken !== '' && !hash_equals($this->webhookToken, $incomingToken)) {
            error_log("WIZDAM SECURITY WARNING: Fake Xendit Webhook detected!");
            return null; // Tolak mentah-mentah!
        }

        if (!isset($payload['external_id']) || !isset($payload['status'])) {
            return null;
        }

        $orderParts = explode('-', $payload['external_id']);
        if (count($orderParts) < 3 || $orderParts[0] !== 'WIZDAM' || $orderParts[1] !== 'X') {
            return null;
        }

        $invoiceId = (int) $orderParts[2];
        $xenditStatus = $payload['status'];
        
        $wizdamStatus = 'UNPAID';
        if ($xenditStatus === 'PAID' || $xenditStatus === 'SETTLED') {
            $wizdamStatus = 'PAID';
        } elseif ($xenditStatus === 'EXPIRED') {
            $wizdamStatus = 'CANCELLED';
        }

        return [
            'invoiceId' => $invoiceId,
            'status' => $wizdamStatus,
            'method' => 'Xendit - ' . ($payload['payment_method'] ?? 'Unknown')
        ];
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/checkout/payment/MidtransGateway.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @class MidtransGateway
 * @brief Adapter spesifik untuk Midtrans PHP Library (v2.6.2)
 */

require_once(Core::getBaseDir() . '/lib/wizdam/library/autoload.php');

import('lib.wizdam.classes.payment.PaymentGatewayInterface');
import('lib.wizdam.classes.invoice.Invoice');

use Midtrans\Config;
use Midtrans\Snap;

class MidtransGateway implements PaymentGatewayInterface {
    
    /**
     * MidtransGateway constructor.
     * @param string $serverKey Kunci server Midtrans Anda
     * @param bool $isProduction Set true untuk mode produksi, false untuk sandbox
     */
    public function __construct(string $serverKey, bool $isProduction = false) {
        Config::$serverKey = $serverKey;
        Config::$isProduction = $isProduction;
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Mendapatkan data checkout pembayaran untuk invoice tertentu.
     * @param Invoice $invoice Objek invoice
     * @param array $customerData Data pelanggan
     * @return array Data checkout pembayaran
     */
    public function getPaymentCheckoutData(Invoice $invoice, array $customerData = []): array {
        $params = [
            'transaction_details' => [
                'order_id' => 'WIZDAM-' . $invoice->getInvoiceId() . '-' . time(), 
                'gross_amount' => (int) round($invoice->getAmount()), 
            ],
            'item_details' => [
                [
                    'id' => substr($invoice->getFeeType(), 0, 50), // Midtrans membatasi max 50 char
                    'price' => (int) round($invoice->getAmount()),
                    'quantity' => 1,
                    'name' => substr('Wizdam: ' . $invoice->getFeeType(), 0, 50)
                ]
            ],
            'customer_details' => $customerData 
        ];
        
        // [WIZDAM UX] Filter metode pembayaran langsung ke layar tujuan
        if ($paymentType === 'qris') {
            $params['enabled_payments'] = ['gopay', 'other_qris', 'shopeepay'];
        } elseif ($paymentType === 'bank_transfer') {
            $params['enabled_payments'] = ['bca_va', 'bni_va', 'bri_va', 'echannel', 'permata_va'];
        }

        try {
            $transaction = Snap::createTransaction($params);
            return [
                'gateway' => 'midtrans',
                'token' => $transaction->token,
                'url' => $transaction->redirect_url
            ];
        } catch (\Exception $e) {
            error_log("WIZDAM Midtrans Error: " . $e->getMessage());
            throw new \RuntimeException("Gagal membuat token pembayaran Midtrans.");
        }
    }

    /**
     * Memproses webhook dari Midtrans dan mengembalikan data status pembayaran.
     * @param array $payload Data payload dari webhook Midtrans
     * @return array|null Data status pembayaran atau null jika validasi gagal
     */
    public function processWebhook(array $payload): ?array {
        // Pengecekan atribut wajib Midtrans
        if (!isset($payload['order_id'], $payload['transaction_status'], $payload['status_code'], $payload['gross_amount'], $payload['signature_key'])) {
            return null;
        }

        // [SECURITY SHIELD] Validasi Signature Key Asli dari Midtrans
        $expectedSignature = hash('sha512', $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . Config::$serverKey);
        if (!hash_equals($expectedSignature, $payload['signature_key'])) {
            error_log("WIZDAM SECURITY WARNING: Fake Midtrans Webhook detected for Order ID " . $payload['order_id']);
            return null; // Tolak mentah-mentah!
        }

        $orderParts = explode('-', $payload['order_id']);
        if (count($orderParts) < 2 || $orderParts[0] !== 'WIZDAM') {
            return null;
        }

        $invoiceId = (int) $orderParts[1];
        $midtransStatus = $payload['transaction_status'];
        
        $wizdamStatus = 'UNPAID';
        if (in_array($midtransStatus, ['capture', 'settlement'])) {
            $wizdamStatus = 'PAID';
        } elseif (in_array($midtransStatus, ['deny', 'cancel', 'expire'])) {
            $wizdamStatus = 'CANCELLED';
        }

        return [
            'invoiceId' => $invoiceId,
            'status' => $wizdamStatus,
            'method' => 'Midtrans - ' . ($payload['payment_type'] ?? 'Unknown')
        ];
    }
}
?>
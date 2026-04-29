<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/payment/PaymentGatewayInterface.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @interface PaymentGatewayInterface
 * @brief Kontrak standar untuk semua Payment Gateway di ekosistem WIZDAM.
 */

import('lib.wizdam.classes.invoice.Invoice');

interface PaymentGatewayInterface {
    
    /**
     * Meminta URL/Token halaman pembayaran (Snap/Checkout URL) ke provider.
     * @param Invoice $invoice Objek tagihan WIZDAM
     * @param array $customerData Data nama/email penulis (opsional untuk struk)
     * @return string URL pembayaran yang akan diklik/di-redirect ke user
     */
    public function getPaymentCheckoutData(Invoice $invoice, array $customerData = [], string $paymentType = 'all'): array;

    /**
     * Memproses data JSON/Array dari Webhook/Callback provider.
     * Mengembalikan format standar (Universal WIZDAM Format)
     * @param array $payload Data mentah dari request POST webhook
     * @return array|null Format terstandar: ['invoiceId' => int, 'status' => string, 'method' => string]
     */
    public function processWebhook(array $payload): ?array;
}
?>
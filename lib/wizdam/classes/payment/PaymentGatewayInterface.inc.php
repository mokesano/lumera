<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/classes/payment/PaymentGatewayInterface.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @interface PaymentGatewayInterface
 * 
 * @brief Kontrak standar untuk semua Payment Gateway di ekosistem WIZDAM.
 */

import('lib.wizdam.classes.invoice.Invoice');

interface PaymentGatewayInterface {
    
    /**
     * Meminta URL/Token halaman pembayaran (Snap/Checkout URL) ke provider.
     * @param Invoice $invoice
     * @param array $customerData
     * @return array
     */
    public function getPaymentCheckoutData(Invoice $invoice, array $customerData = [], string $paymentType = 'all'): array;

    /**
     * Memproses data JSON/Array dari Webhook/Callback provider.
     * Mengembalikan format standar (Universal WIZDAM Format)
     * @param array $payload
     * @return array|null
     */
    public function processWebhook(array $payload): ?array;

}
?>
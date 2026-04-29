<?php
declare(strict_types=1);

/**
 * @file pages/billing/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @brief Route dispatcher utama untuk Domain Finansial (B2B Billing & Payment).
 * Menangani URL: /billing/...
 */

switch ($op) {
    // 
    // B2B & Personal Financial Control Center
    // 
    case 'index':     // Dasbor Tagihan Aktif (UNPAID/PENDING)
    case 'history':   // Arsip Tagihan (PAID/VOID/EXPIRED)
    case 'invoice':   // Smart Router (HTML & PDF Download) dengan Validasi Hash
    case 'pay':       // Proses Pembayaran ke Payment Gateway
    case 'cancel':    // Pembatalan Tagihan
        define('HANDLER_CLASS', 'BillingHandler');
        import('pages.billing.BillingHandler');
        break;

    // 
    // [WIZDAM CORE LOGIC] Payment Gateway Integration
    // 
    // Endpoint Callback/Webhook dari Xendit/Midtrans.
    // Menangani update status dari UNPAID menjadi PAID secara otomatis.
    case 'webhook':
        define('HANDLER_CLASS', 'WebhookHandler');
        // Asumsi WebhookHandler dipindahkan ke domain billing
        import('pages.billing.WebhookHandler'); 
        break;
}
?>
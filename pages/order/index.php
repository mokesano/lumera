<?php
declare(strict_types=1);

/**
 * @file pages/order/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.4 Strict Compliance & DDD
 * @brief Route dispatcher utama untuk Domain B2C / Publik 
 * (Shopping Cart & Checkout).
 * Menangani URL: /order/cart dan /order/checkout
 */

switch ($op) {
    case 'cart':       // Menampilkan UI Keranjang Belanja
    case 'checkout':   // Memproses isi keranjang menjadi Invoice (POST)
        define('HANDLER_CLASS', 'OrderHandler');
        import('pages.order.OrderHandler');
        break;
}
?>
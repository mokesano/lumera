<?php
declare(strict_types=1);

/**
 * @file pages/order/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 * 
 * @brief Route dispatcher utama untuk Domain B2C / Publik 
 * (Shopping Cart & Checkout).
 * Menangani URL: /order/cart dan /order/checkout
 */

/** @var string $op */
switch ($op) {
    case 'cart':       // Menampilkan UI Keranjang Belanja
    case 'checkout':   // Memproses isi keranjang menjadi Invoice (POST)
        define('HANDLER_CLASS', 'OrderHandler');
        import('pages.order.OrderHandler');
        break;
}

?>
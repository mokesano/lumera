<?php
declare(strict_types=1);

/**
 * @file pages/checkout/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @brief Route Dispatcher rute untuk proses checkout 3-tahap.
 * Menghubungkan URL ke CheckoutHandler untuk menangani Cart, Billing, dan Payment.
 */

switch ($op) {
    //
    // Cart Steps
    //
    case 'index':
    case 'cart':
    case 'checkoutSubmit': // Dont change kebab-case to camelCase
	case 'billing':
	case 'payment':
	case 'finalize':
	case 'updateCartAjax':
		define('HANDLER_CLASS', 'CheckoutHandler');
		import('pages.checkout.CheckoutHandler');
		break;
}
?>
<?php
declare(strict_types=1);

/**
 * @file pages/redeem/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 * 
 * @class RedeemHandler
 * @ingroup pages_redeem
 * 
 * @brief Route dispatcher utama untuk Domain Loyalti / Dompet Virtual.
 */

/** @var string $op */
switch ($op) {
    case 'index':    // Dasbor dompet virtual
    case 'exchange': // Proses penukaran poin
        define('HANDLER_CLASS', 'RedeemHandler');
        import('pages.redeem.RedeemHandler');
        break;
}

?>
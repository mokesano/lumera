<?php
declare(strict_types=1);

/**
 * @file pages/redeem/index.php
 *
 * [WIZDAM EDITION]
 * @brief Route dispatcher utama untuk Domain Loyalti / Dompet Virtual.
 */

switch ($op) {
    case 'index':    // Dasbor dompet virtual
    case 'exchange': // Proses penukaran poin
        define('HANDLER_CLASS', 'RedeemHandler');
        import('pages.redeem.RedeemHandler');
        break;
}
?>
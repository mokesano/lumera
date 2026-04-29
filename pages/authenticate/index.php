<?php
declare(strict_types=1);

/**
 * @file pages/authenticate/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @brief Route dispatcher publik untuk verifikasi QR Code (LoA, Invoice, Sertifikat).
 * URL ini akan di-generate dalam bentuk QR Code oleh QrCodeService WIZDAM.
 */

switch ($op) {
    //
    // Endpoint utama verifikasi
    //
    case 'index':
    // Endpoint LoA berdasarkan Hash-ID
    case 'loa':
    // Endpoint Invoice berdasarkan Hash-ID
    case 'invoice':
    // Endpoint Sertifikat (Reviewer/Author) berdasarkan Hash-ID
    case 'certificate':
        define('HANDLER_CLASS', 'AuthenticateHandler');
        import('pages.authenticate.AuthenticateHandler');
        break;
}
?>
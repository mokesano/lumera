<?php
declare(strict_types=1);

/**
 * @defgroup pages_document
 */
 
/**
 * @file pages/document/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION]
 * @brief Route dispatcher utama untuk Domain Dokumen Resmi (LoA, Sertifikat, dll).
 * Menangani URL: /document/...
 */

/** @var string $op */
switch ($op) {
    //
    // [BARU] Halaman landing Dokumen Resmi
    //
    case 'index':
        define('HANDLER_CLASS', 'DocumentIndexHandler');
        import('pages.document.DocumentIndexHandler');
        break;

    //
    // Letter of Acceptance
    //
    case 'loa':
    case 'loaIndex': // [BARU] halaman indeks
        define('HANDLER_CLASS', 'LoAHandler');
        import('pages.document.LoAHandler'); 
        break;
        
    // 
    // certificate fo Editor & Reviewer
    //
    case 'certificate':
    case 'certificateIndex': // [BARU] halaman indeks
        define('HANDLER_CLASS', 'CertificateHandler');
        import('pages.document.CertificateHandler');
        break;
}

?>
<?php
declare(strict_types=1);

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

switch ($op) {
    //
    // Letter of Acceptance
    //
    case 'loa':
        define('HANDLER_CLASS', 'LoAHandler');
        import('pages.document.LoAHandler'); 
        break;
        
    // 
    // certificate fo Editor & Reviewer
    //
    case 'certificate':
        define('HANDLER_CLASS', 'CertificateHandler');
        import('pages.document.CertificateHandler');
        break;
}
?>
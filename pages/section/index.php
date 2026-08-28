<?php
declare(strict_types=1);

/**
 * @file pages/section/index.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Wizdam Team
 * Distributed under the GNU GPL v3.
 *
 * [WIZDAM] - Native Route Registry for 'section' pages.
 * Menangani URL: /{context}/section/{section_path}
 */

/** @var string $op */
switch ($op) {
    case 'index':
    case 'about':
    case 'articles':
    case '':
        // Jika hanya mengakses /section
        define('HANDLER_CLASS', 'SectionHandler');
        import('pages.section.SectionHandler');
        break;
        
    default:
        // Menangkap semua string dinamis (nama section) sebagai $op
        define('HANDLER_CLASS', 'SectionHandler');
        import('pages.section.SectionHandler');
        break;
}

?>
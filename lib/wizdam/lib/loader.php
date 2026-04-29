<?php
declare(strict_types=1);

/**
 * Wizdam Library Loader
 * Memuat: TCPDF, FPDI, dan Smalot PdfParser
 * Kompatibel: PHP 7.4 - 8.x
 */

$wizdamLibDir = dirname(__FILE__);

// -------------------------------------------------------------------------
// 1. LOAD TCPDF (Legacy Style)
// -------------------------------------------------------------------------
if (!class_exists('TCPDF')) {
    $tcpdfPath = $wizdamLibDir . '/tcpdf/tcpdf.php';
    if (file_exists($tcpdfPath)) {
        require_once $tcpdfPath;
    }
}

// -------------------------------------------------------------------------
// 2. LOAD FPDI (Namespaced)
// -------------------------------------------------------------------------
if (!class_exists('\setasign\Fpdi\Tcpdf\Fpdi')) {
    $fpdiAutoload = $wizdamLibDir . '/fpdi/src/autoload.php';
    if (file_exists($fpdiAutoload)) {
        require_once $fpdiAutoload;
    }
}

// -------------------------------------------------------------------------
// 3. LOAD SMALOT PDF PARSER (Namespaced - Manual Autoloader)
// -------------------------------------------------------------------------
if (!class_exists('\Smalot\PdfParser\Parser')) {
    // Kita daftarkan fungsi autoload khusus untuk namespace Smalot
    spl_autoload_register(function ($class) use ($wizdamLibDir) {
        $prefix = 'Smalot\\PdfParser\\';
        $base_dir = $wizdamLibDir . '/pdfparser/src/Smalot/PdfParser/';

        // Cek apakah class menggunakan prefix ini
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        // Ambil nama class relatif (setelah prefix)
        $relative_class = substr($class, $len);

        // Ganti namespace separator dengan directory separator
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        // Jika file ada, require
        if (file_exists($file)) {
            require $file;
        }
    });
}
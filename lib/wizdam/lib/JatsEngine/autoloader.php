<?php
declare(strict_types=1);

/**
 * Autoloader untuk Wizdam\JatsEngine
 * Menangani mapping namespace modern ke struktur folder manual
 */

spl_autoload_register(function ($class) {
    // 1. Definisikan Prefix Namespace Proyek Ini
    $prefix = 'Wizdam\\JatsEngine\\';

    // 2. Cek apakah class yang dipanggil menggunakan prefix ini
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // Bukan urusan kita, serahkan ke autoloader lain (jika ada)
    }

    // 3. Ambil nama class relatif (setelah prefix)
    $relativeClass = substr($class, $len);

    // 4. Tentukan Base Directory dari file ini
    $baseDir = dirname(__FILE__);

    // 5. LOGIKA MAPPING KHUSUS
    // Kasus A: Class Utama (JatsEngine) ada di root folder library
    if ($relativeClass === 'JatsEngine') {
        $file = $baseDir . '/JatsEngine.php';
    } 
    // Kasus B: Class lainnya (Builders, Parsers) ada di dalam folder 'src/'
    else {
        // Ganti backslash namespace dengan directory separator
        $path = str_replace('\\', '/', $relativeClass);
        $file = $baseDir . '/src/' . $path . '.php';
    }

    // 6. Jika file ditemukan, require
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Opsional: Error log untuk debugging
        // error_log("JatsEngine Autoloader: File tidak ditemukan untuk class $class di $file");
    }
});
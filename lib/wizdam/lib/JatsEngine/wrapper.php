<?php
declare(strict_types=1);

/**
 * Wizdam JatsEngine Wrapper
 * Entry point untuk aplikasi legacy ScholarWizdam
 */

// 1. Pastikan Autoloader Dimuat
$autoloaderPath = dirname(__FILE__) . '/autoloader.php';

if (file_exists($autoloaderPath)) {
    require_once($autoloaderPath);
} else {
    // Log error pemantau jika komponen vital hilang
    error_log('Wizdam JatsEngine Critical Error: autoloader.php is missing.');
    // Fatal error jika komponen vital hilang
    die('Wizdam JatsEngine Error: Component autoloader.php is missing.');
}

// 2. Class Alias (Opsional - Sesuai gaya SimplePie Anda)
// Ini memudahkan pemanggilan di OJS tanpa harus ketik 'use Wizdam\JatsEngine\...'
// Tapi hati-hati bentrok nama. Saya sarankan tetap pakai namespace penuh di controller OJS
// untuk menghindari konflik dengan class bawaan OJS.
// Namun jika Anda ingin shortcut global:

/* if (class_exists('Wizdam\JatsEngine\JatsEngine') && !class_exists('JatsEngine')) {
    class_alias('Wizdam\JatsEngine\JatsEngine', 'JatsEngine');
}
*/

// Catatan: Saya menonaktifkan alias di atas karena nama 'JatsEngine' cukup umum.
// Lebih aman memanggilnya dengan: $engine = new \Wizdam\JatsEngine\JatsEngine($id);
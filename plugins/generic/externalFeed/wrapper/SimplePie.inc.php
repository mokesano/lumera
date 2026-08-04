<?php
declare(strict_types=1);

/**
 * @file plugins/generic/externalFeed/wrapper/SimplePie.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2027-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 * 
 * @class SimplePieWrapper
 * @ingroup plugins_generic_externalFeed
 * 
 * @brief Wrapper kompatibilitas SimplePie Modern (Namespaced)  
 * SHIM Wrapper SimplePie untuk fork Lumera Editorial Workflow
 * Menjembatani sistem lama dengan SimplePie Modern (Namespaced)
 */

/** 
 * 1. Panggil Autoloader bawaan SimplePie
 * Pastikan file autoloader.php ada di folder yang sama dengan file ini
 */ 
$autoloaderPath = dirname(__FILE__) . '/autoloader.php';

if (file_exists($autoloaderPath)) {
    require_once($autoloaderPath);
} else {
    // Logging error jika autoloader hilang
    error_log('SimplePie Wrapper: autoloader.php tidak ditemukan di ' . $autoloaderPath);
}

/**
 * 2. MAPPING NAMESPACE (Kunci Perbaikan Error Anda)
 * Kita cek apakah class 'SimplePie\SimplePie' tersedia lewat autoloader
 * Jika ya, kita buat alias menjadi 'SimplePie' biasa agar OJS bisa membacanya.
 */ 
if (class_exists('SimplePie\SimplePie')) {
    if (!class_exists('SimplePie')) {
        class_alias('SimplePie\SimplePie', 'SimplePie');
    }
} 

/**
 * 3. FALLBACK MANUAL (Jaga-jaga jika autoloader gagal)
 * SimplePie Master biasanya menaruh file utama di folder /src/SimplePie.php
 */ 
else {
    $manualSource = dirname(__FILE__) . '/src/SimplePie.php';
    if (file_exists($manualSource)) {
        require_once($manualSource);
        // Cek lagi dan buat alias
        if (class_exists('SimplePie\SimplePie') && !class_exists('SimplePie')) {
            class_alias('SimplePie\SimplePie', 'SimplePie');
        }
    }

}
?>
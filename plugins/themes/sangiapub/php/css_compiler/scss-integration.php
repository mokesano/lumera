<?php
// File: php/scss-integration.php

// Lokasi folder CSS dan SCSS
$cssDir = __DIR__ . '/../css/';
$scssOutputDir = __DIR__ . '/../css/sass/';

// Aktifkan caching untuk file SCSS
header("Cache-Control: public, max-age=86400"); // Cache selama 1 hari

// Jalankan proses konversi CSS ke SCSS
include_once __DIR__ . '/scss-compiler.php';

// Fungsi untuk menemukan file SCSS terbaru
function findLatestScssFiles($scssDir) {
    $scssFiles = glob("$scssDir/*.scss");
    if (empty($scssFiles)) {
        echo 'Tidak ada file SCSS ditemukan.';
        exit;
    }

    // Urutkan file berdasarkan waktu modifikasi (terbaru)
    usort($scssFiles, function ($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    return $scssFiles;
}

// Dapatkan file SCSS terbaru
$scssFiles = findLatestScssFiles($scssOutputDir);

// Tampilkan file SCSS terbaru di halaman web
$scssUrls = [];
foreach ($scssFiles as $scssFile) {
    $scssFileName = basename($scssFile);
    $scssUrls[] = "/plugins/themes/sangiapub/css/sass/$scssFileName";
}

// Assign variabel ke template Smarty
$templateMgr = TemplateManager::getManager();
$templateMgr->assign('scssUrls', $scssUrls);
?>
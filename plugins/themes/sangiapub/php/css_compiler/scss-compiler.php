<?php
// File: php/scss-compiler.php

// Fungsi untuk membaca semua file CSS dari folder
function processCssFiles($cssDir, $scssOutputDir) {
    try {
        // Baca semua file CSS dari folder
        $cssFiles = glob("$cssDir/*.css");

        if (empty($cssFiles)) {
            echo "Tidak ada file CSS ditemukan di folder: $cssDir\n";
            return;
        }

        echo "File CSS yang ditemukan: " . implode(", ", $cssFiles) . "\n";

        // Proses setiap file CSS
        foreach ($cssFiles as $cssFilePath) {
            $cssFileName = basename($cssFilePath);
            $scssFileName = str_replace('.css', '.scss', $cssFileName);
            $scssOutputPath = "$scssOutputDir/$scssFileName";

            // Konversi CSS ke SCSS
            convertCssToScss($cssFilePath, $scssOutputPath);
        }

        echo "Proses konversi selesai.\n";
    } catch (Exception $e) {
        echo "Terjadi kesalahan: " . $e->getMessage() . "\n";
    }
}

// Fungsi untuk mengonversi CSS ke SCSS
function convertCssToScss($cssFilePath, $scssOutputPath) {
    try {
        echo "Mengonversi CSS ke SCSS: $cssFilePath -> $scssOutputPath\n";

        // Baca konten file CSS
        $cssContent = file_get_contents($cssFilePath);

        // Konversi CSS ke SCSS (sederhana)
        $scssContent = preg_replace_callback(
            '/([^{]+)\{([^}]+)\}/',
            function ($matches) {
                $selector = trim($matches[1]);
                $rules = trim($matches[2]);
                return "$selector {\n" . preg_replace('/;/', ";\n", $rules) . "\n}";
            },
            $cssContent
        );

        // Simpan hasil konversi ke file SCSS
        file_put_contents($scssOutputPath, $scssContent);
        echo "File SCSS berhasil disimpan: $scssOutputPath\n";
    } catch (Exception $e) {
        echo "Gagal mengonversi CSS ke SCSS: " . $e->getMessage() . "\n";
    }
}

// Path folder sumber dan output
$cssDir = __DIR__ . '/../css'; // Folder CSS sumber
$scssOutputDir = __DIR__ . '/../css/sass'; // Folder output SCSS

// Pastikan folder SCSS ada
if (!is_dir($scssOutputDir)) {
    mkdir($scssOutputDir, 0755, true);
    echo "Folder SCSS dibuat: $scssOutputDir\n";
}

// Jalankan proses konversi
processCssFiles($cssDir, $scssOutputDir);
?>
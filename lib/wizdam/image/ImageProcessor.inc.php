<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/image/ImageProcessor.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2.
 *
 * @class ImageProcessor
 * @ingroup image
 *
 * @brief Helper class to handle native PHP GD image resizing, center cropping, and optimization.
 */

class ImageProcessor {

    public function __construct() {
        // Init
    }

    /**
     * Proporsional Resize (Fit to Box)
     * Digunakan saat hanya parameter width atau height yang diberikan.
     */
    public function resizeAndOptimize(string $sourcePath, string $destPath, int $maxWidth, int $quality = 85): bool {
        return $this->_processImage($sourcePath, $destPath, $maxWidth, 0, false, $quality);
    }

    /**
     * Exact Crop & Resize (Center)
     * Digunakan saat width dan height spesifik diberikan (contoh: w735h400).
     */
    public function cropAndResize(string $sourcePath, string $destPath, int $width, int $height, int $quality = 85): bool {
        return $this->_processImage($sourcePath, $destPath, $width, $height, true, $quality);
    }

    /**
     * Core Engine untuk Manipulasi GD
     */
    private function _processImage(string $sourcePath, string $destPath, int $targetWidth, int $targetHeight, bool $isCrop, int $quality): bool {
        if (!extension_loaded('gd')) {
            error_log("ImageProcessor: PHP GD Extension is required.");
            return false;
        }

        $info = @getimagesize($sourcePath);
        if (!$info) return false;

        list($origWidth, $origHeight, $type) = $info;

        $srcX = 0; $srcY = 0;
        $srcW = $origWidth; $srcH = $origHeight;

        if ($isCrop && $targetWidth > 0 && $targetHeight > 0) {
            // Logika Center Cropping
            $originalRatio = $origWidth / $origHeight;
            $targetRatio = $targetWidth / $targetHeight;

            if ($originalRatio > $targetRatio) {
                // Gambar asli terlalu lebar, potong sisi kiri & kanan
                $srcW = (int) round($origHeight * $targetRatio);
                $srcX = (int) round(($origWidth - $srcW) / 2);
            } else {
                // Gambar asli terlalu tinggi, potong sisi atas & bawah
                $srcH = (int) round($origWidth / $targetRatio);
                $srcY = (int) round(($origHeight - $srcH) / 2);
            }
        } else {
            // Logika Proportional Resize (Tidak dipotong)
            if ($targetWidth <= 0) $targetWidth = $origWidth;
            $targetHeight = (int) round(($targetWidth / $origWidth) * $origHeight);
        }

        // 1. Muat Gambar Asli
        $sourceImage = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                $sourceImage = @imagecreatefromjpeg($sourcePath);
                break;
            case IMAGETYPE_PNG:
                $sourceImage = @imagecreatefrompng($sourcePath);
                break;
            case IMAGETYPE_GIF:
                $sourceImage = @imagecreatefromgif($sourcePath);
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) {
                    $sourceImage = @imagecreatefromwebp($sourcePath);
                }
                break;
        }

        if (!$sourceImage) return false;

        // 2. Siapkan Kanvas Baru
        $destImage = imagecreatetruecolor($targetWidth, $targetHeight);

        // Pertahankan Transparansi untuk PNG, GIF, dan WebP
        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $targetWidth, $targetHeight, $transparent);
        }

        // 3. Eksekusi Resampling & Cropping
        imagecopyresampled($destImage, $sourceImage, 0, 0, $srcX, $srcY, $targetWidth, $targetHeight, $srcW, $srcH);

        // 4. Deteksi output ekstensi dari destinasi dan Simpan
        $ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
        if (empty($ext)) $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        $success = false;
        switch ($ext) {
            case 'webp':
                if (function_exists('imagewebp')) {
                    $success = imagewebp($destImage, $destPath, $quality);
                } else {
                    $success = imagejpeg($destImage, $destPath, $quality); // Fallback ke JPG
                }
                break;
            case 'png':
                $pngQuality = (int) max(0, min(9, 9 - round(($quality / 100) * 9)));
                $success = imagepng($destImage, $destPath, $pngQuality);
                break;
            case 'gif':
                $success = imagegif($destImage, $destPath);
                break;
            case 'jpg':
            case 'jpeg':
            default:
                $success = imagejpeg($destImage, $destPath, $quality);
                break;
        }

        imagedestroy($sourceImage);
        imagedestroy($destImage);

        return $success;
    }
}
?>
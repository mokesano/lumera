<?php
declare(strict_types=1);

/**
 * @file classes/image/ImageProcessor.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2.
 *
 * @class ImageProcessor
 * @ingroup image
 *
 * @brief Helper class to handle image resizing, cropping, and optimization using GD Library.
 */

class ImageProcessor {

    public function __construct() {
        // Init
    }

    /**
     * Resize and optimize an image using PHP GD Library.
     * Auto-calculates aspect ratio and preserves transparency.
     * 
     * @param string $sourcePath Path of the original image
     * @param string $destPath Path to save the resized image
     * @param int $maxWidth Max width allowed
     * @param int $maxHeight Max height allowed
     * @param int $quality JPEG/WebP quality (0-100)
     * @return bool True if successful, false otherwise
     */
    public function resizeAndOptimize(string $sourcePath, string $destPath, int $maxWidth, int $maxHeight, int $quality = 75): bool {
        if (!extension_loaded('gd')) {
            error_log("ImageProcessor: PHP GD Extension is required.");
            return false;
        }

        $info = @getimagesize($sourcePath);
        if (!$info) return false;

        list($origWidth, $origHeight, $type) = $info;

        // Auto-kalkulasi Aspek Rasio
        if ($maxWidth <= 0 && $maxHeight <= 0) {
            $maxWidth = $origWidth;
            $maxHeight = $origHeight;
        } elseif ($maxWidth <= 0) {
            $maxWidth = (int) round(($maxHeight / $origHeight) * $origWidth);
        } elseif ($maxHeight <= 0) {
            $maxHeight = (int) round(($maxWidth / $origWidth) * $origHeight);
        } else {
            // Menjaga proporsi agar tidak gepeng (Fit into bounding box)
            $ratio = min($maxWidth / $origWidth, $maxHeight / $origHeight);
            $maxWidth = (int) round($origWidth * $ratio);
            $maxHeight = (int) round($origHeight * $ratio);
        }

        // Inisialisasi resource berdasarkan tipe MIME asli
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

        $destImage = imagecreatetruecolor($maxWidth, $maxHeight);

        // Pertahankan Transparansi untuk PNG, GIF, dan WebP
        if (in_array($type, [IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP])) {
            imagealphablending($destImage, false);
            imagesavealpha($destImage, true);
            $transparent = imagecolorallocatealpha($destImage, 255, 255, 255, 127);
            imagefilledrectangle($destImage, 0, 0, $maxWidth, $maxHeight, $transparent);
        }

        // Resampling (Scaling yang lebih halus)
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $maxWidth, $maxHeight, $origWidth, $origHeight);

        // Deteksi output ekstensi dari destinasi
        $ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
        if (empty($ext)) $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        $success = false;
        switch ($ext) {
            case 'png':
                $pngQuality = (int) max(0, min(9, 9 - round(($quality / 100) * 9)));
                $success = imagepng($destImage, $destPath, $pngQuality);
                break;
            case 'gif':
                $success = imagegif($destImage, $destPath);
                break;
            case 'webp':
                if (function_exists('imagewebp')) {
                    $success = imagewebp($destImage, $destPath, $quality);
                } else {
                    $success = imagejpeg($destImage, $destPath, $quality);
                }
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
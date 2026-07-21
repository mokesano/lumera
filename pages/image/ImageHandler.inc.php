<?php
declare(strict_types=1);

/**
 * @file pages/image/ImageHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImageHandler
 * @ingroup pages_image
 *
 * @brief Custom Image Resizing & Caching Handler
 */

import('classes.handler.Handler');
import('lib.pkp.classes.file.FileManager');

class ImageHandler extends Handler {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ImageHandler() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::ImageHandler(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * HANDLER 1: COVER ISSUE
     * @param mixed $args
     */
    public function issue($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_processRequest($args, $request, 'issues', ''); 
    }

    /**
     * HANDLER 2: PAGE HEADER
     * @param mixed $args
     */
    public function header($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_processRequest($args, $request, 'headers', ''); 
    }

    /**
     * HANDLER 3: ARTICLE COVER
     * @param mixed $args
     */
    public function article($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_processRequest($args, $request, 'articles', ''); 
    }

    /**
     * --- FUNGSI INTI (THE CORE LOGIC) ---
     * @param mixed $args
     * @param mixed $request
     * @param mixed $typeFolder
     */
    protected function _processRequest($args, $request, $typeFolder, $sourceSubFolder = '') {
        $journal = $request->getJournal();
        
        if (!$journal || count($args) < 4) {
            header('HTTP/1.0 404 Not Found'); 
            exit;
        }

        // 1. Ambil Parameter
        $objId    = (int) array_shift($args); 
        $width    = (int) array_shift($args);
        $height   = (int) array_shift($args);
        $fileName = (string) array_shift($args);

        // 2. Sanitasi
        $fileName = basename($fileName); 
        if (!ctype_alnum(str_replace(['_', '.', '-'], '', $fileName))) {
            header('HTTP/1.0 403 Forbidden'); 
            exit;
        }

        // 3. Setup Path
        import('classes.file.PublicFileManager');
        $publicFileManager = new PublicFileManager();
        $journalBase = $publicFileManager->getJournalFilesPath($journal->getId());
        
        $originalFilePath = ($sourceSubFolder != '') 
            ? $journalBase . '/' . $sourceSubFolder . '/' . $fileName 
            : $journalBase . '/' . $fileName;

        $cacheBaseDir = $journalBase . '/cache';
        $cacheTypeDir = $cacheBaseDir . '/' . $typeFolder;
        
        if (!file_exists($cacheTypeDir)) {
            if (!mkdir($cacheTypeDir, 0755, true) && !is_dir($cacheTypeDir)) {
                error_log("ImageHandler: Failed to create cache directory: " . $cacheTypeDir);
            }
        }
        
        $cacheFileName = $width . 'x' . $height . '_' . $fileName;
        $cacheFilePath = $cacheTypeDir . '/' . $cacheFileName;

        // 4. Eksekusi (Cek Cache / Resize)
        if (file_exists($cacheFilePath)) {
            $this->_serveImage($cacheFilePath);
        } elseif (file_exists($originalFilePath)) {
            // [ROBUST SOLUTION] Gunakan internal resizer alih-alih memanggil FileManager
            if ($this->_resizeAndOptimizeImage($originalFilePath, $cacheFilePath, $width, $height, 75)) {
                $this->_serveImage($cacheFilePath);
            } else {
                // Fallback: Jika GD Library gagal atau file bukan gambar valid,
                // sajikan saja gambar aslinya tanpa di-resize agar UI tidak broken.
                $this->_serveImage($originalFilePath);
            }
        } else {
            header('HTTP/1.0 404 Not Found'); 
            exit;
        }
    }

    /**
     * [NEW] Image Resizer & Optimizer Engine
     * Memanfaatkan PHP GD Library dengan kalkulasi aspek rasio otomatis.
     * @param mixed $sourcePath
     * @param mixed $destPath
     * @param mixed $maxWidth
     * @param mixed $maxHeight
     */
    protected function _resizeAndOptimizeImage($sourcePath, $destPath, $maxWidth, $maxHeight, $quality = 75) {
        if (!extension_loaded('gd')) {
            error_log("ImageHandler: PHP GD Extension is required for image resizing.");
            return false;
        }

        $info = @getimagesize($sourcePath);
        if (!$info) return false;

        list($origWidth, $origHeight, $type) = $info;

        // Auto-kalkulasi Aspek Rasio jika salah satu sisi diisi 0
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

        // Lakukan Resampling (Scaling yang lebih halus dari sekadar Resize)
        imagecopyresampled($destImage, $sourceImage, 0, 0, 0, 0, $maxWidth, $maxHeight, $origWidth, $origHeight);

        // Deteksi output ekstensi dari destinasi
        $ext = strtolower(pathinfo($destPath, PATHINFO_EXTENSION));
        if (empty($ext)) $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        $success = false;
        switch ($ext) {
            case 'png':
                // Kualitas PNG di GD adalah dari 0 (no compression) ke 9 (max compression)
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
                    $success = imagejpeg($destImage, $destPath, $quality); // Fallback ke JPEG
                }
                break;
            case 'jpg':
            case 'jpeg':
            default:
                $success = imagejpeg($destImage, $destPath, $quality);
                break;
        }

        // Bersihkan memori server
        imagedestroy($sourceImage);
        imagedestroy($destImage);

        return $success;
    }

    /**
     * Helper Serve File
     * @param mixed $filePath
     */
    protected function _serveImage($filePath) {
        $mime = 'image/jpeg';
        
        // Mempertahankan pengecekan MIME adaptif ala FileManager
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($filePath);
        }
        
        // Fallback presisi ekstensi (meniru mapping dari FileManager)
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'png': $mime = 'image/png'; break;
            case 'gif': $mime = 'image/gif'; break;
            case 'webp': $mime = 'image/webp'; break;
        }

        // Header caching agresif untuk optimalisasi load speed
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=31536000, public');
        header('Pragma: public');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        readfile($filePath);
        exit;
    }

}
?>
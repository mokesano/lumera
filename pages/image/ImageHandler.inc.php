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
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 * Custom Image Resizing & Caching Handler
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
     * URL: index.php/journal/image/issue/ID/width/height/filename
     * Cache: /public/journals/X/cache/issues/
     * @param array $args
     * @param PKPRequest $request
     */
    public function issue($args, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        // Tipe: 'issues', Source: Root Folder Jurnal
        $this->_processRequest($args, $request, 'issues', ''); 
    }

    /**
     * HANDLER 2: PAGE HEADER
     * URL: index.php/journal/image/header/ID/width/height/filename
     * Cache: /public/journals/X/cache/headers/
     * @param array $args
     * @param PKPRequest $request
     */
    public function header($args, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Tipe: 'headers', Source: Root Folder Jurnal
        $this->_processRequest($args, $request, 'headers', ''); 
    }

    /**
     * HANDLER 3: ARTICLE COVER
     * URL: index.php/journal/image/article/ID/width/height/filename
     * Cache: /public/journals/X/cache/articles/
     * @param array $args
     * @param PKPRequest $request
     */
    public function article($args, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        // Tipe: 'articles', Source: Root Folder Jurnal
        $this->_processRequest($args, $request, 'articles', ''); 
    }

    /**
     * --- FUNGSI INTI (THE CORE LOGIC) ---
     * @param array $args
     * @param PKPRequest $request
     * @param string $typeFolder Nama folder cache (issues/headers/articles)
     * @param string $sourceSubFolder Subfolder sumber (kosong jika di root jurnal)
     */
    protected function _processRequest($args, $request, $typeFolder, $sourceSubFolder = '') {
        $journal = $request->getJournal();
        
        if (!$journal || count($args) < 4) {
            header('HTTP/1.0 404 Not Found'); 
            exit;
        }

        // 1. Ambil Parameter
        $objId    = (int) array_shift($args); // IssueID atau ArticleID (Unused variable in logic but consumes arg)
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
        
        // Tentukan File Asli (Sumber)
        if ($sourceSubFolder != '') {
            $originalFilePath = $journalBase . '/' . $sourceSubFolder . '/' . $fileName;
        } else {
            $originalFilePath = $journalBase . '/' . $fileName;
        }

        // Tentukan File Cache (Tujuan) -> DISINI STRUKTUR RAPI TERBENTUK
        // Contoh: /public/journals/2/cache/issues/
        $cacheBaseDir = $journalBase . '/cache';
        $cacheTypeDir = $cacheBaseDir . '/' . $typeFolder;
        
        // Buat folder secara rekursif jika belum ada
        if (!file_exists($cacheTypeDir)) {
            // [WIZDAM] Improved mkdir with permission check
            if (!mkdir($cacheTypeDir, 0755, true) && !is_dir($cacheTypeDir)) {
                error_log("ImageHandler: Failed to create cache directory: " . $cacheTypeDir);
            }
        }
        
        // Nama file: 200x0_filename.jpg
        $cacheFileName = $width . 'x' . $height . '_' . $fileName;
        $cacheFilePath = $cacheTypeDir . '/' . $cacheFileName;

        // 4. Eksekusi (Cek Cache / Resize)
        if (file_exists($cacheFilePath)) {
            $this->_serveImage($cacheFilePath);
        } elseif (file_exists($originalFilePath)) {
            $fileManager = new FileManager();
            // Panggil Otak di FileManager
            // Parameter 75 adalah kualitas JPEG
            if ($fileManager->resizeAndOptimizeImage($originalFilePath, $cacheFilePath, $width, $height, 75)) {
                $this->_serveImage($cacheFilePath);
            } else {
                header('HTTP/1.0 404 Not Found'); 
                exit;
            }
        } else {
            header('HTTP/1.0 404 Not Found'); 
            exit;
        }
    }

    /**
     * Helper Serve File
     * @param string $filePath
     */
    protected function _serveImage($filePath) {
        $mime = 'image/jpeg';
        
        // [WIZDAM] Modern MIME detection
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($filePath);
        }
        
        // Fallback or explicit check for PNG extension if detection fails or returns generic
        if (strtolower(substr($filePath, -3)) == 'png') {
            $mime = 'image/png';
        }

        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($filePath));
        header('Cache-Control: max-age=31536000, public');
        readfile($filePath);
        exit;
    }
}
?>
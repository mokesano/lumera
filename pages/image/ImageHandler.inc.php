<?php
declare(strict_types=1);

/**
 * @file pages/image/ImageHandler.inc.php
 *
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady
 * Distributed under the GNU GPL v3.
 *
 * @class ImageHandler
 * @ingroup pages_image
 *
 * @brief Custom Image Request & Caching Handler
 */

import('classes.handler.Handler');
// Hapus import FileManager jika tidak ada fungsi file_manager lain yang dipakai
// import('lib.pkp.classes.file.FileManager'); 

// Import kelas otak pemrosesan kita yang baru
import('lib.wizdam.image.ImageProcessor');

class ImageHandler extends Handler {

    /**
     * Construct
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * @param mixed $args
     */
    public function issue($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_processRequest($args, $request, 'issues', ''); 
    }

    /**
     * @param mixed $args
     */
    public function header($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_processRequest($args, $request, 'headers', ''); 
    }

    /**
     * @param mixed $args
     */
    public function article($args, $request = null) {
        if (!$request) $request = Application::get()->getRequest();
        $this->_processRequest($args, $request, 'articles', ''); 
    }

    /**
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

        $objId    = (int) array_shift($args); 
        $width    = (int) array_shift($args);
        $height   = (int) array_shift($args);
        $fileName = (string) array_shift($args);

        $fileName = basename($fileName); 
        if (!ctype_alnum(str_replace(['_', '.', '-'], '', $fileName))) {
            header('HTTP/1.0 403 Forbidden'); 
            exit;
        }

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

        // EKSEKUSI UTAMA DENGAN DELEGASI
        if (file_exists($cacheFilePath)) {
            $this->_serveImage($cacheFilePath);
        } elseif (file_exists($originalFilePath)) {
            // Panggil delegasi pemroses gambar (The Brain)
            $processor = new ImageProcessor();
            
            if ($processor->resizeAndOptimize($originalFilePath, $cacheFilePath, $width, $height, 75)) { // Too many arguments. Expected 4. Found 5.
                $this->_serveImage($cacheFilePath);
            } else {
                // Fallback aman: jika gagal diproses, tampilkan yang asli
                $this->_serveImage($originalFilePath);
            }
        } else {
            header('HTTP/1.0 404 Not Found'); 
            exit;
        }
    }

    /**
     * @param mixed $filePath
     */
    protected function _serveImage($filePath) {
        $mime = 'image/jpeg';
        
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $filePath);
            finfo_close($finfo);
        } elseif (function_exists('mime_content_type')) {
            $mime = mime_content_type($filePath);
        }
        
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        switch ($ext) {
            case 'png': $mime = 'image/png'; break;
            case 'gif': $mime = 'image/gif'; break;
            case 'webp': $mime = 'image/webp'; break;
        }

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
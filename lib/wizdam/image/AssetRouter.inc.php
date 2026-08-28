<?php
declare(strict_types=1);

/**
 * @class lib/wizdam/image/AssetRouter.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class ImageRouter
 * @brief Menangani Semantic URL: /assets/images/[MODIFIER]/[TYPE]/[ID]?as=[FORMAT]
 *        Contoh: /assets/images/w735h400/issue/59?as=webp
 * 
 * @brief Menangani URL: /assets/images/[TYPE]/[ID]/[DIMENSION]?as=[FORMAT]
 */

import('lib.wizdam.image.ImageProcessor');
import('classes.file.PublicFileManager');

class AssetRouter {
    
    /**
     * @param mixed $requestUri
     */
    function route($requestUri) {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if (strpos($path, '/assets/images/') === false) return false;

        // STRUKTUR BARU (SPRINGER STYLE):
        // /assets/images/[MODIFIER]/[TYPE]/[ID]
        // Contoh: /assets/images/w735/issue/59
        
        $parts = explode('/', substr($path, strpos($path, '/assets/images/') + 15));
        
        $modifier = isset($parts[0]) ? $parts[0] : 'original'; // w735, w200, original
        $type     = isset($parts[1]) ? $parts[1] : null;       // issue
        $id       = isset($parts[2]) ? (int)$parts[2] : 0;     // 59

        if (!$type || !$id) return false;

        // Parse Modifier (w735 atau w735h400)
        $width = 0; $height = 0;
        
        if ($modifier != 'original') {
            // Hapus karakter 'w' dan 'h' agar sisa angka
            // Contoh: w735 -> 735. w735h400 -> 735, 400
            if (preg_match('/w(\d+)(h(\d+))?/', $modifier, $matches)) {
                $width = (int)$matches[1];
                if (isset($matches[3])) $height = (int)$matches[3];
            }
        }

        // Format WebP via Query String (Standard)
        $format = isset($_GET['as']) ? $_GET['as'] : 'original';

        $this->serve($type, $id, $width, $height, $format);
        return true;
    }

    /**
     * @param mixed $type
     * @param mixed $id
     * @param mixed $width
     * @param mixed $height
     * @param mixed $format
     */
    function serve($type, $id, $width, $height, $format) {
        // (LOGIKA DATABASE SAMA SEPERTI SEBELUMNYA) ...
        $fileName = null; $journalId = 0; $subFolder = '';
        switch ($type) {
            case 'issue':
                /** @var IssueDAO $dao */
                $dao = DAORegistry::getDAO('IssueDAO');
                $obj = $dao->getIssueById($id);
                if ($obj) {
                    $journalId = $obj->getJournalId();
                    $fileName = $obj->getFileName(AppLocale::getLocale());
                    $subFolder = 'cover_issue';
                }
                break;
            case 'article':
                /** @var PublishedArticleDAO $dao */
                $dao = DAORegistry::getDAO('PublishedArticleDAO');
                $obj = $dao->getPublishedArticleById($id);
                if ($obj) {
                    $journalId = $obj->getJournalId();
                    $fileName = $obj->getLocalizedHideCoverPageAbstract();
                    $subFolder = 'cover_article';
                }
                break;
             case 'header':
                /** @var JournalDAO $dao */
                $dao = DAORegistry::getDAO('JournalDAO');
                $obj = $dao->getJournal($id);
                if ($obj) {
                    $journalId = $obj->getId();
                    $s = $obj->getSettings();
                    $img = isset($s['pageHeaderTitleImage']) ? $s['pageHeaderTitleImage'] : (isset($s['pageHeaderLogoImage']) ? $s['pageHeaderLogoImage'] : null);
                    if (isset($img['uploadName'])) $fileName = $img['uploadName'];
                    elseif (isset($img[AppLocale::getLocale()]['uploadName'])) $fileName = $img[AppLocale::getLocale()]['uploadName'];
                    $subFolder = 'header';
                }
                break;
        }

        if (!$fileName) { header('HTTP/1.0 404 Not Found'); exit; }

        // ... (LOGIKA PATH SAMA SEPERTI SEBELUMNYA) ...
        import('classes.file.PublicFileManager');
        $pubMgr = new PublicFileManager();
        $sourcePath = $pubMgr->getJournalFilesPath($journalId) . '/' . $fileName;

        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($format == 'webp') $ext = 'webp';

        // Nama File Cache Unik (Kode Unik internal, tidak perlu di URL)
        $dimSuffix = ($width > 0) ? "_w{$width}" : "";
        if ($height > 0) $dimSuffix .= "_h{$height}";
        
        $targetName = "J{$journalId}_{$type}{$id}{$dimSuffix}.{$ext}";
        $baseDir = Core::getBaseDir();
        $targetDir = $baseDir . '/assets/images/' . $subFolder;
        $targetPath = $targetDir . '/' . $targetName;

        // EKSEKUSI
        if (file_exists($targetPath)) {
            $this->outputFile($targetPath);
        } elseif (file_exists($sourcePath)) {
            if (!file_exists($targetDir)) @mkdir($targetDir, 0777, true);

            $processor = new ImageProcessor();
            $processSuccess = false;

            if ($width === 0) {
                // Modifikasi 'original': Salin langsung (atau konversi jika target format diubah ke webp)
                if ($format === 'webp' || $format !== 'original') {
                    $processSuccess = $processor->resizeAndOptimize($sourcePath, $targetPath, 0, 85);
                } else {
                    $processSuccess = copy($sourcePath, $targetPath);
                }
            } else {
                // Mendelegasikan ke Crop atau Resize berdasarkan parameter Height
                if ($height > 0) {
                    $processSuccess = $processor->cropAndResize($sourcePath, $targetPath, $width, $height, 85);
                } else {
                    $processSuccess = $processor->resizeAndOptimize($sourcePath, $targetPath, $width, 85);
                }
            }
            
            if ($processSuccess && file_exists($targetPath)) {
                $this->outputFile($targetPath);
            } else {
                // Fallback aman: jika proses GD gagal (memori habis/korup), sajikan file asli
                $this->outputFile($sourcePath);
            }
        } else {
            header('HTTP/1.0 404 Not Found'); exit;
        }
    }

    /**
     * @param mixed $path
     */
    function outputFile($path) {
        $mime = 'image/jpeg';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext == 'png') $mime = 'image/png';
        if ($ext == 'webp') $mime = 'image/webp';
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: max-age=31536000, public');
        readfile($path);
        exit;
    }

}
?>
<?php
declare(strict_types=1);

/**
 * @class AssetRouter
 * @brief Menangani URL: /assets/images/[TYPE]/[ID]/[DIMENSION]?as=[FORMAT]
 */
class AssetRouter {
    
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

    function serve($type, $id, $width, $height, $format) {
        // ... (LOGIKA DATABASE SAMA SEPERTI SEBELUMNYA) ...
        $fileName = null; $journalId = 0; $subFolder = '';
        switch ($type) {
            case 'issue':
                $dao = DAORegistry::getDAO('IssueDAO');
                $obj = $dao->getIssueById($id);
                if ($obj) {
                    $journalId = $obj->getJournalId();
                    $fileName = $obj->getFileName(AppLocale::getLocale()) ?: $obj->getFileName($obj->getLocale());
                    $subFolder = 'cover_issue';
                }
                break;
            case 'article':
                $dao = DAORegistry::getDAO('PublishedArticleDAO');
                $obj = $dao->getPublishedArticleById($id);
                if ($obj) {
                    $journalId = $obj->getJournalId();
                    $fileName = $obj->getCoverPageFileName(AppLocale::getLocale()) ?: $obj->getCoverPageFileName($obj->getLocale());
                    $subFolder = 'cover_article';
                }
                break;
             case 'header':
                // ... (Logika Header sama) ...
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
            import('lib.pkp.classes.file.FileManager');
            $fm = new FileManager();
            
            // Jika width 0 (original), lakukan copy saja
            if ($width == 0) {
                copy($sourcePath, $targetPath);
                $this->outputFile($targetPath);
            } else {
                // Crop atau Resize
                $ok = ($height > 0) 
                    ? $fm->cropAndResizeImage($sourcePath, $targetPath, $width, $height, 85) 
                    : $fm->resizeAndOptimizeImage($sourcePath, $targetPath, $width, 85);
                
                if ($ok) $this->outputFile($targetPath);
                else { header('HTTP/1.0 500 Error'); exit; }
            }
        } else {
            header('HTTP/1.0 404 Not Found'); exit;
        }
    }

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
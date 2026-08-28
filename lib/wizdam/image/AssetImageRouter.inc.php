<?php
declare(strict_types=1);

/**
 * @file lib/wizdam/image/AssetImageRouter.inc.php
 * 
 * Copyright (c) 2017-2026 Sangia Publishing House
 * Copyright (c) 2017-2026 Rochmady and Lumera Team
 * Distributed under the GNU GPL v3.
 *
 * @class AssetImageRouter
 * @brief Menangani Semantic URL: /assets/images/[MODIFIER]/[TYPE]/[ID]?as=[FORMAT]
 *        Contoh: /assets/images/w735h400/issue/59?as=webp
 */

import('lib.wizdam.image.ImageProcessor');
import('classes.file.PublicFileManager');

class AssetImageRouter {
    
    /**
     * Membedah URI dan mengarahkan rute
     */
    public function route(string $requestUri): bool {
        $path = parse_url($requestUri, PHP_URL_PATH);
        if ($path === null || strpos($path, '/assets/images/') === false) {
            return false;
        }

        // Parse path menjadi array
        $basePos = strpos($path, '/assets/images/') + 15;
        $parts = explode('/', substr($path, $basePos));
        
        $modifier = $parts[0] ?? 'original'; 
        $type     = $parts[1] ?? null;       
        $id       = isset($parts[2]) ? (int)$parts[2] : 0; 

        if (!$type || !$id) return false;

        $width = 0; 
        $height = 0;
        
        // Parse w735 atau w735h400
        if ($modifier !== 'original') {
            if (preg_match('/w(\d+)(h(\d+))?/', $modifier, $matches)) {
                $width = (int)$matches[1];
                if (isset($matches[3])) $height = (int)$matches[3];
            }
        }

        // Sanitasi query string format
        $format = isset($_GET['as']) ? strtolower(trim((string)$_GET['as'])) : 'original';
        if (!in_array($format, ['webp', 'png', 'jpg', 'original'])) {
            $format = 'original';
        }

        $this->serve((string)$type, $id, $width, $height, $format);
        return true;
    }

    /**
     * Logika Lookup DAO dan Delegasi Eksekusi
     */
    private function serve(string $type, int $id, int $width, int $height, string $format): void {
        $fileName = null; 
        $journalId = 0; 
        $subFolder = '';

        switch ($type) {
            case 'issue':
                /** @var IssueDAO $issueDao */
                $issueDao = DAORegistry::getDAO('IssueDAO');
                $obj = $issueDao->getIssueById($id);
                if ($obj) {
                    $journalId = (int) $obj->getJournalId();
                    $fileName = $obj->getLocalizedFileName();
                    $subFolder = 'cover_issue';
                }
                break;
                
            case 'article':
                /** @var PublishedArticleDAO $articleDao */
                $articleDao = DAORegistry::getDAO('PublishedArticleDAO');
                $obj = $articleDao->getPublishedArticleById($id);
                if ($obj) {
                    $journalId = (int) $obj->getJournalId();
                    $fileName = $obj->getLocalizedHideCoverPageAbstract(); 
                    $subFolder = 'cover_article';
                }
                break;
                
            case 'header':
                /** @var JournalDAO $journalDao */
                $journalDao = DAORegistry::getDAO('JournalDAO');
                $obj = $journalDao->getJournal($id);
                if ($obj) {
                    $journalId = (int) $obj->getId();
                    $s = $obj->getSettings();
                    $img = $s['pageHeaderTitleImage'] ?? ($s['pageHeaderLogoImage'] ?? null);
                    
                    if (isset($img['uploadName'])) {
                        $fileName = $img['uploadName'];
                    } elseif (isset($img[AppLocale::getLocale()]['uploadName'])) {
                        $fileName = $img[AppLocale::getLocale()]['uploadName'];
                    }
                    $subFolder = 'header';
                }
                break;

            case 'profile':
                // [WIZDAM] Foto profil user/editor TIDAK tersimpan sebagai
                // field DB (beda dari issue/article/header di atas) --
                // namanya konvensi tetap "profileImage-{userId}.{ext}",
                // ekstensi ditentukan lewat pengecekan file mana yang ada
                // (meniru pola yang sudah dipakai UserDAO/EditorialStaff).
                // Berlaku SITE-LEVEL, bukan per-jurnal -- $journalId tetap 0.
                foreach (['jpg', 'jpeg', 'png', 'gif'] as $ext) {
                    $candidate = 'profileImage-' . $id . '.' . $ext;
                    if (file_exists((new PublicFileManager())->getSiteFilesPath() . '/' . $candidate)) {
                        $fileName = $candidate;
                        break;
                    }
                }
                $subFolder = 'profile';
                break;
        }

        if (!$fileName) {
            header('HTTP/1.1 404 Not Found'); 
            exit;
        }

        // Set path sumber asli
        // [WIZDAM] 'profile' bersumber dari getSiteFilesPath() (site-level),
        // tipe lain (issue/article/header) tetap getJournalFilesPath()
        // (per-jurnal, $journalId).
        $pubMgr = new PublicFileManager();
        $sourcePath = ($type === 'profile')
            ? $pubMgr->getSiteFilesPath() . '/' . $fileName
            : $pubMgr->getJournalFilesPath($journalId) . '/' . $fileName;

        // Tentukan ekstensi output
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        if ($format === 'webp') {
            $ext = 'webp';
        }

        // Hasilkan nama file cache yang ter-obfuscate (menghindari path traversal)
        $dimSuffix = ($width > 0) ? "_w{$width}" : "";
        if ($height > 0) {
            $dimSuffix .= "_h{$height}";
        }
        
        $targetName = sprintf("J%d_%s%d%s.%s", $journalId, $type, $id, $dimSuffix, strtolower($ext));
        $targetDir = Core::getBaseDir() . '/assets/images/' . $subFolder;
        $targetPath = $targetDir . '/' . $targetName;

        // Eksekusi Pendelegasian
        if (file_exists($targetPath)) {
            $this->outputFile($targetPath);
        } elseif (file_exists($sourcePath)) {
            
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }
            
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
            header('HTTP/1.1 404 Not Found'); 
            exit;
        }
    }

    /**
     * Memuntahkan file dengan header agresif untuk optimalisasi TTFB
     */
    private function outputFile(string $path): void {
        $mime = 'image/jpeg';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $mime = match ($ext) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            default => 'image/jpeg'
        };
        
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: max-age=31536000, public, immutable');
        header('Pragma: public');
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        
        readfile($path);
        exit;
    }

}
?>
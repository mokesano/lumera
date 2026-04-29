<?php
declare(strict_types=1);

/**
 * @file ImageHandler.php
 * @version 1.4 (Secure Version)
 * @brief Menangani ekstraksi gambar dengan prioritas keamanan.
 * HANYA menggunakan ekstensi PHP Imagick resmi. TIDAK menggunakan exec().
 */

namespace Wizdam\JatsEngine\Parsers\Docx;

use ZipArchive;
use Imagick;
use Exception;

class ImageHandler {
    private string $docxPath;
    private string $assetsDir; 
    private string $publicDir; 
    private array $relationships = [];

    public function __construct(string $docxPath, string $assetsDir) {
        $this->docxPath = $docxPath;
        $this->assetsDir = rtrim($assetsDir, '/');
        $this->publicDir = 'media/'; 
        $this->loadRelationships();
    }

    private function loadRelationships(): void {
        $zip = new ZipArchive();
        if ($zip->open($this->docxPath) === true) {
            $xmlContent = $zip->getFromName('word/_rels/document.xml.rels');
            if ($xmlContent) {
                $xml = simplexml_load_string($xmlContent, 'SimpleXMLElement', LIBXML_NOERROR | LIBXML_NOWARNING);
                if ($xml) {
                    $xml->registerXPathNamespace('rel', 'http://schemas.openxmlformats.org/package/2006/relationships');
                    foreach ($xml->xpath('//rel:Relationship') as $rel) {
                        $this->relationships[(string)$rel['Id']] = (string)$rel['Target'];
                    }
                }
            }
            $zip->close();
        }
    }

    public function processImage(string $rId): string {
        if (!isset($this->relationships[$rId])) return ''; 

        $targetFile = str_replace('../', '', $this->relationships[$rId]);
        $zipEntryName = 'word/' . $targetFile;
        
        $zip = new ZipArchive();
        if ($zip->open($this->docxPath) !== true) return '';

        // Resolusi Path Cerdas
        if ($zip->locateName($zipEntryName) === false) {
             if ($zip->locateName($targetFile) !== false) {
                 $zipEntryName = $targetFile;
             } else {
                 $cleanTarget = ltrim($targetFile, '/');
                 if ($zip->locateName($cleanTarget) !== false) $zipEntryName = $cleanTarget;
                 else { $zip->close(); return ''; }
             }
        }

        $filename = basename($targetFile);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mediaDir = $this->assetsDir . '/media';
        if (!is_dir($mediaDir)) mkdir($mediaDir, 0755, true);

        // Deteksi Metafile
        $isMetafile = in_array($ext, ['emf', 'wmf']);
        $finalFilename = $isMetafile ? pathinfo($filename, PATHINFO_FILENAME) . '.png' : $filename;
        $outputPath = $mediaDir . '/' . $finalFilename;

        // Optimasi cache
        if (file_exists($outputPath)) { $zip->close(); return $this->publicDir . $finalFilename; }
        if ($isMetafile && file_exists($mediaDir . '/' . $filename)) { $zip->close(); return $this->publicDir . $filename; }

        // Ekstraksi & Konversi Aman
        $sourceStream = "zip://{$this->docxPath}#{$zipEntryName}";
        
        if ($isMetafile) {
            $tempPath = sys_get_temp_dir() . '/' . uniqid('wizdam_', true) . '.' . $ext;
            if (copy($sourceStream, $tempPath)) {
                // HANYA gunakan konverter PHP native aman
                $converted = $this->convertMetafileToPngSecure($tempPath, $outputPath);
                @unlink($tempPath); 
                $zip->close();
                return $this->publicDir . ($converted ? $finalFilename : $filename);
            }
        } else {
            if (copy($sourceStream, $outputPath)) { $zip->close(); return $this->publicDir . $finalFilename; }
        }

        $zip->close();
        return '';
    }

    /**
     * Konversi EMF/WMF ke PNG yang AMAN (Tanpa exec).
     * Hanya mengandalkan ekstensi PHP Imagick. Jika gagal, return false.
     */
    private function convertMetafileToPngSecure(string $sourcePath, string $destPath): bool {
        // Cek ketersediaan ekstensi PHP Imagick
        if (!extension_loaded('imagick')) {
            $this->logConversionFailure($sourcePath, $destPath, "Ekstensi PHP Imagick tidak aktif.");
            return false;
        }

        try {
            $im = new Imagick();
            // Cek apakah format didukung oleh Imagick di server ini
            if (!in_array(strtoupper(pathinfo($sourcePath, PATHINFO_EXTENSION)), $im->queryFormats())) {
               throw new Exception("Format file tidak didukung oleh delegasi Imagick di server ini.");
            }
            
            $im->setResolution(300, 300); 
            $im->readImage($sourcePath);
            $im->setImageBackgroundColor('white');
            $im->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $im->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
            $im->setImageFormat('png');
            $im->writeImage($destPath);
            $im->clear(); $im->destroy();
            return true;
        } catch (Exception $e) {
            $this->logConversionFailure($sourcePath, $destPath, $e->getMessage());
            return false;
        }
    }

    private function logConversionFailure(string $source, string $dest, string $reason): void {
        // Simpan file asli sebagai fallback
        $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
        $fallbackPath = str_replace('.png', '.' . $ext, $dest);
        copy($source, $fallbackPath);
        // Catat error di log server
        error_log("Wizdam JatsEngine [SECURE]: Gagal konversi $ext ke PNG. File asli disimpan. Alasan: $reason. User disarankan konversi manual di Word.");
    }
}
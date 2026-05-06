<?php
declare(strict_types=1);

/**
 * @defgroup file
 */

/**
 * @file classes/file/FileManager.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileManager
 * @ingroup file
 *
 * @brief Class defining basic operations for file management.
 */

define('FILE_MODE_MASK', 0666);
define('DIRECTORY_MODE_MASK', 0777);

define('DOCUMENT_TYPE_DEFAULT', 'default');
define('DOCUMENT_TYPE_EXCEL', 'excel');
define('DOCUMENT_TYPE_HTML', 'html');
define('DOCUMENT_TYPE_IMAGE', 'image');
define('DOCUMENT_TYPE_PDF', 'pdf');
define('DOCUMENT_TYPE_WORD', 'word');
define('DOCUMENT_TYPE_ZIP', 'zip');

class FileManager {
    
    /**
     * Constructor
     */
    public function __construct() {
        // No construct
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FileManager() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::FileManager(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct();
    }

    /**
     * Return true if an uploaded file exists.
     * @param $fileName string the name of the file used in the POST form
     * @return boolean
     */
    public function uploadedFileExists($fileName) {
        if (isset($_FILES[$fileName]) && isset($_FILES[$fileName]['tmp_name'])
                && is_uploaded_file($_FILES[$fileName]['tmp_name'])) {
            return true;
        }
        return false;
    }

    /**
     * Return true iff an error occurred when trying to upload a file.
     * @param $fileName string the name of the file used in the POST form
     * @return boolean
     */
    public function uploadError($fileName) {
        return (isset($_FILES[$fileName]) && $_FILES[$fileName]['error'] != 0);
    }

    /**
     * Return the (temporary) path to an uploaded file.
     * @param $fileName string the name of the file used in the POST form
     * @return string|false (boolean false if no such file)
     */
    public function getUploadedFilePath($fileName) {
        if (isset($_FILES[$fileName]['tmp_name']) && is_uploaded_file($_FILES[$fileName]['tmp_name'])) {
            return $_FILES[$fileName]['tmp_name'];
        }
        return false;
    }

    /**
     * Return the user-specific (not temporary) filename of an uploaded file.
     * @param $fileName string the name of the file used in the POST form
     * @return string|false (boolean false if no such file)
     */
    public function getUploadedFileName($fileName) {
        if (isset($_FILES[$fileName]['name'])) {
            return $_FILES[$fileName]['name'];
        }
        return false;
    }

    /**
     * Get the file type of an uploaded file.
     * Modernized: Uses fileinfo extension if available, falls back to secure detection.
     * @param $fileName string
     * @return string|false
     */
    public function getUploadedFileType($fileName) {
        if (isset($_FILES[$fileName])) {
            $tmpName = $_FILES[$fileName]['tmp_name'];
            $name = $_FILES[$fileName]['name'];
            $type = null;

            // [WIZDAM FIX] Validasi eksistensi file fisik untuk menghindari PHP 8 Warning
            // Pastikan file temporary benar-benar ada sebelum dibaca oleh server
            $isFileValid = !empty($tmpName) && file_exists($tmpName);

            // 1. Try PHP's fileinfo extension (Most reliable)
            if ($isFileValid && function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $type = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);
                }
            }

            // 2. Try external 'file' command (Linux/Unix fallback)
            if (empty($type) && $isFileValid) {
                $fileCommand = Config::getVar('files', 'file_command');
                if (!empty($fileCommand) && is_executable(preg_replace('/ .*$/', '', $fileCommand))) {
                    $command = str_replace('%f', escapeshellarg($tmpName), $fileCommand);
                    $type = @exec($command);
                }
            }
            
            if (!empty($type)) return $type;

            // 3. Fallback to browser provided type (Less secure but better than nothing)
            if (!empty($_FILES[$fileName]['type'])) {
                return $_FILES[$fileName]['type'];
            }
    
            // 4. Last resort: Extension mapping
            switch (strtolower_codesafe(pathinfo($name, PATHINFO_EXTENSION))) {
                case 'pdf': return 'application/pdf';
                case 'doc': return 'application/msword';
                case 'docx': return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                case 'rtf': return 'application/rtf';
                case 'jpg': case 'jpeg': return 'image/jpeg';
                case 'png': return 'image/png';
                case 'gif': return 'image/gif';
            }
        }
        return false;
    }

    /**
     * Upload a file.
     * Enhanced security: Strict MIME type and extension checking.
     * @param $fileName string name of the file used in the POST form
     * @param $dest string path where the file is to be saved
     * @param $errorMsg string|null error message (passed by reference removed, handled via return or exception if needed, but keeping signature compatible)
     * @return boolean returns true if successful
     */
    public function uploadFile($fileName, $dest, &$errorMsg = null) {
        if (!$this->uploadedFileExists($fileName)) {
            $errorMsg = __('common.uploadFailed');
            return false;
        }

        // --- (A) LOGIKA ASLI (Membuat Direktori) ---
        $destDir = dirname($dest);
        if (!$this->mkdirtree($destDir)) {
             $errorMsg = __('common.uploadFailed');
             return false;
        }

        // --- (B) PERBAIKAN KEAMANAN (WHITELIST) ---
        // Peta keamanan yang diperluas
        $securityMap = array(
            // Dokumen
            'pdf' => array('application/pdf', 'application/x-pdf', 'text/pdf'),
            'epub' => array('application/epub+zip'), 
            'epdf' => array('application/pdf'),
            'doc' => array('application/msword'),
            'docx'=> array('application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'rtf' => array('application/rtf', 'text/rtf'),
            'odt' => array('application/vnd.oasis.opendocument.text'),
            
            // Gambar
            'webp'=> array('image/webp'),
            'jpg' => array('image/jpeg', 'image/pjpeg'),
            'jpeg'=> array('image/jpeg', 'image/pjpeg'),
            'png' => array('image/png', 'image/x-png'),
            'gif' => array('image/gif'),
            'ico' => array('image/vnd.microsoft.icon', 'image/x-icon', 'image/x-ico', 'image/ico'),

            // Data
            'csv' => array('text/csv', 'text/plain'), 
            'xml' => array('text/xml', 'application/xml'),
            
            // Arsip
            'zip' => array('application/zip', 'application/x-zip-compressed'),
            'rar' => array('application/rar', 'application/x-rar-compressed'),
            'gz'  => array('application/gzip', 'application/x-gzip'),
            'tar' => array('application/x-tar'),

            // Multimedia
            'mp3' => array('audio/mpeg'),
            'mp4' => array('video/mp4'),
            'mov' => array('video/quicktime'),
            'mpg' => array('video/mpeg'),
            'wav' => array('audio/x-wav')
        );

        // Ambil Nama File Asli
        $originalFileName = $_FILES[$fileName]['name'];

        // [WIZDAM FIX] 1. Ekstrak Semua Ekstensi untuk mencegah Double Extension Bypass (misal: file.php.jpg)
        $parts = explode('.', $originalFileName);
        $fileExtension = strtolower(end($parts)); 

        // [WIZDAM FIX] 2. STRICT WHITELISTING
        // Jika tidak ada di peta, LANGSUNG TOLAK. Jangan gunakan blacklist.
        if (empty($fileExtension) || !array_key_exists($fileExtension, $securityMap)) {
            $errorMsg = __('manager.setup.config.mimeTypeNotAllowed') . ' (Invalid Extension: ' . htmlentities((string)$fileExtension) . ')';
            return false; 
        }

        // 3. Periksa Tipe MIME
        $detectedMimeType = $this->getUploadedFileType($fileName);
        $allowedMimes = $securityMap[$fileExtension];

        // [WIZDAM FIX] 3. Validasi MIME yang Diperketat
        if ($detectedMimeType) {
            if (!in_array($detectedMimeType, $allowedMimes)) {
                $errorMsg = __('manager.setup.config.mimeTypeNotAllowed') . ' (MIME Mismatch)';
                return false; 
            }
        } else {
            // Jika server gagal mendeteksi MIME (fallback gagal semua), kita blokir demi keamanan.
            // Jangan biarkan file lolos tanpa identitas yang jelas.
            $errorMsg = __('manager.setup.config.mimeTypeNotAllowed') . ' (Cannot detect MIME type)';
            return false;
        }

        // --- (C) FINAL Upload ---
        if (move_uploaded_file($_FILES[$fileName]['tmp_name'], $dest)) {
            $this->setMode($dest, FILE_MODE_MASK);
            return true;
        }
        
        $errorMsg = __('common.uploadFailed');
        return false;
    }

    /**
     * Write a file.
     * @param $dest string the path where the file is to be saved
     * @param $contents string the contents to write to the file
     * @return boolean returns true if successful
     */
    public function writeFile($dest, $contents) {
        $success = true;
        $destDir = dirname($dest);
        if (!$this->fileExists($destDir, 'dir')) {
            $this->mkdirtree($destDir);
        }
        if (($f = fopen($dest, 'wb'))===false) $success = false;
        if ($success && fwrite($f, $contents)===false) $success = false;
        @fclose($f);

        if ($success)
            return $this->setMode($dest, FILE_MODE_MASK);
        return false;
    }

    /**
     * Copy a file.
     * @param $source string the source URL for the file
     * @param $dest string the path where the file is to be saved
     * @return boolean returns true if successful
     */
    public function copyFile($source, $dest) {
        $success = true;
        $destDir = dirname($dest);
        if (!$this->fileExists($destDir, 'dir')) {
            $this->mkdirtree($destDir);
        }
        if (copy($source, $dest))
            return $this->setMode($dest, FILE_MODE_MASK);
        return false;
    }

    /**
     * Copy a directory.
     * @param $source string the path to the source directory
     * @param $dest string the path where the directory is to be saved
     * @return boolean returns true if successful
     */
    public function copyDir($source, $dest) {
        if (is_dir($source)) {
            $this->mkdir($dest);
            $destDir = dir($source);

            while (($entry = $destDir->read()) !== false) {
                if ($entry == '.' || $entry == '..') {
                    continue;
                }

                $Entry = $source . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($Entry) ) {
                    $this->copyDir($Entry, $dest . DIRECTORY_SEPARATOR . $entry );
                    continue;
                }
                $this->copyFile($Entry, $dest . DIRECTORY_SEPARATOR . $entry );
            }

            $destDir->close();
        } else {
            $this->copyFile($source, $dest);
        }

        if ($this->fileExists($dest, 'dir')) {
            return true;
        } else return false;
    }


    /**
     * Read a file's contents.
     * @param $filePath string the location of the file to be read
     * @param $output boolean output the file's contents instead of returning a string
     * @return string|boolean
     */
    public function readFile($filePath, $output = false) {
        if (is_readable($filePath)) {
            $f = fopen($filePath, 'rb');
            $data = '';
            while (!feof($f)) {
                $chunk = fread($f, 4096);
                if ($output) {
                    echo $chunk;
                } else {
                    $data .= $chunk;
                }
            }
            fclose($f);

            if ($output) {
                return true;
            } else {
                return $data;
            }

        } else {
            return false;
        }
    }

    /**
     * Download a file.
     * Outputs HTTP headers and file content for download
     * @param $filePath string the location of the file to be sent
     * @param $mediaType string the MIME type of the file, optional
     * @param $inline print file as inline instead of attachment, optional
     * @param $fileName string optional filename
     * @return boolean
     */
    public function downloadFile($filePath, $mediaType = null, $inline = false, $fileName = null) {
        $result = null;
        // Modernisasi: Hapus & pada array params, HookRegistry menggunakan pass by value untuk simple vars kecuali ditentukan lain
        if (HookRegistry::dispatch('FileManager::downloadFile', array($filePath, $mediaType, $inline, &$result, $fileName))) return $result;
        
        $postDownloadHookList = array('FileManager::downloadFileFinished', 'UsageEventPlugin::getUsageEvent');
        if (is_readable($filePath)) {
            if ($mediaType === null) {
                $mediaType = PKPString::mime_content_type($filePath);
                if (empty($mediaType)) $mediaType = 'application/octet-stream';
            }
            if ($fileName === null) {
                $fileName = basename($filePath);
            }

            $postDownloadHooks = null;
            $hooks = HookRegistry::getHooks();
            foreach ($postDownloadHookList as $hookName) {
                if (isset($hooks[$hookName])) {
                    $postDownloadHooks[$hookName] = $hooks[$hookName];
                }
            }
            unset($hooks);
            Registry::clear();

            header("Content-Type: $mediaType");
            header('Content-Length: ' . filesize($filePath));
            header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename=\"$fileName\"");
            header('Cache-Control: private'); 
            header('Pragma: public');

            // Static method call
            self::readFile($filePath, true);

            if ($postDownloadHooks) {
                foreach ($postDownloadHooks as $hookName => $hooks) {
                    HookRegistry::setHooks($hookName, $hooks);
                }
            }
            $returner = true;
        } else {
            $returner = false;
        }
        
        HookRegistry::dispatch('FileManager::downloadFileFinished', array(&$returner));

        return $returner;
    }

    /**
     * Delete a file.
     * @param $filePath string the location of the file to be deleted
     * @return boolean returns true if successful
     */
    public function deleteFile($filePath) {
        if ($this->fileExists($filePath)) {
            return unlink($filePath);
        } else {
            return false;
        }
    }

    /**
     * Create a new directory.
     * @param $dirPath string the full path of the directory to be created
     * @param $perms string the permissions level of the directory (optional)
     * @return boolean returns true if successful
     */
    public function mkdir($dirPath, $perms = null) {
        if ($perms !== null) {
            return mkdir($dirPath, $perms);
        } else {
            if (mkdir($dirPath))
                return $this->setMode($dirPath, DIRECTORY_MODE_MASK);
            return false;
        }
    }

    /**
     * Remove a directory.
     * @param $dirPath string the full path of the directory to be delete
     * @return boolean returns true if successful
     */
    public function rmdir($dirPath) {
        return rmdir($dirPath);
    }

    /**
     * Delete all contents including directory (equivalent to "rm -r")
     * @param $file string the full path of the directory to be removed
     */
    public function rmtree($file) {
        if (file_exists($file)) {
            if (is_dir($file)) {
                $handle = opendir($file);
                while (($filename = readdir($handle)) !== false) {
                    if ($filename != '.' && $filename != '..') {
                        $this->rmtree($file . '/' . $filename);
                    }
                }
                closedir($handle);
                rmdir($file);

            } else {
                unlink($file);
            }
        }
    }

    /**
     * Create a new directory, including all intermediate directories if required (equivalent to "mkdir -p")
     * @param $dirPath string the full path of the directory to be created
     * @param $perms string the permissions level of the directory (optional)
     * @return boolean returns true if successful
     */
    public function mkdirtree($dirPath, $perms = null) {
        if (!file_exists($dirPath)) {
            //Avoid infinite recursion
            if ($dirPath == dirname($dirPath)) {
                fatalError('There are no readable files in this directory tree. Are safe mode or open_basedir active?');
                return false;
            } else if ($this->mkdirtree(dirname($dirPath), $perms)) {
                return $this->mkdir($dirPath, $perms);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if a file path is valid;
     * @param $filePath string the file/directory to check
     * @param $type string (file|dir) the type of path
     * @return boolean
     */
    public function fileExists($filePath, $type = 'file') {
        switch ($type) {
            case 'file':
                return file_exists($filePath);
            case 'dir':
                return file_exists($filePath) && is_dir($filePath);
            default:
                return false;
        }
    }

    /**
     * Returns a file type, based on generic categories defined above
     * @param $type String
     * @return string (Enuemrated DOCUMENT_TYPEs)
     */
    public function getDocumentType($type) {
        if ($this->getImageExtension($type))
            return DOCUMENT_TYPE_IMAGE;

        switch ($type) {
            case 'application/pdf':
            case 'application/x-pdf':
            case 'text/pdf':
            case 'text/x-pdf':
                return DOCUMENT_TYPE_PDF;
            case 'application/msword':
            case 'application/word':
                return DOCUMENT_TYPE_WORD;
            case 'application/excel':
                return DOCUMENT_TYPE_EXCEL;
            case 'text/html':
                return DOCUMENT_TYPE_HTML;
            case 'application/zip':
            case 'application/x-zip':
            case 'application/x-zip-compressed':
            case 'application/x-compress':
            case 'application/x-compressed':
            case 'multipart/x-zip':
                return DOCUMENT_TYPE_ZIP;
            default:
                return DOCUMENT_TYPE_DEFAULT;
        }
    }

    /**
     * Returns file extension associated with the given document type,
     * or false if the type does not belong to a recognized document type.
     * @param $type string
     * @return string|false
     */
    public function getDocumentExtension($type) {
        switch ($type) {
            case 'application/pdf':
                return '.pdf';
            case 'application/word':
                return '.doc';
            case 'text/html':
                return '.html';
            default:
                return false;
        }
    }

    /**
     * Returns file extension associated with the given image type,
     * or false if the type does not belong to a recognized image type.
     * @param $type string
     * @return string|false
     */
    public function getImageExtension($type) {
        switch ($type) {
            case 'image/gif':
                return '.gif';
            case 'image/jpeg':
            case 'image/pjpeg':
                return '.jpg';
            case 'image/png':
            case 'image/x-png':
                return '.png';
            case 'image/vnd.microsoft.icon':
            case 'image/x-icon':
            case 'image/x-ico':
            case 'image/ico':
                return '.ico';
            case 'application/x-shockwave-flash':
                return '.swf';
            case 'video/x-flv':
            case 'application/x-flash-video':
            case 'flv-application/octet-stream':
                return '.flv';
            case 'audio/mpeg':
                return '.mp3';
            case 'audio/x-aiff':
                return '.aiff';
            case 'audio/x-wav':
                return '.wav';
            case 'video/mpeg':
                return '.mpg';
            case 'video/quicktime':
                return '.mov';
            case 'video/mp4':
                return '.mp4';
            case 'text/javascript':
                return '.js';
            default:
                return false;
        }
    }

    /**
     * Parse file extension from file name.
     * @param $fileName string a valid file name
     * @return string extension
     */
    public function getExtension($fileName) {
        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
        return is_string($ext) ? $ext : '';
    }

    /**
     * Truncate a filename to fit in the specified length.
     * @param $fileName string
     * @param $length int
     * @return string
     */
    public function truncateFileName($fileName, $length = 127) {
        if (PKPString::strlen($fileName) <= $length) return $fileName;
        $ext = $this->getExtension($fileName);
        
        $truncated = PKPString::substr($fileName, 0, $length - 1 - PKPString::strlen($ext)) . '.' . $ext;
        return PKPString::substr($truncated, 0, $length);
    }

    /**
     * Return pretty file size string (in B, KB, MB, or GB units).
     * Enhanced: High precision output (3 decimals for MB and GB).
     * @param $size int|float|string file size in bytes
     * @return string
     */
    public function getNiceFileSize($size) {
        // Cast parameter $size ke float
        $size = (float) $size;

        $kb = 1024;
        $mb = 1024 * $kb;
        $gb = 1024 * $mb;

        // Jika ukuran >= 1 GB (Tampilkan 3 desimal)
        if ($size >= $gb) {
            return number_format($size / $gb, 3, ',', '.') . ' GB';
        } 
        // Jika ukuran >= 1 MB (Tampilkan 3 desimal, misal: 1,263 MB)
        elseif ($size >= $mb) {
            return number_format($size / $mb, 3, ',', '.') . ' MB';
        } 
        // Jika ukuran >= 1 KB (Tampilkan bulat tanpa desimal)
        elseif ($size >= $kb) {
            return number_format($size / $kb, 0, ',', '.') . ' KB';
        } 
        // Jika ukuran di bawah 1 KB
        else {
            return number_format($size, 0, ',', '.') . ' B';
        }
    }

    /**
     * Set file/directory mode based on the 'umask' config setting.
     * @param $path string
     * @param $mask int
     * @return boolean
     */
    public function setMode($path, $mask) {
        $umask = Config::getVar('files', 'umask');
        if (!$umask)
            return true;
        return chmod($path, $mask & ~$umask);
    }

    /**
     * Parse the file extension from a filename/path.
     * @param $fileName string
     * @return string
     */
    public function parseFileExtension($fileName) {
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

        // Security check
        if (empty($fileExtension) || stristr((string)$fileExtension, 'php') || strlen((string)$fileExtension) > 6 || !preg_match('/^\w+$/', (string)$fileExtension)) {
            $fileExtension = 'txt';
        }

        // consider .tar.gz extension
        if (strtolower(substr($fileName, -7)) == '.tar.gz') {
            $fileExtension = substr($fileName, -6);
        }

        return $fileExtension;
    }

    /**
     * Decompress passed gziped file.
     * @param $filePath string
     * @param $errorMsg string
     * @return boolean|string
     */
    public function decompressFile($filePath, &$errorMsg = null) {
        return $this->_executeGzip($filePath, true, $errorMsg);
    }

    /**
     * Compress passed file.
     * @param $filePath string The file to be compressed.
     * @param $errorMsg string
     * @return boolean|string
     */
    public function compressFile($filePath, &$errorMsg = null) {
        return $this->_executeGzip($filePath, false, $errorMsg);
    }


    //
    // Private helper methods.
    //
    /**
     * Execute gzip to compress or extract files.
     * @param $filePath string file to be compressed or uncompressed.
     * @param $decompress boolean optional Set true if the passed file needs to be decompressed.
     * @param $errorMsg string
     * @return false|string The file path that was created with the operation or false in case of fail.
     */
    public function _executeGzip($filePath, $decompress = false, &$errorMsg = null) {
        PKPLocale::requireComponents(LOCALE_COMPONENT_CORE_ADMIN);
        $gzipPath = Config::getVar('cli', 'gzip');
        
        if (empty($gzipPath) || !is_string($gzipPath) || !is_executable($gzipPath)) {
            $errorMsg = __('admin.error.executingUtil', array('utilPath' => (string)$gzipPath, 'utilVar' => 'gzip'));
            return false;
        }
        
        $gzipCmd = escapeshellarg($gzipPath);
        if ($decompress) $gzipCmd .= ' -d';
        // Make sure any output message will mention the file path.
        $output = array($filePath);
        $returnValue = 0;
        $gzipCmd .= ' ' . escapeshellarg($filePath); // Security fix: Escape path
        if (!Core::isWindows()) {
            // Get the output, redirecting stderr to stdout.
            $gzipCmd .= ' 2>&1';
        }
        exec($gzipCmd, $output, $returnValue);
        if ($returnValue > 0) {
            $errorMsg = __('admin.error.utilExecutionProblem', array('utilPath' => $gzipPath, 'output' => implode(PHP_EOL, $output)));
            return false;
        }

        if ($decompress) {
            return substr($filePath, 0, -3);
        } else {
            return $filePath . '.gz';
        }
    }
}

?>
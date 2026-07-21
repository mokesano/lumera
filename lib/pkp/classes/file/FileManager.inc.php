<?php
declare(strict_types=1);

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
        $this->__construct();
    }

    /**
     * Return true if an uploaded file exists.
     * @param string $fileName
     * @return boolean
     */
    public function uploadedFileExists($fileName) {
        if (isset($_FILES[$fileName]) && isset($_FILES[$fileName]['tmp_name']) && is_uploaded_file($_FILES[$fileName]['tmp_name'])) {
            return true;
        }
        return false;
    }

    /**
     * Return true iff an error occurred when trying to upload a file.
     * @param string $fileName
     * @return boolean
     */
    public function uploadError($fileName) {
        return (isset($_FILES[$fileName]) && $_FILES[$fileName]['error'] !== 0);
    }

    /**
     * Return the (temporary) path to an uploaded file.
     * @param string $fileName
     * @return string|false
     */
    public function getUploadedFilePath($fileName) {
        if (isset($_FILES[$fileName]['tmp_name']) && is_uploaded_file($_FILES[$fileName]['tmp_name'])) {
            return (string) $_FILES[$fileName]['tmp_name'];
        }
        return false;
    }

    /**
     * Return the user-specific (not temporary) filename of an uploaded file.
     * @param string $fileName
     * @return string|false
     */
    public function getUploadedFileName($fileName) {
        if (isset($_FILES[$fileName]['name'])) {
            return (string) $_FILES[$fileName]['name'];
        }
        return false;
    }

    /**
     * Get the file type of an uploaded file.
     * @param string $fileName
     * @return string|false
     */
    public function getUploadedFileType($fileName) {
        if (isset($_FILES[$fileName])) {
            $tmpName = (string) $_FILES[$fileName]['tmp_name'];
            $name = (string) $_FILES[$fileName]['name'];
            $type = null;

            $isFileValid = !empty($tmpName) && file_exists($tmpName);

            // 1. Try PHP's fileinfo extension
            if ($isFileValid && function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo) {
                    $type = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);
                }
            }

            // 2. Try external 'file' command
            if (empty($type) && $isFileValid) {
                $fileCommand = Config::getVar('files', 'file_command');
                if (!empty($fileCommand) && is_executable(preg_replace('/ .*$/', '', (string) $fileCommand))) {
                    $command = str_replace('%f', escapeshellarg($tmpName), (string) $fileCommand);
                    $type = @exec($command);
                }
            }
            
            if (!empty($type)) {
                return (string) $type;
            }

            // 3. Fallback to browser provided type
            if (!empty($_FILES[$fileName]['type'])) {
                return (string) $_FILES[$fileName]['type'];
            }
    
            // 4. Last resort: Extension mapping
            $ext = strtolower_codesafe(pathinfo($name, PATHINFO_EXTENSION));
            switch ($ext) {
                case 'pdf': return 'application/pdf';
                case 'doc': return 'application/msword';
                case 'docx': return 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                case 'rtf': return 'application/rtf';
                case 'jpg': 
                case 'jpeg': return 'image/jpeg';
                case 'png': return 'image/png';
                case 'gif': return 'image/gif';
            }
        }
        return false;
    }

    /**
     * Upload a file.
     * @param string $fileName
     * @param string $dest
     * @param string|null $errorMsg
     * @return boolean
     */
    public function uploadFile($fileName, $dest, &$errorMsg = null) {
        if (!$this->uploadedFileExists($fileName)) {
            $errorMsg = __('common.uploadFailed');
            return false;
        }

        $destDir = dirname($dest);
        if (!$this->mkdirtree($destDir)) {
             $errorMsg = __('common.uploadFailed');
             return false;
        }

        $securityMap = [
            'pdf' => ['application/pdf', 'application/x-pdf', 'text/pdf'],
            'epub' => ['application/epub+zip'], 
            'epdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx'=> ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'rtf' => ['application/rtf', 'text/rtf'],
            'odt' => ['application/vnd.oasis.opendocument.text'],
            'webp'=> ['image/webp'],
            'jpg' => ['image/jpeg', 'image/pjpeg'],
            'jpeg'=> ['image/jpeg', 'image/pjpeg'],
            'png' => ['image/png', 'image/x-png'],
            'gif' => ['image/gif'],
            'ico' => ['image/vnd.microsoft.icon', 'image/x-icon', 'image/x-ico', 'image/ico'],
            'csv' => ['text/csv', 'text/plain'], 
            'xml' => ['text/xml', 'application/xml'],
            'zip' => ['application/zip', 'application/x-zip-compressed'],
            'rar' => ['application/rar', 'application/x-rar-compressed'],
            'gz'  => ['application/gzip', 'application/x-gzip'],
            'tar' => ['application/x-tar'],
            'mp3' => ['audio/mpeg'],
            'mp4' => ['video/mp4'],
            'mov' => ['video/quicktime'],
            'mpg' => ['video/mpeg'],
            'wav' => ['audio/x-wav']
        ];

        $originalFileName = (string) $_FILES[$fileName]['name'];
        $parts = explode('.', $originalFileName);
        $fileExtension = strtolower_codesafe(end($parts)); 

        if (empty($fileExtension) || !array_key_exists($fileExtension, $securityMap)) {
            $errorMsg = __('manager.setup.config.mimeTypeNotAllowed') . ' (Invalid Extension: ' . htmlentities($fileExtension) . ')';
            return false; 
        }

        $detectedMimeType = $this->getUploadedFileType($fileName);
        $allowedMimes = $securityMap[$fileExtension];

        if ($detectedMimeType) {
            if (!in_array($detectedMimeType, $allowedMimes, true)) { // [LUMERA] Strict in_array
                $errorMsg = __('manager.setup.config.mimeTypeNotAllowed') . ' (MIME Mismatch)';
                return false; 
            }
        } else {
            $errorMsg = __('manager.setup.config.mimeTypeNotAllowed') . ' (Cannot detect MIME type)';
            return false;
        }

        if (move_uploaded_file($_FILES[$fileName]['tmp_name'], $dest)) {
            $this->setMode($dest, FILE_MODE_MASK);
            return true;
        }
        
        $errorMsg = __('common.uploadFailed');
        return false;
    }

    /**
     * Write a file.
     * @param string $dest
     * @param string $contents
     * @return boolean
     */
    public function writeFile($dest, $contents) {
        $destDir = dirname($dest);
        if (!$this->fileExists($destDir, 'dir')) {
            $this->mkdirtree($destDir);
        }

        $f = fopen($dest, 'wb');
        if ($f === false) {
            return false;
        }
        
        $success = (fwrite($f, (string) $contents) !== false);
        fclose($f);

        if ($success) {
            return $this->setMode($dest, FILE_MODE_MASK);
        }
        return false;
    }

    /**
     * Copy a file.
     * @param string $source
     * @param string $dest
     * @return boolean
     */
    public function copyFile($source, $dest) {
        $destDir = dirname($dest);
        if (!$this->fileExists($destDir, 'dir')) {
            $this->mkdirtree($destDir);
        }
        if (copy((string) $source, (string) $dest)) {
            return $this->setMode($dest, FILE_MODE_MASK);
        }
        return false;
    }

    /**
     * Copy a directory.
     * @param string $source
     * @param string $dest
     * @return boolean
     */
    public function copyDir($source, $dest) {
        if (is_dir($source)) {
            $this->mkdir($dest);
            $destDir = dir($source);

            if ($destDir !== false) {
                while (($entry = $destDir->read()) !== false) {
                    if ($entry === '.' || $entry === '..') {
                        continue;
                    }

                    $Entry = $source . DIRECTORY_SEPARATOR . $entry;
                    if (is_dir($Entry)) {
                        $this->copyDir($Entry, $dest . DIRECTORY_SEPARATOR . $entry);
                    } else {
                        $this->copyFile($Entry, $dest . DIRECTORY_SEPARATOR . $entry);
                    }
                }
                $destDir->close();
            }
        } else {
            $this->copyFile($source, $dest);
        }

        return $this->fileExists($dest, 'dir');
    }

    /**
     * Read a file's contents.
     * @param string $filePath
     * @param bool $output
     * @return string|boolean
     */
    public function readFile($filePath, $output = false) {
        if (is_readable($filePath)) {
            $f = fopen($filePath, 'rb');

            if ($f === false) {
                return false;
            }

            $data = '';
            while (!feof($f)) {
                $chunk = fread($f, 4096);
                if ($chunk === false) {
                    break;
                }
                if ($output) {
                    echo $chunk;
                } else {
                    $data .= $chunk;
                }
            }
            fclose($f);

            return $output ? true : $data;
        }
        return false;
    }

    /**
     * Download a file.
     * @param string $filePath
     * @param string|null $mediaType
     * @param bool $inline
     * @param string|null $fileName
     * @return boolean
     */
    public function downloadFile($filePath, $mediaType = null, $inline = false, $fileName = null) {
        $result = null;
        if (HookRegistry::dispatch('FileManager::downloadFile', [$filePath, $mediaType, $inline, &$result, $fileName])) {
            return $result;
        }
        
        $postDownloadHookList = ['FileManager::downloadFileFinished', 'UsageEventPlugin::getUsageEvent'];
        $returner = false;

        if (is_readable($filePath)) {
            if ($mediaType === null) {
                $mediaType = PKPString::mime_content_type($filePath);
                if (empty($mediaType)) {
                    $mediaType = 'application/octet-stream';
                }
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

            header("Content-Type: " . (string) $mediaType);
            header('Content-Length: ' . (int) filesize($filePath));
            header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . "; filename=\"" . (string) $fileName . "\"");
            header('Cache-Control: private'); 
            header('Pragma: public');

            self::readFile($filePath, true);

            if ($postDownloadHooks) {
                foreach ($postDownloadHooks as $hookName => $hooks) {
                    HookRegistry::setHooks($hookName, $hooks);
                }
            }
            $returner = true;
        }
        
        HookRegistry::dispatch('FileManager::downloadFileFinished', [&$returner]);

        return $returner;
    }

    /**
     * Delete a file.
     * @param string $filePath
     * @return boolean
     */
    public function deleteFile($filePath) {
        if ($this->fileExists($filePath)) {
            return unlink((string) $filePath);
        }
        return false;
    }

    /**
     * Create a new directory.
     * @param string $dirPath
     * @param int|null $perms
     * @return boolean
     */
    public function mkdir($dirPath, $perms = null) {
        if ($perms !== null) {
            return mkdir((string) $dirPath, (int) $perms);
        } else {
            if (mkdir((string) $dirPath)) {
                return $this->setMode($dirPath, DIRECTORY_MODE_MASK);
            }
            return false;
        }
    }

    /**
     * Remove a directory.
     * @param string $dirPath
     * @return boolean
     */
    public function rmdir($dirPath) {
        return rmdir((string) $dirPath);
    }

    /**
     * Delete all contents including directory (equivalent to "rm -r")
     * @param string $file
     */
    public function rmtree($file) {
        $file = (string) $file;
        if (file_exists($file)) {
            if (is_dir($file)) {
                $handle = opendir($file);
                if ($handle !== false) {
                    while (($filename = readdir($handle)) !== false) {
                        if ($filename !== '.' && $filename !== '..') {
                            $this->rmtree($file . DIRECTORY_SEPARATOR . $filename);
                        }
                    }
                    closedir($handle);
                }
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }

    /**
     * Create a new directory, including all intermediate directories if required (equivalent to "mkdir -p")
     * @param string $dirPath
     * @param int|null $perms
     * @return boolean
     */
    public function mkdirtree($dirPath, $perms = null) {
        $dirPath = (string) $dirPath;
        if (!file_exists($dirPath)) {
            if ($dirPath === dirname($dirPath)) {
                fatalError('There are no readable files in this directory tree. Are safe mode or open_basedir active?');
                return false;
            } elseif ($this->mkdirtree(dirname($dirPath), $perms)) {
                return $this->mkdir($dirPath, $perms);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Check if a file path is valid;
     * @param string $filePath
     * @param string $type
     * @return boolean
     */
    public function fileExists($filePath, $type = 'file') {
        $filePath = (string) $filePath;
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
     * @param string $type
     * @return string
     */
    public function getDocumentType($type) {
        if ($this->getImageExtension((string) $type)) {
            return DOCUMENT_TYPE_IMAGE;
        }

        switch ((string) $type) {
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
     * Returns file extension associated with the given document type
     * @param string $type
     * @return string|false
     */
    public function getDocumentExtension($type) {
        switch ((string) $type) {
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
     * Returns file extension associated with the given image type
     * @param string $type
     * @return string|false
     */
    public function getImageExtension($type) {
        switch ((string) $type) {
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
     * @param string $fileName
     * @return string
     */
    public function getExtension($fileName) {
        $ext = pathinfo((string) $fileName, PATHINFO_EXTENSION);
        return is_string($ext) ? $ext : '';
    }

    /**
     * Truncate a filename to fit in the specified length.
     * @param string $fileName
     * @param int $length
     * @return string
     */
    public function truncateFileName($fileName, $length = 127) {
        $fileName = (string) $fileName;
        $length = (int) $length;
        if (PKPString::strlen($fileName) <= $length) {
            return $fileName;
        }
        $ext = $this->getExtension($fileName);
        $truncated = PKPString::substr($fileName, 0, $length - 1 - PKPString::strlen($ext)) . '.' . $ext;
        return PKPString::substr($truncated, 0, $length);
    }

    /**
     * Return pretty file size string (in B, KB, MB, or GB units).
     * @param mixed $size
     * @return string
     */
    public function getNiceFileSize($size) {
        $size = (float) $size;
        $kb = 1024;
        $mb = 1024 * $kb;
        $gb = 1024 * $mb;

        if ($size >= $gb) {
            return number_format($size / $gb, 3, ',', '.') . ' GB';
        } elseif ($size >= $mb) {
            return number_format($size / $mb, 3, ',', '.') . ' MB';
        } elseif ($size >= $kb) {
            return number_format($size / $kb, 0, ',', '.') . ' KB';
        } else {
            return number_format($size, 0, ',', '.') . ' B';
        }
    }

    /**
     * Set file/directory mode based on the 'umask' config setting.
     * @param string $path
     * @param int $mask
     * @return boolean
     */
    public function setMode($path, $mask) {
        $umask = Config::getVar('files', 'umask');
        if (!$umask) {
            return true;
        }
        return chmod((string) $path, (int) $mask & ~(int) $umask);
    }

    /**
     * Parse the file extension from a filename/path.
     * @param string $fileName
     * @return string
     */
    public function parseFileExtension($fileName) {
        $fileName = (string) $fileName;
        $fileExtension = (string) pathinfo($fileName, PATHINFO_EXTENSION);

        if (empty($fileExtension) || stristr($fileExtension, 'php') || strlen($fileExtension) > 6 || !preg_match('/^\w+$/', $fileExtension)) {
            $fileExtension = 'txt';
        }

        if (strtolower(substr($fileName, -7)) === '.tar.gz') {
            $fileExtension = substr($fileName, -6);
        }

        return $fileExtension;
    }

    /**
     * Decompress passed gziped file.
     * @param string $filePath
     * @param string|null $errorMsg
     * @return boolean|string
     */
    public function decompressFile($filePath, &$errorMsg = null) {
        return $this->_executeGzip((string) $filePath, true, $errorMsg);
    }

    /**
     * Compress passed file.
     * @param string $filePath
     * @param string|null $errorMsg
     * @return boolean|string
     */
    public function compressFile($filePath, &$errorMsg = null) {
        return $this->_executeGzip((string) $filePath, false, $errorMsg);
    }

    //
    // Private helper methods.
    //

    /**
     * Execute gzip to compress or extract files.
     * @param string $filePath
     * @param bool $decompress
     * @param string|null $errorMsg
     * @return false|string
     */
    public function _executeGzip($filePath, $decompress = false, &$errorMsg = null) {
        PKPLocale::requireComponents(LOCALE_COMPONENT_CORE_ADMIN);
        $gzipPath = Config::getVar('cli', 'gzip');
        
        if (empty($gzipPath) || !is_string($gzipPath) || !is_executable($gzipPath)) {
            $errorMsg = __('admin.error.executingUtil', ['utilPath' => (string) $gzipPath, 'utilVar' => 'gzip']);
            return false;
        }
        
        $gzipCmd = escapeshellarg($gzipPath);
        if ($decompress) {
            $gzipCmd .= ' -d';
        }
        
        $output = [$filePath];
        $returnValue = 0;
        $gzipCmd .= ' ' . escapeshellarg($filePath);
        
        if (!Core::isWindows()) {
            $gzipCmd .= ' 2>&1';
        }
        
        exec($gzipCmd, $output, $returnValue);
        
        if ($returnValue > 0) {
            $errorMsg = __('admin.error.utilExecutionProblem', ['utilPath' => $gzipPath, 'output' => implode(PHP_EOL, $output)]);
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
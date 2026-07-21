<?php
declare(strict_types=1);

/**
 * @file classes/file/FileWrapper.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileWrapper
 * @ingroup file
 *
 * @brief Class abstracting operations for reading remote files using various protocols.
 * (for when allow_url_fopen is disabled).
 */

class FileWrapper {

    /** @var string URL to the file */
    public $url;

    /** @var array parsed URL info */
    public $info;

    /** @var resource|null the file descriptor */
    public $fp = null;

    /**
     * Constructor.
     * @param mixed $url
     * @param mixed $info
     */
    public function __construct($url, $info) {
        $this->url = (string) $url;
        $this->info = is_array($info) ? $info : [];
        $this->fp = null;
    }

    /**
     * [SHIM] Backward Compatibility
     * @param mixed $url
     * @param mixed $info
     */
    public function FileWrapper($url, $info) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::FileWrapper(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        $this->__construct($url, $info);
    }

    /**
     * Read and return the contents of the file (like file_get_contents()).
     * @return string
     */
    public function contents() {
        $contents = '';
        $retval = $this->open();
        
        if ($retval) {
            if (is_object($retval)) {
                return $retval->contents();
            }
            while (!$this->eof()) {
                $contents .= $this->read();
            }
            $this->close();
        }
        return $contents;
    }

    /**
     * Open the file.
     * @param string $mode only 'r' (read-only) is currently supported
     * @return boolean|object
     */
    public function open($mode = 'r') {
        $this->fp = null;
        
        if (empty($this->url)) {
            return false;
        }

        $this->fp = @fopen($this->url, (string) $mode);
        return ($this->fp !== false);
    }

    /**
     * Close the file.
     */
    public function close() {
        if (is_resource($this->fp)) {
            fclose($this->fp);
        }
        $this->fp = null;
    }

    /**
     * Read from the file.
     * @param int $len
     * @return string
     */
    public function read($len = 8192) {
        if (!is_resource($this->fp)) {
            return '';
        }
        return (string) fread($this->fp, (int) $len);
    }

    /**
     * Check for end-of-file.
     * @return boolean
     */
    public function eof() {
        if (!is_resource($this->fp)) {
            return true;
        }
        return (bool) feof($this->fp);
    }

    //
    // Static
    //

    /**
     * Return instance of a class for reading the specified URL.
     * @param mixed $source
     * @return FileWrapper
     */
    public static function wrapper($source) {
        $wrapper = null;

        if (ini_get('allow_url_fopen') && Config::getVar('general', 'allow_url_fopen') && is_string($source)) {
            $info = parse_url($source);
            $wrapper = new FileWrapper($source, is_array($info) ? $info : []);
            
        } elseif (is_resource($source)) {
            import('lib.pkp.classes.file.wrappers.ResourceWrapper');
            $wrapper = new ResourceWrapper($source);
            
        } else {
            $sourceString = (string) $source;
            $info = parse_url($sourceString);

            if (is_array($info) && isset($info['scheme'])) {
                $scheme = $info['scheme'];
            } else {
                $scheme = null;
            }

            $application = Application::getApplication();
            if ($application === null) {
                $userAgent = 'ScholarWizdam/?';
            } elseif (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE') || defined('PHPUNIT_CURRENT_MOCK_ENV')) {
                $userAgent = $application->getName() . '/?';
            } else {
                $currentVersion = $application->getCurrentVersion();
                $versionString = $currentVersion ? (string) $currentVersion->getVersionString() : '?';
                $userAgent = $application->getName() . '/' . $versionString;
            }

            $safeInfo = is_array($info) ? $info : [];

            switch ($scheme) {
                case 'http':
                    import('lib.pkp.classes.file.wrappers.HTTPFileWrapper');
                    $wrapper = new HTTPFileWrapper($sourceString, $safeInfo);
                    $wrapper->addHeader('User-Agent', $userAgent);
                    break;
                case 'https':
                    import('lib.pkp.classes.file.wrappers.HTTPSFileWrapper');
                    $wrapper = new HTTPSFileWrapper($sourceString, $safeInfo);
                    $wrapper->addHeader('User-Agent', $userAgent);
                    break;
                case 'ftp':
                    import('lib.pkp.classes.file.wrappers.FTPFileWrapper');
                    $wrapper = new FTPFileWrapper($sourceString, $safeInfo);
                    break;
                default:
                    $wrapper = new FileWrapper($sourceString, $safeInfo);
            }
        }

        if ($wrapper === null) {
            $wrapper = new FileWrapper((string) $source, []);
        }

        return $wrapper;
    }

}
?>
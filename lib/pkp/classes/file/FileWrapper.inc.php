<?php
declare(strict_types=1);

/**
 * @defgroup file_wrapper
 */

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
 *
 * TODO:
 * - Other protocols?
 * - Write mode (where possible)
 */

class FileWrapper {

    /** @var string URL to the file */
    public $url;

    /** @var array parsed URL info */
    public $info;

    /** @var resource|null the file descriptor */
    public $fp;

    /**
     * Constructor.
     * @param $url string
     * @param $info array
     */
    public function __construct($url, $info) {
        $this->url = $url;
        $this->info = $info;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function FileWrapper($url, $info) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::FileWrapper(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($url, $info);
    }

    /**
     * Read and return the contents of the file (like file_get_contents()).
     * @return string
     */
    public function contents() {
        $contents = '';
        if ($retval = $this->open()) {
            if (is_object($retval)) { // It may be a redirect
                return $retval->contents();
            }
            while (!$this->eof())
                $contents .= $this->read();
            $this->close();
        }
        return $contents;
    }

    /**
     * Open the file.
     * @param $mode string only 'r' (read-only) is currently supported
     * @return boolean|object
     */
    public function open($mode = 'r') {
        $this->fp = null;
        $this->fp = fopen($this->url, $mode);
        return ($this->fp !== false);
    }

    /**
     * Close the file.
     */
    public function close() {
        fclose($this->fp);
        unset($this->fp);
    }

    /**
     * Read from the file.
     * @param $len int
     * @return string
     */
    public function read($len = 8192) {
        return fread($this->fp, $len);
    }

    /**
     * Check for end-of-file.
     * @return boolean
     */
    public function eof() {
        return feof($this->fp);
    }


    //
    // Static
    //

    /**
     * Return instance of a class for reading the specified URL.
     * @param $source mixed; URL, filename, or resources
     * @return FileWrapper
     */
    public static function wrapper($source) {
        if (ini_get('allow_url_fopen') && Config::getVar('general', 'allow_url_fopen') && is_string($source)) {
            $info = parse_url($source);
            $wrapper = new FileWrapper($source, $info);
        } elseif (is_resource($source)) {
            // $source is an already-opened file descriptor.
            import('lib.pkp.classes.file.wrappers.ResourceWrapper');
            $wrapper = new ResourceWrapper($source);
        } else {
            // $source should be a URL.
            $info = parse_url((string) $source);
            if (isset($info['scheme'])) {
                $scheme = $info['scheme'];
            } else {
                $scheme = null;
            }

            // [WIZDAM FIX] Application bisa null saat FileWrapper dipanggil 
            // prematur misalnya saat locale plugin di-load sebelum Application selesai diregistrasi.
            // Fallback ke user agent generik agar tidak crash.
            $application = Application::getApplication();
            if ($application === null) {
                $userAgent = 'ScholarWizdam/?';
            } elseif (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE') || defined('PHPUNIT_CURRENT_MOCK_ENV')) {
                $userAgent = $application->getName() . '/?';
            } else {
                $currentVersion = $application->getCurrentVersion();
                $userAgent = $application->getName() . '/' . $currentVersion->getVersionString();
            }

            switch ($scheme) {
                case 'http':
                    import('lib.pkp.classes.file.wrappers.HTTPFileWrapper');
                    $wrapper = new HTTPFileWrapper($source, $info);
                    $wrapper->addHeader('User-Agent', $userAgent);
                    break;
                case 'https':
                    import('lib.pkp.classes.file.wrappers.HTTPSFileWrapper');
                    $wrapper = new HTTPSFileWrapper($source, $info);
                    $wrapper->addHeader('User-Agent', $userAgent);
                    break;
                case 'ftp':
                    import('lib.pkp.classes.file.wrappers.FTPFileWrapper');
                    $wrapper = new FTPFileWrapper($source, $info);
                    break;
                default:
                    $wrapper = new FileWrapper($source, $info);
            }
        }

        return $wrapper;
    }
}

?>
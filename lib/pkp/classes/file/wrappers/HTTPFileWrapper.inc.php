<?php
declare(strict_types=1);

/**
 * @file classes/file/wrappers/HTTPFileWrapper.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package file.wrappers
 * @ingroup file_wrappers
 *
 * Class providing a wrapper for the HTTP protocol.
 * (for when allow_url_fopen is disabled).
 *
 */

class HTTPFileWrapper extends FileWrapper {
    /** @var array Headers */
    public $headers;
    public $defaultPort;
    public $defaultHost;
    public $defaultPath;
    public $redirects;

    public $proxyHost;
    public $proxyPort;
    public $proxyUsername;
    public $proxyPassword;

    /**
     * Constructor.
     * @param $url string
     * @param $info array
     * @param $redirects int
     */
    public function __construct($url, $info, $redirects = 5) {
        parent::__construct($url, $info);
        $this->setDefaultPort(80);
        $this->setDefaultHost('localhost');
        $this->setDefaultPath('/');
        $this->redirects = $redirects;

        $this->proxyHost = Config::getVar('proxy', 'http_host');
        $this->proxyPort = Config::getVar('proxy', 'http_port');
        $this->proxyUsername = Config::getVar('proxy', 'proxy_username');
        $this->proxyPassword = Config::getVar('proxy', 'proxy_password');
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function HTTPFileWrapper($url, $info, $redirects = 5) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error('Class ' . get_class($this) . ' uses deprecated constructor parent::HTTPFileWrapper(). Please refactor to parent::__construct().', E_USER_DEPRECATED);
        }
        self::__construct($url, $info, $redirects);
    }

    public function setDefaultPort($port) {
        $this->defaultPort = $port;
    }

    public function setDefaultHost($host) {
        $this->defaultHost = $host;
    }

    public function setDefaultPath($path) {
        $this->defaultPath = $path;
    }

    public function addHeader($name, $value) {
        if (!isset($this->headers)) {
            $this->headers = array();
        }
        $this->headers[$name] = $value;
    }

    /**
     * Open the file.
     * @param $mode string
     * @return boolean|object
     */
    public function open($mode = 'r') {
        $realHost = $host = isset($this->info['host']) ? $this->info['host'] : $this->defaultHost;
        $port = isset($this->info['port']) ? (int)$this->info['port'] : $this->defaultPort;
        $path = isset($this->info['path']) ? $this->info['path'] : $this->defaultPath;
        if (isset($this->info['query'])) $path .= '?' . $this->info['query'];

        if (!empty($this->proxyHost)) {
            $realHost = $host;
            $host = $this->proxyHost;
            $port = $this->proxyPort;
            if (!empty($this->proxyUsername)) {
                $this->headers['Proxy-Authorization'] = 'Basic ' . base64_encode($this->proxyUsername . ':' . $this->proxyPassword);
            }
        }

        if (!($this->fp = fsockopen($host, $port, $errno, $errstr)))
            return false;

        $additionalHeadersString = '';
        if (is_array($this->headers)) foreach ($this->headers as $name => $value) {
            $additionalHeadersString .= "$name: $value\r\n";
        }

        $requestHost = preg_replace("!^.*://!", "", $realHost);
        $request = 'GET ' . (empty($this->proxyHost)?$path:$this->url) . " HTTP/1.0\r\n" .
            "Host: $requestHost\r\n" .
            $additionalHeadersString .
            "Connection: Close\r\n\r\n";
        fwrite($this->fp, $request);

        $response = fgets($this->fp, 4096);
        $rc = 0;
        sscanf($response, "HTTP/%*s %u %*[^\r\n]\r\n", $rc);
        if ($rc == 200) {
            while(fgets($this->fp, 4096) !== "\r\n");
            return true;
        }
        if(preg_match('!^3\d\d$!', (string)$rc) && $this->redirects >= 1) {
            for($response = '', $time = time(); !feof($this->fp) && $time >= time() - 15; ) $response .= fgets($this->fp, 128);
            if (preg_match('!^(?:(?:Location)|(?:URI)|(?:location)): ([^\s]+)[\r\n]!m', $response, $matches)) {
                $this->close();
                $location = $matches[1];
                if (preg_match('!^[a-z]+://!', $location)) {
                    $this->url = $location;
                } else {
                    $newPath = ($this->info['path'] !== '' && strpos($location, '/') !== 0  ? dirname($this->info['path']) . '/' : (strpos($location, '/') === 0 ? '' : '/')) . $location;
                    $this->info['path'] = $newPath;
                    $this->url = $this->glue_url($this->info);
                }
                // Hapus '&' pada return value
                $returner = FileWrapper::wrapper($this->url);
                if (is_object($returner) && property_exists($returner, 'redirects')) {
                    $returner->redirects = $this->redirects - 1;
                }
                return $returner;
            }
        }
        $this->close();
        return false;
    }

    public function glue_url ($parsed) {
        // Thanks to php dot net at NOSPAM dot juamei dot com
        // See http://www.php.net/manual/en/function.parse-url.php
        if (! is_array($parsed)) return false;
        $uri = isset($parsed['scheme']) ? $parsed['scheme'].':'.((strtolower_codesafe($parsed['scheme']) == 'mailto') ? '':'//'): '';
        $uri .= isset($parsed['user']) ? $parsed['user'].($parsed['pass']? ':'.$parsed['pass']:'').'@':'';
        $uri .= isset($parsed['host']) ? $parsed['host'] : '';
        $uri .= isset($parsed['port']) ? ':'.$parsed['port'] : '';
        $uri .= isset($parsed['path']) ? $parsed['path'] : '';
        $uri .= isset($parsed['query']) ? '?'.$parsed['query'] : '';
        $uri .= isset($parsed['fragment']) ? '#'.$parsed['fragment'] : '';
        return $uri;
    }
}

?>
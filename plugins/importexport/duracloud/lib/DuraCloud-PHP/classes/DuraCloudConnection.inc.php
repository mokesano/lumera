<?php
declare(strict_types=1);

/**
 * @defgroup duracloud_classes
 */

/**
 * @file classes/DuraCloudConnection.inc.php
 *
 * Copyright (c) 2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DuraCloudConnection
 * @ingroup duracloud_classes
 *
 * @brief DuraCloud Connection class
 * [WIZDAM EDITION] Refactored for PHP 8.0+ (Strict Types, Visibility, Reference Cleanup)
 */

class DuraCloudConnection {
    
    /** @var string */
    protected $baseUrl;

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var string */
    protected $headers = '';

    /** @var string|null */
    protected $data = null;

    /** @var bool Used internally by getFile */
    protected $inHeader = false;

    /** @var resource|null Used internally by getFile */
    protected $fp;

    /**
     * Construct a new DuraCloudConnection.
     * @param string $baseUrl Base URL to DuraCloud, i.e. https://pkp.duracloud.org
     * @param string $username Username
     * @param string $password Password
     */
    public function __construct($baseUrl, $username, $password) {
        $this->baseUrl = $baseUrl;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function DuraCloudConnection($baseUrl, $username, $password) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Execute a GET request to DuraCloud. Not for external use.
     * @param string $path
     * @param array $params
     * @return string|bool data or false
     */
    public function get($path, $params = []) {
        $ch = $this->_curlOpenHandle($this->username, $this->password);
        if (!$ch) return false;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/' . $path . $this->_buildUrlVars($params));
        list($this->headers, $this->data) = $this->_separateHeadersFromData(curl_exec($ch));

        curl_close($ch);
        return $this->data;
    }

    /**
     * Execute a GET request to DuraCloud, returning output in a file. Not for external use.
     * @param string $path
     * @param resource $fp
     * @param array $params
     * @return mixed false on failure, file size on success
     */
    public function getFile($path, $fp, $params = []) {
        $ch = $this->_curlOpenHandle($this->username, $this->password);
        if (!$ch) return false;

        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/' . $path . $this->_buildUrlVars($params));
        $this->fp = $fp;
        $this->headers = '';
        $this->inHeader = true;
        
        // Remove reference & from $this in array callback for PHP 8 compatibility
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, [$this, '_addData']);
        
        $result = curl_exec($ch);
        if (!$result) {
            curl_close($ch);
            return false; // Failure
        }
        $this->fp = null; // Clear internal reference
        $result = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
        curl_close($ch);
        $this->data = null;

        return $result;
    }

    /**
     * Execute a HEAD request to DuraCloud. Not for external use.
     * @param string $path
     * @param array $params
     * @return array|bool headers or false
     */
    public function head($path, $params = []) {
        $ch = $this->_curlOpenHandle($this->username, $this->password);
        if (!$ch) return false;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/' . $path . $this->_buildUrlVars($params));
        curl_setopt($ch, CURLOPT_NOBODY, 1);

        list($this->headers, $this->data) = $this->_separateHeadersFromData(curl_exec($ch));

        curl_close($ch);
        return $this->getHeaders();
    }

    /**
     * Execute a POST request to DuraCloud. Not for external use.
     * @param string $path
     * @param array $params Associative array of POST parameters
     * @param array $headers
     * @return string|bool
     */
    public function post($path, $params = [], $headers = []) {
        $ch = $this->_curlOpenHandle($this->username, $this->password);
        if (!$ch) return false;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/' . $path);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_makeHeaderList($headers));

        list($this->headers, $this->data) = $this->_separateHeadersFromData(curl_exec($ch));

        curl_close($ch);
        return $this->data;
    }

    /**
     * Execute a PUT request to DuraCloud. Not for external use.
     * @param string $path
     * @param resource|null $fp
     * @param int $size
     * @param array $params Associative array of URL parameters
     * @param array $headers Associative array of HTTP headers
     * @return array|bool
     */
    public function put($path, $fp = null, $size = 0, $params = [], $headers = []) {
        $ch = $this->_curlOpenHandle($this->username, $this->password);
        if (!$ch) return false;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PUT, 1);
        curl_setopt($ch, CURLOPT_INFILESIZE, $size);

        // Force an empty Expect header; see
        // http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
        $headers['Expect'] = '';
        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_makeHeaderList($headers));

        if ($fp) curl_setopt($ch, CURLOPT_INFILE, $fp);
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/' . $path . $this->_buildUrlVars($params));

        list($this->headers, $this->data) = $this->_separateHeadersFromData(curl_exec($ch));

        curl_close($ch);
        return $this->getHeaders();
    }

    /**
     * Execute a DELETE request to DuraCloud. Not for external use.
     * @param string $path
     * @param array $params Associative array of URL parameters
     * @return string|bool
     */
    public function delete($path, $params = []) {
        $ch = $this->_curlOpenHandle($this->username, $this->password);
        if (!$ch) return false;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_URL, $this->baseUrl . '/' . $path . $this->_buildUrlVars($params));

        list($this->headers, $this->data) = $this->_separateHeadersFromData(curl_exec($ch));

        curl_close($ch);
        return $this->data;
    }

    /**
     * Return the data resulting from the last successful operation
     * @return string|null
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Return the headers resulting from the last successful operation
     * @return array
     */
    public function getHeaders() {
        // First, split the header chunk into lines
        $lines = explode("\r\n", $this->headers);

        // Remove the response line and treat it specially
        $response = array_shift($lines);
        $returner = ['response' => $response];

        // For the rest of the lines, split into associative array
        foreach ($lines as $line) {
            $i = strpos($line, ':');
            if ($i !== false) {
                $returner[trim(substr($line, 0, $i))] = trim(substr($line, $i+2));
            }
        }
        return $returner;
    }


    //
    // cURL / REST-related functions.
    //

    /**
     * Open a cURL handle. Not for external use.
     * @param string $username
     * @param string $password
     * @return \CurlHandle|resource|bool
     */
    protected function _curlOpenHandle($username, $password) {
        // Check to see whether or not cURL support is installed
        if (!function_exists('curl_init')) {
            return false;
        }

        // Initialize the cURL handle object, if possible.
        $ch = curl_init();

        if ($ch) {
            // Set common cURL options
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            if (defined('DURACLOUD_PHP_VERSION')) {
                curl_setopt($ch, CURLOPT_USERAGENT, 'DuraCloud-PHP ' . DURACLOUD_PHP_VERSION); 
            }
            curl_setopt($ch, CURLOPT_SSLVERSION, 6); // TLSv1.2
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        return $ch;
    }

    /**
     * Used internally to take a response and split it into headers and response data.
     * @param string|bool $response
     * @return array (headers, data) iff both headers and data were found; otherwise string data
     */
    protected function _separateHeadersFromData($response) {
        if ($response === false) return ['', ''];
        
        $separator = "\r\n\r\n";
        $i = strpos($response, $separator);
        if (!$i) return [$response, '']; // If no separator was found, assume logic (or handle as error)

        return [
            substr($response, 0, $i),
            substr($response, $i + strlen($separator))
        ];
    }

    /**
     * Used internally to build a portion of a URL describing variables (from the '?' onwards).
     * @param array $urlVars
     * @return string
     */
    protected function _buildUrlVars($urlVars) {
        $returner = '';
        foreach ($urlVars as $name => $value) {
            if (!empty($returner)) $returner .= '&';
            $returner .= urlencode($name) . '=' . urlencode((string)$value);
        }
        if ($returner !== '') $returner = '?' . $returner;
        return $returner;
    }

    /**
     * Turn an associative array of headers into a list as CURLOPT_HTTPHEADER expects.
     * @param array $headers
     * @return array
     */
    protected function _makeHeaderList($headers) {
        $headerList = [];
        foreach ($headers as $name => $value) {
            $headerList[] = "$name: $value";
        }
        return $headerList;
    }

    /**
     * Used in getFile to read data from the server, toggling from headers to data
     * @param resource $ch
     * @param string $data
     * @return int
     */
    public function _addData($ch, $data) {
        if (!$this->inHeader) {
            if (is_resource($this->fp)) {
                return fwrite($this->fp, $data);
            }
            return 0;
        }

        // We're still in the headers; append to current data set
        if ($data === "\r\n") {
            $this->inHeader = false;
        } else {
            $this->headers .= $data;
        }
        return strlen($data);
    }
}

?>
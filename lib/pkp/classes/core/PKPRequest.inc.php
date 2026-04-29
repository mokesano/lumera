<?php
declare(strict_types=1);

/**
 * @file classes/core/PKPRequest.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPRequest
 * @ingroup core
 *
 * @brief Class providing operations associated with HTTP requests.
 */

class PKPRequest {
    //
    // Internal state - please do not reference directly
    //
    /** @var PKPRouter router instance used to route this request */
    public $_router = null;
    /** @var Dispatcher dispatcher instance used to dispatch this request */
    public $_dispatcher = null;
    /** @var array the request variables cache (GET/POST) */
    public $_requestVars = null;
    /** @var string request base path */
    public $_basePath;
    /** @var string request path */
    public $_requestPath;
    /** @var boolean true if restful URLs are enabled in the config */
    public $_isRestfulUrlsEnabled;
    /** @var boolean true if path info is enabled for this server */
    public $_isPathInfoEnabled;
    /** @var string server host */
    public $_serverHost;
    /** @var string base url */
    public $_baseUrl;
    /** @var string request protocol */
    public $_protocol;

    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPRequest() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPRequest(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get the router instance
     * @return PKPRouter
     */
    public static function getRouter() {
        $instance = self::_checkThis();
        return $instance->_router;
    }

    /**
     * Set the router instance
     * @param $router instance PKPRouter
     */
    public static function setRouter($router) {
        $instance = self::_checkThis();
        $instance->_router = $router;
    }

    /**
     * Set the dispatcher
     * @param $dispatcher Dispatcher
     */
    public static function setDispatcher($dispatcher) {
        $instance = self::_checkThis();
        $instance->_dispatcher = $dispatcher;
    }

    /**
     * Get the dispatcher
     * @return Dispatcher
     */
    public static function getDispatcher() {
        $instance = self::_checkThis();
        return $instance->_dispatcher;
    }


    /**
     * Perform an HTTP redirect to an absolute or relative (to base system URL) URL.
     * @param $url string (exclude protocol for local redirects)
     */
    public static function redirectUrl($url) {
        // self::_checkThis(); // Optional verification
        
        // HOOK: Request::redirect
        if (HookRegistry::dispatch('Request::redirect', array(&$url))) {
            return;
        }

        $url = preg_replace('/[\r\n]/', '', $url);
        
        // [WIZDAM SECURITY] Validasi URL relatif menggunakan isPathValid() 
        // yang sudah ada dari pemanggilan redirectUrl() langsung oleh plugin
        // dengan input yang tidak dikonstruksi melalui dispatcher.
        if (!str_starts_with($url, 'http://') && 
            !str_starts_with($url, 'https://') && 
            !self::isPathValid($url)) {
            error_log('[WIZDAM SECURITY] redirectUrl() blocked invalid path: ' . $url);
            return;
        }
        
        if (!headers_sent()) { 
            header("Location: $url"); 
        }
        exit();
    }

    /**
     * Request an HTTP redirect via JSON to be used from components.
     * @param $url string
     */
    public static function redirectUrlJson($url) {
        import('lib.pkp.classes.core.JSONMessage');
        $json = new JSONMessage(true);
        $json->setEvent('redirectRequested', $url);
        header('Content-Type: application/json');
        return $json->getString();
    }

    /**
     * Redirect to the current URL, forcing the HTTPS protocol to be used.
     */
    public static function redirectSSL() {
        $instance = self::_checkThis();

        // Note that we are intentionally skipping PKP processing of REQUEST_URI and QUERY_STRING for a protocol redirect
        // This processing is deferred to the redirected (target) URI
        $url = 'https://' . $instance->getServerHost() . $_SERVER['REQUEST_URI'];
        $queryString = $_SERVER['QUERY_STRING'];
        if (!empty($queryString)) $url .= "?$queryString";
        $instance->redirectUrl($url);
    }

    /**
     * Redirect to the current URL, forcing the HTTP protocol to be used.
     */
    public static function redirectNonSSL() {
        $instance = self::_checkThis();

        // Note that we are intentionally skipping PKP processing of REQUEST_URI and QUERY_STRING for a protocol redirect
        // This processing is deferred to the redirected (target) URI
        $url = 'http://' . $instance->getServerHost() . $_SERVER['REQUEST_URI'];
        $queryString = $_SERVER['QUERY_STRING'];
        if (!empty($queryString)) $url .= "?$queryString";
        $instance->redirectUrl($url);
    }

    /**
     * Get the IF_MODIFIED_SINCE date (as a numerical timestamp) if available
     * @return int
     */
    public static function getIfModifiedSince() {
        if (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) return null;
        return strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    }

    /**
     * Get the base URL of the request (excluding script).
     * @return string
     */
    public static function getBaseUrl() {
        $instance = self::_checkThis();

        if (!isset($instance->_baseUrl)) {
            $serverHost = $instance->getServerHost(null, false); // Default behavior check
            if ($serverHost !== false && !is_null($serverHost)) {
                // Auto-detection worked.
                $instance->_baseUrl = $instance->getProtocol() . '://' . $instance->getServerHost() . $instance->getBasePath();
            } else {
                // Auto-detection didn't work (e.g. this is a command-line call); use configuration param
                $instance->_baseUrl = Config::getVar('general', 'base_url');
            }
            // HOOK: Request::getBaseUrl
            HookRegistry::dispatch('Request::getBaseUrl', array(&$instance->_baseUrl));
        }

        return $instance->_baseUrl;
    }

    /**
     * Get the base path of the request (excluding trailing slash).
     * @return string
     */
    public static function getBasePath() {
        $instance = self::_checkThis();

        if (!isset($instance->_basePath)) {
            # Strip the PHP filename off of the script's executed path
            $scriptName = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
            $path = preg_replace('#/[^/]*$#', '', $scriptName . (substr($scriptName, -1) == '/' || preg_match('#.php$#i', $scriptName) ? '' : '/'));

            // Encode characters which need to be encoded in a URL.
            $parts = explode('/', $path);
            foreach ($parts as $i => $part) {
                // Use self:: instead of $this for callback in static context
                $pieces = array_map(array('PKPRequest', 'encodeBasePathFragment'), str_split($part));
                $parts[$i] = implode('', $pieces);
            }
            $instance->_basePath = implode('/', $parts);

            if ($instance->_basePath == '/' || $instance->_basePath == '\\') {
                $instance->_basePath = '';
            }
            // HOOK: Request::getBasePath
            HookRegistry::dispatch('Request::getBasePath', array(&$instance->_basePath));
        }

        return $instance->_basePath;
    }

    /**
     * Callback function for getBasePath() to correctly encode (or not encode)
     * a basepath fragment.
     * @param string $fragment
     * @return string
     */
    public static function encodeBasePathFragment($fragment) {
        if (!preg_match('/[A-Za-z0-9-._~!$&\'()*+,;=:@]/', $fragment)) {
            return rawurlencode($fragment);
        }
        return $fragment;
    }

    /**
     * Deprecated
     * @see PKPPageRouter::getIndexUrl()
     */
    public static function getIndexUrl() {
        static $indexUrl;

        $instance = self::_checkThis();
        if (!isset($indexUrl)) {
            $indexUrl = $instance->_delegateToRouter('getIndexUrl');

            // HOOK: Request::getIndexUrl
            HookRegistry::dispatch('Request::getIndexUrl', array(&$indexUrl));
        }

        return $indexUrl;
    }

    /**
     * Get the complete URL to this page, including parameters.
     * @return string
     */
    public static function getCompleteUrl() {
        $instance = self::_checkThis();

        static $completeUrl;

        if (!isset($completeUrl)) {
            $completeUrl = $instance->getRequestUrl();
            $queryString = $instance->getQueryString();
            if (!empty($queryString)) $completeUrl .= "?$queryString";
            // HOOK: Request::getCompleteUrl
            HookRegistry::dispatch('Request::getCompleteUrl', array(&$completeUrl));
        }

        return $completeUrl;
    }

    /**
     * Get the complete URL of the request.
     * @return string
     */
    public static function getRequestUrl() {
        $instance = self::_checkThis();

        static $requestUrl;

        if (!isset($requestUrl)) {
            $requestUrl = $instance->getProtocol() . '://' . $instance->getServerHost() . $instance->getRequestPath();
            // HOOK: Request::getRequestUrl
            HookRegistry::dispatch('Request::getRequestUrl', array(&$requestUrl));
        }

        return $requestUrl;
    }

    /**
     * Get the complete set of URL parameters to the current request.
     * @return string
     */
    public static function getQueryString() {
        static $queryString;

        if (!isset($queryString)) {
            $queryString = isset($_SERVER['QUERY_STRING'])?$_SERVER['QUERY_STRING']:'';
            // HOOK: Request::getQueryString
            HookRegistry::dispatch('Request::getQueryString', array(&$queryString));
        }

        return $queryString;
    }

    /**
     * Get the complete set of URL parameters to the current request as an
     * associative array.
     * @return array
     */
    public static function getQueryArray() {
        $instance = self::_checkThis();

        $queryString = $instance->getQueryString();
        $queryArray = array();

        if (isset($queryString)) {
            parse_str($queryString, $queryArray);
        }

        // Filter out disable_path_info reserved parameters
        foreach (array_merge(Application::getContextList(), array('path', 'page', 'op')) as $varName) {
            if (isset($queryArray[$varName])) unset($queryArray[$varName]);
        }

        return $queryArray;
    }

    /**
     * Get the completed path of the request.
     * @return string
     */
    public static function getRequestPath() {
        $instance = self::_checkThis();

        if (!isset($instance->_requestPath)) {
            if ($instance->isRestfulUrlsEnabled()) {
                $instance->_requestPath = $instance->getBasePath();
            } else {
                $instance->_requestPath = isset($_SERVER['SCRIPT_NAME'])?$_SERVER['SCRIPT_NAME']:'';
            }

            if ($instance->isPathInfoEnabled()) {
                $instance->_requestPath .= isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
            }
            // HOOK: Request::getRequestPath
            HookRegistry::dispatch('Request::getRequestPath', array(&$instance->_requestPath));
        }
        return $instance->_requestPath;
    }

    /**
     * Get the server hostname in the request.
     * @param $default string Default hostname (defaults to localhost)
     * @param $includePort boolean Whether to include non-standard port number; default true
     * @return string
     */
    public static function getServerHost($default = null, $includePort = true) {
        if ($default === null) $default = 'localhost';

        $instance = self::_checkThis();

        if (!isset($instance->_serverHost)) {
            $instance->_serverHost = isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST']
                : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST']
                : (isset($_SERVER['HOSTNAME']) ? $_SERVER['HOSTNAME']
                : null));
            
            // HOOK: Request::getServerHost
            HookRegistry::dispatch('Request::getServerHost', array(&$instance->_serverHost, &$default, &$includePort));
        }

        $host = $instance->_serverHost === null ? $default : $instance->_serverHost;

        if (!$includePort) {
            // Strip the port number, if one is included. (#3912)
            return preg_replace("/:\d*$/", '', $host);
        }

        return $host;
    }

    /**
     * Get the protocol used for the request (HTTP or HTTPS).
     * @return string
     */
    public static function getProtocol() {
        $instance = self::_checkThis();

        if (!isset($instance->_protocol)) {
            $https = isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : '';
            // Use PKPString::strtolower_codesafe if available, otherwise native strtolower
            $httpsLower = class_exists('PKPString') ? PKPString::strtolower($https) : strtolower($https);
            
            $instance->_protocol = ($httpsLower != 'on') ? 'http' : 'https';
            // HOOK: Request::getProtocol
            HookRegistry::dispatch('Request::getProtocol', array(&$instance->_protocol));
        }
        return $instance->_protocol;
    }

    /**
     * Get the request method
     * @return string
     */
    public static function getRequestMethod() {
        $requestMethod = (isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '');
        return $requestMethod;
    }

    /**
     * Determine whether the request is a POST request
     * @return boolean
     */
    public static function isPost() {
        $instance = self::_checkThis();
        return ($instance->getRequestMethod() == 'POST');
    }

    /**
     * Determine whether the request is a GET request
     * @return boolean
     */
    public static function isGet() {
        $instance = self::_checkThis();
        return ($instance->getRequestMethod() == 'GET');
    }

    /**
     * Get the remote IP address for the current request.
     * [WIZDAM] Kompatibilitas PHP 7.4 dan parsing X-Forwarded-For yang benar
     * @return string
     */
    public static function getRemoteAddr() {
        // Gunakan cache statis dalam permintaan ini
        static $remoteAddr;
        if (isset($remoteAddr)) return $remoteAddr;

        // PERBAIKAN 1: Baca dari [general] (sesuai kode OJS 2.4.8)
        // dan JANGAN gunakan default 'true' agar pengaturan 'Off' Anda dihargai.
        $trustedProxy = Config::getVar('general', 'trust_x_forwarded_for'); 
        $ip = null;

        if ($trustedProxy) {
            // Kita percaya proxy. Coba header yang paling umum.
            $headerNames = array(
                'HTTP_X_FORWARDED_FOR',
                'HTTP_CLIENT_IP',
                'HTTP_X_REAL_IP',
                'HTTP_X_CLUSTER_CLIENT_IP',
                'HTTP_FORWARDED_FOR',
                'HTTP_FORWARDED'
            );

            foreach ($headerNames as $header) {
                if (!empty($_SERVER[$header])) {
                    // PERBAIKAN 2: Ambil IP PERTAMA, bukan TERAKHIR
                    $ipList = explode(',', $_SERVER[$header]);
                    $ip = trim($ipList[0]); // [0] adalah IP PERTAMA
                    
                    // Validasi bahwa ini adalah IP yang valid
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        break; // Kita temukan IP yang valid, hentikan pencarian
                    } else {
                        $ip = null; // Bukan IP valid, lanjut cari
                    }
                }
            }
        }

        // Jika setelah semua itu kita tidak menemukan IP,
        // baru kita gunakan REMOTE_ADDR standar
        if ($ip === null) {
            $ip = !empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        }

        // Simpan hasilnya agar tidak perlu dihitung lagi
        $remoteAddr = $ip;

        // HOOK: Request::getRemoteAddr (Restored but commented per original instruction or logic override)
        // HookRegistry::dispatch('Request::getRemoteAddr', array(&$remoteAddr));
        
        return $remoteAddr;
    }

    /**
     * Get the remote domain of the current request
     * @return string
     */
    public static function getRemoteDomain() {
        $instance = self::_checkThis();

        static $remoteDomain;
        if (!isset($remoteDomain)) {
            $remoteDomain = null;
            $remoteAddr = $instance->getRemoteAddr();
            if ($remoteAddr) {
                $remoteDomain = @getHostByAddr($remoteAddr);
            }
            // HOOK: Request::getRemoteDomain
            HookRegistry::dispatch('Request::getRemoteDomain', array(&$remoteDomain));
        }
        return $remoteDomain;
    }

    /**
     * Get the user agent of the current request.
     * @return string
     */
    public static function getUserAgent() {
        static $userAgent;
        if (!isset($userAgent)) {
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $userAgent = $_SERVER['HTTP_USER_AGENT'];
            }
            if (!isset($userAgent) || empty($userAgent)) {
                $userAgent = getenv('HTTP_USER_AGENT');
            }
            if (!isset($userAgent) || $userAgent == false) {
                $userAgent = '';
            }
            // HOOK: Request::getUserAgent
            HookRegistry::dispatch('Request::getUserAgent', array(&$userAgent));
        }
        return $userAgent;
    }

    /**
     * Determine whether the user agent is a bot or not.
     * @return boolean
     */
    public static function isBot() {
        $instance = self::_checkThis();

        static $isBot;
        if (!isset($isBot)) {
            $userAgent = $instance->getUserAgent();
            $isBot = Core::isUserAgentBot($userAgent);
        }
        return $isBot;
    }

    /**
     * Return true if PATH_INFO is enabled.
     */
    public static function isPathInfoEnabled() {
        $instance = self::_checkThis();

        if (!isset($instance->_isPathInfoEnabled)) {
            $instance->_isPathInfoEnabled = Config::getVar('general', 'disable_path_info')?false:true;
        }
        return $instance->_isPathInfoEnabled;
    }

    /**
     * Return true if RESTFUL_URLS is enabled.
     */
    public static function isRestfulUrlsEnabled() {
        $instance = self::_checkThis();

        if (!isset($instance->_isRestfulUrlsEnabled)) {
            $instance->_isRestfulUrlsEnabled = Config::getVar('general', 'restful_urls')?true:false;
        }
        return $instance->_isRestfulUrlsEnabled;
    }

    /**
     * Get site data.
     * @return Site
     */
    public static function getSite() {
        $site = Registry::get('site', true, null);
        if ($site === null) {
            $siteDao = DAORegistry::getDAO('SiteDAO');
            $site = $siteDao->getSite();
            // PHP bug? This is needed for reason or extra queries results.
            Registry::set('site', $site);
        }

        return $site;
    }

    /**
     * Get the user session associated with the current request.
     * @return Session
     */
    public static function getSession() {
        $session = Registry::get('session', true, null);

        if ($session === null) {
            $sessionManager = SessionManager::getManager();
            $session = $sessionManager->getUserSession();
        }

        return $session;
    }

    /**
     * Get the user associated with the current request.
     * @return User
     */
    public static function getUser() {
        $user = Registry::get('user', true, null);
        if ($user === null) {
            $sessionManager = SessionManager::getManager();
            $session = $sessionManager->getUserSession();
            $user = $session->getUser();
        }

        return $user;
    }

    /**
     * Get the value of a GET/POST variable.
     * @return mixed
     */
    public static function getUserVar($key) {
        $instance = self::_checkThis();

        // Get all vars (already cleaned)
        $vars = $instance->getUserVars();

        if (isset($vars[$key])) {
            return $vars[$key];
        } else {
            return null;
        }
    }

    /**
     * Get all GET/POST variables as an array
     * @return array
     */
    public static function getUserVars() {
        $instance = self::_checkThis();

        if (!isset($instance->_requestVars)) {
            $instance->_requestVars = array_merge($_GET, $_POST);
            $instance->cleanUserVar($instance->_requestVars);
        }

        return $instance->_requestVars;
    }

    /**
     * Get the value of a GET/POST variable generated using the Smarty
     * html_select_date and/or html_select_time function.
     * @return Date
     */
    public static function getUserDateVar($prefix, $defaultDay = null, $defaultMonth = null, $defaultYear = null, $defaultHour = 0, $defaultMinute = 0, $defaultSecond = 0) {
        // Ambil dari $_REQUEST agar tidak terpengaruh oleh state $instance
        $month  = $_REQUEST[$prefix . 'Month']  ?? $defaultMonth;
        $day    = $_REQUEST[$prefix . 'Day']    ?? $defaultDay;
        $year   = $_REQUEST[$prefix . 'Year']   ?? $defaultYear;
        $hour   = $_REQUEST[$prefix . 'Hour']   ?? $defaultHour;
        $minute = $_REQUEST[$prefix . 'Minute'] ?? $defaultMinute;
        $second = $_REQUEST[$prefix . 'Second'] ?? $defaultSecond;
    
        // Tambahkan log internal jika ingin memastikan
        // error_log("Wizdam Catch: $year-$month-$day");
    
        if (empty($month) || empty($day) || empty($year)) {
            return null;
        }
    
        // Penanganan Meridian
        $meridian = $_REQUEST[$prefix . 'Meridian'] ?? '';
        if ($meridian === 'pm' && (int)$hour != 12) $hour = (int)$hour + 12;
        if ($meridian === 'am' && (int)$hour == 12) $hour = 0;
    
        return mktime((int)$hour, (int)$minute, (int)$second, (int)$month, (int)$day, (int)$year);
    }

    /**
     * Sanitize a user-submitted variable (i.e., GET/POST/Cookie variable).
     * Strips slashes if necessary, then sanitizes variable as per Core::cleanVar().
     * @param $var mixed
     */
    public static function cleanUserVar(&$var) {
        if (isset($var) && is_array($var)) {
            foreach ($var as $key => $value) {
                self::cleanUserVar($var[$key]);
            }
        } else if (isset($var)) {
            // PHP 8 Modernization: Safe magic quotes check
            $magicQuotes = function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc();
            $var = Core::cleanVar($magicQuotes ? stripslashes($var) : $var);
        } else {
            return null;
        }
    }

    /**
     * Get the value of a cookie variable.
     * @return mixed
     */
    public static function getCookieVar($key) {
        $instance = self::_checkThis();

        if (isset($_COOKIE[$key])) {
            $value = $_COOKIE[$key];
            $instance->cleanUserVar($value);
            return $value;
        } else {
            return null;
        }
    }

    /**
     * Set a cookie variable.
     * @param $key string
     * @param $value mixed
     * @param $expire int (optional)
     */
    public static function setCookieVar($key, $value, $expire = 0) {
        $instance = self::_checkThis();

        $basePath = $instance->getBasePath();
        if (!$basePath) $basePath = '/';

        setcookie($key, $value, $expire, $basePath);
        $_COOKIE[$key] = $value;
    }

	/**
	 * Redirect to the specified page within a PKP Application.
	 * Shorthand for a common call to $request->redirect($dispatcher->url($request, ROUTE_PAGE, ...)).
	 * @param $context Array The optional contextual paths
	 * @param $page string The name of the op to redirect to.
	 * @param $op string optional The name of the op to redirect to.
	 * @param $path mixed string or array containing path info for redirect.
	 * @param $params array Map of name => value pairs for additional parameters
	 * @param $anchor string Name of desired anchor on the target page
	 */
    public static function redirect($context = null, $page = null, $op = null, $path = null, $params = null, $anchor = null) {
        $instance = self::_checkThis();
        $dispatcher = $instance->getDispatcher();
        $instance->redirectUrl($dispatcher->url($instance, ROUTE_PAGE, $context, $page, $op, $path, $params, $anchor));
    }

    /**
     * Deprecated
     * @see PKPPageRouter::getContext()
     */
    public static function getContext() {
        $instance = self::_checkThis();
        return $instance->_delegateToRouter('getContext');
    }

    /**
     * Deprecated
     * @see PKPPageRouter::getRequestedContextPath()
     */
    public static function getRequestedContextPath($contextLevel = null) {
        $instance = self::_checkThis();

        // Emulate the old behavior of getRequestedContextPath for
        // backwards compatibility.
        if (is_null($contextLevel)) {
            return $instance->_delegateToRouter('getRequestedContextPaths');
        } else {
            return array($instance->_delegateToRouter('getRequestedContextPath', $contextLevel));
        }
    }

    /**
     * Deprecated
     * @see PKPPageRouter::getRequestedPage()
     */
    public static function getRequestedPage() {
        $instance = self::_checkThis();
        return $instance->_delegateToRouter('getRequestedPage');
    }

    /**
     * Deprecated
     * @see PKPPageRouter::getRequestedOp()
     */
    public static function getRequestedOp() {
        $instance = self::_checkThis();
        return $instance->_delegateToRouter('getRequestedOp');
    }

    /**
     * Deprecated
     * @see PKPPageRouter::getRequestedArgs()
     */
    public static function getRequestedArgs() {
        $instance = self::_checkThis();
        return $instance->_delegateToRouter('getRequestedArgs');
    }

    /**
     * Deprecated
     * @see PKPPageRouter::url()
     */
    public static function url($context = null, $page = null, $op = null, $path = null,
            $params = null, $anchor = null, $escape = false) {
        $instance = self::_checkThis();
        return $instance->_delegateToRouter('url', $context, $page, $op, $path,
                $params, $anchor, $escape);
    }

    /**
     * This method exists to maintain backwards compatibility
     * with static calls to PKPRequest.
     * @return PKPRequest
     */
    public static function _checkThis() {
        $instance = Registry::get('request');
        if (is_null($instance)) {
            // [WIZDAM] Pertahankan log sebagai sinyal diagnostik
            // Alihkan dari fatalError() ke error_log() agar eksekusi berlanjut
            error_log('PKPRequest singleton not properly initialized.');
            
            // [WIZDAM] Self-initialize fallback instance agar method pemanggil
            // tidak menerima null dan crash dengan error yang tidak jelas
            $instance = self::_initializeFallbackInstance();
        }
        return $instance;
    }

    /**
     * [WIZDAM] Initialize a minimal PKPRequest fallback instance.
     * Digunakan ketika singleton belum terdaftar di Registry,
     * misalnya saat CLI, cron job, atau hook yang dipanggil terlalu awal.
     * @return PKPRequest
     */
    protected static function _initializeFallbackInstance() {
        $instance = new static();
    
        // Ambil base_url dari config sebagai fallback
        if (!isset($instance->_baseUrl)) {
            $instance->_baseUrl = Config::getVar('general', 'base_url');
        }
    
        // Protokol default ke https jika tidak ada HTTP context
        if (!isset($instance->_protocol)) {
            $instance->_protocol = 'https';
        }
    
        // Daftarkan ke Registry agar pemanggilan berikutnya tidak re-trigger log
        Registry::set('request', $instance);
    
        return $instance;
    }

    /**
     * This method exists to maintain backwards compatibility
     * with calls to methods that have been factored into the
     * Router implementations.
     * @return mixed depends on the called method
     */
    public function _delegateToRouter($method) {
        $router = $this->getRouter(); 

        if (is_null($router)) {
            assert(false);
            $nullValue = null;
            return $nullValue;
        }

        // Construct the method call
        $callable = array($router, $method);

        // Get additional parameters but replace
        // the first parameter (currently the
        // method to be called) with the request
        $parameters = func_get_args();
        $parameters[0] = $this;

        $returner = call_user_func_array($callable, $parameters);
        return $returner;
    }
    
    /**
     * Check whether a path component is valid (not an external URL or malicious path).
     * [WIZDAM SECURITY] Check whether a path component is valid.
     * Updated for PHP 8.1 strictness.
     * @param string|null $path Path component
     * @return boolean
     */
    public static function isPathValid($path) {
        // [PHP 8 FIX] Casting ke string sebelum trim untuk menangani null
        $path = trim((string) $path);
    
        // Path kosong dianggap valid (tidak melakukan redirect berbahaya)
        if (empty($path)) return true; 
    
        // Cek #1: Protocol handler (http://, ftp://, javascript:, data:, dll)
        if (preg_match('/^([a-zA-Z0-9\+\-\.]+):/i', $path)) {
            return false;
        }
    
        // Cek #2: Karakter berbahaya dan Directory Traversal
        // Menambahkan pengecekan null byte (\0) yang lebih ketat
        if (preg_match('/[\\\\\x00-\x1F\x7F]|\.\.\/?|\.\//', $path)) {
            return false;
        }
        
        return true;
    }
}

?>
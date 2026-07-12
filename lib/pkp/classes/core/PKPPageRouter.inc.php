<?php
declare(strict_types=1);

/**
 * @file classes/core/PKPPageRouter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPPageRouter
 * @ingroup core
 *
 * @brief Class mapping an HTTP request to a handler or context.
 * [WIZDAM EDITION] Smart Routing, Magic Methods, Secure Web Cache, Strict Fixed
 */

define('ROUTER_DEFAULT_PAGE', './pages/index/index.php');
define('ROUTER_DEFAULT_OP', 'index');

import('lib.pkp.classes.core.PKPRouter');

class PKPPageRouter extends PKPRouter {
    
    /** @var array pages that don't need an installed system to be displayed */
    public $_installationPages = ['install', 'help', 'header'];

    // Internal state cache variables
    /** @var string the requested page */
    protected $_page;

    /** @var string the requested operation */
    protected $_op;

    /** @var string index url */
    protected $_indexUrl;
    
    /** @var string cache filename */
    protected $_cacheFilename;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [DEPRECATED] SHIM Backward compatibility.
     * Use __construct() instead.
     * @deprecated
     */
    public function PKPPageRouter() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPPageRouter(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Get the installation pages
     * @return array
     */
    public function getInstallationPages() {
        return $this->_installationPages;
    }

    /**
     * Get the cacheable pages
     * @return array
     */
    public function getCacheablePages() {
        // Can be overridden by sub-classes.
        return [];
    }

    /**
     * Determine whether or not the request is cacheable.
     * @param PKPRequest $request
     * @param bool $testOnly required for unit test
     * @return bool
     */
    public function isCacheable($request, $testOnly = false) {
        if (defined('SESSION_DISABLE_INIT') && !$testOnly) return false;
        if (!Config::getVar('general', 'installed')) return false;
        
        // [WIZDAM FIX] Check $_POST strictness
        if (!empty($_POST) || Validation::isLoggedIn()) return false;

        if ($request->isPathInfoEnabled()) {
            if (!empty($_GET)) return false;
        } else {
            $application = $this->getApplication();
            $ok = array_merge($application->getContextList(), ['page', 'op', 'path']);
            // [WIZDAM FIX] PHP 8 safe array comparison
            if (!empty($_GET) && count(array_diff(array_keys($_GET), $ok)) != 0) {
                return false;
            }
        }

        if (in_array($this->getRequestedPage($request), $this->getCacheablePages())) return true;

        return false;
    }

    /**
     * Get the page requested in the URL.
     * @param PKPRequest $request the request to be routed
     * @return string the page path
     */
    public function getRequestedPage($request) {
        if (!isset($this->_page)) {
            $this->_page = $this->_getRequestedUrlParts(['Core', 'getPage'], $request);
        }
        return $this->_page;
    }

    /**
     * Get the operation requested in the URL.
     * @param PKPRequest $request
     * @return string
     */
    public function getRequestedOp($request) {
        if (!isset($this->_op)) {
            $this->_op = $this->_getRequestedUrlParts(['Core', 'getOp'], $request);
        }
        return $this->_op;
    }

    /**
     * Get the arguments requested in the URL.
     * @param PKPRequest $request
     * @return array
     */
    public function getRequestedArgs($request) {
        return $this->_getRequestedUrlParts(['Core', 'getArgs'], $request);
    }

    //
    // Implement template methods from PKPRouter
    //
    
    /**
     * Get the filename to use for caching the current request.
     * @see PKPRouter::getCacheFilename()
     * [WIZDAM NOTE] Ini adalah "Jembatan" antara Router dan Dispatcher.
     * Dispatcher menggunakan nama file yang dihasilkan di sini untuk menyimpan/membaca cache.
     * Kita tetap menggunakan .html agar readfile() bisa langsung menyajikannya ke browser.
     * @param PKPRequest $request
     * @return string the cache filename
     */
    public function getCacheFilename($request) {
        if (!isset($this->_cacheFilename)) {
            if ($request->isPathInfoEnabled()) {
                $id = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : 'index';
                $id .= '-' . AppLocale::getLocale();
            } else {
                $id = '';
                $application = $this->getApplication();
                foreach($application->getContextList() as $contextName) {
                    $val = $request->getUserVar($contextName);
                    $id .= (is_scalar($val) ? $val : '') . '-'; // [WIZDAM] PHP 8 Scalar check
                }
                
                // [WIZDAM FIX] Strict scalar checks for concatenation
                $p = $request->getUserVar('page');
                $o = $request->getUserVar('op');
                $pa = $request->getUserVar('path');
                
                $id .= (is_scalar($p)?$p:'') . '-' . (is_scalar($o)?$o:'') . '-' . (is_scalar($pa)?$pa:'') . '-' . AppLocale::getLocale();
            }
            $path = dirname(INDEX_FILE_LOCATION);
            
            // Nama file cache di-hash agar seragam dan aman dari karakter aneh
            $this->_cacheFilename = $path . '/cache/wc-' . md5($id) . '.html';
        }
        return $this->_cacheFilename;
    }

    /**
     * Route the request to the appropriate handler.
     * @see PKPRouter::route()
     * @param PKPRequest $request the request to be routed
     */
    public function route($request) {
        // Determine the requested page and operation
        $page = $this->getRequestedPage($request);
        $op = $this->getRequestedOp($request);

        // Installation Check
        if (!Config::getVar('general', 'installed')) {
            define('SESSION_DISABLE_INIT', 1);
            if (!in_array($page, $this->getInstallationPages())) {
                $redirectMethod = [$request, 'redirect'];
                $application = $this->getApplication();
                $contextDepth = $application->getContextDepth();
                $redirectArguments = array_pad(['install'], - $contextDepth - 1, null);
                call_user_func_array($redirectMethod, $redirectArguments);
            }
        }

        // Determine the page index file.
        $sourceFile = sprintf('pages/%s/index.php', $page);

        // [WIZDAM] Protocol 3 Exception: HOOKS
        // Hook 'LoadHandler' requires parameters by reference because plugins 
        // MAY modify which page/handler serves the request. 
        // We keep '&' here strictly for this reason.
        if (!HookRegistry::dispatch('LoadHandler', [&$page, &$op, &$sourceFile])) {
            if (file_exists($sourceFile)) require('./'.$sourceFile);
            elseif (file_exists('lib/pkp/'.$sourceFile)) require('./lib/pkp/'.$sourceFile);
            elseif (empty($page)) require(ROUTER_DEFAULT_PAGE);
            else {
                $this->getDispatcher()->handle404($request);
                return;
            }
        }

        if (!defined('SESSION_DISABLE_INIT')) {
            $sessionManager = SessionManager::getManager();
        }

        if (empty($op)) $op = ROUTER_DEFAULT_OP;

        // ----- [WIZDAM] STRICT CAMELCASE ROUTING (OPTIMIZED + GUARDED) -----
        if (strpos($op, '-') !== false && defined('HANDLER_CLASS')) {
            // Guard: buang karakter selain huruf kecil, angka, dan tanda hubung
            $op = preg_replace('/[^a-z0-9-]/', '', strtolower($op));
            
            // Transformasi: 'editorial-team-bio' -> 'editorialTeamBio'
            $callableOp = lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $op))));
            
            if (method_exists(HANDLER_CLASS, $callableOp)) {
                $op = $callableOp;
            }
        }
        // ------------------------------------------------
        
        // 404 Check
        $methods = [];
        if (defined('HANDLER_CLASS')) {
            $classMethods = get_class_methods(HANDLER_CLASS);
            if (is_array($classMethods)) {
                $methods = array_map('strtolower_codesafe', $classMethods);
            }
        }
        
        // [WIZDAM FIX] Magic Method Bypass
        if (!in_array(strtolower_codesafe($op), $methods)) {
            $hasMagic = false;
            if (defined('HANDLER_CLASS') && class_exists(HANDLER_CLASS)) {
                if (method_exists(HANDLER_CLASS, '__call')) {
                    $hasMagic = true;
                }
            }

            if (!$hasMagic) {
                $this->getDispatcher()->handle404($request);
                return;
            }
        }

        // Instantiate the handler class
        $HandlerClass = HANDLER_CLASS;
        $handler = new $HandlerClass();

        // Authorize and initialize the request
        $args = $this->getRequestedArgs($request);
        $serviceEndpoint = [$handler, $op];
        
        // Logic caching dihapus dari sini, panggil fungsi bersih.
        $this->_authorizeInitializeAndCallRequest($serviceEndpoint, $request, $args, false);
    }

    /**
     * Generate a URL.
     * @see PKPRouter::url()
     * @param PKPRequest $request
     * @param string|null $newContext optional new context to use in the URL
     * @param string|null $page optional page to use in the URL
     * @param string|null $op optional operation to use in the URL
     * @param string|array|null $path additional path info to use in the URL
     * @param array|null $params additional query parameters to use in the URL
     * @param string|null $anchor optional anchor to use in the URL
     * @param bool $escape whether to escape the URL
     * @return string
     */
    public function url($request, $newContext = null, $page = null, $op = null, $path = null, $params = null, $anchor = null, $escape = false) {
        $pathInfoEnabled = $request->isPathInfoEnabled();

        // Base URL and Context
        $newContext = $this->_urlCanonicalizeNewContext($newContext);
        $baseUrlAndContext = $this->_urlGetBaseAndContext($request, $newContext);
        $baseUrl = array_shift($baseUrlAndContext);
        $context = $baseUrlAndContext;

        // Additional path info
        if (empty($path)) {
            $additionalPath = [];
        } else {
            // [CRITICAL FIX] Strict type casting for rawurlencode
            if (is_array($path)) {
                // Ensure every element is a string before encoding
                $additionalPath = array_map(function($item) {
                    return rawurlencode((string)$item);
                }, $path);
            } else {
                // Ensure single element is a string
                $additionalPath = [rawurlencode((string)$path)];
            }

            if (!$pathInfoEnabled) {
                $pathKey = $escape ? 'path%5B%5D=' : 'path[]=';
                foreach($additionalPath as $key => $pathElement) {
                    $additionalPath[$key] = $pathKey . $pathElement;
                }
            }
        }

        // Page and Operation
        // [WIZDAM FIX] Replaced is_a() with instanceof
        $currentRequestIsAPageRequest = ($request->getRouter() instanceof PKPPageRouter);

        // Determine the operation
        if ($op) {
            // [CRITICAL FIX] Cast to string for safety
            $op = rawurlencode((string)$op);
        } else {
            if (empty($newContext) && empty($page) && $currentRequestIsAPageRequest) {
                $op = $this->getRequestedOp($request);
            } else {
                if (empty($additionalPath)) {
                    $op = null;
                } else {
                    $op = 'index';
                }
            }
        }

        // Determine the page
        if ($page) {
            $page = rawurlencode(is_scalar($page) ? (string)$page : '');
        } else {
            if (empty($newContext) && $currentRequestIsAPageRequest) {
                $page = $this->getRequestedPage($request);
            } else {
                if (empty($op)) {
                    $page = null;
                } else {
                    // [WIZDAM] Jangan sertakan 'index' sebagai page di path URL
                    // Router tetap me-resolve ini ke pages/index/ saat dispatch
                    // $page = 'index';  // Kode lama
                    $page = null;
                }
            }
        }

        // Additional query parameters
        $additionalParameters = $this->_urlGetAdditionalParameters($request, $params, $escape);

        // Anchor
        $anchor = (empty($anchor) ? '' : '#' . rawurlencode((string)$anchor));

        // Assemble URL
        if ($pathInfoEnabled) {
            $pathInfoArray = $context;
            if (!empty($page)) {
                $pathInfoArray[] = $page;
                if (!empty($op)) {
                    $pathInfoArray[] = $op;
                }
            }
            $pathInfoArray = array_merge($pathInfoArray, $additionalPath);
            $queryParametersArray = $additionalParameters;
        } else {
            $pathInfoArray = [];
            $queryParametersArray = $context;
            if (!empty($page)) {
                $queryParametersArray[] = "page=$page";
                if (!empty($op)) {
                    $queryParametersArray[] = "op=$op";
                }
            }
            $queryParametersArray = array_merge($queryParametersArray, $additionalPath, $additionalParameters);
        }

        return $this->_urlFromParts($baseUrl, $pathInfoArray, $queryParametersArray, $anchor, $escape);
    }

    /**
     * Handle an authorization failure.
     * @see PKPRouter::handleAuthorizationFailure()
     * @param PKPRequest $request
     * @param string $authorizationMessage
     * @return string the response to be sent to the client
     */
    public function handleAuthorizationFailure($request, $authorizationMessage) {
        if (!$request->getUser()) Validation::redirectLogin();
        $request->redirect(null, 'user', 'authorizationDenied', null, ['message' => $authorizationMessage]);
    }

    //
    // Private helper methods.
    //
    
    /**
     * Retrieve part of the current requested url
     * @param callable $callback the callback to retrieve the url part
     * @param PKPRequest $request the request to be routed
     * @return mixed the result of the callback
     */
    public function _getRequestedUrlParts($callback, $request) {
        $url = null;
        // [WIZDAM FIX] instanceof check
        assert($request->getRouter() instanceof PKPPageRouter);
        $isPathInfoEnabled = $request->isPathInfoEnabled();

        if ($isPathInfoEnabled) {
            if (isset($_SERVER['PATH_INFO'])) {
                $url = $_SERVER['PATH_INFO'];
            }
        } else {
            $url = $request->getCompleteUrl();
        }

        $userVars = $request->getUserVars();
        return call_user_func_array($callback, [$url, $isPathInfoEnabled, $userVars]);
    }
    
    /**
     * Authorize, initialize, and call the request handler.
     * @see PKPRouter::_authorizeInitializeAndCallRequest()
     * @param callable $serviceEndpoint the handler and operation to call
     * @param PKPRequest $request the request to be routed
     * @param array $args the arguments to pass to the handler
     * @param bool $validate whether to call the handler's validate() method before execution
     */
    public function _authorizeInitializeAndCallRequest($serviceEndpoint, $request, $args, $validate = true) {
        assert(is_callable($serviceEndpoint));

        $handler = $serviceEndpoint[0];
        $op = $serviceEndpoint[1];

        $handler->setDispatcher($this->getDispatcher());

        $roleAssignments = $handler->getRoleAssignments();
        assert(is_array($roleAssignments));

        if ($handler->authorize($request, $args, $roleAssignments)) {
            if ($validate) $handler->validate($request, $args);
            $handler->initialize($request, $args);

            $methodExists = method_exists($handler, $op);
            $hasMagic = method_exists($handler, '__call');

            if (!$methodExists && !$hasMagic) {
                $this->getDispatcher()->handle404($request);
                return;
            }

            // Reflection untuk Parameter Injection (Request Object)
            $wantsRequest = false;
            if ($methodExists) {
                $reflection = new ReflectionMethod($handler, $op);
                $params = $reflection->getParameters();
                if (isset($params[1]) && $params[1]->getName() == 'request') {
                    $wantsRequest = true;
                }
            }

            // Eksekusi Handler Murni
            if ($wantsRequest) {
                $result = call_user_func([$handler, $op], $args, $request);
            } else {
                $result = call_user_func([$handler, $op], $args);
            }
            
            // Catatan: Return value handler biasanya string HTML atau null (jika langsung echo/display)
            // Hasil ini akan ditangkap oleh ob_start() di Dispatcher.inc.php jika Caching aktif.

        } else {
            $authorizationMessage = $handler->getLastAuthorizationMessage();
            if ($authorizationMessage == '') $authorizationMessage = 'user.authorization.accessDenied';
            $result = $this->handleAuthorizationFailure($request, $authorizationMessage);
        }

        if (is_string($result)) echo $result;
    }
}
?>
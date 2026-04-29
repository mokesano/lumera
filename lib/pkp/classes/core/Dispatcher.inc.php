<?php
declare(strict_types=1);

/**
 * @file classes/core/Dispatcher.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Dispatcher
 * @ingroup core
 *
 * @brief Class dispatching HTTP requests to handlers.
 * [WIZDAM EDITION] Modernized. PHP 8 Safe. Intelligent Caching. Strict Typed. Pure DI.
 */

class Dispatcher {
    
    /** @var PKPApplication */
    protected PKPApplication $_application;

    /** @var array an array of Router implementation class names */
    protected array $_routerNames = [];

    /** @var array an array of Router instances */
    protected array $_routerInstances = [];

    /** @var PKPRouter|null */
    protected ?PKPRouter $_router = null;

    /** @var PKPRequest|null Used for a callback hack */
    protected ?PKPRequest $_requestCallbackHack = null;

    // [WIZDAM CONSTANT] - Default cache TTL fallback
    const WIZDAM_CACHE_TTL_DEFAULT = 3600;

    /**
     * Get the application
     * @return PKPApplication
     */
    public function getApplication(): PKPApplication {
        return $this->_application;
    }

    /**
     * Set the application
     * @param PKPApplication $application
     */
    public function setApplication(PKPApplication $application): void {
        $this->_application = $application;
    }

    /**
     * Get the router names
     * @return array
     */
    public function getRouterNames(): array {
        return $this->_routerNames;
    }

    /**
     * Add a router name.
     * @param string $routerName
     * @param string $shortcut
     */
    public function addRouterName(string $routerName, string $shortcut): void {
        $this->_routerNames[$shortcut] = $routerName;
    }

    /**
     * Determine the correct router for this request.
     * @param PKPRequest $request
     */
    public function dispatch(PKPRequest $request): void {
        
        // [WIZDAM] NEW Routing url tanpa /index/ di level root/publisher
        if ($request->isPathInfoEnabled()) {
        
            // Ambil pathInfo dengan fallback ke REQUEST_URI
            $pathInfo = $_SERVER['PATH_INFO'] ?? '';
            if (empty($pathInfo) && isset($_SERVER['REQUEST_URI'])) {
                $uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $basePath = $request->getBasePath();
                $pathInfo = ($basePath && strpos($uri, $basePath) === 0)
                            ? substr($uri, strlen($basePath))
                            : $uri;
            }
        
            // BLOK 1: Redirect /index/... → /...
            if (preg_match('#^/index(/.*)?$#', $pathInfo, $matches)) {
                $cleanPath   = $matches[1] ?? '/';
                $baseUrl     = $request->getBaseUrl();
                $qs          = $request->getQueryString();
                $redirectUrl = $baseUrl . $cleanPath;
                if (!empty($qs)) $redirectUrl .= '?' . $qs;
        
                header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $redirectUrl);
                exit();
            }
        
            // BLOK 2: Internal rewrite /page/op → /index/page/op
            $segments     = explode('/', trim($pathInfo, '/'));
            $firstSegment = $segments[0] ?? '';
        
            $isKnownJournal = false;
            if (!empty($firstSegment) && $firstSegment !== 'index') {
                // [WIZDAM FIX] Hanya query ke DB jika aplikasi SUDAH terinstal
                if (\Config::getVar('general', 'installed')) {
                    $journalDao     = DAORegistry::getDAO('JournalDAO');
                    $journalByPath  = $journalDao->getJournalByPath($firstSegment);
                    $isKnownJournal = ($journalByPath !== null);
                }
            }
        
            if (!$isKnownJournal && $firstSegment !== 'index' && !empty($firstSegment)) {
                $rewrittenPath = '/index' . $pathInfo;
        
                // [WIZDAM FIX] Set keduanya agar router tidak miss
                $_SERVER['PATH_INFO']   = $rewrittenPath;
                $_SERVER['REQUEST_URI'] = $request->getBasePath() . $rewrittenPath;
            }
        }
        // [WIZDAM] END routing url tanpa /index/ level root/publisher
    
        $routerNames = $this->getRouterNames();
        if (count($routerNames) === 0) {
            fatalError('Dispatcher: No routers configured.');
        }

        $router = null; 

        foreach($routerNames as $shortcut => $routerCandidateName) {
            $routerCandidate = $this->_instantiateRouter($routerCandidateName, $shortcut);

            if ($routerCandidate->supports($request)) {
                $request->setRouter($routerCandidate);
                $request->setDispatcher($this);

                $router = $routerCandidate;
                $this->_router = $router;
                break;
            }
        }

        if (is_null($router)) {
            // [WIZDAM DI] Pass request explicitly instead of relying on global state
            $this->handle404($request);
            return; // handle404 calls fatalError/exit, but return for safety
        }

        // Can we serve a cached response?
        if ($router->isCacheable($request)) {
            $this->_requestCallbackHack = $request;
            if (Config::getVar('cache', 'web_cache')) {
                if ($this->_displayCached($router, $request)) exit(); 
                
                ob_start([$this, '_cacheContent']);
            }
        } else {
            // Anti-prefetching logic
            if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch') {
                header('HTTP/1.0 403 Forbidden');
                echo '403: Forbidden<br><br>Pre-fetching not allowed.';
                exit;
            }
        }

        AppLocale::initialize();
        PluginRegistry::loadCategory('generic', true);

        $router->route($request);
    }

    /**
     * Build a handler request URL into PKPApplication.
     * @return string the URL
     */
    public function url(PKPRequest $request, string $shortcut, $newContext = null, $handler = null, $op = null, $path = null,
            $params = null, $anchor = null, $escape = false): string {
        
        if (!isset($this->_routerNames[$shortcut])) {
            fatalError("Dispatcher: Invalid router shortcut '$shortcut'.");
        }
        
        $routerName = $this->_routerNames[$shortcut];
        $router = $this->_instantiateRouter($routerName, $shortcut);

        return $router->url($request, $newContext, $handler, $op, $path, $params, $anchor, $escape);
    }

    //
    // Private helper methods
    //

    /**
     * Instantiate a router
     * @return PKPRouter
     */
    protected function _instantiateRouter(string $routerName, string $shortcut): PKPRouter {
        if (!isset($this->_routerInstances[$shortcut])) {
            $allowedRouterPackages = ['classes.core', 'lib.pkp.classes.core'];

            $router = instantiate($routerName, 'PKPRouter', $allowedRouterPackages);
            if (!($router instanceof PKPRouter)) {
                fatalError('Cannot instantiate requested router. Routers must belong to the core package and be of type "PKPRouter".');
            }
            $router->setApplication($this->_application);
            $router->setDispatcher($this);

            $this->_routerInstances[$shortcut] = $router;
        }

        return $this->_routerInstances[$shortcut];
    }

    /**
     * [WIZDAM INTELLIGENT CACHE]
     * Menggunakan ETag (Hash) dan Cache-Control: must-revalidate.
     * @param PKPRouter $router
     * @param PKPRequest $request
     */
    protected function _displayCached(PKPRouter $router, PKPRequest $request): bool {
        $filename = $router->getCacheFilename($request);
        if (!file_exists($filename)) return false;

        // 1. Cek Server-Side Expiry
        $web_cache_hours = Config::getVar('cache', 'web_cache_hours');
        $lifetime = ($web_cache_hours ? $web_cache_hours : 1) * 3600;
        
        if (filemtime($filename) < (time() - $lifetime)) {
            return false;
        }

        // 2. [CORE UPGRADE] ETag / Hash Verification
        $etag = sprintf('"%s"', md5_file($filename)); 

        header('Cache-Control: public, max-age=' . $lifetime . ', must-revalidate');
        header('ETag: ' . $etag);

        // 3. Cek Header dari Browser (If-None-Match)
        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
            header('HTTP/1.1 304 Not Modified');
            exit();
        }

        // 4. Sajikan file
        header('Content-Type: text/html; charset=' . Config::getVar('i18n', 'client_charset'));
        header('Content-Length: ' . filesize($filename));
        
        // Debugging Stats
        if (Config::getVar('debug', 'show_stats')) {
             echo "\n\n";
        }
        
        readfile($filename);
        return true;
    }

    /**
     * Cache content as a local file.
     * [WIZDAM FIX] Saves Pure HTML ensuring Atomic Writes.
     * @param string $contents
     * @return string
     */
    public function _cacheContent(string $contents): string {
        if ($contents === '') return $contents; 
        
        $filename = $this->_router->getCacheFilename($this->_requestCallbackHack);
        
        // [WIZDAM SANITIZER] Cleanup timestamp garbage from legacy outputs
        if (preg_match('/^\d{10}:/', $contents)) {
            $contents = preg_replace('/^\d{10}:/', '', $contents);
        }
        $contents = ltrim($contents); 

        // Cek permission folder
        $dir = dirname($filename);
        if (!is_dir($dir) || !is_writable($dir)) {
            error_log("Wizdam Cache Error: Cannot write to $dir");
            return $contents;
        }

        // [WIZDAM ATOMIC WRITE]
        file_put_contents($filename, $contents, LOCK_EX);
        
        return $contents;
    }

    /**
     * Handle a 404 error (page not found).
     * WIZDAM EDITION: Custom Error Handling
     * [HIGHER STANDARD] Explicit Method Injection. No Service Locator used.
     * @param PKPRequest $request The request object must be passed explicitly.
     */
    public function handle404(PKPRequest $request): void {
        // 1. Mengumpulkan informasi URL
        $path = $request->getRequestPath();
        $queryString = $request->getQueryString();
        $fullUrl = $path . ($queryString ? '?' . $queryString : '');
        
        // [WIZDAM SECURITY] Sanitasi mencegah Log Injection.
        $sanitize = function(string $value, int $maxLen): string {
            $value = strip_tags($value);
            $value = str_replace(["\r\n", "\r", "\n"], ' ', $value);
            $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);
            return substr($value, 0, $maxLen);
        };
        
        // 2. Mengumpulkan informasi server
        $ip        = $sanitize((string) ($_SERVER['REMOTE_ADDR']     ?? 'UNKNOWN IP'),   45);
        $referer   = $sanitize((string) ($_SERVER['HTTP_REFERER']    ?? 'No Referer'),  512);
        $userAgent = $sanitize((string) ($_SERVER['HTTP_USER_AGENT'] ?? 'No User Agent'), 512);
        $fullUrl   = $sanitize($fullUrl, 1024);
        
        // 3. Membuat pesan error multi-baris yang terstruktur
        $errorMessage = '404 Not Found' . PHP_EOL . 
                        '  URL: '        . $fullUrl  . PHP_EOL .
                        '  IP: '         . $ip       . PHP_EOL .
                        '  Referer: '    . $referer  . PHP_EOL .
                        '  User-Agent: ' . $userAgent;
        
        // 4. Mencatat error
        header('HTTP/1.0 404 Not Found');
        fatalError($errorMessage, 404);
    }
}
?>
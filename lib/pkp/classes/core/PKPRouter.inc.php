<?php
declare(strict_types=1);

/**
 * @file classes/core/PKPRouter.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPRouter
 * @see PKPPageRouter
 * @see PKPComponentRouter
 * @ingroup core
 *
 * @brief Basic router class that has functionality common to all routers.
 */

class PKPRouter {
    //
    // Internal state cache variables
    // NB: Please do not access directly but
    // only via their respective getters/setters
    //
    /** @var PKPApplication|null */
    protected $_application;
    
    /** @var Dispatcher|null */
    protected $_dispatcher;
    
    /** @var int|null context depth */
    protected $_contextDepth;
    
    /** @var array|null context list */
    protected $_contextList;
    
    /** @var array context list with keys and values flipped */
    protected $_flippedContextList;
    
    /** @var array context paths */
    protected $_contextPaths = [];
    
    /** @var array contexts */
    protected $_contexts = [];

    /** @var string|null index URL cache */
    protected $_indexUrl;

    /**
     * Constructor.
     */
    public function __construct() {
        // Init default values if needed
    }

    /**
     * Get the application
     * @return PKPApplication|null
     */
    public function getApplication() {
        return $this->_application;
    }

    /**
     * Set the application
     * @param mixed $application PKPApplication
     */
    public function setApplication($application) {
        $this->_application = $application;

        // Retrieve context depth and list
        if ($application) {
            $this->_contextDepth = $application->getContextDepth();
            $this->_contextList = $application->getContextList();
            $this->_flippedContextList = array_flip($this->_contextList);
        }
    }

    /**
     * Get the dispatcher
     * @return Dispatcher|null
     */
    public function getDispatcher() {
        return $this->_dispatcher;
    }

    /**
     * Set the dispatcher
     * @param mixed $dispatcher Dispatcher
     */
    public function setDispatcher($dispatcher) {
        $this->_dispatcher = $dispatcher;
    }

    /**
     * Determines whether this router can route the given request.
     * @param mixed $request PKPRequest
     * @return bool true
     */
    public function supports($request) {
        // Default implementation returns always true
        return true;
    }

    /**
     * Determine whether or not this request is cacheable
     * @param mixed $request PKPRequest
     * @return bool
     */
    public function isCacheable($request) {
        // Default implementation returns always false
        return false;
    }

    /**
     * A generic method to return an array of context paths (e.g. a Press or a Conference/SchedConf paths)
     * @param mixed $request PKPRequest
     * @return array
     */
    public function getRequestedContextPaths($request) {
        // Handle context depth 0
        if (!$this->_contextDepth) {
            return [];
        }

        $isPathInfoEnabled = $request->isPathInfoEnabled();
        $userVars = [];
        $url = null;

        // Determine the context path
        if (empty($this->_contextPaths)) {
            if ($isPathInfoEnabled) {
                // Retrieve url from the path info
                if (isset($_SERVER['PATH_INFO'])) {
                    $url = $_SERVER['PATH_INFO'];
                }
            } else {
                $url = $request->getCompleteUrl();
                $userVars = $request->getUserVars();
            }

            $this->_contextPaths = Core::getContextPaths(
                $url, 
                $isPathInfoEnabled,
                $this->_contextList, 
                $this->_contextDepth, 
                $userVars
            );

            HookRegistry::dispatch('Router::getRequestedContextPaths', [&$this->_contextPaths]);
        }

        return $this->_contextPaths;
    }

    /**
     * A generic method to return a single context path (e.g. a Press or a SchedConf path)
     * @param mixed $request PKPRequest
     * @param int $requestedContextLevel
     * @return string|null
     */
    public function getRequestedContextPath($request, $requestedContextLevel = 1) {
        // Handle context depth 0
        if (!$this->_contextDepth) {
            return null;
        }

        // Return the full context, then retrieve the requested context path
        $contextPaths = $this->getRequestedContextPaths($request);
        $index = $requestedContextLevel - 1;
        
        return $contextPaths[$index] ?? null;
    }

    /**
     * A Generic call to a context defining object (e.g. Conference or Press)
     * @param mixed $request PKPRequest
     * @param int $requestedContextLevel
     * @return object|null
     */
    public function getContext($request, $requestedContextLevel = 1) {
        // Handle context depth 0
        if (!$this->_contextDepth) {
            return null;
        }

        if (!isset($this->_contexts[$requestedContextLevel])) {
            // Retrieve the requested context path (this validates the context level and the path)
            $path = $this->getRequestedContextPath($request, $requestedContextLevel);
            
            if ($path === 'index' || $path === null) {
                $this->_contexts[$requestedContextLevel] = null;
            } else {
                // Get the context name (this validates the context name)
                $requestedContextName = $this->_contextLevelToContextName($requestedContextLevel);
                $contextClass = ucfirst($requestedContextName);
                $daoName = $contextClass . 'DAO';
                $daoInstance = DAORegistry::getDAO($daoName);
                $daoMethod = 'get' . $contextClass . 'ByPath';
                
                if ($daoInstance && method_exists($daoInstance, $daoMethod)) {
                    $this->_contexts[$requestedContextLevel] = $daoInstance->$daoMethod($path);
                } else {
                    $this->_contexts[$requestedContextLevel] = null;
                }
            }
        }

        return $this->_contexts[$requestedContextLevel];
    }

    /**
     * Get the object that represents the desired context (e.g. Conference or Press)
     * @param mixed $request PKPRequest
     * @param string $requestedContextName
     * @return object|null
     */
    public function getContextByName($request, $requestedContextName) {
        // Handle context depth 0
        if (!$this->_contextDepth) {
            return null;
        }

        $requestedContextLevel = $this->_contextNameToContextLevel($requestedContextName);
        return $this->getContext($request, $requestedContextLevel);
    }

    /**
     * Get the URL to the index script.
     * @param mixed $request PKPRequest the request to be routed
     * @return string
     */
    public function getIndexUrl($request) {
        if ($this->_indexUrl === null) {
            if ($request->isRestfulUrlsEnabled()) {
                $this->_indexUrl = $request->getBaseUrl();
            } else {
                $this->_indexUrl = $request->getBaseUrl() . '/index.php';
            }
            HookRegistry::dispatch('Router::getIndexUrl', [&$this->_indexUrl]);
        }

        return $this->_indexUrl;
    }


    //
    // Protected template methods to be implemented by sub-classes.
    //
    /**
     * Determine the filename to use for a local cache file.
     * @param mixed $request PKPRequest
     * @return string
     */
    public function getCacheFilename($request) {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Routes a given request to a handler operation
     * @param mixed $request PKPRequest
     */
    public function route($request) {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Build a handler request URL into PKPApplication.
     * @param mixed $request PKPRequest
     * @param mixed $newContext mixed 
     * @param string $handler 
     * @param string $op 
     * @param mixed $path 
     * @param array $params 
     * @param string $anchor 
     * @param bool $escape 
     * @return string the URL
     */
    public function url($request, $newContext = null, $handler = null, $op = null, $path = null,
                $params = null, $anchor = null, $escape = false) {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }

    /**
     * Handle an authorization failure.
     * @param mixed $request Request
     * @param string $authorizationMessage
     * @return mixed
     */
    public function handleAuthorizationFailure($request, $authorizationMessage) {
        throw new \BadMethodCallException('Must be implemented by sub-classes.');
    }


    //
    // Private helper methods
    //
    /**
     * This is the method that implements the basic
     * life-cycle of a handler request:
     * 1) authorization
     * 2) validation
     * 3) initialization
     * 4) execution
     * 5) client response
     *
     * @param mixed $serviceEndpoint callable the handler operation
     * @param mixed $request PKPRequest
     * @param mixed $args array
     * @param bool $validate
     */
    public function _authorizeInitializeAndCallRequest($serviceEndpoint, $request, $args, $validate = true) {
        // Pass the dispatcher to the handler.
        $serviceEndpoint[0]->setDispatcher($this->getDispatcher());

        // Authorize the request.
        $roleAssignments = $serviceEndpoint[0]->getRoleAssignments();
        
        if ($serviceEndpoint[0]->authorize($request, $args, $roleAssignments)) {
            // Execute class-wide data integrity checks.
            if ($validate) {
                $serviceEndpoint[0]->validate($request, $args);
            }
            $serviceEndpoint[0]->initialize($request, $args);
            $result = call_user_func($serviceEndpoint, $args, $request);
        } else {
            $authorizationMessage = $serviceEndpoint[0]->getLastAuthorizationMessage();
            if ($authorizationMessage === '') {
                $authorizationMessage = 'user.authorization.accessDenied';
            }

            $result = $this->handleAuthorizationFailure($request, $authorizationMessage);
        }

        if (is_string($result)) {
            echo $result;
        }
    }

    /**
     * Canonicalizes the new context.
     * @param mixed $newContext the raw context array
     * @return array the canonicalized context array
     */
    public function _urlCanonicalizeNewContext($newContext) {
        // Create an empty array in case no new context was given.
        $newContext = $newContext ?? [];

        // If we got the new context as a scalar then transform it into an array.
        if (is_scalar($newContext)) {
            $newContext = [$newContext];
        }

        // Check whether any new context has been provided.
        $newContextProvided = false;
        foreach ($newContext as $contextElement) {
            if (isset($contextElement)) {
                $newContextProvided = true;
                break;
            }
        }
        
        if (!$newContextProvided) {
            $newContext = [];
        }

        return $newContext;
    }

    /**
     * Build the base URL and add the context part of the URL.
     * @param mixed $request PKPRequest the request to be routed
     * @param array $newContext the new context to be added to the URL
     * @return array
     */
    public function _urlGetBaseAndContext($request, $newContext = []) {
        $pathInfoEnabled = $request->isPathInfoEnabled();
        $contextList = $this->_contextList;

        // Determine URL context
        $context = [];
        $overriddenBaseUrl = null;

        foreach ($contextList as $contextKey => $contextName) {
            $contextParameter = $pathInfoEnabled ? '' : $contextName . '=';

            $newContextValue = array_shift($newContext);
            if ($newContextValue !== null) {
                // A new context has been set so use it.
                $contextValue = rawurlencode((string) $newContextValue);
            } else {
                // No new context has been set so determine the current request's context
                $contextObject = $this->getContextByName($request, $contextName);
                $contextValue = $contextObject ? $contextObject->getPath() : 'index';
            }

            // Check whether the base URL is overridden.
            if ($contextKey === 0) {
                $overriddenBaseUrl = Config::getVar('general', "base_url[$contextValue]");
            }

            // [WIZDAM] Jika context adalah 'index' (site-level) dan pathInfo aktif,
            // JANGAN sertakan dalam URL. Sistem tetap mengetahui contextnya adalah 'index'
            // melalui Core::getContextPaths() saat parsing request masuk.
            if ($pathInfoEnabled && $contextValue === 'index') {
                // Lewati — tidak perlu ditampilkan dalam URL
            } else {
                $context[] = $contextParameter . $contextValue;
            }
        }

        // Generate the base url
        if (!empty($overriddenBaseUrl)) {
            $baseUrl = $overriddenBaseUrl;
            // Throw the overridden context away
            array_shift($context);
        } else {
            $baseUrl = $this->getIndexUrl($request);
        }

        // Join base URL and context and return the result
        return array_merge([$baseUrl], $context);
    }

    /**
     * Build the additional parameters part of the URL.
     * @param mixed $request PKPRequest
     * @param array|null $params
     * @param bool $escape
     * @return array
     */
    public function _urlGetAdditionalParameters($request, $params = null, $escape = true) {
        $additionalParameters = [];
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $element) {
                        $additionalParameters[] = $key . ($escape ? '%5B%5D=' : '[]=') . rawurlencode((string) $element);
                    }
                } else {
                    $additionalParameters[] = $key . '=' . rawurlencode((string) $value);
                }
            }
        }

        return $additionalParameters;
    }

    /**
     * Creates a valid URL from parts.
     * @param string $baseUrl
     * @param array $pathInfoArray
     * @param array $queryParametersArray
     * @param string $anchor
     * @param bool $escape
     * @return string
     */
    public function _urlFromParts($baseUrl, $pathInfoArray = [], $queryParametersArray = [], $anchor = '', $escape = false) {
        // Parse the base url
        $baseUrlParts = parse_url($baseUrl);
        
        // [LUMERA] Safety check for parse_url (returns false on malformed URLs)
        if ($baseUrlParts === false) {
            $baseUrlParts = ['scheme' => 'http', 'host' => 'localhost', 'path' => '/'];
        }

        // Reconstruct the base url without path and query
        $baseUrl = (isset($baseUrlParts['scheme']) ? $baseUrlParts['scheme'] : 'http') . '://';
        if (isset($baseUrlParts['user'])) {
            $baseUrl .= $baseUrlParts['user'];
            if (isset($baseUrlParts['pass'])) {
                $baseUrl .= ':' . $baseUrlParts['pass'];
            }
            $baseUrl .= '@';
        }
        $baseUrl .= isset($baseUrlParts['host']) ? $baseUrlParts['host'] : 'localhost';
        if (isset($baseUrlParts['port'])) {
            $baseUrl .= ':' . $baseUrlParts['port'];
        }
        $baseUrl .= '/';

        // Add path info from the base URL
        if (isset($baseUrlParts['path'])) {
            $pathInfoArray = array_merge(explode('/', trim($baseUrlParts['path'], '/')), $pathInfoArray);
        }

        // Add query parameters from the base URL
        if (isset($baseUrlParts['query'])) {
            $queryParametersArray = array_merge(explode('&', $baseUrlParts['query']), $queryParametersArray);
        }

        // Expand path info
        $pathInfo = implode('/', $pathInfoArray);

        // Expand query parameters
        $amp = $escape ? '&amp;' : '&';
        $queryParameters = implode($amp, $queryParametersArray);
        $queryParameters = empty($queryParameters) ? '' : '?' . $queryParameters;

        // Assemble and return the final URL
        return $baseUrl . $pathInfo . $queryParameters . $anchor;
    }

    /**
     * Convert a context level to its corresponding context name.
     * @param int $contextLevel
     * @return string context name
     */
    public function _contextLevelToContextName($contextLevel) {
        $index = $contextLevel - 1;
        return $this->_contextList[$index] ?? '';
    }

    /**
     * Convert a context name to its corresponding context level.
     * @param string $contextName
     * @return int context level
     */
    public function _contextNameToContextLevel($contextName) {
        $level = $this->_flippedContextList[$contextName] ?? -1;
        return $level + 1;
    }

}
?>
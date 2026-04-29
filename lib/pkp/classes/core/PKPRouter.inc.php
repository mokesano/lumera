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
 * [WIZDAM EDITION] PHP 8 Compatible, Strict Visibility, Type Safety
 */

class PKPRouter {
    //
    // Internal state cache variables
    // NB: Please do not access directly but
    // only via their respective getters/setters
    //
    /** @var PKPApplication */
    protected $_application;
    
    /** @var Dispatcher */
    protected $_dispatcher;
    
    /** @var integer context depth */
    protected $_contextDepth;
    
    /** @var integer context list */
    protected $_contextList;
    
    /** @var integer context list with keys and values flipped */
    protected $_flippedContextList;
    
    /** @var array context paths */
    protected $_contextPaths = array();
    
    /** @var array contexts */
    protected $_contexts = array();

    /**
     * Constructor
     * [MODERNISASI] Added explicit constructor
     */
    public function __construct() {
        // Init default values if needed
    }

    /**
     * get the application
     * @return PKPApplication
     */
    public function getApplication() {
        // [MODERNISASI] Gunakan instanceof daripada is_a
        assert($this->_application instanceof PKPApplication);
        return $this->_application;
    }

    /**
     * set the application
     * @param $application PKPApplication
     */
    public function setApplication($application) {
        $this->_application = $application;

        // Retrieve context depth and list
        $this->_contextDepth = $application->getContextDepth();
        $this->_contextList = $application->getContextList();
        $this->_flippedContextList = array_flip($this->_contextList);
    }

    /**
     * get the dispatcher
     * @return Dispatcher
     */
    public function getDispatcher() {
        assert($this->_dispatcher instanceof Dispatcher);
        return $this->_dispatcher;
    }

    /**
     * set the dispatcher
     * @param $dispatcher PKPDispatcher
     */
    public function setDispatcher($dispatcher) {
        $this->_dispatcher = $dispatcher;
    }

    /**
     * Determines whether this router can route the given request.
     * @param $request PKPRequest
     * @return boolean true, if the router supports this request, otherwise false
     */
    public function supports($request) {
        // Default implementation returns always true
        return true;
    }

    /**
     * Determine whether or not this request is cacheable
     * @param $request PKPRequest
     * @return boolean
     */
    public function isCacheable($request) {
        // Default implementation returns always false
        return false;
    }

    /**
     * A generic method to return an array of context paths (e.g. a Press or a Conference/SchedConf paths)
     * @param $request PKPRequest the request to be routed
     * @param $requestedContextLevel int (optional) the context level to return in the path
     * @return array of string (each element the path to one context element)
     */
    public function getRequestedContextPaths($request) {
        // Handle context depth 0
        if (!$this->_contextDepth) return array();

        // Validate context parameters
        assert(isset($this->_contextDepth) && isset($this->_contextList));

        $isPathInfoEnabled = $request->isPathInfoEnabled();
        $userVars = array();
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

            $this->_contextPaths = Core::getContextPaths($url, $isPathInfoEnabled,
                $this->_contextList, $this->_contextDepth, $userVars);

            // [WIZDAM] Hooks tetap butuh reference & untuk memodifikasi array
            HookRegistry::dispatch('Router::getRequestedContextPaths', array(&$this->_contextPaths));
        }

        return $this->_contextPaths;
    }

    /**
     * A generic method to return a single context path (e.g. a Press or a SchedConf path)
     * @param $request PKPRequest the request to be routed
     * @param $requestedContextLevel int (optional) the context level to return
     * @return string
     */
    public function getRequestedContextPath($request, $requestedContextLevel = 1) {
        // Handle context depth 0
        if (!$this->_contextDepth) return null;

        // Validate the context level
        assert(isset($this->_contextDepth) && isset($this->_contextList));
        assert($requestedContextLevel > 0 && $requestedContextLevel <= $this->_contextDepth);

        // Return the full context, then retrieve the requested context path
        $contextPaths = $this->getRequestedContextPaths($request);
        assert(isset($this->_contextPaths[$requestedContextLevel - 1]));
        return $this->_contextPaths[$requestedContextLevel - 1];
    }

    /**
     * A Generic call to a context defining object (e.g. a Press, a Conference, or a SchedConf)
     * @param $request PKPRequest the request to be routed
     * @param $requestedContextLevel int (optional) the desired context level
     * @return object
     */
    public function getContext($request, $requestedContextLevel = 1) {
        // Handle context depth 0
        if (!$this->_contextDepth) {
            $nullVar = null;
            return $nullVar;
        }

        if (!isset($this->_contexts[$requestedContextLevel])) {
            // Retrieve the requested context path (this validates the context level and the path)
            $path = $this->getRequestedContextPath($request, $requestedContextLevel);

            // Resolve the path to the context
            if ($path == 'index') {
                $this->_contexts[$requestedContextLevel] = null;
            } else {
                // Get the context name (this validates the context name)
                $requestedContextName = $this->_contextLevelToContextName($requestedContextLevel);

                // Get the DAO for the requested context.
                $contextClass = ucfirst($requestedContextName);
                $daoName = $contextClass.'DAO';
                
                // [MODERNISASI] Hapus referensi &
                $daoInstance = DAORegistry::getDAO($daoName);

                // Retrieve the context from the DAO (by path)
                $daoMethod = 'get'.$contextClass.'ByPath';
                
                // [WIZDAM] Safety Check
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
     * @param $request PKPRequest the request to be routed
     * @param $requestedContextName string page context
     * @return object
     */
    public function getContextByName($request, $requestedContextName) {
        // Handle context depth 0
        if (!$this->_contextDepth) {
            $nullVar = null;
            return $nullVar;
        }

        // Convert the context name to a context level (this validates the context name)
        $requestedContextLevel = $this->_contextNameToContextLevel($requestedContextName);

        // Retrieve the requested context by level
        $returner = $this->getContext($request, $requestedContextLevel);
        return $returner;
    }

    /**
     * Get the URL to the index script.
     * @param $request PKPRequest the request to be routed
     * @return string
     */
    public function getIndexUrl($request) {
        if (!isset($this->_indexUrl)) {
            if ($request->isRestfulUrlsEnabled()) {
                $this->_indexUrl = $request->getBaseUrl();
            } else {
                $this->_indexUrl = $request->getBaseUrl() . '/index.php';
            }
            HookRegistry::dispatch('Router::getIndexUrl', array(&$this->_indexUrl));
        }

        return $this->_indexUrl;
    }


    //
    // Protected template methods to be implemented by sub-classes.
    //
    /**
     * Determine the filename to use for a local cache file.
     * @param $request PKPRequest
     * @return string
     */
    public function getCacheFilename($request) {
        // must be implemented by sub-classes
        assert(false);
    }

    /**
     * Routes a given request to a handler operation
     * @param $request PKPRequest
     */
    public function route($request) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Build a handler request URL into PKPApplication.
     * @param $request PKPRequest the request to be routed
     * @param $newContext mixed Optional contextual paths
     * @param $handler string Optional name of the handler to invoke
     * @param $op string Optional name of operation to invoke
     * @param $path mixed Optional string or array of args to pass to handler
     * @param $params array Optional set of name => value pairs to pass as user parameters
     * @param $anchor string Optional name of anchor to add to URL
     * @param $escape boolean Whether or not to escape ampersands, square brackets, etc. for this URL; default false.
     * @return string the URL
     */
    public function url($request, $newContext = null, $handler = null, $op = null, $path = null,
                $params = null, $anchor = null, $escape = false) {
        // Must be implemented by sub-classes.
        assert(false);
    }

    /**
     * Handle an authorization failure.
     * @param $request Request
     * @param $authorizationMessage string a translation key with the authorization
     * failure message.
     */
    public function handleAuthorizationFailure($request, $authorizationMessage) {
        // Must be implemented by sub-classes.
        assert(false);
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
     * @param $serviceEndpoint callable the handler operation
     * @param $request PKPRequest
     * @param $args array
     * @param $validate boolean whether or not to execute the
     * validation step.
     */
    public function _authorizeInitializeAndCallRequest($serviceEndpoint, $request, $args, $validate = true) {
        assert(is_callable($serviceEndpoint));

        // Pass the dispatcher to the handler.
        $serviceEndpoint[0]->setDispatcher($this->getDispatcher());

        // Authorize the request.
        $roleAssignments = $serviceEndpoint[0]->getRoleAssignments();
        assert(is_array($roleAssignments));
        if ($serviceEndpoint[0]->authorize($request, $args, $roleAssignments)) {
            // Execute class-wide data integrity checks.
            if ($validate) $serviceEndpoint[0]->validate($request, $args);

            // Let the handler initialize itself.
            $serviceEndpoint[0]->initialize($request, $args);

            // Call the service endpoint.
            $result = call_user_func($serviceEndpoint, $args, $request);
        } else {
            // Authorization failed - try to retrieve a user
            // message.
            $authorizationMessage = $serviceEndpoint[0]->getLastAuthorizationMessage();

            // Set a generic authorization message if no
            // specific authorization message was set.
            if ($authorizationMessage == '') $authorizationMessage = 'user.authorization.accessDenied';

            // Handle the authorization failure.
            $result = $this->handleAuthorizationFailure($request, $authorizationMessage);
        }

        // Return the result of the operation to the client.
        if (is_string($result)) echo $result;
    }

    /**
     * Canonicalizes the new context.
     * @param $newContext the raw context array
     * @return array the canonicalized context array
     */
    public function _urlCanonicalizeNewContext($newContext) {
        // Create an empty array in case no new context was given.
        if (is_null($newContext)) $newContext = array();

        // If we got the new context as a scalar then transform
        // it into an array.
        if (is_scalar($newContext)) $newContext = array($newContext);

        // Check whether any new context has been provided.
        // If not then return an empty array.
        $newContextProvided = false;
        foreach($newContext as $contextElement) {
            if(isset($contextElement)) $newContextProvided = true;
        }
        if (!$newContextProvided) $newContext = array();

        return $newContext;
    }

    /**
     * Build the base URL and add the context part of the URL.
     * @return array An array consisting of the base url as the first
     * entry and the context as the remaining entries.
     */
    public function _urlGetBaseAndContext($request, $newContext = array()) {
        $pathInfoEnabled = $request->isPathInfoEnabled();

        // Retrieve the context list.
        $contextList = $this->_contextList;

        // Determine URL context
        $context = array();
        $overriddenBaseUrl = null; // [MODERNISASI] Init variable to prevent warning

        foreach ($contextList as $contextKey => $contextName) {
            if ($pathInfoEnabled) {
                $contextParameter = '';
            } else {
                $contextParameter = $contextName.'=';
            }

            $newContextValue = array_shift($newContext);
            if (isset($newContextValue)) {
                // A new context has been set so use it.
                $contextValue = rawurlencode((string)$newContextValue);
            } else {
                // No new context has been set so determine
                // the current request's context
                $contextObject = $this->getContextByName($request, $contextName);
                if ($contextObject) $contextValue = $contextObject->getPath();
                else $contextValue = 'index';
            }

            // Check whether the base URL is overridden.
            if ($contextKey == 0) {
                $overriddenBaseUrl = Config::getVar('general', "base_url[$contextValue]");
            }

            // [WIZDAM] Jika context adalah 'index' (site-level) dan pathInfo aktif,
            // JANGAN sertakan dalam URL. Sistem tetap mengetahui contextnya adalah 'index'
            // melalui Core::getContextPaths() saat parsing request masuk.
            // $context[] = $contextParameter.$contextValue; // Kode lama
            if ($pathInfoEnabled && $contextValue === 'index') {
                // Lewati — tidak perlu ditampilkan dalam URL
            } else {
                $context[] = $contextParameter.$contextValue;
            }
        }

        // Generate the base url
        if (!empty($overriddenBaseUrl)) {
            $baseUrl = $overriddenBaseUrl;

            // Throw the overridden context away
            array_shift($context);
            array_shift($contextList);
        } else {
            $baseUrl = $this->getIndexUrl($request);
        }

        // Join base URL and context and return the result
        $baseUrlAndContext = array_merge(array($baseUrl), $context);
        return $baseUrlAndContext;
    }

    /**
     * Build the additional parameters part of the URL.
     */
    public function _urlGetAdditionalParameters($request, $params = null, $escape = true) {
        $additionalParameters = array();
        if (!empty($params)) {
            assert(is_array($params));
            foreach ($params as $key => $value) {
                if (is_array($value)) {
                    foreach($value as $element) {
                        $additionalParameters[] = $key.($escape?'%5B%5D=':'[]=').rawurlencode((string)$element);
                    }
                } else {
                    $additionalParameters[] = $key.'='.rawurlencode((string)$value);
                }
            }
        }

        return $additionalParameters;
    }

    /**
     * Creates a valid URL from parts.
     */
    public function _urlFromParts($baseUrl, $pathInfoArray = array(), $queryParametersArray = array(), $anchor = '', $escape = false) {
        // Parse the base url
        $baseUrlParts = parse_url($baseUrl);
        
        // [WIZDAM] Safety check for parse_url
        if ($baseUrlParts === false) {
             // Handle malformed URL gracefully or log it
             $baseUrlParts = array('scheme' => 'http', 'host' => 'localhost', 'path' => '/');
        }

        // Reconstruct the base url without path and query
        $baseUrl = (isset($baseUrlParts['scheme']) ? $baseUrlParts['scheme'] : 'http') .'://';
        if (isset($baseUrlParts['user'])) {
            $baseUrl .= $baseUrlParts['user'];
            if (isset($baseUrlParts['pass'])) {
                $baseUrl .= ':'.$baseUrlParts['pass'];
            }
            $baseUrl .= '@';
        }
        $baseUrl .= isset($baseUrlParts['host']) ? $baseUrlParts['host'] : 'localhost';
        if (isset($baseUrlParts['port'])) $baseUrl .= ':'.$baseUrlParts['port'];
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
        $amp = ($escape ? '&amp;' : '&');
        $queryParameters = implode($amp, $queryParametersArray);
        $queryParameters = (empty($queryParameters) ? '' : '?'.$queryParameters);

        // Assemble and return the final URL
        return $baseUrl.$pathInfo.$queryParameters.$anchor;
    }

    /**
     * Convert a context level to its corresponding context name.
     * @param $contextLevel integer
     * @return string context name
     */
    public function _contextLevelToContextName($contextLevel) {
        assert(isset($this->_contextList[$contextLevel - 1]));
        return $this->_contextList[$contextLevel - 1];
    }

    /**
     * Convert a context name to its corresponding context level.
     * @param $contextName string
     * @return integer context level
     */
    public function _contextNameToContextLevel($contextName) {
        assert(isset($this->_flippedContextList[$contextName]));
        return $this->_flippedContextList[$contextName] + 1;
    }
}

?>
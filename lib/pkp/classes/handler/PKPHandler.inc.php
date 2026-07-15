<?php
declare(strict_types=1);

/**
 * @file classes/core/PKPHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @package core
 * @class PKPHandler
 *
 * Base request handler abstract class.
 * [LUMERA] Transition Mode: Loose Signatures, Modern Internals.
 */

// FIXME: remove these import statements - handler validators are deprecated.
import('lib.pkp.classes.handler.validation.HandlerValidator');
import('lib.pkp.classes.handler.validation.HandlerValidatorRoles');
import('lib.pkp.classes.handler.validation.HandlerValidatorCustom');

class PKPHandler {
    
    /**
     * @var string|null identifier of the controller instance
     */
    public $_id = null;

    /** @var Dispatcher|null */
    public $_dispatcher = null;

    /** @var array validation checks for this page */
    public $_checks = [];

    /** @var array role assignments */
    public $_roleAssignments = [];

    /** @var AuthorizationDecisionManager|null */
    public $_authorizationDecisionManager = null;

    /** 
     * [WIZDAM] New Property: Request Object Storage
     * @var PKPRequest|null 
     */
    public $_request = null;

    /**
     * Constructor
     */
    public function __construct($request = null) {
        // [Wizdam] Capture injected request or fetch singleton from Application
        if ($request) {
            $this->_request = $request;
        } else {
            // Modern Singleton Access with fallback check
            if (class_exists('Application')) {
                $this->_request = Application::get()->getRequest();
            } else {
                $this->_request = Registry::get('request');
            }
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPHandler($request = null) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to use parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }


    //
    // [WIZDAM] New Helper Methods
    //
    
    /**
     * Get the Request object associated with this handler.
     * @return PKPRequest
     */
    public function getRequest() {
        if ($this->_request) {
            return $this->_request;
        }
        $this->_request = Application::get()->getRequest();
        return $this->_request;
    }


    //
    // Setters and Getters
    //
    
    /**
     * Set the controller id
     * @param string $id
     */
    public function setId($id) {
        $this->_id = $id;
    }

    /**
     * Get the controller id
     * @return string|null
     */
    public function getId() {
        return $this->_id;
    }

    /**
     * Get the dispatcher
     * @return Dispatcher
     */
    public function getDispatcher() {
        if (is_null($this->_dispatcher)) {
             $dispatcher = $this->getRequest()->getDispatcher();
             if ($dispatcher) {
                 $this->_dispatcher = $dispatcher;
             } else {
                 // fatalError('PKPHandler: Dispatcher not available.');
             }
        }
        return $this->_dispatcher;
    }

    /**
     * Set the dispatcher
     * @param Dispatcher $dispatcher
     */
    public function setDispatcher($dispatcher) {
        $this->_dispatcher = $dispatcher;
    }

    /**
     * Fallback method in case request handler does not implement index method.
     * @param array $args
     * @param PKPRequest|null $request
     */
    public function index(array $args = [], $request = null) {
        $dispatcher = $this->getDispatcher();
        if ($dispatcher) {
            $dispatcher->handle404($this->getRequest());
        } else {
             // Emergency fallback
             header('HTTP/1.0 404 Not Found');
             exit('404 Not Found (No Dispatcher)');
        }
    }

    /**
     * Add a validation check to the handler.
     * @param HandlerValidator $handlerValidator
     */
    public function addCheck($handlerValidator) {
        $this->_checks[] = $handlerValidator;
    }

    /**
     * Add an authorization policy for this handler.
     * @param AuthorizationPolicy $authorizationPolicy
     * @param bool $addToTop
     */
    public function addPolicy($authorizationPolicy, $addToTop = false) {
        if (is_null($this->_authorizationDecisionManager)) {
            import('lib.pkp.classes.security.authorization.AuthorizationDecisionManager');
            $this->_authorizationDecisionManager = new AuthorizationDecisionManager();
        }
        $this->_authorizationDecisionManager->addPolicy($authorizationPolicy, $addToTop);
    }

    /**
     * Retrieve authorized context objects.
     * @param int $assocType
     * @return mixed
     */
    public function getAuthorizedContextObject($assocType) {
        if (!is_object($this->_authorizationDecisionManager)) return null;
        return $this->_authorizationDecisionManager->getAuthorizedContextObject($assocType);
    }

    /**
     * Get the authorized context.
     * @return array
     */
    public function getAuthorizedContext() {
        if (!is_object($this->_authorizationDecisionManager)) return [];
        return $this->_authorizationDecisionManager->getAuthorizedContext();
    }

    /**
     * Retrieve the last authorization message.
     * @return string|false
     */
    public function getLastAuthorizationMessage() {
        if (!is_object($this->_authorizationDecisionManager)) return '';
        $authorizationMessages = $this->_authorizationDecisionManager->getAuthorizationMessages();
        return end($authorizationMessages);
    }

    /**
     * Add role - operation assignments to the handler.
     * @param int|array $roleIds
     * @param string|array $operations
     */
    public function addRoleAssignment($roleIds, $operations) {
        if (!is_array($operations)) $operations = [$operations];
        if (!is_array($roleIds)) $roleIds = [$roleIds];

        foreach($roleIds as $roleId) {
            if (!isset($this->_roleAssignments[$roleId])) {
                $this->_roleAssignments[$roleId] = [];
            }
            $this->_roleAssignments[$roleId] = array_merge(
                $this->_roleAssignments[$roleId],
                $operations
            );
        }
    }

    /**
     * This method returns an assignment of operation names for the given role.
     * @param int|null $roleId
     * @return array|null
     */
    public function getRoleAssignment($roleId) {
        if (!is_null($roleId)) {
            if (isset($this->_roleAssignments[$roleId])) {
                return $this->_roleAssignments[$roleId];
            }
        }
        return null;
    }

    /**
     * This method returns an assignment of roles to operation names.
     * @return array
     */
    public function getRoleAssignments() {
        return $this->_roleAssignments;
    }

    /**
     * Authorize this request.
     * @param PKPRequest $request
     * @param array $args
     * @param array $roleAssignments
     * @return bool
     */
    public function authorize($request, $args, $roleAssignments) {
        import('lib.pkp.classes.security.authorization.RestrictedSiteAccessPolicy');
        $this->addPolicy(new RestrictedSiteAccessPolicy($request), true);

        if ($this->requireSSL()) {
            import('lib.pkp.classes.security.authorization.HttpsPolicy');
            $this->addPolicy(new HttpsPolicy($request), true);
        }

        if (!defined('SESSION_DISABLE_INIT')) {
            $user = $request->getUser();
            if (is_a($user, 'User')) { // Kept is_a for broader compatibility momentarily
                import('lib.pkp.classes.security.authorization.UserRolesRequiredPolicy');
                $this->addPolicy(new UserRolesRequiredPolicy($request), true);
            }
        }

        if (!is_object($this->_authorizationDecisionManager)) {
            // Fail open or closed depending on legacy logic?
            // Usually fail open in old OJS for backwards compat if auth not set up.
             return true;
        }

        $router = $request->getRouter();
        if (is_a($router, 'PKPPageRouter')) {
            $this->_authorizationDecisionManager->setDecisionIfNoPolicyApplies(AUTHORIZATION_PERMIT);
        } else {
            $this->_authorizationDecisionManager->setDecisionIfNoPolicyApplies(AUTHORIZATION_DENY);
        }

        $decision = $this->_authorizationDecisionManager->decide();
        return $decision == AUTHORIZATION_PERMIT;
    }

    /**
     * Perform data integrity checks.
     * @param array|null $requiredContexts
     * @param PKPRequest|null $request
     * @return bool
     */
    public function validate($requiredContexts = null, $request = null) {
        if (!isset($request)) {
            $request = $this->getRequest();
        }

        // [LUMERA] Global CSRF Validation
        $requestMethod = strtolower($request->getRequestMethod());
        $protectedMethods = ['post', 'put', 'patch', 'delete'];

        if (in_array($requestMethod, $protectedMethods)) {
            $op = $request->getUserVar('op');
            $exemptedOps = ['callback', 'webhook']; 

            if (!in_array($op, $exemptedOps)) {
                import('lib.pkp.classes.validation.ValidatorCSRF'); 
                $clientToken = $request->getUserVar(ValidatorCSRF::FIELD_NAME);
                
                /** LUMERA: CSRF global */
                if (!ValidatorCSRF::checkSignedToken($clientToken, 'global', [], false)) {
                    $session = $request->getSession();
                    
                    $userInput = $request->getUserVars();
                    unset($userInput[ValidatorCSRF::FIELD_NAME]); 
                    $session->setSessionVar('wizdam_old_input', $userInput);

                    import('classes.notification.NotificationManager');
                    $notificationManager = new NotificationManager();
                    $user = $request->getUser();
                    $userId = $user ? $user->getId() : 0;
                    
                    $notificationManager->createTrivialNotification(
                        $userId,
                        NOTIFICATION_TYPE_ERROR,
                        ['contents' => __('common.csrf.validation.error')]
                    );

                    $referer = $_SERVER['HTTP_REFERER'] ?? $request->getBaseUrl();
                    $request->redirectUrl($referer);
                    exit;
                }
            }
        }

        foreach ($this->_checks as $check) {
            $check->_setHandler($this);

            if ( !$check->isValid() ) {
                if ( $check->redirectToLogin ) {
                    Validation::redirectLogin();
                } else {
                    $request->redirect(null, 'index');
                }
            }
        }

        return true;
    }

    /**
     * Subclasses can override this method to configure the handler.
     * [FIX] Removed type hints to match legacy children signatures: initialize($request, $args)
     * @param PKPRequest|null $request
     * @param array|null $args
     */
    public function initialize($request, $args = null) {
        // [LUMERA] Eksekusi Smart Locale Auto-Loader
        AppLocale::requireComponentsForRequest($request);
        
        $router = $request->getRouter();
        if (is_a($router, 'PKPComponentRouter')) {
            $componentId = $router->getRequestedComponent($request);
            $componentId = str_replace('.', '-', PKPString::strtolower(PKPString::substr($componentId, 0, -7)));
            $this->setId($componentId);
        } else {
            if (is_a($router, 'PKPPageRouter')) {
                $this->setId($router->getRequestedPage($request));
            }
        }
    }

    /**
     * Return the DBResultRange structure.
     * [LUMERA] Loose signature
     * @param string $rangeName
     * @param array|null $contextData
     * @return DBResultRange
     */
    public static function getRangeInfo($rangeName, $contextData = null) {
        $request = Application::get()->getRequest();
        $context = $request->getRouter()->getContext($request);
        $pageNum = (int) $request->getUserVar($rangeName . 'Page');
        
        if (empty($pageNum)) {
            $session = $request->getSession();
            $pageNum = 1; 
            if ($session && $contextData !== null) {
                $contextHash = self::hashPageContext($contextData);

                $clearContext = (int) $request->getUserVar('clearPageContext');
                if ($clearContext !== 0 && $clearContext !== 1) $clearContext = 0; 
                
                if ($clearContext) {
                    $session->unsetSessionVar("page-$contextHash");
                } else {
                    $oldPage = $session->getSessionVar("page-$contextHash");
                    if (is_numeric($oldPage)) $pageNum = (int) $oldPage;
                }
            }
        } else {
            $session = $request->getSession();
            if ($session && $contextData !== null) {
                $contextHash = self::hashPageContext($contextData);
                $session->setSessionVar("page-$contextHash", $pageNum);
            }
        }

        if ($context) $count = $context->getSetting('itemsPerPage');
        if (!isset($count)) $count = Config::getVar('interface', 'items_per_page');

        import('lib.pkp.classes.db.DBResultRange');

        if (isset($count)) $returner = new DBResultRange($count, $pageNum);
        else $returner = new DBResultRange(-1, -1);

        return $returner;
    }

    /**
     * Setup Template
     */
    public function setupTemplate() {
        AppLocale::requireComponents(
            LOCALE_COMPONENT_CORE_COMMON,
            LOCALE_COMPONENT_CORE_USER
        );
        if (defined('LOCALE_COMPONENT_APPLICATION_COMMON')) {
            AppLocale::requireComponents(LOCALE_COMPONENT_APPLICATION_COMMON);
        }

        $templateMgr = TemplateManager::getManager($this->getRequest());
        $templateMgr->assign('userRoles', $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES));
        $accessibleWorkflowStages = $this->getAuthorizedContextObject(ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES);
        if ($accessibleWorkflowStages) $templateMgr->assign('accessibleWorkflowStages', $accessibleWorkflowStages);
    }

    /**
     * Generate a unique-ish hash.
     * @param array $contextData
     * @return string
     */
    public static function hashPageContext($contextData = []) {
        $request = Application::get()->getRequest();
        $router  = $request->getRouter();

        $path = $router->getRequestedContextPaths($request);

        $op = '';
        if ($router instanceof PKPPageRouter || $router instanceof PKPComponentRouter) {
            $op = $router->getRequestedOp($request) ?? '';
        }

        $page = '';
        if ($router instanceof PKPPageRouter) {
            $page = $router->getRequestedPage($request) ?? '';
        }

        return md5(
            implode(',', $path) . ',' .
            $page . ',' .
            $op . ',' .
            serialize($contextData)
        );
    }

    /**
     * Get a list of pages that don't require login.
     * @return array
     */
    public function getLoginExemptions() {
        import('lib.pkp.classes.security.authorization.RestrictedSiteAccessPolicy');
        return RestrictedSiteAccessPolicy::_getLoginExemptions();
    }

    /**
     * Assume SSL is required.
     * @return bool
     */
    public function requireSSL() {
        return true;
    }

}
?>
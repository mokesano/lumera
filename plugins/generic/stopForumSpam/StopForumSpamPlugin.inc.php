<?php
declare(strict_types=1);

/**
 * @file plugins/generic/stopForumSpam/StopForumSpamPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StopForumSpamPlugin
 * @ingroup plugins_generic_stopForumSpam
 *
 * @brief Stop Forum Spam plugin class.
 */

define('STOP_FORUM_SPAM_API_ENDPOINT', 'http://www.stopforumspam.com/api?');

import('lib.pkp.classes.plugins.GenericPlugin');

class StopForumSpamPlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function StopForumSpamPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor " . get_class($this) . "(). Please refactor to use __construct().",
                E_USER_DEPRECATED
            );
        }
        $args = func_get_args();
        call_user_func_array([$this, '__construct'], $args);
    }

    /**
     * Called as a plugin is registered to the registry.
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) {
            return true;
        }
        if ($success && $this->getEnabled()) {
            // Hook for validate in registration form
            HookRegistry::register('registrationform::validate', [$this, 'validateExecute']);
        }
        return $success;
    }

    /**
     * Get display name.
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.stopForumSpam.displayName');
    }

    /**
     * Get description.
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.stopForumSpam.description');
    }

    /**
     * Extend the {url ...} smarty to support this plugin.
     * @param array $params
     * @param object $smarty
     * @return string
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = [$this->getCategory(), $this->getName()];
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, [$params['path']]);
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['id'])) {
            $params['path'] = array_merge($params['path'], [$params['id']]);
            unset($params['id']);
        }
        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Set the page's breadcrumbs, given the plugin's tree of items to append.
     * @param bool $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        $request = Application::get()->getRequest();
        $templateMgr = TemplateManager::getManager($request);
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user'
            ],
            [
                $request->url(null, 'manager'),
                'user.role.manager'
            ]
        ];
        if ($isSubclass) {
            $pageCrumbs[] = [
                $request->url(null, 'manager', 'plugins'),
                'manager.plugins'
            ];
        }

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs
     * @param mixed $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);
        if ($this->getEnabled($request)) {
            $verbs[] = ['settings', __('plugins.generic.stopForumSpam.manager.settings')];
        }
        return $verbs;
    }

    /**
     * Provides a hook against validate() method in the RegistrationForm class.
     * This function initiates a curl() call to the Stop Forum Spam API and 
     * submits the new user data for querying. If there is a positive match, 
     * the method inserts a form validation error and returns true, preventing 
     * the form from validating successfully.
     *
     * The first element in the $params array is the form object being submitted.
     * @param string $hookName
     * @param array $params
     * @return bool
     */
    public function validateExecute(string $hookName, array $params): bool {
        $request = Application::get()->getRequest();
        $form = $params[0];
        $journal = $request->getJournal();
        
        if (!$journal) {
            return false;
        }
        $journalId = (int) $journal->getId();
    
        // 1. Ekstrak data
        $ipData = (string) $request->getRemoteAddr();
        $emailData = (string) $form->getData('email');
        $usernameData = (string) $form->getData('username');
    
        // 2. Siapkan parameter query.
        $queryParams = [
            'ip'       => (bool) $this->getSetting($journalId, 'checkIp') ? $ipData : '',
            'email'    => (bool) $this->getSetting($journalId, 'checkEmail') ? $emailData : '',
            'username' => (bool) $this->getSetting($journalId, 'checkUsername') ? $usernameData : '',
        ];
    
        // 3. Merakit URL
        $url = STOP_FORUM_SPAM_API_ENDPOINT . http_build_query($queryParams);
    
        // 4. Eksekusi cURL
        $curlCh = curl_init();
        $httpProxyHost = Config::getVar('proxy', 'http_host');
        if ($httpProxyHost) {
            curl_setopt($curlCh, CURLOPT_PROXY, $httpProxyHost);
            curl_setopt($curlCh, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            $proxyUsername = Config::getVar('proxy', 'username');
            if ($proxyUsername) {
                curl_setopt($curlCh, CURLOPT_PROXYUSERPWD, $proxyUsername . ':' . Config::getVar('proxy', 'password'));
            }
        }
        
        curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlCh, CURLOPT_TIMEOUT, 10);
        curl_setopt($curlCh, CURLOPT_URL, $url);
    
        $response = curl_exec($curlCh);
        // 5. Tutup koneksi cURL
        curl_close($curlCh);
    
        // 6. Evaluasi respons
        if ($response !== false && preg_match('/<appears>yes<\/appears>/', (string) $response)) {
            $form->addError(__('plugins.generic.stopForumSpam.checkName'), __('plugins.generic.stopForumSpam.checkMessage'));
            return true;
        }
    
        return false;
    }

    /**
     * Execute a management verb on this plugin.
     * @param string $verb
     * @param array $args
     * @param string|null $message
     * @param array|null $messageParams
     * @param mixed $request
     * @return bool
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) {
            return false;
        }

        // Lumera Singleton Fallback
        if (!$request) {
            $request = Application::get()->getRequest();
        }

        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $journal = $request->getJournal();
                
                if (!$journal) {
                    return false;
                }

                $this->import('StopForumSpamSettingsForm');
                $form = new StopForumSpamSettingsForm($this, (int) $journal->getId());
                
                if ($request->getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', [$this->getCategory()]);
                        return false;
                    } else {
                        $this->setBreadcrumbs(true);
                        $form->display($request);
                    }
                } else {
                    $this->setBreadcrumbs(true);
                    $form->initData();
                    $form->display($request);
                }
                return true;
            default:
                throw new \BadMethodCallException('Unknown management verb');
        }
    }
    
}
?>
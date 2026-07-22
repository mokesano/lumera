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
            trigger_error("Class '" . get_class($this) . "' uses deprecated constructor parent::StopForumSpamPlugin(). Please refactor to parent::__construct().", E_USER_DEPRECATED);
        }
        $args = func_get_args();
        call_user_func_array(array($this, '__construct'), $args);
    }

    /**
     * Called as a plugin is registered to the registry
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        $success = parent::register($category, $path);
        if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
        if ($success && $this->getEnabled()) {
            // Hook for validate in registration form
            HookRegistry::register('registrationform::validate', [$this, 'validateExecute']);
        }
        return $success;
    }

    /**
     * Get display name
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.stopForumSpam.displayName');
    }

    /**
     * Get description
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
     * Set the page's breadcrumbs, given the plugin's tree of items
     * to append.
     * @param boolean $isSubclass
     */
    public function setBreadcrumbs($isSubclass = false) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                Request::url(null, 'user'),
                'navigation.user'
            ],
            [
                Request::url(null, 'manager'),
                'user.role.manager'
            ]
        ];
        if ($isSubclass) $pageCrumbs[] = [
            Request::url(null, 'manager', 'plugins'),
            'manager.plugins'
        ];

        $templateMgr->assign('pageHierarchy', $pageCrumbs);
    }

    /**
     * Display verbs for the management interface.
     * @param array $verbs An array of management verbs
     * @param Request $request
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
     * submits the new user data for querying.  If there is a positive match, 
     * the method inserts a form validation error and returns true, preventing 
     * the form from validating successfully.
     *
     * The first element in the $params array is the form object being submitted
     * @param string $hookName
     * @param array $params
     * @return boolean
     */
    public function validateExecute(string $hookName, array $params): bool {
        $form = $params[0];
        $journal = Request::getJournal();
        $journalId = $journal->getId();
    
        // 1. Ekstrak data dan paksa menjadi string secara aman (Null Coalescing + Casting)
        // Jika getData() mengembalikan null, ubah menjadi string kosong ('')
        $ipData = Request::getRemoteAddr() ?? '';
        $emailData = $form->getData('email') ?? '';
        $usernameData = $form->getData('username') ?? '';
    
        // 2. Siapkan parameter query. 
        // Catatan API: Parameter kosong akan menghasilkan <appears>no</appears>
        $queryParams = [
            'ip'       => (bool) $this->getSetting($journalId, 'checkIp') ? (string) $ipData : '',
            'email'    => (bool) $this->getSetting($journalId, 'checkEmail') ? (string) $emailData : '',
            'username' => (bool) $this->getSetting($journalId, 'checkUsername') ? (string) $usernameData : '',
        ];
    
        // 3. Merakit URL menggunakan fungsi bawaan (otomatis melakukan urlencode yang aman)
        $url = STOP_FORUM_SPAM_API_ENDPOINT . '?' . http_build_query($queryParams);
    
        // 4. Eksekusi cURL
        $curlCh = curl_init();
        if ($httpProxyHost = Config::getVar('proxy', 'http_host')) {
            curl_setopt($curlCh, CURLOPT_PROXY, $httpProxyHost);
            curl_setopt($curlCh, CURLOPT_PROXYPORT, Config::getVar('proxy', 'http_port', '80'));
            if ($username = Config::getVar('proxy', 'username')) {
                curl_setopt($curlCh, CURLOPT_PROXYUSERPWD, $username . ':' . Config::getVar('proxy', 'password'));
            }
        }
        
        curl_setopt($curlCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlCh, CURLOPT_TIMEOUT, 10);
        curl_setopt($curlCh, CURLOPT_URL, $url);
    
        $response = curl_exec($curlCh);
        
        // 5. Tutup koneksi cURL untuk mencegah memory leak (Bug dari kode asli)
        curl_close($curlCh);
    
        // 6. Evaluasi respons
        if ($response && preg_match('/<appears>yes<\/appears>/', (string) $response)) {
            $form->addError(__('plugins.generic.stopForumSpam.checkName'), __('plugins.generic.stopForumSpam.checkMessage'));
            return true;
        }
    
        return false;
    }

    /**
     * Execute a management verb on this plugin
     * @param string $verb
     * @param array $args
     * @param string $message Result status message
     * @param array $messageParams Parameters for the message key
     * @return boolean
     */
    public function manage(string $verb, array $args, ?string &$message = null, ?array &$messageParams = null, $request = null): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) return false;

        if (!$request) $request = Registry::get('request');

        switch ($verb) {
            case 'settings':
                $templateMgr = TemplateManager::getManager();
                $templateMgr->register_function('plugin_url', [$this, 'smartyPluginUrl']);
                $journal = $request->getJournal();

                $this->import('StopForumSpamSettingsForm');
                $form = new StopForumSpamSettingsForm($this, $journal->getId());
                if (Request::getUserVar('save')) {
                    $form->readInputData();
                    if ($form->validate()) {
                        $form->execute();
                        $request->redirect(null, 'manager', 'plugins', $this->getCategory());
                        return false;
                    } else {
                        $this->setBreadcrumbs(true);
                        $form->display();
                    }
                } else {
                    $this->setBreadcrumbs(true);
                    $form->initData();
                    $form->display();
                }
                return true;
            default:
                // Unknown management verb
                assert(false);
                return false;
        }
    }
    
}
?>
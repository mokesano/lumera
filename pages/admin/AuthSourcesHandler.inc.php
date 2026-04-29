<?php
declare(strict_types=1);

/**
 * @file pages/admin/AuthSourcesHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthSourcesHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for authentication source management in site administration. 
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('classes.plugins.AuthPlugin');
import('lib.pkp.classes.security.AuthSourceDAO');
import('pages.admin.AdminHandler');

class AuthSourcesHandler extends AdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AuthSourcesHandler() {
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
     * Display a list of authentication sources.
     * @param array $args
     * @param PKPRequest $request
     */
    public function auth($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate($request, true);

        $authDao = DAORegistry::getDAO('AuthSourceDAO');
        $sources = $authDao->getSources();

        $plugins = PluginRegistry::loadCategory(AUTH_PLUGIN_CATEGORY);
        $pluginOptions = [];
        foreach ($plugins as $plugin) {
            $pluginOptions[$plugin->getName()] = $plugin->getDisplayName();
        }

        $templateMgr = TemplateManager::getManager();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('sources', $sources);
        $templateMgr->assign('pluginOptions', $pluginOptions);
        $templateMgr->assign('helpTopicId', 'site.siteManagement');
        $templateMgr->display('admin/auth/sources.tpl');
    }

    /**
     * Update the default authentication source.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateAuthSources($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $authDao = DAORegistry::getDAO('AuthSourceDAO');
        $authDao->setDefault((int) $request->getUserVar('defaultAuthId'));

        $request->redirect(null, null, 'auth');
    }

    /**
     * Create an authentication source.
     * @param array $args
     * @param PKPRequest $request
     */
    public function createAuthSource($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $authDao = DAORegistry::getDAO('AuthSourceDAO');
        $auth = $authDao->newDataObject();
        
        // [WIZDAM] Safe string casting and sanitization
        $pluginName = trim(basename((string) $request->getUserVar('plugin')));
        $auth->setPlugin($pluginName);

        if ($authDao->insertSource($auth)) {
            $request->redirect(null, null, 'editAuthSource', $auth->getAuthId());
        } else {
            $request->redirect(null, null, 'auth');
        }
    }

    /**
     * Display form to edit an authentication source.
     * @param array $args
     * @param PKPRequest $request
     */
    public function editAuthSource($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();
        
        $this->setupTemplate($request, true);

        import('classes.security.form.AuthSourceSettingsForm');
        $form = new AuthSourceSettingsForm((int) array_shift($args));
        $form->initData();
        $form->display();
    }

    /**
     * Update an authentication source.
     * @param array $args
     * @param PKPRequest $request
     */
    public function updateAuthSource($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        import('classes.security.form.AuthSourceSettingsForm');
        $form = new AuthSourceSettingsForm((int) array_shift($args));
        $form->readInputData();
        $form->execute();
        $request->redirect(null, null, 'auth');
    }

    /**
     * Delete an authentication source.
     * @param array $args
     * @param PKPRequest $request
     */
    public function deleteAuthSource($args, $request = null) {
        $this->validate();
        
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        $authId = (int) array_shift($args);
        $authDao = DAORegistry::getDAO('AuthSourceDAO');
        $authDao->deleteObject($authId);
        $request->redirect(null, null, 'auth');
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file pages/admin/AdminFunctionsHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminFunctionsHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for site administrative/maintenance functions.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

import('lib.pkp.classes.site.Version');
import('lib.pkp.classes.site.VersionDAO');
import('lib.pkp.classes.site.VersionCheck');
import('pages.admin.AdminHandler');

class AdminFunctionsHandler extends AdminHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AdminFunctionsHandler() {
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
     * Show system information summary.
     * @param array $args
     * @param PKPRequest $request
     */
    public function systemInfo($args, $request) {
        $this->validate($request);
        $this->setupTemplate($request, true);

        $configData = Config::getData();

        $dbconn = DBConnection::getConn();
        $dbServerInfo = $dbconn->ServerInfo();

        $versionDao = DAORegistry::getDAO('VersionDAO');
        $currentVersion = $versionDao->getCurrentVersion();
        $versionHistory = $versionDao->getVersionHistory();

        $serverInfo = [
            'admin.server.platform' => Core::serverPHPOS(),
            'admin.server.phpVersion' => Core::serverPHPVersion(),
            'admin.server.apacheVersion' => (function_exists('apache_get_version') ? apache_get_version() : __('common.notAvailable')),
            'admin.server.dbDriver' => Config::getVar('database', 'driver'),
            'admin.server.dbVersion' => (empty($dbServerInfo['description']) ? $dbServerInfo['version'] : $dbServerInfo['description'])
        ];

        $templateMgr = TemplateManager::getManager();
        // [WIZDAM] Removed assign_by_ref
        $templateMgr->assign('currentVersion', $currentVersion);
        $templateMgr->assign('versionHistory', $versionHistory);
        $templateMgr->assign('configData', $configData);
        $templateMgr->assign('serverInfo', $serverInfo);
        
        if ((int) $request->getUserVar('versionCheck')) {
            $latestVersionInfo = VersionCheck::getLatestVersion();
            $latestVersionInfo['patch'] = VersionCheck::getPatch($latestVersionInfo);
            $templateMgr->assign('latestVersionInfo', $latestVersionInfo);
        }
        $templateMgr->assign('helpTopicId', 'site.administrativeFunctions');
        $templateMgr->display('admin/systemInfo.tpl');
    }

    /**
     * Show full PHP configuration information.
     */
    public function phpinfo() {
        $this->validate();
        phpinfo();
    }

    /**
     * Expire all user sessions (will log out all users currently logged in).
     */
    public function expireSessions() {
        $this->validate();
        $sessionDao = DAORegistry::getDAO('SessionDAO');
        $sessionDao->deleteAllSessions();
        Application::get()->getRequest()->redirect(null, 'admin');
    }

    /**
     * Clear compiled templates.
     */
    public function clearTemplateCache() {
        $this->validate();
        $templateMgr = TemplateManager::getManager();
        $templateMgr->clearTemplateCache();
        Application::get()->getRequest()->redirect(null, 'admin');
    }

    /**
     * Clear the data cache.
     */
    public function clearDataCache() {
        $this->validate();

        // Clear the CacheManager's caches
        $cacheManager = CacheManager::getManager();
        $cacheManager->flush(null, CACHE_TYPE_FILE);
        $cacheManager->flush(null, CACHE_TYPE_OBJECT);

        // Clear ADODB's cache
        $userDao = DAORegistry::getDAO('UserDAO'); // As good as any
        $userDao->flushCache();

        Application::get()->getRequest()->redirect(null, 'admin');
    }

    /**
     * Download scheduled task execution log file.
     */
    public function downloadScheduledTaskLogFile() {
        $this->validate();
        $application = Application::getApplication();
        $request = $application->getRequest();

        // [SECURITY FIX] Sanitasi nama file untuk mencegah directory traversal
        $file = basename(trim((string) $request->getUserVar('file')));
        
        import('lib.pkp.classes.scheduledTask.ScheduledTaskHelper');
        ScheduledTaskHelper::downloadExecutionLog($file);
    }
    
    /**
     * Clear scheduled tasks execution logs.
     */
    public function clearScheduledTaskLogFiles() {
        $this->validate();
        import('lib.pkp.classes.scheduledTask.ScheduledTaskHelper');
        ScheduledTaskHelper::clearExecutionLogs();    

        Application::get()->getRequest()->redirect(null, 'admin');
    }
}
?>
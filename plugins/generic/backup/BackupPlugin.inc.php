<?php
declare(strict_types=1);

/**
 * @file plugins/generic/backup/BackupPlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BackupPlugin
 * @ingroup plugins_generic_backup
 *
 * @brief Plugin to allow generation of a backup extract
 * [WIZDAM EDITION] Modernized. PHP 8 Safe CLI Execution & Resource Mgmt.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class BackupPlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function BackupPlugin() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::BackupPlugin(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Register the plugin, if enabled
     * @param string $category
     * @param string $path
     * @return bool
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            $this->addLocaleData();
            if ($this->getEnabled()) {
                HookRegistry::register('Templates::Admin::Index::AdminFunctions', array($this, 'addLink'));
                HookRegistry::register('LoadHandler', array($this, 'handleRequest'));
            }
            return true;
        }
        return false;
    }

    /**
     * Designate this plugin as a site plugin
     * @return bool
     */
    public function isSitePlugin(): bool {
        return true;
    }

    /**
     * [PERISAI KEAMANAN]
     * Override fungsi bawaan OJS. Memaksa semua pengaturan plugin ini
     * selalu disimpan ke level Site (Context 0), tidak peduli dari 
     * jurnal mana tombol "Enable" ditekan.
     */
    public function updateSetting($contextId, $name, $value, $type = null) {
        parent::updateSetting(0, $name, $value, $type);
    }

    /**
     * [PERISAI KEAMANAN]
     * Override fungsi bawaan OJS. Memaksa sistem selalu membaca status
     * dari level Site (Context 0).
     */
    public function getSetting($contextId, $name) {
        return parent::getSetting(0, $name);
    }

    /**
     * Hook callback function for TemplateManager::display
     * @param string $hookName
     * @param array $args
     */
    public function addLink($hookName, $args) {
        // Otorisasi — saat hook benar-benar dipanggil
        if (!Validation::isSiteAdmin()) return false;

        $request = Application::getRequest();

        echo '<li><a href="' . $request->url(null, 'backup') . '">' . __('plugins.generic.backup.link') . '</a></li>';
        
        return false;
    }

    /**
     * Hook callback function for LoadHandler
     * @param string $hookName
     * @param array $args
     */
    public function handleRequest($hookName, $args) {
        $page = $args[0];
        
        if ($page !== 'backup') return false;
        
        // Otorisasi di sini — saat request backup benar-benar datang
        if (!Validation::isSiteAdmin()) return false;
        
        $op = $args[1];
        $sourceFile = $args[2];
        $request = Application::getRequest();

        AppLocale::requireComponents(LOCALE_COMPONENT_PKP_ADMIN, LOCALE_COMPONENT_APPLICATION_COMMON);
        $returnValue = 0;
        $dateStamp = date('Y-m-d');

        switch ($op) {
            case 'index':
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign('isDumpConfigured', Config::getVar('cli', 'dump')!='');
                $templateMgr->assign('isTarConfigured', Config::getVar('cli', 'tar')!='');
                $templateMgr->display($this->getTemplatePath() . 'index.tpl');
                exit();
                
            case 'db':
                // [WIZDAM RESOURCE] Prevent Timeout & Buffer Issues
                set_time_limit(0);
                if (function_exists('ob_clean')) ob_clean();
                if (function_exists('flush')) flush();

                $dumpTool = Config::getVar('cli', 'dump');
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename=db-' . $dateStamp . '.sql');
                header('Content-Type: text/plain');
                header('Content-Transfer-Encoding: binary');

                passthru(sprintf(
                    $dumpTool,
                    escapeshellarg(Config::getVar('database', 'host')),
                    escapeshellarg(Config::getVar('database', 'username')),
                    escapeshellarg(Config::getVar('database', 'password')),
                    escapeshellarg(Config::getVar('database', 'name'))
                ), $returnValue);
                
                if ($returnValue !== 0) $request->redirect(null, null, 'failure');
                exit();
                
            case 'files':
                // [WIZDAM RESOURCE] Prevent Timeout
                set_time_limit(0);
                if (function_exists('ob_clean')) ob_clean();
                if (function_exists('flush')) flush();

                $tarTool = Config::getVar('cli', 'tar');
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename=files-' . $dateStamp . '.tar.gz');
                header('Content-Type: application/x-gzip'); // Corrected MIME type
                header('Content-Transfer-Encoding: binary');
                
                passthru($tarTool . ' -c -z ' . escapeshellarg(Config::getVar('files', 'files_dir')), $returnValue);
                
                if ($returnValue !== 0) $request->redirect(null, null, 'failure');
                exit();
                
            case 'code':
                // [WIZDAM RESOURCE] Prevent Timeout
                set_time_limit(0);
                if (function_exists('ob_clean')) ob_clean();
                if (function_exists('flush')) flush();

                $tarTool = Config::getVar('cli', 'tar');
                header('Content-Description: File Transfer');
                header('Content-Disposition: attachment; filename=code-' . $dateStamp . '.tar.gz');
                header('Content-Type: application/x-gzip');
                header('Content-Transfer-Encoding: binary');
                
                // Backup OJS Root Dir (Up 4 levels from this plugin file)
                $rootDir = dirname(dirname(dirname(dirname(__FILE__))));
                passthru($tarTool . ' -c -z ' . escapeshellarg($rootDir), $returnValue);
                
                if ($returnValue !== 0) $request->redirect(null, null, 'failure');
                exit();
                
            case 'failure':
                $templateMgr = TemplateManager::getManager($request);
                $templateMgr->assign('message', 'plugins.generic.backup.failure');
                $templateMgr->assign('backLink', $request->url(null, null, 'backup'));
                $templateMgr->assign('backLinkLabel', 'plugins.generic.backup.link');
                $templateMgr->display('common/message.tpl');
                exit();
        }
        return false;
    }

    /**
     * Get the symbolic name of this plugin
     * @return string
     */
    public function getName(): string {
        return 'BackupPlugin';
    }

    /**
     * Get the display name of this plugin
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.backup.name');
    }

    /**
     * Get the description of this plugin
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.backup.description');
    }

    /**
     * Check whether or not this plugin is enabled
     * @param null|mixed $request
     * @return bool
     */
    public function getEnabled($request = null): bool {
        return (bool) $this->getSetting(0, 'enabled');
    }

    /**
     * Get a list of available management verbs for this plugin
     * @param array $verbs
     * @param null|mixed $request
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $isEnabled = $this->getEnabled($request);

        return array(array(
            ($isEnabled?'disable':'enable'),
            __($isEnabled?'manager.plugins.disable':'manager.plugins.enable')
        ));
    }

    /**
     * Perform management operations for this plugin
     * @see PKPPlugin::manage()
     * @param string $verb
     * @param array $args
     * @param string $message
     * @param array $messageParams
     * @param null|mixed $request
     * @return bool
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        switch ($verb) {
            case 'enable':
                $this->updateSetting(0, 'enabled', true);
                
                // [WIZDAM] Gunakan NotificationManager
                import('classes.notification.NotificationManager');
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_SUCCESS,
                    array('contents' => __('plugins.generic.backup.enabled'))
                );
                break;
                
            case 'disable':
                $this->updateSetting(0, 'enabled', false);
                
                // [WIZDAM] Gunakan NotificationManager
                import('classes.notification.NotificationManager');
                $notificationMgr = new NotificationManager();
                $notificationMgr->createTrivialNotification(
                    $request->getUser()->getId(),
                    NOTIFICATION_TYPE_SUCCESS,
                    array('contents' => __('plugins.generic.backup.disabled'))
                );
                break;
        }
        return false;
    }
}
?>
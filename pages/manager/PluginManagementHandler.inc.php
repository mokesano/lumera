<?php
declare(strict_types=1);

/**
 * @file pages/manager/PluginManagementHandler.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PluginManagementHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for installing/upgrading/deleting plugins.
 *
 * [WIZDAM EDITION] Refactored for PHP 8.1+ Strict Compliance
 */

define('VERSION_FILE', '/version.xml');
define('INSTALL_FILE', '/install.xml');
define('UPGRADE_FILE', '/upgrade.xml');

import('lib.pkp.classes.site.Version');
import('lib.pkp.classes.site.VersionCheck');
import('lib.pkp.classes.file.FileManager');
import('classes.install.Install');
import('classes.install.Upgrade');
import('pages.manager.ManagerHandler');

class PluginManagementHandler extends ManagerHandler {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PluginManagementHandler() {
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
     * Display a list of plugins along with management options.
     * @param array $args
     * @param PKPRequest $request
     */
    public function managePlugins($args, $request) {
        $this->validate($request);
        $path = isset($args[0]) ? $args[0] : null;
        $category = isset($args[1]) ? $args[1] : null;
        $plugin = isset($args[2]) ? $args[2] : null;

        switch($path) {
            case 'install':
                $this->_showInstallForm($request);
                break;
            case 'installPlugin':
                $this->_uploadPlugin($request, 'install');
                break;
            case 'upgrade':
                $this->_showUpgradeForm($request, $category, $plugin);
                break;
            case 'upgradePlugin':
                $this->_uploadPlugin($request, 'upgrade', $category, $plugin);
                break;
            case 'delete':
                $this->_showDeleteForm($request, $category, $plugin);
                break;
            case 'deletePlugin':
                $this->_deletePlugin($request, $category, $plugin);
                break;
            default:
                $request->redirect(null, 'manager', 'plugins');
        }

        $this->setupTemplate(true);
    }

    /**
     * The site setting option 'preventManagerPluginManagement' must not be set for
     * journal managers to be able to manage plugins.
     * @param mixed|null $requiredContexts (legacy param, ignored)
     * @param PKPRequest|null $request
     */
    public function validate($requiredContexts = null, $request = null) {
        // [WIZDAM] Singleton Fallback
        if (!$request) $request = Application::get()->getRequest();

        parent::validate();
        if (!Validation::isSiteAdmin()) {
            $site = $request->getSite();
            $preventManagerPluginManagement = $site->getSetting('preventManagerPluginManagement');
            if ($preventManagerPluginManagement) {
                $request->redirect(null, 'manager', 'plugins');
            }
        }
    }

    /**
     * Show plugin installation form.
     * @param PKPRequest $request
     */
    public function _showInstallForm($request) {
        $this->validate($request);
        $templateMgr = TemplateManager::getManager();
        $this->setupTemplate(true);

        $templateMgr->assign('path', 'install');
        $templateMgr->assign('uploaded', false);
        $templateMgr->assign('error', false);

        $templateMgr->assign('pageHierarchy', $this->_setBreadcrumbs($request, true));

        $templateMgr->display('manager/plugins/managePlugins.tpl');
    }

    /**
     * Show form to select plugin for upgrade.
     * @param PKPRequest $request
     * @param string $category
     * @param string $plugin
     */
    public function _showUpgradeForm($request, $category, $plugin) {
        $this->validate($request);
        $templateMgr = TemplateManager::getManager();
        $this->setupTemplate(true);

        $templateMgr->assign('path', 'upgrade');
        $templateMgr->assign('category', $category);
        $templateMgr->assign('plugin', $plugin);
        $templateMgr->assign('uploaded', false);
        $templateMgr->assign('pageHierarchy', $this->_setBreadcrumbs($request, true, $category));

        $templateMgr->display('manager/plugins/managePlugins.tpl');
    }

    /**
     * Confirm deletion of plugin.
     * @param PKPRequest $request
     * @param string $category
     * @param string $plugin
     */
    public function _showDeleteForm($request, $category, $plugin) {
        $this->validate($request);
        $templateMgr = TemplateManager::getManager();
        $this->setupTemplate(true);

        $templateMgr->assign('path', 'delete');
        $templateMgr->assign('category', $category);
        $templateMgr->assign('plugin', $plugin);
        $templateMgr->assign('deleted', false);
        $templateMgr->assign('error', false);
        $templateMgr->assign('pageHierarchy', $this->_setBreadcrumbs($request, true, $category));

        $templateMgr->display('manager/plugins/managePlugins.tpl');
    }


    /**
     * Decompress uploaded plugin and install in the correct plugin directory.
     * @param PKPRequest $request
     * @param string $function type of operation to perform after upload ('upgrade' or 'install')
     * @param string|null $category the category of the uploaded plugin (upgrade only)
     * @param string|null $plugin the name of the uploaded plugin (upgrade only)
     */
    public function _uploadPlugin($request, $function, $category = null, $plugin = null) {
        $this->validate($request);
        $templateMgr = TemplateManager::getManager();
        $this->setupTemplate(true);

        $templateMgr->assign('error', false);
        $templateMgr->assign('uploaded', false);
        $templateMgr->assign('path', $function);

        $errorMsg = '';
        $temporaryFileManager = null;
        $user = null;

        if ((int) $request->getUserVar('uploadPlugin')) {
            import('classes.file.TemporaryFileManager');
            $temporaryFileManager = new TemporaryFileManager();
            $user = $request->getUser();
        } else {
            $errorMsg = 'manager.plugins.fileSelectError';
        }

        $temporaryFile = null;
        $pluginDir = '';
        $pluginName = '';

        if (empty($errorMsg) && $temporaryFileManager && $user) {
            if ($temporaryFile = $temporaryFileManager->handleUpload('newPlugin', $user->getId())) {
                // tar archive basename (less potential version number) must equal plugin directory name
                // and plugin files must be in a directory named after the plug-in.
                $matches = [];
                PKPString::regexp_match_get('/^[a-zA-Z0-9]+/', basename($temporaryFile->getOriginalFileName(), '.tar.gz'), $matches);
                $pluginName = array_pop($matches);
                // Create random dirname to avoid symlink attacks.
                $pluginDir = dirname($temporaryFile->getFilePath()) . DIRECTORY_SEPARATOR . $pluginName . substr(md5((string) mt_rand()), 0, 10);
                mkdir($pluginDir);
            } else {
                $errorMsg = 'manager.plugins.uploadError';
            }
        }

        if (empty($errorMsg) && $temporaryFile) {
            // Test whether the tar binary is available for the export to work
            $tarBinary = Config::getVar('cli', 'tar');
            if (!empty($tarBinary) && file_exists($tarBinary)) {
                exec($tarBinary.' -xzf ' . escapeshellarg($temporaryFile->getFilePath()) . ' -C ' . escapeshellarg($pluginDir));
            } else {
                $errorMsg = 'manager.plugins.tarCommandNotFound';
            }
        }

        if (empty($errorMsg)) {
            // We should now find a directory named after the
            // plug-in within the extracted archive.
            $pluginDir .= DIRECTORY_SEPARATOR . $pluginName;
            if (is_dir($pluginDir)) {
                if ($function == 'install') {
                    $this->_installPlugin($request, $pluginDir, $templateMgr);
                } else if ($function == 'upgrade') {
                    $this->_upgradePlugin($request, $pluginDir, $templateMgr, $category, $plugin);
                }
            } else {
                $errorMsg = 'manager.plugins.invalidPluginArchive';
            }
        }

        if (!empty($errorMsg)) {
            $templateMgr->assign('error', true);
            $templateMgr->assign('message', $errorMsg);
        }

        $templateMgr->display('manager/plugins/managePlugins.tpl');
    }

    /**
     * Installs the uploaded plugin
     * @param PKPRequest $request
     * @param string $path path to plugin Directory
     * @param TemplateManager $templateMgr reference to template manager
     * @return bool
     */
    public function _installPlugin($request, $path, &$templateMgr) {
        $this->validate($request);
        $versionFile = $path . VERSION_FILE;
        $templateMgr->assign('error', true);
        $templateMgr->assign('pageHierarchy', $this->_setBreadcrumbs($request, true));

        $pluginVersion = VersionCheck::getValidPluginVersionInfo($versionFile);
        if ($pluginVersion === null) return false;
        
        // [WIZDAM] Type Checking
        if (!($pluginVersion instanceof Version)) return false;

        $versionDao = DAORegistry::getDAO('VersionDAO'); /* @var $versionDao VersionDAO */
        $installedPlugin = $versionDao->getCurrentVersion($pluginVersion->getProductType(), $pluginVersion->getProduct(), true);

        if(!$installedPlugin) {
            $pluginDest = Core::getBaseDir() . DIRECTORY_SEPARATOR . strtr($pluginVersion->getProductType(), '.', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $pluginVersion->getProduct();

            // Copy the plug-in from the temporary folder to the
            // target folder.
            $fileManager = new FileManager();
            if(!$fileManager->copyDir($path, $pluginDest)) {
                $templateMgr->assign('message', 'manager.plugins.copyError');
                return false;
            }

            // Remove the temporary folder.
            $fileManager->rmtree(dirname($path));

            // Upgrade the database with the new plug-in.
            $installFile = $pluginDest . INSTALL_FILE;
            if(!is_file($installFile)) $installFile = Core::getBaseDir() . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'xml' . DIRECTORY_SEPARATOR . 'defaultPluginInstall.xml';
            
            // [WIZDAM] assert replaced with explicit check
            if (!is_file($installFile)) return false;

            $params = $this->_setConnectionParams();
            $installer = new Install($params, $installFile, true);
            $installer->setCurrentVersion($pluginVersion);
            if (!$installer->execute()) {
                // Roll back the copy
                if (is_dir($pluginDest)) $fileManager->rmtree($pluginDest);
                $templateMgr->assign('message', ['manager.plugins.installFailed', $installer->getErrorString()]);
                return false;
            }

            $message = ['manager.plugins.installSuccessful', $pluginVersion->getVersionString()];
            $templateMgr->assign('message', $message);
            $templateMgr->assign('uploaded', true);
            $templateMgr->assign('error', false);

            $versionDao->insertVersion($pluginVersion, true);
            return true;
        } else {
            if ($this->_checkIfNewer($pluginVersion->getProductType(), $pluginVersion->getProduct(), $pluginVersion)) {
                $templateMgr->assign('message', 'manager.plugins.pleaseUpgrade');
                return false;
            } else {
                $templateMgr->assign('message', 'manager.plugins.installedVersionOlder');
                return false;
            }
        }
    }

    /**
     * Upgrade a plugin to a newer version from the user's filesystem
     * @param PKPRequest $request
     * @param string $path path to plugin Directory
     * @param TemplateManager $templateMgr reference to template manager
     * @param string $category
     * @param string $plugin
     * @return bool
     */
    public function _upgradePlugin($request, $path, &$templateMgr, $category, $plugin) {
        $this->validate($request);
        $versionFile = $path . VERSION_FILE;
        $templateMgr->assign('error', true);
        $templateMgr->assign('pageHierarchy', $this->_setBreadcrumbs($request, true, $category));

        $pluginVersion = VersionCheck::getValidPluginVersionInfo($versionFile);
        if ($pluginVersion === null) return false;
        
        // [WIZDAM] Type Checking
        if (!($pluginVersion instanceof Version)) return false;

        // Check whether the uploaded plug-in fits the original plug-in.
        if ('plugins.'.$category != $pluginVersion->getProductType()) {
            $templateMgr->assign('message', 'manager.plugins.wrongCategory');
            return false;
        }

        if ($plugin != $pluginVersion->getProduct()) {
            $templateMgr->assign('message', 'manager.plugins.wrongName');
            return false;
        }

        $versionDao = DAORegistry::getDAO('VersionDAO');
        $installedPlugin = $versionDao->getCurrentVersion($pluginVersion->getProductType(), $pluginVersion->getProduct(), true);
        if(!$installedPlugin) {
            $templateMgr->assign('message', 'manager.plugins.pleaseInstall');
            return false;
        }

        if ($this->_checkIfNewer($pluginVersion->getProductType(), $pluginVersion->getProduct(), $pluginVersion)) {
            $templateMgr->assign('message', 'manager.plugins.installedVersionNewer');
            return false;
        } else {
            $pluginDest = Core::getBaseDir() . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $plugin;

            // Delete existing files.
            $fileManager = new FileManager();
            if (is_dir($pluginDest)) $fileManager->rmtree($pluginDest);

            // Check whether deleting has worked.
            if(is_dir($pluginDest)) {
                $templateMgr->assign('message', 'manager.plugins.deleteError');
                return false;
            }

            // Copy the plug-in from the temporary folder to the
            // target folder.
            if(!$fileManager->copyDir($path, $pluginDest)) {
                $templateMgr->assign('message', 'manager.plugins.copyError');
                return false;
            }

            // Remove the temporary folder.
            $fileManager->rmtree(dirname($path));

            $upgradeFile = $pluginDest . UPGRADE_FILE;
            if($fileManager->fileExists($upgradeFile)) {
                $params = $this->_setConnectionParams();
                $installer = new Upgrade($params, $upgradeFile, true);

                if (!$installer->execute()) {
                    $templateMgr->assign('message', ['manager.plugins.upgradeFailed', $installer->getErrorString()]);
                    return false;
                }
            }

            $installedPlugin->setCurrent(0);
            $pluginVersion->setCurrent(1);
            $versionDao->insertVersion($pluginVersion, true);

            $templateMgr->assign('category', $category);
            $templateMgr->assign('plugin', $plugin);
            $templateMgr->assign('message', ['manager.plugins.upgradeSuccessful', $pluginVersion->getVersionString()]);
            $templateMgr->assign('uploaded', true);
            $templateMgr->assign('error', false);

            return true;
        }
    }

    /**
     * Delete a plugin from the system
     * @param PKPRequest $request
     * @param string $category
     * @param string $plugin
     */
    public function _deletePlugin($request, $category, $plugin) {
        $this->validate($request);
        $templateMgr = TemplateManager::getManager();
        $this->setupTemplate(true);

        $templateMgr->assign('path', 'delete');
        $templateMgr->assign('deleted', false);
        $templateMgr->assign('error', false);

        $versionDao = DAORegistry::getDAO('VersionDAO'); /* @var $versionDao VersionDAO */
        $installedPlugin = $versionDao->getCurrentVersion('plugins.'.$category, $plugin, true);

        if ($installedPlugin) {
            $pluginDest = Core::getBaseDir() . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $category . DIRECTORY_SEPARATOR . $plugin;

            //make sure plugin type is valid and then delete the files
            if (in_array($category, PluginRegistry::getCategories())) {
                // Delete the plugin from the file system.
                $fileManager = new FileManager();
                $fileManager->rmtree($pluginDest);
            }

            if(is_dir($pluginDest)) {
                $templateMgr->assign('error', true);
                $templateMgr->assign('message', 'manager.plugins.deleteError');
            } else {
                $versionDao->disableVersion('plugins.'.$category, $plugin);
                $templateMgr->assign('deleted', true);
            }

        } else {
            $templateMgr->assign('error', true);
            $templateMgr->assign('message', 'manager.plugins.doesNotExist');
        }

        $templateMgr->assign('pageHierarchy', $this->_setBreadcrumbs($request, true, $category));
        $templateMgr->display('manager/plugins/managePlugins.tpl');
    }

    /**
     * Checks to see if local version of plugin is newer than installed version
     * @param string $productType Product type of plugin
     * @param string $productName Product name of plugin
     * @param Version $newVersion Version object of plugin to check against database
     * @return bool
     */
    public function _checkIfNewer($productType, $productName, $newVersion) {
        $versionDao = DAORegistry::getDAO('VersionDAO');
        $installedPlugin = $versionDao->getCurrentVersion($productType, $productName, true);

        if (!$installedPlugin) return false;
        if ($installedPlugin->compare($newVersion) > 0) return true;
        else return false;
    }

    /**
     * Set the page's breadcrumbs
     * @param PKPRequest $request
     * @param bool $subclass
     * @param string|null $category
     * @return array
     */
    public function _setBreadcrumbs($request, $subclass = false, $category = null) {
        $templateMgr = TemplateManager::getManager();
        $pageCrumbs = [
            [
                $request->url(null, 'user'),
                'navigation.user',
                false
            ],
            [
                $request->url(null, 'manager'),
                'manager.journalManagement',
                false
            ]
        ];

        if ($subclass) {
            $pageCrumbs[] = [
                $request->url(null, 'manager', 'plugins'),
                'manager.plugins.pluginManagement',
                false
            ];
        }

        if ($category) {
            $pageCrumbs[] = [
                $request->url(null, 'manager', 'plugins', $category),
                "plugins.categories.$category",
                false
            ];
        }

        return $pageCrumbs;
    }

    /**
     * Load database connection parameters into an array (needed for upgrade).
     * @return array
     */
    public function _setConnectionParams() {
        return [
            'clientCharset' => Config::getVar('i18n', 'client_charset'),
            'connectionCharset' => Config::getVar('i18n', 'connection_charset'),
            'databaseCharset' => Config::getVar('i18n', 'database_charset'),
            'databaseDriver' => Config::getVar('database', 'driver'),
            'databaseHost' => Config::getVar('database', 'host'),
            'databaseUsername' => Config::getVar('database', 'username'),
            'databasePassword' => Config::getVar('database', 'password'),
            'databaseName' => Config::getVar('database', 'name')
        ];
    }
}
?>
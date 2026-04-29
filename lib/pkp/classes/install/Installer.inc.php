<?php
declare(strict_types=1);

/**
 * @file classes/install/Installer.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Installer
 * @ingroup install
 *
 * @brief Base class for install and upgrade scripts.
 */

// Database installation files
define('INSTALLER_DATA_DIR', 'dbscripts/xml');

// Installer error codes
define('INSTALLER_ERROR_GENERAL', 1);
define('INSTALLER_ERROR_DB', 2);

// Default data
define('INSTALLER_DEFAULT_LOCALE', 'en_US');

import('lib.pkp.classes.db.DBDataXMLParser');
import('lib.pkp.classes.site.Version');
import('lib.pkp.classes.site.VersionDAO');
import('lib.pkp.classes.config.ConfigParser');

require_once './lib/pkp/lib/adodb/adodb-xmlschema.inc.php';

class Installer {

    /** @var string descriptor path (relative to INSTALLER_DATA_DIR) */
    public $descriptor;

    /** @var bool indicates if a plugin is being installed (thus modifying the descriptor path) */
    public $isPlugin;

    /** @var array installation parameters */
    public $params;

    /** @var Version currently installed version */
    public $currentVersion;

    /** @var Version version after installation */
    public $newVersion;

    /** @var ADOConnection|object database connection */
    public $dbconn;

    /** @var string default locale */
    public $locale;

    /** @var array available locales */
    public $installedLocales;

    /** @var DBDataXMLParser database data parser */
    public $dataXMLParser;

    /** @var array installer actions to be performed */
    public $actions;

    /** @var array SQL statements for database installation */
    public $sql;

    /** @var array installation notes */
    public $notes;

    /** @var string contents of the updated config file */
    public $configContents;

    /** @var bool indicating if config file was written or not */
    public $wroteConfig;

    /** @var int error code (null | INSTALLER_ERROR_GENERAL | INSTALLER_ERROR_DB) */
    public $errorType;

    /** @var string the error message, if an installation error has occurred */
    public $errorMsg;

    /** @var Logger|object logging object */
    public $logger;


    /**
     * Constructor.
     * @param string $descriptor descriptor path
     * @param array $params installer parameters
     * @param bool $isPlugin true iff a plugin is being installed
     */
    public function __construct($descriptor, $params = [], $isPlugin = false) {
        // Load all plugins. If any of them use installer hooks,
        // they'll need to be loaded here.
        PluginRegistry::loadAllPlugins();
        $this->isPlugin = $isPlugin;

        // Give the HookRegistry the opportunity to override this
        // method or alter its parameters.
        if (!HookRegistry::dispatch('Installer::Installer', [&$this, &$descriptor, &$params])) {
            $this->descriptor = $descriptor;
            $this->params = $params;
            $this->actions = [];
            $this->sql = [];
            $this->notes = [];
            $this->wroteConfig = true;
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Installer() {
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
     * Returns true iff this is an upgrade process.
     */
    public function isUpgrade() {
        die ('ABSTRACT CLASS');
    }

    /**
     * Destroy / clean-up after the installer.
     */
    public function destroy() {
        if (isset($this->dataXMLParser)) {
            $this->dataXMLParser->destroy();
        }

        HookRegistry::dispatch('Installer::destroy', [&$this]);
    }

    /**
     * Pre-installation.
     * @return bool
     */
    public function preInstall() {
        $this->log('pre-install');
        if (!isset($this->dbconn)) {
            // Connect to the database.
            $conn = DBConnection::getInstance();
            $this->dbconn = $conn->getDBConn();

            if (!$conn->isConnected()) {
                $this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
                return false;
            }
        }

        if (!isset($this->currentVersion)) {
            // Retrieve the currently installed version
            $versionDao = DAORegistry::getDAO('VersionDAO'); /* @var $versionDao VersionDAO */
            $this->currentVersion = $versionDao->getCurrentVersion();
        }

        if (!isset($this->locale)) {
            $this->locale = AppLocale::getLocale();
        }

        if (!isset($this->installedLocales)) {
            $this->installedLocales = array_keys(AppLocale::getAllLocales());
        }

        if (!isset($this->dataXMLParser)) {
            $this->dataXMLParser = new DBDataXMLParser();
            $this->dataXMLParser->setDBConn($this->dbconn);
        }

        $result = true;
        HookRegistry::dispatch('Installer::preInstall', [&$this, &$result]);

        return $result;
    }

    /**
     * Installation.
     * @return bool
     */
    public function execute() {
        // Ensure that the installation will not get interrupted if it takes
        // longer than max_execution_time (php.ini). Note that this does not
        // work under safe mode.
        @set_time_limit (0);

        if (!$this->preInstall()) {
            return false;
        }

        if (!$this->parseInstaller()) {
            return false;
        }

        if (!$this->executeInstaller()) {
            return false;
        }

        if (!$this->postInstall()) {
            return false;
        }

        return $this->updateVersion();
    }

    /**
     * Post-installation.
     * @return bool
     */
    public function postInstall() {
        $this->log('post-install');
        $result = true;
        HookRegistry::dispatch('Installer::postInstall', [&$this, &$result]);
        return $result;
    }


    /**
     * Record message to installation log.
     * @param string $message
     */
    public function log($message) {
        if (isset($this->logger)) {
            call_user_func([$this->logger, 'log'], $message);
        }
    }


    //
    // Main actions
    //

    /**
     * Parse the installation descriptor XML file.
     * @return bool
     */
    public function parseInstaller() {
        // Read installation descriptor file
        $this->log(sprintf('load: %s', $this->descriptor));
        $xmlParser = new PKPXMLParser();
        $installPath = $this->isPlugin ? $this->descriptor : INSTALLER_DATA_DIR . DIRECTORY_SEPARATOR . $this->descriptor;
        $installTree = $xmlParser->parse($installPath);
        if (!$installTree) {
            // Error reading installation file
            $xmlParser->destroy();
            $this->setError(INSTALLER_ERROR_GENERAL, 'installer.installFileError');
            return false;
        }

        $versionString = $installTree->getAttribute('version');
        if (isset($versionString)) {
            $this->newVersion = Version::fromString($versionString);
        } else {
            $this->newVersion = $this->currentVersion;
        }

        // Parse descriptor
        $this->parseInstallNodes($installTree);
        $xmlParser->destroy();

        $result = $this->getErrorType() == 0;

        HookRegistry::dispatch('Installer::parseInstaller', [&$this, &$result]);
        return $result;
    }

    /**
     * Execute the installer actions.
     * @return bool
     */
    public function executeInstaller() {
        $this->log(sprintf('version: %s', $this->newVersion->getVersionString()));
        foreach ($this->actions as $action) {
            if (!$this->executeAction($action)) {
                return false;
            }
        }

        $result = true;
        HookRegistry::dispatch('Installer::executeInstaller', [&$this, &$result]);

        return $result;
    }

    /**
     * Update the version number.
     * @return bool
     */
    public function updateVersion() {
        if ($this->newVersion->compare($this->currentVersion) > 0) {
            $versionDao = DAORegistry::getDAO('VersionDAO'); /* @var $versionDao VersionDAO */
            if (!$versionDao->insertVersion($this->newVersion)) {
                return false;
            }
        }

        $result = true;
        HookRegistry::dispatch('Installer::updateVersion', [&$this, &$result]);

        return $result;
    }


    //
    // Installer Parsing
    //

    /**
     * Parse children nodes in the install descriptor.
     * @param XMLNode $installTree
     */
    public function parseInstallNodes(&$installTree) {
        foreach ($installTree->getChildren() as $node) {
            switch ($node->getName()) {
                case 'schema':
                case 'data':
                case 'code':
                case 'note':
                    $this->addInstallAction($node);
                    break;
                case 'upgrade':
                    $minVersion = $node->getAttribute('minversion');
                    $maxVersion = $node->getAttribute('maxversion');
                    if ((!isset($minVersion) || $this->currentVersion->compare($minVersion) >= 0) && (!isset($maxVersion) || $this->currentVersion->compare($maxVersion) <= 0)) {
                        $this->parseInstallNodes($node);
                    }
                    break;
            }
        }
    }

    /**
     * Add an installer action from the descriptor.
     * @param XMLNode $node
     */
    public function addInstallAction(&$node) {
        $fileName = $node->getAttribute('file');

        if (!isset($fileName)) {
            $this->actions[] = ['type' => $node->getName(), 'file' => null, 'attr' => $node->getAttributes()];

        } else if (strstr($fileName, '{$installedLocale}')) {
            // Filename substitution for locales
            foreach ($this->installedLocales as $thisLocale) {
                $newFileName = str_replace('{$installedLocale}', $thisLocale, $fileName);
                $this->actions[] = ['type' => $node->getName(), 'file' => $newFileName, 'attr' => $node->getAttributes()];
            }

        } else {
            $newFileName = str_replace('{$locale}', $this->locale, $fileName);
            if (!file_exists($newFileName)) {
                // Use version from default locale if data file is not available in the selected locale
                $newFileName = str_replace('{$locale}', INSTALLER_DEFAULT_LOCALE, $fileName);
            }

            $this->actions[] = ['type' => $node->getName(), 'file' => $newFileName, 'attr' => $node->getAttributes()];
        }
    }


    //
    // Installer Execution
    //

    /**
     * Execute a single installer action.
     * @param array $action
     * @return bool
     */
    public function executeAction($action) {
        switch ($action['type']) {
            case 'schema':
                $fileName = $action['file'];
                $this->log(sprintf('schema: %s', $action['file']));

                require_once './lib/pkp/lib/adodb/adodb-xmlschema.inc.php';
                $schemaXMLParser = new adoSchema($this->dbconn);
                $dict = $schemaXMLParser->dict;
                $dict->SetCharSet($this->dbconn->charSet);
                $sql = $schemaXMLParser->parseSchema($fileName);
                $schemaXMLParser->destroy();

                if ($sql) {
                    return $this->executeSQL($sql);
                } else {
                    $this->setError(INSTALLER_ERROR_DB, str_replace('{$file}', $fileName, __('installer.installParseDBFileError')));
                    return false;
                }
                break;
            case 'data':
                $fileName = $action['file'];
                $condition = isset($action['attr']['condition']) ? $action['attr']['condition'] : null;
                $includeAction = true;
                if ($condition) {
                    // Modern replacement for create_function using eval within closure context
                    $includeAction = (function ($installer, $action) use ($condition) {
                        return eval('return ' . $condition . ';');
                    })($this, $action);
                }
                $this->log('data: ' . $action['file'] . ($includeAction ? '' : ' (skipped)'));
                if (!$includeAction) break;

                $sql = $this->dataXMLParser->parseData($fileName);
                // We might get an empty SQL if the upgrade script has
                // been executed before.
                if ($sql) {
                    return $this->executeSQL($sql);
                }
                break;
            case 'code':
                $condition = isset($action['attr']['condition']) ? $action['attr']['condition'] : null;
                $includeAction = true;
                if ($condition) {
                    $includeAction = (function ($installer, $action) use ($condition) {
                        return eval('return ' . $condition . ';');
                    })($this, $action);
                }
                $this->log(sprintf('code: %s %s::%s' . ($includeAction ? '' : ' (skipped)'), isset($action['file']) ? $action['file'] : 'Installer', isset($action['attr']['class']) ? $action['attr']['class'] : 'Installer', $action['attr']['function']));
                if (!$includeAction) return true; // Condition not met; skip the action.

                if (isset($action['file'])) {
                    require_once($action['file']);
                }
                if (isset($action['attr']['class'])) {
                    return call_user_func([$action['attr']['class'], $action['attr']['function']], $this, $action['attr']);
                } else {
                    return call_user_func([$this, $action['attr']['function']], $this, $action['attr']);
                }
                break;
            case 'note':
                $condition = isset($action['attr']['condition']) ? $action['attr']['condition'] : null;
                $includeAction = true;
                if ($condition) {
                    $includeAction = (function ($installer, $action) use ($condition) {
                        return eval('return ' . $condition . ';');
                    })($this, $action);
                }
                if (!$includeAction) break;

                $this->log(sprintf('note: %s', $action['file']));
                $this->notes[] = join('', file($action['file']));
                break;
        }

        return true;
    }

    /**
     * Execute an SQL statement.
     * @param string|array $sql
     * @return bool
     */
    public function executeSQL($sql) {
        if (is_array($sql)) {
            foreach($sql as $stmt) {
                if (!$this->executeSQL($stmt)) {
                    return false;
                }
            }
        } else {
            $this->dbconn->execute($sql);
            if ($this->dbconn->errorNo() != 0) {
                $this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
                return false;
            }
        }

        return true;
    }

    /**
     * Update the specified configuration parameters.
     * @param array $configParams
     * @return bool
     */
    public function updateConfig($configParams) {
        // Update config file
        $configParser = new ConfigParser();
        if (!$configParser->updateConfig(Config::getConfigFileName(), $configParams)) {
            // Error reading config file
            $this->setError(INSTALLER_ERROR_GENERAL, 'installer.configFileError');
            return false;
        }

        $this->configContents = $configParser->getFileContents();
        if (!$configParser->writeConfig(Config::getConfigFileName())) {
            $this->wroteConfig = false;
        }

        return true;
    }


    //
    // Accessors
    //

    /**
     * Get the value of an installation parameter.
     * @param string $name
     * @return mixed
     */
    public function getParam($name) {
        return isset($this->params[$name]) ? $this->params[$name] : null;
    }

    /**
     * Return currently installed version.
     * @return Version
     */
    public function getCurrentVersion() {
        return $this->currentVersion;
    }

    /**
     * Return new version after installation.
     * @return Version
     */
    public function getNewVersion() {
        return $this->newVersion;
    }

    /**
     * Get the set of SQL statements required to perform the install.
     * @return array
     */
    public function getSQL() {
        return $this->sql;
    }

    /**
     * Get the set of installation notes.
     * @return array
     */
    public function getNotes() {
        return $this->notes;
    }

    /**
     * Get the contents of the updated configuration file.
     * @return string
     */
    public function getConfigContents() {
        return $this->configContents;
    }

    /**
     * Check if installer was able to write out new config file.
     * @return bool
     */
    public function wroteConfig() {
        return $this->wroteConfig;
    }

    /**
     * Return the error code.
     * Valid return values are:
     * - 0 = no error
     * - INSTALLER_ERROR_GENERAL = general installation error.
     * - INSTALLER_ERROR_DB = database installation error
     * @return int
     */
    public function getErrorType() {
        return isset($this->errorType) ? $this->errorType : 0;
    }

    /**
     * The error message, if an error has occurred.
     * In the case of a database error, an unlocalized string containing the error message is returned.
     * For any other error, a localization key for the error message is returned.
     * @return string
     */
    public function getErrorMsg() {
        return $this->errorMsg;
    }

    /**
     * Return the error message as a localized string.
     * @return string.
     */
    public function getErrorString() {
        switch ($this->getErrorType()) {
            case INSTALLER_ERROR_DB:
                return 'DB: ' . $this->getErrorMsg();
            default:
                return __($this->getErrorMsg());
        }
    }

    /**
     * Set the error type and messgae.
     * @param int $type
     * @param string $msg
     */
    public function setError($type, $msg) {
        $this->errorType = $type;
        $this->errorMsg = $msg;
    }

    /**
     * Set the logger for this installer.
     * @param Logger $logger
     */
    public function setLogger($logger) {
        $this->logger = $logger;
    }

    /**
     * Clear the data cache files (needed because of direct tinkering
     * with settings tables)
     * @return bool
     */
    public function clearDataCache() {
        $cacheManager = CacheManager::getManager();
        $cacheManager->flush(null, CACHE_TYPE_FILE);
        $cacheManager->flush(null, CACHE_TYPE_OBJECT);
        return true;
    }

    /**
     * Set the current version for this installer.
     * @param Version $version
     */
    public function setCurrentVersion(&$version) {
        $this->currentVersion = $version;
    }

    /**
     * For upgrade: install email templates and data
     * @param object $installer
     * @param array $attr Attributes: array containing
     * 'key' => 'EMAIL_KEY_HERE',
     * 'locales' => 'en_US,fr_CA,...'
     * @return bool
     */
    public function installEmailTemplate($installer, $attr) {
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
        $emailTemplateDao->installEmailTemplates($emailTemplateDao->getMainEmailTemplatesFilename(), false, $attr['key']);
        foreach (explode(',', $attr['locales']) as $locale) {
            $emailTemplateDao->installEmailTemplateData($emailTemplateDao->getMainEmailTemplateDataFilename($locale), false, $attr['key']);
        }
        return true;
    }

    /**
     * Install the given filter configuration file.
     * @param string $filterConfigFile
     * @return bool true when successful, otherwise false
     */
    public function installFilterConfig($filterConfigFile) {
        static $filterHelper = false;

        // Parse the filter configuration.
        $xmlParser = new PKPXMLParser();
        $tree = $xmlParser->parse($filterConfigFile);

        // Validate the filter configuration.
        if (!$tree) {
            $xmlParser->destroy();
            return false;
        }

        // Get the filter helper.
        if ($filterHelper === false) {
            import('lib.pkp.classes.filter.FilterHelper');
            $filterHelper = new FilterHelper();
        }

        // Are there any filter groups to be installed?
        $filterGroupsNode = $tree->getChildByName('filterGroups');
        if ($filterGroupsNode instanceof XMLNode) {
            $filterHelper->installFilterGroups($filterGroupsNode);
        }

        // Are there any filters to be installed?
        $filtersNode = $tree->getChildByName('filters');
        if ($filtersNode instanceof XMLNode) {
            foreach ($filtersNode->getChildren() as $filterNode) { /* @var $filterNode XMLNode */
                $filterHelper->configureFilter($filterNode);
            }
        }

        // Get rid of the parser.
        $xmlParser->destroy();
        unset($xmlParser);

        return true;
    }

    /**
     * Check to see whether a column exists.
     * Used in installer XML in conditional checks on <data> nodes.
     * @param string $tableName
     * @param string $columnName
     * @return bool
     */
    public function columnExists($tableName, $columnName) {
        $siteDao = DAORegistry::getDAO('SiteDAO'); /* @var $siteDao SiteDAO */
        $dict = NewDataDictionary($siteDao->getDataSource());

        // Make sure the table exists
        $tables = $dict->MetaTables('TABLES', false);
        if (!in_array($tableName, $tables)) return false;

        // Check to see whether it contains the specified column.
        // Oddly, MetaColumnNames doesn't appear to be available.
        $columns = $dict->MetaColumns($tableName);
        foreach ($columns as $column) {
            if ($column->name == $columnName) return true;
        }
        return false;
    }

    /**
     * Check to see whether a table exists.
     * Used in installer XML in conditional checks on <data> nodes.
     * @param string $tableName
     * @return bool
     */
    public function tableExists($tableName) {
        $siteDao = DAORegistry::getDAO('SiteDAO'); /* @var $siteDao SiteDAO */
        $dict = NewDataDictionary($siteDao->getDataSource());

        // Check whether the table exists.
        $tables = $dict->MetaTables('TABLES', false);
        return in_array($tableName, $tables);
    }

    /**
     * Check to see whether the passed file exists.
     * @param string $filePath
     * @return bool
     */
    public function fileExists($filePath) {
        import('lib.pkp.classes.file.FileManager');
        $fileMgr = new FileManager();

        return $fileMgr->fileExists(realpath($filePath));
    }

    /**
     * Insert or update plugin data in versions
     * and plugin_settings tables.
     * @return bool
     */
    public function addPluginVersions() {
        $versionDao = DAORegistry::getDAO('VersionDAO'); /* @var $versionDao VersionDAO */
        import('lib.pkp.classes.site.VersionCheck');
        $fileManager = new FileManager();
        $categories = PluginRegistry::getCategories();
        foreach ($categories as $category) {
            PluginRegistry::loadCategory($category);
            $plugins = PluginRegistry::getPlugins($category);
            if (is_array($plugins)) {
                foreach ($plugins as $plugin) {
                    $versionFile = $plugin->getPluginPath() . '/version.xml';

                    if ($fileManager->fileExists($versionFile)) {
                        $versionInfo = VersionCheck::parseVersionXML($versionFile);
                        $pluginVersion = $versionInfo['version'];
                    } else {
                        $pluginVersion = new Version(
                            1, 0, 0, 0, // Major, minor, revision, build
                            Core::getCurrentDate(), // Date installed
                            1,    // Current
                            'plugins.'.$category, // Type
                            basename($plugin->getPluginPath()), // Product
                            '',    // Class name
                            0,    // Lazy load
                            $plugin->isSitePlugin()    // Site wide
                        );
                    }
                    $versionDao->insertVersion($pluginVersion, true);
                }
            }
        }

        return true;
    }
}
?>
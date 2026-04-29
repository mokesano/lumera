<?php
declare(strict_types=1);

/**
 * @file plugins/generic/customLocale/CustomLocalePlugin.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomLocalePlugin
 *
 * @brief This plugin enables customization of locale strings.
 * * MODERNIZED FOR PHP 7.4+ & OJS FORK
 */

define('CUSTOM_LOCALE_DIR', 'customLocale');
import('lib.pkp.classes.plugins.GenericPlugin');

class CustomLocalePlugin extends GenericPlugin {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CustomLocalePlugin() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::CustomLocalePlugin(). Please refactor to use parent::__construct().",
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Called as a plugin is registered to the registry
     * @param $category String Name of category plugin was registered to
     * @param $path String Plugin path
     * @return boolean True if plugin initialized successfully
     */
    public function register(string $category, string $path): bool {
        if (parent::register($category, $path)) {
            if ($this->getEnabled()) {
                $journal = Request::getJournal();
                
                // [WIZDAM FIX] ARSITEKTUR PRUDEN: 
                // Jika tidak ada konteks jurnal (misal di halaman utama situs admin), 
                // batalkan pemuatan locale kustom. Jangan paksakan memanggil getId().
                if (!$journal) {
                    return true;
                }

                $journalId = $journal->getId();
                $locale = AppLocale::getLocale();
                $localeFiles = AppLocale::getLocaleFiles($locale);
                
                $publicFilesDir = Config::getVar('files', 'public_files_dir');
                $customLocalePathBase = $publicFilesDir . DIRECTORY_SEPARATOR . 'journals' . DIRECTORY_SEPARATOR . $journalId . DIRECTORY_SEPARATOR . CUSTOM_LOCALE_DIR . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR;

                import('lib.pkp.classes.file.FileManager');
                $fileManager = new FileManager();
                foreach ($localeFiles as $localeFile) {
                    $customLocalePath = $customLocalePathBase . $localeFile->getFilename();
                    if ($fileManager->fileExists($customLocalePath)) {
                        AppLocale::registerLocaleFile($locale, $customLocalePath, true);
                    }
                }

                // Add custom locale data for all locale files registered after this plugin
                HookRegistry::register('PKPLocale::registerLocaleFile', array($this, 'addCustomLocale'));
            }

            return true;
        }
        return false;
    }

    /**
     * Hook callback to add custom locale files.
     * @param $hookName string
     * @param $args array [0] => locale, [1] => locale filename
     * @return boolean
     */
    public function addCustomLocale($hookName, $args) {
        $locale = $args[0];
        $localeFilename = $args[1];

        $journal = Request::getJournal();
        
        // Pengaman Null: Hentikan eksekusi jika tidak ada konteks jurnal
        if (!$journal) {
            return false; 
        }
        
        // [WIZDAM FIX] Definisikan $journalId sebelum menggunakannya
        $journalId = $journal->getId();
        
        $publicFilesDir = Config::getVar('files', 'public_files_dir');
        $customLocalePath = $publicFilesDir . DIRECTORY_SEPARATOR . 'journals' . DIRECTORY_SEPARATOR . $journalId . DIRECTORY_SEPARATOR . CUSTOM_LOCALE_DIR . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $localeFilename;

        import('lib.pkp.classes.file.FileManager');
        $fileManager = new FileManager();
        if ($fileManager->fileExists($customLocalePath)) {
            AppLocale::registerLocaleFile($locale, $customLocalePath, false);
        }

        return true;
    }

    /**
     * Get display name
     * @return string
     */
    public function getDisplayName(): string {
        return __('plugins.generic.customLocale.name');
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription(): string {
        return __('plugins.generic.customLocale.description');
    }

    /**
     * Extend the {url ...} smarty to support this plugin.
     * @param $params array
     * @param $smarty Smarty
     * @return string
     */
    public function smartyPluginUrl(array $params, $smarty): string {
        $path = array($this->getCategory(), $this->getName());
        if (is_array($params['path'])) {
            $params['path'] = array_merge($path, $params['path']);
        } elseif (!empty($params['path'])) {
            $params['path'] = array_merge($path, array($params['path']));
        } else {
            $params['path'] = $path;
        }

        if (!empty($params['key'])) {
            $params['path'] = array_merge($params['path'], array($params['key']));
            unset($params['key']);
        }

        if (!empty($params['file'])) {
            $params['path'] = array_merge($params['path'], array($params['file']));
            unset($params['file']);
        }

        return $smarty->smartyUrl($params, $smarty);
    }

    /**
     * Display verbs for the management interface.
     * @param $verbs array
     * @param $request PKPRequest
     * @return array
     */
    public function getManagementVerbs(array $verbs = [], $request = null): array {
        $verbs = parent::getManagementVerbs($verbs, $request);

        if ($this->getEnabled($request)) {
            $verbs[] = array('index', __('plugins.generic.customLocale.customize'));
        }
        
        return $verbs;
    }

    /**
     * Execute a management verb on this plugin
     * @param $verb string
     * @param $args array
     * @param $message string Result status message
     * @param $messageParams array Parameters for the message key
     * @return boolean
     */
    public function manage(string $verb, array $args, string $message, array $messageParams, $request = NULL): bool {
        if (!parent::manage($verb, $args, $message, $messageParams, $request)) return false;

        $this->import('CustomLocaleHandler');
        $customLocaleHandler = new CustomLocaleHandler($this->getName());
        switch ($verb) {
            case 'edit':
                $customLocaleHandler->edit($args);
                return true;
            case 'saveLocaleChanges':
                $customLocaleHandler->saveLocaleChanges($args);
                return true;
            case 'editLocaleFile':
                $customLocaleHandler->editLocaleFile($args);
                return true;
            case 'saveLocaleFile':
                $customLocaleHandler->saveLocaleFile($args);
                return true;
            default:
                $customLocaleHandler->index();
                return true;
        }
    }
}
?>
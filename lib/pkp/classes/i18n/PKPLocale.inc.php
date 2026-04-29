<?php
declare(strict_types=1);

/**
 * @defgroup i18n
 */

/**
 * @file classes/i18n/PKPLocale.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PKPLocale
 * @ingroup i18n
 *
 * @brief Provides methods for loading locale data and translating strings identified by unique keys
 * WIZDAM EDITION: PHP 8 Compatibility, Registry State Fixes
 */

import('lib.pkp.classes.i18n.LocaleFile');

if (!defined('LOCALE_REGISTRY_FILE')) {
    define('LOCALE_REGISTRY_FILE', Config::getVar('general', 'registry_dir') . DIRECTORY_SEPARATOR . 'locales.xml');
}
if (!defined('LOCALE_DEFAULT')) {
    define('LOCALE_DEFAULT', Config::getVar('i18n', 'locale'));
}
if (!defined('LOCALE_ENCODING')) {
    define('LOCALE_ENCODING', Config::getVar('i18n', 'client_charset'));
}

define('MASTER_LOCALE', 'en_US');

// Error types for locale checking.
define('LOCALE_ERROR_MISSING_KEY', 'LOCALE_ERROR_MISSING_KEY');
define('LOCALE_ERROR_EXTRA_KEY', 'LOCALE_ERROR_EXTRA_KEY');
define('LOCALE_ERROR_DIFFERING_PARAMS', 'LOCALE_ERROR_DIFFERING_PARAMS');
define('LOCALE_ERROR_MISSING_FILE', 'LOCALE_ERROR_MISSING_FILE');

define('EMAIL_ERROR_MISSING_EMAIL', 'EMAIL_ERROR_MISSING_EMAIL');
define('EMAIL_ERROR_EXTRA_EMAIL', 'EMAIL_ERROR_EXTRA_EMAIL');
define('EMAIL_ERROR_DIFFERING_PARAMS', 'EMAIL_ERROR_DIFFERING_PARAMS');

// Locale components
define('LOCALE_COMPONENT_CORE_COMMON', 0x00000001);
define('LOCALE_COMPONENT_CORE_ADMIN', 0x00000002);
define('LOCALE_COMPONENT_CORE_INSTALLER', 0x00000003);
define('LOCALE_COMPONENT_CORE_MANAGER', 0x00000004);
define('LOCALE_COMPONENT_CORE_READER', 0x00000005);
define('LOCALE_COMPONENT_CORE_SUBMISSION', 0x00000006);
define('LOCALE_COMPONENT_CORE_USER', 0x00000007);
define('LOCALE_COMPONENT_CORE_GRID', 0x00000008);
define('LOCALE_COMPONENT_CORE_METADATA', 0x00000009);

class PKPLocale {

    /**
     * Constructor.
     */
    public function __construct() {
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function PKPLocale() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::PKPLocale(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get a list of locale files currently registered, either in all
     * locales (in an array for each locale), or for a specific locale.
     * @param $locale string Locale identifier (optional)
     * @return array
     */
    public static function getLocaleFiles($locale = null) {
        // [Wizdam] Fetch by value (Copy)
        $localeFiles = Registry::get('localeFiles', true, array());
        
        if ($locale !== null) {
            if (!isset($localeFiles[$locale])) {
                // Return empty array if not set, but DON'T modify registry here 
                // (Getters shouldn't have side effects ideally, but if needed, calling code handles it)
                return array();
            }
            return $localeFiles[$locale];
        }
        return $localeFiles;
    }

    /**
     * Translate a string using the selected locale.
     * Substitution works by replacing tokens like "{$foo}" with the value
     * of the parameter named "foo" (if supplied).
     * @param $key string
     * @param $params array named substitution parameters
     * @param $locale string the locale to use
     * @return string
     */
    public static function translate($key, $params = array(), $locale = null) {
        if (!isset($locale)) $locale = AppLocale::getLocale();
        if (($key = trim((string)$key)) == '') return '';

        $localeFiles = AppLocale::getLocaleFiles($locale);
        $value = '';
        
        // Loop backwards through files (LIFO)
        for ($i = count($localeFiles) - 1 ; $i >= 0 ; $i --) {
            $value = $localeFiles[$i]->translate($key, $params);
            if ($value !== null) {
                // Translation found
                break; // Break loop, but still need to call hook below
            }
        }
        
        // If value is still null/empty (not found), default it
        if ($value === null) $value = '';

        // [Wizdam] Debug Notes Logic (Fetch-Modify-Set)
        if ($value === '') {
            $notes = Registry::get('system.debug.notes', true, array());
            $notes[] = array('debug.notes.missingLocaleKey', array('key' => $key));
            Registry::set('system.debug.notes', $notes);
        }

        // HookRegistry::dispatch
        // Keep & for primitives ($key, $params, $locale, $value) so plugins can modify translation
        // $localeFiles is array of objects, passed by reference in array to be safe with legacy plugins
        if (!HookRegistry::dispatch('PKPLocale::translate', array(&$key, &$params, &$locale, &$localeFiles, &$value))) {
            if ($value === '') {
                 // Add some octothorpes to missing keys to make them more obvious
                 return '##' . htmlentities($key) . '##';
            }
            return $value;
        } else {
            return $value;
        }
    }

    /**
     * Initialize the locale system.
     */
    public static function initialize() {
        // Use defaults if locale info unspecified.
        $locale = AppLocale::getLocale();

        $sysLocale = $locale . '.' . LOCALE_ENCODING;
        if (!@setlocale(LC_ALL, $sysLocale, $locale)) {
            // For PHP < 4.3.0
            if(setlocale(LC_ALL, $sysLocale) != $sysLocale) {
                setlocale(LC_ALL, $locale);
            }
        }

        AppLocale::registerLocaleFile($locale, "lib/pkp/locale/$locale/common.xml");
    }

    /**
     * Build an associative array of LOCALE_COMPOMENT_... => filename
     * (use getFilenameComponentMap instead)
     * @param $locale string
     * @return array
     */
    public static function makeComponentMap($locale) {
        $baseDir = "lib/pkp/locale/$locale/";

        return array(
            LOCALE_COMPONENT_CORE_COMMON => $baseDir . 'common.xml',
            LOCALE_COMPONENT_CORE_ADMIN => $baseDir . 'admin.xml',
            LOCALE_COMPONENT_CORE_INSTALLER => $baseDir . 'installer.xml',
            LOCALE_COMPONENT_CORE_MANAGER => $baseDir . 'manager.xml',
            LOCALE_COMPONENT_CORE_READER => $baseDir . 'reader.xml',
            LOCALE_COMPONENT_CORE_SUBMISSION => $baseDir . 'submission.xml',
            LOCALE_COMPONENT_CORE_USER => $baseDir . 'user.xml',
            LOCALE_COMPONENT_CORE_GRID => $baseDir . 'grid.xml',
            LOCALE_COMPONENT_CORE_METADATA => $baseDir . 'metadata.xml'
        );
    }

    /**
     * Get an associative array of LOCALE_COMPOMENT_... => filename
     * @param $locale string
     * @return array
     */
    public static function getFilenameComponentMap($locale) {
        $filenameComponentMap = Registry::get('localeFilenameComponentMap', true, array());
        if (!isset($filenameComponentMap[$locale])) {
            $filenameComponentMap[$locale] = AppLocale::makeComponentMap($locale);
            // [Wizdam] Update Registry
            Registry::set('localeFilenameComponentMap', $filenameComponentMap);
        }
        return $filenameComponentMap[$locale];
    }

    /**
     * Load a set of locale components. Parameters of mixed length may
     * be supplied, each a LOCALE_COMPONENT_... constant. An optional final
     * parameter may be supplied to specify the locale (e.g. 'en_US').
     */
    public static function requireComponents() {
        $params = func_get_args();
        $paramCount = count($params);
        if ($paramCount === 0) return;

        // Get the locale
        $lastParam = $params[$paramCount-1];
        if (is_string($lastParam)) {
            $locale = $lastParam;
            $paramCount--;
        } else {
            $locale = AppLocale::getLocale();
        }

        // Backwards compatibility: the list used to be supplied
        // as an array in the first parameter.
        if (is_array($params[0])) {
            $params = $params[0];
            $paramCount = count($params);
        }

        // Go through and make sure each component is loaded if valid.
        // [Wizdam] Fetch array copy
        $loadedComponents = Registry::get('loadedLocaleComponents', true, array());
        $filenameComponentMap = AppLocale::getFilenameComponentMap($locale);
        
        $hasChanges = false; // Track changes to update registry only once

        for ($i=0; $i<$paramCount; $i++) {
            $component = $params[$i];

            // Don't load components twice
            if (isset($loadedComponents[$locale][$component])) continue;

            // Validate component
            if (!isset($filenameComponentMap[$component])) {
                fatalError('Unknown locale component ' . $component);
            }

            $filename = $filenameComponentMap[$component];
            AppLocale::registerLocaleFile($locale, $filename);
            
            $loadedComponents[$locale][$component] = true;
            $hasChanges = true;
        }

        if ($hasChanges) {
            Registry::set('loadedLocaleComponents', $loadedComponents);
        }
    }

    /**
     * Register a locale file against the current list.
     * @param $locale string Locale key
     * @param $filename string Filename to new locale XML file
     * @param $addToTop boolean Whether to add to the top of the list (true)
     * or the bottom (false). Allows overriding.
     */
    public static function registerLocaleFile ($locale, $filename, $addToTop = false) {
        // [Wizdam] 1. Get complete registry array
        $allLocaleFiles = Registry::get('localeFiles', true, array());
        
        // Ensure locale key exists
        if (!isset($allLocaleFiles[$locale])) {
            $allLocaleFiles[$locale] = array();
        }

        $localeFile = new LocaleFile($locale, $filename);
        
        // HOOK: PKPLocale::registerLocaleFile::isValidLocaleFile
        // $localeFile is Object, passed by handle (no & needed)
        if (!HookRegistry::dispatch('PKPLocale::registerLocaleFile::isValidLocaleFile', array($localeFile))) {
            if (!$localeFile->isValid()) {
                return null;
            }
        }

        if ($addToTop) {
            array_unshift($allLocaleFiles[$locale], $localeFile);
        } else {
            $allLocaleFiles[$locale][] = $localeFile;
        }

        // [Wizdam] 2. Save modified array back to Registry
        Registry::set('localeFiles', $allLocaleFiles);

        // HOOK: PKPLocale::registerLocaleFile
        // Primitives need &
        HookRegistry::dispatch('PKPLocale::registerLocaleFile', array(&$locale, &$filename, &$addToTop));
        
        return $localeFile;
    }

    /**
     * Get the stylesheet filename for a particular locale.
     * @param $locale string
     * @return string or null if none configured.
     */
    public static function getLocaleStyleSheet($locale) {
        $contents = AppLocale::_getAllLocalesCacheContent();
        if (isset($contents[$locale]['stylesheet'])) {
            return $contents[$locale]['stylesheet'];
        }
        return null;
    }

    /**
     * Determine whether or not a locale is marked incomplete.
     * @param $locale xx_XX symbolic name of locale to check
     * @return boolean
     */
    public static function isLocaleComplete($locale) {
        $contents = AppLocale::_getAllLocalesCacheContent();
        if (!isset($contents[$locale])) return false;
        if (isset($contents[$locale]['complete']) && $contents[$locale]['complete'] == 'false') {
            return false;
        }
        return true;
    }

    /**
     * Check if the supplied locale is currently installable.
     * @param $locale string
     * @return boolean
     */
    public static function isLocaleValid($locale) {
        if (empty($locale)) return false;
    
        // [WIZDAM FIX] Mendukung tiga format locale:
        //   xx_XX       → ISO 639-1 + region    (id_ID, en_US, de_DE)
        //   xxx_XX      → ISO 639-3 + region    (arn_CL, tpi_PG, sah_RU)
        //   xx_Xxxx     → ISO 639-1 + script    (zh_Hans, zh_Hant)
        //   xx_Xxxx_XX  → ISO 639-1 + script + region (zh_Hant_HK, zh_Hans_SG)
        if (!preg_match('/^[a-z]{2,3}(_[A-Z][a-z]{3})?(_[A-Z]{2})?$/', $locale)) {
            return false;
        }
    
        // Minimal harus ada satu subtag setelah language code
        if (!str_contains($locale, '_')) return false;
    
        if (file_exists('locale/' . $locale)) return true;
        return false;
    }

    /**
     * Load a locale list from a file.
     * @param $filename string
     * @return array
     */
    public static function loadLocaleList($filename) {
        $xmlDao = new XMLDAO();
        $data = $xmlDao->parseStruct($filename, array('locale'));
        $allLocales = array();

        // Build array with ($localKey => $localeName)
        if (isset($data['locale'])) {
            foreach ($data['locale'] as $localeData) {
                $allLocales[$localeData['attributes']['key']] = $localeData['attributes'];
            }
        }

        return $allLocales;
    }

    /**
     * Return a list of all available locales.
     * @return array
     */
    public static function getAllLocales() {
        $rawContents = AppLocale::_getAllLocalesCacheContent();
        $allLocales = array();

        foreach ($rawContents as $locale => $contents) {
            $allLocales[$locale] = $contents['name'];
        }

        // if client encoding is set to iso-8859-1, transcode locales from utf8
        if (LOCALE_ENCODING == "iso-8859-1") {
            $allLocales = array_map('utf8_decode', $allLocales);
        }

        return $allLocales;
    }

    /**
     * Install support for a new locale.
     * @param $locale string
     */
    public static function installLocale($locale) {
        // Install default locale-specific data
        import('lib.pkp.classes.db.DBDataXMLParser');

        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplateDao->installEmailTemplateData($emailTemplateDao->getMainEmailTemplateDataFilename($locale));

        // Load all plugins so they can add locale data if needed
        $categories = PluginRegistry::getCategories();
        foreach ($categories as $category) {
            PluginRegistry::loadCategory($category);
        }
        // HOOK: PKPLocale::installLocale
        HookRegistry::dispatch('PKPLocale::installLocale', array(&$locale));
    }

    /**
     * Uninstall support for an existing locale.
     * @param $locale string
     */
    public static function uninstallLocale($locale) {
        // Delete locale-specific data
        $emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
        $emailTemplateDao->deleteEmailTemplatesByLocale($locale);
        $emailTemplateDao->deleteDefaultEmailTemplatesByLocale($locale);
    }

    /**
     * Reload locale-specific data.
     * @param $locale string
     */
    public static function reloadLocale($locale) {
        AppLocale::uninstallLocale($locale);
        AppLocale::installLocale($locale);
    }

    /**
     * Given a locale string, get the list of parameter references of the
     * form {$myParameterName}.
     * @param $source string
     * @return array
     */
    public static function getParameterNames($source) {
        $matches = null;
        PKPString::regexp_match_all('/({\$[^}]+})/' /* '/{\$[^}]+})/' */, $source, $matches);
        array_shift($matches); // Knock the top element off the array
        if (isset($matches[0])) return $matches[0];
        return array();
    }

    /**
     * Translate the ISO 2-letter language string (ISO639-1)
     * into a ISO compatible 3-letter string (ISO639-2b).
     * @param $iso2Letter string
     * @return string the translated string or null if we
     * don't know about the given language.
     */
    public static function get3LetterFrom2LetterIsoLanguage($iso2Letter) {
        // Validation/Assertion
        if (strlen($iso2Letter) != 2) return null;

        $locales = AppLocale::_getAllLocalesCacheContent();
        foreach($locales as $locale => $localeData) {
            if (substr($locale, 0, 2) == $iso2Letter) {
                if(isset($localeData['iso639-2b'])) {
                    return $localeData['iso639-2b'];
                }
            }
        }
        return null;
    }

    /**
     * Translate the ISO 3-letter language string (ISO639-2b)
     * into a ISO compatible 2-letter string (ISO639-1).
     * @param $iso3Letter string
     * @return string the translated string or null if we
     * don't know about the given language.
     */
    public static function get2LetterFrom3LetterIsoLanguage($iso3Letter) {
        if (strlen($iso3Letter) != 3) return null;

        $locales = AppLocale::_getAllLocalesCacheContent();
        foreach($locales as $locale => $localeData) {
            if (isset($localeData['iso639-2b']) && $localeData['iso639-2b'] == $iso3Letter) {
                return substr($locale, 0, 2);
            }
        }
        return null;
    }

    /**
     * Translate the PKP locale identifier into an
     * ISO639-2b compatible 3-letter string.
     * @param $locale string
     * @return string
     */
    public static function get3LetterIsoFromLocale($locale) {
        if (strlen($locale) != 5) return ''; // Fail safe
        $iso2Letter = substr($locale, 0, 2);
        return AppLocale::get3LetterFrom2LetterIsoLanguage($iso2Letter);
    }

    /**
     * Translate an ISO639-2b compatible 3-letter string
     * into the PKP locale identifier.
     * @param $iso3letter string
     * @return string
     */
    public static function getLocaleFrom3LetterIso($iso3Letter) {
        if (strlen($iso3Letter) != 3) return null;

        $primaryLocale = AppLocale::getPrimaryLocale();
        $localeCandidates = array();
        $locales = AppLocale::_getAllLocalesCacheContent();
        
        foreach($locales as $locale => $localeData) {
            if (isset($localeData['iso639-2b']) && $localeData['iso639-2b'] == $iso3Letter) {
                if ($locale == $primaryLocale) {
                    return $primaryLocale;
                }
                $localeCandidates[] = $locale;
            }
        }

        if (empty($localeCandidates)) return null;

        if (count($localeCandidates) > 1) {
            $supportedLocales = AppLocale::getSupportedLocales();
            foreach($supportedLocales as $supportedLocale => $localeName) {
                if (in_array($supportedLocale, $localeCandidates)) return $supportedLocale;
            }
        }

        return array_shift($localeCandidates);
    }

    /**
     * Translate the ISO 2-letter language string (ISO639-1) into ISO639-3.
     * @param $iso1 string
     * @return string the translated string or null
     */
    public static function getIso3FromIso1($iso1) {
        if (strlen($iso1) != 2) return null;

        $locales = AppLocale::_getAllLocalesCacheContent();
        foreach($locales as $locale => $localeData) {
            if (substr($locale, 0, 2) == $iso1) {
                if(isset($localeData['iso639-3'])) {
                    return $localeData['iso639-3'];
                }
            }
        }
        return null;
    }

    /**
     * Translate the ISO639-3 into ISO639-1.
     * @param $iso3 string
     * @return string the translated string or null
     */
    public static function getIso1FromIso3($iso3) {
        if (strlen($iso3) != 3) return null;

        $locales = AppLocale::_getAllLocalesCacheContent();
        foreach($locales as $locale => $localeData) {
            if (isset($localeData['iso639-3']) && $localeData['iso639-3'] == $iso3) {
                return substr($locale, 0, 2);
            }
        }
        return null;
    }

    /**
     * Translate the PKP locale identifier into an
     * ISO639-3 compatible 3-letter string.
     * @param $locale string
     * @return string
     */
    public static function getIso3FromLocale($locale) {
        if (strlen($locale) != 5) return '';
        $iso1 = substr($locale, 0, 2);
        return AppLocale::getIso3FromIso1($iso1);
    }

    /**
    * Translate the PKP locale identifier into an
    * ISO639-1 compatible 2-letter string.
    * @param $locale string
    * @return string
    */
    public static function getIso1FromLocale($locale) {
        if (strlen($locale) != 5) return '';
        return substr($locale, 0, 2);
    }

    /**
     * Translate an ISO639-3 compatible 3-letter string
     * into the PKP locale identifier.
     * @param $iso3 string
     * @return string
     */
    public static function getLocaleFromIso3($iso3) {
        if (strlen($iso3) != 3) return null;
        $primaryLocale = AppLocale::getPrimaryLocale();

        $localeCandidates = array();
        $locales = AppLocale::_getAllLocalesCacheContent();
        foreach($locales as $locale => $localeData) {
            if (isset($localeData['iso639-3']) && $localeData['iso639-3'] == $iso3) {
                if ($locale == $primaryLocale) {
                    return $primaryLocale;
                }
                $localeCandidates[] = $locale;
            }
        }

        if (empty($localeCandidates)) return null;

        if (count($localeCandidates) > 1) {
            $supportedLocales = AppLocale::getSupportedLocales();
            foreach($supportedLocales as $supportedLocale => $localeName) {
                if (in_array($supportedLocale, $localeCandidates)) return $supportedLocale;
            }
        }

        return array_shift($localeCandidates);
    }

    //
    // Private helper methods.
    //
    /**
     * Retrieves locale data from the locales cache.
     * @return array
     */
    public static function _getAllLocalesCacheContent() {
        static $contents = false;
        if ($contents === false) {
            // [Wizdam] Fetch cache object handle
            $allLocalesCache = AppLocale::_getAllLocalesCache();
            $contents = $allLocalesCache->getContents();
        }
        return $contents;
    }

    /**
     * Get the cache object for the current list of all locales.
     * @return FileCache
     */
    public static function _getAllLocalesCache() {
        // [Wizdam] Fetch by handle
        $cache = Registry::get('allLocalesCache', true, null);
        if ($cache === null) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                'locale', 'list',
                array('AppLocale', '_allLocalesCacheMiss')
            );

            // Check to see if the data is outdated
            $cacheTime = $cache->getCacheTime();
            if ($cacheTime !== null && $cacheTime < filemtime(LOCALE_REGISTRY_FILE)) {
                $cache->flush();
            }
            
            // [Wizdam] Update Registry
            Registry::set('allLocalesCache', $cache);
        }
        return $cache;
    }

    /**
     * Create a cache file with locale data AND Auto-Heal locales.xml atomically
     * Compatible & Optimized for PHP 7.4 up to PHP 8.4+
     * @param $cache CacheManager
     * @param $id the cache id
     */
    public static function _allLocalesCacheMiss($cache, $id) {
        $allLocales = Registry::get('allLocales', true, null);
        if ($allLocales === null) {
            // [Wizdam] Fetch-Modify-Set pattern for debug notes
            $notes = Registry::get('system.debug.notes', true, array());
            $notes[] = array('debug.notes.localeListLoad', array('localeList' => LOCALE_REGISTRY_FILE));
            Registry::set('system.debug.notes', $notes);

            // 1. Baca daftar bahasa dari file untuk Cache
            $allLocales = AppLocale::loadLocaleList(LOCALE_REGISTRY_FILE);

            // [WIZDAM] NATIVE SELF-HEALING REGISTRY ENGINE (PHP 8.4 READY)
            $registryNeedsUpdate = false;
            
            // Tahan error XML agar tidak memicu PHP Warning/Fatal Error
            $useErrors = libxml_use_internal_errors(true);
            
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            
            if ($dom->load(LOCALE_REGISTRY_FILE)) {
                $localeNodes = $dom->getElementsByTagName('locale');
                
                foreach ($localeNodes as $node) {
                    $localeKey = $node->getAttribute('key');
                    
                    if ($localeKey === MASTER_LOCALE) continue;

                    $isComplete = self::_auditLocaleCompleteness($localeKey);
                    $hasCompleteAttr = $node->hasAttribute('complete');
                    
                    // getAttribute mengembalikan string kosong ("") jika tidak ada atribut,
                    // bukan null, sehingga aman untuk strict types PHP 8.1+
                    $currentAttrValue = $hasCompleteAttr ? $node->getAttribute('complete') : '';

                    if (!$isComplete) {
                        if (!$hasCompleteAttr || $currentAttrValue !== 'false') {
                            $node->setAttribute('complete', 'false');
                            $allLocales[$localeKey]['complete'] = 'false';
                            $registryNeedsUpdate = true;
                        }
                    } else {
                        if ($hasCompleteAttr) {
                            $node->removeAttribute('complete');
                            unset($allLocales[$localeKey]['complete']);
                            $registryNeedsUpdate = true;
                        }
                    }
                }

                // Atomic Write menggunakan LOCK_EX untuk keamanan konkurensi data
                if ($registryNeedsUpdate) {
                    $xmlString = $dom->saveXML();
                    if ($xmlString !== false) {
                        file_put_contents(LOCALE_REGISTRY_FILE, $xmlString, LOCK_EX);
                    }
                }
            }
            
            // Kembalikan state libxml ke awal untuk mencegah memory leak
            libxml_clear_errors();
            libxml_use_internal_errors($useErrors);
            // ========================================================

            ksort($allLocales);
            $cache->setEntireCache($allLocales);
            
            // [Wizdam] Update Registry
            Registry::set('allLocales', $allLocales);
        }
        return null;
    }

    /**
     * [WIZDAM] Smart Audit: Evaluate if a locale is structurally complete.
     * ULTRA-OPTIMIZED: Hanya melakukan Recursive Scan 1 kali per request 
     * menggunakan Static Cache. PHP 7.4 - 8.4 Optimized.
     * @param $targetLocale string
     * @return boolean
     */
    private static function _auditLocaleCompleteness($targetLocale) {
        $masterLocale = MASTER_LOCALE;
        
        // STATIC CACHE: Simpan daftar lengkap file Master di RAM
        static $masterFilesCache = null;

        // 1. LAKUKAN SCAN HARD DRIVE HANYA 1 KALI UNTUK MASTER LOCALE
        if ($masterFilesCache === null) {
            $masterFilesCache = array();
            $baseDir = Core::getBaseDir(); 
            
            $directoryIterator = new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS);
            $filterIterator = new RecursiveCallbackFilterIterator($directoryIterator, function ($current, $key, $iterator) {
                if ($current->isDir()) {
                    // Bypass folder berat yang pasti tidak berisi file locale
                    $excludeDirs = array('cache', 'public', 'files', 'upload', '.git', 'vendor', 'node_modules');
                    if (in_array($current->getFilename(), $excludeDirs, true)) {
                        return false; 
                    }
                }
                return true;
            });

            $iterator = new RecursiveIteratorIterator($filterIterator, RecursiveIteratorIterator::SELF_FIRST);

            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isDir() && $fileinfo->getFilename() === $masterLocale) {
                    $parentDirName = basename(dirname($fileinfo->getPathname()));
                    if ($parentDirName !== 'locale') continue;

                    $masterDirPath = $fileinfo->getPathname();
                    $files = scandir($masterDirPath);
                    if ($files !== false) {
                        foreach ($files as $file) {
                            if (pathinfo($file, PATHINFO_EXTENSION) === 'xml') {
                                // Simpan path absolut setiap file XML Master
                                $masterFilesCache[] = $masterDirPath . DIRECTORY_SEPARATOR . $file;
                            }
                        }
                    }
                }
            }
        }

        // 2. AUDIT TARGET LOCALE (SANGAT CEPAT, HANYA CEK KEBERADAAN FILE)
        $ds = DIRECTORY_SEPARATOR;
        foreach ($masterFilesCache as $masterFilePath) {
            // Kalkulasi path target dengan mereplace '/en_US/' menjadi target
            $targetFilePath = str_replace(
                $ds . $masterLocale . $ds, 
                $ds . $targetLocale . $ds, 
                $masterFilePath
            );

            // Jika XML di Master ada, tapi di Target tidak terbaca, status: Incomplete
            if (is_readable($masterFilePath) && !is_readable($targetFilePath)) {
                return false; 
            }
        }

        return true; 
    }
}

/**
 * Wrapper around PKPLocale::translate().
 * @param $key string
 * @param $params array named substitution parameters
 * @param $locale string the locale to use
 * @return string
 */
function __($key, $params = array(), $locale = null) {
    return AppLocale::translate($key, $params, $locale);
}

?>
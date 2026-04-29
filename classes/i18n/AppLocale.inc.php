<?php
declare(strict_types=1);

/**
 * @file classes/i18n/AppLocale.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AppLocale
 * @ingroup i18n
 *
 * @brief Provides methods for loading locale data and translating strings identified by unique keys
 * WIZDAM EDITION: PHP 8 Compatibility (Static Methods)
 */

import('lib.pkp.classes.i18n.PKPLocale');

define('LOCALE_COMPONENT_APPLICATION_COMMON', 0x00000101);
define('LOCALE_COMPONENT_APP_AUTHOR',         0x00000102);
define('LOCALE_COMPONENT_APP_EDITOR',         0x00000103);
define('LOCALE_COMPONENT_APP_MANAGER',        0x00000104);
define('LOCALE_COMPONENT_APP_ADMIN',          0x00000105);
define('LOCALE_COMPONENT_APP_DEFAULT',        0x00000106);

// Konstanta OJS Baru (Hasil pecahan/refactoring dari locale.xml)
define('LOCALE_COMPONENT_APP_PAYMENT',        0x00000107);
define('LOCALE_COMPONENT_APP_AUTHORIZATION',  0x00000108);
define('LOCALE_COMPONENT_APP_NOTIFICATION',   0x00000109);
define('LOCALE_COMPONENT_APP_READING_TOOLS',  0x0000010A);
define('LOCALE_COMPONENT_APP_LOG',            0x0000010B);
define('LOCALE_COMPONENT_APP_USER',           0x0000010C);
define('LOCALE_COMPONENT_APP_SUBMISSION',     0x0000010D);
define('LOCALE_COMPONENT_APP_EDITORIAL',      0x0000010E);

class AppLocale extends PKPLocale {

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function AppLocale() {
        trigger_error(
            "Class '" . get_class($this) . "' uses deprecated constructor parent::AppLocale(). Please refactor to parent::__construct().", 
            E_USER_DEPRECATED
        );
        self::__construct();
    }

    /**
     * Get all supported UI locales for the current context.
     * @return array
     */
    public static function getSupportedLocales() {
        static $supportedLocales;
        if (!isset($supportedLocales)) {
            if (defined('SESSION_DISABLE_INIT') || !Config::getVar('general', 'installed')) {
                $supportedLocales = AppLocale::getAllLocales();
            } elseif (($journal = Request::getJournal())) {
                $supportedLocales = $journal->getSupportedLocaleNames();
            } else {
                $site = Request::getSite();
                $supportedLocales = $site->getSupportedLocaleNames();
            }
        }
        return $supportedLocales;
    }

    /**
     * Get all supported form locales for the current context.
     * @return array
     */
    public static function getSupportedFormLocales() {
        static $supportedFormLocales;
        if (!isset($supportedFormLocales)) {
            if (defined('SESSION_DISABLE_INIT') || !Config::getVar('general', 'installed')) {
                $supportedFormLocales = AppLocale::getAllLocales();
            } elseif (($journal = Request::getJournal())) {
                $supportedFormLocales = $journal->getSupportedFormLocaleNames();
            } else {
                $site = Request::getSite();
                $supportedFormLocales = $site->getSupportedLocaleNames();
            }
        }
        return $supportedFormLocales;
    }

    /**
     * Return the key name of the user's currently selected locale (default
     * is "en_US" for U.S. English).
     * @return string
     */
    public static function getLocale() {
        static $currentLocale;
        if (!isset($currentLocale)) {
            if (defined('SESSION_DISABLE_INIT') || !Config::getVar('general', 'installed')) {
                // If the locale is specified in the URL, allow
                // it to override. (Necessary when locale is
                // being set, as cookie will not yet be re-set)
                $locale = Request::getUserVar('setLocale');
                
                // PHP 8 Safety: keys are strings
                $supportedKeys = array_keys(AppLocale::getSupportedLocales());
                if (empty($locale) || !in_array($locale, $supportedKeys)) {
                    $locale = Request::getCookieVar('currentLocale');
                }
            } else {
                $sessionManager = SessionManager::getManager();
                $session = $sessionManager->getUserSession();
                $locale = Request::getUserVar('uiLocale');

                $journal = Request::getJournal();
                $site = Request::getSite();

                if (!isset($locale)) {
                    $locale = $session->getSessionVar('currentLocale');
                }

                if (!isset($locale)) {
                    $locale = Request::getCookieVar('currentLocale');
                }

                if (isset($locale)) {
                    // Check if user-specified locale is supported
                    if ($journal != null) {
                        $locales = $journal->getSupportedLocaleNames();
                    } else {
                        $locales = $site->getSupportedLocaleNames();
                    }

                    if (!in_array($locale, array_keys($locales))) {
                        unset($locale);
                    }
                }

                if (!isset($locale)) {
                    // Use journal/site default
                    if ($journal != null) {
                        $locale = $journal->getPrimaryLocale();
                    }

                    if (!isset($locale)) {
                        $locale = $site->getPrimaryLocale();
                    }
                }
            }

            if (!AppLocale::isLocaleValid($locale)) {
                $locale = LOCALE_DEFAULT;
            }

            $currentLocale = $locale;
        }
        return $currentLocale;
    }

    /**
     * Get the stack of "important" locales, most important first.
     * @return array
     */
    public static function getLocalePrecedence() {
        static $localePrecedence;
        if (!isset($localePrecedence)) {
            $localePrecedence = array(AppLocale::getLocale());

            $journal = Request::getJournal();
            if ($journal && !in_array($journal->getPrimaryLocale(), $localePrecedence)) {
                $localePrecedence[] = $journal->getPrimaryLocale();
            }

            $site = Request::getSite();
            if ($site && !in_array($site->getPrimaryLocale(), $localePrecedence)) {
                $localePrecedence[] = $site->getPrimaryLocale();
            }
        }
        return $localePrecedence;
    }

    /**
     * Retrieve the primary locale of the current context.
     * @return string
     */
    public static function getPrimaryLocale() {
        static $locale;
        if ($locale) return $locale;

        if (defined('SESSION_DISABLE_INIT') || !Config::getVar('general', 'installed')) {
            return $locale = LOCALE_DEFAULT;
        }

        $journal = Request::getJournal();

        if (isset($journal)) {
            $locale = $journal->getPrimaryLocale();
        }

        if (!isset($locale)) {
            $site = Request::getSite();
            $locale = $site->getPrimaryLocale();
        }

        if (!isset($locale) || !AppLocale::isLocaleValid($locale)) {
            $locale = LOCALE_DEFAULT;
        }

        return $locale;
    }

    /**
     * Make a map of components to their respective files.
     * @param $locale string
     * @return array
     */
    public static function makeComponentMap($locale) {
        $componentMap = parent::makeComponentMap($locale);
        $baseDir = "locale/$locale/";
        
        $componentMap[LOCALE_COMPONENT_APPLICATION_COMMON] = $baseDir . 'locale.xml';
        $componentMap[LOCALE_COMPONENT_APP_AUTHOR] = $baseDir . 'author.xml';
        $componentMap[LOCALE_COMPONENT_APP_EDITOR] = $baseDir . 'editor.xml';
        $componentMap[LOCALE_COMPONENT_APP_MANAGER] = $baseDir . 'manager.xml';
        $componentMap[LOCALE_COMPONENT_APP_ADMIN] = $baseDir . 'admin.xml';
        $componentMap[LOCALE_COMPONENT_APP_DEFAULT] = $baseDir . 'default.xml';
        
        // 2. File Hasil Pecahan (Refactoring)
        $componentMap[LOCALE_COMPONENT_APP_PAYMENT] = $baseDir . 'payment.xml';
        $componentMap[LOCALE_COMPONENT_APP_AUTHORIZATION] = $baseDir . 'authorization.xml';
        $componentMap[LOCALE_COMPONENT_APP_NOTIFICATION] = $baseDir . 'notification.xml';
        $componentMap[LOCALE_COMPONENT_APP_READING_TOOLS] = $baseDir . 'reading_tools.xml';
        $componentMap[LOCALE_COMPONENT_APP_LOG] = $baseDir . 'log.xml';
        $componentMap[LOCALE_COMPONENT_APP_USER] = $baseDir . 'user.xml';
        $componentMap[LOCALE_COMPONENT_APP_SUBMISSION] = $baseDir . 'submission.xml';
        $componentMap[LOCALE_COMPONENT_APP_EDITORIAL] = $baseDir . 'editorial.xml';
        
        return $componentMap;
    }
    
    /**
     * [WIZDAM] Smart Locale Auto-Loader
     * Secara dinamis memuat komponen locale berdasarkan Page/Handler yang sedang diakses
     * @param PKPRequest $request
     */
    public static function requireComponentsForRequest($request) {
        $router = $request->getRouter();
        
        // Pastikan router tersedia (terkadang script CLI tidak memiliki router web)
        if (!$router || !is_callable(array($router, 'getRequestedPage'))) {
            // Fallback: Hanya muat yang paling umum
            self::requireComponents(LOCALE_COMPONENT_CORE_COMMON, LOCALE_COMPONENT_APPLICATION_COMMON);
            return;
        }

        $page = $router->getRequestedPage($request);
        $op = $router->getRequestedOp($request);

        // 1. KOMPONEN GLOBAL: Selalu dimuat di SETIAP halaman
        $componentsToLoad = array(
            LOCALE_COMPONENT_CORE_COMMON,
            LOCALE_COMPONENT_APPLICATION_COMMON,
            LOCALE_COMPONENT_APP_DEFAULT
        );

        // 2. MAPPING DINAMIS: Tentukan komponen apa saja untuk halaman tertentu
        // Format: 'nama_page_dari_url' => array(ID_KOMPONEN_1, ID_KOMPONEN_2)
        $routeMap = array(
            // Rancangan Locale untuk penyatuan semua role (Author, Editor, SectionEditor, CopyEditor, LayoutEditor, dan Proofread hanya menjadi workflow/mywizdam/myfrontedge/overview)
            'submission' => array(
                LOCALE_COMPONENT_CORE_SUBMISSION, 
                LOCALE_COMPONENT_APP_SUBMISSION, 
                LOCALE_COMPONENT_APP_AUTHOR,
                LOCALE_COMPONENT_CORE_GRID
            ),
            'workflow' => array(
                LOCALE_COMPONENT_CORE_SUBMISSION, 
                LOCALE_COMPONENT_APP_SUBMISSION, 
                LOCALE_COMPONENT_APP_EDITORIAL,
                LOCALE_COMPONENT_CORE_GRID
            ),
            'author' => array(
                LOCALE_COMPONENT_CORE_SUBMISSION, 
                LOCALE_COMPONENT_APP_SUBMISSION, 
                LOCALE_COMPONENT_APP_AUTHOR
            ),
            'editor' => array(
                LOCALE_COMPONENT_CORE_SUBMISSION, 
                LOCALE_COMPONENT_APP_SUBMISSION, 
                LOCALE_COMPONENT_APP_EDITOR,
                LOCALE_COMPONENT_APP_EDITORIAL
            ),
            'sectionEditor' => array(
                LOCALE_COMPONENT_CORE_SUBMISSION, 
                LOCALE_COMPONENT_APP_SUBMISSION, 
                LOCALE_COMPONENT_APP_EDITOR,
                LOCALE_COMPONENT_APP_EDITORIAL
            ),
            'copyeditor' => array(
                LOCALE_COMPONENT_CORE_SUBMISSION,
                LOCALE_COMPONENT_APP_EDITORIAL
            ),
            'layoutEditor' => array(
                LOCALE_COMPONENT_CORE_SUBMISSION,
                LOCALE_COMPONENT_APP_EDITORIAL
            ),
            'proofreader' => array(
                LOCALE_COMPONENT_CORE_SUBMISSION,
                LOCALE_COMPONENT_APP_EDITORIAL
            ),
            'manager' => array(
                LOCALE_COMPONENT_CORE_MANAGER, 
                LOCALE_COMPONENT_APP_MANAGER
            ),
            'admin' => array(
                LOCALE_COMPONENT_CORE_ADMIN, 
                LOCALE_COMPONENT_APP_ADMIN
            ),
            'user' => array(
                LOCALE_COMPONENT_CORE_USER, 
                LOCALE_COMPONENT_APP_USER,
                LOCALE_COMPONENT_APP_AUTHORIZATION
            ),
            'payment' => array(
                LOCALE_COMPONENT_APP_PAYMENT
            ),
            'article' => array(
                LOCALE_COMPONENT_CORE_READER,
                LOCALE_COMPONENT_APP_READING_TOOLS,
                LOCALE_COMPONENT_APP_LOG
            )
        );

        // 3. INJEKSI KOMPONEN SPESIFIK
        if (isset($routeMap[$page])) {
            $componentsToLoad = array_merge($componentsToLoad, $routeMap[$page]);
        }

        // Opsional: Bisa menambahkan filter spesifik berdasarkan $op (operasi)
        if ($page === 'user' && $op === 'authorization') {
            $componentsToLoad[] = LOCALE_COMPONENT_APP_AUTHORIZATION;
        }

        // 4. EKSEKUSI PEMUATAN
        // Menghilangkan duplikat jika komponen sama, lalu memuatnya sekaligus
        $componentsToLoad = array_unique($componentsToLoad);
        call_user_func_array(array('AppLocale', 'requireComponents'), $componentsToLoad);
    }
    
    /**
     * [WIZDAM] Set the current locale (Minimal Shim for multi-locale search).
     * Bypasses the missing normalizeLocale() and Application::setLocale() 
     * by directly manipulating the session and cookie variables, forcing 
     * AppLocale::getLocale() to pick up the new value in the current request.
     * @param string $locale
     */
    public static function setLocale($locale) {
        $supportedLocales = AppLocale::getSupportedLocales();

        if (!in_array($locale, array_keys($supportedLocales))) {
            return false;
        }

        // 1. Set locale di Cookie (Request::getCookieVar)
        Request::setCookieVar('currentLocale', $locale);

        // 2. Set locale di Session (SessionManager::getUserSession)
        if (!defined('SESSION_DISABLE_INIT') && Config::getVar('general', 'installed')) {
            $sessionManager = SessionManager::getManager();
            $session = $sessionManager->getUserSession();
            if ($session) {
                $session->setSessionVar('currentLocale', $locale);
            }
        }

        // 3. Reset state statis di class AppLocale
        // Ini adalah variable statis yang diakses oleh AppLocale::getLocale()
        unset($GLOBALS['currentLocale']); 
        
        // Ensure Request::getUserVar('setLocale') is set for this request
        // Fallback lain yang dijamin dilihat oleh AppLocale::getLocale()
        $_GET['setLocale'] = $locale; 
        
        return true;
    }
}

if (!class_exists('Locale')) {
    /**
     * Shim class for backward compatibility.
     * Some older plugins might still reference 'Locale' instead of 'AppLocale'.
     */
    class Locale extends AppLocale {
        // This is used for backwards compatibility (bug #5240)
    }
}

?>
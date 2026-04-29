<?php
declare(strict_types=1);

/**
 * @file classes/i18n/LocaleFile.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LocaleFile
 * @ingroup i18n
 *
 * @brief Abstraction of a locale file
 * [WIZDAM EDITION] Modernized for PHP 8 (Static Methods & No References)
 */

class LocaleFile {
    
    /** @var GenericCache Cache of this locale file */
    public $cache;

    /** @var string The identifier for this locale file */
    public $locale;

    /** @var string The filename for this locale file */
    public $filename;

    /**
     * Constructor.
     * @param $locale string Key for this locale file
     * @param $filename string Filename to this locale file
     */
    public function __construct($locale, $filename) {
        $this->locale = $locale;
        $this->filename = $filename;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function LocaleFile($locale, $filename) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::LocaleFile(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct($locale, $filename);
    }

    /**
     * Get the cache object for this locale file.
     * [WIZDAM EDITION] Dilengkapi pencegatan I/O untuk locale yang tidak aktif.
     * [WIZDAM EDITION] Absolute I/O Intercept + Self-Cleaning Protocol.
     * @param $locale string
     * @return GenericCache
     */
    public function _getCache($locale) {
        if (!isset($this->cache)) {
            // [WIZDAM] LEAN CACHE: Evaluasi kelayakan
            if (self::_isCacheable($this->locale)) {
                // FASE AKTIF (Primary/Support/Master): Gunakan mesin cache fisik
                $cacheManager = CacheManager::getManager();
                $this->cache = $cacheManager->getFileCache(
                    'locale', md5($this->filename),
                    array($this, '_cacheMiss')
                );

                $cacheTime = $this->cache->getCacheTime();
                if ($cacheTime === null || $cacheTime < filemtime($this->filename)) {
                    $this->cache->setEntireCache(self::load($this->filename));
                }
            } else {
                // FASE PASIF: Bahasa tidak diizinkan di Production!
                
                // 1. PROTOKOL SAPU BERSIH (Self-Cleaning sisa Instalasi)
                $cacheManager = CacheManager::getManager();
                $tempCache = $cacheManager->getFileCache(
                    'locale', md5($this->filename),
                    array($this, '_cacheMiss')
                );
                // Jika file fisik sisa instalasi (atau bug masa lalu) ternyata masih ada di hard drive, HAPUS SEKARANG!
                if ($tempCache->getCacheTime() !== null) {
                    $tempCache->flush(); 
                }

                // 2. RAM-ONLY CLASS: Berikan terjemahan lewat memori tanpa menulis apapun ke disk
                $this->cache = new class(self::load($this->filename)) {
                    private $data;
                    
                    public function __construct($data) {
                        $this->data = $data;
                    }
                    
                    public function get($id) {
                        return isset($this->data[$id]) ? $this->data[$id] : null;
                    }
                    
                    public function getCacheTime() { return time(); }
                    public function setEntireCache($data) { $this->data = $data; }
                    public function flush() {}
                };
            }
        }
        return $this->cache;
    }

    /**
     * Register a cache miss.
     * @param $cache GenericCache
     * @param $id string
     */
    public function _cacheMiss($cache, $id) {
        return null; // It's not in this locale file.
    }

    /**
     * Get the filename for this locale file.
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * Translate a string using the selected locale.
     * Substitution works by replacing tokens like "{$foo}" with the value of
     * the parameter named "foo" (if supplied).
     * @param $key string
     * @param $params array named substitution parameters
     * @param $locale string the locale to use
     * @return string
     */
    public function translate($key, $params = array(), $locale = null) {
        if ($this->isValid()) {
            $key = trim((string) $key); // Amankan key agar pasti berupa string
            if (empty($key)) {
                return '';
            }

            $cache = $this->_getCache($this->locale);
            $message = $cache->get($key);
            
            if (!isset($message)) {
                // Try to force loading the plugin locales.
                $message = $this->_cacheMiss($cache, $key);
            }

            if (isset($message)) {
                if (!empty($params) && is_array($params)) {
                    
                    // [WIZDAM] LEVEL 1: AUTO-FLATTENING ---
                    // Menangkap dan membongkar pengirim (EventLogEntry) yang salah mengirimkan array bersarang di dalam key 'params'
                    if (isset($params['params']) && is_array($params['params'])) {
                        $nestedParams = $params['params'];
                        unset($params['params']);
                        $params = array_merge($params, $nestedParams);
                    }

                    // [WIZDAM] LEVEL 2: SAFE SUBSTITUTION ---
                    // Substitute custom parameters
                    foreach ($params as $paramKey => $paramValue) {
                        // Sanity Check: Cegah Fatal Error: Jika value berupa array, atau object yang tak bisa dijadikan string
                        if (is_array($paramValue) || (is_object($paramValue) && !method_exists($paramValue, '__toString'))) {
                            $safeValue = ''; // Netralisir menjadi string kosong agar PHP tidak crash
                            
                            // Opsional: Aktifkan error_log untuk melacaknya
                            // error_log("[WIZDAM] Warning: Array passed to Locale translation for key {$paramKey}");
                        } else {
                            $safeValue = (string) ($paramValue ?? '');
                        }

                        $message = str_replace("{\$$paramKey}", $safeValue, $message);
                    }
                }

                // if client encoding is set to iso-8859-1, transcode string from utf8 since we store all XML files in utf8
                if (LOCALE_ENCODING == "iso-8859-1") $message = utf8_decode($message);

                return $message;
            }
        }
        return null;
    }

    /**
     * [WIZDAM] Lean Cache Gatekeeper: Validasi status aktif sebuah locale.
     * Mencegah locale pasif menguras Inodes Disk dan RAM Server.
     * @param string $locale
     * @return boolean
     */
    public static function _isCacheable($locale) {
        // 1. Master Locale (en_US) memiliki hak istimewa (VIP) mutlak.
        if ($locale === MASTER_LOCALE) return true;

        // 2. CEK FASE: Apakah sistem masih dalam proses instalasi?
        // Membaca parameter 'installed' dari file config.inc.php
        if (!Config::getVar('general', 'installed')) {
            // Saat instalasi, izinkan cache HANYA untuk bahasa 
            // yang sedang dipilih/dipakai oleh pengguna di browser.
            return ($locale === AppLocale::getLocale());
        }

        // 3. FASE NORMAL: Sistem sudah beroperasi penuh (Database aktif)
        $primaryLocale = AppLocale::getPrimaryLocale();
        $supportedLocales = AppLocale::getSupportedLocales();

        if (!is_array($supportedLocales)) $supportedLocales = array();

        // Izinkan cache hanya jika ia adalah Primary atau ada di daftar Supported.
        return ($locale === $primaryLocale || array_key_exists($locale, $supportedLocales));
    }

    /**
     * Static method: Load a locale array from a file. Not cached!
     * [WIZDAM] WAJIB PUBLIC STATIC agar bisa dipanggil via LocaleFile::load()
     * @param $filename string Filename to locale XML to load
     * @return array
     */
    public static function load($filename) {
        $localeData = array();

        // Reload localization XML file
        $xmlDao = new XMLDAO();
        $data = $xmlDao->parseStruct($filename, array('message'));

        // Build array with ($key => $string)
        if (isset($data['message'])) {
            foreach ($data['message'] as $messageData) {
                $localeData[$messageData['attributes']['key']] = $messageData['value'];
            }
        }

        return $localeData;
    }

    /**
     * Check if a locale is valid.
     * @param $locale string
     * @return boolean
     */
    public function isValid() {
        return isset($this->locale) && file_exists($this->filename);
    }

    /**
     * Test a locale file against the given reference locale file and
     * return an array of errorType => array(errors).
     * @param $referenceLocaleFile object
     * @return array
     */
    public function testLocale($referenceLocaleFile) {
        $errors = array(
            LOCALE_ERROR_MISSING_KEY => array(),
            LOCALE_ERROR_EXTRA_KEY => array(),
            LOCALE_ERROR_DIFFERING_PARAMS => array(),
            LOCALE_ERROR_MISSING_FILE => array()
        );

        if ($referenceLocaleFile->isValid()) {
            if (!$this->isValid()) {
                $errors[LOCALE_ERROR_MISSING_FILE][] = array(
                    'locale' => $this->locale,
                    'filename' => $this->filename
                );
                return $errors;
            }
        } else {
            // If the reference file itself does not exist or is invalid then
            // there's nothing to be translated here.
            return $errors;
        }

        // [WIZDAM] Panggil static method dengan benar
        $localeContents = self::load($this->filename);
        $referenceContents = self::load($referenceLocaleFile->filename);

        foreach ($referenceContents as $key => $referenceValue) {
            if (!isset($localeContents[$key])) {
                $errors[LOCALE_ERROR_MISSING_KEY][] = array(
                    'key' => $key,
                    'locale' => $this->locale,
                    'filename' => $this->filename,
                    'reference' => $referenceValue
                );
                continue;
            }
            $value = $localeContents[$key];

            $referenceParams = AppLocale::getParameterNames($referenceValue);
            $params = AppLocale::getParameterNames($value);
            if (count(array_diff($referenceParams, $params)) > 0) {
                $errors[LOCALE_ERROR_DIFFERING_PARAMS][] = array(
                    'key' => $key,
                    'locale' => $this->locale,
                    'mismatch' => array_diff($referenceParams, $params),
                    'filename' => $this->filename,
                    'reference' => $referenceValue,
                    'value' => $value
                );
            }
            // After processing a key, remove it from the list;
            // this way, the remainder at the end of the loop
            // will be extra unnecessary keys.
            unset($localeContents[$key]);
        }

        // Leftover keys are extraneous.
        foreach ($localeContents as $key => $value) {
            $errors[LOCALE_ERROR_EXTRA_KEY][] = array(
                'key' => $key,
                'locale' => $this->locale,
                'filename' => $this->filename
            );
        }

        return $errors;
    }
}
?>
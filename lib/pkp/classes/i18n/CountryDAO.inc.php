<?php
declare(strict_types=1);

/**
 * @file classes/i18n/CountryDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CountryDAO
 * @package i18n
 *
 * @brief Provides methods for loading localized country name data.
 * [WIZDAM EDITION] Modernized for PHP 8 (No References, Wizdam Cache)
 */

class CountryDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function CountryDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::CountryDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the filename of the countries registry file for the given locale.
     * @param $locale string Name of locale (optional)
     * @return string
     */
    public function getFilename($locale = null) {
        if ($locale === null) $locale = AppLocale::getLocale();
        return "lib/pkp/locale/$locale/countries.xml";
    }

    /**
     * Get the cache object for a specific locale
     * @param $locale string Name of locale (optional)
     * @return object Cache
     */
    public function _getCountryCache($locale = null) {
        // Kita gunakan static variable dalam fungsi untuk in-memory caching sederhana pengganti Registry yang berat
        static $caches = array();

        if (!isset($locale)) $locale = AppLocale::getLocale();

        if (!isset($caches[$locale])) {
            $cacheManager = CacheManager::getManager();
            $caches[$locale] = $cacheManager->getFileCache(
                'country', 
                $locale,
                array($this, '_countryCacheMiss') // [PHP 8] Callback tanpa &
            );

            // Check to see if the data is outdated
            $cacheTime = $caches[$locale]->getCacheTime();
            if ($cacheTime !== null && $cacheTime < filemtime($this->getFilename($locale))) {
                $caches[$locale]->flush();
            }
        }
        return $caches[$locale];
    }

    /**
     * Cache Miss Handler (FINAL VERSION)
     * @param $cache object Cache
     * @param $id string Locale identifier
     * @return array
     */
    public function _countryCacheMiss($cache, $id) {
        $countries = array();
        $xmlDao = new XMLDAO();
        
        $filename = $this->getFilename($id); 

        if (file_exists($filename)) {
            $data = $xmlDao->parseStruct($filename, array('countries', 'country'));
            // Tambahkan ini sementara untuk diagnosa jika masih kosong:
            if (!$data) error_log("Wizdam Error: Gagal parsing XML Negara di $filename");

            if (isset($data['countries'])) {
                foreach ($data['country'] as $countryData) {
                    $code = $countryData['attributes']['code'] ?? null;
                    $name = $countryData['attributes']['name'] ?? null;

                    if ($code && $name) {
                        // [FIX] Gunakan $name langsung. 
                        // File di folder locale/xx_XX/countries.xml sudah berisi nama negara yang benar.
                        $countries[$code] = $name;
                    }
                }
            }
        }
        
        asort($countries);
        $cache->setEntireCache($countries);
        return $countries;
    }

    /**
     * Return a list of all countries.
     * @param $locale string Name of locale (optional)
     * @return array
     */
    public function getCountries() {
        // 1. Cek Registry statis (paling cepat)
        $countries = Registry::get('allCountries');
        if (is_array($countries) && !empty($countries)) return $countries;
        
        // 2. Jika gagal, siapkan Cache
        $cache = $this->_getCountryCache();
        $countries = $cache->getContents();

        // 3. [WIZDAM ROBUST FIX] Paksa ambil data jika cache gagal
        if (!is_array($countries) || empty($countries)) {
            $locale = AppLocale::getLocale();
            error_log("Wizdam: Cache empty/corrupt, manual reload for locale: " . $locale);
            
            // Pastikan kita memanggil miss handler dengan locale yang valid
            $countries = $this->_countryCacheMiss($cache, $locale);
        }

        // 4. Update Registry dan kembalikan data
        if (is_array($countries)) {
            Registry::set('allCountries', $countries);
        }
        
        return $countries;
    }

    /**
     * Return a translated country name, given a code.
     * @param $code string Country code (e.g. 'ID')
     * @param $locale string Name of locale (optional)
     * @return string
     */
    public function getCountry($code, $locale = null) {
        if ($locale === null) $locale = AppLocale::getLocale();
    
        $cache = $this->_getCountryCache($locale);
        $countries = $cache->getContents();
    
        // Fallback jika cache masih kosong setelah getContents()
        if (!is_array($countries) || empty($countries)) {
            $countries = $this->_countryCacheMiss($cache, $locale); // ← locale, bukan $code
        }
    
        return $countries[$code] ?? null;
    }
}

?>
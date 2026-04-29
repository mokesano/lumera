<?php
declare(strict_types=1);

/**
 * @file classes/i18n/TimeZoneDAO.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TimeZoneDAO
 * @package i18n
 *
 * @brief Provides methods for loading localized time zone name data.
 * [WIZDAM EDITION] Modernized for PHP 8 (No References, No Registry Dependency)
 */

class TimeZoneDAO extends DAO {
    
    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function TimeZoneDAO() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::TimeZoneDAO(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    /**
     * Get the filename of the time zone registry file for the given locale
     * @return string
     */
    public function getFilename() {
        return "lib/pkp/registry/timeZones.xml";
    }

    /**
     * Get the cache object for timezones
     */
    public function _getTimeZoneCache() {
        // [WIZDAM] Gunakan static variable pengganti Registry yang berat
        static $cache;

        if (!isset($cache)) {
            $cacheManager = CacheManager::getManager();
            $cache = $cacheManager->getFileCache(
                'timeZone', 
                'list',
                array($this, '_timeZoneCacheMiss') // [PHP 8] Callback tanpa &
            );

            // Check to see if the data is outdated
            $cacheTime = $cache->getCacheTime();
            if ($cacheTime !== null && $cacheTime < filemtime($this->getFilename())) {
                $cache->flush();
            }
        }
        return $cache;
    }

    /**
     * Cache Miss Handler
     */
    public function _timeZoneCacheMiss($cache, $id) {
        $timeZones = array();
        
        // Reload time zone registry file
        $xmlDao = new XMLDAO();
        $data = $xmlDao->parseStruct($this->getFilename(), array('timezones', 'entry'));
        
        if (isset($data['entry'])) {
            foreach ($data['entry'] as $timeZoneData) {
                $key = $timeZoneData['attributes']['key'];
                $name = $timeZoneData['attributes']['name'];
                // Terjemahkan langsung di sini jika perlu, atau simpan key-nya
                $timeZones[$key] = __($name);
            }
        }
        
        asort($timeZones);
        $cache->setEntireCache($timeZones);
        
        return $timeZones;
    }

    /**
     * Return a list of all time zones.
     * @return array
     */
    public function getTimeZones() {
        $cache = $this->_getTimeZoneCache();
        return $cache->getContents();
    }
}

?>
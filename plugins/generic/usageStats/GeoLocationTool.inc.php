<?php
declare(strict_types=1);

/**
 * @file plugins/generic/usageStats/GeoLocationTool.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GeoLocationTool
 * @ingroup plugins_generic_usageStats
 *
 * @brief Geo location by ip wrapper class.
 */

/** GeoIp tool for geo location based on ip */
include('lib' . DIRECTORY_SEPARATOR . 'geoIp' . DIRECTORY_SEPARATOR . 'geoipcity.inc');

if (!defined('GEOIP_STANDARD')) {
    define('GEOIP_STANDARD', 0);
}
if (!defined('GEOIP_MEMORY_CACHE')) {
    define('GEOIP_MEMORY_CACHE', 1);
}

if (!function_exists('geoip_open')) {
    /**
     * @param string $filename
     * @param int $flags
     * @return resource|object|null
     */
    function geoip_open($filename, $flags) {
        return null;
    }
}

if (!function_exists('geoip_record_by_addr')) {
    /**
     * @param resource|object $gi
     * @param string $addr
     * @return object|null
     */
    function geoip_record_by_addr($gi, $addr) {
        return null;
    }
}

class GeoLocationTool {

    /** @var object|null GeoIP Handle (stdClass from geoip_open) */
    protected $_geoLocationTool;

    /** @var array List of region names */
    protected $_regionName;

    /** @var bool */
    protected $_isDbFilePresent;

    /**
     * Constructor.
     */
    public function __construct() {
        $geoLocationDbFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'GeoLiteCity.dat';
        
        if (file_exists($geoLocationDbFile)) {
            $this->_isDbFilePresent = true;
            
            // Open GeoIP Database (Standard Mode)
            if (function_exists('geoip_open')) {
                $this->_geoLocationTool = geoip_open($geoLocationDbFile, GEOIP_STANDARD);
            } else {
                // Fallback safety if library includes failed
                $this->_isDbFilePresent = false;
                $this->_geoLocationTool = null;
                return;
            }
            
            // Load Region Names Variables
            include('lib' . DIRECTORY_SEPARATOR . 'geoIp' . DIRECTORY_SEPARATOR . 'geoipregionvars.php');
            
            // $GEOIP_REGION_NAME berasal dari file include di atas
            $this->_regionName = isset($GEOIP_REGION_NAME) ? $GEOIP_REGION_NAME : [];
        } else {
            $this->_isDbFilePresent = false;
            $this->_geoLocationTool = null;
            $this->_regionName = [];
        }
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function GeoLocationTool() {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::GeoLocationTool(). Please refactor to parent::__construct().", 
                E_USER_DEPRECATED
            );
        }
        self::__construct();
    }

    //
    // Public methods.
    //
    /**
     * Return country code, city name, and region for the passed ip address.
     * [LUMERA] Ensures UTF-8 encoding for City and Region.
     * @param string $ip
     * @return array [CountryCode, City, Region]
     */
    public function getGeoLocation($ip) {
        // If no geolocation tool, the geo database file is missing.
        if (!$this->_geoLocationTool) {
            return [null, null, null];
        }

        // Retrieve record from GeoIP
        $record = geoip_record_by_addr($this->_geoLocationTool, $ip);

        if (!$record) {
            return [null, null, null];
        }

        // 1. Resolve Region Name
        $regionName = null;
        if (isset($record->country_code, $record->region, $this->_regionName[$record->country_code][$record->region])) {
            $regionName = $this->_regionName[$record->country_code][$record->region];
        } else {
            // Fallback: use the region code if name not found
            $regionName = $record->region ?? null;
        }

        // 2. Resolve City and Country
        $city = $record->city ?? null;
        $countryCode = $record->country_code ?? null;

        // [WIZDAM FIX] Encoding Handling
        // GeoIP Legacy databases are typically ISO-8859-1 (Latin-1).
        // Database storage usually requires UTF-8.
        if ($city) {
            if (function_exists('mb_convert_encoding')) {
                $city = mb_convert_encoding($city, 'UTF-8', 'ISO-8859-1');
            } elseif (function_exists('utf8_encode')) {
                // Fallback for environments without mbstring (suppress deprecation warning in PHP 8.2+)
                $city = @utf8_encode($city);
            }
        }

        if ($regionName) {
            if (function_exists('mb_convert_encoding')) {
                $regionName = mb_convert_encoding($regionName, 'UTF-8', 'ISO-8859-1');
            } elseif (function_exists('utf8_encode')) {
                $regionName = @utf8_encode($regionName);
            }
        }

        return [$countryCode, $city, $regionName];
    }

    /**
     * Identify if the geolocation database tool is available for use.
     * @return bool
     */
    public function isPresent() {
        return $this->_isDbFilePresent;
    }

    /**
     * Get all country codes.
     * @return array|null
     */
    public function getAllCountryCodes() {
        if (!$this->_geoLocationTool) {
            return null;
        }

        $tool = $this->_geoLocationTool;
        
        if (isset($tool->GEOIP_COUNTRY_CODES)) {
            $countryCodes = $tool->GEOIP_COUNTRY_CODES;
            // Overwrite the first empty record with the code to unknown country.
            $unknownId = defined('STATISTICS_UNKNOWN_COUNTRY_ID') ? STATISTICS_UNKNOWN_COUNTRY_ID : 'other';
            $countryCodes[0] = $unknownId;
            return $countryCodes;
        }
        
        return null;
    }

    /**
     * Return the 3 letters version of country codes based on the passed 2 letters version.
     * @param string $countryCode
     * @return string|null
     */
    public function get3LettersCountryCode($countryCode) {
        return $this->_getCountryCodeOnList($countryCode, 'GEOIP_COUNTRY_CODES3');
    }

    /**
     * Return the 2 letter version of country codes based on the passed 3 letters version.
     * @param string $countryCode3
     * @return string|null
     */
    public function get2LettersCountryCode($countryCode3) {
        return $this->_getCountryCodeOnList($countryCode3, 'GEOIP_COUNTRY_CODES');
    }

    /**
     * Get regions by country.
     * @param string $countryId
     * @return array
     */
    public function getRegions($countryId) {
        return $this->_regionName[$countryId] ?? [];
    }

    //
    // Protected helper methods.
    //
    /**
     * Get the passed country code inside the passed list.
     * @param string $countryCode
     * @param string $countryCodeListName
     * @return string|null
     */
    protected function _getCountryCodeOnList($countryCode, $countryCodeListName) {
        if (!$this->_geoLocationTool) {
            return null;
        }
        
        $tool = $this->_geoLocationTool;
        if (!isset($tool->$countryCodeListName)) {
            return null;
        }
        
        $countryCodeList = $tool->$countryCodeListName;
        $countryCodesIndex = $tool->GEOIP_COUNTRY_CODE_TO_NUMBER ?? [];
        $countryCodeIndex = $countryCodesIndex[$countryCode] ?? null;
        if ($countryCodeIndex !== null && isset($countryCodeList[$countryCodeIndex])) {
            return $countryCodeList[$countryCodeIndex];
        }

        return null;
    }

}
?>
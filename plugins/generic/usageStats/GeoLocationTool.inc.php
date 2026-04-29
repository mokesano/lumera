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
 * * REFACTORED: Wizdam Edition (PHP 8 Compatibility & Data Completeness)
 */

/** GeoIp tool for geo location based on ip */
include('lib' . DIRECTORY_SEPARATOR . 'geoIp' . DIRECTORY_SEPARATOR . 'geoipcity.inc');

class GeoLocationTool {

    /** @var object|null GeoIP Handle */
    public $_geoLocationTool;

    /** @var array List of region names */
    public $_regionName;

    /** @var boolean */
    public $_isDbFilePresent;

    /**
     * Constructor.
     */
    public function __construct() {
        $geoLocationDbFile = dirname(__FILE__) . DIRECTORY_SEPARATOR . "GeoLiteCity.dat";
        
        if (file_exists($geoLocationDbFile)) {
            $this->_isDbFilePresent = true;
            
            // Open GeoIP Database (Standard Mode)
            // Pastikan library geoipcity.inc sudah diload
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
            if (isset($GEOIP_REGION_NAME)) {
                $this->_regionName = $GEOIP_REGION_NAME;
            } else {
                $this->_regionName = array();
            }
        } else {
            $this->_isDbFilePresent = false;
            $this->_geoLocationTool = null;
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
     * [WIZDAM] Ensures UTF-8 encoding for City and Region.
     * @param $ip string
     * @return array [CountryCode, City, Region]
     */
    public function getGeoLocation($ip) {
        // If no geolocation tool, the geo database file is missing.
        if (!$this->_geoLocationTool) {
            return array(null, null, null);
        }

        // Retrieve record from GeoIP
        $record = geoip_record_by_addr($this->_geoLocationTool, $ip);

        if (!$record) {
            return array(null, null, null);
        }

        // 1. Resolve Region Name
        // GeoIP Legacy returns region codes (e.g., "01", "CA"). We map it to names if possible.
        $regionName = null;
        if (isset($record->country_code, $record->region) && isset($this->_regionName[$record->country_code][$record->region])) {
            $regionName = $this->_regionName[$record->country_code][$record->region];
        } else {
            // Fallback: use the region code if name not found
            $regionName = isset($record->region) ? $record->region : null;
        }

        // 2. Resolve City
        $city = isset($record->city) ? $record->city : null;

        // [WIZDAM FIX] Encoding Handling
        // GeoIP Legacy databases are typically ISO-8859-1 (Latin-1).
        // Database storage usually requires UTF-8.
        
        if ($city) {
            if (function_exists('mb_convert_encoding')) {
                // Modern, robust conversion
                $city = mb_convert_encoding($city, 'UTF-8', 'ISO-8859-1');
            } elseif (function_exists('utf8_encode')) {
                // Deprecated in PHP 8.2, removed in PHP 9.0, but used as fallback
                $city = utf8_encode($city);
            }
        }

        if ($regionName) {
            if (function_exists('mb_convert_encoding')) {
                $regionName = mb_convert_encoding($regionName, 'UTF-8', 'ISO-8859-1');
            } elseif (function_exists('utf8_encode')) {
                $regionName = utf8_encode($regionName);
            }
        }

        // Return Array: Country, City, Region
        return array(
            isset($record->country_code) ? $record->country_code : null,
            $city,
            $regionName
        );
    }

    /**
     * Identify if the geolocation database tool is available for use.
     * @return boolean
     */
    public function isPresent() {
        return $this->_isDbFilePresent;
    }

    /**
     * Get all country codes.
     * @return mixed array or null
     */
    public function getAllCountryCodes() {
        if (!$this->_geoLocationTool) return null;

        $tool = $this->_geoLocationTool;
        
        if (isset($tool->GEOIP_COUNTRY_CODES)) {
            $countryCodes = $tool->GEOIP_COUNTRY_CODES;
            // Overwrite the first empty record with the code to unknow country.
            // Check if STATISTICS_UNKNOWN_COUNTRY_ID is defined (usually in UsageStatsPlugin)
            $unknownId = defined('STATISTICS_UNKNOWN_COUNTRY_ID') ? STATISTICS_UNKNOWN_COUNTRY_ID : 'other';
            $countryCodes[0] = $unknownId;
            return $countryCodes;
        }
        
        return null;
    }

    /**
     * Return the 3 letters version of country codes
     * based on the passed 2 letters version.
     * @param $countryCode string
     * @return mixed string or null
     */
    public function get3LettersCountryCode($countryCode) {
        return $this->_getCountryCodeOnList($countryCode, 'GEOIP_COUNTRY_CODES3');
    }

    /**
     * Return the 2 letter version of country codes
     * based on the passed 3 letters version.
     * @param $countryCode3 string
     * @return mixed string or null
     */
    public function get2LettersCountryCode($countryCode3) {
        return $this->_getCountryCodeOnList($countryCode3, 'GEOIP_COUNTRY_CODES');
    }

    /**
     * Get regions by country.
     * @param $countryId int
     * @return array
     */
    public function getRegions($countryId) {
        $regions = array();
        $database = $this->_regionName;
        if (isset($database[$countryId])) {
            $regions = $database[$countryId];
        }

        return $regions;
    }

    /**
     * Get the passed country code inside the passed list.
     * @param $countryCode The 2 letters country code.
     * @param $countryCodeListName array Any geoip country code list.
     * @return mixed String or null.
     */
    public function _getCountryCodeOnList($countryCode, $countryCodeListName) {
        $returner = null;

        if (!$this->_geoLocationTool) return $returner;
        
        $tool = $this->_geoLocationTool;

        if (isset($tool->$countryCodeListName)) {
            $countryCodeList = $tool->$countryCodeListName;
        } else {
            return $returner;
        }

        // Access properties directly if public, check isset first
        $countryCodesIndex = isset($tool->GEOIP_COUNTRY_CODE_TO_NUMBER) ? $tool->GEOIP_COUNTRY_CODE_TO_NUMBER : array();
        $countryCodeIndex = null;

        if (isset($countryCodesIndex[$countryCode])) {
            $countryCodeIndex = $countryCodesIndex[$countryCode];
        }

        if ($countryCodeIndex) {
            if (isset($countryCodeList[$countryCodeIndex])) {
                $returner = $countryCodeList[$countryCodeIndex];
            }
        }

        return $returner;
    }
}
?>
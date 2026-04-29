<?php
declare(strict_types=1);

/**
 * @defgroup config
 */

/**
 * @file classes/config/Config.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Config
 * @ingroup config
 *
 * @brief Config class for accessing configuration parameters.
 */

/** The path to the default configuration file */
if (!defined('CONFIG_FILE')) {
    define('CONFIG_FILE', Core::getBaseDir() . DIRECTORY_SEPARATOR . 'config.inc.php');
}

import('lib.pkp.classes.config.ConfigParser');

class Config {
    
    /**
     * Retrieve a specified configuration variable.
     * @param string $section
     * @param string $key
     * @param mixed $default Optional default if the var doesn't exist
     * @return mixed May return boolean (in case of "off"/"on"/etc), numeric, string, or null.
     */
    public static function getVar(string $section, string $key, $default = null) {
        $configData = self::getData();
        return isset($configData[$section][$key]) ? $configData[$section][$key] : $default;
    }

    /**
     * Get the current configuration data.
     * @return array the configuration data
     */
    public static function getData(): array {
        $configData = Registry::get('configData', true, null);

        if ($configData === null) {
            // Load configuration data only once per request, implicitly
            // sets config data by ref in the registry.
            $configData = self::reloadData();
        }

        return $configData;
    }

    /**
     * Load configuration data from a file.
     * The file is assumed to be formatted in php.ini style.
     * @return array the configuration data
     */
    public static function reloadData(): array {
        $configFile = self::getConfigFileName();
        $configData = ConfigParser::readConfig($configFile);
        
        // [WIZDAM PROTOCOL COMPLIANCE]
        if ($configData === false) {
            // 1. Log detail lengkap (Path Absolut) ke Server Log untuk Administrator
            error_log('Wizdam Critical Error: Cannot read configuration file at path: ' . $configFile);

            // 2. Tampilkan pesan aman (Hanya Nama File) ke Pengguna
            // basename() membuang path folder, mengubah '/var/www/html/config.inc.php' menjadi 'config.inc.php'
            $safeMessage = sprintf('Cannot read configuration file: %s', basename($configFile));
            
            fatalError($safeMessage);
        }

        Registry::set('configData', $configData);
        return $configData;
    }

    /**
     * Set the path to the configuration file.
     * @param string $configFile
     */
    public static function setConfigFileName(string $configFile): void {
        // Reset the config data
        Registry::set('configData', null);

        // Set the config file
        Registry::set('configFile', $configFile);
    }

    /**
     * Return the path to the configuration file.
     * @return string
     */
    public static function getConfigFileName(): string {
        return (string) Registry::get('configFile', true, CONFIG_FILE);
    }

    /**
     * Get context base urls from config file.
     * @return array Empty array if none is set.
     */
    public static function getContextBaseUrls(): array {
        $contextBaseUrls = Registry::get('contextBaseUrls');

        if ($contextBaseUrls === null) {
            $contextBaseUrls = [];
            $configData = self::getData();
            
            // Filter the settings.
            if (isset($configData['general']) && is_array($configData['general'])) {
                foreach ($configData['general'] as $settingName => $settingValue) {
                    $matches = null;
                    if (preg_match('/base_url\[(.*)\]/', $settingName, $matches)) {
                        $workingContextPath = $matches[1];
                        $contextBaseUrls[$workingContextPath] = $settingValue;
                    }
                }
            }
            
            // Save back to Registry
            Registry::set('contextBaseUrls', $contextBaseUrls);
        }

        return $contextBaseUrls;
    }
}
?>
<?php
declare(strict_types=1);

/**
 * @file classes/config/ConfigParser.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ConfigParser
 * @ingroup config
 *
 * @brief Class for parsing and modifying php.ini style configuration files.
 */

class ConfigParser {

    /** * Contents of the config file currently being parsed 
     * @var string
     */
    public string $content = '';

    /**
     * Constructor.
     */
    public function __construct() {
        // not implement construct
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function ConfigParser() {
        self::__construct();
    }

    /**
     * Read a configuration file into a multidimensional array.
     * This is a replacement for the PHP parse_ini_file function, which does not type setting values.
     * NOTE: This method is STATIC to support usage in Config::reloadData().
     * Returns FALSE on failure so the caller (Config class) can trigger fatalError().
     * @param string $file full path to the config file
     * @return array|bool the configuration data (same format as http://php.net/parse_ini_file) or false on failure
     */
    public static function readConfig(string $file) {
        $configData = [];
        $currentSection = false;

        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }

        $fp = fopen($file, 'rb');
        if (!$fp) {
            return false;
        }

        while (!feof($fp)) {
            $line = fgets($fp, 1024);
            // Handle EOF returning false in some contexts
            if ($line === false) break;

            $line = trim($line);
            if ($line === '' || strpos($line, ';') === 0) {
                // Skip empty or commented line
                continue;
            }

            if (preg_match('/^\[(.+)\]/', $line, $matches)) {
                // Found a section
                $currentSection = $matches[1];
                if (!isset($configData[$currentSection])) {
                    $configData[$currentSection] = [];
                }

            } else if (strpos($line, '=') !== false) {
                // Found a setting
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // FIXME This may produce incorrect results if the line contains a comment
                if (preg_match('/^[\"\'](.*)[\"\']$/', $value, $matches)) {
                    // Treat value as a string
                    $value = stripslashes($matches[1]);

                } else {
                    preg_match('/^([\S]*)/', $value, $matches);
                    $value = $matches[1] ?? ''; // PHP 8 fix for undefined index

                    // Try to determine the type of the value
                    if ($value === '') {
                        $value = null;

                    } else if (is_numeric($value)) {
                        if (strstr($value, '.')) {
                            // floating-point
                            $value = (float) $value;
                        } else if (substr($value, 0, 2) == '0x') {
                            // hex
                            $value = intval($value, 16);
                        } else if (substr($value, 0, 1) == '0') {
                            // octal
                            $value = intval($value, 8);
                        } else {
                            // integer
                            $value = (int) $value;
                        }

                    } else if (strtolower($value) == 'true' || strtolower($value) == 'on') {
                        $value = true;

                    } else if (strtolower($value) == 'false' || strtolower($value) == 'off') {
                        $value = false;

                    } else if (defined($value)) {
                        // The value matches a named constant
                        $value = constant($value);
                    }
                }

                if ($currentSection === false) {
                    $configData[$key] = $value;

                } else if (is_array($configData[$currentSection])) {
                    $configData[$currentSection][$key] = $value;
                }
            }
        }

        fclose($fp);

        return $configData;
    }

    /**
     * Read a configuration file and update variables.
     * This method stores the updated configuration but does not write it out.
     * Use writeConfig() or getFileContents() afterwards to do something with the new config.
     * @param string $file full path to the config file
     * @param array $params an associative array of configuration parameters to update.
     * @return bool true if file could be read, false otherwise
     */
    public function updateConfig(string $file, array $params): bool {
        if (!file_exists($file) || !is_readable($file)) {
            return false;
        }

        $this->content = '';
        $lines = file($file);

        if ($lines === false) {
            return false;
        }

        // Parse each line of the configuration file
        $currentSection = null; // Initialize to null
        
        foreach ($lines as $line) {
            if (preg_match('/^;/', $line) || preg_match('/^\s*$/', $line)) {
                // Comment or empty line
                $this->content .= $line;

            } else if (preg_match('/^\s*\[(\w+)\]/', $line, $matches)) {
                // Start of new section
                $currentSection = $matches[1];
                $this->content .= $line;

            } else if (preg_match('/^\s*(\w+)\s*=/', $line, $matches)) {
                // Variable definition
                $key = $matches[1];
                $value = null;
                $shouldUpdate = false;

                if ($currentSection === null && array_key_exists($key, $params) && !is_array($params[$key])) {
                    // Variable not in a section
                    $value = $params[$key];
                    $shouldUpdate = true;

                } else if ($currentSection !== null && isset($params[$currentSection]) && is_array($params[$currentSection]) && array_key_exists($key, $params[$currentSection])) {
                    // Variable in a section
                    $value = $params[$currentSection][$key];
                    $shouldUpdate = true;
                }

                if ($shouldUpdate) {
                    // Update the value
                    if (is_string($value) && preg_match('/[^\w\-\/]/', $value)) {
                        // Escape strings containing non-alphanumeric characters
                        $valueString = '"' . $value . '"';
                    } else {
                        // Cast to string to be safe
                        $valueString = (string) $value;
                    }
                    $this->content .= "$key = $valueString\n";
                } else {
                    // Keep original line
                    $this->content .= $line;
                }

            } else {
                $this->content .= $line;
            }
        }

        return true;
    }

    /**
     * Write contents of current config file
     * @param string $file full path to output file
     * @return bool file write is successful
     */
    public function writeConfig(string $file): bool {
        // Check if file is writable, or directory is writable if file doesn't exist
        if (!(file_exists($file) && is_writable($file))
            && !(!file_exists($file) && is_dir(dirname($file)) && is_writable(dirname($file)))) {
            return false;
        }

        $fp = @fopen($file, 'wb');
        if (!$fp) {
            return false;
        }

        $result = fwrite($fp, $this->content);
        fclose($fp);
        
        return ($result !== false);
    }

    /**
     * Return the contents of the current config file.
     * @return string
     */
    public function getFileContents(): string {
        return $this->content;
    }
}
?>
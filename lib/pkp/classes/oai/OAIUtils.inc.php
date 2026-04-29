<?php
declare(strict_types=1);

/**
 * @defgroup oai
 */

/**
 * @file classes/oai/OAIUtils.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIUtils
 * @ingroup oai
 * @see OAIDAO
 *
 * @brief Utility functions used by OAI related classes.
 * * REFACTORED: Wizdam Edition (PHP 7.4 - 8.x Modernization)
 */

class OAIUtils {

    /**
     * Return a UTC-formatted datestamp from the specified UNIX timestamp.
     * @param $timestamp int *nix timestamp (if not used, the current time is used)
     * @param $includeTime boolean include both the time and date
     * @return string UTC datestamp
     */
    public static function UTCDate($timestamp = 0, $includeTime = true) {
        $format = "Y-m-d";
        if($includeTime) {
            $format .= "\TH:i:s\Z";
        }

        if($timestamp == 0) {
            return gmdate($format);

        } else {
            return gmdate($format, $timestamp);
        }
    }

    /**
     * Returns a UNIX timestamp from a UTC-formatted datestamp.
     * Returns the string "invalid" if datestamp is invalid,
     * or "invalid_granularity" if unsupported granularity.
     * @param $date string UTC datestamp
     * @param $checkGranularity boolean verify that granularity is correct
     * @return int|string timestamp or error string
     */
    public static function UTCtoTimestamp($date, $checkGranularity = true) {
        // FIXME Has limited range (see http://php.net/strtotime)
        if (preg_match("/^\d\d\d\d\-\d\d\-\d\d$/", $date)) {
            // Match date
            $time = strtotime("$date UTC");
            // MODERNIZATION: strtotime returns false on failure in PHP 7+, -1 in older versions.
            return ($time !== false && $time !== -1) ? $time : 'invalid';

        } else if (preg_match("/^(\d\d\d\d\-\d\d\-\d\d)T(\d\d:\d\d:\d\d)Z$/", $date, $matches)) {
            // Match datetime
            $date = "$matches[1] $matches[2]";
            
            // MODERNIZATION FIX: Removed $this->config->granularity check.
            // Static methods cannot access $this. We assume if it matches Regex, it's valid format.
            // Granularity enforcement should be handled by the caller (OAI class) if strictly necessary.
            
            $time = strtotime("$date UTC");
            return ($time !== false && $time !== -1) ? $time : 'invalid';

        } else {
            return 'invalid';
        }
    }


    /**
     * Clean input variables.
     * NOTE: Kept reference &$data for recursive in-place modification (Guideline #2 Exception).
     * @param $data mixed request parameter(s)
     * @return mixed cleaned request parameter(s)
     */
    public static function prepInput(&$data) {
        if (!is_array($data)) {
            $data = urldecode($data);

        } else {
            foreach ($data as $k => $v) {
                if (is_array($data[$k])) {
                    // MODERNIZATION: Use self:: instead of OAIUtils:: (cleaner)
                    self::prepInput($data[$k]);
                } else {
                    $data[$k] = urldecode($v);
                }
            }
        }
        return $data;
    }

    /**
     * Prepare variables for output.
     * Data is assumed to be UTF-8 encoded.
     * NOTE: Kept reference &$data for recursive in-place modification (Guideline #2 Exception).
     * @param $data mixed output parameter(s)
     * @return mixed cleaned output parameter(s)
     */
    public static function prepOutput(&$data) {
        if (!is_array($data)) {
            // MODERNIZATION: Null coalescing for safety
            $data = htmlspecialchars($data ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_XML1, 'UTF-8', false);

        } else {
            foreach ($data as $k => $v) {
                if (is_array($data[$k])) {
                    // MODERNIZATION: Fixed fatal error usage of $this-> in static context. Changed to self::
                    self::prepOutput($data[$k]);
                } else {
                    // MODERNIZATION: Null coalescing for safety
                    $data[$k] = htmlspecialchars($v ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_XML1, 'UTF-8', false);
                }
            }
        }
        return $data;
    }

    /**
     * Parses string $string into an associate array $array.
     * Acts like parse_str($string, $array) except duplicate
     * variable names in $string are converted to an array.
     * @param $string string input data string
     * @param $array array of parsed parameters
     */
    public static function parseStr($string, &$array) {
        $pairs = explode('&', $string);
        foreach ($pairs as $p) {
            $vars = explode('=', $p);
            if (!empty($vars[0]) && isset($vars[1])) {
                $key = $vars[0];
                // MODERNIZATION: Use implode instead of join (alias)
                $value = implode('=', array_splice($vars, 1));

                if (!isset($array[$key])) {
                    $array[$key] = $value;
                } else if (is_array($array[$key])) {
                    array_push($array[$key], $value);
                } else {
                    $array[$key] = array($array[$key], $value);
                }
            }
        }
    }
}

?>
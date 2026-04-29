<?php
declare(strict_types=1);

/**
 * @file classes/core/Transcoder.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Transcoder
 * @ingroup db
 *
 * @brief Multi-class transcoder; uses mbstring and iconv if available, otherwise falls back to built-in classes
 * [WIZDAM EDITION] Refactored for PHP 7.4+/8.x Strict Standards & Type Safety.
 */

class Transcoder {
    /** * @var string Name of source encoding 
     * [WIZDAM] Public visibility maintained for legacy compatibility
     */
    public $fromEncoding = '';

    /** * @var string Name of target encoding 
     * [WIZDAM] Public visibility maintained for legacy compatibility
     */
    public $toEncoding = '';

    /** * @var bool Whether or not to transliterate while transcoding 
     * [WIZDAM] Public visibility maintained for legacy compatibility
     */
    public $translit = false;

    /**
     * Constructor
     * @param string $fromEncoding Name of source encoding
     * @param string $toEncoding Name of target encoding
     * @param bool $translit Whether or not to transliterate while transcoding
     */
    public function __construct(string $fromEncoding, string $toEncoding, bool $translit = false) {
        $this->fromEncoding = $fromEncoding;
        $this->toEncoding = $toEncoding;
        $this->translit = $translit;
    }

    /**
     * [SHIM] Backward Compatibility
     */
    public function Transcoder($fromEncoding, $toEncoding, $translit = false) {
        if (Config::getVar('debug', 'deprecation_warnings')) {
            trigger_error(
                "Class '" . get_class($this) . "' uses deprecated constructor parent::" . get_class($this) . "(). Please refactor to parent::__construct().",
                E_USER_DEPRECATED
            );
        }
        self::__construct((string)$fromEncoding, (string)$toEncoding, (bool)$translit);
    }

    /**
     * Transcode a string
     * @param string $string String to transcode
     * @return string Result of transcoding
     */
    public function trans(string $string): string {
        // Detect existence of encoding conversion libraries
        // [WIZDAM NOTE] In modern PHP, these are almost always available, but we check for robustness.
        $mbstring = function_exists('mb_convert_encoding');
        $iconv = function_exists('iconv');

        // Optimization: Don't do work unless we have to
        if (strtolower($this->fromEncoding) === strtolower($this->toEncoding)) {
            return $string;
        }

        // 'HTML-ENTITIES' is not a valid encoding for iconv, so transcode manually
        if ($this->toEncoding === 'HTML-ENTITIES' && !$mbstring) {
            // [WIZDAM CLEANUP] Removed legacy PHP < 5.2.3 checks. 
            // We strictly use the 4-parameter version to prevent double encoding.
            return htmlentities($string, ENT_COMPAT, $this->fromEncoding, false);

        } elseif ($this->fromEncoding === 'HTML-ENTITIES' && !$mbstring) {
            // [WIZDAM CLEANUP] Removed legacy PHP < 4.3.0 checks.
            // Directly use html_entity_decode.
            return html_entity_decode($string, ENT_COMPAT, $this->toEncoding);

        // Special cases for transliteration ("down-sampling")
        } elseif ($this->translit && $iconv) {
            // Use the iconv library to transliterate
            // [WIZDAM] Cast return to string to satisfy strict return type (iconv can return false on failure)
            $result = iconv($this->fromEncoding, $this->toEncoding . '//TRANSLIT', $string);
            return $result === false ? $string : $result;

        } elseif ($this->translit && $this->fromEncoding === "UTF-8" && $this->toEncoding === "ASCII") {
            // Use the utf2ascii library
            // [WIZDAM] Path check for safety
            $libPath = './lib/pkp/lib/phputf8/utf8_to_ascii.php';
            if (file_exists($libPath)) {
                require_once $libPath;
                if (function_exists('utf8_to_ascii')) {
                    return utf8_to_ascii($string);
                }
            }
            // Fallback if library missing
            return $string;

        } elseif ($mbstring) {
            // Use the mbstring library to transcode
            return mb_convert_encoding($string, $this->toEncoding, $this->fromEncoding);

        } elseif ($iconv) {
            // Use the iconv library to transcode
            $result = iconv($this->fromEncoding, $this->toEncoding . '//IGNORE', $string);
            return $result === false ? $string : $result;

        } else {
            // Fail gracefully by returning the original string unchanged
            return $string;
        }
    }
}
?>
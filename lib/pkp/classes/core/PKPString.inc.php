<?php
declare(strict_types=1);

/**
 * @file classes/core/PKPString.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * [WIZDAM EDITION v3.4]
 * - Refactored for PHP 8.1+ Strict Mode
 * - Converted to Static Class Architecture
 * - Fixed deprecated string access syntax ({}) -> ([])
 * - Enhanced Type Safety
 *
 * @class PKPString
 * @ingroup core
 *
 * @brief String manipulation wrapper class.
 */

/*
 * Perl-compatibile regular expression (PCRE) constants:
 * These are defined application-wide for consistency
 *
 * Originally published under the "New BSD License"
 * http://www.opensource.org/licenses/bsd-license.php
 * Copyright (c) 2005-2008, Stephan Luckow <stephan.luckow@uni-bielefeld.de>
 * All rights reserved.
 */
define('PCRE_URI', '(?:([a-z][-+.a-z0-9]*):)?' .                                         // Scheme
                   '(?://' .
                   '(?:((?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'();:\&=+$,])*)@)?' .               // User
                   '(?:((?:[a-z0-9](?:[-a-z0-9]*[a-z0-9])?\.)*[a-z](?:[a-z0-9]+)?\.?)' . // Hostname
                   '|([0-9]{1,3}(?:\.[0-9]{1,3}){3}))' .                                  // IP Address
                   '(?::([0-9]*))?)' .                                                    // Port
                   '((?:/(?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'():@\&=+$,])*)*/?)?' .           // Path
                   '(?:\?([^#]*))?' .                                                     // Query String
                   '(?:\#((?:%[0-9a-f]{2}|[-a-z0-9_.!~*\'();/?:@\&=+$,])*))?');           // Fragment

// RFC-2822 email addresses
define('PCRE_EMAIL_ADDRESS',
    '[-a-z0-9!#\$%&\'\*\+\/=\?\^_\`\{\|\}~]' . '+' . // One or more atom characters.
    '(\.' . '[-a-z0-9!#\$%&\'\*\+\/=\?\^_\`\{\|\}~]' . '+)*'. // Followed by zero or more dot separated sets of one or more atom characters.
    '@'. // Followed by an "at" character.
    '(' . '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)' . '{1,63}\.)+'. // Followed by one or max 63 domain characters (dot separated).
    '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)' . '{2,63}' // Must be followed by one set consisting a period of two or max 63 domain characters.
    );

// Two different types of camel case: one for class names and one for method names
define ('CAMEL_CASE_HEAD_UP', 0x01);
define ('CAMEL_CASE_HEAD_DOWN', 0x02);

define('DEFAULT_ALLOWED_HTML', '<a> <em> <strong> <cite> <code> <ul> <ol> <li> <dl> <dt> <dd> <b> <i> <u> <img src|alt> <sup> <sub> <br> <p>');

class PKPString {
    
    /**
     * Perform initialization required for the string wrapper library.
     */
    public static function init(): void {
        $clientCharset = strtolower((string) Config::getVar('i18n', 'client_charset'));

        // Check if mbstring is installed (requires PHP >= 4.3.0)
        if (self::hasMBString()) {
            // mbstring routines are available
            if (!defined('ENABLE_MBSTRING')) {
                define('ENABLE_MBSTRING', true);
            }

            // Set up required ini settings for mbstring
            mb_internal_encoding($clientCharset);
            mb_substitute_character(63);        // question mark
        }

        // Define modifier to be used in regexp_* routines
        if ($clientCharset === 'utf-8' && self::hasPCREUTF8()) {
            if (!defined('PCRE_UTF8')) define('PCRE_UTF8', 'u');
        } else {
            if (!defined('PCRE_UTF8')) define('PCRE_UTF8', '');
        }

        if (!defined('USE_HTML_PURIFIER')) {
            define('USE_HTML_PURIFIER', 1);
        }
    }

    /**
     * Check if server has the mbstring library.
     * @return bool
     */
    public static function hasMBString(): bool {
        static $hasMBString;
        if (isset($hasMBString)) return $hasMBString;

        // If string overloading is active, it will break many of the
        // native implementations. mbstring.func_overload must be set
        // to 0, 1 or 4 in php.ini (string overloading disabled).
        $funcOverload = ini_get('mbstring.func_overload');
        if ($funcOverload && defined('MB_OVERLOAD_STRING')) {
            $hasMBString = false;
        } else {
            $hasMBString = (
                extension_loaded('mbstring') &&
                function_exists('mb_strlen') &&
                function_exists('mb_strpos') &&
                function_exists('mb_strrpos') &&
                function_exists('mb_substr') &&
                function_exists('mb_strtolower') &&
                function_exists('mb_strtoupper') &&
                function_exists('mb_substr_count') &&
                function_exists('mb_send_mail')
            );
        }
        return $hasMBString;
    }

    /**
     * Check if server supports the PCRE_UTF8 modifier.
     * @return bool
     */
    public static function hasPCREUTF8(): bool {
        // Evil check to see if PCRE_UTF8 is supported
        if (@preg_match('//u', '')) {
            return true;
        } else {
            return false;
        }
    }

    //
    // Wrappers for basic string manipulation routines.
    //

    /**
     * @see http://ca.php.net/manual/en/function.strlen.php
     * @param string $string
     * @return int
     */
    public static function strlen(string $string): int {
        if (defined('ENABLE_MBSTRING')) {
            // In modern PHP/Wizdam environment, we prefer native mb_ calls if available
            // but we keep the require structure for library consistency if files exist
            if (file_exists('./lib/pkp/lib/phputf8/mbstring/core.php')) {
                require_once './lib/pkp/lib/phputf8/mbstring/core.php';
            }
            return mb_strlen($string);
        } else {
            require_once './lib/pkp/lib/phputf8/utils/unicode.php';
            require_once './lib/pkp/lib/phputf8/native/core.php';
            return utf8_strlen($string);
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.strpos.php
     * @param string $haystack
     * @param string $needle
     * @param int $offset
     * @return int|false
     */
    public static function strpos(string $haystack, string $needle, int $offset = 0) {
        if (defined('ENABLE_MBSTRING')) {
             if (file_exists('./lib/pkp/lib/phputf8/mbstring/core.php')) {
                require_once './lib/pkp/lib/phputf8/mbstring/core.php';
             }
             return mb_strpos($haystack, $needle, $offset);
        } else {
            require_once './lib/pkp/lib/phputf8/utils/unicode.php';
            require_once './lib/pkp/lib/phputf8/native/core.php';
            return utf8_strpos($haystack, $needle, $offset);
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.strrpos.php
     * @param string $haystack
     * @param string $needle
     * @return int|false
     */
    public static function strrpos(string $haystack, string $needle) {
        if (defined('ENABLE_MBSTRING')) {
            if (file_exists('./lib/pkp/lib/phputf8/mbstring/core.php')) {
                require_once './lib/pkp/lib/phputf8/mbstring/core.php';
            }
            return mb_strrpos($haystack, $needle);
        } else {
            require_once './lib/pkp/lib/phputf8/utils/unicode.php';
            require_once './lib/pkp/lib/phputf8/native/core.php';
            return utf8_strrpos($haystack, $needle);
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.substr.php
     * @param string $string
     * @param int $start
     * @param int|false|null $length
     * @return string
     */
    public static function substr(string $string, int $start, $length = false): string {
        if (defined('ENABLE_MBSTRING')) {
            if (file_exists('./lib/pkp/lib/phputf8/mbstring/core.php')) {
                require_once './lib/pkp/lib/phputf8/mbstring/core.php';
            }
            return mb_substr($string, $start, $length === false ? null : $length);
        } else {
            require_once './lib/pkp/lib/phputf8/utils/unicode.php';
            require_once './lib/pkp/lib/phputf8/native/core.php';
            // The default length value for the native implementation differs
            if ($length === false) $length = null;
            return utf8_substr($string, $start, $length);
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.substr_replace.php
     * @param string $string
     * @param string $replacement
     * @param int $start
     * @param int|null $length
     * @return string
     */
    public static function substr_replace(string $string, string $replacement, int $start, ?int $length = null): string {
        if (extension_loaded('mbstring') === true) {
            $string_length = self::strlen($string);

            if ($start < 0) {
                $start = max(0, $string_length + $start);
            } else if ($start > $string_length) {
                $start = $string_length;
            }

            if ($length !== null && $length < 0) {
                $length = max(0, $string_length - $start + $length);
            } else if ($length === null || $length > $string_length) {
                $length = $string_length;
            }

            if (($start + $length) > $string_length) {
                $length = $string_length - $start;
            }

            return self::substr($string, 0, $start) . $replacement . self::substr($string, $start + $length, $string_length - $start - $length);
        }

        return ($length === null) 
            ? substr_replace($string, $replacement, $start) 
            : substr_replace($string, $replacement, $start, $length);
    }

    /**
     * @see http://ca.php.net/manual/en/function.strtolower.php
     * @param string $string
     * @return string
     */
    public static function strtolower(string $string): string {
        if (defined('ENABLE_MBSTRING')) {
            if (file_exists('./lib/pkp/lib/phputf8/mbstring/core.php')) {
                require_once './lib/pkp/lib/phputf8/mbstring/core.php';
            }
            return mb_strtolower($string);
        } else {
            require_once './lib/pkp/lib/phputf8/utils/unicode.php';
            require_once './lib/pkp/lib/phputf8/native/core.php';
            return utf8_strtolower($string);
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.strtoupper.php
     * @param string $string
     * @return string
     */
    public static function strtoupper(string $string): string {
        if (defined('ENABLE_MBSTRING')) {
            if (file_exists('./lib/pkp/lib/phputf8/mbstring/core.php')) {
                require_once './lib/pkp/lib/phputf8/mbstring/core.php';
            }
            return mb_strtoupper($string);
        } else {
            require_once './lib/pkp/lib/phputf8/utils/unicode.php';
            require_once './lib/pkp/lib/phputf8/native/core.php';
            return utf8_strtoupper($string);
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.ucfirst.php
     * @param string $string
     * @return string
     */
    public static function ucfirst(string $string): string {
        if (defined('ENABLE_MBSTRING')) {
            if (file_exists('./lib/pkp/lib/phputf8/mbstring/core.php')) {
                require_once './lib/pkp/lib/phputf8/mbstring/core.php';
                require_once './lib/pkp/lib/phputf8/ucfirst.php';
            }
            return utf8_ucfirst($string); // Assuming utf8_ucfirst handles mbstring logic if enabled in lib
        } else {
            require_once './lib/pkp/lib/phputf8/utils/unicode.php';
            require_once './lib/pkp/lib/phputf8/native/core.php';
            require_once './lib/pkp/lib/phputf8/ucfirst.php';
            return utf8_ucfirst($string);
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.substr_count.php
     * @param string $haystack
     * @param string $needle
     * @return int
     */
    public static function substr_count(string $haystack, string $needle): int {
        if (defined('ENABLE_MBSTRING')) {
            return mb_substr_count($haystack, $needle);
        } else {
            return substr_count($haystack, $needle);
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.encode_mime_header.php
     * @param string $string
     * @return string
     */
    public static function encode_mime_header(string $string): string {
        if (defined('ENABLE_MBSTRING')) {
            return mb_encode_mimeheader($string, mb_internal_encoding(), 'B', MAIL_EOL);
        }  else {
            return $string;
        }
    }

    /**
     * @see http://ca.php.net/manual/en/function.mail.php
     * @param string $to
     * @param string $subject
     * @param string $message
     * @param string $additional_headers
     * @param string $additional_parameters
     * @return bool
     */
    public static function mail(string $to, string $subject, string $message, string $additional_headers = '', string $additional_parameters = ''): bool {
        // Cannot use mb_send_mail as it base64 encodes the whole body of the email,
        // making it useless for multipart emails
        if (empty($additional_parameters)) {
            return mail($to, $subject, $message, $additional_headers);
        } else {
            return mail($to, $subject, $message, $additional_headers, $additional_parameters);
        }
    }

    //
    // Wrappers for PCRE-compatible regular expression routines.
    //

    /**
     * @see http://ca.php.net/manual/en/function.regexp_quote.php
     * @param string $string
     * @param string $delimiter
     * @return string
     */
    public static function regexp_quote(string $string, string $delimiter = '/'): string {
        return preg_quote($string, $delimiter);
    }

    /**
     * @see http://ca.php.net/manual/en/function.regexp_grep.php
     * @param string $pattern
     * @param array $input
     * @return array|false
     */
    public static function regexp_grep(string $pattern, array $input) {
        // Note: Logic for utf8_bad_strip on array input would need iteration, assuming original logic didn't account for array or PCRE_UTF8 handling handles it.
        // Simplified for Wizdam v3.4 to trust input or standard behavior.
        return preg_grep($pattern . (defined('PCRE_UTF8') ? PCRE_UTF8 : ''), $input);
    }

    /**
     * @see http://ca.php.net/manual/en/function.regexp_match.php
     * @param string $pattern
     * @param string $subject
     * @return int|false
     */
    public static function regexp_match(string $pattern, string $subject) {
        if (defined('PCRE_UTF8') && PCRE_UTF8 && !self::utf8_compliant($subject)) $subject = self::utf8_bad_strip($subject);
        return preg_match($pattern . (defined('PCRE_UTF8') ? PCRE_UTF8 : ''), $subject);
    }

    /**
     * @see http://ca.php.net/manual/en/function.regexp_match_get.php
     * @param string $pattern
     * @param string $subject
     * @param array $matches (Reference)
     * @return int|false
     */
    public static function regexp_match_get(string $pattern, string $subject, &$matches) {
        if (defined('PCRE_UTF8') && PCRE_UTF8 && !self::utf8_compliant($subject)) $subject = self::utf8_bad_strip($subject);
        return preg_match($pattern . (defined('PCRE_UTF8') ? PCRE_UTF8 : ''), $subject, $matches);
    }

    /**
     * @see http://ca.php.net/manual/en/function.regexp_match_all.php
     * @param string $pattern
     * @param string $subject
     * @param array $matches (Reference)
     * @return int|false
     */
    public static function regexp_match_all(string $pattern, string $subject, &$matches) {
        if (defined('PCRE_UTF8') && PCRE_UTF8 && !self::utf8_compliant($subject)) $subject = self::utf8_bad_strip($subject);
        return preg_match_all($pattern . (defined('PCRE_UTF8') ? PCRE_UTF8 : ''), $subject, $matches);
    }

    /**
     * @see http://ca.php.net/manual/en/function.regexp_replace.php
     * @param string|array $pattern
     * @param string|array $replacement
     * @param string|array $subject
     * @param int $limit
     * @return string|array|null
     */
    public static function regexp_replace($pattern, $replacement, $subject, int $limit = -1) {
        // Handle string subject validation
        if (is_string($subject) && defined('PCRE_UTF8') && PCRE_UTF8 && !self::utf8_compliant($subject)) {
            $subject = self::utf8_bad_strip($subject);
        }
        
        $modifier = defined('PCRE_UTF8') ? PCRE_UTF8 : '';
        if (is_array($pattern)) {
             foreach ($pattern as &$p) {
                 $p .= $modifier;
             }
             unset($p);
        } else {
            $pattern .= $modifier;
        }

        return preg_replace($pattern, $replacement, $subject, $limit);
    }

    /**
     * @see http://ca.php.net/manual/en/function.regexp_replace_callback.php
     * @param string|array $pattern
     * @param callable $callback
     * @param string|array $subject
     * @param int $limit
     * @return string|array|null
     */
    public static function regexp_replace_callback($pattern, callable $callback, $subject, int $limit = -1) {
        if (is_string($subject) && defined('PCRE_UTF8') && PCRE_UTF8 && !self::utf8_compliant($subject)) {
            $subject = self::utf8_bad_strip($subject);
        }
        
        $modifier = defined('PCRE_UTF8') ? PCRE_UTF8 : '';
        if (is_array($pattern)) {
            foreach ($pattern as &$p) {
                $p .= $modifier;
            }
            unset($p);
        } else {
            $pattern .= $modifier;
        }

        return preg_replace_callback($pattern, $callback, $subject, $limit);
    }

    /**
     * @see http://ca.php.net/manual/en/function.regexp_split.php
     * @param string $pattern
     * @param string $subject
     * @param int $limit
     * @return array|false
     */
    public static function regexp_split(string $pattern, string $subject, int $limit = -1) {
        if (defined('PCRE_UTF8') && PCRE_UTF8 && !self::utf8_compliant($subject)) $subject = self::utf8_bad_strip($subject);
        return preg_split($pattern . (defined('PCRE_UTF8') ? PCRE_UTF8 : ''), $subject, $limit);
    }

    /**
     * @see http://ca.php.net/manual/en/function.mime_content_type.php
     * @param string $filename
     * @param string $suggestedExtension
     * @return string|null
     */
    public static function mime_content_type(string $filename, string $suggestedExtension = ''): ?string {
        $result = null;
        if (function_exists('mime_content_type')) {
            $result = mime_content_type($filename);
            // mime_content_type appears to return a charset
            // (erroneously?) in recent versions of PHP5
            if (($i = strpos((string)$result, ';')) !== false) {
                $result = trim(substr((string)$result, 0, $i));
            }
        } elseif (function_exists('finfo_open')) {
            $fi = Registry::get('fileInfo', true, null);
            if ($fi === null) {
                $fi = finfo_open(FILEINFO_MIME, (string)Config::getVar('finfo', 'mime_database_path'));
            }
            if ($fi !== false) {
                $result = strtok(finfo_file($fi, $filename), ' ;');
            }
        }

        // Fall back on an external "file" tool
        if (!$result) {
            $f = escapeshellarg($filename);
            // Suppress error output
            $result = trim(`file --brief --mime $f`);
            // Make sure we just return the mime type.
            if (($i = strpos($result, ';')) !== false) {
                $result = trim(substr($result, 0, $i));
            }
        }
        
        // Check ambiguous mimetypes against extension
        $parts = explode('.', $filename);
        $ext = array_pop($parts);
        if ($suggestedExtension) {
            $ext = $suggestedExtension;
        }
        // SUGGESTED_EXTENSION:DETECTED_MIME_TYPE => OVERRIDE_MIME_TYPE
        $ambiguities = [
            'css:text/x-c' => 'text/css',
            'css:text/plain' => 'text/css',
            'xlsx:application/zip' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xltx:application/zip' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'potx:application/zip' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'ppsx:application/zip' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'pptx:application/zip' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'sldx:application/zip' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'docx:application/zip' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dotx:application/zip' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'epub:application/zip' => 'application/epub+zip',
        ];
        
        $key = strtolower($ext.':'.$result);
        if (isset($ambiguities[$key])) {
            $result = $ambiguities[$key];
        }
        return $result;
    }


    /**
     * Strip unsafe HTML from the input text. Covers XSS attacks.
     * @param string $input input string
     * @return string
     */
    public static function stripUnsafeHtml($input): string {
        // Suppress HTMLPurifier continue warnings
        $oldErrorReporting = error_reporting();
        error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR);
        // If possible, use the HTML purifier.
        if (defined('USE_HTML_PURIFIER')) {
            require_once('lib/pkp/lib/htmlpurifier/library/HTMLPurifier.path.php');
            require_once('HTMLPurifier.includes.php');
            static $purifier;
            if (!isset($purifier)) {
                $config = HTMLPurifier_Config::createDefault();
                $config->set('Core.Encoding', (string)Config::getVar('i18n', 'client_charset'));
                $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
                // Transform the old allowed_html setting into
                // a form HTMLPurifier can use.
                $allowed = preg_replace(
                    '/<(\w+)[ ]?([^>]*)>[ ]?/',
                    '${1}[${2}],',
                    (string)Config::getVar('security', 'allowed_html', DEFAULT_ALLOWED_HTML)
                );
                $config->set('HTML.Allowed', $allowed);
                $config->set('Cache.SerializerPath', 'cache');
                $purifier = new HTMLPurifier($config);
            }
            $clean = $purifier->purify($input);
            // Restore error reporting
            error_reporting($oldErrorReporting);
            return $clean;
        }

        // Restore error reporting for fallback
        error_reporting($oldErrorReporting);

        // Fall back on imperfect but PHP4-capable implementation.
        static $allowedHtml;
        if (!isset($allowedHtml)) {
            $allowedHtml = preg_replace(
                '/<(\w+)( [^>]+)*>/', // Strip out attr specs
                '<${1}> ',
                (string)Config::getVar('security', 'allowed_html', DEFAULT_ALLOWED_HTML)
            );
        }

        $html = strip_tags($input, $allowedHtml);

        // Change space entities to space characters
        $html = preg_replace('/&#(x0*20|0*32);?/i', ' ', $html);

        // Remove non-printable characters
        $html = preg_replace('/&#x?0*([9A-D]|1[0-3]);/i', '&nbsp;', $html);
        $html = preg_replace('/&#x?0*[9A-D]([^0-9A-F]|$)/i', '&nbsp\\1', $html);
        $html = preg_replace('/&#0*(9|1[0-3])([^0-9]|$)/i', '&nbsp\\2', $html);

        // Remove overly long numeric entities
        $html = preg_replace('/&#x?0*[0-9A-F]{6,};?/i', '&nbsp;', $html);

        /* Get all attribute="javascript:foo()" tags. */
        $preg    = '/((&#0*61;?|&#x0*3D;?|=)|'
            . '((u|&#0*85;?|&#x0*55;?|&#0*117;?|&#x0*75;?)\s*'
            . '(r|&#0*82;?|&#x0*52;?|&#0*114;?|&#x0*72;?)\s*'
            . '(l|&#0*76;?|&#x0*4c;?|&#0*108;?|&#x0*6c;?)\s*'
            . '(\()))\s*'
            . '(&#0*34;?|&#x0*22;?|"|&#0*39;?|&#x0*27;?|\')?'
            . '[^>]*\s*'
            . '(s|&#0*83;?|&#x0*53;?|&#0*115;?|&#x0*73;?)\s*'
            . '(c|&#0*67;?|&#x0*43;?|&#0*99;?|&#x0*63;?)\s*'
            . '(r|&#0*82;?|&#x0*52;?|&#0*114;?|&#x0*72;?)\s*'
            . '(i|&#0*73;?|&#x0*49;?|&#0*105;?|&#x0*69;?)\s*'
            . '(p|&#0*80;?|&#x0*50;?|&#0*112;?|&#x0*70;?)\s*'
            . '(t|&#0*84;?|&#x0*54;?|&#0*116;?|&#x0*74;?)\s*'
            . '(:|&#0*58;?|&#x0*3a;?)/i';
        $html = preg_replace($preg, '\1\8PKPCleaned', $html);

        /* Get all on<foo>="bar()". NEVER allow these. */
        $html =    preg_replace('/([\s"\']+'
            . '(o|&#0*79;?|&#0*4f;?|&#0*111;?|&#0*6f;?)'
            . '(n|&#0*78;?|&#0*4e;?|&#0*110;?|&#0*6e;?)'
            . '\w+)\s*=/i', '\1PKPCleaned=', $html);

        $pattern = [
            '|<([^>]*)&{.*}([^>]*)>|',
            '|<([^>]*)mocha:([^>]*)>|i',
            '|<([^>]*)binding:([^>]*)>|i'
        ];
        $replace = ['<&{;}\3>', '<\1PKPCleaned:\2>', '<\1PKPCleaned:\2>'];
        $html = preg_replace($pattern, $replace, $html);

        return $html;
    }

    /**
     * Convert limited HTML into a string.
     * @param string $html
     * @return string
     */
    public static function html2text(string $html): string {
        $html = self::regexp_replace('/<[\/]?p>/', "\n", $html);
        $html = self::regexp_replace('/<li>/', '&bull; ', $html);
        $html = self::regexp_replace('/<\/li>/', "\n", $html);
        $html = self::regexp_replace('/<br[ ]?[\/]?>/', "\n", $html);
        $html = self::html2utf(strip_tags($html));
        return $html;
    }

    //
    // Wrappers for UTF-8 validation routines
    //

    /**
     * Detect whether a string contains non-ascii multibyte sequences in the UTF-8 range
     * @param string $str
     * @return bool
     */
    public static function utf8_is_valid(string $str): bool {
        require_once './lib/pkp/lib/phputf8/utils/validation.php';
        return utf8_is_valid($str);
    }

    /**
     * Tests whether a string complies as UTF-8
     * @param string $str
     * @return bool
     */
    public static function utf8_compliant(string $str): bool {
        require_once './lib/pkp/lib/phputf8/utils/validation.php';
        return utf8_compliant($str);
    }

    /**
     * Locates the first bad byte in a UTF-8 string returning it's byte index
     * @param string $str
     * @return int|false
     */
    public static function utf8_bad_find(string $str) {
        require_once './lib/pkp/lib/phputf8/utils/bad.php';
        return utf8_bad_find($str);
    }

    /**
     * Strips out any bad bytes from a UTF-8 string and returns the rest
     * @param string $str
     * @return string
     */
    public static function utf8_bad_strip(string $str): string {
        require_once './lib/pkp/lib/phputf8/utils/bad.php';
        return utf8_bad_strip($str);
    }

    /**
     * Replace bad bytes with an alternative character
     * @param string $str
     * @param string $replace
     * @return string
     */
    public static function utf8_bad_replace(string $str, string $replace = '?'): string {
        require_once './lib/pkp/lib/phputf8/utils/bad.php';
        return utf8_bad_replace($str, $replace);
    }

    /**
     * Replace bad bytes with an alternative character - ASCII character
     * @param string $str
     * @return string
     */
    public static function utf8_strip_ascii_ctrl(string $str): string {
        require_once './lib/pkp/lib/phputf8/utils/ascii.php';
        return utf8_strip_ascii_ctrl($str);
    }

    /**
     * Normalize a string in an unknown (non-UTF8) encoding into a valid UTF-8 sequence
     * @param string $str
     * @return string
     */
    public static function utf8_normalize(string $str): string {
        import('lib.pkp.classes.core.Transcoder');

        if (self::hasMBString()) {
            // NB: CP-1252 often segfaults; we've left it out here but it will detect as 'ISO-8859-1'
            $mb_encoding_order = 'UTF-8, UTF-7, ASCII, ISO-8859-1, EUC-JP, SJIS, eucJP-win, SJIS-win, JIS, ISO-2022-JP';
            $detected_encoding = mb_detect_encoding($str, $mb_encoding_order, false);
        } elseif (function_exists('iconv') && strlen(iconv('CP1252', 'UTF-8', $str)) != strlen(iconv('ISO-8859-1', 'UTF-8', $str))) {
            // use iconv to detect CP-1252, assuming default ISO-8859-1
            $detected_encoding = 'CP1252';
        } else {
            // assume ISO-8859-1, PHP default
            $detected_encoding = 'ISO-8859-1';
        }

        if (!$detected_encoding) {
             $detected_encoding = 'ISO-8859-1';
        }

        // transcode CP-1252/ISO-8859-1 into HTML entities
        if ('ISO-8859-1' == $detected_encoding || 'CP1252' == $detected_encoding) {
            $trans = new Transcoder('CP1252', 'HTML-ENTITIES');
            $str = $trans->trans($str);
        }

        // transcode from detected encoding to to UTF-8
        $trans = new Transcoder($detected_encoding, 'UTF-8');
        $str = $trans->trans($str);

        return $str;
    }

    /**
     * US-ASCII transliterations of Unicode text
     * @param string $str
     * @return string
     */
    public static function utf8_to_ascii(string $str): string {
        require_once('./lib/pkp/lib/phputf8/utf8_to_ascii.php');
        return utf8_to_ascii($str);
    }

    /**
     * Returns the UTF-8 string corresponding to the unicode value
     * @param int $num
     * @return string
     */
    public static function code2utf(int $num): string {
        if ($num < 128) return chr($num);
        if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
        if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
        return '';
    }

    /**
     * Convert UTF-8 encoded characters in a string to escaped HTML entities
     * [WIZDAM] Fixed deprecated usage of curly braces for string offset
     * @param string $str
     * @return string
     */
    public static function utf2html(string $str): string {
        $ret = "";
        $max = strlen($str);
        $last = 0;  // keeps the index of the last regular character

        for ($i=0; $i<$max; $i++) {
            $c = $str[$i];
            $c1 = ord($c);
            if ($c1>>5 == 6) {                                            // 110x xxxx
                $ret .= substr($str, $last, $i-$last);            // append regular chars
                $c1 &= 31;
                $c2 = ord($str[++$i]);
                $c2 &= 63;
                $c2 |= (($c1 & 3) << 6);
                $c1 >>= 2;
                $ret .= "&#" . ($c1 * 0x100 + $c2) . ";";
                $last = $i+1;
            }
            elseif ($c1>>4 == 14) {                                       // 1110 xxxx
                $ret .= substr($str, $last, $i-$last);
                $c2 = ord($str[++$i]);
                $c3 = ord($str[++$i]);
                $c1 &= 15;
                $c2 &= 63;
                $c3 &= 63;
                $c3 |= (($c2 & 3) << 6);
                $c2 >>=2;
                $c2 |= (($c1 & 15) << 4);
                $c1 >>= 4;
                $ret .= '&#' . (($c1 * 0x10000) + ($c2 * 0x100) + $c3) . ';';
                $last = $i+1;
            }
        }
        $ret .= substr($str, $last, $i);

        return $ret;
    }

    /**
     * Convert numeric HTML entities in a string to UTF-8 encoded characters
     * @param string $str
     * @return string
     */
    public static function html2utf(string $str): string {
        // convert named entities to numeric entities
        $str = strtr($str, self::getHTMLEntities());

        // use PCRE-aware replace function to replace numeric entities
        $str = self::regexp_replace_callback('~&#x([0-9a-f]+);~i', function ($matches) { return self::code2utf((int)hexdec($matches[1])); }, $str);
        $str = self::regexp_replace_callback('~&#([0-9]+);~', function ($matches) { return self::code2utf((int)$matches[1]); }, $str);

        return $str;
    }

    /**
     * Return an associative array of named->numeric HTML entities
     * @return array
     */
    public static function getHTMLEntities(): array {
        // define the conversion table
        $html_entities = [
            "&Aacute;" => "&#193;",    "&aacute;" => "&#225;",    "&Acirc;" => "&#194;",
            "&acirc;" => "&#226;",     "&acute;" => "&#180;",     "&AElig;" => "&#198;",
            "&aelig;" => "&#230;",     "&Agrave;" => "&#192;",    "&agrave;" => "&#224;",
            "&alefsym;" => "&#8501;",  "&Alpha;" => "&#913;",     "&alpha;" => "&#945;",
            "&amp;" => "&#38;",        "&and;" => "&#8743;",      "&ang;" => "&#8736;",
            "&apos;" => "&#39;",       "&Aring;" => "&#197;",     "&aring;" => "&#229;",
            "&asymp;" => "&#8776;",    "&Atilde;" => "&#195;",    "&atilde;" => "&#227;",
            "&Auml;" => "&#196;",      "&auml;" => "&#228;",      "&bdquo;" => "&#8222;",
            "&Beta;" => "&#914;",      "&beta;" => "&#946;",      "&brvbar;" => "&#166;",
            "&bull;" => "&#8226;",     "&cap;" => "&#8745;",      "&Ccedil;" => "&#199;",
            "&ccedil;" => "&#231;",    "&cedil;" => "&#184;",     "&cent;" => "&#162;",
            "&Chi;" => "&#935;",       "&chi;" => "&#967;",       "&circ;" => "&#94;",
            "&clubs;" => "&#9827;",    "&cong;" => "&#8773;",     "&copy;" => "&#169;",
            "&crarr;" => "&#8629;",    "&cup;" => "&#8746;",      "&curren;" => "&#164;",
            "&dagger;" => "&#8224;",   "&Dagger;" => "&#8225;",   "&darr;" => "&#8595;",
            "&dArr;" => "&#8659;",     "&deg;" => "&#176;",       "&Delta;" => "&#916;",
            "&delta;" => "&#948;",     "&diams;" => "&#9830;",    "&divide;" => "&#247;",
            "&Eacute;" => "&#201;",    "&eacute;" => "&#233;",    "&Ecirc;" => "&#202;",
            "&ecirc;" => "&#234;",     "&Egrave;" => "&#200;",    "&egrave;" => "&#232;",
            "&empty;" => "&#8709;",    "&emsp;" => "&#8195;",     "&ensp;" => "&#8194;",
            "&Epsilon;" => "&#917;",   "&epsilon;" => "&#949;",   "&equiv;" => "&#8801;",
            "&Eta;" => "&#919;",       "&eta;" => "&#951;",       "&ETH;" => "&#208;",
            "&eth;" => "&#240;",       "&Euml;" => "&#203;",      "&euml;" => "&#235;",
            "&euro;" => "&#8364;",     "&exist;" => "&#8707;",    "&fnof;" => "&#402;",
            "&forall;" => "&#8704;",   "&frac12;" => "&#189;",    "&frac14;" => "&#188;",
            "&frac34;" => "&#190;",    "&frasl;" => "&#8260;",    "&Gamma;" => "&#915;",
            "&gamma;" => "&#947;",     "&ge;" => "&#8805;",       "&gt;" => "&#62;",
            "&harr;" => "&#8596;",     "&hArr;" => "&#8660;",     "&hearts;" => "&#9829;",
            "&hellip;" => "&#8230;",   "&Iacute;" => "&#205;",    "&iacute;" => "&#237;",
            "&Icirc;" => "&#206;",     "&icirc;" => "&#238;",     "&iexcl;" => "&#161;",
            "&Igrave;" => "&#204;",    "&igrave;" => "&#236;",    "&image;" => "&#8465;",
            "&infin;" => "&#8734;",    "&int;" => "&#8747;",      "&Iota;" => "&#921;",
            "&iota;" => "&#953;",      "&iquest;" => "&#191;",    "&isin;" => "&#8712;",
            "&Iuml;" => "&#207;",      "&iuml;" => "&#239;",      "&Kappa;" => "&#922;",
            "&kappa;" => "&#954;",     "&Lambda;" => "&#923;",    "&lambda;" => "&#955;",
            "&lang;" => "&#9001;",     "&laquo;" => "&#171;",     "&larr;" => "&#8592;",
            "&lArr;" => "&#8656;",     "&lceil;" => "&#8968;",
            "&ldquo;" => "&#8220;",    "&le;" => "&#8804;",       "&lfloor;" => "&#8970;",
            "&lowast;" => "&#8727;",   "&loz;" => "&#9674;",      "&lrm;" => "&#8206;",
            "&lsaquo;" => "&#8249;",   "&lsquo;" => "&#8216;",    "&lt;" => "&#60;",
            "&macr;" => "&#175;",      "&mdash;" => "&#8212;",    "&micro;" => "&#181;",
            "&middot;" => "&#183;",    "&minus;" => "&#45;",      "&Mu;" => "&#924;",
            "&mu;" => "&#956;",        "&nabla;" => "&#8711;",    "&nbsp;" => "&#160;",
            "&ndash;" => "&#8211;",    "&ne;" => "&#8800;",       "&ni;" => "&#8715;",
            "&not;" => "&#172;",       "&notin;" => "&#8713;",    "&nsub;" => "&#8836;",
            "&Ntilde;" => "&#209;",    "&ntilde;" => "&#241;",    "&Nu;" => "&#925;",
            "&nu;" => "&#957;",        "&Oacute;" => "&#211;",    "&oacute;" => "&#243;",
            "&Ocirc;" => "&#212;",     "&ocirc;" => "&#244;",     "&OElig;" => "&#338;",
            "&oelig;" => "&#339;",     "&Ograve;" => "&#210;",    "&ograve;" => "&#242;",
            "&oline;" => "&#8254;",    "&Omega;" => "&#937;",     "&omega;" => "&#969;",
            "&Omicron;" => "&#927;",   "&omicron;" => "&#959;",   "&oplus;" => "&#8853;",
            "&or;" => "&#8744;",       "&ordf;" => "&#170;",      "&ordm;" => "&#186;",
            "&Oslash;" => "&#216;",    "&oslash;" => "&#248;",    "&Otilde;" => "&#213;",
            "&otilde;" => "&#245;",    "&otimes;" => "&#8855;",   "&Ouml;" => "&#214;",
            "&ouml;" => "&#246;",      "&para;" => "&#182;",      "&part;" => "&#8706;",
            "&permil;" => "&#8240;",   "&perp;" => "&#8869;",     "&Phi;" => "&#934;",
            "&phi;" => "&#966;",       "&Pi;" => "&#928;",        "&pi;" => "&#960;",
            "&piv;" => "&#982;",       "&plusmn;" => "&#177;",    "&pound;" => "&#163;",
            "&prime;" => "&#8242;",    "&Prime;" => "&#8243;",    "&prod;" => "&#8719;",
            "&prop;" => "&#8733;",     "&Psi;" => "&#936;",       "&psi;" => "&#968;",
            "&quot;" => "&#34;",       "&radic;" => "&#8730;",    "&rang;" => "&#9002;",
            "&raquo;" => "&#187;",     "&rarr;" => "&#8594;",     "&rArr;" => "&#8658;",
            "&rceil;" => "&#8969;",    "&rdquo;" => "&#8221;",    "&real;" => "&#8476;",
            "&reg;" => "&#174;",       "&rfloor;" => "&#8971;",   "&Rho;" => "&#929;",
            "&rho;" => "&#961;",       "&rlm;" => "&#8207;",      "&rsaquo;" => "&#8250;",
            "&rsquo;" => "&#8217;",    "&sbquo;" => "&#8218;",    "&Scaron;" => "&#352;",
            "&scaron;" => "&#353;",    "&sdot;" => "&#8901;",     "&sect;" => "&#167;",
            "&shy;" => "&#173;",       "&Sigma;" => "&#931;",     "&sigma;" => "&#963;",
            "&sigmaf;" => "&#962;",    "&sim;" => "&#8764;",      "&spades;" => "&#9824;",
            "&sub;" => "&#8834;",      "&sube;" => "&#8838;",     "&sum;" => "&#8721;",
            "&sup1;" => "&#185;",      "&sup2;" => "&#178;",      "&sup3;" => "&#179;",
            "&sup;" => "&#8835;",      "&supe;" => "&#8839;",     "&szlig;" => "&#223;",
            "&Tau;" => "&#932;",       "&tau;" => "&#964;",       "&there4;" => "&#8756;",
            "&Theta;" => "&#920;",     "&theta;" => "&#952;",     "&thetasym;" => "&#977;",
            "&thinsp;" => "&#8201;",   "&THORN;" => "&#222;",     "&thorn;" => "&#254;",
            "&tilde;" => "&#126;",     "&times;" => "&#215;",     "&trade;" => "&#8482;",
            "&Uacute;" => "&#218;",    "&uacute;" => "&#250;",    "&uarr;" => "&#8593;",
            "&uArr;" => "&#8657;",     "&Ucirc;" => "&#219;",     "&ucirc;" => "&#251;",
            "&Ugrave;" => "&#217;",    "&ugrave;" => "&#249;",    "&uml;" => "&#168;",
            "&upsih;" => "&#978;",     "&Upsilon;" => "&#933;",   "&upsilon;" => "&#965;",
            "&Uuml;" => "&#220;",      "&uuml;" => "&#252;",      "&weierp;" => "&#8472;",
            "&Xi;" => "&#926;",        "&xi;" => "&#958;",        "&Yacute;" => "&#221;",
            "&yacute;" => "&#253;",    "&yen;" => "&#165;",       "&yuml;" => "&#255;",
            "&Yuml;" => "&#376;",      "&Zeta;" => "&#918;",      "&zeta;" => "&#950;",
            "&zwj;" => "&#8205;",      "&zwnj;" => "&#8204;"
        ];

        return $html_entities;
    }

    /**
     * Wrapper around fputcsv
     * @param resource $handle
     * @param array $fields
     * @param string $delimiter
     * @param string $enclosure
     * @return int|false
     */
    public static function fputcsv($handle, array $fields = [], string $delimiter = ',', string $enclosure = '"') {
        // [WIZDAM] Removed reference & on $handle as resources are identifiers
        if (function_exists('fputcsv')) {
            return fputcsv($handle, $fields, $delimiter, $enclosure);
        }
        $str = '';
        $escape_char = '\\';
        foreach ($fields as $value) {
            if (    strpos($value, $delimiter) !== false ||
                strpos($value, $enclosure) !== false ||
                strpos($value, "\n") !== false ||
                strpos($value, "\r") !== false ||
                strpos($value, "\t") !== false ||
                strpos($value, ' ') !== false
            ) {
                $str2 = $enclosure;
                $escaped = 0;
                $len = strlen($value);
                for ($i=0; $i<$len; $i++) {
                    if ($value[$i] == $escape_char) $escaped = 1;
                    elseif (!$escaped && $value[$i] == $enclosure) $str2 .= $enclosure;
                    else $escaped = 0;
                    $str2 .= $value[$i];
                }
                $str2 .= $enclosure;
                $str .= $str2 . $delimiter;
            } else {
                $str .= $value . $delimiter;
            }
        }
        $str = substr($str, 0, -1);
        $str .= "\n";
        return fwrite($handle, $str);
    }

    /**
     * Trim punctuation from a string
     * @param string $string
     * @return string
     */
    public static function trimPunctuation(string $string): string {
        return trim($string, ' ,.;:!?&()[]\\/');
    }

    /**
     * Convert a string to proper title case
     * @param string $title
     * @return string
     */
    public static function titleCase(string $title): string {
        AppLocale::requireComponents(LOCALE_COMPONENT_PKP_COMMON);
        $smallWords = explode(' ', __('common.titleSmallWords'));

        $words = explode(' ', $title);
        foreach ($words as $key => $word) {
            if ($key == 0 or !in_array(self::strtolower($word), $smallWords)) {
                $words[$key] = ucfirst(self::strtolower($word));
            } else {
                $words[$key] = self::strtolower($word);
            }
        }

        $newTitle = implode(' ', $words);
        return $newTitle;
    }

    /**
     * Iterate over an array of delimiters
     * @param array $delimiters
     * @param string $input
     * @return array
     */
    public static function iterativeExplode(array $delimiters, string $input): array {
        foreach($delimiters as $delimiter) {
            if (strstr($input, $delimiter) !== false) {
                return explode($delimiter, $input);
            }
        }
        return [$input];
    }

    /**
     * Transform "handler-class" to "HandlerClass"
     * and "my-op" to "myOp".
     * @param string $string
     * @param int $type
     * @return string
     */
    public static function camelize(string $string, int $type = CAMEL_CASE_HEAD_UP): string {
        assert($type == CAMEL_CASE_HEAD_UP || $type == CAMEL_CASE_HEAD_DOWN);

        // Transform "handler-class" to "HandlerClass" and "my-op" to "MyOp"
        $string = str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));

        // Transform "MyOp" to "myOp"
        if ($type == CAMEL_CASE_HEAD_DOWN) {
            $string = lcfirst($string);
        }

        return $string;
    }

    /**
     * Transform "HandlerClass" to "handler-class"
     * and "myOp" to "my-op".
     * @param string $string
     * @return string
     */
    public static function uncamelize(string $string): string {
        assert(!empty($string));

        // Transform "myOp" to "MyOp"
        $string = ucfirst($string);

        // Insert hyphens between words and return the string in lowercase
        $words = [];
        self::regexp_match_all('/[A-Z][a-z0-9]*/', $string, $words);
        
        // Safety check
        if (!isset($words[0]) || empty($words[0])) {
             return strtolower($string);
        }

        return strtolower(implode('-', $words[0]));
    }

    /**
     * Calculate the differences between two strings
     * @param string $originalString
     * @param string $editedString
     * @return array
     */
    public static function diff(string $originalString, string $editedString): array {
        // Split strings into character arrays (multi-byte compatible).
        foreach(['originalStringCharacters' => $originalString, 'editedStringCharacters' => $editedString] as $characterArrayName => $string) {
            ${$characterArrayName} = [];
            self::regexp_match_all('/./', $string, ${$characterArrayName});
            if (isset(${$characterArrayName}[0])) {
                ${$characterArrayName} = ${$characterArrayName}[0];
            }
        }

        // Determine the length of the strings.
        $originalStringLength = count($originalStringCharacters);
        $editedStringLength = count($editedStringCharacters);

        // Is there anything to compare?
        if ($originalStringLength == 0 && $editedStringLength == 0) return [];

        // Is the original string empty?
        if ($originalStringLength == 0) {
            return [[1 => $editedString]];
        }

        // Is the edited string empty?
        if ($editedStringLength == 0) {
            return [[-1 => $originalString]];
        }

        // Initialize the local indices:
        // 1) Create a character index for the edited string.
        $characterIndex = [];
        for($characterPosition = 0; $characterPosition < $editedStringLength; $characterPosition++) {
            $characterIndex[$editedStringCharacters[$characterPosition]][] = $characterPosition;
        }
        // 2) Initialize the substring and the length index.
        $substringIndex = $lengthIndex = [];

        // Iterate over the original string to identify
        // the largest common string.
        for($originalPosition = 0; $originalPosition < $originalStringLength; $originalPosition++) {
            // Find all occurrences of the original character
            // in the target string.
            $comparedCharacter = $originalStringCharacters[$originalPosition];

            // Do we have a commonality between the original string
            // and the edited string?
            if (isset($characterIndex[$comparedCharacter])) {
                // Loop over all commonalities.
                foreach($characterIndex[$comparedCharacter] as $editedPosition) {
                    // Calculate the current and the preceding position
                    // ids for indexation.
                    $currentPosition = $originalPosition . '-' . $editedPosition;
                    $previousPosition = ($originalPosition-1) . '-' . ($editedPosition-1);

                    // Does the occurrence in the target string continue
                    // an existing common substring or does it start
                    // a new one?
                    if (isset($substringIndex[$previousPosition])) {
                        // This is a continuation of an existing common
                        // substring...
                        $newSubstring = $substringIndex[$previousPosition].$comparedCharacter;
                        $newSubstringLength = self::strlen($newSubstring);

                        // Move the substring in the substring index.
                        $substringIndex[$currentPosition] = $newSubstring;
                        unset($substringIndex[$previousPosition]);

                        // Move the substring in the length index.
                        $lengthIndex[$newSubstringLength][$currentPosition] = $newSubstring;
                        unset($lengthIndex[$newSubstringLength - 1][$previousPosition]);
                    } else {
                        // Start a new common substring...
                        // Add the substring to the substring index.
                        $substringIndex[$currentPosition] = $comparedCharacter;

                        // Add the substring to the length index.
                        $lengthIndex[1][$currentPosition] = $comparedCharacter;
                    }
                }
            }
        }

        // If we have no commonalities at all then mark the original
        // string as deleted and the edited string as added and
        // return.
        if (empty($lengthIndex)) {
            return [
                [ -1 => $originalString ],
                [ 1 => $editedString ]
            ];
        }

        // Pop the largest common substrings from the length index.
        end($lengthIndex);
        $largestSubstringLength = key($lengthIndex);

        // Take the first common substring if we have more than
        // one substring with the same length.
        reset($lengthIndex[$largestSubstringLength]);
        $largestSubstringPosition = key($lengthIndex[$largestSubstringLength]);
        list($largestSubstringEndOriginal, $largestSubstringEndEdited) = explode('-', $largestSubstringPosition);
        $largestSubstring = $lengthIndex[$largestSubstringLength][$largestSubstringPosition];

        // Add the largest common substring to the result set
        $diffResult = [[ 0 => $largestSubstring ]];

        // Prepend the diff of the substrings before the common substring
        // to the result diff (by recursion).
        $precedingSubstringOriginal = self::substr($originalString, 0, (int)$largestSubstringEndOriginal - $largestSubstringLength + 1);
        $precedingSubstringEdited = self::substr($editedString, 0, (int)$largestSubstringEndEdited - $largestSubstringLength + 1);
        $diffResult = array_merge(self::diff($precedingSubstringOriginal, $precedingSubstringEdited), $diffResult);

        // Append the diff of the substrings after thr common substring
        // to the result diff (by recursion).
        $succeedingSubstringOriginal = self::substr($originalString, (int)$largestSubstringEndOriginal + 1);
        $succeedingSubstringEdited = self::substr($editedString, (int)$largestSubstringEndEdited + 1);
        $diffResult = array_merge($diffResult, self::diff($succeedingSubstringOriginal, $succeedingSubstringEdited));

        // Return the array representing the diff.
        return $diffResult;
    }

    /**
     * Get a letter $steps places after 'A'
     * @param int $steps
     * @return string
     */
    public static function enumerateAlphabetically(int $steps): string {
        return chr(ord('A') + $steps);
    }

    /**
     * Create a new UUID (version 4)
     * @return string
     */
    public static function generateUUID(): string {
        // [WIZDAM] Removed mt_srand, PHP handles seeding automatically since 7.1
        $charid = strtoupper(md5(uniqid((string)rand(), true)));
        $hyphen = '-';
        $uuid = substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .'4'.substr($charid,13, 3).$hyphen
                .strtoupper(dechex(hexdec(ord(substr($charid,16,1))) % 4 + 8)).substr($charid,17, 3).$hyphen
                .substr($charid,20,12);
        return $uuid;
    }
    
    /**
     * Membuat string menjadi URL-safe "slug"
     * @param string $string
     * @return string
     */
    public static function slugify(string $string): string {
        $string = strtolower($string);
        $string = preg_replace('/[^a-z0-9\s-]/', '', $string); 
        $string = preg_replace('/[\s-]+/', '-', $string);    
        $string = trim($string, '-'); 
        return $string;
    }
}
?>
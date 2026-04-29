<?php
declare(strict_types=1);

/**
 * @file includes/functions.inc.php
 *
 * Copyright (c) 2013-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup index
 *
 * @brief Contains definitions for common functions used system-wide.
 * [WIZDAM EDITION] Enhanced for Namespaces, API Responses & Strict Typing.
 */

/**
 * [WIZDAM POLYFILL] PHP 8.0+ string functions — PHP 7.x safe 
 * (strncmp/substr sejak PHP 4)
 */
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return strncmp($haystack, $needle, strlen($needle)) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        if ($needle === '') return true;
        return substr($haystack, -strlen($needle)) === $needle;
    }
}

if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return $needle === '' || strpos($haystack, $needle) !== false;
    }
}

/**
 * Emulate a Java-style import statement.
 * [WIZDAM] Updated to support PSR-4 Autoloading bypass.
 * If a class contains backslashes, we assume it's handled by Composer/Autoloader.
 * @param string $class the complete name of the class to be imported
 */
if (!function_exists('import')) {
    function import(string $class): void {
        static $importedClasses = [];
        
        if (isset($importedClasses[$class])) return;

        if (strpos($class, '\\') !== false) {
            $importedClasses[$class] = true;
            return;
        }

        $filePath = str_replace('.', '/', $class) . '.inc.php';

        if (defined('BASE_SYS_DIR') && file_exists(BASE_SYS_DIR . '/' . $filePath)) {
            // include_once: graceful — eksekusi berlanjut ke require_once fallback jika gagal
            include_once BASE_SYS_DIR . '/' . $filePath;
        } else {
            // require_once: fatal jika tidak ditemukan — tidak ada jalur fallback lagi
            require_once($filePath);
        }
        
        $importedClasses[$class] = true;
    }
}

/**
 * Wrapper around die() to pretty-print an error message.
 * [WIZDAM BLUEPRINT] Uses custom Sangia error page for clean user experience.
 * @param string $reason
 * @param int $httpStatus (Status code: 404 for Not Found, 500 for Fatal Error)
 */
function fatalError(string $reason, int $httpStatus = 500): void {
    static $isErrorCondition = false;
    if ($isErrorCondition) { die('Recursive Fatal Error: ' . $reason); }
    $isErrorCondition = true;

    $statusHeader = $httpStatus === 404 ? 'HTTP/1.0 404 Not Found' : 'HTTP/1.1 500 Internal Server Error';
    header($statusHeader);

    // [WIZDAM FIX-A] Closure menggantikan goto
    $logAndDie = function() use ($reason): void {
        $applicationName = '';
        if (class_exists('Registry')) {
            $app = Registry::get('application', true, null);
            if ($app && method_exists($app, 'getName')) {
                $applicationName = $app->getName() . ': ';
            }
        }
        error_log($applicationName . $reason);
        // [WIZDAM FIX] PHP 8.4: E_USER_ERROR deprecated dalam trigger_error()
        // Ganti dengan throw Exception agar kompatibel PHP 7.4 hingga 8.4
        if (defined('DONT_DIE_ON_ERROR') && DONT_DIE_ON_ERROR === true) {
            throw new \RuntimeException($reason);
        }
        die();
    };

    // [WIZDAM FIX-C] OJS 2.x tidak memiliki REST API — hanya AJAX internal jQuery
    $isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                     strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // [WIZDAM FIX-B] JSON response: pesan generik ke client, $reason hanya ke error_log
    if ($isAjaxRequest) {
        header('Content-Type: application/json');
        echo json_encode([
            'status'    => false,
            'error'     => $httpStatus === 404 ? 'Not Found' : 'Internal Server Error',
            'message'   => $httpStatus === 404 ? 'The requested resource was not found.' : 'An internal error occurred. Please try again later.',
            'timestamp' => date('c')
        ]);
        $logAndDie();
        return;
    }

    $is404 = $httpStatus === 404;

    $pageTitle   = $is404 ? 'Page Not Found (404) | Wizdam Editorial' : 'Unavailable (500) | Wizdam Editorial';
    $mainMessage = $is404 ? 'Page Not Found' : 'Application Unavailable';
    
    if ($is404) {
        $userFacingContent = '<p>The page you requested could not be found. Please check the URL address or return to the main page. This could be due to an incorrect address or an internal routing issue. ScholarWizdam part of Sangia unless otherwise stated.</p>';
    } else {
        $errorContext = '';
        if (!empty($reason)) {
            $errorContext .= ' (' . htmlspecialchars($reason, ENT_QUOTES, 'UTF-8') . ').';
        }
        $userFacingContent = "<p>This ScholarWizdam application is currently unavailable due to an internal system error. {$errorContext} ScholarWizdam part of Sangia unless otherwise stated.</p>";
    }

    $css = "
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        html { height: 100%; }
        body { min-height: 100%; padding: 10% 0; }
        html {
            background-color: #fff;
            color: #222;
            font-family: -apple-system, BlinkMacSystemFont, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.5;
            text-align: left;
        }
        .live-area { padding: 0 2%; margin: 0 auto; max-width: 800px; }
        #brand { width: 80%; max-width: 220px; margin-bottom: 0.25em; }
        .message {
            background-color: #fff;
            border-radius: 4px;
            border: 1px solid #e2e2e2;
            box-shadow: 0 20px 20px -10px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            padding: 3% 10% 4% 5%;
        }
        h1 { font-weight: normal; line-height: 1.25; font-size: 1.6em; margin-bottom: 1em; padding-right: 10%; }
        code { border-radius: 3px; padding: .4em .75em .35em; margin-left: .75em; color: #555; }
        @media screen and (min-width: 768px) {
            .message { padding: 3% 10% 4% 5%; }
            h1 { padding-right: 40%; }
        }
        @media screen and (min-width: 1024px) { html { font-size: 110%; } }
    ";

    echo "<!DOCTYPE html>";
    echo "<html lang='en'>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<meta http-equiv='x-ua-compatible' content='IE=edge'>";
    echo "<meta name='viewport' content='width=device-width,initial-scale=1'>";
    echo "<title>{$pageTitle}</title>";
    echo "<style type='text/css'>{$css}</style>";
    echo "</head>";
    echo "<body>";
    echo "<div class='live-area'>";
    echo "<img id='brand' alt='Sangia Publishing' src='//assets.sangia.org/img/sangia-black-branded-v3.svg'>";
    echo "<div class='message'>";
    echo "<h1>{$mainMessage}</h1>";
    echo $userFacingContent;
    echo "<p>Please try again later or refresh your browser. Thank you for your patience.</p>";
    echo "</div>";
    echo "</div>";
    echo "</body></html>";

    $logAndDie();
}

/**
 * Check to see if the server meets a minimum version requirement for PHP.
 * @param string $version
 * @return bool
 */
function checkPhpVersion(string $version): bool {
    return version_compare(PHP_VERSION, $version) !== -1;
}

/**
 * Create a shallow copy of the given object.
 * @param object $object
 * @return object
 */
function cloneObject(object $object): object {
    return clone $object;
}

/**
 * Instantiates an object for a given fully qualified class name.
 * [WIZDAM] Updated Regex to allow Namespaces (Backslashes).
 * @param string $fullyQualifiedClassName (e.g., 'lib.pkp...Core' OR 'APP\core\Application')
 * @param mixed $expectedTypes
 * @param mixed $expectedPackages
 * @param mixed $expectedMethods
 * @param mixed $constructorArg
 * @return object|false
 */
function instantiate(string $fullyQualifiedClassName, $expectedTypes = null, $expectedPackages = null, $expectedMethods = null, $constructorArg = null) {
    $errorFlag = false;

    if (!PKPString::regexp_match('/^[a-zA-Z0-9._\\\\]+$/', $fullyQualifiedClassName)) {
        // [WIZDAM FIX-D] Log agar silent fail tidak menyulitkan debugging di production
        error_log('[WIZDAM] instantiate(): Invalid class name rejected: ' . $fullyQualifiedClassName);
        return $errorFlag;
    }

    if (strpos($fullyQualifiedClassName, '\\') === false && $expectedPackages !== null) {
        if (is_scalar($expectedPackages)) {
            $expectedPackages = [$expectedPackages];
        }
        
        $validPackage = false;
        foreach ($expectedPackages as $expectedPackage) {
            if (substr($fullyQualifiedClassName, 0, strlen($expectedPackage) + 1) == $expectedPackage . '.') {
                $validPackage = true;
                break;
            }
        }

        if (!$validPackage) {
            fatalError('Class instantiation violation: "' . $fullyQualifiedClassName . '" is not in expected packages.');
        }
    }

    import($fullyQualifiedClassName);

    if (strpos($fullyQualifiedClassName, '\\') !== false) {
        $className = $fullyQualifiedClassName;
    } else {
        $parts = explode('.', $fullyQualifiedClassName);
        $className = array_pop($parts);
    }

    if (!class_exists($className)) {
        fatalError('Cannot instantiate class. Class "' . $className . '" not found.');
    }

    $classInstance = $constructorArg === null ? new $className() : new $className($constructorArg);

    if ($expectedTypes !== null) {
        if (is_scalar($expectedTypes)) {
            $expectedTypes = [$expectedTypes];
        }
        
        $validType = false;
        foreach ($expectedTypes as $expectedType) {
            if ($classInstance instanceof $expectedType) {
                $validType = true;
                break;
            }
        }
        
        if (!$validType) return $errorFlag;
    }

    return $classInstance;
}

/**
 * Remove empty elements from an array.
 * @param array $array
 * @return array
 */
function arrayClean(array $array): array {
    return array_filter($array, fn($o) => !empty($o));
}

/**
 * Recursively strip HTML from a (multidimensional) array.
 * @param array $values
 * @param bool $useClientCharset
 * @return array
 */
function stripAssocArray(array $values, bool $useClientCharset = false): array {
    foreach ($values as $key => $value) {
        if (is_scalar($value)) {
            $valStr = (string) $value;
            $values[$key] = strip_tags($valStr);
            
            if ($useClientCharset && strtolower(Config::getVar('i18n', 'client_charset')) !== 'utf-8') {
                $values[$key] = html_entity_decode($values[$key], ENT_QUOTES, Config::getVar('i18n', 'client_charset'));
            } else {
                $values[$key] = PKPString::html2utf($values[$key]);
            }
        } else {
            $values[$key] = stripAssocArray((array) $values[$key], $useClientCharset);
        }
    }
    return $values;
}

/**
 * Perform a code-safe strtolower.
 * @param string $str
 * @return string
 */
function strtolower_codesafe(string $str): string {
    return strtr($str, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', 'abcdefghijklmnopqrstuvwxyz');
}
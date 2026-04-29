<?php
declare(strict_types=1);

/**
 * Registers a shutdown function to handle fatal errors in a controlled manner.
 *
 * This function checks for fatal errors at the end of script execution and
 * outputs a generic error message while logging the detailed error information.
 * It avoids interfering if a custom shutdown handler is already defined.
 */

function registerWizdamFatalErrorHandler(): void {
    register_shutdown_function(function () {

        // Kalau rich handler sudah ada, JANGAN ambil alih
        if (function_exists('wizdamShutdownHandler')) {
            return;
        }

        $error = error_get_last();
        if (!$error) {
            return;
        }

        if (!in_array($error['type'], [
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_COMPILE_ERROR,
            E_USER_ERROR
        ], true)) {
            return;
        }

        while (ob_get_level()) {
            ob_end_clean();
        }

        $reason = sprintf(
            '[%d] %s in %s on line %d',
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );

        header('HTTP/1.1 500 Internal Server Error');
        error_log($reason);
        echo 'System Error';
        die();
    });
}
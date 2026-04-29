<?php
/**
 * File: /plugins/themes/[nama_tema]/php/turnstile/helper.php
 * 
 * Helper functions untuk integrasi Turnstile dengan OJS
 */

class TurnstileHelper {
    
    /**
     * Cek apakah user sudah terverifikasi Turnstile
     * 
     * @param int $timeout Timeout dalam detik (default dari config)
     * @return bool True jika sudah terverifikasi dan belum timeout
     */
    public static function isVerified($timeout = null) {
        if ($timeout === null) {
            $timeout = defined('TURNSTILE_TIMEOUT') ? TURNSTILE_TIMEOUT : 300;
        }
        
        if (!isset($_SESSION)) {
            session_start();
        }
        
        if (!isset($_SESSION['turnstile_verified']) || !$_SESSION['turnstile_verified']) {
            return false;
        }
        
        if (!isset($_SESSION['turnstile_timestamp'])) {
            return false;
        }
        
        // Cek timeout
        if (time() - $_SESSION['turnstile_timestamp'] > $timeout) {
            self::resetVerification();
            return false;
        }
        
        return true;
    }
    
    /**
     * Reset status verifikasi Turnstile
     */
    public static function resetVerification() {
        if (!isset($_SESSION)) {
            session_start();
        }
        unset($_SESSION['turnstile_verified']);
        unset($_SESSION['turnstile_timestamp']);
    }
    
    /**
     * Set status terverifikasi dengan timestamp
     */
    public static function setVerified() {
        if (!isset($_SESSION)) {
            session_start();
        }
        $_SESSION['turnstile_verified'] = true;
        $_SESSION['turnstile_timestamp'] = time();
    }
    
    /**
     * Generate HTML untuk widget Turnstile
     * 
     * @param string $siteKey Site key Turnstile
     * @param string $containerId ID container untuk widget
     * @param string $theme Tema widget (auto, light, dark)
     * @return string HTML untuk container widget
     */
    public static function generateWidget($siteKey, $containerId = 'turnstile-container', $theme = 'auto') {
        return '<div id="' . htmlspecialchars($containerId) . '" class="turnstile-widget"></div>';
    }
    
    /**
     * Generate JavaScript untuk inisialisasi widget Turnstile
     * 
     * @param string $siteKey Site key Turnstile
     * @param string $containerId ID container untuk widget
     * @param string $theme Tema widget
     * @return string JavaScript code
     */
    public static function generateScript($siteKey, $containerId = 'turnstile-container', $theme = 'auto') {
        return '
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<script>
var turnstileLoaded = false;
var turnstileToken = null;

window.onloadTurnstileCallback = function() {
    if (turnstileLoaded) return;
    turnstileLoaded = true;
    
    if (document.getElementById("' . htmlspecialchars($containerId) . '")) {
        turnstile.render("#' . htmlspecialchars($containerId) . '", {
            sitekey: "' . htmlspecialchars($siteKey) . '",
            theme: "' . htmlspecialchars($theme) . '",
            callback: function(token) {
                turnstileToken = token;
                console.log("Turnstile verification completed");
                
                // Trigger custom event
                var event = new CustomEvent("turnstileSuccess", {detail: {token: token}});
                document.dispatchEvent(event);
            },
            "error-callback": function() {
                turnstileToken = null;
                console.error("Turnstile verification failed");
                
                // Trigger custom event
                var event = new CustomEvent("turnstileError");
                document.dispatchEvent(event);
            },
            "expired-callback": function() {
                turnstileToken = null;
                console.warn("Turnstile token expired");
                
                // Trigger custom event
                var event = new CustomEvent("turnstileExpired");
                document.dispatchEvent(event);
            }
        });
    }
};

// Helper function untuk mendapatkan token
function getTurnstileToken() {
    return turnstileToken;
}

// Helper function untuk reset widget
function resetTurnstile() {
    if (turnstileLoaded && turnstile) {
        turnstile.reset();
        turnstileToken = null;
    }
}
</script>';
    }
    
    /**
     * Cek apakah halaman saat ini memerlukan verifikasi Turnstile
     * 
     * @return bool True jika halaman perlu verifikasi
     */
    public static function needsVerification() {
        $currentPath = $_SERVER['REQUEST_URI'];
        
        // Daftar path yang memerlukan verifikasi Turnstile
        $protectedPaths = array(
            '/login',
            '/user/register',
            '/user/account',
            '/login/signIn'
        );
        
        foreach ($protectedPaths as $path) {
            if (strpos($currentPath, $path) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get user IP address (support proxy/load balancer)
     * 
     * @return string IP address
     */
    public static function getUserIP() {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_REAL_IP'])) {
            return $_SERVER['HTTP_X_REAL_IP'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        }
        return '';
    }
}
?>
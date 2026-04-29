<?php
/**
 * Turnstile Integration for OJS 2.4.8.2
 * PHP 5.4+ Compatible Version
 * 
 * Location: /plugins/themes/[nama_tema]/php/turnstile/turnstile.php
 */

class TurnstileOJS {
    
    // KONFIGURASI - Edit bagian ini sesuai kebutuhan
    private static $config = array(
        'site_key' => '0x4AAAAAAA7b4JHByoY2iX27',
        'secret_key' => '0x4AAAAAAA7b4PNJSkfQ6jEM9Zt6hxZcdfY',
        'verify_url' => 'https://challenges.cloudflare.com/turnstile/v0/siteverify',
        'theme' => 'auto', // auto, light, dark
        'size' => 'normal', // normal, compact
        'timeout' => 30
    );
    
    /**
     * Get configuration value
     */
    public static function getConfig($key) {
        return isset(self::$config[$key]) ? self::$config[$key] : null;
    }
    
    /**
     * Set configuration value
     */
    public static function setConfig($key, $value) {
        self::$config[$key] = $value;
    }
    
    /**
     * Initialize Turnstile for templates
     */
    public static function initTemplate($templateManager) {
        // Set template variables
        $templateManager->assign('turnstileSiteKey', self::getConfig('site_key'));
        $templateManager->assign('turnstileTheme', self::getConfig('theme'));
        $templateManager->assign('turnstileSize', self::getConfig('size'));
        
        // Auto-detect if we need turnstile (login/register pages)
        $needsTurnstile = self::shouldShowTurnstile();
        $templateManager->assign('needsTurnstile', $needsTurnstile);
        
        // Get theme path
        $themePath = self::getThemePath($templateManager);
        $templateManager->assign('themePath', $themePath);
        
        // Debug logging
        //error_log("TurnstileOJS: initialized with needsTurnstile=" . ($needsTurnstile ? 'true' : 'false'));
        //error_log("TurnstileOJS: theme path=" . $themePath);
    }
    
    /**
     * Determine if current page needs Turnstile
     */
    public static function shouldShowTurnstile() {
        // Auto-detect based on URL or page
        $currentPage = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        
        // Force enable with URL parameter
        if (isset($_GET['turnstile'])) {
            return true;
        }
        
        // Enable on specific pages
        return (
            strpos($currentPage, 'login') !== false ||
            strpos($currentPage, 'register') !== false ||
            strpos($currentPage, 'user') !== false
        );
    }
    
    /**
     * Get current theme path from template manager
     */
    public static function getThemePath($templateManager) {
        $themePath = '';
        $templateDirs = (array)$templateManager->template_dir;
        
        foreach ($templateDirs as $dir) {
            if (preg_match('/plugins\/themes\/([a-zA-Z0-9_\-]+)/', $dir, $matches)) {
                $themePath = $matches[1];
                break;
            }
        }
        
        return $themePath;
    }
    
    /**
     * Validate Turnstile token
     */
    public static function validate($token, $remoteIp = null) {
        if (empty($token)) {
            return array(
                'success' => false,
                'error' => 'Token is required'
            );
        }
        
        $secretKey = self::getConfig('secret_key');
        if (empty($secretKey)) {
            return array(
                'success' => false,
                'error' => 'Secret key not configured'
            );
        }
        
        // Prepare POST data
        $postData = array(
            'secret' => $secretKey,
            'response' => $token
        );
        
        if ($remoteIp) {
            $postData['remoteip'] = $remoteIp;
        }
        
        // Make request to Cloudflare
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => self::getConfig('verify_url'),
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => self::getConfig('timeout'),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            )
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return array(
                'success' => false,
                'error' => 'CURL error: ' . $error
            );
        }
        
        if ($httpCode !== 200) {
            return array(
                'success' => false,
                'error' => 'HTTP error: ' . $httpCode
            );
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'success' => false,
                'error' => 'Invalid JSON response'
            );
        }
        
        return $result;
    }
    
    /**
     * Helper: Check if token is valid
     */
    public static function isValid($token, $remoteIp = null) {
        $result = self::validate($token, $remoteIp);
        return isset($result['success']) && $result['success'] === true;
    }
    
    /**
     * Helper: Get validation error message
     */
    public static function getError($token, $remoteIp = null) {
        $result = self::validate($token, $remoteIp);
        return isset($result['error']) ? $result['error'] : null;
    }
    
    /**
     * Generate JavaScript configuration
     */
    public static function getJavaScriptConfig($baseUrl, $themePath) {
        $config = array(
            'siteKey' => self::getConfig('site_key'),
            'proxyUrl' => $baseUrl . '/plugins/themes/' . $themePath . '/php/turnstile/proxy.php',
            'theme' => self::getConfig('theme'),
            'size' => self::getConfig('size')
        );
        
        return json_encode($config);
    }
}
<?php
/**
 * Turnstile Template Initialization for OJS 2.4.8.2
 * PHP 5.4+ Compatible Version
 * 
 * Location: /plugins/themes/[nama_tema]/php/turnstile/init.php
 */

// Include main class
require_once 'turnstile.php';

try {
    // Method 1: Try to use the template manager passed from Smarty
    if (isset($this) && method_exists($this, 'assign')) {
        TurnstileOJS::initTemplate($this);
        //error_log("Turnstile init: initialized using \$this template manager");
    } 
    // Method 2: Try global Smarty instance
    elseif (isset($smarty) && method_exists($smarty, 'assign')) {
        TurnstileOJS::initTemplate($smarty);
        //error_log("Turnstile init: initialized using global \$smarty");
    }
    // Method 3: Try to get template manager from globals
    elseif (isset($GLOBALS['smarty']) && method_exists($GLOBALS['smarty'], 'assign')) {
        TurnstileOJS::initTemplate($GLOBALS['smarty']);
        //error_log("Turnstile init: initialized using \$GLOBALS['smarty']");
    }
    // Method 4: Manual fallback (jika ada akses ke template manager)
    else {
        //error_log("Turnstile init: no template manager found, manual setup required");
        
        // Anda bisa uncomment dan sesuaikan jika diperlukan:
        /*
        // Manual assignment jika template manager tersedia dengan cara lain
        if (function_exists('getTemplateManager')) {
            $templateMgr = getTemplateManager();
            TurnstileOJS::initTemplate($templateMgr);
        }
        */
    }
    
} catch (Exception $e) {
    //error_log("Turnstile init error: " . $e->getMessage());
}
<?php
/**
 * File: /plugins/themes/[nama_tema]/php/turnstile/init.php
 * 
 * File inisialisasi untuk setup Turnstile di template OJS
 * Include file ini di template yang membutuhkan Turnstile
 */

// Start session jika belum ada
if (!isset($_SESSION)) {
    session_start();
}

// Include dependencies
require_once('config.php');
require_once('helper.php');

// Setup template variables untuk Smarty (jika tersedia)
if (isset($smarty) || class_exists('Smarty')) {
    global $smarty;
    
    // Coba dapatkan instance Smarty dari TemplateManager OJS
    if (!$smarty && function_exists('TemplateManager')) {
        $smarty = TemplateManager::getManager();
    }
    
    if ($smarty) {
        $smarty->assign('turnstileSiteKey', TURNSTILE_SITE_KEY);
        $smarty->assign('needsTurnstile', TurnstileHelper::needsVerification());
        $smarty->assign('turnstileVerified', TurnstileHelper::isVerified());
        $smarty->assign('turnstileTheme', TURNSTILE_DEFAULT_THEME);
        $smarty->assign('turnstileSize', TURNSTILE_DEFAULT_SIZE);
    }
}

// Setup global functions untuk template PHP (jika diperlukan)
function turnstile_site_key() {
    return TURNSTILE_SITE_KEY;
}

function turnstile_needs_verification() {
    return TurnstileHelper::needsVerification();
}

function turnstile_is_verified() {
    return TurnstileHelper::isVerified();
}

function turnstile_widget($containerId = 'turnstile-container', $theme = null) {
    if ($theme === null) {
        $theme = defined('TURNSTILE_DEFAULT_THEME') ? TURNSTILE_DEFAULT_THEME : 'auto';
    }
    return TurnstileHelper::generateWidget(TURNSTILE_SITE_KEY, $containerId, $theme);
}

function turnstile_script($containerId = 'turnstile-container', $theme = null) {
    if ($theme === null) {
        $theme = defined('TURNSTILE_DEFAULT_THEME') ? TURNSTILE_DEFAULT_THEME : 'auto';
    }
    return TurnstileHelper::generateScript(TURNSTILE_SITE_KEY, $containerId, $theme);
}

function turnstile_proxy_url() {
    // Detect base URL and theme path
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $baseUrl = $protocol . '://' . $_SERVER['HTTP_HOST'];
    
    // Try to get current theme path
    $themePath = '';
    if (isset($GLOBALS['currentTheme'])) {
        $themePath = $GLOBALS['currentTheme']->getPath();
    }
    
    return $baseUrl . '/plugins/themes/' . $themePath . '/php/turnstile/proxy.php';
}
?>
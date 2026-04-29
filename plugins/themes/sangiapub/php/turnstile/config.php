<?php
/**
 * File: /plugins/themes/[nama_tema]/php/turnstile/config.php
 * 
 * Konfigurasi Turnstile Cloudflare untuk OJS
 */

// Konfigurasi Turnstile Keys - Ganti dengan keys Anda yang sebenarnya
define('TURNSTILE_SECRET_KEY', '0x4AAAAAAA7b4PNJSkfQ6jEM9Zt6hxZcdfY');
define('TURNSTILE_SITE_KEY', '0x4AAAAAAA7b4JHByoY2iX27');

// Konfigurasi Timeout Verifikasi (dalam detik)
define('TURNSTILE_TIMEOUT', 300); // 5 menit

// Mode Debug - Set true untuk logging error
define('TURNSTILE_DEBUG', false);

// Tema default untuk widget
define('TURNSTILE_DEFAULT_THEME', 'auto'); // auto, light, dark

// Ukuran default widget
define('TURNSTILE_DEFAULT_SIZE', 'normal'); // normal, compact
?>
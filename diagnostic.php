<?php
declare(strict_types=1);

/*
 * WIZDAM System Diagnostic Endpoint
 *
 * This script provides a simple JSON response indicating the system status,
 * the application name, the PHP version, and the current server time.
 *
 * Usage: Access this script via a web browser or HTTP client to get the status.
 */

// --- 1. Logika Health Check (Pengecekan Kesehatan) ---
// Kita cek resource sebelum membuat output JSON
$diskFree = disk_free_space(".");
$diskTotal = disk_total_space(".");
$memUsage = memory_get_usage();

// Default status: OK (200)
$httpCode = 200;
$statusText = 'ok';
$message = 'System is operational.';

// CONTOH ALERT: Jika sisa disk kurang dari 100MB, ubah status jadi 503 (Error)
if ($diskFree < (100 * 1024 * 1024)) {
    $httpCode = 503; // Service Unavailable
    $statusText = 'warning';
    $message = 'Low disk space detected!';
}

// --- 2. Kirim HTTP Header ---
// Agar tools monitoring tahu status asli server (Hijau/Merah)
http_response_code($httpCode);
header('Content-Type: application/json; charset=utf-8');

// --- 3. Fungsi Helper ---
function formatSize($bytes) {
    if ($bytes <= 0) return '0 B';
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . ['B', 'KB', 'MB', 'GB', 'TB'][$i];
}

// Mengambil Load Average (Linux Only)
$load = function_exists('sys_getloadavg') ? sys_getloadavg() : null;

// --- 4. Menyusun Data JSON ---
$response = [
    // [META] Data teknis untuk API Client
    'meta' => [
        'code'      => $httpCode,
        'status'    => $statusText,
        'message'   => $message,
        'timestamp' => time(),
        'dt_human'  => date('Y-m-d H:i:s')
    ],

    // [APP] Informasi Aplikasi
    'app_info' => [
        'name'        => 'Wizdam Editorial Systems',
        'version'     => '1.0.0.0',
        'last_update' => '2025-10-27',
        'environment' => 'production'
    ],

    // [SYSTEM] Lingkungan Server
    'system' => [
        'php_version'     => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'sapi_name'       => php_sapi_name(),
        'timezone'        => date_default_timezone_get(),
        'server_time'     => date('Y-m-d H:i:s'),
    ],

    // [CONFIG] Pengaturan PHP (Data Baru yang Anda Minta)
    'php_config' => [
        // Waktu maksimal skrip boleh berjalan
        'max_execution_time' => ini_get('max_execution_time') . 's',
        
        // Waktu maksimal parsing input
        'max_input_time'     => ini_get('max_input_time') . 's',
        
        // Jumlah variabel maksimal (Anti DoS attack)
        'max_input_vars'     => (int)ini_get('max_input_vars'),
        
        // Batas memori per script
        'memory_limit'       => ini_get('memory_limit'),
        
        // Ukuran maksimal POST data
        'post_max_size'      => ini_get('post_max_size'),
        
        // Ukuran maksimal upload file (Penting untuk jurnal)
        'upload_max_filesize'=> ini_get('upload_max_filesize'),
        
        // Umur sesi login
        'session_gc_lifetime'=> ini_get('session.gc_maxlifetime') . 's',
    ],

    // [TEAM] Informasi Pengembang
    'developers' => [
         [
            'name'    => 'Rochmady',
            'role'    => 'Lead Project',
            'github'  => 'https://github.com/rochmady',
            'website' => 'https://wizdam.sangia.org',
            'contact' => 'rochmady@sangia.org'
        ],
        [
            'name'    => 'Wizdam Core Team',
            'role'    => 'Lead Development',
            'website' => 'https://wizdam.sangia.org',
            'contact' => 'tech@sangia.org'
        ],
        [
            'name'    => 'Susiana',
            'role'    => 'Backend Engineer',
            'website' => 'https://wizdam.sangia.org',
            'contact' => 'susiana@sangia.org'
        ],
        [
            'name'    => 'Darsilan',
            'role'    => 'Frontend & UI/UX',
            'dribbble'=> 'https://dribbble.com/darsilan',
            'contact' => 'darsilan@sangia.org'
        ]
    ],

    // [RESOURCE] Monitor Kesehatan Hardware
    'resources' => [
        'memory' => [
            'current' => formatSize($memUsage),
            'peak'    => formatSize(memory_get_peak_usage()), 
            'limit'   => ini_get('memory_limit'), 
        ],
        'disk' => [
            'free_space'  => formatSize($diskFree), 
            'total_space' => formatSize($diskTotal),
        ],
        'cpu_load' => [
            '1_min'  => $load[0] ?? 'n/a',
            '5_min'  => $load[1] ?? 'n/a',
            '15_min' => $load[2] ?? 'n/a',
        ]
    ]
];

// Output JSON
echo json_encode($response, JSON_PRETTY_PRINT);